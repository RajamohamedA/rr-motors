<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Mechanic;
use App\Models\MechanicCommission;
use App\Models\MechanicLedgerEntry;
use App\Models\MechanicPayment;
use App\Models\SalesReturn;
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

class MechanicLedgerController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('permission:mechanic-ledger.view'),
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
        $mechanic = $this->selectedMechanic(
            $filters['mechanic_id'],
            $companyId,
            $storeId
        );

        $entries = null;
        $openingBalance = 0.0;
        $periodDebit = 0.0;
        $periodCredit = 0.0;
        $closingBalance = 0.0;

        if ($mechanic) {
            $openingBalance = $this->openingBalance(
                $companyId,
                $storeId,
                $mechanic->id,
                $filters['from_date']
            );

            $query = $this->periodQuery(
                $companyId,
                $storeId,
                $mechanic->id,
                $filters
            );

            $periodDebit = (float) (clone $query)->sum('debit');
            $periodCredit = (float) (clone $query)->sum('credit');
            $closingBalance = round(
                $openingBalance + $periodCredit - $periodDebit,
                2
            );

            $entries = $query
                ->with(['creator:id,name'])
                ->select('mechanic_ledger_entries.*')
                ->selectRaw(
                    '? + SUM(credit - debit) OVER '
                        . '(ORDER BY transaction_date ASC, id ASC) AS running_balance',
                    [$openingBalance]
                )
                ->orderBy('transaction_date')
                ->orderBy('id')
                ->paginate($filters['per_page'])
                ->withQueryString();

            $entries->through(
                fn(MechanicLedgerEntry $entry): array => $this->entryData($entry)
            );
        }

        return Inertia::render('MechanicLedgers/Index', [
            'entries' => $entries,
            'mechanic' => $mechanic
                ? $this->mechanicData($mechanic, $closingBalance)
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
    | AJAX Mechanic Search
    |--------------------------------------------------------------------------
    */

    public function searchMechanics(Request $request): JsonResponse
    {
        [$companyId, $storeId] = $this->context();
        $search = trim($request->string('search')->toString());

        $mechanics = $this->mechanicQuery($companyId, $storeId)
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner
                        ->where('mechanic_name', 'like', "%{$search}%")
                        ->orWhere('mechanic_code', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%");
                });
            })
            ->orderBy('mechanic_name')
            ->limit(20)
            ->get([
                'id',
                'mechanic_code',
                'mechanic_name',
                'mobile',
            ])
            ->map(function (Mechanic $mechanic) use ($companyId, $storeId): array {
                $balance = $this->currentBalance(
                    $companyId,
                    $storeId,
                    $mechanic->id
                );

                return $this->mechanicData($mechanic, $balance);
            })
            ->values();

        return response()->json(['data' => $mechanics]);
    }

    /*
    |--------------------------------------------------------------------------
    | Print / PDF / CSV
    |--------------------------------------------------------------------------
    */

    public function print(Request $request): ViewContract
    {
        $report = $this->reportData($request);

        return view('mechanic_ledgers.print', [
            ...$report,
            'pdfMode' => false,
        ]);
    }

    public function pdf(Request $request): HttpResponse
    {
        $report = $this->reportData($request);
        $mechanicCode = $report['mechanic']->mechanic_code ?: $report['mechanic']->id;

        return Pdf::loadView('mechanic_ledgers.pdf', [
            ...$report,
            'pdfMode' => true,
        ])
            ->setPaper($report['paper'], 'portrait')
            ->download("mechanic-ledger-{$mechanicCode}.pdf");
    }

    public function excel(Request $request): StreamedResponse
    {
        $report = $this->reportData($request);
        $mechanicCode = $report['mechanic']->mechanic_code ?: $report['mechanic']->id;
        $filename = "mechanic-ledger-{$mechanicCode}.csv";

        return response()->streamDownload(function () use ($report): void {
            $handle = fopen('php://output', 'wb');

            // UTF-8 BOM keeps mechanic names readable in Microsoft Excel.
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Mechanic Ledger']);
            fputcsv($handle, ['Mechanic', $report['mechanic']->mechanic_name]);
            fputcsv($handle, ['Mechanic Code', $report['mechanic']->mechanic_code]);
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
        $mechanic = $this->selectedMechanic(
            $filters['mechanic_id'],
            $companyId,
            $storeId
        );

        abort_unless($mechanic, 422, 'Select a mechanic before exporting the ledger.');

        $openingBalance = $this->openingBalance(
            $companyId,
            $storeId,
            $mechanic->id,
            $filters['from_date']
        );

        $models = $this->periodQuery(
            $companyId,
            $storeId,
            $mechanic->id,
            $filters
        )
            ->with(['creator:id,name'])
            ->orderBy('transaction_date')
            ->orderBy('id')
            ->get();

        $runningBalance = $openingBalance;
        $periodDebit = 0.0;
        $periodCredit = 0.0;

        $entries = $models->map(function (MechanicLedgerEntry $entry) use (
            &$runningBalance,
            &$periodDebit,
            &$periodCredit
        ): array {
            $debit = (float) $entry->debit;
            $credit = (float) $entry->credit;
            $periodDebit += $debit;
            $periodCredit += $credit;
            $runningBalance = round($runningBalance + $credit - $debit, 2);

            return $this->entryData($entry, $runningBalance);
        });

        $company = Company::query()->findOrFail($companyId);
        $store = Store::query()->findOrFail($storeId);

        return [
            'company' => $company,
            'store' => $store,
            'mechanic' => $mechanic,
            'entries' => $entries,
            'filters' => $filters,
            'paper' => $filters['paper'],
            'company_name' => $company->company_name
                ?? $company->name
                ?? 'Company',
            'store_name' => $store->store_name
                ?? $store->name
                ?? 'Branch',
            'from_date_text' => date(
                'd-m-Y',
                strtotime($filters['from_date'])
            ),
            'to_date_text' => date(
                'd-m-Y',
                strtotime($filters['to_date'])
            ),
            'opening_balance_label' => $this->balanceLabel(
                $openingBalance
            ),
            'closing_balance_label' => $this->balanceLabel(
                $runningBalance
            ),
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
        int $mechanicId,
        array $filters
    ): Builder {
        return MechanicLedgerEntry::query()
            ->forContext($companyId, $storeId)
            ->forMechanic($mechanicId)
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
        int $mechanicId,
        string $fromDate
    ): float {
        return round((float) MechanicLedgerEntry::query()
            ->forContext($companyId, $storeId)
            ->forMechanic($mechanicId)
            ->whereDate('transaction_date', '<', $fromDate)
            ->selectRaw('COALESCE(SUM(credit - debit), 0) AS balance')
            ->value('balance'), 2);
    }

    private function currentBalance(
        int $companyId,
        int $storeId,
        int $mechanicId
    ): float {
        return round((float) MechanicLedgerEntry::query()
            ->forContext($companyId, $storeId)
            ->forMechanic($mechanicId)
            ->selectRaw('COALESCE(SUM(credit - debit), 0) AS balance')
            ->value('balance'), 2);
    }

    private function entryData(
        MechanicLedgerEntry $entry,
        ?float $runningBalance = null
    ): array {
        $balance = $runningBalance
            ?? (float) ($entry->getAttribute('running_balance') ?? 0);

        return [
            'id' => $entry->id,
            'transaction_date' => $entry->transaction_date?->format('Y-m-d'),
            'transaction_type' => $entry->transaction_type,
            'type_label' => $this->entryTypeLabel($entry),
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

    private function referenceUrl(MechanicLedgerEntry $entry): ?string
    {
        if (!$entry->reference_id) {
            return null;
        }

        $routeName = match ($entry->reference_type) {
            MechanicCommission::class,
            'mechanic_commission' => 'sales.show',
            MechanicPayment::class,
            'mechanic_payment' => 'mechanic-payments.show',
            SalesReturn::class,
            'sales_return' => 'sales-returns.show',
            default => null,
        };

        $routeParameter = $entry->reference_id;

        if (
            $routeName === 'sales.show'
            && in_array(
                $entry->reference_type,
                [MechanicCommission::class, 'mechanic_commission'],
                true
            )
        ) {
            $routeParameter = MechanicCommission::query()
                ->whereKey($entry->reference_id)
                ->value('sale_id');
        }

        return $routeName && $routeParameter && Route::has($routeName)
            ? route($routeName, $routeParameter)
            : null;
    }

    private function validatedFilters(
        Request $request,
        bool $mechanicRequired = false
    ): array {
        $validated = $request->validate([
            'mechanic_id' => [$mechanicRequired ? 'required' : 'nullable', 'integer'],
            'paper' => ['nullable', 'string', 'in:a4,a5'],
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'transaction_type' => ['nullable', 'string', 'max:40'],
            'search' => ['nullable', 'string', 'max:100'],
            'per_page' => ['nullable', 'integer', 'in:20,50,100'],
        ]);

        $fromDate = (string) ($validated['from_date'] ?? now()->startOfMonth()->toDateString());
        $toDate = (string) ($validated['to_date'] ?? now()->toDateString());
        $type = (string) ($validated['transaction_type'] ?? '');
        $paper = strtolower((string) ($validated['paper'] ?? 'a4'));

        if ($type !== '' && !array_key_exists($type, $this->transactionTypes())) {
            $type = '';
        }

        if (!in_array($paper, ['a4', 'a5'], true)) {
            $paper = 'a4';
        }

        return [
            'mechanic_id' => isset($validated['mechanic_id'])
                ? (int) $validated['mechanic_id']
                : null,
            'paper' => $paper,
            'from_date' => $fromDate,
            'to_date' => $toDate,
            'transaction_type' => $type,
            'search' => trim((string) ($validated['search'] ?? '')),
            'per_page' => (int) ($validated['per_page'] ?? 20),
        ];
    }

    private function selectedMechanic(
        ?int $mechanicId,
        int $companyId,
        int $storeId
    ): ?Mechanic {
        if (!$mechanicId) {
            return null;
        }

        return $this->mechanicQuery($companyId, $storeId)
            ->findOrFail($mechanicId);
    }

    private function mechanicQuery(
        int $companyId,
        int $storeId
    ): Builder {
        return Mechanic::query()
            ->where('company_id', $companyId)
            ->where('status', true)
            ->whereHas('stores', function (Builder $query) use ($storeId): void {
                $query->where('stores.id', $storeId);
            });
    }

    private function mechanicData(Mechanic $mechanic, float $balance): array
    {
        return [
            'id' => $mechanic->id,
            'mechanic_code' => $mechanic->mechanic_code,
            'mechanic_name' => $mechanic->mechanic_name,
            'mobile' => $mechanic->mobile,
            'current_balance' => round($balance, 2),
            'balance_label' => $this->balanceLabel($balance),
        ];
    }

    private function transactionTypes(): array
    {
        return collect(MechanicLedgerEntry::transactionTypes())
            ->mapWithKeys(fn(string $type): array => [
                $type => $this->typeLabel($type),
            ])
            ->all();
    }

    private function typeLabel(string $type): string
    {
        return match ($type) {
            MechanicLedgerEntry::TYPE_OPENING_BALANCE => 'Opening Balance',
            MechanicLedgerEntry::TYPE_COMMISSION => 'Commission Earned',
            MechanicLedgerEntry::TYPE_COMMISSION_REVERSAL => 'Commission Reversal',
            MechanicLedgerEntry::TYPE_MECHANIC_PAYMENT => 'Mechanic Payment',
            MechanicLedgerEntry::TYPE_PAYMENT_REVERSAL => 'Payment Reversal',
            MechanicLedgerEntry::TYPE_SALES_RETURN_COMMISSION =>
            'Sales Return Commission',
            MechanicLedgerEntry::TYPE_SALES_RETURN_COMMISSION_REVERSAL =>
            'Sales Return Commission Reversal',
            MechanicLedgerEntry::TYPE_ADJUSTMENT => 'Ledger Adjustment',
            MechanicLedgerEntry::TYPE_ADJUSTMENT_REVERSAL => 'Adjustment Reversal',
            default => ucwords(str_replace('_', ' ', $type)),
        };
    }

    private function entryTypeLabel(
        MechanicLedgerEntry $entry
    ): string {
        $isSalesReturn = in_array(
            $entry->reference_type,
            [
                SalesReturn::class,
                'sales_return',
            ],
            true
        );

        if (
            $isSalesReturn
            && $entry->transaction_type
            === MechanicLedgerEntry::TYPE_ADJUSTMENT
        ) {
            return 'Sales Return Commission';
        }

        if (
            $isSalesReturn
            && $entry->transaction_type
            === MechanicLedgerEntry::TYPE_ADJUSTMENT_REVERSAL
        ) {
            return 'Sales Return Commission Reversal';
        }

        return $this->typeLabel($entry->transaction_type);
    }

    private function balanceLabel(float $balance): string
    {
        if ($balance > 0.009) {
            return number_format(abs($balance), 2)
                . ' Payable (Cr)';
        }

        if ($balance < -0.009) {
            return number_format(abs($balance), 2)
                . ' Advance (Dr)';
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
            'Select an active company and branch before accessing mechanic ledger.'
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
