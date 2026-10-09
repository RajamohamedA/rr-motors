import React from "react";
import { Head, Link } from "@inertiajs/react";

import BusinessLayout from "@/Layouts/BusinessLayout";
import MasterNavigation from "../../Components/Business/MasterNavigation";

import "../../../css/business/purchase-report.css";
import "../../../css/business/purchase-report-show.css";

export default function Show({ purchase = {} }) {
    const paid = Number(purchase.paid_amount ?? 0);
    const total = Number(purchase.grand_total ?? 0);
    const balance = Number(purchase.balance_amount ?? 0);
    const paidPercent = total > 0
        ? Math.min(100, Math.max(0, (paid / total) * 100))
        : 0;
    const items = purchase.items ?? [];
    const totalQuantity = items.reduce(
        (sum, item) => sum + Number(item.quantity ?? 0),
        0,
    );
    const totalGst = items.reduce(
        (sum, item) => sum + Number(item.gst_amount ?? 0),
        0,
    );

    return (
        <>
            <Head title={`Purchase ${purchase.purchase_no ?? "Details"}`} />

            <div className="rr-pr-page rr-pr-show-page">
                <MasterNavigation
                    active="purchase-reports"
                    title="Purchase Report"
                    subtitle="Invoice-level purchase analysis"
                />

                <section className="rr-pr-show-hero">
                    <div className="rr-pr-show-hero-main">
                        <Link
                            href="/purchase-reports"
                            className="rr-pr-show-back"
                            title="Return to purchase report"
                        >
                            <i className="bi bi-arrow-left" />
                        </Link>

                        <div className="rr-pr-show-document-icon">
                            <i className="bi bi-receipt-cutoff" />
                        </div>

                        <div className="rr-pr-show-heading">
                            <span>Purchase Invoice Analysis</span>
                            <h1>{purchase.purchase_no ?? "Purchase Details"}</h1>
                            <p>
                                <i className="bi bi-calendar3" />
                                {displayDate(purchase.purchase_date)}
                                <span>•</span>
                                {purchase.billing_type === "gst" ? "GST Bill" : "Non-GST Bill"}
                            </p>
                        </div>
                    </div>

                    <div className="rr-pr-show-hero-actions">
                        <Status value={purchase.payment_status} />
                        <Link href="/purchase-reports" className="rr-pr-show-list-btn">
                            <i className="bi bi-list-ul" />
                            Report List
                        </Link>
                    </div>
                </section>

                <section className="rr-pr-show-kpis">
                    <KpiCard
                        tone="blue"
                        icon="bi-currency-rupee"
                        label="Invoice Total"
                        value={money(total)}
                        note="Gross purchase value"
                    />
                    <KpiCard
                        tone="green"
                        icon="bi-check-circle"
                        label="Amount Paid"
                        value={money(paid)}
                        note={`${paidPercent.toFixed(1)}% settled`}
                    />
                    <KpiCard
                        tone="red"
                        icon="bi-hourglass-split"
                        label="Outstanding"
                        value={money(balance)}
                        note={balance > 0 ? "Pending supplier payment" : "Invoice fully settled"}
                    />
                    <KpiCard
                        tone="violet"
                        icon="bi-box-seam"
                        label="Items / Quantity"
                        value={`${items.length} / ${quantity(totalQuantity)}`}
                        note="Product lines and quantity"
                    />
                </section>

                <div className="rr-pr-show-grid">
                    <section className="rr-pr-show-card supplier-card">
                        <CardHeader
                            icon="bi-truck"
                            title="Supplier Information"
                            subtitle="Supplier linked to this purchase"
                        />

                        <div className="rr-pr-show-supplier">
                            <div className="rr-pr-show-avatar">
                                {initials(purchase.supplier?.name)}
                            </div>
                            <div>
                                <small>SUPPLIER</small>
                                <strong>{purchase.supplier?.name ?? "Not available"}</strong>
                                <span>{purchase.supplier?.code ?? "No supplier code"}</span>
                            </div>
                        </div>

                        <dl className="rr-pr-show-details">
                            <Detail
                                icon="bi-hash"
                                label="Purchase Number"
                                value={purchase.purchase_no}
                            />
                            <Detail
                                icon="bi-calendar-check"
                                label="Purchase Date"
                                value={displayDate(purchase.purchase_date)}
                            />
                            <Detail
                                icon="bi-file-earmark-text"
                                label="Billing Type"
                                value={purchase.billing_type === "gst" ? "GST" : "Non-GST"}
                            />
                            <Detail
                                icon="bi-activity"
                                label="Record Status"
                                value={formatStatus(purchase.status)}
                            />
                        </dl>
                    </section>

                    <section className="rr-pr-show-card payment-card">
                        <CardHeader
                            icon="bi-wallet2"
                            title="Payment Position"
                            subtitle="Invoice settlement overview"
                        />

                        <div className="rr-pr-progress-block">
                            <div className="rr-pr-progress-head">
                                <div>
                                    <small>PAYMENT COMPLETION</small>
                                    <strong>{paidPercent.toFixed(1)}%</strong>
                                </div>
                                <Status value={purchase.payment_status} />
                            </div>

                            <div
                                className="rr-pr-payment-progress"
                                role="progressbar"
                                aria-valuemin="0"
                                aria-valuemax="100"
                                aria-valuenow={Math.round(paidPercent)}
                            >
                                <span style={{ width: `${paidPercent}%` }} />
                            </div>
                        </div>

                        <div className="rr-pr-payment-breakdown">
                            <PaymentRow
                                label="Invoice Amount"
                                value={money(total)}
                                tone="total"
                            />
                            <PaymentRow
                                label="Paid Amount"
                                value={money(paid)}
                                tone="paid"
                            />
                            <PaymentRow
                                label="Balance Payable"
                                value={money(balance)}
                                tone="due"
                            />
                            <PaymentRow
                                label="Item GST Total"
                                value={money(totalGst)}
                                tone="tax"
                            />
                        </div>
                    </section>
                </div>

                <section className="rr-pr-show-card rr-pr-items-card">
                    <CardHeader
                        icon="bi-boxes"
                        title="Purchased Item Details"
                        subtitle={`${items.length} product lines in this invoice`}
                    />

                    <div className="rr-pr-show-table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th className="serial">#</th>
                                    <th>Product</th>
                                    <th>Godown</th>
                                    <th className="number">Quantity</th>
                                    <th className="number">Purchase Price</th>
                                    <th className="number">GST</th>
                                    <th className="number">Line Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                {items.length ? (
                                    items.map((item, index) => (
                                        <tr key={item.id ?? index}>
                                            <td className="serial">{index + 1}</td>
                                            <td>
                                                <div className="rr-pr-product-cell">
                                                    <span>
                                                        <i className="bi bi-box" />
                                                    </span>
                                                    <strong>{item.product_name ?? "Product"}</strong>
                                                </div>
                                            </td>
                                            <td>{item.godown_name ?? "—"}</td>
                                            <td className="number quantity">
                                                {quantity(item.quantity)}
                                            </td>
                                            <td className="number">
                                                {money(item.purchase_price)}
                                            </td>
                                            <td className="number tax">
                                                {money(item.gst_amount)}
                                            </td>
                                            <td className="number total">
                                                {money(item.line_total)}
                                            </td>
                                        </tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td colSpan="7">
                                            <div className="rr-pr-show-empty">
                                                <i className="bi bi-inbox" />
                                                <strong>No item details available</strong>
                                            </div>
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                            {items.length > 0 && (
                                <tfoot>
                                    <tr>
                                        <td colSpan="3">Invoice Totals</td>
                                        <td className="number">{quantity(totalQuantity)}</td>
                                        <td />
                                        <td className="number">{money(totalGst)}</td>
                                        <td className="number">{money(total)}</td>
                                    </tr>
                                </tfoot>
                            )}
                        </table>
                    </div>
                </section>

                <section className="rr-pr-show-footer-bar">
                    <div>
                        <i className="bi bi-info-circle" />
                        <span>
                            This page displays purchase information within the active
                            company and branch only.
                        </span>
                    </div>
                    <Link href="/purchase-reports">
                        <i className="bi bi-arrow-left" />
                        Back to Purchase Report
                    </Link>
                </section>
            </div>
        </>
    );
}

function KpiCard({ tone, icon, label, value, note }) {
    return (
        <article className={`rr-pr-show-kpi ${tone}`}>
            <div className="rr-pr-show-kpi-icon">
                <i className={`bi ${icon}`} />
            </div>
            <div>
                <small>{label}</small>
                <strong>{value}</strong>
                <span>{note}</span>
            </div>
            <i className={`bi ${icon} watermark`} />
        </article>
    );
}

function CardHeader({ icon, title, subtitle }) {
    return (
        <header className="rr-pr-show-card-head">
            <div>
                <i className={`bi ${icon}`} />
            </div>
            <span>
                <strong>{title}</strong>
                <small>{subtitle}</small>
            </span>
        </header>
    );
}

function Detail({ icon, label, value }) {
    return (
        <div>
            <dt>
                <i className={`bi ${icon}`} />
                {label}
            </dt>
            <dd>{value || "—"}</dd>
        </div>
    );
}

function PaymentRow({ label, value, tone }) {
    return (
        <div className={tone}>
            <span>{label}</span>
            <strong>{value}</strong>
        </div>
    );
}

function Status({ value }) {
    const normalized = String(value ?? "Unpaid").toLowerCase();
    const tone = normalized === "paid"
        ? "paid"
        : normalized.includes("partial")
            ? "partial"
            : "unpaid";

    return (
        <span className={`rr-pr-status ${tone}`}>
            <i
                className={`bi ${tone === "paid"
                        ? "bi-check-circle-fill"
                        : tone === "partial"
                            ? "bi-circle-half"
                            : "bi-exclamation-circle-fill"
                    }`}
            />
            {value ?? "Unpaid"}
        </span>
    );
}

function money(value) {
    return new Intl.NumberFormat("en-IN", {
        style: "currency",
        currency: "INR",
        minimumFractionDigits: 2,
    }).format(Number(value ?? 0));
}

function quantity(value) {
    return new Intl.NumberFormat("en-IN", {
        minimumFractionDigits: 0,
        maximumFractionDigits: 3,
    }).format(Number(value ?? 0));
}

function displayDate(value) {
    if (!value) return "—";
    return new Date(`${value}T00:00:00`).toLocaleDateString("en-IN", {
        day: "2-digit",
        month: "short",
        year: "numeric",
    });
}

function initials(value) {
    return String(value ?? "S")
        .trim()
        .split(/\s+/)
        .slice(0, 2)
        .map((part) => part[0])
        .join("")
        .toUpperCase();
}

function formatStatus(value) {
    if (value === true || value === 1 || value === "1") return "Completed";
    if (value === false || value === 0 || value === "0") return "Inactive";
    return value || "—";
}

Show.layout = (page) => <BusinessLayout>{page}</BusinessLayout>;
