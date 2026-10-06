import React, { useState } from "react";

import { Head, Link, router, usePage } from "@inertiajs/react";

import BusinessLayout from "@/Layouts/BusinessLayout";
import MasterNavigation from "../../Components/Business/MasterNavigation";
import ConfirmModal from "../../Components/ConfirmModal";

import "../../../css/business/sales-return.css";

export default function Show({
    salesReturn,
    refundReversal = null,
    ledgerEntries = [],
    mechanicImpact = null,
}) {
    const { flash = {} } = usePage().props;

    const items = Array.isArray(salesReturn?.items)
        ? salesReturn.items
        : [];
    const customer = salesReturn?.customer ?? {};
    const company = salesReturn?.company ?? {};
    const store = salesReturn?.store ?? {};
    const sale = salesReturn?.sale ?? {};
    const account = salesReturn?.account ?? null;
    const refundTransaction = salesReturn?.refund_account_transaction ?? null;
    const customerCredit = salesReturn?.refund_status === "credited";
    const isGstBill = (salesReturn?.billing_type ?? "gst") === "gst";
    const isCancelled = salesReturn?.status === "cancelled";
    const [cancelConfirmation, setCancelConfirmation] = useState(false);
    const [cancelling, setCancelling] = useState(false);

    const totalQuantity = items.reduce(
        (total, item) => total + numberValue(item.quantity),
        0,
    );
    const totalFreeQuantity = items.reduce(
        (total, item) => total + numberValue(item.free_quantity),
        0,
    );

    const openCancelConfirmation = () => {
        if (isCancelled) return;

        setCancelConfirmation(true);
    };

    const closeCancelConfirmation = () => {
        if (cancelling) return;

        setCancelConfirmation(false);
    };

    const confirmCancelReturn = () => {
        if (isCancelled || cancelling) return;

        setCancelling(true);

        router.patch(
            `/sales-returns/${salesReturn.id}/cancel`,
            {},
            {
                preserveScroll: true,
                onFinish: () => {
                    setCancelling(false);
                    setCancelConfirmation(false);
                },
            },
        );
    };

    return (
        <>
            <Head title={`Sales Return ${salesReturn?.return_no ?? ""}`} />

            <div className="rr-return-page rr-return-show-page">
                <MasterNavigation
                    active="sales-returns"
                    title="Sales Returns"
                    subtitle="Returned items, refund and stock movement details"
                />

                {flash.success && (
                    <div className="rr-return-alert">
                        <i className="bi bi-check-circle-fill"></i>
                        {flash.success}
                    </div>
                )}

                {flash.error && (
                    <div className="rr-return-alert danger">
                        <i className="bi bi-exclamation-triangle-fill"></i>
                        {flash.error}
                    </div>
                )}

                <header className="rr-return-header rr-return-show-header">
                    <div className="rr-return-header-main">
                        <Link
                            href="/sales-returns"
                            className="rr-return-back"
                            title="Back to sales returns"
                        >
                            <i className="bi bi-arrow-left"></i>
                        </Link>

                        <div className="rr-return-header-icon">
                            <i className="bi bi-arrow-counterclockwise"></i>
                        </div>

                        <div>
                            <span className="rr-return-eyebrow">Sales Return Note</span>
                            <h1>{salesReturn?.return_no ?? "Sales Return"}</h1>
                            <div className="rr-return-header-meta">
                                <span>
                                    <i className="bi bi-calendar3"></i>{" "}
                                    {displayDate(salesReturn?.return_date)}
                                </span>
                                <StatusBadge value={salesReturn?.status} />
                                <span className={isGstBill ? "gst" : "non-gst"}>
                                    {isGstBill ? "GST RETURN" : "WITHOUT GST"}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div className="rr-return-actions rr-return-show-actions">
                        <Link
                            href="/sales-returns"
                            className="rr-return-btn"
                        >
                            <i className="bi bi-arrow-left"></i> Back
                        </Link>

                        <a
                            href={`/sales-returns/${salesReturn.id}/print?paper=a4`}
                            target="_blank"
                            rel="noreferrer"
                            className="rr-return-btn"
                        >
                            <i className="bi bi-printer"></i> Print
                        </a>

                        <div className="rr-return-paper-menu">
                            <button type="button" className="rr-return-btn">
                                <i className="bi bi-file-earmark-pdf"></i> PDF
                                <i className="bi bi-chevron-down"></i>
                            </button>
                            <div className="rr-return-paper-options">
                                <a href={`/sales-returns/${salesReturn.id}/pdf?paper=a4`}>
                                    <i className="bi bi-file-earmark-text"></i>
                                    A4 PDF
                                </a>
                                <a href={`/sales-returns/${salesReturn.id}/pdf?paper=a5`}>
                                    <i className="bi bi-file-earmark"></i>
                                    A5 PDF
                                </a>
                            </div>
                        </div>

                        {!isCancelled && (
                            <>
                                <Link
                                    href={`/sales-returns/${salesReturn.id}/edit`}
                                    className="rr-return-btn primary"
                                >
                                    <i className="bi bi-pencil-square"></i> Edit
                                </Link>
                                <button
                                    type="button"
                                    className="rr-return-btn danger"
                                    onClick={openCancelConfirmation}
                                >
                                    <i className="bi bi-x-octagon"></i> Cancel Return
                                </button>
                            </>
                        )}
                    </div>
                </header>

                <div className="rr-return-show-metrics">
                    <Metric
                        icon="bi-box-seam"
                        label="Returned Lines"
                        value={items.length}
                        tone="blue"
                    />
                    <Metric
                        icon="bi-stack"
                        label="Return Quantity"
                        value={formatQuantity(totalQuantity)}
                        tone="slate"
                    />
                    <Metric
                        icon="bi-gift"
                        label="Free Quantity"
                        value={formatQuantity(totalFreeQuantity)}
                        tone="slate"
                    />
                    <Metric
                        icon="bi-currency-rupee"
                        label={customerCredit ? "Customer Credit" : "Refund Amount"}
                        value={formatMoney(
                            customerCredit
                                ? salesReturn?.total_amount
                                : salesReturn?.refund_amount,
                        )}
                        tone={
                            isCancelled ? "red" : customerCredit ? "blue" : "green"
                        }
                    />
                </div>

                <div className="rr-return-show-info-grid">
                    <InfoWindow icon="bi-receipt" title="Return Information">
                        <InfoRow label="Return No." value={salesReturn?.return_no} />
                        <InfoRow
                            label="Return Date"
                            value={displayDate(salesReturn?.return_date)}
                        />
                        <InfoRow
                            label="Original Invoice"
                            value={sale?.invoice_no ?? `#${salesReturn?.sale_id}`}
                            href={sale?.id ? `/sales/${sale.id}` : null}
                        />
                        <InfoRow
                            label="Invoice Date"
                            value={displayDate(sale?.invoice_date)}
                        />
                    </InfoWindow>

                    <InfoWindow icon="bi-person" title="Customer">
                        <InfoRow
                            label="Name"
                            value={customer.customer_name ?? "Walk-in Customer"}
                        />
                        <InfoRow label="Code" value={customer.customer_code} />
                        <InfoRow
                            label="Mobile"
                            value={customer.mobile ?? customer.phone}
                        />
                        {isGstBill && (
                            <InfoRow label="GSTIN" value={customer.gst_number} />
                        )}
                    </InfoWindow>

                    <InfoWindow icon="bi-building" title="Company & Branch">
                        <InfoRow label="Company" value={company.company_name} />
                        <InfoRow label="Branch" value={store.store_name} />
                        <InfoRow label="Phone" value={store.phone ?? company.phone} />
                        {isGstBill && (
                            <InfoRow label="GSTIN" value={company.gst_number} />
                        )}
                    </InfoWindow>

                    <InfoWindow icon="bi-wallet2" title="Refund Information">
                        <InfoRow
                            label="Method"
                            value={labelize(salesReturn?.refund_method)}
                        />
                        <InfoRow
                            label="Refund Status"
                            value={labelize(salesReturn?.refund_status)}
                        />
                        <InfoRow
                            label="Refund Amount"
                            value={formatMoney(salesReturn?.refund_amount)}
                            strong
                        />
                        <InfoRow
                            label="Refund Account"
                            value={
                                account
                                    ? `${account.account_name}${account.account_code
                                        ? ` (${account.account_code})`
                                        : ""
                                    }`
                                    : customerCredit
                                        ? "Customer Ledger Credit"
                                        : "Not selected"
                            }
                        />
                        {refundTransaction && (
                            <InfoRow
                                label="Account Entry"
                                value={`${refundTransaction.reference_no ??
                                    salesReturn?.return_no
                                    } · ${formatMoney(refundTransaction.credit)}`}
                            />
                        )}
                        {refundReversal && (
                            <InfoRow
                                label="Refund Reversal"
                                value={`${displayDate(
                                    refundReversal.transaction_date,
                                )} · ${formatMoney(refundReversal.debit)}`}
                                strong
                            />
                        )}
                        <InfoRow
                            label="Return Status"
                            value={labelize(salesReturn?.status)}
                        />
                    </InfoWindow>

                    {mechanicImpact && (
                        <InfoWindow
                            icon="bi-person-gear"
                            title="Mechanic Commission Impact"
                        >
                            <InfoRow
                                label="Mechanic"
                                value={`${mechanicImpact.mechanic?.mechanic_name ?? "-"} (${mechanicImpact.mechanic?.mechanic_code ?? "-"})`}
                                href={
                                    mechanicImpact.mechanic?.id
                                        ? `/mechanics/${mechanicImpact.mechanic.id}`
                                        : null
                                }
                            />
                            <InfoRow
                                label="Original Commission"
                                value={formatMoney(mechanicImpact.original_commission)}
                            />
                            <InfoRow
                                label="Net Return Deduction"
                                value={formatMoney(mechanicImpact.this_return_deduction)}
                                strong
                            />
                            {numberValue(mechanicImpact.this_return_reversed) > 0 && (
                                <InfoRow
                                    label="Deduction Reversed"
                                    value={formatMoney(mechanicImpact.this_return_reversed)}
                                    strong
                                />
                            )}
                            <InfoRow
                                label="Total Return Deduction"
                                value={formatMoney(mechanicImpact.total_return_deduction)}
                            />
                            <InfoRow
                                label="Effective Commission"
                                value={formatMoney(mechanicImpact.effective_commission)}
                                strong
                            />
                            <InfoRow
                                label="Already Paid"
                                value={formatMoney(mechanicImpact.paid_commission)}
                            />
                            <InfoRow
                                label="Current Payable"
                                value={formatMoney(mechanicImpact.payable_balance)}
                                strong
                            />
                            <InfoRow
                                label="Mechanic Advance"
                                value={formatMoney(mechanicImpact.advance_amount)}
                                strong
                            />
                            <InfoRow
                                label="Commission Status"
                                value={mechanicImpact.commission_status}
                            />
                        </InfoWindow>
                    )}
                </div>

                <section className="rr-return-window">
                    <div className="rr-return-window-title">
                        <div>
                            <i className="bi bi-table"></i>
                            <strong>Returned Products</strong>
                        </div>
                        <span className="rr-return-record-count">
                            {items.length} line(s)
                        </span>
                    </div>

                    <div className="rr-return-table-wrap">
                        <table className="rr-return-table rr-return-show-table">
                            <thead>
                                <tr>
                                    <th className="serial">#</th>
                                    <th>Product</th>
                                    <th>Model / Part No.</th>
                                    <th className="number">Qty</th>
                                    <th className="number">Free</th>
                                    <th className="number">Rate</th>
                                    <th className="number">Discount</th>
                                    <th className="number">Taxable</th>
                                    {isGstBill && <th className="number">GST</th>}
                                    <th className="number">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                {items.length ? (
                                    items.map((item, index) => (
                                        <tr key={item.id ?? index}>
                                            <td className="serial">{index + 1}</td>
                                            <td>
                                                <strong>
                                                    {item.product?.product_name ?? "Product"}
                                                </strong>
                                                <span>
                                                    {item.product?.product_code ?? "No product code"}
                                                </span>
                                            </td>
                                            <td>
                                                <strong>{productModel(item.product)}</strong>
                                                <span>{item.product?.manufacturer ?? ""}</span>
                                            </td>
                                            <td className="number balance-cell">
                                                {formatQuantity(item.quantity)}{" "}
                                                {unitName(item.product)}
                                            </td>
                                            <td className="number">
                                                {formatQuantity(item.free_quantity)}
                                            </td>
                                            <td className="number">
                                                {formatMoney(item.sale_price)}
                                            </td>
                                            <td className="number">
                                                <strong>{formatMoney(item.discount_amount)}</strong>
                                                <span>{discountLabel(item)}</span>
                                            </td>
                                            <td className="number">
                                                {formatMoney(item.taxable_amount)}
                                            </td>
                                            {isGstBill && (
                                                <td className="number">
                                                    <strong>
                                                        {formatQuantity(item.gst_percent)}%
                                                    </strong>
                                                    <span>{formatMoney(item.gst_amount)}</span>
                                                </td>
                                            )}
                                            <td className="number amount-cell">
                                                {formatMoney(item.line_total)}
                                            </td>
                                        </tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td
                                            colSpan={isGstBill ? 10 : 9}
                                            className="rr-return-empty"
                                        >
                                            <i className="bi bi-inbox"></i>
                                            <strong>No returned products found</strong>
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </section>

                <section className="rr-return-window">
                    <div className="rr-return-window-title">
                        <div>
                            <i className="bi bi-journal-text"></i>
                            <strong>Customer Ledger Effect</strong>
                        </div>
                        <span className="rr-return-record-count">
                            {ledgerEntries.length} entry(s)
                        </span>
                    </div>

                    <div className="rr-return-table-wrap">
                        <table className="rr-return-table rr-return-show-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th>Reference</th>
                                    <th>Remarks</th>
                                    <th className="number">Debit</th>
                                    <th className="number">Credit</th>
                                </tr>
                            </thead>
                            <tbody>
                                {ledgerEntries.length ? (
                                    ledgerEntries.map((entry) => (
                                        <tr key={entry.id}>
                                            <td>
                                                {displayDate(entry.transaction_date)}
                                            </td>
                                            <td>
                                                {labelize(entry.transaction_type)}
                                            </td>
                                            <td>{entry.reference_no ?? "-"}</td>
                                            <td>{entry.remarks ?? "-"}</td>
                                            <td className="number">
                                                {formatMoney(entry.debit)}
                                            </td>
                                            <td className="number">
                                                {formatMoney(entry.credit)}
                                            </td>
                                        </tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td
                                            colSpan="6"
                                            className="rr-return-empty"
                                        >
                                            No customer ledger entries found.
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </section>

                <div className="rr-return-show-bottom-grid">
                    <section className="rr-return-window">
                        <div className="rr-return-window-title">
                            <div>
                                <i className="bi bi-chat-left-text"></i>
                                <strong>Remarks</strong>
                            </div>
                        </div>
                        <div className="rr-return-show-remarks">
                            {salesReturn?.remarks || "No remarks entered for this return."}
                        </div>
                    </section>

                    <section className="rr-return-window">
                        <div className="rr-return-window-title">
                            <div>
                                <i className="bi bi-calculator"></i>
                                <strong>Return Totals</strong>
                            </div>
                        </div>
                        <div className="rr-return-summary">
                            <TotalRow
                                label="Gross / Subtotal"
                                value={salesReturn?.sub_total}
                            />
                            <TotalRow
                                label="Item Discount"
                                value={salesReturn?.discount_total}
                                negative
                            />
                            {isGstBill && (
                                <TotalRow label="GST Total" value={salesReturn?.gst_total} />
                            )}
                            <TotalRow label="Round Off" value={salesReturn?.round_off} />
                            <TotalRow
                                label="Return Total"
                                value={salesReturn?.total_amount}
                                grand
                            />
                        </div>
                    </section>
                </div>

                {isCancelled && (
                    <div className="rr-return-cancelled-note">
                        <i className="bi bi-exclamation-octagon-fill"></i>
                        <div>
                            <strong>This sales return is cancelled.</strong>
                            <span>
                                Its stock movement has been reversed and it cannot be edited.
                            </span>
                        </div>
                    </div>
                )}

                <ConfirmModal
                    show={cancelConfirmation}
                    title="Cancel Sales Return"
                    message={`Are you sure you want to cancel sales return "${salesReturn?.return_no ?? ""}"? Stock added through this return will be reversed. This return cannot be edited after cancellation.`}
                    confirmText="Cancel Return"
                    cancelText="Keep Return"
                    icon="bi-x-octagon"
                    variant="danger"
                    processing={cancelling}
                    onConfirm={confirmCancelReturn}
                    onCancel={closeCancelConfirmation}
                />
            </div>
        </>
    );
}

function StatusBadge({ value }) {
    const status = value ?? "completed";

    return (
        <span className={`rr-return-status ${status}`}>
            {labelize(status)}
        </span>
    );
}

function Metric({ icon, label, value, tone }) {
    return (
        <div className={`rr-return-metric ${tone}`}>
            <div>
                <i className={`bi ${icon}`}></i>
            </div>
            <span>{label}</span>
            <strong>{value}</strong>
        </div>
    );
}

function InfoWindow({ icon, title, children }) {
    return (
        <section className="rr-return-window rr-return-show-info-window">
            <div className="rr-return-window-title">
                <div>
                    <i className={`bi ${icon}`}></i>
                    <strong>{title}</strong>
                </div>
            </div>
            <div className="rr-return-show-info-body">{children}</div>
        </section>
    );
}

function InfoRow({ label, value, href = null, strong = false }) {
    const displayedValue = value || "-";

    return (
        <div className="rr-return-show-info-row">
            <span>{label}</span>
            {href ? (
                <Link href={href}>{displayedValue}</Link>
            ) : (
                <strong className={strong ? "highlight" : ""}>
                    {displayedValue}
                </strong>
            )}
        </div>
    );
}

function TotalRow({ label, value, negative = false, grand = false }) {
    return (
        <div className={`rr-return-total-row ${grand ? "grand" : ""}`}>
            <span>{label}</span>
            <strong>
                {negative && numberValue(value) > 0 ? "− " : ""}
                {formatMoney(value)}
            </strong>
        </div>
    );
}

function productModel(product) {
    return (
        product?.model ||
        product?.model_name ||
        product?.part_number ||
        product?.oem_number ||
        "-"
    );
}

function unitName(product) {
    return product?.unit?.short_name ?? product?.unit?.unit_name ?? "";
}

function discountLabel(item) {
    const value = numberValue(item.discount_value);

    if (numberValue(item.discount_amount) <= 0) return "No discount";
    if (item.discount_type === "percentage") {
        return `${formatQuantity(value)}%`;
    }

    return "Fixed";
}

function labelize(value) {
    if (!value) return "-";

    return String(value)
        .replaceAll("_", " ")
        .replace(/\b\w/g, (letter) => letter.toUpperCase());
}

function numberValue(value) {
    const number = Number(value ?? 0);
    return Number.isFinite(number) ? number : 0;
}

function formatQuantity(value) {
    return numberValue(value).toLocaleString("en-IN", {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    });
}

function formatMoney(value) {
    return new Intl.NumberFormat("en-IN", {
        style: "currency",
        currency: "INR",
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(numberValue(value));
}

function displayDate(value) {
    if (!value) return "-";

    return new Intl.DateTimeFormat("en-IN", {
        day: "2-digit",
        month: "short",
        year: "numeric",
    }).format(new Date(value));
}

Show.layout = (page) => <BusinessLayout>{page}</BusinessLayout>;
