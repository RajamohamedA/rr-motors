import React, { useMemo, useState } from "react";
import { Head, Link, router } from "@inertiajs/react";

import BusinessLayout from "@/Layouts/BusinessLayout";
import MasterNavigation from "../../Components/Business/MasterNavigation";

import "../../../css/business/outstanding-reports.css";

export default function Index({
    rows = {},
    filters = {},
    summary = {},
    context = {},
}) {
    const [form, setForm] = useState({
        type: filters.type ?? "customer",
        search: filters.search ?? "",
        as_of_date: filters.as_of_date ?? "",
        minimum_balance: String(filters.minimum_balance ?? 0),
        per_page: String(filters.per_page ?? 20),
    });
    const [paper, setPaper] = useState("a4");
    const [filtersOpen, setFiltersOpen] = useState(true);

    const reportRows = rows?.data ?? [];
    const activeSummary =
        form.type === "supplier" ? summary.supplier ?? {} : summary.customer ?? {};
    const typeLabel = form.type === "supplier" ? "Supplier" : "Customer";

    const hasFilters = useMemo(
        () =>
            form.search.trim() !== "" ||
            Number(form.minimum_balance || 0) > 0 ||
            form.per_page !== "20" ||
            form.as_of_date !== filters.as_of_date,
        [form, filters.as_of_date],
    );

    const update = (key, value) => {
        setForm((current) => ({ ...current, [key]: value }));
    };

    const visitReport = (next, replace = true) => {
        router.get("/accounts/outstanding", cleanParams(next), {
            preserveState: true,
            preserveScroll: true,
            replace,
        });
    };

    const switchType = (type) => {
        const next = { ...form, type };
        setForm(next);
        visitReport(next);
    };

    const applyFilters = (event) => {
        event.preventDefault();
        visitReport(form);
    };

    const clearFilters = () => {
        const next = {
            type: form.type,
            search: "",
            as_of_date: filters.as_of_date ?? "",
            minimum_balance: "0",
            per_page: "20",
        };

        setForm(next);
        visitReport(next);
    };

    const exportParams = new URLSearchParams(
        cleanParams({
            ...form,
            paper,
        }),
    ).toString();

    return (
        <>
            <Head title="Outstanding Reports" />

            <main className="rr-out-page">
                <MasterNavigation
                    active="outstanding-reports"
                    title="Outstanding Reports"
                    subtitle={
                        context.store_name
                            ? `Receivables and payables • ${context.store_name}`
                            : "Customer receivables and supplier payables"
                    }
                />

                <section className="rr-out-commandbar">
                    <div>
                        <span className="rr-out-eyebrow">ACCOUNTS CONTROL</span>
                        <h1>Outstanding Position</h1>
                        <p>
                            Review amounts receivable from customers and payable to suppliers
                            as of a selected date.
                        </p>
                    </div>

                    <div className="rr-out-export-tools">
                        <div className="rr-out-paper-switch">
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

                        <a
                            href={`/accounts/outstanding/print?${exportParams}`}
                            target="_blank"
                            rel="noreferrer"
                            className="rr-out-btn print"
                        >
                            <i className="bi bi-printer" />
                            Print
                        </a>
                        <a
                            href={`/accounts/outstanding/pdf?${exportParams}`}
                            className="rr-out-btn pdf"
                        >
                            <i className="bi bi-file-earmark-pdf" />
                            PDF
                        </a>
                        <a
                            href={`/accounts/outstanding/excel?${exportParams}`}
                            className="rr-out-btn excel"
                        >
                            <i className="bi bi-file-earmark-spreadsheet" />
                            CSV
                        </a>
                    </div>
                </section>

                <section className="rr-out-overview-grid">
                    <OverviewCard
                        tone="customer"
                        icon="bi-people"
                        label="Customer Outstanding"
                        value={money(summary.customer?.total)}
                        note={`${number(summary.customer?.count)} customers`}
                        active={form.type === "customer"}
                        onClick={() => switchType("customer")}
                    />
                    <OverviewCard
                        tone="supplier"
                        icon="bi-truck"
                        label="Supplier Outstanding"
                        value={money(summary.supplier?.total)}
                        note={`${number(summary.supplier?.count)} suppliers`}
                        active={form.type === "supplier"}
                        onClick={() => switchType("supplier")}
                    />
                    <OverviewCard
                        tone="combined"
                        icon="bi-calculator"
                        label="Combined Exposure"
                        value={money(summary.combined_total)}
                        note="Receivable plus payable"
                    />
                    <OverviewCard
                        tone="highest"
                        icon="bi-graph-up-arrow"
                        label={`Highest ${typeLabel}`}
                        value={money(activeSummary.highest)}
                        note={`As of ${date(form.as_of_date)}`}
                    />
                </section>

                <section className="rr-out-tabbar" aria-label="Outstanding type">
                    <button
                        type="button"
                        className={form.type === "customer" ? "active customer" : "customer"}
                        onClick={() => switchType("customer")}
                    >
                        <i className="bi bi-person-lines-fill" />
                        <span>
                            <strong>Customer Receivables</strong>
                            <small>Money customers owe the business</small>
                        </span>
                        <em>{number(summary.customer?.count)}</em>
                    </button>

                    <button
                        type="button"
                        className={form.type === "supplier" ? "active supplier" : "supplier"}
                        onClick={() => switchType("supplier")}
                    >
                        <i className="bi bi-building" />
                        <span>
                            <strong>Supplier Payables</strong>
                            <small>Money the business owes suppliers</small>
                        </span>
                        <em>{number(summary.supplier?.count)}</em>
                    </button>
                </section>

                <section className={`rr-out-filter-card ${filtersOpen ? "open" : ""}`}>
                    <button
                        type="button"
                        className="rr-out-filter-head"
                        onClick={() => setFiltersOpen((value) => !value)}
                        aria-expanded={filtersOpen}
                    >
                        <span>
                            <i className="bi bi-funnel" />
                            <span>
                                <strong>Outstanding Filters</strong>
                                <small>Search parties and calculate balances as of a date</small>
                            </span>
                        </span>
                        <span className="rr-out-filter-state">
                            {hasFilters && <em>Active</em>}
                            <i
                                className={`bi ${filtersOpen ? "bi-chevron-up" : "bi-chevron-down"
                                    }`}
                            />
                        </span>
                    </button>

                    {filtersOpen && (
                        <form className="rr-out-filter-body" onSubmit={applyFilters}>
                            <Field label="Search Party" icon="bi-search" wide>
                                <input
                                    type="search"
                                    value={form.search}
                                    onChange={(event) => update("search", event.target.value)}
                                    placeholder={`${typeLabel} name, code, mobile or contact person`}
                                />
                            </Field>

                            <Field label="As Of Date" icon="bi-calendar-check">
                                <input
                                    type="date"
                                    value={form.as_of_date}
                                    onChange={(event) => update("as_of_date", event.target.value)}
                                    onClick={openDatePicker}
                                    onFocus={openDatePicker}
                                />
                            </Field>

                            <Field label="Minimum Balance" icon="bi-currency-rupee">
                                <input
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    value={form.minimum_balance}
                                    onChange={(event) =>
                                        update("minimum_balance", event.target.value)
                                    }
                                    placeholder="0.00"
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

                            <div className="rr-out-filter-actions">
                                <button type="submit" className="rr-out-btn filter">
                                    <i className="bi bi-search" />
                                    Apply
                                </button>
                                <button
                                    type="button"
                                    className="rr-out-btn reset"
                                    onClick={clearFilters}
                                >
                                    <i className="bi bi-arrow-counterclockwise" />
                                    Reset
                                </button>
                            </div>
                        </form>
                    )}
                </section>

                <section className={`rr-out-register ${form.type}`}>
                    <header className="rr-out-register-head">
                        <div>
                            <span>
                                <i
                                    className={`bi ${form.type === "customer" ? "bi-people" : "bi-truck"
                                        }`}
                                />
                            </span>
                            <div>
                                <strong>{typeLabel} Outstanding Register</strong>
                                <small>
                                    {number(rows?.total ?? reportRows.length)} outstanding parties •
                                    As of {date(form.as_of_date)}
                                </small>
                            </div>
                        </div>

                        <strong className="rr-out-register-total">
                            {money(activeSummary.total)}
                        </strong>
                    </header>

                    <div className="rr-out-table-wrap">
                        <table className="rr-out-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Party Code</th>
                                    <th>{typeLabel} Name</th>
                                    <th>Contact</th>
                                    <th>City</th>
                                    <th className="number">Credit Limit</th>
                                    <th className="number">Credit Days</th>
                                    <th className="number">Outstanding</th>
                                    <th>Balance</th>
                                    <th className="actions">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {reportRows.length ? (
                                    reportRows.map((row, index) => (
                                        <OutstandingRow
                                            key={`${row.type}-${row.id}`}
                                            row={row}
                                            serial={(rows?.from ?? 1) + index}
                                        />
                                    ))
                                ) : (
                                    <tr>
                                        <td colSpan="10">
                                            <EmptyState type={form.type} />
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>

                    <Pagination links={rows?.links ?? []} />
                </section>
            </main>
        </>
    );
}

function OverviewCard({
    tone,
    icon,
    label,
    value,
    note,
    active = false,
    onClick,
}) {
    return (
        <article
            className={`rr-out-overview-card ${tone} ${active ? "active" : ""} ${onClick ? "clickable" : ""
                }`}
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
            <span className="rr-out-card-icon">
                <i className={`bi ${icon}`} />
            </span>
            <div>
                <small>{label}</small>
                <strong>{value}</strong>
                <p>{note}</p>
            </div>
            {active && <em>Viewing</em>}
        </article>
    );
}

function Field({ label, icon, wide = false, children }) {
    return (
        <label className={`rr-out-field ${wide ? "wide" : ""}`}>
            <span>
                <i className={`bi ${icon}`} />
                {label}
            </span>
            {children}
        </label>
    );
}

function OutstandingRow({ row, serial }) {
    const openParty = (event) => {
        if (event.target.closest("a, button")) return;
        router.visit(row.party_url);
    };

    return (
        <tr onClick={openParty}>
            <td>{serial}</td>
            <td>
                <Link href={row.party_url} className="rr-out-party-code">
                    {row.code}
                </Link>
            </td>
            <td>
                <strong className="rr-out-party-name">{row.name}</strong>
                <small>{row.contact_person || "—"}</small>
            </td>
            <td>{row.mobile || "—"}</td>
            <td>{row.city || "—"}</td>
            <td className="number">{money(row.credit_limit)}</td>
            <td className="number">{number(row.credit_days)}</td>
            <td className="number outstanding">{money(row.outstanding_balance)}</td>
            <td>
                <span className={`rr-out-balance ${row.type}`}>
                    {row.type === "customer" ? "Dr" : "Cr"}
                </span>
            </td>
            <td className="actions">
                <div className="rr-out-actions">
                    <Link href={row.party_url} className="party" title="View party">
                        <i className="bi bi-person-vcard" />
                    </Link>
                    <Link href={row.ledger_url} className="ledger" title="Open ledger">
                        <i className="bi bi-journal-text" />
                    </Link>
                </div>
            </td>
        </tr>
    );
}

function EmptyState({ type }) {
    return (
        <div className="rr-out-empty">
            <span>
                <i className="bi bi-check2-circle" />
            </span>
            <strong>No {type} outstanding found</strong>
            <p>There are no parties matching the current balance and date filters.</p>
        </div>
    );
}

function Pagination({ links }) {
    if (!Array.isArray(links) || links.length <= 3) return null;

    return (
        <nav className="rr-out-pagination" aria-label="Outstanding report pages">
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
            ([, value]) => value !== "" && value !== null && value !== undefined,
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

Index.layout = (page) => <BusinessLayout>{page}</BusinessLayout>;
