<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Store;
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

class CustomerImportController extends Controller implements HasMiddleware
{
    private const MAX_ROWS = 3000;

    private const HEADERS = [
        'customer_name',
        'mobile',
        'customer_code',
        'contact_person',
        'phone',
        'email',
        'billing_address',
        'shipping_address',
        'city',
        'state',
        'country',
        'pincode',
        'gst_number',
        'pan_number',
        'remarks',
        'status',
    ];

    public static function middleware(): array
    {
        return [
            new Middleware(
                'permission:customer.create',
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

        return Inertia::render('Customers/Import', [
            'template_url' => route('customers.import.template'),
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
        $sheet->setTitle('Customers');

        foreach (self::HEADERS as $index => $header) {
            $sheet->setCellValue([$index + 1, 1], $header);
        }

        $example = [
            'Sample Customer',
            '9876543210',
            '',
            '',
            '',
            'customer@example.com',
            'Door No, Street',
            '',
            'Thiruthuraipoondi',
            'Tamil Nadu',
            'India',
            '614713',
            '',
            '',
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
            ->setARGB('FF174C78');
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
            ['CUSTOMER IMPORT INSTRUCTIONS'],
            ['Required', 'customer_name and mobile'],
            ['Mobile', 'Enter a unique 10-digit Indian mobile number. +91 and spaces are accepted and removed automatically.'],
            ['Existing customer', 'If the mobile exists in the company, the customer is attached to the active branch.'],
            ['Already in branch', 'The row is safely skipped. A duplicate customer is not created.'],
            ['Customer code', 'Optional. Leave blank to generate CUS000001 style codes automatically.'],
            ['Status', 'Use active or inactive. Blank status defaults to active.'],
            ['Country', 'Blank country defaults to India.'],
            ['Maximum rows', (string) self::MAX_ROWS],
            ['Important', 'Do not rename or remove columns from the Customers sheet.'],
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
            ->setARGB('FF174C78');

        return response()->streamDownload(
            function () use ($spreadsheet): void {
                $writer = new Xlsx($spreadsheet);
                $writer->setPreCalculateFormulas(false);
                $writer->save('php://output');
                $spreadsheet->disconnectWorksheets();
            },
            'customer_import_template.xlsx',
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
                ? 'The customer file is ready to import.'
                : 'Please correct the invalid customer rows before importing.',
            'summary' => [
                'total_rows' => count($result['rows']),
                'new_customers' => count($result['create']),
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
                    'message' => 'The customer import contains invalid rows.',
                    'errors' => $result['errors'],
                ], 422);
            }

            throw ValidationException::withMessages([
                'file' =>
                'The file contains invalid rows. Preview and correct the file before importing.',
            ]);
        }

        $counts = DB::transaction(
            function () use ($result, $companyId, $storeId): array {
                Store::query()
                    ->whereKey($storeId)
                    ->lockForUpdate()
                    ->firstOrFail();

                $nextNumber =
                    ((int) Customer::query()
                        ->where('company_id', $companyId)
                        ->max('id')) + 1;

                $created = 0;
                $attached = 0;
                $skipped = count($result['skip']);

                foreach ($result['attach'] as $row) {
                    $customer = Customer::query()
                        ->where('company_id', $companyId)
                        ->whereKey($row['existing_customer_id'])
                        ->lockForUpdate()
                        ->firstOrFail();

                    $customer->stores()->syncWithoutDetaching([
                        $storeId,
                    ]);

                    $attached++;
                }

                foreach ($result['create'] as $row) {
                    $customerCode = $row['customer_code'];

                    if ($customerCode === null) {
                        do {
                            $customerCode = 'CUS'
                                . str_pad(
                                    (string) $nextNumber,
                                    6,
                                    '0',
                                    STR_PAD_LEFT
                                );
                            $nextNumber++;
                        } while (
                            Customer::query()
                            ->where('company_id', $companyId)
                            ->where('customer_code', $customerCode)
                            ->exists()
                        );
                    }

                    $customer = Customer::query()->create([
                        'company_id' => $companyId,
                        'customer_code' => $customerCode,
                        'customer_name' => $row['customer_name'],
                        'customer_type' => 'regular',
                        'is_system_customer' => false,
                        'contact_person' => $row['contact_person'],
                        'mobile' => $row['mobile'],
                        'phone' => $row['phone'],
                        'email' => $row['email'],
                        'billing_address' => $row['billing_address'],
                        'shipping_address' => $row['shipping_address'],
                        'city' => $row['city'],
                        'state' => $row['state'],
                        'country' => $row['country'] ?? 'India',
                        'pincode' => $row['pincode'],
                        'gst_number' => $row['gst_number'],
                        'pan_number' => $row['pan_number'],
                        'credit_limit' => 0,
                        'credit_days' => 0,
                        'opening_balance' => 0,
                        'balance_type' => 'Dr',
                        'status' => $row['status'] === 'active',
                        'remarks' => $row['remarks'],
                    ]);

                    $customer->stores()->syncWithoutDetaching([
                        $storeId,
                    ]);

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
            '%d customers created, %d attached to this branch and %d existing rows skipped.',
            $counts['created'],
            $counts['attached'],
            $counts['skipped']
        );

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'summary' => $counts,
                'redirect' => route('customers.index'),
            ]);
        }

        return redirect()
            ->route('customers.index')
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
        $sheet = $spreadsheet->getSheet(0);
        $rawRows = $sheet->toArray(null, true, true, false);
        $spreadsheet->disconnectWorksheets();

        if ($rawRows === []) {
            throw ValidationException::withMessages([
                'file' => 'The uploaded customer file is empty.',
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
                    . ' customer rows can be imported at one time.',
            ]);
        }

        if ($rows === []) {
            throw ValidationException::withMessages([
                'file' => 'No customer rows were found in the uploaded file.',
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
        $fileMobiles = [];
        $fileCodes = [];

        foreach ($rows as $source) {
            $rowNumber = (int) $source['_row'];
            $row = $this->normaliseRow($source);

            $validator = Validator::make($row, [
                'customer_name' => [
                    'required',
                    'string',
                    'max:255',
                ],
                'mobile' => [
                    'required',
                    'regex:/^[6-9][0-9]{9}$/',
                ],
                'customer_code' => [
                    'nullable',
                    'string',
                    'max:30',
                ],
                'contact_person' => [
                    'nullable',
                    'string',
                    'max:255',
                ],
                'phone' => [
                    'nullable',
                    'string',
                    'max:30',
                ],
                'email' => [
                    'nullable',
                    'email',
                    'max:255',
                ],
                'billing_address' => [
                    'nullable',
                    'string',
                ],
                'shipping_address' => [
                    'nullable',
                    'string',
                ],
                'city' => [
                    'nullable',
                    'string',
                    'max:255',
                ],
                'state' => [
                    'nullable',
                    'string',
                    'max:255',
                ],
                'country' => [
                    'required',
                    'string',
                    'max:255',
                ],
                'pincode' => [
                    'nullable',
                    'regex:/^[0-9]{6}$/',
                ],
                'gst_number' => [
                    'nullable',
                    'regex:/^[0-9]{2}[A-Z]{5}[0-9]{4}[A-Z][A-Z0-9]Z[A-Z0-9]$/',
                ],
                'pan_number' => [
                    'nullable',
                    'regex:/^[A-Z]{5}[0-9]{4}[A-Z]$/',
                ],
                'remarks' => [
                    'nullable',
                    'string',
                ],
                'status' => [
                    'required',
                    'in:active,inactive,1,0,yes,no,true,false',
                ],
            ], [
                'mobile.regex' =>
                'Mobile must be a valid 10-digit Indian mobile number.',
                'pincode.regex' =>
                'Pincode must contain exactly 6 digits.',
                'gst_number.regex' =>
                'GST number format is invalid.',
                'pan_number.regex' =>
                'PAN number format is invalid.',
            ]);

            $rowErrors = $validator->errors()->all();

            if (isset($fileMobiles[$row['mobile']])) {
                $rowErrors[] =
                    'The same mobile number is repeated in this import file.';
            }

            $fileMobiles[$row['mobile']] = true;

            if ($row['customer_code'] !== null) {
                $codeKey = Str::lower($row['customer_code']);

                if (isset($fileCodes[$codeKey])) {
                    $rowErrors[] =
                        'The same customer code is repeated in this import file.';
                }

                $fileCodes[$codeKey] = true;
            }

            $existing = Customer::query()
                ->where('company_id', $companyId)
                ->where('mobile', $row['mobile'])
                ->withExists([
                    'stores as attached_to_current_store' =>
                    fn($query) => $query->where('stores.id', $storeId),
                ])
                ->first();

            if (
                !$existing
                &&
                $row['customer_code'] !== null
            ) {
                $codeExists = Customer::query()
                    ->where('company_id', $companyId)
                    ->where('customer_code', $row['customer_code'])
                    ->exists();

                if ($codeExists) {
                    $rowErrors[] =
                        'Customer code already belongs to another customer in this company.';
                }
            }

            $action = match (true) {
                $rowErrors !== [] => 'invalid',
                !$existing => 'create',
                (bool) $existing->attached_to_current_store => 'skip',
                default => 'attach',
            };

            $actionMessage = match ($action) {
                'create' => 'Create new customer',
                'attach' => 'Attach existing company customer to this branch',
                'skip' => 'Already available in this branch',
                default => 'Correction required',
            };

            $previewRow = [
                'row' => $rowNumber,
                'customer_name' => $row['customer_name'],
                'mobile' => $row['mobile'],
                'customer_code' => $existing?->customer_code
                    ?? $row['customer_code'],
                'city' => $row['city'],
                'action' => $action,
                'action_message' => $actionMessage,
                'errors' => array_values(array_unique($rowErrors)),
            ];

            $preview[] = $previewRow;

            if ($rowErrors !== []) {
                $errors[] = [
                    'row' => $rowNumber,
                    'customer_name' => $row['customer_name'],
                    'mobile' => $row['mobile'],
                    'messages' => array_values(array_unique($rowErrors)),
                ];
                continue;
            }

            if ($action === 'create') {
                $create[] = $row;
                continue;
            }

            $row['existing_customer_id'] = (int) $existing->id;

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

    private function normaliseRow(array $source): array
    {
        $row = [];

        foreach (self::HEADERS as $header) {
            $value = $source[$header] ?? null;
            $value = is_string($value) ? trim($value) : $value;
            $row[$header] = $value === '' ? null : $value;
        }

        $row['customer_name'] =
            trim((string) ($row['customer_name'] ?? ''));
        $row['mobile'] =
            $this->normaliseMobile($row['mobile']);
        $row['customer_code'] =
            $row['customer_code'] === null
            ? null
            : strtoupper((string) $row['customer_code']);
        $row['gst_number'] =
            $row['gst_number'] === null
            ? null
            : strtoupper((string) $row['gst_number']);
        $row['pan_number'] =
            $row['pan_number'] === null
            ? null
            : strtoupper((string) $row['pan_number']);
        $row['pincode'] =
            $row['pincode'] === null
            ? null
            : preg_replace('/\D+/', '', (string) $row['pincode']);
        $row['country'] =
            $row['country'] ?? 'India';
        $status =
            Str::lower((string) ($row['status'] ?? 'active'));
        $row['status'] =
            in_array(
                $status,
                ['active', '1', 'yes', 'true'],
                true
            )
            ? 'active'
            : 'inactive';

        return $row;
    }

    private function normaliseMobile(mixed $value): string
    {
        $digits = preg_replace('/\D+/', '', (string) $value);

        if (
            strlen($digits) === 12
            &&
            str_starts_with($digits, '91')
        ) {
            return substr($digits, 2);
        }

        if (
            strlen($digits) === 11
            &&
            str_starts_with($digits, '0')
        ) {
            return substr($digits, 1);
        }

        return $digits;
    }

    private function rowIsEmpty(array $row): bool
    {
        foreach ($row as $value) {
            if (
                $value !== null
                &&
                trim((string) $value) !== ''
            ) {
                return false;
            }
        }

        return true;
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
