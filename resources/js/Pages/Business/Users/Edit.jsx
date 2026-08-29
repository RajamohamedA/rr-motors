import {
    Head,
    Link,
    useForm,
} from '@inertiajs/react';

import BusinessLayout
    from '../../../Layouts/BusinessLayout';

import PageShell
    from '../../../Components/Business/PageShell';


export default function UserEdit({
    company,
    managedUser,
    roles,
    branches,
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
            managedUser?.name
            ?? '',

        email:
            managedUser?.email
            ?? '',

        mobile:
            managedUser?.mobile
            ?? '',

        password:
            '',

        password_confirmation:
            '',

        role:
            managedUser?.role
            ?? '',

        store_ids:
            managedUser?.store_ids
            ?? [],

        status:
            Boolean(
                managedUser?.status
            ),
    });


    /*
    |--------------------------------------------------------------------------
    | Toggle Branch
    |--------------------------------------------------------------------------
    */

    const toggleBranch =
        (
            branchId
        ) => {

            const current =
                Array.isArray(
                    data.store_ids
                )
                    ? data.store_ids
                    : [];


            const exists =
                current.includes(
                    branchId
                );


            setData(
                'store_ids',
                exists
                    ? current.filter(
                        id =>
                            id !== branchId
                    )
                    : [
                        ...current,
                        branchId,
                    ]
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Select All Branches
    |--------------------------------------------------------------------------
    */

    const selectAllBranches =
        () => {

            setData(
                'store_ids',
                (
                    branches
                    ?? []
                ).map(
                    branch =>
                        branch.id
                )
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Clear Branches
    |--------------------------------------------------------------------------
    */

    const clearBranches =
        () => {

            setData(
                'store_ids',
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
                `/company/users/${managedUser.id}`,
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
                    `Edit ${managedUser?.name
                    ?? 'User'
                    }`
                }
            />


            <PageShell
                eyebrow="Company Management"
                title="Edit User"
                description="Update employee details, role and branch access."
                icon="bi-person-gear"
                breadcrumb={[
                    {
                        label:
                            'Company',
                        href:
                            '/company/dashboard',
                    },

                    {
                        label:
                            'Users & Access',
                        href:
                            '/company/users',
                    },

                    {
                        label:
                            managedUser?.name
                            ?? 'User',
                        href:
                            `/company/users/${managedUser.id}`,
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
                                `/company/users/${managedUser.id}`
                            }
                            className="rr-page-secondary-btn"
                        >
                            <i className="bi bi-eye"></i>

                            View
                        </Link>

                        <Link
                            href="/company/users"
                            className="rr-page-secondary-btn"
                        >
                            <i className="bi bi-arrow-left"></i>

                            Back
                        </Link>
                    </>
                }
            >

                {/* =====================================================
                    USER CONTEXT
                   ===================================================== */}

                <section className="rr-users-create-context">

                    <div className="rr-users-create-context-main">

                        <div className="rr-users-create-context-avatar">

                            {
                                getInitials(
                                    managedUser?.name
                                )
                            }

                        </div>


                        <div>

                            <span>
                                User Account
                            </span>

                            <strong>
                                {
                                    managedUser?.name
                                    ?? 'User'
                                }
                            </strong>

                            <small>
                                {
                                    managedUser?.email
                                    ?? '—'
                                }
                            </small>

                        </div>

                    </div>


                    <div className="rr-users-create-context-security">

                        <i className="bi bi-shield-check"></i>

                        <div>

                            <strong>
                                Company Access
                            </strong>

                            <span>
                                {
                                    company?.company_name
                                    ?? 'Company'
                                }
                            </span>

                        </div>

                    </div>

                </section>


                {/* =====================================================
                    FORM
                   ===================================================== */}

                <form
                    onSubmit={
                        submit
                    }
                    className="rr-user-create-form"
                >

                    <div className="rr-user-create-grid">

                        {/* =============================================
                            LEFT SIDE
                           ============================================= */}

                        <div className="rr-user-create-main">

                            {/* USER INFORMATION */}

                            <section className="rr-page-panel rr-user-create-panel">

                                <div className="rr-page-panel-header">

                                    <div className="rr-page-panel-title">

                                        <h2>
                                            User Information
                                        </h2>

                                        <p>
                                            Employee contact and login identity
                                        </p>

                                    </div>


                                    <div className="rr-user-create-panel-icon">

                                        <i className="bi bi-person"></i>

                                    </div>

                                </div>


                                <div className="rr-user-create-panel-body">

                                    <div className="rr-user-form-grid">

                                        <FormField
                                            label="Full Name"
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
                                            />

                                        </FormField>


                                        <FormField
                                            label="Mobile"
                                            error={
                                                errors.mobile
                                            }
                                        >

                                            <div className="rr-user-input-icon">

                                                <i className="bi bi-telephone"></i>

                                                <input
                                                    type="text"
                                                    value={
                                                        data.mobile
                                                    }
                                                    onChange={
                                                        event =>
                                                            setData(
                                                                'mobile',
                                                                event.target.value
                                                            )
                                                    }
                                                />

                                            </div>

                                        </FormField>


                                        <FormField
                                            label="Email"
                                            required
                                            error={
                                                errors.email
                                            }
                                            wide
                                        >

                                            <div className="rr-user-input-icon">

                                                <i className="bi bi-envelope"></i>

                                                <input
                                                    type="email"
                                                    value={
                                                        data.email
                                                    }
                                                    onChange={
                                                        event =>
                                                            setData(
                                                                'email',
                                                                event.target.value
                                                            )
                                                    }
                                                />

                                            </div>

                                        </FormField>

                                    </div>

                                </div>

                            </section>


                            {/* PASSWORD */}

                            <section className="rr-page-panel rr-user-create-panel">

                                <div className="rr-page-panel-header">

                                    <div className="rr-page-panel-title">

                                        <h2>
                                            Password
                                        </h2>

                                        <p>
                                            Leave blank to keep the current password
                                        </p>

                                    </div>


                                    <div className="rr-user-create-panel-icon">

                                        <i className="bi bi-key"></i>

                                    </div>

                                </div>


                                <div className="rr-user-create-panel-body">

                                    <div className="rr-user-form-grid">

                                        <FormField
                                            label="New Password"
                                            error={
                                                errors.password
                                            }
                                        >

                                            <input
                                                type="password"
                                                value={
                                                    data.password
                                                }
                                                onChange={
                                                    event =>
                                                        setData(
                                                            'password',
                                                            event.target.value
                                                        )
                                                }
                                                placeholder="Leave blank to keep current password"
                                                autoComplete="new-password"
                                            />

                                        </FormField>


                                        <FormField
                                            label="Confirm Password"
                                        >

                                            <input
                                                type="password"
                                                value={
                                                    data.password_confirmation
                                                }
                                                onChange={
                                                    event =>
                                                        setData(
                                                            'password_confirmation',
                                                            event.target.value
                                                        )
                                                }
                                                placeholder="Confirm new password"
                                                autoComplete="new-password"
                                            />

                                        </FormField>

                                    </div>

                                </div>

                            </section>

                        </div>


                        {/* =============================================
                            RIGHT SIDE
                           ============================================= */}

                        <div className="rr-user-create-access">

                            {/* ROLE */}

                            <section className="rr-page-panel rr-user-access-panel">

                                <div className="rr-user-access-panel-header">

                                    <div>

                                        <span>
                                            Access Configuration
                                        </span>

                                        <strong>
                                            Role
                                        </strong>

                                    </div>


                                    <i className="bi bi-person-badge"></i>

                                </div>


                                <div className="rr-user-access-panel-body">

                                    <label className="rr-user-access-label">
                                        Select Role
                                        <span>*</span>
                                    </label>


                                    <div className="rr-user-role-list">

                                        {
                                            (
                                                roles
                                                ?? []
                                            ).map(
                                                role => (

                                                    <label
                                                        key={
                                                            role.id
                                                        }
                                                        className={
                                                            `rr-user-role-option ${data.role === role.name
                                                                ? 'selected'
                                                                : ''
                                                            }`
                                                        }
                                                    >

                                                        <input
                                                            type="radio"
                                                            name="role"
                                                            value={
                                                                role.name
                                                            }
                                                            checked={
                                                                data.role === role.name
                                                            }
                                                            onChange={
                                                                event =>
                                                                    setData(
                                                                        'role',
                                                                        event.target.value
                                                                    )
                                                            }
                                                        />


                                                        <div className="rr-user-role-option-icon">

                                                            <i className="bi bi-person-badge"></i>

                                                        </div>


                                                        <div>

                                                            <strong>
                                                                {
                                                                    role.name
                                                                }
                                                            </strong>

                                                            <span>
                                                                Business access role
                                                            </span>

                                                        </div>


                                                        <i className="bi bi-check-circle-fill rr-user-role-check"></i>

                                                    </label>

                                                )
                                            )
                                        }

                                    </div>


                                    {
                                        errors.role && (

                                            <div className="rr-user-form-error">

                                                <i className="bi bi-exclamation-circle"></i>

                                                {
                                                    errors.role
                                                }

                                            </div>

                                        )
                                    }

                                </div>

                            </section>


                            {/* BRANCH ACCESS */}

                            <section className="rr-page-panel rr-user-access-panel">

                                <div className="rr-user-access-panel-header">

                                    <div>

                                        <span>
                                            Access Configuration
                                        </span>

                                        <strong>
                                            Branch Access
                                        </strong>

                                    </div>


                                    <i className="bi bi-diagram-3"></i>

                                </div>


                                <div className="rr-user-access-panel-body">

                                    <div className="rr-user-branch-toolbar">

                                        <span>

                                            {
                                                data.store_ids.length
                                            } selected

                                        </span>


                                        <div>

                                            <button
                                                type="button"
                                                onClick={
                                                    selectAllBranches
                                                }
                                            >
                                                Select All
                                            </button>

                                            <button
                                                type="button"
                                                onClick={
                                                    clearBranches
                                                }
                                            >
                                                Clear
                                            </button>

                                        </div>

                                    </div>


                                    <div className="rr-user-branch-list">

                                        {
                                            (
                                                branches
                                                ?? []
                                            ).map(
                                                branch => {

                                                    const selected =
                                                        data.store_ids
                                                            .includes(
                                                                branch.id
                                                            );


                                                    return (

                                                        <label
                                                            key={
                                                                branch.id
                                                            }
                                                            className={
                                                                `rr-user-branch-option ${selected
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
                                                                        toggleBranch(
                                                                            branch.id
                                                                        )
                                                                }
                                                            />


                                                            <div className="rr-user-branch-option-icon">

                                                                <i className="bi bi-shop"></i>

                                                            </div>


                                                            <div>

                                                                <strong>
                                                                    {
                                                                        branch.store_name
                                                                    }
                                                                </strong>

                                                                <span>
                                                                    {
                                                                        branch.store_code
                                                                        ?? ''
                                                                    }
                                                                </span>

                                                            </div>


                                                            <i className="bi bi-check-lg rr-user-branch-check"></i>

                                                        </label>

                                                    );
                                                }
                                            )
                                        }

                                    </div>


                                    {
                                        errors.store_ids && (

                                            <div className="rr-user-form-error">

                                                <i className="bi bi-exclamation-circle"></i>

                                                {
                                                    errors.store_ids
                                                }

                                            </div>

                                        )
                                    }

                                </div>

                            </section>


                            {/* STATUS */}

                            <section className="rr-page-panel rr-user-access-panel">

                                <div className="rr-user-access-panel-header">

                                    <div>

                                        <span>
                                            Account
                                        </span>

                                        <strong>
                                            Account Status
                                        </strong>

                                    </div>


                                    <i className="bi bi-toggle-on"></i>

                                </div>


                                <div className="rr-user-access-panel-body">

                                    <label className="rr-user-status-switch">

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


                                        <span className="rr-user-status-slider"></span>


                                        <div>

                                            <strong>

                                                {
                                                    data.status
                                                        ? 'Active'
                                                        : 'Inactive'
                                                }

                                            </strong>

                                            <span>

                                                {
                                                    data.status
                                                        ? 'User can sign in.'
                                                        : 'User login is disabled.'
                                                }

                                            </span>

                                        </div>

                                    </label>

                                </div>

                            </section>

                        </div>

                    </div>


                    {/* =================================================
                        ACTIONS
                       ================================================= */}

                    <div className="rr-user-create-actions">

                        <div className="rr-user-create-actions-info">

                            <i className="bi bi-shield-lock"></i>

                            <span>
                                Company and Super Admin access cannot be changed here.
                            </span>

                        </div>


                        <div>

                            <Link
                                href="/company/users"
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
                `rr-user-form-field ${wide
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
                    `rr-user-form-control ${error
                        ? 'has-error'
                        : ''
                    }`
                }
            >

                {children}

            </div>


            {
                error && (

                    <div className="rr-user-form-error">

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


/*
|--------------------------------------------------------------------------
| Initials
|--------------------------------------------------------------------------
*/

function getInitials(
    name
) {

    if (
        !name
    ) {

        return 'U';
    }


    const parts =
        String(
            name
        )
            .trim()
            .split(
                /\s+/
            )
            .filter(
                Boolean
            );


    if (
        parts.length === 0
    ) {

        return 'U';
    }


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
        parts[0][0]
        +
        parts[
        parts.length - 1
        ][0]
    ).toUpperCase();
}