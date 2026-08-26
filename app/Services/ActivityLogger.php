<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;

class ActivityLogger
{
    public static function log(
        string $module,
        string $action,
        string $description,
        ?Model $subject = null,
        ?array $oldValues = null,
        ?array $newValues = null
    ): ActivityLog {

        return ActivityLog::create([
            'user_id' => auth()->id(),

            'module' => strtolower(
                trim($module)
            ),

            'action' => strtolower(
                trim($action)
            ),

            'description' => $description,

            'subject_type' =>
            $subject
                ? $subject::class
                : null,

            'subject_id' =>
            $subject?->getKey(),

            'ip_address' =>
            request()->ip(),

            'user_agent' =>
            request()->userAgent(),

            'old_values' =>
            self::sanitize(
                $oldValues
            ),

            'new_values' =>
            self::sanitize(
                $newValues
            ),
        ]);
    }


    private static function sanitize(
        ?array $values
    ): ?array {

        if ($values === null) {
            return null;
        }

        $blocked = [
            'password',
            'password_confirmation',
            'remember_token',
            'token',
            'access_token',
            'refresh_token',
        ];

        foreach ($blocked as $key) {
            unset(
                $values[$key]
            );
        }

        return $values;
    }
}
