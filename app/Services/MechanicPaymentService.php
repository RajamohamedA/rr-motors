<?php



namespace App\Services;



use App\Models\AccountTransaction;

use App\Models\Mechanic;

use App\Models\MechanicCommission;

use App\Models\MechanicPayment;

use App\Models\MechanicPaymentAllocation;

use App\Models\SalesReturn;

use Illuminate\Support\Carbon;

use Illuminate\Support\Facades\DB;

use Illuminate\Validation\ValidationException;



class MechanicPaymentService

{

    public function __construct(

        private readonly MechanicLedgerService $mechanicLedgerService,

        private readonly AccountTransactionService $accountTransactionService

    ) {}



    /*

    |--------------------------------------------------------------------------

    | Create Mechanic Payment

    |--------------------------------------------------------------------------

    |

    | Allocation format:

    |

    | [

    |     [

    |         'mechanic_commission_id' => 1,

    |         'allocated_amount' => 500,

    |     ],

    | ]

    |

    | Allocations may be empty. The unallocated amount becomes a mechanic

    | advance and produces a negative mechanic payable balance.

    |

    */



    public function create(

        array $data,

        array $allocations = [],

        ?int $createdBy = null

    ): MechanicPayment {

        $companyId = (int) ($data['company_id'] ?? 0);

        $storeId = (int) ($data['store_id'] ?? 0);

        $mechanicId = (int) ($data['mechanic_id'] ?? 0);

        $accountId = (int) ($data['account_id'] ?? 0);

        $amount = round((float) ($data['amount'] ?? 0), 2);



        $this->validateBasicData(

            companyId: $companyId,

            storeId: $storeId,

            mechanicId: $mechanicId,

            accountId: $accountId,

            amount: $amount,

            paymentDate: $data['payment_date'] ?? null,

            paymentMode: $data['payment_mode'] ?? null

        );



        $normalisedAllocations = $this->normaliseAllocations(

            $allocations

        );



        $allocatedTotal = round(

            array_sum(

                array_column(

                    $normalisedAllocations,

                    'allocated_amount'

                )

            ),

            2

        );



        if ($allocatedTotal > $amount) {

            throw ValidationException::withMessages([

                'allocations' =>

                'Allocated amount cannot exceed the payment amount.',

            ]);
        }



        return DB::transaction(function () use (

            $data,

            $normalisedAllocations,

            $companyId,

            $storeId,

            $mechanicId,

            $accountId,

            $amount,

            $createdBy

        ) {

            $mechanic = $this->resolveMechanic(

                companyId: $companyId,

                storeId: $storeId,

                mechanicId: $mechanicId,

                lock: true

            );



            /*

            |------------------------------------------------------------------

            | Lock and Validate Allocated Commissions

            |------------------------------------------------------------------

            */



            $commissions = $this->resolveCommissions(

                companyId: $companyId,

                storeId: $storeId,

                mechanicId: $mechanic->id,

                allocations: $normalisedAllocations

            );



            $paymentDate = Carbon::parse(

                $data['payment_date']

            )->toDateString();



            $this->accountTransactionService

                ->ensureSufficientBalance(

                    companyId: $companyId,

                    storeId: $storeId,

                    accountId: $accountId,

                    requiredAmount: $amount,

                    asOfDate: $paymentDate,

                    lock: true

                );



            $payment = MechanicPayment::query()->create([

                'company_id' => $companyId,

                'store_id' => $storeId,

                'mechanic_id' => $mechanic->id,

                'account_id' => $accountId,

                'payment_no' => MechanicPayment::generatePaymentNo(

                    $companyId,

                    $storeId

                ),

                'payment_date' => $paymentDate,

                'amount' => $amount,

                'payment_mode' => trim(

                    (string) $data['payment_mode']

                ),

                'reference_no' => $this->nullableString(

                    $data['reference_no'] ?? null

                ),

                'status' => MechanicPayment::STATUS_COMPLETED,

                'remarks' => $this->nullableString(

                    $data['remarks'] ?? null

                ),

                'created_by' => $createdBy,

                'cancelled_by' => null,

                'cancelled_at' => null,

            ]);



            foreach ($normalisedAllocations as $allocationData) {

                $commissionId = (int) $allocationData['mechanic_commission_id'];



                $commission = $commissions->get(

                    $commissionId

                );



                MechanicPaymentAllocation::query()->create([

                    'company_id' => $companyId,

                    'store_id' => $storeId,

                    'mechanic_payment_id' => $payment->id,

                    'mechanic_commission_id' => $commission->id,

                    'allocated_amount' => $allocationData['allocated_amount'],

                ]);



                $this->refreshCommissionPaymentSummary(

                    $commission

                );
            }



            /*

            |------------------------------------------------------------------

            | Mechanic Ledger

            |------------------------------------------------------------------

            */



            $this->mechanicLedgerService->recordPayment(

                payment: $payment,

                createdBy: $createdBy

            );



            /*

            |------------------------------------------------------------------

            | Cash / Bank Account Ledger

            |------------------------------------------------------------------

            |

            | Payment to mechanic decreases the selected account balance.

            |

            */



            $this->accountTransactionService->credit(

                companyId: $companyId,

                storeId: $storeId,

                accountId: $accountId,

                transactionDate: $paymentDate,

                transactionType: AccountTransaction::TYPE_MECHANIC_PAYMENT,

                referenceType: $payment->getMorphClass(),

                referenceId: (int) $payment->id,

                amount: $amount,

                referenceNo: $payment->payment_no,

                paymentMode: $payment->payment_mode,

                externalReference: $payment->reference_no,

                remarks: $payment->remarks

                    ?: 'Payment made to mechanic '

                    . $mechanic->mechanic_name,

                createdBy: $createdBy

            );



            return $payment->load([

                'mechanic',

                'account',

                'allocations.commission.sale',

                'creator',

            ]);
        });
    }



    /*

    |--------------------------------------------------------------------------

    | Cancel Mechanic Payment

    |--------------------------------------------------------------------------

    */



    public function cancel(

        MechanicPayment $payment,

        ?int $cancelledBy = null,

        ?string $remarks = null

    ): MechanicPayment {

        return DB::transaction(function () use (

            $payment,

            $cancelledBy,

            $remarks

        ) {

            $lockedPayment = MechanicPayment::query()

                ->whereKey($payment->id)

                ->lockForUpdate()

                ->firstOrFail();



            if ($lockedPayment->isCancelled()) {

                return $lockedPayment->load([

                    'mechanic',

                    'account',

                    'allocations.commission.sale',

                    'creator',

                    'canceller',

                ]);
            }



            $allocations = MechanicPaymentAllocation::query()

                ->where(

                    'mechanic_payment_id',

                    $lockedPayment->id

                )

                ->orderBy('id')

                ->lockForUpdate()

                ->get();



            $commissionIds = $allocations

                ->pluck('mechanic_commission_id')

                ->unique()

                ->values();



            $commissions = MechanicCommission::query()

                ->whereIn('id', $commissionIds)

                ->orderBy('id')

                ->lockForUpdate()

                ->get()

                ->keyBy('id');



            /*

            |------------------------------------------------------------------

            | Mark Cancelled Before Recalculating Allocations

            |------------------------------------------------------------------

            |

            | Completed-payment filtering will then exclude this payment.

            |

            */



            $lockedPayment->forceFill([

                'status' => MechanicPayment::STATUS_CANCELLED,

                'cancelled_by' => $cancelledBy,

                'cancelled_at' => now(),

                'remarks' => $remarks

                    ?: $lockedPayment->remarks,

            ])->save();



            foreach ($commissions as $commission) {

                $this->refreshCommissionPaymentSummary(

                    $commission

                );
            }



            $this->mechanicLedgerService->reversePayment(

                payment: $lockedPayment,

                createdBy: $cancelledBy,

                remarks: $remarks

                    ?: 'Mechanic payment cancelled.'

            );



            $this->accountTransactionService->reverseReference(

                companyId: (int) $lockedPayment->company_id,

                storeId: (int) $lockedPayment->store_id,

                referenceType: $lockedPayment->getMorphClass(),

                referenceId: (int) $lockedPayment->id,

                remarks: $remarks

                    ?: 'Mechanic payment cancelled.',

                createdBy: $cancelledBy

            );



            return $lockedPayment->fresh([

                'mechanic',

                'account',

                'allocations.commission.sale',

                'creator',

                'canceller',

            ]);
        });
    }



    /*

    |--------------------------------------------------------------------------

    | Commission Payment Summary

    |--------------------------------------------------------------------------

    */



    public function refreshCommissionPaymentSummary(

        MechanicCommission $commission

    ): MechanicCommission {

        $lockedCommission = MechanicCommission::query()

            ->whereKey($commission->id)

            ->lockForUpdate()

            ->firstOrFail();



        if ($lockedCommission->isCancelled()) {

            return $lockedCommission;
        }



        $paidAmount = (float) MechanicPaymentAllocation::query()

            ->where(

                'mechanic_commission_id',

                $lockedCommission->id

            )

            ->whereHas(

                'payment',

                fn($query) => $query->where(

                    'status',

                    MechanicPayment::STATUS_COMPLETED

                )

            )

            ->sum('allocated_amount');



        $originalCommissionAmount = round(

            (float) $lockedCommission->commission_amount,

            2

        );



        $returnedCommissionAmount =

            $this->returnedCommissionAmount(

                $lockedCommission,

                $originalCommissionAmount

            );



        $commissionAmount = max(

            0,

            round(

                $originalCommissionAmount

                    - $returnedCommissionAmount,

                2

            )

        );



        $paidAmount = min(

            round($paidAmount, 2),

            $originalCommissionAmount

        );



        $balanceAmount = max(

            0,

            round($commissionAmount - $paidAmount, 2)

        );



        $status = match (true) {

            $balanceAmount <= 0 =>

            MechanicCommission::STATUS_PAID,



            $paidAmount > 0 =>

            MechanicCommission::STATUS_PARTIALLY_PAID,



            default =>

            MechanicCommission::STATUS_PENDING,
        };



        $lockedCommission->forceFill([

            'paid_amount' => $paidAmount,

            'balance_amount' => $balanceAmount,

            'status' => $status,

        ])->save();



        $lockedCommission->sale()

            ->update([

                'commission_status' => $status,

            ]);



        return $lockedCommission;
    }



    /*

    |--------------------------------------------------------------------------

    | Sales Return Commission Reduction

    |--------------------------------------------------------------------------

    |

    | Sales-return commission adjustments are posted separately in the

    | mechanic ledger as debits. This helper also reduces the commission's

    | allocatable balance, preventing a later payment from paying commission

    | that has already been reversed by a sales return.

    |

    | When the original commission was already paid, paid_amount may exceed

    | the reduced commission. balance_amount remains zero and the excess is

    | represented as mechanic advance by the debit in the mechanic ledger.

    |

    */



    private function returnedCommissionAmount(

        MechanicCommission $commission,

        float $commissionAmount

    ): float {

        $commissionBase = round(

            (float) $commission->commission_base,

            2

        );



        if ($commissionBase <= 0 || $commissionAmount <= 0) {

            return 0;
        }



        $returnedBase = (float) SalesReturn::query()

            ->forContext(

                (int) $commission->company_id,

                (int) $commission->store_id

            )

            ->where('sale_id', $commission->sale_id)

            ->completed()

            ->sum('sub_total');



        return min(

            $commissionAmount,

            round(

                $commissionAmount

                    * min(1, max(0, $returnedBase) / $commissionBase),

                2

            )

        );
    }



    private function resolveMechanic(

        int $companyId,

        int $storeId,

        int $mechanicId,

        bool $lock

    ): Mechanic {

        $query = Mechanic::query()

            ->whereKey($mechanicId)

            ->forContext($companyId, $storeId)

            ->active();



        if ($lock) {

            $query->lockForUpdate();
        }



        $mechanic = $query->first();



        if (!$mechanic) {

            throw ValidationException::withMessages([

                'mechanic_id' =>

                'The selected mechanic is unavailable in the active branch.',

            ]);
        }



        return $mechanic;
    }



    private function resolveCommissions(

        int $companyId,

        int $storeId,

        int $mechanicId,

        array $allocations

    ) {

        if ($allocations === []) {

            return collect();
        }



        $commissionIds = collect($allocations)

            ->pluck('mechanic_commission_id')

            ->map(fn($id) => (int) $id)

            ->unique()

            ->sort()

            ->values();



        $commissions = MechanicCommission::query()

            ->forContext($companyId, $storeId)

            ->forMechanic($mechanicId)

            ->whereIn('id', $commissionIds)

            ->notCancelled()

            ->orderBy('id')

            ->lockForUpdate()

            ->get()

            ->keyBy('id');



        if ($commissions->count() !== $commissionIds->count()) {

            throw ValidationException::withMessages([

                'allocations' =>

                'One or more selected commissions are unavailable.',

            ]);
        }



        foreach ($allocations as $allocation) {

            $commission = $commissions->get(

                (int) $allocation['mechanic_commission_id']

            );



            $commission = $this->refreshCommissionPaymentSummary(

                $commission

            );



            $commissions->put(

                $commission->id,

                $commission

            );



            if (

                (float) $allocation['allocated_amount']

                > (float) $commission->balance_amount

            ) {

                throw ValidationException::withMessages([

                    'allocations' =>

                    'Allocated amount exceeds the commission balance for '

                        . ($commission->sale?->invoice_no

                            ?: 'commission #' . $commission->id)

                        . '.',

                ]);
            }
        }



        return $commissions;
    }



    private function normaliseAllocations(

        array $allocations

    ): array {

        $normalised = [];

        $seenCommissionIds = [];



        foreach ($allocations as $index => $allocation) {

            $commissionId = (int) (

                $allocation['mechanic_commission_id']

                ?? $allocation['commission_id']

                ?? 0

            );



            $amount = round(

                (float) ($allocation['allocated_amount'] ?? 0),

                2

            );



            if ($commissionId <= 0 || $amount <= 0) {

                throw ValidationException::withMessages([

                    "allocations.$index" =>

                    'Select a commission and enter an allocation greater than zero.',

                ]);
            }



            if (isset($seenCommissionIds[$commissionId])) {

                throw ValidationException::withMessages([

                    "allocations.$index" =>

                    'The same commission cannot be allocated twice.',

                ]);
            }



            $seenCommissionIds[$commissionId] = true;



            $normalised[] = [

                'mechanic_commission_id' => $commissionId,

                'allocated_amount' => $amount,

            ];
        }



        return $normalised;
    }



    private function validateBasicData(

        int $companyId,

        int $storeId,

        int $mechanicId,

        int $accountId,

        float $amount,

        mixed $paymentDate,

        mixed $paymentMode

    ): void {

        $errors = [];



        if ($companyId <= 0) {

            $errors['company_id'] = 'Active company is required.';
        }



        if ($storeId <= 0) {

            $errors['store_id'] = 'Active branch is required.';
        }



        if ($mechanicId <= 0) {

            $errors['mechanic_id'] = 'Select a mechanic.';
        }



        if ($accountId <= 0) {

            $errors['account_id'] = 'Select a cash or bank account.';
        }



        if ($amount <= 0) {

            $errors['amount'] =

                'Payment amount must be greater than zero.';
        }



        if (!$paymentDate) {

            $errors['payment_date'] = 'Payment date is required.';
        }



        if (trim((string) $paymentMode) === '') {

            $errors['payment_mode'] = 'Payment mode is required.';
        }



        if ($errors !== []) {

            throw ValidationException::withMessages($errors);
        }
    }



    private function nullableString(mixed $value): ?string

    {

        $value = trim((string) $value);



        return $value === '' ? null : $value;
    }
}
