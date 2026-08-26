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


export default function Show({
    roleData,
}) {

    /*
    |--------------------------------------------------------------------------
    | Permission Search
    |--------------------------------------------------------------------------
    */

    const [permissionSearch, setPermissionSearch] =
        useState('');

    const [showDeleteModal, setShowDeleteModal] =
        useState(false);

    const [deleting, setDeleting] =
        useState(false);


    /*
    |--------------------------------------------------------------------------
    | Filter Permission Groups
    |--------------------------------------------------------------------------
    */

    const filteredPermissionGroups =
        useMemo(() => {

            const term =
                permissionSearch
                    .trim()
                    .toLowerCase();

            const groups =
                roleData?.permission_groups ?? [];


            if (!term) {
                return groups;
            }


            return groups
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
                    | Permission Match
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
            roleData?.permission_groups,
            permissionSearch,
        ]);


    /*
    |--------------------------------------------------------------------------
    | Filtered Permission Count
    |--------------------------------------------------------------------------
    */

    const filteredPermissionCount =
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


    return (

        <SuperAdminLayout
            title={
                formatRole(
                    roleData?.name
                )
            }
            subtitle="Role details and assigned permissions"
            breadcrumbs={[
                {
                    label: 'Roles',
                    href: '/super-admin/roles',
                },
                {
                    label:
                        formatRole(
                            roleData?.name
                        ),
                },
            ]}
        >

            <Head
                title={
                    formatRole(
                        roleData?.name
                    )
                }
            />


            {/* =====================================================
                ROLE HEADER
               ===================================================== */}

            <div className="rr-admin-panel mb-4">

                <div className="rr-role-details-header">

                    <div className="rr-role-details-identity">

                        <div className="rr-role-details-icon">

                            <i className="bi bi-person-badge"></i>

                        </div>


                        <div>

                            <div className="rr-role-details-name">

                                {formatRole(
                                    roleData?.name
                                )}

                            </div>


                            <div className="rr-role-details-code">

                                {roleData?.name}

                            </div>

                        </div>

                    </div>


                    <div className="rr-role-details-actions">

                        <Link
                            href={`/super-admin/roles/${roleData.id}/edit`}
                            className="rr-btn rr-btn-secondary"
                        >
                            <i className="bi bi-pencil"></i>

                            Edit Role
                        </Link>


                        <button
                            type="button"
                            className="rr-btn rr-btn-danger"
                            onClick={() =>
                                setShowDeleteModal(true)
                            }
                        >
                            <i className="bi bi-trash"></i>

                            Delete Role
                        </button>

                    </div>

                </div>

            </div>


            {/* =====================================================
                SUMMARY
               ===================================================== */}

            <div className="row g-3 mb-4">

                <RoleSummaryCard
                    title="Assigned Users"
                    value={
                        roleData?.users_count ?? 0
                    }
                    icon="bi-people"
                    description="Users using this role"
                    type="primary"
                />


                <RoleSummaryCard
                    title="Permissions"
                    value={
                        roleData?.permissions_count ?? 0
                    }
                    icon="bi-shield-check"
                    description="Permissions assigned"
                    type="success"
                />


                <RoleSummaryCard
                    title="Modules"
                    value={
                        roleData?.permission_groups
                            ?.length ?? 0
                    }
                    icon="bi-grid"
                    description="Permission modules"
                    type="info"
                />

            </div>


            {/* =====================================================
                ASSIGNED PERMISSIONS
               ===================================================== */}

            <div className="rr-admin-panel">

                {/* Header */}

                <div className="rr-admin-panel-header">

                    <div className="rr-admin-panel-heading">

                        <h2 className="rr-admin-panel-title">
                            Assigned Permissions
                        </h2>

                        <div className="rr-admin-panel-description">
                            Permissions grouped by module
                        </div>

                    </div>


                    <span className="rr-count-badge">

                        {
                            roleData?.permissions_count
                            ?? 0
                        }

                    </span>

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

                            {filteredPermissionCount}
                            {' '}
                            permissions shown

                        </span>

                    </div>

                </div>


                {/* =================================================
                    PERMISSION GROUPS
                   ================================================= */}

                <div className="rr-admin-panel-body">

                    {filteredPermissionGroups.length > 0 ? (

                        <div className="rr-role-show-modules">

                            {filteredPermissionGroups.map(
                                (group) => (

                                    <RolePermissionGroup
                                        key={
                                            group.module
                                        }
                                        group={
                                            group
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
                                            : 'bi bi-shield'
                                    }
                                ></i>

                            </div>


                            <div className="rr-empty-state-title">

                                {permissionSearch
                                    ? 'No permissions found'
                                    : 'No permissions assigned'}

                            </div>


                            <div className="rr-empty-state-text">

                                {permissionSearch
                                    ? `No permissions match "${permissionSearch}".`
                                    : 'Edit this role to assign permissions.'}

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
                                    <i className="bi bi-x-lg"></i>

                                    Clear Search
                                </button>

                            )}

                        </div>

                    )}

                </div>

            </div>


            <ConfirmModal
                show={showDeleteModal}
                title="Delete Role"
                message={
                    roleData.users_count > 0
                        ? `"${formatRole(roleData.name)}" is assigned to ${roleData.users_count} user(s). Reassign those users before deleting this role.`
                        : `Delete "${formatRole(roleData.name)}"? This action cannot be undone.`
                }
                confirmText="Delete Role"
                icon="bi-trash"
                variant="danger"
                processing={deleting}
                confirmDisabled={
                    Number(
                        roleData.users_count ?? 0
                    ) > 0
                }
                onCancel={() => {

                    if (!deleting) {
                        setShowDeleteModal(false);
                    }

                }}
                onConfirm={() => {

                    if (
                        Number(
                            roleData.users_count ?? 0
                        ) > 0
                    ) {
                        return;
                    }

                    setDeleting(true);

                    router.delete(
                        `/super-admin/roles/${roleData.id}`,
                        {
                            onFinish: () => {
                                setDeleting(false);
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
| Permission Group
|--------------------------------------------------------------------------
*/

function RolePermissionGroup({
    group,
}) {

    return (

        <div className="rr-role-show-module">

            {/* Module Header */}

            <div className="rr-role-show-module-header">

                <div className="rr-role-show-module-left">

                    <div className="rr-role-show-module-icon">

                        <i className="bi bi-grid"></i>

                    </div>


                    <div>

                        <div className="rr-role-show-module-name">

                            {formatText(
                                group.module
                            )}

                        </div>


                        <div className="rr-role-show-module-count">

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


            {/* Permission Items */}

            <div className="rr-role-show-permissions">

                {(group.permissions ?? []).map(
                    (permission) => (

                        <div
                            key={
                                permission.name
                            }
                            className="rr-role-show-permission"
                        >

                            <div className="rr-role-show-permission-check">

                                <i className="bi bi-check-lg"></i>

                            </div>


                            <div>

                                <div className="rr-role-show-permission-action">

                                    {formatText(
                                        permission.action
                                    )}

                                </div>


                                <div className="rr-role-show-permission-code">

                                    {permission.name}

                                </div>

                            </div>

                        </div>

                    )
                )}

            </div>

        </div>

    );
}


/*
|--------------------------------------------------------------------------
| Summary Card
|--------------------------------------------------------------------------
*/

function RoleSummaryCard({
    title,
    value,
    icon,
    description,
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

                        <i
                            className={
                                `bi ${icon}`
                            }
                        ></i>

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
| Format Role
|--------------------------------------------------------------------------
*/

function formatRole(role) {

    return String(
        role ?? ''
    )
        .replaceAll(
            '-',
            ' '
        )
        .replaceAll(
            '_',
            ' '
        )
        .replace(
            /\b\w/g,
            (character) =>
                character.toUpperCase()
        );

}


/*
|--------------------------------------------------------------------------
| Format Permission / Module
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