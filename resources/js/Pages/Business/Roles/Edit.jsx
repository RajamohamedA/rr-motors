import {
    Head,
    Link,
    useForm,
} from '@inertiajs/react';

import BusinessLayout
    from '../../../Layouts/BusinessLayout';

import PageShell
    from '../../../Components/Business/PageShell';


export default function RoleEdit({
    company,
    managedRole,
    permissionGroups,
}) {

    /*
    |--------------------------------------------------------------------------
    | Form
    |--------------------------------------------------------------------------
    */

    const {
        data,
        setData,
        put,
        processing,
        errors,
    } = useForm({

        name:
            managedRole?.name
            ?? '',

        description:
            managedRole?.description
            ?? '',

        status:
            Boolean(
                managedRole?.status
            ),

        permissions:
            managedRole?.permission_ids
            ?? [],
    });


    /*
    |--------------------------------------------------------------------------
    | Toggle Permission
    |--------------------------------------------------------------------------
    */

    const togglePermission =
        permissionId => {

            const current =
                Array.isArray(
                    data.permissions
                )
                    ? data.permissions
                    : [];


            const exists =
                current.includes(
                    permissionId
                );


            setData(
                'permissions',
                exists
                    ? current.filter(
                        id =>
                            id !== permissionId
                    )
                    : [
                        ...current,
                        permissionId,
                    ]
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Select Group
    |--------------------------------------------------------------------------
    */

    const selectGroup =
        group => {

            const ids =
                (
                    group?.permissions
                    ?? []
                ).map(
                    permission =>
                        permission.id
                );


            setData(
                'permissions',
                Array.from(
                    new Set([
                        ...data.permissions,
                        ...ids,
                    ])
                )
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Clear Group
    |--------------------------------------------------------------------------
    */

    const clearGroup =
        group => {

            const ids =
                new Set(
                    (
                        group?.permissions
                        ?? []
                    ).map(
                        permission =>
                            permission.id
                    )
                );


            setData(
                'permissions',
                data.permissions.filter(
                    id =>
                        !ids.has(id)
                )
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Select All
    |--------------------------------------------------------------------------
    */

    const selectAll =
        () => {

            const ids =
                (
                    permissionGroups
                    ?? []
                )
                    .flatMap(
                        group =>
                            group.permissions
                            ?? []
                    )
                    .map(
                        permission =>
                            permission.id
                    );


            setData(
                'permissions',
                ids
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Clear All
    |--------------------------------------------------------------------------
    */

    const clearAll =
        () => {

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

    const submit =
        event => {

            event.preventDefault();


            put(
                `/company/roles/${managedRole.id}`,
                {
                    preserveScroll:
                        true,
                }
            );
        };


    return (

        <BusinessLayout>

            <Head
                title={
                    `Edit ${managedRole?.name
                    ?? 'Role'
                    }`
                }
            />


            <PageShell
                eyebrow="Company Management"
                title="Edit Role"
                description="Update the role details and permission access."
                icon="bi-shield-gear"
                breadcrumb={[
                    {
                        label:
                            'Company',
                        href:
                            '/company/dashboard',
                    },

                    {
                        label:
                            'Roles & Permissions',
                        href:
                            '/company/roles',
                    },

                    {
                        label:
                            managedRole?.name
                            ?? 'Role',
                        href:
                            `/company/roles/${managedRole.id}`,
                    },

                    {
                        label:
                            'Edit',
                    },
                ]}
                actions={
                    <>
                        <Link
                            href={
                                `/company/roles/${managedRole.id}`
                            }
                            className="rr-page-secondary-btn"
                        >
                            <i className="bi bi-eye"></i>

                            View
                        </Link>


                        <Link
                            href="/company/roles"
                            className="rr-page-secondary-btn"
                        >
                            <i className="bi bi-arrow-left"></i>

                            Back
                        </Link>
                    </>
                }
            >

                {/* =====================================================
                    ROLE CONTEXT
                   ===================================================== */}

                <section className="rr-role-create-context">

                    <div className="rr-role-create-context-main">

                        <div className="rr-role-create-context-avatar">

                            <i className="bi bi-person-badge"></i>

                        </div>


                        <div>

                            <span>
                                Role
                            </span>

                            <strong>
                                {
                                    managedRole?.name
                                    ?? 'Role'
                                }
                            </strong>

                            <small>
                                {
                                    company?.company_name
                                    ?? 'Company'
                                }
                            </small>

                        </div>

                    </div>


                    <div className="rr-role-create-context-note">

                        <i className="bi bi-shield-lock"></i>

                        <span>
                            This role belongs only to this company.
                        </span>

                    </div>

                </section>


                {/* =====================================================
                    FORM
                   ===================================================== */}

                <form
                    onSubmit={
                        submit
                    }
                    className="rr-role-create-form"
                >

                    {/* =================================================
                        ROLE INFORMATION
                       ================================================= */}

                    <section className="rr-page-panel rr-role-create-panel">

                        <div className="rr-page-panel-header">

                            <div className="rr-page-panel-title">

                                <h2>
                                    Role Information
                                </h2>

                                <p>
                                    Update role name, description and status
                                </p>

                            </div>


                            <div className="rr-role-create-panel-icon">

                                <i className="bi bi-person-badge"></i>

                            </div>

                        </div>


                        <div className="rr-role-create-panel-body">

                            <div className="rr-role-form-grid">

                                <FormField
                                    label="Role Name"
                                    required
                                    error={
                                        errors.name
                                    }
                                >

                                    <input
                                        type="text"
                                        value={
                                            data.name
                                        }
                                        onChange={
                                            event =>
                                                setData(
                                                    'name',
                                                    event.target.value
                                                )
                                        }
                                        autoFocus
                                    />

                                </FormField>


                                <FormField
                                    label="Status"
                                    error={
                                        errors.status
                                    }
                                >

                                    <label className="rr-role-status-switch">

                                        <input
                                            type="checkbox"
                                            checked={
                                                Boolean(
                                                    data.status
                                                )
                                            }
                                            onChange={
                                                event =>
                                                    setData(
                                                        'status',
                                                        event.target.checked
                                                    )
                                            }
                                        />


                                        <span className="rr-role-status-slider"></span>


                                        <div>

                                            <strong>

                                                {
                                                    data.status
                                                        ? 'Active'
                                                        : 'Inactive'
                                                }

                                            </strong>

                                            <small>

                                                {
                                                    data.status
                                                        ? 'Role can be assigned to users.'
                                                        : 'Role is currently disabled.'
                                                }

                                            </small>

                                        </div>

                                    </label>

                                </FormField>


                                <FormField
                                    label="Description"
                                    error={
                                        errors.description
                                    }
                                    wide
                                >

                                    <textarea
                                        rows="3"
                                        value={
                                            data.description
                                        }
                                        onChange={
                                            event =>
                                                setData(
                                                    'description',
                                                    event.target.value
                                                )
                                        }
                                        placeholder="Describe what this role is used for..."
                                    />

                                </FormField>

                            </div>

                        </div>

                    </section>


                    {/* =================================================
                        PERMISSIONS
                       ================================================= */}

                    <section className="rr-page-panel rr-role-permission-panel">

                        <div className="rr-role-permission-header">

                            <div>

                                <span>
                                    Access Control
                                </span>

                                <strong>
                                    Permissions
                                </strong>

                                <small>
                                    Update what users with this role can view or manage.
                                </small>

                            </div>


                            <div className="rr-role-permission-actions">

                                <span>
                                    {
                                        data.permissions.length
                                    } selected
                                </span>


                                <button
                                    type="button"
                                    onClick={
                                        selectAll
                                    }
                                >

                                    <i className="bi bi-check2-all"></i>

                                    Select All

                                </button>


                                <button
                                    type="button"
                                    onClick={
                                        clearAll
                                    }
                                >

                                    <i className="bi bi-x-lg"></i>

                                    Clear

                                </button>

                            </div>

                        </div>


                        {
                            errors.permissions && (

                                <div className="rr-role-form-error rr-role-permission-error">

                                    <i className="bi bi-exclamation-circle"></i>

                                    {
                                        errors.permissions
                                    }

                                </div>

                            )
                        }


                        <div className="rr-role-permission-table-wrap">

                            <table className="rr-role-permission-table">

                                <thead>

                                    <tr>

                                        <th>
                                            Module
                                        </th>

                                        <th>
                                            Permissions
                                        </th>

                                        <th className="rr-role-permission-group-actions-head">
                                            Group
                                        </th>

                                    </tr>

                                </thead>


                                <tbody>

                                    {
                                        (
                                            permissionGroups
                                            ?? []
                                        ).map(
                                            group => (

                                                <PermissionGroupRow
                                                    key={
                                                        group.module
                                                    }

                                                    group={
                                                        group
                                                    }

                                                    selectedPermissions={
                                                        data.permissions
                                                    }

                                                    onToggle={
                                                        togglePermission
                                                    }

                                                    onSelectGroup={
                                                        selectGroup
                                                    }

                                                    onClearGroup={
                                                        clearGroup
                                                    }
                                                />

                                            )
                                        )
                                    }

                                </tbody>

                            </table>

                        </div>

                    </section>


                    {/* =================================================
                        ACTION BAR
                       ================================================= */}

                    <div className="rr-role-create-actions">

                        <div className="rr-role-create-actions-info">

                            <i className="bi bi-info-circle"></i>

                            <span>
                                Permission changes affect every user currently assigned to this role.
                            </span>

                        </div>


                        <div>

                            <Link
                                href={
                                    `/company/roles/${managedRole.id}`
                                }
                                className="rr-page-secondary-btn"
                            >
                                Cancel
                            </Link>


                            <button
                                type="submit"
                                className="rr-page-primary-btn"
                                disabled={
                                    processing
                                }
                            >

                                {
                                    processing
                                        ? (
                                            <>
                                                <span className="spinner-border spinner-border-sm"></span>

                                                Saving...
                                            </>
                                        )
                                        : (
                                            <>
                                                <i className="bi bi-check2"></i>

                                                Save Changes
                                            </>
                                        )
                                }

                            </button>

                        </div>

                    </div>

                </form>

            </PageShell>

        </BusinessLayout>
    );
}


/*
|--------------------------------------------------------------------------
| Permission Group
|--------------------------------------------------------------------------
*/

function PermissionGroupRow({
    group,
    selectedPermissions,
    onToggle,
    onSelectGroup,
    onClearGroup,
}) {

    const permissions =
        group?.permissions
        ?? [];


    const selectedCount =
        permissions.filter(
            permission =>
                selectedPermissions.includes(
                    permission.id
                )
        ).length;


    const allSelected =
        permissions.length > 0 &&
        selectedCount ===
        permissions.length;


    return (

        <tr>

            <td>

                <div className="rr-role-permission-module">

                    <div>

                        <i className="bi bi-grid"></i>

                    </div>


                    <section>

                        <strong>
                            {
                                group?.label
                                ?? group?.module
                                ?? 'Module'
                            }
                        </strong>

                        <span>
                            {
                                selectedCount
                            } / {
                                permissions.length
                            } selected
                        </span>

                    </section>

                </div>

            </td>


            <td>

                <div className="rr-role-permission-options">

                    {
                        permissions.map(
                            permission => {

                                const selected =
                                    selectedPermissions.includes(
                                        permission.id
                                    );


                                return (

                                    <label
                                        key={
                                            permission.id
                                        }
                                        className={
                                            `rr-role-permission-option ${selected
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
                                            onChange={
                                                () =>
                                                    onToggle(
                                                        permission.id
                                                    )
                                            }
                                        />


                                        <span className="rr-role-permission-checkbox">

                                            <i className="bi bi-check"></i>

                                        </span>


                                        <span>
                                            {
                                                permission.label
                                                ?? permission.action
                                            }
                                        </span>

                                    </label>

                                );
                            }
                        )
                    }

                </div>

            </td>


            <td>

                <div className="rr-role-group-actions">

                    {
                        allSelected
                            ? (

                                <button
                                    type="button"
                                    onClick={
                                        () =>
                                            onClearGroup(
                                                group
                                            )
                                    }
                                >
                                    Clear
                                </button>

                            )
                            : (

                                <button
                                    type="button"
                                    onClick={
                                        () =>
                                            onSelectGroup(
                                                group
                                            )
                                    }
                                >
                                    Select
                                </button>

                            )
                    }

                </div>

            </td>

        </tr>
    );
}


/*
|--------------------------------------------------------------------------
| Form Field
|--------------------------------------------------------------------------
*/

function FormField({
    label,
    required = false,
    error = null,
    wide = false,
    children,
}) {

    return (

        <div
            className={
                `rr-role-form-field ${wide
                    ? 'wide'
                    : ''
                }`
            }
        >

            <label>

                {label}

                {
                    required && (
                        <span>
                            *
                        </span>
                    )
                }

            </label>


            <div
                className={
                    `rr-role-form-control ${error
                        ? 'has-error'
                        : ''
                    }`
                }
            >

                {children}

            </div>


            {
                error && (

                    <div className="rr-role-form-error">

                        <i className="bi bi-exclamation-circle"></i>

                        {
                            error
                        }

                    </div>

                )
            }

        </div>
    );
}