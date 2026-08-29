<?php

namespace App\Http\Controllers\Business;

use App\Http\Controllers\Controller;
use App\Support\AppResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Notification List
    |--------------------------------------------------------------------------
    */

    public function index(
        Request $request
    ): Response {

        $user =
            $request->user();


        abort_unless(
            $user,
            401
        );


        /*
        |--------------------------------------------------------------------------
        | Notifications
        |--------------------------------------------------------------------------
        */

        $notifications =
            $user
            ->notifications()
            ->latest()
            ->paginate(
                20
            );


        /*
        |--------------------------------------------------------------------------
        | Transform
        |--------------------------------------------------------------------------
        */

        $notifications
            ->getCollection()
            ->transform(
                function (
                    $notification
                ) {

                    $data =
                        $notification->data
                        ?? [];


                    return [

                        'id' =>
                        $notification->id,

                        'title' =>
                        $data['title']
                            ?? 'Notification',

                        'message' =>
                        $data['message']
                            ?? '',

                        'category' =>
                        $data['category']
                            ?? 'system',

                        'level' =>
                        $data['level']
                            ?? 'info',

                        'icon' =>
                        $data['icon']
                            ?? 'bi-bell',

                        'company_id' =>
                        $data['company_id']
                            ?? null,

                        'store_id' =>
                        $data['store_id']
                            ?? null,

                        'target_type' =>
                        $data['target_type']
                            ?? null,

                        'target_id' =>
                        $data['target_id']
                            ?? null,

                        'fallback_url' =>
                        $data['fallback_url']
                            ?? $data['url']
                            ?? null,

                        'read' =>
                        $notification
                            ->read_at
                            !== null,

                        'read_at' =>
                        $notification
                            ->read_at
                            ?->toISOString(),

                        'created_at' =>
                        $notification
                            ->created_at
                            ?->toISOString(),

                        'created_at_human' =>
                        $notification
                            ->created_at
                            ?->diffForHumans(),
                    ];
                }
            );


        /*
        |--------------------------------------------------------------------------
        | Summary
        |--------------------------------------------------------------------------
        */

        $unreadCount =
            $user
            ->unreadNotifications()
            ->count();


        $totalCount =
            $user
            ->notifications()
            ->count();


        /*
        |--------------------------------------------------------------------------
        | Response
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'Business/Notifications/Index',
            [
                'notificationPage' =>
                $notifications,

                'summary' => [

                    'total' =>
                    $totalCount,

                    'unread' =>
                    $unreadCount,

                    'read' =>
                    max(
                        0,
                        $totalCount -
                            $unreadCount
                    ),
                ],
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Mark One As Read
    |--------------------------------------------------------------------------
    */

    public function markAsRead(
        Request $request,
        string $notification
    ): RedirectResponse {

        $user =
            $request->user();


        abort_unless(
            $user,
            401
        );


        /*
        |--------------------------------------------------------------------------
        | Security
        |--------------------------------------------------------------------------
        |
        | Search only inside the logged-in user's notifications.
        |
        */

        $notificationRecord =
            $user
            ->notifications()
            ->where(
                'id',
                $notification
            )
            ->first();


        if (
            !$notificationRecord
        ) {

            abort(
                404,
                'Notification not found.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Mark Read
        |--------------------------------------------------------------------------
        */

        if (
            !$notificationRecord
                ->read_at
        ) {

            $notificationRecord
                ->markAsRead();
        }


        return AppResponse::success(
            'Notification marked as read.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Mark All As Read
    |--------------------------------------------------------------------------
    */

    public function markAllAsRead(
        Request $request
    ): RedirectResponse {

        $user =
            $request->user();


        abort_unless(
            $user,
            401
        );


        /*
        |--------------------------------------------------------------------------
        | Mark All
        |--------------------------------------------------------------------------
        */

        $user
            ->unreadNotifications
            ->markAsRead();


        return AppResponse::success(
            'All notifications marked as read.'
        );
    }
}
