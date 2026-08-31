import {
    Head,
    Link,
    router,
    usePage,
} from '@inertiajs/react';

import {
    useEffect,
    useMemo,
    useState,
} from 'react';

import BusinessLayout
    from '../../Layouts/BusinessLayout';

import MasterNavigation
    from '../../Components/Business/MasterNavigation';

import '../../../css/business/customers.css';


export default function Index({
    customers,
    summary,
    filters,
}) {

    /*
    |--------------------------------------------------------------------------
    | Shared Props
    |--------------------------------------------------------------------------
    */

    const {
        auth,
        business,
    } = usePage().props;


    const user =
        auth?.user;


    const permissions =
        user?.permissions
        ?? [];


    const roles =
        user?.roles
        ?? [];


    const isSuperAdmin =
        Boolean(
            user?.is_super_user
        );


    const isCompanyAdmin =
        roles.includes(
            'Company Admin'
        );


    const can =
        permission => {

            if (
                isSuperAdmin ||
                isCompanyAdmin
            ) {

                return true;
            }


            return permissions.includes(
                permission
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Local Filters
    |--------------------------------------------------------------------------
    */

    const [
        search,
        setSearch,
    ] =
        useState(
            filters?.search
            ?? ''
        );


    const [
        status,
        setStatus,
    ] =
        useState(
            filters?.status
            ?? ''
        );


    const [
        type,
        setType,
    ] =
        useState(
            filters?.type
            ?? ''
        );


    /*
    |--------------------------------------------------------------------------
    | Search
    |--------------------------------------------------------------------------
    */

    const applyFilters =
        (
            overrides = {}
        ) => {

            router.get(
                '/customers',
                {
                    search:
                        overrides.search
                        ?? search,

                    status:
                        overrides.status
                        ?? status,

                    type:
                        overrides.type
                        ?? type,
                },
                {
                    preserveScroll:
                        true,

                    preserveState:
                        true,

                    replace:
                        true,
                }
            );
        };


    const submitSearch =
        event => {

            event.preventDefault();

            applyFilters();
        };


    const clearFilters =
        () => {

            setSearch(
                ''
            );

            setStatus(
                ''
            );

            setType(
                ''
            );


            router.get(
                '/customers',
                {},
                {
                    preserveScroll:
                        true,

                    preserveState:
                        true,

                    replace:
                        true,
                }
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Auto Filter Status / Type
    |--------------------------------------------------------------------------
    */

    useEffect(
        () => {

            if (
                status ===
                (
                    filters?.status
                    ?? ''
                )
                &&
                type ===
                (
                    filters?.type
                    ?? ''
                )
            ) {

                return;
            }


            const timer =
                setTimeout(
                    () => {

                        applyFilters({
                            status,
                            type,
                        });
                    },
                    100
                );


            return () =>
                clearTimeout(
                    timer
                );
        },
        [
            status,
            type,
        ]
    );


    /*
    |--------------------------------------------------------------------------
    | Data
    |--------------------------------------------------------------------------
    */

    const rows =
        customers?.data
        ?? [];


    const hasFilters =
        Boolean(
            search ||
            status !== '' ||
            type
        );


    const paginationLinks =
        customers?.links
        ?? [];


    const pageInfo =
        useMemo(
            () => {

                const from =
                    customers?.from
                    ?? 0;


                const to =
                    customers?.to
                    ?? 0;


                const total =
                    customers?.total
                    ?? 0;


                return {
                    from,
                    to,
                    total,
                };
            },
            [
                customers?.from,
                customers?.to,
                customers?.total,
            ]
        );


    /*
    |--------------------------------------------------------------------------
    | Row Click
    |--------------------------------------------------------------------------
    */

    const openCustomer =
        customer => {

            if (
                !can(
                    'customer.show'
                )
            ) {

                return;
            }


            router.visit(
                `/customers/${customer.id}`
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Remove From Branch
    |--------------------------------------------------------------------------
    */

    const removeFromBranch =
        customer => {

            if (
                customer.is_system_customer
            ) {

                return;
            }


            if (
                !window.confirm(
                    `Remove ${customer.customer_name} from this branch?\n\nThe customer master will not be deleted.`
                )
            ) {

                return;
            }


            router.delete(
                `/customers/${customer.id}/remove-from-branch`,
                {
                    preserveScroll:
                        true,
                }
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */

    return (
        <BusinessLayout>

            <Head
                title="Customers"
            />


            <div className="rr-customers-page">

                <MasterNavigation
                    active="customers"
                    title="Customers"
                    subtitle="Customer accounts, GST, contact and credit management"
                />


                {/* =====================================================
                    PAGE HEADER
                   ===================================================== */}

                <section className="rr-customers-header">

                    <div className="rr-customers-header-main">

                        <div className="rr-customers-header-icon">

                            <i className="bi bi-person-vcard"></i>

                        </div>


                        <div>

                            <span className="rr-customers-eyebrow">
                                Customer Management
                            </span>

                            <h1>
                                Customers
                            </h1>

                            <p>
                                Manage customers available to {
                                    business?.store_name
                                    ?? 'the active branch'
                                }.
                            </p>

                        </div>

                    </div>


                    <div className="rr-customers-header-actions">

                        <button
                            type="button"
                            className="rr-customer-toolbar-btn"
                            onClick={
                                () =>
                                    router.reload({
                                        preserveScroll:
                                            true,
                                    })
                            }
                        >

                            <i className="bi bi-arrow-clockwise"></i>

                            Refresh

                        </button>


                        {
                            can(
                                'customer.create'
                            ) && (

                                <Link
                                    href="/customers/create"
                                    className="rr-customer-primary-btn"
                                >

                                    <i className="bi bi-plus-lg"></i>

                                    Add Customer

                                </Link>

                            )
                        }

                    </div>

                </section>


                {/* =====================================================
                    SUMMARY
                   ===================================================== */}

                <section className="rr-customers-summary">

                    <SummaryCard
                        label="Total Customers"
                        value={
                            summary?.total
                            ?? 0
                        }
                        icon="bi-people"
                    />

                    <SummaryCard
                        label="Active"
                        value={
                            summary?.active
                            ?? 0
                        }
                        icon="bi-person-check"
                        tone="success"
                    />

                    <SummaryCard
                        label="Inactive"
                        value={
                            summary?.inactive
                            ?? 0
                        }
                        icon="bi-person-dash"
                        tone="muted"
                    />

                    <SummaryCard
                        label="Credit Customers"
                        value={
                            summary?.credit_customers
                            ?? 0
                        }
                        icon="bi-credit-card"
                        tone="warning"
                    />

                </section>


                {/* =====================================================
                    MAIN GRID PANEL
                   ===================================================== */}

                <section className="rr-customers-panel">

                    {/* =================================================
                        TOOLBAR
                       ================================================= */}

                    <div className="rr-customers-toolbar">

                        <form
                            className="rr-customers-search"
                            onSubmit={
                                submitSearch
                            }
                        >

                            <div className="rr-customers-search-box">

                                <i className="bi bi-search"></i>

                                <input
                                    type="text"
                                    value={
                                        search
                                    }
                                    placeholder="Search code, customer, mobile, email or GST..."
                                    onChange={
                                        event =>
                                            setSearch(
                                                event.target.value
                                            )
                                    }
                                />


                                {
                                    search && (

                                        <button
                                            type="button"
                                            title="Clear search"
                                            onClick={
                                                () => {

                                                    setSearch(
                                                        ''
                                                    );

                                                    applyFilters({
                                                        search:
                                                            '',
                                                    });
                                                }
                                            }
                                        >

                                            <i className="bi bi-x-lg"></i>

                                        </button>

                                    )
                                }

                            </div>


                            <button
                                type="submit"
                                className="rr-customer-search-btn"
                            >
                                Search
                            </button>

                        </form>


                        <div className="rr-customers-filter-group">

                            <div className="rr-customers-filter">

                                <label>
                                    Status
                                </label>

                                <select
                                    value={
                                        status
                                    }
                                    onChange={
                                        event =>
                                            setStatus(
                                                event.target.value
                                            )
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


                            <div className="rr-customers-filter">

                                <label>
                                    Type
                                </label>

                                <select
                                    value={
                                        type
                                    }
                                    onChange={
                                        event =>
                                            setType(
                                                event.target.value
                                            )
                                    }
                                >

                                    <option value="">
                                        All Types
                                    </option>

                                    <option value="regular">
                                        Regular
                                    </option>

                                    <option value="walk_in">
                                        Walk-in
                                    </option>

                                </select>

                            </div>


                            {
                                hasFilters && (

                                    <button
                                        type="button"
                                        className="rr-customers-clear-btn"
                                        onClick={
                                            clearFilters
                                        }
                                    >

                                        <i className="bi bi-funnel"></i>

                                        Clear

                                    </button>

                                )
                            }

                        </div>

                    </div>


                    {/* =================================================
                        GRID STATUS
                       ================================================= */}

                    <div className="rr-customers-grid-status">

                        <div>

                            <i className="bi bi-table"></i>

                            <span>
                                Showing
                            </span>

                            <strong>
                                {
                                    pageInfo.from
                                }–{
                                    pageInfo.to
                                }
                            </strong>

                            <span>
                                of
                            </span>

                            <strong>
                                {
                                    pageInfo.total
                                }
                            </strong>

                            <span>
                                customers
                            </span>

                        </div>


                        <span className="rr-customers-row-limit">

                            <i className="bi bi-layout-three-columns"></i>

                            15 rows per page

                        </span>

                    </div>


                    {/* =================================================
                        TABLE
                       ================================================= */}

                    <div className="rr-customers-table-wrap">

                        <table className="rr-customers-table">

                            <thead>

                                <tr>

                                    <th className="rr-col-code">
                                        Code
                                    </th>

                                    <th>
                                        Customer
                                    </th>

                                    <th>
                                        Contact
                                    </th>

                                    <th>
                                        GST / Location
                                    </th>

                                    <th className="rr-col-credit">
                                        Credit
                                    </th>

                                    <th className="rr-col-status">
                                        Status
                                    </th>

                                    <th className="rr-col-actions">
                                        Actions
                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                                {
                                    rows.length > 0
                                        ? (

                                            rows.map(
                                                customer => (

                                                    <CustomerRow
                                                        key={
                                                            customer.id
                                                        }
                                                        customer={
                                                            customer
                                                        }
                                                        can={
                                                            can
                                                        }
                                                        onOpen={
                                                            () =>
                                                                openCustomer(
                                                                    customer
                                                                )
                                                        }
                                                        onRemove={
                                                            () =>
                                                                removeFromBranch(
                                                                    customer
                                                                )
                                                        }
                                                    />

                                                )
                                            )

                                        )
                                        : (

                                            <tr>

                                                <td
                                                    colSpan="7"
                                                >

                                                    <EmptyState
                                                        filtered={
                                                            hasFilters
                                                        }
                                                        canCreate={
                                                            can(
                                                                'customer.create'
                                                            )
                                                        }
                                                    />

                                                </td>

                                            </tr>

                                        )
                                }

                            </tbody>

                        </table>

                    </div>


                    {/* =================================================
                        PAGINATION
                       ================================================= */}

                    {
                        pageInfo.total > 0 && (

                            <div className="rr-customers-pagination">

                                <div className="rr-customers-pagination-info">

                                    Page

                                    <strong>
                                        {
                                            customers?.current_page
                                            ?? 1
                                        }
                                    </strong>

                                    of

                                    <strong>
                                        {
                                            customers?.last_page
                                            ?? 1
                                        }
                                    </strong>

                                </div>


                                <div className="rr-customers-pagination-links">

                                    {
                                        paginationLinks.map(
                                            (
                                                link,
                                                index
                                            ) => (

                                                <PaginationLink
                                                    key={
                                                        `${link.label}-${index}`
                                                    }
                                                    link={
                                                        link
                                                    }
                                                />

                                            )
                                        )
                                    }

                                </div>

                            </div>

                        )
                    }

                </section>

            </div>

        </BusinessLayout>
    );
}


/*
|--------------------------------------------------------------------------
| Customer Row
|--------------------------------------------------------------------------
*/

function CustomerRow({
    customer,
    can,
    onOpen,
    onRemove,
}) {

    const clickable =
        can(
            'customer.show'
        );


    return (

        <tr
            className={
                [
                    clickable
                        ? 'is-clickable'
                        : '',

                    customer.is_system_customer
                        ? 'is-system-customer'
                        : '',
                ]
                    .filter(
                        Boolean
                    )
                    .join(
                        ' '
                    )
            }
            onClick={
                clickable
                    ? onOpen
                    : undefined
            }
        >

            <td>

                <div className="rr-customer-code">

                    {
                        customer.customer_code
                        ?? '—'
                    }

                </div>

                {
                    customer.is_system_customer && (

                        <span className="rr-customer-system-badge">

                            <i className="bi bi-shield-check"></i>

                            System

                        </span>

                    )
                }

            </td>


            <td>

                <div className="rr-customer-name-cell">

                    <div className="rr-customer-avatar">

                        {
                            getInitials(
                                customer.customer_name
                            )
                        }

                    </div>


                    <div>

                        <strong>
                            {
                                customer.customer_name
                            }
                        </strong>


                        <div className="rr-customer-meta-line">

                            {
                                customer.customer_type ===
                                    'walk_in'
                                    ? (
                                        <span>
                                            Walk-in billing customer
                                        </span>
                                    )
                                    : (
                                        <span>
                                            Regular customer
                                        </span>
                                    )
                            }


                            {
                                Number(
                                    customer.stores_count
                                    ?? 0
                                ) > 1 && (

                                    <span className="rr-customer-shared-badge">

                                        <i className="bi bi-diagram-3"></i>

                                        {
                                            customer.stores_count
                                        } branches

                                    </span>

                                )
                            }

                        </div>

                    </div>

                </div>

            </td>


            <td>

                <div className="rr-customer-contact">

                    <strong>
                        {
                            customer.mobile
                            ?? customer.email
                            ?? '—'
                        }
                    </strong>

                    {
                        customer.mobile &&
                        customer.email && (

                            <span>
                                {
                                    customer.email
                                }
                            </span>

                        )
                    }

                </div>

            </td>


            <td>

                <div className="rr-customer-location">

                    <strong>
                        {
                            customer.gst_number
                            ?? 'No GST'
                        }
                    </strong>

                    <span>
                        {
                            [
                                customer.city,
                                customer.state,
                            ]
                                .filter(
                                    Boolean
                                )
                                .join(
                                    ', '
                                )
                            || '—'
                        }
                    </span>

                </div>

            </td>


            <td>

                <div className="rr-customer-credit">

                    <strong>
                        ₹{
                            formatMoney(
                                customer.credit_limit
                            )
                        }
                    </strong>

                    <span>
                        {
                            Number(
                                customer.credit_days
                                ?? 0
                            )
                        } days
                    </span>

                </div>

            </td>


            <td>

                <span
                    className={
                        `rr-customer-status ${customer.status
                            ? 'active'
                            : 'inactive'
                        }`
                    }
                >

                    <span></span>

                    {
                        customer.status
                            ? 'Active'
                            : 'Inactive'
                    }

                </span>

            </td>


            <td
                onClick={
                    event =>
                        event.stopPropagation()
                }
            >

                <div className="rr-customer-actions">

                    {
                        can(
                            'customer.show'
                        ) && (

                            <Link
                                href={
                                    `/customers/${customer.id}`
                                }
                                className="rr-customer-action-btn view"
                                title="View customer"
                            >

                                <i className="bi bi-eye"></i>

                            </Link>

                        )
                    }


                    {
                        can(
                            'customer.edit'
                        )
                        &&
                        !customer.is_system_customer
                        && (

                            <Link
                                href={
                                    `/customers/${customer.id}/edit`
                                }
                                className="rr-customer-action-btn edit"
                                title="Edit customer"
                            >

                                <i className="bi bi-pencil"></i>

                            </Link>

                        )
                    }


                    {
                        can(
                            'customer.delete'
                        )
                        &&
                        !customer.is_system_customer
                        && (

                            <button
                                type="button"
                                className="rr-customer-action-btn remove"
                                title="Remove from this branch"
                                onClick={
                                    onRemove
                                }
                            >

                                <i className="bi bi-person-dash"></i>

                            </button>

                        )
                    }


                    {
                        customer.is_system_customer && (

                            <button
                                type="button"
                                className="rr-customer-action-btn locked"
                                title="Protected system customer"
                                disabled
                            >

                                <i className="bi bi-lock"></i>

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
| Summary Card
|--------------------------------------------------------------------------
*/

function SummaryCard({
    label,
    value,
    icon,
    tone = 'primary',
}) {

    return (

        <div
            className={
                `rr-customer-summary-card ${tone}`
            }
        >

            <div className="rr-customer-summary-icon">

                <i
                    className={
                        `bi ${icon}`
                    }
                ></i>

            </div>


            <div>

                <span>
                    {
                        label
                    }
                </span>

                <strong>
                    {
                        value
                    }
                </strong>

            </div>

        </div>
    );
}


/*
|--------------------------------------------------------------------------
| Pagination
|--------------------------------------------------------------------------
*/

function PaginationLink({
    link,
}) {

    const disabled =
        !link.url;


    const label =
        String(
            link.label
            ?? ''
        )
            .replace(
                '&laquo;',
                '«'
            )
            .replace(
                '&raquo;',
                '»'
            );


    if (
        disabled
    ) {

        return (

            <span className="rr-customer-page-link disabled">

                {
                    label
                }

            </span>
        );
    }


    return (

        <Link
            href={
                link.url
            }
            preserveScroll
            preserveState
            className={
                `rr-customer-page-link ${link.active
                    ? 'active'
                    : ''
                }`
            }
        >

            {
                label
            }

        </Link>
    );
}


/*
|--------------------------------------------------------------------------
| Empty State
|--------------------------------------------------------------------------
*/

function EmptyState({
    filtered,
    canCreate,
}) {

    return (

        <div className="rr-customers-empty">

            <div className="rr-customers-empty-icon">

                <i className="bi bi-person-vcard"></i>

            </div>


            <strong>

                {
                    filtered
                        ? 'No customers match your filters'
                        : 'No customers in this branch'
                }

            </strong>


            <span>

                {
                    filtered
                        ? 'Change the search or filter values and try again.'
                        : 'Add a customer to begin using this branch customer list.'
                }

            </span>


            {
                canCreate &&
                !filtered && (

                    <Link
                        href="/customers/create"
                        className="rr-customer-primary-btn"
                    >

                        <i className="bi bi-plus-lg"></i>

                        Add Customer

                    </Link>

                )
            }

        </div>
    );
}


/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function getInitials(
    value
) {

    const text =
        String(
            value
            ?? ''
        )
            .trim();


    if (
        !text
    ) {

        return 'CU';
    }


    return text
        .split(
            /\s+/
        )
        .slice(
            0,
            2
        )
        .map(
            part =>
                part
                    .charAt(
                        0
                    )
                    .toUpperCase()
        )
        .join(
            ''
        );
}


function formatMoney(
    value
) {

    return Number(
        value
        ?? 0
    )
        .toLocaleString(
            'en-IN',
            {
                minimumFractionDigits:
                    2,

                maximumFractionDigits:
                    2,
            }
        );
}
