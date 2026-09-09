import React, { useMemo, useState } from "react";

import axios from "axios";

import { Head, Link, router, usePage } from "@inertiajs/react";

import BusinessLayout from "@/Layouts/BusinessLayout";
import MasterNavigation from "../../Components/Business/MasterNavigation";
import ConfirmModal from "../../Components/ConfirmModal";

import "../../../css/business/quotations.css";

export default function Index({
    quotations = { data: [], links: [] },
    filters = {},
    todayAmount = 0,
}) {
    const { flash = {} } = usePage().props;
    const records = Array.isArray(quotations.data) ? quotations.data : [];

    const [form, setForm] = useState({
        search: filters.search ?? "",
        quick_filter: filters.quick_filter ?? "today",
        document_type: filters.document_type ?? "all",
        status: filters.status ?? "all",
        date_from: filters.date_from ?? "",
        date_to: filters.date_to ?? "",
    });

    const [confirmation, setConfirmation] = useState({
        show: false,
        action: null,
        quotation: null,
        processing: false,
    });

    const [stockModal, setStockModal] = useState({
        show: false,
        loadingId: null,
        processing: false,
        quotation: null,
        shortages: [],
        transfers: [],
        error: "",
    });

    const pageAmount = useMemo(
        () =>
            records
                .filter((item) => !["Rejected", "Cancelled"].includes(item.status))
                .reduce((total, item) => total + numberValue(item.grand_total), 0),
        [records],
    );

    const approvedCount = records.filter(
        (item) => item.status === "Approved",
    ).length;
    const convertedCount = records.filter(
        (item) => item.status === "Converted",
    ).length;

    const applyFilters = (nextForm = form) => {
        router.get("/quotations", cleanFilters(nextForm), {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    };

    const updateFilter = (field, value, apply = false) => {
        const next = { ...form, [field]: value };
        setForm(next);
        if (apply) applyFilters(next);
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
            document_type: "all",
            status: "all",
            date_from: "",
            date_to: "",
        };

        setForm(next);
        applyFilters(next);
    };

    const openDetails = (event, quotation) => {
        if (event.target.closest("a, button, select")) return;
        router.visit(`/quotations/${quotation.id}`);
    };

    const changeStatus = (quotation, status) => {
        router.patch(
            `/quotations/${quotation.id}/status`,
            { status },
            { preserveScroll: true },
        );
    };

    const openConfirmation = (action, quotation) => {
        setConfirmation({
            show: true,
            action,
            quotation,
            processing: false,
        });
    };

    const prepareConversion = async (quotation) => {
        setStockModal((previous) => ({
            ...previous,
            loadingId: quotation.id,
            error: "",
        }));

        try {
            const response = await axios.get(
                `/quotations/${quotation.id}/conversion-stock`,
                { headers: ajaxHeaders() },
            );
            ensureJsonResponse(response);

            const shortages = response.data?.shortages ?? [];

            if (response.data?.ready || shortages.length === 0) {
                setStockModal((previous) => ({ ...previous, loadingId: null }));
                openConfirmation("convert", quotation);
                return;
            }

            setStockModal({
                show: true,
                loadingId: null,
                processing: false,
                quotation,
                shortages,
                transfers: defaultTransfers(shortages),
                error: "",
            });
        } catch (error) {
            setStockModal({
                show: true,
                loadingId: null,
                processing: false,
                quotation,
                shortages: [],
                transfers: [],
                error: apiError(error, "Unable to check conversion stock."),
            });
        }
    };

    const updateTransfer = (productId, field, value) => {
        setStockModal((previous) => ({
            ...previous,
            transfers: previous.transfers.map((transfer) =>
                Number(transfer.product_id) === Number(productId)
                    ? { ...transfer, [field]: value }
                    : transfer,
            ),
        }));
    };

    const closeStockModal = () => {
        if (stockModal.processing) return;

        setStockModal({
            show: false,
            loadingId: null,
            processing: false,
            quotation: null,
            shortages: [],
            transfers: [],
            error: "",
        });
    };

    const transferSelectedStock = async (event) => {
        event.preventDefault();
        if (!stockModal.quotation) return;

        const transfers = stockModal.transfers
            .filter(
                (transfer) =>
                    transfer.godown_id && numberValue(transfer.quantity) > 0,
            )
            .map((transfer) => ({
                product_id: transfer.product_id,
                godown_id: transfer.godown_id,
                quantity: transfer.quantity,
            }));

        if (!transfers.length) {
            setStockModal((previous) => ({
                ...previous,
                error: "Select a Godown and transfer quantity for at least one item.",
            }));
            return;
        }

        setStockModal((previous) => ({
            ...previous,
            processing: true,
            error: "",
        }));

        try {
            const response = await axios.post(
                `/quotations/${stockModal.quotation.id}/transfer-stock`,
                { transfers },
                { headers: ajaxHeaders() },
            );
            ensureJsonResponse(response);

            const shortages = response.data?.shortages ?? [];

            if (response.data?.ready || shortages.length === 0) {
                const quotation = stockModal.quotation;
                closeStockModalAfterRequest();
                openConfirmation("convert", quotation);
                return;
            }

            setStockModal((previous) => ({
                ...previous,
                processing: false,
                shortages,
                transfers: defaultTransfers(shortages),
                error: response.data?.message ?? "Some Shop shortages remain.",
            }));
        } catch (error) {
            setStockModal((previous) => ({
                ...previous,
                processing: false,
                error: apiError(error, "Unable to transfer stock."),
            }));
        }
    };

    const closeStockModalAfterRequest = () => {
        setStockModal({
            show: false,
            loadingId: null,
            processing: false,
            quotation: null,
            shortages: [],
            transfers: [],
            error: "",
        });
    };

    const closeConfirmation = () => {
        if (confirmation.processing) return;

        setConfirmation({
            show: false,
            action: null,
            quotation: null,
            processing: false,
        });
    };

    const confirmAction = () => {
        const quotation = confirmation.quotation;
        if (!quotation) return;

        setConfirmation((previous) => ({
            ...previous,
            processing: true,
        }));

        const finish = () => {
            setConfirmation({
                show: false,
                action: null,
                quotation: null,
                processing: false,
            });
        };

        if (confirmation.action === "convert") {
            router.post(
                `/quotations/${quotation.id}/convert`,
                {},
                {
                    preserveScroll: true,
                    onFinish: finish,
                },
            );
            return;
        }

        router.patch(
            `/quotations/${quotation.id}/cancel`,
            {},
            {
                preserveScroll: true,
                onFinish: finish,
            },
        );
    };

    const confirmationIsConvert = confirmation.action === "convert";

    return (
        <>
            <Head title="Quotations & Proforma Invoices" />

            <div className="rr-quote-page">
                <MasterNavigation
                    active="quotations"
                    title="Quotations"
                    subtitle="Quotations, proforma invoices and sales conversion"
                />

                {flash.success && (
                    <div className="rr-quote-alert success">
                        <i className="bi bi-check-circle-fill"></i>
                        {flash.success}
                    </div>
                )}

                {flash.error && (
                    <div className="rr-quote-alert danger">
                        <i className="bi bi-exclamation-triangle-fill"></i>
                        {flash.error}
                    </div>
                )}

                <header className="rr-quote-header">
                    <div className="rr-quote-header-main">
                        <div className="rr-quote-header-icon">
                            <i className="bi bi-file-earmark-text"></i>
                        </div>
                        <div>
                            <span className="rr-quote-eyebrow">Sales Documents</span>
                            <h1>Quotation & Proforma Register</h1>
                            <div className="rr-quote-header-meta">
                                <span>{quotations.total ?? records.length} records</span>
                                <span>Branch-isolated</span>
                            </div>
                        </div>
                    </div>

                    <div className="rr-quote-header-actions">
                        <Link
                            href="/quotations/create?type=quotation"
                            className="rr-quote-btn"
                        >
                            <i className="bi bi-file-earmark-plus"></i>
                            New Quotation
                        </Link>
                        <Link
                            href="/quotations/create?type=proforma_invoice"
                            className="rr-quote-btn primary"
                        >
                            <i className="bi bi-receipt"></i>
                            New Proforma
                        </Link>
                    </div>
                </header>

                <div className="rr-quote-metrics">
                    <Metric
                        icon="bi-calendar-check"
                        label="Today's Amount"
                        value={formatMoney(todayAmount)}
                        tone="blue"
                    />
                    <Metric
                        icon="bi-files"
                        label="Page Amount"
                        value={formatMoney(pageAmount)}
                        tone="slate"
                    />
                    <Metric
                        icon="bi-patch-check"
                        label="Approved"
                        value={approvedCount}
                        tone="green"
                    />
                    <Metric
                        icon="bi-arrow-right-circle"
                        label="Converted"
                        value={convertedCount}
                        tone="violet"
                    />
                </div>

                <section className="rr-quote-window rr-quote-filter-window">
                    <div className="rr-quote-window-title">
                        <div>
                            <i className="bi bi-funnel"></i>
                            <strong>Search & Filters</strong>
                        </div>
                        <button
                            type="button"
                            className="rr-quote-reset"
                            onClick={resetFilters}
                        >
                            <i className="bi bi-arrow-counterclockwise"></i>
                            Reset
                        </button>
                    </div>

                    <div className="rr-quote-window-body">
                        <div className="rr-quote-quick-filters">
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
                            className="rr-quote-filter-grid"
                            onSubmit={(event) => {
                                event.preventDefault();
                                applyFilters();
                            }}
                        >
                            <Field label="Search">
                                <div className="rr-quote-search">
                                    <i className="bi bi-search"></i>
                                    <input
                                        type="search"
                                        value={form.search}
                                        placeholder="Document no., customer, mobile or code..."
                                        onChange={(event) =>
                                            updateFilter("search", event.target.value)
                                        }
                                    />
                                </div>
                            </Field>

                            <Field label="Document Type">
                                <select
                                    value={form.document_type}
                                    onChange={(event) =>
                                        updateFilter("document_type", event.target.value, true)
                                    }
                                >
                                    <option value="all">All Documents</option>
                                    <option value="quotation">Quotation</option>
                                    <option value="proforma_invoice">Proforma Invoice</option>
                                </select>
                            </Field>

                            <Field label="Status">
                                <select
                                    value={form.status}
                                    onChange={(event) =>
                                        updateFilter("status", event.target.value, true)
                                    }
                                >
                                    <option value="all">All Statuses</option>
                                    {[
                                        "Draft",
                                        "Pending",
                                        "Approved",
                                        "Rejected",
                                        "Converted",
                                        "Expired",
                                        "Cancelled",
                                    ].map((status) => (
                                        <option key={status} value={status}>
                                            {status}
                                        </option>
                                    ))}
                                </select>
                            </Field>

                            {form.quick_filter === "custom" && (
                                <>
                                    <Field label="From Date">
                                        <input
                                            type="date"
                                            value={form.date_from}
                                            onChange={(event) =>
                                                updateFilter("date_from", event.target.value)
                                            }
                                        />
                                    </Field>
                                    <Field label="To Date">
                                        <input
                                            type="date"
                                            value={form.date_to}
                                            onChange={(event) =>
                                                updateFilter("date_to", event.target.value)
                                            }
                                        />
                                    </Field>
                                </>
                            )}

                            <button type="submit" className="rr-quote-btn primary">
                                <i className="bi bi-search"></i>
                                Apply
                            </button>
                        </form>
                    </div>
                </section>

                <section className="rr-quote-window">
                    <div className="rr-quote-window-title">
                        <div>
                            <i className="bi bi-list-ul"></i>
                            <strong>Document Register</strong>
                        </div>
                        <span className="rr-quote-record-count">
                            Click a row to view details
                        </span>
                    </div>

                    <div className="rr-quote-table-wrap">
                        <table className="rr-quote-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Document</th>
                                    <th>Customer / Source</th>
                                    <th>Validity</th>
                                    <th>Billing</th>
                                    <th className="number">Amount</th>
                                    <th>Status</th>
                                    <th>Converted Sale</th>
                                    <th className="actions">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {records.length ? (
                                    records.map((quotation) => {
                                        const expired = isExpired(quotation);
                                        const terminal = [
                                            "Converted",
                                            "Rejected",
                                            "Cancelled",
                                        ].includes(quotation.status);

                                        return (
                                            <tr
                                                key={quotation.id}
                                                className={terminal ? "terminal" : ""}
                                                tabIndex="0"
                                                onClick={(event) => openDetails(event, quotation)}
                                                onKeyDown={(event) => {
                                                    if (event.key === "Enter") {
                                                        router.visit(`/quotations/${quotation.id}`);
                                                    }
                                                }}
                                            >
                                                <td>
                                                    <strong>{displayDate(quotation.quotation_date)}</strong>
                                                    <span>{relativeDate(quotation.quotation_date)}</span>
                                                </td>
                                                <td>
                                                    <strong>{quotation.quotation_no}</strong>
                                                    <span className="rr-quote-type">
                                                        {documentTitle(quotation.document_type)}
                                                    </span>
                                                </td>
                                                <td>
                                                    <strong>
                                                        {quotation.customer?.customer_name ?? "Customer"}
                                                    </strong>
                                                    <span>
                                                        {quotation.sale_source === "mechanic"
                                                            ? `Mechanic: ${quotation.mechanic?.mechanic_name ?? "-"}`
                                                            : quotation.customer?.mobile ?? "Direct customer"}
                                                    </span>
                                                </td>
                                                <td>
                                                    <strong>{displayDate(quotation.valid_until)}</strong>
                                                    <span className={expired ? "expired-text" : ""}>
                                                        {expired ? "Expired" : validityText(quotation.valid_until)}
                                                    </span>
                                                </td>
                                                <td>
                                                    <span
                                                        className={`rr-quote-billing ${quotation.billing_type}`}
                                                    >
                                                        {quotation.billing_type === "gst"
                                                            ? "GST"
                                                            : "NON-GST"}
                                                    </span>
                                                </td>
                                                <td className="number amount">
                                                    {formatMoney(quotation.grand_total)}
                                                </td>
                                                <td>
                                                    <StatusBadge
                                                        status={expired ? "Expired" : quotation.status}
                                                    />
                                                </td>
                                                <td>
                                                    {quotation.converted_sale ? (
                                                        <Link
                                                            href={`/sales/${quotation.converted_sale.id}`}
                                                            className="rr-quote-sale-link"
                                                        >
                                                            {quotation.converted_sale.invoice_no}
                                                        </Link>
                                                    ) : (
                                                        <span>-</span>
                                                    )}
                                                </td>
                                                <td className="actions">
                                                    <Link
                                                        href={`/quotations/${quotation.id}`}
                                                        title="View"
                                                    >
                                                        <i className="bi bi-eye"></i>
                                                    </Link>

                                                    {!terminal && !expired && (
                                                        <Link
                                                            href={`/quotations/${quotation.id}/edit`}
                                                            title="Edit"
                                                        >
                                                            <i className="bi bi-pencil"></i>
                                                        </Link>
                                                    )}

                                                    <a
                                                        href={`/quotations/${quotation.id}/print`}
                                                        target="_blank"
                                                        rel="noreferrer"
                                                        title="Print"
                                                    >
                                                        <i className="bi bi-printer"></i>
                                                    </a>

                                                    {quotation.status === "Approved" && !expired && (
                                                        <button
                                                            type="button"
                                                            className="convert"
                                                            title="Convert to Sale"
                                                            disabled={stockModal.loadingId === quotation.id}
                                                            onClick={() => prepareConversion(quotation)}
                                                        >
                                                            {stockModal.loadingId === quotation.id ? (
                                                                <span className="spinner-border spinner-border-sm"></span>
                                                            ) : (
                                                                <i className="bi bi-arrow-right-circle"></i>
                                                            )}
                                                        </button>
                                                    )}

                                                    {["Draft", "Pending"].includes(quotation.status) &&
                                                        !expired && (
                                                            <button
                                                                type="button"
                                                                className="approve"
                                                                title="Approve"
                                                                onClick={() =>
                                                                    changeStatus(quotation, "Approved")
                                                                }
                                                            >
                                                                <i className="bi bi-check2"></i>
                                                            </button>
                                                        )}

                                                    {!terminal && (
                                                        <button
                                                            type="button"
                                                            className="danger"
                                                            title="Cancel document"
                                                            onClick={() =>
                                                                openConfirmation("cancel", quotation)
                                                            }
                                                        >
                                                            <i className="bi bi-x-octagon"></i>
                                                        </button>
                                                    )}
                                                </td>
                                            </tr>
                                        );
                                    })
                                ) : (
                                    <tr>
                                        <td colSpan="9" className="rr-quote-empty">
                                            <i className="bi bi-inbox"></i>
                                            <strong>No quotation documents found</strong>
                                            <span>Change the filters or create a new document.</span>
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>

                    <Pagination links={quotations.links ?? []} />
                </section>

                <ConfirmModal
                    show={confirmation.show}
                    title={
                        confirmationIsConvert
                            ? "Convert to Sales Invoice"
                            : "Cancel Document"
                    }
                    message={
                        confirmationIsConvert
                            ? `Convert "${confirmation.quotation?.quotation_no ?? ""}" into a Sales invoice? Current shop stock will be checked and reduced.`
                            : `Cancel "${confirmation.quotation?.quotation_no ?? ""}"? The document will remain available for audit history.`
                    }
                    confirmText={
                        confirmationIsConvert ? "Convert to Sale" : "Cancel Document"
                    }
                    cancelText={confirmationIsConvert ? "Not Now" : "Keep Document"}
                    icon={
                        confirmationIsConvert
                            ? "bi-arrow-right-circle"
                            : "bi-x-octagon"
                    }
                    variant={confirmationIsConvert ? "primary" : "danger"}
                    processing={confirmation.processing}
                    onConfirm={confirmAction}
                    onCancel={closeConfirmation}
                />

                <StockTransferModal
                    state={stockModal}
                    onUpdate={updateTransfer}
                    onClose={closeStockModal}
                    onSubmit={transferSelectedStock}
                />
            </div>
        </>
    );
}

function Metric({ icon, label, value, tone }) {
    return (
        <div className={`rr-quote-metric ${tone}`}>
            <div>
                <i className={`bi ${icon}`}></i>
            </div>
            <span>{label}</span>
            <strong>{value}</strong>
        </div>
    );
}

function Field({ label, children }) {
    return (
        <label className="rr-quote-field">
            <span>{label}</span>
            {children}
        </label>
    );
}

function StatusBadge({ status }) {
    return (
        <span className={`rr-quote-status ${String(status).toLowerCase()}`}>
            {status}
        </span>
    );
}

function Pagination({ links }) {
    if (!Array.isArray(links) || links.length <= 3) return null;

    return (
        <nav className="rr-quote-pagination">
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

function StockTransferModal({ state, onUpdate, onClose, onSubmit }) {
    if (!state.show) return null;

    return (
        <div className="rr-quote-modal-backdrop" role="presentation">
            <form
                className="rr-quote-modal rr-quote-stock-modal"
                onSubmit={onSubmit}
            >
                <div className="rr-quote-modal-title">
                    <div>
                        <i className="bi bi-arrow-left-right"></i>
                        <strong>Transfer Godown Stock to Shop</strong>
                    </div>
                    <button type="button" disabled={state.processing} onClick={onClose}>
                        <i className="bi bi-x-lg"></i>
                    </button>
                </div>

                <div className="rr-quote-stock-document">
                    <span>Document</span>
                    <strong>{state.quotation?.quotation_no ?? "—"}</strong>
                    <span>Customer</span>
                    <strong>
                        {state.quotation?.customer?.customer_name ?? "Customer"}
                    </strong>
                </div>

                <div className="rr-quote-stock-notice">
                    <i className="bi bi-info-circle"></i>
                    Select the Godown and edit the transfer quantity for every required
                    item. No stock is transferred automatically.
                </div>

                {state.error && (
                    <div className="rr-quote-stock-error">{state.error}</div>
                )}

                {state.shortages.length ? (
                    <div className="rr-quote-stock-table-wrap">
                        <table className="rr-quote-stock-table">
                            <thead>
                                <tr>
                                    <th>Product</th>
                                    <th className="number">Required</th>
                                    <th className="number">Shop</th>
                                    <th className="number">Shortage</th>
                                    <th>From Godown</th>
                                    <th className="number">Transfer Qty</th>
                                </tr>
                            </thead>
                            <tbody>
                                {state.shortages.map((shortage) => {
                                    const transfer = state.transfers.find(
                                        (item) =>
                                            Number(item.product_id) === Number(shortage.product_id),
                                    );

                                    return (
                                        <tr key={shortage.product_id}>
                                            <td>
                                                <strong>{shortage.product_name}</strong>
                                                <span>
                                                    {[shortage.product_code, shortage.part_number]
                                                        .filter(Boolean)
                                                        .join(" · ")}
                                                </span>
                                                {!shortage.can_fulfil && (
                                                    <em>Total available stock is insufficient</em>
                                                )}
                                            </td>
                                            <td className="number">
                                                {formatQty(shortage.required_quantity)}
                                            </td>
                                            <td className="number">
                                                {formatQty(shortage.shop_stock)}
                                            </td>
                                            <td className="number shortage">
                                                {formatQty(shortage.shortage_quantity)}
                                            </td>
                                            <td>
                                                <select
                                                    required
                                                    value={transfer?.godown_id ?? ""}
                                                    disabled={!shortage.godowns?.length}
                                                    onChange={(event) =>
                                                        onUpdate(
                                                            shortage.product_id,
                                                            "godown_id",
                                                            event.target.value,
                                                        )
                                                    }
                                                >
                                                    <option value="">Select Godown</option>
                                                    {(shortage.godowns ?? []).map((godown) => (
                                                        <option key={godown.id} value={godown.id}>
                                                            {godown.godown_name} — {formatQty(godown.stock)}
                                                        </option>
                                                    ))}
                                                </select>
                                            </td>
                                            <td>
                                                <input
                                                    required
                                                    type="number"
                                                    min="0.01"
                                                    step="0.01"
                                                    value={transfer?.quantity ?? ""}
                                                    onFocus={(event) => event.target.select()}
                                                    onChange={(event) =>
                                                        onUpdate(
                                                            shortage.product_id,
                                                            "quantity",
                                                            event.target.value,
                                                        )
                                                    }
                                                />
                                            </td>
                                        </tr>
                                    );
                                })}
                            </tbody>
                        </table>
                    </div>
                ) : (
                    <div className="rr-quote-stock-empty">
                        No stock details were returned. Please refresh and try again.
                    </div>
                )}

                <div className="rr-quote-modal-actions">
                    <button type="button" disabled={state.processing} onClick={onClose}>
                        Close
                    </button>
                    <button
                        type="submit"
                        className="primary"
                        disabled={state.processing || !state.shortages.length}
                    >
                        {state.processing ? "Transferring..." : "Transfer Selected Stock"}
                    </button>
                </div>
            </form>
        </div>
    );
}

function cleanFilters(filters) {
    return Object.fromEntries(
        Object.entries(filters).filter(
            ([, value]) => value !== "" && value !== null && value !== undefined,
        ),
    );
}

function documentTitle(type) {
    return type === "proforma_invoice" ? "Proforma Invoice" : "Quotation";
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

function relativeDate(value) {
    if (!value) return "";
    const date = new Date(value);
    const today = new Date();
    date.setHours(0, 0, 0, 0);
    today.setHours(0, 0, 0, 0);
    const days = Math.round((date - today) / 86400000);

    if (days === 0) return "Today";
    if (days === -1) return "Yesterday";
    return days < 0 ? `${Math.abs(days)} days ago` : `In ${days} days`;
}

function isExpired(quotation) {
    if (quotation.status === "Expired") return true;
    if (!quotation.valid_until) return false;

    const validUntil = new Date(quotation.valid_until);
    const today = new Date();
    validUntil.setHours(23, 59, 59, 999);

    return validUntil < today;
}

function validityText(value) {
    if (!value) return "No expiry";

    const date = new Date(value);
    const today = new Date();
    date.setHours(0, 0, 0, 0);
    today.setHours(0, 0, 0, 0);
    const days = Math.round((date - today) / 86400000);

    if (days === 0) return "Valid today";
    if (days === 1) return "1 day left";
    return `${Math.max(0, days)} days left`;
}

function defaultTransfers(shortages) {
    return shortages.map((shortage) => {
        const sufficientGodown = (shortage.godowns ?? []).find(
            (godown) =>
                numberValue(godown.stock) >= numberValue(shortage.shortage_quantity),
        );
        const selectedGodown =
            sufficientGodown ?? shortage.godowns?.[0] ?? null;

        return {
            product_id: shortage.product_id,
            godown_id: selectedGodown?.id ? String(selectedGodown.id) : "",
            quantity: selectedGodown
                ? String(
                    Math.min(
                        numberValue(shortage.shortage_quantity),
                        numberValue(selectedGodown.stock),
                    ),
                )
                : "",
        };
    });
}

function formatQty(value) {
    return numberValue(value).toLocaleString("en-IN", {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    });
}

function ajaxHeaders() {
    return {
        Accept: "application/json",
        "X-Requested-With": "XMLHttpRequest",
    };
}

function ensureJsonResponse(response) {
    const contentType = response?.headers?.["content-type"] ?? "";

    if (!contentType.includes("application/json")) {
        throw new Error(
            "The server redirected the stock request. Clear the Laravel route cache and sign in again.",
        );
    }
}

function apiError(error, fallback) {
    const errors = error?.response?.data?.errors;

    if (errors && typeof errors === "object") {
        const first = Object.values(errors).flat().find(Boolean);
        if (first) return String(first);
    }

    return error?.response?.data?.message ?? error?.message ?? fallback;
}

Index.layout = (page) => <BusinessLayout>{page}</BusinessLayout>;
