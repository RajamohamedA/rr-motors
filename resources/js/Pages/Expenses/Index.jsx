import React, { useMemo, useState } from "react";
import { Head, Link, router, usePage } from "@inertiajs/react";

import BusinessLayout from "@/Layouts/BusinessLayout";
import MasterNavigation from "../../Components/Business/MasterNavigation";
import ConfirmModal from "../../Components/ConfirmModal";

import "../../../css/business/expenses.css";

export default function Index({
    expenses = {},
    accounts = [],
    categories = [],
    filters = {},
    summary = {},
    context = {},
}) {
    const { auth = {}, flash = {} } = usePage().props;
    const permissions = auth?.user?.permissions ?? [];
    const roles = auth?.user?.roles ?? [];
    const privileged =
        Boolean(auth?.user?.is_super_user) ||
        roles.includes("Company Admin") ||
        roles.includes("Super Admin");

    const can = (permission) => privileged || permissions.includes(permission);

    const [form, setForm] = useState({
        search: filters.search ?? "",
        account_id: filters.account_id ? String(filters.account_id) : "",
        expense_category: filters.expense_category ?? "",
        period: filters.period ?? "today",
        status: filters.status ?? "all",
        from_date: filters.from_date ?? "",
        to_date: filters.to_date ?? "",
        per_page: String(filters.per_page ?? 20),
    });
    const [filtersOpen, setFiltersOpen] = useState(true);
    const [selectedExpense, setSelectedExpense] = useState(null);
    const [processing, setProcessing] = useState(false);

    const rows = expenses?.data ?? [];
    const hasFilters = useMemo(
        () =>
            form.search.trim() !== "" ||
            form.account_id !== "" ||
            form.expense_category !== "" ||
            form.period !== "today" ||
            form.status !== "all" ||
            form.from_date !== "" ||
            form.to_date !== "" ||
            form.per_page !== "20",
        [form],
    );

    const update = (key, value) => {
        setForm((current) => ({ ...current, [key]: value }));
    };

    const applyFilters = (event) => {
        event.preventDefault();

        router.get("/expenses", cleanParams(form), {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    };

    const clearFilters = () => {
        const cleared = {
            search: "",
            account_id: "",
            expense_category: "",
            period: "today",
            status: "all",
            from_date: "",
            to_date: "",
            per_page: "20",
        };

        setForm(cleared);
        router.get("/expenses", { period: "today" }, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    };

    const applyPeriod = (period) => {
        const next = {
            ...form,
            period,
            from_date: "",
            to_date: "",
        };

        setForm(next);
        router.get("/expenses", cleanParams(next), {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    };

    const cancelExpense = () => {
        if (!selectedExpense || processing) return;

        setProcessing(true);
        router.patch(
            `/expenses/${selectedExpense.id}/cancel`,
            {},
            {
                preserveScroll: true,
                onFinish: () => {
                    setProcessing(false);
                    setSelectedExpense(null);
                },
            },
        );
    };

    return (
        <>
            <Head title="Expenses" />

            <main className="rr-exp-page rr-exp-index-page">
                <MasterNavigation
                    active="expenses"
                    title="Expense Register"
                    subtitle={
                        context.store_name
                            ? `Operating expenses • ${context.store_name}`
                            : "Branch expense and Cash/Bank payment register"
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

                <section className="rr-exp-commandbar">
                    <div className="rr-exp-command-copy">
                        <span className="rr-exp-eyebrow">EXPENSE CONTROL</span>
                        <h1>Cash & Bank Expenses</h1>
                        <p>
                            Monitor branch operating costs with account-level audit and safe
                            reversal controls.
                        </p>
                    </div>

                    {can("expense.create") && (
                        <Link href="/expenses/create" className="rr-exp-btn primary">
                            <i className="bi bi-plus-lg" />
                            Record Expense
                        </Link>
                    )}
                </section>

                <section className="rr-exp-summary-grid" aria-label="Expense summary">
                    <SummaryCard
                        tone="spent"
                        icon="bi-wallet2"
                        label={`${summary.period_label ?? "Today"} Expenses`}
                        value={money(summary.completed_total)}
                        note="Click to view the selected period"
                        onClick={() => applyPeriod(summary.period ?? "today")}
                    />
                    <SummaryCard
                        tone="cancelled"
                        icon="bi-arrow-counterclockwise"
                        label="Cancelled Value"
                        value={money(summary.cancelled_total)}
                        note="Reversed account entries"
                    />
                    <SummaryCard
                        tone="records"
                        icon="bi-receipt"
                        label="Expense Records"
                        value={number(summary.total_count)}
                        note="Matching current filters"
                    />
                    <SummaryCard
                        tone="average"
                        icon="bi-calculator"
                        label="Average Expense"
                        value={money(
                            Number(summary.total_count ?? 0) > 0
                                ? Number(summary.completed_total ?? 0) /
                                Number(summary.total_count ?? 1)
                                : 0,
                        )}
                        note="Completed total per record"
                    />
                </section>

                <section className={`rr-exp-filter-card ${filtersOpen ? "open" : ""}`}>
                    <button
                        type="button"
                        className="rr-exp-filter-head"
                        onClick={() => setFiltersOpen((value) => !value)}
                        aria-expanded={filtersOpen}
                    >
                        <span className="rr-exp-filter-title">
                            <i className="bi bi-funnel" />
                            <span>
                                <strong>Expense Filters</strong>
                                <small>Search by category, account, date or status</small>
                            </span>
                        </span>

                        <span className="rr-exp-filter-state">
                            {hasFilters && <em>Active</em>}
                            <i
                                className={`bi ${filtersOpen ? "bi-chevron-up" : "bi-chevron-down"
                                    }`}
                            />
                        </span>
                    </button>

                    {filtersOpen && (
                        <form className="rr-exp-filter-body" onSubmit={applyFilters}>
                            <div className="rr-exp-quick-periods">
                                <span>
                                    <i className="bi bi-lightning-charge" />
                                    Quick Period
                                </span>
                                <div>
                                    {[
                                        ["today", "Today", "bi-calendar-day"],
                                        ["yesterday", "Yesterday", "bi-calendar-minus"],
                                        ["month", "This Month", "bi-calendar3"],
                                        ["all", "All Time", "bi-infinity"],
                                    ].map(([value, text, icon]) => (
                                        <button
                                            key={value}
                                            type="button"
                                            className={form.period === value ? "active" : ""}
                                            onClick={() => applyPeriod(value)}
                                        >
                                            <i className={`bi ${icon}`} />
                                            {text}
                                        </button>
                                    ))}
                                </div>
                            </div>

                            <Field label="Search" icon="bi-search" wide>
                                <input
                                    type="search"
                                    value={form.search}
                                    onChange={(event) => update("search", event.target.value)}
                                    placeholder="Expense no, payee, reference or remarks"
                                />
                            </Field>

                            <Field label="Account" icon="bi-bank">
                                <select
                                    value={form.account_id}
                                    onChange={(event) => update("account_id", event.target.value)}
                                >
                                    <option value="">All Accounts</option>
                                    {accounts.map((account) => (
                                        <option key={account.id} value={account.id}>
                                            {account.name} ({account.code})
                                        </option>
                                    ))}
                                </select>
                            </Field>

                            <Field label="Category" icon="bi-tags">
                                <select
                                    value={form.expense_category}
                                    onChange={(event) =>
                                        update("expense_category", event.target.value)
                                    }
                                >
                                    <option value="">All Categories</option>
                                    {categories.map((category) => (
                                        <option key={category} value={category}>
                                            {category}
                                        </option>
                                    ))}
                                </select>
                            </Field>

                            <Field label="Status" icon="bi-check2-circle">
                                <select
                                    value={form.status}
                                    onChange={(event) => update("status", event.target.value)}
                                >
                                    <option value="all">All Statuses</option>
                                    <option value="Completed">Completed</option>
                                    <option value="Cancelled">Cancelled</option>
                                </select>
                            </Field>

                            <Field label="From Date" icon="bi-calendar3">
                                <input
                                    type="date"
                                    value={form.from_date}
                                    onChange={(event) =>
                                        setForm((current) => ({
                                            ...current,
                                            period: "custom",
                                            from_date: event.target.value,
                                        }))
                                    }
                                    onClick={openDatePicker}
                                    onFocus={openDatePicker}
                                />
                            </Field>

                            <Field label="To Date" icon="bi-calendar-check">
                                <input
                                    type="date"
                                    value={form.to_date}
                                    min={form.from_date || undefined}
                                    onChange={(event) =>
                                        setForm((current) => ({
                                            ...current,
                                            period: "custom",
                                            to_date: event.target.value,
                                        }))
                                    }
                                    onClick={openDatePicker}
                                    onFocus={openDatePicker}
                                />
                            </Field>

                            <Field label="Rows" icon="bi-list-ol">
                                <select
                                    value={form.per_page}
                                    onChange={(event) => update("per_page", event.target.value)}
                                >
                                    {[10, 20, 50, 100].map((size) => (
                                        <option key={size} value={size}>
                                            {size} rows
                                        </option>
                                    ))}
                                </select>
                            </Field>

                            <div className="rr-exp-filter-actions">
                                <button type="submit" className="rr-exp-btn filter">
                                    <i className="bi bi-search" />
                                    Apply Filters
                                </button>
                                <button
                                    type="button"
                                    className="rr-exp-btn clear"
                                    onClick={clearFilters}
                                    disabled={!hasFilters}
                                >
                                    <i className="bi bi-arrow-counterclockwise" />
                                    Reset
                                </button>
                            </div>
                        </form>
                    )}
                </section>

                <section className="rr-exp-register-card">
                    <header className="rr-exp-register-head">
                        <div>
                            <span className="rr-exp-register-icon">
                                <i className="bi bi-journal-minus" />
                            </span>
                            <span>
                                <strong>Expense Register</strong>
                                <small>
                                    {summary.period_label ?? "Today"} • {expenses?.total ?? rows.length} record
                                    {(expenses?.total ?? rows.length) === 1 ? "" : "s"}
                                </small>
                            </span>
                        </div>

                        <span className="rr-exp-register-key">
                            <em className="completed" /> Completed
                            <em className="cancelled" /> Cancelled
                        </span>
                    </header>

                    <div className="rr-exp-table-wrap">
                        <table className="rr-exp-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Expense No.</th>
                                    <th>Category</th>
                                    <th>Paid To</th>
                                    <th>Payment Account</th>
                                    <th>Mode / Reference</th>
                                    <th className="number">Amount</th>
                                    <th>Status</th>
                                    <th className="actions">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {rows.length ? (
                                    rows.map((expense) => (
                                        <ExpenseRow
                                            key={expense.id}
                                            expense={expense}
                                            canShow={can("expense.show") || can("expense.view")}
                                            canPrint={can("expense.print")}
                                            canCancel={can("expense.delete")}
                                            onCancel={() => setSelectedExpense(expense)}
                                        />
                                    ))
                                ) : (
                                    <tr>
                                        <td colSpan="9">
                                            <EmptyState canCreate={can("expense.create")} />
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>

                    <Pagination links={expenses?.links ?? []} />
                </section>
            </main>

            <ConfirmModal
                show={Boolean(selectedExpense)}
                title="Cancel Expense"
                message={
                    selectedExpense
                        ? `Cancel ${selectedExpense.expense_no}? The Cash/Bank entry will be reversed while the audit history remains available.`
                        : ""
                }
                confirmText="Cancel Expense"
                cancelText="Keep Expense"
                icon="bi-x-octagon"
                variant="danger"
                processing={processing}
                onConfirm={cancelExpense}
                onCancel={() => !processing && setSelectedExpense(null)}
            />
        </>
    );
}

function SummaryCard({ tone, icon, label, value, note, onClick }) {
    return (
        <article
            className={`rr-exp-summary-card ${tone} ${onClick ? "clickable" : ""}`}
            role={onClick ? "button" : undefined}
            tabIndex={onClick ? 0 : undefined}
            onClick={onClick}
            onKeyDown={(event) => {
                if (onClick && (event.key === "Enter" || event.key === " ")) {
                    event.preventDefault();
                    onClick();
                }
            }}
        >
            <span className="rr-exp-summary-icon">
                <i className={`bi ${icon}`} />
            </span>
            <div>
                <small>{label}</small>
                <strong>{value}</strong>
                <p>{note}</p>
            </div>
            <span className="rr-exp-summary-glow" />
        </article>
    );
}

function Field({ label, icon, wide = false, children }) {
    return (
        <label className={`rr-exp-field ${wide ? "wide" : ""}`}>
            <span>
                <i className={`bi ${icon}`} />
                {label}
            </span>
            {children}
        </label>
    );
}

function ExpenseRow({
    expense,
    canShow,
    canPrint,
    canCancel,
    onCancel,
}) {
    const cancelled = expense.is_cancelled || expense.status === "Cancelled";
    const showUrl = `/expenses/${expense.id}`;

    const openRow = (event) => {
        if (!canShow || event.target.closest("a, button")) return;
        router.visit(showUrl);
    };

    return (
        <tr className={cancelled ? "cancelled" : "completed"} onClick={openRow}>
            <td>
                <span className="rr-exp-date">{date(expense.expense_date)}</span>
            </td>
            <td>
                {canShow ? (
                    <Link href={showUrl} className="rr-exp-number">
                        {expense.expense_no}
                    </Link>
                ) : (
                    <span className="rr-exp-number">{expense.expense_no}</span>
                )}
            </td>
            <td>
                <span className="rr-exp-category">
                    <i className="bi bi-tag" />
                    {expense.expense_category}
                </span>
            </td>
            <td>{expense.payee_name || "General Expense"}</td>
            <td>
                <strong className="rr-exp-account">
                    {expense.account?.name ?? "—"}
                </strong>
                <small>{expense.account?.code ?? ""}</small>
            </td>
            <td>
                <strong>{label(expense.payment_mode)}</strong>
                <small>{expense.reference_no || "No reference"}</small>
            </td>
            <td className="number amount">{money(expense.amount)}</td>
            <td>
                <span
                    className={`rr-exp-status ${cancelled ? "cancelled" : "completed"}`}
                >
                    <i
                        className={`bi ${cancelled ? "bi-x-circle" : "bi-check-circle"}`}
                    />
                    {expense.status}
                </span>
            </td>
            <td className="actions">
                <div className="rr-exp-row-actions">
                    {canShow && (
                        <Link href={showUrl} title="View expense" className="view">
                            <i className="bi bi-eye" />
                        </Link>
                    )}

                    {canPrint && (
                        <a
                            href={`/expenses/${expense.id}/print?paper=a4`}
                            target="_blank"
                            rel="noreferrer"
                            title="Print expense"
                            className="print"
                        >
                            <i className="bi bi-printer" />
                        </a>
                    )}

                    {canCancel && !cancelled && (
                        <button
                            type="button"
                            onClick={onCancel}
                            title="Cancel expense"
                            className="cancel"
                        >
                            <i className="bi bi-x-lg" />
                        </button>
                    )}
                </div>
            </td>
        </tr>
    );
}

function EmptyState({ canCreate }) {
    return (
        <div className="rr-exp-empty">
            <span>
                <i className="bi bi-wallet2" />
            </span>
            <strong>No expenses found</strong>
            <p>Change the filters or record the branch&apos;s first expense.</p>
            {canCreate && (
                <Link href="/expenses/create" className="rr-exp-btn primary">
                    <i className="bi bi-plus-lg" />
                    Record Expense
                </Link>
            )}
        </div>
    );
}

function Pagination({ links }) {
    if (!Array.isArray(links) || links.length <= 3) return null;

    return (
        <nav className="rr-exp-pagination" aria-label="Expense pages">
            {links.map((link, index) =>
                link.url ? (
                    <Link
                        key={`${link.label}-${index}`}
                        href={link.url}
                        preserveScroll
                        preserveState
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

function cleanParams(values) {
    return Object.fromEntries(
        Object.entries(values).filter(
            ([, value]) => value !== "" && value !== null,
        ),
    );
}

function openDatePicker(event) {
    if (typeof event.currentTarget?.showPicker !== "function") return;

    try {
        event.currentTarget.showPicker();
    } catch {
        // Some browsers permit showPicker only during a direct pointer action.
    }
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

    const [year, month, day] = String(value).slice(0, 10).split("-");

    return year && month && day ? `${day}-${month}-${year}` : value;
}

function label(value) {
    return String(value ?? "")
        .replaceAll("_", " ")
        .replace(/\b\w/g, (character) => character.toUpperCase());
}

Index.layout = (page) => <BusinessLayout>{page}</BusinessLayout>;
