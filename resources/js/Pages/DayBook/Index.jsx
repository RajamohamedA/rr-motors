import React, { useMemo, useState } from "react";
import { Head, Link, router } from "@inertiajs/react";

import BusinessLayout from "@/Layouts/BusinessLayout";
import MasterNavigation from "../../Components/Business/MasterNavigation";

import "../../../css/business/day-book.css";

export default function Index({
    rows = [],
    filters = {},
    summary = {},
    expense_details: expenseDetails = {},
    expense_categories: expenseCategories = [],
    context = {},
}) {
    const [mode, setMode] = useState(filters.mode ?? "daily");
    const [selectedDate, setSelectedDate] = useState(filters.date ?? localDate());
    const [selectedMonth, setSelectedMonth] = useState(
        filters.month ?? localDate().slice(0, 7),
    );
    const [expenseFilters, setExpenseFilters] = useState({
        expense_category: filters.expense_category ?? "",
        expense_search: filters.expense_search ?? "",
    });
    const [paper, setPaper] = useState("a4");
    const [expensesOpen, setExpensesOpen] = useState(true);

    const activeExpenseDate = expenseDetails.selected_date ?? null;
    const finalProfit = Number(summary.final_profit ?? 0);
    const profitPositive = finalProfit >= 0;

    const reportParams = useMemo(
        () => ({
            mode,
            date: selectedDate,
            month: selectedMonth,
            ...expenseFilters,
        }),
        [mode, selectedDate, selectedMonth, expenseFilters],
    );

    const exportQuery = new URLSearchParams(
        cleanParams({ ...reportParams, paper }),
    ).toString();

    const visit = (next, options = {}) => {
        router.get("/accounts/day-book", cleanParams(next), {
            preserveState: true,
            preserveScroll: true,
            replace: true,
            ...options,
        });
    };

    const switchMode = (nextMode) => {
        setMode(nextMode);
        visit({ ...reportParams, mode: nextMode, detail_date: null });
    };

    const applyPeriod = (value) => {
        if (mode === "monthly") {
            setSelectedMonth(value);
            visit({ ...reportParams, month: value, detail_date: null });
            return;
        }

        setSelectedDate(value);
        visit({ ...reportParams, date: value, detail_date: null });
    };

    const selectExpenseDate = (date) => {
        setExpensesOpen(true);
        visit(
            {
                ...reportParams,
                detail_date: activeExpenseDate === date ? null : date,
            },
            { preserveScroll: true },
        );
    };

    const applyExpenseFilters = (event) => {
        event.preventDefault();
        visit({
            ...reportParams,
            ...expenseFilters,
            detail_date: activeExpenseDate,
        });
    };

    const clearExpenseFilters = () => {
        const next = { expense_category: "", expense_search: "" };
        setExpenseFilters(next);
        visit({ ...reportParams, ...next, detail_date: activeExpenseDate });
    };

    return (
        <>
            <Head title="Day Book" />

            <main className="rr-day-page">
                <MasterNavigation
                    active="day-book"
                    title="Day Book"
                    subtitle={
                        context.store_name
                            ? `Daily and monthly performance • ${context.store_name}`
                            : "Sales, purchases, expenses and profit in one view"
                    }
                />

                <section className="rr-day-commandbar">
                    <div className="rr-day-command-copy">
                        <span>BUSINESS PERFORMANCE</span>
                        <h1>Day Book Control Centre</h1>
                        <p>
                            Monitor turnover, purchasing, operating costs, mechanic commission,
                            cash movement, and final profit.
                        </p>
                    </div>

                    <div className="rr-day-command-actions">
                        <div className="rr-day-paper-toggle">
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
                            className="rr-day-export print"
                            href={`/accounts/day-book/print?${exportQuery}`}
                            target="_blank"
                            rel="noreferrer"
                        >
                            <i className="bi bi-printer" /> Print
                        </a>
                        <a
                            className="rr-day-export pdf"
                            href={`/accounts/day-book/pdf?${exportQuery}`}
                        >
                            <i className="bi bi-file-earmark-pdf" /> PDF
                        </a>
                        <a
                            className="rr-day-export csv"
                            href={`/accounts/day-book/excel?${exportQuery}`}
                        >
                            <i className="bi bi-file-earmark-spreadsheet" /> CSV
                        </a>
                    </div>
                </section>

                <section className="rr-day-periodbar">
                    <div className="rr-day-mode-switch">
                        <button
                            type="button"
                            className={mode === "daily" ? "active daily" : "daily"}
                            onClick={() => switchMode("daily")}
                        >
                            <i className="bi bi-calendar-day" />
                            <span><strong>Daily Book</strong><small>One selected business day</small></span>
                        </button>
                        <button
                            type="button"
                            className={mode === "monthly" ? "active monthly" : "monthly"}
                            onClick={() => switchMode("monthly")}
                        >
                            <i className="bi bi-calendar3" />
                            <span><strong>Monthly Book</strong><small>Every day in the selected month</small></span>
                        </button>
                    </div>

                    <label className="rr-day-period-input">
                        <span>{mode === "monthly" ? "Select Month" : "Select Date"}</span>
                        <input
                            type={mode === "monthly" ? "month" : "date"}
                            value={mode === "monthly" ? selectedMonth : selectedDate}
                            onChange={(event) => applyPeriod(event.target.value)}
                            onClick={showPicker}
                            onFocus={showPicker}
                        />
                    </label>
                </section>

                <section className="rr-day-primary-grid">
                    <MetricCard
                        tone="sales"
                        icon="bi-receipt-cutoff"
                        label="Sales Amount"
                        value={money(summary.sales_amount)}
                        note={`${number(summary.sales_count)} sales bill(s)`}
                    />
                    <MetricCard
                        tone="purchase"
                        icon="bi-box-seam"
                        label="Purchase Amount"
                        value={money(summary.purchase_amount)}
                        note={`${number(summary.purchase_count)} purchase bill(s)`}
                    />
                    <MetricCard
                        tone="expense"
                        icon="bi-wallet2"
                        label="Total Expenses"
                        value={money(summary.expense_amount)}
                        note={`${number(summary.expense_count)} expense record(s)`}
                        onClick={() => setExpensesOpen(true)}
                    />
                    <MetricCard
                        tone="commission"
                        icon="bi-tools"
                        label="Mechanic Commission"
                        value={money(summary.mechanic_commission)}
                        note="Commission earned for selected period"
                    />
                </section>

                <section className="rr-day-profit-flow">
                    <ProfitStep
                        tone="gross"
                        icon="bi-graph-up-arrow"
                        label="Gross Profit"
                        value={money(summary.gross_profit)}
                        note={`Cost of goods: ${money(summary.cost_total)}`}
                    />
                    <FlowArrow />
                    <ProfitStep
                        tone="commission"
                        icon="bi-dash-circle"
                        label="After Discount & Commission"
                        value={money(summary.net_profit_before_expenses)}
                        note="Before operating expenses"
                    />
                    <FlowArrow />
                    <ProfitStep
                        tone={profitPositive ? "final-positive" : "final-negative"}
                        icon={profitPositive ? "bi-trophy" : "bi-exclamation-triangle"}
                        label={profitPositive ? "Final Profit" : "Final Loss"}
                        value={money(summary.final_profit)}
                        note={`After expenses: ${money(summary.expense_amount)}`}
                        featured
                    />
                </section>

                {Number(summary.missing_cost_lines ?? 0) > 0 && (
                    <div className="rr-day-cost-warning">
                        <i className="bi bi-exclamation-triangle" />
                        <span>
                            <strong>Profit requires attention.</strong>
                            {" "}{number(summary.missing_cost_lines)} sale item(s) do not have a
                            historical cost snapshot.
                        </span>
                    </div>
                )}

                <section className="rr-day-flow-grid">
                    <article className="rr-day-flow-card money-in">
                        <i className="bi bi-arrow-down-left-circle" />
                        <div><small>Money In</small><strong>{money(summary.money_in)}</strong></div>
                    </article>
                    <article className="rr-day-flow-card money-out">
                        <i className="bi bi-arrow-up-right-circle" />
                        <div><small>Money Out</small><strong>{money(summary.money_out)}</strong></div>
                    </article>
                    <article className="rr-day-flow-card net-flow">
                        <i className="bi bi-arrow-left-right" />
                        <div>
                            <small>Net Cash Movement</small>
                            <strong>{money(Number(summary.money_in ?? 0) - Number(summary.money_out ?? 0))}</strong>
                        </div>
                    </article>
                </section>

                <section className="rr-day-register">
                    <header>
                        <div>
                            <span><i className="bi bi-calendar2-week" /></span>
                            <div>
                                <strong>{mode === "monthly" ? "Monthly Day-wise Register" : "Daily Business Register"}</strong>
                                <small>{date(filters.from_date)} to {date(filters.to_date)}</small>
                            </div>
                        </div>
                        <em>{number(rows.length)} day(s)</em>
                    </header>

                    <div className="rr-day-table-wrap">
                        <table className="rr-day-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th className="number sales">Sales</th>
                                    <th className="number purchase">Purchases</th>
                                    <th className="number expense">Expenses</th>
                                    <th className="number commission">Commission</th>
                                    <th className="number gross">Gross Profit</th>
                                    <th className="number final">Final Profit</th>
                                    <th className="number money-in">Money In</th>
                                    <th className="number money-out">Money Out</th>
                                    <th className="action">Details</th>
                                </tr>
                            </thead>
                            <tbody>
                                {rows.length ? rows.map((row) => (
                                    <DayRow
                                        key={row.date}
                                        row={row}
                                        selected={activeExpenseDate === row.date}
                                        onExpenses={() => selectExpenseDate(row.date)}
                                    />
                                )) : (
                                    <tr><td colSpan="10"><EmptyState /></td></tr>
                                )}
                            </tbody>
                            {rows.length > 0 && (
                                <tfoot>
                                    <tr>
                                        <td>PERIOD TOTAL</td>
                                        <td className="number">{money(summary.sales_amount)}</td>
                                        <td className="number">{money(summary.purchase_amount)}</td>
                                        <td className="number">{money(summary.expense_amount)}</td>
                                        <td className="number">{money(summary.mechanic_commission)}</td>
                                        <td className="number">{money(summary.gross_profit)}</td>
                                        <td className="number">{money(summary.final_profit)}</td>
                                        <td className="number">{money(summary.money_in)}</td>
                                        <td className="number">{money(summary.money_out)}</td>
                                        <td />
                                    </tr>
                                </tfoot>
                            )}
                        </table>
                    </div>
                </section>

                <section className={`rr-day-expenses ${expensesOpen ? "open" : ""}`}>
                    <button
                        type="button"
                        className="rr-day-expense-head"
                        onClick={() => setExpensesOpen((current) => !current)}
                    >
                        <span>
                            <i className="bi bi-receipt" />
                            <span>
                                <strong>Expense Details</strong>
                                <small>
                                    {activeExpenseDate
                                        ? `Selected day: ${date(activeExpenseDate)}`
                                        : "All expenses in the report period"}
                                </small>
                            </span>
                        </span>
                        <span>
                            <em>{money(expenseDetails.total_amount)}</em>
                            <i className={`bi ${expensesOpen ? "bi-chevron-up" : "bi-chevron-down"}`} />
                        </span>
                    </button>

                    {expensesOpen && (
                        <div className="rr-day-expense-body">
                            <form className="rr-day-expense-filters" onSubmit={applyExpenseFilters}>
                                <label>
                                    <span>Category</span>
                                    <select
                                        value={expenseFilters.expense_category}
                                        onChange={(event) => setExpenseFilters((current) => ({
                                            ...current,
                                            expense_category: event.target.value,
                                        }))}
                                    >
                                        <option value="">All categories</option>
                                        {expenseCategories.map((category) => (
                                            <option key={category} value={category}>{category}</option>
                                        ))}
                                    </select>
                                </label>
                                <label className="search">
                                    <span>Search Expense</span>
                                    <input
                                        type="search"
                                        value={expenseFilters.expense_search}
                                        placeholder="Number, payee, reference or remarks"
                                        onChange={(event) => setExpenseFilters((current) => ({
                                            ...current,
                                            expense_search: event.target.value,
                                        }))}
                                    />
                                </label>
                                <button type="submit" className="apply"><i className="bi bi-search" /> Apply</button>
                                <button type="button" className="reset" onClick={clearExpenseFilters}><i className="bi bi-arrow-counterclockwise" /> Reset</button>
                                {activeExpenseDate && (
                                    <button
                                        type="button"
                                        className="all-days"
                                        onClick={() => visit({ ...reportParams, detail_date: null })}
                                    >
                                        <i className="bi bi-calendar3" /> All Days
                                    </button>
                                )}
                            </form>

                            <div className="rr-day-expense-grid">
                                {(expenseDetails.data ?? []).length ? (
                                    expenseDetails.data.map((expense) => (
                                        <ExpenseItem key={expense.id} expense={expense} />
                                    ))
                                ) : (
                                    <div className="rr-day-expense-empty">
                                        <i className="bi bi-check2-circle" />
                                        <strong>No expenses found</strong>
                                        <span>No expense records match the selected period and filters.</span>
                                    </div>
                                )}
                            </div>
                        </div>
                    )}
                </section>
            </main>
        </>
    );
}

function MetricCard({ tone, icon, label, value, note, onClick }) {
    return (
        <article
            className={`rr-day-metric ${tone} ${onClick ? "clickable" : ""}`}
            onClick={onClick}
            role={onClick ? "button" : undefined}
            tabIndex={onClick ? 0 : undefined}
            onKeyDown={(event) => {
                if (onClick && (event.key === "Enter" || event.key === " ")) onClick();
            }}
        >
            <span><i className={`bi ${icon}`} /></span>
            <div><small>{label}</small><strong>{value}</strong><p>{note}</p></div>
        </article>
    );
}

function ProfitStep({ tone, icon, label, value, note, featured = false }) {
    return (
        <article className={`rr-day-profit-step ${tone} ${featured ? "featured" : ""}`}>
            <i className={`bi ${icon}`} />
            <div><small>{label}</small><strong>{value}</strong><span>{note}</span></div>
        </article>
    );
}

function FlowArrow() {
    return <span className="rr-day-flow-arrow"><i className="bi bi-chevron-right" /></span>;
}

function DayRow({ row, selected, onExpenses }) {
    const positive = Number(row.final_profit) >= 0;

    return (
        <tr className={selected ? "selected" : ""}>
            <td><strong>{date(row.date)}</strong><small>{row.day_label}</small></td>
            <td className="number sales"><strong>{money(row.sales_amount)}</strong><small>{number(row.sales_count)} bill(s)</small></td>
            <td className="number purchase"><strong>{money(row.purchase_amount)}</strong><small>{number(row.purchase_count)} bill(s)</small></td>
            <td className="number expense"><strong>{money(row.expense_amount)}</strong><small>{number(row.expense_count)} item(s)</small></td>
            <td className="number commission">{money(row.mechanic_commission)}</td>
            <td className="number gross">{money(row.gross_profit)}</td>
            <td className={`number final ${positive ? "positive" : "negative"}`}>{money(row.final_profit)}</td>
            <td className="number money-in">{money(row.money_in)}</td>
            <td className="number money-out">{money(row.money_out)}</td>
            <td className="action">
                <button type="button" onClick={onExpenses} title="View expenses">
                    <i className="bi bi-receipt" />
                    Expenses
                </button>
            </td>
        </tr>
    );
}

function ExpenseItem({ expense }) {
    return (
        <Link href={expense.show_url} className="rr-day-expense-item">
            <span className="icon"><i className="bi bi-receipt-cutoff" /></span>
            <div className="main">
                <span><strong>{expense.category}</strong><em>{expense.expense_no}</em></span>
                <p>{expense.payee_name || expense.remarks || "General business expense"}</p>
                <small>{date(expense.date)} • {expense.account_name || expense.payment_mode}</small>
            </div>
            <strong className="amount">{money(expense.amount)}</strong>
            <i className="bi bi-chevron-right arrow" />
        </Link>
    );
}

function EmptyState() {
    return (
        <div className="rr-day-empty">
            <i className="bi bi-calendar-x" />
            <strong>No business activity found</strong>
            <span>Select another date or month.</span>
        </div>
    );
}

function cleanParams(values) {
    return Object.fromEntries(
        Object.entries(values).filter(([, value]) => value !== "" && value !== null && value !== undefined),
    );
}

function showPicker(event) {
    if (typeof event.currentTarget?.showPicker !== "function") return;
    try { event.currentTarget.showPicker(); } catch { /* browser restriction */ }
}

function localDate() {
    const value = new Date();
    value.setMinutes(value.getMinutes() - value.getTimezoneOffset());
    return value.toISOString().slice(0, 10);
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

Index.layout = (page) => <BusinessLayout>{page}</BusinessLayout>;
