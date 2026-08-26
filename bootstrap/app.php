<?php

use App\Http\Middleware\HandleInertiaRequests;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;
use App\Http\Middleware\SuperAdminMiddleware;

return Application::configure(
    basePath: dirname(__DIR__)
)
    ->withRouting(

        web: __DIR__ . '/../routes/web.php',

        commands: __DIR__ . '/../routes/console.php',

        health: '/up',

    )

    ->withMiddleware(function (Middleware $middleware): void {

        /*
        |--------------------------------------------------------------------------
        | Inertia Web Middleware
        |--------------------------------------------------------------------------
        */

        $middleware->web(
            append: [
                HandleInertiaRequests::class,
            ]
        );


        /*
        |--------------------------------------------------------------------------
        | Middleware Aliases
        |--------------------------------------------------------------------------
        */

        $middleware->alias([

            'permission' =>
            PermissionMiddleware::class,

            'role' =>
            RoleMiddleware::class,

            'role_or_permission' =>
            RoleOrPermissionMiddleware::class,

            'active.branch' =>
            \App\Http\Middleware\ActiveBranch::class,

            'super.admin' =>
            SuperAdminMiddleware::class,

        ]);
    })

    ->withExceptions(function (Exceptions $exceptions): void {

        $exceptions->shouldRenderJsonWhen(
            fn(Request $request) =>
            $request->is('api/*'),
        );
    })

    ->create();
