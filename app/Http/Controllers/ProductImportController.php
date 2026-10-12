<?php

namespace App\Http\Controllers;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Godown;
use App\Models\Product;
use App\Models\StockLedger;
use App\Models\Store;
use App\Models\Supplier;
use App\Models\Unit;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use PhpOffice\PhpSpreadsheet\Cell\DataValidation;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProductImportController extends Controller implements HasMiddleware
{
    private const MAX_ROWS = 2000;

    private const HEADERS = [
        'product_code',
        'barcode',
        'product_name',
        'category',
        'brand',
        'unit',
        'supplier',
        'part_number',
        'manufacturer',
        'rack_location',
        'hsn_code',
        'gst_percent',
        'purchase_price',
        'selling_price',
        'wholesale_price',
        'mechanic_price',
        'mrp',
        'opening_stock',
        'opening_location',
        'godown',
        'minimum_stock',
        'maximum_stock',
        'status',
        'description',
    ];

    public static function middleware(): array
    {
        return [
            new Middleware(
                'permission:product.create',
                only: [
                    'create',
                    'downloadTemplate',
                    'preview',
                    'store',
                ]
            ),
        ];
    }

    public function create(): Response
    {
        [$companyId, $storeId] = $this->context();

        return Inertia::render('Products/Import', [
            'template_url' => route('products.import.template'),
            'limits' => [
                'maximum_rows' => self::MAX_ROWS,
                'maximum_file_size_mb' => 10,
            ],
            'masters' => [
                'categories' => $this->categories($companyId, $storeId),
                'brands' => $this->brands($companyId, $storeId),
                'units' => $this->units($companyId, $storeId),
                'suppliers' => $this->suppliers($companyId, $storeId),
                'godowns' => $this->godowns($companyId, $storeId),
            ],
        ]);
    }

    public function downloadTemplate(): StreamedResponse
    {
        [$companyId, $storeId] = $this->context();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Products');

        foreach (self::HEADERS as $index => $header) {
            $sheet->setCellValue([$index + 1, 1], $header);
        }

        $example = [
            '',
            '',
            'Sample Product',
            $this->categories($companyId, $storeId)->first()->name ?? '',
            $this->brands($companyId, $storeId)->first()->brand_name ?? '',
            $this->units($companyId, $storeId)->first()->unit_name ?? '',
            $this->suppliers($companyId, $storeId)->first()->supplier_name ?? '',
            'PART-001',
            'Manufacturer',
            'RACK-A1',
            '8501',
            18,
            100,
            125,
            120,
            115,
            150,
            0,
            'shop',
            '',
            0,
            '',
            'active',
            '',
        ];

        foreach ($example as $index => $value) {
            $sheet->setCellValue([$index + 1, 2], $value);
        }

        $lastColumn = $sheet->getHighestColumn();
        $sheet->freezePane('A2');
        $sheet->setAutoFilter("A1:{$lastColumn}1");
        $sheet->getStyle("A1:{$lastColumn}1")->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $sheet->getStyle("A1:{$lastColumn}1")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF173B67');

        foreach (range('A', $lastColumn) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $this->addListValidation($sheet, 'S2:S2001', ['shop', 'godown']);
        $this->addListValidation($sheet, 'W2:W2001', ['active', 'inactive']);

        $instructions = $spreadsheet->createSheet();
        $instructions->setTitle('Instructions');
        $instructions->fromArray([
            ['PRODUCT IMPORT INSTRUCTIONS'],
            ['Required columns', 'product_name, category, unit, gst_percent, all price fields, opening_stock, minimum_stock, status'],
            ['Product code', 'For repeat-safe imports, enter a stable unique product code or barcode. Leave blank only when importing a new product once.'],
            ['Category / Brand / Unit', 'Enter the exact active master name from the current branch.'],
            ['Supplier', 'Optional. Enter the exact supplier name available in the current branch.'],
            ['Opening location', 'Use shop or godown. Godown is required when opening stock is above zero and location is godown.'],
            ['Status', 'Use active or inactive.'],
            ['Maximum rows', (string) self::MAX_ROWS],
            ['Important', 'Do not rename or remove columns from the Products sheet.'],
        ]);
        $instructions->getColumnDimension('A')->setWidth(24);
        $instructions->getColumnDimension('B')->setWidth(105);
        $instructions->getStyle('A1:B1')->getFont()->setBold(true)->getColor()->setARGB('FFFFFFFF');
        $instructions->getStyle('A1:B1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF173B67');

        return response()->streamDownload(
            function () use ($spreadsheet): void {
                $writer = new Xlsx($spreadsheet);
                $writer->setPreCalculateFormulas(false);
                $writer->save('php://output');
                $spreadsheet->disconnectWorksheets();
            },
            'product_import_template.xlsx',
            [
                'Content-Type' =>
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Cache-Control' =>
                'max-age=0, no-cache, no-store, must-revalidate',
                'Pragma' =>
                'public',
            ]
        );
    }

    public function preview(Request $request): JsonResponse
    {
        [$companyId, $storeId] = $this->context();
        $file = $this->validatedFile($request);
        $rows = $this->readRows($file->getRealPath());
        $result = $this->prepareRows($rows, $companyId, $storeId);

        return response()->json([
            'message' => $result['errors'] === []
                ? 'The file is ready to import.'
                : 'Please correct the invalid rows before importing.',
            'summary' => [
                'total_rows' => count($result['rows']),
                'valid_rows' => count($result['valid']),
                'invalid_rows' => count($result['errors']),
            ],
            'rows' => array_slice($result['rows'], 0, 100),
            'errors' => $result['errors'],
        ]);
    }

    public function store(Request $request): RedirectResponse|JsonResponse
    {
        [$companyId, $storeId] = $this->context();
        $file = $this->validatedFile($request);
        $rows = $this->readRows($file->getRealPath());
        $result = $this->prepareRows($rows, $companyId, $storeId);

        if ($result['errors'] !== []) {
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'The import contains invalid rows.',
                    'errors' => $result['errors'],
                ], 422);
            }

            throw ValidationException::withMessages([
                'file' => 'The file contains invalid rows. Preview the file and correct them before importing.',
            ]);
        }

        $created = DB::transaction(function () use ($result, $companyId, $storeId): int {
            $store = Store::query()->whereKey($storeId)->lockForUpdate()->firstOrFail();
            $nextNumber = ((int) Product::query()->where('store_id', $storeId)->max('id')) + 1;
            $created = 0;

            foreach ($result['valid'] as $row) {
                $productCode = $row['product_code'];

                if ($productCode === null) {
                    do {
                        $productCode = strtoupper((string) ($store->store_code ?: 'PRD'))
                            . '-P'
                            . str_pad((string) $nextNumber, 6, '0', STR_PAD_LEFT);
                        $nextNumber++;
                    } while (
                        Product::query()
                        ->where('store_id', $storeId)
                        ->where('product_code', $productCode)
                        ->exists()
                    );
                }

                $product = Product::query()->create([
                    'company_id' => $companyId,
                    'store_id' => $storeId,
                    'category_id' => $row['category_id'],
                    'brand_id' => $row['brand_id'],
                    'unit_id' => $row['unit_id'],
                    'supplier_id' => $row['supplier_id'],
                    'product_code' => $productCode,
                    'barcode' => $row['barcode'],
                    'product_name' => $row['product_name'],
                    'part_number' => $row['part_number'],
                    'manufacturer' => $row['manufacturer'],
                    'rack_location' => $row['rack_location'],
                    'hsn_code' => $row['hsn_code'],
                    'gst_percent' => $row['gst_percent'],
                    'purchase_price' => $row['purchase_price'],
                    'selling_price' => $row['selling_price'],
                    'wholesale_price' => $row['wholesale_price'],
                    'mechanic_price' => $row['mechanic_price'],
                    'mrp' => $row['mrp'],
                    'opening_stock' => $row['opening_stock'],
                    'current_stock' => $row['opening_stock'],
                    'minimum_stock' => $row['minimum_stock'],
                    'maximum_stock' => $row['maximum_stock'],
                    'image' => null,
                    'description' => $row['description'],
                    'status' => $row['status'],
                ]);

                if ($row['opening_stock'] > 0) {
                    StockLedger::query()->create([
                        'company_id' => $companyId,
                        'store_id' => $storeId,
                        'godown_id' => $row['godown_id'],
                        'product_id' => $product->id,
                        'transaction_date' => now(),
                        'transaction_type' => 'opening_stock',
                        'reference_id' => $product->id,
                        'reference_no' => $productCode,
                        'qty_in' => $row['opening_stock'],
                        'qty_out' => 0,
                        'balance_qty' => $row['opening_stock'],
                        'unit_cost' => $row['purchase_price'],
                        'total_cost' => round($row['opening_stock'] * $row['purchase_price'], 2),
                        'remarks' => $row['godown_id'] === null
                            ? 'Opening stock imported to Shop.'
                            : 'Opening stock imported to Godown.',
                    ]);
                }

                $created++;
            }

            return $created;
        });

        if ($request->expectsJson()) {
            return response()->json([
                'message' => "{$created} products imported successfully.",
                'redirect' => route('products.index'),
            ]);
        }

        return redirect()
            ->route('products.index')
            ->with('success', "{$created} products imported successfully.");
    }

    private function validatedFile(Request $request): \Illuminate\Http\UploadedFile
    {
        $validated = $request->validate([
            'file' => [
                'required',
                'file',
                'mimes:xlsx,xls,csv,txt',
                'max:10240',
            ],
        ]);

        return $validated['file'];
    }

    private function readRows(string $path): array
    {
        $spreadsheet = IOFactory::load($path);
        $sheet = $spreadsheet->getSheet(0);
        $rawRows = $sheet->toArray(null, true, true, false);
        $spreadsheet->disconnectWorksheets();

        if ($rawRows === []) {
            throw ValidationException::withMessages([
                'file' => 'The uploaded file is empty.',
            ]);
        }

        $headers = array_map(
            fn($value) => Str::snake(trim((string) $value)),
            array_shift($rawRows)
        );

        $missing = array_values(array_diff(self::HEADERS, $headers));

        if ($missing !== []) {
            throw ValidationException::withMessages([
                'file' => 'Missing columns: ' . implode(', ', $missing) . '.',
            ]);
        }

        $rows = [];

        foreach ($rawRows as $index => $values) {
            $values = array_pad($values, count($headers), null);
            $row = array_combine($headers, array_slice($values, 0, count($headers)));

            if ($this->rowIsEmpty($row)) {
                continue;
            }

            $row['_row'] = $index + 2;
            $rows[] = $row;
        }

        if (count($rows) > self::MAX_ROWS) {
            throw ValidationException::withMessages([
                'file' => 'A maximum of ' . self::MAX_ROWS . ' product rows can be imported at one time.',
            ]);
        }

        if ($rows === []) {
            throw ValidationException::withMessages([
                'file' => 'No product rows were found in the uploaded file.',
            ]);
        }

        return $rows;
    }

    private function prepareRows(array $rows, int $companyId, int $storeId): array
    {
        $masters = $this->masterMaps($companyId, $storeId);
        $valid = [];
        $errors = [];
        $preview = [];
        $fileCodes = [];
        $fileBarcodes = [];
        $fileProducts = [];

        foreach ($rows as $source) {
            $rowNumber = (int) $source['_row'];
            $row = $this->normaliseRow($source);

            $validator = Validator::make($row, [
                'product_code' => ['nullable', 'string', 'max:50'],
                'barcode' => ['nullable', 'string', 'max:100'],
                'product_name' => ['required', 'string', 'max:255'],
                'category' => ['required', 'string', 'max:255'],
                'brand' => ['nullable', 'string', 'max:255'],
                'unit' => ['required', 'string', 'max:255'],
                'supplier' => ['nullable', 'string', 'max:255'],
                'part_number' => ['nullable', 'string', 'max:255'],
                'manufacturer' => ['nullable', 'string', 'max:255'],
                'rack_location' => ['nullable', 'string', 'max:255'],
                'hsn_code' => ['nullable', 'string', 'max:20'],
                'gst_percent' => ['required', 'numeric', 'min:0', 'max:100'],
                'purchase_price' => ['required', 'numeric', 'min:0'],
                'selling_price' => ['required', 'numeric', 'min:0'],
                'wholesale_price' => ['required', 'numeric', 'min:0'],
                'mechanic_price' => ['required', 'numeric', 'min:0'],
                'mrp' => ['required', 'numeric', 'min:0'],
                'opening_stock' => ['required', 'numeric', 'min:0'],
                'opening_location' => ['required', 'in:shop,godown'],
                'godown' => ['nullable', 'string', 'max:255'],
                'minimum_stock' => ['required', 'numeric', 'min:0'],
                'maximum_stock' => ['nullable', 'numeric', 'gte:minimum_stock'],
                'status' => ['required', 'in:active,inactive,1,0,yes,no,true,false'],
                'description' => ['nullable', 'string'],
            ]);

            $rowErrors = $validator->errors()->all();
            $categoryId = $masters['categories'][$this->key($row['category'])] ?? null;
            $brandId = $row['brand'] === null ? null : ($masters['brands'][$this->key($row['brand'])] ?? null);
            $unitId = $masters['units'][$this->key($row['unit'])] ?? null;
            $supplierId = $row['supplier'] === null ? null : ($masters['suppliers'][$this->key($row['supplier'])] ?? null);
            $godownId = $row['godown'] === null ? null : ($masters['godowns'][$this->key($row['godown'])] ?? null);

            if ($categoryId === null) {
                $rowErrors[] = 'Category was not found in the active branch.';
            }
            if ($row['brand'] !== null && $brandId === null) {
                $rowErrors[] = 'Brand was not found in the active branch.';
            }
            if ($unitId === null) {
                $rowErrors[] = 'Unit was not found in the active branch.';
            }
            if ($row['supplier'] !== null && $supplierId === null) {
                $rowErrors[] = 'Supplier is not available in the active branch.';
            }
            if ($row['opening_location'] === 'godown' && (float) $row['opening_stock'] > 0 && $godownId === null) {
                $rowErrors[] = 'A valid godown is required for godown opening stock.';
            }

            if ($row['product_code'] !== null) {
                $codeKey = Str::lower($row['product_code']);
                if (isset($fileCodes[$codeKey])) {
                    $rowErrors[] = 'Product code is duplicated in this file.';
                }
                $fileCodes[$codeKey] = true;

                if (Product::query()->where('store_id', $storeId)->where('product_code', $row['product_code'])->exists()) {
                    $rowErrors[] = 'Product code already exists in this branch.';
                }
            }

            if ($row['barcode'] !== null) {
                $barcodeKey = Str::lower($row['barcode']);
                if (isset($fileBarcodes[$barcodeKey])) {
                    $rowErrors[] = 'Barcode is duplicated in this file.';
                }
                $fileBarcodes[$barcodeKey] = true;

                if (Product::query()->where('store_id', $storeId)->where('barcode', $row['barcode'])->exists()) {
                    $rowErrors[] = 'Barcode already exists in this branch.';
                }
            }

            /*
            |------------------------------------------------------------------
            | Fallback Duplicate Protection
            |------------------------------------------------------------------
            |
            | When product_code and barcode are both empty, Laravel generates a
            | new product code. Without a fallback identity, uploading the same
            | file again would create duplicate products. The fallback combines
            | the branch master IDs with product name and part number.
            |
            */

            $productFingerprint = match (true) {
                $row['product_code'] !== null =>
                'code|' . $this->key($row['product_code']),

                $row['barcode'] !== null =>
                'barcode|' . $this->key($row['barcode']),

                default => implode('|', [
                    'product',
                    $storeId,
                    $categoryId ?? 0,
                    $unitId ?? 0,
                    $this->key($row['product_name']),
                    $this->key($row['part_number']),
                ]),
            };

            if (isset($fileProducts[$productFingerprint])) {
                $rowErrors[] =
                    'The same product name, category, unit and part number are duplicated in this file.';
            }

            $fileProducts[$productFingerprint] = true;

            if (
                $row['product_code'] === null
                &&
                $row['barcode'] === null
                &&
                $categoryId !== null
                &&
                $unitId !== null
            ) {
                $existingProduct = Product::query()
                    ->where('company_id', $companyId)
                    ->where('store_id', $storeId)
                    ->where('category_id', $categoryId)
                    ->where('unit_id', $unitId)
                    ->whereRaw(
                        'LOWER(TRIM(product_name)) = ?',
                        [$this->key($row['product_name'])]
                    )
                    ->when(
                        $row['part_number'] === null,
                        fn(Builder $query) => $query->whereNull('part_number'),
                        fn(Builder $query) => $query->whereRaw(
                            'LOWER(TRIM(part_number)) = ?',
                            [$this->key($row['part_number'])]
                        )
                    )
                    ->first(['id', 'product_code']);

                if ($existingProduct) {
                    $rowErrors[] =
                        'This product already exists in the branch as '
                        . $existingProduct->product_code
                        . '. Add a unique product code or barcode only if this is a different product.';
                }
            }

            $preview[] = [
                'row' => $rowNumber,
                'product_code' => $row['product_code'],
                'product_name' => $row['product_name'],
                'category' => $row['category'],
                'unit' => $row['unit'],
                'selling_price' => (float) $row['selling_price'],
                'opening_stock' => (float) $row['opening_stock'],
                'valid' => $rowErrors === [],
                'errors' => $rowErrors,
            ];

            if ($rowErrors !== []) {
                $errors[] = [
                    'row' => $rowNumber,
                    'product_name' => $row['product_name'],
                    'messages' => array_values(array_unique($rowErrors)),
                ];
                continue;
            }

            $valid[] = array_merge($row, [
                'category_id' => $categoryId,
                'brand_id' => $brandId,
                'unit_id' => $unitId,
                'supplier_id' => $supplierId,
                'godown_id' => $row['opening_location'] === 'godown' ? $godownId : null,
                'gst_percent' => (float) $row['gst_percent'],
                'purchase_price' => (float) $row['purchase_price'],
                'selling_price' => (float) $row['selling_price'],
                'wholesale_price' => (float) $row['wholesale_price'],
                'mechanic_price' => (float) $row['mechanic_price'],
                'mrp' => (float) $row['mrp'],
                'opening_stock' => (float) $row['opening_stock'],
                'minimum_stock' => (float) $row['minimum_stock'],
                'maximum_stock' => $row['maximum_stock'] === null ? null : (float) $row['maximum_stock'],
                'status' => in_array($row['status'], ['active', '1', 'yes', 'true'], true),
            ]);
        }

        return [
            'rows' => $preview,
            'valid' => $valid,
            'errors' => $errors,
        ];
    }

    private function normaliseRow(array $source): array
    {
        $row = [];

        foreach (self::HEADERS as $header) {
            $value = $source[$header] ?? null;
            $value = is_string($value) ? trim($value) : $value;
            $row[$header] = $value === '' ? null : $value;
        }

        $row['product_code'] = $row['product_code'] === null ? null : strtoupper((string) $row['product_code']);
        $row['barcode'] = $row['barcode'] === null ? null : (string) $row['barcode'];
        $row['opening_location'] = Str::lower((string) ($row['opening_location'] ?? 'shop'));
        $row['status'] = Str::lower((string) ($row['status'] ?? 'active'));

        return $row;
    }

    private function masterMaps(int $companyId, int $storeId): array
    {
        $categories = $this->categories($companyId, $storeId);
        $brands = $this->brands($companyId, $storeId);
        $units = $this->units($companyId, $storeId);
        $suppliers = $this->suppliers($companyId, $storeId);
        $godowns = $this->godowns($companyId, $storeId);

        return [
            'categories' => $this->mapMasters($categories, ['name']),
            'brands' => $this->mapMasters($brands, ['brand_name']),
            'units' => $this->mapMasters($units, ['unit_name', 'short_name']),
            'suppliers' => $this->mapMasters($suppliers, ['supplier_name', 'supplier_code']),
            'godowns' => $this->mapMasters($godowns, ['godown_name', 'godown_code']),
        ];
    }

    private function mapMasters(Collection $models, array $columns): array
    {
        $map = [];

        foreach ($models as $model) {
            foreach ($columns as $column) {
                if (filled($model->{$column} ?? null)) {
                    $map[$this->key($model->{$column})] = (int) $model->id;
                }
            }
        }

        return $map;
    }

    private function categories(int $companyId, int $storeId): Collection
    {
        return Category::query()
            ->where('company_id', $companyId)
            ->where('store_id', $storeId)
            ->where('status', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    private function brands(int $companyId, int $storeId): Collection
    {
        return Brand::query()
            ->where('company_id', $companyId)
            ->where('store_id', $storeId)
            ->where('status', true)
            ->orderBy('brand_name')
            ->get(['id', 'brand_name']);
    }

    private function units(int $companyId, int $storeId): Collection
    {
        return Unit::query()
            ->where('company_id', $companyId)
            ->where('store_id', $storeId)
            ->where('status', true)
            ->orderBy('unit_name')
            ->get(['id', 'unit_name', 'short_name']);
    }

    private function suppliers(int $companyId, int $storeId): Collection
    {
        return Supplier::query()
            ->where('company_id', $companyId)
            ->where('status', true)
            ->whereHas('stores', fn(Builder $query) => $query->where('stores.id', $storeId))
            ->orderBy('supplier_name')
            ->get(['id', 'supplier_code', 'supplier_name']);
    }

    private function godowns(int $companyId, int $storeId): Collection
    {
        return Godown::query()
            ->where('company_id', $companyId)
            ->where('store_id', $storeId)
            ->where('status', true)
            ->orderBy('godown_name')
            ->get(['id', 'godown_name', 'godown_code']);
    }

    private function context(): array
    {
        $companyId = (int) session('active_company_id');
        $storeId = (int) session('active_store_id');

        abort_unless($companyId > 0 && $storeId > 0, 403, 'No active company or branch is selected.');

        $valid = Store::query()
            ->whereKey($storeId)
            ->where('company_id', $companyId)
            ->where('status', true)
            ->exists();

        abort_unless($valid, 403, 'The selected branch is unavailable.');

        return [$companyId, $storeId];
    }

    private function addListValidation($sheet, string $range, array $values): void
    {
        $validation = $sheet->getCell(explode(':', $range)[0])->getDataValidation();
        $validation->setType(DataValidation::TYPE_LIST);
        $validation->setErrorStyle(DataValidation::STYLE_STOP);
        $validation->setAllowBlank(false);
        $validation->setShowDropDown(true);
        $validation->setFormula1('"' . implode(',', $values) . '"');

        $sheet->setDataValidation($range, $validation);
    }

    private function rowIsEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if ($value !== null && trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    private function key(mixed $value): string
    {
        return Str::lower(trim((string) $value));
    }
}
