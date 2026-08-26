<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\Company;
use App\Models\Store;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class ActivityLogController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index(
        Request $request
    ): Response {

        /*
        |--------------------------------------------------------------------------
        | Validate Filters
        |--------------------------------------------------------------------------
        */

        $request->validate([

            'search' => [
                'nullable',
                'string',
                'max:255',
            ],

            'module' => [
                'nullable',
                'string',
                'max:100',
            ],

            'action' => [
                'nullable',
                'string',
                'max:100',
            ],

            'user_id' => [
                'nullable',
                'integer',
                'exists:users,id',
            ],

            'from' => [
                'nullable',
                'date',
            ],

            'to' => [
                'nullable',
                'date',
                'after_or_equal:from',
            ],
        ]);


        /*
        |--------------------------------------------------------------------------
        | Base Query
        |--------------------------------------------------------------------------
        */

        $query =
            ActivityLog::query()
            ->with([
                'user:id,name,email',
            ]);


        /*
        |--------------------------------------------------------------------------
        | Search
        |--------------------------------------------------------------------------
        */

        $query->when(
            $request->filled(
                'search'
            ),
            function ($query) use ($request) {

                $search =
                    trim(
                        $request->input(
                            'search'
                        )
                    );


                $query->where(
                    function ($query) use ($search) {

                        $query
                            ->where(
                                'description',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'module',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'action',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'ip_address',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'subject_type',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhere(
                                'subject_id',
                                'like',
                                "%{$search}%"
                            )
                            ->orWhereHas(
                                'user',
                                function ($query) use ($search) {

                                    $query
                                        ->where(
                                            'name',
                                            'like',
                                            "%{$search}%"
                                        )
                                        ->orWhere(
                                            'email',
                                            'like',
                                            "%{$search}%"
                                        );
                                }
                            );
                    }
                );
            }
        );


        /*
        |--------------------------------------------------------------------------
        | Module Filter
        |--------------------------------------------------------------------------
        */

        $query->when(
            $request->filled(
                'module'
            ),
            fn($query) =>
            $query->where(
                'module',
                $request->input(
                    'module'
                )
            )
        );


        /*
        |--------------------------------------------------------------------------
        | Action Filter
        |--------------------------------------------------------------------------
        */

        $query->when(
            $request->filled(
                'action'
            ),
            fn($query) =>
            $query->where(
                'action',
                $request->input(
                    'action'
                )
            )
        );


        /*
        |--------------------------------------------------------------------------
        | Administrator Filter
        |--------------------------------------------------------------------------
        */

        $query->when(
            $request->filled(
                'user_id'
            ),
            fn($query) =>
            $query->where(
                'user_id',
                $request->integer(
                    'user_id'
                )
            )
        );


        /*
        |--------------------------------------------------------------------------
        | Date From
        |--------------------------------------------------------------------------
        */

        $query->when(
            $request->filled(
                'from'
            ),
            fn($query) =>
            $query->whereDate(
                'created_at',
                '>=',
                $request->input(
                    'from'
                )
            )
        );


        /*
        |--------------------------------------------------------------------------
        | Date To
        |--------------------------------------------------------------------------
        */

        $query->when(
            $request->filled(
                'to'
            ),
            fn($query) =>
            $query->whereDate(
                'created_at',
                '<=',
                $request->input(
                    'to'
                )
            )
        );


        /*
        |--------------------------------------------------------------------------
        | Pagination
        |--------------------------------------------------------------------------
        */

        $activities =
            $query
            ->latest(
                'id'
            )
            ->paginate(
                20
            )
            ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | Transform Activity Records
        |--------------------------------------------------------------------------
        */

        $activities->through(
            function (
                ActivityLog $activity
            ) {

                return [

                    /*
                    |--------------------------------------------------------------------------
                    | Identification
                    |--------------------------------------------------------------------------
                    */

                    'id' =>
                    $activity->id,

                    'module' =>
                    $activity->module,

                    'action' =>
                    $activity->action,

                    'description' =>
                    $activity->description,


                    /*
                    |--------------------------------------------------------------------------
                    | Subject
                    |--------------------------------------------------------------------------
                    */

                    'subject_type' =>
                    $activity->subject_type,

                    'subject_id' =>
                    $activity->subject_id,

                    'subject_name' =>
                    $this->subjectName(
                        $activity->subject_type
                    ),

                    'target_url' =>
                    $this->resolveSubjectUrl(
                        $activity->subject_type,
                        $activity->subject_id
                    ),


                    /*
                    |--------------------------------------------------------------------------
                    | Request Information
                    |--------------------------------------------------------------------------
                    */

                    'ip_address' =>
                    $activity->ip_address,

                    'user_agent' =>
                    $activity->user_agent,


                    /*
                    |--------------------------------------------------------------------------
                    | Audit Values
                    |--------------------------------------------------------------------------
                    */

                    'old_values' =>
                    $this->normalizeValues(
                        $activity->old_values
                    ),

                    'new_values' =>
                    $this->normalizeValues(
                        $activity->new_values
                    ),


                    /*
                    |--------------------------------------------------------------------------
                    | Administrator
                    |--------------------------------------------------------------------------
                    */

                    'user' =>
                    $activity->user
                        ? [
                            'id' =>
                            $activity->user->id,

                            'name' =>
                            $activity->user->name,

                            'email' =>
                            $activity->user->email,
                        ]
                        : null,


                    /*
                    |--------------------------------------------------------------------------
                    | Time
                    |--------------------------------------------------------------------------
                    */

                    'created_at' =>
                    $activity
                        ->created_at
                        ?->toISOString(),

                    'updated_at' =>
                    $activity
                        ->updated_at
                        ?->toISOString(),
                ];
            }
        );


        /*
        |--------------------------------------------------------------------------
        | Module Filter Options
        |--------------------------------------------------------------------------
        */

        $modules =
            ActivityLog::query()
            ->whereNotNull(
                'module'
            )
            ->where(
                'module',
                '!=',
                ''
            )
            ->distinct()
            ->orderBy(
                'module'
            )
            ->pluck(
                'module'
            )
            ->values();


        /*
        |--------------------------------------------------------------------------
        | Action Filter Options
        |--------------------------------------------------------------------------
        */

        $actions =
            ActivityLog::query()
            ->whereNotNull(
                'action'
            )
            ->where(
                'action',
                '!=',
                ''
            )
            ->distinct()
            ->orderBy(
                'action'
            )
            ->pluck(
                'action'
            )
            ->values();


        /*
        |--------------------------------------------------------------------------
        | Administrators Used In Activity Logs
        |--------------------------------------------------------------------------
        */

        $activityUserIds =
            ActivityLog::query()
            ->whereNotNull(
                'user_id'
            )
            ->distinct()
            ->pluck(
                'user_id'
            );


        $users =
            User::query()
            ->whereIn(
                'id',
                $activityUserIds
            )
            ->orderBy(
                'name'
            )
            ->get([
                'id',
                'name',
                'email',
            ]);


        /*
        |--------------------------------------------------------------------------
        | Summary Statistics
        |--------------------------------------------------------------------------
        */

        $summary = [

            'total' =>
            ActivityLog::query()
                ->count(),

            'today' =>
            ActivityLog::query()
                ->whereDate(
                    'created_at',
                    today()
                )
                ->count(),

            'this_week' =>
            ActivityLog::query()
                ->whereBetween(
                    'created_at',
                    [
                        now()
                            ->startOfWeek(),

                        now()
                            ->endOfWeek(),
                    ]
                )
                ->count(),

            'this_month' =>
            ActivityLog::query()
                ->whereYear(
                    'created_at',
                    now()->year
                )
                ->whereMonth(
                    'created_at',
                    now()->month
                )
                ->count(),
        ];


        /*
        |--------------------------------------------------------------------------
        | Current Filters
        |--------------------------------------------------------------------------
        */

        $filters = [

            'search' =>
            $request->input(
                'search',
                ''
            ),

            'module' =>
            $request->input(
                'module',
                ''
            ),

            'action' =>
            $request->input(
                'action',
                ''
            ),

            'user_id' =>
            $request->input(
                'user_id',
                ''
            ),

            'from' =>
            $request->input(
                'from',
                ''
            ),

            'to' =>
            $request->input(
                'to',
                ''
            ),
        ];


        /*
        |--------------------------------------------------------------------------
        | Render
        |--------------------------------------------------------------------------
        */

        return Inertia::render(
            'SuperAdmin/Activity/Index',
            [
                'activities' =>
                $activities,

                'summary' =>
                $summary,

                'filters' =>
                $filters,

                'filterOptions' => [

                    'modules' =>
                    $modules,

                    'actions' =>
                    $actions,

                    'users' =>
                    $users,
                ],
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Normalize JSON Values
    |--------------------------------------------------------------------------
    */

    private function normalizeValues(
        mixed $values
    ): array {

        /*
        |--------------------------------------------------------------------------
        | Already Array
        |--------------------------------------------------------------------------
        */

        if (
            is_array(
                $values
            )
        ) {

            return $values;
        }


        /*
        |--------------------------------------------------------------------------
        | Object
        |--------------------------------------------------------------------------
        */

        if (
            is_object(
                $values
            )
        ) {

            return (array) $values;
        }


        /*
        |--------------------------------------------------------------------------
        | JSON String
        |--------------------------------------------------------------------------
        */

        if (
            is_string(
                $values
            ) &&
            trim(
                $values
            ) !== ''
        ) {

            $decoded =
                json_decode(
                    $values,
                    true
                );


            return is_array(
                $decoded
            )
                ? $decoded
                : [];
        }


        /*
        |--------------------------------------------------------------------------
        | Empty
        |--------------------------------------------------------------------------
        */

        return [];
    }


    /*
    |--------------------------------------------------------------------------
    | Subject Display Name
    |--------------------------------------------------------------------------
    */

    private function subjectName(
        ?string $subjectType
    ): ?string {

        if (
            !$subjectType
        ) {

            return null;
        }


        $parts =
            explode(
                '\\',
                $subjectType
            );


        return end(
            $parts
        ) ?: null;
    }


    /*
    |--------------------------------------------------------------------------
    | Resolve Subject URL
    |--------------------------------------------------------------------------
    |
    | Provides a safe destination for activity records.
    |
    | If the original target was deleted, the controller returns the
    | appropriate module index rather than generating a broken URL.
    |
    */

    private function resolveSubjectUrl(
        ?string $subjectType,
        int|string|null $subjectId
    ): ?string {

        /*
        |--------------------------------------------------------------------------
        | Missing Subject
        |--------------------------------------------------------------------------
        */

        if (
            !$subjectType ||
            $subjectId === null ||
            $subjectId === ''
        ) {

            return null;
        }


        /*
        |--------------------------------------------------------------------------
        | Company
        |--------------------------------------------------------------------------
        */

        if (
            $subjectType ===
            Company::class
        ) {

            $company =
                Company::query()
                ->find(
                    $subjectId
                );


            if (
                !$company
            ) {

                return route(
                    'super-admin.companies.index'
                );
            }


            return route(
                'super-admin.companies.show',
                $company
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Branch / Store
        |--------------------------------------------------------------------------
        |
        | There is currently no super-admin.branches.show route.
        |
        | Therefore existing branches open the parent company page.
        |
        */

        if (
            $subjectType ===
            Store::class
        ) {

            $store =
                Store::query()
                ->find(
                    $subjectId
                );


            if (
                !$store
            ) {

                return route(
                    'super-admin.branches.index'
                );
            }


            $company =
                Company::query()
                ->find(
                    $store->company_id
                );


            if (
                !$company
            ) {

                return route(
                    'super-admin.branches.index'
                );
            }


            return route(
                'super-admin.companies.show',
                $company
            );
        }


        /*
        |--------------------------------------------------------------------------
        | User
        |--------------------------------------------------------------------------
        */

        if (
            $subjectType ===
            User::class
        ) {

            $user =
                User::query()
                ->find(
                    $subjectId
                );


            if (
                !$user ||
                (bool) $user->is_super_user
            ) {

                return route(
                    'super-admin.users.index'
                );
            }


            return route(
                'super-admin.users.show',
                $user
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Role
        |--------------------------------------------------------------------------
        */

        if (
            $subjectType ===
            Role::class
        ) {

            $role =
                Role::query()
                ->find(
                    $subjectId
                );


            if (
                !$role
            ) {

                return route(
                    'super-admin.roles.index'
                );
            }


            return route(
                'super-admin.roles.show',
                $role
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Permission
        |--------------------------------------------------------------------------
        */

        if (
            $subjectType ===
            Permission::class
        ) {

            $permission =
                Permission::query()
                ->find(
                    $subjectId
                );


            if (
                !$permission
            ) {

                return route(
                    'super-admin.permissions.index'
                );
            }


            return route(
                'super-admin.permissions.edit',
                $permission
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Unknown Subject
        |--------------------------------------------------------------------------
        */

        return null;
    }
}
