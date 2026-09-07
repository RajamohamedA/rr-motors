import React from 'react';
import { Head, Link, usePage } from '@inertiajs/react';

import BusinessLayout from '@/Layouts/BusinessLayout';
import MasterNavigation from '../../Components/Business/MasterNavigation';

import '../../../css/business/sales.css';

export default function Show({ sale }) {
    const { business } = usePage().props;
    const isGstBill = (sale?.billing_type ?? 'gst') === 'gst';
    const items = Array.isArray(sale?.items) ? sale.items : [];
    const customer = sale?.customer ?? {};
    const company = sale?.company ?? {};
    const store = sale?.store ?? {};
    const mechanic = sale?.mechanic ?? null;
    const commission =
        sale?.mechanic_commission ?? sale?.mechanicCommission ?? null;

    const itemDiscountTotal = items.reduce(
        (total, item) => total + numberValue(item.discount),
        0
    );

    return (
        <>
            <Head title={`Sale ${sale?.invoice_no ?? ''}`} />

            <div className="rr-sale-show-page">
                <MasterNavigation
                    active="sales"
                    title="Sales"
                    subtitle="Invoice details, payment and stock movement"
                />

                <header className="rr-show-command-window">
                    <div className="rr-show-title-area">
                        <Link
                            href="/sales"
                            className="rr-show-icon-button"
                            title="Back to sales"
                        >
                            <i className="bi bi-arrow-left"></i>
                        </Link>

                        <div className="rr-show-document-icon">
                            <i className="bi bi-receipt-cutoff"></i>
                        </div>

                        <div>
                            <span className="rr-show-eyebrow">
                                Sales Invoice
                            </span>
                            <h1>{sale?.invoice_no ?? 'Invoice'}</h1>
                            <div className="rr-show-title-meta">
                                <span>
                                    <i className="bi bi-calendar3"></i>
                                    {formatDate(sale?.invoice_date)}
                                </span>
                                <StatusBadge
                                    value={sale?.status ?? 'Completed'}
                                />
                                <span
                                    className={`rr-show-bill-badge ${isGstBill ? 'gst' : 'non-gst'}`}
                                >
                                    {isGstBill ? 'GST BILL' : 'WITHOUT GST'}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div className="rr-show-command-actions">
                        <a
                            href={`/sales/${sale.id}/print`}
                            target="_blank"
                            rel="noreferrer"
                            className="rr-show-button print"
                        >
                            <i className="bi bi-printer"></i> Print
                        </a>
                        <a
                            href={`/sales/${sale.id}/pdf`}
                            className="rr-show-button pdf"
                        >
                            <i className="bi bi-file-earmark-pdf"></i> PDF
                        </a>
                        {sale?.status !== 'Cancelled' && (
                            <Link
                                href={`/sales/${sale.id}/edit`}
                                className="rr-show-button primary"
                            >
                                <i className="bi bi-pencil-square"></i> Edit
                                Sale
                            </Link>
                        )}
                    </div>
                </header>

                <div className="rr-show-summary-strip">
                    <SummaryCard
                        icon="bi-currency-rupee"
                        label="Net Amount"
                        value={money(sale?.grand_total)}
                        tone="blue"
                    />
                    <SummaryCard
                        icon="bi-check2-circle"
                        label="Paid Amount"
                        value={money(sale?.paid_amount)}
                        tone="green"
                    />
                    <SummaryCard
                        icon="bi-hourglass-split"
                        label="Balance"
                        value={money(sale?.balance_amount)}
                        tone={
                            numberValue(sale?.balance_amount) > 0
                                ? 'red'
                                : 'green'
                        }
                    />
                    <SummaryCard
                        icon="bi-wallet2"
                        label="Payment Status"
                        value={sale?.payment_status ?? 'Unpaid'}
                        tone={paymentTone(sale?.payment_status)}
                    />
                </div>

                <div className="rr-show-party-grid">
                    <InfoWindow icon="bi-building" title="Company & Branch">
                        <InfoPrimary
                            value={
                                business?.company_name ??
                                company.company_name ??
                                'Company'
                            }
                        />
                        <InfoLine
                            icon="bi-shop"
                            value={business?.store_name ?? store.store_name}
                        />
                        <InfoLine
                            icon="bi-geo-alt"
                            value={joinAddress(store)}
                        />
                        <InfoLine
                            icon="bi-telephone"
                            value={store.phone ?? company.phone}
                        />
                        {isGstBill && (
                            <InfoLine
                                icon="bi-patch-check"
                                label="GSTIN"
                                value={company.gst_number}
                            />
                        )}
                    </InfoWindow>

                    <InfoWindow icon="bi-person" title="Bill To">
                        <InfoPrimary
                            value={customer.customer_name ?? 'Customer'}
                        />
                        <InfoLine
                            icon="bi-telephone"
                            value={customer.mobile ?? customer.phone}
                        />
                        <InfoLine icon="bi-envelope" value={customer.email} />
                        <InfoLine
                            icon="bi-geo-alt"
                            value={joinAddress(customer)}
                        />
                        {isGstBill && (
                            <InfoLine
                                icon="bi-patch-check"
                                label="GSTIN"
                                value={customer.gst_number}
                            />
                        )}
                    </InfoWindow>

                    <InfoWindow
                        icon={mechanic ? 'bi-tools' : 'bi-info-circle'}
                        title="Sale Information"
                    >
                        <InfoLine
                            icon="bi-diagram-3"
                            label="Source"
                            value={
                                mechanic
                                    ? 'Mechanic Referral'
                                    : 'Direct Customer'
                            }
                        />
                        {mechanic && (
                            <InfoPrimary value={mechanic.mechanic_name} />
                        )}
                        {mechanic && (
                            <InfoLine
                                icon="bi-telephone"
                                value={mechanic.mobile}
                            />
                        )}
                        {mechanic && (
                            <InfoLine
                                icon="bi-percent"
                                label="Commission"
                                value={commissionText(sale, commission)}
                            />
                        )}
                        <InfoLine
                            icon="bi-box-seam"
                            label="Items"
                            value={`${items.length} product(s)`}
                        />
                    </InfoWindow>
                </div>

                <section className="rr-show-window rr-show-items-window">
                    <WindowTitle
                        icon="bi-table"
                        title="Invoice Items"
                        detail={`${items.length} line(s)`}
                    />

                    <div className="rr-show-table-wrap">
                        <table className="rr-show-items-table">
                            <thead>
                                <tr>
                                    <th className="serial">#</th>
                                    <th>Product</th>
                                    <th>Location</th>
                                    <th className="number">Qty</th>
                                    <th className="number">Free</th>
                                    <th className="number">Rate</th>
                                    <th className="number">Discount</th>
                                    {isGstBill && (
                                        <th className="number">GST</th>
                                    )}
                                    <th className="number">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                {items.map((item, index) => (
                                    <tr key={item.id ?? index}>
                                        <td className="serial">{index + 1}</td>
                                        <td>
                                            <div className="rr-show-product-cell">
                                                <div className="rr-show-product-icon">
                                                    <i className="bi bi-box-seam"></i>
                                                </div>
                                                <div>
                                                    <strong>
                                                        {item.product
                                                            ?.product_name ??
                                                            'Product'}
                                                    </strong>
                                                    <span>
                                                        {productMeta(
                                                            item.product
                                                        )}
                                                    </span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span className="rr-show-location-badge">
                                                <i
                                                    className={`bi ${item.godown ? 'bi-boxes' : 'bi-shop'}`}
                                                ></i>
                                                {item.godown?.godown_name ??
                                                    'Shop'}
                                            </span>
                                        </td>
                                        <td className="number quantity">
                                            {quantity(item.quantity)}{' '}
                                            {item.product?.unit?.short_name ??
                                                ''}
                                        </td>
                                        <td className="number muted">
                                            {quantity(item.free_quantity)}
                                        </td>
                                        <td className="number">
                                            {money(item.sale_price)}
                                        </td>
                                        <td className="number">
                                            <div className="rr-show-discount-cell">
                                                <strong>
                                                    {money(item.discount)}
                                                </strong>
                                                {numberValue(item.discount) >
                                                    0 && (
                                                        <small>
                                                            {discountLabel(item)}
                                                        </small>
                                                    )}
                                            </div>
                                        </td>
                                        {isGstBill && (
                                            <td className="number">
                                                <div className="rr-show-gst-cell">
                                                    <strong>
                                                        {quantity(
                                                            item.gst_percent
                                                        )}
                                                        %
                                                    </strong>
                                                    <small>
                                                        {money(item.gst_amount)}
                                                    </small>
                                                </div>
                                            </td>
                                        )}
                                        <td className="number line-total">
                                            {money(item.line_total)}
                                        </td>
                                    </tr>
                                ))}

                                {!items.length && (
                                    <tr>
                                        <td colSpan={isGstBill ? 9 : 8}>
                                            <div className="rr-show-empty">
                                                <i className="bi bi-inbox"></i>
                                                <strong>
                                                    No invoice items found
                                                </strong>
                                            </div>
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </section>

                <div className="rr-show-bottom-grid">
                    <div className="rr-show-left-stack">
                        {sale?.remarks && (
                            <section className="rr-show-window">
                                <WindowTitle
                                    icon="bi-chat-left-text"
                                    title="Remarks"
                                />
                                <div className="rr-show-remarks">
                                    {sale.remarks}
                                </div>
                            </section>
                        )}

                        {mechanic && (
                            <section className="rr-show-window">
                                <WindowTitle
                                    icon="bi-tools"
                                    title="Mechanic Commission"
                                />
                                <div className="rr-show-commission-grid">
                                    <Detail
                                        label="Mechanic"
                                        value={mechanic.mechanic_name}
                                    />
                                    <Detail
                                        label="Commission Type"
                                        value={capitalize(sale.commission_type)}
                                    />
                                    <Detail
                                        label="Commission Amount"
                                        value={money(sale.commission_amount)}
                                        strong
                                    />
                                    <Detail
                                        label="Status"
                                        value={
                                            commission?.status ??
                                            sale.commission_status ??
                                            'Pending'
                                        }
                                    />
                                </div>
                            </section>
                        )}
                    </div>

                    <section className="rr-show-total-panel">
                        <div className="rr-show-total-title">
                            <i className="bi bi-calculator"></i>
                            Invoice Summary
                        </div>
                        <div className="rr-show-total-body">
                            <TotalRow
                                label={
                                    isGstBill
                                        ? 'Taxable Subtotal'
                                        : 'Items Subtotal'
                                }
                                value={sale?.sub_total}
                            />
                            {itemDiscountTotal > 0 && (
                                <TotalRow
                                    label="Item Discounts"
                                    value={-itemDiscountTotal}
                                    muted
                                />
                            )}
                            {isGstBill && (
                                <TotalRow
                                    label="GST Total"
                                    value={sale?.gst_total}
                                />
                            )}
                            <TotalRow
                                label="Gross Total"
                                value={
                                    numberValue(sale?.sub_total) +
                                    numberValue(sale?.gst_total)
                                }
                                strong
                            />
                            <TotalRow
                                label="Invoice Discount"
                                value={-numberValue(sale?.final_discount)}
                                muted
                            />
                            <TotalRow
                                label="Round Off"
                                value={sale?.round_off}
                                signed
                            />

                            <div className="rr-show-net-total">
                                <span>NET AMOUNT</span>
                                <strong>{money(sale?.grand_total)}</strong>
                            </div>

                            <TotalRow
                                label="Paid Amount"
                                value={sale?.paid_amount}
                                tone="paid"
                            />

                            <div
                                className={`rr-show-balance ${numberValue(sale?.balance_amount) > 0 ? 'due' : 'settled'}`}
                            >
                                <span>Balance</span>
                                <strong>{money(sale?.balance_amount)}</strong>
                            </div>
                        </div>
                    </section>
                </div>

                <footer className="rr-show-footer-bar">
                    <div>
                        <i className="bi bi-clock-history"></i>
                        Created {formatDateTime(sale?.created_at)}
                    </div>
                    <div>
                        <Link
                            href="/sales"
                            className="rr-show-button secondary"
                        >
                            Back to Sales
                        </Link>
                        <a
                            href={`/sales/${sale.id}/print`}
                            target="_blank"
                            rel="noreferrer"
                            className="rr-show-button primary"
                        >
                            <i className="bi bi-printer"></i> Print Invoice
                        </a>
                    </div>
                </footer>
            </div>
        </>
    );
}

function WindowTitle({ icon, title, detail }) {
    return (
        <div className="rr-show-window-title">
            <div>
                <i className={`bi ${icon}`}></i>
                <strong>{title}</strong>
            </div>
            {detail && <span>{detail}</span>}
        </div>
    );
}

function SummaryCard({ icon, label, value, tone }) {
    return (
        <div className={`rr-show-summary-card ${tone}`}>
            <div className="rr-show-summary-icon">
                <i className={`bi ${icon}`}></i>
            </div>
            <div>
                <span>{label}</span>
                <strong>{value}</strong>
            </div>
        </div>
    );
}

function InfoWindow({ icon, title, children }) {
    return (
        <section className="rr-show-window rr-show-info-window">
            <WindowTitle icon={icon} title={title} />
            <div className="rr-show-info-body">{children}</div>
        </section>
    );
}

function InfoPrimary({ value }) {
    if (!value) return null;
    return <strong className="rr-show-info-primary">{value}</strong>;
}

function InfoLine({ icon, label, value }) {
    if (!value) return null;
    return (
        <div className="rr-show-info-line">
            <i className={`bi ${icon}`}></i>
            <span>
                {label && <small>{label}</small>}
                {value}
            </span>
        </div>
    );
}

function StatusBadge({ value }) {
    return (
        <span className={`rr-show-status ${String(value).toLowerCase()}`}>
            {value}
        </span>
    );
}

function Detail({ label, value, strong }) {
    return (
        <div>
            <span>{label}</span>
            <strong className={strong ? 'highlight' : ''}>
                {value || '—'}
            </strong>
        </div>
    );
}

function TotalRow({
    label,
    value,
    strong = false,
    muted = false,
    signed = false,
    tone = '',
}) {
    const numeric = numberValue(value);
    const shown = signed
        ? `${numeric >= 0 ? '+' : '-'}${money(Math.abs(numeric))}`
        : money(numeric);

    return (
        <div
            className={`rr-show-total-row ${strong ? 'strong' : ''} ${muted ? 'muted' : ''} ${tone}`}
        >
            <span>{label}</span>
            <strong>{shown}</strong>
        </div>
    );
}

function discountLabel(item) {
    if (item.discount_type === 'percentage') {
        return `${quantity(item.discount_value)}% discount`;
    }
    return 'Fixed discount';
}

function commissionText(sale, commission) {
    if (sale?.commission_type === 'percentage') {
        return `${quantity(sale.commission_percent)}% • ${money(sale.commission_amount)}`;
    }
    return `${money(sale?.commission_amount ?? commission?.commission_amount)} fixed`;
}

function productMeta(product = {}) {
    return [product.product_code, product.part_number, product.unit?.short_name]
        .filter(Boolean)
        .join(' • ');
}

function joinAddress(record = {}) {
    return [record.address, record.city, record.state, record.pincode]
        .filter(Boolean)
        .join(', ');
}

function paymentTone(status) {
    if (status === 'Paid') return 'green';
    if (status === 'Partially Paid') return 'amber';
    return 'red';
}

function capitalize(value) {
    if (!value) return '—';
    return (
        String(value).charAt(0).toUpperCase() +
        String(value).slice(1).replaceAll('_', ' ')
    );
}

function numberValue(value) {
    const parsed = Number(value ?? 0);
    return Number.isFinite(parsed) ? parsed : 0;
}

function money(value) {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency',
        currency: 'INR',
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(numberValue(value));
}

function quantity(value) {
    return new Intl.NumberFormat('en-IN', {
        minimumFractionDigits: 0,
        maximumFractionDigits: 3,
    }).format(numberValue(value));
}

function formatDate(value) {
    if (!value) return '—';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return value;
    return new Intl.DateTimeFormat('en-IN', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    }).format(date);
}

function formatDateTime(value) {
    if (!value) return '—';
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) return value;
    return new Intl.DateTimeFormat('en-IN', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    }).format(date);
}

Show.layout = (page) => <BusinessLayout>{page}</BusinessLayout>;
