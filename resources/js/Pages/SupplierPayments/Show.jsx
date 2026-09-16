import React, {
    useState,
} from "react";

import {
    Head,
    Link,
    router,
} from "@inertiajs/react";

import BusinessLayout from "@/Layouts/BusinessLayout";
import MasterNavigation from "../../Components/Business/MasterNavigation";
import ConfirmModal from "../../Components/ConfirmModal";

import "../../../css/business/supplier-payment.css";

export default function Show({
    payment,
    account_transaction = null,
}) {
    const [confirmation, setConfirmation] = useState(false);
    const [processing, setProcessing] = useState(false);

    const cancelled = payment.status === "Cancelled";

    const cancelPayment = () => {
        setProcessing(true);

        router.patch(
            `/supplier-payments/${payment.id}/cancel`,
            {},
            {
                preserveScroll: true,
                onSuccess: () => setConfirmation(false),
                onFinish: () => setProcessing(false),
            },
        );
    };

    return (
        <>
            <Head title={`Supplier Payment ${payment.payment_no}`} />

            <div className="rr-sp-page rr-sp-show-page">
                <MasterNavigation />

                <header className="rr-sp-header rr-sp-show-header">
                    <div>
                        <span className="rr-sp-eyebrow">
                            <i className="bi bi-receipt"></i>
                            Accounts / Supplier Payments
                        </span>

                        <div className="rr-sp-show-title-line">
                            <h1>{payment.payment_no}</h1>
                            <StatusBadge status={payment.status} />
                        </div>

                        <p>
                            Payment voucher dated {dateValue(payment.payment_date)}
                        </p>
                    </div>

                    <div className="rr-sp-show-header-actions">
                        <Link href="/supplier-payments" className="back">
                            <i className="bi bi-arrow-left"></i>
                            Register
                        </Link>

                        <a
                            href={`/supplier-payments/${payment.id}/print`}
                            target="_blank"
                            rel="noreferrer"
                            className="back"
                        >
                            <i className="bi bi-printer"></i>
                            Print
                        </a>

                        <a
                            href={`/supplier-payments/${payment.id}/pdf`}
                            className="back"
                        >
                            <i className="bi bi-file-earmark-pdf"></i>
                            PDF
                        </a>

                        {payment.can_cancel && (
                            <button
                                type="button"
                                className="cancel"
                                onClick={() => setConfirmation(true)}
                            >
                                <i className="bi bi-x-octagon"></i>
                                Cancel Payment
                            </button>
                        )}
                    </div>
                </header>

                {cancelled && (
                    <div className="rr-sp-cancelled-banner">
                        <i className="bi bi-shield-exclamation"></i>

                        <div>
                            <strong>This payment has been cancelled</strong>
                            <span>
                                The original voucher remains available for audit and
                                an opposite account transaction has been created.
                            </span>
                        </div>

                        <small>
                            {payment.cancelled_at
                                ? dateTimeValue(payment.cancelled_at)
                                : "Cancellation recorded"}
                        </small>
                    </div>
                )}

                <section className="rr-sp-voucher">
                    <div className="rr-sp-voucher-topbar">
                        <div className="rr-sp-voucher-brand">
                            <span><i className="bi bi-wallet2"></i></span>
                            <div>
                                <strong>SUPPLIER PAYMENT VOUCHER</strong>
                                <small>Branch accounting document</small>
                            </div>
                        </div>

                        <div className="rr-sp-voucher-number">
                            <small>Voucher Number</small>
                            <strong>{payment.payment_no}</strong>
                        </div>
                    </div>

                    <div className="rr-sp-voucher-grid">
                        <VoucherBlock
                            icon="bi-truck"
                            title="Paid To"
                        >
                            <strong>{payment.supplier?.supplier_name ?? "—"}</strong>
                            <span>{payment.supplier?.supplier_code ?? "No supplier code"}</span>
                            {payment.supplier?.phone && <span>{payment.supplier.phone}</span>}
                            {payment.supplier?.gst_number && (
                                <span>GST: {payment.supplier.gst_number}</span>
                            )}
                        </VoucherBlock>

                        <VoucherBlock
                            icon="bi-calendar3"
                            title="Payment Information"
                        >
                            <DetailLine label="Payment date" value={dateValue(payment.payment_date)} />
                            <DetailLine label="Payment mode" value={payment.payment_mode} />
                            <DetailLine label="Reference" value={payment.reference_no || "—"} />
                        </VoucherBlock>

                        <VoucherBlock
                            icon="bi-bank"
                            title="Paid From Account"
                        >
                            <strong>{payment.account?.account_name ?? "Account unavailable"}</strong>
                            <span>{payment.account?.account_code ?? "—"}</span>
                            {payment.account?.bank_name && <span>{payment.account.bank_name}</span>}
                            {payment.account?.account_number && (
                                <span>A/C: {maskAccount(payment.account.account_number)}</span>
                            )}
                            {payment.account?.upi_id && <span>UPI: {payment.account.upi_id}</span>}
                        </VoucherBlock>

                        <VoucherBlock
                            icon="bi-file-earmark-text"
                            title="Purchase Allocation"
                        >
                            {payment.purchase ? (
                                <>
                                    <strong>{payment.purchase.purchase_no}</strong>
                                    <span>{dateValue(payment.purchase.purchase_date)}</span>
                                    <span>Purchase total: {currency(payment.purchase.grand_total)}</span>
                                </>
                            ) : (
                                <>
                                    <strong>Supplier Advance</strong>
                                    <span>Not allocated to a specific purchase</span>
                                </>
                            )}
                        </VoucherBlock>
                    </div>

                    <div className="rr-sp-voucher-amount">
                        <div>
                            <small>Amount Paid</small>
                            <strong>{currency(payment.amount)}</strong>
                            <span>{amountInWords(payment.amount)}</span>
                        </div>

                        <span className="rr-sp-mode-seal">
                            <i className={paymentModeIcon(payment.payment_mode)}></i>
                            {payment.payment_mode}
                        </span>
                    </div>

                    <div className="rr-sp-voucher-notes">
                        <span>
                            <i className="bi bi-chat-left-text"></i>
                            Remarks
                        </span>
                        <p>{payment.remarks || "No remarks were entered for this payment."}</p>
                    </div>

                    <div className="rr-sp-voucher-footer">
                        <div>
                            <small>Created By</small>
                            <strong>{payment.creator?.name ?? "System"}</strong>
                        </div>

                        <div>
                            <small>Document Status</small>
                            <strong className={cancelled ? "danger" : "success"}>
                                {payment.status}
                            </strong>
                        </div>

                        <div>
                            <small>Authorised Signature</small>
                            <span className="rr-sp-signature-line"></span>
                        </div>
                    </div>
                </section>

                <section className="rr-sp-show-cards">
                    <article className="rr-sp-account-entry-card">
                        <div className="rr-sp-show-card-heading">
                            <span><i className="bi bi-journal-check"></i></span>
                            <div>
                                <strong>Account Transaction</strong>
                                <small>Linked bookkeeping entry</small>
                            </div>
                        </div>

                        {account_transaction ? (
                            <div className="rr-sp-entry-details">
                                <DetailTile label="Entry ID" value={`#${account_transaction.id}`} />
                                <DetailTile label="Debit" value={currency(account_transaction.debit)} />
                                <DetailTile label="Credit" value={currency(account_transaction.credit)} strong />
                                <DetailTile
                                    label="Entry Type"
                                    value={account_transaction.is_reversal ? "Reversal" : "Original"}
                                />
                            </div>
                        ) : (
                            <div className="rr-sp-entry-missing">
                                <i className="bi bi-exclamation-triangle"></i>
                                <span>
                                    <strong>Account entry unavailable</strong>
                                    <small>Check the accounting transaction posting for this voucher.</small>
                                </span>
                            </div>
                        )}
                    </article>

                    <article className="rr-sp-audit-card">
                        <div className="rr-sp-show-card-heading">
                            <span><i className="bi bi-shield-check"></i></span>
                            <div>
                                <strong>Audit Information</strong>
                                <small>Document ownership and cancellation</small>
                            </div>
                        </div>

                        <div className="rr-sp-audit-list">
                            <DetailLine label="Created by" value={payment.creator?.name ?? "System"} />
                            <DetailLine label="Current status" value={payment.status} />
                            <DetailLine label="Cancelled by" value={payment.canceller?.name ?? "—"} />
                            <DetailLine
                                label="Cancelled at"
                                value={payment.cancelled_at ? dateTimeValue(payment.cancelled_at) : "—"}
                            />
                        </div>
                    </article>
                </section>

                <ConfirmModal
                    show={confirmation}
                    title="Cancel Supplier Payment"
                    message={`Cancel "${payment.payment_no}" for ${currency(payment.amount)}? A reversing account entry will be created. This voucher will not be deleted.`}
                    confirmText="Cancel Payment"
                    cancelText="Keep Payment"
                    icon="bi-x-octagon"
                    variant="danger"
                    processing={processing}
                    onConfirm={cancelPayment}
                    onCancel={() => {
                        if (!processing) setConfirmation(false);
                    }}
                />
            </div>
        </>
    );
}

function VoucherBlock({ icon, title, children }) {
    return (
        <article className="rr-sp-voucher-block">
            <header>
                <i className={`bi ${icon}`}></i>
                {title}
            </header>
            <div>{children}</div>
        </article>
    );
}

function DetailLine({ label, value }) {
    return (
        <span className="rr-sp-detail-line">
            <small>{label}</small>
            <b>{value}</b>
        </span>
    );
}

function DetailTile({ label, value, strong = false }) {
    return (
        <div className={`rr-sp-detail-tile ${strong ? "strong" : ""}`}>
            <small>{label}</small>
            <b>{value}</b>
        </div>
    );
}

function StatusBadge({ status }) {
    const completed = status === "Completed";

    return (
        <span className={`rr-sp-status ${completed ? "completed" : "cancelled"}`}>
            <i className={`bi ${completed ? "bi-check2-circle" : "bi-x-circle"}`}></i>
            {status}
        </span>
    );
}

function numberValue(value) {
    const parsed = Number(value);
    return Number.isFinite(parsed) ? parsed : 0;
}

function currency(value) {
    return `₹${numberValue(value).toLocaleString("en-IN", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })}`;
}

function dateValue(value) {
    if (!value) return "—";
    const date = new Date(`${String(value).slice(0, 10)}T00:00:00`);
    return Number.isNaN(date.getTime())
        ? value
        : date.toLocaleDateString("en-IN", {
            day: "2-digit",
            month: "short",
            year: "numeric",
        });
}

function dateTimeValue(value) {
    if (!value) return "—";
    const date = new Date(value);
    return Number.isNaN(date.getTime())
        ? value
        : date.toLocaleString("en-IN", {
            day: "2-digit",
            month: "short",
            year: "numeric",
            hour: "2-digit",
            minute: "2-digit",
        });
}

function maskAccount(value) {
    const text = String(value ?? "");
    return text.length > 4
        ? `${"•".repeat(Math.min(8, text.length - 4))}${text.slice(-4)}`
        : text;
}

function paymentModeIcon(mode) {
    const normalized = String(mode ?? "").toLowerCase();
    if (normalized === "cash") return "bi bi-cash";
    if (normalized.includes("bank") || normalized === "cheque") return "bi bi-bank";
    if (normalized === "card") return "bi bi-credit-card";
    return "bi bi-phone";
}

function amountInWords(value) {
    const amount = numberValue(value);
    return amount > 0
        ? `Indian Rupees ${amount.toLocaleString("en-IN", {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        })} only`
        : "Indian Rupees Zero only";
}

Show.layout = (page) => (
    <BusinessLayout title="Supplier Payment Details">
        {page}
    </BusinessLayout>
);
