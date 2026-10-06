<?php

namespace App\Services;

use App\Models\Mechanic;
use App\Models\MechanicCommission;
use App\Models\MechanicLedgerEntry;
use App\Models\MechanicPayment;
use App\Models\SalesReturn;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class MechanicLedgerService
{
    public const DIRECTION_DEBIT = 'debit';

    public const DIRECTION_CREDIT = 'credit';

    /*
    |--------------------------------------------------------------------------
    | Record Commission
    |--------------------------------------------------------------------------
    |
    | Commission is a credit because it increases the amount payable to the
    | mechanic.
    |
    */

    public function recordCommission(
        MechanicCommission $commission,
        ?int $createdBy = null
    ): MechanicLedgerEntry {
        $commission->loadMissing([
            'sale:id,invoice_no,invoice_date',
        ]);

        if ($commission->isCancelled()) {
            throw ValidationException::withMessages([
                'commission' =>
                'A cancelled mechanic commission cannot be posted.',
            ]);
        }

        return $this->credit(
            companyId: (int) $commission->company_id,
            storeId: (int) $commission->store_id,
            mechanicId: (int) $commission->mechanic_id,
            transactionDate: $commission->sale?->invoice_date
                ? $commission->sale->invoice_date->toDateString()
                : now()->toDateString(),
            transactionType: MechanicLedgerEntry::TYPE_COMMISSION,
            referenceType: $commission->getMorphClass(),
            referenceId: (int) $commission->id,
            amount: (float) $commission->commission_amount,
            referenceNo: $commission->sale?->invoice_no,
            remarks: $commission->remarks
                ?: 'Commission earned from sales invoice.',
            createdBy: $createdBy
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Record Mechanic Payment
    |--------------------------------------------------------------------------
    |
    | Payment is a debit because it reduces the amount payable to the mechanic.
    |
    */

    public function recordPayment(
        MechanicPayment $payment,
        ?int $createdBy = null
    ): MechanicLedgerEntry {
        if (!$payment->isCompleted()) {
            throw ValidationException::withMessages([
                'payment' =>
                'Only a completed mechanic payment can be posted.',
            ]);
        }

        return $this->debit(
            companyId: (int) $payment->company_id,
            storeId: (int) $payment->store_id,
            mechanicId: (int) $payment->mechanic_id,
            transactionDate: $payment->payment_date->toDateString(),
            transactionType: MechanicLedgerEntry::TYPE_MECHANIC_PAYMENT,
            referenceType: $payment->getMorphClass(),
            referenceId: (int) $payment->id,
            amount: (float) $payment->amount,
            referenceNo: $payment->payment_no,
            remarks: $payment->remarks
                ?: 'Payment made to mechanic.',
            createdBy: $createdBy
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Record Sales Return Commission Reduction
    |--------------------------------------------------------------------------
    |
    | A sales return reduces commission payable and is therefore a debit.
    | If the commission was already paid, this debit creates mechanic advance.
    |
    */

    public function recordSalesReturnCommission(
        SalesReturn $salesReturn,
        int $mechanicId,
        float $amount,
        ?int $createdBy = null,
        ?string $invoiceNo = null
    ): MechanicLedgerEntry {
        if (!$salesReturn->isCompleted()) {
            throw ValidationException::withMessages([
                'sales_return' =>
                'Only a completed sales return can reduce commission.',
            ]);
        }

        return $this->debit(
            companyId: (int) $salesReturn->company_id,
            storeId: (int) $salesReturn->store_id,
            mechanicId: $mechanicId,
            transactionDate: $salesReturn->return_date->toDateString(),
            transactionType: MechanicLedgerEntry::TYPE_SALES_RETURN_COMMISSION,
            referenceType: $salesReturn->getMorphClass(),
            referenceId: (int) $salesReturn->id,
            amount: $amount,
            referenceNo: $salesReturn->return_no,
            remarks: 'Commission reduced for sales return '
                . $salesReturn->return_no
                . ($invoiceNo ? ' against invoice ' . $invoiceNo : '')
                . '. Any excess debit is mechanic advance.',
            createdBy: $createdBy
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Adjustment
    |--------------------------------------------------------------------------
    |
    | Credit adjustment increases mechanic payable.
    | Debit adjustment decreases mechanic payable.
    |
    */

    public function recordAdjustment(
        int $companyId,
        int $storeId,
        int $mechanicId,
        string $transactionDate,
        string $referenceType,
        int $referenceId,
        float $amount,
        string $direction,
        ?string $referenceNo = null,
        ?string $remarks = null,
        ?int $createdBy = null
    ): MechanicLedgerEntry {
        return $this->record(
            companyId: $companyId,
            storeId: $storeId,
            mechanicId: $mechanicId,
            transactionDate: $transactionDate,
            transactionType: MechanicLedgerEntry::TYPE_ADJUSTMENT,
            referenceType: $referenceType,
            referenceId: $referenceId,
            amount: $amount,
            direction: $direction,
            referenceNo: $referenceNo,
            remarks: $remarks,
            createdBy: $createdBy
        );
    }

    public function debit(
        int $companyId,
        int $storeId,
        int $mechanicId,
        string $transactionDate,
        string $transactionType,
        string $referenceType,
        int $referenceId,
        float $amount,
        ?string $referenceNo = null,
        ?string $remarks = null,
        ?int $createdBy = null
    ): MechanicLedgerEntry {
        return $this->record(
            companyId: $companyId,
            storeId: $storeId,
            mechanicId: $mechanicId,
            transactionDate: $transactionDate,
            transactionType: $transactionType,
            referenceType: $referenceType,
            referenceId: $referenceId,
            amount: $amount,
            direction: self::DIRECTION_DEBIT,
            referenceNo: $referenceNo,
            remarks: $remarks,
            createdBy: $createdBy
        );
    }

    public function credit(
        int $companyId,
        int $storeId,
        int $mechanicId,
        string $transactionDate,
        string $transactionType,
        string $referenceType,
        int $referenceId,
        float $amount,
        ?string $referenceNo = null,
        ?string $remarks = null,
        ?int $createdBy = null
    ): MechanicLedgerEntry {
        return $this->record(
            companyId: $companyId,
            storeId: $storeId,
            mechanicId: $mechanicId,
            transactionDate: $transactionDate,
            transactionType: $transactionType,
            referenceType: $referenceType,
            referenceId: $referenceId,
            amount: $amount,
            direction: self::DIRECTION_CREDIT,
            referenceNo: $referenceNo,
            remarks: $remarks,
            createdBy: $createdBy
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Generic Ledger Posting
    |--------------------------------------------------------------------------
    */

    public function record(
        int $companyId,
        int $storeId,
        int $mechanicId,
        string $transactionDate,
        string $transactionType,
        string $referenceType,
        int $referenceId,
        float $amount,
        string $direction,
        ?string $referenceNo = null,
        ?string $remarks = null,
        ?int $createdBy = null
    ): MechanicLedgerEntry {
        $amount = $this->normaliseAmount($amount);

        $this->validateDirection($direction);
        $this->validateTransactionType($transactionType);

        return DB::transaction(function () use (
            $companyId,
            $storeId,
            $mechanicId,
            $transactionDate,
            $transactionType,
            $referenceType,
            $referenceId,
            $amount,
            $direction,
            $referenceNo,
            $remarks,
            $createdBy
        ) {
            $mechanic = $this->resolveMechanic(
                companyId: $companyId,
                storeId: $storeId,
                mechanicId: $mechanicId,
                requireActive: true,
                lock: true
            );

            $existing = MechanicLedgerEntry::query()
                ->forContext($companyId, $storeId)
                ->forMechanic($mechanic->id)
                ->forReference($referenceType, $referenceId)
                ->where('transaction_type', $transactionType)
                ->where('is_reversal', false)
                ->whereDoesntHave('reversals')
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return $existing;
            }

            return MechanicLedgerEntry::query()->create([
                'company_id' => $companyId,
                'store_id' => $storeId,
                'mechanic_id' => $mechanic->id,
                'transaction_date' => Carbon::parse(
                    $transactionDate
                )->toDateString(),
                'transaction_type' => $transactionType,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'reference_no' => $referenceNo,
                'debit' => $direction === self::DIRECTION_DEBIT
                    ? $amount
                    : 0,
                'credit' => $direction === self::DIRECTION_CREDIT
                    ? $amount
                    : 0,
                'is_reversal' => false,
                'reversal_of_id' => null,
                'remarks' => $remarks,
                'created_by' => $createdBy,
            ]);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Reversals
    |--------------------------------------------------------------------------
    */

    public function reverse(
        MechanicLedgerEntry $entry,
        ?string $remarks = null,
        ?int $createdBy = null,
        ?string $transactionDate = null
    ): MechanicLedgerEntry {
        return DB::transaction(function () use (
            $entry,
            $remarks,
            $createdBy,
            $transactionDate
        ) {
            $original = MechanicLedgerEntry::query()
                ->whereKey($entry->id)
                ->lockForUpdate()
                ->firstOrFail();

            return $this->createReversal(
                original: $original,
                remarks: $remarks,
                createdBy: $createdBy,
                transactionDate: $transactionDate
            );
        });
    }

    public function reverseReference(
        int $companyId,
        int $storeId,
        string $referenceType,
        int $referenceId,
        ?string $remarks = null,
        ?int $createdBy = null,
        ?string $transactionDate = null
    ): array {
        return DB::transaction(function () use (
            $companyId,
            $storeId,
            $referenceType,
            $referenceId,
            $remarks,
            $createdBy,
            $transactionDate
        ) {
            $entries = MechanicLedgerEntry::query()
                ->forContext($companyId, $storeId)
                ->forReference($referenceType, $referenceId)
                ->where('is_reversal', false)
                ->whereDoesntHave('reversals')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $reversals = [];

            foreach ($entries as $entry) {
                $reversals[] = $this->createReversal(
                    original: $entry,
                    remarks: $remarks,
                    createdBy: $createdBy,
                    transactionDate: $transactionDate
                );
            }

            return $reversals;
        });
    }

    public function reverseCommission(
        MechanicCommission $commission,
        ?int $createdBy = null,
        ?string $remarks = null
    ): array {
        return $this->reverseReference(
            companyId: (int) $commission->company_id,
            storeId: (int) $commission->store_id,
            referenceType: $commission->getMorphClass(),
            referenceId: (int) $commission->id,
            remarks: $remarks
                ?: 'Mechanic commission cancelled.',
            createdBy: $createdBy
        );
    }

    public function reversePayment(
        MechanicPayment $payment,
        ?int $createdBy = null,
        ?string $remarks = null
    ): array {
        return $this->reverseReference(
            companyId: (int) $payment->company_id,
            storeId: (int) $payment->store_id,
            referenceType: $payment->getMorphClass(),
            referenceId: (int) $payment->id,
            remarks: $remarks
                ?: 'Mechanic payment cancelled.',
            createdBy: $createdBy
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Payable Balance
    |--------------------------------------------------------------------------
    |
    | Positive result means the business owes the mechanic.
    | Negative result means the mechanic has received an advance.
    |
    */

    public function balance(
        int $companyId,
        int $storeId,
        int $mechanicId,
        ?string $asOfDate = null
    ): float {
        $this->resolveMechanic(
            companyId: $companyId,
            storeId: $storeId,
            mechanicId: $mechanicId,
            requireActive: false,
            lock: false
        );

        $query = MechanicLedgerEntry::query()
            ->forContext($companyId, $storeId)
            ->forMechanic($mechanicId);

        if ($asOfDate) {
            $query->asOf(
                Carbon::parse($asOfDate)->toDateString()
            );
        }

        $totals = $query
            ->selectRaw(
                'COALESCE(SUM(credit), 0) AS total_credit, '
                    . 'COALESCE(SUM(debit), 0) AS total_debit'
            )
            ->first();

        return round(
            (float) $totals->total_credit
                - (float) $totals->total_debit,
            2
        );
    }

    public function companyBalance(
        int $companyId,
        int $mechanicId,
        ?string $asOfDate = null
    ): float {
        $query = MechanicLedgerEntry::query()
            ->forCompany($companyId)
            ->forMechanic($mechanicId);

        if ($asOfDate) {
            $query->asOf(
                Carbon::parse($asOfDate)->toDateString()
            );
        }

        $totals = $query
            ->selectRaw(
                'COALESCE(SUM(credit), 0) AS total_credit, '
                    . 'COALESCE(SUM(debit), 0) AS total_debit'
            )
            ->first();

        return round(
            (float) $totals->total_credit
                - (float) $totals->total_debit,
            2
        );
    }

    private function createReversal(
        MechanicLedgerEntry $original,
        ?string $remarks,
        ?int $createdBy,
        ?string $transactionDate
    ): MechanicLedgerEntry {
        if ($original->is_reversal) {
            throw ValidationException::withMessages([
                'ledger_entry' =>
                'A reversal ledger entry cannot be reversed directly.',
            ]);
        }

        $existing = MechanicLedgerEntry::query()
            ->where('reversal_of_id', $original->id)
            ->lockForUpdate()
            ->first();

        if ($existing) {
            return $existing;
        }

        return MechanicLedgerEntry::query()->create([
            'company_id' => $original->company_id,
            'store_id' => $original->store_id,
            'mechanic_id' => $original->mechanic_id,
            'transaction_date' => $transactionDate
                ? Carbon::parse($transactionDate)->toDateString()
                : now()->toDateString(),
            'transaction_type' => $this->reversalTypeFor(
                $original->transaction_type
            ),
            'reference_type' => $original->reference_type,
            'reference_id' => $original->reference_id,
            'reference_no' => $original->reference_no,
            'debit' => $original->credit,
            'credit' => $original->debit,
            'is_reversal' => true,
            'reversal_of_id' => $original->id,
            'remarks' => $remarks
                ?: 'Reversal of mechanic ledger entry #'
                . $original->id,
            'created_by' => $createdBy,
        ]);
    }

    private function reversalTypeFor(
        string $transactionType
    ): string {
        return match ($transactionType) {
            MechanicLedgerEntry::TYPE_COMMISSION =>
            MechanicLedgerEntry::TYPE_COMMISSION_REVERSAL,

            MechanicLedgerEntry::TYPE_MECHANIC_PAYMENT =>
            MechanicLedgerEntry::TYPE_PAYMENT_REVERSAL,

            MechanicLedgerEntry::TYPE_SALES_RETURN_COMMISSION =>
            MechanicLedgerEntry::TYPE_SALES_RETURN_COMMISSION_REVERSAL,

            default =>
            MechanicLedgerEntry::TYPE_ADJUSTMENT_REVERSAL,
        };
    }

    private function resolveMechanic(
        int $companyId,
        int $storeId,
        int $mechanicId,
        bool $requireActive,
        bool $lock
    ): Mechanic {
        $query = Mechanic::query()
            ->whereKey($mechanicId)
            ->forCompany($companyId)
            ->forStore($storeId);

        if ($requireActive) {
            $query->active();
        }

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

    private function normaliseAmount(float $amount): float
    {
        $amount = round($amount, 2);

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' =>
                'Ledger amount must be greater than zero.',
            ]);
        }

        return $amount;
    }

    private function validateDirection(string $direction): void
    {
        if (
            !in_array(
                $direction,
                [
                    self::DIRECTION_DEBIT,
                    self::DIRECTION_CREDIT,
                ],
                true
            )
        ) {
            throw ValidationException::withMessages([
                'direction' => 'Invalid ledger direction.',
            ]);
        }
    }

    private function validateTransactionType(
        string $transactionType
    ): void {
        if (
            !in_array(
                $transactionType,
                MechanicLedgerEntry::transactionTypes(),
                true
            )
        ) {
            throw ValidationException::withMessages([
                'transaction_type' =>
                'Invalid mechanic ledger transaction type.',
            ]);
        }
    }
}
