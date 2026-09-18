<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Customer;
use App\Models\CustomerLedgerEntry;
use App\Models\Store;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\Response as HttpResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CustomerLedgerController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:customer-ledger.view'),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Ledger Register
    |--------------------------------------------------------------------------
    */

    public function index(Request $request): Response
    {
        [$companyId, $storeId] = $this->context();
        $filters = $this->validatedFilters($request);
        $customer = $this->selectedCustomer(
            $filters['customer_id'],
            $companyId,
            $storeId
        );

        $entries = null;
        $openingBalance = 0.0;
        $periodDebit = 0.0;
        $periodCredit = 0.0;
        $closingBalance = 0.0;

        if ($customer) {
            $openingBalance = $this->openingBalance(
                $companyId,
                $storeId,
                $customer->id,
                $filters['from_date']
            );

            $query = $this->periodQuery(
                $companyId,
                $storeId,
                $customer->id,
                $filters
            );

            $periodDebit = (float) (clone $query)->sum('debit');
            $periodCredit = (float) (clone $query)->sum('credit');
            $closingBalance = round(
                $openingBalance + $periodDebit - $periodCredit,
                2
            );

            $entries = $query
                ->with(['creator:id,name'])
                ->select('customer_ledger_entries.*')
                ->selectRaw(
                    '? + SUM(debit - credit) OVER '
                        . '(ORDER BY transaction_date ASC, id ASC) AS running_balance',
                    [$openingBalance]
                )
                ->orderBy('transaction_date')
                ->orderBy('id')
                ->paginate($filters['per_page'])
                ->withQueryString();

            $entries->through(
                fn(CustomerLedgerEntry $entry): array => $this->entryData($entry)
            );
        }

        return Inertia::render('CustomerLedgers/Index', [
            'entries' => $entries,
            'customer' => $customer
                ? $this->customerData($customer, $closingBalance)
                : null,
            'summary' => [
                'opening_balance' => $openingBalance,
                'period_debit' => $periodDebit,
                'period_credit' => $periodCredit,
                'closing_balance' => $closingBalance,
            ],
            'filters' => $filters,
            'transaction_types' => $this->transactionTypes(),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | AJAX Customer Search
    |--------------------------------------------------------------------------
    */

    public function searchCustomers(Request $request): JsonResponse
    {
        [$companyId, $storeId] = $this->context();
        $search = trim($request->string('search')->toString());

        $customers = $this->customerQuery($companyId, $storeId)
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner
                        ->where('customer_name', 'like', "%{$search}%")
                        ->orWhere('customer_code', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%")
                        ->orWhere('gst_number', 'like', "%{$search}%");
                });
            })
            ->orderBy('customer_name')
            ->limit(20)
            ->get([
                'id',
                'customer_code',
                'customer_name',
                'mobile',
                'gst_number',
            ])
            ->map(function (Customer $customer) use ($companyId, $storeId): array {
                $balance = $this->currentBalance(
                    $companyId,
                    $storeId,
                    $customer->id
                );

                return $this->customerData($customer, $balance);
            })
            ->values();

        return response()->json(['data' => $customers]);
    }

    /*
    |--------------------------------------------------------------------------
    | Print / PDF / CSV
    |--------------------------------------------------------------------------
    */

    public function print(Request $request): ViewContract
    {
        $report = $this->reportData($request);

        return view('customer_ledgers.print', [
            ...$report,
            'pdfMode' => false,
        ]);
    }

    public function pdf(Request $request): HttpResponse
    {
        $report = $this->reportData($request);
        $customerCode = $report['customer']->customer_code ?: $report['customer']->id;

        return Pdf::loadView('customer_ledgers.pdf', [
            ...$report,
            'pdfMode' => true,
        ])
            ->setPaper('a4', 'portrait')
            ->download("customer-ledger-{$customerCode}.pdf");
    }

    public function excel(Request $request): StreamedResponse
    {
        $report = $this->reportData($request);
        $customerCode = $report['customer']->customer_code ?: $report['customer']->id;
        $filename = "customer-ledger-{$customerCode}.csv";

        return response()->streamDownload(function () use ($report): void {
            $handle = fopen('php://output', 'wb');

            // UTF-8 BOM keeps customer names readable in Microsoft Excel.
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Customer Ledger']);
            fputcsv($handle, ['Customer', $report['customer']->customer_name]);
            fputcsv($handle, ['Customer Code', $report['customer']->customer_code]);
            fputcsv($handle, ['Period', $report['filters']['from_date'] . ' to ' . $report['filters']['to_date']]);
            fputcsv($handle, []);
            fputcsv($handle, [
                'Date',
                'Type',
                'Reference',
                'Remarks',
                'Debit',
                'Credit',
                'Running Balance',
            ]);

            fputcsv($handle, [
                $report['filters']['from_date'],
                'Opening Balance',
                '',
                'Balance brought forward',
                '',
                '',
                $this->balanceLabel($report['summary']['opening_balance']),
            ]);

            foreach ($report['entries'] as $entry) {
                fputcsv($handle, [
                    $entry['transaction_date'],
                    $entry['type_label'],
                    $entry['reference_no'],
                    $entry['remarks'],
                    number_format($entry['debit'], 2, '.', ''),
                    number_format($entry['credit'], 2, '.', ''),
                    $entry['balance_label'],
                ]);
            }

            fputcsv($handle, []);
            fputcsv($handle, ['Period Debit', number_format($report['summary']['period_debit'], 2, '.', '')]);
            fputcsv($handle, ['Period Credit', number_format($report['summary']['period_credit'], 2, '.', '')]);
            fputcsv($handle, ['Closing Balance', $this->balanceLabel($report['summary']['closing_balance'])]);
            fclose($handle);
        }, $filename, [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Report Builder
    |--------------------------------------------------------------------------
    */

    private function reportData(Request $request): array
    {
        [$companyId, $storeId] = $this->context();
        $filters = $this->validatedFilters($request, true);
        $customer = $this->selectedCustomer(
            $filters['customer_id'],
            $companyId,
            $storeId
        );

        abort_unless($customer, 422, 'Select a customer before exporting the ledger.');

        $openingBalance = $this->openingBalance(
            $companyId,
            $storeId,
            $customer->id,
            $filters['from_date']
        );

        $models = $this->periodQuery(
            $companyId,
            $storeId,
            $customer->id,
            $filters
        )
            ->with(['creator:id,name'])
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        $runningBalance = $openingBalance;
        $periodDebit = 0.0;
        $periodCredit = 0.0;

        $entries = $models->map(function (CustomerLedgerEntry $entry) use (
            &$runningBalance,
            &$periodDebit,
            &$periodCredit
        ): array {
            $debit = (float) $entry->debit;
            $credit = (float) $entry->credit;
            $periodDebit += $debit;
            $periodCredit += $credit;
            $runningBalance = round($runningBalance + $debit - $credit, 2);

            return $this->entryData($entry, $runningBalance);
        });

        return [
            'company' => Company::query()->findOrFail($companyId),
            'store' => Store::query()->findOrFail($storeId),
            'customer' => $customer,
            'entries' => $entries,
            'filters' => $filters,
            'summary' => [
                'opening_balance' => round($openingBalance, 2),
                'period_debit' => round($periodDebit, 2),
                'period_credit' => round($periodCredit, 2),
                'closing_balance' => round($runningBalance, 2),
            ],
        ];
    }

    private function periodQuery(
        int $companyId,
        int $storeId,
        int $customerId,
        array $filters
    ): Builder {
        return CustomerLedgerEntry::query()
            ->forContext($companyId, $storeId)
            ->forCustomer($customerId)
            ->betweenDates($filters['from_date'], $filters['to_date'])
            ->when(
                $filters['transaction_type'] !== '',
                fn(Builder $query) => $query->where(
                    'transaction_type',
                    $filters['transaction_type']
                )
            )
            ->when(
                $filters['search'] !== '',
                function (Builder $query) use ($filters): void {
                    $search = $filters['search'];
                    $query->where(function (Builder $inner) use ($search): void {
                        $inner
                            ->where('reference_no', 'like', "%{$search}%")
                            ->orWhere('remarks', 'like', "%{$search}%")
                            ->orWhere('transaction_type', 'like', "%{$search}%");
                    });
                }
            );
    }

    private function openingBalance(
        int $companyId,
        int $storeId,
        int $customerId,
        string $fromDate
    ): float {
        return round((float) CustomerLedgerEntry::query()
            ->forContext($companyId, $storeId)
            ->forCustomer($customerId)
            ->whereDate('transaction_date', '<', $fromDate)
            ->selectRaw('COALESCE(SUM(debit - credit), 0) AS balance')
            ->value('balance'), 2);
    }

    private function currentBalance(
        int $companyId,
        int $storeId,
        int $customerId
    ): float {
        return round((float) CustomerLedgerEntry::query()
            ->forContext($companyId, $storeId)
            ->forCustomer($customerId)
            ->selectRaw('COALESCE(SUM(debit - credit), 0) AS balance')
            ->value('balance'), 2);
    }

    private function entryData(
        CustomerLedgerEntry $entry,
        ?float $runningBalance = null
    ): array {
        $balance = $runningBalance
            ?? (float) ($entry->getAttribute('running_balance') ?? 0);

        return [
            'id' => $entry->id,
            'transaction_date' => $entry->transaction_date?->format('Y-m-d'),
            'transaction_type' => $entry->transaction_type,
            'type_label' => $this->typeLabel($entry->transaction_type),
            'reference_type' => $entry->reference_type,
            'reference_id' => $entry->reference_id,
            'reference_no' => $entry->reference_no,
            'debit' => (float) $entry->debit,
            'credit' => (float) $entry->credit,
            'running_balance' => round($balance, 2),
            'balance_label' => $this->balanceLabel($balance),
            'is_reversal' => (bool) $entry->is_reversal,
            'reversal_of_id' => $entry->reversal_of_id,
            'remarks' => $entry->remarks,
            'creator' => $entry->creator,
            'reference_url' => $this->referenceUrl($entry),
        ];
    }

    private function referenceUrl(CustomerLedgerEntry $entry): ?string
    {
        if (!$entry->reference_id) {
            return null;
        }

        $routeName = match ($entry->reference_type) {
            'sale' => 'sales.show',
            'customer_payment' => 'customer-payments.show',
            'sales_return' => 'sales-returns.show',
            default => null,
        };

        return $routeName && Route::has($routeName)
            ? route($routeName, $entry->reference_id)
            : null;
    }

    private function validatedFilters(
        Request $request,
        bool $customerRequired = false
    ): array {
        $validated = $request->validate([
            'customer_id' => [$customerRequired ? 'required' : 'nullable', 'integer'],
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'transaction_type' => ['nullable', 'string', 'max:40'],
            'search' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'in:20,50,100'],
        ]);

        $fromDate = (string) ($validated['from_date'] ?? now()->startOfMonth()->toDateString());
        $toDate = (string) ($validated['to_date'] ?? now()->toDateString());
        $type = (string) ($validated['transaction_type'] ?? '');

        if ($type !== '' && !array_key_exists($type, $this->transactionTypes())) {
            $type = '';
        }

        return [
            'customer_id' => isset($validated['customer_id'])
                ? (int) $validated['customer_id']
                : null,
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'transaction_type' => $type,
            'search' => trim((string) ($validated['search'] ?? '')),
            'per_page' => (int) ($validated['per_page'] ?? 20),
        ];
    }

    private function selectedCustomer(
        ?int $customerId,
        int $companyId,
        int $storeId
    ): ?Customer {
        if (!$customerId) {
            return null;
        }

        return $this->customerQuery($companyId, $storeId)
            ->findOrFail($customerId);
    }

    private function customerQuery(
        int $companyId,
        int $storeId
    ): Builder {
        return Customer::query()
            ->where('company_id', $companyId)
            ->where('status', true)
            ->whereHas('stores', function (Builder $query) use ($storeId): void {
                $query->where('stores.id', $storeId);
            });
    }

    private function customerData(Customer $customer, float $balance): array
    {
        return [
            'id' => $customer->id,
            'customer_code' => $customer->customer_code,
            'customer_name' => $customer->customer_name,
            'mobile' => $customer->mobile,
            'gst_number' => $customer->gst_number,
            'current_balance' => round($balance, 2),
            'balance_label' => $this->balanceLabel($balance),
        ];
    }

    private function transactionTypes(): array
    {
        return collect(CustomerLedgerEntry::transactionTypes())
            ->mapWithKeys(fn(string $type): array => [
                $type => $this->typeLabel($type),
            ])
            ->all();
    }

    private function typeLabel(string $type): string
    {
        return match ($type) {
            CustomerLedgerEntry::TYPE_OPENING_BALANCE => 'Opening Balance',
            CustomerLedgerEntry::TYPE_SALE => 'Sales Invoice',
            CustomerLedgerEntry::TYPE_SALE_REVERSAL => 'Sale Reversal',
            CustomerLedgerEntry::TYPE_SALES_RETURN => 'Sales Return',
            CustomerLedgerEntry::TYPE_SALES_RETURN_REVERSAL => 'Return Reversal',
            CustomerLedgerEntry::TYPE_CUSTOMER_PAYMENT => 'Customer Payment',
            CustomerLedgerEntry::TYPE_PAYMENT_REVERSAL => 'Payment Reversal',
            CustomerLedgerEntry::TYPE_ADJUSTMENT => 'Ledger Adjustment',
            default => ucwords(str_replace('_', ' ', $type)),
        };
    }

    private function balanceLabel(float $balance): string
    {
        if ($balance > 0.009) {
            return number_format(abs($balance), 2) . ' Dr';
        }

        if ($balance < -0.009) {
            return number_format(abs($balance), 2) . ' Cr';
        }

        return '0.00';
    }

    private function context(): array
    {
        $companyId = (int) session('active_company_id');
        $storeId = (int) session('active_store_id');

        abort_if(
            !$companyId || !$storeId,
            403,
            'Select an active company and branch before accessing customer ledger.'
        );

        $validStore = Store::query()
            ->whereKey($storeId)
            ->where('company_id', $companyId)
            ->where('status', true)
            ->exists();

        abort_unless(
            $validStore,
            403,
            'The active branch is invalid or inactive.'
        );

        return [$companyId, $storeId];
    }
}
