<?php

namespace App\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\SupplierLedgerEntry;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\View\View as ViewContract;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Inertia\Inertia;
use Inertia\Response;

class SupplierLedgerController extends Controller implements HasMiddleware
{
    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    */

    public static function middleware(): array
    {
        return [
            new Middleware(
                'permission:supplier-ledger.view',
                only: [
                    'index',
                    'searchSuppliers',
                    'print',
                    'pdf',
                    'excel',
                ]
            ),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Supplier Ledger
    |--------------------------------------------------------------------------
    */

    public function index(
        Request $request
    ): Response {

        [
            $companyId,
            $storeId,
        ] = $this->context(
            $request
        );


        /*
        |--------------------------------------------------------------------------
        | Filters
        |--------------------------------------------------------------------------
        */

        $validated = $request->validate([
            'supplier_id' => [
                'nullable',
                'integer',
            ],

            'from_date' => [
                'nullable',
                'date',
            ],

            'to_date' => [
                'nullable',
                'date',
                'after_or_equal:from_date',
            ],

            'transaction_type' => [
                'nullable',
                'string',
                'max:40',
            ],

            'search' => [
                'nullable',
                'string',
                'max:100',
            ],

            'per_page' => [
                'nullable',
                'integer',
                'in:25,50,100',
            ],
        ]);


        $supplierId =
            isset(
                $validated['supplier_id']
            )
            ? (int) $validated['supplier_id']
            : null;

        $fromDate =
            $validated['from_date']
            ?? now()
            ->startOfMonth()
            ->toDateString();

        $toDate =
            $validated['to_date']
            ?? now()->toDateString();

        $transactionType =
            $validated['transaction_type']
            ?? null;

        $search =
            trim(
                (string) (
                    $validated['search']
                    ?? ''
                )
            );

        $perPage =
            (int) (
                $validated['per_page']
                ?? 50
            );


        /*
        |--------------------------------------------------------------------------
        | Selected Supplier
        |--------------------------------------------------------------------------
        */

        $selectedSupplier = null;


        if ($supplierId) {

            $selectedSupplier =
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
                    function (
                        Builder $query
                    ) use (
                        $storeId
                    ): void {

                        $query->where(
                            'stores.id',
                            $storeId
                        );
                    }
                )
                ->firstOrFail();
        }


        /*
        |--------------------------------------------------------------------------
        | Supplier Selector
        |--------------------------------------------------------------------------
        */

        $suppliers =
            Supplier::query()
            ->select([
                'suppliers.id',
                'suppliers.supplier_code',
                'suppliers.supplier_name',
                'suppliers.phone',
            ])
            ->where(
                'suppliers.company_id',
                $companyId
            )
            ->where(
                'suppliers.status',
                true
            )
            ->whereHas(
                'stores',
                function (
                    Builder $query
                ) use (
                    $storeId
                ): void {

                    $query->where(
                        'stores.id',
                        $storeId
                    );
                }
            )
            ->orderBy(
                'suppliers.supplier_name'
            )
            ->limit(200)
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Empty Ledger
        |--------------------------------------------------------------------------
        |
        | Do not load every supplier's transactions together. Running balances
        | must always belong to one supplier.
        |
        */

        if (!$selectedSupplier) {

            return Inertia::render(
                'SupplierLedgers/Index',
                [
                    'filters' => [
                        'supplier_id' => null,
                        'from_date' => $fromDate,
                        'to_date' => $toDate,
                        'transaction_type' =>
                        $transactionType,
                        'search' => $search,
                        'per_page' => $perPage,
                    ],

                    'suppliers' =>
                    $suppliers,

                    'selected_supplier' =>
                    null,

                    'entries' =>
                    $this->emptyPaginator(
                        $perPage
                    ),

                    'summary' =>
                    $this->emptySummary(),

                    'transaction_types' =>
                    $this->transactionTypes(),
                ]
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Base Supplier Query
        |--------------------------------------------------------------------------
        */

        $supplierQuery =
            SupplierLedgerEntry::query()
            ->where(
                'company_id',
                $companyId
            )
            ->where(
                'store_id',
                $storeId
            )
            ->where(
                'supplier_id',
                $selectedSupplier->id
            );


        /*
        |--------------------------------------------------------------------------
        | Opening Balance Before Filter Date
        |--------------------------------------------------------------------------
        */

        $openingBalance =
            (float) (
                (clone $supplierQuery)
                ->whereDate(
                    'transaction_date',
                    '<',
                    $fromDate
                )
                ->selectRaw(
                    'COALESCE(SUM(credit - debit), 0) AS balance'
                )
                ->value(
                    'balance'
                )
                ?? 0
            );


        /*
        |--------------------------------------------------------------------------
        | Period Query
        |--------------------------------------------------------------------------
        */

        $periodQuery =
            (clone $supplierQuery)
            ->whereBetween(
                'transaction_date',
                [
                    $fromDate,
                    $toDate,
                ]
            )
            ->when(
                $transactionType,
                function (
                    Builder $query
                ) use (
                    $transactionType
                ): void {

                    $query->where(
                        'transaction_type',
                        $transactionType
                    );
                }
            )
            ->when(
                $search !== '',
                function (
                    Builder $query
                ) use (
                    $search
                ): void {

                    $query->where(
                        function (
                            Builder $searchQuery
                        ) use (
                            $search
                        ): void {

                            $searchQuery
                                ->where(
                                    'reference_no',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'remarks',
                                    'like',
                                    "%{$search}%"
                                );
                        }
                    );
                }
            );


        /*
        |--------------------------------------------------------------------------
        | Period Totals
        |--------------------------------------------------------------------------
        */

        $periodTotals =
            (clone $periodQuery)
            ->selectRaw(
                '
                    COALESCE(SUM(debit), 0) AS total_debit,
                    COALESCE(SUM(credit), 0) AS total_credit
                '
            )
            ->first();


        $totalDebit =
            round(
                (float) (
                    $periodTotals->total_debit
                    ?? 0
                ),
                2
            );

        $totalCredit =
            round(
                (float) (
                    $periodTotals->total_credit
                    ?? 0
                ),
                2
            );


        /*
        |--------------------------------------------------------------------------
        | Current Overall Balance
        |--------------------------------------------------------------------------
        */

        $currentBalance =
            round(
                (float) (
                    (clone $supplierQuery)
                    ->selectRaw(
                        'COALESCE(SUM(credit - debit), 0) AS balance'
                    )
                    ->value(
                        'balance'
                    )
                    ?? 0
                ),
                2
            );


        /*
        |--------------------------------------------------------------------------
        | Paginated Entries
        |--------------------------------------------------------------------------
        */

        $entries =
            (clone $periodQuery)
            ->with([
                'creator:id,name',
            ])
            ->orderBy(
                'transaction_date'
            )
            ->orderBy(
                'id'
            )
            ->paginate(
                $perPage
            )
            ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | Balance Before Current Page
        |--------------------------------------------------------------------------
        |
        | This keeps the running balance correct on page 2, page 3, etc.
        |
        */

        $pageOpeningBalance =
            $openingBalance;


        if (
            $entries->currentPage() > 1
        ) {

            $pageOffset =
                (
                    $entries->currentPage()
                    - 1
                )
                * $entries->perPage();


            $previousPageEffect =
                (float) (
                    (clone $periodQuery)
                    ->orderBy(
                        'transaction_date'
                    )
                    ->orderBy(
                        'id'
                    )
                    ->limit(
                        $pageOffset
                    )
                    ->get([
                        'debit',
                        'credit',
                    ])
                    ->sum(
                        fn(
                            SupplierLedgerEntry $entry
                        ): float =>
                        (float) $entry->credit
                            - (float) $entry->debit
                    )
                );


            $pageOpeningBalance +=
                $previousPageEffect;
        }


        /*
        |--------------------------------------------------------------------------
        | Build Running Balance
        |--------------------------------------------------------------------------
        */

        $runningBalance =
            round(
                $pageOpeningBalance,
                2
            );


        $entries->through(
            function (
                SupplierLedgerEntry $entry
            ) use (
                &$runningBalance
            ): array {

                $runningBalance =
                    round(
                        $runningBalance
                            + (float) $entry->credit
                            - (float) $entry->debit,
                        2
                    );


                return [
                    'id' =>
                    $entry->id,

                    'transaction_date' =>
                    $entry->transaction_date
                        ?->toDateString(),

                    'transaction_type' =>
                    $entry->transaction_type,

                    'transaction_label' =>
                    $this->transactionLabel(
                        $entry->transaction_type
                    ),

                    'reference_type' =>
                    $entry->reference_type,

                    'reference_id' =>
                    $entry->reference_id,

                    'reference_no' =>
                    $entry->reference_no,

                    'reference_url' =>
                    $this->referenceUrl(
                        $entry
                    ),

                    'debit' =>
                    round(
                        (float) $entry->debit,
                        2
                    ),

                    'credit' =>
                    round(
                        (float) $entry->credit,
                        2
                    ),

                    'running_balance' =>
                    $runningBalance,

                    'balance_label' =>
                    $this->balanceLabel(
                        $runningBalance
                    ),

                    'is_reversal' =>
                    (bool) $entry->is_reversal,

                    'reversal_of_id' =>
                    $entry->reversal_of_id,

                    'remarks' =>
                    $entry->remarks,

                    'creator' =>
                    $entry->creator
                        ? [
                            'id' =>
                            $entry->creator->id,

                            'name' =>
                            $entry->creator->name,
                        ]
                        : null,
                ];
            }
        );


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'SupplierLedgers/Index',
            [
                'filters' => [
                    'supplier_id' =>
                    $selectedSupplier->id,

                    'from_date' =>
                    $fromDate,

                    'to_date' =>
                    $toDate,

                    'transaction_type' =>
                    $transactionType,

                    'search' =>
                    $search,

                    'per_page' =>
                    $perPage,
                ],

                'suppliers' =>
                $suppliers,

                'selected_supplier' => [
                    'id' =>
                    $selectedSupplier->id,

                    'supplier_code' =>
                    $selectedSupplier->supplier_code,

                    'supplier_name' =>
                    $selectedSupplier->supplier_name,

                    'phone' =>
                    $selectedSupplier->phone,

                    'balance_type' =>
                    $selectedSupplier->balance_type,
                ],

                'entries' =>
                $entries,

                'summary' => [
                    'opening_balance' =>
                    round(
                        $openingBalance,
                        2
                    ),

                    'total_debit' =>
                    $totalDebit,

                    'total_credit' =>
                    $totalCredit,

                    'closing_balance' =>
                    round(
                        $openingBalance
                            + $totalCredit
                            - $totalDebit,
                        2
                    ),

                    'current_balance' =>
                    $currentBalance,

                    'opening_balance_label' =>
                    $this->balanceLabel(
                        $openingBalance
                    ),

                    'closing_balance_label' =>
                    $this->balanceLabel(
                        $openingBalance
                            + $totalCredit
                            - $totalDebit
                    ),

                    'current_balance_label' =>
                    $this->balanceLabel(
                        $currentBalance
                    ),
                ],

                'transaction_types' =>
                $this->transactionTypes(),
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | AJAX Supplier Search
    |--------------------------------------------------------------------------
    */

    public function searchSuppliers(
        Request $request
    ) {

        [
            $companyId,
            $storeId,
        ] = $this->context(
            $request
        );


        $search =
            trim(
                (string) $request->query(
                    'search',
                    ''
                )
            );


        $suppliers =
            Supplier::query()
            ->select([
                'suppliers.id',
                'suppliers.supplier_code',
                'suppliers.supplier_name',
                'suppliers.phone',
            ])
            ->where(
                'suppliers.company_id',
                $companyId
            )
            ->where(
                'suppliers.status',
                true
            )
            ->whereHas(
                'stores',
                function (
                    Builder $query
                ) use (
                    $storeId
                ): void {

                    $query->where(
                        'stores.id',
                        $storeId
                    );
                }
            )
            ->when(
                $search !== '',
                function (
                    Builder $query
                ) use (
                    $search
                ): void {

                    $query->where(
                        function (
                            Builder $searchQuery
                        ) use (
                            $search
                        ): void {

                            $searchQuery
                                ->where(
                                    'supplier_name',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'supplier_code',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'phone',
                                    'like',
                                    "%{$search}%"
                                );
                        }
                    );
                }
            )
            ->orderBy(
                'supplier_name'
            )
            ->limit(25)
            ->get();


        return response()->json([
            'success' => true,
            'data' => $suppliers,
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Print Report
    |--------------------------------------------------------------------------
    */

    public function print(
        Request $request
    ): ViewContract {

        $report = $this->reportData(
            $request
        );

        return view(
            'supplier_ledgers.print',
            array_merge(
                $report,
                [
                    'paper' => 'a4',
                    'pdfMode' => false,
                ]
            )
        );
    }


    /*
    |--------------------------------------------------------------------------
    | PDF Report
    |--------------------------------------------------------------------------
    */

    public function pdf(
        Request $request
    ): HttpResponse {

        $report = $this->reportData(
            $request
        );

        $fileName =
            'supplier-ledger-'
            . $report['supplier']->supplier_code
            . '-'
            . $report['filters']['from_date']
            . '-to-'
            . $report['filters']['to_date']
            . '.pdf';

        return Pdf::loadView(
            'supplier_ledgers.pdf',
            array_merge(
                $report,
                [
                    'paper' => 'a4',
                    'pdfMode' => true,
                ]
            )
        )
            ->setPaper(
                'a4',
                'landscape'
            )
            ->download(
                $fileName
            );
    }


    /*
    |--------------------------------------------------------------------------
    | Excel-Compatible CSV Report
    |--------------------------------------------------------------------------
    */

    public function excel(
        Request $request
    ): StreamedResponse {

        $report = $this->reportData(
            $request
        );

        $fileName =
            'supplier-ledger-'
            . $report['supplier']->supplier_code
            . '-'
            . $report['filters']['from_date']
            . '-to-'
            . $report['filters']['to_date']
            . '.csv';

        return response()->streamDownload(
            function () use (
                $report
            ): void {

                $handle = fopen(
                    'php://output',
                    'w'
                );

                fwrite(
                    $handle,
                    "\xEF\xBB\xBF"
                );

                fputcsv(
                    $handle,
                    [
                        'Date',
                        'Transaction',
                        'Reference',
                        'Remarks',
                        'Debit',
                        'Credit',
                        'Balance',
                    ]
                );

                foreach (
                    $report['entries']
                    as $entry
                ) {
                    fputcsv(
                        $handle,
                        [
                            $entry['transaction_date'],
                            $entry['transaction_label'],
                            $entry['reference_no'],
                            $entry['remarks'],
                            number_format(
                                $entry['debit'],
                                2,
                                '.',
                                ''
                            ),
                            number_format(
                                $entry['credit'],
                                2,
                                '.',
                                ''
                            ),
                            $entry['balance_label'],
                        ]
                    );
                }

                fclose(
                    $handle
                );
            },
            $fileName,
            [
                'Content-Type' =>
                'text/csv; charset=UTF-8',
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Printable Report Data
    |--------------------------------------------------------------------------
    */

    private function reportData(
        Request $request
    ): array {

        [
            $companyId,
            $storeId,
        ] = $this->context(
            $request
        );

        $validated = $request->validate([
            'supplier_id' => [
                'required',
                'integer',
            ],
            'from_date' => [
                'nullable',
                'date',
            ],
            'to_date' => [
                'nullable',
                'date',
                'after_or_equal:from_date',
            ],
            'transaction_type' => [
                'nullable',
                'string',
                'max:40',
            ],
            'search' => [
                'nullable',
                'string',
                'max:100',
            ],
        ]);

        $fromDate =
            $validated['from_date']
            ?? now()
            ->startOfMonth()
            ->toDateString();

        $toDate =
            $validated['to_date']
            ?? now()->toDateString();

        $transactionType =
            $validated['transaction_type']
            ?? null;

        $search = trim(
            (string) (
                $validated['search']
                ?? ''
            )
        );

        $supplier =
            Supplier::query()
            ->whereKey(
                (int) $validated['supplier_id']
            )
            ->where(
                'company_id',
                $companyId
            )
            ->whereHas(
                'stores',
                fn(Builder $query) =>
                $query->where(
                    'stores.id',
                    $storeId
                )
            )
            ->firstOrFail();

        $store =
            Store::query()
            ->with(
                'company'
            )
            ->whereKey(
                $storeId
            )
            ->where(
                'company_id',
                $companyId
            )
            ->firstOrFail();

        $supplierQuery =
            SupplierLedgerEntry::query()
            ->where(
                'company_id',
                $companyId
            )
            ->where(
                'store_id',
                $storeId
            )
            ->where(
                'supplier_id',
                $supplier->id
            );

        $openingBalance =
            round(
                (float) (
                    (clone $supplierQuery)
                    ->whereDate(
                        'transaction_date',
                        '<',
                        $fromDate
                    )
                    ->selectRaw(
                        'COALESCE(SUM(credit - debit), 0) AS balance'
                    )
                    ->value(
                        'balance'
                    )
                    ?? 0
                ),
                2
            );

        $entriesQuery =
            (clone $supplierQuery)
            ->whereBetween(
                'transaction_date',
                [
                    $fromDate,
                    $toDate,
                ]
            )
            ->when(
                $transactionType,
                fn(Builder $query) =>
                $query->where(
                    'transaction_type',
                    $transactionType
                )
            )
            ->when(
                $search !== '',
                function (
                    Builder $query
                ) use (
                    $search
                ): void {
                    $query->where(
                        function (
                            Builder $searchQuery
                        ) use (
                            $search
                        ): void {
                            $searchQuery
                                ->where(
                                    'reference_no',
                                    'like',
                                    "%{$search}%"
                                )
                                ->orWhere(
                                    'remarks',
                                    'like',
                                    "%{$search}%"
                                );
                        }
                    );
                }
            )
            ->orderBy(
                'transaction_date'
            )
            ->orderBy(
                'id'
            );

        $runningBalance =
            $openingBalance;

        $totalDebit = 0.0;
        $totalCredit = 0.0;

        $entries =
            $entriesQuery
            ->get()
            ->map(
                function (
                    SupplierLedgerEntry $entry
                ) use (
                    &$runningBalance,
                    &$totalDebit,
                    &$totalCredit
                ): array {

                    $debit =
                        round(
                            (float) $entry->debit,
                            2
                        );

                    $credit =
                        round(
                            (float) $entry->credit,
                            2
                        );

                    $totalDebit += $debit;
                    $totalCredit += $credit;

                    $runningBalance =
                        round(
                            $runningBalance
                                + $credit
                                - $debit,
                            2
                        );

                    return [
                        'id' => $entry->id,
                        'transaction_date' =>
                        $entry->transaction_date
                            ?->toDateString(),
                        'transaction_type' =>
                        $entry->transaction_type,
                        'transaction_label' =>
                        $this->transactionLabel(
                            $entry->transaction_type
                        ),
                        'reference_no' =>
                        $entry->reference_no,
                        'remarks' =>
                        $entry->remarks,
                        'debit' => $debit,
                        'credit' => $credit,
                        'running_balance' =>
                        $runningBalance,
                        'balance_label' =>
                        $this->balanceLabel(
                            $runningBalance
                        ),
                        'is_reversal' =>
                        (bool) $entry->is_reversal,
                    ];
                }
            );

        $currentBalance =
            round(
                (float) (
                    (clone $supplierQuery)
                    ->selectRaw(
                        'COALESCE(SUM(credit - debit), 0) AS balance'
                    )
                    ->value(
                        'balance'
                    )
                    ?? 0
                ),
                2
            );

        return [
            'supplier' => $supplier,
            'store' => $store,
            'entries' => $entries,
            'filters' => [
                'supplier_id' =>
                $supplier->id,
                'from_date' =>
                $fromDate,
                'to_date' =>
                $toDate,
                'transaction_type' =>
                $transactionType,
                'search' =>
                $search,
            ],
            'summary' => [
                'opening_balance' =>
                $openingBalance,
                'total_debit' =>
                round(
                    $totalDebit,
                    2
                ),
                'total_credit' =>
                round(
                    $totalCredit,
                    2
                ),
                'closing_balance' =>
                $runningBalance,
                'current_balance' =>
                $currentBalance,
                'opening_balance_label' =>
                $this->balanceLabel(
                    $openingBalance
                ),
                'closing_balance_label' =>
                $this->balanceLabel(
                    $runningBalance
                ),
                'current_balance_label' =>
                $this->balanceLabel(
                    $currentBalance
                ),
            ],
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Active Context
    |--------------------------------------------------------------------------
    */

    private function context(
        Request $request
    ): array {

        $companyId =
            (int) $request->session()->get(
                'active_company_id'
            );

        $storeId =
            (int) $request->session()->get(
                'active_store_id'
            );


        if (
            $companyId <= 0 ||
            $storeId <= 0
        ) {
            abort(
                403,
                'Please select a company and branch.'
            );
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
            ->where(
                'status',
                true
            )
            ->exists();


        if (!$storeExists) {
            abort(
                403,
                'The active branch is invalid.'
            );
        }


        return [
            $companyId,
            $storeId,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Transaction Types
    |--------------------------------------------------------------------------
    */

    private function transactionTypes(): array
    {
        return [
            [
                'value' =>
                SupplierLedgerEntry::TYPE_OPENING_BALANCE,

                'label' =>
                'Opening Balance',
            ],
            [
                'value' =>
                SupplierLedgerEntry::TYPE_PURCHASE,

                'label' =>
                'Purchase',
            ],
            [
                'value' =>
                SupplierLedgerEntry::TYPE_PURCHASE_RETURN,

                'label' =>
                'Purchase Return',
            ],
            [
                'value' =>
                SupplierLedgerEntry::TYPE_SUPPLIER_PAYMENT,

                'label' =>
                'Supplier Payment',
            ],
            [
                'value' =>
                SupplierLedgerEntry::TYPE_PAYMENT_REVERSAL,

                'label' =>
                'Payment Reversal',
            ],
            [
                'value' =>
                'purchase_reversal',

                'label' =>
                'Purchase Reversal',
            ],
            [
                'value' =>
                'purchase_return_reversal',

                'label' =>
                'Purchase Return Reversal',
            ],
            [
                'value' =>
                SupplierLedgerEntry::TYPE_ADJUSTMENT,

                'label' =>
                'Adjustment',
            ],
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Transaction Label
    |--------------------------------------------------------------------------
    */

    private function transactionLabel(
        string $transactionType
    ): string {

        return match ($transactionType) {
            SupplierLedgerEntry::TYPE_OPENING_BALANCE =>
            'Opening Balance',

            SupplierLedgerEntry::TYPE_PURCHASE =>
            'Purchase',

            SupplierLedgerEntry::TYPE_PURCHASE_RETURN =>
            'Purchase Return',

            SupplierLedgerEntry::TYPE_SUPPLIER_PAYMENT =>
            'Supplier Payment',

            SupplierLedgerEntry::TYPE_PAYMENT_REVERSAL =>
            'Payment Reversal',

            'purchase_reversal' =>
            'Purchase Reversal',

            'purchase_return_reversal' =>
            'Purchase Return Reversal',

            SupplierLedgerEntry::TYPE_ADJUSTMENT =>
            'Adjustment',

            default =>
            ucwords(
                str_replace(
                    '_',
                    ' ',
                    $transactionType
                )
            ),
        };
    }


    /*
    |--------------------------------------------------------------------------
    | Reference URL
    |--------------------------------------------------------------------------
    */

    private function referenceUrl(
        SupplierLedgerEntry $entry
    ): ?string {

        if (!$entry->reference_id) {
            return null;
        }


        return match ($entry->transaction_type) {
            SupplierLedgerEntry::TYPE_PURCHASE =>
            "/purchases/{$entry->reference_id}",

            'purchase_reversal' =>
            "/purchases/{$entry->reference_id}",

            SupplierLedgerEntry::TYPE_PURCHASE_RETURN =>
            "/purchase-returns/{$entry->reference_id}",

            'purchase_return_reversal' =>
            "/purchase-returns/{$entry->reference_id}",

            SupplierLedgerEntry::TYPE_SUPPLIER_PAYMENT,
            SupplierLedgerEntry::TYPE_PAYMENT_REVERSAL =>
            "/supplier-payments/{$entry->reference_id}",

            default =>
            null,
        };
    }


    /*
    |--------------------------------------------------------------------------
    | Balance Label
    |--------------------------------------------------------------------------
    */

    private function balanceLabel(
        float $balance
    ): string {

        if ($balance > 0) {
            return number_format(
                abs(
                    $balance
                ),
                2
            ) . ' Cr';
        }


        if ($balance < 0) {
            return number_format(
                abs(
                    $balance
                ),
                2
            ) . ' Dr';
        }


        return '0.00';
    }


    /*
    |--------------------------------------------------------------------------
    | Empty Summary
    |--------------------------------------------------------------------------
    */

    private function emptySummary(): array
    {
        return [
            'opening_balance' => 0,
            'total_debit' => 0,
            'total_credit' => 0,
            'closing_balance' => 0,
            'current_balance' => 0,

            'opening_balance_label' =>
            '0.00',

            'closing_balance_label' =>
            '0.00',

            'current_balance_label' =>
            '0.00',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Empty Paginator
    |--------------------------------------------------------------------------
    */

    private function emptyPaginator(
        int $perPage
    ): array {

        return [
            'current_page' => 1,
            'data' => [],
            'first_page_url' => null,
            'from' => null,
            'last_page' => 1,
            'last_page_url' => null,
            'links' => [],
            'next_page_url' => null,
            'path' => url(
                '/supplier-ledger'
            ),
            'per_page' => $perPage,
            'prev_page_url' => null,
            'to' => null,
            'total' => 0,
        ];
    }
}
