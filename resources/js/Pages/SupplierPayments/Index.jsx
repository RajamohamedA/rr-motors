import React, {
    useMemo,
    useState,
} from "react";

import {
    Head,
    Link,
    router,
} from "@inertiajs/react";

import BusinessLayout from "@/Layouts/BusinessLayout";
import MasterNavigation from "../../Components/Business/MasterNavigation";
import ConfirmModal from "../../Components/ConfirmModal";

import "../../../css/business/supplier-payment.css";

export default function Index({
    payments = {
        data: [],
        links: [],
        meta: {},
    },
    summary = {},
    filters = {},
    payment_modes = [],
}) {
    const records = Array.isArray(payments.data)
        ? payments.data
        : [];

    const [form, setForm] = useState({
        search: filters.search ?? "",
        status: filters.status ?? "",
        payment_mode: filters.payment_mode ?? "",
        from_date: filters.from_date ?? "",
        to_date: filters.to_date ?? "",
    });

    const [confirmation, setConfirmation] = useState({
        show: false,
        payment: null,
        processing: false,
    });

    const activeFilterCount = useMemo(
        () => Object.values(form).filter((value) => value !== "").length,
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
            "/supplier-payments",
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
            status: "",
            payment_mode: "",
            from_date: "",
            to_date: "",
        };

        setForm(next);
        applyFilters(next);
    };

    const openDetails = (event, payment) => {
        if (event.target.closest("a, button, input, select")) {
            return;
        }

        router.visit(`/supplier-payments/${payment.id}`);
    };

    const openCancel = (payment) => {
        setConfirmation({
            show: true,
            payment,
            processing: false,
        });
    };

    const closeCancel = () => {
        if (confirmation.processing) {
            return;
        }

        setConfirmation({
            show: false,
            payment: null,
            processing: false,
        });
    };

    const cancelPayment = () => {
        if (!confirmation.payment) {
            return;
        }

        setConfirmation((current) => ({
            ...current,
            processing: true,
        }));

        router.patch(
            `/supplier-payments/${confirmation.payment.id}/cancel`,
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    setConfirmation({
                        show: false,
                        payment: null,
                        processing: false,
                    });
                },
                onError: () => {
                    setConfirmation((current) => ({
                        ...current,
                        processing: false,
                    }));
                },
                onFinish: () => {
                    setConfirmation((current) => ({
                        ...current,
                        processing: false,
                    }));
                },
            },
        );
    };

    return (
        <>
            <Head title="Supplier Payments" />

            <div className="rr-sp-page">
                <MasterNavigation />

                <header className="rr-sp-header">
                    <div>
                        <span className="rr-sp-eyebrow">
                            <i className="bi bi-wallet2"></i>
                            Accounts / Payables
                        </span>

                        <h1>Supplier Payments</h1>

                        <p>
                            Record supplier settlements, advances and auditable
                            account movements for the active branch.
                        </p>
                    </div>

                    <Link
                        href="/supplier-payments/create"
                        className="rr-sp-new-button"
                    >
                        <i className="bi bi-plus-lg"></i>
                        New Payment
                    </Link>
                </header>

                <section className="rr-sp-metrics">
                    <Metric
                        icon="bi-check2-circle"
                        label="Completed Payments"
                        value={currency(summary.completed_amount)}
                        caption={`${integer(summary.completed_count)} payment(s)`}
                        tone="green"
                    />

                    <Metric
                        icon="bi-cash-stack"
                        label="Supplier Advances"
                        value={currency(summary.advance_amount)}
                        caption="Not linked to a purchase"
                        tone="blue"
                    />

                    <Metric
                        icon="bi-x-octagon"
                        label="Cancelled Value"
                        value={currency(summary.cancelled_amount)}
                        caption="Retained for audit"
                        tone="red"
                    />

                    <Metric
                        icon="bi-funnel"
                        label="Active Filters"
                        value={integer(activeFilterCount)}
                        caption={activeFilterCount ? "Filter(s) applied" : "Showing all records"}
                        tone="amber"
                    />
                </section>

                <section className="rr-sp-filter-panel">
                    <div className="rr-sp-panel-title">
                        <div>
                            <i className="bi bi-sliders"></i>

                            <span>
                                <strong>Search and filters</strong>
                                <small>Find payments by supplier, number or date</small>
                            </span>
                        </div>

                        {activeFilterCount > 0 && (
                            <button type="button" onClick={resetFilters}>
                                <i className="bi bi-arrow-counterclockwise"></i>
                                Clear all
                            </button>
                        )}
                    </div>

                    <form onSubmit={submitFilters} className="rr-sp-filter-grid">
                        <FilterField
                            label="Search"
                            icon="bi-search"
                            className="search"
                        >
                            <div className="rr-sp-search-input">
                                <i className="bi bi-search"></i>

                                <input
                                    type="search"
                                    value={form.search}
                                    placeholder="Payment no., supplier, phone or reference"
                                    onChange={(event) => updateField("search", event.target.value)}
                                />

                                {form.search && (
                                    <button
                                        type="button"
                                        title="Clear search"
                                        onClick={() => updateField("search", "")}
                                    >
                                        <i className="bi bi-x-lg"></i>
                                    </button>
                                )}
                            </div>
                        </FilterField>

                        <FilterField label="Status" icon="bi-activity">
                            <select
                                value={form.status}
                                onChange={(event) => updateField("status", event.target.value)}
                            >
                                <option value="">All statuses</option>
                                <option value="Completed">Completed</option>
                                <option value="Cancelled">Cancelled</option>
                            </select>
                        </FilterField>

                        <FilterField label="Payment mode" icon="bi-credit-card">
                            <select
                                value={form.payment_mode}
                                onChange={(event) => updateField("payment_mode", event.target.value)}
                            >
                                <option value="">All modes</option>

                                {payment_modes.map((mode) => (
                                    <option key={mode} value={mode}>
                                        {mode}
                                    </option>
                                ))}
                            </select>
                        </FilterField>

                        <FilterField label="From date" icon="bi-calendar3">
                            <DateInput
                                value={form.from_date}
                                max={form.to_date || undefined}
                                onChange={(value) => updateField("from_date", value)}
                            />
                        </FilterField>

                        <FilterField label="To date" icon="bi-calendar3">
                            <DateInput
                                value={form.to_date}
                                min={form.from_date || undefined}
                                onChange={(value) => updateField("to_date", value)}
                            />
                        </FilterField>

                        <div className="rr-sp-filter-actions">
                            <button type="submit" className="apply">
                                <i className="bi bi-funnel-fill"></i>
                                Apply
                            </button>

                            <button type="button" className="reset" onClick={resetFilters}>
                                <i className="bi bi-arrow-counterclockwise"></i>
                            </button>
                        </div>
                    </form>
                </section>

                <section className="rr-sp-table-panel">
                    <div className="rr-sp-table-heading">
                        <div>
                            <span className="rr-sp-table-icon">
                                <i className="bi bi-receipt"></i>
                            </span>

                            <span>
                                <strong>Payment register</strong>
                                <small>{integer(payments.total ?? payments.meta?.total ?? records.length)} record(s)</small>
                            </span>
                        </div>

                        <Link href="/supplier-payments/create">
                            <i className="bi bi-plus-circle"></i>
                            Record payment
                        </Link>
                    </div>

                    <div className="rr-sp-table-wrap">
                        <table className="rr-sp-table">
                            <thead>
                                <tr>
                                    <th className="serial">#</th>
                                    <th>Payment</th>
                                    <th>Date</th>
                                    <th>Supplier</th>
                                    <th>Purchase</th>
                                    <th>Account / Mode</th>
                                    <th className="amount">Amount</th>
                                    <th>Status</th>
                                    <th className="actions">Actions</th>
                                </tr>
                            </thead>

                            <tbody>
                                {records.length ? (
                                    records.map((payment, index) => (
                                        <tr
                                            key={payment.id}
                                            className={payment.status === "Cancelled" ? "cancelled-row" : ""}
                                            onClick={(event) => openDetails(event, payment)}
                                        >
                                            <td className="serial">{rowNumber(payments, index)}</td>

                                            <td>
                                                <Link
                                                    href={`/supplier-payments/${payment.id}`}
                                                    className="rr-sp-payment-number"
                                                >
                                                    {payment.payment_no}
                                                </Link>

                                                <small className="rr-sp-reference">
                                                    {payment.reference_no || "No external reference"}
                                                </small>
                                            </td>

                                            <td>
                                                <span className="rr-sp-date">
                                                    <i className="bi bi-calendar3"></i>
                                                    {dateValue(payment.payment_date)}
                                                </span>
                                            </td>

                                            <td>
                                                <div className="rr-sp-supplier">
                                                    <span>{initials(payment.supplier?.supplier_name)}</span>

                                                    <div>
                                                        <strong>{payment.supplier?.supplier_name ?? "—"}</strong>
                                                        <small>
                                                            {payment.supplier?.supplier_code ?? "No code"}
                                                            {payment.supplier?.phone ? ` • ${payment.supplier.phone}` : ""}
                                                        </small>
                                                    </div>
                                                </div>
                                            </td>

                                            <td>
                                                {payment.purchase ? (
                                                    <div className="rr-sp-purchase">
                                                        <strong>{payment.purchase.purchase_no}</strong>
                                                        <small>{dateValue(payment.purchase.purchase_date)}</small>
                                                    </div>
                                                ) : (
                                                    <span className="rr-sp-advance">
                                                        <i className="bi bi-lightning-charge-fill"></i>
                                                        Advance
                                                    </span>
                                                )}
                                            </td>

                                            <td>
                                                <div className="rr-sp-account">
                                                    <strong>{payment.account?.account_name ?? "Account unavailable"}</strong>
                                                    <small>
                                                        <i className={paymentModeIcon(payment.payment_mode)}></i>
                                                        {payment.payment_mode}
                                                    </small>
                                                </div>
                                            </td>

                                            <td className="amount">
                                                <strong>{currency(payment.amount)}</strong>
                                            </td>

                                            <td>
                                                <StatusBadge status={payment.status} />
                                            </td>

                                            <td className="actions">
                                                <div className="rr-sp-row-actions">
                                                    <Link
                                                        href={`/supplier-payments/${payment.id}`}
                                                        className="view"
                                                        title="View payment"
                                                    >
                                                        <i className="bi bi-eye"></i>
                                                    </Link>

                                                    {payment.can_cancel && (
                                                        <button
                                                            type="button"
                                                            className="cancel"
                                                            title="Cancel payment"
                                                            onClick={() => openCancel(payment)}
                                                        >
                                                            <i className="bi bi-x-octagon"></i>
                                                        </button>
                                                    )}
                                                </div>
                                            </td>
                                        </tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td colSpan={9} className="rr-sp-empty">
                                            <i className="bi bi-inbox"></i>
                                            <strong>No supplier payments found</strong>
                                            <span>Change the filters or record a new supplier payment.</span>
                                            <Link href="/supplier-payments/create">
                                                <i className="bi bi-plus-lg"></i>
                                                New Payment
                                            </Link>
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>

                    <TableFooter payments={payments} records={records} />
                </section>

                <ConfirmModal
                    show={confirmation.show}
                    title="Cancel Supplier Payment"
                    message={`Cancel "${confirmation.payment?.payment_no ?? ""}" for ${currency(confirmation.payment?.amount)}? An opposite account entry will be created and the original payment will remain in the audit history.`}
                    confirmText="Cancel Payment"
                    cancelText="Keep Payment"
                    icon="bi-x-octagon"
                    variant="danger"
                    processing={confirmation.processing}
                    onConfirm={cancelPayment}
                    onCancel={closeCancel}
                />
            </div>
        </>
    );
}

function Metric({ icon, label, value, caption, tone }) {
    return (
        <article className={`rr-sp-metric ${tone}`}>
            <span className="rr-sp-metric-icon">
                <i className={`bi ${icon}`}></i>
            </span>

            <div>
                <small>{label}</small>
                <strong>{value}</strong>
                <em>{caption}</em>
            </div>
        </article>
    );
}

function FilterField({ label, icon, className = "", children }) {
    return (
        <label className={`rr-sp-field ${className}`}>
            <span>
                <i className={`bi ${icon}`}></i>
                {label}
            </span>
            {children}
        </label>
    );
}

function DateInput({ value, min, max, onChange }) {
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
            max={max}
            onClick={openPicker}
            onChange={(event) => onChange(event.target.value)}
        />
    );
}

function StatusBadge({ status }) {
    const completed = status === "Completed";

    return (
        <span className={`rr-sp-status ${completed ? "completed" : "cancelled"}`}>
            <i className={`bi ${completed ? "bi-check2-circle" : "bi-x-circle"}`}></i>
            {status}
        </span>
    );
}

function TableFooter({ payments, records }) {
    const links = Array.isArray(payments.links) ? payments.links : [];
    const from = payments.from ?? payments.meta?.from ?? (records.length ? 1 : 0);
    const to = payments.to ?? payments.meta?.to ?? records.length;
    const total = payments.total ?? payments.meta?.total ?? records.length;

    return (
        <footer className="rr-sp-table-footer">
            <span>
                Showing <strong>{integer(from)}</strong> to <strong>{integer(to)}</strong> of <strong>{integer(total)}</strong>
            </span>

            {links.length > 3 && (
                <nav aria-label="Supplier payment pages">
                    {links.map((link, index) => (
                        <Link
                            key={`${link.label}-${index}`}
                            href={link.url ?? "#"}
                            preserveScroll
                            preserveState
                            className={`${link.active ? "active" : ""} ${!link.url ? "disabled" : ""}`}
                            dangerouslySetInnerHTML={{ __html: link.label }}
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

function rowNumber(payments, index) {
    return Number(payments.from ?? payments.meta?.from ?? 1) + index;
}

function numberValue(value) {
    const parsed = Number(value);
    return Number.isFinite(parsed) ? parsed : 0;
}

function integer(value) {
    return Math.round(numberValue(value)).toLocaleString("en-IN");
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

    return Number.isNaN(date.getTime())
        ? value
        : date.toLocaleDateString("en-IN", {
            day: "2-digit",
            month: "short",
            year: "numeric",
        });
}

function initials(value) {
    const words = String(value ?? "Supplier")
        .trim()
        .split(/\s+/)
        .filter(Boolean);

    return words.slice(0, 2).map((word) => word[0]).join("").toUpperCase();
}

function paymentModeIcon(mode) {
    const normalized = String(mode ?? "").toLowerCase();

    if (normalized === "cash") return "bi bi-cash";
    if (normalized.includes("bank") || normalized === "cheque") return "bi bi-bank";
    if (normalized === "card") return "bi bi-credit-card";
    return "bi bi-phone";
}

Index.layout = (page) => (
    <BusinessLayout title="Supplier Payments">
        {page}
    </BusinessLayout>
);
