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
    roles = [],
}) {

    const [search, setSearch] =
        useState('');

    const [deleteRole, setDeleteRole] =
        useState(null);

    const [deleting, setDeleting] =
        useState(false);


    const filteredRoles =
        useMemo(() => {

            const term =
                search
                    .trim()
                    .toLowerCase();

            if (!term) {
                return roles;
            }

            return roles.filter(
                (role) =>
                    role.name
                        .toLowerCase()
                        .includes(term)
            );

        }, [
            roles,
            search,
        ]);


    const totalUsers =
        roles.reduce(
            (total, role) =>
                total +
                Number(
                    role.users_count ?? 0
                ),
            0
        );


    return (

        <SuperAdminLayout
            title="Roles"
            subtitle="Manage roles and permission sets"
            breadcrumbs={[
                {
                    label: 'Roles',
                },
            ]}
        >

            <Head title="Roles" />


            {/* Summary */}

            <div className="row g-3 mb-4">

                <RoleStatCard
                    title="Total Roles"
                    value={roles.length}
                    description="Configured roles"
                    icon="bi-person-badge"
                />

                <RoleStatCard
                    title="Assigned Users"
                    value={totalUsers}
                    description="Users with roles"
                    icon="bi-people"
                    type="success"
                />

            </div>


            {/* Directory */}

            <div className="rr-admin-panel">

                <div className="rr-admin-panel-header">

                    <div className="rr-admin-panel-heading">

                        <h2 className="rr-admin-panel-title">
                            Role Directory
                        </h2>

                        <div className="rr-admin-panel-description">
                            Roles control what company users can access
                        </div>

                    </div>


                    <Link
                        href="/super-admin/roles/create"
                        className="rr-btn rr-btn-primary"
                    >
                        <i className="bi bi-plus-lg"></i>

                        New Role
                    </Link>

                </div>


                <div className="rr-role-toolbar">

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
                            placeholder="Search roles..."
                        />

                    </div>


                    <div className="rr-user-result-count">
                        {filteredRoles.length} roles
                    </div>

                </div>


                <div className="rr-admin-table-wrapper">

                    <table className="rr-admin-table">

                        <thead>

                            <tr>

                                <th>
                                    Role
                                </th>

                                <th className="text-center">
                                    Users
                                </th>

                                <th className="text-center">
                                    Permissions
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

                            {filteredRoles.length > 0 ? (

                                filteredRoles.map(
                                    (role) => (

                                        <tr key={role.id}>

                                            <td>

                                                <div className="rr-role-cell">

                                                    <div className="rr-role-icon">
                                                        <i className="bi bi-person-badge"></i>
                                                    </div>

                                                    <div>

                                                        <div className="rr-role-name">
                                                            {formatRole(
                                                                role.name
                                                            )}
                                                        </div>

                                                        <div className="rr-table-secondary">
                                                            {role.name}
                                                        </div>

                                                    </div>

                                                </div>

                                            </td>


                                            <td className="text-center">

                                                <span className="rr-count-badge">
                                                    {role.users_count}
                                                </span>

                                            </td>


                                            <td className="text-center">

                                                <span className="rr-count-badge">
                                                    {role.permissions_count}
                                                </span>

                                            </td>


                                            <td>
                                                {role.guard_name}
                                            </td>


                                            <td className="text-end">

                                                <div className="rr-table-actions">

                                                    <Link
                                                        href={`/super-admin/roles/${role.id}`}
                                                        className="rr-icon-action rr-icon-action-primary"
                                                        title="View role"
                                                    >
                                                        <i className="bi bi-eye"></i>
                                                    </Link>


                                                    <Link
                                                        href={`/super-admin/roles/${role.id}/edit`}
                                                        className="rr-icon-action"
                                                        title="Edit role"
                                                    >
                                                        <i className="bi bi-pencil"></i>
                                                    </Link>


                                                    <button
                                                        type="button"
                                                        className="rr-icon-action rr-icon-action-danger"
                                                        title={
                                                            Number(role.users_count ?? 0) > 0
                                                                ? 'Role is assigned to users'
                                                                : 'Delete role'
                                                        }
                                                        onClick={() =>
                                                            setDeleteRole(role)
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

                                    <td colSpan="5">

                                        <div className="rr-empty-state">

                                            <div className="rr-empty-state-icon">
                                                <i className="bi bi-person-badge"></i>
                                            </div>

                                            <div className="rr-empty-state-title">
                                                No roles found
                                            </div>

                                        </div>

                                    </td>

                                </tr>

                            )}

                        </tbody>

                    </table>

                </div>

            </div>

            <ConfirmModal
                show={Boolean(deleteRole)}

                title="Delete Role"

                message={
                    deleteRole
                        ? Number(deleteRole.users_count ?? 0) > 0
                            ? `"${formatRole(deleteRole.name)}" is assigned to ${deleteRole.users_count} user(s). Reassign those users before deleting this role.`
                            : `Delete "${formatRole(deleteRole.name)}"? This role and its permission assignments will be permanently removed.`
                        : ''
                }

                confirmText="Delete Role"

                icon="bi-trash"

                variant="danger"

                processing={deleting}

                confirmDisabled={
                    Number(
                        deleteRole?.users_count ?? 0
                    ) > 0
                }

                onCancel={() => {

                    if (!deleting) {
                        setDeleteRole(null);
                    }

                }}

                onConfirm={() => {

                    if (!deleteRole) {
                        return;
                    }

                    if (
                        Number(
                            deleteRole.users_count ?? 0
                        ) > 0
                    ) {
                        return;
                    }

                    setDeleting(true);

                    router.delete(
                        `/super-admin/roles/${deleteRole.id}`,
                        {
                            preserveScroll: true,

                            onSuccess: () => {
                                setDeleteRole(null);
                            },

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


function RoleStatCard({
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


function formatRole(role) {

    return String(role ?? '')
        .replaceAll('-', ' ')
        .replaceAll('_', ' ')
        .replace(
            /\b\w/g,
            (character) =>
                character.toUpperCase()
        );
}