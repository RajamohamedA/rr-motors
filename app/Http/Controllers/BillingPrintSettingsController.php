<?php

namespace App\Http\Controllers;

use App\Models\BranchBillingPrintSetting;
use App\Models\Company;
use App\Models\CompanyBillingPrintSetting;
use App\Models\Store;
use App\Services\BillingPrintSettingsService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class BillingPrintSettingsController extends Controller
{
    public function __construct(private readonly BillingPrintSettingsService $settings) {}

    public function company(Request $request): Response
    {
        [$company, $store] = $this->context($request);
        $this->authorizeSettings($request, true, false);

        return $this->page($request, $company, $store, 'company');
    }

    public function edit(Request $request): Response
    {
        [$company, $store] = $this->context($request);
        $this->authorizeSettings($request, false, false);

        return $this->page($request, $company, $store, 'branch');
    }

    public function updateCompany(Request $request): RedirectResponse
    {
        [$company] = $this->context($request);
        $this->authorizeSettings($request, true, true);
        $validated = $request->validate($this->rules(false));

        CompanyBillingPrintSetting::query()->updateOrCreate(
            ['company_id' => $company->id],
            [
                'settings' => array_replace($validated['settings'], ['footer_text' => $validated['settings']['footer_text'] ?? '']),
                'updated_by' => $request->user()->id,
            ],
        );

        return back()->with('success', 'Company billing and print defaults saved.');
    }

    public function update(Request $request): RedirectResponse
    {
        [$company, $store] = $this->context($request);
        $this->authorizeSettings($request, false, true);
        $validated = $request->validate($this->rules(true));

        // This endpoint replaces the complete overrides map. Omitted keys inherit.
        // An empty object/array removes all overrides. False and zero are retained.
        BranchBillingPrintSetting::query()->updateOrCreate(
            ['store_id' => $store->id],
            [
                'overrides' => array_map(static fn($value) => $value === null ? '' : $value, $validated['settings']),
                'updated_by' => $request->user()->id,
            ],
        );

        return back()->with('success', 'Branch billing and print settings saved.');
    }

    private function page(Request $request, Company $company, Store $store, string $scope): Response
    {
        $companySettings = $this->settings->companyDefaults($company->id);
        $overrides = $this->settings->branchOverrides($company->id, $store->id);
        $admin = $this->isAdministrator($request);

        return Inertia::render('Settings/BillingPrint', [
            'scope' => $scope,
            'company' => $company->only(['id', 'company_name', 'company_code']),
            'store' => $store->only(['id', 'store_name', 'store_code']),
            'defaults' => BillingPrintSettingsService::DEFAULTS,
            'companySettings' => $companySettings,
            'branchOverrides' => $overrides,
            'effectiveSettings' => array_replace($companySettings, $overrides),
            'canManageCompany' => $admin,
            'canEdit' => $admin || ($scope === 'branch' && $request->user()->can('settings.edit')),
        ]);
    }

    private function rules(bool $branch): array
    {
        $presence = $branch ? 'sometimes' : 'required';

        return [
            'company_id' => ['prohibited'],
            'store_id' => ['prohibited'],
            'updated_by' => ['prohibited'],
            'settings' => ['present', 'array:' . implode(',', array_keys(BillingPrintSettingsService::DEFAULTS))],
            'settings.default_billing_type' => [$presence, Rule::in(['gst', 'non_gst'])],
            'settings.fallback_gst_percent' => [$presence, 'numeric', 'between:0,100'],
            'settings.rounding_mode' => [$presence, Rule::in(['none', 'nearest_rupee'])],
            'settings.default_paper' => [$presence, Rule::in(['a4', 'a5', 'quick'])],
            'settings.quick_bill_width' => [$presence, 'integer', Rule::in([58, 80])],
            'settings.auto_print' => [$presence, 'boolean'],
            'settings.show_company_email' => [$presence, 'boolean'],
            'settings.footer_text' => [$branch ? 'sometimes' : 'present', 'nullable', 'string', 'max:300'],
            'settings.quick_quantity_mode' => [$presence, 'boolean'],
            'settings.shortcuts_expanded' => [$presence, 'boolean'],
        ];
    }

    private function isAdministrator(Request $request): bool
    {
        return (bool) $request->user()->is_super_user
            || $request->user()->hasRole('Company Admin');
    }

    private function authorizeSettings(Request $request, bool $companyScope, bool $editing): void
    {
        if ($this->isAdministrator($request)) return;

        abort_if($companyScope, 403, 'Company settings require Company Admin access.');
        abort_unless(
            $request->user()->can($editing ? 'settings.edit' : 'settings.view'),
            403,
            'You do not have permission to access these settings.',
        );
    }

    private function context(Request $request): array
    {
        $user = $request->user();
        abort_unless($user && (bool) $user->status, 403);

        $companyId = (int) $request->session()->get('active_company_id');
        $storeId = (int) $request->session()->get('active_store_id');
        abort_unless($companyId > 0 && $storeId > 0, 403, 'No active business branch selected.');

        $company = Company::query()->whereKey($companyId)->where('status', true)->first();
        abort_unless($company, 403, 'The active company is unavailable.');

        $store = Store::query()->whereKey($storeId)
            ->where('company_id', $companyId)->where('status', true)->first();
        abort_unless($store, 403, 'The active branch is unavailable.');

        if (!$user->is_super_user) {
            abort_unless((int) $user->company_id === $companyId, 403);
            if (!$user->hasRole('Company Admin')) {
                abort_unless($user->stores()->where('stores.id', $storeId)
                    ->where('stores.status', true)->exists(), 403);
            }
        }

        return [$company, $store];
    }
}
