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
    userData,
}) {

    const [statusAction, setStatusAction] =
        useState(null);

    const [processing, setProcessing] =
        useState(false);

    const [permissionSearch, setPermissionSearch] =
        useState('');

    const [showDeleteModal, setShowDeleteModal] =
        useState(false);

    const [deletingUser, setDeletingUser] =
        useState(false);


    /*
    |--------------------------------------------------------------------------
    | Filter Effective Permissions
    |--------------------------------------------------------------------------
    */

    const filteredPermissionGroups =
        useMemo(() => {

            const term =
                permissionSearch
                    .trim()
                    .toLowerCase();

            const groups =
                userData.permission_groups ?? [];

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
            userData.permission_groups,
            permissionSearch,
        ]);


    /*
    |--------------------------------------------------------------------------
    | Number of Filtered Permissions
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


    /*
    |--------------------------------------------------------------------------
    | Date Formatter
    |--------------------------------------------------------------------------
    */

    const formatDate = (value) => {

        if (!value) {
            return 'Never';
        }

        const date =
            new Date(value);

        if (
            Number.isNaN(
                date.getTime()
            )
        ) {
            return '—';
        }


        return new Intl.DateTimeFormat(
            'en-IN',
            {
                day: '2-digit',
                month: 'short',
                year: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
            }
        ).format(date);

    };


    return (

        <SuperAdminLayout
            title={
                userData?.name ?? 'User'
            }
            subtitle="User account and access details"
            breadcrumbs={[
                {
                    label: 'Users',
                    href: '/super-admin/users',
                },
                {
                    label:
                        userData?.name ?? 'User',
                },
            ]}
        >

            <Head
                title={
                    userData?.name ?? 'User'
                }
            />


            {/* =====================================================
                USER HEADER
               ===================================================== */}

            <div className="rr-admin-panel mb-4">

                <div className="rr-user-details-header">

                    <div className="rr-user-details-identity">

                        <div className="rr-user-details-avatar">

                            {getInitials(
                                userData?.name
                            )}

                        </div>


                        <div>

                            <div className="rr-user-details-name">

                                {userData?.name}

                            </div>

                            <div className="rr-user-details-email">

                                {userData?.email}

                            </div>

                        </div>

                    </div>


                    <div className="rr-user-details-actions">

                        <span
                            className={
                                `rr-status ${userData?.status
                                    ? 'rr-status-active'
                                    : 'rr-status-inactive'
                                }`
                            }
                        >

                            {userData?.status
                                ? 'Active'
                                : 'Inactive'}

                        </span>


                        <Link
                            href={
                                `/super-admin/users/${userData.id}/edit`
                            }
                            className="rr-btn rr-btn-secondary"
                        >
                            <i className="bi bi-pencil"></i>

                            Edit User
                        </Link>


                        {userData?.status ? (

                            <button
                                type="button"
                                className="rr-btn rr-btn-danger"
                                onClick={() =>
                                    setStatusAction(
                                        'deactivate'
                                    )
                                }
                            >
                                <i className="bi bi-person-dash"></i>

                                Deactivate
                            </button>

                        ) : (

                            <button
                                type="button"
                                className="rr-btn rr-btn-primary"
                                onClick={() =>
                                    setStatusAction(
                                        'activate'
                                    )
                                }
                            >
                                <i className="bi bi-person-check"></i>

                                Activate
                            </button>

                        )}

                        <button
                            type="button"
                            className="rr-btn rr-btn-danger"
                            onClick={() =>
                                setShowDeleteModal(true)
                            }
                        >
                            <i className="bi bi-trash"></i>

                            Delete User
                        </button>

                    </div>

                </div>

            </div>


            {/* =====================================================
                ACCOUNT + COMPANY SUMMARY
               ===================================================== */}

            <div className="row g-3 mb-4">

                {/* Account Information */}

                <div className="col-xl-6">

                    <div className="rr-admin-panel h-100">

                        <div className="rr-admin-panel-header">

                            <div className="rr-admin-panel-heading">

                                <h2 className="rr-admin-panel-title">
                                    Account Information
                                </h2>

                                <div className="rr-admin-panel-description">
                                    Login and contact details
                                </div>

                            </div>

                        </div>


                        <div className="rr-user-info-grid">

                            <InfoItem
                                label="Full Name"
                                value={
                                    userData?.name
                                }
                            />

                            <InfoItem
                                label="Email"
                                value={
                                    userData?.email
                                }
                            />

                            <InfoItem
                                label="Mobile"
                                value={
                                    userData?.mobile
                                }
                            />

                            <InfoItem
                                label="Last Login"
                                value={
                                    formatDate(
                                        userData?.last_login_at
                                    )
                                }
                            />

                        </div>

                    </div>

                </div>


                {/* Company & Role */}

                <div className="col-xl-6">

                    <div className="rr-admin-panel h-100">

                        <div className="rr-admin-panel-header">

                            <div className="rr-admin-panel-heading">

                                <h2 className="rr-admin-panel-title">
                                    Company & Role
                                </h2>

                                <div className="rr-admin-panel-description">
                                    Organisation and access role
                                </div>

                            </div>

                        </div>


                        <div className="rr-user-info-grid">

                            <InfoItem
                                label="Company"
                                value={
                                    userData?.company
                                        ?.company_name
                                }
                            />

                            <InfoItem
                                label="Company Code"
                                value={
                                    userData?.company
                                        ?.company_code
                                }
                            />

                            <InfoItem
                                label="Role"
                                value={
                                    userData?.roles
                                        ?.length
                                        ? userData.roles
                                            .map(
                                                (role) =>
                                                    formatRole(
                                                        role.name
                                                    )
                                            )
                                            .join(', ')
                                        : 'No role assigned'
                                }
                            />

                            <InfoItem
                                label="Assigned Branches"
                                value={
                                    userData?.stores
                                        ?.length ?? 0
                                }
                            />

                        </div>

                    </div>

                </div>

            </div>


            {/* =====================================================
                BRANCH ACCESS
               ===================================================== */}

            <div className="rr-admin-panel mb-4">

                <div className="rr-admin-panel-header">

                    <div className="rr-admin-panel-heading">

                        <h2 className="rr-admin-panel-title">
                            Branch Access
                        </h2>

                        <div className="rr-admin-panel-description">
                            Branches assigned to this user
                        </div>

                    </div>


                    <span className="rr-count-badge">

                        {
                            userData?.stores
                                ?.length ?? 0
                        }

                    </span>

                </div>


                <div className="rr-admin-table-wrapper">

                    <table className="rr-admin-table">

                        <thead>

                            <tr>

                                <th>
                                    Branch Code
                                </th>

                                <th>
                                    Branch
                                </th>

                                <th>
                                    Location
                                </th>

                                <th>
                                    Status
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            {userData?.stores?.length > 0 ? (

                                userData.stores.map(
                                    (store) => (

                                        <tr
                                            key={
                                                store.id
                                            }
                                        >

                                            <td>

                                                <span className="rr-branch-code">

                                                    {
                                                        store.store_code
                                                    }

                                                </span>

                                            </td>


                                            <td>

                                                <div className="rr-branch-table-cell">

                                                    <div className="rr-branch-table-icon">

                                                        <i className="bi bi-shop"></i>

                                                    </div>


                                                    <div>

                                                        <div className="rr-branch-table-name">

                                                            {
                                                                store.store_name
                                                            }

                                                        </div>

                                                    </div>

                                                </div>

                                            </td>


                                            <td>

                                                <div>

                                                    {
                                                        store.city
                                                        || '—'
                                                    }

                                                </div>


                                                {store.state && (

                                                    <div className="rr-table-secondary">

                                                        {
                                                            store.state
                                                        }

                                                    </div>

                                                )}

                                            </td>


                                            <td>

                                                <span
                                                    className={
                                                        `rr-status ${store.status
                                                            ? 'rr-status-active'
                                                            : 'rr-status-inactive'
                                                        }`
                                                    }
                                                >

                                                    {
                                                        store.status
                                                            ? 'Active'
                                                            : 'Inactive'
                                                    }

                                                </span>

                                            </td>

                                        </tr>

                                    )
                                )

                            ) : (

                                <tr>

                                    <td colSpan="4">

                                        <div className="rr-empty-state">

                                            <div className="rr-empty-state-icon">

                                                <i className="bi bi-shop"></i>

                                            </div>

                                            <div className="rr-empty-state-title">
                                                No branch access
                                            </div>

                                            <div className="rr-empty-state-text">
                                                This user has not been assigned to any branches.
                                            </div>

                                        </div>

                                    </td>

                                </tr>

                            )}

                        </tbody>

                    </table>

                </div>

            </div>


            {/* =====================================================
                EFFECTIVE PERMISSIONS
               ===================================================== */}

            <div className="rr-admin-panel">

                <div className="rr-admin-panel-header">

                    <div className="rr-admin-panel-heading">

                        <h2 className="rr-admin-panel-title">
                            Effective Permissions
                        </h2>

                        <div className="rr-admin-panel-description">
                            Permissions inherited from the user's assigned role
                        </div>

                    </div>


                    <span className="rr-count-badge">

                        {
                            userData?.permissions_count
                            ?? 0
                        }

                    </span>

                </div>


                {/* =================================================
                    PERMISSION SEARCH
                   ================================================= */}

                <div className="rr-user-permission-toolbar">

                    <div className="rr-user-permission-search">

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
                                className="rr-user-permission-clear"
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


                    <div className="rr-user-permission-result-count">

                        {filteredPermissionCount}
                        {' '}
                        permissions shown

                    </div>

                </div>


                {/* =================================================
                    PERMISSION GROUPS
                   ================================================= */}

                <div className="rr-admin-panel-body">

                    {filteredPermissionGroups.length > 0 ? (

                        <div className="rr-user-permission-modules">

                            {filteredPermissionGroups.map(
                                (group) => (

                                    <UserPermissionGroup
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
                                    : 'This user has no effective permissions.'}

                            </div>

                        </div>

                    )}

                </div>

            </div>


            {/* =====================================================
                STATUS CONFIRMATION
               ===================================================== */}

            <ConfirmModal
                show={
                    Boolean(
                        statusAction
                    )
                }

                title={
                    statusAction ===
                        'deactivate'
                        ? 'Deactivate User'
                        : 'Activate User'
                }

                message={
                    statusAction ===
                        'deactivate'
                        ? `Deactivate "${userData.name}"? This user will no longer be able to sign in until activated again.`
                        : `Activate "${userData.name}"? This user will be able to sign in again.`
                }

                confirmText={
                    statusAction ===
                        'deactivate'
                        ? 'Deactivate User'
                        : 'Activate User'
                }

                icon={
                    statusAction ===
                        'deactivate'
                        ? 'bi-person-dash'
                        : 'bi-person-check'
                }

                variant={
                    statusAction ===
                        'deactivate'
                        ? 'danger'
                        : 'primary'
                }

                processing={
                    processing
                }

                onCancel={() => {

                    if (!processing) {

                        setStatusAction(
                            null
                        );

                    }

                }}

                onConfirm={() => {

                    if (!statusAction) {
                        return;
                    }

                    setProcessing(
                        true
                    );


                    router.post(
                        `/super-admin/users/${userData.id}/${statusAction}`,
                        {},
                        {
                            preserveScroll:
                                true,

                            onSuccess: () => {

                                setStatusAction(
                                    null
                                );

                            },

                            onFinish: () => {

                                setProcessing(
                                    false
                                );

                            },
                        }
                    );

                }}
            />

            <ConfirmModal
                show={showDeleteModal}

                title="Delete User"

                message={
                    `Delete "${userData.name}"? This account will be permanently removed along with its branch and role assignments.`
                }

                confirmText="Delete User"

                icon="bi-trash"

                variant="danger"

                processing={deletingUser}

                onCancel={() => {

                    if (!deletingUser) {
                        setShowDeleteModal(false);
                    }

                }}

                onConfirm={() => {

                    setDeletingUser(true);

                    router.delete(
                        `/super-admin/users/${userData.id}`,
                        {
                            onFinish: () => {
                                setDeletingUser(false);
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
| Information Item
|--------------------------------------------------------------------------
*/

function InfoItem({
    label,
    value,
}) {

    return (

        <div className="rr-user-info-item">

            <div className="rr-user-info-label">
                {label}
            </div>

            <div className="rr-user-info-value">

                {value !== null &&
                    value !== undefined &&
                    value !== ''
                    ? value
                    : '—'}

            </div>

        </div>

    );
}


/*
|--------------------------------------------------------------------------
| Effective Permission Module
|--------------------------------------------------------------------------
*/

function UserPermissionGroup({
    group,
}) {

    return (

        <div className="rr-user-permission-module">

            {/* Module Header */}

            <div className="rr-user-permission-module-header">

                <div className="rr-user-permission-module-left">

                    <div className="rr-user-permission-module-icon">

                        <i className="bi bi-grid"></i>

                    </div>


                    <div>

                        <div className="rr-user-permission-module-name">

                            {formatPermissionText(
                                group.module
                            )}

                        </div>

                        <div className="rr-user-permission-module-count">

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


            {/* Permissions */}

            <div className="rr-user-permission-grid">

                {(group.permissions ?? []).map(
                    (permission) => (

                        <div
                            key={
                                permission.name
                            }
                            className="rr-user-permission-item"
                        >

                            <div className="rr-user-permission-check">

                                <i className="bi bi-check-lg"></i>

                            </div>


                            <div className="rr-user-permission-info">

                                <div className="rr-user-permission-action">

                                    {formatPermissionText(
                                        permission.action
                                    )}

                                </div>


                                <div className="rr-user-permission-code">

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
| Initials
|--------------------------------------------------------------------------
*/

function getInitials(name) {

    if (!name) {
        return 'U';
    }


    const parts =
        name
            .trim()
            .split(/\s+/)
            .filter(Boolean);


    if (
        parts.length === 1
    ) {

        return parts[0]
            .substring(
                0,
                2
            )
            .toUpperCase();

    }


    return (
        parts[0].charAt(0) +
        parts[
            parts.length - 1
        ].charAt(0)
    ).toUpperCase();

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
| Format Permission Text
|--------------------------------------------------------------------------
*/

function formatPermissionText(value) {

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