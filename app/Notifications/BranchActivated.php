<?php

namespace App\Notifications;

use App\Models\Company;
use App\Models\Store;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BranchActivated extends Notification
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
            'Branch Activated',

            'message' =>
            "Your branch {$this->store->store_name} has been activated and is ready to use.",

            'category' =>
            'branch',

            'level' =>
            'success',

            'icon' =>
            'bi-shop',

            'company_id' =>
            $this->company->id,

            'store_id' =>
            $this->store->id,

            'target_type' =>
            'branch',

            'target_id' =>
            $this->store->id,

            'fallback_url' =>
            '/company/branches',
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
