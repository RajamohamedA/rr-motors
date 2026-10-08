import React, { useMemo, useState } from "react";
import { Head, Link, router, usePage } from "@inertiajs/react";

import BusinessLayout from "@/Layouts/BusinessLayout";
import MasterNavigation from "../../Components/Business/MasterNavigation";
import ConfirmModal from "../../Components/ConfirmModal";

import "../../../css/business/vouchers.css";

export default function Index({
    vouchers = {},
    accounts = [],
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
        voucher_type: filters.voucher_type ?? "all",
        status: filters.status ?? "all",
        account_id: filters.account_id ? String(filters.account_id) : "",
        from_date: filters.from_date ?? "",
        to_date: filters.to_date ?? "",
        per_page: String(filters.per_page ?? 20),
    });
    const [filtersOpen, setFiltersOpen] = useState(true);
    const [selectedVoucher, setSelectedVoucher] = useState(null);
    const [processing, setProcessing] = useState(false);

    const rows = vouchers?.data ?? [];
    const hasFilters = useMemo(
        () =>
            form.search.trim() !== "" ||
            form.voucher_type !== "all" ||
            form.status !== "all" ||
            form.account_id !== "" ||
            form.from_date !== "" ||
            form.to_date !== "" ||
            form.per_page !== "20",
        [form],
    );

    const update = (key, value) => {
        setForm((current) => ({ ...current, [key]: value }));
    };

    const applyFilters = (event) => {
        event?.preventDefault();
        router.get("/vouchers", cleanParams(form), {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    };

    const clearFilters = () => {
        const cleared = {
            search: "",
            voucher_type: "all",
            status: "all",
            account_id: "",
            from_date: "",
            to_date: "",
            per_page: "20",
        };

        setForm(cleared);
        router.get("/vouchers", {}, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    };

    const cancelVoucher = () => {
        if (!selectedVoucher || processing) return;

        setProcessing(true);
        router.patch(
            `/vouchers/${selectedVoucher.id}/cancel`,
            {},
            {
                preserveScroll: true,
                onFinish: () => {
                    setProcessing(false);
                    setSelectedVoucher(null);
                },
            },
        );
    };

    return (
        <>
            <Head title="Receipt & Payment Vouchers" />

            <main className="rr-vch-page rr-vch-index-page">
                <MasterNavigation
                    active="vouchers"
                    title="Receipt & Payment Vouchers"
                    subtitle={
                        context.store_name
                            ? `Cash flow register • ${context.store_name}`
                            : "Branch receipt and payment voucher register"
                    }
                />

                {(flash.success || flash.error) && (
                    <div
                        className={`rr-vch-alert ${flash.error ? "error" : "success"}`}
                        role="alert"
                    >
                        <i
                            className={`bi ${flash.error ? "bi-exclamation-octagon" : "bi-check-circle"
                                }`}
                        />
                        <span>{flash.error ?? flash.success}</span>
                    </div>
                )}

                <section className="rr-vch-commandbar">
                    <div className="rr-vch-command-copy">
                        <span className="rr-vch-eyebrow">ACCOUNTING REGISTER</span>
                        <h1>Money In & Money Out</h1>
                        <p>
                            Record non-invoice receipts and payments with a complete Cash/Bank
                            audit trail.
                        </p>
                    </div>

                    <div className="rr-vch-command-actions">
                        {can("voucher.create") && (
                            <>
                                <Link
                                    href="/vouchers/create?type=receipt"
                                    className="rr-vch-btn receipt"
                                >
                                    <i className="bi bi-box-arrow-in-down" />
                                    New Receipt
                                </Link>

                                <Link
                                    href="/vouchers/create?type=payment"
                                    className="rr-vch-btn payment"
                                >
                                    <i className="bi bi-box-arrow-up-right" />
                                    New Payment
                                </Link>
                            </>
                        )}
                    </div>
                </section>

                <section className="rr-vch-summary-grid" aria-label="Voucher summary">
                    <SummaryCard
                        tone="receipt"
                        icon="bi-arrow-down-left-circle"
                        label="Total Receipts"
                        value={money(summary.receipt_total)}
                        note="Money received into accounts"
                    />
                    <SummaryCard
                        tone="payment"
                        icon="bi-arrow-up-right-circle"
                        label="Total Payments"
                        value={money(summary.payment_total)}
                        note="Money paid from accounts"
                    />
                    <SummaryCard
                        tone={Number(summary.net_flow ?? 0) >= 0 ? "net-positive" : "net-negative"}
                        icon="bi-activity"
                        label="Net Cash Flow"
                        value={money(summary.net_flow)}
                        note="Receipts less payments"
                    />
                    <SummaryCard
                        tone="count"
                        icon="bi-receipt-cutoff"
                        label="Completed Vouchers"
                        value={number(summary.voucher_count)}
                        note="Matching current filters"
                    />
                </section>

                <section className={`rr-vch-filter-card ${filtersOpen ? "open" : ""}`}>
                    <button
                        type="button"
                        className="rr-vch-filter-head"
                        onClick={() => setFiltersOpen((value) => !value)}
                        aria-expanded={filtersOpen}
                    >
                        <span className="rr-vch-filter-title">
                            <i className="bi bi-funnel" />
                            <span>
                                <strong>Voucher Filters</strong>
                                <small>Search and narrow the accounting register</small>
                            </span>
                        </span>
                        <span className="rr-vch-filter-state">
                            {hasFilters && <em>Active</em>}
                            <i className={`bi ${filtersOpen ? "bi-chevron-up" : "bi-chevron-down"}`} />
                        </span>
                    </button>

                    {filtersOpen && (
                        <form className="rr-vch-filter-body" onSubmit={applyFilters}>
                            <Field label="Search" wide icon="bi-search">
                                <input
                                    type="search"
                                    value={form.search}
                                    onChange={(event) => update("search", event.target.value)}
                                    placeholder="Voucher no, party, reference or remarks"
                                />
                            </Field>

                            <Field label="Voucher Type" icon="bi-arrow-left-right">
                                <select
                                    value={form.voucher_type}
                                    onChange={(event) => update("voucher_type", event.target.value)}
                                >
                                    <option value="all">All Types</option>
                                    <option value="receipt">Receipt</option>
                                    <option value="payment">Payment</option>
                                </select>
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
                                    onChange={(event) => update("from_date", event.target.value)}
                                    onClick={openDatePicker}
                                    onFocus={openDatePicker}
                                />
                            </Field>

                            <Field label="To Date" icon="bi-calendar-check">
                                <input
                                    type="date"
                                    value={form.to_date}
                                    min={form.from_date || undefined}
                                    onChange={(event) => update("to_date", event.target.value)}
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

                            <div className="rr-vch-filter-actions">
                                <button type="submit" className="rr-vch-btn filter">
                                    <i className="bi bi-search" />
                                    Apply
                                </button>
                                <button
                                    type="button"
                                    className="rr-vch-btn clear"
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

                <section className="rr-vch-register-card">
                    <header className="rr-vch-register-head">
                        <div>
                            <span className="rr-vch-register-icon">
                                <i className="bi bi-journal-text" />
                            </span>
                            <span>
                                <strong>Voucher Register</strong>
                                <small>
                                    {vouchers?.total ?? rows.length} record
                                    {(vouchers?.total ?? rows.length) === 1 ? "" : "s"}
                                </small>
                            </span>
                        </div>
                        <span className="rr-vch-flow-key">
                            <em className="in" /> Receipt
                            <em className="out" /> Payment
                        </span>
                    </header>

                    <div className="rr-vch-table-wrap">
                        <table className="rr-vch-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Voucher</th>
                                    <th>Type</th>
                                    <th>Account</th>
                                    <th>Party / Particulars</th>
                                    <th>Reference</th>
                                    <th className="number">Amount</th>
                                    <th>Status</th>
                                    <th className="actions">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {rows.length ? (
                                    rows.map((voucher) => (
                                        <VoucherRow
                                            key={voucher.id}
                                            voucher={voucher}
                                            canShow={can("voucher.show")}
                                            canCancel={can("voucher.delete")}
                                            onCancel={() => setSelectedVoucher(voucher)}
                                        />
                                    ))
                                ) : (
                                    <tr>
                                        <td colSpan="9">
                                            <EmptyState canCreate={can("voucher.create")} />
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>

                    <Pagination links={vouchers?.links ?? []} />
                </section>
            </main>

            <ConfirmModal
                show={Boolean(selectedVoucher)}
                title="Cancel Voucher"
                message={
                    selectedVoucher
                        ? `Cancel ${selectedVoucher.voucher_no}? A reversing account entry will be created and the original audit history will remain available.`
                        : ""
                }
                confirmText="Cancel Voucher"
                cancelText="Keep Voucher"
                icon="bi-x-octagon"
                variant="danger"
                processing={processing}
                onConfirm={cancelVoucher}
                onCancel={() => !processing && setSelectedVoucher(null)}
            />
        </>
    );
}

function SummaryCard({ tone, icon, label, value, note }) {
    return (
        <article className={`rr-vch-summary-card ${tone}`}>
            <span className="rr-vch-summary-icon">
                <i className={`bi ${icon}`} />
            </span>
            <div>
                <small>{label}</small>
                <strong>{value}</strong>
                <p>{note}</p>
            </div>
            <span className="rr-vch-summary-glow" />
        </article>
    );
}

function Field({ label, icon, wide = false, children }) {
    return (
        <label className={`rr-vch-field ${wide ? "wide" : ""}`}>
            <span>
                <i className={`bi ${icon}`} />
                {label}
            </span>
            {children}
        </label>
    );
}

function VoucherRow({ voucher, canShow, canCancel, onCancel }) {
    const receipt = voucher.voucher_type === "receipt";
    const cancelled = voucher.is_cancelled || voucher.status === "Cancelled";
    const showUrl = `/vouchers/${voucher.id}`;

    const openRow = (event) => {
        if (!canShow || event.target.closest("a, button")) return;
        router.visit(showUrl);
    };

    return (
        <tr
            className={`${receipt ? "receipt" : "payment"} ${cancelled ? "cancelled" : ""}`}
            onClick={openRow}
        >
            <td>
                <span className="rr-vch-date">{date(voucher.voucher_date)}</span>
            </td>
            <td>
                {canShow ? (
                    <Link href={showUrl} className="rr-vch-number">
                        {voucher.voucher_no}
                    </Link>
                ) : (
                    <span className="rr-vch-number">{voucher.voucher_no}</span>
                )}
                <small className="rr-vch-mode">{label(voucher.payment_mode)}</small>
            </td>
            <td>
                <span className={`rr-vch-type ${receipt ? "receipt" : "payment"}`}>
                    <i className={`bi ${receipt ? "bi-arrow-down-left" : "bi-arrow-up-right"}`} />
                    {receipt ? "Receipt" : "Payment"}
                </span>
            </td>
            <td>
                <strong className="rr-vch-account">{voucher.account?.name ?? "—"}</strong>
                <small>{voucher.account?.code ?? ""}</small>
            </td>
            <td>
                <strong>{voucher.party_name || "General"}</strong>
                <small>{voucher.party_type ? label(voucher.party_type) : voucher.remarks || "—"}</small>
            </td>
            <td>{voucher.reference_no || "—"}</td>
            <td className={`number amount ${receipt ? "receipt" : "payment"}`}>
                {receipt ? "+" : "−"} {money(voucher.amount)}
            </td>
            <td>
                <span className={`rr-vch-status ${cancelled ? "cancelled" : "completed"}`}>
                    <i className={`bi ${cancelled ? "bi-x-circle" : "bi-check-circle"}`} />
                    {voucher.status}
                </span>
            </td>
            <td className="actions">
                <div className="rr-vch-row-actions">
                    {canShow && (
                        <Link href={showUrl} title="View voucher" className="view">
                            <i className="bi bi-eye" />
                        </Link>
                    )}
                    {canShow && (
                        <a
                            href={`/vouchers/${voucher.id}/print?paper=a4`}
                            target="_blank"
                            rel="noreferrer"
                            title="Print voucher"
                            className="print"
                        >
                            <i className="bi bi-printer" />
                        </a>
                    )}
                    {canCancel && !cancelled && (
                        <button type="button" onClick={onCancel} title="Cancel voucher" className="cancel">
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
        <div className="rr-vch-empty">
            <span><i className="bi bi-receipt" /></span>
            <strong>No vouchers found</strong>
            <p>Change the filters or record the first receipt/payment voucher.</p>
            {canCreate && (
                <Link href="/vouchers/create?type=receipt" className="rr-vch-btn receipt">
                    <i className="bi bi-plus-lg" />
                    Create Voucher
                </Link>
            )}
        </div>
    );
}

function Pagination({ links }) {
    if (!Array.isArray(links) || links.length <= 3) return null;

    return (
        <nav className="rr-vch-pagination" aria-label="Voucher pages">
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
        Object.entries(values).filter(([, value]) => value !== "" && value !== null),
    );
}

function openDatePicker(event) {
    if (typeof event.currentTarget?.showPicker !== "function") return;
    try {
        event.currentTarget.showPicker();
    } catch {
        // Browser may block showPicker when focus was not initiated by the user.
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

