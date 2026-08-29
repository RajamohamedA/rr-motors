import {
    Head,
    Link,
    router,
    useForm,
} from '@inertiajs/react';

import BusinessLayout
    from '../../../Layouts/BusinessLayout';

import PageShell
    from '../../../Components/Business/PageShell';


export default function UsersIndex({
    company,
    users,
    summary,
    roles,
    filters,
}) {

    /*
    |--------------------------------------------------------------------------
    | Filter Form
    |--------------------------------------------------------------------------
    */

    const {
        data,
        setData,
    } = useForm({

        search:
            filters?.search
            ?? '',

        role:
            filters?.role
            ?? '',

        status:
            filters?.status
            ?? '',
    });


    /*
    |--------------------------------------------------------------------------
    | Users
    |--------------------------------------------------------------------------
    */

    const userItems =
        users?.data
        ?? [];


    /*
    |--------------------------------------------------------------------------
    | Apply Filters
    |--------------------------------------------------------------------------
    */

    const applyFilters =
        (
            overrides = {}
        ) => {

            const payload = {

                search:
                    overrides.search
                    ?? data.search,

                role:
                    overrides.role
                    ?? data.role,

                status:
                    overrides.status
                    ?? data.status,
            };


            router.get(
                '/company/users',
                payload,
                {
                    preserveState:
                        true,

                    preserveScroll:
                        true,

                    replace:
                        true,
                }
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Search
    |--------------------------------------------------------------------------
    */

    const submitSearch =
        event => {

            event.preventDefault();

            applyFilters();
        };


    /*
    |--------------------------------------------------------------------------
    | Role Filter
    |--------------------------------------------------------------------------
    */

    const changeRole =
        event => {

            const value =
                event.target.value;


            setData(
                'role',
                value
            );


            applyFilters({
                role:
                    value,
            });
        };


    /*
    |--------------------------------------------------------------------------
    | Status Filter
    |--------------------------------------------------------------------------
    */

    const changeStatus =
        event => {

            const value =
                event.target.value;


            setData(
                'status',
                value
            );


            applyFilters({
                status:
                    value,
            });
        };


    /*
    |--------------------------------------------------------------------------
    | Clear Search
    |--------------------------------------------------------------------------
    */

    const clearSearch =
        () => {

            setData(
                'search',
                ''
            );


            applyFilters({
                search:
                    '',
            });
        };


    /*
    |--------------------------------------------------------------------------
    | Clear All Filters
    |--------------------------------------------------------------------------
    */

    const clearFilters =
        () => {

            setData({
                search:
                    '',
                role:
                    '',
                status:
                    '',
            });


            router.get(
                '/company/users',
                {},
                {
                    preserveState:
                        true,

                    preserveScroll:
                        true,

                    replace:
                        true,
                }
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Refresh
    |--------------------------------------------------------------------------
    */

    const refresh =
        () => {

            router.reload({
                only: [
                    'users',
                    'summary',
                ],

                preserveScroll:
                    true,
            });
        };


    /*
    |--------------------------------------------------------------------------
    | Change Status
    |--------------------------------------------------------------------------
    */

    const changeUserStatus =
        (
            managedUser,
            status
        ) => {

            if (
                !managedUser?.id ||
                managedUser?.is_current_user ||
                managedUser?.role ===
                'Company Admin'
            ) {
                return;
            }


            router.patch(
                `/company/users/${managedUser.id}/status`,
                {
                    status,
                },
                {
                    preserveScroll:
                        true,
                }
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Active Filters
    |--------------------------------------------------------------------------
    */

    const hasFilters =
        Boolean(
            String(
                filters?.search
                ?? ''
            ).trim()
            ||
            String(
                filters?.role
                ?? ''
            ).trim()
            ||
            String(
                filters?.status
                ?? ''
            ).trim()
        );


    return (

        <BusinessLayout>

            <Head title="Users & Access" />


            <PageShell
                eyebrow="Company Management"
                title="Users & Access"
                description="Manage employees, roles and branch access."
                icon="bi-people"
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
                    },
                ]}
                actions={
                    <Link
                        href="/company/users/create"
                        className="rr-page-primary-btn"
                    >
                        <i className="bi bi-person-plus"></i>

                        Add User
                    </Link>
                }
            >

                {/* =====================================================
                    COMPANY STRIP
                   ===================================================== */}

                <section className="rr-users-company-strip">

                    <div>

                        <div className="rr-users-company-avatar">

                            {
                                getInitials(
                                    company?.company_name
                                )
                            }

                        </div>


                        <div className="rr-users-company-info">

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


                    <div className="rr-users-company-security">

                        <i className="bi bi-shield-check"></i>

                        <span>
                            Company-isolated user management
                        </span>

                    </div>

                </section>


                {/* =====================================================
                    SUMMARY
                   ===================================================== */}

                <section className="rr-users-summary">

                    <SummaryItem
                        label="Total Users"
                        value={
                            summary?.total
                            ?? 0
                        }
                        icon="bi-people"
                    />

                    <SummaryItem
                        label="Active Users"
                        value={
                            summary?.active
                            ?? 0
                        }
                        icon="bi-person-check"
                        tone="success"
                    />

                    <SummaryItem
                        label="Inactive"
                        value={
                            summary?.inactive
                            ?? 0
                        }
                        icon="bi-person-dash"
                        tone="danger"
                    />

                    <SummaryItem
                        label="Branch Access"
                        value={
                            summary?.branches
                            ?? 0
                        }
                        icon="bi-diagram-3"
                        tone="primary"
                    />

                </section>


                {/* =====================================================
                    MAIN PANEL
                   ===================================================== */}

                <section className="rr-users-panel">

                    {/* =================================================
                        TOOLBAR
                       ================================================= */}

                    <div className="rr-users-toolbar">

                        <form
                            className="rr-users-search-form"
                            onSubmit={
                                submitSearch
                            }
                        >

                            <div className="rr-users-search-box">

                                <i className="bi bi-search"></i>


                                <input
                                    type="text"
                                    value={
                                        data.search
                                    }
                                    placeholder="Search name, email or mobile..."
                                    onChange={
                                        event =>
                                            setData(
                                                'search',
                                                event.target.value
                                            )
                                    }
                                />


                                {
                                    data.search && (

                                        <button
                                            type="button"
                                            onClick={
                                                clearSearch
                                            }
                                            className="rr-users-search-clear"
                                            title="Clear search"
                                        >

                                            <i className="bi bi-x-lg"></i>

                                        </button>

                                    )
                                }

                            </div>


                            <button
                                type="submit"
                                className="rr-users-search-btn"
                            >
                                Search
                            </button>

                        </form>


                        <div className="rr-users-filter-group">

                            <div className="rr-users-filter">

                                <label>
                                    Role
                                </label>

                                <select
                                    value={
                                        data.role
                                    }
                                    onChange={
                                        changeRole
                                    }
                                >
                                    <option value="">
                                        All Roles
                                    </option>

                                    {
                                        (
                                            roles
                                            ?? []
                                        ).map(
                                            role => (

                                                <option
                                                    key={
                                                        role.id
                                                    }
                                                    value={
                                                        role.name
                                                    }
                                                >
                                                    {
                                                        role.name
                                                    }
                                                </option>

                                            )
                                        )
                                    }

                                </select>

                            </div>


                            <div className="rr-users-filter">

                                <label>
                                    Status
                                </label>

                                <select
                                    value={
                                        data.status
                                    }
                                    onChange={
                                        changeStatus
                                    }
                                >
                                    <option value="">
                                        All
                                    </option>

                                    <option value="1">
                                        Active
                                    </option>

                                    <option value="0">
                                        Inactive
                                    </option>

                                </select>

                            </div>


                            {
                                hasFilters && (

                                    <button
                                        type="button"
                                        className="rr-users-clear-filters"
                                        onClick={
                                            clearFilters
                                        }
                                    >

                                        <i className="bi bi-funnel"></i>

                                        Clear

                                    </button>

                                )
                            }


                            <button
                                type="button"
                                className="rr-users-refresh-btn"
                                onClick={
                                    refresh
                                }
                            >

                                <i className="bi bi-arrow-clockwise"></i>

                                Refresh

                            </button>

                        </div>

                    </div>


                    {/* =================================================
                        TABLE
                       ================================================= */}

                    {
                        userItems.length > 0
                            ? (

                                <div className="rr-users-table-wrap">

                                    <table className="rr-users-table">

                                        <thead>

                                            <tr>

                                                <th>
                                                    User
                                                </th>

                                                <th>
                                                    Role
                                                </th>

                                                <th>
                                                    Branch Access
                                                </th>

                                                <th>
                                                    Status
                                                </th>

                                                <th className="rr-users-actions-heading">
                                                    Actions
                                                </th>

                                            </tr>

                                        </thead>


                                        <tbody>

                                            {
                                                userItems.map(
                                                    managedUser => (

                                                        <UserRow
                                                            key={
                                                                managedUser.id
                                                            }

                                                            user={
                                                                managedUser
                                                            }

                                                            onStatusChange={
                                                                changeUserStatus
                                                            }
                                                        />

                                                    )
                                                )
                                            }

                                        </tbody>

                                    </table>

                                </div>

                            )
                            : (

                                <EmptyUsers
                                    hasFilters={
                                        hasFilters
                                    }
                                />

                            )
                    }

                </section>


                {/* =====================================================
                    PAGINATION
                   ===================================================== */}

                {
                    (
                        users?.links
                        ?? []
                    ).length > 3 && (

                        <div className="rr-users-pagination">

                            {
                                users.links.map(
                                    (
                                        link,
                                        index
                                    ) => (

                                        link.url
                                            ? (

                                                <Link
                                                    key={
                                                        index
                                                    }
                                                    href={
                                                        link.url
                                                    }
                                                    preserveScroll
                                                    className={
                                                        link.active
                                                            ? 'active'
                                                            : ''
                                                    }
                                                >

                                                    <span
                                                        dangerouslySetInnerHTML={{
                                                            __html:
                                                                link.label,
                                                        }}
                                                    />

                                                </Link>

                                            )
                                            : (

                                                <span
                                                    key={
                                                        index
                                                    }
                                                    className="disabled"
                                                    dangerouslySetInnerHTML={{
                                                        __html:
                                                            link.label,
                                                    }}
                                                />

                                            )

                                    )
                                )
                            }

                        </div>

                    )
                }

            </PageShell>

        </BusinessLayout>
    );
}


/*
|--------------------------------------------------------------------------
| Summary
|--------------------------------------------------------------------------
*/

function SummaryItem({
    label,
    value,
    icon,
    tone = 'default',
}) {

    return (

        <div
            className={
                `rr-users-summary-item rr-users-summary-${tone}`
            }
        >

            <div className="rr-users-summary-icon">

                <i
                    className={
                        `bi ${icon}`
                    }
                ></i>

            </div>


            <div>

                <span>
                    {label}
                </span>

                <strong>
                    {
                        Number(
                            value
                            ?? 0
                        ).toLocaleString(
                            'en-IN'
                        )
                    }
                </strong>

            </div>

        </div>

    );
}


/*
|--------------------------------------------------------------------------
| User Row
|--------------------------------------------------------------------------
*/

function UserRow({
    user,
    onStatusChange,
}) {

    const isCompanyAdmin =
        user?.role ===
        'Company Admin';


    const branches =
        user?.branches
        ?? [];


    const branchText =
        user?.all_branches
            ? 'All Branches'
            : getBranchSummary(
                branches
            );


    return (

        <tr
            className={
                user?.is_current_user
                    ? 'current-user'
                    : ''
            }
        >

            {/* USER */}

            <td>

                <div className="rr-users-user-cell">

                    <div className="rr-users-avatar">

                        {
                            getInitials(
                                user?.name
                            )
                        }

                    </div>


                    <div className="rr-users-user-info">

                        <div>

                            <strong>
                                {
                                    user?.name
                                    ?? 'User'
                                }
                            </strong>


                            {
                                user?.is_current_user && (

                                    <span className="rr-users-you-badge">
                                        You
                                    </span>

                                )
                            }

                        </div>


                        <span>

                            <i className="bi bi-envelope"></i>

                            {
                                user?.email
                                ?? '—'
                            }

                        </span>


                        {
                            user?.mobile && (

                                <span>

                                    <i className="bi bi-telephone"></i>

                                    {
                                        user.mobile
                                    }

                                </span>

                            )
                        }

                    </div>

                </div>

            </td>


            {/* ROLE */}

            <td>

                <span
                    className={
                        `rr-users-role-badge ${isCompanyAdmin
                            ? 'company-admin'
                            : ''
                        }`
                    }
                >

                    <i
                        className={
                            isCompanyAdmin
                                ? 'bi bi-shield-lock'
                                : 'bi bi-person-badge'
                        }
                    ></i>

                    {
                        user?.role
                        ?? 'No Role'
                    }

                </span>

            </td>


            {/* BRANCH ACCESS */}

            <td>

                <div className="rr-users-branch-access">

                    <i className="bi bi-diagram-3"></i>

                    <span>
                        {branchText}
                    </span>

                </div>

            </td>


            {/* STATUS */}

            <td>

                <span
                    className={
                        user?.status
                            ? 'rr-users-status active'
                            : 'rr-users-status inactive'
                    }
                >

                    <span></span>

                    {
                        user?.status
                            ? 'Active'
                            : 'Inactive'
                    }

                </span>

            </td>


            {/* ACTIONS */}

            <td>

                <div className="rr-users-row-actions">

                    <Link
                        href={
                            `/company/users/${user.id}`
                        }
                        className="rr-users-action-btn"
                        title="View user"
                    >

                        <i className="bi bi-eye"></i>

                        View

                    </Link>


                    {
                        !isCompanyAdmin && (

                            <Link
                                href={
                                    `/company/users/${user.id}/edit`
                                }
                                className="rr-users-action-btn"
                                title="Edit user"
                            >

                                <i className="bi bi-pencil-square"></i>

                                Edit

                            </Link>

                        )
                    }


                    {
                        !user?.is_current_user &&
                        !isCompanyAdmin && (

                            <button
                                type="button"
                                className={
                                    user?.status
                                        ? 'rr-users-status-action deactivate'
                                        : 'rr-users-status-action activate'
                                }
                                onClick={
                                    () =>
                                        onStatusChange(
                                            user,
                                            !user.status
                                        )
                                }
                            >

                                <i
                                    className={
                                        user?.status
                                            ? 'bi bi-person-x'
                                            : 'bi bi-person-check'
                                    }
                                ></i>

                                {
                                    user?.status
                                        ? 'Disable'
                                        : 'Enable'
                                }

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
| Empty
|--------------------------------------------------------------------------
*/

function EmptyUsers({
    hasFilters,
}) {

    return (

        <div className="rr-users-empty">

            <div>

                <i
                    className={
                        hasFilters
                            ? 'bi bi-search'
                            : 'bi bi-person-plus'
                    }
                ></i>

            </div>


            <strong>

                {
                    hasFilters
                        ? 'No matching users'
                        : 'No users created yet'
                }

            </strong>


            <p>

                {
                    hasFilters
                        ? 'Change the search or filter options and try again.'
                        : 'Create a business user and assign their role and branch access.'
                }

            </p>


            {
                !hasFilters && (

                    <Link
                        href="/company/users/create"
                        className="rr-page-primary-btn"
                    >

                        <i className="bi bi-person-plus"></i>

                        Add User

                    </Link>

                )
            }

        </div>

    );
}


/*
|--------------------------------------------------------------------------
| Branch Summary
|--------------------------------------------------------------------------
*/

function getBranchSummary(
    branches
) {

    if (
        !Array.isArray(
            branches
        ) ||
        branches.length === 0
    ) {

        return 'No Branch Access';
    }


    if (
        branches.length === 1
    ) {

        return branches[0]?.store_name
            ?? '1 Branch';
    }


    return `${branches[0]?.store_name
        ?? 'Branch'
        } +${branches.length - 1}`;
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