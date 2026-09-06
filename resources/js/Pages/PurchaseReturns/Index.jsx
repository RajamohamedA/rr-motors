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

import '../../../css/business/purchase-returns.css';


export default function Index({
    returns,
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


    const firstRender =
        useRef(
            true
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

        purchaseReturn:
            null,

        processing:
            false,
    });


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
                            '/purchase-returns',
                            {
                                search,

                                supplier_id:
                                    supplierId,

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
            dateFrom,
            dateTo,
        ]
    );


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
            dateFrom
            ||
            dateTo
        );


    /*
    |--------------------------------------------------------------------------
    | Reset
    |--------------------------------------------------------------------------
    */

    const resetFilters =
        () => {

            setSearch('');

            setSupplierId('');

            setDateFrom('');

            setDateTo('');


            router.get(
                '/purchase-returns',
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
    | Open Row
    |--------------------------------------------------------------------------
    */

    const openReturn =
        purchaseReturn => {

            router.visit(
                `/purchase-returns/${purchaseReturn.id}`
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    */

    const openDeleteConfirm =
        (
            event,
            purchaseReturn
        ) => {

            event.stopPropagation();


            setConfirmModal({
                show:
                    true,

                purchaseReturn,

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

                purchaseReturn:
                    null,

                processing:
                    false,
            });
        };


    const confirmDelete =
        () => {

            const purchaseReturn =
                confirmModal.purchaseReturn;


            if (
                !purchaseReturn
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
                `/purchase-returns/${purchaseReturn.id}`,
                {
                    preserveScroll:
                        true,

                    onFinish:
                        () => {

                            setConfirmModal({
                                show:
                                    false,

                                purchaseReturn:
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
    | Render
    |--------------------------------------------------------------------------
    */

    return (
        <>
            <Head
                title="Purchase Returns"
            />


            <div className="rr-pr-page">

                {/* =====================================================
                    MASTER NAVIGATION
                   ===================================================== */}

                <MasterNavigation
                    active="purchase-returns"
                    title="Purchase Returns"
                    subtitle="Supplier return register and stock reversal"
                />


                {/* =====================================================
                    HEADER
                   ===================================================== */}

                <section className="rr-pr-header">

                    <div className="rr-pr-header-main">

                        <Link
                            href="/business/dashboard"
                            className="rr-pr-back-btn"
                            title="Back to Dashboard"
                        >
                            <i className="bi bi-arrow-left"></i>
                        </Link>


                        <div className="rr-pr-header-icon">

                            <i className="bi bi-arrow-return-left"></i>

                        </div>


                        <div className="rr-pr-header-content">

                            <span className="rr-pr-eyebrow">
                                Purchase Return Management
                            </span>


                            <h1>
                                Purchase Return Register
                            </h1>


                            <div className="rr-pr-header-meta">

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


                    <div className="rr-pr-header-actions">

                        <Link
                            href="/purchase-returns/create"
                            className="rr-pr-primary-btn"
                        >
                            <i className="bi bi-plus-lg"></i>

                            New Purchase Return
                        </Link>

                    </div>

                </section>


                {/* =====================================================
                    SUMMARY
                   ===================================================== */}

                <section className="rr-pr-summary">

                    <SummaryCard
                        title="Total Returns"
                        value={
                            summary.total ?? 0
                        }
                        icon="bi-arrow-return-left"
                        tone="primary"
                    />


                    <SummaryCard
                        title="Return Value"
                        value={
                            formatMoney(
                                summary.return_value
                            )
                        }
                        icon="bi-currency-rupee"
                        tone="warning"
                    />


                    <SummaryCard
                        title="GST Value"
                        value={
                            formatMoney(
                                summary.gst_value
                            )
                        }
                        icon="bi-percent"
                        tone="success"
                    />

                </section>


                {/* =====================================================
                    FILTER WINDOW
                   ===================================================== */}

                <section className="rr-pr-filter-window">

                    <div className="rr-pr-filter-header">

                        <div>

                            <i className="bi bi-funnel"></i>

                            <strong>
                                Return Search
                            </strong>

                        </div>


                        <span>

                            {
                                returns?.total ?? 0
                            }

                            {' '}

                            record(s)

                        </span>

                    </div>


                    <div className="rr-pr-filter-body">

                        {/* Search */}

                        <div className="rr-pr-search-box">

                            <i className="bi bi-search"></i>


                            <input
                                type="search"
                                value={
                                    search
                                }
                                placeholder="Return no, purchase no, supplier, invoice no..."
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


                        {/* From */}

                        <DateFilter
                            label="From Date"
                            value={
                                dateFrom
                            }
                            onChange={
                                setDateFrom
                            }
                        />


                        {/* To */}

                        <DateFilter
                            label="To Date"
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
                            className="rr-pr-clear-btn"
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
                    GRID
                   ===================================================== */}

                <section className="rr-pr-grid-window">

                    <div className="rr-pr-grid-toolbar">

                        <div className="rr-pr-grid-title">

                            <i className="bi bi-table"></i>


                            <div>

                                <strong>
                                    Return Register
                                </strong>


                                <span>
                                    Click a row to view full purchase return details
                                </span>

                            </div>

                        </div>


                        <div className="rr-pr-grid-count">

                            Showing

                            {' '}

                            <strong>
                                {
                                    returns?.from ?? 0
                                }
                            </strong>

                            {' '}

                            to

                            {' '}

                            <strong>
                                {
                                    returns?.to ?? 0
                                }
                            </strong>

                            {' '}

                            of

                            {' '}

                            <strong>
                                {
                                    returns?.total ?? 0
                                }
                            </strong>

                        </div>

                    </div>


                    <div className="rr-pr-table-wrap">

                        <table className="rr-pr-table">

                            <thead>

                                <tr>

                                    <th>
                                        Return
                                    </th>

                                    <th>
                                        Purchase
                                    </th>

                                    <th>
                                        Supplier
                                    </th>

                                    <th>
                                        Date
                                    </th>

                                    <th className="number">
                                        Subtotal
                                    </th>

                                    <th className="number">
                                        GST
                                    </th>

                                    <th className="number">
                                        Return Total
                                    </th>

                                    <th className="center">
                                        Action
                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                                {
                                    returns?.data?.length > 0
                                        ? (

                                            returns.data.map(
                                                (
                                                    purchaseReturn,
                                                    index
                                                ) => (

                                                    <tr
                                                        key={
                                                            purchaseReturn.id
                                                        }
                                                        className={
                                                            `rr-pr-row ${index % 2 === 0
                                                                ? 'row-even'
                                                                : 'row-odd'
                                                            }`
                                                        }
                                                        onClick={
                                                            () =>
                                                                openReturn(
                                                                    purchaseReturn
                                                                )
                                                        }
                                                    >

                                                        {/* Return */}

                                                        <td>

                                                            <ReturnIdentity
                                                                purchaseReturn={
                                                                    purchaseReturn
                                                                }
                                                            />

                                                        </td>


                                                        {/* Purchase */}

                                                        <td>

                                                            <div className="rr-pr-purchase-ref">

                                                                <strong>
                                                                    {
                                                                        purchaseReturn.purchase?.purchase_no
                                                                        ?? '—'
                                                                    }
                                                                </strong>


                                                                <span>

                                                                    {
                                                                        purchaseReturn.purchase?.tax_type === 'gst'
                                                                            ? 'GST Purchase'
                                                                            : 'Without GST'
                                                                    }

                                                                </span>

                                                            </div>

                                                        </td>


                                                        {/* Supplier */}

                                                        <td>

                                                            <div className="rr-pr-supplier">

                                                                <i className="bi bi-truck"></i>


                                                                <strong>
                                                                    {
                                                                        purchaseReturn.supplier?.supplier_name
                                                                        ?? '—'
                                                                    }
                                                                </strong>

                                                            </div>

                                                        </td>


                                                        {/* Date */}

                                                        <td>

                                                            <div className="rr-pr-date-cell">

                                                                <i className="bi bi-calendar3"></i>

                                                                {
                                                                    formatDate(
                                                                        purchaseReturn.return_date
                                                                    )
                                                                }

                                                            </div>

                                                        </td>


                                                        {/* Subtotal */}

                                                        <MoneyCell
                                                            value={
                                                                purchaseReturn.sub_total
                                                            }
                                                        />


                                                        {/* GST */}

                                                        <MoneyCell
                                                            value={
                                                                purchaseReturn.gst_total
                                                            }
                                                        />


                                                        {/* Total */}

                                                        <td className="number rr-pr-total">

                                                            {
                                                                formatMoney(
                                                                    purchaseReturn.total_amount
                                                                )
                                                            }

                                                        </td>


                                                        {/* Action */}

                                                        <td
                                                            className="center"
                                                            onClick={
                                                                event =>
                                                                    event.stopPropagation()
                                                            }
                                                        >

                                                            <div className="rr-pr-actions">

                                                                <Link
                                                                    href={
                                                                        `/purchase-returns/${purchaseReturn.id}`
                                                                    }
                                                                    className="rr-pr-action-btn view"
                                                                    title="View Purchase Return"
                                                                >
                                                                    <i className="bi bi-eye"></i>
                                                                </Link>


                                                                <Link
                                                                    href={
                                                                        `/purchase-returns/${purchaseReturn.id}/edit`
                                                                    }
                                                                    className="rr-pr-action-btn edit"
                                                                    title="Edit Purchase Return"
                                                                >
                                                                    <i className="bi bi-pencil-square"></i>
                                                                </Link>


                                                                <a
                                                                    href={
                                                                        `/purchase-returns/${purchaseReturn.id}/print`
                                                                    }
                                                                    target="_blank"
                                                                    rel="noreferrer"
                                                                    className="rr-pr-action-btn print"
                                                                    title="Print Purchase Return"
                                                                >
                                                                    <i className="bi bi-printer"></i>
                                                                </a>


                                                                <button
                                                                    type="button"
                                                                    className="rr-pr-action-btn delete"
                                                                    title="Delete Purchase Return"
                                                                    onClick={
                                                                        event =>
                                                                            openDeleteConfirm(
                                                                                event,
                                                                                purchaseReturn
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

                                                <td
                                                    colSpan="8"
                                                >

                                                    <div className="rr-pr-empty">

                                                        <div>
                                                            <i className="bi bi-arrow-return-left"></i>
                                                        </div>


                                                        <strong>
                                                            No purchase returns found
                                                        </strong>


                                                        <span>
                                                            {
                                                                hasFilters
                                                                    ? 'Change or clear the selected filters.'
                                                                    : 'Create the first purchase return for this branch.'
                                                            }
                                                        </span>


                                                        {
                                                            !hasFilters && (

                                                                <Link
                                                                    href="/purchase-returns/create"
                                                                    className="rr-pr-primary-btn"
                                                                >
                                                                    <i className="bi bi-plus-lg"></i>

                                                                    New Purchase Return
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
                        returns?.links?.length > 0 && (

                            <div className="rr-pr-pagination">

                                <div>

                                    Page

                                    {' '}

                                    <strong>
                                        {
                                            returns.current_page ?? 1
                                        }
                                    </strong>

                                    {' '}

                                    of

                                    {' '}

                                    <strong>
                                        {
                                            returns.last_page ?? 1
                                        }
                                    </strong>

                                </div>


                                <div className="rr-pr-pagination-buttons">

                                    {
                                        returns.links.map(
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
                                                        `rr-pr-page-btn ${link.active
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
                    CONFIRM DELETE
                   ===================================================== */}

                <ConfirmModal
                    show={
                        confirmModal.show
                    }
                    title="Delete Purchase Return"
                    message={
                        `Are you sure you want to delete return "${confirmModal.purchaseReturn?.return_no ?? ''}"? The returned stock will be restored to the original Shop/Godown location.`
                    }
                    confirmText="Delete Return"
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
                `rr-pr-stat ${tone}`
            }
        >

            <div className="rr-pr-stat-icon">

                <i className={`bi ${icon}`}></i>

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
| Filter
|--------------------------------------------------------------------------
*/

function FilterBox({
    label,
    value,
    onChange,
    children,
}) {

    return (

        <div className="rr-pr-filter">

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
                typeof input.showPicker === 'function'
            ) {

                try {

                    input.showPicker();

                    return;

                } catch {
                    // fallback
                }
            }


            input.focus();
        };


    return (

        <div className="rr-pr-filter">

            <label>
                {label}
            </label>


            <div
                className={
                    `rr-pr-date-picker ${value
                        ? 'has-value'
                        : ''
                    }`
                }
            >

                <button
                    type="button"
                    className="rr-pr-date-leading-icon"
                    onClick={
                        openDatePicker
                    }
                    tabIndex="-1"
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
                                className="rr-pr-date-clear"
                                title="Clear date"
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
                                className="rr-pr-date-icon"
                                tabIndex="-1"
                                onClick={
                                    openDatePicker
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
| Return Identity
|--------------------------------------------------------------------------
*/

function ReturnIdentity({
    purchaseReturn,
}) {

    return (

        <div className="rr-pr-identity">

            <div className="rr-pr-avatar">

                <i className="bi bi-arrow-return-left"></i>

            </div>


            <div>

                <strong>
                    {
                        purchaseReturn.return_no
                    }
                </strong>


                <span>
                    Supplier Return
                </span>

            </div>

        </div>
    );
}


/*
|--------------------------------------------------------------------------
| Money
|--------------------------------------------------------------------------
*/

function MoneyCell({
    value,
}) {

    return (

        <td className="number rr-pr-money">

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
| Money Format
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


    const raw =
        String(
            value
        );


    const date =
        raw.includes('T')
            ? new Date(
                raw
            )
            : new Date(
                `${raw}T00:00:00`
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