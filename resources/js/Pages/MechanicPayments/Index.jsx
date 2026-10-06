import React, { useMemo, useState } from "react";

import { Head, Link, router } from "@inertiajs/react";

import BusinessLayout from "@/Layouts/BusinessLayout";
import MasterNavigation from "../../Components/Business/MasterNavigation";

import "../../../css/business/customer-payments.css";

export default function Index({
    payments = { data: [], links: [] },
    summary = {},
    filters = {},
    accounts = [],
    payment_modes = [],
}) {
    const records = Array.isArray(payments.data)
        ? payments.data
        : [];

    const [filterOpen, setFilterOpen] = useState(true);

    const [form, setForm] = useState({
        search: filters.search ?? "",
        status: filters.status ?? "",
        payment_mode: filters.payment_mode ?? "",
        account_id: filters.account_id
            ? String(filters.account_id)
            : "",
        from_date: filters.from_date ?? "",
        to_date: filters.to_date ?? "",
    });

    const [confirmation, setConfirmation] = useState({
        open: false,
        payment: null,
        processing: false,
    });

    const activeFilterCount = useMemo(
        () => Object.values(form).filter(Boolean).length,
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
            "/mechanic-payments",
            cleanObject(nextForm),
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
        const empty = {
            search: "",
            status: "",
            payment_mode: "",
            account_id: "",
            from_date: "",
            to_date: "",
        };

        setForm(empty);
        applyFilters(empty);
    };

    const openPayment = (event, payment) => {
        if (event.target.closest("a, button, input, select")) {
            return;
        }

        router.visit(`/mechanic-payments/${payment.id}`);
    };

    const requestCancellation = (payment) => {
        setConfirmation({
            open: true,
            payment,
            processing: false,
        });
    };

    const closeCancellation = () => {
        if (confirmation.processing) {
            return;
        }

        setConfirmation({
            open: false,
            payment: null,
            processing: false,
        });
    };

    const confirmCancellation = () => {
        if (!confirmation.payment) {
            return;
        }

        setConfirmation((current) => ({
            ...current,
            processing: true,
        }));

        router.patch(
            `/mechanic-payments/${confirmation.payment.id}/cancel`,
            {},
            {
                preserveScroll: true,
                onSuccess: closeCancellation,
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
            <Head title="Mechanic Payments" />

            <div className="rr-cp-page">
                <MasterNavigation
                    active="mechanic-payments"
                    title="Accounts"
                    subtitle="Mechanic commissions, payments and advances"
                />

                <header className="rr-cp-header">
                    <div>
                        <span className="rr-cp-eyebrow">
                            <i className="bi bi-wrench-adjustable-circle" />
                            Accounts Payable
                        </span>

                        <h1>Mechanic Payments</h1>

                        <p>
                            Review commission settlements, advances and
                            reversed payments for the active branch.
                        </p>
                    </div>

                    <Link
                        href="/mechanic-payments/create"
                        className="rr-cp-new-button"
                    >
                        <i className="bi bi-plus-lg" />
                        New Payment
                    </Link>
                </header>

                <section className="rr-cp-metrics">
                    <Metric
                        tone="green"
                        icon="bi-check-circle"
                        label="Completed Payments"
                        value={currency(summary.completed_amount)}
                        caption={`${integer(summary.completed_count)} payments`}
                    />

                    <Metric
                        tone="blue"
                        icon="bi-diagram-3"
                        label="Allocated"
                        value={currency(summary.allocated_amount)}
                        caption="Applied against commissions"
                    />

                    <Metric
                        tone="amber"
                        icon="bi-wallet2"
                        label="Mechanic Advance"
                        value={currency(summary.advance_amount)}
                        caption="Unallocated payment balance"
                    />

                    <Metric
                        tone="red"
                        icon="bi-arrow-counterclockwise"
                        label="Cancelled"
                        value={currency(summary.cancelled_amount)}
                        caption={`${integer(summary.cancelled_count)} reversed payments`}
                    />
                </section>

                <section
                    className={`rr-cp-filter-panel ${filterOpen ? "" : "collapsed"
                        }`}
                >
                    <div className="rr-cp-panel-title">
                        <div>
                            <i className="bi bi-funnel" />

                            <span>
                                <strong>Search &amp; Filters</strong>
                                <small>
                                    {activeFilterCount > 0
                                        ? `${activeFilterCount} active filters`
                                        : "Filter the payment register"}
                                </small>
                            </span>
                        </div>

                        <div className="rr-cp-panel-actions">
                            {activeFilterCount > 0 && (
                                <button
                                    type="button"
                                    onClick={resetFilters}
                                >
                                    <i className="bi bi-arrow-counterclockwise" />
                                    Reset
                                </button>
                            )}

                            <button
                                type="button"
                                className="icon-only"
                                title={
                                    filterOpen
                                        ? "Collapse filters"
                                        : "Expand filters"
                                }
                                onClick={() => setFilterOpen((open) => !open)}
                            >
                                <i
                                    className={`bi ${filterOpen
                                            ? "bi-chevron-up"
                                            : "bi-chevron-down"
                                        }`}
                                />
                            </button>
                        </div>
                    </div>

                    <form
                        className="rr-cp-filter-body"
                        onSubmit={submitFilters}
                    >
                        <div className="rr-cp-filter-grid">
                            <label className="rr-cp-field search">
                                <span>
                                    <i className="bi bi-search" />
                                    Search
                                </span>

                                <div className="rr-cp-search-input">
                                    <i className="bi bi-search" />

                                    <input
                                        type="search"
                                        value={form.search}
                                        placeholder="Payment no., mechanic or reference"
                                        onChange={(event) =>
                                            updateField(
                                                "search",
                                                event.target.value,
                                            )
                                        }
                                    />

                                    {form.search && (
                                        <button
                                            type="button"
                                            title="Clear search"
                                            onClick={() =>
                                                updateField("search", "")
                                            }
                                        >
                                            <i className="bi bi-x-lg" />
                                        </button>
                                    )}
                                </div>
                            </label>

                            <SelectField
                                label="Status"
                                value={form.status}
                                onChange={(value) =>
                                    updateField("status", value)
                                }
                            >
                                <option value="">All Statuses</option>
                                <option value="Completed">Completed</option>
                                <option value="Cancelled">Cancelled</option>
                            </SelectField>

                            <SelectField
                                label="Payment Mode"
                                value={form.payment_mode}
                                onChange={(value) =>
                                    updateField("payment_mode", value)
                                }
                            >
                                <option value="">All Modes</option>
                                {payment_modes.map((mode) => (
                                    <option key={mode} value={mode}>
                                        {mode}
                                    </option>
                                ))}
                            </SelectField>

                            <SelectField
                                label="Account"
                                value={form.account_id}
                                onChange={(value) =>
                                    updateField("account_id", value)
                                }
                            >
                                <option value="">All Accounts</option>
                                {accounts.map((account) => (
                                    <option
                                        key={account.id}
                                        value={account.id}
                                    >
                                        {account.account_name}
                                    </option>
                                ))}
                            </SelectField>

                            <DateField
                                label="From Date"
                                value={form.from_date}
                                onChange={(value) =>
                                    updateField("from_date", value)
                                }
                            />

                            <DateField
                                label="To Date"
                                value={form.to_date}
                                min={form.from_date || undefined}
                                onChange={(value) =>
                                    updateField("to_date", value)
                                }
                            />

                            <button
                                type="submit"
                                className="rr-cp-apply-button"
                            >
                                <i className="bi bi-search" />
                                Apply
                            </button>
                        </div>
                    </form>
                </section>

                <section className="rr-cp-table-panel">
                    <div className="rr-cp-table-heading">
                        <div>
                            <i className="bi bi-table rr-cp-table-icon" />

                            <span>
                                <strong>Payment Register</strong>
                                <small>
                                    {integer(
                                        payments.total ?? records.length,
                                    )} records
                                </small>
                            </span>
                        </div>
                    </div>

                    {records.length > 0 ? (
                        <>
                            <div className="rr-cp-table-wrap">
                                <table className="rr-cp-table">
                                    <thead>
                                        <tr>
                                            <th className="serial">#</th>
                                            <th>Payment</th>
                                            <th>Mechanic</th>
                                            <th>Account</th>
                                            <th>Allocation</th>
                                            <th className="amount">Amount</th>
                                            <th>Status</th>
                                            <th className="actions">Actions</th>
                                        </tr>
                                    </thead>

                                    <tbody>
                                        {records.map((payment, index) => (
                                            <PaymentRow
                                                key={payment.id}
                                                payment={payment}
                                                serial={rowNumber(
                                                    payments,
                                                    index,
                                                )}
                                                onOpen={openPayment}
                                                onCancel={requestCancellation}
                                            />
                                        ))}
                                    </tbody>
                                </table>
                            </div>

                            <Pagination paginator={payments} />
                        </>
                    ) : (
                        <div className="rr-cp-empty">
                            <i className="bi bi-wallet2" />
                            <strong>No mechanic payments found</strong>
                            <span>
                                Change the filters or record the first payment.
                            </span>

                            <Link href="/mechanic-payments/create">
                                <i className="bi bi-plus-lg" /> New Payment
                            </Link>
                        </div>
                    )}
                </section>
            </div>

            {confirmation.open && (
                <CancelModal
                    payment={confirmation.payment}
                    processing={confirmation.processing}
                    onClose={closeCancellation}
                    onConfirm={confirmCancellation}
                />
            )}
        </>
    );
}

function PaymentRow({ payment, serial, onOpen, onCancel }) {
    const cancelled = payment.status === "Cancelled";

    return (
        <tr
            className={cancelled ? "cancelled-row" : ""}
            tabIndex="0"
            onClick={(event) => onOpen(event, payment)}
            onKeyDown={(event) => {
                if (event.key === "Enter" || event.key === " ") {
                    event.preventDefault();
                    router.visit(`/mechanic-payments/${payment.id}`);
                }
            }}
        >
            <td className="serial">{serial}</td>

            <td>
                <Link
                    href={`/mechanic-payments/${payment.id}`}
                    className="rr-cp-receipt-number"
                >
                    {payment.payment_no}
                </Link>

                <small className="rr-cp-date">
                    <i className="bi bi-calendar3" />
                    {dateValue(payment.payment_date)}
                </small>

                {payment.reference_no && (
                    <small className="rr-cp-reference">
                        Ref: {payment.reference_no}
                    </small>
                )}
            </td>

            <td>
                <div className="rr-cp-customer">
                    <span>{initials(payment.mechanic?.mechanic_name)}</span>

                    <div>
                        <strong>
                            {payment.mechanic?.mechanic_name ?? "—"}
                        </strong>
                        <small>
                            {payment.mechanic?.mechanic_code ?? "No code"}
                            {payment.mechanic?.mobile
                                ? ` · ${payment.mechanic.mobile}`
                                : ""}
                        </small>
                    </div>
                </div>
            </td>

            <td>
                <div className="rr-cp-account">
                    <strong>{payment.account?.account_name ?? "—"}</strong>
                    <small>
                        <i className="bi bi-credit-card" />
                        {payment.payment_mode}
                    </small>
                </div>
            </td>

            <td>
                <div className="rr-cp-allocation">
                    <strong>{currency(payment.allocated_amount)}</strong>
                    <small>Allocated to commissions</small>

                    {numberValue(payment.advance_amount) > 0 && (
                        <em>
                            Advance {currency(payment.advance_amount)}
                        </em>
                    )}
                </div>
            </td>

            <td className="amount">
                <strong>{currency(payment.amount)}</strong>
            </td>

            <td>
                <span
                    className={`rr-cp-status ${cancelled ? "cancelled" : "completed"
                        }`}
                >
                    <i
                        className={`bi ${cancelled
                                ? "bi-x-circle"
                                : "bi-check-circle"
                            }`}
                    />
                    {payment.status}
                </span>
            </td>

            <td className="actions">
                <div className="rr-cp-row-actions">
                    <Link
                        href={`/mechanic-payments/${payment.id}`}
                        className="view"
                        title="View payment"
                    >
                        <i className="bi bi-eye" />
                    </Link>

                    <a
                        href={`/mechanic-payments/${payment.id}/print`}
                        className="print"
                        title="Print payment"
                        target="_blank"
                        rel="noreferrer"
                    >
                        <i className="bi bi-printer" />
                    </a>

                    <a
                        href={`/mechanic-payments/${payment.id}/pdf`}
                        className="pdf"
                        title="Download PDF"
                    >
                        <i className="bi bi-file-earmark-pdf" />
                    </a>

                    {!cancelled && (
                        <button
                            type="button"
                            className="cancel"
                            title="Cancel payment"
                            onClick={() => onCancel(payment)}
                        >
                            <i className="bi bi-x-lg" />
                        </button>
                    )}
                </div>
            </td>
        </tr>
    );
}

function Metric({ tone, icon, label, value, caption }) {
    return (
        <article className={`rr-cp-metric ${tone}`}>
            <span className="rr-cp-metric-icon">
                <i className={`bi ${icon}`} />
            </span>

            <div>
                <small>{label}</small>
                <strong>{value}</strong>
                <em>{caption}</em>
            </div>
        </article>
    );
}

function SelectField({ label, value, onChange, children }) {
    return (
        <label className="rr-cp-field">
            <span>{label}</span>
            <select
                value={value}
                onChange={(event) => onChange(event.target.value)}
            >
                {children}
            </select>
        </label>
    );
}

function DateField({ label, value, min, onChange }) {
    return (
        <label className="rr-cp-field">
            <span>
                <i className="bi bi-calendar3" />
                {label}
            </span>
            <input
                type="date"
                value={value}
                min={min}
                onChange={(event) => onChange(event.target.value)}
            />
        </label>
    );
}

function Pagination({ paginator }) {
    const links = Array.isArray(paginator.links)
        ? paginator.links
        : [];

    if (links.length <= 3) {
        return null;
    }

    return (
        <footer className="rr-cp-table-footer">
            <span>
                Showing {integer(paginator.from)}–{integer(paginator.to)} of{" "}
                {integer(paginator.total)}
            </span>

            <nav aria-label="Payment pagination">
                {links.map((link, index) => {
                    const label = decodeLabel(link.label);

                    if (!link.url) {
                        return (
                            <span key={`${label}-${index}`} className="disabled">
                                {label}
                            </span>
                        );
                    }

                    return (
                        <Link
                            key={`${label}-${index}`}
                            href={link.url}
                            preserveScroll
                            preserveState
                            className={link.active ? "active" : ""}
                        >
                            {label}
                        </Link>
                    );
                })}
            </nav>
        </footer>
    );
}

function CancelModal({ payment, processing, onClose, onConfirm }) {
    return (
        <div
            className="rr-cp-modal-backdrop"
            role="presentation"
            onMouseDown={(event) => {
                if (event.target === event.currentTarget) {
                    onClose();
                }
            }}
        >
            <section
                className="rr-cp-modal"
                role="dialog"
                aria-modal="true"
                aria-labelledby="cancel-payment-title"
            >
                <header>
                    <span>
                        <i className="bi bi-exclamation-triangle" />
                    </span>

                    <div>
                        <strong id="cancel-payment-title">
                            Cancel Mechanic Payment?
                        </strong>
                        <small>
                            Ledger and bank entries will be reversed.
                        </small>
                    </div>
                </header>

                <div className="rr-cp-modal-body">
                    <dl>
                        <div>
                            <dt>Payment No.</dt>
                            <dd>{payment?.payment_no}</dd>
                        </div>
                        <div>
                            <dt>Mechanic</dt>
                            <dd>{payment?.mechanic?.mechanic_name}</dd>
                        </div>
                        <div>
                            <dt>Amount</dt>
                            <dd>{currency(payment?.amount)}</dd>
                        </div>
                    </dl>

                    <p>
                        The original payment will remain available for audit.
                        This action cannot be undone from this screen.
                    </p>
                </div>

                <footer>
                    <button
                        type="button"
                        className="secondary"
                        disabled={processing}
                        onClick={onClose}
                    >
                        Keep Payment
                    </button>

                    <button
                        type="button"
                        className="danger"
                        disabled={processing}
                        onClick={onConfirm}
                    >
                        {processing ? (
                            <>
                                <i className="bi bi-arrow-repeat" /> Cancelling…
                            </>
                        ) : (
                            <>
                                <i className="bi bi-x-circle" /> Cancel Payment
                            </>
                        )}
                    </button>
                </footer>
            </section>
        </div>
    );
}

function cleanObject(values) {
    return Object.fromEntries(
        Object.entries(values).filter(([, value]) => value !== ""),
    );
}

function rowNumber(paginator, index) {
    return Number(paginator.from ?? 1) + index;
}

function numberValue(value) {
    const number = Number(value);
    return Number.isFinite(number) ? number : 0;
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

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return date.toLocaleDateString("en-IN", {
        day: "2-digit",
        month: "short",
        year: "numeric",
    });
}

function initials(name) {
    const parts = String(name ?? "M")
        .trim()
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2);

    return parts.map((part) => part[0]?.toUpperCase()).join("") || "M";
}

function decodeLabel(label) {
    return String(label ?? "")
        .replace(/&laquo;/g, "‹")
        .replace(/&raquo;/g, "›")
        .replace(/<[^>]*>/g, "");
}

Index.layout = (page) => (
    <BusinessLayout title="Mechanic Payments">
        {page}
    </BusinessLayout>
);
