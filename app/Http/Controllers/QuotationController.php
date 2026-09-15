<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\AccountTransaction;
use App\Models\Customer;
use App\Models\Godown;
use App\Models\Mechanic;
use App\Models\MechanicCommission;
use App\Models\Product;
use App\Models\Quotation;
use App\Models\QuotationItem;
use App\Models\SaleItems;
use App\Models\SalePayment;
use App\Models\Sales;
use App\Models\StockLedger;
use App\Models\Store;
use App\Services\StockService;
use App\Support\AppResponse;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use Throwable;

class QuotationController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(
                'permission:quotation.view',
                only: ['index']
            ),
            new Middleware(
                'permission:quotation.show',
                only: ['show']
            ),
            new Middleware(
                'permission:quotation.create',
                only: ['create', 'store']
            ),
            new Middleware(
                'permission:quotation.create|quotation.edit',
                only: [
                    'searchProducts',
                    'searchCustomers',
                    'searchMechanics',
                    'quickCustomer',
                    'quickMechanic',
                ]
            ),
            new Middleware(
                'permission:quotation.edit',
                only: ['edit', 'update', 'updateStatus']
            ),
            new Middleware(
                'permission:quotation.delete',
                only: ['cancel']
            ),
            new Middleware(
                'permission:quotation.print',
                only: ['print', 'pdf']
            ),
            new Middleware(
                'permission:quotation.convert-sale',
                only: ['convertToSale']
            ),
            new Middleware(
                'permission:quotation.convert-sale',
                only: ['checkConversionStock', 'bulkTransferStock']
            ),
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index(Request $request): Response
    {
        [$companyId, $storeId] = $this->context($request);

        $filters = [
            'search' => trim((string) $request->input('search', '')),
            'quick_filter' => $request->input('quick_filter', 'today'),
            'document_type' => $request->input('document_type', 'all'),
            'status' => $request->input('status', 'all'),
            'date_from' => $request->input('date_from'),
            'date_to' => $request->input('date_to'),
        ];

        $query = Quotation::query()
            ->forContext($companyId, $storeId)
            ->with([
                'customer:id,customer_code,customer_name,mobile',
                'mechanic:id,mechanic_code,mechanic_name,mobile',
            ])
            ->when(
                $filters['search'] !== '',
                function (Builder $query) use ($filters) {
                    $search = $filters['search'];

                    $query->where(function (Builder $query) use ($search) {
                        $query
                            ->where('quotation_no', 'like', "%{$search}%")
                            ->orWhereHas(
                                'customer',
                                fn(Builder $customer) => $customer
                                    ->where('customer_name', 'like', "%{$search}%")
                                    ->orWhere('customer_code', 'like', "%{$search}%")
                                    ->orWhere('mobile', 'like', "%{$search}%")
                            );
                    });
                }
            )
            ->when(
                in_array(
                    $filters['document_type'],
                    ['quotation', 'proforma_invoice'],
                    true
                ),
                fn(Builder $query) => $query->where(
                    'document_type',
                    $filters['document_type']
                )
            )
            ->when(
                in_array(
                    $filters['status'],
                    [
                        'Draft',
                        'Pending',
                        'Approved',
                        'Rejected',
                        'Converted',
                        'Expired',
                        'Cancelled',
                    ],
                    true
                ),
                fn(Builder $query) => $query->where(
                    'status',
                    $filters['status']
                )
            );

        $this->applyDateFilter($query, $filters);

        $quotations = $query
            ->latest('quotation_date')
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        $todayAmount = (float) Quotation::query()
            ->forContext($companyId, $storeId)
            ->whereDate('quotation_date', today())
            ->whereNotIn('status', ['Rejected', 'Cancelled'])
            ->sum('grand_total');

        return Inertia::render('Quotations/Index', [
            'quotations' => $quotations,
            'filters' => $filters,
            'todayAmount' => round($todayAmount, 2),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */

    public function create(Request $request): Response
    {
        [, $storeId] = $this->context($request);

        $documentType = $request->input('type', 'quotation');

        if (!in_array($documentType, ['quotation', 'proforma_invoice'], true)) {
            $documentType = 'quotation';
        }

        return Inertia::render('Quotations/Create', [
            'documentType' => $documentType,
            'documentNo' => $this->previewDocumentNo(
                $storeId,
                $documentType
            ),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Search Products
    |--------------------------------------------------------------------------
    */

    public function searchProducts(Request $request): JsonResponse
    {
        [$companyId, $storeId] = $this->context($request);

        $validated = $request->validate([
            'search' => ['required', 'string', 'min:2', 'max:100'],
        ]);

        $search = trim($validated['search']);

        $products = Product::query()
            ->where('company_id', $companyId)
            ->where('store_id', $storeId)
            ->where('status', true)
            ->with([
                'unit',
                'brand',
                'category',
            ])
            ->where(function (Builder $query) use ($search) {
                $query
                    ->where('product_name', 'like', "%{$search}%")
                    ->orWhere('product_code', 'like', "%{$search}%")
                    ->orWhere('barcode', 'like', "%{$search}%")
                    ->orWhere('part_number', 'like', "%{$search}%")
                    ->orWhere('manufacturer', 'like', "%{$search}%")
                    ->orWhere('hsn_code', 'like', "%{$search}%");
            })
            ->orderBy('product_name')
            ->limit(30)
            ->get();

        $products->each(function (Product $product) use (
            $companyId,
            $storeId
        ) {
            $product->setAttribute(
                'shop_stock',
                $this->currentStock(
                    $companyId,
                    $storeId,
                    null,
                    (int) $product->id
                )
            );
        });

        return response()->json([
            'success' => true,
            'products' => $products,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Search Customers
    |--------------------------------------------------------------------------
    */

    public function searchCustomers(Request $request): JsonResponse
    {
        [$companyId, $storeId] = $this->context($request);

        $validated = $request->validate([
            'search' => ['required', 'string', 'min:2', 'max:100'],
        ]);

        $search = trim($validated['search']);

        $customers = Customer::query()
            ->where('company_id', $companyId)
            ->where('status', true)
            ->whereHas(
                'stores',
                fn(Builder $query) => $query->where('stores.id', $storeId)
            )
            ->where(function (Builder $query) use ($search) {
                $query
                    ->where('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_code', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%")
                    ->orWhere('gst_number', 'like', "%{$search}%");
            })
            ->orderByRaw('is_system_customer DESC')
            ->orderBy('customer_name')
            ->limit(20)
            ->get([
                'id',
                'customer_code',
                'customer_name',
                'mobile',
                'phone',
                'gst_number',
                'customer_type',
                'is_system_customer',
            ]);

        return response()->json([
            'success' => true,
            'customers' => $customers,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Search Mechanics
    |--------------------------------------------------------------------------
    */

    public function searchMechanics(Request $request): JsonResponse
    {
        [$companyId, $storeId] = $this->context($request);

        $validated = $request->validate([
            'search' => ['required', 'string', 'min:2', 'max:100'],
        ]);

        $search = trim($validated['search']);

        $mechanics = Mechanic::query()
            ->where('company_id', $companyId)
            ->where('status', true)
            ->whereHas(
                'stores',
                fn(Builder $query) => $query->where('stores.id', $storeId)
            )
            ->where(function (Builder $query) use ($search) {
                $query
                    ->where('mechanic_name', 'like', "%{$search}%")
                    ->orWhere('mechanic_code', 'like', "%{$search}%")
                    ->orWhere('mobile', 'like', "%{$search}%");
            })
            ->orderBy('mechanic_name')
            ->limit(20)
            ->get([
                'id',
                'mechanic_code',
                'mechanic_name',
                'mobile',
            ]);

        return response()->json([
            'success' => true,
            'mechanics' => $mechanics,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Quick Customer
    |--------------------------------------------------------------------------
    */

    public function quickCustomer(Request $request): JsonResponse
    {
        [$companyId, $storeId] = $this->context($request);

        $validated = $request->validate([
            'customer_name' => ['required', 'string', 'max:150'],
            'mobile' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'gst_number' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:1000'],
        ]);

        $mobile = $this->nullableTrim($validated['mobile'] ?? null);
        $gstNumber = $this->nullableTrim($validated['gst_number'] ?? null);
        $gstNumber = $gstNumber ? strtoupper($gstNumber) : null;

        $existing = Customer::query()
            ->where('company_id', $companyId)
            ->where(function (Builder $query) use ($mobile, $gstNumber) {
                $query
                    ->when($mobile, fn(Builder $query) => $query
                        ->where('mobile', $mobile))
                    ->when(
                        $mobile && $gstNumber,
                        fn(Builder $query) => $query->orWhere(
                            'gst_number',
                            $gstNumber
                        )
                    )
                    ->when(
                        !$mobile && $gstNumber,
                        fn(Builder $query) => $query->where(
                            'gst_number',
                            $gstNumber
                        )
                    );
            })
            ->when(
                !$mobile && !$gstNumber,
                fn(Builder $query) => $query->whereRaw('1 = 0')
            )
            ->first();

        if ($existing) {
            $existing->attachToStore($storeId);

            return response()->json([
                'success' => true,
                'existing' => true,
                'message' => 'Existing customer added to this branch.',
                'customer' => $this->customerJson($existing),
            ]);
        }

        try {
            $customer = DB::transaction(function () use (
                $validated,
                $companyId,
                $storeId,
                $mobile,
                $gstNumber
            ) {
                $customer = Customer::create([
                    'company_id' => $companyId,
                    'customer_code' => $this->generateCustomerCode($companyId),
                    'customer_name' => trim($validated['customer_name']),
                    'customer_type' => 'regular',
                    'is_system_customer' => false,
                    'mobile' => $mobile,
                    'phone' => $mobile,
                    'email' => $this->nullableTrim($validated['email'] ?? null),
                    'billing_address' => $this->nullableTrim(
                        $validated['address'] ?? null
                    ),
                    'shipping_address' => $this->nullableTrim(
                        $validated['address'] ?? null
                    ),
                    'gst_number' => $gstNumber,
                    'credit_limit' => 0,
                    'credit_days' => 0,
                    'opening_balance' => 0,
                    'balance_type' => 'Dr',
                    'status' => true,
                ]);

                $customer->attachToStore($storeId);

                return $customer;
            });

            return response()->json([
                'success' => true,
                'existing' => false,
                'message' => 'Customer created successfully.',
                'customer' => $this->customerJson($customer),
            ]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'message' => $exception->getMessage()
                    ?: 'Unable to create customer.',
            ], 500);
        }
    }

    /*
    |--------------------------------------------------------------------------
    | Quick Mechanic
    |--------------------------------------------------------------------------
    */

    public function quickMechanic(Request $request): JsonResponse
    {
        [$companyId, $storeId] = $this->context($request);

        $validated = $request->validate([
            'mechanic_name' => ['required', 'string', 'max:150'],
            'mobile' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'city' => ['nullable', 'string', 'max:100'],
        ]);

        $mobile = $this->nullableTrim($validated['mobile'] ?? null);

        if ($mobile) {
            $existing = Mechanic::query()
                ->where('company_id', $companyId)
                ->where('mobile', $mobile)
                ->first();

            if ($existing) {
                $existing->stores()->syncWithoutDetaching([$storeId]);

                return response()->json([
                    'success' => true,
                    'existing' => true,
                    'message' => 'Existing mechanic added to this branch.',
                    'mechanic' => $this->mechanicJson($existing),
                ]);
            }
        }

        $mechanic = DB::transaction(function () use (
            $validated,
            $companyId,
            $storeId,
            $mobile
        ) {
            $mechanic = Mechanic::create([
                'company_id' => $companyId,
                'mechanic_code' => $this->generateMechanicCode($companyId),
                'mechanic_name' => trim($validated['mechanic_name']),
                'mobile' => $mobile,
                'email' => $this->nullableTrim($validated['email'] ?? null),
                'address' => $this->nullableTrim($validated['address'] ?? null),
                'city' => $this->nullableTrim($validated['city'] ?? null),
                'status' => true,
            ]);

            $mechanic->stores()->syncWithoutDetaching([$storeId]);

            return $mechanic;
        });

        return response()->json([
            'success' => true,
            'existing' => false,
            'message' => 'Mechanic created successfully.',
            'mechanic' => $this->mechanicJson($mechanic),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */

    public function store(Request $request): RedirectResponse
    {
        [$companyId, $storeId] = $this->context($request);
        $validated = $this->validateQuotationRequest($request);

        $quotation = DB::transaction(function () use (
            $request,
            $validated,
            $companyId,
            $storeId
        ) {
            $customer = $this->validateCustomer(
                (int) $validated['customer_id'],
                $companyId,
                $storeId
            );

            $lines = $this->buildLines(
                $validated,
                $companyId,
                $storeId
            );
            $totals = $this->calculateTotals($lines, $validated);
            $commission = $this->quotationCommission(
                $validated,
                $companyId,
                $storeId,
                $totals['sub_total']
            );

            $quotation = Quotation::create([
                'company_id' => $companyId,
                'store_id' => $storeId,
                'customer_id' => $customer->id,
                'document_type' => $validated['document_type'],
                ...$commission,
                'quotation_no' => Quotation::generateDocumentNo(
                    $storeId,
                    $validated['document_type']
                ),
                'quotation_date' => $validated['quotation_date'],
                'valid_until' => $validated['valid_until'] ?? null,
                'billing_type' => $validated['billing_type'],
                ...$totals,
                'terms_conditions' => $validated['terms_conditions'] ?? null,
                'remarks' => $validated['remarks'] ?? null,
                'status' => $validated['status'],
                'created_by' => $request->user()->id,
            ]);

            $this->saveLines($quotation, $lines);

            return $quotation;
        });

        return redirect()
            ->route('quotations.show', $quotation)
            ->with('success', $quotation->documentTitle() . ' created successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */

    public function show(
        Request $request,
        Quotation $quotation
    ): Response {
        [$companyId, $storeId] = $this->context($request);
        $this->ensureQuotationContext($quotation, $companyId, $storeId);
        $this->loadDocument($quotation);

        return Inertia::render('Quotations/Show', [
            'quotation' => $quotation,
            'accounts' => $this->paymentAccountsForStore(
                $companyId,
                $storeId
            ),
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Edit
    |--------------------------------------------------------------------------
    */

    public function edit(
        Request $request,
        Quotation $quotation
    ): Response {
        $this->ensureQuotationContext($quotation, ...$this->context($request));

        if (!$quotation->canBeEdited()) {
            abort(422, 'This document can no longer be edited.');
        }

        $quotation->load([
            'customer',
            'mechanic',
            'items.product.unit',
        ]);

        return Inertia::render('Quotations/Edit', [
            'quotation' => $quotation,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        Quotation $quotation
    ): RedirectResponse {
        [$companyId, $storeId] = $this->context($request);
        $this->ensureQuotationContext($quotation, $companyId, $storeId);
        $validated = $this->validateQuotationRequest($request);

        DB::transaction(function () use (
            $quotation,
            $validated,
            $companyId,
            $storeId
        ) {
            $lockedQuotation = Quotation::query()
                ->lockForUpdate()
                ->findOrFail($quotation->id);

            if (!$lockedQuotation->canBeEdited()) {
                throw ValidationException::withMessages([
                    'quotation' => 'This document can no longer be edited.',
                ]);
            }

            if (
                $validated['document_type']
                !== $lockedQuotation->document_type
            ) {
                throw ValidationException::withMessages([
                    'document_type' =>
                    'Document type cannot be changed after creation.',
                ]);
            }

            $customer = $this->validateCustomer(
                (int) $validated['customer_id'],
                $companyId,
                $storeId
            );
            $lines = $this->buildLines(
                $validated,
                $companyId,
                $storeId
            );
            $totals = $this->calculateTotals($lines, $validated);
            $commission = $this->quotationCommission(
                $validated,
                $companyId,
                $storeId,
                $totals['sub_total']
            );

            $lockedQuotation->update([
                'customer_id' => $customer->id,
                'document_type' => $validated['document_type'],
                ...$commission,
                'quotation_date' => $validated['quotation_date'],
                'valid_until' => $validated['valid_until'] ?? null,
                'billing_type' => $validated['billing_type'],
                ...$totals,
                'terms_conditions' => $validated['terms_conditions'] ?? null,
                'remarks' => $validated['remarks'] ?? null,
                'status' => $validated['status'],
            ]);

            $lockedQuotation->items()->delete();
            $this->saveLines($lockedQuotation, $lines);
        });

        return AppResponse::successRoute(
            'quotations.show',
            $quotation->documentTitle() . ' updated successfully.',
            $quotation
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Update Status
    |--------------------------------------------------------------------------
    */

    public function updateStatus(
        Request $request,
        Quotation $quotation
    ): RedirectResponse {
        $this->ensureQuotationContext($quotation, ...$this->context($request));

        $validated = $request->validate([
            'status' => [
                'required',
                Rule::in(['Draft', 'Pending', 'Approved', 'Rejected']),
            ],
        ]);

        if (in_array($quotation->status, ['Converted', 'Cancelled'], true)) {
            throw ValidationException::withMessages([
                'status' => 'Converted or cancelled documents cannot be changed.',
            ]);
        }

        if (
            $validated['status'] === 'Approved'
            && $quotation->hasExpired()
        ) {
            throw ValidationException::withMessages([
                'status' => 'An expired document cannot be approved.',
            ]);
        }

        $quotation->update(['status' => $validated['status']]);

        return back()->with('success', 'Document status updated successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Cancel
    |--------------------------------------------------------------------------
    */

    public function cancel(
        Request $request,
        Quotation $quotation
    ): RedirectResponse {
        $this->ensureQuotationContext($quotation, ...$this->context($request));

        if ($quotation->isConverted()) {
            throw ValidationException::withMessages([
                'quotation' => 'A converted document cannot be cancelled.',
            ]);
        }

        if ($quotation->isCancelled()) {
            throw ValidationException::withMessages([
                'quotation' => 'This document is already cancelled.',
            ]);
        }

        $quotation->update(['status' => 'Cancelled']);

        return back()->with('success', 'Document cancelled successfully.');
    }

    /*
    |--------------------------------------------------------------------------
    | Convert To Sale
    |--------------------------------------------------------------------------
    */

    public function checkConversionStock(
        Request $request,
        Quotation $quotation
    ): JsonResponse {
        [$companyId, $storeId] = $this->context($request);
        $this->ensureQuotationContext($quotation, $companyId, $storeId);

        $quotation->load(['items.product']);

        if (!$quotation->canBeConverted()) {
            throw ValidationException::withMessages([
                'quotation' =>
                'Only an approved, unexpired document can be converted.',
            ]);
        }

        $shortages = $this->quotationStockShortages(
            $quotation,
            $companyId,
            $storeId
        );

        return response()->json([
            'success' => true,
            'ready' => count($shortages) === 0,
            'shortages' => $shortages,
        ]);
    }

    public function bulkTransferStock(
        Request $request,
        Quotation $quotation,
        StockService $stockService
    ): JsonResponse {
        [$companyId, $storeId] = $this->context($request);
        $this->ensureQuotationContext($quotation, $companyId, $storeId);

        $validator = Validator::make($request->all(), [
            'transfers' => ['required', 'array', 'min:1'],
            'transfers.*.product_id' => [
                'required',
                'integer',
                'exists:products,id',
            ],
            'transfers.*.godown_id' => [
                'required',
                'integer',
                'exists:godowns,id',
            ],
            'transfers.*.quantity' => [
                'required',
                'numeric',
                'gt:0',
            ],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors' => $validator->errors()->toArray(),
            ], 422);
        }

        $validated = $validator->validated();

        try {
            DB::transaction(function () use (
                $quotation,
                $validated,
                $companyId,
                $storeId,
                $stockService
            ) {
                $document = Quotation::query()
                    ->with(['items.product'])
                    ->lockForUpdate()
                    ->findOrFail($quotation->id);

                $this->ensureQuotationContext($document, $companyId, $storeId);

                if (!$document->canBeConverted()) {
                    throw ValidationException::withMessages([
                        'quotation' =>
                        'This document is no longer available for conversion.',
                    ]);
                }

                $shortages = collect(
                    $this->quotationStockShortages(
                        $document,
                        $companyId,
                        $storeId
                    )
                )->keyBy('product_id');

                foreach ($validated['transfers'] as $index => $transfer) {
                    $productId = (int) $transfer['product_id'];
                    $shortage = $shortages->get($productId);

                    if (!$shortage) {
                        throw ValidationException::withMessages([
                            "transfers.{$index}.product_id" =>
                            'This product does not currently require a transfer.',
                        ]);
                    }

                    $product = Product::query()
                        ->where('company_id', $companyId)
                        ->where('store_id', $storeId)
                        ->where('status', true)
                        ->lockForUpdate()
                        ->find($productId);

                    $godown = Godown::query()
                        ->where('company_id', $companyId)
                        ->where('store_id', $storeId)
                        ->where('status', true)
                        ->lockForUpdate()
                        ->find((int) $transfer['godown_id']);

                    if (!$product || !$godown) {
                        throw ValidationException::withMessages([
                            "transfers.{$index}.godown_id" =>
                            'The selected product or Godown is unavailable.',
                        ]);
                    }

                    $quantity = round((float) $transfer['quantity'], 3);
                    $freshShortage = round(
                        max(
                            0,
                            (float) $shortage['required_quantity']
                                - $this->currentStock(
                                    $companyId,
                                    $storeId,
                                    null,
                                    $productId
                                )
                        ),
                        2
                    );
                    $godownStock = round(
                        $this->currentStock(
                            $companyId,
                            $storeId,
                            $godown->id,
                            $productId
                        ),
                        2
                    );

                    if (($quantity - $freshShortage) > 0.001) {
                        throw ValidationException::withMessages([
                            "transfers.{$index}.quantity" =>
                            'Transfer quantity exceeds the current Shop shortage of '
                                . $this->formatStockQuantity($freshShortage)
                                . '.',
                        ]);
                    }

                    if (($quantity - $godownStock) > 0.001) {
                        throw ValidationException::withMessages([
                            "transfers.{$index}.quantity" =>
                            'Transfer quantity exceeds the selected Godown stock of '
                                . $this->formatStockQuantity($godownStock)
                                . '.',
                        ]);
                    }

                    $referenceNo = sprintf(
                        'QTR-%d-%d-%d-%s',
                        $document->id,
                        $product->id,
                        $godown->id,
                        now()->format('Hisv')
                    );
                    $remarks = 'User transfer for '
                        . $document->quotation_no
                        . ' from '
                        . $godown->godown_name;

                    $stockService->decreaseStock(
                        $product,
                        $quantity,
                        'QUICK_TRANSFER_OUT',
                        $companyId,
                        $storeId,
                        $godown->id,
                        $document->id,
                        $referenceNo,
                        0,
                        $remarks
                    );

                    $stockService->increaseStock(
                        $product,
                        $quantity,
                        'QUICK_TRANSFER_IN',
                        $companyId,
                        $storeId,
                        null,
                        $document->id,
                        $referenceNo,
                        0,
                        $remarks
                    );
                }
            });
        } catch (ValidationException $exception) {
            return response()->json([
                'success' => false,
                'message' => collect($exception->errors())
                    ->flatten()
                    ->first()
                    ?? 'The stock transfer information is invalid.',
                'errors' => $exception->errors(),
            ], 422);
        } catch (Throwable $exception) {
            report($exception);

            $message = trim($exception->getMessage());
            $isStockError = str_contains(
                strtolower($message),
                'insufficient stock'
            );

            return response()->json([
                'success' => false,
                'message' => $isStockError
                    ? $message
                    : 'Unable to transfer stock. Please try again.',
                'errors' => $isStockError
                    ? ['stock' => [$message]]
                    : [],
            ], $isStockError ? 422 : 500);
        }

        $quotation->load(['items.product']);
        $remaining = $this->quotationStockShortages(
            $quotation,
            $companyId,
            $storeId
        );

        return response()->json([
            'success' => true,
            'ready' => count($remaining) === 0,
            'message' => count($remaining) === 0
                ? 'All required stock is now available in Shop.'
                : 'Selected stock was transferred. Some shortages remain.',
            'shortages' => $remaining,
        ]);
    }

    public function convertToSale(
        Request $request,
        Quotation $quotation,
        StockService $stockService
    ): RedirectResponse {
        [$companyId, $storeId] = $this->context($request);
        $this->ensureQuotationContext($quotation, $companyId, $storeId);

        $validated = $request->validate([
            'invoice_date' => ['nullable', 'date'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'payments' => ['nullable', 'array'],
            'payments.*.account_id' => [
                'required',
                'integer',
                'exists:accounts,id',
            ],
            'payments.*.payment_mode' => [
                'required',
                Rule::in([
                    'cash',
                    'gpay',
                    'phonepe',
                    'upi',
                    'bank_transfer',
                    'card',
                    'cheque',
                ]),
            ],
            'payments.*.amount' => [
                'required',
                'numeric',
                'gt:0',
            ],
            'payments.*.reference_no' => [
                'nullable',
                'string',
                'max:150',
            ],
            'remarks' => ['nullable', 'string', 'max:2000'],
        ]);

        $userId = (int) $request->user()->id;

        try {
            $sale = DB::transaction(function () use (
                $quotation,
                $validated,
                $companyId,
                $storeId,
                $stockService,
                $userId
            ) {
                $document = Quotation::query()
                    ->with(['items.product'])
                    ->lockForUpdate()
                    ->findOrFail($quotation->id);

                $this->ensureQuotationContext($document, $companyId, $storeId);

                if (!$document->canBeConverted()) {
                    throw ValidationException::withMessages([
                        'quotation' =>
                        'Only an approved, unexpired and unconverted document can be converted.',
                    ]);
                }

                if ($document->items->isEmpty()) {
                    throw ValidationException::withMessages([
                        'items' => 'The document does not contain any products.',
                    ]);
                }

                $this->validateCustomer(
                    (int) $document->customer_id,
                    $companyId,
                    $storeId
                );

                $lines = $document->items->map(function (QuotationItem $item) use (
                    $companyId,
                    $storeId,
                    $document
                ) {
                    $product = Product::query()
                        ->where('company_id', $companyId)
                        ->where('store_id', $storeId)
                        ->where('status', true)
                        ->find($item->product_id);

                    if (!$product) {
                        throw ValidationException::withMessages([
                            'items' => 'A quoted product is no longer available in this branch.',
                        ]);
                    }

                    $quantity = round((float) $item->quantity, 3);
                    $freeQuantity = round((float) $item->free_quantity, 3);
                    $gross = round($quantity * (float) $item->sale_price, 2);
                    $discount = $item->isPercentageDiscount()
                        ? round($gross * (float) $item->discount_value / 100, 2)
                        : round((float) $item->discount_value, 2);
                    $discount = min($gross, $discount);
                    $taxable = round(max(0, $gross - $discount), 2);
                    $gstPercent = $document->isGstBill()
                        ? (float) $item->gst_percent
                        : 0;
                    $gst = round($taxable * $gstPercent / 100, 2);

                    return [
                        'product' => $product,
                        'quantity' => $quantity,
                        'free_quantity' => $freeQuantity,
                        'sale_price' => (float) $item->sale_price,
                        'discount_type' => $item->discount_type,
                        'discount_value' => (float) $item->discount_value,
                        'discount' => $discount,
                        'taxable_amount' => $taxable,
                        'gst_percent' => $gstPercent,
                        'gst_amount' => $gst,
                        'line_total' => round($taxable + $gst, 2),
                    ];
                })->all();

                $subTotal = round(collect($lines)->sum('taxable_amount'), 2);
                $gstTotal = round(collect($lines)->sum('gst_amount'), 2);
                $grossTotal = round($subTotal + $gstTotal, 2);
                $finalDiscount = round((float) $document->final_discount, 2);

                if ($finalDiscount > $grossTotal) {
                    throw ValidationException::withMessages([
                        'final_discount' => 'Final discount exceeds the invoice amount.',
                    ]);
                }

                $beforeRound = round($grossTotal - $finalDiscount, 2);
                $grandTotal = round($beforeRound);
                $roundOff = round($grandTotal - $beforeRound, 2);
                $paymentLines = $this->buildPaymentLines(
                    $validated,
                    $companyId,
                    $storeId
                );

                $paidAmount = count($paymentLines) > 0
                    ? round(collect($paymentLines)->sum('amount'), 2)
                    : round((float) ($validated['paid_amount'] ?? 0), 2);

                if ($paidAmount > $grandTotal) {
                    throw ValidationException::withMessages([
                        'paid_amount' => 'Paid amount cannot exceed the invoice amount.',
                    ]);
                }

                $balanceAmount = round($grandTotal - $paidAmount, 2);
                $paymentStatus = $this->paymentStatus(
                    $paidAmount,
                    $balanceAmount
                );

                $commission = $this->conversionCommission(
                    $document,
                    $companyId,
                    $storeId,
                    $subTotal
                );

                $stockShortages = $this->stockShortagesFromLines(
                    $lines,
                    $companyId,
                    $storeId
                );

                if ($stockShortages !== []) {
                    throw ValidationException::withMessages([
                        'stock' =>
                        'Shop stock is insufficient. Open the stock transfer window and choose the Godown stock to transfer.',
                    ]);
                }

                $sale = Sales::create([
                    'company_id' => $companyId,
                    'store_id' => $storeId,
                    'quotation_id' => $document->id,
                    'customer_id' => $document->customer_id,
                    ...$commission,
                    'commission_status' => 'Pending',
                    'invoice_no' => $this->generateInvoiceNo($storeId),
                    'invoice_date' => $validated['invoice_date'] ?? today(),
                    'billing_type' => $document->billing_type,
                    'sub_total' => $subTotal,
                    'discount' => 0,
                    'gst_total' => $gstTotal,
                    'final_discount' => $finalDiscount,
                    'round_off' => $roundOff,
                    'grand_total' => $grandTotal,
                    'paid_amount' => $paidAmount,
                    'balance_amount' => $balanceAmount,
                    'payment_status' => $paymentStatus,
                    'remarks' => $validated['remarks']
                        ?? 'Converted from ' . $document->quotation_no,
                    'status' => 'Completed',
                ]);

                foreach ($lines as $line) {
                    $stockQuantity = round(
                        $line['quantity'] + $line['free_quantity'],
                        3
                    );

                    $stockSnapshot = $stockService->decreaseStock(
                        $line['product'],
                        $stockQuantity,
                        'SALE',
                        $companyId,
                        $storeId,
                        null,
                        $sale->id,
                        $sale->invoice_no,
                        0,
                        'Converted from ' . $document->quotation_no
                    );

                    $costTotal = round(
                        (float) $stockSnapshot['cost_total'],
                        2
                    );

                    SaleItems::create([
                        'sale_id' => $sale->id,
                        'product_id' => $line['product']->id,
                        'godown_id' => null,
                        'quantity' => $line['quantity'],
                        'free_quantity' => $line['free_quantity'],
                        'sale_price' => $line['sale_price'],
                        'unit_cost' => $stockSnapshot['unit_cost'],
                        'cost_total' => $costTotal,
                        'cost_source' => $stockSnapshot['cost_source'],
                        'discount_type' => $line['discount_type'],
                        'discount_value' => $line['discount_value'],
                        'discount' => $line['discount'],
                        'gst_percent' => $line['gst_percent'],
                        'gst_amount' => $line['gst_amount'],
                        'line_total' => $line['line_total'],
                        'net_sale_amount' => $line['taxable_amount'],
                        'gross_profit' => round(
                            $line['taxable_amount'] - $costTotal,
                            2
                        ),
                    ]);
                }

                $this->recordSalePayments(
                    $sale,
                    $paymentLines,
                    $paidAmount,
                    $companyId,
                    $storeId,
                    $userId,
                    $document->quotation_no
                );

                if (
                    $commission['sale_source'] === 'mechanic'
                    && $commission['mechanic_id']
                    && $commission['commission_amount'] > 0
                ) {
                    MechanicCommission::create([
                        'company_id' => $companyId,
                        'store_id' => $storeId,
                        'mechanic_id' => $commission['mechanic_id'],
                        'sale_id' => $sale->id,
                        'commission_type' => $commission['commission_type'],
                        'commission_percent' =>
                        $commission['commission_type'] === 'percentage'
                            ? $commission['commission_percent']
                            : null,
                        'commission_base' => $subTotal,
                        'commission_amount' => $commission['commission_amount'],
                        'paid_amount' => 0,
                        'balance_amount' => $commission['commission_amount'],
                        'status' => 'Pending',
                        'remarks' => 'Commission generated from sale '
                            . $sale->invoice_no,
                    ]);
                }

                $document->update([
                    'status' => 'Converted',
                    'converted_at' => now(),
                ]);

                return $sale;
            });
        } catch (ValidationException $exception) {
            throw $exception;
        } catch (Throwable $exception) {
            $message = trim($exception->getMessage());

            if (str_contains($message, 'Insufficient stock')) {
                return back()
                    ->withErrors([
                        'stock' => $message,
                    ])
                    ->with(
                        'error',
                        $message
                    );
            }

            throw $exception;
        }

        return AppResponse::successRoute(
            'sales.show',
            'Quotation converted to Sale successfully.',
            $sale
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Print And PDF
    |--------------------------------------------------------------------------
    */

    public function print(
        Request $request,
        Quotation $quotation
    ) {
        $this->ensureQuotationContext($quotation, ...$this->context($request));
        $this->loadDocument($quotation);

        return view('quotations.print', compact('quotation'));
    }

    public function pdf(
        Request $request,
        Quotation $quotation
    ) {
        $this->ensureQuotationContext($quotation, ...$this->context($request));

        $paper = $request->validate([
            'paper' => ['nullable', Rule::in(['a4', 'a5'])],
        ])['paper'] ?? 'a4';

        $this->loadDocument($quotation);

        return Pdf::loadView(
            'quotations.pdf',
            compact('quotation', 'paper')
        )
            ->setPaper($paper, 'portrait')
            ->download(
                strtolower(str_replace(' ', '-', $quotation->documentTitle()))
                    . '-'
                    . $quotation->quotation_no
                    . '-'
                    . strtoupper($paper)
                    . '.pdf'
            );
    }

    /*
    |--------------------------------------------------------------------------
    | Validation And Calculations
    |--------------------------------------------------------------------------
    */

    private function validateQuotationRequest(Request $request): array
    {
        return $request->validate([
            'customer_id' => ['required', 'integer', 'exists:customers,id'],
            'document_type' => [
                'required',
                Rule::in(['quotation', 'proforma_invoice']),
            ],
            'sale_source' => [
                'required',
                Rule::in(['customer', 'mechanic']),
            ],
            'mechanic_id' => ['nullable', 'integer', 'exists:mechanics,id'],
            'commission_type' => [
                'nullable',
                Rule::in(['percentage', 'fixed']),
            ],
            'commission_percent' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'commission_amount' => ['nullable', 'numeric', 'min:0'],
            'quotation_date' => ['required', 'date'],
            'valid_until' => ['nullable', 'date', 'after_or_equal:quotation_date'],
            'billing_type' => ['required', Rule::in(['gst', 'non_gst'])],
            'final_discount' => ['nullable', 'numeric', 'min:0'],
            'terms_conditions' => ['nullable', 'string', 'max:5000'],
            'remarks' => ['nullable', 'string', 'max:2000'],
            'status' => [
                'required',
                Rule::in(['Draft', 'Pending', 'Approved']),
            ],
            'product_id' => ['required', 'array', 'min:1'],
            'product_id.*' => ['required', 'integer', 'exists:products,id'],
            'qty' => ['required', 'array', 'min:1'],
            'qty.*' => ['required', 'numeric', 'gt:0'],
            'free_qty' => ['nullable', 'array'],
            'free_qty.*' => ['nullable', 'numeric', 'min:0'],
            'sale_price' => ['required', 'array', 'min:1'],
            'sale_price.*' => ['required', 'numeric', 'min:0'],
            'discount_type' => ['required', 'array', 'min:1'],
            'discount_type.*' => [
                'required',
                Rule::in(['percentage', 'fixed']),
            ],
            'discount_value' => ['required', 'array', 'min:1'],
            'discount_value.*' => ['required', 'numeric', 'min:0'],
            'gst_percent' => ['required', 'array', 'min:1'],
            'gst_percent.*' => ['required', 'numeric', 'min:0', 'max:100'],
        ]);
    }

    private function buildLines(
        array $validated,
        int $companyId,
        int $storeId
    ): array {
        $lines = [];
        $billingType = $validated['billing_type'];

        foreach ($validated['product_id'] as $index => $productId) {
            foreach (
                [
                    'qty',
                    'sale_price',
                    'discount_type',
                    'discount_value',
                    'gst_percent',
                ] as $field
            ) {
                if (!array_key_exists($index, $validated[$field] ?? [])) {
                    throw ValidationException::withMessages([
                        "{$field}.{$index}" =>
                        'Product row data is incomplete.',
                    ]);
                }
            }

            $product = Product::query()
                ->where('company_id', $companyId)
                ->where('store_id', $storeId)
                ->where('status', true)
                ->find($productId);

            if (!$product) {
                throw ValidationException::withMessages([
                    "product_id.{$index}" =>
                    'Selected product is not available in this branch.',
                ]);
            }

            $quantity = round((float) $validated['qty'][$index], 3);
            $freeQuantity = round(
                (float) ($validated['free_qty'][$index] ?? 0),
                3
            );
            $salePrice = round((float) $validated['sale_price'][$index], 2);
            $discountType = $validated['discount_type'][$index];
            $discountValue = round(
                (float) $validated['discount_value'][$index],
                2
            );
            $gstPercent = $billingType === 'gst'
                ? round((float) $validated['gst_percent'][$index], 2)
                : 0;
            $grossAmount = round($quantity * $salePrice, 2);

            if ($discountType === 'percentage' && $discountValue > 100) {
                throw ValidationException::withMessages([
                    "discount_value.{$index}" =>
                    'Percentage discount cannot exceed 100%.',
                ]);
            }

            $discountAmount = $discountType === 'percentage'
                ? round($grossAmount * $discountValue / 100, 2)
                : $discountValue;

            if ($discountAmount > $grossAmount) {
                throw ValidationException::withMessages([
                    "discount_value.{$index}" =>
                    'Item discount cannot exceed the item amount.',
                ]);
            }

            $taxableAmount = round($grossAmount - $discountAmount, 2);
            $gstAmount = round($taxableAmount * $gstPercent / 100, 2);

            $lines[] = [
                'product' => $product,
                'quantity' => $quantity,
                'free_quantity' => $freeQuantity,
                'sale_price' => $salePrice,
                'discount_type' => $discountType,
                'discount_value' => $discountValue,
                'discount_amount' => $discountAmount,
                'taxable_amount' => $taxableAmount,
                'gst_percent' => $gstPercent,
                'gst_amount' => $gstAmount,
                'line_total' => round($taxableAmount + $gstAmount, 2),
            ];
        }

        return $lines;
    }

    private function calculateTotals(
        array $lines,
        array $validated
    ): array {
        $subTotal = round(collect($lines)->sum('taxable_amount'), 2);
        $gstTotal = round(collect($lines)->sum('gst_amount'), 2);
        $grossTotal = round($subTotal + $gstTotal, 2);
        $finalDiscount = round(
            (float) ($validated['final_discount'] ?? 0),
            2
        );

        if ($finalDiscount > $grossTotal) {
            throw ValidationException::withMessages([
                'final_discount' =>
                'Final discount cannot exceed the document amount.',
            ]);
        }

        $beforeRound = round($grossTotal - $finalDiscount, 2);
        $grandTotal = round($beforeRound);

        return [
            'sub_total' => $subTotal,
            'gst_total' => $gstTotal,
            'final_discount' => $finalDiscount,
            'round_off' => round($grandTotal - $beforeRound, 2),
            'grand_total' => round($grandTotal, 2),
        ];
    }

    private function saveLines(
        Quotation $quotation,
        array $lines
    ): void {
        foreach ($lines as $line) {
            QuotationItem::create([
                'quotation_id' => $quotation->id,
                'product_id' => $line['product']->id,
                'quantity' => $line['quantity'],
                'free_quantity' => $line['free_quantity'],
                'sale_price' => $line['sale_price'],
                'discount_type' => $line['discount_type'],
                'discount_value' => $line['discount_value'],
                'discount_amount' => $line['discount_amount'],
                'taxable_amount' => $line['taxable_amount'],
                'gst_percent' => $line['gst_percent'],
                'gst_amount' => $line['gst_amount'],
                'line_total' => $line['line_total'],
            ]);
        }
    }

    private function quotationCommission(
        array $validated,
        int $companyId,
        int $storeId,
        float $commissionBase
    ): array {
        if ($validated['sale_source'] !== 'mechanic') {
            return [
                'sale_source' => 'customer',
                'mechanic_id' => null,
                'commission_type' => null,
                'commission_percent' => null,
                'commission_amount' => 0,
            ];
        }

        $mechanic = $this->validateMechanic(
            (int) ($validated['mechanic_id'] ?? 0),
            $companyId,
            $storeId
        );
        $type = $validated['commission_type'] ?? null;

        if (!in_array($type, ['percentage', 'fixed'], true)) {
            throw ValidationException::withMessages([
                'commission_type' => 'Select a commission type.',
            ]);
        }

        if ($type === 'percentage') {
            $percent = round(
                (float) ($validated['commission_percent'] ?? 0),
                2
            );

            if ($percent <= 0 || $percent > 100) {
                throw ValidationException::withMessages([
                    'commission_percent' =>
                    'Commission percentage must be between 0 and 100.',
                ]);
            }

            return [
                'sale_source' => 'mechanic',
                'mechanic_id' => $mechanic->id,
                'commission_type' => 'percentage',
                'commission_percent' => $percent,
                'commission_amount' => round(
                    $commissionBase * $percent / 100,
                    2
                ),
            ];
        }

        $amount = round(
            (float) ($validated['commission_amount'] ?? 0),
            2
        );

        if ($amount > $commissionBase) {
            throw ValidationException::withMessages([
                'commission_amount' =>
                'Fixed commission cannot exceed the taxable amount.',
            ]);
        }

        return [
            'sale_source' => 'mechanic',
            'mechanic_id' => $mechanic->id,
            'commission_type' => 'fixed',
            'commission_percent' => null,
            'commission_amount' => $amount,
        ];
    }

    private function conversionCommission(
        Quotation $quotation,
        int $companyId,
        int $storeId,
        float $commissionBase
    ): array {
        if (!$quotation->isMechanicQuotation()) {
            return [
                'sale_source' => 'customer',
                'mechanic_id' => null,
                'commission_type' => null,
                'commission_percent' => null,
                'commission_amount' => 0,
            ];
        }

        $mechanic = $this->validateMechanic(
            (int) $quotation->mechanic_id,
            $companyId,
            $storeId
        );

        $amount = $quotation->commission_type === 'percentage'
            ? round(
                $commissionBase
                    * (float) $quotation->commission_percent
                    / 100,
                2
            )
            : round((float) $quotation->commission_amount, 2);

        return [
            'sale_source' => 'mechanic',
            'mechanic_id' => $mechanic->id,
            'commission_type' => $quotation->commission_type,
            'commission_percent' =>
            $quotation->commission_type === 'percentage'
                ? (float) $quotation->commission_percent
                : null,
            'commission_amount' => $amount,
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | User-Controlled Stock Transfer Helpers
    |--------------------------------------------------------------------------
    */

    private function quotationStockShortages(
        Quotation $quotation,
        int $companyId,
        int $storeId
    ): array {
        $lines = $quotation->items->map(function (QuotationItem $item) {
            return [
                'product' => $item->product,
                'quantity' => (float) $item->quantity,
                'free_quantity' => (float) $item->free_quantity,
            ];
        })->all();

        return $this->stockShortagesFromLines(
            $lines,
            $companyId,
            $storeId
        );
    }

    private function stockShortagesFromLines(
        array $lines,
        int $companyId,
        int $storeId
    ): array {
        $requirements = [];

        foreach ($lines as $line) {
            /** @var Product $product */
            $product = $line['product'];
            $productId = (int) $product->id;
            $required = round(
                (float) $line['quantity']
                    + (float) $line['free_quantity'],
                2
            );

            if (!isset($requirements[$productId])) {
                $requirements[$productId] = [
                    'product' => $product,
                    'required_quantity' => 0,
                ];
            }

            $requirements[$productId]['required_quantity'] = round(
                $requirements[$productId]['required_quantity'] + $required,
                2
            );
        }

        $godowns = Godown::query()
            ->where('company_id', $companyId)
            ->where('store_id', $storeId)
            ->where('status', true)
            ->orderBy('godown_name')
            ->get([
                'id',
                'godown_name',
            ]);
        $shortages = [];

        foreach ($requirements as $requirement) {
            /** @var Product $product */
            $product = $requirement['product'];
            $required = (float) $requirement['required_quantity'];
            $shopStock = round(
                $this->currentStock(
                    $companyId,
                    $storeId,
                    null,
                    $product->id
                ),
                2
            );
            $shortage = round(max(0, $required - $shopStock), 2);

            if ($shortage < 0.01) {
                continue;
            }

            $availableGodowns = $godowns
                ->map(function (Godown $godown) use (
                    $companyId,
                    $storeId,
                    $product
                ) {
                    return [
                        'id' => $godown->id,
                        'godown_name' => $godown->godown_name,
                        'stock' => round(
                            $this->currentStock(
                                $companyId,
                                $storeId,
                                $godown->id,
                                $product->id
                            ),
                            2
                        ),
                    ];
                })
                ->filter(fn(array $godown) => $godown['stock'] > 0)
                ->sortByDesc('stock')
                ->values()
                ->all();
            $godownStock = round(
                (float) collect($availableGodowns)->sum('stock'),
                2
            );

            $shortages[] = [
                'product_id' => $product->id,
                'product_code' => $product->product_code,
                'product_name' => $product->product_name,
                'part_number' => $product->part_number,
                'required_quantity' => $required,
                'shop_stock' => $shopStock,
                'shortage_quantity' => $shortage,
                'godown_stock' => $godownStock,
                'total_stock' => round($shopStock + $godownStock, 2),
                'can_fulfil' => round(
                    $shopStock + $godownStock,
                    2
                ) >= round($required, 2),
                'godowns' => $availableGodowns,
            ];
        }

        return $shortages;
    }

    /*
    |--------------------------------------------------------------------------
    | Legacy Automatic Transfer Helper (Not Used)
    |--------------------------------------------------------------------------
    |
    | Retained temporarily for backward code comparison. Conversion never calls
    | this method; all Godown transfers require explicit user submission.
    |
    */

    private function prepareShopStock(
        array $lines,
        Quotation $quotation,
        int $companyId,
        int $storeId,
        StockService $stockService
    ): void {
        $requirements = [];

        /*
        | A quotation should normally contain each product once. Grouping here
        | also protects conversion if an older document has duplicate rows.
        */

        foreach ($lines as $line) {
            $productId = (int) $line['product']->id;
            $requiredQuantity = round(
                (float) $line['quantity']
                    + (float) $line['free_quantity'],
                3
            );

            if (!isset($requirements[$productId])) {
                $requirements[$productId] = [
                    'product' => $line['product'],
                    'quantity' => 0,
                    'unit_cost' => (float) $line['sale_price'],
                ];
            }

            $requirements[$productId]['quantity'] = round(
                $requirements[$productId]['quantity'] + $requiredQuantity,
                3
            );
        }

        $godowns = Godown::query()
            ->where('company_id', $companyId)
            ->where('store_id', $storeId)
            ->where('status', true)
            ->orderBy('godown_name')
            ->lockForUpdate()
            ->get([
                'id',
                'godown_name',
            ]);

        foreach ($requirements as $requirement) {
            /** @var Product $product */
            $product = $requirement['product'];
            $requiredQuantity = (float) $requirement['quantity'];
            $shopStock = $this->currentStock(
                $companyId,
                $storeId,
                null,
                $product->id
            );
            $shortage = round(
                max(0, $requiredQuantity - $shopStock),
                3
            );

            if ($shortage <= 0) {
                continue;
            }

            $godownBalances = $godowns
                ->map(function (Godown $godown) use (
                    $companyId,
                    $storeId,
                    $product
                ) {
                    return [
                        'godown' => $godown,
                        'stock' => $this->currentStock(
                            $companyId,
                            $storeId,
                            $godown->id,
                            $product->id
                        ),
                    ];
                })
                ->filter(
                    fn(array $balance) => $balance['stock'] > 0
                )
                ->sortByDesc('stock')
                ->values();

            $godownStock = round(
                (float) $godownBalances->sum('stock'),
                3
            );
            $totalAvailable = round($shopStock + $godownStock, 3);

            if ($totalAvailable < $requiredQuantity) {
                throw ValidationException::withMessages([
                    'stock' => sprintf(
                        'Insufficient stock for %s. Shop: %s, Godown: %s, Required: %s.',
                        $product->product_name ?: $product->product_code,
                        $this->formatStockQuantity($shopStock),
                        $this->formatStockQuantity($godownStock),
                        $this->formatStockQuantity($requiredQuantity)
                    ),
                ]);
            }

            $remaining = $shortage;

            foreach ($godownBalances as $balance) {
                if ($remaining <= 0) {
                    break;
                }

                /** @var Godown $godown */
                $godown = $balance['godown'];
                $transferQuantity = round(
                    min($remaining, (float) $balance['stock']),
                    3
                );

                if ($transferQuantity <= 0) {
                    continue;
                }

                $referenceNo = sprintf(
                    'QTN-AUTO-%d-%d-%d',
                    $quotation->id,
                    $product->id,
                    $godown->id
                );
                $remarks = 'Automatic transfer for '
                    . $quotation->quotation_no
                    . ' from '
                    . $godown->godown_name;

                $stockService->decreaseStock(
                    $product,
                    $transferQuantity,
                    'QUICK_TRANSFER_OUT',
                    $companyId,
                    $storeId,
                    $godown->id,
                    $quotation->id,
                    $referenceNo,
                    $requirement['unit_cost'],
                    $remarks
                );

                $stockService->increaseStock(
                    $product,
                    $transferQuantity,
                    'QUICK_TRANSFER_IN',
                    $companyId,
                    $storeId,
                    null,
                    $quotation->id,
                    $referenceNo,
                    $requirement['unit_cost'],
                    $remarks
                );

                $remaining = round(
                    $remaining - $transferQuantity,
                    3
                );
            }

            if ($remaining > 0) {
                throw ValidationException::withMessages([
                    'stock' => 'Unable to transfer sufficient Godown stock for '
                        . ($product->product_name ?: $product->product_code)
                        . '.',
                ]);
            }
        }
    }

    private function currentStock(
        int $companyId,
        int $storeId,
        ?int $godownId,
        int $productId
    ): float {
        $balance = StockLedger::query()
            ->where('company_id', $companyId)
            ->where('store_id', $storeId)
            ->where('product_id', $productId)
            ->when(
                $godownId === null,
                fn(Builder $query) => $query->whereNull('godown_id'),
                fn(Builder $query) => $query->where(
                    'godown_id',
                    $godownId
                )
            )
            ->latest('id')
            ->value('balance_qty');

        return round((float) ($balance ?? 0), 3);
    }

    private function formatStockQuantity(float $quantity): string
    {
        return number_format($quantity, 3, '.', '');
    }

    /*
    |--------------------------------------------------------------------------
    | Context And Lookup Helpers
    |--------------------------------------------------------------------------
    */

    private function context(Request $request): array
    {
        $user = $request->user();

        abort_unless($user && (bool) $user->status, 403);

        $companyId = (int) $request->session()->get('active_company_id');
        $storeId = (int) $request->session()->get('active_store_id');

        abort_unless(
            $companyId > 0 && $storeId > 0,
            403,
            'No active business branch selected.'
        );

        $store = Store::query()
            ->whereKey($storeId)
            ->where('company_id', $companyId)
            ->where('status', true)
            ->first();

        abort_unless($store, 403, 'The active branch is unavailable.');

        if (!$user->is_super_user) {
            abort_unless((int) $user->company_id === $companyId, 403);

            if (!$user->hasRole('Company Admin')) {
                $assigned = $user->stores()
                    ->where('stores.id', $storeId)
                    ->where('stores.status', true)
                    ->exists();

                abort_unless($assigned, 403);
            }
        }

        return [$companyId, $storeId];
    }

    private function ensureQuotationContext(
        Quotation $quotation,
        int $companyId,
        int $storeId
    ): void {
        abort_unless(
            (int) $quotation->company_id === $companyId
                && (int) $quotation->store_id === $storeId,
            404
        );
    }

    private function validateCustomer(
        int $customerId,
        int $companyId,
        int $storeId
    ): Customer {
        $customer = Customer::query()
            ->where('company_id', $companyId)
            ->where('status', true)
            ->whereHas(
                'stores',
                fn(Builder $query) => $query->where('stores.id', $storeId)
            )
            ->find($customerId);

        if (!$customer) {
            throw ValidationException::withMessages([
                'customer_id' =>
                'Selected customer is not available in this branch.',
            ]);
        }

        return $customer;
    }

    private function validateMechanic(
        int $mechanicId,
        int $companyId,
        int $storeId
    ): Mechanic {
        $mechanic = Mechanic::query()
            ->where('company_id', $companyId)
            ->where('status', true)
            ->whereHas(
                'stores',
                fn(Builder $query) => $query->where('stores.id', $storeId)
            )
            ->find($mechanicId);

        if (!$mechanic) {
            throw ValidationException::withMessages([
                'mechanic_id' =>
                'Selected mechanic is not available in this branch.',
            ]);
        }

        return $mechanic;
    }

    private function loadDocument(Quotation $quotation): void
    {
        $quotation->load([
            'company',
            'store',
            'customer',
            'mechanic',
            'creator:id,name',
            'items.product.unit',
        ]);
    }

    private function applyDateFilter(
        Builder $query,
        array $filters
    ): void {
        if ($filters['date_from'] || $filters['date_to']) {
            $query
                ->when(
                    $filters['date_from'],
                    fn(Builder $query) => $query->whereDate(
                        'quotation_date',
                        '>=',
                        $filters['date_from']
                    )
                )
                ->when(
                    $filters['date_to'],
                    fn(Builder $query) => $query->whereDate(
                        'quotation_date',
                        '<=',
                        $filters['date_to']
                    )
                );

            return;
        }

        match ($filters['quick_filter']) {
            'today' => $query->whereDate('quotation_date', today()),
            'yesterday' => $query->whereDate(
                'quotation_date',
                today()->subDay()
            ),
            'this_week' => $query->whereBetween('quotation_date', [
                now()->startOfWeek()->toDateString(),
                now()->endOfWeek()->toDateString(),
            ]),
            'this_month' => $query
                ->whereYear('quotation_date', now()->year)
                ->whereMonth('quotation_date', now()->month),
            default => null,
        };
    }

    /*
    |--------------------------------------------------------------------------
    | Number And Response Helpers
    |--------------------------------------------------------------------------
    */

    private function previewDocumentNo(
        int $storeId,
        string $documentType
    ): string {
        $prefix = $documentType === 'proforma_invoice' ? 'PI' : 'QTN';
        $last = Quotation::query()
            ->where('store_id', $storeId)
            ->where('document_type', $documentType)
            ->where('quotation_no', 'like', $prefix . '%')
            ->latest('id')
            ->first();
        $number = $last
            ? (int) preg_replace('/\D/', '', (string) $last->quotation_no)
            : 0;

        return $prefix . str_pad(
            (string) ($number + 1),
            6,
            '0',
            STR_PAD_LEFT
        );
    }

    private function generateInvoiceNo(int $storeId): string
    {
        $lastSale = Sales::query()
            ->where('store_id', $storeId)
            ->latest('id')
            ->lockForUpdate()
            ->first();
        $number = $lastSale
            ? (int) preg_replace('/\D/', '', (string) $lastSale->invoice_no)
            : 0;

        return 'INV' . str_pad(
            (string) ($number + 1),
            6,
            '0',
            STR_PAD_LEFT
        );
    }

    private function generateCustomerCode(int $companyId): string
    {
        $last = Customer::query()
            ->where('company_id', $companyId)
            ->whereNotNull('customer_code')
            ->lockForUpdate()
            ->latest('id')
            ->first();
        $number = $last
            ? (int) preg_replace('/\D/', '', (string) $last->customer_code)
            : 0;

        return 'CUS' . str_pad(
            (string) ($number + 1),
            6,
            '0',
            STR_PAD_LEFT
        );
    }

    private function generateMechanicCode(int $companyId): string
    {
        $last = Mechanic::query()
            ->where('company_id', $companyId)
            ->whereNotNull('mechanic_code')
            ->lockForUpdate()
            ->latest('id')
            ->first();
        $number = $last
            ? (int) preg_replace('/\D/', '', (string) $last->mechanic_code)
            : 0;

        return 'MEC' . str_pad(
            (string) ($number + 1),
            6,
            '0',
            STR_PAD_LEFT
        );
    }

    private function paymentAccountsForStore(
        int $companyId,
        int $storeId
    ) {
        return Account::query()
            ->where('company_id', $companyId)
            ->where('store_id', $storeId)
            ->where('status', true)
            ->orderByRaw(
                "FIELD(account_type, 'cash', 'upi', 'bank', 'card', 'other')"
            )
            ->orderBy('account_name')
            ->get([
                'id',
                'account_code',
                'account_name',
                'account_type',
                'payment_mode',
            ]);
    }

    private function buildPaymentLines(
        array $validated,
        int $companyId,
        int $storeId
    ): array {
        $payments = $validated['payments'] ?? [];

        if (!is_array($payments) || count($payments) === 0) {
            return [];
        }

        $lines = [];

        foreach ($payments as $index => $payment) {
            $account = Account::query()
                ->where('company_id', $companyId)
                ->where('store_id', $storeId)
                ->where('status', true)
                ->find($payment['account_id']);

            if (!$account) {
                throw ValidationException::withMessages([
                    "payments.{$index}.account_id" =>
                    'The selected payment account is invalid for the active branch.',
                ]);
            }

            $lines[] = [
                'account_id' => $account->id,
                'payment_mode' => $payment['payment_mode'],
                'amount' => round((float) $payment['amount'], 2),
                'reference_no' => filled($payment['reference_no'] ?? null)
                    ? trim((string) $payment['reference_no'])
                    : null,
            ];
        }

        return $lines;
    }

    private function recordSalePayments(
        Sales $sale,
        array $paymentLines,
        float $paidAmount,
        int $companyId,
        int $storeId,
        int $userId,
        string $quotationNo
    ): void {
        if ($paidAmount <= 0) {
            return;
        }

        if (count($paymentLines) === 0) {
            SalePayment::create([
                'company_id' => $companyId,
                'store_id' => $storeId,
                'sale_id' => $sale->id,
                'account_id' => null,
                'payment_date' => $sale->invoice_date,
                'payment_mode' => 'legacy',
                'amount' => $paidAmount,
                'status' => SalePayment::STATUS_COMPLETED,
                'remarks' =>
                'Payment captured while converting quotation '
                    . $quotationNo
                    . '; payment account was not supplied.',
                'created_by' => $userId,
            ]);

            return;
        }

        foreach ($paymentLines as $line) {
            $payment = SalePayment::create([
                'company_id' => $companyId,
                'store_id' => $storeId,
                'sale_id' => $sale->id,
                'account_id' => $line['account_id'],
                'payment_date' => $sale->invoice_date,
                'payment_mode' => $line['payment_mode'],
                'amount' => $line['amount'],
                'reference_no' => $line['reference_no'],
                'status' => SalePayment::STATUS_COMPLETED,
                'remarks' => 'Payment received while converting quotation '
                    . $quotationNo
                    . '.',
                'created_by' => $userId,
            ]);

            AccountTransaction::create([
                'company_id' => $companyId,
                'store_id' => $storeId,
                'account_id' => $line['account_id'],
                'transaction_date' => $sale->invoice_date,
                'transaction_type' => AccountTransaction::TYPE_SALE,
                'reference_type' => SalePayment::class,
                'reference_id' => $payment->id,
                'reference_no' => $sale->invoice_no,
                'debit' => $line['amount'],
                'credit' => 0,
                'payment_mode' => $line['payment_mode'],
                'external_reference' => $line['reference_no'],
                'remarks' => 'Sales collection for '
                    . $sale->invoice_no
                    . ' converted from '
                    . $quotationNo,
                'created_by' => $userId,
            ]);
        }
    }

    private function paymentStatus(
        float $paidAmount,
        float $balanceAmount
    ): string {
        if ($balanceAmount <= 0) {
            return 'Paid';
        }

        return $paidAmount > 0
            ? 'Partially Paid'
            : 'Unpaid';
    }

    private function nullableTrim(mixed $value): ?string
    {
        return filled($value)
            ? trim((string) $value)
            : null;
    }

    private function customerJson(Customer $customer): array
    {
        return [
            'id' => $customer->id,
            'customer_code' => $customer->customer_code,
            'customer_name' => $customer->customer_name,
            'mobile' => $customer->mobile,
            'gst_number' => $customer->gst_number,
        ];
    }

    private function mechanicJson(Mechanic $mechanic): array
    {
        return [
            'id' => $mechanic->id,
            'mechanic_code' => $mechanic->mechanic_code,
            'mechanic_name' => $mechanic->mechanic_name,
            'mobile' => $mechanic->mobile,
        ];
    }
}
