<?php

namespace App\Http\Controllers;

use App\Models\Store;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;

class SupplierImportController extends Controller implements HasMiddleware
{
    private const MAX_ROWS = 3000;

    private const HEADERS = [
        'supplier_name',
        'phone',
        'supplier_code',
        'contact_person',
        'alternate_phone',
        'email',
        'gst_number',
        'pan_number',
        'address',
        'city',
        'state',
        'country',
        'pincode',
        'credit_limit',
        'credit_days',
        'remarks',
        'status',
    ];

    public static function middleware(): array
    {
        return [
            new Middleware(
                'permission:supplier.create',
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

        return Inertia::render('Suppliers/Import', [
            'template_url' => route('suppliers.import.template'),
            'limits' => [
                'maximum_rows' => self::MAX_ROWS,
                'maximum_file_size_mb' => 10,
            ],
            'context' => [
                'company_id' => $companyId,
                'store_id' => $storeId,
                'store_name' => Store::query()
                    ->whereKey($storeId)
                    ->value('store_name'),
            ],
        ]);
    }

    public function downloadTemplate(): StreamedResponse
    {
        $this->context();

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Suppliers');

        foreach (self::HEADERS as $index => $header) {
            $sheet->setCellValue([$index + 1, 1], $header);
        }

        $example = [
            'Sample Supplier',
            '9876543210',
            '',
            'Contact Person',
            '',
            'supplier@example.com',
            '',
            '',
            'Door No, Street',
            'Thiruthuraipoondi',
            'Tamil Nadu',
            'India',
            '614713',
            0,
            0,
            '',
            'active',
        ];

        foreach ($example as $index => $value) {
            $sheet->setCellValue([$index + 1, 2], $value);
        }

        $lastColumn = $sheet->getHighestColumn();
        $sheet->freezePane('A2');
        $sheet->setAutoFilter("A1:{$lastColumn}1");
        $sheet->getStyle("A1:{$lastColumn}1")
            ->getFont()
            ->setBold(true)
            ->getColor()
            ->setARGB('FFFFFFFF');
        $sheet->getStyle("A1:{$lastColumn}1")
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB('FF5C3B83');
        $sheet->getStyle('A1:B1')
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB('FF16705A');

        foreach (range('A', $lastColumn) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $instructions = $spreadsheet->createSheet();
        $instructions->setTitle('Instructions');
        $instructions->fromArray([
            ['SUPPLIER IMPORT INSTRUCTIONS'],
            ['Required', 'supplier_name'],
            ['Recommended', 'Enter phone or GST number for reliable duplicate detection.'],
            ['Duplicate priority', 'GST number, phone, supplier code, then supplier name with city.'],
            ['Existing company supplier', 'The supplier is attached to the active branch.'],
            ['Already in branch', 'The row is safely skipped.'],
            ['Supplier code', 'Optional. Leave blank to generate SUP000001 style codes automatically.'],
            ['Opening balance', 'Imported suppliers start with zero opening balance. Enter balances through the accounting workflow.'],
            ['Status', 'Use active or inactive. Blank status defaults to active.'],
            ['Maximum rows', (string) self::MAX_ROWS],
            ['Important', 'Do not rename or remove columns from the Suppliers sheet.'],
        ]);
        $instructions->getColumnDimension('A')->setWidth(24);
        $instructions->getColumnDimension('B')->setWidth(105);
        $instructions->getStyle('A1:B1')
            ->getFont()
            ->setBold(true)
            ->getColor()
            ->setARGB('FFFFFFFF');
        $instructions->getStyle('A1:B1')
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB('FF5C3B83');

        return response()->streamDownload(
            function () use ($spreadsheet): void {
                $writer = new Xlsx($spreadsheet);
                $writer->setPreCalculateFormulas(false);
                $writer->save('php://output');
                $spreadsheet->disconnectWorksheets();
            },
            'supplier_import_template.xlsx',
            [
                'Content-Type' =>
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Cache-Control' =>
                'max-age=0, no-cache, no-store, must-revalidate',
                'Pragma' => 'public',
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
                ? 'The supplier file is ready to import.'
                : 'Please correct the invalid supplier rows before importing.',
            'summary' => [
                'total_rows' => count($result['rows']),
                'new_suppliers' => count($result['create']),
                'branch_attachments' => count($result['attach']),
                'skipped_rows' => count($result['skip']),
                'invalid_rows' => count($result['errors']),
            ],
            'rows' => array_slice($result['rows'], 0, 150),
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
                    'message' => 'The supplier import contains invalid rows.',
                    'errors' => $result['errors'],
                ], 422);
            }

            throw ValidationException::withMessages([
                'file' =>
                'The file contains invalid rows. Preview and correct it before importing.',
            ]);
        }

        $counts = DB::transaction(
            function () use ($result, $companyId, $storeId): array {
                Store::query()
                    ->whereKey($storeId)
                    ->lockForUpdate()
                    ->firstOrFail();

                $nextNumber =
                    ((int) Supplier::query()
                        ->where('company_id', $companyId)
                        ->max('id')) + 1;

                $created = 0;
                $attached = 0;
                $skipped = count($result['skip']);

                foreach ($result['attach'] as $row) {
                    $supplier = Supplier::query()
                        ->where('company_id', $companyId)
                        ->whereKey($row['existing_supplier_id'])
                        ->lockForUpdate()
                        ->firstOrFail();

                    $supplier->stores()->syncWithoutDetaching([$storeId]);
                    $attached++;
                }

                foreach ($result['create'] as $row) {
                    $supplierCode = $row['supplier_code'];

                    if ($supplierCode === null) {
                        do {
                            $supplierCode = 'SUP'
                                . str_pad(
                                    (string) $nextNumber,
                                    6,
                                    '0',
                                    STR_PAD_LEFT
                                );
                            $nextNumber++;
                        } while (
                            Supplier::query()
                            ->where('company_id', $companyId)
                            ->where('supplier_code', $supplierCode)
                            ->exists()
                        );
                    }

                    $supplier = Supplier::query()->create([
                        'company_id' => $companyId,
                        'supplier_code' => $supplierCode,
                        'supplier_name' => $row['supplier_name'],
                        'contact_person' => $row['contact_person'],
                        'phone' => $row['phone'],
                        'alternate_phone' => $row['alternate_phone'],
                        'email' => $row['email'],
                        'gst_number' => $row['gst_number'],
                        'pan_number' => $row['pan_number'],
                        'address' => $row['address'],
                        'city' => $row['city'],
                        'state' => $row['state'],
                        'country' => $row['country'],
                        'pincode' => $row['pincode'],
                        'opening_balance' => 0,
                        'credit_limit' => $row['credit_limit'],
                        'credit_days' => $row['credit_days'],
                        'balance_type' => 'Cr',
                        'status' => $row['status'] === 'active',
                        'remarks' => $row['remarks'],
                    ]);

                    $supplier->stores()->syncWithoutDetaching([$storeId]);
                    $created++;
                }

                return [
                    'created' => $created,
                    'attached' => $attached,
                    'skipped' => $skipped,
                ];
            }
        );

        $message = sprintf(
            '%d suppliers created, %d attached to this branch and %d existing rows skipped.',
            $counts['created'],
            $counts['attached'],
            $counts['skipped']
        );

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'summary' => $counts,
                'redirect' => route('suppliers.index'),
            ]);
        }

        return redirect()
            ->route('suppliers.index')
            ->with('success', $message);
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
        $rawRows = $spreadsheet
            ->getSheet(0)
            ->toArray(null, true, true, false);
        $spreadsheet->disconnectWorksheets();

        if ($rawRows === []) {
            throw ValidationException::withMessages([
                'file' => 'The uploaded supplier file is empty.',
            ]);
        }

        $headers = array_map(
            fn($value) => Str::snake(trim((string) $value)),
            array_shift($rawRows)
        );

        $missing = array_values(array_diff(self::HEADERS, $headers));

        if ($missing !== []) {
            throw ValidationException::withMessages([
                'file' =>
                'Missing columns: ' . implode(', ', $missing) . '.',
            ]);
        }

        $rows = [];

        foreach ($rawRows as $index => $values) {
            $values = array_pad($values, count($headers), null);
            $row = array_combine(
                $headers,
                array_slice($values, 0, count($headers))
            );

            if ($this->rowIsEmpty($row)) {
                continue;
            }

            $row['_row'] = $index + 2;
            $rows[] = $row;
        }

        if (count($rows) > self::MAX_ROWS) {
            throw ValidationException::withMessages([
                'file' =>
                'A maximum of '
                    . self::MAX_ROWS
                    . ' supplier rows can be imported at one time.',
            ]);
        }

        if ($rows === []) {
            throw ValidationException::withMessages([
                'file' => 'No supplier rows were found in the uploaded file.',
            ]);
        }

        return $rows;
    }

    private function prepareRows(
        array $rows,
        int $companyId,
        int $storeId
    ): array {
        $preview = [];
        $create = [];
        $attach = [];
        $skip = [];
        $errors = [];
        $fileIdentities = [];
        $fileCodes = [];

        foreach ($rows as $source) {
            $rowNumber = (int) $source['_row'];
            $row = $this->normaliseRow($source);

            $validator = Validator::make($row, [
                'supplier_name' => ['required', 'string', 'max:150'],
                'phone' => ['nullable', 'regex:/^[6-9][0-9]{9}$/'],
                'supplier_code' => ['nullable', 'string', 'max:30'],
                'contact_person' => ['nullable', 'string', 'max:150'],
                'alternate_phone' => ['nullable', 'regex:/^[6-9][0-9]{9}$/'],
                'email' => ['nullable', 'email', 'max:255'],
                'gst_number' => [
                    'nullable',
                    'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z][A-Z0-9]Z[A-Z0-9]$/',
                ],
                'pan_number' => ['nullable', 'regex:/^[A-Z]{5}[0-9]{4}[A-Z]$/'],
                'address' => ['nullable', 'string'],
                'city' => ['nullable', 'string', 'max:100'],
                'state' => ['nullable', 'string', 'max:100'],
                'country' => ['required', 'string', 'max:100'],
                'pincode' => ['nullable', 'regex:/^[0-9]{6}$/'],
                'credit_limit' => ['required', 'numeric', 'min:0'],
                'credit_days' => ['required', 'integer', 'min:0', 'max:3650'],
                'remarks' => ['nullable', 'string'],
                'status' => ['required', 'in:active,inactive'],
            ], [
                'phone.regex' => 'Phone must be a valid 10-digit Indian mobile number.',
                'alternate_phone.regex' => 'Alternate phone must be a valid 10-digit Indian mobile number.',
                'gst_number.regex' => 'GST number format is invalid.',
                'pan_number.regex' => 'PAN number format is invalid.',
                'pincode.regex' => 'Pincode must contain exactly 6 digits.',
            ]);

            $rowErrors = $validator->errors()->all();
            $identity = $this->identity($row);

            if (isset($fileIdentities[$identity])) {
                $rowErrors[] =
                    'The same supplier identity is repeated in this import file.';
            }
            $fileIdentities[$identity] = true;

            if ($row['supplier_code'] !== null) {
                $codeKey = Str::lower($row['supplier_code']);
                if (isset($fileCodes[$codeKey])) {
                    $rowErrors[] =
                        'The same supplier code is repeated in this import file.';
                }
                $fileCodes[$codeKey] = true;
            }

            $existing = $this->findExistingSupplier($row, $companyId, $storeId);

            if (!$existing && $row['supplier_code'] !== null) {
                $codeExists = Supplier::query()
                    ->where('company_id', $companyId)
                    ->where('supplier_code', $row['supplier_code'])
                    ->exists();

                if ($codeExists) {
                    $rowErrors[] =
                        'Supplier code already belongs to another supplier in this company.';
                }
            }

            $action = match (true) {
                $rowErrors !== [] => 'invalid',
                !$existing => 'create',
                (bool) $existing->attached_to_current_store => 'skip',
                default => 'attach',
            };

            $actionMessage = match ($action) {
                'create' => 'Create new supplier',
                'attach' => 'Attach existing company supplier to this branch',
                'skip' => 'Already available in this branch',
                default => 'Correction required',
            };

            $preview[] = [
                'row' => $rowNumber,
                'supplier_name' => $row['supplier_name'],
                'phone' => $row['phone'],
                'gst_number' => $row['gst_number'],
                'supplier_code' => $existing?->supplier_code
                    ?? $row['supplier_code'],
                'city' => $row['city'],
                'action' => $action,
                'action_message' => $actionMessage,
                'errors' => array_values(array_unique($rowErrors)),
            ];

            if ($rowErrors !== []) {
                $errors[] = [
                    'row' => $rowNumber,
                    'supplier_name' => $row['supplier_name'],
                    'phone' => $row['phone'],
                    'messages' => array_values(array_unique($rowErrors)),
                ];
                continue;
            }

            if ($action === 'create') {
                $create[] = $row;
                continue;
            }

            $row['existing_supplier_id'] = (int) $existing->id;

            if ($action === 'attach') {
                $attach[] = $row;
                continue;
            }

            $skip[] = $row;
        }

        return [
            'rows' => $preview,
            'create' => $create,
            'attach' => $attach,
            'skip' => $skip,
            'errors' => $errors,
        ];
    }

    private function findExistingSupplier(
        array $row,
        int $companyId,
        int $storeId
    ): ?Supplier {
        $query = Supplier::query()
            ->where('company_id', $companyId)
            ->withExists([
                'stores as attached_to_current_store' =>
                fn($storeQuery) =>
                $storeQuery->where('stores.id', $storeId),
            ]);

        if ($row['gst_number'] !== null) {
            return $query
                ->where('gst_number', $row['gst_number'])
                ->first();
        }

        if ($row['phone'] !== null) {
            return $query
                ->where('phone', $row['phone'])
                ->first();
        }

        if ($row['supplier_code'] !== null) {
            return $query
                ->where('supplier_code', $row['supplier_code'])
                ->first();
        }

        return $query
            ->whereRaw(
                'LOWER(TRIM(supplier_name)) = ?',
                [$this->key($row['supplier_name'])]
            )
            ->when(
                $row['city'] === null,
                fn($builder) => $builder->whereNull('city'),
                fn($builder) => $builder->whereRaw(
                    'LOWER(TRIM(city)) = ?',
                    [$this->key($row['city'])]
                )
            )
            ->first();
    }

    private function identity(array $row): string
    {
        return match (true) {
            $row['gst_number'] !== null =>
            'gst|' . $this->key($row['gst_number']),
            $row['phone'] !== null =>
            'phone|' . $row['phone'],
            $row['supplier_code'] !== null =>
            'code|' . $this->key($row['supplier_code']),
            default =>
            'name|'
                . $this->key($row['supplier_name'])
                . '|'
                . $this->key($row['city']),
        };
    }

    private function normaliseRow(array $source): array
    {
        $row = [];

        foreach (self::HEADERS as $header) {
            $value = $source[$header] ?? null;
            $value = is_string($value) ? trim($value) : $value;
            $row[$header] = $value === '' ? null : $value;
        }

        $row['supplier_name'] = trim((string) ($row['supplier_name'] ?? ''));
        $row['phone'] = $this->normalisePhone($row['phone']);
        $row['alternate_phone'] = $this->normalisePhone($row['alternate_phone']);
        $row['supplier_code'] = $row['supplier_code'] === null
            ? null
            : strtoupper((string) $row['supplier_code']);
        $row['gst_number'] = $row['gst_number'] === null
            ? null
            : strtoupper((string) $row['gst_number']);
        $row['pan_number'] = $row['pan_number'] === null
            ? null
            : strtoupper((string) $row['pan_number']);
        $row['pincode'] = $row['pincode'] === null
            ? null
            : preg_replace('/\D+/', '', (string) $row['pincode']);
        $row['country'] = $row['country'] ?? 'India';
        $row['credit_limit'] = $row['credit_limit'] ?? 0;
        $row['credit_days'] = $row['credit_days'] ?? 0;

        $status = Str::lower((string) ($row['status'] ?? 'active'));
        $row['status'] = in_array(
            $status,
            ['active', '1', 'yes', 'true'],
            true
        ) ? 'active' : 'inactive';

        return $row;
    }

    private function normalisePhone(mixed $value): ?string
    {
        if ($value === null || trim((string) $value) === '') {
            return null;
        }

        $digits = preg_replace('/\D+/', '', (string) $value);

        if (strlen($digits) === 12 && str_starts_with($digits, '91')) {
            return substr($digits, 2);
        }

        if (strlen($digits) === 11 && str_starts_with($digits, '0')) {
            return substr($digits, 1);
        }

        return $digits;
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

    private function context(): array
    {
        $companyId = (int) session('active_company_id');
        $storeId = (int) session('active_store_id');

        abort_unless(
            $companyId > 0 && $storeId > 0,
            403,
            'No active company or branch is selected.'
        );

        $valid = Store::query()
            ->whereKey($storeId)
            ->where('company_id', $companyId)
            ->where('status', true)
            ->exists();

        abort_unless(
            $valid,
            403,
            'The selected branch is unavailable.'
        );

        return [$companyId, $storeId];
    }
}
