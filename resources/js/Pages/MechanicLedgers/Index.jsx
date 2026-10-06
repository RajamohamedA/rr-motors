import React, { useEffect, useRef, useState } from "react";

import axios from "axios";

import { Head, Link, router } from "@inertiajs/react";

import BusinessLayout from "@/Layouts/BusinessLayout";
import MasterNavigation from "../../Components/Business/MasterNavigation";

import "../../../css/business/mechanic-ledger.css";

export default function Index({
    entries = null,
    mechanic = null,
    summary = {},
    filters = {},
    transaction_types: transactionTypes = {},
}) {
    const [form, setForm] = useState({
        mechanic_id: filters.mechanic_id ? String(filters.mechanic_id) : "",
        from_date: filters.from_date ?? "",
        to_date: filters.to_date ?? "",
        transaction_type: filters.transaction_type ?? "",
        paper: filters.paper ?? "a4",
        search: filters.search ?? "",
        per_page: String(filters.per_page ?? 20),
    });

    const [mechanicSearch, setMechanicSearch] = useState(
        mechanic?.mechanic_name ?? "",
    );
    const [mechanicResults, setMechanicResults] = useState([]);
    const [searchingMechanics, setSearchingMechanics] = useState(false);
    const [mechanicDropdown, setMechanicDropdown] = useState(false);
    const mechanicTimer = useRef(null);
    const mechanicBox = useRef(null);

    useEffect(() => {
        const closeDropdown = (event) => {
            if (!mechanicBox.current?.contains(event.target)) {
                setMechanicDropdown(false);
            }
        };

        document.addEventListener("mousedown", closeDropdown);

        return () => {
            document.removeEventListener("mousedown", closeDropdown);
            window.clearTimeout(mechanicTimer.current);
        };
    }, []);

    const searchMechanics = (value) => {
        setMechanicSearch(value);
        setForm((current) => ({ ...current, mechanic_id: "" }));
        window.clearTimeout(mechanicTimer.current);

        if (value.trim().length < 1) {
            setMechanicResults([]);
            setMechanicDropdown(false);
            return;
        }

        mechanicTimer.current = window.setTimeout(async () => {
            setSearchingMechanics(true);

            try {
                const response = await axios.get(
                    "/mechanic-ledger/mechanics/search",
                    { params: { search: value.trim() } },
                );

                setMechanicResults(response.data?.data ?? []);
                setMechanicDropdown(true);
            } catch {
                setMechanicResults([]);
                setMechanicDropdown(true);
            } finally {
                setSearchingMechanics(false);
            }
        }, 300);
    };

    const selectMechanic = (selected) => {
        setForm((current) => ({
            ...current,
            mechanic_id: String(selected.id),
        }));
        setMechanicSearch(selected.mechanic_name ?? "");
        setMechanicResults([]);
        setMechanicDropdown(false);
    };

    const clearMechanic = () => {
        setForm((current) => ({ ...current, mechanic_id: "" }));
        setMechanicSearch("");
        setMechanicResults([]);
        setMechanicDropdown(false);
    };

    const submitFilters = (event) => {
        event.preventDefault();

        router.get("/mechanic-ledger", cleanParams(form), {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    };

    const resetFilters = () => {
        const reset = {
            mechanic_id: "",
            from_date: firstDayOfMonth(),
            to_date: today(),
            transaction_type: "",
            paper: "a4",
            search: "",
            per_page: "20",
        };

        setForm(reset);
        setMechanicSearch("");
        router.get("/mechanic-ledger", cleanParams(reset), {
            preserveState: false,
            replace: true,
        });
    };

    const exportUrl = (type) => {
        const query = new URLSearchParams(cleanParams(form)).toString();
        return `/mechanic-ledger/${type}${query ? `?${query}` : ""}`;
    };

    const rows = entries?.data ?? [];
    const hasMechanic = Boolean(mechanic?.id);

    return (
        <>
            <Head title="Mechanic Ledger" />

            <div className="rr-ml-page">
                <MasterNavigation
                    active="mechanic-ledger"
                    title="Mechanic Ledger"
                    subtitle="Commission payable, payments and running balance"
                />

                <section className="rr-ml-titlebar">
                    <div>
                        <span className="rr-ml-eyebrow">ACCOUNTS REGISTER</span>
                        <h1>Mechanic Ledger</h1>
                        <p>
                            Review commission credits, payment debits and the
                            current amount payable to each mechanic.
                        </p>
                    </div>

                    <div className="rr-ml-actions">
                        <a
                            className={`rr-ml-btn print ${!hasMechanic ? "disabled" : ""}`}
                            href={hasMechanic ? exportUrl("print") : undefined}
                            target="_blank"
                            rel="noreferrer"
                        >
                            <i className="bi bi-printer"></i>
                            Print
                        </a>
                        <a
                            className={`rr-ml-btn pdf ${!hasMechanic ? "disabled" : ""}`}
                            href={hasMechanic ? exportUrl("pdf") : undefined}
                        >
                            <i className="bi bi-file-earmark-pdf"></i>
                            PDF
                        </a>
                        <a
                            className={`rr-ml-btn excel ${!hasMechanic ? "disabled" : ""}`}
                            href={hasMechanic ? exportUrl("excel") : undefined}
                        >
                            <i className="bi bi-file-earmark-spreadsheet"></i>
                            Excel
                        </a>
                    </div>
                </section>

                <form className="rr-ml-filter" onSubmit={submitFilters}>
                    <div className="rr-ml-filter-head">
                        <div>
                            <i className="bi bi-funnel-fill"></i>
                            <strong>Ledger Filters</strong>
                        </div>
                        <span>Select a mechanic before viewing the ledger</span>
                    </div>

                    <div className="rr-ml-filter-grid">
                        <div className="rr-ml-field mechanic-field" ref={mechanicBox}>
                            <label>Mechanic</label>
                            <div className="rr-ml-search-input">
                                <i className="bi bi-search"></i>
                                <input
                                    value={mechanicSearch}
                                    onChange={(event) => searchMechanics(event.target.value)}
                                    onFocus={() => {
                                        if (mechanicResults.length) setMechanicDropdown(true);
                                    }}
                                    placeholder="Search name, code or mobile..."
                                    autoComplete="off"
                                />
                                {searchingMechanics && (
                                    <span className="spinner-border spinner-border-sm"></span>
                                )}
                                {mechanicSearch && !searchingMechanics && (
                                    <button type="button" onClick={clearMechanic} title="Clear mechanic">
                                        <i className="bi bi-x-lg"></i>
                                    </button>
                                )}
                            </div>

                            {mechanicDropdown && (
                                <div className="rr-ml-results">
                                    {mechanicResults.length ? (
                                        mechanicResults.map((result) => (
                                            <button
                                                type="button"
                                                key={result.id}
                                                onClick={() => selectMechanic(result)}
                                            >
                                                <span className="rr-ml-result-icon">
                                                    <i className="bi bi-person-gear"></i>
                                                </span>
                                                <span>
                                                    <strong>{result.mechanic_name}</strong>
                                                    <small>
                                                        {result.mechanic_code || "No code"}
                                                        {result.mobile ? ` • ${result.mobile}` : ""}
                                                    </small>
                                                </span>
                                                <b>{result.balance_label ?? "0.00"}</b>
                                            </button>
                                        ))
                                    ) : (
                                        <div className="rr-ml-no-result">
                                            <i className="bi bi-person-x"></i>
                                            No mechanics found
                                        </div>
                                    )}
                                </div>
                            )}
                        </div>

                        <Field label="From Date">
                            <input
                                type="date"
                                value={form.from_date}
                                onClick={openDatePicker}
                                onFocus={openDatePicker}
                                onChange={(event) => setForm({ ...form, from_date: event.target.value })}
                            />
                        </Field>

                        <Field label="To Date">
                            <input
                                type="date"
                                value={form.to_date}
                                onClick={openDatePicker}
                                onFocus={openDatePicker}
                                onChange={(event) => setForm({ ...form, to_date: event.target.value })}
                            />
                        </Field>

                        <Field label="Transaction Type">
                            <select
                                value={form.transaction_type}
                                onChange={(event) => setForm({ ...form, transaction_type: event.target.value })}
                            >
                                <option value="">All Transactions</option>
                                {Object.entries(transactionTypes).map(([value, label]) => (
                                    <option key={value} value={value}>{label}</option>
                                ))}
                            </select>
                        </Field>

                        <Field label="Print Paper">
                            <select
                                value={form.paper}
                                onChange={(event) =>
                                    setForm({
                                        ...form,
                                        paper: event.target.value,
                                    })
                                }
                            >
                                <option value="a4">A4 — 210 × 297 mm</option>
                                <option value="a5">A5 — 148 × 210 mm</option>
                            </select>
                        </Field>

                        <Field label="Reference / Remarks">
                            <input
                                value={form.search}
                                onChange={(event) => setForm({ ...form, search: event.target.value })}
                                placeholder="Invoice, payment or remarks"
                            />
                        </Field>

                        <Field label="Rows">
                            <select
                                value={form.per_page}
                                onChange={(event) => setForm({ ...form, per_page: event.target.value })}
                            >
                                <option value="20">20 rows</option>
                                <option value="50">50 rows</option>
                                <option value="100">100 rows</option>
                            </select>
                        </Field>
                    </div>

                    <div className="rr-ml-filter-actions">
                        <button className="rr-ml-btn reset" type="button" onClick={resetFilters}>
                            <i className="bi bi-arrow-counterclockwise"></i>
                            Reset
                        </button>
                        <button className="rr-ml-btn apply" type="submit">
                            <i className="bi bi-search"></i>
                            View Ledger
                        </button>
                    </div>
                </form>

                {hasMechanic ? (
                    <>
                        <section className="rr-ml-mechanic">
                            <div className="rr-ml-mechanic-avatar">
                                {initials(mechanic.mechanic_name)}
                            </div>
                            <div>
                                <span>SELECTED MECHANIC</span>
                                <h2>{mechanic.mechanic_name}</h2>
                                <p>
                                    {mechanic.mechanic_code || "No code"}
                                    {mechanic.mobile ? ` • ${mechanic.mobile}` : ""}
                                </p>
                            </div>
                            <div className="rr-ml-current-balance">
                                <span>Current Payable</span>
                                <strong>{mechanic.balance_label}</strong>
                            </div>
                        </section>

                        <section className="rr-ml-summary">
                            <SummaryCard icon="bi-box-arrow-in-right" tone="blue" label="Opening Balance" value={balance(summary.opening_balance)} />
                            <SummaryCard icon="bi-arrow-up-circle" tone="red" label="Period Debit" value={money(summary.period_debit)} />
                            <SummaryCard icon="bi-arrow-down-circle" tone="green" label="Period Credit" value={money(summary.period_credit)} />
                            <SummaryCard icon="bi-wallet2" tone="purple" label="Closing Payable" value={balance(summary.closing_balance)} />
                        </section>

                        <section className="rr-ml-table-card">
                            <div className="rr-ml-table-head">
                                <div>
                                    <i className="bi bi-journal-text"></i>
                                    <strong>Transaction Register</strong>
                                </div>
                                <span>{entries?.total ?? rows.length} entries</span>
                            </div>

                            <div className="rr-ml-table-wrap">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Date</th>
                                            <th>Transaction</th>
                                            <th>Reference</th>
                                            <th>Remarks</th>
                                            <th className="number">Debit</th>
                                            <th className="number">Credit</th>
                                            <th className="number">Balance</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {rows.length ? rows.map((entry) => (
                                            <tr
                                                key={entry.id}
                                                className={[
                                                    entry.is_reversal ? "reversal" : "",
                                                    isSalesReturn(entry.transaction_type)
                                                        ? "sales-return-row"
                                                        : "",
                                                ].filter(Boolean).join(" ")}
                                            >
                                                <td>{displayDate(entry.transaction_date)}</td>
                                                <td>
                                                    <span
                                                        className={[
                                                            "rr-ml-type",
                                                            typeTone(entry.transaction_type),
                                                            entry.is_reversal ? "reversed" : "",
                                                        ].filter(Boolean).join(" ")}
                                                    >
                                                        <i className={`bi ${typeIcon(entry.transaction_type)}`}></i>
                                                        {entry.type_label}
                                                    </span>
                                                </td>
                                                <td>
                                                    {entry.reference_url ? (
                                                        <Link className="rr-ml-reference" href={entry.reference_url}>
                                                            {entry.reference_no || `#${entry.reference_id}`}
                                                            <i className="bi bi-box-arrow-up-right"></i>
                                                        </Link>
                                                    ) : (entry.reference_no || "—")}
                                                </td>
                                                <td className="remarks" title={entry.remarks ?? ""}>
                                                    {entry.remarks || "—"}
                                                </td>
                                                <td className="number debit">{entry.debit > 0 ? money(entry.debit) : "—"}</td>
                                                <td className="number credit">{entry.credit > 0 ? money(entry.credit) : "—"}</td>
                                                <td className="number balance">{entry.balance_label}</td>
                                            </tr>
                                        )) : (
                                            <tr>
                                                <td colSpan="7">
                                                    <EmptyState text="No ledger entries found for the selected period." />
                                                </td>
                                            </tr>
                                        )}
                                    </tbody>
                                </table>
                            </div>

                            <Pagination links={entries?.links ?? []} />
                        </section>
                    </>
                ) : (
                    <section className="rr-ml-select-empty">
                        <span><i className="bi bi-person-lines-fill"></i></span>
                        <h2>Select a mechanic</h2>
                        <p>Search and select a mechanic above to display commission and payment transactions.</p>
                    </section>
                )}
            </div>
        </>
    );
}

function Field({ label, children }) {
    return <div className="rr-ml-field"><label>{label}</label>{children}</div>;
}

function SummaryCard({ icon, tone, label, value }) {
    return (
        <article className={`rr-ml-summary-card ${tone}`}>
            <span><i className={`bi ${icon}`}></i></span>
            <div><small>{label}</small><strong>{value}</strong></div>
        </article>
    );
}

function EmptyState({ text }) {
    return <div className="rr-ml-empty"><i className="bi bi-inbox"></i><span>{text}</span></div>;
}

function Pagination({ links }) {
    if (!Array.isArray(links) || links.length <= 3) return null;

    return (
        <div className="rr-ml-pagination">
            {links.map((link, index) => link.url ? (
                <Link
                    key={`${link.label}-${index}`}
                    href={link.url}
                    className={link.active ? "active" : ""}
                    preserveScroll
                    dangerouslySetInnerHTML={{ __html: link.label }}
                />
            ) : (
                <span key={`${link.label}-${index}`} dangerouslySetInnerHTML={{ __html: link.label }} />
            ))}
        </div>
    );
}

function cleanParams(values) {
    return Object.fromEntries(
        Object.entries(values).filter(([, value]) => value !== "" && value !== null),
    );
}

function money(value) {
    return new Intl.NumberFormat("en-IN", {
        style: "currency",
        currency: "INR",
        minimumFractionDigits: 2,
    }).format(Number(value ?? 0));
}

function balance(value) {
    const amount = Number(value ?? 0);
    if (amount > 0.009) return `${money(Math.abs(amount))} Cr`;
    if (amount < -0.009) return `${money(Math.abs(amount))} Dr`;
    return money(0);
}

function displayDate(value) {
    if (!value) return "—";
    return new Date(`${value}T00:00:00`).toLocaleDateString("en-IN");
}

function openDatePicker(event) {
    if (typeof event.currentTarget.showPicker === "function") {
        try {
            event.currentTarget.showPicker();
        } catch {
            // Browsers without programmatic picker access still use native input.
        }
    }
}

function initials(name) {
    return String(name ?? "M").trim().split(/\s+/).slice(0, 2).map((part) => part[0]).join("").toUpperCase();
}

function isSalesReturn(type) {
    return [
        "sales_return_commission",
        "sales_return_commission_reversal",
    ].includes(String(type ?? ""));
}

function typeTone(type) {
    const value = String(type ?? "");

    if (value === "commission") return "commission";
    if (value === "mechanic_payment") return "payment";
    if (value === "sales_return_commission") return "sales-return";
    if (value.includes("reversal")) return "reversal-type";
    if (value.includes("adjustment")) return "adjustment";
    if (value === "opening_balance") return "opening";

    return "default";
}

function typeIcon(type) {
    const value = String(type ?? "");

    if (value === "commission") return "bi-award";
    if (value === "mechanic_payment") return "bi-cash-stack";
    if (value === "sales_return_commission") return "bi-arrow-return-left";
    if (value.includes("reversal")) return "bi-arrow-counterclockwise";
    if (value.includes("adjustment")) return "bi-sliders";
    if (value === "opening_balance") return "bi-box-arrow-in-right";

    return "bi-journal-text";
}

function today() {
    return new Date().toISOString().slice(0, 10);
}

function firstDayOfMonth() {
    const date = new Date();
    return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}-01`;
}

Index.layout = (page) => <BusinessLayout>{page}</BusinessLayout>;
