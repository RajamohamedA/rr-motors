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

import '../../../css/business/suppliers.css';


export default function Index({
    suppliers,
    summary,
    filters,
}) {

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
    | Filters
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


    const applyFilters =
        (
            overrides = {}
        ) => {

            router.get(
                '/suppliers',
                {
                    search:
                        overrides.search
                        ?? search,

                    status:
                        overrides.status
                        ?? status,
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


            router.get(
                '/suppliers',
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


    useEffect(
        () => {

            if (
                status ===
                (
                    filters?.status
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
        ]
    );


    /*
    |--------------------------------------------------------------------------
    | Data
    |--------------------------------------------------------------------------
    */

    const rows =
        suppliers?.data
        ?? [];


    const hasFilters =
        Boolean(
            search ||
            status !== ''
        );


    const pageInfo =
        useMemo(
            () => ({

                from:
                    suppliers?.from
                    ?? 0,

                to:
                    suppliers?.to
                    ?? 0,

                total:
                    suppliers?.total
                    ?? 0,

            }),
            [
                suppliers?.from,
                suppliers?.to,
                suppliers?.total,
            ]
        );


    /*
    |--------------------------------------------------------------------------
    | Confirmation State
    |--------------------------------------------------------------------------
    */

    const [
        removeSupplier,
        setRemoveSupplier,
    ] =
        useState(
            null
        );


    const [
        removeProcessing,
        setRemoveProcessing,
    ] =
        useState(
            false
        );


    /*
    |--------------------------------------------------------------------------
    | Status Confirmation
    |--------------------------------------------------------------------------
    */

    const [
        statusSupplier,
        setStatusSupplier,
    ] =
        useState(
            null
        );


    const [
        statusProcessing,
        setStatusProcessing,
    ] =
        useState(
            false
        );


    /*
    |--------------------------------------------------------------------------
    | Actions
    |--------------------------------------------------------------------------
    */

    const openSupplier =
        supplier => {

            if (
                !can(
                    'supplier.show'
                )
            ) {

                return;
            }


            router.visit(
                `/suppliers/${supplier.id}`
            );
        };


    const removeFromBranch =
        supplier => {

            setRemoveSupplier(
                supplier
            );
        };


    const confirmRemoveFromBranch =
        () => {

            if (
                !removeSupplier ||
                removeProcessing
            ) {

                return;
            }


            setRemoveProcessing(
                true
            );


            router.delete(
                `/suppliers/${removeSupplier.id}/remove-from-branch`,
                {
                    preserveScroll:
                        true,

                    onSuccess:
                        () => {

                            setRemoveSupplier(
                                null
                            );
                        },

                    onFinish:
                        () => {

                            setRemoveProcessing(
                                false
                            );
                        },
                }
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Activate / Deactivate
    |--------------------------------------------------------------------------
    */

    const requestStatusChange =
        supplier => {

            if (
                !can(
                    'supplier.edit'
                )
            ) {

                return;
            }


            setStatusSupplier(
                supplier
            );
        };


    const closeStatusChange =
        () => {

            if (
                statusProcessing
            ) {

                return;
            }


            setStatusSupplier(
                null
            );
        };


    const confirmStatusChange =
        () => {

            if (
                !statusSupplier ||
                statusProcessing
            ) {

                return;
            }


            setStatusProcessing(
                true
            );


            router.patch(
                `/suppliers/${statusSupplier.id}/toggle-status`,
                {},
                {
                    preserveScroll:
                        true,

                    onSuccess:
                        () => {

                            setStatusSupplier(
                                null
                            );


                            window.scrollTo({
                                top:
                                    0,

                                behavior:
                                    'smooth',
                            });
                        },

                    onError:
                        () => {

                            window.scrollTo({
                                top:
                                    0,

                                behavior:
                                    'smooth',
                            });
                        },

                    onFinish:
                        () => {

                            setStatusProcessing(
                                false
                            );
                        },
                }
            );
        };


    return (

        <BusinessLayout>

            <Head
                title="Suppliers"
            />


            <div className="rr-suppliers-page">

                <MasterNavigation
                    active="suppliers"
                    title="Suppliers"
                    subtitle="Supplier master, GST, credit and branch management"
                />


                {/* =====================================================
                    PAGE HEADER
                   ===================================================== */}

                <section className="rr-suppliers-header">

                    <div className="rr-suppliers-header-main">

                        <div className="rr-suppliers-header-icon">

                            <i className="bi bi-truck"></i>

                        </div>


                        <div>

                            <span className="rr-suppliers-eyebrow">
                                Supplier Management
                            </span>

                            <h1>
                                Suppliers
                            </h1>

                            <p>
                                Manage suppliers available to {
                                    business?.store_name
                                    ?? 'the active branch'
                                }.
                            </p>

                        </div>

                    </div>


                    <div className="rr-suppliers-header-actions">

                        <button
                            type="button"
                            className="rr-supplier-toolbar-btn"
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
                                'supplier.create'
                            ) && (

                                <Link
                                    href="/suppliers/create"
                                    className="rr-supplier-primary-btn"
                                >

                                    <i className="bi bi-plus-lg"></i>

                                    Add Supplier

                                </Link>

                            )
                        }

                    </div>

                </section>


                {/* =====================================================
                    SUMMARY
                   ===================================================== */}

                <section className="rr-suppliers-summary">

                    <SummaryCard
                        label="Total Suppliers"
                        value={
                            summary?.total
                            ?? 0
                        }
                        icon="bi-truck"
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

                    <SummaryCard
                        label="GST Suppliers"
                        value={
                            summary?.gst_suppliers
                            ?? 0
                        }
                        icon="bi-receipt"
                        tone="primary"
                    />

                    <SummaryCard
                        label="Credit Suppliers"
                        value={
                            summary?.credit_suppliers
                            ?? 0
                        }
                        icon="bi-credit-card"
                        tone="warning"
                    />

                </section>


                {/* =====================================================
                    MAIN PANEL
                   ===================================================== */}

                <section className="rr-suppliers-panel">

                    <div className="rr-suppliers-toolbar">

                        <form
                            className="rr-suppliers-search"
                            onSubmit={
                                submitSearch
                            }
                        >

                            <div className="rr-suppliers-search-box">

                                <i className="bi bi-search"></i>


                                <input
                                    type="text"
                                    value={
                                        search
                                    }
                                    placeholder="Search code, supplier, phone, email or GST..."
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
                                className="rr-supplier-search-btn"
                            >
                                Search
                            </button>

                        </form>


                        <div className="rr-suppliers-filter-group">

                            <div className="rr-suppliers-filter">

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
                                        className="rr-suppliers-clear-btn"
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


                    <div className="rr-suppliers-grid-status">

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
                                suppliers
                            </span>

                        </div>


                        <span className="rr-suppliers-row-limit">

                            <i className="bi bi-layout-three-columns"></i>

                            15 rows per page

                        </span>

                    </div>


                    <div className="rr-suppliers-table-wrap">

                        <table className="rr-suppliers-table">

                            <thead>

                                <tr>

                                    <th className="rr-supplier-col-code">
                                        Code
                                    </th>

                                    <th>
                                        Supplier
                                    </th>

                                    <th>
                                        Contact
                                    </th>

                                    <th>
                                        GST / Location
                                    </th>

                                    <th className="rr-supplier-col-credit">
                                        Credit
                                    </th>

                                    <th className="rr-supplier-col-status">
                                        Status
                                    </th>

                                    <th className="rr-supplier-col-actions">
                                        Actions
                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                                {
                                    rows.length > 0
                                        ? rows.map(
                                            supplier => (

                                                <SupplierRow
                                                    key={
                                                        supplier.id
                                                    }
                                                    supplier={
                                                        supplier
                                                    }
                                                    can={
                                                        can
                                                    }
                                                    onOpen={
                                                        () =>
                                                            openSupplier(
                                                                supplier
                                                            )
                                                    }
                                                    onRemove={
                                                        () =>
                                                            removeFromBranch(
                                                                supplier
                                                            )
                                                    }
                                                    onStatusChange={
                                                        () =>
                                                            requestStatusChange(
                                                                supplier
                                                            )
                                                    }
                                                />

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
                                                                'supplier.create'
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


                    {
                        pageInfo.total > 0 && (

                            <div className="rr-suppliers-pagination">

                                <div className="rr-suppliers-pagination-info">

                                    Page

                                    <strong>
                                        {
                                            suppliers?.current_page
                                            ?? 1
                                        }
                                    </strong>

                                    of

                                    <strong>
                                        {
                                            suppliers?.last_page
                                            ?? 1
                                        }
                                    </strong>

                                </div>


                                <div className="rr-suppliers-pagination-links">

                                    {
                                        (
                                            suppliers?.links
                                            ?? []
                                        ).map(
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
                    COMMON CONFIRMATION MODAL
                   ===================================================== */}

                <ConfirmModal
                    show={
                        Boolean(
                            removeSupplier
                        )
                    }
                    title="Remove Supplier From This Branch?"
                    message={
                        removeSupplier
                            ? `${removeSupplier.supplier_name} will be removed from the current branch. The supplier master will remain available to other assigned branches.`
                            : ''
                    }
                    confirmText="Remove From Branch"
                    cancelText="Cancel"
                    icon="bi-diagram-2"
                    variant="danger"
                    processing={
                        removeProcessing
                    }
                    onConfirm={
                        confirmRemoveFromBranch
                    }
                    onCancel={
                        () => {

                            if (
                                !removeProcessing
                            ) {

                                setRemoveSupplier(
                                    null
                                );
                            }
                        }
                    }
                />


                <ConfirmModal
                    show={
                        Boolean(
                            statusSupplier
                        )
                    }
                    title={
                        statusSupplier?.status
                            ? 'Deactivate Supplier?'
                            : 'Activate Supplier?'
                    }
                    message={
                        statusSupplier
                            ? (
                                statusSupplier.status
                                    ? `${statusSupplier.supplier_name} will become inactive and should no longer be available for new purchase transactions.`
                                    : `${statusSupplier.supplier_name} will become active and available for purchase transactions again.`
                            )
                            : ''
                    }
                    confirmText={
                        statusSupplier?.status
                            ? 'Deactivate Supplier'
                            : 'Activate Supplier'
                    }
                    cancelText="Cancel"
                    icon={
                        statusSupplier?.status
                            ? 'bi-pause-circle'
                            : 'bi-play-circle'
                    }
                    variant={
                        statusSupplier?.status
                            ? 'danger'
                            : 'primary'
                    }
                    processing={
                        statusProcessing
                    }
                    onConfirm={
                        confirmStatusChange
                    }
                    onCancel={
                        closeStatusChange
                    }
                />

            </div>

        </BusinessLayout>
    );
}


/*
|--------------------------------------------------------------------------
| Supplier Row
|--------------------------------------------------------------------------
*/

function SupplierRow({
    supplier,
    can,
    onOpen,
    onRemove,
    onStatusChange,
}) {

    const clickable =
        can(
            'supplier.show'
        );


    return (

        <tr
            className={
                clickable
                    ? 'is-clickable'
                    : ''
            }
            onClick={
                clickable
                    ? onOpen
                    : undefined
            }
        >

            <td>

                <div className="rr-supplier-code">

                    {
                        supplier.supplier_code
                        ?? '—'
                    }

                </div>

            </td>


            <td>

                <div className="rr-supplier-name-cell">

                    <div className="rr-supplier-avatar">

                        {
                            getInitials(
                                supplier.supplier_name
                            )
                        }

                    </div>


                    <div>

                        <strong>
                            {
                                supplier.supplier_name
                            }
                        </strong>


                        <div className="rr-supplier-meta-line">

                            <span>
                                {
                                    supplier.contact_person
                                    ?? 'Supplier account'
                                }
                            </span>


                            {
                                Number(
                                    supplier.stores_count
                                    ?? 0
                                ) > 1 && (

                                    <span className="rr-supplier-shared-badge">

                                        <i className="bi bi-diagram-3"></i>

                                        {
                                            supplier.stores_count
                                        } branches

                                    </span>

                                )
                            }

                        </div>

                    </div>

                </div>

            </td>


            <td>

                <div className="rr-supplier-contact">

                    <strong>
                        {
                            supplier.phone
                            ?? supplier.email
                            ?? '—'
                        }
                    </strong>

                    {
                        supplier.phone &&
                        supplier.email && (

                            <span>
                                {
                                    supplier.email
                                }
                            </span>

                        )
                    }

                </div>

            </td>


            <td>

                <div className="rr-supplier-location">

                    <strong>
                        {
                            supplier.gst_number
                            ?? 'No GST'
                        }
                    </strong>

                    <span>
                        {
                            [
                                supplier.city,
                                supplier.state,
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

                <div className="rr-supplier-credit">

                    <strong>
                        ₹{
                            formatMoney(
                                supplier.credit_limit
                            )
                        }
                    </strong>

                    <span>
                        {
                            Number(
                                supplier.credit_days
                                ?? 0
                            )
                        } days
                    </span>

                </div>

            </td>


            <td>

                <span
                    className={
                        `rr-supplier-status ${supplier.status
                            ? 'active'
                            : 'inactive'
                        }`
                    }
                >

                    <span></span>

                    {
                        supplier.status
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

                <div className="rr-supplier-actions">

                    {
                        can(
                            'supplier.show'
                        ) && (

                            <Link
                                href={
                                    `/suppliers/${supplier.id}`
                                }
                                className="rr-supplier-action-btn view"
                                title="View supplier"
                            >

                                <i className="bi bi-eye"></i>

                            </Link>

                        )
                    }


                    {
                        can(
                            'supplier.edit'
                        ) && (

                            <Link
                                href={
                                    `/suppliers/${supplier.id}/edit`
                                }
                                className="rr-supplier-action-btn edit"
                                title="Edit supplier"
                            >

                                <i className="bi bi-pencil"></i>

                            </Link>

                        )
                    }


                    {
                        can(
                            'supplier.edit'
                        ) && (

                            <button
                                type="button"
                                className={
                                    `rr-supplier-action-btn ${supplier.status
                                        ? 'deactivate'
                                        : 'activate'
                                    }`
                                }
                                title={
                                    supplier.status
                                        ? 'Deactivate supplier'
                                        : 'Activate supplier'
                                }
                                onClick={
                                    onStatusChange
                                }
                            >

                                <i
                                    className={
                                        supplier.status
                                            ? 'bi bi-pause-circle'
                                            : 'bi bi-play-circle'
                                    }
                                ></i>

                            </button>

                        )
                    }


                    {
                        can(
                            'supplier.delete'
                        ) && (

                            <button
                                type="button"
                                className="rr-supplier-action-btn remove"
                                title="Remove from this branch"
                                onClick={
                                    onRemove
                                }
                            >

                                <i className="bi bi-dash-circle"></i>

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
                `rr-supplier-summary-card ${tone}`
            }
        >

            <div className="rr-supplier-summary-icon">

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

            <span className="rr-supplier-page-link disabled">

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
                `rr-supplier-page-link ${link.active
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

        <div className="rr-suppliers-empty">

            <div className="rr-suppliers-empty-icon">

                <i className="bi bi-truck"></i>

            </div>


            <strong>

                {
                    filtered
                        ? 'No suppliers match your filters'
                        : 'No suppliers in this branch'
                }

            </strong>


            <span>

                {
                    filtered
                        ? 'Change the search or status filter and try again.'
                        : 'Add a supplier to begin using this branch supplier list.'
                }

            </span>


            {
                canCreate &&
                !filtered && (

                    <Link
                        href="/suppliers/create"
                        className="rr-supplier-primary-btn"
                    >

                        <i className="bi bi-plus-lg"></i>

                        Add Supplier

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

        return 'SU';
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
