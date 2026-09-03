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

import ConfirmModal
    from '../../Components/ConfirmModal';

import '../../../css/business/godowns.css';


export default function Index({
    godowns,
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
                isSuperAdmin
                ||
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
    | Filters
    |--------------------------------------------------------------------------
    */

    const [
        search,
        setSearch,
    ] = useState(
        filters?.search
        ?? ''
    );


    const [
        status,
        setStatus,
    ] = useState(
        filters?.status
        ?? ''
    );


    /*
    |--------------------------------------------------------------------------
    | Confirm
    |--------------------------------------------------------------------------
    */

    const [
        confirmState,
        setConfirmState,
    ] = useState({

        open:
            false,

        godown:
            null,

        action:
            null,
    });


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
                '/godowns',
                {
                    search:
                        overrides.search
                        ?? search,

                    status:
                        overrides.status
                        ?? status,
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
    | Status Auto Filter
    |--------------------------------------------------------------------------
    */

    useEffect(
        () => {

            const currentStatus =
                String(
                    filters?.status
                    ?? ''
                );


            if (
                String(status) ===
                currentStatus
            ) {
                return;
            }


            const timer =
                window.setTimeout(
                    () => {

                        applyFilters();

                    },
                    120
                );


            return () =>
                window.clearTimeout(
                    timer
                );

        },
        [
            status,
        ]
    );


    /*
    |--------------------------------------------------------------------------
    | Clear
    |--------------------------------------------------------------------------
    */

    const clearFilters =
        () => {

            setSearch('');
            setStatus('');


            router.get(
                '/godowns',
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
    | Data
    |--------------------------------------------------------------------------
    */

    const rows =
        godowns?.data
        ?? [];


    const paginationLinks =
        godowns?.links
        ?? [];


    const hasFilters =
        Boolean(
            search
            ||
            status !== ''
        );


    /*
    |--------------------------------------------------------------------------
    | Open
    |--------------------------------------------------------------------------
    */

    const openGodown =
        godown => {

            if (
                !can(
                    'godown.view'
                )
            ) {
                return;
            }


            router.visit(
                `/godowns/${godown.id}`
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Status
    |--------------------------------------------------------------------------
    */

    const requestStatusChange =
        godown => {

            if (
                !can(
                    'godown.edit'
                )
            ) {
                return;
            }


            setConfirmState({

                open:
                    true,

                godown,

                action:
                    godown.status
                        ? 'deactivate'
                        : 'activate',
            });
        };


    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    */

    const requestDelete =
        godown => {

            if (
                !can(
                    'godown.delete'
                )
            ) {
                return;
            }


            setConfirmState({

                open:
                    true,

                godown,

                action:
                    'delete',
            });
        };


    /*
    |--------------------------------------------------------------------------
    | Close Confirm
    |--------------------------------------------------------------------------
    */

    const closeConfirm =
        () => {

            setConfirmState({

                open:
                    false,

                godown:
                    null,

                action:
                    null,
            });
        };


    /*
    |--------------------------------------------------------------------------
    | Confirm Action
    |--------------------------------------------------------------------------
    */

    const confirmAction =
        () => {

            const godown =
                confirmState.godown;


            if (
                !godown
            ) {

                closeConfirm();

                return;
            }


            if (
                confirmState.action ===
                'delete'
            ) {

                router.delete(
                    `/godowns/${godown.id}`,
                    {
                        preserveScroll:
                            true,

                        onFinish:
                            closeConfirm,
                    }
                );


                return;
            }


            router.patch(
                `/godowns/${godown.id}/toggle-status`,
                {},
                {
                    preserveScroll:
                        true,

                    onFinish:
                        closeConfirm,
                }
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Confirm Content
    |--------------------------------------------------------------------------
    */

    const confirmContent =
        useMemo(
            () => {

                const godown =
                    confirmState.godown;


                if (
                    !godown
                ) {

                    return {

                        title:
                            '',

                        message:
                            '',

                        confirmText:
                            'Confirm',

                        tone:
                            'warning',
                    };
                }


                if (
                    confirmState.action ===
                    'delete'
                ) {

                    return {

                        title:
                            'Delete Godown?',

                        message:
                            `Delete "${godown.godown_name}"? This is only allowed if the Godown has no current stock and no stock transaction history.`,

                        confirmText:
                            'Delete Godown',

                        tone:
                            'danger',
                    };
                }


                if (
                    confirmState.action ===
                    'deactivate'
                ) {

                    return {

                        title:
                            'Deactivate Godown?',

                        message:
                            `Deactivate "${godown.godown_name}" for this branch? Existing stock will remain unchanged.`,

                        confirmText:
                            'Deactivate',

                        tone:
                            'warning',
                    };
                }


                return {

                    title:
                        'Activate Godown?',

                    message:
                        `Activate "${godown.godown_name}" for this branch?`,

                    confirmText:
                        'Activate',

                    tone:
                        'success',
                };

            },
            [
                confirmState,
            ]
        );


    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */

    return (

        <BusinessLayout>

            <Head
                title="Godowns"
            />


            <div className="rr-godowns-page">

                <MasterNavigation
                    active="godowns"
                    title="Godowns"
                    subtitle="Warehouse and branch stock locations"
                />


                {/* =====================================================
                    HEADER
                   ===================================================== */}

                <section className="rr-godowns-header">

                    <div className="rr-godowns-header-main">

                        <div className="rr-godowns-header-icon">

                            <i className="bi bi-boxes"></i>

                        </div>


                        <div>

                            <span className="rr-godowns-eyebrow">
                                Inventory Master
                            </span>


                            <h1>
                                Godowns
                            </h1>


                            <p>
                                Manage stock locations for {
                                    business?.store_name
                                    ?? 'the active branch'
                                }.
                            </p>

                        </div>

                    </div>


                    <div className="rr-godowns-header-actions">

                        <button
                            type="button"
                            className="rr-godown-toolbar-btn"
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
                                'godown.create'
                            ) && (

                                <Link
                                    href="/godowns/create"
                                    className="rr-godown-primary-btn"
                                >

                                    <i className="bi bi-plus-lg"></i>

                                    Add Godown

                                </Link>

                            )
                        }

                    </div>

                </section>


                {/* =====================================================
                    SUMMARY
                   ===================================================== */}

                <section className="rr-godowns-summary">

                    <SummaryCard
                        label="Total Godowns"
                        value={
                            summary?.total
                            ?? 0
                        }
                        icon="bi-boxes"
                    />


                    <SummaryCard
                        label="Active"
                        value={
                            summary?.active
                            ?? 0
                        }
                        icon="bi-check-circle"
                        tone="success"
                    />


                    <SummaryCard
                        label="Inactive"
                        value={
                            summary?.inactive
                            ?? 0
                        }
                        icon="bi-dash-circle"
                        tone="muted"
                    />

                </section>


                {/* =====================================================
                    PANEL
                   ===================================================== */}

                <section className="rr-godowns-panel">

                    {/* =================================================
                        TOOLBAR
                       ================================================= */}

                    <div className="rr-godowns-toolbar">

                        <form
                            className="rr-godowns-search"
                            onSubmit={
                                submitSearch
                            }
                        >

                            <div className="rr-godowns-search-box">

                                <i className="bi bi-search"></i>


                                <input
                                    type="text"
                                    value={
                                        search
                                    }
                                    placeholder="Search Godown name, code or address..."
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
                                            onClick={
                                                () => {

                                                    setSearch('');

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
                                className="rr-godown-search-btn"
                            >

                                Search

                            </button>

                        </form>


                        <div className="rr-godowns-filters">

                            <div className="rr-godown-filter">

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


                            {
                                hasFilters && (

                                    <button
                                        type="button"
                                        className="rr-godowns-clear-btn"
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
                        GRID INFO
                       ================================================= */}

                    <div className="rr-godowns-grid-status">

                        <div>

                            <i className="bi bi-table"></i>

                            Showing

                            <strong>
                                {
                                    godowns?.from
                                    ?? 0
                                }–{
                                    godowns?.to
                                    ?? 0
                                }
                            </strong>

                            of

                            <strong>
                                {
                                    godowns?.total
                                    ?? 0
                                }
                            </strong>

                            Godowns

                        </div>


                        <span>
                            Click a row to view Godown details
                        </span>

                    </div>


                    {/* =================================================
                        TABLE
                       ================================================= */}

                    <div className="rr-godowns-table-wrap">

                        <table className="rr-godowns-table">

                            <thead>

                                <tr>

                                    <th>
                                        Godown
                                    </th>

                                    <th>
                                        Code
                                    </th>

                                    <th>
                                        Address
                                    </th>

                                    <th className="number">
                                        Products
                                    </th>

                                    <th className="number">
                                        Stock Qty
                                    </th>

                                    <th className="number">
                                        Est. Cost
                                    </th>

                                    <th className="number">
                                        Est. Selling
                                    </th>

                                    <th className="number">
                                        Low Stock
                                    </th>

                                    <th className="center">
                                        Status
                                    </th>

                                    <th className="center actions">
                                        Actions
                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                                {
                                    rows.length > 0
                                        ? (

                                            rows.map(
                                                godown => (

                                                    <GodownRow
                                                        key={
                                                            godown.id
                                                        }

                                                        godown={
                                                            godown
                                                        }

                                                        can={
                                                            can
                                                        }

                                                        onOpen={
                                                            () =>
                                                                openGodown(
                                                                    godown
                                                                )
                                                        }

                                                        onStatusChange={
                                                            () =>
                                                                requestStatusChange(
                                                                    godown
                                                                )
                                                        }

                                                        onDelete={
                                                            () =>
                                                                requestDelete(
                                                                    godown
                                                                )
                                                        }
                                                    />

                                                )
                                            )

                                        )
                                        : (

                                            <tr>

                                                <td
                                                    colSpan="10"
                                                >

                                                    <EmptyState
                                                        filtered={
                                                            hasFilters
                                                        }

                                                        canCreate={
                                                            can(
                                                                'godown.create'
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
                        Number(
                            godowns?.total
                            ?? 0
                        ) > 0 && (

                            <div className="rr-godowns-pagination">

                                <div>

                                    Page

                                    <strong>
                                        {
                                            godowns?.current_page
                                            ?? 1
                                        }
                                    </strong>

                                    of

                                    <strong>
                                        {
                                            godowns?.last_page
                                            ?? 1
                                        }
                                    </strong>

                                </div>


                                <div className="rr-godowns-pagination-links">

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


                {/* =====================================================
                    CONFIRM
                   ===================================================== */}

                <ConfirmModal
                    show={
                        confirmState.open
                    }

                    title={
                        confirmContent.title
                    }

                    message={
                        confirmContent.message
                    }

                    confirmText={
                        confirmContent.confirmText
                    }

                    variant={
                        confirmContent.tone
                    }

                    onConfirm={
                        confirmAction
                    }

                    onCancel={
                        closeConfirm
                    }
                />

            </div>

        </BusinessLayout>
    );
}


/*
|--------------------------------------------------------------------------
| Godown Row
|--------------------------------------------------------------------------
*/

function GodownRow({
    godown,
    can,
    onOpen,
    onStatusChange,
    onDelete,
}) {

    const canOpen =
        can(
            'godown.view'
        );


    return (

        <tr
            className={
                canOpen
                    ? 'is-clickable'
                    : ''
            }
            onClick={
                canOpen
                    ? onOpen
                    : undefined
            }
        >

            <td>

                <div className="rr-godown-name-cell">

                    <div className="rr-godown-avatar">

                        {
                            getInitials(
                                godown.godown_name
                            )
                        }

                    </div>


                    <div>

                        <strong>
                            {
                                godown.godown_name
                            }
                        </strong>


                        <span>
                            Stock location
                        </span>

                    </div>

                </div>

            </td>


            <td>

                <span className="rr-godown-code">

                    {
                        godown.godown_code
                        || '—'
                    }

                </span>

            </td>


            <td>

                <div className="rr-godown-address">

                    {
                        godown.address
                        || 'No address'
                    }

                </div>

            </td>


            <td className="number">

                <strong>

                    {
                        formatNumber(
                            godown.products_count
                        )
                    }

                </strong>

            </td>


            <td className="number">

                {
                    formatQty(
                        godown.stock_quantity
                    )
                }

            </td>


            <td className="number">

                {
                    formatMoney(
                        godown.estimated_cost
                    )
                }

            </td>


            <td className="number">

                {
                    formatMoney(
                        godown.estimated_selling_value
                    )
                }

            </td>


            <td className="number">

                <span
                    className={
                        `rr-godown-low-stock ${Number(
                            godown.low_stock_count
                            ?? 0
                        ) > 0
                            ? 'warning'
                            : 'ok'
                        }`
                    }
                >

                    {
                        godown.low_stock_count
                        ?? 0
                    }

                </span>

            </td>


            <td className="center">

                <span
                    className={
                        `rr-godown-status ${godown.status
                            ? 'active'
                            : 'inactive'
                        }`
                    }
                >

                    <span></span>

                    {
                        godown.status
                            ? 'Active'
                            : 'Inactive'
                    }

                </span>

            </td>


            <td
                className="center"
                onClick={
                    event =>
                        event.stopPropagation()
                }
            >

                <div className="rr-godown-actions">

                    {
                        can(
                            'godown.view'
                        ) && (

                            <Link
                                href={
                                    `/godowns/${godown.id}`
                                }
                                className="rr-godown-action-btn view"
                                title="View Godown"
                            >

                                <i className="bi bi-eye"></i>

                            </Link>

                        )
                    }


                    {
                        can(
                            'godown.view'
                        ) && (

                            <Link
                                href={
                                    `/godowns/${godown.id}/products`
                                }
                                className="rr-godown-action-btn inventory"
                                title="View inventory"
                            >

                                <i className="bi bi-box-seam"></i>

                            </Link>

                        )
                    }


                    {
                        can(
                            'godown.edit'
                        ) && (

                            <Link
                                href={
                                    `/godowns/${godown.id}/edit`
                                }
                                className="rr-godown-action-btn edit"
                                title="Edit Godown"
                            >

                                <i className="bi bi-pencil"></i>

                            </Link>

                        )
                    }


                    {
                        can(
                            'godown.edit'
                        ) && (

                            <button
                                type="button"
                                className={
                                    `rr-godown-action-btn ${godown.status
                                        ? 'deactivate'
                                        : 'activate'
                                    }`
                                }
                                title={
                                    godown.status
                                        ? 'Deactivate Godown'
                                        : 'Activate Godown'
                                }
                                onClick={
                                    onStatusChange
                                }
                            >

                                <i
                                    className={
                                        godown.status
                                            ? 'bi bi-toggle-on'
                                            : 'bi bi-toggle-off'
                                    }
                                ></i>

                            </button>

                        )
                    }


                    {
                        can(
                            'godown.delete'
                        ) && (

                            <button
                                type="button"
                                className="rr-godown-action-btn delete"
                                title="Delete Godown"
                                onClick={
                                    onDelete
                                }
                            >

                                <i className="bi bi-trash"></i>

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
| Summary
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
                `rr-godown-summary-card ${tone}`
            }
        >

            <div className="rr-godown-summary-icon">

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
                    {value}
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

    const label =
        String(
            link?.label
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
        !link?.url
    ) {

        return (

            <span className="rr-godown-page-link disabled">

                {label}

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
                `rr-godown-page-link ${link.active
                    ? 'active'
                    : ''
                }`
            }
        >

            {label}

        </Link>
    );
}


/*
|--------------------------------------------------------------------------
| Empty
|--------------------------------------------------------------------------
*/

function EmptyState({
    filtered,
    canCreate,
}) {

    return (

        <div className="rr-godowns-empty">

            <div className="rr-godowns-empty-icon">

                <i className="bi bi-boxes"></i>

            </div>


            <strong>

                {
                    filtered
                        ? 'No Godowns match your filters'
                        : 'No Godowns found for this branch'
                }

            </strong>


            <span>

                {
                    filtered
                        ? 'Change or clear the search filters and try again.'
                        : 'Create a Godown to manage branch warehouse stock.'
                }

            </span>


            {
                canCreate
                &&
                !filtered && (

                    <Link
                        href="/godowns/create"
                        className="rr-godown-primary-btn"
                    >

                        <i className="bi bi-plus-lg"></i>

                        Add Godown

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

function formatMoney(
    value
) {

    const number =
        Number(
            value
            ?? 0
        );


    if (
        Number.isNaN(
            number
        )
    ) {
        return '₹0.00';
    }


    return new Intl.NumberFormat(
        'en-IN',
        {
            style:
                'currency',

            currency:
                'INR',

            minimumFractionDigits:
                2,

            maximumFractionDigits:
                2,
        }
    ).format(
        number
    );
}


function formatQty(
    value
) {

    const number =
        Number(
            value
            ?? 0
        );


    if (
        Number.isNaN(
            number
        )
    ) {
        return '0';
    }


    return new Intl.NumberFormat(
        'en-IN',
        {
            maximumFractionDigits:
                3,
        }
    ).format(
        number
    );
}


function formatNumber(
    value
) {

    const number =
        Number(
            value
            ?? 0
        );


    if (
        Number.isNaN(
            number
        )
    ) {
        return '0';
    }


    return new Intl.NumberFormat(
        'en-IN'
    ).format(
        number
    );
}


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
        return 'GD';
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