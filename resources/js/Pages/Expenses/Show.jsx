import React, { useState } from "react";
import { Head, Link, router, usePage } from "@inertiajs/react";

import BusinessLayout from "@/Layouts/BusinessLayout";
import MasterNavigation from "../../Components/Business/MasterNavigation";
import ConfirmModal from "../../Components/ConfirmModal";

import "../../../css/business/expenses.css";

export default function Show({ expense, context = {} }) {
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

    const cancelled = expense?.is_cancelled || expense?.status === "Cancelled";
    const transactions = expense?.transactions ?? [];

    const cancelExpense = () => {
        if (processing || cancelled) return;

        setProcessing(true);
        router.patch(
            `/expenses/${expense.id}/cancel`,
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
            <Head title={expense?.expense_no ?? "Expense Details"} />

            <main className="rr-exp-page rr-exp-show-page">
                <MasterNavigation
                    active="expenses"
                    title="Expense Details"
                    subtitle={
                        context.store_name
                            ? `${expense.expense_no} • ${context.store_name}`
                            : expense.expense_no
                    }
                />

                {(flash.success || flash.error) && (
                    <div
                        className={`rr-exp-alert ${flash.error ? "error" : "success"}`}
                        role="alert"
                    >
                        <i
                            className={`bi ${flash.error ? "bi-exclamation-octagon" : "bi-check-circle"
                                }`}
                        />
                        <span>{flash.error ?? flash.success}</span>
                    </div>
                )}

                <section className="rr-exp-detail-hero">
                    <div className="rr-exp-detail-identity">
                        <span className={`rr-exp-document-icon ${cancelled ? "cancelled" : ""}`}>
                            <i className="bi bi-receipt-cutoff" />
                        </span>
                        <div>
                            <span className="rr-exp-eyebrow">EXPENSE DOCUMENT</span>
                            <h1>{expense.expense_no}</h1>
                            <p>
                                {expense.expense_category} • {date(expense.expense_date)}
                            </p>
                        </div>
                    </div>

                    <div className="rr-exp-detail-actions">
                        <div className="rr-exp-paper-switch" aria-label="Paper size">
                            {[
                                ["a4", "A4"],
                                ["a5", "A5"],
                            ].map(([value, text]) => (
                                <button
                                    key={value}
                                    type="button"
                                    className={paper === value ? "active" : ""}
                                    onClick={() => setPaper(value)}
                                >
                                    {text}
                                </button>
                            ))}
                        </div>

                        {can("expense.print") && (
                            <>
                                <a
                                    href={`/expenses/${expense.id}/print?paper=${paper}`}
                                    target="_blank"
                                    rel="noreferrer"
                                    className="rr-exp-btn print"
                                >
                                    <i className="bi bi-printer" />
                                    Print {paper.toUpperCase()}
                                </a>
                                <a
                                    href={`/expenses/${expense.id}/pdf?paper=${paper}`}
                                    className="rr-exp-btn pdf"
                                >
                                    <i className="bi bi-file-earmark-pdf" />
                                    PDF
                                </a>
                            </>
                        )}

                        {can("expense.delete") && !cancelled && (
                            <button
                                type="button"
                                className="rr-exp-btn danger"
                                onClick={() => setCancelOpen(true)}
                            >
                                <i className="bi bi-x-octagon" />
                                Cancel
                            </button>
                        )}
                    </div>
                </section>

                {cancelled && (
                    <section className="rr-exp-cancelled-banner">
                        <i className="bi bi-x-octagon-fill" />
                        <div>
                            <strong>This expense is cancelled</strong>
                            <span>
                                Cancelled by {expense.cancelled_by || "System"}
                                {expense.cancelled_at
                                    ? ` on ${dateTime(expense.cancelled_at)}`
                                    : ""}
                            </span>
                        </div>
                    </section>
                )}

                <section className="rr-exp-detail-summary">
                    <Summary
                        tone="amount"
                        icon="bi-currency-rupee"
                        label="Expense Amount"
                        value={money(expense.amount)}
                    />
                    <Summary
                        tone="account"
                        icon="bi-bank"
                        label="Payment Account"
                        value={expense.account?.name ?? "—"}
                        note={expense.account?.code}
                    />
                    <Summary
                        tone="mode"
                        icon="bi-credit-card"
                        label="Payment Mode"
                        value={label(expense.payment_mode)}
                    />
                    <Summary
                        tone={cancelled ? "cancelled" : "status"}
                        icon={cancelled ? "bi-x-circle" : "bi-check-circle"}
                        label="Document Status"
                        value={expense.status}
                    />
                </section>

                <div className="rr-exp-detail-grid">
                    <section className="rr-exp-detail-card">
                        <CardHeader
                            icon="bi-card-checklist"
                            title="Expense Information"
                            text="Document and payee details"
                            tone="orange"
                        />

                        <dl className="rr-exp-detail-list">
                            <Detail label="Expense Number" value={expense.expense_no} />
                            <Detail label="Expense Date" value={date(expense.expense_date)} />
                            <Detail label="Category" value={expense.expense_category} />
                            <Detail label="Paid To" value={expense.payee_name || "General Expense"} />
                            <Detail label="Reference" value={expense.reference_no || "—"} />
                            <Detail label="Created By" value={expense.created_by || "System"} />
                        </dl>
                    </section>

                    <section className="rr-exp-detail-card">
                        <CardHeader
                            icon="bi-bank"
                            title="Account Posting"
                            text="Cash/Bank impact of this expense"
                            tone="blue"
                        />

                        <dl className="rr-exp-detail-list">
                            <Detail
                                label="Account Name"
                                value={expense.account?.name ?? "—"}
                            />
                            <Detail
                                label="Account Code"
                                value={expense.account?.code ?? "—"}
                            />
                            <Detail
                                label="Account Type"
                                value={label(expense.account?.type) || "—"}
                            />
                            <Detail
                                label="Payment Mode"
                                value={label(expense.payment_mode)}
                            />
                            <Detail label="Accounting Side" value="Credit (Money Out)" />
                            <Detail label="Posted Amount" value={money(expense.amount)} strong />
                        </dl>
                    </section>
                </div>

                <section className="rr-exp-narration-card">
                    <CardHeader
                        icon="bi-chat-left-text"
                        title="Narration / Remarks"
                        text="Supporting description for this expense"
                        tone="green"
                    />
                    <p>{expense.remarks || "No remarks were entered for this expense."}</p>
                </section>

                <section className="rr-exp-audit-card">
                    <header className="rr-exp-register-head">
                        <div>
                            <span className="rr-exp-register-icon">
                                <i className="bi bi-shield-check" />
                            </span>
                            <span>
                                <strong>Account Audit Trail</strong>
                                <small>{transactions.length} posting entries</small>
                            </span>
                        </div>
                    </header>

                    <div className="rr-exp-table-wrap">
                        <table className="rr-exp-table audit">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Transaction Type</th>
                                    <th>Remarks</th>
                                    <th className="number">Debit</th>
                                    <th className="number">Credit</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                {transactions.length ? (
                                    transactions.map((transaction) => (
                                        <tr
                                            key={transaction.id}
                                            className={transaction.is_reversal ? "cancelled" : "completed"}
                                        >
                                            <td>{date(transaction.date)}</td>
                                            <td>{label(transaction.type)}</td>
                                            <td>{transaction.remarks || "—"}</td>
                                            <td className="number debit">
                                                {Number(transaction.debit) > 0
                                                    ? money(transaction.debit)
                                                    : "—"}
                                            </td>
                                            <td className="number credit">
                                                {Number(transaction.credit) > 0
                                                    ? money(transaction.credit)
                                                    : "—"}
                                            </td>
                                            <td>
                                                <span
                                                    className={`rr-exp-status ${transaction.is_reversal ? "cancelled" : "completed"
                                                        }`}
                                                >
                                                    {transaction.is_reversal ? "Reversal" : "Posted"}
                                                </span>
                                            </td>
                                        </tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td colSpan="6">
                                            <div className="rr-exp-empty compact">
                                                <strong>No account posting found</strong>
                                                <p>Verify that the expense was posted successfully.</p>
                                            </div>
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </section>

                <footer className="rr-exp-show-footer">
                    <Link href="/expenses" className="rr-exp-btn secondary">
                        <i className="bi bi-arrow-left" />
                        Back to Register
                    </Link>
                </footer>
            </main>

            <ConfirmModal
                show={cancelOpen}
                title="Cancel Expense"
                message={`Cancel ${expense.expense_no}? A debit reversal of ${money(
                    expense.amount,
                )} will be posted back to ${expense.account?.name ?? "the account"}.`}
                confirmText="Cancel Expense"
                cancelText="Keep Expense"
                icon="bi-x-octagon"
                variant="danger"
                processing={processing}
                onConfirm={cancelExpense}
                onCancel={() => !processing && setCancelOpen(false)}
            />
        </>
    );
}

function Summary({ tone, icon, label: title, value, note }) {
    return (
        <article className={`rr-exp-detail-summary-card ${tone}`}>
            <span>
                <i className={`bi ${icon}`} />
            </span>
            <div>
                <small>{title}</small>
                <strong>{value}</strong>
                {note && <p>{note}</p>}
            </div>
        </article>
    );
}

function CardHeader({ icon, title, text, tone }) {
    return (
        <header className={`rr-exp-form-card-head ${tone}`}>
            <span>
                <i className={`bi ${icon}`} />
            </span>
            <div>
                <strong>{title}</strong>
                <small>{text}</small>
            </div>
        </header>
    );
}

function Detail({ label: title, value, strong = false }) {
    return (
        <div>
            <dt>{title}</dt>
            <dd className={strong ? "strong" : ""}>{value || "—"}</dd>
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
    const [year, month, day] = String(value).slice(0, 10).split("-");
    return year && month && day ? `${day}-${month}-${year}` : value;
}

function dateTime(value) {
    if (!value) return "—";
    return new Intl.DateTimeFormat("en-IN", {
        dateStyle: "medium",
        timeStyle: "short",
    }).format(new Date(value));
}

function label(value) {
    return String(value ?? "")
        .replaceAll("_", " ")
        .replace(/\b\w/g, (character) => character.toUpperCase());
}

Show.layout = (page) => <BusinessLayout>{page}</BusinessLayout>;
