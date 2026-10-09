import React, { useMemo, useState } from "react";
import { Head, Link, router } from "@inertiajs/react";

import BusinessLayout from "@/Layouts/BusinessLayout";
import MasterNavigation from "../../Components/Business/MasterNavigation";

import "../../../css/business/purchase-report.css";

export default function Index({
    rows = [],
    summary = {},
    purchases = {},
    filters = {},
    suppliers = [],
    context = {},
}) {
    const [form, setForm] = useState({
        mode: filters.mode ?? "daily",
        date: filters.date ?? localDate(),
        month: filters.month ?? localDate().slice(0, 7),
        year: filters.year ?? new Date().getFullYear(),
        from_date: filters.from_date ?? "",
        to_date: filters.to_date ?? "",
        supplier_id: filters.supplier_id ?? "",
        payment_status: filters.payment_status ?? "",
        billing_type: filters.billing_type ?? "",
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

    const applyFilters = (event) => {
        event.preventDefault();
        router.get("/purchase-reports", cleanParams(form), {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    };

    const switchMode = (mode) => {
        const next = {
            ...form,
            mode,
            from_date: "",
            to_date: "",
        };

        setForm(next);
        router.get("/purchase-reports", cleanParams(next), {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    };

    const reset = () => {
        const next = {
            mode: "daily",
            date: localDate(),
            month: localDate().slice(0, 7),
            year: new Date().getFullYear(),
            from_date: "",
            to_date: "",
            supplier_id: "",
            payment_status: "",
            billing_type: "",
            search: "",
        };

        setForm(next);
        router.get("/purchase-reports", next, {
            preserveScroll: true,
            replace: true,
        });
    };

    const purchaseRows = purchases?.data ?? [];

    return (
        <>
            <Head title="Purchase Report" />

            <div className="rr-pr-page">
                <MasterNavigation
                    active="purchase-reports"
                    title="Purchase Report"
                    subtitle={
                        context.store_name
                            ? `${context.store_name} · Daily, monthly and yearly analysis`
                            : "Daily, monthly and yearly purchase analysis"
                    }
                />

                <section className="rr-pr-hero">
                    <div className="rr-pr-hero-copy">
                        <span className="rr-pr-eyebrow">
                            <i className="bi bi-graph-up-arrow" />
                            PURCHASE INTELLIGENCE
                        </span>
                        <h1>Purchase Performance</h1>
                        <p>
                            Monitor supplier purchases, returns, payments and
                            outstanding balances from one report.
                        </p>
                    </div>

                    <div className="rr-pr-export-bar">
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
                            className="rr-pr-action print"
                            href={`/purchase-reports/print?${exportQuery}`}
                            target="_blank"
                            rel="noreferrer"
                        >
                            <i className="bi bi-printer" />
                            Print
                        </a>

                        <a
                            className="rr-pr-action pdf"
                            href={`/purchase-reports/pdf?${exportQuery}`}
                        >
                            <i className="bi bi-file-earmark-pdf" />
                            PDF
                        </a>

                        <a
                            className="rr-pr-action excel"
                            href={`/purchase-reports/excel?${exportQuery}`}
                        >
                            <i className="bi bi-file-earmark-spreadsheet" />
                            CSV
                        </a>
                    </div>
                </section>

                <section className="rr-pr-mode-panel">
                    <div className="rr-pr-mode-title">
                        <i className="bi bi-calendar3" />
                        <div>
                            <strong>Reporting Period</strong>
                            <small>Select how purchase data is grouped</small>
                        </div>
                    </div>

                    <div className="rr-pr-mode-tabs">
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

                    <div className="rr-pr-primary-period">
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
                            <label className="rr-pr-field">
                                <span>Report Year</span>
                                <input
                                    type="number"
                                    min="2000"
                                    max="2100"
                                    value={form.year}
                                    onChange={(event) =>
                                        update("year", event.target.value)
                                    }
                                />
                            </label>
                        )}

                        <button
                            type="button"
                            className="rr-pr-run"
                            onClick={applyFilters}
                        >
                            <i className="bi bi-play-fill" />
                            Run Report
                        </button>
                    </div>
                </section>

                <section className="rr-pr-summary-grid">
                    <SummaryCard
                        tone="indigo"
                        icon="bi-receipt"
                        label="Purchase Bills"
                        value={number(summary.bill_count)}
                        note="Completed purchase invoices"
                    />
                    <SummaryCard
                        tone="blue"
                        icon="bi-cart-check"
                        label="Gross Purchase"
                        value={money(summary.gross_purchase)}
                        note="Before purchase returns"
                    />
                    <SummaryCard
                        tone="orange"
                        icon="bi-arrow-return-left"
                        label="Purchase Returns"
                        value={money(summary.return_amount)}
                        note="Returned supplier value"
                    />
                    <SummaryCard
                        tone="violet"
                        icon="bi-bag-check"
                        label="Net Purchase"
                        value={money(summary.net_purchase)}
                        note="Gross less returns"
                    />
                    <SummaryCard
                        tone="green"
                        icon="bi-check2-circle"
                        label="Paid Amount"
                        value={money(summary.paid_amount)}
                        note="Settled supplier amount"
                    />
                    <SummaryCard
                        tone="red"
                        icon="bi-hourglass-split"
                        label="Outstanding"
                        value={money(summary.outstanding_amount)}
                        note="Amount payable to suppliers"
                    />
                </section>

                <section className="rr-pr-filter-card">
                    <button
                        type="button"
                        className="rr-pr-section-head"
                        onClick={() => setFiltersOpen((open) => !open)}
                    >
                        <span>
                            <i className="bi bi-funnel" />
                            <span>
                                <strong>Advanced Filters</strong>
                                <small>
                                    Narrow the report by supplier and bill status
                                </small>
                            </span>
                        </span>
                        <i
                            className={`bi ${filtersOpen ? "bi-chevron-up" : "bi-chevron-down"
                                }`}
                        />
                    </button>

                    {filtersOpen && (
                        <form className="rr-pr-filter-body" onSubmit={applyFilters}>
                            <label className="rr-pr-field grow">
                                <span>Search</span>
                                <div className="rr-pr-input-icon">
                                    <i className="bi bi-search" />
                                    <input
                                        type="search"
                                        value={form.search}
                                        placeholder="Purchase no. or supplier"
                                        onChange={(event) =>
                                            update("search", event.target.value)
                                        }
                                    />
                                </div>
                            </label>

                            <label className="rr-pr-field">
                                <span>Supplier</span>
                                <select
                                    value={form.supplier_id}
                                    onChange={(event) =>
                                        update("supplier_id", event.target.value)
                                    }
                                >
                                    <option value="">All Suppliers</option>
                                    {suppliers.map((supplier) => (
                                        <option key={supplier.id} value={supplier.id}>
                                            {supplier.supplier_name}
                                        </option>
                                    ))}
                                </select>
                            </label>

                            <label className="rr-pr-field">
                                <span>Payment Status</span>
                                <select
                                    value={form.payment_status}
                                    onChange={(event) =>
                                        update("payment_status", event.target.value)
                                    }
                                >
                                    <option value="">All Statuses</option>
                                    <option value="Unpaid">Unpaid</option>
                                    <option value="Partially Paid">Partially Paid</option>
                                    <option value="Paid">Paid</option>
                                </select>
                            </label>

                            <label className="rr-pr-field">
                                <span>Billing Type</span>
                                <select
                                    value={form.billing_type}
                                    onChange={(event) =>
                                        update("billing_type", event.target.value)
                                    }
                                >
                                    <option value="">All Bills</option>
                                    <option value="gst">GST</option>
                                    <option value="non_gst">Non-GST</option>
                                </select>
                            </label>

                            <DateControl
                                label="Custom From"
                                type="date"
                                value={form.from_date}
                                onChange={(value) => update("from_date", value)}
                            />

                            <DateControl
                                label="Custom To"
                                type="date"
                                value={form.to_date}
                                onChange={(value) => update("to_date", value)}
                            />

                            <div className="rr-pr-filter-actions">
                                <button type="button" onClick={reset}>
                                    <i className="bi bi-arrow-counterclockwise" />
                                    Reset
                                </button>
                                <button type="submit" className="primary">
                                    <i className="bi bi-funnel-fill" />
                                    Apply
                                </button>
                            </div>
                        </form>
                    )}
                </section>

                <section className="rr-pr-table-card period-table">
                    <TableHeader
                        icon="bi-bar-chart-line"
                        title={`${modeLabel(form.mode)} Summary`}
                        subtitle={`${filters.from_date ?? ""} to ${filters.to_date ?? ""}`}
                    />

                    <div className="rr-pr-table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Period</th>
                                    <th className="number">Bills</th>
                                    <th className="number">Gross</th>
                                    <th className="number">Returns</th>
                                    <th className="number">Net Purchase</th>
                                    <th className="number">Paid</th>
                                    <th className="number">Outstanding</th>
                                </tr>
                            </thead>
                            <tbody>
                                {rows.length ? (
                                    rows.map((row) => (
                                        <tr key={row.key}>
                                            <td className="period-cell">
                                                <i className="bi bi-calendar-event" />
                                                <strong>{row.label}</strong>
                                            </td>
                                            <td className="number">{number(row.bill_count)}</td>
                                            <td className="number">{money(row.gross_purchase)}</td>
                                            <td className="number returns">{money(row.return_amount)}</td>
                                            <td className="number net">{money(row.net_purchase)}</td>
                                            <td className="number paid">{money(row.paid_amount)}</td>
                                            <td className="number due">{money(row.outstanding_amount)}</td>
                                        </tr>
                                    ))
                                ) : (
                                    <EmptyRow colSpan="7" />
                                )}
                            </tbody>
                        </table>
                    </div>
                </section>

                <section className="rr-pr-table-card invoice-table">
                    <TableHeader
                        icon="bi-receipt-cutoff"
                        title="Purchase Invoice Details"
                        subtitle={`${purchases?.total ?? purchaseRows.length} matching records`}
                    />

                    <div className="rr-pr-table-wrap">
                        <table>
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Purchase No.</th>
                                    <th>Supplier</th>
                                    <th>Bill Type</th>
                                    <th className="number">Total</th>
                                    <th className="number">Paid</th>
                                    <th className="number">Balance</th>
                                    <th>Status</th>
                                    <th className="action">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                {purchaseRows.length ? (
                                    purchaseRows.map((purchase) => (
                                        <tr key={purchase.id}>
                                            <td>{displayDate(purchase.purchase_date)}</td>
                                            <td>
                                                <strong>{purchase.purchase_no}</strong>
                                            </td>
                                            <td>
                                                <div className="rr-pr-supplier">
                                                    <span>
                                                        {initials(purchase.supplier?.name)}
                                                    </span>
                                                    <div>
                                                        <strong>
                                                            {purchase.supplier?.name ?? "—"}
                                                        </strong>
                                                        <small>
                                                            {purchase.supplier?.code ?? ""}
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td>
                                                <span className={`rr-pr-bill-type ${purchase.billing_type ?? ""}`}>
                                                    {purchase.billing_type === "gst" ? "GST" : "Non-GST"}
                                                </span>
                                            </td>
                                            <td className="number net">{money(purchase.grand_total)}</td>
                                            <td className="number paid">{money(purchase.paid_amount)}</td>
                                            <td className="number due">{money(purchase.balance_amount)}</td>
                                            <td>
                                                <Status value={purchase.payment_status} />
                                            </td>
                                            <td className="action">
                                                <Link
                                                    href={`/purchase-reports/${purchase.id}`}
                                                    className="rr-pr-view"
                                                    title="View purchase details"
                                                >
                                                    <i className="bi bi-eye" />
                                                </Link>
                                            </td>
                                        </tr>
                                    ))
                                ) : (
                                    <EmptyRow colSpan="9" />
                                )}
                            </tbody>
                        </table>
                    </div>

                    <Pagination links={purchases?.links ?? []} />
                </section>
            </div>
        </>
    );
}

function ModeButton({ active, icon, label, onClick }) {
    return (
        <button
            type="button"
            className={active ? "active" : ""}
            onClick={onClick}
        >
            <i className={`bi ${icon}`} />
            <span>{label}</span>
        </button>
    );
}

function DateControl({ label, type, value, onChange }) {
    const openPicker = (event) => event.currentTarget.showPicker?.();

    return (
        <label className="rr-pr-field">
            <span>{label}</span>
            <div className="rr-pr-date-control">
                <input
                    type={type}
                    value={value}
                    onClick={openPicker}
                    onFocus={openPicker}
                    onChange={(event) => onChange(event.target.value)}
                />
                <i className="bi bi-calendar3" />
            </div>
        </label>
    );
}

function SummaryCard({ tone, icon, label, value, note }) {
    return (
        <article className={`rr-pr-summary-card ${tone}`}>
            <div className="rr-pr-summary-icon">
                <i className={`bi ${icon}`} />
            </div>
            <div>
                <small>{label}</small>
                <strong>{value}</strong>
                <span>{note}</span>
            </div>
            <i className={`bi ${icon} rr-pr-summary-watermark`} />
        </article>
    );
}

function TableHeader({ icon, title, subtitle }) {
    return (
        <header className="rr-pr-table-head">
            <div className="rr-pr-table-icon">
                <i className={`bi ${icon}`} />
            </div>
            <div>
                <strong>{title}</strong>
                <small>{subtitle}</small>
            </div>
        </header>
    );
}

function Status({ value }) {
    const normalized = String(value ?? "Unpaid").toLowerCase();
    const tone = normalized === "paid"
        ? "paid"
        : normalized.includes("partial")
            ? "partial"
            : "unpaid";

    return <span className={`rr-pr-status ${tone}`}>{value ?? "Unpaid"}</span>;
}

function EmptyRow({ colSpan }) {
    return (
        <tr>
            <td colSpan={colSpan}>
                <div className="rr-pr-empty">
                    <i className="bi bi-inbox" />
                    <strong>No purchase records found</strong>
                    <span>Change the selected period or filters.</span>
                </div>
            </td>
        </tr>
    );
}

function Pagination({ links }) {
    if (!links?.length) return null;

    return (
        <nav className="rr-pr-pagination" aria-label="Purchase report pages">
            {links.map((link, index) => (
                <Link
                    key={`${link.label}-${index}`}
                    href={link.url ?? "#"}
                    preserveScroll
                    preserveState
                    className={`${link.active ? "active" : ""} ${!link.url ? "disabled" : ""}`}
                    dangerouslySetInnerHTML={{ __html: link.label }}
                    onClick={(event) => {
                        if (!link.url) event.preventDefault();
                    }}
                />
            ))}
        </nav>
    );
}

function cleanParams(values) {
    return Object.fromEntries(
        Object.entries(values).filter(
            ([, value]) => value !== "" && value !== null && value !== undefined,
        ),
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

function displayDate(value) {
    if (!value) return "—";
    return new Date(`${value}T00:00:00`).toLocaleDateString("en-IN");
}

function initials(value) {
    return String(value ?? "S")
        .trim()
        .split(/\s+/)
        .slice(0, 2)
        .map((part) => part[0])
        .join("")
        .toUpperCase();
}

function localDate() {
    const now = new Date();
    const offset = now.getTimezoneOffset() * 60_000;
    return new Date(now.getTime() - offset).toISOString().slice(0, 10);
}

function modeLabel(mode) {
    if (mode === "yearly") return "Yearly";
    if (mode === "monthly") return "Monthly";
    return "Day-wise";
}

Index.layout = (page) => <BusinessLayout>{page}</BusinessLayout>;
