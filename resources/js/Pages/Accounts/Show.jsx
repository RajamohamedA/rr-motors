import React, { useMemo, useState } from "react";
import { Head, Link, router, usePage } from "@inertiajs/react";

import BusinessLayout from "@/Layouts/BusinessLayout";
import MasterNavigation from "../../Components/Business/MasterNavigation";

import "../../../css/business/accounts.css";

export default function Show({
    account,
    transactions = {},
    summary = {},
    filters = {},
    transactionTypes = {},
    context = {},
}) {
    const { auth = {} } = usePage().props;
    const permissions = auth?.user?.permissions ?? [];
    const isPrivileged =
        Boolean(auth?.user?.is_super_user) ||
        (auth?.user?.roles ?? []).includes("Company Admin");
    const canEdit = isPrivileged || permissions.includes("account.edit");

    const [form, setForm] = useState({
        from_date: filters.from_date ?? "",
        to_date: filters.to_date ?? "",
        transaction_type: filters.transaction_type ?? "all",
        search: filters.search ?? "",
        per_page: String(filters.per_page ?? 50),
    });

    const rows = transactions?.data ?? [];
    const hasFilters = useMemo(
        () =>
            form.search.trim() !== "" ||
            form.transaction_type !== "all" ||
            form.from_date !== (filters.from_date ?? "") ||
            form.to_date !== (filters.to_date ?? "") ||
            form.per_page !== "50",
        [form, filters.from_date, filters.to_date],
    );

    const applyFilters = (event) => {
        event.preventDefault();
        router.get(`/accounts/${account.id}`, cleanParams(form), {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    };

    const clearFilters = () => {
        router.get(
            `/accounts/${account.id}`,
            {},
            {
                preserveState: false,
                preserveScroll: true,
                replace: true,
            },
        );
    };

    return (
        <>
            <Head title={account.account_name} />

            <main className="rr-acc-page rr-acc-show-page">
                <MasterNavigation
                    active="accounts"
                    title="Account Details"
                    subtitle={
                        context.store_name
                            ? `${context.company_name ?? "Company"} • ${context.store_name}`
                            : "Cash and Bank Account"
                    }
                />

                <section className="rr-acc-commandbar">
                    <div className="rr-acc-command-title">
                        <span className="rr-acc-command-icon">
                            <i className={`bi ${typeIcon(account.account_type)}`} />
                        </span>
                        <div>
                            <span className="rr-acc-eyebrow">
                                {account.account_code} • {account.account_type_label}
                            </span>
                            <h1>{account.account_name}</h1>
                            <p>{accountSubtitle(account)}</p>
                        </div>
                    </div>

                    <div className="rr-acc-command-actions">
                        <Link className="rr-acc-btn filter" href="/accounts">
                            <i className="bi bi-arrow-left" />
                            Account Register
                        </Link>
                        {canEdit && (
                            <Link
                                className="rr-acc-btn primary"
                                href={`/accounts/${account.id}/edit`}
                            >
                                <i className="bi bi-pencil-square" />
                                Edit Account
                            </Link>
                        )}
                    </div>
                </section>

                <section className="rr-acc-summary-grid rr-acc-show-summary">
                    <SummaryCard
                        tone="navy"
                        icon="bi-box-arrow-in-right"
                        label="Opening Period Balance"
                        value={money(summary.opening_balance)}
                        note={`As of ${date(filters.from_date)}`}
                    />
                    <SummaryCard
                        tone="green"
                        icon="bi-arrow-down-left-circle"
                        label="Money In (Debit)"
                        value={money(summary.period_debit)}
                        note="Receipts and account increases"
                    />
                    <SummaryCard
                        tone="violet"
                        icon="bi-arrow-up-right-circle"
                        label="Money Out (Credit)"
                        value={money(summary.period_credit)}
                        note="Payments and account decreases"
                    />
                    <SummaryCard
                        tone={Number(summary.closing_balance) < 0 ? "red" : "blue"}
                        icon="bi-wallet2"
                        label="Closing Balance"
                        value={money(summary.closing_balance)}
                        note={`Up to ${date(filters.to_date)}`}
                    />
                </section>

                <div className="rr-acc-detail-grid">
                    <aside className="rr-acc-detail-card rr-acc-account-profile">
                        <CardHeading
                            icon="bi-info-square"
                            title="Account Information"
                            text="Master details for this payment account."
                        />

                        <div className="rr-acc-account-identity">
                            <span className={`rr-acc-profile-icon ${account.account_type}`}>
                                <i className={`bi ${typeIcon(account.account_type)}`} />
                            </span>
                            <div>
                                <strong>{account.account_name}</strong>
                                <small>{account.account_code}</small>
                            </div>
                            <span
                                className={`rr-acc-status ${account.status ? "active" : "inactive"
                                    }`}
                            >
                                <i className="bi bi-circle-fill" />
                                {account.status ? "Active" : "Inactive"}
                            </span>
                        </div>

                        <dl className="rr-acc-definition-list">
                            <Detail label="Account Type" value={account.account_type_label} />
                            <Detail
                                label="Payment Channel"
                                value={paymentMode(account.payment_mode)}
                            />
                            <Detail label="Bank Name" value={account.bank_name} />
                            <Detail
                                label="Account Number"
                                value={account.masked_account_number}
                            />
                            <Detail label="UPI ID" value={account.upi_id} />
                            <Detail
                                label="Opening Balance"
                                value={money(account.opening_balance)}
                            />
                            <Detail
                                label="Opening Date"
                                value={date(account.opening_balance_date)}
                            />
                            <Detail
                                label="Current Balance"
                                value={money(account.current_balance)}
                                strong
                            />
                            <Detail
                                label="Account Source"
                                value={account.is_system ? "System Account" : "Manual Account"}
                            />
                        </dl>
                    </aside>

                    <section className="rr-acc-ledger-column">
                        <form className="rr-acc-filter-panel" onSubmit={applyFilters}>
                            <div className="rr-acc-panel-heading">
                                <div>
                                    <i className="bi bi-funnel" />
                                    <span>Transaction Filters</span>
                                </div>
                                <small>Filter this account’s transaction history</small>
                            </div>

                            <div className="rr-acc-ledger-filter-grid">
                                <Field label="From Date" icon="bi-calendar3">
                                    <input
                                        type="date"
                                        value={form.from_date}
                                        max={form.to_date || undefined}
                                        onClick={openDatePicker}
                                        onFocus={openDatePicker}
                                        onChange={(event) =>
                                            setForm((current) => ({
                                                ...current,
                                                from_date: event.target.value,
                                            }))
                                        }
                                    />
                                </Field>

                                <Field label="To Date" icon="bi-calendar3">
                                    <input
                                        type="date"
                                        value={form.to_date}
                                        min={form.from_date || undefined}
                                        onClick={openDatePicker}
                                        onFocus={openDatePicker}
                                        onChange={(event) =>
                                            setForm((current) => ({
                                                ...current,
                                                to_date: event.target.value,
                                            }))
                                        }
                                    />
                                </Field>

                                <Field label="Transaction Type" icon="bi-tag">
                                    <select
                                        value={form.transaction_type}
                                        onChange={(event) =>
                                            setForm((current) => ({
                                                ...current,
                                                transaction_type: event.target.value,
                                            }))
                                        }
                                    >
                                        <option value="all">All transaction types</option>
                                        {Object.entries(transactionTypes).map(([value, label]) => (
                                            <option key={value} value={value}>
                                                {label}
                                            </option>
                                        ))}
                                    </select>
                                </Field>

                                <Field label="Search" icon="bi-search" wide>
                                    <input
                                        type="search"
                                        value={form.search}
                                        placeholder="Reference number, external reference or remarks"
                                        onChange={(event) =>
                                            setForm((current) => ({
                                                ...current,
                                                search: event.target.value,
                                            }))
                                        }
                                    />
                                </Field>

                                <Field label="Rows" icon="bi-list-ol">
                                    <select
                                        value={form.per_page}
                                        onChange={(event) =>
                                            setForm((current) => ({
                                                ...current,
                                                per_page: event.target.value,
                                            }))
                                        }
                                    >
                                        <option value="20">20 rows</option>
                                        <option value="50">50 rows</option>
                                        <option value="100">100 rows</option>
                                    </select>
                                </Field>
                            </div>

                            <div className="rr-acc-filter-actions">
                                <button
                                    type="button"
                                    className="rr-acc-btn secondary"
                                    onClick={clearFilters}
                                    disabled={!hasFilters}
                                >
                                    <i className="bi bi-arrow-counterclockwise" /> Reset
                                </button>
                                <button type="submit" className="rr-acc-btn apply">
                                    <i className="bi bi-search" /> Apply Filters
                                </button>
                            </div>
                        </form>

                        <section className="rr-acc-register rr-acc-ledger-register">
                            <header className="rr-acc-register-head">
                                <div>
                                    <i className="bi bi-journal-text" />
                                    <div>
                                        <h2>Account Transactions</h2>
                                        <p>
                                            Showing {number(transactions.from)}–
                                            {number(transactions.to)} of {number(transactions.total)}
                                        </p>
                                    </div>
                                </div>
                                <span className="rr-acc-period-label">
                                    {date(filters.from_date)} – {date(filters.to_date)}
                                </span>
                            </header>

                            <div className="rr-acc-table-wrap">
                                <table className="rr-acc-table rr-acc-ledger-table">
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Type / Reference</th>
                                            <th>Remarks</th>
                                            <th className="amount">Money In</th>
                                            <th className="amount">Money Out</th>
                                            <th className="amount">Balance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {rows.length > 0 ? (
                                            rows.map((entry) => (
                                                <TransactionRow key={entry.id} entry={entry} />
                                            ))
                                        ) : (
                                            <tr>
                                                <td colSpan="6">
                                                    <div className="rr-acc-empty rr-acc-ledger-empty">
                                                        <span>
                                                            <i className="bi bi-journal-x" />
                                                        </span>
                                                        <h3>No transactions found</h3>
                                                        <p>
                                                            No entries match the selected period and filters.
                                                        </p>
                                                    </div>
                                                </td>
                                            </tr>
                                        )}
                                    </tbody>
                                </table>
                            </div>

                            <Pagination links={transactions.links ?? []} />
                        </section>
                    </section>
                </div>
            </main>
        </>
    );
}

function SummaryCard({ tone, icon, label, value, note }) {
    return (
        <article className={`rr-acc-summary-card ${tone}`}>
            <span className="rr-acc-summary-icon">
                <i className={`bi ${icon}`} />
            </span>
            <div>
                <small>{label}</small>
                <strong>{value}</strong>
                <p>{note}</p>
            </div>
        </article>
    );
}

function CardHeading({ icon, title, text }) {
    return (
        <header className="rr-acc-card-heading">
            <div>
                <i className={`bi ${icon}`} />
                <div>
                    <h2>{title}</h2>
                    <p>{text}</p>
                </div>
            </div>
        </header>
    );
}

function Detail({ label, value, strong = false }) {
    return (
        <div>
            <dt>{label}</dt>
            <dd className={strong ? "strong" : ""}>{value || "—"}</dd>
        </div>
    );
}

function Field({ label, icon, wide = false, children }) {
    return (
        <label className={`rr-acc-field ${wide ? "wide" : ""}`}>
            <span>
                <i className={`bi ${icon}`} />
                {label}
            </span>
            <div className="rr-acc-input-shell">{children}</div>
        </label>
    );
}

function TransactionRow({ entry }) {
    return (
        <tr className={entry.is_reversal ? "reversal" : ""}>
            <td className="rr-acc-date-cell">
                <strong>{date(entry.transaction_date)}</strong>
                <small>{entry.created_by}</small>
            </td>
            <td>
                <div className="rr-acc-reference-cell">
                    <span
                        className={`rr-acc-entry-icon ${entry.is_reversal ? "reversal" : ""}`}
                    >
                        <i className={`bi ${transactionIcon(entry.transaction_type)}`} />
                    </span>
                    <div>
                        <strong>{entry.type_label}</strong>
                        <small>{entry.reference_no || "No reference"}</small>
                    </div>
                </div>
            </td>
            <td className="rr-acc-remarks-cell">
                <span>{entry.remarks || "—"}</span>
                {entry.external_reference && <small>{entry.external_reference}</small>}
            </td>
            <td className="amount money-in">
                {Number(entry.debit) > 0 ? money(entry.debit) : "—"}
            </td>
            <td className="amount money-out">
                {Number(entry.credit) > 0 ? money(entry.credit) : "—"}
            </td>
            <td
                className={`amount balance ${Number(entry.balance) < 0 ? "negative" : ""}`}
            >
                {money(entry.balance)}
            </td>
        </tr>
    );
}

function Pagination({ links }) {
    if (!Array.isArray(links) || links.length <= 3) return null;

    return (
        <nav className="rr-acc-pagination" aria-label="Transaction pages">
            {links.map((link, index) => (
                <Link
                    key={`${link.label}-${index}`}
                    href={link.url ?? "#"}
                    preserveScroll
                    preserveState
                    className={`${link.active ? "active" : ""} ${!link.url ? "disabled" : ""
                        }`}
                    dangerouslySetInnerHTML={{ __html: link.label }}
                    onClick={(event) => !link.url && event.preventDefault()}
                />
            ))}
        </nav>
    );
}

function accountSubtitle(account) {
    if (account.bank_name) {
        return `${account.bank_name}${account.masked_account_number ? ` • ${account.masked_account_number}` : ""
            }`;
    }

    if (account.upi_id) return account.upi_id;
    return paymentMode(account.payment_mode);
}

function cleanParams(values) {
    return Object.fromEntries(
        Object.entries(values).filter(([, value]) => value !== "" && value != null),
    );
}

function openDatePicker(event) {
    if (typeof event.currentTarget.showPicker !== "function") return;

    try {
        event.currentTarget.showPicker();
    } catch {
        // Browsers without programmatic picker support still open on normal click.
    }
}

function typeIcon(type) {
    return (
        {
            cash: "bi-cash-stack",
            bank: "bi-bank",
            upi: "bi-phone",
            card: "bi-credit-card",
            other: "bi-wallet2",
        }[type] ?? "bi-wallet2"
    );
}

function transactionIcon(type) {
    if (type?.includes("reversal")) return "bi-arrow-counterclockwise";
    if (type?.includes("receipt") || type === "sale") return "bi-arrow-down-left";
    if (type?.includes("payment") || type === "expense")
        return "bi-arrow-up-right";
    if (type === "contra") return "bi-arrow-left-right";
    return "bi-journal-text";
}

function paymentMode(value) {
    return (
        {
            cash: "Cash",
            gpay: "GPay / UPI",
            bank_transfer: "Bank Transfer",
            card: "Card",
            cheque: "Cheque",
            other: "Other",
        }[value] ??
        value?.replaceAll("_", " ") ??
        "—"
    );
}

function money(value) {
    return new Intl.NumberFormat("en-IN", {
        style: "currency",
        currency: "INR",
        minimumFractionDigits: 2,
    }).format(Number(value ?? 0));
}

function number(value) {
    return new Intl.NumberFormat("en-IN").format(Number(value ?? 0));
}

function date(value) {
    if (!value) return "—";
    return new Date(`${String(value).slice(0, 10)}T00:00:00`).toLocaleDateString(
        "en-IN",
    );
}

Show.layout = (page) => <BusinessLayout>{page}</BusinessLayout>;
