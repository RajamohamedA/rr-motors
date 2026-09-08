import React, { useMemo, useState } from "react";

import { Head, Link, router, usePage } from "@inertiajs/react";

import BusinessLayout from "@/Layouts/BusinessLayout";
import MasterNavigation from "../../Components/Business/MasterNavigation";
import ConfirmModal from "../../Components/ConfirmModal";

import "../../../css/business/sales-return.css";

export default function Index({
    returns = { data: [], links: [] },
    filters = {},
    todayAmount = 0,
}) {
    const { flash = {} } = usePage().props;

    const [form, setForm] = useState({
        search: filters.search ?? "",
        quick_filter: filters.quick_filter ?? "today",
        date_from: filters.date_from ?? "",
        date_to: filters.date_to ?? "",
        status: filters.status ?? "all",
    });

    const [confirmModal, setConfirmModal] = useState({
        show: false,
        salesReturn: null,
        processing: false,
    });

    const records = Array.isArray(returns.data) ? returns.data : [];

    const pageAmount = useMemo(
        () =>
            records
                .filter((item) => item.status === "completed")
                .reduce((total, item) => total + numberValue(item.total_amount), 0),
        [records],
    );

    const completedCount = records.filter(
        (item) => item.status === "completed",
    ).length;
    const cancelledCount = records.filter(
        (item) => item.status === "cancelled",
    ).length;

    const applyFilters = (nextForm = form) => {
        router.get("/sales-returns", cleanFilters(nextForm), {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    };

    const chooseQuickFilter = (quickFilter) => {
        const next = {
            ...form,
            quick_filter: quickFilter,
            date_from: quickFilter === "custom" ? form.date_from : "",
            date_to: quickFilter === "custom" ? form.date_to : "",
        };

        setForm(next);
        applyFilters(next);
    };

    const resetFilters = () => {
        const next = {
            search: "",
            quick_filter: "today",
            date_from: "",
            date_to: "",
            status: "all",
        };

        setForm(next);
        applyFilters(next);
    };

    const openDetails = (event, item) => {
        if (event.target.closest("a, button")) return;
        router.visit(`/sales-returns/${item.id}`);
    };

    const openCancelConfirm = (item) => {
        setConfirmModal({
            show: true,
            salesReturn: item,
            processing: false,
        });
    };

    const closeCancelConfirm = () => {
        if (confirmModal.processing) return;

        setConfirmModal({
            show: false,
            salesReturn: null,
            processing: false,
        });
    };

    const confirmCancel = () => {
        const salesReturn = confirmModal.salesReturn;

        if (!salesReturn) return;

        setConfirmModal((previous) => ({
            ...previous,
            processing: true,
        }));

        router.patch(
            `/sales-returns/${salesReturn.id}/cancel`,
            {},
            {
                preserveScroll: true,
                onFinish: () => {
                    setConfirmModal({
                        show: false,
                        salesReturn: null,
                        processing: false,
                    });
                },
            },
        );
    };

    return (
        <>
            <Head title="Sales Returns" />

            <div className="rr-return-page rr-return-index-page">
                <MasterNavigation
                    active="sales-returns"
                    title="Sales Returns"
                    subtitle="Invoice returns, refunds and restored stock"
                />

                <header className="rr-return-header rr-return-index-header">
                    <div className="rr-return-header-main">
                        <div className="rr-return-header-icon">
                            <i className="bi bi-arrow-counterclockwise"></i>
                        </div>
                        <div>
                            <span className="rr-return-eyebrow">Sales Register</span>
                            <h1>Sales Returns</h1>
                            <div className="rr-return-header-meta">
                                <span>{returns.total ?? records.length} total records</span>
                                <span>Branch-isolated register</span>
                            </div>
                        </div>
                    </div>

                    <Link
                        href="/sales-returns/create"
                        className="rr-return-btn primary"
                    >
                        <i className="bi bi-plus-lg"></i>
                        New Sales Return
                    </Link>
                </header>

                {(flash.success || flash.error) && (
                    <div
                        className={`rr-return-alert ${flash.error ? "danger" : "success"}`}
                    >
                        <i
                            className={`bi ${flash.error ? "bi-exclamation-octagon" : "bi-check-circle"
                                }`}
                        ></i>
                        <span>{flash.error || flash.success}</span>
                    </div>
                )}

                <section className="rr-return-metrics">
                    <Metric
                        icon="bi-calendar-check"
                        label="Today's Return Amount"
                        value={formatMoney(todayAmount)}
                        tone="blue"
                    />
                    <Metric
                        icon="bi-cash-stack"
                        label="Current Page Amount"
                        value={formatMoney(pageAmount)}
                        tone="green"
                    />
                    <Metric
                        icon="bi-check-circle"
                        label="Completed on Page"
                        value={completedCount}
                        tone="slate"
                    />
                    <Metric
                        icon="bi-x-octagon"
                        label="Cancelled on Page"
                        value={cancelledCount}
                        tone="red"
                    />
                </section>

                <section className="rr-return-window rr-return-filter-window">
                    <div className="rr-return-window-title">
                        <div>
                            <i className="bi bi-funnel"></i>
                            <strong>Search and Filter</strong>
                        </div>
                        <button
                            type="button"
                            className="rr-return-reset"
                            onClick={resetFilters}
                        >
                            <i className="bi bi-arrow-counterclockwise"></i>
                            Reset
                        </button>
                    </div>

                    <div className="rr-return-window-body">
                        <div className="rr-return-quick-filters">
                            {[
                                ["today", "Today"],
                                ["yesterday", "Yesterday"],
                                ["this_week", "This Week"],
                                ["this_month", "This Month"],
                                ["all", "All"],
                                ["custom", "Custom Date"],
                            ].map(([value, label]) => (
                                <button
                                    key={value}
                                    type="button"
                                    className={form.quick_filter === value ? "active" : ""}
                                    onClick={() => chooseQuickFilter(value)}
                                >
                                    {label}
                                </button>
                            ))}
                        </div>

                        <form
                            className="rr-return-filter-grid"
                            onSubmit={(event) => {
                                event.preventDefault();
                                applyFilters();
                            }}
                        >
                            <div className="rr-return-field rr-return-search-field">
                                <label>Search</label>
                                <div className="rr-return-filter-search">
                                    <i className="bi bi-search"></i>
                                    <input
                                        type="search"
                                        value={form.search}
                                        placeholder="Return no, invoice, customer or mobile..."
                                        onChange={(event) =>
                                            setForm((previous) => ({
                                                ...previous,
                                                search: event.target.value,
                                            }))
                                        }
                                    />
                                </div>
                            </div>

                            <div className="rr-return-field">
                                <label>Status</label>
                                <select
                                    value={form.status}
                                    onChange={(event) =>
                                        setForm((previous) => ({
                                            ...previous,
                                            status: event.target.value,
                                        }))
                                    }
                                >
                                    <option value="all">All Statuses</option>
                                    <option value="completed">Completed</option>
                                    <option value="cancelled">Cancelled</option>
                                </select>
                            </div>

                            {form.quick_filter === "custom" && (
                                <>
                                    <div className="rr-return-field">
                                        <label>From Date</label>
                                        <input
                                            type="date"
                                            value={form.date_from}
                                            onChange={(event) =>
                                                setForm((previous) => ({
                                                    ...previous,
                                                    date_from: event.target.value,
                                                }))
                                            }
                                        />
                                    </div>
                                    <div className="rr-return-field">
                                        <label>To Date</label>
                                        <input
                                            type="date"
                                            value={form.date_to}
                                            onChange={(event) =>
                                                setForm((previous) => ({
                                                    ...previous,
                                                    date_to: event.target.value,
                                                }))
                                            }
                                        />
                                    </div>
                                </>
                            )}

                            <button
                                type="submit"
                                className="rr-return-btn primary rr-return-apply-filter"
                            >
                                <i className="bi bi-search"></i>
                                Apply Filter
                            </button>
                        </form>
                    </div>
                </section>

                <section className="rr-return-window">
                    <div className="rr-return-window-title">
                        <div>
                            <i className="bi bi-list-ul"></i>
                            <strong>Return Register</strong>
                        </div>
                        <span className="rr-return-record-count">
                            Showing {returns.from ?? 0}–{returns.to ?? 0} of{" "}
                            {returns.total ?? 0}
                        </span>
                    </div>

                    <div className="rr-return-index-table-wrap">
                        <table className="rr-return-index-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Return</th>
                                    <th>Original Invoice</th>
                                    <th>Customer</th>
                                    <th>Date</th>
                                    <th>Refund</th>
                                    <th className="number">Amount</th>
                                    <th>Status</th>
                                    <th className="actions">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {records.length ? (
                                    records.map((item, index) => (
                                        <tr
                                            key={item.id}
                                            className={item.status === "cancelled" ? "cancelled" : ""}
                                            tabIndex="0"
                                            title="Click row to view return details"
                                            onClick={(event) => openDetails(event, item)}
                                            onKeyDown={(event) => {
                                                if (event.key === "Enter") {
                                                    router.visit(`/sales-returns/${item.id}`);
                                                }
                                            }}
                                        >
                                            <td>{(returns.from ?? 1) + index}</td>
                                            <td>
                                                <strong>{item.return_no}</strong>
                                                <span>
                                                    {item.billing_type === "gst" ? "GST" : "Non-GST"}
                                                </span>
                                            </td>
                                            <td>{item.sale?.invoice_no ?? "-"}</td>
                                            <td>
                                                <strong>
                                                    {item.customer?.customer_name ?? "Customer"}
                                                </strong>
                                                <span>{item.customer?.mobile ?? ""}</span>
                                            </td>
                                            <td>{displayDate(item.return_date)}</td>
                                            <td>
                                                <strong>{labelize(item.refund_status)}</strong>
                                                <span>{labelize(item.refund_method)}</span>
                                            </td>
                                            <td className="number amount">
                                                {formatMoney(item.total_amount)}
                                            </td>
                                            <td>
                                                <span className={`rr-return-status ${item.status}`}>
                                                    {labelize(item.status)}
                                                </span>
                                            </td>
                                            <td className="actions">
                                                <Link
                                                    href={`/sales-returns/${item.id}`}
                                                    title="View"
                                                >
                                                    <i className="bi bi-eye"></i>
                                                </Link>
                                                {item.status === "completed" && (
                                                    <Link
                                                        href={`/sales-returns/${item.id}/edit`}
                                                        title="Edit"
                                                    >
                                                        <i className="bi bi-pencil"></i>
                                                    </Link>
                                                )}
                                                <a
                                                    href={`/sales-returns/${item.id}/print`}
                                                    target="_blank"
                                                    rel="noreferrer"
                                                    title="Print"
                                                >
                                                    <i className="bi bi-printer"></i>
                                                </a>
                                                <a
                                                    href={`/sales-returns/${item.id}/pdf`}
                                                    title="PDF"
                                                >
                                                    <i className="bi bi-file-earmark-pdf"></i>
                                                </a>
                                                {item.status === "completed" && (
                                                    <button
                                                        type="button"
                                                        className="danger"
                                                        title="Cancel return"
                                                        onClick={() => openCancelConfirm(item)}
                                                    >
                                                        <i className="bi bi-x-octagon"></i>
                                                    </button>
                                                )}
                                            </td>
                                        </tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td
                                            colSpan="9"
                                            className="rr-return-empty"
                                        >
                                            <i className="bi bi-inbox"></i>
                                            <strong>No sales returns found</strong>
                                            <span>
                                                Change the filters or create a new sales return.
                                            </span>
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>

                    <Pagination links={returns.links ?? []} />
                </section>

                <ConfirmModal
                    show={confirmModal.show}
                    title="Cancel Sales Return"
                    message={`Are you sure you want to cancel sales return "${confirmModal.salesReturn?.return_no ?? ""}"? Stock added through this return will be reversed. This action cannot be edited afterward.`}
                    confirmText="Cancel Return"
                    cancelText="Keep Return"
                    icon="bi-x-octagon"
                    variant="danger"
                    processing={confirmModal.processing}
                    onConfirm={confirmCancel}
                    onCancel={closeCancelConfirm}
                />
            </div>
        </>
    );
}

function Metric({ icon, label, value, tone }) {
    return (
        <div className={`rr-return-metric ${tone}`}>
            <div>
                <i className={`bi ${icon}`}></i>
            </div>
            <span>{label}</span>
            <strong>{value}</strong>
        </div>
    );
}

function Pagination({ links }) {
    if (!Array.isArray(links) || links.length <= 3) return null;

    return (
        <nav className="rr-return-pagination">
            {links.map((link, index) => (
                <Link
                    key={`${link.label}-${index}`}
                    href={link.url || "#"}
                    preserveScroll
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

function cleanFilters(filters) {
    return Object.fromEntries(
        Object.entries(filters).filter(
            ([, value]) => value !== "" && value !== null && value !== undefined,
        ),
    );
}

function labelize(value) {
    if (!value) return "-";
    return String(value)
        .replaceAll("_", " ")
        .replace(/\b\w/g, (letter) => letter.toUpperCase());
}

function numberValue(value) {
    const number = Number(value ?? 0);
    return Number.isFinite(number) ? number : 0;
}

function formatMoney(value) {
    return new Intl.NumberFormat("en-IN", {
        style: "currency",
        currency: "INR",
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(numberValue(value));
}

function displayDate(value) {
    if (!value) return "-";
    return new Intl.DateTimeFormat("en-IN", {
        day: "2-digit",
        month: "short",
        year: "numeric",
    }).format(new Date(value));
}

Index.layout = (page) => <BusinessLayout>{page}</BusinessLayout>;
