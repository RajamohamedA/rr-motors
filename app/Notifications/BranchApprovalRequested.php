<?php

namespace App\Notifications;

use App\Models\Company;
use App\Models\Store;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class BranchApprovalRequested extends Notification
{
    use Queueable;


    public function __construct(
        private readonly Company $company,
        private readonly Store $store
    ) {}


    /*
    |--------------------------------------------------------------------------
    | Channels
    |--------------------------------------------------------------------------
    */

    public function via(
        object $notifiable
    ): array {

        return [
            'database',
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Database Notification
    |--------------------------------------------------------------------------
    */

    public function toDatabase(
        object $notifiable
    ): array {

        return [

            'title' =>
            'Branch Approval Requested',

            'message' =>
            "{$this->company->company_name} created a new branch "
                . "{$this->store->store_name} "
                . "({$this->store->store_code}) and is waiting for approval.",

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

            /*
            |--------------------------------------------------------------------------
            | Super Admin Destination
            |--------------------------------------------------------------------------
            */

            'fallback_url' =>
            route(
                'super-admin.branches.index'
            ),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Array
    |--------------------------------------------------------------------------
    */

    public function toArray(
        object $notifiable
    ): array {

        return $this->toDatabase(
            $notifiable
        );
    }
}
