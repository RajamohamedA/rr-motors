import React from 'react';

import {
    Head,
    Link,
    usePage,
} from '@inertiajs/react';

import BusinessLayout from '@/Layouts/BusinessLayout';

import MasterNavigation
    from '../../Components/Business/MasterNavigation';

import '../../../css/business/purchase-returns.css';


export default function Show({
    purchaseReturn,
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
    | Purchase
    |--------------------------------------------------------------------------
    */

    const purchase =
        purchaseReturn?.purchase
        ?? null;


    /*
    |--------------------------------------------------------------------------
    | Items
    |--------------------------------------------------------------------------
    */

    const items =
        purchaseReturn?.items
        ?? [];


    /*
    |--------------------------------------------------------------------------
    | Total Quantity
    |--------------------------------------------------------------------------
    */

    const totalQty =
        items.reduce(
            (
                total,
                item
            ) =>
                total
                +
                numberValue(
                    item.quantity
                ),
            0
        );


    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */

    return (
        <>
            <Head
                title={
                    `Purchase Return ${purchaseReturn?.return_no ?? ''}`
                }
            />


            <div className="rr-pr-page">

                {/* =====================================================
                    MASTER NAVIGATION
                   ===================================================== */}

                <MasterNavigation
                    active="purchase-returns"
                    title="Purchase Returns"
                    subtitle="Supplier return details and stock reversal"
                />


                {/* =====================================================
                    HEADER
                   ===================================================== */}

                <section className="rr-pr-header rr-pr-show-header">

                    <div className="rr-pr-header-main">

                        <Link
                            href="/purchase-returns"
                            className="rr-pr-back-btn"
                            title="Back to Purchase Returns"
                        >
                            <i className="bi bi-arrow-left"></i>
                        </Link>


                        <div className="rr-pr-header-icon">

                            <i className="bi bi-arrow-return-left"></i>

                        </div>


                        <div className="rr-pr-header-content">

                            <span className="rr-pr-eyebrow">
                                Purchase Return Details
                            </span>


                            <h1>
                                {
                                    purchaseReturn?.return_no
                                    ?? 'Purchase Return'
                                }
                            </h1>


                            <div className="rr-pr-header-meta">

                                <span>

                                    <i className="bi bi-calendar3"></i>

                                    {
                                        formatDate(
                                            purchaseReturn?.return_date
                                        )
                                    }

                                </span>


                                <span>

                                    <i className="bi bi-buildings"></i>

                                    {
                                        business?.company_name
                                        ??
                                        purchase?.company?.company_name
                                        ??
                                        'Company'
                                    }

                                </span>


                                <span>

                                    <i className="bi bi-shop"></i>

                                    {
                                        business?.store_name
                                        ??
                                        purchase?.store?.store_name
                                        ??
                                        'Branch'
                                    }

                                </span>


                                <TaxBadge
                                    type={
                                        purchase?.tax_type
                                    }
                                />

                            </div>

                        </div>

                    </div>


                    <div className="rr-pr-header-actions">

                        {/* Edit */}

                        <Link
                            href={
                                `/purchase-returns/${purchaseReturn.id}/edit`
                            }
                            className="rr-pr-secondary-btn rr-pr-show-action-edit"
                        >
                            <i className="bi bi-pencil-square"></i>

                            Edit
                        </Link>


                        {/* Print */}

                        <a
                            href={
                                `/purchase-returns/${purchaseReturn.id}/print`
                            }
                            target="_blank"
                            rel="noreferrer"
                            className="rr-pr-secondary-btn rr-pr-show-action-print"
                        >
                            <i className="bi bi-printer"></i>

                            Print
                        </a>


                        {/* PDF */}

                        <a
                            href={
                                `/purchase-returns/${purchaseReturn.id}/pdf`
                            }
                            className="rr-pr-secondary-btn rr-pr-show-action-pdf"
                        >
                            <i className="bi bi-file-earmark-pdf"></i>

                            PDF
                        </a>


                        <Link
                            href="/purchase-returns"
                            className="rr-pr-primary-btn"
                        >
                            <i className="bi bi-list-ul"></i>

                            Return List
                        </Link>

                    </div>

                </section>


                {/* =====================================================
                    SUMMARY
                   ===================================================== */}

                <section className="rr-pr-summary rr-pr-show-summary">

                    <SummaryCard
                        title="Return Total"
                        value={
                            formatMoney(
                                purchaseReturn?.total_amount
                            )
                        }
                        icon="bi-currency-rupee"
                        tone="primary"
                    />


                    <SummaryCard
                        title="Subtotal"
                        value={
                            formatMoney(
                                purchaseReturn?.sub_total
                            )
                        }
                        icon="bi-calculator"
                        tone="neutral"
                    />


                    <SummaryCard
                        title="GST"
                        value={
                            formatMoney(
                                purchaseReturn?.gst_total
                            )
                        }
                        icon="bi-percent"
                        tone="success"
                    />


                    <SummaryCard
                        title="Returned Qty"
                        value={
                            formatQty(
                                totalQty
                            )
                        }
                        icon="bi-box-arrow-left"
                        tone="warning"
                    />

                </section>


                {/* =====================================================
                    MAIN LAYOUT
                   ===================================================== */}

                <div className="rr-pr-show-layout">

                    {/* =================================================
                        MAIN CONTENT
                       ================================================= */}

                    <div className="rr-pr-show-main">

                        {/* =============================================
                            RETURN INFORMATION
                           ============================================= */}

                        <ShowWindow
                            title="Return Information"
                            icon="bi-file-earmark-text"
                        >

                            <div className="rr-pr-show-detail-grid">

                                <DetailItem
                                    label="Return Number"
                                    value={
                                        purchaseReturn?.return_no
                                    }
                                />


                                <DetailItem
                                    label="Return Date"
                                    value={
                                        formatDate(
                                            purchaseReturn?.return_date
                                        )
                                    }
                                />


                                <DetailItem
                                    label="Purchase Number"
                                    value={
                                        purchase?.purchase_no
                                    }
                                />


                                <DetailItem
                                    label="Purchase Date"
                                    value={
                                        formatDate(
                                            purchase?.purchase_date
                                        )
                                    }
                                />


                                <DetailItem
                                    label="Supplier"
                                    value={
                                        purchaseReturn?.supplier?.supplier_name
                                        ?? '—'
                                    }
                                />


                                <DetailItem
                                    label="Supplier Invoice"
                                    value={
                                        purchase?.invoice_no
                                        || '—'
                                    }
                                />


                                <DetailItem
                                    label="Invoice Date"
                                    value={
                                        purchase?.invoice_date
                                            ? formatDate(
                                                purchase.invoice_date
                                            )
                                            : '—'
                                    }
                                />


                                <DetailItem
                                    label="Purchase Type"
                                >

                                    <TaxBadge
                                        type={
                                            purchase?.tax_type
                                        }
                                    />

                                </DetailItem>

                            </div>

                        </ShowWindow>


                        {/* =============================================
                            RETURN ITEMS
                           ============================================= */}

                        <ShowWindow
                            title="Returned Items"
                            icon="bi-table"
                            noPadding
                        >

                            <div className="rr-pr-show-table-wrap">

                                <table className="rr-pr-show-table">

                                    <thead>

                                        <tr>

                                            <th className="serial">
                                                #
                                            </th>

                                            <th>
                                                Product
                                            </th>

                                            <th>
                                                Location
                                            </th>

                                            <th className="number">
                                                Return Qty
                                            </th>

                                            <th className="number">
                                                Rate
                                            </th>

                                            {
                                                purchase?.tax_type === 'gst' && (
                                                    <>
                                                        <th className="number">
                                                            GST %
                                                        </th>

                                                        <th className="number">
                                                            GST
                                                        </th>
                                                    </>
                                                )
                                            }

                                            <th className="number">
                                                Total
                                            </th>

                                        </tr>

                                    </thead>


                                    <tbody>

                                        {
                                            items.length > 0
                                                ? (

                                                    items.map(
                                                        (
                                                            item,
                                                            index
                                                        ) => (

                                                            <tr
                                                                key={
                                                                    item.id
                                                                    ?? index
                                                                }
                                                            >

                                                                <td className="serial">
                                                                    {
                                                                        index + 1
                                                                    }
                                                                </td>


                                                                <td>

                                                                    <div className="rr-pr-product">

                                                                        <div className="rr-pr-product-icon">

                                                                            <i className="bi bi-box-seam"></i>

                                                                        </div>


                                                                        <div>

                                                                            <strong>
                                                                                {
                                                                                    item.product?.product_name
                                                                                    ?? 'Product'
                                                                                }
                                                                            </strong>


                                                                            <span>

                                                                                {
                                                                                    item.product?.product_code
                                                                                    ?? ''
                                                                                }

                                                                                {
                                                                                    item.product?.part_number
                                                                                        ? ` • ${item.product.part_number}`
                                                                                        : ''
                                                                                }

                                                                                {
                                                                                    item.product?.unit?.short_name
                                                                                        ? ` • ${item.product.unit.short_name}`
                                                                                        : item.product?.unit?.unit_name
                                                                                            ? ` • ${item.product.unit.unit_name}`
                                                                                            : ''
                                                                                }

                                                                            </span>

                                                                        </div>

                                                                    </div>

                                                                </td>


                                                                <td>

                                                                    <LocationBadge
                                                                        godown={
                                                                            item.godown
                                                                        }
                                                                    />

                                                                </td>


                                                                <td className="number rr-pr-returned-qty">

                                                                    {
                                                                        formatQty(
                                                                            item.quantity
                                                                        )
                                                                    }

                                                                </td>


                                                                <td className="number rr-pr-rate">

                                                                    {
                                                                        formatMoney(
                                                                            item.purchase_price
                                                                        )
                                                                    }

                                                                </td>


                                                                {
                                                                    purchase?.tax_type === 'gst' && (
                                                                        <>

                                                                            <td className="number">

                                                                                {
                                                                                    numberValue(
                                                                                        item.gst_percent
                                                                                    ).toFixed(2)
                                                                                }

                                                                                %

                                                                            </td>


                                                                            <td className="number rr-pr-tax-value">

                                                                                {
                                                                                    formatMoney(
                                                                                        item.gst_amount
                                                                                    )
                                                                                }

                                                                            </td>

                                                                        </>
                                                                    )
                                                                }


                                                                <td className="number rr-pr-line-total">

                                                                    {
                                                                        formatMoney(
                                                                            item.line_total
                                                                        )
                                                                    }

                                                                </td>

                                                            </tr>

                                                        )
                                                    )

                                                )
                                                : (

                                                    <tr>

                                                        <td
                                                            colSpan={
                                                                purchase?.tax_type === 'gst'
                                                                    ? 8
                                                                    : 6
                                                            }
                                                        >

                                                            <div className="rr-pr-show-empty">
                                                                No returned items found.
                                                            </div>

                                                        </td>

                                                    </tr>

                                                )
                                        }

                                    </tbody>

                                </table>

                            </div>

                        </ShowWindow>


                        {/* =============================================
                            REMARKS
                           ============================================= */}

                        <ShowWindow
                            title="Remarks"
                            icon="bi-card-text"
                        >

                            <div className="rr-pr-show-remarks">

                                {
                                    purchaseReturn?.remarks
                                    ||
                                    'No remarks available.'
                                }

                            </div>

                        </ShowWindow>

                    </div>


                    {/* =================================================
                        SIDE PANEL
                       ================================================= */}

                    <aside className="rr-pr-show-side">

                        {/* =============================================
                            SUPPLIER
                           ============================================= */}

                        <div className="rr-pr-show-side-card">

                            <div className="rr-pr-show-side-title">

                                <i className="bi bi-truck"></i>

                                Supplier

                            </div>


                            <div className="rr-pr-show-supplier-card">

                                <div className="rr-pr-show-supplier-icon">

                                    <i className="bi bi-building"></i>

                                </div>


                                <div>

                                    <strong>
                                        {
                                            purchaseReturn?.supplier?.supplier_name
                                            ?? 'Supplier'
                                        }
                                    </strong>


                                    <span>
                                        Return supplier
                                    </span>

                                </div>

                            </div>


                            <InfoRow
                                label="Purchase No."
                                value={
                                    purchase?.purchase_no
                                    ?? '—'
                                }
                            />


                            <InfoRow
                                label="Invoice No."
                                value={
                                    purchase?.invoice_no
                                    || '—'
                                }
                            />

                        </div>


                        {/* =============================================
                            STOCK REVERSAL
                           ============================================= */}

                        <div className="rr-pr-show-side-card">

                            <div className="rr-pr-show-side-title">

                                <i className="bi bi-box-arrow-left"></i>

                                Stock Reversal

                            </div>


                            <InfoRow
                                label="Returned Qty"
                                value={
                                    formatQty(
                                        totalQty
                                    )
                                }
                                strong
                            />


                            <div className="rr-pr-stock-note">

                                <i className="bi bi-info-circle"></i>

                                <span>
                                    Stock was reduced from the original Shop/Godown location.
                                </span>

                            </div>

                        </div>


                        {/* =============================================
                            FINANCIAL SUMMARY
                           ============================================= */}

                        <div className="rr-pr-show-side-card">

                            <div className="rr-pr-show-side-title">

                                <i className="bi bi-calculator"></i>

                                Financial Summary

                            </div>


                            <MoneyInfoRow
                                label="Subtotal"
                                value={
                                    purchaseReturn?.sub_total
                                }
                            />


                            {
                                purchase?.tax_type === 'gst' && (

                                    <MoneyInfoRow
                                        label="GST"
                                        value={
                                            purchaseReturn?.gst_total
                                        }
                                    />

                                )
                            }


                            <div className="rr-pr-show-grand-total">

                                <span>
                                    Return Total
                                </span>


                                <strong>
                                    {
                                        formatMoney(
                                            purchaseReturn?.total_amount
                                        )
                                    }
                                </strong>

                            </div>

                        </div>


                        {/* =============================================
                            BUSINESS
                           ============================================= */}

                        <div className="rr-pr-show-side-card">

                            <div className="rr-pr-show-side-title">

                                <i className="bi bi-building"></i>

                                Business Context

                            </div>


                            <InfoRow
                                label="Company"
                                value={
                                    business?.company_name
                                    ??
                                    purchase?.company?.company_name
                                    ??
                                    '—'
                                }
                            />


                            <InfoRow
                                label="Branch"
                                value={
                                    business?.store_name
                                    ??
                                    purchase?.store?.store_name
                                    ??
                                    '—'
                                }
                            />


                            <InfoRow
                                label="Return No."
                                value={
                                    purchaseReturn?.return_no
                                }
                                strong
                            />

                        </div>

                    </aside>

                </div>


                {/* =====================================================
                    COMMAND BAR
                   ===================================================== */}

                <div className="rr-pr-command-bar rr-pr-show-command-bar">

                    <div>

                        <i className="bi bi-info-circle"></i>

                        Purchase return stock has been deducted from the original storage location.

                    </div>


                    <div>

                        <Link
                            href="/purchase-returns"
                            className="rr-pr-secondary-btn"
                        >
                            <i className="bi bi-arrow-left"></i>

                            Back
                        </Link>


                        <a
                            href={
                                `/purchase-returns/${purchaseReturn.id}/print`
                            }
                            target="_blank"
                            rel="noreferrer"
                            className="rr-pr-secondary-btn rr-pr-show-action-print"
                        >
                            <i className="bi bi-printer"></i>

                            Print
                        </a>


                        <a
                            href={
                                `/purchase-returns/${purchaseReturn.id}/pdf`
                            }
                            className="rr-pr-secondary-btn rr-pr-show-action-pdf"
                        >
                            <i className="bi bi-file-earmark-pdf"></i>

                            PDF
                        </a>


                        <Link
                            href={
                                `/purchase-returns/${purchaseReturn.id}/edit`
                            }
                            className="rr-pr-primary-btn"
                        >
                            <i className="bi bi-pencil-square"></i>

                            Edit Return
                        </Link>

                    </div>

                </div>

            </div>
        </>
    );
}


/*
|--------------------------------------------------------------------------
| Show Window
|--------------------------------------------------------------------------
*/

function ShowWindow({
    title,
    icon,
    children,
    noPadding = false,
}) {

    return (

        <section className="rr-pr-entry-window rr-pr-show-window">

            <div className="rr-pr-entry-title">

                <div>

                    <i className={`bi ${icon}`}></i>

                    <strong>
                        {title}
                    </strong>

                </div>

            </div>


            {
                noPadding
                    ? children
                    : (
                        <div className="rr-pr-entry-body">
                            {children}
                        </div>
                    )
            }

        </section>
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

        <div className={`rr-pr-stat ${tone}`}>

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
| Detail Item
|--------------------------------------------------------------------------
*/

function DetailItem({
    label,
    value,
    children,
}) {

    return (

        <div className="rr-pr-show-detail-item">

            <span>
                {label}
            </span>


            {
                children
                    ? children
                    : (
                        <strong>
                            {
                                value
                                ?? '—'
                            }
                        </strong>
                    )
            }

        </div>
    );
}


/*
|--------------------------------------------------------------------------
| Tax Badge
|--------------------------------------------------------------------------
*/

function TaxBadge({
    type,
}) {

    const gst =
        type === 'gst';


    return (

        <span
            className={
                gst
                    ? 'rr-pr-tax-badge gst'
                    : 'rr-pr-tax-badge non-gst'
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
                    ? 'GST Purchase'
                    : 'Without GST'
            }

        </span>
    );
}


/*
|--------------------------------------------------------------------------
| Location
|--------------------------------------------------------------------------
*/

function LocationBadge({
    godown,
}) {

    if (
        !godown
    ) {

        return (

            <span className="rr-pr-location-badge shop">

                <i className="bi bi-shop"></i>

                Shop

            </span>
        );
    }


    return (

        <span className="rr-pr-location-badge godown">

            <i className="bi bi-boxes"></i>

            {
                godown.godown_name
                ?? 'Godown'
            }

        </span>
    );
}


/*
|--------------------------------------------------------------------------
| Info Row
|--------------------------------------------------------------------------
*/

function InfoRow({
    label,
    value,
    strong = false,
}) {

    return (

        <div className="rr-pr-show-info-row">

            <span>
                {label}
            </span>


            <strong
                className={
                    strong
                        ? 'highlight'
                        : ''
                }
            >
                {
                    value
                    ?? '—'
                }
            </strong>

        </div>
    );
}


/*
|--------------------------------------------------------------------------
| Money Row
|--------------------------------------------------------------------------
*/

function MoneyInfoRow({
    label,
    value,
}) {

    return (

        <div className="rr-pr-show-info-row">

            <span>
                {label}
            </span>


            <strong>
                {
                    formatMoney(
                        value
                    )
                }
            </strong>

        </div>
    );
}


/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function numberValue(
    value
) {

    const number =
        Number(
            value
            ?? 0
        );


    return Number.isFinite(
        number
    )
        ? number
        : 0;
}


function formatMoney(
    value
) {

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
        numberValue(
            value
        )
    );
}


function formatQty(
    value
) {

    return new Intl.NumberFormat(
        'en-IN',
        {
            minimumFractionDigits:
                0,

            maximumFractionDigits:
                2,
        }
    ).format(
        numberValue(
            value
        )
    );
}


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

Show.layout =
    page => (

        <BusinessLayout>
            {page}
        </BusinessLayout>

    );