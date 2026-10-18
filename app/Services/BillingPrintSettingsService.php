<?php

namespace App\Services;

use App\Models\BranchBillingPrintSetting;
use App\Models\Company;
use App\Models\CompanyBillingPrintSetting;
use App\Models\Store;

class BillingPrintSettingsService
{
    public const DEFAULTS = [
        'default_billing_type' => 'non_gst',
        'fallback_gst_percent' => 18,
        'rounding_mode' => 'nearest_rupee',
        'default_paper' => 'a4',
        'quick_bill_width' => 80,
        'auto_print' => true,
        'show_company_email' => false,
        'footer_text' => 'Thank you for your business.',
        'quick_quantity_mode' => false,
        'shortcuts_expanded' => false,
    ];

    public function companyDefaults(int $companyId): array
    {
        abort_unless(Company::query()->whereKey($companyId)->where('status', true)->exists(), 403);

        $record = CompanyBillingPrintSetting::query()->where('company_id', $companyId)->first();

        return array_replace(self::DEFAULTS, $this->known($record?->settings ?? []));
    }

    public function branchOverrides(int $companyId, int $storeId): array
    {
        abort_unless(Store::query()->whereKey($storeId)
            ->where('company_id', $companyId)->where('status', true)->exists(), 403);

        $record = BranchBillingPrintSetting::query()->where('store_id', $storeId)->first();

        return $this->known($record?->overrides ?? []);
    }

    public function resolve(int $companyId, int $storeId): array
    {
        return array_replace(
            $this->companyDefaults($companyId),
            $this->branchOverrides($companyId, $storeId),
        );
    }

    private function known(array $values): array
    {
        return array_filter(
            array_intersect_key($values, self::DEFAULTS),
            static fn($value) => $value !== null,
        );
    }
}
