import React from 'react';

import {
    Head,
    Link,
    usePage,
} from '@inertiajs/react';

import BusinessLayout from '@/Layouts/BusinessLayout';

import MasterNavigation
    from '../../Components/Business/MasterNavigation';

import '../../../css/business/purchases.css';


export default function Show({
    purchase,
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
    | Items
    |--------------------------------------------------------------------------
    */

    const items =
        purchase?.items ?? [];


    /*
    |--------------------------------------------------------------------------
    | Totals
    |--------------------------------------------------------------------------
    */

    const totalQuantity =
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


    const totalFreeQuantity =
        items.reduce(
            (
                total,
                item
            ) =>
                total
                +
                numberValue(
                    item.free_quantity
                ),
            0
        );


    const physicalQuantity =
        totalQuantity
        +
        totalFreeQuantity;


    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */

    return (
        <>
            <Head
                title={
                    `Purchase ${purchase?.purchase_no ?? ''}`
                }
            />


            <div className="rr-purchase-page">

                {/* =====================================================
                    MASTER NAVIGATION
                   ===================================================== */}

                <MasterNavigation
                    active="purchases"
                    title="Purchases"
                    subtitle="Purchase details and stock receipt"
                />


                {/* =====================================================
                    HEADER
                   ===================================================== */}

                <section className="rr-purchase-header rr-purchase-show-header">

                    <div className="rr-purchase-header-main">

                        <Link
                            href="/purchases"
                            className="rr-purchase-back-btn"
                            title="Back to Purchases"
                        >
                            <i className="bi bi-arrow-left"></i>
                        </Link>


                        <div className="rr-purchase-header-icon">
                            <i className="bi bi-receipt"></i>
                        </div>


                        <div className="rr-purchase-header-content">

                            <span className="rr-purchase-eyebrow">
                                Purchase Details
                            </span>


                            <h1>
                                {
                                    purchase?.purchase_no
                                    ?? 'Purchase'
                                }
                            </h1>


                            <div className="rr-purchase-header-meta">

                                <span>
                                    <i className="bi bi-calendar3"></i>

                                    {
                                        formatDate(
                                            purchase?.purchase_date
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


                                <TaxTypeBadge
                                    type={
                                        purchase?.tax_type
                                    }
                                />

                            </div>

                        </div>

                    </div>


                    <div className="rr-purchase-header-actions">

                        {/* Edit */}

                        <Link
                            href={
                                `/purchases/${purchase.id}/edit`
                            }
                            className="rr-purchase-secondary-btn rr-purchase-show-action-edit"
                        >
                            <i className="bi bi-pencil-square"></i>

                            Edit
                        </Link>


                        {/* Print */}

                        <a
                            href={
                                `/purchases/${purchase.id}/print`
                            }
                            target="_blank"
                            rel="noreferrer"
                            className="rr-purchase-secondary-btn rr-purchase-show-action-print"
                        >
                            <i className="bi bi-printer"></i>

                            Print
                        </a>


                        {/* PDF */}

                        <a
                            href={
                                `/purchases/${purchase.id}/pdf`
                            }
                            className="rr-purchase-secondary-btn rr-purchase-show-action-pdf"
                        >
                            <i className="bi bi-file-earmark-pdf"></i>

                            Download PDF
                        </a>


                        {/* Purchase List */}

                        <Link
                            href="/purchases"
                            className="rr-purchase-primary-btn"
                        >
                            <i className="bi bi-list-ul"></i>

                            Purchase List
                        </Link>

                    </div>

                </section>


                {/* =====================================================
                    SUMMARY
                   ===================================================== */}

                <section className="rr-purchase-summary rr-purchase-show-summary">

                    <SummaryCard
                        title="Grand Total"
                        value={
                            formatMoney(
                                purchase?.grand_total
                            )
                        }
                        icon="bi-currency-rupee"
                        tone="primary"
                    />


                    <SummaryCard
                        title="Paid Amount"
                        value={
                            formatMoney(
                                purchase?.paid_amount
                            )
                        }
                        icon="bi-check-circle"
                        tone="success"
                    />


                    <SummaryCard
                        title="Balance"
                        value={
                            formatMoney(
                                purchase?.balance_amount
                            )
                        }
                        icon="bi-wallet2"
                        tone={
                            numberValue(
                                purchase?.balance_amount
                            ) > 0
                                ? 'warning'
                                : 'success'
                        }
                    />


                    <SummaryCard
                        title="Purchased Qty"
                        value={
                            formatQty(
                                totalQuantity
                            )
                        }
                        icon="bi-box"
                        tone="info"
                    />


                    <SummaryCard
                        title="Physical Stock"
                        value={
                            formatQty(
                                physicalQuantity
                            )
                        }
                        icon="bi-boxes"
                        tone="neutral"
                    />

                </section>


                {/* =====================================================
                    MAIN SHOW LAYOUT
                   ===================================================== */}

                <div className="rr-purchase-show-layout rr-purchase-show-shell">

                    {/* =================================================
                        MAIN
                       ================================================= */}

                    <div className="rr-purchase-show-main">

                        {/* =============================================
                            PURCHASE INFORMATION
                           ============================================= */}

                        <ShowWindow
                            title="Purchase Information"
                            icon="bi-file-earmark-text"
                        >

                            <div className="rr-purchase-show-detail-grid rr-purchase-show-detail-grid-enhanced">

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
                                    label="Tax Type"
                                >
                                    <TaxTypeBadge
                                        type={
                                            purchase?.tax_type
                                        }
                                    />
                                </DetailItem>


                                <DetailItem
                                    label="Status"
                                >
                                    <StatusBadge
                                        active={
                                            purchase?.status
                                        }
                                    />
                                </DetailItem>


                                <DetailItem
                                    label="Supplier"
                                    value={
                                        purchase?.supplier?.supplier_name
                                    }
                                />


                                <DetailItem
                                    label="Supplier Invoice No."
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
                                    label="Number of Items"
                                    value={
                                        items.length
                                    }
                                />

                            </div>

                        </ShowWindow>


                        {/* =============================================
                            PURCHASE ITEMS
                           ============================================= */}

                        <ShowWindow
                            title="Purchase Items"
                            icon="bi-table"
                            noPadding
                        >

                            <div className="rr-purchase-show-table-wrap rr-purchase-show-table-wrap-enhanced">

                                <table className="rr-purchase-show-table">

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
                                                Qty
                                            </th>

                                            <th className="number">
                                                Free
                                            </th>

                                            <th className="number">
                                                Stock Qty
                                            </th>

                                            <th className="number">
                                                Rate
                                            </th>

                                            <th className="number">
                                                Discount
                                            </th>

                                            <th className="number">
                                                Taxable
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

                                                                {/* Serial */}

                                                                <td className="serial">
                                                                    {
                                                                        index + 1
                                                                    }
                                                                </td>


                                                                {/* Product */}

                                                                <td>

                                                                    <div className="rr-purchase-show-product">

                                                                        <div className="rr-purchase-show-product-icon">

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


                                                                {/* Location */}

                                                                <td>

                                                                    <LocationBadge
                                                                        godown={
                                                                            item.godown
                                                                        }
                                                                    />

                                                                </td>


                                                                {/* Qty */}

                                                                <td className="number">
                                                                    {
                                                                        formatQty(
                                                                            item.quantity
                                                                        )
                                                                    }
                                                                </td>


                                                                {/* Free Qty */}

                                                                <td className="number rr-purchase-free-qty">

                                                                    {
                                                                        formatQty(
                                                                            item.free_quantity
                                                                        )
                                                                    }

                                                                </td>


                                                                {/* Physical Stock */}

                                                                <td className="number rr-purchase-stock-qty">

                                                                    {
                                                                        formatQty(
                                                                            numberValue(
                                                                                item.quantity
                                                                            )
                                                                            +
                                                                            numberValue(
                                                                                item.free_quantity
                                                                            )
                                                                        )
                                                                    }

                                                                </td>


                                                                {/* Purchase Rate */}

                                                                <td className="number">

                                                                    {
                                                                        formatMoney(
                                                                            item.purchase_price
                                                                        )
                                                                    }

                                                                </td>


                                                                {/* Discount */}

                                                                <td className="number">

                                                                    {
                                                                        numberValue(
                                                                            item.discount
                                                                        ) > 0
                                                                            ? (
                                                                                <span className="rr-purchase-show-discount">

                                                                                    -

                                                                                    {
                                                                                        formatMoney(
                                                                                            item.discount
                                                                                        )
                                                                                    }

                                                                                </span>
                                                                            )
                                                                            : formatMoney(0)
                                                                    }

                                                                </td>


                                                                {/* Taxable */}

                                                                <td className="number">

                                                                    {
                                                                        formatMoney(
                                                                            item.taxable_amount
                                                                        )
                                                                    }

                                                                </td>


                                                                {/* GST */}

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


                                                                            <td className="number rr-purchase-show-tax">

                                                                                {
                                                                                    formatMoney(
                                                                                        item.gst_amount
                                                                                    )
                                                                                }

                                                                            </td>

                                                                        </>
                                                                    )
                                                                }


                                                                {/* Total */}

                                                                <td className="number rr-purchase-show-line-total">

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
                                                                    ? 12
                                                                    : 10
                                                            }
                                                            className="rr-purchase-show-empty"
                                                        >
                                                            No purchase items found.
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

                            <div className="rr-purchase-show-remarks">

                                {
                                    purchase?.remarks
                                    ||
                                    'No remarks available.'
                                }

                            </div>

                        </ShowWindow>

                    </div>


                    {/* =================================================
                        SIDE
                       ================================================= */}

                    <aside className="rr-purchase-show-side rr-purchase-show-side-enhanced">

                        {/* =============================================
                            SUPPLIER
                           ============================================= */}

                        <div className="rr-purchase-show-side-card">

                            <div className="rr-purchase-show-side-title">

                                <i className="bi bi-truck"></i>

                                Supplier

                            </div>


                            <div className="rr-purchase-show-supplier-card">

                                <div className="rr-purchase-show-supplier-icon">
                                    <i className="bi bi-building"></i>
                                </div>


                                <div>

                                    <strong>
                                        {
                                            purchase?.supplier?.supplier_name
                                            ?? 'Supplier'
                                        }
                                    </strong>


                                    <span>
                                        Purchase supplier
                                    </span>

                                </div>

                            </div>


                            <InfoRow
                                label="Invoice No."
                                value={
                                    purchase?.invoice_no
                                    || '—'
                                }
                            />


                            <InfoRow
                                label="Invoice Date"
                                value={
                                    purchase?.invoice_date
                                        ? formatDate(
                                            purchase.invoice_date
                                        )
                                        : '—'
                                }
                            />

                        </div>


                        {/* =============================================
                            STOCK RECEIPT
                           ============================================= */}

                        <div className="rr-purchase-show-side-card">

                            <div className="rr-purchase-show-side-title">

                                <i className="bi bi-boxes"></i>

                                Stock Receipt

                            </div>


                            <InfoRow
                                label="Purchased Qty"
                                value={
                                    formatQty(
                                        totalQuantity
                                    )
                                }
                            />


                            <InfoRow
                                label="Free Qty"
                                value={
                                    formatQty(
                                        totalFreeQuantity
                                    )
                                }
                            />


                            <InfoRow
                                label="Physical Qty"
                                value={
                                    formatQty(
                                        physicalQuantity
                                    )
                                }
                                strong
                            />

                        </div>


                        {/* =============================================
                            FINANCIAL SUMMARY
                           ============================================= */}

                        <div className="rr-purchase-show-side-card">

                            <div className="rr-purchase-show-side-title">

                                <i className="bi bi-calculator"></i>

                                Financial Summary

                            </div>


                            <MoneyInfoRow
                                label="Subtotal"
                                value={
                                    purchase?.subtotal
                                }
                            />


                            <MoneyInfoRow
                                label="Discount"
                                value={
                                    purchase?.discount
                                }
                                negative
                            />


                            {
                                purchase?.tax_type === 'gst' && (

                                    <MoneyInfoRow
                                        label="GST"
                                        value={
                                            purchase?.tax_amount
                                        }
                                    />

                                )
                            }


                            <MoneyInfoRow
                                label="Other Charges"
                                value={
                                    purchase?.other_charges
                                }
                            />


                            <div className="rr-purchase-show-grand-total">

                                <span>
                                    Grand Total
                                </span>


                                <strong>
                                    {
                                        formatMoney(
                                            purchase?.grand_total
                                        )
                                    }
                                </strong>

                            </div>

                        </div>


                        {/* =============================================
                            PAYMENT
                           ============================================= */}

                        <div className="rr-purchase-show-side-card">

                            <div className="rr-purchase-show-side-title">

                                <i className="bi bi-wallet2"></i>

                                Payment

                            </div>


                            <MoneyInfoRow
                                label="Paid"
                                value={
                                    purchase?.paid_amount
                                }
                            />


                            <div
                                className={
                                    `rr-purchase-show-balance ${numberValue(
                                        purchase?.balance_amount
                                    ) > 0
                                        ? 'pending'
                                        : 'paid'
                                    }`
                                }
                            >

                                <span>
                                    Balance
                                </span>


                                <strong>
                                    {
                                        formatMoney(
                                            purchase?.balance_amount
                                        )
                                    }
                                </strong>

                            </div>

                        </div>


                        {/* =============================================
                            BUSINESS CONTEXT
                           ============================================= */}

                        <div className="rr-purchase-show-side-card">

                            <div className="rr-purchase-show-side-title">

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
                                label="Purchase No."
                                value={
                                    purchase?.purchase_no
                                }
                                strong
                            />

                        </div>

                    </aside>

                </div>


                {/* =====================================================
                    COMMAND BAR
                   ===================================================== */}

                <div className="rr-purchase-command-bar rr-purchase-show-command-bar">

                    <div>

                        <i className="bi bi-info-circle"></i>

                        {
                            purchase?.tax_type === 'gst'
                                ? 'GST Purchase • Stock includes purchased and free quantity.'
                                : 'Without GST • Stock includes purchased and free quantity.'
                        }

                    </div>


                    <div>

                        {/* Back */}

                        <Link
                            href="/purchases"
                            className="rr-purchase-secondary-btn"
                        >
                            <i className="bi bi-arrow-left"></i>

                            Back
                        </Link>


                        {/* Print */}

                        <a
                            href={
                                `/purchases/${purchase.id}/print`
                            }
                            target="_blank"
                            rel="noreferrer"
                            className="rr-purchase-secondary-btn rr-purchase-show-action-print"
                        >
                            <i className="bi bi-printer"></i>

                            Print
                        </a>


                        {/* PDF */}

                        <a
                            href={
                                `/purchases/${purchase.id}/pdf`
                            }
                            className="rr-purchase-secondary-btn rr-purchase-show-action-pdf"
                        >
                            <i className="bi bi-file-earmark-pdf"></i>

                            PDF
                        </a>


                        {/* Edit */}

                        <Link
                            href={
                                `/purchases/${purchase.id}/edit`
                            }
                            className="rr-purchase-primary-btn"
                        >
                            <i className="bi bi-pencil-square"></i>

                            Edit Purchase
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

        <section className="rr-purchase-entry-window rr-purchase-show-window">

            <div className="rr-purchase-entry-title">

                <div>

                    <i
                        className={
                            `bi ${icon}`
                        }
                    ></i>


                    <strong>
                        {title}
                    </strong>

                </div>

            </div>


            {
                noPadding
                    ? children
                    : (
                        <div className="rr-purchase-entry-body">
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
| Detail Item
|--------------------------------------------------------------------------
*/

function DetailItem({
    label,
    value,
    children,
}) {

    return (

        <div className="rr-purchase-show-detail-item">

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
| Tax Type Badge
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
                    ? 'GST Purchase'
                    : 'Without GST'
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

            <span className="rr-purchase-location-badge shop">

                <i className="bi bi-shop"></i>

                Shop

            </span>
        );
    }


    return (

        <span className="rr-purchase-location-badge godown">

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

        <div className="rr-purchase-show-info-row">

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
| Money Info Row
|--------------------------------------------------------------------------
*/

function MoneyInfoRow({
    label,
    value,
    negative = false,
}) {

    const amount =
        numberValue(
            value
        );


    return (

        <div className="rr-purchase-show-info-row">

            <span>
                {label}
            </span>


            <strong
                className={
                    negative && amount > 0
                        ? 'negative'
                        : ''
                }
            >

                {
                    negative && amount > 0
                        ? '- '
                        : ''
                }


                {
                    formatMoney(
                        amount
                    )
                }

            </strong>

        </div>
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


/*
|--------------------------------------------------------------------------
| Quantity
|--------------------------------------------------------------------------
*/

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


/*
|--------------------------------------------------------------------------
| Number
|--------------------------------------------------------------------------
*/

function numberValue(
    value
) {

    const number =
        Number(
            value ?? 0
        );


    return Number.isFinite(
        number
    )
        ? number
        : 0;
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

Show.layout =
    page => (

        <BusinessLayout>
            {page}
        </BusinessLayout>

    );