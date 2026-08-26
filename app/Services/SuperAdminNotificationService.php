<?php

namespace App\Services;

use App\Models\User;
use App\Notifications\SuperAdminAlert;

class SuperAdminNotificationService
{
    /*
    |--------------------------------------------------------------------------
    | Notify Super Administrators
    |--------------------------------------------------------------------------
    */

    public static function send(
        string $title,
        string $message,
        string $category = 'system',
        string $icon = 'bi-bell',
        ?string $targetType = null,
        int|string|null $targetId = null,
        ?string $fallbackUrl = null,
        string $level = 'info',
    ): void {

        /*
        |--------------------------------------------------------------------------
        | Get Active Super Administrators
        |--------------------------------------------------------------------------
        */

        $superAdmins = User::query()
            ->where(
                'is_super_user',
                true
            )
            ->where(
                'status',
                true
            )
            ->get();


        /*
        |--------------------------------------------------------------------------
        | Send Notifications
        |--------------------------------------------------------------------------
        */

        foreach (
            $superAdmins as $admin
        ) {

            $admin->notify(
                new SuperAdminAlert(
                    title: $title,
                    message: $message,
                    category: $category,
                    icon: $icon,
                    targetType: $targetType,
                    targetId: $targetId,
                    fallbackUrl: $fallbackUrl,
                    level: $level,
                )
            );
        }
    }
}
