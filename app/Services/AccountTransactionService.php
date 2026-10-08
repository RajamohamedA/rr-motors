<?php

namespace App\Services;

use App\Models\Account;
use App\Models\AccountTransaction;
use App\Models\CustomerPayment;
use App\Models\MechanicPayment;
use App\Models\SalePayment;
use App\Models\SalesReturn;
use App\Models\SupplierPayment;
use App\Models\Voucher;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AccountTransactionService
{
    public const DIRECTION_DEBIT = 'debit';

    public const DIRECTION_CREDIT = 'credit';

    /*
    |--------------------------------------------------------------------------
    | Debit
    |--------------------------------------------------------------------------
    |
    | Debit increases the selected Cash, Bank, Card or UPI account balance.
    |
    */

    public function debit(
        int $companyId,
        int $storeId,
        int $accountId,
        string $transactionDate,
        string $transactionType,
        string $referenceType,
        int $referenceId,
        float $amount,
        ?string $referenceNo = null,
        ?string $paymentMode = null,
        ?string $externalReference = null,
        ?string $remarks = null,
        ?int $createdBy = null
    ): AccountTransaction {
        return $this->record(
            companyId: $companyId,
            storeId: $storeId,
            accountId: $accountId,
            transactionDate: $transactionDate,
            transactionType: $transactionType,
            referenceType: $referenceType,
            referenceId: $referenceId,
            amount: $amount,
            direction: self::DIRECTION_DEBIT,
            referenceNo: $referenceNo,
            paymentMode: $paymentMode,
            externalReference: $externalReference,
            remarks: $remarks,
            createdBy: $createdBy
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Credit
    |--------------------------------------------------------------------------
    |
    | Credit decreases the selected Cash, Bank, Card or UPI account balance.
    |
    */

    public function credit(
        int $companyId,
        int $storeId,
        int $accountId,
        string $transactionDate,
        string $transactionType,
        string $referenceType,
        int $referenceId,
        float $amount,
        ?string $referenceNo = null,
        ?string $paymentMode = null,
        ?string $externalReference = null,
        ?string $remarks = null,
        ?int $createdBy = null
    ): AccountTransaction {
        return $this->record(
            companyId: $companyId,
            storeId: $storeId,
            accountId: $accountId,
            transactionDate: $transactionDate,
            transactionType: $transactionType,
            referenceType: $referenceType,
            referenceId: $referenceId,
            amount: $amount,
            direction: self::DIRECTION_CREDIT,
            referenceNo: $referenceNo,
            paymentMode: $paymentMode,
            externalReference: $externalReference,
            remarks: $remarks,
            createdBy: $createdBy
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Business Document Posting Helpers
    |--------------------------------------------------------------------------
    |
    | These methods keep controllers from deciding debit/credit direction or
    | inventing transaction-type strings. Reference names intentionally retain
    | compatibility with the existing payment and return controllers.
    |
    */

    public function postSalePayment(
        SalePayment $payment,
        ?int $createdBy = null
    ): AccountTransaction {
        $this->requireAccountId(
            $payment->account_id,
            'A Cash/Bank account is required for this sale payment.'
        );

        return $this->debit(
            companyId: (int) $payment->company_id,
            storeId: (int) $payment->store_id,
            accountId: (int) $payment->account_id,
            transactionDate: $this->dateString($payment->payment_date),
            transactionType: AccountTransaction::TYPE_SALE,
            referenceType: $payment->getMorphClass(),
            referenceId: (int) $payment->id,
            amount: (float) $payment->amount,
            referenceNo: $payment->reference_no,
            paymentMode: $payment->payment_mode,
            externalReference: $payment->reference_no,
            remarks: $payment->remarks ?: 'Payment received against sales invoice',
            createdBy: $createdBy ?? $payment->created_by
        );
    }

    public function postCustomerReceipt(
        CustomerPayment $payment,
        ?int $createdBy = null
    ): AccountTransaction {
        $this->requireAccountId(
            $payment->account_id,
            'A Cash/Bank account is required for this customer receipt.'
        );

        return $this->debit(
            companyId: (int) $payment->company_id,
            storeId: (int) $payment->store_id,
            accountId: (int) $payment->account_id,
            transactionDate: $this->dateString($payment->payment_date),
            transactionType: AccountTransaction::TYPE_CUSTOMER_RECEIPT,
            referenceType: 'customer_payment',
            referenceId: (int) $payment->id,
            amount: (float) $payment->amount,
            referenceNo: $payment->receipt_no,
            paymentMode: $payment->payment_mode,
            externalReference: $payment->reference_no,
            remarks: $payment->remarks,
            createdBy: $createdBy ?? $payment->created_by
        );
    }

    public function postSupplierPayment(
        SupplierPayment $payment,
        ?int $createdBy = null
    ): AccountTransaction {
        $this->requireAccountId(
            $payment->account_id,
            'A Cash/Bank account is required for this supplier payment.'
        );

        return $this->credit(
            companyId: (int) $payment->company_id,
            storeId: (int) $payment->store_id,
            accountId: (int) $payment->account_id,
            transactionDate: $this->dateString($payment->payment_date),
            transactionType: AccountTransaction::TYPE_SUPPLIER_PAYMENT,
            referenceType: 'supplier_payment',
            referenceId: (int) $payment->id,
            amount: (float) $payment->amount,
            referenceNo: $payment->payment_no,
            paymentMode: $payment->payment_mode,
            externalReference: $payment->reference_no,
            remarks: $payment->remarks,
            createdBy: $createdBy ?? $payment->created_by
        );
    }

    public function postMechanicPayment(
        MechanicPayment $payment,
        ?int $createdBy = null
    ): AccountTransaction {
        $this->requireAccountId(
            $payment->account_id,
            'A Cash/Bank account is required for this mechanic payment.'
        );

        return $this->credit(
            companyId: (int) $payment->company_id,
            storeId: (int) $payment->store_id,
            accountId: (int) $payment->account_id,
            transactionDate: $this->dateString($payment->payment_date),
            transactionType: AccountTransaction::TYPE_MECHANIC_PAYMENT,
            referenceType: $payment->getMorphClass(),
            referenceId: (int) $payment->id,
            amount: (float) $payment->amount,
            referenceNo: $payment->payment_no,
            paymentMode: $payment->payment_mode,
            externalReference: $payment->reference_no,
            remarks: $payment->remarks,
            createdBy: $createdBy ?? $payment->created_by
        );
    }

    public function postCustomerRefund(
        SalesReturn $return,
        int $accountId,
        float $amount,
        ?int $createdBy = null
    ): AccountTransaction {
        return $this->credit(
            companyId: (int) $return->company_id,
            storeId: (int) $return->store_id,
            accountId: $accountId,
            transactionDate: $this->dateString($return->return_date),
            transactionType: AccountTransaction::TYPE_CUSTOMER_REFUND,
            referenceType: 'sales_return_refund',
            referenceId: (int) $return->id,
            amount: $amount,
            referenceNo: $return->return_no,
            paymentMode: $return->refund_method,
            remarks: 'Refund paid for sales return ' . $return->return_no,
            createdBy: $createdBy
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Receipt and Payment Vouchers
    |--------------------------------------------------------------------------
    |
    | Receipt voucher: debit increases the selected account.
    | Payment voucher: credit decreases the selected account.
    |
    */

    public function postVoucher(
        Voucher $voucher,
        ?int $createdBy = null
    ): AccountTransaction {
        if ($voucher->isReceipt()) {
            return $this->postReceiptVoucher(
                $voucher,
                $createdBy
            );
        }

        if ($voucher->isPayment()) {
            return $this->postPaymentVoucher(
                $voucher,
                $createdBy
            );
        }

        throw ValidationException::withMessages([
            'voucher_type' => 'Invalid receipt or payment voucher type.',
        ]);
    }

    public function postReceiptVoucher(
        Voucher $voucher,
        ?int $createdBy = null
    ): AccountTransaction {
        $this->requireAccountId(
            $voucher->account_id,
            'A Cash/Bank account is required for this receipt voucher.'
        );

        if (!$voucher->isReceipt()) {
            throw ValidationException::withMessages([
                'voucher_type' =>
                'Only a receipt voucher can be posted as money received.',
            ]);
        }

        return $this->debit(
            companyId: (int) $voucher->company_id,
            storeId: (int) $voucher->store_id,
            accountId: (int) $voucher->account_id,
            transactionDate: $this->dateString($voucher->voucher_date),
            transactionType: AccountTransaction::TYPE_RECEIPT_VOUCHER,
            referenceType: $voucher->getMorphClass(),
            referenceId: (int) $voucher->id,
            amount: (float) $voucher->amount,
            referenceNo: $voucher->voucher_no,
            paymentMode: $voucher->payment_mode,
            externalReference: $voucher->reference_no,
            remarks: $voucher->remarks
                ?: 'Receipt voucher ' . $voucher->voucher_no,
            createdBy: $createdBy ?? $voucher->created_by
        );
    }

    public function postPaymentVoucher(
        Voucher $voucher,
        ?int $createdBy = null
    ): AccountTransaction {
        $this->requireAccountId(
            $voucher->account_id,
            'A Cash/Bank account is required for this payment voucher.'
        );

        if (!$voucher->isPayment()) {
            throw ValidationException::withMessages([
                'voucher_type' =>
                'Only a payment voucher can be posted as money paid.',
            ]);
        }

        $this->ensureSufficientBalance(
            companyId: (int) $voucher->company_id,
            storeId: (int) $voucher->store_id,
            accountId: (int) $voucher->account_id,
            requiredAmount: (float) $voucher->amount,
            asOfDate: $this->dateString($voucher->voucher_date),
            lock: true
        );

        return $this->credit(
            companyId: (int) $voucher->company_id,
            storeId: (int) $voucher->store_id,
            accountId: (int) $voucher->account_id,
            transactionDate: $this->dateString($voucher->voucher_date),
            transactionType: AccountTransaction::TYPE_PAYMENT_VOUCHER,
            referenceType: $voucher->getMorphClass(),
            referenceId: (int) $voucher->id,
            amount: (float) $voucher->amount,
            referenceNo: $voucher->voucher_no,
            paymentMode: $voucher->payment_mode,
            externalReference: $voucher->reference_no,
            remarks: $voucher->remarks
                ?: 'Payment voucher ' . $voucher->voucher_no,
            createdBy: $createdBy ?? $voucher->created_by
        );
    }

    public function reverseVoucher(
        Voucher $voucher,
        ?int $createdBy = null,
        ?string $transactionDate = null
    ): array {
        return $this->reverseReference(
            companyId: (int) $voucher->company_id,
            storeId: (int) $voucher->store_id,
            referenceType: $voucher->getMorphClass(),
            referenceId: (int) $voucher->id,
            remarks: 'Cancellation of ' . $voucher->typeLabel()
                . ' ' . $voucher->voucher_no,
            createdBy: $createdBy,
            transactionDate: $transactionDate
                ?: now()->toDateString()
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Record Transaction
    |--------------------------------------------------------------------------
    */

    public function record(
        int $companyId,
        int $storeId,
        int $accountId,
        string $transactionDate,
        string $transactionType,
        string $referenceType,
        int $referenceId,
        float $amount,
        string $direction,
        ?string $referenceNo = null,
        ?string $paymentMode = null,
        ?string $externalReference = null,
        ?string $remarks = null,
        ?int $createdBy = null
    ): AccountTransaction {
        $amount = $this->normaliseAmount($amount);

        $this->validateDirection($direction);
        $this->validateTransactionType($transactionType);
        $this->validateReference($referenceType, $referenceId);

        return DB::transaction(function () use (
            $companyId,
            $storeId,
            $accountId,
            $transactionDate,
            $transactionType,
            $referenceType,
            $referenceId,
            $amount,
            $direction,
            $referenceNo,
            $paymentMode,
            $externalReference,
            $remarks,
            $createdBy
        ) {
            $account = $this->resolveAccount(
                companyId: $companyId,
                storeId: $storeId,
                accountId: $accountId,
                requireActive: true,
                lock: true
            );

            /*
            |------------------------------------------------------------------
            | Idempotency
            |------------------------------------------------------------------
            |
            | One source document can post only one entry of the same type to
            | the same account. Repeated requests return the existing entry.
            |
            */

            $existing = AccountTransaction::query()
                ->forContext($companyId, $storeId)
                ->forAccount($account->id)
                ->forReference($referenceType, $referenceId)
                ->where('transaction_type', $transactionType)
                ->where('is_reversal', false)
                ->whereDoesntHave('reversals')
                ->lockForUpdate()
                ->first();

            if ($existing) {
                return $existing;
            }

            return AccountTransaction::query()->create([
                'company_id' => $companyId,
                'store_id' => $storeId,
                'account_id' => $account->id,
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
                'payment_mode' => $paymentMode
                    ?: $account->payment_mode,
                'external_reference' => $externalReference,
                'remarks' => $remarks,
                'is_reversal' => false,
                'reversal_of_id' => null,
                'created_by' => $createdBy,
            ]);
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Reverse One Transaction
    |--------------------------------------------------------------------------
    */

    public function reverse(
        AccountTransaction $transaction,
        ?string $remarks = null,
        ?int $createdBy = null,
        ?string $transactionDate = null
    ): AccountTransaction {
        return DB::transaction(function () use (
            $transaction,
            $remarks,
            $createdBy,
            $transactionDate
        ) {
            $original = AccountTransaction::query()
                ->whereKey($transaction->id)
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

    /*
    |--------------------------------------------------------------------------
    | Reverse All Transactions for a Reference
    |--------------------------------------------------------------------------
    */

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
            $transactions = AccountTransaction::query()
                ->forContext($companyId, $storeId)
                ->forReference($referenceType, $referenceId)
                ->where('is_reversal', false)
                ->whereDoesntHave('reversals')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $reversals = [];

            foreach ($transactions as $transaction) {
                $reversals[] = $this->createReversal(
                    original: $transaction,
                    remarks: $remarks,
                    createdBy: $createdBy,
                    transactionDate: $transactionDate
                );
            }

            return $reversals;
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Contra Transfer
    |--------------------------------------------------------------------------
    |
    | Transfers money from one account to another account in the same branch.
    | Source account is credited and destination account is debited.
    |
    */

    public function contra(
        int $companyId,
        int $storeId,
        int $fromAccountId,
        int $toAccountId,
        string $transactionDate,
        string $referenceType,
        int $referenceId,
        float $amount,
        ?string $referenceNo = null,
        ?string $externalReference = null,
        ?string $remarks = null,
        ?int $createdBy = null
    ): array {
        if ($fromAccountId === $toAccountId) {
            throw ValidationException::withMessages([
                'to_account_id' =>
                'Source and destination accounts must be different.',
            ]);
        }

        $amount = $this->normaliseAmount($amount);

        return DB::transaction(function () use (
            $companyId,
            $storeId,
            $fromAccountId,
            $toAccountId,
            $transactionDate,
            $referenceType,
            $referenceId,
            $amount,
            $referenceNo,
            $externalReference,
            $remarks,
            $createdBy
        ) {
            $this->ensureSufficientBalance(
                companyId: $companyId,
                storeId: $storeId,
                accountId: $fromAccountId,
                requiredAmount: $amount,
                asOfDate: $transactionDate,
                lock: true
            );

            $sourceEntry = $this->credit(
                companyId: $companyId,
                storeId: $storeId,
                accountId: $fromAccountId,
                transactionDate: $transactionDate,
                transactionType: AccountTransaction::TYPE_CONTRA,
                referenceType: $referenceType,
                referenceId: $referenceId,
                amount: $amount,
                referenceNo: $referenceNo,
                externalReference: $externalReference,
                remarks: $remarks ?: 'Contra transfer sent',
                createdBy: $createdBy
            );

            $destinationEntry = $this->debit(
                companyId: $companyId,
                storeId: $storeId,
                accountId: $toAccountId,
                transactionDate: $transactionDate,
                transactionType: AccountTransaction::TYPE_CONTRA,
                referenceType: $referenceType,
                referenceId: $referenceId,
                amount: $amount,
                referenceNo: $referenceNo,
                externalReference: $externalReference,
                remarks: $remarks ?: 'Contra transfer received',
                createdBy: $createdBy
            );

            return [
                'source' => $sourceEntry,
                'destination' => $destinationEntry,
            ];
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Current / As-Of Balance
    |--------------------------------------------------------------------------
    */

    public function balance(
        int $companyId,
        int $storeId,
        int $accountId,
        ?string $asOfDate = null,
        bool $lock = false
    ): float {
        $account = $this->resolveAccount(
            companyId: $companyId,
            storeId: $storeId,
            accountId: $accountId,
            requireActive: false,
            lock: $lock
        );

        $query = AccountTransaction::query()
            ->forContext($companyId, $storeId)
            ->forAccount($account->id);

        if ($asOfDate) {
            $query->asOf(
                Carbon::parse($asOfDate)->toDateString()
            );
        }

        $totals = $query
            ->selectRaw(
                'COALESCE(SUM(debit), 0) AS total_debit, '
                    . 'COALESCE(SUM(credit), 0) AS total_credit'
            )
            ->first();

        $openingBalance = (float) $account->opening_balance;

        if (
            $asOfDate
            && $account->opening_balance_date
            && Carbon::parse($asOfDate)->lt(
                Carbon::parse($account->opening_balance_date)
            )
        ) {
            $openingBalance = 0;
        }

        return round(
            $openingBalance
                + (float) $totals->total_debit
                - (float) $totals->total_credit,
            2
        );
    }

    public function ensureSufficientBalance(
        int $companyId,
        int $storeId,
        int $accountId,
        float $requiredAmount,
        ?string $asOfDate = null,
        bool $lock = false
    ): void {
        $requiredAmount = $this->normaliseAmount(
            $requiredAmount
        );

        $availableBalance = $this->balance(
            companyId: $companyId,
            storeId: $storeId,
            accountId: $accountId,
            asOfDate: $asOfDate,
            lock: $lock
        );

        if ($requiredAmount > $availableBalance) {
            throw ValidationException::withMessages([
                'account_id' =>
                'Insufficient account balance. Available balance: '
                    . number_format($availableBalance, 2),
            ]);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Internal Reversal Creation
    |--------------------------------------------------------------------------
    */

    private function createReversal(
        AccountTransaction $original,
        ?string $remarks,
        ?int $createdBy,
        ?string $transactionDate
    ): AccountTransaction {
        if ($original->is_reversal) {
            throw ValidationException::withMessages([
                'transaction' =>
                'A reversal entry cannot be reversed directly.',
            ]);
        }

        $existing = AccountTransaction::query()
            ->where('reversal_of_id', $original->id)
            ->lockForUpdate()
            ->first();

        if ($existing) {
            return $existing;
        }

        return AccountTransaction::query()->create([
            'company_id' => $original->company_id,
            'store_id' => $original->store_id,
            'account_id' => $original->account_id,
            'transaction_date' => $transactionDate
                ? Carbon::parse($transactionDate)->toDateString()
                : now()->toDateString(),
            'transaction_type' => AccountTransaction::TYPE_REVERSAL,
            'reference_type' => $original->reference_type,
            'reference_id' => $original->reference_id,
            'reference_no' => $original->reference_no,
            'debit' => $original->credit,
            'credit' => $original->debit,
            'payment_mode' => $original->payment_mode,
            'external_reference' => $original->external_reference,
            'remarks' => $remarks
                ?: 'Reversal of account transaction #'
                . $original->id,
            'is_reversal' => true,
            'reversal_of_id' => $original->id,
            'created_by' => $createdBy,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Resolve Account with Company and Branch Isolation
    |--------------------------------------------------------------------------
    */

    private function resolveAccount(
        int $companyId,
        int $storeId,
        int $accountId,
        bool $requireActive,
        bool $lock
    ): Account {
        $query = Account::query()
            ->whereKey($accountId)
            ->where('company_id', $companyId)
            ->where('store_id', $storeId);

        if ($requireActive) {
            $query->where('status', true);
        }

        if ($lock) {
            $query->lockForUpdate();
        }

        $account = $query->first();

        if (!$account) {
            throw ValidationException::withMessages([
                'account_id' =>
                'The selected account is unavailable in the active branch.',
            ]);
        }

        return $account;
    }

    private function normaliseAmount(float $amount): float
    {
        $amount = round($amount, 2);

        if ($amount <= 0) {
            throw ValidationException::withMessages([
                'amount' =>
                'Transaction amount must be greater than zero.',
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
                'direction' => 'Invalid transaction direction.',
            ]);
        }
    }

    private function validateTransactionType(
        string $transactionType
    ): void {
        if (
            !in_array(
                $transactionType,
                AccountTransaction::transactionTypes(),
                true
            )
        ) {
            throw ValidationException::withMessages([
                'transaction_type' =>
                'Invalid account transaction type.',
            ]);
        }
    }

    private function validateReference(
        string $referenceType,
        int $referenceId
    ): void {
        if (trim($referenceType) === '' || $referenceId <= 0) {
            throw ValidationException::withMessages([
                'reference' => 'A valid source document reference is required.',
            ]);
        }
    }

    private function requireAccountId(
        mixed $accountId,
        string $message
    ): void {
        if ((int) $accountId <= 0) {
            throw ValidationException::withMessages([
                'account_id' => $message,
            ]);
        }
    }

    private function dateString(mixed $date): string
    {
        return Carbon::parse($date)->toDateString();
    }
}
