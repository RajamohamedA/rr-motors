import React, { useMemo, useState } from "react";
import { Head, Link, router, usePage } from "@inertiajs/react";

import BusinessLayout from "@/Layouts/BusinessLayout";
import MasterNavigation from "../../Components/Business/MasterNavigation";
import ConfirmModal from "../../Components/ConfirmModal";

import "../../../css/business/vouchers.css";

export default function Show({ voucher = {}, context = {} }) {
    const { auth = {}, flash = {} } = usePage().props;
    const permissions = auth?.user?.permissions ?? [];
    const roles = auth?.user?.roles ?? [];
    const privileged =
        Boolean(auth?.user?.is_super_user) ||
        roles.includes("Company Admin") ||
        roles.includes("Super Admin");

    const can = (permission) => privileged || permissions.includes(permission);
    const [paper, setPaper] = useState("a4");
    const [cancelOpen, setCancelOpen] = useState(false);
    const [processing, setProcessing] = useState(false);

    const receipt = voucher.voucher_type === "receipt";
    const cancelled = voucher.is_cancelled || voucher.status === "Cancelled";
    const transactions = voucher.transactions ?? [];
    const activeTransaction = useMemo(
        () => transactions.find((transaction) => !transaction.is_reversal),
        [transactions],
    );

    const cancelVoucher = () => {
        if (processing || cancelled) return;

        setProcessing(true);
        router.patch(
            `/vouchers/${voucher.id}/cancel`,
            {},
            {
                preserveScroll: true,
                onFinish: () => {
                    setProcessing(false);
                    setCancelOpen(false);
                },
            },
        );
    };

    return (
        <>
            <Head title={voucher.voucher_no ?? "Voucher Details"} />

            <main className={`rr-vch-page rr-vch-show-page ${receipt ? "receipt" : "payment"}`}>
                <MasterNavigation
                    active="vouchers"
                    title="Receipt & Payment Vouchers"
                    subtitle={context.store_name ? `Current branch: ${context.store_name}` : "Voucher audit details"}
                />

                {(flash.success || flash.error) && (
                    <div className={`rr-vch-alert ${flash.error ? "error" : "success"}`} role="alert">
                        <i className={`bi ${flash.error ? "bi-exclamation-octagon" : "bi-check-circle"}`} />
                        <span>{flash.error ?? flash.success}</span>
                    </div>
                )}

                <section className="rr-vch-show-head">
                    <div className="rr-vch-show-identity">
                        <span className="rr-vch-show-flow-icon">
                            <i className={`bi ${receipt ? "bi-arrow-down-left" : "bi-arrow-up-right"}`} />
                        </span>
                        <div>
                            <span>{receipt ? "RECEIPT VOUCHER" : "PAYMENT VOUCHER"}</span>
                            <h1>{voucher.voucher_no}</h1>
                            <p>
                                {date(voucher.voucher_date)} • {voucher.account?.name ?? "Account unavailable"}
                            </p>
                        </div>
                    </div>

                    <div className="rr-vch-show-head-right">
                        <span className={`rr-vch-show-status ${cancelled ? "cancelled" : "completed"}`}>
                            <i className={`bi ${cancelled ? "bi-x-circle-fill" : "bi-check-circle-fill"}`} />
                            {voucher.status}
                        </span>
                        <strong className="rr-vch-show-amount">
                            {receipt ? "+" : "−"} {money(voucher.amount)}
                        </strong>
                    </div>
                </section>

                <section className="rr-vch-show-toolbar">
                    <div className="rr-vch-paper-control">
                        <span>Paper</span>
                        <button type="button" className={paper === "a4" ? "active" : ""} onClick={() => setPaper("a4")}>A4</button>
                        <button type="button" className={paper === "a5" ? "active" : ""} onClick={() => setPaper("a5")}>A5</button>
                    </div>

                    <div className="rr-vch-show-actions">
                        <Link href="/vouchers" className="rr-vch-btn back">
                            <i className="bi bi-arrow-left" /> Register
                        </Link>

                        {can("voucher.show") && (
                            <>
                                <a
                                    href={`/vouchers/${voucher.id}/print?paper=${paper}`}
                                    target="_blank"
                                    rel="noreferrer"
                                    className="rr-vch-btn print"
                                >
                                    <i className="bi bi-printer" /> Print {paper.toUpperCase()}
                                </a>
                                <a href={`/vouchers/${voucher.id}/pdf?paper=${paper}`} className="rr-vch-btn pdf">
                                    <i className="bi bi-file-earmark-pdf" /> PDF {paper.toUpperCase()}
                                </a>
                            </>
                        )}

                        {can("voucher.delete") && !cancelled && (
                            <button type="button" className="rr-vch-btn cancel" onClick={() => setCancelOpen(true)}>
                                <i className="bi bi-x-octagon" /> Cancel Voucher
                            </button>
                        )}
                    </div>
                </section>

                {cancelled && (
                    <section className="rr-vch-cancel-banner">
                        <i className="bi bi-shield-x" />
                        <div>
                            <strong>This voucher has been cancelled</strong>
                            <p>
                                The original entry remains for audit purposes and an equal reversing account transaction was posted.
                            </p>
                        </div>
                        <span>
                            {voucher.cancelled_by || "System"}
                            {voucher.cancelled_at ? ` • ${dateTime(voucher.cancelled_at)}` : ""}
                        </span>
                    </section>
                )}

                <section className="rr-vch-show-summary">
                    <ShowMetric
                        tone={receipt ? "receipt" : "payment"}
                        icon={receipt ? "bi-box-arrow-in-down" : "bi-box-arrow-up-right"}
                        label={receipt ? "Amount Received" : "Amount Paid"}
                        value={money(voucher.amount)}
                    />
                    <ShowMetric
                        tone="account"
                        icon="bi-bank"
                        label="Cash / Bank Account"
                        value={voucher.account?.name ?? "—"}
                        note={voucher.account?.code}
                    />
                    <ShowMetric
                        tone="party"
                        icon="bi-person-vcard"
                        label={receipt ? "Received From" : "Paid To"}
                        value={voucher.party_name || "General"}
                        note={voucher.party_type ? label(voucher.party_type) : "Other particulars"}
                    />
                    <ShowMetric
                        tone="mode"
                        icon="bi-credit-card"
                        label="Payment Mode"
                        value={label(voucher.payment_mode) || "—"}
                        note={voucher.reference_no || "No external reference"}
                    />
                </section>

                <div className="rr-vch-show-grid">
                    <section className="rr-vch-show-card">
                        <ShowCardHeader icon="bi-receipt" title="Voucher Information" subtitle="Primary document particulars" />
                        <dl className="rr-vch-detail-list">
                            <Detail label="Voucher Number" value={voucher.voucher_no} strong />
                            <Detail label="Voucher Date" value={date(voucher.voucher_date)} />
                            <Detail label="Voucher Type" value={receipt ? "Receipt" : "Payment"} badge={receipt ? "receipt" : "payment"} />
                            <Detail label="Status" value={voucher.status} badge={cancelled ? "cancelled" : "completed"} />
                            <Detail label="Payment Mode" value={label(voucher.payment_mode)} />
                            <Detail label="Reference Number" value={voucher.reference_no || "—"} />
                        </dl>
                    </section>

                    <section className="rr-vch-show-card">
                        <ShowCardHeader icon="bi-building" title="Account & Party" subtitle="Money-flow destination and counterparty" />
                        <dl className="rr-vch-detail-list">
                            <Detail label="Account Name" value={voucher.account?.name ?? "—"} strong />
                            <Detail label="Account Code" value={voucher.account?.code ?? "—"} />
                            <Detail label="Account Type" value={label(voucher.account?.type) || "—"} />
                            <Detail label="Party Type" value={voucher.party_type ? label(voucher.party_type) : "Other"} />
                            <Detail label="Party / Particular" value={voucher.party_name || "General"} strong />
                            <Detail label="Amount" value={money(voucher.amount)} amount={receipt ? "receipt" : "payment"} />
                        </dl>
                    </section>
                </div>

                <section className="rr-vch-show-card rr-vch-narration-card">
                    <ShowCardHeader icon="bi-card-text" title="Narration" subtitle="Description recorded with this voucher" />
                    <div className="rr-vch-narration">
                        <i className="bi bi-quote" />
                        <p>{voucher.remarks || "No narration was entered for this voucher."}</p>
                    </div>
                </section>

                <section className="rr-vch-show-card rr-vch-audit-card">
                    <ShowCardHeader icon="bi-shield-check" title="Account Transaction Audit" subtitle="Immutable debit, credit and reversal trail" />
                    <div className="rr-vch-audit-table-wrap">
                        <table className="rr-vch-audit-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Transaction Type</th>
                                    <th>Nature</th>
                                    <th className="number">Debit</th>
                                    <th className="number">Credit</th>
                                    <th>Remarks</th>
                                </tr>
                            </thead>
                            <tbody>
                                {transactions.length ? (
                                    transactions.map((transaction) => (
                                        <tr key={transaction.id} className={transaction.is_reversal ? "reversal" : "original"}>
                                            <td>{date(transaction.date)}</td>
                                            <td><strong>{label(transaction.type)}</strong></td>
                                            <td>
                                                <span className={`rr-vch-audit-nature ${transaction.is_reversal ? "reversal" : "posted"}`}>
                                                    <i className={`bi ${transaction.is_reversal ? "bi-arrow-counterclockwise" : "bi-check2"}`} />
                                                    {transaction.is_reversal ? "Reversal" : "Posted"}
                                                </span>
                                            </td>
                                            <td className="number debit">{Number(transaction.debit) > 0 ? money(transaction.debit) : "—"}</td>
                                            <td className="number credit">{Number(transaction.credit) > 0 ? money(transaction.credit) : "—"}</td>
                                            <td>{transaction.remarks || "—"}</td>
                                        </tr>
                                    ))
                                ) : (
                                    <tr><td colSpan="6" className="empty">No account transaction is linked to this voucher.</td></tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </section>

                <section className="rr-vch-audit-footer">
                    <div>
                        <i className="bi bi-person-check" />
                        <span><small>Created By</small><strong>{voucher.created_by || "System"}</strong></span>
                    </div>
                    <div>
                        <i className="bi bi-clock-history" />
                        <span><small>Created At</small><strong>{dateTime(voucher.created_at)}</strong></span>
                    </div>
                    <div>
                        <i className="bi bi-fingerprint" />
                        <span><small>Transaction ID</small><strong>{activeTransaction?.id ? `#${activeTransaction.id}` : "—"}</strong></span>
                    </div>
                </section>
            </main>

            <ConfirmModal
                show={cancelOpen}
                title="Cancel Voucher"
                message={`Cancel ${voucher.voucher_no}? This creates a reversing account entry. The voucher and its original transaction will remain visible for audit.`}
                confirmText="Cancel Voucher"
                cancelText="Keep Voucher"
                icon="bi-x-octagon"
                variant="danger"
                processing={processing}
                onConfirm={cancelVoucher}
                onCancel={() => !processing && setCancelOpen(false)}
            />
        </>
    );
}

function ShowMetric({ tone, icon, label: text, value, note }) {
    return (
        <article className={`rr-vch-show-metric ${tone}`}>
            <span><i className={`bi ${icon}`} /></span>
            <div><small>{text}</small><strong>{value}</strong>{note && <p>{note}</p>}</div>
        </article>
    );
}

function ShowCardHeader({ icon, title, subtitle }) {
    return (
        <header className="rr-vch-show-card-head">
            <i className={`bi ${icon}`} />
            <span><strong>{title}</strong><small>{subtitle}</small></span>
        </header>
    );
}

function Detail({ label: text, value, strong = false, badge = "", amount = "" }) {
    return (
        <div>
            <dt>{text}</dt>
            <dd className={`${strong ? "strong" : ""} ${amount ? `amount ${amount}` : ""}`}>
                {badge ? <span className={`rr-vch-detail-badge ${badge}`}>{value}</span> : value || "—"}
            </dd>
        </div>
    );
}

function money(value) {
    return new Intl.NumberFormat("en-IN", {
        style: "currency",
        currency: "INR",
        minimumFractionDigits: 2,
    }).format(Number(value ?? 0));
}

function date(value) {
    if (!value) return "—";
    const raw = String(value).slice(0, 10);
    const [year, month, day] = raw.split("-");
    return year && month && day ? `${day}-${month}-${year}` : raw;
}

function dateTime(value) {
    if (!value) return "—";
    const parsed = new Date(value);
    if (Number.isNaN(parsed.getTime())) return value;
    return parsed.toLocaleString("en-IN", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
}

function label(value) {
    return String(value ?? "")
        .replaceAll("_", " ")
        .replace(/\b\w/g, (character) => character.toUpperCase());
}

Show.layout = (page) => <BusinessLayout>{page}</BusinessLayout>;

