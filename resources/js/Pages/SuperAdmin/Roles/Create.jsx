import {
    Head,
    Link,
    useForm,
} from '@inertiajs/react';

import {
    useMemo,
    useState,
} from 'react';

import SuperAdminLayout
    from '../../../Layouts/SuperAdminLayout';


export default function Create({
    permissions = [],
}) {

    const [permissionSearch, setPermissionSearch] =
        useState('');


    const {
        data,
        setData,
        post,
        processing,
        errors,
    } = useForm({

        name: '',

        permissions: [],

    });


    /*
    |--------------------------------------------------------------------------
    | Filter Permissions
    |--------------------------------------------------------------------------
    */

    const filteredPermissionGroups =
        useMemo(() => {

            const term =
                permissionSearch
                    .trim()
                    .toLowerCase();


            if (!term) {
                return permissions;
            }


            return permissions
                .map((group) => {

                    /*
                    |--------------------------------------------------------------------------
                    | Module Match
                    |--------------------------------------------------------------------------
                    */

                    const moduleMatches =
                        String(
                            group.module ?? ''
                        )
                            .toLowerCase()
                            .includes(term);


                    if (moduleMatches) {
                        return group;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Individual Permission Match
                    |--------------------------------------------------------------------------
                    */

                    const matchingPermissions =
                        (
                            group.permissions ?? []
                        )
                            .filter(
                                (permission) => {

                                    return [
                                        permission.name,
                                        permission.action,
                                    ]
                                        .filter(Boolean)
                                        .some(
                                            (value) =>
                                                String(value)
                                                    .toLowerCase()
                                                    .includes(term)
                                        );

                                }
                            );


                    if (
                        matchingPermissions.length === 0
                    ) {
                        return null;
                    }


                    return {
                        ...group,

                        permissions:
                            matchingPermissions,
                    };

                })
                .filter(Boolean);

        }, [
            permissions,
            permissionSearch,
        ]);


    /*
    |--------------------------------------------------------------------------
    | Visible Permission Count
    |--------------------------------------------------------------------------
    */

    const visiblePermissionCount =
        useMemo(() => {

            return filteredPermissionGroups
                .reduce(
                    (total, group) =>
                        total +
                        (
                            group.permissions?.length
                            ?? 0
                        ),
                    0
                );

        }, [
            filteredPermissionGroups,
        ]);


    /*
    |--------------------------------------------------------------------------
    | Toggle Permission
    |--------------------------------------------------------------------------
    */

    const togglePermission = (permissionName) => {

        const selected =
            data.permissions ?? [];


        if (
            selected.includes(
                permissionName
            )
        ) {

            setData(
                'permissions',
                selected.filter(
                    (name) =>
                        name !== permissionName
                )
            );

            return;
        }


        setData(
            'permissions',
            [
                ...selected,
                permissionName,
            ]
        );

    };


    /*
    |--------------------------------------------------------------------------
    | Module Permission Names
    |--------------------------------------------------------------------------
    */

    const getModulePermissionNames =
        (group) => {

            return (
                group.permissions ?? []
            ).map(
                (permission) =>
                    permission.name
            );

        };


    /*
    |--------------------------------------------------------------------------
    | Is Entire Module Selected
    |--------------------------------------------------------------------------
    */

    const isModuleSelected =
        (group) => {

            const permissionNames =
                getModulePermissionNames(
                    group
                );


            if (
                permissionNames.length === 0
            ) {
                return false;
            }


            return permissionNames.every(
                (name) =>
                    data.permissions.includes(
                        name
                    )
            );

        };


    /*
    |--------------------------------------------------------------------------
    | Toggle Entire Module
    |--------------------------------------------------------------------------
    */

    const toggleModule = (group) => {

        const permissionNames =
            getModulePermissionNames(
                group
            );

        const allSelected =
            isModuleSelected(
                group
            );


        if (allSelected) {

            setData(
                'permissions',
                data.permissions.filter(
                    (name) =>
                        !permissionNames.includes(
                            name
                        )
                )
            );

            return;
        }


        const merged =
            new Set([
                ...data.permissions,
                ...permissionNames,
            ]);


        setData(
            'permissions',
            Array.from(
                merged
            )
        );

    };


    /*
    |--------------------------------------------------------------------------
    | Select All Permissions
    |--------------------------------------------------------------------------
    */

    const selectAll = () => {

        const allPermissions =
            permissions.flatMap(
                (group) =>
                    (
                        group.permissions ?? []
                    ).map(
                        (permission) =>
                            permission.name
                    )
            );


        setData(
            'permissions',
            Array.from(
                new Set(
                    allPermissions
                )
            )
        );

    };


    /*
    |--------------------------------------------------------------------------
    | Select Visible Permissions
    |--------------------------------------------------------------------------
    */

    const selectVisible = () => {

        const visiblePermissions =
            filteredPermissionGroups
                .flatMap(
                    (group) =>
                        (
                            group.permissions ?? []
                        ).map(
                            (permission) =>
                                permission.name
                        )
                );


        setData(
            'permissions',
            Array.from(
                new Set([
                    ...data.permissions,
                    ...visiblePermissions,
                ])
            )
        );

    };


    /*
    |--------------------------------------------------------------------------
    | Clear All
    |--------------------------------------------------------------------------
    */

    const clearAll = () => {

        setData(
            'permissions',
            []
        );

    };


    /*
    |--------------------------------------------------------------------------
    | Submit
    |--------------------------------------------------------------------------
    */

    const submit = (event) => {

        event.preventDefault();


        post(
            '/super-admin/roles',
            {
                preserveScroll: true,
            }
        );

    };


    return (

        <SuperAdminLayout
            title="New Role"
            subtitle="Create a role and assign permissions"
            breadcrumbs={[
                {
                    label: 'Roles',
                    href: '/super-admin/roles',
                },
                {
                    label: 'New Role',
                },
            ]}
        >

            <Head title="New Role" />


            <form onSubmit={submit}>

                {/* =====================================================
                    ROLE INFORMATION
                   ===================================================== */}

                <div className="rr-admin-panel mb-4">

                    <div className="rr-admin-panel-header">

                        <div className="rr-admin-panel-heading">

                            <h2 className="rr-admin-panel-title">
                                Role Information
                            </h2>

                            <div className="rr-admin-panel-description">
                                Define the role used by company users
                            </div>

                        </div>

                    </div>


                    <div className="rr-admin-panel-body">

                        <div className="row g-3">

                            <div className="col-lg-6">

                                <label className="rr-form-label">

                                    Role Name

                                    <span className="text-danger ms-1">
                                        *
                                    </span>

                                </label>


                                <input
                                    type="text"
                                    className={
                                        `rr-form-control ${errors.name
                                            ? 'rr-form-control-error'
                                            : ''
                                        }`
                                    }
                                    value={
                                        data.name
                                    }
                                    onChange={(event) =>
                                        setData(
                                            'name',
                                            event.target.value
                                        )
                                    }
                                    placeholder="Example: Branch Manager"
                                    autoFocus
                                />


                                {errors.name && (

                                    <FormError
                                        error={
                                            errors.name
                                        }
                                    />

                                )}


                                <div className="rr-form-help">
                                    Use a clear business role name such as Branch Manager, Sales User or Accountant.
                                </div>

                            </div>

                        </div>

                    </div>

                </div>


                {/* =====================================================
                    PERMISSIONS
                   ===================================================== */}

                <div className="rr-admin-panel">

                    {/* Header */}

                    <div className="rr-admin-panel-header">

                        <div className="rr-admin-panel-heading">

                            <h2 className="rr-admin-panel-title">
                                Permissions
                            </h2>

                            <div className="rr-admin-panel-description">
                                Select the actions this role can perform
                            </div>

                        </div>


                        <div className="d-flex align-items-center gap-2 flex-wrap">

                            <span className="rr-role-selected-count">
                                {data.permissions.length} selected
                            </span>


                            <button
                                type="button"
                                className="rr-btn rr-btn-secondary"
                                onClick={
                                    selectAll
                                }
                            >
                                <i className="bi bi-check2-all"></i>

                                Select All
                            </button>


                            <button
                                type="button"
                                className="rr-btn rr-btn-secondary"
                                onClick={
                                    clearAll
                                }
                                disabled={
                                    data.permissions.length === 0
                                }
                            >
                                <i className="bi bi-x-lg"></i>

                                Clear
                            </button>

                        </div>

                    </div>


                    {/* =================================================
                        SEARCH TOOLBAR
                       ================================================= */}

                    <div className="rr-role-permission-toolbar">

                        <div className="rr-role-permission-search">

                            <i className="bi bi-search"></i>


                            <input
                                type="text"
                                value={
                                    permissionSearch
                                }
                                onChange={(event) =>
                                    setPermissionSearch(
                                        event.target.value
                                    )
                                }
                                placeholder="Search module, action or permission..."
                            />


                            {permissionSearch && (

                                <button
                                    type="button"
                                    className="rr-role-permission-search-clear"
                                    onClick={() =>
                                        setPermissionSearch(
                                            ''
                                        )
                                    }
                                    title="Clear search"
                                >
                                    <i className="bi bi-x-lg"></i>
                                </button>

                            )}

                        </div>


                        <div className="rr-role-permission-toolbar-right">

                            <span className="rr-role-permission-search-count">

                                {visiblePermissionCount}
                                {' '}
                                permissions shown

                            </span>


                            {permissionSearch &&
                                visiblePermissionCount > 0 && (

                                    <button
                                        type="button"
                                        className="rr-btn rr-btn-secondary"
                                        onClick={
                                            selectVisible
                                        }
                                    >
                                        <i className="bi bi-check2-square"></i>

                                        Select Shown
                                    </button>

                                )}

                        </div>

                    </div>


                    <div className="rr-admin-panel-body">

                        {/* Validation */}

                        {errors.permissions && (

                            <div className="mb-3">

                                <FormError
                                    error={
                                        errors.permissions
                                    }
                                />

                            </div>

                        )}


                        {/* =================================================
                            MODULES
                           ================================================= */}

                        {filteredPermissionGroups.length > 0 ? (

                            <div className="rr-permission-modules">

                                {filteredPermissionGroups.map(
                                    (group) => (

                                        <PermissionModule
                                            key={
                                                group.module
                                            }
                                            group={
                                                group
                                            }
                                            selectedPermissions={
                                                data.permissions
                                            }
                                            onTogglePermission={
                                                togglePermission
                                            }
                                            onToggleModule={
                                                toggleModule
                                            }
                                            allSelected={
                                                isModuleSelected(
                                                    group
                                                )
                                            }
                                        />

                                    )
                                )}

                            </div>

                        ) : (

                            <div className="rr-empty-state">

                                <div className="rr-empty-state-icon">

                                    <i
                                        className={
                                            permissionSearch
                                                ? 'bi bi-search'
                                                : 'bi bi-shield-lock'
                                        }
                                    ></i>

                                </div>


                                <div className="rr-empty-state-title">

                                    {permissionSearch
                                        ? 'No permissions found'
                                        : 'No permissions available'}

                                </div>


                                <div className="rr-empty-state-text">

                                    {permissionSearch
                                        ? `No permissions match "${permissionSearch}".`
                                        : 'No permissions are currently configured.'}

                                </div>


                                {permissionSearch && (

                                    <button
                                        type="button"
                                        className="rr-btn rr-btn-secondary mt-3"
                                        onClick={() =>
                                            setPermissionSearch(
                                                ''
                                            )
                                        }
                                    >
                                        Clear Search
                                    </button>

                                )}

                            </div>

                        )}

                    </div>


                    {/* =================================================
                        FOOTER
                       ================================================= */}

                    <div className="rr-company-form-footer">

                        <Link
                            href="/super-admin/roles"
                            className="rr-btn rr-btn-secondary"
                        >
                            <i className="bi bi-x-lg"></i>

                            Cancel
                        </Link>


                        <button
                            type="submit"
                            className="rr-btn rr-btn-primary"
                            disabled={
                                processing
                            }
                        >

                            {processing ? (

                                <>
                                    <span className="spinner-border spinner-border-sm"></span>

                                    Creating...
                                </>

                            ) : (

                                <>
                                    <i className="bi bi-check-lg"></i>

                                    Create Role
                                </>

                            )}

                        </button>

                    </div>

                </div>

            </form>

        </SuperAdminLayout>
    );
}


/*
|--------------------------------------------------------------------------
| Permission Module
|--------------------------------------------------------------------------
*/

function PermissionModule({
    group,
    selectedPermissions,
    onTogglePermission,
    onToggleModule,
    allSelected,
}) {

    const selectedCount =
        (
            group.permissions ?? []
        ).filter(
            (permission) =>
                selectedPermissions.includes(
                    permission.name
                )
        ).length;


    return (

        <div className="rr-permission-module">

            {/* Module Header */}

            <div className="rr-permission-module-header">

                <div className="rr-permission-module-title">

                    <div className="rr-permission-module-icon">

                        <i className="bi bi-grid"></i>

                    </div>


                    <div>

                        <div className="rr-permission-module-name">

                            {formatText(
                                group.module
                            )}

                        </div>


                        <div className="rr-permission-module-count">

                            {
                                group.permissions
                                    ?.length ?? 0
                            }
                            {' '}
                            permissions

                            {selectedCount > 0 && (
                                <>
                                    {' '}
                                    •
                                    {' '}
                                    {selectedCount} selected
                                </>
                            )}

                        </div>

                    </div>

                </div>


                <label className="rr-permission-module-toggle">

                    <input
                        type="checkbox"
                        checked={
                            allSelected
                        }
                        onChange={() =>
                            onToggleModule(
                                group
                            )
                        }
                    />

                    <span>
                        Select module
                    </span>

                </label>

            </div>


            {/* Permission Grid */}

            <div className="rr-permission-grid">

                {(group.permissions ?? [])
                    .map((permission) => {

                        const selected =
                            selectedPermissions.includes(
                                permission.name
                            );


                        return (

                            <label
                                key={
                                    permission.id
                                    ?? permission.name
                                }
                                className={
                                    `rr-permission-option ${selected
                                        ? 'selected'
                                        : ''
                                    }`
                                }
                            >

                                <input
                                    type="checkbox"
                                    checked={
                                        selected
                                    }
                                    onChange={() =>
                                        onTogglePermission(
                                            permission.name
                                        )
                                    }
                                />


                                <div className="rr-permission-option-check">

                                    {selected ? (

                                        <i className="bi bi-check-lg"></i>

                                    ) : (

                                        <i className="bi bi-dash"></i>

                                    )}

                                </div>


                                <div className="rr-permission-option-content">

                                    <div className="rr-permission-option-action">

                                        {formatText(
                                            permission.action
                                        )}

                                    </div>


                                    <div className="rr-permission-option-name">

                                        {permission.name}

                                    </div>

                                </div>

                            </label>

                        );

                    })}

            </div>

        </div>

    );
}


/*
|--------------------------------------------------------------------------
| Form Error
|--------------------------------------------------------------------------
*/

function FormError({
    error,
}) {

    return (

        <div className="rr-form-error">

            <i className="bi bi-exclamation-circle"></i>

            <span>
                {error}
            </span>

        </div>

    );
}


/*
|--------------------------------------------------------------------------
| Format Display Text
|--------------------------------------------------------------------------
*/

function formatText(value) {

    return String(
        value ?? ''
    )
        .replaceAll(
            '-',
            ' '
        )
        .replaceAll(
            '_',
            ' '
        )
        .replaceAll(
            '.',
            ' '
        )
        .replace(
            /\b\w/g,
            (character) =>
                character.toUpperCase()
        );

}