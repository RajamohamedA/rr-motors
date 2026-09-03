<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Support\AppResponse;

use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Validation\Rule;

use Inertia\Inertia;
use Inertia\Response;


class UnitController extends Controller implements HasMiddleware
{
    /*
    |--------------------------------------------------------------------------
    | Middleware
    |--------------------------------------------------------------------------
    */

    public static function middleware(): array
    {
        return [

            new Middleware(
                'permission:unit.view',
                only: [
                    'index',
                    'show',
                ]
            ),

            new Middleware(
                'permission:unit.create',
                only: [
                    'create',
                    'store',
                ]
            ),

            new Middleware(
                'permission:unit.edit',
                only: [
                    'edit',
                    'update',
                    'toggleStatus',
                ]
            ),

            new Middleware(
                'permission:unit.delete',
                only: [
                    'destroy',
                ]
            ),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Index
    |--------------------------------------------------------------------------
    */

    public function index(
        Request $request
    ): Response {

        [
            $companyId,
            $storeId,
        ] = $this->context();


        $search =
            trim(
                (string) $request->input(
                    'search',
                    ''
                )
            );


        $status =
            $request->input(
                'status'
            );


        $type =
            $request->input(
                'type'
            );


        /*
        |--------------------------------------------------------------------------
        | Units
        |--------------------------------------------------------------------------
        */

        $units =
            Unit::query()

            ->forContext(
                $companyId,
                $storeId
            )

            ->when(
                $search !== '',
                function (
                    $query
                ) use (
                    $search
                ) {

                    $query->where(
                        function (
                            $subQuery
                        ) use (
                            $search
                        ) {

                            $subQuery
                                ->where(
                                    'unit_name',
                                    'like',
                                    "%{$search}%"
                                )

                                ->orWhere(
                                    'short_name',
                                    'like',
                                    "%{$search}%"
                                )

                                ->orWhere(
                                    'description',
                                    'like',
                                    "%{$search}%"
                                );
                        }
                    );
                }
            )

            ->when(
                $status !== null
                    &&
                    $status !== '',
                function (
                    $query
                ) use (
                    $status
                ) {

                    $query->where(
                        'status',
                        (bool) ((int) $status)
                    );
                }
            )

            ->when(
                $type === 'system',
                fn($query) =>
                $query->where(
                    'is_system_unit',
                    true
                )
            )

            ->when(
                $type === 'custom',
                fn($query) =>
                $query->where(
                    'is_system_unit',
                    false
                )
            )

            ->orderByDesc(
                'is_system_unit'
            )

            ->orderBy(
                'unit_name'
            )

            ->paginate(
                20
            )

            ->withQueryString();


        /*
        |--------------------------------------------------------------------------
        | Summary
        |--------------------------------------------------------------------------
        */

        $baseQuery =
            Unit::query()
            ->forContext(
                $companyId,
                $storeId
            );


        $summary = [

            'total' => (clone $baseQuery)
                ->count(),

            'active' => (clone $baseQuery)

                ->where(
                    'status',
                    true
                )

                ->count(),

            'inactive' => (clone $baseQuery)

                ->where(
                    'status',
                    false
                )

                ->count(),

            'system' => (clone $baseQuery)

                ->where(
                    'is_system_unit',
                    true
                )

                ->count(),

            'custom' => (clone $baseQuery)

                ->where(
                    'is_system_unit',
                    false
                )

                ->count(),
        ];


        return Inertia::render(
            'Units/Index',
            [

                'units' =>
                $units,

                'summary' =>
                $summary,

                'filters' => [

                    'search' =>
                    $search,

                    'status' =>
                    $status ?? '',

                    'type' =>
                    $type ?? '',
                ],
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Create
    |--------------------------------------------------------------------------
    */

    public function create(): Response
    {
        return Inertia::render(
            'Units/Create'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Store
    |--------------------------------------------------------------------------
    */

    public function store(
        Request $request
    ) {

        [
            $companyId,
            $storeId,
        ] = $this->context();


        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate([

                'unit_name' => [
                    'required',
                    'string',
                    'max:100',

                    Rule::unique(
                        'units',
                        'unit_name'
                    )
                        ->where(
                            fn($query) =>
                            $query->where(
                                'store_id',
                                $storeId
                            )
                        ),
                ],

                'short_name' => [
                    'required',
                    'string',
                    'max:20',

                    Rule::unique(
                        'units',
                        'short_name'
                    )
                        ->where(
                            fn($query) =>
                            $query->where(
                                'store_id',
                                $storeId
                            )
                        ),
                ],

                'description' => [
                    'nullable',
                    'string',
                    'max:1000',
                ],

                'status' => [
                    'required',
                    'boolean',
                ],
            ]);


        /*
        |--------------------------------------------------------------------------
        | Normalize
        |--------------------------------------------------------------------------
        */

        $validated['unit_name'] =
            trim(
                $validated['unit_name']
            );


        $validated['short_name'] =
            trim(
                $validated['short_name']
            );


        $validated['description'] =
            filled(
                $validated['description']
                    ?? null
            )
            ? trim(
                $validated['description']
            )
            : null;


        /*
        |--------------------------------------------------------------------------
        | Create
        |--------------------------------------------------------------------------
        |
        | Manually-created units are always custom units.
        |
        */

        try {

            Unit::create([

                'company_id' =>
                $companyId,

                'store_id' =>
                $storeId,

                'unit_name' =>
                $validated['unit_name'],

                'short_name' =>
                $validated['short_name'],

                'description' =>
                $validated['description'],

                'is_system_unit' =>
                false,

                'status' =>
                $validated['status'],
            ]);
        } catch (
            QueryException $exception
        ) {

            if (
                $this->isDuplicateException(
                    $exception
                )
            ) {

                return AppResponse::error(
                    'A unit with this name or short name already exists in this branch.',
                    true
                );
            }


            report(
                $exception
            );


            return AppResponse::error(
                'Unit could not be created. Please try again.',
                true
            );
        }


        return AppResponse::successRoute(
            'units.index',
            'Unit created successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Show
    |--------------------------------------------------------------------------
    */

    public function show(
        Unit $unit
    ): Response {

        $this->ensureUnitAvailable(
            $unit
        );


        return Inertia::render(
            'Units/Show',
            [

                'unit' =>
                $this->unitData(
                    $unit
                ),
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Edit
    |--------------------------------------------------------------------------
    */

    public function edit(
        Unit $unit
    ): Response {

        $this->ensureUnitAvailable(
            $unit
        );


        return Inertia::render(
            'Units/Edit',
            [

                'unit' =>
                $this->unitData(
                    $unit
                ),
            ]
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Update
    |--------------------------------------------------------------------------
    */

    public function update(
        Request $request,
        Unit $unit
    ) {

        [
            $companyId,
            $storeId,
        ] = $this->context();


        $this->ensureUnitAvailable(
            $unit
        );


        /*
        |--------------------------------------------------------------------------
        | Validation
        |--------------------------------------------------------------------------
        */

        $validated =
            $request->validate([

                'unit_name' => [
                    'required',
                    'string',
                    'max:100',

                    Rule::unique(
                        'units',
                        'unit_name'
                    )
                        ->where(
                            fn($query) =>
                            $query->where(
                                'store_id',
                                $storeId
                            )
                        )
                        ->ignore(
                            $unit->id
                        ),
                ],

                'short_name' => [
                    'required',
                    'string',
                    'max:20',

                    Rule::unique(
                        'units',
                        'short_name'
                    )
                        ->where(
                            fn($query) =>
                            $query->where(
                                'store_id',
                                $storeId
                            )
                        )
                        ->ignore(
                            $unit->id
                        ),
                ],

                'description' => [
                    'nullable',
                    'string',
                    'max:1000',
                ],

                'status' => [
                    'required',
                    'boolean',
                ],
            ]);


        /*
        |--------------------------------------------------------------------------
        | Protect System Unit Identity
        |--------------------------------------------------------------------------
        |
        | System units can be activated/deactivated, but their standard
        | name and short name should not be changed from branch management.
        |
        */

        if (
            $unit->is_system_unit
        ) {

            $validated['unit_name'] =
                $unit->unit_name;


            $validated['short_name'] =
                $unit->short_name;
        }


        /*
        |--------------------------------------------------------------------------
        | Normalize
        |--------------------------------------------------------------------------
        */

        $validated['unit_name'] =
            trim(
                $validated['unit_name']
            );


        $validated['short_name'] =
            trim(
                $validated['short_name']
            );


        $validated['description'] =
            filled(
                $validated['description']
                    ?? null
            )
            ? trim(
                $validated['description']
            )
            : null;


        /*
        |--------------------------------------------------------------------------
        | Update
        |--------------------------------------------------------------------------
        */

        try {

            $unit->update([

                'unit_name' =>
                $validated['unit_name'],

                'short_name' =>
                $validated['short_name'],

                'description' =>
                $validated['description'],

                'status' =>
                $validated['status'],
            ]);
        } catch (
            QueryException $exception
        ) {

            if (
                $this->isDuplicateException(
                    $exception
                )
            ) {

                return AppResponse::error(
                    'Another unit with this name or short name already exists in this branch.',
                    true
                );
            }


            report(
                $exception
            );


            return AppResponse::error(
                'Unit could not be updated. Please try again.',
                true
            );
        }


        return AppResponse::successRoute(
            'units.index',
            'Unit updated successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Toggle Status
    |--------------------------------------------------------------------------
    */

    public function toggleStatus(
        Unit $unit
    ) {

        $this->ensureUnitAvailable(
            $unit
        );


        $newStatus =
            !$unit->status;


        try {

            $unit->update([

                'status' =>
                $newStatus,
            ]);
        } catch (
            QueryException $exception
        ) {

            report(
                $exception
            );


            return AppResponse::error(
                'Unit status could not be updated.'
            );
        }


        return AppResponse::success(
            $newStatus
                ? 'Unit activated successfully.'
                : 'Unit deactivated successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Destroy
    |--------------------------------------------------------------------------
    */

    public function destroy(
        Unit $unit
    ) {

        $this->ensureUnitAvailable(
            $unit
        );


        /*
        |--------------------------------------------------------------------------
        | System Unit Protection
        |--------------------------------------------------------------------------
        */

        if (
            $unit->is_system_unit
        ) {

            return AppResponse::error(
                'Default system units cannot be deleted. You may deactivate the unit instead.'
            );
        }


        /*
        |--------------------------------------------------------------------------
        | Product Usage Protection
        |--------------------------------------------------------------------------
        */

        if (
            $unit
            ->products()
            ->exists()
        ) {

            return AppResponse::error(
                'This unit is used by one or more products and cannot be deleted.'
            );
        }


        try {

            $unit->delete();
        } catch (
            QueryException $exception
        ) {

            report(
                $exception
            );


            return AppResponse::error(
                'Unit could not be deleted because it is being used by another record.'
            );
        }


        return AppResponse::successRoute(
            'units.index',
            'Unit deleted successfully.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Business Context
    |--------------------------------------------------------------------------
    */

    private function context(): array
    {
        $companyId =
            (int) session(
                'active_company_id'
            );


        $storeId =
            (int) session(
                'active_store_id'
            );


        if (
            !$companyId ||
            !$storeId
        ) {

            abort(
                403,
                'No active company or branch selected.'
            );
        }


        return [
            $companyId,
            $storeId,
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Unit Isolation
    |--------------------------------------------------------------------------
    */

    private function ensureUnitAvailable(
        Unit $unit
    ): void {

        [
            $companyId,
            $storeId,
        ] = $this->context();


        if (
            (int) $unit->company_id !==
            $companyId
            ||
            (int) $unit->store_id !==
            $storeId
        ) {

            abort(
                404
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Unit Data
    |--------------------------------------------------------------------------
    */

    private function unitData(
        Unit $unit
    ): array {

        return [

            'id' =>
            $unit->id,

            'unit_name' =>
            $unit->unit_name,

            'short_name' =>
            $unit->short_name,

            'description' =>
            $unit->description,

            'is_system_unit' =>
            (bool) $unit->is_system_unit,

            'status' =>
            (bool) $unit->status,

            'created_at' =>
            $unit->created_at
                ?->toISOString(),

            'updated_at' =>
            $unit->updated_at
                ?->toISOString(),
        ];
    }


    /*
    |--------------------------------------------------------------------------
    | Duplicate Exception
    |--------------------------------------------------------------------------
    */

    private function isDuplicateException(
        QueryException $exception
    ): bool {

        return in_array(
            (string) $exception->getCode(),
            [
                '23000',
            ],
            true
        );
    }
}
