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


export default function RolesIndex({
    company,
    roles,
    summary,
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

        status:
            filters?.status
            ?? '',
    });


    /*
    |--------------------------------------------------------------------------
    | Role Collection
    |--------------------------------------------------------------------------
    */

    const roleItems =
        roles?.data
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

            router.get(
                '/company/roles',
                {
                    search:
                        overrides.search
                        ?? data.search,

                    status:
                        overrides.status
                        ?? data.status,
                },
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
    | Clear Filters
    |--------------------------------------------------------------------------
    */

    const clearFilters =
        () => {

            setData({
                search:
                    '',
                status:
                    '',
            });


            router.get(
                '/company/roles',
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
                    'roles',
                    'summary',
                ],

                preserveScroll:
                    true,
            });
        };


    /*
    |--------------------------------------------------------------------------
    | Status Toggle
    |--------------------------------------------------------------------------
    */

    const changeRoleStatus =
        (
            role,
            status
        ) => {

            if (
                !role?.id
            ) {
                return;
            }


            router.patch(
                `/company/roles/${role.id}/status`,
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
    | Delete
    |--------------------------------------------------------------------------
    */

    const deleteRole =
        role => {

            if (
                !role?.id
            ) {
                return;
            }


            const confirmed =
                window.confirm(
                    `Delete role "${role.name}"?`
                );


            if (
                !confirmed
            ) {
                return;
            }


            router.delete(
                `/company/roles/${role.id}`,
                {
                    preserveScroll:
                        true,
                }
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Filter State
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
                filters?.status
                ?? ''
            ).trim()
        );


    return (

        <BusinessLayout>

            <Head title="Roles & Permissions" />


            <PageShell
                eyebrow="Company Management"
                title="Roles & Permissions"
                description="Create company-specific roles and control what each user can access."
                icon="bi-shield-lock"
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
                    },
                ]}
                actions={
                    <Link
                        href="/company/roles/create"
                        className="rr-page-primary-btn"
                    >
                        <i className="bi bi-plus-circle"></i>

                        Add Role
                    </Link>
                }
            >

                {/* =====================================================
                    COMPANY STRIP
                   ===================================================== */}

                <section className="rr-roles-company-strip">

                    <div>

                        <div className="rr-roles-company-avatar">

                            {
                                getInitials(
                                    company?.company_name
                                )
                            }

                        </div>


                        <div className="rr-roles-company-info">

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


                    <div className="rr-roles-company-security">

                        <i className="bi bi-shield-check"></i>

                        <span>
                            Roles are isolated to this company
                        </span>

                    </div>

                </section>


                {/* =====================================================
                    SUMMARY
                   ===================================================== */}

                <section className="rr-roles-summary">

                    <SummaryItem
                        label="Total Roles"
                        value={
                            summary?.total
                            ?? 0
                        }
                        icon="bi-person-badge"
                    />

                    <SummaryItem
                        label="Active"
                        value={
                            summary?.active
                            ?? 0
                        }
                        icon="bi-check-circle"
                        tone="success"
                    />

                    <SummaryItem
                        label="Inactive"
                        value={
                            summary?.inactive
                            ?? 0
                        }
                        icon="bi-slash-circle"
                        tone="danger"
                    />

                    <SummaryItem
                        label="Permissions"
                        value={
                            summary?.permissions
                            ?? 0
                        }
                        icon="bi-key"
                        tone="primary"
                    />

                </section>


                {/* =====================================================
                    MAIN PANEL
                   ===================================================== */}

                <section className="rr-roles-panel">

                    {/* =================================================
                        TOOLBAR
                       ================================================= */}

                    <div className="rr-roles-toolbar">

                        <form
                            className="rr-roles-search-form"
                            onSubmit={
                                submitSearch
                            }
                        >

                            <div className="rr-roles-search-box">

                                <i className="bi bi-search"></i>


                                <input
                                    type="text"
                                    value={
                                        data.search
                                    }
                                    placeholder="Search role name or description..."
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
                                            className="rr-roles-search-clear"
                                            onClick={
                                                clearSearch
                                            }
                                        >
                                            <i className="bi bi-x-lg"></i>
                                        </button>

                                    )
                                }

                            </div>


                            <button
                                type="submit"
                                className="rr-roles-search-btn"
                            >
                                Search
                            </button>

                        </form>


                        <div className="rr-roles-filter-group">

                            <div className="rr-roles-filter">

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
                                        className="rr-roles-clear-btn"
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
                                className="rr-roles-refresh-btn"
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
                        roleItems.length > 0
                            ? (

                                <div className="rr-roles-table-wrap">

                                    <table className="rr-roles-table">

                                        <thead>

                                            <tr>

                                                <th>
                                                    Role
                                                </th>

                                                <th>
                                                    Users
                                                </th>

                                                <th>
                                                    Permissions
                                                </th>

                                                <th>
                                                    Status
                                                </th>

                                                <th className="rr-roles-actions-heading">
                                                    Actions
                                                </th>

                                            </tr>

                                        </thead>


                                        <tbody>

                                            {
                                                roleItems.map(
                                                    role => (

                                                        <RoleRow
                                                            key={
                                                                role.id
                                                            }
                                                            role={
                                                                role
                                                            }
                                                            onStatusChange={
                                                                changeRoleStatus
                                                            }
                                                            onDelete={
                                                                deleteRole
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

                                <EmptyRoles
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
                        roles?.links
                        ?? []
                    ).length > 3 && (

                        <div className="rr-roles-pagination">

                            {
                                roles.links.map(
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
| Summary Item
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
                `rr-roles-summary-item rr-roles-summary-${tone}`
            }
        >

            <div className="rr-roles-summary-icon">

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
| Role Row
|--------------------------------------------------------------------------
*/

function RoleRow({
    role,
    onStatusChange,
    onDelete,
}) {

    const hasUsers =
        Number(
            role?.users_count
            ?? 0
        ) > 0;


    return (

        <tr>

            {/* ROLE */}

            <td>

                <div className="rr-roles-role-cell">

                    <div className="rr-roles-role-icon">

                        <i className="bi bi-person-badge"></i>

                    </div>


                    <div>

                        <strong>
                            {
                                role?.name
                                ?? 'Role'
                            }
                        </strong>

                        <span>
                            {
                                role?.description
                                || 'No description'
                            }
                        </span>

                    </div>

                </div>

            </td>


            {/* USERS */}

            <td>

                <div className="rr-roles-count-cell">

                    <i className="bi bi-people"></i>

                    <strong>
                        {
                            role?.users_count
                            ?? 0
                        }
                    </strong>

                    <span>
                        user{
                            Number(
                                role?.users_count
                                ?? 0
                            ) === 1
                                ? ''
                                : 's'
                        }
                    </span>

                </div>

            </td>


            {/* PERMISSIONS */}

            <td>

                <div className="rr-roles-count-cell">

                    <i className="bi bi-key"></i>

                    <strong>
                        {
                            role?.permissions_count
                            ?? 0
                        }
                    </strong>

                    <span>
                        permission{
                            Number(
                                role?.permissions_count
                                ?? 0
                            ) === 1
                                ? ''
                                : 's'
                        }
                    </span>

                </div>

            </td>


            {/* STATUS */}

            <td>

                <span
                    className={
                        role?.status
                            ? 'rr-roles-status active'
                            : 'rr-roles-status inactive'
                    }
                >

                    <span></span>

                    {
                        role?.status
                            ? 'Active'
                            : 'Inactive'
                    }

                </span>

            </td>


            {/* ACTIONS */}

            <td>

                <div className="rr-roles-row-actions">

                    <Link
                        href={
                            `/company/roles/${role.id}`
                        }
                        className="rr-roles-action-btn"
                    >

                        <i className="bi bi-eye"></i>

                        View

                    </Link>


                    <Link
                        href={
                            `/company/roles/${role.id}/edit`
                        }
                        className="rr-roles-action-btn"
                    >

                        <i className="bi bi-pencil-square"></i>

                        Edit

                    </Link>


                    <button
                        type="button"
                        className={
                            role?.status
                                ? 'rr-roles-status-action deactivate'
                                : 'rr-roles-status-action activate'
                        }
                        onClick={
                            () =>
                                onStatusChange(
                                    role,
                                    !role.status
                                )
                        }
                    >

                        <i
                            className={
                                role?.status
                                    ? 'bi bi-slash-circle'
                                    : 'bi bi-check-circle'
                            }
                        ></i>

                        {
                            role?.status
                                ? 'Disable'
                                : 'Enable'
                        }

                    </button>


                    <button
                        type="button"
                        className="rr-roles-delete-btn"
                        disabled={
                            hasUsers
                        }
                        title={
                            hasUsers
                                ? 'This role is assigned to users'
                                : 'Delete role'
                        }
                        onClick={
                            () =>
                                onDelete(
                                    role
                                )
                        }
                    >

                        <i className="bi bi-trash"></i>

                    </button>

                </div>

            </td>

        </tr>

    );
}


/*
|--------------------------------------------------------------------------
| Empty State
|--------------------------------------------------------------------------
*/

function EmptyRoles({
    hasFilters,
}) {

    return (

        <div className="rr-roles-empty">

            <div>

                <i
                    className={
                        hasFilters
                            ? 'bi bi-search'
                            : 'bi bi-shield-plus'
                    }
                ></i>

            </div>


            <strong>

                {
                    hasFilters
                        ? 'No matching roles'
                        : 'No company roles created'
                }

            </strong>


            <p>

                {
                    hasFilters
                        ? 'Change your search or status filter and try again.'
                        : 'Create a role and choose the permissions employees with that role can use.'
                }

            </p>


            {
                !hasFilters && (

                    <Link
                        href="/company/roles/create"
                        className="rr-page-primary-btn"
                    >

                        <i className="bi bi-plus-circle"></i>

                        Add Role

                    </Link>

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