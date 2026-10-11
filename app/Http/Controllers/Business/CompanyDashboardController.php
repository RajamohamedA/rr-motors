<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CompanyDashboardController extends Controller
{
    public function index(Request $request): Response
    {
        /** @var User|null $user */
        $user = $request->user();

        abort_if(!$user, 401);

        $company = $this->resolveCompany($request);

        abort_if(
            !(bool) $company->status,
            403,
            $user->is_super_user
                ? 'The selected company is currently inactive.'
                : 'Your company is currently inactive.'
        );

        $storesQuery = Store::query()
            ->where('company_id', $company->id);

        $branchSummary = [
            'total' => (clone $storesQuery)->count(),
            'active' => (clone $storesQuery)
                ->where('status', true)
                ->count(),
            'pending' => (clone $storesQuery)
                ->where('status', false)
                ->count(),
        ];

        $usersQuery = User::query()
            ->where('company_id', $company->id)
            ->where('is_super_user', false);

        $userSummary = [
            'total' => (clone $usersQuery)->count(),
            'active' => (clone $usersQuery)
                ->where('status', true)
                ->count(),
            'inactive' => (clone $usersQuery)
                ->where('status', false)
                ->count(),
        ];

        $branches = Store::query()
            ->where('company_id', $company->id)
            ->orderByDesc('status')
            ->orderBy('store_name')
            ->get([
                'id',
                'store_code',
                'store_name',
                'city',
                'state',
                'status',
                'created_at',
            ])
            ->map(static fn(Store $store): array => [
                'id' => $store->id,
                'store_code' => $store->store_code,
                'store_name' => $store->store_name,
                'city' => $store->city,
                'state' => $store->state,
                'status' => (bool) $store->status,
                'status_label' => $store->status
                    ? 'Active'
                    : 'Pending Approval',
                'created_at' => $store->created_at?->format('d M Y'),
            ]);

        $activeBranch = $this->resolveActiveBranch(
            $company->id,
            (int) session('active_store_id')
        );

        return Inertia::render('Business/CompanyDashboard', [
            'context' => [
                'is_super_admin' => (bool) $user->is_super_user,
                'is_company_admin' => $this->isCompanyAdministrator($user),
                'is_super_admin_context' => (bool) session(
                    'super_admin_business_context',
                    false
                ),
            ],
            'company' => [
                'id' => $company->id,
                'company_name' => $company->company_name,
                'company_code' => $company->company_code,
                'owner_name' => $company->owner_name,
                'email' => $company->email,
                'phone' => $company->phone,
                'city' => $company->city,
                'state' => $company->state,
                'status' => (bool) $company->status,
            ],
            'activeBranch' => $activeBranch,
            'branchSummary' => $branchSummary,
            'userSummary' => $userSummary,
            'branches' => $branches,
        ]);
    }

    private function resolveCompany(Request $request): Company
    {
        /** @var User|null $user */
        $user = $request->user();

        abort_if(!$user, 401);

        if ((bool) $user->is_super_user) {
            $companyId = (int) session('active_company_id');

            abort_if(
                !$companyId,
                403,
                'No active company is selected.'
            );

            return Company::query()
                ->whereKey($companyId)
                ->firstOrFail();
        }

        abort_unless(
            $this->isCompanyAdministrator($user),
            403,
            'Only Company Admin can access the company dashboard.'
        );

        $companyId = (int) $user->company_id;

        abort_if(
            !$companyId,
            403,
            'No company is assigned to this account.'
        );

        return Company::query()
            ->whereKey($companyId)
            ->firstOrFail();
    }

    /**
     * Resolve role access without depending on Spatie's current/default guard.
     * This also supports older installations that used underscore role names.
     */
    private function isCompanyAdministrator(User $user): bool
    {
        if ((bool) $user->is_super_user) {
            return true;
        }

        $hasRole = $user->roles()
            ->whereIn('name', [
                'Company Admin',
                'Company Administrator',
                'Company Owner',
                'company_admin',
                'company-administrator',
                'company_owner',
            ])
            ->exists();

        if ($hasRole) {
            return true;
        }

        return $user->permissions()
            ->whereIn('name', [
                'company-dashboard.view',
                'company.dashboard.view',
            ])
            ->exists();
    }

    private function resolveActiveBranch(
        int $companyId,
        int $activeStoreId
    ): ?array {
        if (!$activeStoreId) {
            return null;
        }

        $store = Store::query()
            ->where('company_id', $companyId)
            ->whereKey($activeStoreId)
            ->first([
                'id',
                'store_code',
                'store_name',
                'city',
                'state',
                'status',
            ]);

        if (!$store) {
            return null;
        }

        return [
            'id' => $store->id,
            'store_code' => $store->store_code,
            'store_name' => $store->store_name,
            'city' => $store->city,
            'state' => $store->state,
            'status' => (bool) $store->status,
        ];
    }
}
