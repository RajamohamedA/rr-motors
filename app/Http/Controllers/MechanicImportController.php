<?php

namespace App\Http\Controllers;

use App\Models\Mechanic;
use App\Models\Store;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
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

class MechanicImportController extends Controller implements HasMiddleware
{
    private const MAX_ROWS = 3000;

    private const HEADERS = [
        'mechanic_name',
        'mobile',
        'mechanic_code',
        'email',
        'address',
        'city',
        'state',
        'pincode',
        'status',
    ];

    public static function middleware(): array
    {
        return [
            new Middleware(
                'permission:mechanic.create',
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

        return Inertia::render('Mechanics/Import', [
            'template_url' => route('mechanics.import.template'),
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
        $sheet->setTitle('Mechanics');

        foreach (self::HEADERS as $index => $header) {
            $sheet->setCellValue([$index + 1, 1], $header);
        }

        $example = [
            'Sample Mechanic',
            '9876543210',
            '',
            'mechanic@example.com',
            'Door No, Street',
            'Thiruthuraipoondi',
            'Tamil Nadu',
            '614713',
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
            ->setARGB('FF365F8B');
        $sheet->getStyle('A1:B1')
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB('FF18795B');

        foreach (range('A', $lastColumn) as $column) {
            $sheet->getColumnDimension($column)->setAutoSize(true);
        }

        $instructions = $spreadsheet->createSheet();
        $instructions->setTitle('Instructions');
        $instructions->fromArray([
            ['MECHANIC IMPORT INSTRUCTIONS'],
            ['Required', 'mechanic_name'],
            ['Recommended', 'Enter mobile number for reliable duplicate detection.'],
            ['Duplicate priority', 'Mobile, mechanic code, then mechanic name with city.'],
            ['Existing company mechanic', 'The mechanic is attached to the active branch.'],
            ['Already in branch', 'The row is safely skipped.'],
            ['Mechanic code', 'Optional. Blank values generate MEC00001 style codes.'],
            ['Status', 'Use active or inactive. Blank status defaults to active.'],
            ['Maximum rows', (string) self::MAX_ROWS],
            ['Important', 'Do not rename or remove columns from the Mechanics sheet.'],
        ]);
        $instructions->getColumnDimension('A')->setWidth(24);
        $instructions->getColumnDimension('B')->setWidth(100);
        $instructions->getStyle('A1:B1')
            ->getFont()
            ->setBold(true)
            ->getColor()
            ->setARGB('FFFFFFFF');
        $instructions->getStyle('A1:B1')
            ->getFill()
            ->setFillType(Fill::FILL_SOLID)
            ->getStartColor()
            ->setARGB('FF365F8B');

        return response()->streamDownload(
            function () use ($spreadsheet): void {
                $writer = new Xlsx($spreadsheet);
                $writer->setPreCalculateFormulas(false);
                $writer->save('php://output');
                $spreadsheet->disconnectWorksheets();
            },
            'mechanic_import_template.xlsx',
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
                ? 'The mechanic file is ready to import.'
                : 'Please correct the invalid mechanic rows before importing.',
            'summary' => [
                'total_rows' => count($result['rows']),
                'new_mechanics' => count($result['create']),
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
                    'message' => 'The mechanic import contains invalid rows.',
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

                $created = 0;
                $attached = 0;
                $skipped = count($result['skip']);

                foreach ($result['attach'] as $row) {
                    $mechanic = Mechanic::query()
                        ->where('company_id', $companyId)
                        ->whereKey($row['existing_mechanic_id'])
                        ->lockForUpdate()
                        ->firstOrFail();

                    $mechanic->stores()->syncWithoutDetaching([$storeId]);
                    $attached++;
                }

                foreach ($result['create'] as $row) {
                    $mechanicCode = $row['mechanic_code']
                        ?? $this->generateMechanicCode($companyId);

                    $mechanic = Mechanic::query()->create([
                        'company_id' => $companyId,
                        'mechanic_code' => $mechanicCode,
                        'mechanic_name' => $row['mechanic_name'],
                        'mobile' => $row['mobile'],
                        'email' => $row['email'],
                        'address' => $row['address'],
                        'city' => $row['city'],
                        'state' => $row['state'],
                        'pincode' => $row['pincode'],
                        'status' => $row['status'] === 'active',
                    ]);

                    $mechanic->stores()->syncWithoutDetaching([$storeId]);
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
            '%d mechanics created, %d attached to this branch and %d existing rows skipped.',
            $counts['created'],
            $counts['attached'],
            $counts['skipped']
        );

        if ($request->expectsJson()) {
            return response()->json([
                'message' => $message,
                'summary' => $counts,
                'redirect' => route('mechanics.index'),
            ]);
        }

        return redirect()
            ->route('mechanics.index')
            ->with('success', $message);
    }

    private function validatedFile(Request $request): UploadedFile
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
                'file' => 'The uploaded mechanic file is empty.',
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
                    . ' mechanic rows can be imported at one time.',
            ]);
        }

        if ($rows === []) {
            throw ValidationException::withMessages([
                'file' => 'No mechanic rows were found in the uploaded file.',
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
                'mechanic_name' => ['required', 'string', 'max:150'],
                'mobile' => ['nullable', 'regex:/^[6-9][0-9]{9}$/'],
                'mechanic_code' => ['nullable', 'string', 'max:30'],
                'email' => ['nullable', 'email', 'max:255'],
                'address' => ['nullable', 'string'],
                'city' => ['nullable', 'string', 'max:100'],
                'state' => ['nullable', 'string', 'max:100'],
                'pincode' => ['nullable', 'regex:/^[0-9]{6}$/'],
                'status' => ['required', 'in:active,inactive'],
            ], [
                'mobile.regex' =>
                'Mobile must be a valid 10-digit Indian mobile number.',
                'pincode.regex' => 'Pincode must contain exactly 6 digits.',
            ]);

            $rowErrors = $validator->errors()->all();
            $identity = $this->identity($row);

            if (isset($fileIdentities[$identity])) {
                $rowErrors[] =
                    'The same mechanic identity is repeated in this import file.';
            }
            $fileIdentities[$identity] = true;

            if ($row['mechanic_code'] !== null) {
                $codeKey = $this->key($row['mechanic_code']);

                if (isset($fileCodes[$codeKey])) {
                    $rowErrors[] =
                        'The same mechanic code is repeated in this import file.';
                }
                $fileCodes[$codeKey] = true;
            }

            $existing = $this->findExistingMechanic(
                $row,
                $companyId,
                $storeId
            );

            if (!$existing && $row['mechanic_code'] !== null) {
                $codeExists = Mechanic::query()
                    ->where('company_id', $companyId)
                    ->where('mechanic_code', $row['mechanic_code'])
                    ->exists();

                if ($codeExists) {
                    $rowErrors[] =
                        'Mechanic code already belongs to another mechanic in this company.';
                }
            }

            $action = match (true) {
                $rowErrors !== [] => 'invalid',
                !$existing => 'create',
                (bool) $existing->attached_to_current_store => 'skip',
                default => 'attach',
            };

            $actionMessage = match ($action) {
                'create' => 'Create new mechanic',
                'attach' => 'Attach existing company mechanic to this branch',
                'skip' => 'Already available in this branch',
                default => 'Correction required',
            };

            $preview[] = [
                'row' => $rowNumber,
                'mechanic_name' => $row['mechanic_name'],
                'mobile' => $row['mobile'],
                'mechanic_code' => $existing?->mechanic_code
                    ?? $row['mechanic_code'],
                'city' => $row['city'],
                'action' => $action,
                'action_message' => $actionMessage,
                'errors' => array_values(array_unique($rowErrors)),
            ];

            if ($rowErrors !== []) {
                $errors[] = [
                    'row' => $rowNumber,
                    'mechanic_name' => $row['mechanic_name'],
                    'mobile' => $row['mobile'],
                    'messages' => array_values(array_unique($rowErrors)),
                ];
                continue;
            }

            if ($action === 'create') {
                $create[] = $row;
                continue;
            }

            $row['existing_mechanic_id'] = (int) $existing->id;

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

    private function findExistingMechanic(
        array $row,
        int $companyId,
        int $storeId
    ): ?Mechanic {
        $query = Mechanic::query()
            ->where('company_id', $companyId)
            ->withExists([
                'stores as attached_to_current_store' =>
                fn($storeQuery) =>
                $storeQuery->where('stores.id', $storeId),
            ]);

        if ($row['mobile'] !== null) {
            return $query
                ->where('mobile', $row['mobile'])
                ->first();
        }

        if ($row['mechanic_code'] !== null) {
            return $query
                ->where('mechanic_code', $row['mechanic_code'])
                ->first();
        }

        return $query
            ->whereRaw(
                'LOWER(TRIM(mechanic_name)) = ?',
                [$this->key($row['mechanic_name'])]
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
            $row['mobile'] !== null => 'mobile|' . $row['mobile'],
            $row['mechanic_code'] !== null =>
            'code|' . $this->key($row['mechanic_code']),
            default =>
            'name|'
                . $this->key($row['mechanic_name'])
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

        $row['mechanic_name'] = trim((string) ($row['mechanic_name'] ?? ''));
        $row['mobile'] = $this->normalisePhone($row['mobile']);
        $row['mechanic_code'] = $row['mechanic_code'] === null
            ? null
            : strtoupper((string) $row['mechanic_code']);
        $row['pincode'] = $row['pincode'] === null
            ? null
            : preg_replace('/\D+/', '', (string) $row['pincode']);

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

    private function generateMechanicCode(int $companyId): string
    {
        $lastCode = Mechanic::query()
            ->where('company_id', $companyId)
            ->where('mechanic_code', 'like', 'MEC%')
            ->orderByDesc('id')
            ->lockForUpdate()
            ->value('mechanic_code');

        $nextNumber = 1;

        if ($lastCode) {
            $numericPart = preg_replace('/\D+/', '', (string) $lastCode);
            $nextNumber = ((int) $numericPart) + 1;
        }

        do {
            $mechanicCode = 'MEC' . str_pad(
                (string) $nextNumber,
                5,
                '0',
                STR_PAD_LEFT
            );

            $exists = Mechanic::query()
                ->where('company_id', $companyId)
                ->where('mechanic_code', $mechanicCode)
                ->exists();

            $nextNumber++;
        } while ($exists);

        return $mechanicCode;
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
