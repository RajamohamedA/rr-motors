<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\CustomerLedgerEntry;
use App\Models\CustomerPayment;
use App\Models\Sales;
use App\Models\SalesReturn;
use Illuminate\Support\Carbon;
use Illuminate\Validation\ValidationException;

class CustomerLedgerService
{
    /*
    |--------------------------------------------------------------------------
    | Opening Balance
    |--------------------------------------------------------------------------
    |
    | Customer opening_balance is company-level, not branch-level. Therefore
    | it is posted once for the company/customer and belongs to the first
    | active branch that posts a transaction for that customer.
    |
    | Call this service from inside the source document DB transaction.
    |
    */
    public function ensureOpeningBalance(
        Customer $customer,
        int $companyId,
        int $storeId,
        ?int $createdBy = null
    ): ?CustomerLedgerEntry {
        $this->assertCustomerContext(
            $customer,
            $companyId,
            $storeId
        );
        $lockedCustomer = Customer::query()
            ->whereKey(
                $customer->id
            )
            ->where(
                'company_id',
                $companyId
            )
            ->lockForUpdate()
            ->firstOrFail();
        $existing = CustomerLedgerEntry::query()
            ->where(
                'company_id',
                $companyId
            )
            ->where(
                'customer_id',
                $lockedCustomer->id
            )
            ->where(
                'transaction_type',
                CustomerLedgerEntry::TYPE_OPENING_BALANCE
            )
            ->lockForUpdate()
            ->first();
        $amount = round(
            abs(
                (float) $lockedCustomer->opening_balance
            ),
            2
        );
        $isCredit = strtoupper(
            (string) $lockedCustomer->balance_type
        ) === 'CR';
        if ($existing) {
            if ($existing->hasBeenReversed()) {
                throw ValidationException::withMessages([
                    'opening_balance' =>
                    'A reversed opening balance cannot be modified.',
                ]);
            }
            if ($amount <= 0) {
                $existing->delete();
                return null;
            }
            $existing->update([
                'reference_no' => $lockedCustomer->customer_code,
                'debit' => $isCredit ? 0 : $amount,
                'credit' => $isCredit ? $amount : 0,
                'remarks' => 'Customer opening balance',
            ]);
            return $existing->refresh();
        }
        if ($amount <= 0) {
            return null;
        }
        return CustomerLedgerEntry::create([
            'company_id' => $companyId,
            'store_id' => $storeId,
            'customer_id' => $lockedCustomer->id,
            'transaction_date' =>
            $lockedCustomer->created_at
                ?->toDateString()
                ?? now()->toDateString(),
            'transaction_type' =>
            CustomerLedgerEntry::TYPE_OPENING_BALANCE,
            'reference_type' =>
            'customer_opening_balance',
            'reference_id' =>
            $lockedCustomer->id,
            'reference_no' =>
            $lockedCustomer->customer_code,
            'debit' =>
            $isCredit ? 0 : $amount,
            'credit' =>
            $isCredit ? $amount : 0,
            'is_reversal' => false,
            'reversal_of_id' => null,
            'remarks' =>
            'Customer opening balance',
            'created_by' => $createdBy,
        ]);
    }
    /*
    |--------------------------------------------------------------------------
    | Sale — Debit
    |--------------------------------------------------------------------------
    */
    public function postSale(
        Sales $sale,
        ?int $createdBy = null
    ): CustomerLedgerEntry {
        $this->assertSaleContext(
            $sale
        );
        $customer = $this->customer(
            $sale->customer_id,
            $sale->company_id,
            $sale->store_id
        );
        $this->ensureOpeningBalance(
            $customer,
            (int) $sale->company_id,
            (int) $sale->store_id,
            $createdBy
        );
        return $this->syncOriginalEntry(
            companyId: (int) $sale->company_id,
            storeId: (int) $sale->store_id,
            customerId: (int) $sale->customer_id,
            transactionDate: $sale->invoice_date,
            transactionType: CustomerLedgerEntry::TYPE_SALE,
            referenceType: 'sale',
            referenceId: (int) $sale->id,
            referenceNo: $sale->invoice_no,
            debit: (float) $sale->grand_total,
            credit: 0,
            remarks: $sale->remarks
                ?: "Sale invoice {$sale->invoice_no}",
            createdBy: $createdBy
        );
    }
    public function reverseSale(
        Sales $sale,
        string|Carbon|null $reversalDate = null,
        ?string $remarks = null,
        ?int $createdBy = null
    ): CustomerLedgerEntry {
        $this->assertSaleContext(
            $sale
        );
        $original = $this->originalEntry(
            (int) $sale->company_id,
            (int) $sale->store_id,
            'sale',
            (int) $sale->id,
            CustomerLedgerEntry::TYPE_SALE
        );
        return $this->reverseEntry(
            original: $original,
            reversalType: CustomerLedgerEntry::TYPE_SALE_REVERSAL,
            reversalDate: $reversalDate,
            remarks: $remarks
                ?: "Reversal of sale {$sale->invoice_no}",
            createdBy: $createdBy
        );
    }
    /*
    |--------------------------------------------------------------------------
    | Collection Received With Sale — Credit
    |--------------------------------------------------------------------------
    |
    | Only money collected directly on the invoice is posted here. Advance
    | already exists as a credit from its original customer receipt and must
    | never be credited for a second time when it is applied to a sale.
    |
    */
    public function syncSaleCollection(
        Sales $sale,
        float $amount,
        ?int $createdBy = null
    ): ?CustomerLedgerEntry {
        $this->assertSaleContext($sale);
        $amount = round($amount, 2);
        $existing = CustomerLedgerEntry::query()
            ->forContext(
                (int) $sale->company_id,
                (int) $sale->store_id
            )
            ->forReference('sale_collection', (int) $sale->id)
            ->where(
                'transaction_type',
                CustomerLedgerEntry::TYPE_CUSTOMER_PAYMENT
            )
            ->where('is_reversal', false)
            ->lockForUpdate()
            ->first();
        if ($amount <= 0) {
            if ($existing && $existing->hasBeenReversed()) {
                throw ValidationException::withMessages([
                    'ledger' =>
                    'A reversed sale collection cannot be modified.',
                ]);
            }
            $existing?->delete();
            return null;
        }
        $customer = $this->customer(
            (int) $sale->customer_id,
            (int) $sale->company_id,
            (int) $sale->store_id
        );
        $this->ensureOpeningBalance(
            $customer,
            (int) $sale->company_id,
            (int) $sale->store_id,
            $createdBy
        );
        return $this->syncOriginalEntry(
            companyId: (int) $sale->company_id,
            storeId: (int) $sale->store_id,
            customerId: (int) $sale->customer_id,
            transactionDate: $sale->invoice_date,
            transactionType: CustomerLedgerEntry::TYPE_CUSTOMER_PAYMENT,
            referenceType: 'sale_collection',
            referenceId: (int) $sale->id,
            referenceNo: $sale->invoice_no,
            debit: 0,
            credit: $amount,
            remarks: "Collection received with sale {$sale->invoice_no}",
            createdBy: $createdBy
        );
    }
    public function reverseSaleCollection(
        Sales $sale,
        string|Carbon|null $reversalDate = null,
        ?string $remarks = null,
        ?int $createdBy = null
    ): ?CustomerLedgerEntry {
        $this->assertSaleContext($sale);
        $original = CustomerLedgerEntry::query()
            ->forContext(
                (int) $sale->company_id,
                (int) $sale->store_id
            )
            ->forReference('sale_collection', (int) $sale->id)
            ->where(
                'transaction_type',
                CustomerLedgerEntry::TYPE_CUSTOMER_PAYMENT
            )
            ->where('is_reversal', false)
            ->lockForUpdate()
            ->first();
        if (!$original) {
            return null;
        }
        return $this->reverseEntry(
            original: $original,
            reversalType: CustomerLedgerEntry::TYPE_PAYMENT_REVERSAL,
            reversalDate: $reversalDate,
            remarks: $remarks
                ?: "Reversal of sale collection {$sale->invoice_no}",
            createdBy: $createdBy
        );
    }
    /*
    |--------------------------------------------------------------------------
    | Sales Return — Credit
    |--------------------------------------------------------------------------
    */
    public function postSalesReturn(
        SalesReturn $salesReturn,
        ?int $createdBy = null
    ): CustomerLedgerEntry {
        $this->assertSalesReturnContext(
            $salesReturn
        );
        $customer = $this->customer(
            $salesReturn->customer_id,
            $salesReturn->company_id,
            $salesReturn->store_id
        );
        $this->ensureOpeningBalance(
            $customer,
            (int) $salesReturn->company_id,
            (int) $salesReturn->store_id,
            $createdBy
        );
        return $this->syncOriginalEntry(
            companyId: (int) $salesReturn->company_id,
            storeId: (int) $salesReturn->store_id,
            customerId: (int) $salesReturn->customer_id,
            transactionDate: $salesReturn->return_date,
            transactionType: CustomerLedgerEntry::TYPE_SALES_RETURN,
            referenceType: 'sales_return',
            referenceId: (int) $salesReturn->id,
            referenceNo: $salesReturn->return_no,
            debit: 0,
            credit: (float) $salesReturn->total_amount,
            remarks: $salesReturn->remarks
                ?: "Sales return {$salesReturn->return_no}",
            createdBy: $createdBy
        );
    }
    public function reverseSalesReturn(
        SalesReturn $salesReturn,
        string|Carbon|null $reversalDate = null,
        ?string $remarks = null,
        ?int $createdBy = null
    ): CustomerLedgerEntry {
        $this->assertSalesReturnContext(
            $salesReturn
        );
        $original = $this->originalEntry(
            (int) $salesReturn->company_id,
            (int) $salesReturn->store_id,
            'sales_return',
            (int) $salesReturn->id,
            CustomerLedgerEntry::TYPE_SALES_RETURN
        );
        return $this->reverseEntry(
            original: $original,
            reversalType: CustomerLedgerEntry::TYPE_SALES_RETURN_REVERSAL,
            reversalDate: $reversalDate,
            remarks: $remarks
                ?: "Reversal of sales return {$salesReturn->return_no}",
            createdBy: $createdBy
        );
    }
    /*
    |--------------------------------------------------------------------------
    | Sales Return Refund — Debit
    |--------------------------------------------------------------------------
    |
    | A sales return first credits the customer. When money is actually paid
    | back, this debit clears that credit and the account transaction records
    | the cash/bank outflow.
    |
    */
    public function syncSalesReturnRefund(
        SalesReturn $salesReturn,
        float $amount,
        ?int $createdBy = null
    ): ?CustomerLedgerEntry {
        $this->assertSalesReturnContext($salesReturn);
        $amount = round($amount, 2);
        $existing = CustomerLedgerEntry::query()
            ->forContext(
                (int) $salesReturn->company_id,
                (int) $salesReturn->store_id
            )
            ->forReference(
                'sales_return_refund',
                (int) $salesReturn->id
            )
            ->where('transaction_type', 'sales_return_refund')
            ->where('is_reversal', false)
            ->lockForUpdate()
            ->first();
        if ($amount <= 0) {
            if ($existing && $existing->hasBeenReversed()) {
                throw ValidationException::withMessages([
                    'refund_status' =>
                    'A reversed sales return refund cannot be modified.',
                ]);
            }
            $existing?->delete();
            return null;
        }
        return $this->syncOriginalEntry(
            companyId: (int) $salesReturn->company_id,
            storeId: (int) $salesReturn->store_id,
            customerId: (int) $salesReturn->customer_id,
            transactionDate: $salesReturn->return_date,
            transactionType: 'sales_return_refund',
            referenceType: 'sales_return_refund',
            referenceId: (int) $salesReturn->id,
            referenceNo: $salesReturn->return_no,
            debit: $amount,
            credit: 0,
            remarks: "Refund paid for sales return {$salesReturn->return_no}",
            createdBy: $createdBy
        );
    }
    public function reverseSalesReturnRefund(
        SalesReturn $salesReturn,
        string|Carbon|null $reversalDate = null,
        ?string $remarks = null,
        ?int $createdBy = null
    ): ?CustomerLedgerEntry {
        $this->assertSalesReturnContext($salesReturn);
        $original = CustomerLedgerEntry::query()
            ->forContext(
                (int) $salesReturn->company_id,
                (int) $salesReturn->store_id
            )
            ->forReference(
                'sales_return_refund',
                (int) $salesReturn->id
            )
            ->where('transaction_type', 'sales_return_refund')
            ->where('is_reversal', false)
            ->lockForUpdate()
            ->first();
        if (!$original) {
            return null;
        }
        return $this->reverseEntry(
            original: $original,
            reversalType: 'sales_return_refund_reversal',
            reversalDate: $reversalDate,
            remarks: $remarks
                ?: "Reversal of refund {$salesReturn->return_no}",
            createdBy: $createdBy
        );
    }
    /*
    |--------------------------------------------------------------------------
    | Customer Payment — Credit
    |--------------------------------------------------------------------------
    */
    public function postCustomerPayment(
        CustomerPayment $payment,
        ?int $createdBy = null
    ): CustomerLedgerEntry {
        $this->assertPaymentContext(
            $payment
        );
        $customer = $this->customer(
            $payment->customer_id,
            $payment->company_id,
            $payment->store_id
        );
        $this->ensureOpeningBalance(
            $customer,
            (int) $payment->company_id,
            (int) $payment->store_id,
            $createdBy
        );
        return $this->syncOriginalEntry(
            companyId: (int) $payment->company_id,
            storeId: (int) $payment->store_id,
            customerId: (int) $payment->customer_id,
            transactionDate: $payment->payment_date,
            transactionType: CustomerLedgerEntry::TYPE_CUSTOMER_PAYMENT,
            referenceType: 'customer_payment',
            referenceId: (int) $payment->id,
            referenceNo: $payment->receipt_no,
            debit: 0,
            credit: (float) $payment->amount,
            remarks: $payment->remarks
                ?: "Customer receipt {$payment->receipt_no}",
            createdBy: $createdBy
        );
    }
    public function reverseCustomerPayment(
        CustomerPayment $payment,
        string|Carbon|null $reversalDate = null,
        ?string $remarks = null,
        ?int $createdBy = null
    ): CustomerLedgerEntry {
        $this->assertPaymentContext(
            $payment
        );
        $original = $this->originalEntry(
            (int) $payment->company_id,
            (int) $payment->store_id,
            'customer_payment',
            (int) $payment->id,
            CustomerLedgerEntry::TYPE_CUSTOMER_PAYMENT
        );
        return $this->reverseEntry(
            original: $original,
            reversalType: CustomerLedgerEntry::TYPE_PAYMENT_REVERSAL,
            reversalDate: $reversalDate,
            remarks: $remarks
                ?: "Reversal of customer receipt {$payment->receipt_no}",
            createdBy: $createdBy
        );
    }
    /*
    |--------------------------------------------------------------------------
    | Current Balance
    |--------------------------------------------------------------------------
    */
    public function currentBalance(
        int $companyId,
        int $storeId,
        int $customerId
    ): float {
        return round(
            (float) CustomerLedgerEntry::query()
                ->forContext(
                    $companyId,
                    $storeId
                )
                ->forCustomer(
                    $customerId
                )
                ->selectRaw(
                    'COALESCE(SUM(debit - credit), 0) AS balance'
                )
                ->value(
                    'balance'
                ),
            2
        );
    }
    /*
    |--------------------------------------------------------------------------
    | Internal Entry Synchronization
    |--------------------------------------------------------------------------
    */
    private function syncOriginalEntry(
        int $companyId,
        int $storeId,
        int $customerId,
        string|Carbon $transactionDate,
        string $transactionType,
        string $referenceType,
        int $referenceId,
        ?string $referenceNo,
        float $debit,
        float $credit,
        ?string $remarks,
        ?int $createdBy
    ): CustomerLedgerEntry {
        $debit = round(
            $debit,
            2
        );
        $credit = round(
            $credit,
            2
        );
        if ($debit < 0 || $credit < 0) {
            throw ValidationException::withMessages([
                'ledger' =>
                'Ledger debit and credit cannot be negative.',
            ]);
        }
        if (
            ($debit > 0 && $credit > 0)
            || ($debit <= 0 && $credit <= 0)
        ) {
            throw ValidationException::withMessages([
                'ledger' =>
                'A ledger entry must contain either a debit or a credit amount.',
            ]);
        }
        $existing = CustomerLedgerEntry::query()
            ->forContext(
                $companyId,
                $storeId
            )
            ->forReference(
                $referenceType,
                $referenceId
            )
            ->where(
                'transaction_type',
                $transactionType
            )
            ->lockForUpdate()
            ->first();
        if (
            $existing
            && $existing->hasBeenReversed()
        ) {
            throw ValidationException::withMessages([
                'ledger' =>
                'A reversed ledger transaction cannot be modified.',
            ]);
        }
        $values = [
            'customer_id' => $customerId,
            'transaction_date' =>
            $this->dateString(
                $transactionDate
            ),
            'reference_no' => $referenceNo,
            'debit' => $debit,
            'credit' => $credit,
            'is_reversal' => false,
            'reversal_of_id' => null,
            'remarks' => $remarks,
            'created_by' => $createdBy,
        ];
        if ($existing) {
            $existing->update(
                $values
            );
            return $existing->refresh();
        }
        return CustomerLedgerEntry::create(
            array_merge(
                [
                    'company_id' => $companyId,
                    'store_id' => $storeId,
                    'transaction_type' =>
                    $transactionType,
                    'reference_type' =>
                    $referenceType,
                    'reference_id' =>
                    $referenceId,
                ],
                $values
            )
        );
    }
    private function originalEntry(
        int $companyId,
        int $storeId,
        string $referenceType,
        int $referenceId,
        string $transactionType
    ): CustomerLedgerEntry {
        return CustomerLedgerEntry::query()
            ->forContext(
                $companyId,
                $storeId
            )
            ->forReference(
                $referenceType,
                $referenceId
            )
            ->where(
                'transaction_type',
                $transactionType
            )
            ->where(
                'is_reversal',
                false
            )
            ->lockForUpdate()
            ->firstOrFail();
    }
    private function reverseEntry(
        CustomerLedgerEntry $original,
        string $reversalType,
        string|Carbon|null $reversalDate,
        string $remarks,
        ?int $createdBy
    ): CustomerLedgerEntry {
        $existing = CustomerLedgerEntry::query()
            ->where(
                'reversal_of_id',
                $original->id
            )
            ->lockForUpdate()
            ->first();
        if ($existing) {
            return $existing;
        }
        return CustomerLedgerEntry::create([
            'company_id' => $original->company_id,
            'store_id' => $original->store_id,
            'customer_id' => $original->customer_id,
            'transaction_date' =>
            $this->dateString(
                $reversalDate
                    ?? now()
            ),
            'transaction_type' => $reversalType,
            'reference_type' => $original->reference_type,
            'reference_id' => $original->reference_id,
            'reference_no' => $original->reference_no,
            'debit' => $original->credit,
            'credit' => $original->debit,
            'is_reversal' => true,
            'reversal_of_id' => $original->id,
            'remarks' => $remarks,
            'created_by' => $createdBy,
        ]);
    }
    /*
    |--------------------------------------------------------------------------
    | Context Validation
    |--------------------------------------------------------------------------
    */
    private function customer(
        int $customerId,
        int $companyId,
        int $storeId
    ): Customer {
        return Customer::query()
            ->whereKey(
                $customerId
            )
            ->where(
                'company_id',
                $companyId
            )
            ->whereHas(
                'stores',
                fn($query) =>
                $query->where(
                    'stores.id',
                    $storeId
                )
            )
            ->lockForUpdate()
            ->firstOrFail();
    }
    private function assertCustomerContext(
        Customer $customer,
        int $companyId,
        int $storeId
    ): void {
        $available =
            (int) $customer->company_id === $companyId
            && $customer
            ->stores()
            ->where(
                'stores.id',
                $storeId
            )
            ->exists();
        abort_unless(
            $available,
            404
        );
    }
    private function assertSaleContext(
        Sales $sale
    ): void {
        abort_unless(
            (int) $sale->company_id > 0
                && (int) $sale->store_id > 0
                && (int) $sale->customer_id > 0,
            422,
            'The sale context is incomplete.'
        );
    }
    private function assertSalesReturnContext(
        SalesReturn $salesReturn
    ): void {
        abort_unless(
            (int) $salesReturn->company_id > 0
                && (int) $salesReturn->store_id > 0
                && (int) $salesReturn->customer_id > 0,
            422,
            'The sales return context is incomplete.'
        );
    }
    private function assertPaymentContext(
        CustomerPayment $payment
    ): void {
        abort_unless(
            (int) $payment->company_id > 0
                && (int) $payment->store_id > 0
                && (int) $payment->customer_id > 0,
            422,
            'The customer payment context is incomplete.'
        );
    }
    private function dateString(
        string|Carbon $date
    ): string {
        return $date instanceof Carbon
            ? $date->toDateString()
            : Carbon::parse(
                $date
            )->toDateString();
    }
}
