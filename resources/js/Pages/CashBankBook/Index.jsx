import React, { useMemo, useState } from "react";
import { Head, Link, router } from "@inertiajs/react";

import BusinessLayout from "@/Layouts/BusinessLayout";
import MasterNavigation from "../../Components/Business/MasterNavigation";

import "../../../css/business/cash-bank-book.css";

export default function Index({
    transactions = {},
    accounts = [],
    filters = {},
    summary = {},
    transaction_types: transactionTypes = [],
    context = {},
}) {
    const [form, setForm] = useState({
        account_id: filters.account_id ? String(filters.account_id) : "",
        from_date: filters.from_date ?? "",
        to_date: filters.to_date ?? "",
        transaction_type: filters.transaction_type ?? "",
        search: filters.search ?? "",
        per_page: String(filters.per_page ?? 20),
    });
    const [paper, setPaper] = useState("a4");
    const [filtersOpen, setFiltersOpen] = useState(true);

    const rows = transactions?.data ?? [];
    const selectedAccount = useMemo(
        () =>
            accounts.find((account) => String(account.id) === form.account_id) ?? null,
        [accounts, form.account_id],
    );

    const hasFilters =
        form.account_id !== "" ||
        form.transaction_type !== "" ||
        form.search.trim() !== "" ||
        form.per_page !== "20";

    const update = (key, value) => {
        setForm((current) => ({ ...current, [key]: value }));
    };

    const visit = (next, replace = true) => {
        router.get("/accounts/cash-bank-book", cleanParams(next), {
            preserveState: true,
            preserveScroll: true,
            replace,
        });
    };

    const submit = (event) => {
        event.preventDefault();
        visit(form);
    };

    const reset = () => {
        const today = localDate();
        const next = {
            account_id: "",
            from_date: monthStart(today),
            to_date: today,
            transaction_type: "",
            search: "",
            per_page: "20",
        };

        setForm(next);
        visit(next);
    };

    const applyPeriod = (period) => {
        const today = localDate();
        let fromDate = today;
        let toDate = today;

        if (period === "yesterday") {
            fromDate = shiftDate(today, -1);
            toDate = fromDate;
        }

        if (period === "month") {
            fromDate = monthStart(today);
        }

        if (period === "financial-year") {
            const year = Number(today.slice(0, 4));
            const month = Number(today.slice(5, 7));
            fromDate = `${month >= 4 ? year : year - 1}-04-01`;
        }

        const next = { ...form, from_date: fromDate, to_date: toDate };
        setForm(next);
        visit(next);
    };

    const exportQuery = new URLSearchParams(
        cleanParams({ ...form, paper }),
    ).toString();

    return (
        <>
            <Head title="Cash / Bank Book" />

            <main className="rr-cbb-page">
                <MasterNavigation
                    active="cash-bank-book"
                    title="Cash / Bank Book"
                    subtitle={
                        context.store_name
                            ? `Account movement register • ${context.store_name}`
                            : "Cash, bank, UPI and card movement register"
                    }
                />

                <section className="rr-cbb-commandbar">
                    <div className="rr-cbb-command-copy">
                        <span>ACCOUNTING CONTROL</span>
                        <h1>Cash &amp; Bank Book</h1>
                        <p>
                            Track every receipt and payment with account-wise running and
                            closing balances.
                        </p>
                    </div>

                    <div className="rr-cbb-command-actions">
                        <div className="rr-cbb-paper-toggle" aria-label="Export paper size">
                            {["a4", "a5"].map((size) => (
                                <button
                                    key={size}
                                    type="button"
                                    className={paper === size ? "active" : ""}
                                    onClick={() => setPaper(size)}
                                >
                                    {size.toUpperCase()}
                                </button>
                            ))}
                        </div>

                        <a
                            className="rr-cbb-action print"
                            href={`/accounts/cash-bank-book/print?${exportQuery}`}
                            target="_blank"
                            rel="noreferrer"
                        >
                            <i className="bi bi-printer" />
                            Print
                        </a>
                        <a
                            className="rr-cbb-action pdf"
                            href={`/accounts/cash-bank-book/pdf?${exportQuery}`}
                        >
                            <i className="bi bi-file-earmark-pdf" />
                            PDF
                        </a>
                        <a
                            className="rr-cbb-action csv"
                            href={`/accounts/cash-bank-book/excel?${exportQuery}`}
                        >
                            <i className="bi bi-file-earmark-spreadsheet" />
                            CSV
                        </a>
                    </div>
                </section>

                <section className="rr-cbb-summary-grid">
                    <SummaryCard
                        tone="opening"
                        icon="bi-wallet2"
                        label="Opening Balance"
                        value={money(summary.opening_balance)}
                        note={selectedAccount?.name ?? "All active accounts"}
                    />
                    <SummaryCard
                        tone="income"
                        icon="bi-arrow-down-left"
                        label="Money In"
                        value={money(summary.money_in)}
                        note="Receipts and deposits"
                    />
                    <SummaryCard
                        tone="outgoing"
                        icon="bi-arrow-up-right"
                        label="Money Out"
                        value={money(summary.money_out)}
                        note="Payments and withdrawals"
                    />
                    <SummaryCard
                        tone="closing"
                        icon="bi-bank"
                        label="Closing Balance"
                        value={money(summary.closing_balance)}
                        note={`${number(summary.accounts_count)} account(s)`}
                    />
                </section>

                <section className="rr-cbb-account-strip">
                    <header className="rr-cbb-account-filter-head">
                        <span>
                            <i className="bi bi-wallet2" />
                            <span>
                                <strong>Account Filter</strong>
                                <small>Select one account or view the combined book</small>
                            </span>
                        </span>
                        <em>{selectedAccount?.name ?? "All Accounts"}</em>
                    </header>

                    <div className="rr-cbb-account-options">
                        <button
                            type="button"
                            className={form.account_id === "" ? "active all" : "all"}
                            data-label={"All Accounts\nCombined Cash & Bank Book"}
                            aria-label="All Accounts - Combined Cash and Bank Book"
                            onClick={() => {
                                const next = { ...form, account_id: "" };
                                setForm(next);
                                visit(next);
                            }}
                        >
                            <i className="bi bi-grid" />
                            <div className="rr-cbb-account-copy rr-cbb-all-copy">
                                <strong>All Accounts</strong>
                                <small>Combined Cash &amp; Bank Book</small>
                            </div>
                        </button>

                        {accounts.map((account) => (
                            <button
                                key={account.id}
                                type="button"
                                data-label={`${account.name}\n${account.code} • ${label(account.type)}`}
                                aria-label={`${account.name} - ${account.code} - ${label(account.type)}`}
                                className={`${accountTone(account.type)} ${form.account_id === String(account.id) ? "active" : ""
                                    }`}
                                onClick={() => {
                                    const next = { ...form, account_id: String(account.id) };
                                    setForm(next);
                                    visit(next);
                                }}
                            >
                                <i className={`bi ${accountIcon(account.type)}`} />
                                <div className="rr-cbb-account-copy">
                                    <strong>{account.name}</strong>
                                    <small>{account.code} • {label(account.type)}</small>
                                </div>
                            </button>
                        ))}
                    </div>
                </section>

                <section className={`rr-cbb-filter ${filtersOpen ? "open" : ""}`}>
                    <button
                        className="rr-cbb-filter-head"
                        type="button"
                        onClick={() => setFiltersOpen((current) => !current)}
                        aria-expanded={filtersOpen}
                    >
                        <span>
                            <i className="bi bi-funnel" />
                            <span>
                                <strong>Book Filters</strong>
                                <small>Filter transactions by period, type and reference</small>
                            </span>
                        </span>
                        <span>
                            {hasFilters && <em>Active</em>}
                            <i className={`bi ${filtersOpen ? "bi-chevron-up" : "bi-chevron-down"}`} />
                        </span>
                    </button>

                    {filtersOpen && (
                        <div className="rr-cbb-filter-content">
                            <div className="rr-cbb-periods">
                                <span>Quick Period</span>
                                <div>
                                    <PeriodButton text="Today" onClick={() => applyPeriod("today")} />
                                    <PeriodButton text="Yesterday" onClick={() => applyPeriod("yesterday")} />
                                    <PeriodButton text="This Month" onClick={() => applyPeriod("month")} />
                                    <PeriodButton text="Financial Year" onClick={() => applyPeriod("financial-year")} />
                                </div>
                            </div>

                            <form className="rr-cbb-filter-form" onSubmit={submit}>
                                <Field label="From Date" icon="bi-calendar3">
                                    <input
                                        type="date"
                                        value={form.from_date}
                                        onChange={(event) => update("from_date", event.target.value)}
                                        onClick={showDatePicker}
                                        onFocus={showDatePicker}
                                    />
                                </Field>
                                <Field label="To Date" icon="bi-calendar-check">
                                    <input
                                        type="date"
                                        value={form.to_date}
                                        onChange={(event) => update("to_date", event.target.value)}
                                        onClick={showDatePicker}
                                        onFocus={showDatePicker}
                                    />
                                </Field>
                                <Field label="Transaction Type" icon="bi-arrow-left-right">
                                    <select
                                        value={form.transaction_type}
                                        onChange={(event) => update("transaction_type", event.target.value)}
                                    >
                                        <option value="">All transaction types</option>
                                        {transactionTypes.map((type) => (
                                            <option key={type.value} value={type.value}>{type.label}</option>
                                        ))}
                                    </select>
                                </Field>
                                <Field label="Search" icon="bi-search" wide>
                                    <input
                                        type="search"
                                        value={form.search}
                                        onChange={(event) => update("search", event.target.value)}
                                        placeholder="Reference, remarks or account"
                                    />
                                </Field>
                                <Field label="Rows" icon="bi-list-ol">
                                    <select
                                        value={form.per_page}
                                        onChange={(event) => update("per_page", event.target.value)}
                                    >
                                        {[10, 20, 50, 100].map((size) => (
                                            <option key={size} value={size}>{size} rows</option>
                                        ))}
                                    </select>
                                </Field>

                                <div className="rr-cbb-filter-actions">
                                    <button type="submit" className="apply">
                                        <i className="bi bi-search" /> Apply
                                    </button>
                                    <button type="button" className="reset" onClick={reset}>
                                        <i className="bi bi-arrow-counterclockwise" /> Reset
                                    </button>
                                </div>
                            </form>
                        </div>
                    )}
                </section>

                <section className="rr-cbb-register">
                    <header>
                        <div>
                            <span><i className="bi bi-journal-bookmark-fill" /></span>
                            <div>
                                <strong>{selectedAccount?.name ?? "Combined Account Register"}</strong>
                                <small>
                                    {date(form.from_date)} to {date(form.to_date)} • {number(transactions.total ?? rows.length)} transaction(s)
                                </small>
                            </div>
                        </div>
                        <div className="rr-cbb-register-balance">
                            <small>Closing Balance</small>
                            <strong>{money(summary.closing_balance)}</strong>
                        </div>
                    </header>

                    <div className="rr-cbb-table-wrap">
                        <table className="rr-cbb-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Account</th>
                                    <th>Transaction</th>
                                    <th>Reference</th>
                                    <th>Particulars</th>
                                    <th className="number">Money In</th>
                                    <th className="number">Money Out</th>
                                    <th className="number">Balance</th>
                                </tr>
                            </thead>
                            <tbody>
                                {rows.length ? (
                                    rows.map((row, index) => (
                                        <TransactionRow
                                            key={row.id}
                                            row={row}
                                            serial={(transactions.from ?? 1) + index}
                                        />
                                    ))
                                ) : (
                                    <tr>
                                        <td colSpan="9">
                                            <div className="rr-cbb-empty">
                                                <i className="bi bi-journal-x" />
                                                <strong>No transactions found</strong>
                                                <span>Change the account, period, or filter selection.</span>
                                            </div>
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>

                    <Pagination links={transactions.links ?? []} />
                </section>
            </main>
        </>
    );
}

function SummaryCard({ tone, icon, label: text, value, note }) {
    return (
        <article className={`rr-cbb-summary ${tone}`}>
            <span><i className={`bi ${icon}`} /></span>
            <div><small>{text}</small><strong>{value}</strong><p>{note}</p></div>
        </article>
    );
}

function PeriodButton({ text, onClick }) {
    return <button type="button" onClick={onClick}>{text}</button>;
}

function Field({ label: text, icon, wide = false, children }) {
    return (
        <label className={`rr-cbb-field ${wide ? "wide" : ""}`}>
            <span><i className={`bi ${icon}`} />{text}</span>
            {children}
        </label>
    );
}

function TransactionRow({ row, serial }) {
    return (
        <tr className={row.is_reversal ? "reversal" : ""}>
            <td>{serial}</td>
            <td>{date(row.date)}</td>
            <td>
                <strong className={`rr-cbb-account-name ${accountTone(row.account_type)}`}>
                    {row.account_name ?? "—"}
                </strong>
                <small>{row.account_code ?? "—"}</small>
            </td>
            <td>
                <span className={`rr-cbb-type ${typeTone(row.transaction_type)}`}>
                    {row.transaction_type_label}
                </span>
            </td>
            <td>
                <strong>{row.reference_no || "—"}</strong>
                {row.external_reference && <small>{row.external_reference}</small>}
            </td>
            <td>{row.remarks || label(row.payment_mode) || "—"}</td>
            <td className="number money-in">{row.debit > 0 ? money(row.debit) : "—"}</td>
            <td className="number money-out">{row.credit > 0 ? money(row.credit) : "—"}</td>
            <td className={`number running ${Number(row.running_balance) < 0 ? "negative" : ""}`}>
                {money(row.running_balance)}
            </td>
        </tr>
    );
}

function Pagination({ links }) {
    if (!Array.isArray(links) || links.length <= 3) return null;

    return (
        <nav className="rr-cbb-pagination">
            {links.map((link, index) =>
                link.url ? (
                    <Link
                        key={`${link.label}-${index}`}
                        href={link.url}
                        preserveState
                        preserveScroll
                        className={link.active ? "active" : ""}
                        dangerouslySetInnerHTML={{ __html: link.label }}
                    />
                ) : (
                    <span
                        key={`${link.label}-${index}`}
                        className="disabled"
                        dangerouslySetInnerHTML={{ __html: link.label }}
                    />
                ),
            )}
        </nav>
    );
}

function accountTone(type) {
    if (type === "card") return "account-card";
    return ["cash", "bank", "upi"].includes(type) ? type : "other";
}

function accountIcon(type) {
    return {
        cash: "bi-cash-stack",
        bank: "bi-bank",
        upi: "bi-phone",
        card: "bi-credit-card-2-front",
    }[type] ?? "bi-wallet2";
}

function typeTone(type = "") {
    if (["sale", "customer_receipt", "receipt_voucher"].includes(type)) return "receipt";
    if (["supplier_payment", "mechanic_payment", "expense", "payment_voucher"].includes(type)) return "payment";
    if (type.includes("reversal")) return "reversal";
    return "neutral";
}

function cleanParams(values) {
    return Object.fromEntries(
        Object.entries(values).filter(([, value]) => value !== "" && value !== null && value !== undefined),
    );
}

function showDatePicker(event) {
    if (typeof event.currentTarget?.showPicker !== "function") return;
    try { event.currentTarget.showPicker(); } catch { /* browser restriction */ }
}

function localDate() {
    const value = new Date();
    value.setMinutes(value.getMinutes() - value.getTimezoneOffset());
    return value.toISOString().slice(0, 10);
}

function shiftDate(value, days) {
    const dateValue = new Date(`${value}T00:00:00`);
    dateValue.setDate(dateValue.getDate() + days);
    return [dateValue.getFullYear(), String(dateValue.getMonth() + 1).padStart(2, "0"), String(dateValue.getDate()).padStart(2, "0")].join("-");
}

function monthStart(value) { return `${value.slice(0, 7)}-01`; }

function money(value) {
    return new Intl.NumberFormat("en-IN", { style: "currency", currency: "INR", minimumFractionDigits: 2 }).format(Number(value ?? 0));
}

function number(value) { return new Intl.NumberFormat("en-IN").format(Number(value ?? 0)); }

function label(value) {
    if (!value) return "";
    return String(value).replaceAll("_", " ").replace(/\b\w/g, (character) => character.toUpperCase());
}

function date(value) {
    if (!value) return "—";
    const [year, month, day] = String(value).slice(0, 10).split("-");
    return year && month && day ? `${day}-${month}-${year}` : value;
}

Index.layout = (page) => <BusinessLayout>{page}</BusinessLayout>;
