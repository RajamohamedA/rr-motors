import {
    Head,
    Link,
    router,
} from '@inertiajs/react';

import {
    useMemo,
    useState,
} from 'react';

import SuperAdminLayout
    from '../../../Layouts/SuperAdminLayout';

import ConfirmModal
    from '../../../Components/ConfirmModal';


export default function Index({
    permissions = [],
}) {

    const [search, setSearch] =
        useState('');

    const [moduleFilter, setModuleFilter] =
        useState('all');

    const [usageFilter, setUsageFilter] =
        useState('all');

    const [deletePermission, setDeletePermission] =
        useState(null);

    const [deleting, setDeleting] =
        useState(false);


    /*
    |--------------------------------------------------------------------------
    | Summary
    |--------------------------------------------------------------------------
    */

    const summary = useMemo(() => {

        const total =
            permissions.length;

        const used =
            permissions.filter(
                (permission) =>
                    Number(
                        permission.roles_count ?? 0
                    ) > 0
            ).length;

        const unused =
            total - used;

        const modules =
            new Set(
                permissions
                    .map(
                        (permission) =>
                            permission.module
                    )
                    .filter(Boolean)
            ).size;

        return {
            total,
            used,
            unused,
            modules,
        };

    }, [permissions]);


    /*
    |--------------------------------------------------------------------------
    | Modules
    |--------------------------------------------------------------------------
    */

    const modules = useMemo(() => {

        return Array.from(
            new Set(
                permissions
                    .map(
                        (permission) =>
                            permission.module
                    )
                    .filter(Boolean)
            )
        ).sort();

    }, [permissions]);


    /*
    |--------------------------------------------------------------------------
    | Filter
    |--------------------------------------------------------------------------
    */

    const filteredPermissions =
        useMemo(() => {

            const term =
                search
                    .trim()
                    .toLowerCase();


            return permissions.filter(
                (permission) => {

                    /*
                    |--------------------------------------------------------------------------
                    | Module
                    |--------------------------------------------------------------------------
                    */

                    if (
                        moduleFilter !== 'all' &&
                        permission.module !== moduleFilter
                    ) {
                        return false;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Usage
                    |--------------------------------------------------------------------------
                    */

                    if (
                        usageFilter === 'used' &&
                        Number(
                            permission.roles_count ?? 0
                        ) === 0
                    ) {
                        return false;
                    }


                    if (
                        usageFilter === 'unused' &&
                        Number(
                            permission.roles_count ?? 0
                        ) > 0
                    ) {
                        return false;
                    }


                    /*
                    |--------------------------------------------------------------------------
                    | Search
                    |--------------------------------------------------------------------------
                    */

                    if (!term) {
                        return true;
                    }


                    return [
                        permission.name,
                        permission.module,
                        permission.action,
                        permission.guard_name,
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

        }, [
            permissions,
            search,
            moduleFilter,
            usageFilter,
        ]);


    /*
    |--------------------------------------------------------------------------
    | Group By Module
    |--------------------------------------------------------------------------
    */

    const groupedPermissions =
        useMemo(() => {

            const map =
                new Map();


            filteredPermissions.forEach(
                (permission) => {

                    const module =
                        permission.module
                        || 'other';


                    if (!map.has(module)) {

                        map.set(
                            module,
                            []
                        );

                    }


                    map
                        .get(module)
                        .push(
                            permission
                        );

                }
            );


            return Array
                .from(
                    map.entries()
                )
                .map(
                    ([
                        module,
                        items,
                    ]) => ({
                        module,
                        permissions:
                            items,
                    })
                );

        }, [
            filteredPermissions,
        ]);


    return (

        <SuperAdminLayout
            title="Permissions"
            subtitle="Manage system permission definitions"
            breadcrumbs={[
                {
                    label: 'Permissions',
                },
            ]}
        >

            <Head title="Permissions" />


            {/* =====================================================
                SUMMARY
               ===================================================== */}

            <div className="row g-3 mb-4">

                <PermissionStatCard
                    title="Total Permissions"
                    value={summary.total}
                    description="Configured permissions"
                    icon="bi-shield-check"
                    type="primary"
                />

                <PermissionStatCard
                    title="Modules"
                    value={summary.modules}
                    description="Permission modules"
                    icon="bi-grid"
                    type="info"
                />

                <PermissionStatCard
                    title="Used"
                    value={summary.used}
                    description="Assigned to roles"
                    icon="bi-check-circle"
                    type="success"
                />

                <PermissionStatCard
                    title="Unused"
                    value={summary.unused}
                    description="Not assigned"
                    icon="bi-dash-circle"
                    type="warning"
                />

            </div>


            {/* =====================================================
                DIRECTORY
               ===================================================== */}

            <div className="rr-admin-panel">

                <div className="rr-admin-panel-header">

                    <div className="rr-admin-panel-heading">

                        <h2 className="rr-admin-panel-title">
                            Permission Directory
                        </h2>

                        <div className="rr-admin-panel-description">
                            View permissions grouped by application module
                        </div>

                    </div>


                    <Link
                        href="/super-admin/permissions/create"
                        className="rr-btn rr-btn-primary"
                    >
                        <i className="bi bi-plus-lg"></i>

                        New Permission
                    </Link>

                </div>


                {/* =================================================
                    TOOLBAR
                   ================================================= */}

                <div className="rr-permission-toolbar">

                    <div className="rr-permission-search">

                        <i className="bi bi-search"></i>

                        <input
                            type="text"
                            value={search}
                            onChange={(event) =>
                                setSearch(
                                    event.target.value
                                )
                            }
                            placeholder="Search module, action or permission..."
                        />


                        {search && (

                            <button
                                type="button"
                                className="rr-permission-search-clear"
                                onClick={() =>
                                    setSearch('')
                                }
                            >
                                <i className="bi bi-x-lg"></i>
                            </button>

                        )}

                    </div>


                    <div className="rr-permission-toolbar-filters">

                        <select
                            className="rr-permission-filter"
                            value={moduleFilter}
                            onChange={(event) =>
                                setModuleFilter(
                                    event.target.value
                                )
                            }
                        >

                            <option value="all">
                                All Modules
                            </option>

                            {modules.map(
                                (module) => (

                                    <option
                                        key={module}
                                        value={module}
                                    >
                                        {formatText(
                                            module
                                        )}
                                    </option>

                                )
                            )}

                        </select>


                        <select
                            className="rr-permission-filter"
                            value={usageFilter}
                            onChange={(event) =>
                                setUsageFilter(
                                    event.target.value
                                )
                            }
                        >

                            <option value="all">
                                All Usage
                            </option>

                            <option value="used">
                                Used
                            </option>

                            <option value="unused">
                                Unused
                            </option>

                        </select>


                        <span className="rr-permission-result-count">

                            {filteredPermissions.length}
                            {' '}
                            permissions

                        </span>

                    </div>

                </div>


                {/* =================================================
                    GROUPS
                   ================================================= */}

                <div className="rr-admin-panel-body">

                    {groupedPermissions.length > 0 ? (

                        <div className="rr-permission-directory-modules">

                            {groupedPermissions.map(
                                (group) => (

                                    <PermissionModule
                                        key={group.module}
                                        group={group}
                                        onDelete={
                                            setDeletePermission
                                        }
                                    />

                                )
                            )}

                        </div>

                    ) : (

                        <div className="rr-empty-state">

                            <div className="rr-empty-state-icon">
                                <i className="bi bi-search"></i>
                            </div>

                            <div className="rr-empty-state-title">
                                No permissions found
                            </div>

                            <div className="rr-empty-state-text">
                                Try changing the search or filters.
                            </div>

                        </div>

                    )}

                </div>

            </div>


            {/* =====================================================
                DELETE
               ===================================================== */}

            <ConfirmModal
                show={
                    Boolean(
                        deletePermission
                    )
                }

                title="Delete Permission"

                message={
                    deletePermission
                        ? Number(
                            deletePermission.roles_count ?? 0
                        ) > 0
                            ? `"${deletePermission.name}" is assigned to ${deletePermission.roles_count} role(s). Remove it from those roles before deleting it.`
                            : `Delete "${deletePermission.name}"? This permission definition will be permanently removed.`
                        : ''
                }

                confirmText="Delete Permission"

                icon="bi-trash"

                variant="danger"

                processing={
                    deleting
                }

                confirmDisabled={
                    Number(
                        deletePermission
                            ?.roles_count ?? 0
                    ) > 0
                }

                onCancel={() => {

                    if (!deleting) {
                        setDeletePermission(
                            null
                        );
                    }

                }}

                onConfirm={() => {

                    if (!deletePermission) {
                        return;
                    }


                    if (
                        Number(
                            deletePermission
                                .roles_count ?? 0
                        ) > 0
                    ) {
                        return;
                    }


                    setDeleting(
                        true
                    );


                    router.delete(
                        `/super-admin/permissions/${deletePermission.id}`,
                        {
                            preserveScroll:
                                true,

                            onSuccess: () => {

                                setDeletePermission(
                                    null
                                );

                            },

                            onFinish: () => {

                                setDeleting(
                                    false
                                );

                            },
                        }
                    );

                }}
            />

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
    onDelete,
}) {

    return (

        <div className="rr-permission-directory-module">

            <div className="rr-permission-directory-module-header">

                <div className="rr-permission-directory-module-left">

                    <div className="rr-permission-directory-module-icon">

                        <i className="bi bi-grid"></i>

                    </div>


                    <div>

                        <div className="rr-permission-directory-module-name">

                            {formatText(
                                group.module
                            )}

                        </div>


                        <div className="rr-permission-directory-module-count">

                            {
                                group.permissions
                                    ?.length ?? 0
                            }
                            {' '}
                            permissions

                        </div>

                    </div>

                </div>

            </div>


            <div className="rr-permission-directory-table-wrapper">

                <table className="rr-permission-directory-table">

                    <thead>

                        <tr>

                            <th>
                                Action
                            </th>

                            <th>
                                Permission
                            </th>

                            <th>
                                Roles
                            </th>

                            <th>
                                Guard
                            </th>

                            <th className="text-end">
                                Actions
                            </th>

                        </tr>

                    </thead>


                    <tbody>

                        {(group.permissions ?? [])
                            .map(
                                (permission) => (

                                    <tr
                                        key={
                                            permission.id
                                        }
                                    >

                                        <td>

                                            <div className="rr-permission-action-name">

                                                {formatText(
                                                    permission.action
                                                )}

                                            </div>

                                        </td>


                                        <td>

                                            <span className="rr-permission-code">

                                                {
                                                    permission.name
                                                }

                                            </span>

                                        </td>


                                        <td>

                                            <span
                                                className={
                                                    Number(
                                                        permission.roles_count
                                                        ?? 0
                                                    ) > 0
                                                        ? 'rr-permission-usage rr-permission-usage-used'
                                                        : 'rr-permission-usage rr-permission-usage-unused'
                                                }
                                            >

                                                {
                                                    permission.roles_count
                                                    ?? 0
                                                }
                                                {' '}
                                                roles

                                            </span>

                                        </td>


                                        <td>

                                            <span className="rr-table-secondary">

                                                {
                                                    permission.guard_name
                                                }

                                            </span>

                                        </td>


                                        <td className="text-end">

                                            <div className="rr-table-actions">

                                                <Link
                                                    href={
                                                        `/super-admin/permissions/${permission.id}/edit`
                                                    }
                                                    className="rr-icon-action"
                                                    title="Edit permission"
                                                >
                                                    <i className="bi bi-pencil"></i>
                                                </Link>


                                                <button
                                                    type="button"
                                                    className="rr-icon-action rr-icon-action-danger"
                                                    title={
                                                        Number(
                                                            permission.roles_count
                                                            ?? 0
                                                        ) > 0
                                                            ? 'Permission is assigned to roles'
                                                            : 'Delete permission'
                                                    }
                                                    onClick={() =>
                                                        onDelete(
                                                            permission
                                                        )
                                                    }
                                                >
                                                    <i className="bi bi-trash"></i>
                                                </button>

                                            </div>

                                        </td>

                                    </tr>

                                )
                            )}

                    </tbody>

                </table>

            </div>

        </div>

    );
}


/*
|--------------------------------------------------------------------------
| Summary Card
|--------------------------------------------------------------------------
*/

function PermissionStatCard({
    title,
    value,
    description,
    icon,
    type = 'primary',
}) {

    return (

        <div className="col-xl-3 col-md-6">

            <div className="rr-user-stat-card">

                <div className="rr-user-stat-top">

                    <div className="rr-user-stat-title">
                        {title}
                    </div>


                    <div
                        className={
                            `rr-user-stat-icon ` +
                            `rr-user-stat-icon-${type}`
                        }
                    >

                        <i className={`bi ${icon}`}></i>

                    </div>

                </div>


                <div className="rr-user-stat-value">

                    {value}

                </div>


                <div className="rr-user-stat-description">

                    {description}

                </div>

            </div>

        </div>

    );
}


/*
|--------------------------------------------------------------------------
| Formatter
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