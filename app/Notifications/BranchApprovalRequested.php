<?php

namespace App\Notifications;

use App\Models\Company;
use App\Models\Store;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class BranchApprovalRequested extends Notification
{
    use Queueable;

    public function __construct(
        private readonly Company $company,
        private readonly Store $store
    ) {}

    public function via(
        object $notifiable
    ): array {
        return [
            'database',
        ];
    }

    public function toDatabase(
        object $notifiable
    ): array {
        return [
            'title' =>
            'Branch Approval Requested',

            'message' =>
            "{$this->company->company_name} created a new branch: {$this->store->store_name}.",

            'category' =>
            'branch',

            'level' =>
            'warning',

            'icon' =>
            'bi-shop-window',

            'company_id' =>
            $this->company->id,

            'store_id' =>
            $this->store->id,

            'target_type' =>
            'branch',

            'target_id' =>
            $this->store->id,

            'fallback_url' =>
            route(
                'super-admin.companies.show',
                $this->company->id
            ),
        ];
    }

    public function toArray(
        object $notifiable
    ): array {
        return $this->toDatabase(
            $notifiable
        );
    }
}
