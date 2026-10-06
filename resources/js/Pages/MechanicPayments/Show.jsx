import React, { useState } from "react";

import { Head, Link, router, usePage } from "@inertiajs/react";

import BusinessLayout from "@/Layouts/BusinessLayout";
import MasterNavigation from "../../Components/Business/MasterNavigation";

import "../../../css/business/mechanic-payment.css";

export default function Show({
    payment,
    account_transactions: accountTransactions = [],
    ledger_entries: ledgerEntries = [],
}) {
    const { flash = {}, auth = {} } = usePage().props;
    const [paper, setPaper] = useState("a4");
    const [cancelOpen, setCancelOpen] = useState(false);
    const [cancelling, setCancelling] = useState(false);

    const mechanic = payment?.mechanic ?? {};
    const account = payment?.account ?? {};
    const allocations = Array.isArray(payment?.allocations)
        ? payment.allocations
        : [];
    const cancelled = payment?.status === "Cancelled";

    const permissions = auth?.user?.permissions ?? [];
    const roles = auth?.user?.roles ?? [];
    const privileged = Boolean(auth?.user?.is_super_user)
        || roles.includes("Company Admin");
    const canCancel = privileged
        || permissions.includes("mechanic-payment.delete");
    const canPrint = privileged
        || permissions.includes("mechanic-payment.print");

    const cancelPayment = () => {
        if (cancelled || cancelling) return;

        setCancelling(true);
        router.patch(
            `/mechanic-payments/${payment.id}/cancel`,
            {},
            {
                preserveScroll: true,
                onSuccess: () => setCancelOpen(false),
                onFinish: () => setCancelling(false),
            },
        );
    };

    const documentUrl = (type) =>
        `/mechanic-payments/${payment.id}/${type}?paper=${paper}`;

    return (
        <>
            <Head title={`Mechanic Payment ${payment?.payment_no ?? ""}`} />

            <div className="rr-mp-show-page">
                <MasterNavigation
                    active="mechanic-payments"
                    title="Mechanic Payments"
                    subtitle="Payment voucher and accounting details"
                />

                {flash.success && (
                    <div className="rr-mp-alert success">
                        <i className="bi bi-check-circle-fill"></i>
                        {flash.success}
                    </div>
                )}
                {flash.error && (
                    <div className="rr-mp-alert danger">
                        <i className="bi bi-exclamation-triangle-fill"></i>
                        {flash.error}
                    </div>
                )}
                {flash.warning && (
                    <div className="rr-mp-alert warning">
                        <i className="bi bi-exclamation-circle-fill"></i>
                        {flash.warning}
                    </div>
                )}

                <header className="rr-mp-show-header">
                    <div className="rr-mp-heading">
                        <Link href="/mechanic-payments" className="rr-mp-back">
                            <i className="bi bi-arrow-left"></i>
                        </Link>
                        <div>
                            <span>PAYMENT VOUCHER</span>
                            <h1>{payment.payment_no}</h1>
                            <p>
                                Created {dateTime(payment.created_at)}
                                {payment.creator?.name
                                    ? ` by ${payment.creator.name}`
                                    : ""}
                            </p>
                        </div>
                    </div>

                    <div className="rr-mp-header-actions">
                        {canPrint && (
                            <label className="rr-mp-paper">
                                <span>Paper</span>
                                <select value={paper} onChange={(event) => setPaper(event.target.value)}>
                                    <option value="a4">A4</option>
                                    <option value="a5">A5</option>
                                </select>
                            </label>
                        )}
                        {canPrint && (
                            <a className="rr-mp-btn print" href={documentUrl("print")} target="_blank" rel="noreferrer">
                                <i className="bi bi-printer"></i>Print
                            </a>
                        )}
                        {canPrint && (
                            <a className="rr-mp-btn pdf" href={documentUrl("pdf")}>
                                <i className="bi bi-file-earmark-pdf"></i>PDF
                            </a>
                        )}
                        {canCancel && !cancelled && (
                            <button className="rr-mp-btn cancel" type="button" onClick={() => setCancelOpen(true)}>
                                <i className="bi bi-x-octagon"></i>Cancel Payment
                            </button>
                        )}
                    </div>
                </header>

                <section className="rr-mp-status-row">
                    <span className={`rr-mp-status ${cancelled ? "cancelled" : "completed"}`}>
                        <i className={`bi ${cancelled ? "bi-x-circle" : "bi-check-circle"}`}></i>
                        {payment.status}
                    </span>
                    {cancelled && (
                        <span className="rr-mp-cancel-note">
                            Cancelled {dateTime(payment.cancelled_at)}
                            {payment.canceller?.name ? ` by ${payment.canceller.name}` : ""}
                        </span>
                    )}
                </section>

                <section className="rr-mp-summary-grid">
                    <Summary icon="bi-currency-rupee" tone="blue" label="Payment Amount" value={money(payment.amount)} />
                    <Summary icon="bi-diagram-3" tone="green" label="Allocated" value={money(payment.allocated_amount)} />
                    <Summary icon="bi-wallet2" tone="purple" label="Advance" value={money(payment.advance_amount)} />
                    <Summary icon="bi-calendar3" tone="orange" label="Payment Date" value={displayDate(payment.payment_date)} />
                </section>

                <div className="rr-mp-content-grid">
                    <section className="rr-mp-card">
                        <CardTitle icon="bi-person-gear" title="Mechanic Details" />
                        <div className="rr-mp-profile">
                            <span className="rr-mp-avatar">{initials(mechanic.mechanic_name)}</span>
                            <div>
                                <h2>{mechanic.mechanic_name || "—"}</h2>
                                <p>{mechanic.mechanic_code || "No mechanic code"}</p>
                            </div>
                        </div>
                        <dl className="rr-mp-details">
                            <Detail label="Mobile" value={mechanic.mobile} />
                            <Detail label="Email" value={mechanic.email} />
                            <Detail label="Address" value={address(mechanic)} wide />
                        </dl>
                    </section>

                    <section className="rr-mp-card">
                        <CardTitle icon="bi-bank" title="Payment Account" />
                        <dl className="rr-mp-details">
                            <Detail label="Account" value={account.account_name} />
                            <Detail label="Account Code" value={account.account_code} />
                            <Detail label="Payment Mode" value={payment.payment_mode} />
                            <Detail label="Account Type" value={account.account_type} />
                            <Detail label="Bank" value={account.bank_name} />
                            <Detail label="Account Number" value={masked(account.account_number)} />
                            <Detail label="UPI ID" value={account.upi_id} />
                            <Detail label="Reference No." value={payment.reference_no} />
                        </dl>
                    </section>
                </div>

                <section className="rr-mp-card rr-mp-allocation-card">
                    <CardTitle
                        icon="bi-diagram-3-fill"
                        title="Commission Allocations"
                        suffix={`${allocations.length} item${allocations.length === 1 ? "" : "s"}`}
                    />
                    <div className="rr-mp-table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Invoice</th>
                                    <th>Invoice Date</th>
                                    <th className="number">Original</th>
                                    <th className="number">Returned</th>
                                    <th className="number">Effective</th>
                                    <th className="number">Allocated</th>
                                    <th className="number">Remaining</th>
                                    <th className="number">Advance</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                {allocations.length ? allocations.map((allocation, index) => {
                                    const commission = allocation.commission ?? {};
                                    return (
                                        <tr key={allocation.id}>
                                            <td>{index + 1}</td>
                                            <td>
                                                <strong className="rr-mp-invoice">
                                                    {commission.invoice_no || "—"}
                                                </strong>
                                            </td>
                                            <td>{displayDate(commission.invoice_date)}</td>
                                            <td className="number">{money(commission.commission_amount)}</td>
                                            <td className="number returned">
                                                {money(commission.returned_commission_amount)}
                                            </td>
                                            <td className="number effective">
                                                {money(commission.effective_commission_amount)}
                                            </td>
                                            <td className="number allocated">{money(allocation.allocated_amount)}</td>
                                            <td className="number">{money(commission.balance_amount)}</td>
                                            <td className="number advance">
                                                {money(commission.advance_amount)}
                                            </td>
                                            <td>
                                                <span className="rr-mp-mini-status">
                                                    {commission.status || "—"}
                                                </span>
                                            </td>
                                        </tr>
                                    );
                                }) : (
                                    <tr>
                                        <td colSpan="10">
                                            <div className="rr-mp-empty">
                                                <i className="bi bi-wallet"></i>
                                                This payment is recorded as mechanic advance.
                                            </div>
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colSpan="6">Total allocated</td>
                                    <td className="number">{money(payment.allocated_amount)}</td>
                                    <td colSpan="3"></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </section>

                <div className="rr-mp-content-grid lower">
                    <section className="rr-mp-card">
                        <CardTitle icon="bi-journal-check" title="Mechanic Ledger Posting" />
                        <PostingRows rows={ledgerEntries} empty="No mechanic ledger entries found." />
                    </section>
                    <section className="rr-mp-card">
                        <CardTitle icon="bi-bank2" title="Account Posting" />
                        <PostingRows rows={accountTransactions} empty="No account transactions found." />
                    </section>
                </div>

                {payment.remarks && (
                    <section className="rr-mp-card rr-mp-remarks">
                        <CardTitle icon="bi-chat-left-text" title="Remarks" />
                        <p>{payment.remarks}</p>
                    </section>
                )}
            </div>

            {cancelOpen && (
                <div className="rr-mp-modal-backdrop" role="presentation" onMouseDown={() => !cancelling && setCancelOpen(false)}>
                    <div className="rr-mp-modal" role="dialog" aria-modal="true" onMouseDown={(event) => event.stopPropagation()}>
                        <span className="rr-mp-modal-icon"><i className="bi bi-exclamation-triangle-fill"></i></span>
                        <h2>Cancel mechanic payment?</h2>
                        <p>
                            Payment <strong>{payment.payment_no}</strong> will be cancelled.
                            Reversing mechanic-ledger and account entries will be created; the voucher will not be deleted.
                        </p>
                        <div>
                            <button type="button" className="rr-mp-btn secondary" disabled={cancelling} onClick={() => setCancelOpen(false)}>Keep Payment</button>
                            <button type="button" className="rr-mp-btn cancel" disabled={cancelling} onClick={cancelPayment}>
                                {cancelling ? <span className="spinner-border spinner-border-sm"></span> : <i className="bi bi-x-octagon"></i>}
                                Confirm Cancellation
                            </button>
                        </div>
                    </div>
                </div>
            )}
        </>
    );
}

function Summary({ icon, tone, label, value }) {
    return <article className={`rr-mp-summary ${tone}`}><span><i className={`bi ${icon}`}></i></span><div><small>{label}</small><strong>{value}</strong></div></article>;
}

function CardTitle({ icon, title, suffix = null }) {
    return <header className="rr-mp-card-title"><div><i className={`bi ${icon}`}></i><strong>{title}</strong></div>{suffix && <span>{suffix}</span>}</header>;
}

function Detail({ label, value, wide = false }) {
    return <div className={wide ? "wide" : ""}><dt>{label}</dt><dd>{value || "—"}</dd></div>;
}

function PostingRows({ rows, empty }) {
    if (!rows.length) return <div className="rr-mp-empty small"><i className="bi bi-inbox"></i>{empty}</div>;
    return <div className="rr-mp-postings">{rows.map((row) => <div key={row.id} className={row.is_reversal ? "reversal" : ""}><span>{row.transaction_type ? words(row.transaction_type) : row.is_reversal ? "Reversal" : "Posted"}</span><b className="debit">Dr {money(row.debit)}</b><b className="credit">Cr {money(row.credit)}</b></div>)}</div>;
}

function money(value) {
    return new Intl.NumberFormat("en-IN", { style: "currency", currency: "INR", minimumFractionDigits: 2 }).format(Number(value ?? 0));
}

function displayDate(value) {
    return value ? new Date(`${value}T00:00:00`).toLocaleDateString("en-IN") : "—";
}

function dateTime(value) {
    return value ? new Date(value).toLocaleString("en-IN") : "—";
}

function initials(value) {
    return String(value ?? "M").trim().split(/\s+/).slice(0, 2).map((part) => part[0]).join("").toUpperCase();
}

function masked(value) {
    if (!value) return "—";
    const text = String(value);
    return text.length <= 4 ? text : `${"•".repeat(Math.min(8, text.length - 4))}${text.slice(-4)}`;
}

function address(person) {
    return [person.address, person.city, person.state, person.pincode].filter(Boolean).join(", ") || "—";
}

function words(value) {
    return String(value ?? "").replaceAll("_", " ").replace(/\b\w/g, (letter) => letter.toUpperCase());
}

Show.layout = (page) => <BusinessLayout>{page}</BusinessLayout>;
