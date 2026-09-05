import React, {
    useEffect,
    useRef,
    useState,
} from 'react';

import {
    Head,
    Link,
    router,
    usePage,
} from '@inertiajs/react';

import BusinessLayout from '@/Layouts/BusinessLayout';

import MasterNavigation
    from '../../Components/Business/MasterNavigation';

import ConfirmModal
    from '../../Components/ConfirmModal';

import '../../../css/business/purchases.css';


export default function Index({
    purchases,
    suppliers = [],
    summary = {},
    filters = {},
}) {

    /*
    |--------------------------------------------------------------------------
    | Shared Context
    |--------------------------------------------------------------------------
    */

    const {
        business,
    } = usePage().props;


    /*
    |--------------------------------------------------------------------------
    | Filter State
    |--------------------------------------------------------------------------
    */

    const [
        search,
        setSearch,
    ] = useState(
        filters.search ?? ''
    );


    const [
        supplierId,
        setSupplierId,
    ] = useState(
        String(
            filters.supplier_id ?? ''
        )
    );


    const [
        taxType,
        setTaxType,
    ] = useState(
        String(
            filters.tax_type ?? ''
        )
    );


    const [
        status,
        setStatus,
    ] = useState(
        String(
            filters.status ?? ''
        )
    );


    const [
        dateFrom,
        setDateFrom,
    ] = useState(
        filters.date_from ?? ''
    );


    const [
        dateTo,
        setDateTo,
    ] = useState(
        filters.date_to ?? ''
    );


    /*
    |--------------------------------------------------------------------------
    | Confirm Modal
    |--------------------------------------------------------------------------
    */

    const [
        confirmModal,
        setConfirmModal,
    ] = useState({
        show:
            false,

        purchase:
            null,

        processing:
            false,
    });


    const firstRender =
        useRef(
            true
        );


    /*
    |--------------------------------------------------------------------------
    | Auto Filter
    |--------------------------------------------------------------------------
    */

    useEffect(
        () => {

            if (
                firstRender.current
            ) {

                firstRender.current =
                    false;

                return;
            }


            const timer =
                window.setTimeout(
                    () => {

                        router.get(
                            '/purchases',
                            {
                                search,

                                supplier_id:
                                    supplierId,

                                tax_type:
                                    taxType,

                                status,

                                date_from:
                                    dateFrom,

                                date_to:
                                    dateTo,
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

                    },
                    350
                );


            return () =>
                window.clearTimeout(
                    timer
                );

        },
        [
            search,
            supplierId,
            taxType,
            status,
            dateFrom,
            dateTo,
        ]
    );


    /*
    |--------------------------------------------------------------------------
    | Reset Filters
    |--------------------------------------------------------------------------
    */

    const resetFilters =
        () => {

            setSearch('');
            setSupplierId('');
            setTaxType('');
            setStatus('');
            setDateFrom('');
            setDateTo('');


            router.get(
                '/purchases',
                {},
                {
                    preserveState:
                        false,

                    preserveScroll:
                        true,

                    replace:
                        true,
                }
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Open Purchase
    |--------------------------------------------------------------------------
    */

    const openPurchase =
        purchase => {

            router.visit(
                `/purchases/${purchase.id}`
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Delete Confirm
    |--------------------------------------------------------------------------
    */

    const openDeleteConfirm =
        (
            event,
            purchase
        ) => {

            event.stopPropagation();


            setConfirmModal({
                show:
                    true,

                purchase:
                    purchase,

                processing:
                    false,
            });
        };


    const closeDeleteConfirm =
        () => {

            if (
                confirmModal.processing
            ) {
                return;
            }


            setConfirmModal({
                show:
                    false,

                purchase:
                    null,

                processing:
                    false,
            });
        };


    const confirmDelete =
        () => {

            const purchase =
                confirmModal.purchase;


            if (
                !purchase
            ) {
                return;
            }


            setConfirmModal(
                previous => ({
                    ...previous,

                    processing:
                        true,
                })
            );


            router.delete(
                `/purchases/${purchase.id}`,
                {
                    preserveScroll:
                        true,

                    onFinish:
                        () => {

                            setConfirmModal({
                                show:
                                    false,

                                purchase:
                                    null,

                                processing:
                                    false,
                            });
                        },
                }
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Has Filters
    |--------------------------------------------------------------------------
    */

    const hasFilters =
        Boolean(
            search
            ||
            supplierId
            ||
            taxType
            ||
            status !== ''
            ||
            dateFrom
            ||
            dateTo
        );


    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */

    return (
        <>
            <Head
                title="Purchases"
            />


            <div className="rr-purchase-page">

                {/* =====================================================
                    MASTER NAVIGATION
                   ===================================================== */}

                <MasterNavigation
                    active="purchases"
                    title="Purchases"
                    subtitle="Supplier purchases, GST and branch stock"
                />


                {/* =====================================================
                    HEADER
                   ===================================================== */}

                <section className="rr-purchase-header">

                    <div className="rr-purchase-header-main">

                        <Link
                            href="/business/dashboard"
                            className="rr-purchase-back-btn"
                            title="Back to Dashboard"
                        >

                            <i className="bi bi-arrow-left"></i>

                        </Link>


                        <div className="rr-purchase-header-icon">

                            <i className="bi bi-cart-plus"></i>

                        </div>


                        <div className="rr-purchase-header-content">

                            <span className="rr-purchase-eyebrow">
                                Purchase Management
                            </span>


                            <h1>
                                Purchase Register
                            </h1>


                            <div className="rr-purchase-header-meta">

                                <span>

                                    <i className="bi bi-buildings"></i>

                                    {
                                        business?.company_name
                                        ?? 'Company'
                                    }

                                </span>


                                <span>

                                    <i className="bi bi-shop"></i>

                                    {
                                        business?.store_name
                                        ?? 'Branch'
                                    }

                                </span>

                            </div>

                        </div>

                    </div>


                    <div className="rr-purchase-header-actions">

                        <Link
                            href="/purchases/create"
                            className="rr-purchase-primary-btn"
                        >

                            <i className="bi bi-plus-lg"></i>

                            New Purchase

                        </Link>

                    </div>

                </section>


                {/* =====================================================
                    SUMMARY
                   ===================================================== */}

                <section className="rr-purchase-summary">

                    <SummaryCard
                        title="Total Purchases"
                        value={
                            summary.total ?? 0
                        }
                        icon="bi-receipt"
                        tone="primary"
                    />


                    <SummaryCard
                        title="GST Purchases"
                        value={
                            summary.gst ?? 0
                        }
                        icon="bi-percent"
                        tone="success"
                    />


                    <SummaryCard
                        title="Without GST"
                        value={
                            summary.non_gst ?? 0
                        }
                        icon="bi-slash-circle"
                        tone="neutral"
                    />


                    <SummaryCard
                        title="Purchase Value"
                        value={
                            formatMoney(
                                summary.purchase_value
                            )
                        }
                        icon="bi-currency-rupee"
                        tone="info"
                    />


                    <SummaryCard
                        title="Outstanding"
                        value={
                            formatMoney(
                                summary.balance
                            )
                        }
                        icon="bi-wallet2"
                        tone="warning"
                    />

                </section>


                {/* =====================================================
                    FILTER WINDOW
                   ===================================================== */}

                <section className="rr-purchase-filter-window">

                    <div className="rr-purchase-filter-header">

                        <div>

                            <i className="bi bi-funnel"></i>

                            <strong>
                                Purchase Search
                            </strong>

                        </div>


                        <span>

                            {
                                purchases?.total ?? 0
                            }

                            {' '}

                            record(s)

                        </span>

                    </div>


                    <div className="rr-purchase-filter-body">

                        {/* Search */}

                        <div className="rr-purchase-search-box">

                            <i className="bi bi-search"></i>


                            <input
                                type="search"
                                value={
                                    search
                                }
                                placeholder="Purchase no, supplier, invoice no..."
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
                                            () =>
                                                setSearch('')
                                        }
                                    >

                                        <i className="bi bi-x-lg"></i>

                                    </button>

                                )
                            }

                        </div>


                        {/* Supplier */}

                        <FilterBox
                            label="Supplier"
                            value={
                                supplierId
                            }
                            onChange={
                                setSupplierId
                            }
                        >

                            <option value="">
                                All Suppliers
                            </option>


                            {
                                suppliers.map(
                                    supplier => (

                                        <option
                                            key={
                                                supplier.id
                                            }
                                            value={
                                                supplier.id
                                            }
                                        >

                                            {
                                                supplier.supplier_name
                                            }

                                        </option>

                                    )
                                )
                            }

                        </FilterBox>


                        {/* Tax Type */}

                        <FilterBox
                            label="Tax Type"
                            value={
                                taxType
                            }
                            onChange={
                                setTaxType
                            }
                        >

                            <option value="">
                                All Purchase Types
                            </option>

                            <option value="gst">
                                GST Purchase
                            </option>

                            <option value="non_gst">
                                Without GST
                            </option>

                        </FilterBox>


                        {/* Status */}

                        <FilterBox
                            label="Status"
                            value={
                                status
                            }
                            onChange={
                                setStatus
                            }
                        >

                            <option value="">
                                All Status
                            </option>

                            <option value="1">
                                Active
                            </option>

                            <option value="0">
                                Inactive
                            </option>

                        </FilterBox>


                        {/* From */}

                        <DateFilter
                            label="From"
                            value={
                                dateFrom
                            }
                            onChange={
                                setDateFrom
                            }
                        />


                        {/* To */}

                        <DateFilter
                            label="To"
                            value={
                                dateTo
                            }
                            onChange={
                                setDateTo
                            }
                        />


                        {/* Clear */}

                        <button
                            type="button"
                            className="rr-purchase-clear-btn"
                            disabled={
                                !hasFilters
                            }
                            onClick={
                                resetFilters
                            }
                        >

                            <i className="bi bi-arrow-counterclockwise"></i>

                            Clear

                        </button>

                    </div>

                </section>


                {/* =====================================================
                    TABLE
                   ===================================================== */}

                <section className="rr-purchase-grid-window">

                    <div className="rr-purchase-grid-toolbar">

                        <div className="rr-purchase-grid-title">

                            <i className="bi bi-table"></i>


                            <div>

                                <strong>
                                    Purchase Register
                                </strong>


                                <span>
                                    Click a row to view complete purchase details
                                </span>

                            </div>

                        </div>


                        <div className="rr-purchase-grid-count">

                            Showing

                            {' '}

                            <strong>
                                {
                                    purchases?.from ?? 0
                                }
                            </strong>

                            {' '}

                            to

                            {' '}

                            <strong>
                                {
                                    purchases?.to ?? 0
                                }
                            </strong>

                            {' '}

                            of

                            {' '}

                            <strong>
                                {
                                    purchases?.total ?? 0
                                }
                            </strong>

                        </div>

                    </div>


                    <div className="rr-purchase-table-wrap">

                        <table className="rr-purchase-table">

                            <thead>

                                <tr>

                                    <th>
                                        Purchase
                                    </th>

                                    <th>
                                        Supplier
                                    </th>

                                    <th>
                                        Supplier Invoice
                                    </th>

                                    <th>
                                        Tax Type
                                    </th>

                                    <th className="number">
                                        Subtotal
                                    </th>

                                    <th className="number">
                                        Discount
                                    </th>

                                    <th className="number">
                                        Tax
                                    </th>

                                    <th className="number">
                                        Grand Total
                                    </th>

                                    <th className="number">
                                        Paid
                                    </th>

                                    <th className="number">
                                        Balance
                                    </th>

                                    <th className="center">
                                        Status
                                    </th>

                                    <th className="center">
                                        Action
                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                                {
                                    purchases?.data?.length > 0
                                        ? (

                                            purchases.data.map(
                                                (
                                                    purchase,
                                                    index
                                                ) => (

                                                    <tr
                                                        key={
                                                            purchase.id
                                                        }
                                                        className={
                                                            `rr-purchase-row ${index % 2 === 0
                                                                ? 'row-even'
                                                                : 'row-odd'
                                                            }`
                                                        }
                                                        onClick={
                                                            () =>
                                                                openPurchase(
                                                                    purchase
                                                                )
                                                        }
                                                    >

                                                        {/* Purchase */}

                                                        <td>

                                                            <PurchaseIdentity
                                                                purchase={
                                                                    purchase
                                                                }
                                                            />

                                                        </td>


                                                        {/* Supplier */}

                                                        <td>

                                                            <div className="rr-purchase-supplier">

                                                                <strong>

                                                                    {
                                                                        purchase.supplier?.supplier_name
                                                                        ?? '—'
                                                                    }

                                                                </strong>

                                                            </div>

                                                        </td>


                                                        {/* Invoice */}

                                                        <td>

                                                            <div className="rr-purchase-invoice">

                                                                <strong>

                                                                    {
                                                                        purchase.invoice_no
                                                                        || '—'
                                                                    }

                                                                </strong>


                                                                {
                                                                    purchase.invoice_date && (

                                                                        <span>

                                                                            {
                                                                                formatDate(
                                                                                    purchase.invoice_date
                                                                                )
                                                                            }

                                                                        </span>

                                                                    )
                                                                }

                                                            </div>

                                                        </td>


                                                        {/* Tax Type */}

                                                        <td>

                                                            <TaxTypeBadge
                                                                type={
                                                                    purchase.tax_type
                                                                }
                                                            />

                                                        </td>


                                                        {/* Subtotal */}

                                                        <MoneyCell
                                                            value={
                                                                purchase.subtotal
                                                            }
                                                        />


                                                        {/* Discount */}

                                                        <MoneyCell
                                                            value={
                                                                purchase.discount
                                                            }
                                                        />


                                                        {/* Tax */}

                                                        <MoneyCell
                                                            value={
                                                                purchase.tax_amount
                                                            }
                                                        />


                                                        {/* Grand Total */}

                                                        <td className="number rr-purchase-total">

                                                            {
                                                                formatMoney(
                                                                    purchase.grand_total
                                                                )
                                                            }

                                                        </td>


                                                        {/* Paid */}

                                                        <MoneyCell
                                                            value={
                                                                purchase.paid_amount
                                                            }
                                                        />


                                                        {/* Balance */}

                                                        <td className="number">

                                                            <BalanceBadge
                                                                value={
                                                                    purchase.balance_amount
                                                                }
                                                            />

                                                        </td>


                                                        {/* Status */}

                                                        <td className="center">

                                                            <StatusBadge
                                                                active={
                                                                    purchase.status
                                                                }
                                                            />

                                                        </td>


                                                        {/* Actions */}

                                                        <td
                                                            className="center"
                                                            onClick={
                                                                event =>
                                                                    event.stopPropagation()
                                                            }
                                                        >

                                                            <div className="rr-purchase-actions">

                                                                {/* View */}

                                                                <Link
                                                                    href={
                                                                        `/purchases/${purchase.id}`
                                                                    }
                                                                    className="rr-purchase-action-btn view"
                                                                    title="View Purchase"
                                                                >

                                                                    <i className="bi bi-eye"></i>

                                                                </Link>


                                                                {/* Edit */}

                                                                <Link
                                                                    href={
                                                                        `/purchases/${purchase.id}/edit`
                                                                    }
                                                                    className="rr-purchase-action-btn edit"
                                                                    title="Edit Purchase"
                                                                >

                                                                    <i className="bi bi-pencil-square"></i>

                                                                </Link>


                                                                {/* Delete */}

                                                                <button
                                                                    type="button"
                                                                    className="rr-purchase-action-btn delete"
                                                                    title="Delete Purchase"
                                                                    onClick={
                                                                        event =>
                                                                            openDeleteConfirm(
                                                                                event,
                                                                                purchase
                                                                            )
                                                                    }
                                                                >

                                                                    <i className="bi bi-trash3"></i>

                                                                </button>

                                                            </div>

                                                        </td>

                                                    </tr>

                                                )
                                            )

                                        )
                                        : (

                                            <tr>

                                                <td colSpan="12">

                                                    <div className="rr-purchase-empty">

                                                        <div>

                                                            <i className="bi bi-cart-x"></i>

                                                        </div>


                                                        <strong>
                                                            No purchases found
                                                        </strong>


                                                        <span>

                                                            {
                                                                hasFilters
                                                                    ? 'Change or clear the selected filters.'
                                                                    : 'Create the first purchase for this branch.'
                                                            }

                                                        </span>


                                                        {
                                                            !hasFilters && (

                                                                <Link
                                                                    href="/purchases/create"
                                                                    className="rr-purchase-primary-btn"
                                                                >

                                                                    <i className="bi bi-plus-lg"></i>

                                                                    New Purchase

                                                                </Link>

                                                            )
                                                        }

                                                    </div>

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
                        purchases?.links?.length > 0 && (

                            <div className="rr-purchase-pagination">

                                <div>

                                    Page

                                    {' '}

                                    <strong>
                                        {
                                            purchases.current_page ?? 1
                                        }
                                    </strong>

                                    {' '}

                                    of

                                    {' '}

                                    <strong>
                                        {
                                            purchases.last_page ?? 1
                                        }
                                    </strong>

                                </div>


                                <div className="rr-purchase-pagination-buttons">

                                    {
                                        purchases.links.map(
                                            (
                                                link,
                                                index
                                            ) => (

                                                <button
                                                    key={
                                                        `${link.label}-${index}`
                                                    }
                                                    type="button"
                                                    disabled={
                                                        !link.url
                                                    }
                                                    className={
                                                        `rr-purchase-page-btn ${link.active
                                                            ? 'active'
                                                            : ''
                                                        }`
                                                    }
                                                    onClick={
                                                        () => {

                                                            if (
                                                                !link.url
                                                            ) {
                                                                return;
                                                            }


                                                            router.visit(
                                                                link.url,
                                                                {
                                                                    preserveState:
                                                                        true,

                                                                    preserveScroll:
                                                                        true,
                                                                }
                                                            );
                                                        }
                                                    }
                                                    dangerouslySetInnerHTML={{
                                                        __html:
                                                            link.label,
                                                    }}
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
                    COMMON CONFIRM MODAL
                   ===================================================== */}

                <ConfirmModal
                    show={
                        confirmModal.show
                    }

                    title="Delete Purchase"

                    message={
                        `Are you sure you want to delete purchase "${confirmModal.purchase?.purchase_no ?? ''}"? Stock received through this purchase will be reversed. Purchases with purchase returns cannot be deleted.`
                    }

                    confirmText="Delete Purchase"

                    cancelText="Cancel"

                    icon="bi-trash3"

                    variant="danger"

                    processing={
                        confirmModal.processing
                    }

                    onConfirm={
                        confirmDelete
                    }

                    onCancel={
                        closeDeleteConfirm
                    }
                />

            </div>
        </>
    );
}


/*
|--------------------------------------------------------------------------
| Summary Card
|--------------------------------------------------------------------------
*/

function SummaryCard({
    title,
    value,
    icon,
    tone = 'primary',
}) {

    return (

        <div
            className={
                `rr-purchase-stat ${tone}`
            }
        >

            <div className="rr-purchase-stat-icon">

                <i
                    className={
                        `bi ${icon}`
                    }
                ></i>

            </div>


            <div>

                <span>
                    {title}
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
| Filter Box
|--------------------------------------------------------------------------
*/

function FilterBox({
    label,
    value,
    onChange,
    children,
}) {

    return (

        <div className="rr-purchase-filter">

            <label>
                {label}
            </label>


            <select
                value={
                    value
                }
                onChange={
                    event =>
                        onChange(
                            event.target.value
                        )
                }
            >

                {children}

            </select>

        </div>
    );
}


/*
|--------------------------------------------------------------------------
| Date Filter
|--------------------------------------------------------------------------
*/

function DateFilter({
    label,
    value,
    onChange,
}) {

    const inputRef =
        useRef(
            null
        );


    const openDatePicker =
        () => {

            const input =
                inputRef.current;


            if (
                !input
            ) {
                return;
            }


            if (
                typeof input.showPicker ===
                'function'
            ) {

                try {

                    input.showPicker();

                    return;

                } catch {
                    // Fall through to focus.
                }
            }


            input.focus();
        };


    return (

        <div className="rr-purchase-filter rr-purchase-date-filter">

            <label>
                {label}
            </label>


            <div
                className={
                    `rr-purchase-date-picker ${value
                        ? 'has-value'
                        : ''
                    }`
                }
            >

                <button
                    type="button"
                    className="rr-purchase-date-leading-icon"
                    tabIndex="-1"
                    aria-label={`Open ${label} date picker`}
                    onClick={
                        event => {

                            event.stopPropagation();

                            openDatePicker();
                        }
                    }
                >
                    <i className="bi bi-calendar3"></i>
                </button>


                <input
                    ref={
                        inputRef
                    }
                    type="date"
                    value={
                        value
                    }
                    onClick={
                        event => {

                            event.stopPropagation();

                            openDatePicker();
                        }
                    }
                    onChange={
                        event =>
                            onChange(
                                event.target.value
                            )
                    }
                />


                {
                    value
                        ? (
                            <button
                                type="button"
                                className="rr-purchase-date-clear"
                                title={`Clear ${label} date`}
                                aria-label={`Clear ${label} date`}
                                onClick={
                                    event => {

                                        event.stopPropagation();

                                        onChange('');
                                    }
                                }
                            >
                                <i className="bi bi-x-lg"></i>
                            </button>
                        )
                        : (
                            <button
                                type="button"
                                className="rr-purchase-date-icon"
                                tabIndex="-1"
                                aria-label={`Select ${label} date`}
                                onClick={
                                    event => {

                                        event.stopPropagation();

                                        openDatePicker();
                                    }
                                }
                            >
                                <i className="bi bi-chevron-down"></i>
                            </button>
                        )
                }

            </div>

        </div>
    );
}


/*
|--------------------------------------------------------------------------
| Purchase Identity
|--------------------------------------------------------------------------
*/

function PurchaseIdentity({
    purchase,
}) {

    return (

        <div className="rr-purchase-identity">

            <div className="rr-purchase-avatar">

                <i className="bi bi-receipt"></i>

            </div>


            <div>

                <strong>

                    {
                        purchase.purchase_no
                    }

                </strong>


                <span>

                    {
                        formatDate(
                            purchase.purchase_date
                        )
                    }

                </span>

            </div>

        </div>
    );
}


/*
|--------------------------------------------------------------------------
| Tax Type
|--------------------------------------------------------------------------
*/

function TaxTypeBadge({
    type,
}) {

    const gst =
        type === 'gst';


    return (

        <span
            className={
                gst
                    ? 'rr-purchase-tax-badge gst'
                    : 'rr-purchase-tax-badge non-gst'
            }
        >

            <i
                className={
                    gst
                        ? 'bi bi-percent'
                        : 'bi bi-slash-circle'
                }
            ></i>


            {
                gst
                    ? 'GST'
                    : 'Without GST'
            }

        </span>
    );
}


/*
|--------------------------------------------------------------------------
| Money Cell
|--------------------------------------------------------------------------
*/

function MoneyCell({
    value,
}) {

    return (

        <td className="number rr-purchase-money">

            {
                formatMoney(
                    value
                )
            }

        </td>
    );
}


/*
|--------------------------------------------------------------------------
| Balance
|--------------------------------------------------------------------------
*/

function BalanceBadge({
    value,
}) {

    const amount =
        Number(
            value ?? 0
        );


    return (

        <span
            className={
                amount > 0
                    ? 'rr-purchase-balance pending'
                    : 'rr-purchase-balance paid'
            }
        >

            {
                formatMoney(
                    amount
                )
            }

        </span>
    );
}


/*
|--------------------------------------------------------------------------
| Status
|--------------------------------------------------------------------------
*/

function StatusBadge({
    active,
}) {

    return (

        <span
            className={
                `rr-purchase-status ${active
                    ? 'active'
                    : 'inactive'
                }`
            }
        >

            <span></span>


            {
                active
                    ? 'Active'
                    : 'Inactive'
            }

        </span>
    );
}


/*
|--------------------------------------------------------------------------
| Money
|--------------------------------------------------------------------------
*/

function formatMoney(
    value
) {

    const number =
        Number(
            value ?? 0
        );


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
        Number.isFinite(
            number
        )
            ? number
            : 0
    );
}


/*
|--------------------------------------------------------------------------
| Date
|--------------------------------------------------------------------------
*/

function formatDate(
    value
) {

    if (
        !value
    ) {

        return '—';
    }


    const date =
        new Date(
            `${value}T00:00:00`
        );


    if (
        Number.isNaN(
            date.getTime()
        )
    ) {

        return '—';
    }


    return new Intl.DateTimeFormat(
        'en-IN',
        {
            day:
                '2-digit',

            month:
                'short',

            year:
                'numeric',
        }
    ).format(
        date
    );
}


/*
|--------------------------------------------------------------------------
| Layout
|--------------------------------------------------------------------------
*/

Index.layout =
    page => (

        <BusinessLayout>
            {page}
        </BusinessLayout>

    );