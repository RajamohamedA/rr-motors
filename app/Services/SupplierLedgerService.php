<?php

namespace App\Services;

use App\Models\Purchase;
use App\Models\PurchaseReturn;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\SupplierLedgerEntry;
use App\Models\SupplierPayment;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SupplierLedgerService
{
    /*
    |--------------------------------------------------------------------------
    | Post Supplier Opening Balance
    |--------------------------------------------------------------------------
    |
    | Supplier opening balances are company-level values. The store receiving
    | the opening balance must therefore be explicitly supplied.
    |
    */

    public function postOpeningBalance(
        Supplier $supplier,
        int $storeId,
        ?int $createdBy = null
    ): ?SupplierLedgerEntry {

        $amount = round(
            (float) $supplier->opening_balance,
            2
        );


        if ($amount <= 0) {
            return null;
        }


        $store = Store::query()
            ->whereKey($storeId)
            ->where(
                'company_id',
                $supplier->company_id
            )
            ->firstOrFail();


        $balanceType = strtoupper(
            (string) $supplier->balance_type
        );


        return $this->postEntry(
            companyId: (int) $supplier->company_id,
            storeId: (int) $store->id,
            supplierId: (int) $supplier->id,

            transactionDate: $supplier->created_at
                ? $supplier->created_at->toDateString()
                : now()->toDateString(),

            transactionType: SupplierLedgerEntry::TYPE_OPENING_BALANCE,

            reference: $supplier,
            referenceNo: $supplier->supplier_code,

            debit: $balanceType === 'DR'
                ? $amount
                : 0,

            credit: $balanceType === 'CR'
                ? $amount
                : 0,

            remarks: 'Supplier opening balance',

            createdBy: $createdBy
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Post Purchase
    |--------------------------------------------------------------------------
    |
    | A purchase increases the amount payable to the supplier.
    | Therefore, the purchase amount is posted as a credit.
    |
    */

    public function postPurchase(
        Purchase $purchase,
        ?int $createdBy = null
    ): SupplierLedgerEntry {

        $supplier =
            Supplier::query()
            ->findOrFail(
                $purchase->supplier_id
            );

        $this->postOpeningBalance(
            $supplier,
            (int) $purchase->store_id,
            $createdBy
        );

        $this->validateSourceContext(
            source: $purchase,
            companyId: (int) $purchase->company_id,
            storeId: (int) $purchase->store_id,
            supplierId: (int) $purchase->supplier_id
        );


        $amount = round(
            (float) $purchase->grand_total,
            2
        );


        $this->validatePositiveAmount(
            $amount,
            'Purchase grand total'
        );


        return $this->postEntry(
            companyId: (int) $purchase->company_id,
            storeId: (int) $purchase->store_id,
            supplierId: (int) $purchase->supplier_id,

            transactionDate: $purchase->purchase_date,

            transactionType: SupplierLedgerEntry::TYPE_PURCHASE,

            reference: $purchase,

            referenceNo: $purchase->purchase_no,

            debit: 0,

            credit: $amount,

            remarks: 'Purchase posted to supplier ledger',

            createdBy: $createdBy
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Reverse Purchase
    |--------------------------------------------------------------------------
    */

    public function reversePurchase(
        Purchase $purchase,
        ?string $reversalDate = null,
        ?string $remarks = null,
        ?int $createdBy = null
    ): SupplierLedgerEntry {

        return $this->reverseEntry(
            reference: $purchase,
            originalType: SupplierLedgerEntry::TYPE_PURCHASE,
            reversalType: 'purchase_reversal',
            reversalDate: $reversalDate,
            remarks: $remarks
                ?? "Reversal of purchase {$purchase->purchase_no}",
            createdBy: $createdBy
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Post Purchase Return
    |--------------------------------------------------------------------------
    |
    | A purchase return reduces the amount payable to the supplier.
    | Therefore, the return amount is posted as a debit.
    |
    */

    public function postPurchaseReturn(
        PurchaseReturn $purchaseReturn,
        ?int $createdBy = null
    ): SupplierLedgerEntry {

        $purchaseReturn->loadMissing(
            'purchase'
        );


        $purchase =
            $purchaseReturn->purchase;


        if (!$purchase) {
            throw ValidationException::withMessages([
                'purchase_return' =>
                'The purchase return does not have a valid purchase.',
            ]);
        }


        $companyId = (int) (
            $purchaseReturn->company_id
            ?? $purchase->company_id
        );

        $storeId = (int) (
            $purchaseReturn->store_id
            ?? $purchase->store_id
        );

        $supplierId = (int) (
            $purchaseReturn->supplier_id
            ?? $purchase->supplier_id
        );


        $supplier =
            Supplier::query()
            ->findOrFail(
                $supplierId
            );

        $this->postOpeningBalance(
            $supplier,
            $storeId,
            $createdBy
        );


        $this->validateSourceContext(
            source: $purchaseReturn,
            companyId: $companyId,
            storeId: $storeId,
            supplierId: $supplierId
        );


        $amount = round(
            (float) (
                $purchaseReturn->total_amount
                ?? $purchaseReturn->grand_total
                ?? 0
            ),
            2
        );


        $this->validatePositiveAmount(
            $amount,
            'Purchase return total'
        );


        return $this->postEntry(
            companyId: $companyId,
            storeId: $storeId,
            supplierId: $supplierId,

            transactionDate: $purchaseReturn->return_date,

            transactionType: SupplierLedgerEntry::TYPE_PURCHASE_RETURN,

            reference: $purchaseReturn,

            referenceNo: $purchaseReturn->return_no,

            debit: $amount,

            credit: 0,

            remarks: 'Purchase return posted to supplier ledger',

            createdBy: $createdBy
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Reverse Purchase Return
    |--------------------------------------------------------------------------
    */

    public function reversePurchaseReturn(
        PurchaseReturn $purchaseReturn,
        ?string $reversalDate = null,
        ?string $remarks = null,
        ?int $createdBy = null
    ): SupplierLedgerEntry {

        return $this->reverseEntry(
            reference: $purchaseReturn,
            originalType: SupplierLedgerEntry::TYPE_PURCHASE_RETURN,
            reversalType: 'purchase_return_reversal',
            reversalDate: $reversalDate,
            remarks: $remarks
                ?? "Reversal of purchase return {$purchaseReturn->return_no}",
            createdBy: $createdBy
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Post Supplier Payment
    |--------------------------------------------------------------------------
    |
    | A supplier payment reduces the amount payable.
    | Therefore, the payment is posted as a debit.
    |
    */

    public function postSupplierPayment(
        SupplierPayment $supplierPayment,
        ?int $createdBy = null
    ): SupplierLedgerEntry {

        $supplier =
            Supplier::query()
            ->findOrFail(
                $supplierPayment->supplier_id
            );

        $this->postOpeningBalance(
            $supplier,
            (int) $supplierPayment->store_id,
            $createdBy
        );

        $this->validateSourceContext(
            source: $supplierPayment,
            companyId: (int) $supplierPayment->company_id,
            storeId: (int) $supplierPayment->store_id,
            supplierId: (int) $supplierPayment->supplier_id
        );


        if (
            strtolower(
                (string) $supplierPayment->status
            ) === 'cancelled'
        ) {
            throw ValidationException::withMessages([
                'supplier_payment' =>
                'A cancelled supplier payment cannot be posted.',
            ]);
        }


        $amount = round(
            (float) $supplierPayment->amount,
            2
        );


        $this->validatePositiveAmount(
            $amount,
            'Supplier payment amount'
        );


        return $this->postEntry(
            companyId: (int) $supplierPayment->company_id,

            storeId: (int) $supplierPayment->store_id,

            supplierId: (int) $supplierPayment->supplier_id,

            transactionDate: $supplierPayment->payment_date,

            transactionType: SupplierLedgerEntry::TYPE_SUPPLIER_PAYMENT,

            reference: $supplierPayment,

            referenceNo: $supplierPayment->payment_no,

            debit: $amount,

            credit: 0,

            remarks: $supplierPayment->remarks
                ?: 'Supplier payment posted to supplier ledger',

            createdBy: $createdBy
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Reverse Supplier Payment
    |--------------------------------------------------------------------------
    |
    | The original payment entry remains unchanged.
    | A reversal credit is created to restore the supplier payable.
    |
    */

    public function reverseSupplierPayment(
        SupplierPayment $supplierPayment,
        ?string $reversalDate = null,
        ?string $remarks = null,
        ?int $createdBy = null
    ): SupplierLedgerEntry {

        return DB::transaction(
            function () use (
                $supplierPayment,
                $reversalDate,
                $remarks,
                $createdBy
            ): SupplierLedgerEntry {

                $originalEntry =
                    SupplierLedgerEntry::query()
                    ->where(
                        'company_id',
                        $supplierPayment->company_id
                    )
                    ->where(
                        'store_id',
                        $supplierPayment->store_id
                    )
                    ->where(
                        'supplier_id',
                        $supplierPayment->supplier_id
                    )
                    ->where(
                        'reference_type',
                        $supplierPayment->getMorphClass()
                    )
                    ->where(
                        'reference_id',
                        $supplierPayment->id
                    )
                    ->where(
                        'transaction_type',
                        SupplierLedgerEntry::TYPE_SUPPLIER_PAYMENT
                    )
                    ->lockForUpdate()
                    ->first();


                if (!$originalEntry) {
                    throw ValidationException::withMessages([
                        'supplier_payment' =>
                        'The original supplier payment ledger entry was not found.',
                    ]);
                }


                $existingReversal =
                    SupplierLedgerEntry::query()
                    ->where(
                        'company_id',
                        $supplierPayment->company_id
                    )
                    ->where(
                        'store_id',
                        $supplierPayment->store_id
                    )
                    ->where(
                        'reference_type',
                        $supplierPayment->getMorphClass()
                    )
                    ->where(
                        'reference_id',
                        $supplierPayment->id
                    )
                    ->where(
                        'transaction_type',
                        SupplierLedgerEntry::TYPE_PAYMENT_REVERSAL
                    )
                    ->first();


                if ($existingReversal) {
                    return $existingReversal;
                }


                return SupplierLedgerEntry::query()
                    ->create([
                        'company_id' =>
                        $originalEntry->company_id,

                        'store_id' =>
                        $originalEntry->store_id,

                        'supplier_id' =>
                        $originalEntry->supplier_id,

                        'transaction_date' =>
                        $reversalDate
                            ?? now()->toDateString(),

                        'transaction_type' =>
                        SupplierLedgerEntry::TYPE_PAYMENT_REVERSAL,

                        'reference_type' =>
                        $supplierPayment->getMorphClass(),

                        'reference_id' =>
                        $supplierPayment->id,

                        'reference_no' =>
                        $supplierPayment->payment_no,

                        'debit' =>
                        0,

                        'credit' =>
                        $originalEntry->debit,

                        'is_reversal' =>
                        true,

                        'reversal_of_id' =>
                        $originalEntry->id,

                        'remarks' =>
                        $remarks
                            ?? 'Reversal of cancelled supplier payment',

                        'created_by' =>
                        $createdBy
                            ?? Auth::id(),
                    ]);
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Reverse Generic Source Entry
    |--------------------------------------------------------------------------
    */

    private function reverseEntry(
        Model $reference,
        string $originalType,
        string $reversalType,
        ?string $reversalDate,
        ?string $remarks,
        ?int $createdBy
    ): SupplierLedgerEntry {

        return DB::transaction(
            function () use (
                $reference,
                $originalType,
                $reversalType,
                $reversalDate,
                $remarks,
                $createdBy
            ): SupplierLedgerEntry {

                $originalEntry =
                    SupplierLedgerEntry::query()
                    ->where(
                        'reference_type',
                        $reference->getMorphClass()
                    )
                    ->where(
                        'reference_id',
                        $reference->getKey()
                    )
                    ->where(
                        'transaction_type',
                        $originalType
                    )
                    ->lockForUpdate()
                    ->first();


                if (!$originalEntry) {
                    throw ValidationException::withMessages([
                        'ledger' =>
                        'The original supplier ledger entry was not found.',
                    ]);
                }


                return SupplierLedgerEntry::query()
                    ->firstOrCreate(
                        [
                            'company_id' =>
                            $originalEntry->company_id,

                            'store_id' =>
                            $originalEntry->store_id,

                            'reference_type' =>
                            $reference->getMorphClass(),

                            'reference_id' =>
                            $reference->getKey(),

                            'transaction_type' =>
                            $reversalType,
                        ],
                        [
                            'supplier_id' =>
                            $originalEntry->supplier_id,

                            'transaction_date' =>
                            $reversalDate
                                ?? now()->toDateString(),

                            'reference_no' =>
                            $originalEntry->reference_no,

                            'debit' =>
                            $originalEntry->credit,

                            'credit' =>
                            $originalEntry->debit,

                            'is_reversal' =>
                            true,

                            'reversal_of_id' =>
                            $originalEntry->id,

                            'remarks' =>
                            $remarks,

                            'created_by' =>
                            $createdBy
                                ?? Auth::id(),
                        ]
                    );
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Post Ledger Entry
    |--------------------------------------------------------------------------
    */

    private function postEntry(
        int $companyId,
        int $storeId,
        int $supplierId,
        mixed $transactionDate,
        string $transactionType,
        Model $reference,
        ?string $referenceNo,
        float $debit,
        float $credit,
        ?string $remarks,
        ?int $createdBy
    ): SupplierLedgerEntry {

        $debit = round(
            $debit,
            2
        );

        $credit = round(
            $credit,
            2
        );


        $this->validateDebitAndCredit(
            $debit,
            $credit
        );


        return DB::transaction(
            function () use (
                $companyId,
                $storeId,
                $supplierId,
                $transactionDate,
                $transactionType,
                $reference,
                $referenceNo,
                $debit,
                $credit,
                $remarks,
                $createdBy
            ): SupplierLedgerEntry {

                return SupplierLedgerEntry::query()
                    ->updateOrCreate(
                        [
                            'company_id' =>
                            $companyId,

                            'store_id' =>
                            $storeId,

                            'reference_type' =>
                            $reference->getMorphClass(),

                            'reference_id' =>
                            $reference->getKey(),

                            'transaction_type' =>
                            $transactionType,
                        ],
                        [
                            'supplier_id' =>
                            $supplierId,

                            'transaction_date' =>
                            $transactionDate,

                            'reference_no' =>
                            $referenceNo,

                            'debit' =>
                            $debit,

                            'credit' =>
                            $credit,

                            'is_reversal' =>
                            false,

                            'reversal_of_id' =>
                            null,

                            'remarks' =>
                            $remarks,

                            'created_by' =>
                            $createdBy
                                ?? Auth::id(),
                        ]
                    );
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Validate Source Context
    |--------------------------------------------------------------------------
    */

    private function validateSourceContext(
        Model $source,
        int $companyId,
        int $storeId,
        int $supplierId
    ): void {

        if (
            $companyId <= 0 ||
            $storeId <= 0 ||
            $supplierId <= 0
        ) {
            throw ValidationException::withMessages([
                'ledger' =>
                'The supplier ledger transaction context is invalid.',
            ]);
        }


        $storeExists =
            Store::query()
            ->whereKey(
                $storeId
            )
            ->where(
                'company_id',
                $companyId
            )
            ->exists();


        if (!$storeExists) {
            throw ValidationException::withMessages([
                'store_id' =>
                'The selected branch does not belong to the transaction company.',
            ]);
        }


        $supplierExists =
            Supplier::query()
            ->whereKey(
                $supplierId
            )
            ->where(
                'company_id',
                $companyId
            )
            ->whereHas(
                'stores',
                function ($query) use (
                    $storeId
                ): void {

                    $query->where(
                        'stores.id',
                        $storeId
                    );
                }
            )
            ->exists();


        if (!$supplierExists) {
            throw ValidationException::withMessages([
                'supplier_id' =>
                'The supplier is not available in the active company and branch.',
            ]);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Validate Positive Amount
    |--------------------------------------------------------------------------
    */

    private function validatePositiveAmount(
        float $amount,
        string $field
    ): void {

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' =>
                "{$field} must be greater than zero.",
            ]);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Validate Debit and Credit
    |--------------------------------------------------------------------------
    */

    private function validateDebitAndCredit(
        float $debit,
        float $credit
    ): void {

        if ($debit < 0 || $credit < 0) {
            throw ValidationException::withMessages([
                'ledger' =>
                'Ledger debit and credit values cannot be negative.',
            ]);
        }


        if ($debit > 0 && $credit > 0) {
            throw ValidationException::withMessages([
                'ledger' =>
                'A supplier ledger entry cannot contain both debit and credit.',
            ]);
        }


        if ($debit <= 0 && $credit <= 0) {
            throw ValidationException::withMessages([
                'ledger' =>
                'A supplier ledger entry must contain a debit or credit amount.',
            ]);
        }
    }
}
