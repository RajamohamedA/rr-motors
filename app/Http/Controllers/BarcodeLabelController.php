<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Picqer\Barcode\BarcodeGeneratorSVG;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

class BarcodeLabelController extends Controller implements HasMiddleware
{
    private const PAPER_A4 = 'a4';
    private const PAPER_A5 = 'a5';
    private const PAPER_ROLL_50X25 = 'roll_50x25';
    private const PAPER_ROLL_40X25 = 'roll_40x25';
    private const PAPER_SMALL_30X20 = 'small_30x20';

    private const PRICE_SELLING = 'selling_price';
    private const PRICE_MRP = 'mrp';
    private const PRICE_BOTH = 'both';
    private const PRICE_NONE = 'none';

    public static function middleware(): array
    {
        return [
            new Middleware(
                'permission:barcode-label.view',
                only: ['index', 'searchProducts']
            ),
            new Middleware(
                'permission:barcode-label.create',
                only: ['generateMissingBarcodes']
            ),
            new Middleware(
                'permission:barcode-label.print',
                only: ['preview', 'print', 'pdf']
            ),
        ];
    }

    public function index(Request $request): Response
    {
        [$companyId, $storeId] = $this->context($request);

        $requestedProductId = $request->integer('product_id');

        $initialProduct = $requestedProductId > 0
            ? Product::query()
            ->with(['category:id,name', 'brand:id,brand_name'])
            ->forContext($companyId, $storeId)
            ->active()
            ->whereKey($requestedProductId)
            ->first()
            : null;

        $products = Product::query()
            ->with(['category:id,name', 'brand:id,brand_name'])
            ->forContext($companyId, $storeId)
            ->active()
            ->when(
                $requestedProductId > 0,
                fn(Builder $query) => $query->whereKey($requestedProductId)
            )
            ->search($request->string('search')->toString())
            ->when(
                $request->boolean('without_barcode'),
                fn(Builder $query) => $query->withoutBarcode()
            )
            ->orderBy('product_name')
            ->paginate(25)
            ->withQueryString()
            ->through(fn(Product $product): array => $this->productPayload($product));

        return Inertia::render('BarcodeLabels/Index', [
            'products' => $products,
            'filters' => [
                'search' => $request->string('search')->toString(),
                'without_barcode' => $request->boolean('without_barcode'),
                'product_id' => $initialProduct?->id,
            ],
            'initial_product' => $initialProduct
                ? $this->productPayload($initialProduct)
                : null,
            'options' => [
                'papers' => $this->paperOptions(),
                'prices' => $this->priceOptions(),
            ],
        ]);
    }

    public function searchProducts(Request $request): JsonResponse
    {
        [$companyId, $storeId] = $this->context($request);

        $validated = $request->validate([
            'search' => ['nullable', 'string', 'max:150'],
            'limit' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        $products = Product::query()
            ->with(['category:id,name', 'brand:id,brand_name'])
            ->forContext($companyId, $storeId)
            ->active()
            ->search($validated['search'] ?? null)
            ->orderBy('product_name')
            ->limit((int) ($validated['limit'] ?? 20))
            ->get()
            ->map(fn(Product $product): array => $this->productPayload($product));

        return response()->json([
            'data' => $products,
        ]);
    }

    public function generateMissingBarcodes(Request $request): RedirectResponse
    {
        [$companyId, $storeId] = $this->context($request);

        $validated = $request->validate([
            'product_ids' => ['required', 'array', 'min:1', 'max:500'],
            'product_ids.*' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('products', 'id')->where(
                    fn($query) => $query
                        ->where('company_id', $companyId)
                        ->where('store_id', $storeId)
                ),
            ],
        ]);

        $generated = DB::transaction(function () use (
            $validated,
            $companyId,
            $storeId
        ): int {
            $products = Product::query()
                ->forContext($companyId, $storeId)
                ->whereIn('id', $validated['product_ids'])
                ->lockForUpdate()
                ->get();

            $count = 0;

            foreach ($products as $product) {
                if (!$product->hasBarcode()) {
                    $product->ensureBarcode();
                    $count++;
                }
            }

            return $count;
        });

        return back()->with(
            'success',
            $generated > 0
                ? "{$generated} product barcode(s) generated successfully."
                : 'All selected products already have barcodes.'
        );
    }

    public function preview(Request $request): HttpResponse
    {
        $data = $this->buildLabelDocument($request);

        return response()->view('barcode_labels.print', [
            ...$data,
            'pdfMode' => false,
            'autoPrint' => false,
        ]);
    }

    public function print(Request $request): HttpResponse
    {
        $data = $this->buildLabelDocument($request);

        return response()->view('barcode_labels.print', [
            ...$data,
            'pdfMode' => false,
            'autoPrint' => true,
        ]);
    }

    public function pdf(Request $request): HttpResponse
    {
        $data = $this->buildLabelDocument($request);
        $paper = $data['paper'];

        $pdf = Pdf::loadView('barcode_labels.print', [
            ...$data,
            'pdfMode' => true,
            'autoPrint' => false,
        ]);

        if ($paper === self::PAPER_A5) {
            $pdf->setPaper('a5', 'portrait');
        } elseif ($paper === self::PAPER_ROLL_50X25) {
            $pdf->setPaper([0, 0, 141.73, 70.87]);
        } elseif ($paper === self::PAPER_ROLL_40X25) {
            $pdf->setPaper([0, 0, 113.39, 70.87]);
        } elseif ($paper === self::PAPER_SMALL_30X20) {
            $pdf->setPaper([0, 0, 85.04, 56.69]);
        } else {
            $pdf->setPaper('a4', 'portrait');
        }

        return $pdf->stream(
            'Barcode_Labels_' . now()->format('Ymd_His') . '.pdf'
        );
    }

    private function buildLabelDocument(Request $request): array
    {
        [$companyId, $storeId] = $this->context($request);

        $validated = $request->validate([
            'items' => ['required', 'array', 'min:1', 'max:250'],
            'items.*.product_id' => [
                'required',
                'integer',
                'distinct',
                Rule::exists('products', 'id')->where(
                    fn($query) => $query
                        ->where('company_id', $companyId)
                        ->where('store_id', $storeId)
                ),
            ],
            'items.*.quantity' => ['required', 'integer', 'min:1', 'max:500'],
            'paper' => [
                'required',
                Rule::in(array_keys($this->paperOptions())),
            ],
            'price_display' => [
                'required',
                Rule::in(array_keys($this->priceOptions())),
            ],
            'show_branch' => ['nullable', 'boolean'],
            'show_product_code' => ['nullable', 'boolean'],
            'show_part_number' => ['nullable', 'boolean'],
        ]);

        $requestedItems = collect($validated['items'])
            ->keyBy(fn(array $item): int => (int) $item['product_id']);

        $labels = DB::transaction(function () use (
            $companyId,
            $storeId,
            $requestedItems,
            $validated
        ): Collection {
            $products = Product::query()
                ->with([
                    'company:id,company_name',
                    'store:id,store_name',
                ])
                ->forContext($companyId, $storeId)
                ->whereIn('id', $requestedItems->keys())
                ->lockForUpdate()
                ->get();

            abort_unless(
                $products->count() === $requestedItems->count(),
                422,
                'One or more selected products are unavailable in this branch.'
            );

            $generator = new BarcodeGeneratorSVG();

            return $products->flatMap(function (Product $product) use (
                $requestedItems,
                $validated,
                $generator
            ): array {
                $quantity = (int) $requestedItems[$product->id]['quantity'];
                $barcode = $product->ensureBarcode();
                $barcodeSvg = $generator->getBarcode(
                    $barcode,
                    BarcodeGeneratorSVG::TYPE_CODE_128,
                    1.8,
                    48
                );

                $label = [
                    ...$product->barcodeLabelData(),
                    'company_name' => $product->company?->company_name,
                    'barcode_image' => 'data:image/svg+xml;base64,'
                        . base64_encode($barcodeSvg),
                    'price_display' => $validated['price_display'],
                ];

                return array_fill(0, $quantity, $label);
            })->values();
        });

        return [
            'labels' => $labels,
            'paper' => $validated['paper'],
            'priceDisplay' => $validated['price_display'],
            'showBranch' => (bool) ($validated['show_branch'] ?? true),
            'showProductCode' => (bool) ($validated['show_product_code'] ?? true),
            'showPartNumber' => (bool) ($validated['show_part_number'] ?? true),
        ];
    }

    private function productPayload(Product $product): array
    {
        return [
            'id' => (int) $product->id,
            'product_code' => $product->product_code,
            'barcode' => $product->barcode,
            'product_name' => $product->product_name,
            'part_number' => $product->part_number,
            'category' => $product->category?->name,
            'brand' => $product->brand?->brand_name,
            'selling_price' => (float) $product->selling_price,
            'mrp' => (float) $product->mrp,
            'current_stock' => (float) $product->current_stock,
            'has_barcode' => $product->hasBarcode(),
        ];
    }

    private function context(Request $request): array
    {
        $companyId = (int) $request->session()->get('active_company_id');
        $storeId = (int) $request->session()->get('active_store_id');

        abort_if(
            $companyId < 1 || $storeId < 1,
            403,
            'Please select a company and branch first.'
        );

        return [$companyId, $storeId];
    }

    private function paperOptions(): array
    {
        return [
            self::PAPER_A4 => 'A4 Sheet',
            self::PAPER_A5 => 'A5 Sheet',
            self::PAPER_ROLL_50X25 => 'Label Roll 50 × 25 mm',
            self::PAPER_ROLL_40X25 => 'Label Roll 40 × 25 mm',
            self::PAPER_SMALL_30X20 => 'Small Label 30 × 20 mm',
        ];
    }

    private function priceOptions(): array
    {
        return [
            self::PRICE_SELLING => 'Selling Price',
            self::PRICE_MRP => 'MRP',
            self::PRICE_BOTH => 'MRP and Selling Price',
            self::PRICE_NONE => 'Hide Price',
        ];
    }
}
