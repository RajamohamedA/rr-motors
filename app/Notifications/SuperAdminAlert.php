<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class SuperAdminAlert extends Notification
{
    use Queueable;


    public function __construct(
        private string $title,
        private string $message,
        private string $category = 'system',
        private string $icon = 'bi-bell',
        private ?string $targetType = null,
        private int|string|null $targetId = null,
        private ?string $fallbackUrl = null,
        private string $level = 'info',
    ) {}


    /*
    |--------------------------------------------------------------------------
    | Delivery Channels
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
            $this->title,

            'message' =>
            $this->message,

            'category' =>
            $this->category,

            'icon' =>
            $this->icon,

            'target_type' =>
            $this->targetType,

            'target_id' =>
            $this->targetId,

            'fallback_url' =>
            $this->fallbackUrl,

            'level' =>
            $this->level,

        ];
    }
}
