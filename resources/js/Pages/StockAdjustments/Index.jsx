import React, { useMemo, useState } from "react";

import { Head, Link, router } from "@inertiajs/react";

import BusinessLayout from "@/Layouts/BusinessLayout";
import MasterNavigation from "../../Components/Business/MasterNavigation";
import ConfirmModal from "../../Components/ConfirmModal";

import "../../../css/business/stock-adjustment.css";

export default function Index({
    adjustments = { data: [], links: [], meta: {} },
    filters = {},
    summary = {},
}) {
    const records = Array.isArray(adjustments.data)
        ? adjustments.data
        : [];

    const [form, setForm] = useState({
        search: filters.search ?? "",
        type: filters.type ?? "",
        status: filters.status ?? "",
        from_date: filters.from_date ?? "",
        to_date: filters.to_date ?? "",
        per_page: String(filters.per_page ?? 25),
    });

    const [confirmation, setConfirmation] = useState({
        show: false,
        adjustment: null,
        processing: false,
    });

    const activeFilterCount = useMemo(
        () =>
            [
                form.search,
                form.type,
                form.status,
                form.from_date,
                form.to_date,
            ].filter(Boolean).length,
        [form],
    );

    const updateField = (field, value) => {
        setForm((current) => ({
            ...current,
            [field]: value,
        }));
    };

    const applyFilters = (nextForm = form) => {
        router.get(
            "/stock-adjustments",
            cleanFilters(nextForm),
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            },
        );
    };

    const submitFilters = (event) => {
        event.preventDefault();
        applyFilters();
    };

    const resetFilters = () => {
        const next = {
            search: "",
            type: "",
            status: "",
            from_date: "",
            to_date: "",
            per_page: "25",
        };

        setForm(next);
        applyFilters(next);
    };

    const changePageSize = (value) => {
        const next = {
            ...form,
            per_page: value,
        };

        setForm(next);
        applyFilters(next);
    };

    const openDetails = (event, adjustment) => {
        if (event.target.closest("a, button, input, select")) {
            return;
        }

        router.visit(`/stock-adjustments/${adjustment.id}`);
    };

    const askToCancel = (adjustment) => {
        setConfirmation({
            show: true,
            adjustment,
            processing: false,
        });
    };

    const closeConfirmation = () => {
        if (confirmation.processing) {
            return;
        }

        setConfirmation({
            show: false,
            adjustment: null,
            processing: false,
        });
    };

    const cancelAdjustment = () => {
        if (!confirmation.adjustment) {
            return;
        }

        setConfirmation((current) => ({
            ...current,
            processing: true,
        }));

        router.patch(
            `/stock-adjustments/${confirmation.adjustment.id}/cancel`,
            {},
            {
                preserveScroll: true,
                onFinish: () => {
                    setConfirmation({
                        show: false,
                        adjustment: null,
                        processing: false,
                    });
                },
            },
        );
    };

    return (
        <>
            <Head title="Stock Adjustments" />

            <div className="rr-adjustment-page rr-adjustment-index-page">
                <MasterNavigation
                    active="stock-adjustments"
                    title="Inventory"
                    subtitle="Stock corrections and audit history"
                />

                <div className="rr-adjustment-index-ribbon">
                    <div>
                        <i className="bi bi-grid-1x2"></i>
                        <span>Inventory</span>
                        <i className="bi bi-chevron-right"></i>
                        <strong>Stock Adjustment Register</strong>
                    </div>

                    <div>
                        <i className="bi bi-shield-check"></i>
                        Ledger-controlled records
                    </div>
                </div>

                <header className="rr-adjustment-header rr-adjustment-index-hero">
                    <div className="rr-adjustment-heading">
                        <div className="rr-adjustment-heading-icon">
                            <i className="bi bi-sliders"></i>
                        </div>

                        <div>
                            <span>Inventory Control</span>
                            <h1>Stock Adjustments</h1>
                            <small>
                                Review increases, decreases and cancelled entries
                            </small>
                        </div>
                    </div>

                    <Link
                        href="/stock-adjustments/create"
                        className="rr-adjustment-create"
                    >
                        <i className="bi bi-plus-lg"></i>
                        New Adjustment
                    </Link>
                </header>

                <section className="rr-adjustment-metrics">
                    <Metric
                        icon="bi-journal-check"
                        label="Total Adjustments"
                        value={integer(summary.total_adjustments)}
                        tone="slate"
                        caption="All registered documents"
                    />

                    <Metric
                        icon="bi-check2-circle"
                        label="Completed"
                        value={integer(summary.completed)}
                        tone="blue"
                        caption="Active ledger entries"
                    />

                    <Metric
                        icon="bi-box-arrow-in-down"
                        label="Stock Increased"
                        value={quantity(summary.increase_quantity)}
                        tone="green"
                        suffix="Qty"
                        caption="Total quantity added"
                    />

                    <Metric
                        icon="bi-box-arrow-up"
                        label="Stock Decreased"
                        value={quantity(summary.decrease_quantity)}
                        tone="red"
                        suffix="Qty"
                        caption="Total quantity removed"
                    />

                    <Metric
                        icon="bi-x-octagon"
                        label="Cancelled"
                        value={integer(summary.cancelled)}
                        tone="amber"
                        caption="Reversed documents"
                    />
                </section>

                <section className="rr-adjustment-window rr-adjustment-filter-window">
                    <div className="rr-adjustment-window-title">
                        <div>
                            <i className="bi bi-funnel"></i>
                            <strong>Search &amp; Filter</strong>

                            {activeFilterCount > 0 && (
                                <span className="rr-adjustment-filter-count">
                                    {activeFilterCount} active
                                </span>
                            )}
                        </div>

                        <button
                            type="button"
                            className="rr-adjustment-reset"
                            onClick={resetFilters}
                        >
                            <i className="bi bi-arrow-counterclockwise"></i>
                            Reset
                        </button>
                    </div>

                    <form
                        className="rr-adjustment-filter-body"
                        onSubmit={submitFilters}
                    >
                        <FilterField
                            className="rr-adjustment-search-field"
                            label="Search"
                            icon="bi-search"
                        >
                            <input
                                type="search"
                                value={form.search}
                                placeholder="Adjustment no., product or remarks"
                                onChange={(event) =>
                                    updateField("search", event.target.value)
                                }
                            />
                        </FilterField>

                        <FilterField label="Adjustment Type" icon="bi-arrow-left-right">
                            <select
                                value={form.type}
                                onChange={(event) =>
                                    updateField("type", event.target.value)
                                }
                            >
                                <option value="">All Types</option>
                                <option value="increase">Stock Increase</option>
                                <option value="decrease">Stock Decrease</option>
                            </select>
                        </FilterField>

                        <FilterField label="Status" icon="bi-check2-square">
                            <select
                                value={form.status}
                                onChange={(event) =>
                                    updateField("status", event.target.value)
                                }
                            >
                                <option value="">All Statuses</option>
                                <option value="Completed">Completed</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                        </FilterField>

                        <FilterField label="From Date" icon="bi-calendar3">
                            <DateInput
                                value={form.from_date}
                                onChange={(value) =>
                                    updateField("from_date", value)
                                }
                            />
                        </FilterField>

                        <FilterField label="To Date" icon="bi-calendar3">
                            <DateInput
                                value={form.to_date}
                                min={form.from_date || undefined}
                                onChange={(value) =>
                                    updateField("to_date", value)
                                }
                            />
                        </FilterField>

                        <button
                            type="submit"
                            className="rr-adjustment-filter-button"
                        >
                            <i className="bi bi-search"></i>
                            Apply Filter
                        </button>
                    </form>
                </section>

                <section className="rr-adjustment-window rr-adjustment-table-window">
                    <div className="rr-adjustment-window-title rr-adjustment-register-title">
                        <div>
                            <i className="bi bi-table"></i>
                            <strong>Adjustment Register</strong>
                            <span className="rr-adjustment-record-count">
                                {integer(
                                    adjustments.total
                                    ?? adjustments.meta?.total
                                    ?? records.length,
                                )} records
                            </span>
                        </div>

                        <div className="rr-adjustment-page-size">
                            <label htmlFor="adjustment-page-size">
                                Rows
                            </label>
                            <select
                                id="adjustment-page-size"
                                value={form.per_page}
                                onChange={(event) =>
                                    changePageSize(event.target.value)
                                }
                            >
                                <option value="25">25</option>
                                <option value="50">50</option>
                                <option value="100">100</option>
                                <option value="200">200</option>
                            </select>
                        </div>
                    </div>

                    <div className="rr-adjustment-table-scroll">
                        <table className="rr-adjustment-table">
                            <thead>
                                <tr>
                                    <th className="serial">#</th>
                                    <th>Adjustment</th>
                                    <th>Date</th>
                                    <th>Type</th>
                                    <th className="number">Items</th>
                                    <th className="number">Total Qty</th>
                                    <th className="number">Stock Value</th>
                                    <th>Created By</th>
                                    <th>Status</th>
                                    <th className="actions">Actions</th>
                                </tr>
                            </thead>

                            <tbody>
                                {records.length > 0 ? (
                                    records.map((adjustment, index) => {
                                        const completed =
                                            adjustment.status === "Completed";

                                        return (
                                            <tr
                                                key={adjustment.id}
                                                className={
                                                    completed
                                                        ? ""
                                                        : "is-cancelled"
                                                }
                                                tabIndex="0"
                                                onClick={(event) =>
                                                    openDetails(event, adjustment)
                                                }
                                                onKeyDown={(event) => {
                                                    if (
                                                        event.key === "Enter"
                                                        || event.key === " "
                                                    ) {
                                                        event.preventDefault();
                                                        router.visit(
                                                            `/stock-adjustments/${adjustment.id}`,
                                                        );
                                                    }
                                                }}
                                            >
                                                <td className="serial">
                                                    {rowNumber(
                                                        adjustments,
                                                        index,
                                                    )}
                                                </td>

                                                <td>
                                                    <Link
                                                        href={`/stock-adjustments/${adjustment.id}`}
                                                        className="rr-adjustment-number"
                                                    >
                                                        {adjustment.adjustment_no}
                                                    </Link>
                                                    <small>
                                                        {truncate(
                                                            adjustment.remarks,
                                                            42,
                                                        ) || "No remarks"}
                                                    </small>
                                                </td>

                                                <td className="date-cell">
                                                    {dateValue(
                                                        adjustment.adjustment_date,
                                                    )}
                                                </td>

                                                <td>
                                                    <TypeBadge
                                                        type={adjustment.type}
                                                    />
                                                </td>

                                                <td className="number strong">
                                                    {integer(
                                                        adjustment.items_count,
                                                    )}
                                                </td>

                                                <td className="number quantity">
                                                    {quantity(
                                                        adjustment.total_quantity,
                                                    )}
                                                </td>

                                                <td className="number money">
                                                    {currency(
                                                        adjustment.total_cost,
                                                    )}
                                                </td>

                                                <td>
                                                    <div className="rr-adjustment-user">
                                                        <i className="bi bi-person-circle"></i>
                                                        <span>
                                                            {adjustment.creator?.name
                                                                ?? "System"}
                                                        </span>
                                                    </div>
                                                </td>

                                                <td>
                                                    <StatusBadge
                                                        status={adjustment.status}
                                                    />
                                                </td>

                                                <td className="actions">
                                                    <div className="rr-adjustment-row-actions">
                                                        <Link
                                                            href={`/stock-adjustments/${adjustment.id}`}
                                                            className="view"
                                                            title="View adjustment"
                                                        >
                                                            <i className="bi bi-eye"></i>
                                                        </Link>

                                                        {completed && (
                                                            <Link
                                                                href={`/stock-adjustments/${adjustment.id}/edit`}
                                                                className="edit"
                                                                title="Edit adjustment"
                                                            >
                                                                <i className="bi bi-pencil-square"></i>
                                                            </Link>
                                                        )}

                                                        <a
                                                            href={`/stock-adjustments/${adjustment.id}/print`}
                                                            target="_blank"
                                                            rel="noreferrer"
                                                            className="print"
                                                            title="Print adjustment"
                                                        >
                                                            <i className="bi bi-printer"></i>
                                                        </a>

                                                        <a
                                                            href={`/stock-adjustments/${adjustment.id}/pdf`}
                                                            className="pdf"
                                                            title="Download PDF"
                                                        >
                                                            <i className="bi bi-file-earmark-pdf"></i>
                                                        </a>

                                                        {completed && (
                                                            <button
                                                                type="button"
                                                                className="cancel"
                                                                title="Cancel adjustment"
                                                                onClick={() =>
                                                                    askToCancel(
                                                                        adjustment,
                                                                    )
                                                                }
                                                            >
                                                                <i className="bi bi-x-octagon"></i>
                                                            </button>
                                                        )}
                                                    </div>
                                                </td>
                                            </tr>
                                        );
                                    })
                                ) : (
                                    <tr>
                                        <td
                                            colSpan="10"
                                            className="rr-adjustment-empty"
                                        >
                                            <i className="bi bi-inbox"></i>
                                            <strong>No adjustments found</strong>
                                            <span>
                                                Change the filters or create a new stock adjustment.
                                            </span>
                                            <Link href="/stock-adjustments/create">
                                                <i className="bi bi-plus-lg"></i>
                                                New Adjustment
                                            </Link>
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>

                    <TableFooter
                        adjustments={adjustments}
                        records={records}
                    />
                </section>

                <ConfirmModal
                    show={confirmation.show}
                    title="Cancel Stock Adjustment"
                    message={`Cancel "${confirmation.adjustment?.adjustment_no ?? ""}"? The system will create reversing stock-ledger entries. The document will remain available for audit history.`}
                    confirmText="Cancel Adjustment"
                    cancelText="Keep Adjustment"
                    icon="bi-x-octagon"
                    variant="danger"
                    processing={confirmation.processing}
                    onConfirm={cancelAdjustment}
                    onCancel={closeConfirmation}
                />
            </div>
        </>
    );
}

function Metric({
    icon,
    label,
    value,
    tone,
    suffix = "",
    caption = "",
}) {
    return (
        <article className={`rr-adjustment-metric ${tone}`}>
            <div className="rr-adjustment-metric-icon">
                <i className={`bi ${icon}`}></i>
            </div>

            <div className="rr-adjustment-metric-content">
                <span>{label}</span>
                <strong>
                    {value}
                    {suffix && <small>{suffix}</small>}
                </strong>
                <em>{caption}</em>
            </div>

            <i className="bi bi-chevron-right rr-adjustment-metric-arrow"></i>
        </article>
    );
}

function FilterField({ label, icon, className = "", children }) {
    return (
        <label className={`rr-adjustment-field ${className}`}>
            <span>
                <i className={`bi ${icon}`}></i>
                {label}
            </span>
            {children}
        </label>
    );
}

function DateInput({ value, min, onChange }) {
    const openPicker = (event) => {
        if (typeof event.currentTarget.showPicker === "function") {
            event.currentTarget.showPicker();
        }
    };

    return (
        <input
            type="date"
            value={value}
            min={min}
            onClick={openPicker}
            onFocus={openPicker}
            onChange={(event) => onChange(event.target.value)}
        />
    );
}

function TypeBadge({ type }) {
    const increase = type === "increase";

    return (
        <span
            className={`rr-adjustment-type ${increase ? "increase" : "decrease"}`}
        >
            <i
                className={`bi ${increase
                    ? "bi-arrow-down-left"
                    : "bi-arrow-up-right"
                    }`}
            ></i>
            {increase ? "Increase" : "Decrease"}
        </span>
    );
}

function StatusBadge({ status }) {
    const completed = status === "Completed";

    return (
        <span
            className={`rr-adjustment-status ${completed ? "completed" : "cancelled"}`}
        >
            <i
                className={`bi ${completed
                    ? "bi-check2-circle"
                    : "bi-x-circle"
                    }`}
            ></i>
            {status}
        </span>
    );
}

function TableFooter({ adjustments, records }) {
    const links = Array.isArray(adjustments.links)
        ? adjustments.links
        : [];
    const from = adjustments.from ?? adjustments.meta?.from ?? 0;
    const to = adjustments.to ?? adjustments.meta?.to ?? records.length;
    const total = adjustments.total ?? adjustments.meta?.total ?? records.length;

    return (
        <footer className="rr-adjustment-table-footer">
            <span>
                Showing <strong>{integer(from)}</strong> to{" "}
                <strong>{integer(to)}</strong> of{" "}
                <strong>{integer(total)}</strong> records
            </span>

            {links.length > 3 && (
                <nav aria-label="Stock adjustment pages">
                    {links.map((link, index) => (
                        <Link
                            key={`${link.label}-${index}`}
                            href={link.url ?? "#"}
                            preserveScroll
                            preserveState
                            className={`${link.active ? "active" : ""
                                } ${!link.url ? "disabled" : ""}`}
                            dangerouslySetInnerHTML={{
                                __html: link.label,
                            }}
                            onClick={(event) => {
                                if (!link.url) {
                                    event.preventDefault();
                                }
                            }}
                        />
                    ))}
                </nav>
            )}
        </footer>
    );
}

function cleanFilters(form) {
    return Object.fromEntries(
        Object.entries(form).filter(([, value]) => value !== ""),
    );
}

function rowNumber(adjustments, index) {
    const from = adjustments.from ?? adjustments.meta?.from ?? 1;

    return Number(from) + index;
}

function numberValue(value) {
    const parsed = Number(value);

    return Number.isFinite(parsed) ? parsed : 0;
}

function integer(value) {
    return Math.round(numberValue(value)).toLocaleString("en-IN");
}

function quantity(value) {
    return numberValue(value).toLocaleString("en-IN", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

function currency(value) {
    return `₹${numberValue(value).toLocaleString("en-IN", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })}`;
}

function dateValue(value) {
    if (!value) {
        return "—";
    }

    const date = new Date(`${String(value).slice(0, 10)}T00:00:00`);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return date.toLocaleDateString("en-IN", {
        day: "2-digit",
        month: "short",
        year: "numeric",
    });
}

function truncate(value, length) {
    const text = String(value ?? "").trim();

    return text.length > length
        ? `${text.slice(0, length - 1)}…`
        : text;
}

Index.layout = (page) => (
    <BusinessLayout title="Stock Adjustments">
        {page}
    </BusinessLayout>
);
