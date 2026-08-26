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

import SearchableSelect
    from '../../../Components/SearchableSelect';

import ConfirmModal
    from '../../../Components/ConfirmModal';


export default function Index({
    users = [],
}) {

    const [search, setSearch] =
        useState('');

    const [companyFilter, setCompanyFilter] =
        useState('all');

    const [roleFilter, setRoleFilter] =
        useState('all');

    const [statusFilter, setStatusFilter] =
        useState('all');

    const [userAction, setUserAction] =
        useState(null);

    const [processingUser, setProcessingUser] =
        useState(false);

    const [deleteUser, setDeleteUser] =
        useState(null);

    const [deletingUser, setDeletingUser] =
        useState(false);


    /*
    |--------------------------------------------------------------------------
    | Summary
    |--------------------------------------------------------------------------
    */

    const summary = useMemo(() => {

        const total = users.length;

        const active = users.filter(
            (user) => user.status
        ).length;

        const inactive =
            total - active;

        const companyIds = new Set(
            users
                .map((user) => user.company_id)
                .filter(Boolean)
        );

        return {
            total,
            active,
            inactive,
            companies: companyIds.size,
        };

    }, [users]);


    /*
    |--------------------------------------------------------------------------
    | Companies
    |--------------------------------------------------------------------------
    */

    const companies = useMemo(() => {

        const map = new Map();

        users.forEach((user) => {

            if (user.company?.id) {

                map.set(
                    user.company.id,
                    user.company
                );

            }

        });

        return Array.from(
            map.values()
        ).sort((a, b) =>
            a.company_name.localeCompare(
                b.company_name
            )
        );

    }, [users]);


    /*
    |--------------------------------------------------------------------------
    | Roles
    |--------------------------------------------------------------------------
    */

    const roles = useMemo(() => {

        const roleSet = new Set();

        users.forEach((user) => {

            (user.roles ?? [])
                .forEach((role) =>
                    roleSet.add(role)
                );

        });

        return Array.from(roleSet)
            .sort();

    }, [users]);


    /*
    |--------------------------------------------------------------------------
    | Filtering
    |--------------------------------------------------------------------------
    */

    const filteredUsers = useMemo(() => {

        const term =
            search
                .trim()
                .toLowerCase();

        return users.filter((user) => {

            /*
            |--------------------------------------------------------------------------
            | Company
            |--------------------------------------------------------------------------
            */

            if (
                companyFilter !== 'all' &&
                Number(user.company_id) !==
                Number(companyFilter)
            ) {
                return false;
            }


            /*
            |--------------------------------------------------------------------------
            | Role
            |--------------------------------------------------------------------------
            */

            if (
                roleFilter !== 'all' &&
                !(user.roles ?? []).includes(
                    roleFilter
                )
            ) {
                return false;
            }


            /*
            |--------------------------------------------------------------------------
            | Status
            |--------------------------------------------------------------------------
            */

            if (
                statusFilter === 'active' &&
                !user.status
            ) {
                return false;
            }

            if (
                statusFilter === 'inactive' &&
                user.status
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
                user.name,
                user.email,
                user.mobile,
                user.company?.company_name,
                user.company?.company_code,

                ...(user.roles ?? []),

                ...(user.stores ?? [])
                    .flatMap((store) => [
                        store.store_code,
                        store.store_name,
                    ]),
            ]
                .filter(Boolean)
                .some((value) =>
                    String(value)
                        .toLowerCase()
                        .includes(term)
                );

        });

    }, [
        users,
        search,
        companyFilter,
        roleFilter,
        statusFilter,
    ]);


    /*
    |--------------------------------------------------------------------------
    | Date
    |--------------------------------------------------------------------------
    */

    const formatDate = (value) => {

        if (!value) {
            return 'Never';
        }

        const date = new Date(value);

        if (Number.isNaN(date.getTime())) {
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
            title="Users"
            subtitle="Manage users, companies, branches and roles"
            breadcrumbs={[
                {
                    label: 'Users',
                },
            ]}
        >

            <Head title="Users" />


            {/* =========================================================
                SUMMARY
               ========================================================= */}

            <div className="row g-3 mb-4">

                <UserStatCard
                    title="Total Users"
                    value={summary.total}
                    description="Company users"
                    icon="bi-people"
                    type="primary"
                />

                <UserStatCard
                    title="Active"
                    value={summary.active}
                    description="Can access the system"
                    icon="bi-check-circle"
                    type="success"
                />

                <UserStatCard
                    title="Inactive"
                    value={summary.inactive}
                    description="Access disabled"
                    icon="bi-pause-circle"
                    type="warning"
                />

                <UserStatCard
                    title="Companies"
                    value={summary.companies}
                    description="Companies with users"
                    icon="bi-buildings"
                    type="info"
                />

            </div>


            {/* =========================================================
                USER DIRECTORY
               ========================================================= */}

            <div className="rr-admin-panel">

                <div className="rr-admin-panel-header">

                    <div className="rr-admin-panel-heading">

                        <h2 className="rr-admin-panel-title">
                            User Directory
                        </h2>

                        <div className="rr-admin-panel-description">
                            Company users and branch access
                        </div>

                    </div>


                    <Link
                        href="/super-admin/users/create"
                        className="rr-btn rr-btn-primary"
                    >
                        <i className="bi bi-person-plus"></i>

                        New User
                    </Link>

                </div>


                {/* =====================================================
                    TOOLBAR
                   ===================================================== */}

                <div className="rr-user-toolbar">

                    <div className="rr-user-search">

                        <i className="bi bi-search"></i>

                        <input
                            type="text"
                            value={search}
                            onChange={(event) =>
                                setSearch(
                                    event.target.value
                                )
                            }
                            placeholder="Search name, email, branch, role..."
                        />

                    </div>


                    <div className="rr-user-toolbar-filters">

                        {/* Company */}

                        <SearchableSelect
                            value={companyFilter}
                            onChange={setCompanyFilter}
                            allLabel="All Companies"
                            searchPlaceholder="Search companies..."
                            options={
                                companies.map(
                                    (company) => ({
                                        value: company.id,

                                        label:
                                            company.company_code
                                                ? `${company.company_name} (${company.company_code})`
                                                : company.company_name,
                                    })
                                )
                            }
                        />


                        {/* Role */}

                        <select
                            className="rr-user-filter"
                            value={roleFilter}
                            onChange={(event) =>
                                setRoleFilter(
                                    event.target.value
                                )
                            }
                        >

                            <option value="all">
                                All Roles
                            </option>

                            {roles.map((role) => (

                                <option
                                    key={role}
                                    value={role}
                                >
                                    {formatRole(role)}
                                </option>

                            ))}

                        </select>


                        {/* Status */}

                        <select
                            className="rr-user-filter"
                            value={statusFilter}
                            onChange={(event) =>
                                setStatusFilter(
                                    event.target.value
                                )
                            }
                        >

                            <option value="all">
                                All Status
                            </option>

                            <option value="active">
                                Active
                            </option>

                            <option value="inactive">
                                Inactive
                            </option>

                        </select>


                        <div className="rr-user-result-count">
                            {filteredUsers.length} users
                        </div>

                    </div>

                </div>


                {/* =====================================================
                    TABLE
                   ===================================================== */}

                <div className="rr-admin-table-wrapper">

                    <table className="rr-admin-table rr-user-table">

                        <thead>

                            <tr>

                                <th>
                                    User
                                </th>

                                <th>
                                    Company
                                </th>

                                <th>
                                    Branch Access
                                </th>

                                <th>
                                    Role
                                </th>

                                <th>
                                    Mobile
                                </th>

                                <th>
                                    Last Login
                                </th>

                                <th>
                                    Status
                                </th>

                                <th className="text-end">
                                    Actions
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            {filteredUsers.length > 0 ? (

                                filteredUsers.map(
                                    (user) => (

                                        <tr key={user.id}>

                                            {/* User */}

                                            <td>

                                                <div className="rr-user-cell">

                                                    <div className="rr-user-avatar">
                                                        {getInitials(
                                                            user.name
                                                        )}
                                                    </div>

                                                    <div className="rr-user-cell-info">

                                                        <div className="rr-user-cell-name">
                                                            {user.name}
                                                        </div>

                                                        <div className="rr-table-secondary">
                                                            {user.email}
                                                        </div>

                                                    </div>

                                                </div>

                                            </td>


                                            {/* Company */}

                                            <td>

                                                {user.company ? (

                                                    <>
                                                        <div className="rr-user-company-name">
                                                            {user.company.company_name}
                                                        </div>

                                                        <div className="rr-table-secondary">
                                                            {user.company.company_code || '—'}
                                                        </div>
                                                    </>

                                                ) : (

                                                    <span className="text-muted">
                                                        —
                                                    </span>

                                                )}

                                            </td>


                                            {/* Branches */}

                                            <td>

                                                <BranchAccess
                                                    stores={
                                                        user.stores ?? []
                                                    }
                                                />

                                            </td>


                                            {/* Roles */}

                                            <td>

                                                <div className="rr-user-role-list">

                                                    {(user.roles ?? []).length > 0 ? (

                                                        user.roles.map(
                                                            (role) => (

                                                                <span
                                                                    key={role}
                                                                    className="rr-user-role-badge"
                                                                >
                                                                    {formatRole(
                                                                        role
                                                                    )}
                                                                </span>

                                                            )
                                                        )

                                                    ) : (

                                                        <span className="rr-table-secondary">
                                                            No role
                                                        </span>

                                                    )}

                                                </div>

                                            </td>


                                            {/* Mobile */}

                                            <td>
                                                {user.mobile || '—'}
                                            </td>


                                            {/* Login */}

                                            <td>

                                                <span className="rr-user-last-login">
                                                    {formatDate(
                                                        user.last_login_at
                                                    )}
                                                </span>

                                            </td>


                                            {/* Status */}

                                            <td>

                                                <span
                                                    className={`rr-status ${user.status
                                                            ? 'rr-status-active'
                                                            : 'rr-status-inactive'
                                                        }`}
                                                >
                                                    {user.status
                                                        ? 'Active'
                                                        : 'Inactive'}
                                                </span>

                                            </td>


                                            {/* Actions */}

                                            <td className="text-end">

                                                <div className="rr-table-actions">


                                                    <Link
                                                        href={`/super-admin/users/${user.id}`}
                                                        className="rr-icon-action rr-icon-action-primary"
                                                        title="View user details"
                                                    >
                                                        <i className="bi bi-eye"></i>
                                                    </Link>

                                                    <Link
                                                        href={
                                                            `/super-admin/users/${user.id}/edit`
                                                        }
                                                        className="rr-icon-action"
                                                        title="Edit user"
                                                    >
                                                        <i className="bi bi-pencil"></i>
                                                    </Link>

                                                    


                                                    {user.status ? (

                                                        <button
                                                            type="button"
                                                            className="rr-icon-action rr-icon-action-warning"
                                                            title="Deactivate user"
                                                            onClick={() =>
                                                                setUserAction({
                                                                    user,
                                                                    action:
                                                                        'deactivate',
                                                                })
                                                            }
                                                        >
                                                            <i className="bi bi-person-dash"></i>
                                                        </button>

                                                    ) : (

                                                        <button
                                                            type="button"
                                                            className="rr-icon-action rr-icon-action-success"
                                                            title="Activate user"
                                                            onClick={() =>
                                                                setUserAction({
                                                                    user,
                                                                    action:
                                                                        'activate',
                                                                })
                                                            }
                                                        >
                                                            <i className="bi bi-person-check"></i>
                                                        </button>

                                                    )}

                                                    <button
                                                        type="button"
                                                        className="rr-icon-action rr-icon-action-danger"
                                                        title="Delete user"
                                                        onClick={() =>
                                                            setDeleteUser(user)
                                                        }
                                                    >
                                                        <i className="bi bi-trash"></i>
                                                    </button>

                                                </div>

                                            </td>

                                        </tr>

                                    )
                                )

                            ) : (

                                <tr>

                                    <td colSpan="8">

                                        <div className="rr-empty-state">

                                            <div className="rr-empty-state-icon">
                                                <i className="bi bi-people"></i>
                                            </div>

                                            <div className="rr-empty-state-title">
                                                No users found
                                            </div>

                                            <div className="rr-empty-state-text">
                                                Try changing the search or filters.
                                            </div>

                                        </div>

                                    </td>

                                </tr>

                            )}

                        </tbody>

                    </table>

                </div>

            </div>


            {/* =========================================================
                CONFIRMATION
               ========================================================= */}

            <ConfirmModal
                show={Boolean(userAction)}

                title={
                    userAction?.action === 'deactivate'
                        ? 'Deactivate User'
                        : 'Activate User'
                }

                message={
                    userAction
                        ? userAction.action === 'deactivate'
                            ? `Deactivate "${userAction.user.name}"? This user will no longer be able to sign in until activated again.`
                            : `Activate "${userAction.user.name}"? This user will be able to sign in again.`
                        : ''
                }

                confirmText={
                    userAction?.action === 'deactivate'
                        ? 'Deactivate User'
                        : 'Activate User'
                }

                icon={
                    userAction?.action === 'deactivate'
                        ? 'bi-person-dash'
                        : 'bi-person-check'
                }

                variant={
                    userAction?.action === 'deactivate'
                        ? 'danger'
                        : 'primary'
                }

                processing={processingUser}

                onCancel={() => {

                    if (!processingUser) {
                        setUserAction(null);
                    }

                }}

                onConfirm={() => {

                    if (!userAction) {
                        return;
                    }

                    setProcessingUser(true);

                    router.post(
                        `/super-admin/users/${userAction.user.id}/${userAction.action}`,
                        {},
                        {
                            preserveScroll: true,

                            onSuccess: () => {
                                setUserAction(null);
                            },

                            onFinish: () => {
                                setProcessingUser(false);
                            },
                        }
                    );

                }}
            />

            <ConfirmModal
                show={Boolean(deleteUser)}

                title="Delete User"

                message={
                    deleteUser
                        ? `Delete "${deleteUser.name}"? This will permanently remove the user account, branch assignments and role assignment.`
                        : ''
                }

                confirmText="Delete User"

                icon="bi-trash"

                variant="danger"

                processing={deletingUser}

                onCancel={() => {

                    if (!deletingUser) {
                        setDeleteUser(null);
                    }

                }}

                onConfirm={() => {

                    if (!deleteUser) {
                        return;
                    }

                    setDeletingUser(true);

                    router.delete(
                        `/super-admin/users/${deleteUser.id}`,
                        {
                            preserveScroll: true,

                            onSuccess: () => {
                                setDeleteUser(null);
                            },

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
| Branch Access
|--------------------------------------------------------------------------
*/

function BranchAccess({
    stores = [],
}) {

    if (stores.length === 0) {

        return (
            <span className="rr-table-secondary">
                No branches
            </span>
        );
    }


    const visible =
        stores.slice(0, 2);

    const remaining =
        stores.length - visible.length;


    return (
        <div className="rr-user-branch-list">

            {visible.map((store) => (

                <span
                    key={store.id}
                    className="rr-user-branch-badge"
                    title={store.store_name}
                >
                    {store.store_code ||
                        store.store_name}
                </span>

            ))}


            {remaining > 0 && (

                <span
                    className="rr-user-branch-more"
                    title={
                        stores
                            .slice(2)
                            .map(
                                (store) =>
                                    `${store.store_code} - ${store.store_name}`
                            )
                            .join('\n')
                    }
                >
                    +{remaining}
                </span>

            )}

        </div>
    );
}


/*
|--------------------------------------------------------------------------
| Statistics
|--------------------------------------------------------------------------
*/

function UserStatCard({
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
| Helpers
|--------------------------------------------------------------------------
*/

function formatRole(role) {

    if (!role) {
        return '';
    }

    return role
        .replaceAll('-', ' ')
        .replaceAll('_', ' ')
        .replace(
            /\b\w/g,
            (character) =>
                character.toUpperCase()
        );
}


function getInitials(name) {

    if (!name) {
        return 'U';
    }

    const parts =
        name
            .trim()
            .split(/\s+/)
            .filter(Boolean);

    if (parts.length === 1) {

        return parts[0]
            .substring(0, 2)
            .toUpperCase();

    }

    return (
        parts[0].charAt(0) +
        parts[parts.length - 1].charAt(0)
    ).toUpperCase();
}