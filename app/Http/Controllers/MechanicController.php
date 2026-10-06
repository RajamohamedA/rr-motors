<?php

namespace App\Http\Controllers;

use App\Models\Mechanic;
use App\Models\MechanicLedgerEntry;
use App\Models\Store;
use App\Support\AppResponse;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class MechanicController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(
                'permission:mechanic.view',
                only: ['index']
            ),
            new Middleware(
                'permission:mechanic.show',
                only: ['show']
            ),
            new Middleware(
                'permission:mechanic.create',
                only: ['create', 'store']
            ),
            new Middleware(
                'permission:mechanic.edit',
                only: ['edit', 'update', 'toggleStatus']
            ),
            new Middleware(
                'permission:mechanic.delete',
                only: ['removeFromBranch', 'destroy']
            ),
        ];
    }

    public function index(Request $request): Response
    {
        [$companyId, $storeId] = $this->context();

        $filters = $request->validate([
            'search' => ['nullable', 'string', 'max:100'],
            'status' => ['nullable', 'in:active,inactive'],
            'per_page' => ['nullable', 'integer', 'in:20,50,100'],
        ]);

        $search = trim((string) ($filters['search'] ?? ''));
        $status = (string) ($filters['status'] ?? '');
        $perPage = (int) ($filters['per_page'] ?? 20);

        $mechanics = Mechanic::query()
            ->forContext($companyId, $storeId)
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner
                        ->where('mechanic_name', 'like', "%{$search}%")
                        ->orWhere('mechanic_code', 'like', "%{$search}%")
                        ->orWhere('mobile', 'like', "%{$search}%")
                        ->orWhere('city', 'like', "%{$search}%");
                });
            })
            ->when(
                $status === 'active',
                fn(Builder $query) => $query->where('status', true)
            )
            ->when(
                $status === 'inactive',
                fn(Builder $query) => $query->where('status', false)
            )
            ->withCount([
                'commissions as pending_commissions_count' => fn(Builder $query) =>
                $query->whereIn('status', ['Pending', 'Partially Paid']),
            ])
            ->withSum([
                'ledgerEntries as ledger_credit' => fn(Builder $query) =>
                $query->where('company_id', $companyId)
                    ->where('store_id', $storeId),
            ], 'credit')
            ->withSum([
                'ledgerEntries as ledger_debit' => fn(Builder $query) =>
                $query->where('company_id', $companyId)
                    ->where('store_id', $storeId),
            ], 'debit')
            ->orderBy('mechanic_name')
            ->paginate($perPage)
            ->withQueryString()
            ->through(fn(Mechanic $mechanic): array => $this->mechanicData(
                $mechanic,
                (float) ($mechanic->ledger_credit ?? 0)
                    - (float) ($mechanic->ledger_debit ?? 0)
            ));

        $summary = [
            'total' => Mechanic::query()
                ->forContext($companyId, $storeId)
                ->count(),
            'active' => Mechanic::query()
                ->forContext($companyId, $storeId)
                ->active()
                ->count(),
            'inactive' => Mechanic::query()
                ->forContext($companyId, $storeId)
                ->where('status', false)
                ->count(),
            'total_payable' => round((float) MechanicLedgerEntry::query()
                ->forContext($companyId, $storeId)
                ->selectRaw('COALESCE(SUM(credit - debit), 0) AS balance')
                ->value('balance'), 2),
        ];

        return Inertia::render('Mechanics/Index', [
            'mechanics' => $mechanics,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'per_page' => $perPage,
            ],
            'summary' => $summary,
        ]);
    }

    public function create(): Response
    {
        [$companyId, $storeId] = $this->context();

        return Inertia::render('Mechanics/Create', [
            'mechanic' => null,
            'next_code' => $this->generateMechanicCode($companyId),
            'context' => $this->contextData($companyId, $storeId),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        [$companyId, $storeId] = $this->context();
        $validated = $this->validatedData($request, $companyId);

        try {
            $mechanic = DB::transaction(function () use (
                $validated,
                $companyId,
                $storeId
            ): Mechanic {
                $mechanic = Mechanic::query()->create([
                    ...$validated,
                    'company_id' => $companyId,
                    'mechanic_code' => $validated['mechanic_code']
                        ?: $this->generateMechanicCode($companyId),
                ]);

                $mechanic->stores()->syncWithoutDetaching([$storeId]);

                return $mechanic;
            });
        } catch (QueryException $exception) {
            if (($exception->errorInfo[1] ?? null) === 1062) {
                return AppResponse::warning(
                    'A mechanic with the same code already exists in this company.'
                );
            }

            report($exception);

            return AppResponse::error(
                'Mechanic could not be created. Please try again.'
            );
        }

        return AppResponse::successRoute(
            'mechanics.show',
            'Mechanic created successfully.',
            $mechanic
        );
    }

    public function show(Mechanic $mechanic): Response
    {
        [$companyId, $storeId] = $this->context();
        $this->ensureAvailable($mechanic, $companyId, $storeId);

        $mechanic->loadCount([
            'sales',
            'commissions',
            'payments',
            'ledgerEntries',
        ]);

        $currentBalance = $this->currentBalance(
            $companyId,
            $storeId,
            $mechanic->id
        );

        $recentEntries = $mechanic->ledgerEntries()
            ->forContext($companyId, $storeId)
            ->latest('transaction_date')
            ->latest('id')
            ->limit(10)
            ->get()
            ->map(fn(MechanicLedgerEntry $entry): array => [
                'id' => $entry->id,
                'date' => $entry->transaction_date?->format('Y-m-d'),
                'type' => ucwords(str_replace('_', ' ', $entry->transaction_type)),
                'reference_no' => $entry->reference_no,
                'debit' => (float) $entry->debit,
                'credit' => (float) $entry->credit,
                'remarks' => $entry->remarks,
            ]);

        return Inertia::render('Mechanics/Show', [
            'mechanic' => [
                ...$this->mechanicData($mechanic, $currentBalance),
                'email' => $mechanic->email,
                'address' => $mechanic->address,
                'city' => $mechanic->city,
                'state' => $mechanic->state,
                'pincode' => $mechanic->pincode,
                'sales_count' => $mechanic->sales_count,
                'commissions_count' => $mechanic->commissions_count,
                'payments_count' => $mechanic->payments_count,
                'ledger_entries_count' => $mechanic->ledger_entries_count,
            ],
            'recent_entries' => $recentEntries,
        ]);
    }

    public function edit(Mechanic $mechanic): Response
    {
        [$companyId, $storeId] = $this->context();
        $this->ensureAvailable($mechanic, $companyId, $storeId);

        return Inertia::render('Mechanics/Edit', [
            'mechanic' => [
                'id' => $mechanic->id,
                'mechanic_code' => $mechanic->mechanic_code,
                'mechanic_name' => $mechanic->mechanic_name,
                'mobile' => $mechanic->mobile,
                'email' => $mechanic->email,
                'address' => $mechanic->address,
                'city' => $mechanic->city,
                'state' => $mechanic->state,
                'pincode' => $mechanic->pincode,
                'status' => (bool) $mechanic->status,
            ],
            'context' => $this->contextData($companyId, $storeId),
        ]);
    }

    public function update(
        Request $request,
        Mechanic $mechanic
    ): RedirectResponse {
        [$companyId, $storeId] = $this->context();
        $this->ensureAvailable($mechanic, $companyId, $storeId);
        $validated = $this->validatedData(
            $request,
            $companyId,
            $mechanic->id
        );

        try {
            $mechanic->update($validated);
        } catch (QueryException $exception) {
            if (($exception->errorInfo[1] ?? null) === 1062) {
                return AppResponse::warning(
                    'Another mechanic already uses this mechanic code.'
                );
            }

            report($exception);

            return AppResponse::error(
                'Mechanic could not be updated. Please try again.'
            );
        }

        return AppResponse::successRoute(
            'mechanics.show',
            'Mechanic updated successfully.',
            $mechanic
        );
    }

    public function toggleStatus(Mechanic $mechanic): RedirectResponse
    {
        [$companyId, $storeId] = $this->context();
        $this->ensureAvailable($mechanic, $companyId, $storeId);

        $mechanic->update(['status' => !$mechanic->status]);

        return AppResponse::success(
            $mechanic->status
                ? 'Mechanic activated successfully.'
                : 'Mechanic deactivated successfully.'
        );
    }

    public function removeFromBranch(Mechanic $mechanic): RedirectResponse
    {
        [$companyId, $storeId] = $this->context();
        $this->ensureAvailable($mechanic, $companyId, $storeId);

        $branchHasHistory = $mechanic->ledgerEntries()
            ->forContext($companyId, $storeId)
            ->exists()
            || $mechanic->commissions()
            ->where('store_id', $storeId)
            ->exists()
            || $mechanic->payments()
            ->where('store_id', $storeId)
            ->exists();

        if ($branchHasHistory) {
            return AppResponse::error(
                'This mechanic has accounting history in this branch. Deactivate the mechanic instead.'
            );
        }

        $mechanic->stores()->detach($storeId);

        return AppResponse::successRoute(
            'mechanics.index',
            'Mechanic removed from this branch successfully.'
        );
    }

    public function destroy(Mechanic $mechanic): RedirectResponse
    {
        [$companyId, $storeId] = $this->context();
        $this->ensureAvailable($mechanic, $companyId, $storeId);

        if ($mechanic->hasAccountingHistory()) {
            return AppResponse::error(
                'This mechanic has sales or accounting history and cannot be permanently deleted. Deactivate the mechanic instead.'
            );
        }

        DB::transaction(function () use ($mechanic): void {
            $mechanic->stores()->detach();
            $mechanic->delete();
        });

        return AppResponse::successRoute(
            'mechanics.index',
            'Mechanic deleted successfully.'
        );
    }

    private function validatedData(
        Request $request,
        int $companyId,
        ?int $ignoreId = null
    ): array {
        return $request->validate([
            'mechanic_code' => [
                'nullable',
                'string',
                'max:30',
                Rule::unique('mechanics', 'mechanic_code')
                    ->where(fn($query) => $query->where(
                        'company_id',
                        $companyId
                    ))
                    ->ignore($ignoreId),
            ],
            'mechanic_name' => ['required', 'string', 'max:150'],
            'mobile' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['nullable', 'string', 'max:1000'],
            'city' => ['nullable', 'string', 'max:100'],
            'state' => ['nullable', 'string', 'max:100'],
            'pincode' => ['nullable', 'string', 'max:20'],
            'status' => ['nullable', 'boolean'],
        ]);
    }

    private function generateMechanicCode(int $companyId): string
    {
        $lastCode = Mechanic::query()
            ->forCompany($companyId)
            ->where('mechanic_code', 'like', 'MEC%')
            ->orderByDesc('id')
            ->value('mechanic_code');

        $number = $lastCode
            ? ((int) preg_replace('/\D/', '', $lastCode)) + 1
            : 1;

        do {
            $code = 'MEC' . str_pad(
                (string) $number,
                5,
                '0',
                STR_PAD_LEFT
            );
            $number++;
        } while (
            Mechanic::query()
            ->forCompany($companyId)
            ->where('mechanic_code', $code)
            ->exists()
        );

        return $code;
    }

    private function mechanicData(
        Mechanic $mechanic,
        float $balance
    ): array {
        return [
            'id' => $mechanic->id,
            'mechanic_code' => $mechanic->mechanic_code,
            'mechanic_name' => $mechanic->mechanic_name,
            'mobile' => $mechanic->mobile,
            'city' => $mechanic->city,
            'status' => (bool) $mechanic->status,
            'pending_commissions_count' =>
            (int) ($mechanic->pending_commissions_count ?? 0),
            'current_balance' => round($balance, 2),
            'balance_label' => $this->balanceLabel($balance),
        ];
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

    private function balanceLabel(float $balance): string
    {
        if ($balance > 0.009) {
            return number_format(abs($balance), 2) . ' Cr';
        }

        if ($balance < -0.009) {
            return number_format(abs($balance), 2) . ' Dr';
        }

        return '0.00';
    }

    private function ensureAvailable(
        Mechanic $mechanic,
        int $companyId,
        int $storeId
    ): void {
        abort_unless(
            (int) $mechanic->company_id === $companyId
                && $mechanic->isAvailableInStore($storeId),
            404
        );
    }

    private function contextData(
        int $companyId,
        int $storeId
    ): array {
        $store = Store::query()
            ->whereKey($storeId)
            ->where('company_id', $companyId)
            ->firstOrFail();

        return [
            'company_id' => $companyId,
            'store_id' => $storeId,
            'store_name' => $store->store_name
                ?? $store->name
                ?? 'Branch',
        ];
    }

    private function context(): array
    {
        $companyId = (int) session('active_company_id');
        $storeId = (int) session('active_store_id');

        abort_if(
            !$companyId || !$storeId,
            403,
            'Select an active company and branch.'
        );

        $validStore = Store::query()
            ->whereKey($storeId)
            ->where('company_id', $companyId)
            ->where('status', true)
            ->exists();

        abort_unless($validStore, 403, 'The active branch is invalid.');

        return [$companyId, $storeId];
    }
}
