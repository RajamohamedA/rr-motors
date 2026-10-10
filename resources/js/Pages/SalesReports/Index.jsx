import React, { useMemo, useState } from "react";
import { Head, Link, router } from "@inertiajs/react";

import BusinessLayout from "@/Layouts/BusinessLayout";
import MasterNavigation from "../../Components/Business/MasterNavigation";

import "../../../css/business/sales-report.css";

export default function Index({
    rows = [],
    summary = {},
    sales = {},
    filters = {},
    customers = [],
    mechanics = [],
    context = {},
}) {
    const [form, setForm] = useState({
        mode: filters.mode ?? "daily",
        date: filters.date ?? localDate(),
        month: filters.month ?? localDate().slice(0, 7),
        year: filters.year ?? new Date().getFullYear(),
        from_date: filters.from_date ?? "",
        to_date: filters.to_date ?? "",
        customer_id: filters.customer_id ?? "",
        mechanic_id: filters.mechanic_id ?? "",
        payment_status: filters.payment_status ?? "",
        billing_type: filters.billing_type ?? "",
        sale_source: filters.sale_source ?? "",
        search: filters.search ?? "",
    });
    const [paper, setPaper] = useState("a4");
    const [filtersOpen, setFiltersOpen] = useState(true);

    const update = (field, value) => {
        setForm((current) => ({ ...current, [field]: value }));
    };

    const query = useMemo(
        () => cleanParams({ ...form, paper }),
        [form, paper],
    );
    const exportQuery = new URLSearchParams(query).toString();
    const saleRows = sales?.data ?? [];
    const netProfit = Number(summary.net_profit ?? 0);

    const visit = (next) => {
        router.get("/sales-reports", cleanParams(next), {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    };

    const applyFilters = (event) => {
        event.preventDefault();
        visit(form);
    };

    const switchMode = (mode) => {
        const next = {
            ...form,
            mode,
            from_date: "",
            to_date: "",
        };
        setForm(next);
        visit(next);
    };

    const reset = () => {
        const next = {
            mode: "daily",
            date: localDate(),
            month: localDate().slice(0, 7),
            year: new Date().getFullYear(),
            from_date: "",
            to_date: "",
            customer_id: "",
            mechanic_id: "",
            payment_status: "",
            billing_type: "",
            sale_source: "",
            search: "",
        };
        setForm(next);
        visit(next);
    };

    return (
        <>
            <Head title="Sales Report" />

            <div className="rr-sr-page">
                <MasterNavigation
                    active="sales-reports"
                    title="Sales Report"
                    subtitle={
                        context.store_name
                            ? `${context.store_name} · Revenue, collections and profit intelligence`
                            : "Revenue, collections and profit intelligence"
                    }
                />

                <section className="rr-sr-hero">
                    <div>
                        <span className="rr-sr-eyebrow">
                            <i className="bi bi-graph-up-arrow" />
                            SALES INTELLIGENCE
                        </span>
                        <h1>Sales Performance</h1>
                        <p>
                            Track revenue, returns, collections, outstanding and
                            bill-wise profitability.
                        </p>
                    </div>

                    <div className="rr-sr-export-bar">
                        <label>
                            <span>Paper</span>
                            <select
                                value={paper}
                                onChange={(event) => setPaper(event.target.value)}
                            >
                                <option value="a4">A4</option>
                                <option value="a5">A5</option>
                            </select>
                        </label>
                        <a
                            className="rr-sr-action print"
                            href={`/sales-reports/print?${exportQuery}`}
                            target="_blank"
                            rel="noreferrer"
                        >
                            <i className="bi bi-printer" /> Print
                        </a>
                        <a
                            className="rr-sr-action pdf"
                            href={`/sales-reports/pdf?${exportQuery}`}
                        >
                            <i className="bi bi-file-earmark-pdf" /> PDF
                        </a>
                        <a
                            className="rr-sr-action excel"
                            href={`/sales-reports/excel?${exportQuery}`}
                        >
                            <i className="bi bi-file-earmark-spreadsheet" /> CSV
                        </a>
                    </div>
                </section>

                <section className="rr-sr-period-panel">
                    <div className="rr-sr-period-title">
                        <i className="bi bi-calendar3" />
                        <span>
                            <strong>Reporting Period</strong>
                            <small>Choose sales grouping</small>
                        </span>
                    </div>

                    <div className="rr-sr-mode-tabs">
                        <ModeButton
                            active={form.mode === "daily"}
                            icon="bi-calendar-day"
                            label="Day Wise"
                            onClick={() => switchMode("daily")}
                        />
                        <ModeButton
                            active={form.mode === "monthly"}
                            icon="bi-calendar-month"
                            label="Monthly"
                            onClick={() => switchMode("monthly")}
                        />
                        <ModeButton
                            active={form.mode === "yearly"}
                            icon="bi-calendar-range"
                            label="Yearly"
                            onClick={() => switchMode("yearly")}
                        />
                    </div>

                    <div className="rr-sr-primary-period">
                        {form.mode === "daily" && (
                            <DateControl
                                label="Report Date"
                                type="date"
                                value={form.date}
                                onChange={(value) => update("date", value)}
                            />
                        )}
                        {form.mode === "monthly" && (
                            <DateControl
                                label="Report Month"
                                type="month"
                                value={form.month}
                                onChange={(value) => update("month", value)}
                            />
                        )}
                        {form.mode === "yearly" && (
                            <label className="rr-sr-field">
                                <span>Report Year</span>
                                <input
                                    type="number"
                                    min="2000"
                                    max="2100"
                                    value={form.year}
                                    onChange={(event) => update("year", event.target.value)}
                                />
                            </label>
                        )}
                        <button
                            type="button"
                            className="rr-sr-run"
                            onClick={applyFilters}
                        >
                            <i className="bi bi-play-fill" />
                            Run Report
                        </button>
                    </div>
                </section>

                <section className="rr-sr-summary-grid">
                    <SummaryCard tone="blue" icon="bi-receipt" label="Sales Bills" value={number(summary.bill_count)} note="Completed invoices" />
                    <SummaryCard tone="indigo" icon="bi-currency-rupee" label="Gross Sales" value={money(summary.gross_sales)} note="Before sales returns" />
                    <SummaryCard tone="orange" icon="bi-arrow-return-left" label="Sales Returns" value={money(summary.return_amount)} note="Returned sales value" />
                    <SummaryCard tone="violet" icon="bi-bag-check" label="Net Sales" value={money(summary.net_sales)} note="Sales less returns" />
                    <SummaryCard tone="green" icon="bi-check2-circle" label="Paid Amount" value={money(summary.paid_amount)} note="Settled invoice value" />
                    <SummaryCard tone="red" icon="bi-hourglass-split" label="Outstanding" value={money(summary.outstanding_amount)} note="Customer receivable" />
                    <SummaryCard tone="slate" icon="bi-box-seam" label="Cost of Goods" value={money(summary.cost_total)} note="Historical cost snapshot" />
                    <SummaryCard
                        tone={netProfit >= 0 ? "profit" : "loss"}
                        icon={netProfit >= 0 ? "bi-graph-up" : "bi-graph-down"}
                        label="Net Profit"
                        value={money(netProfit)}
                        note="After discount and commission"
                    />
                </section>

                <section className="rr-sr-flow-strip">
                    <FlowCard tone="cash" icon="bi-cash-stack" label="Cash" value={summary.cash_amount} />
                    <FlowCard tone="bank" icon="bi-bank" label="Bank" value={summary.bank_amount} />
                    <FlowCard tone="upi" icon="bi-phone" label="GPay / UPI" value={summary.upi_amount} />
                    <FlowCard tone="card" icon="bi-credit-card" label="Card" value={summary.card_amount} />
                    <FlowCard tone="commission" icon="bi-person-gear" label="Mechanic Commission" value={summary.commission_amount} />
                    <FlowCard tone="gross" icon="bi-bar-chart" label="Gross Profit" value={summary.gross_profit} />
                </section>

                <section className="rr-sr-filter-card">
                    <button
                        type="button"
                        className="rr-sr-section-head"
                        onClick={() => setFiltersOpen((open) => !open)}
                    >
                        <span>
                            <i className="bi bi-funnel" />
                            <span>
                                <strong>Sales Filters</strong>
                                <small>Filter invoices, customers and mechanics</small>
                            </span>
                        </span>
                        <i className={`bi ${filtersOpen ? "bi-chevron-up" : "bi-chevron-down"}`} />
                    </button>

                    {filtersOpen && (
                        <form className="rr-sr-filter-body" onSubmit={applyFilters}>
                            <label className="rr-sr-field grow">
                                <span>Search</span>
                                <div className="rr-sr-input-icon">
                                    <i className="bi bi-search" />
                                    <input
                                        type="search"
                                        value={form.search}
                                        placeholder="Invoice, customer or mobile"
                                        onChange={(event) => update("search", event.target.value)}
                                    />
                                </div>
                            </label>

                            <SelectField label="Customer" value={form.customer_id} onChange={(value) => update("customer_id", value)}>
                                <option value="">All Customers</option>
                                {customers.map((customer) => (
                                    <option key={customer.id} value={customer.id}>
                                        {customer.customer_name}
                                    </option>
                                ))}
                            </SelectField>

                            <SelectField label="Mechanic" value={form.mechanic_id} onChange={(value) => update("mechanic_id", value)}>
                                <option value="">All Mechanics</option>
                                {mechanics.map((mechanic) => (
                                    <option key={mechanic.id} value={mechanic.id}>
                                        {mechanic.mechanic_name}
                                    </option>
                                ))}
                            </SelectField>

                            <SelectField label="Payment" value={form.payment_status} onChange={(value) => update("payment_status", value)}>
                                <option value="">All Statuses</option>
                                <option value="Unpaid">Unpaid</option>
                                <option value="Partially Paid">Partially Paid</option>
                                <option value="Paid">Paid</option>
                            </SelectField>

                            <SelectField label="Bill Type" value={form.billing_type} onChange={(value) => update("billing_type", value)}>
                                <option value="">All Bills</option>
                                <option value="gst">GST</option>
                                <option value="non_gst">Non-GST</option>
                            </SelectField>

                            <SelectField label="Sale Source" value={form.sale_source} onChange={(value) => update("sale_source", value)}>
                                <option value="">All Sources</option>
                                <option value="customer">Customer</option>
                                <option value="mechanic">Mechanic</option>
                            </SelectField>

                            <DateControl label="Custom From" type="date" value={form.from_date} onChange={(value) => update("from_date", value)} />
                            <DateControl label="Custom To" type="date" value={form.to_date} onChange={(value) => update("to_date", value)} />

                            <div className="rr-sr-filter-actions">
                                <button type="button" onClick={reset}>
                                    <i className="bi bi-arrow-counterclockwise" /> Reset
                                </button>
                                <button type="submit" className="primary">
                                    <i className="bi bi-funnel-fill" /> Apply
                                </button>
                            </div>
                        </form>
                    )}
                </section>

                <section className="rr-sr-table-card">
                    <TableHeader icon="bi-bar-chart-line" title={`${modeLabel(form.mode)} Sales Summary`} subtitle={`${filters.from_date ?? ""} to ${filters.to_date ?? ""}`} />
                    <div className="rr-sr-table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Period</th>
                                    <th className="number">Bills</th>
                                    <th className="number">Gross Sales</th>
                                    <th className="number">Returns</th>
                                    <th className="number">Net Sales</th>
                                    <th className="number">Paid</th>
                                    <th className="number">Outstanding</th>
                                    <th className="number">Profit</th>
                                </tr>
                            </thead>
                            <tbody>
                                {rows.length ? rows.map((row) => (
                                    <tr key={row.key}>
                                        <td className="period"><i className="bi bi-calendar-event" /><strong>{row.label}</strong></td>
                                        <td className="number">{number(row.bill_count)}</td>
                                        <td className="number sales">{money(row.gross_sales)}</td>
                                        <td className="number returns">{money(row.return_amount)}</td>
                                        <td className="number net">{money(row.net_sales)}</td>
                                        <td className="number paid">{money(row.paid_amount)}</td>
                                        <td className="number due">{money(row.outstanding_amount)}</td>
                                        <td className={`number ${Number(row.net_profit) >= 0 ? "profit" : "loss"}`}>{money(row.net_profit)}</td>
                                    </tr>
                                )) : <EmptyRow colSpan="8" />}
                            </tbody>
                        </table>
                    </div>
                </section>

                <section className="rr-sr-table-card invoice-card">
                    <TableHeader icon="bi-receipt-cutoff" title="Sales Invoice Details" subtitle={`${sales?.total ?? saleRows.length} matching invoices`} />
                    <div className="rr-sr-table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th><th>Invoice</th><th>Customer</th><th>Source</th>
                                    <th className="number">Total</th><th className="number">Paid</th>
                                    <th className="number">Balance</th><th className="number">Profit</th>
                                    <th>Status</th><th className="action">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                {saleRows.length ? saleRows.map((sale) => (
                                    <tr key={sale.id}>
                                        <td>{displayDate(sale.invoice_date)}</td>
                                        <td><strong>{sale.invoice_no}</strong></td>
                                        <td>
                                            <div className="rr-sr-customer">
                                                <span>{initials(sale.customer?.name)}</span>
                                                <div><strong>{sale.customer?.name ?? "—"}</strong><small>{sale.customer?.code ?? ""}</small></div>
                                            </div>
                                        </td>
                                        <td><Source sale={sale} /></td>
                                        <td className="number sales">{money(sale.grand_total)}</td>
                                        <td className="number paid">{money(sale.paid_amount)}</td>
                                        <td className="number due">{money(sale.balance_amount)}</td>
                                        <td className={`number ${Number(sale.net_profit) >= 0 ? "profit" : "loss"}`}>{money(sale.net_profit)}</td>
                                        <td><Status value={sale.payment_status} /></td>
                                        <td className="action">
                                            <Link className="rr-sr-view" href={`/sales-reports/${sale.id}`} title="View invoice analysis"><i className="bi bi-eye" /></Link>
                                        </td>
                                    </tr>
                                )) : <EmptyRow colSpan="10" />}
                            </tbody>
                        </table>
                    </div>
                    <Pagination links={sales?.links ?? []} />
                </section>
            </div>
        </>
    );
}

function ModeButton({ active, icon, label, onClick }) {
    return <button type="button" className={active ? "active" : ""} onClick={onClick}><i className={`bi ${icon}`} /><span>{label}</span></button>;
}

function DateControl({ label, type, value, onChange }) {
    const openPicker = (event) => event.currentTarget.showPicker?.();
    return (
        <label className="rr-sr-field">
            <span>{label}</span>
            <div className="rr-sr-date-control">
                <input type={type} value={value} onClick={openPicker} onFocus={openPicker} onChange={(event) => onChange(event.target.value)} />
                <i className="bi bi-calendar3" />
            </div>
        </label>
    );
}

function SelectField({ label, value, onChange, children }) {
    return <label className="rr-sr-field"><span>{label}</span><select value={value} onChange={(event) => onChange(event.target.value)}>{children}</select></label>;
}

function SummaryCard({ tone, icon, label, value, note }) {
    return <article className={`rr-sr-summary-card ${tone}`}><div className="icon"><i className={`bi ${icon}`} /></div><div><small>{label}</small><strong>{value}</strong><span>{note}</span></div><i className={`bi ${icon} watermark`} /></article>;
}

function FlowCard({ tone, icon, label, value }) {
    return <article className={`rr-sr-flow-card ${tone}`}><i className={`bi ${icon}`} /><span><small>{label}</small><strong>{money(value)}</strong></span></article>;
}

function TableHeader({ icon, title, subtitle }) {
    return <header className="rr-sr-table-head"><div><i className={`bi ${icon}`} /></div><span><strong>{title}</strong><small>{subtitle}</small></span></header>;
}

function Source({ sale }) {
    const mechanic = sale.sale_source === "mechanic";
    return <span className={`rr-sr-source ${mechanic ? "mechanic" : "customer"}`}><i className={`bi ${mechanic ? "bi-person-gear" : "bi-person"}`} />{mechanic ? sale.mechanic?.name ?? "Mechanic" : "Customer"}</span>;
}

function Status({ value }) {
    const normalized = String(value ?? "Unpaid").toLowerCase();
    const tone = normalized === "paid" ? "paid" : normalized.includes("partial") ? "partial" : "unpaid";
    return <span className={`rr-sr-status ${tone}`}>{value ?? "Unpaid"}</span>;
}

function EmptyRow({ colSpan }) {
    return <tr><td colSpan={colSpan}><div className="rr-sr-empty"><i className="bi bi-inbox" /><strong>No sales found</strong><span>Change the period or filters.</span></div></td></tr>;
}

function Pagination({ links }) {
    if (!links?.length) return null;
    return <nav className="rr-sr-pagination">{links.map((link, index) => <Link key={`${link.label}-${index}`} href={link.url ?? "#"} preserveScroll preserveState className={`${link.active ? "active" : ""} ${!link.url ? "disabled" : ""}`} dangerouslySetInnerHTML={{ __html: link.label }} onClick={(event) => { if (!link.url) event.preventDefault(); }} />)}</nav>;
}

function cleanParams(values) {
    return Object.fromEntries(Object.entries(values).filter(([, value]) => value !== "" && value !== null && value !== undefined));
}

function money(value) {
    return new Intl.NumberFormat("en-IN", { style: "currency", currency: "INR", minimumFractionDigits: 2 }).format(Number(value ?? 0));
}

function number(value) { return new Intl.NumberFormat("en-IN").format(Number(value ?? 0)); }
function displayDate(value) { return value ? new Date(`${value}T00:00:00`).toLocaleDateString("en-IN") : "—"; }
function initials(value) { return String(value ?? "C").trim().split(/\s+/).slice(0, 2).map((part) => part[0]).join("").toUpperCase(); }
function localDate() { const now = new Date(); const offset = now.getTimezoneOffset() * 60_000; return new Date(now.getTime() - offset).toISOString().slice(0, 10); }
function modeLabel(mode) { return mode === "yearly" ? "Yearly" : mode === "monthly" ? "Monthly" : "Day-wise"; }

Index.layout = (page) => <BusinessLayout>{page}</BusinessLayout>;
