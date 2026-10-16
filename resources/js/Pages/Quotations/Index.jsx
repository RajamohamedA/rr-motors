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
    accounts = [],
}) {
    const { flash = {} } = usePage().props;
    const records = Array.isArray(quotations.data) ? quotations.data : [];

    const [filterOpen, setFilterOpen] = useState(true);
    const [filtering, setFiltering] = useState(false);

    const [form, setForm] = useState({
        search: filters.search ?? "",
        quick_filter: filters.quick_filter ?? "today",
        document_type: filters.document_type ?? "all",
        status: filters.status ?? "all",
        date_from: filters.date_from ?? "",
        date_to: filters.date_to ?? "",
    });

    const [conversionModal, setConversionModal] = useState({ show: false, processing: false, error: "", invoice_date: today(), remarks: "", payments: [], quotation: null });
    const quotation = conversionModal.quotation ?? {};
    const openConversion = (document) => setConversionModal({ show: true, processing: false, error: "", invoice_date: today(), remarks: `Converted from ${document.quotation_no}`, payments: [], quotation: document });

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
            onStart: () => setFiltering(true),
            onFinish: () => setFiltering(false),
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
                openConversion(quotation);
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
                openConversion(quotation);
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

    const addPayment = () => {
        const usedIds = conversionModal.payments.map((payment) =>
            Number(payment.account_id),
        );
        const account = accounts.find((item) => !usedIds.includes(Number(item.id)))
            ?? accounts[0];

        if (!account) {
            setConversionModal((previous) => ({
                ...previous,
                error: "No active Cash, GPay or Bank account is available for this branch.",
            }));
            return;
        }

        const paid = conversionModal.payments.reduce(
            (sum, payment) => sum + numberValue(payment.amount),
            0,
        );
        const balance = Math.max(0, numberValue(quotation.grand_total) - paid);

        setConversionModal((previous) => ({
            ...previous,
            error: "",
            payments: [
                ...previous.payments,
                {
                    key: `${Date.now()}-${Math.random()}`,
                    account_id: String(account.id),
                    payment_mode: account.payment_mode || accountMode(account),
                    amount: balance > 0 ? String(balance) : "0",
                    reference_no: "",
                },
            ],
        }));
    };

    const updatePayment = (index, field, value) => {
        setConversionModal((previous) => ({
            ...previous,
            error: "",
            payments: previous.payments.map((payment, paymentIndex) => {
                if (paymentIndex !== index) return payment;

                if (field === "account_id") {
                    const account = accounts.find(
                        (item) => Number(item.id) === Number(value),
                    );

                    return {
                        ...payment,
                        account_id: value,
                        payment_mode:
                            account?.payment_mode || accountMode(account),
                    };
                }

                return { ...payment, [field]: value };
            }),
        }));
    };

    const removePayment = (index) => {
        setConversionModal((previous) => ({
            ...previous,
            payments: previous.payments.filter(
                (_, paymentIndex) => paymentIndex !== index,
            ),
        }));
    };

    const submitConversion = (event) => {
        event.preventDefault();

        const payments = conversionModal.payments
            .filter((payment) => numberValue(payment.amount) > 0)
            .map(({ account_id, payment_mode, amount, reference_no }) => ({
                account_id,
                payment_mode,
                amount,
                reference_no: reference_no || null,
            }));
        const paidAmount = payments.reduce(
            (sum, payment) => sum + numberValue(payment.amount),
            0,
        );

        if (paidAmount > numberValue(quotation.grand_total) + 0.001) {
            setConversionModal((previous) => ({
                ...previous,
                error: "Total payment cannot exceed the quotation grand total.",
            }));
            return;
        }

        setConversionModal((previous) => ({
            ...previous,
            processing: true,
            error: "",
        }));

        router.post(
            `/quotations/${quotation.id}/convert`,
            {
                invoice_date: conversionModal.invoice_date,
                remarks: conversionModal.remarks || null,
                paid_amount: paidAmount,
                payments,
            },
            {
                preserveScroll: true,
                onError: (errors) => {
                    setConversionModal((previous) => ({
                        ...previous,
                        error: firstError(errors, "Unable to convert quotation."),
                    }));
                },
                onFinish: () => {
                    setConversionModal((previous) => ({
                        ...previous,
                        processing: false,
                    }));
                },
            },
        );
    };

    const confirmationIsConvert = confirmation.action === "convert";

    return (
        <>
            <Head title="Quotations & Proforma Invoices" />
            <ConversionModal state={conversionModal} quotation={quotation} accounts={accounts} onAddPayment={addPayment} onUpdatePayment={updatePayment} onRemovePayment={removePayment} onChange={(field, value) => setConversionModal((previous) => ({ ...previous, [field]: value }))} onClose={() => { if (!conversionModal.processing) setConversionModal((previous) => ({ ...previous, show: false })); }} onSubmit={submitConversion} />

            <div className="rr-quote-page rr-quote-index-page">
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
                                <span>Quotes, approvals & sales conversion</span>
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
                        label="Current page amount"
                        value={formatMoney(pageAmount)}
                        tone="slate"
                    />
                    <Metric
                        icon="bi-patch-check"
                        label="Approved on this page"
                        value={approvedCount}
                        tone="green"
                    />
                    <Metric
                        icon="bi-arrow-right-circle"
                        label="Converted on this page"
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
                        <div className="rr-quote-filter-tools">
                            <button type="button" className="rr-quote-reset" aria-expanded={filterOpen} aria-controls="quotation-filter-content" onClick={() => setFilterOpen((value) => !value)}><i className={`bi bi-chevron-${filterOpen ? "up" : "down"}`}></i>{filterOpen ? "Hide filters" : "Show filters"}</button>
                            <button
                                type="button"
                                disabled={filtering}
                                className="rr-quote-reset"
                                onClick={resetFilters}
                            >
                                <i className="bi bi-arrow-counterclockwise"></i>
                                Reset
                            </button>
                        </div>
                    </div>

                    <div id="quotation-filter-content" className="rr-quote-window-body" hidden={!filterOpen}>
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
                                    <div className="rr-quote-date-field">
                                        <Field label="From Date">
                                            <input
                                                type="date"
                                                value={form.date_from}
                                                onClick={(event) =>
                                                    event.currentTarget.showPicker?.()
                                                }
                                                onChange={(event) =>
                                                    updateFilter("date_from", event.target.value)
                                                }
                                            />
                                        </Field>
                                    </div>

                                    <div className="rr-quote-date-field">
                                        <Field label="To Date">
                                            <input
                                                type="date"
                                                value={form.date_to}
                                                onClick={(event) =>
                                                    event.currentTarget.showPicker?.()
                                                }
                                                onChange={(event) =>
                                                    updateFilter("date_to", event.target.value)
                                                }
                                            />
                                        </Field>
                                    </div>
                                </>
                            )}

                            <button type="submit" className="rr-quote-btn primary" disabled={filtering}>
                                <i className={filtering ? "bi bi-arrow-repeat rr-quote-index-spin" : "bi bi-search"}></i>
                                {filtering ? "Updating…" : "Apply filters"}
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

                    {filtering && <div className="rr-quote-filter-loading" role="status"><i className="bi bi-arrow-repeat rr-quote-index-spin"></i> Updating document register…</div>}
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
                                                    if (event.key === "Enter" && event.target === event.currentTarget) {
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
                                                        className="view"
                                                        title="View"
                                                    >
                                                        <i className="bi bi-eye"></i>
                                                    </Link>

                                                    {!terminal && !expired && (
                                                        <Link
                                                            href={`/quotations/${quotation.id}/edit`}
                                                            className="edit"
                                                            title="Edit"
                                                        >
                                                            <i className="bi bi-pencil"></i>
                                                        </Link>
                                                    )}

                                                    <a
                                                        href={`/quotations/${quotation.id}/print`}
                                                        className="print"
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
        <nav className="rr-quote-pagination" aria-label="Quotation pages">
            {links.map((link, index) => (
                <Link
                    key={`${link.label}-${index}`}
                    href={link.url || "#"}
                    preserveScroll
                    aria-current={link.active ? "page" : undefined}
                    aria-disabled={!link.url}
                    tabIndex={link.url ? 0 : -1}
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
    const transferForm = React.useRef(null);
    React.useEffect(() => {
        if (!state.show) return;
        const previous = document.activeElement;
        const timer = window.setTimeout(() => transferForm.current?.querySelector("select:not(:disabled), input:not(:disabled)")?.focus(), 0);
        return () => { window.clearTimeout(timer); previous?.focus?.(); };
    }, [state.show]);
    if (!state.show) return null;

    return (
        <div className="rr-quote-modal-backdrop rr-quote-transfer-backdrop" role="presentation">
            <form
                ref={transferForm}
                className="rr-quote-modal rr-quote-stock-modal rr-quote-transfer-modal"
                role="dialog" aria-modal="true" aria-label="Transfer stock to shop"
                onKeyDown={(event) => {
                    if (event.key === "Escape" && !state.processing) { event.preventDefault(); onClose(); return; }
                    if (event.key === "Enter") {
                        event.preventDefault();
                        if (!state.processing && !event.repeat) transferForm.current?.requestSubmit();
                        return;
                    }
                    if (["ArrowLeft", "ArrowRight"].includes(event.key)) {
                        event.preventDefault();
                        const fields = Array.from(transferForm.current.querySelectorAll("select:not(:disabled), input:not(:disabled), button[type='submit']:not(:disabled)"));
                        const index = fields.indexOf(event.target);
                        const next = fields[index + (event.key === "ArrowRight" ? 1 : -1)];
                        next?.focus(); if (next?.tagName === "INPUT") next.select();
                    }
                    if (event.key === "Tab") {
                        const fields = Array.from(transferForm.current.querySelectorAll("button:not(:disabled), select:not(:disabled), input:not(:disabled)"));
                        const first = fields[0], last = fields[fields.length - 1];
                        if (event.shiftKey && event.target === first) { event.preventDefault(); last?.focus(); }
                        if (!event.shiftKey && event.target === last) { event.preventDefault(); first?.focus(); }
                    }
                }}
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

                <div className="rr-quote-transfer-shortcuts"><span><kbd>←</kbd><kbd>→</kbd> Move between fields</span><span><kbd>↑</kbd><kbd>↓</kbd> Choose godown</span><span><kbd>Enter</kbd> Transfer stock</span><span><kbd>Esc</kbd> Close</span></div>
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

function ConversionModal({
    state,
    quotation,
    accounts,
    onAddPayment,
    onUpdatePayment,
    onRemovePayment,
    onChange,
    onClose,
    onSubmit,
}) {
    const formRef = React.useRef(null);
    React.useEffect(() => {
        if (!state.show) return;
        const previous = document.activeElement;
        const timer = window.setTimeout(() => formRef.current?.querySelector("input:not([readonly]), select")?.focus(), 0);
        return () => { window.clearTimeout(timer); previous?.focus?.(); };
    }, [state.show]);
    if (!state.show) return null;

    const grandTotal = numberValue(quotation.grand_total);
    const paidAmount = state.payments.reduce(
        (sum, payment) => sum + numberValue(payment.amount),
        0,
    );
    const balanceAmount = Math.max(0, grandTotal - paidAmount);

    return (
        <div className="rr-quote-modal-backdrop rr-quote-transfer-backdrop" role="presentation">
            <form
                ref={formRef}
                className="rr-quote-modal rr-quote-stock-modal rr-quote-transfer-modal rr-quote-conversion-modal"
                role="dialog" aria-modal="true" aria-label="Convert to Sales Invoice"
                onKeyDown={(event) => {
                    if (event.key === "Escape" && !state.processing) { event.preventDefault(); onClose(); return; }
                    if (["ArrowLeft", "ArrowRight"].includes(event.key) && event.target.tagName !== "TEXTAREA") {
                        event.preventDefault();
                        const fields = Array.from(formRef.current.querySelectorAll("input:not(:disabled):not([readonly]), select:not(:disabled), textarea:not(:disabled), button[type='submit']:not(:disabled)"));
                        const next = fields[fields.indexOf(event.target) + (event.key === "ArrowRight" ? 1 : -1)]; next?.focus(); if (next?.tagName === "INPUT" && next.type !== "date") next.select();
                    }
                    if (event.key === "Enter" && event.target.tagName !== "TEXTAREA" && event.target.tagName !== "BUTTON") {
                        event.preventDefault();
                        const fields = Array.from(formRef.current.querySelectorAll("input:not(:disabled):not([readonly]), select:not(:disabled), textarea:not(:disabled), button[type='submit']:not(:disabled)"));
                        const next = fields[fields.indexOf(event.target) + 1]; next?.focus();
                    }
                    if (event.key === "Enter" && event.ctrlKey && !state.processing) { event.preventDefault(); formRef.current?.requestSubmit(); }
                    if (event.key === "Tab") {
                        const fields = Array.from(formRef.current.querySelectorAll("input:not(:disabled),select:not(:disabled),textarea:not(:disabled),button:not(:disabled)"));
                        if (event.shiftKey && event.target === fields[0]) { event.preventDefault(); fields[fields.length - 1]?.focus(); }
                        if (!event.shiftKey && event.target === fields[fields.length - 1]) { event.preventDefault(); fields[0]?.focus(); }
                    }
                }}
                onSubmit={onSubmit}
            >
                <div className="rr-quote-modal-title">
                    <div>
                        <i className="bi bi-receipt-cutoff"></i>
                        <strong>Convert to Sales Invoice</strong>
                    </div>
                    <button
                        type="button"
                        disabled={state.processing}
                        onClick={onClose}
                    >
                        <i className="bi bi-x-lg"></i>
                    </button>
                </div>

                <div className="rr-quote-stock-notice">
                    <i className="bi bi-info-circle"></i>
                    The quotation will become a completed Sales invoice. Shop stock,
                    bill-wise cost and profit will be recorded during conversion.
                </div>

                {state.error && (
                    <div className="rr-quote-stock-error">{state.error}</div>
                )}

                <div className="rr-quote-window-body rr-quote-mini-grid">
                    <label className="rr-quote-create-field">
                        <span>Invoice Date</span>
                        <input
                            required
                            type="date" onClick={(event) => { try { event.currentTarget.showPicker?.(); } catch { } }}
                            value={state.invoice_date}
                            onChange={(event) =>
                                onChange("invoice_date", event.target.value)
                            }
                        />
                    </label>

                    <label className="rr-quote-create-field">
                        <span>Quotation Total</span>
                        <input readOnly value={formatMoney(grandTotal)} />
                    </label>
                </div>

                <div className="rr-quote-window-title">
                    <div>
                        <i className="bi bi-wallet2"></i>
                        <strong>Payment Collection</strong>
                    </div>
                    <button
                        type="button"
                        className="rr-quote-btn primary compact"
                        disabled={state.processing || !accounts.length}
                        onClick={onAddPayment}
                    >
                        <i className="bi bi-plus-lg"></i>
                        Add Payment
                    </button>
                </div>

                <div className="rr-quote-stock-table-wrap">
                    <table className="rr-quote-stock-table">
                        <thead>
                            <tr>
                                <th>Cash / Bank Account</th>
                                <th>Payment Mode</th>
                                <th>Reference No.</th>
                                <th className="number">Amount</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            {state.payments.length ? (
                                state.payments.map((payment, index) => (
                                    <tr key={payment.key ?? index}>
                                        <td>
                                            <select
                                                required
                                                value={payment.account_id}
                                                onChange={(event) =>
                                                    onUpdatePayment(
                                                        index,
                                                        "account_id",
                                                        event.target.value,
                                                    )
                                                }
                                            >
                                                <option value="">Select Account</option>
                                                {accounts.map((account) => (
                                                    <option
                                                        key={account.id}
                                                        value={account.id}
                                                    >
                                                        {account.account_name}
                                                        {account.account_code
                                                            ? ` — ${account.account_code}`
                                                            : ""}
                                                    </option>
                                                ))}
                                            </select>
                                        </td>
                                        <td>
                                            <select
                                                required
                                                value={payment.payment_mode}
                                                onChange={(event) =>
                                                    onUpdatePayment(
                                                        index,
                                                        "payment_mode",
                                                        event.target.value,
                                                    )
                                                }
                                            >
                                                <option value="cash">Cash</option>
                                                <option value="gpay">GPay</option>
                                                <option value="phonepe">PhonePe</option>
                                                <option value="upi">Other UPI</option>
                                                <option value="bank_transfer">
                                                    Bank Transfer
                                                </option>
                                                <option value="card">Card</option>
                                                <option value="cheque">Cheque</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input
                                                type="text"
                                                maxLength="150"
                                                value={payment.reference_no}
                                                placeholder="Optional"
                                                onChange={(event) =>
                                                    onUpdatePayment(
                                                        index,
                                                        "reference_no",
                                                        event.target.value,
                                                    )
                                                }
                                            />
                                        </td>
                                        <td>
                                            <input
                                                required
                                                type="number"
                                                min="0.01"
                                                step="0.01"
                                                value={payment.amount}
                                                onFocus={(event) =>
                                                    event.target.select()
                                                }
                                                onChange={(event) =>
                                                    onUpdatePayment(
                                                        index,
                                                        "amount",
                                                        event.target.value,
                                                    )
                                                }
                                            />
                                        </td>
                                        <td>
                                            <button
                                                type="button"
                                                className="rr-quote-remove"
                                                title="Remove payment"
                                                onClick={() => onRemovePayment(index)}
                                            >
                                                <i className="bi bi-x-lg"></i>
                                            </button>
                                        </td>
                                    </tr>
                                ))
                            ) : (
                                <tr>
                                    <td colSpan="5" className="rr-quote-items-empty">
                                        No payment added. The invoice will be created as
                                        Unpaid.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>

                <div className="rr-quote-summary rr-quote-show-summary">
                    <SummaryRow label="Invoice Total" value={formatMoney(grandTotal)} />
                    <SummaryRow label="Paid Amount" value={formatMoney(paidAmount)} />
                    <SummaryRow
                        grand
                        label="Balance Amount"
                        value={formatMoney(balanceAmount)}
                    />
                </div>

                <div className="rr-quote-window-body">
                    <label className="rr-quote-create-field">
                        <span>Conversion Remarks</span>
                        <textarea
                            rows="2"
                            maxLength="2000"
                            value={state.remarks}
                            onChange={(event) =>
                                onChange("remarks", event.target.value)
                            }
                        ></textarea>
                    </label>
                </div>

                <div className="rr-quote-transfer-shortcuts"><span><kbd>←</kbd><kbd>→</kbd> Move fields</span><span><kbd>Enter</kbd> Next field / activate button</span><span><kbd>Ctrl + Enter</kbd> Convert invoice</span><span><kbd>Esc</kbd> Close</span></div>
                <div className="rr-quote-modal-actions">
                    <button
                        type="button"
                        disabled={state.processing}
                        onClick={onClose}
                    >
                        Not Now
                    </button>
                    <button
                        type="submit"
                        className="primary"
                        disabled={state.processing || paidAmount > grandTotal}
                    >
                        {state.processing
                            ? "Converting..."
                            : "Convert to Sales Invoice"}
                    </button>
                </div>
            </form>
        </div>
    );
}


function today() {
    const now = new Date();

    return new Date(now.getTime() - now.getTimezoneOffset() * 60000)
        .toISOString()
        .slice(0, 10);
}

function firstError(errors, fallback) {
    if (!errors || typeof errors !== "object") return fallback;

    return Object.values(errors).flat().find(Boolean) || fallback;
}

function accountMode(account) {
    if (!account) return "cash";

    return {
        cash: "cash",
        upi: "upi",
        bank: "bank_transfer",
        card: "card",
    }[account.account_type] ?? "cash";
}

function SummaryRow({ label, value, grand = false }) {
    return (
        <div className={`rr-quote-summary-row ${grand ? "grand" : ""}`}>
            <span>{label}</span>
            <strong>{value}</strong>
        </div>
    );
}
