import {
    Head,
    Link,
    useForm,
} from '@inertiajs/react';

import BusinessLayout
    from '../../../Layouts/BusinessLayout';

import PageShell
    from '../../../Components/Business/PageShell';


export default function UserCreate({
    company,
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
        post,
        processing,
        errors,
        reset,
    } = useForm({

        name:
            '',

        email:
            '',

        mobile:
            '',

        password:
            '',

        password_confirmation:
            '',

        role:
            '',

        store_ids:
            [],

        status:
            true,
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
        (
            event
        ) => {

            event.preventDefault();


            post(
                '/company/users',
                {
                    preserveScroll:
                        true,
                }
            );
        };


    return (

        <BusinessLayout>

            <Head title="Add User" />


            <PageShell
                eyebrow="Company Management"
                title="Add User"
                description="Create an employee account and configure role and branch access."
                icon="bi-person-plus"
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
                            'Add User',
                    },
                ]}
                actions={
                    <Link
                        href="/company/users"
                        className="rr-page-secondary-btn"
                    >
                        <i className="bi bi-arrow-left"></i>

                        Back to Users
                    </Link>
                }
            >

                {/* =====================================================
                    COMPANY CONTEXT
                   ===================================================== */}

                <section className="rr-users-create-context">

                    <div className="rr-users-create-context-main">

                        <div className="rr-users-create-context-avatar">

                            {
                                getInitials(
                                    company?.company_name
                                )
                            }

                        </div>


                        <div>

                            <span>
                                Company
                            </span>

                            <strong>
                                {
                                    company?.company_name
                                    ?? 'Company'
                                }
                            </strong>

                            {
                                company?.company_code && (

                                    <small>
                                        {
                                            company.company_code
                                        }
                                    </small>

                                )
                            }

                        </div>

                    </div>


                    <div className="rr-users-create-context-security">

                        <i className="bi bi-shield-lock"></i>

                        <div>

                            <strong>
                                Company isolated
                            </strong>

                            <span>
                                This user can only access branches within this company.
                            </span>

                        </div>

                    </div>

                </section>


                {/* =====================================================
                    MAIN GRID
                   ===================================================== */}

                <form
                    onSubmit={
                        submit
                    }
                    className="rr-user-create-form"
                >

                    <div className="rr-user-create-grid">

                        {/* =============================================
                            LEFT
                           ============================================= */}

                        <div className="rr-user-create-main">

                            {/* =========================================
                                USER INFORMATION
                               ========================================= */}

                            <section className="rr-page-panel rr-user-create-panel">

                                <div className="rr-page-panel-header">

                                    <div className="rr-page-panel-title">

                                        <h2>
                                            User Information
                                        </h2>

                                        <p>
                                            Employee identity and login details
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
                                                placeholder="Enter employee name"
                                                autoFocus
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
                                                    placeholder="Enter mobile number"
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
                                                    placeholder="Enter login email"
                                                />

                                            </div>

                                        </FormField>

                                    </div>

                                </div>

                            </section>


                            {/* =========================================
                                PASSWORD
                               ========================================= */}

                            <section className="rr-page-panel rr-user-create-panel">

                                <div className="rr-page-panel-header">

                                    <div className="rr-page-panel-title">

                                        <h2>
                                            Login Security
                                        </h2>

                                        <p>
                                            Set the initial account password
                                        </p>

                                    </div>


                                    <div className="rr-user-create-panel-icon">

                                        <i className="bi bi-key"></i>

                                    </div>

                                </div>


                                <div className="rr-user-create-panel-body">

                                    <div className="rr-user-form-grid">

                                        <FormField
                                            label="Password"
                                            required
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
                                                placeholder="Minimum 8 characters"
                                                autoComplete="new-password"
                                            />

                                        </FormField>


                                        <FormField
                                            label="Confirm Password"
                                            required
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
                                                placeholder="Re-enter password"
                                                autoComplete="new-password"
                                            />

                                        </FormField>

                                    </div>

                                </div>

                            </section>

                        </div>


                        {/* =============================================
                            RIGHT - ACCESS
                           ============================================= */}

                        <div className="rr-user-create-access">

                            {/* =========================================
                                ROLE
                               ========================================= */}

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

                                        <span>
                                            *
                                        </span>

                                    </label>


                                    <div className="rr-user-role-list">

                                        {
                                            (
                                                roles
                                                ?? []
                                            ).length > 0
                                                ? (

                                                    roles.map(
                                                        role => (

                                                            <label
                                                                key={
                                                                    role.id
                                                                }
                                                                className={
                                                                    `rr-user-role-option ${data.role ===
                                                                        role.name
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
                                                                        data.role ===
                                                                        role.name
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

                                                )
                                                : (

                                                    <div className="rr-user-access-empty">

                                                        <i className="bi bi-exclamation-circle"></i>

                                                        No assignable roles available.

                                                    </div>

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


                            {/* =========================================
                                BRANCH ACCESS
                               ========================================= */}

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
                                            ).length > 0
                                                ? (

                                                    branches.map(
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

                                                )
                                                : (

                                                    <div className="rr-user-access-empty">

                                                        <i className="bi bi-shop"></i>

                                                        No active branches are available.

                                                    </div>

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


                            {/* =========================================
                                STATUS
                               ========================================= */}

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
                                                        ? 'User can sign in after creation.'
                                                        : 'User account will be disabled.'
                                                }

                                            </span>

                                        </div>

                                    </label>


                                    {
                                        errors.status && (

                                            <div className="rr-user-form-error">

                                                <i className="bi bi-exclamation-circle"></i>

                                                {
                                                    errors.status
                                                }

                                            </div>

                                        )
                                    }

                                </div>

                            </section>

                        </div>

                    </div>


                    {/* =================================================
                        FOOTER ACTION BAR
                       ================================================= */}

                    <div className="rr-user-create-actions">

                        <div className="rr-user-create-actions-info">

                            <i className="bi bi-info-circle"></i>

                            <span>
                                Company and Super Admin access are controlled by the system.
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
                                type="button"
                                className="rr-page-secondary-btn"
                                disabled={
                                    processing
                                }
                                onClick={
                                    () =>
                                        reset()
                                }
                            >
                                Reset
                            </button>


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

                                                Creating...
                                            </>
                                        )
                                        : (
                                            <>
                                                <i className="bi bi-person-check"></i>

                                                Create User
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

        return 'CO';
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

        return 'CO';
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