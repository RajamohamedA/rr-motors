import React, { useMemo, useState } from "react";

import axios from "axios";

import { Head, Link, router, usePage } from "@inertiajs/react";

import BusinessLayout from "@/Layouts/BusinessLayout";
import MasterNavigation from "../../Components/Business/MasterNavigation";
import ConfirmModal from "../../Components/ConfirmModal";

import "../../../css/business/quotations.css";

export default function Show({ quotation }) {
    const { flash = {}, errors: pageErrors = {} } = usePage().props;
    const [statusProcessing, setStatusProcessing] = useState(false);
    const [confirmation, setConfirmation] = useState({
        show: false,
        action: null,
        processing: false,
    });
    const [stockModal, setStockModal] = useState({
        show: false,
        loading: false,
        processing: false,
        shortages: [],
        transfers: [],
        error: "",
    });

    const items = Array.isArray(quotation?.items) ? quotation.items : [];
    const isGst = quotation?.billing_type === "gst";
    const isMechanic =
        quotation?.sale_source === "mechanic" && quotation?.mechanic;
    const expired = isExpired(quotation);
    const terminal = ["Converted", "Rejected", "Cancelled"].includes(
        quotation?.status,
    );
    const canEdit =
        ["Draft", "Pending", "Approved"].includes(quotation?.status) && !expired;
    const canConvert = quotation?.status === "Approved" && !expired;
    const title =
        quotation?.document_type === "proforma_invoice"
            ? "Proforma Invoice"
            : "Quotation";

    const itemTotals = useMemo(
        () =>
            items.reduce(
                (totals, item) => {
                    totals.quantity += numberValue(item.quantity);
                    totals.freeQuantity += numberValue(item.free_quantity);
                    totals.discount += numberValue(item.discount_amount);
                    return totals;
                },
                { quantity: 0, freeQuantity: 0, discount: 0 },
            ),
        [items],
    );

    const updateStatus = (status) => {
        if (!status || status === quotation.status) return;

        setStatusProcessing(true);
        router.patch(
            `/quotations/${quotation.id}/status`,
            { status },
            {
                preserveScroll: true,
                onFinish: () => setStatusProcessing(false),
            },
        );
    };

    const openConfirmation = (action) => {
        setConfirmation({
            show: true,
            action,
            processing: false,
        });
    };

    const prepareConversion = async () => {
        setStockModal((previous) => ({
            ...previous,
            loading: true,
            error: "",
        }));

        try {
            const response = await axios.get(
                `/quotations/${quotation.id}/conversion-stock`,
            );
            const shortages = response.data?.shortages ?? [];

            if (response.data?.ready || shortages.length === 0) {
                setStockModal((previous) => ({ ...previous, loading: false }));
                openConfirmation("convert");
                return;
            }

            setStockModal({
                show: true,
                loading: false,
                processing: false,
                shortages,
                transfers: defaultTransfers(shortages),
                error: "",
            });
        } catch (error) {
            setStockModal((previous) => ({
                ...previous,
                show: true,
                loading: false,
                error: apiError(error, "Unable to check conversion stock."),
            }));
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

    const transferSelectedStock = async (event) => {
        event.preventDefault();
        setStockModal((previous) => ({
            ...previous,
            processing: true,
            error: "",
        }));

        try {
            const response = await axios.post(
                `/quotations/${quotation.id}/transfer-stock`,
                {
                    transfers: stockModal.transfers
                        .filter(
                            (transfer) =>
                                transfer.godown_id && numberValue(transfer.quantity) > 0,
                        )
                        .map((transfer) => ({
                            product_id: transfer.product_id,
                            godown_id: transfer.godown_id,
                            quantity: transfer.quantity,
                        })),
                },
            );
            const shortages = response.data?.shortages ?? [];

            if (response.data?.ready || shortages.length === 0) {
                setStockModal({
                    show: false,
                    loading: false,
                    processing: false,
                    shortages: [],
                    transfers: [],
                    error: "",
                });
                openConfirmation("convert");
                return;
            }

            setStockModal({
                show: true,
                loading: false,
                processing: false,
                shortages,
                transfers: defaultTransfers(shortages),
                error: response.data?.message ?? "Some Shop shortages remain.",
            });
        } catch (error) {
            setStockModal((previous) => ({
                ...previous,
                processing: false,
                error: apiError(error, "Unable to transfer stock."),
            }));
        }
    };

    const closeConfirmation = () => {
        if (confirmation.processing) return;
        setConfirmation({ show: false, action: null, processing: false });
    };

    const confirmAction = () => {
        setConfirmation((previous) => ({ ...previous, processing: true }));

        const finish = () => {
            setConfirmation({ show: false, action: null, processing: false });
        };

        if (confirmation.action === "convert") {
            router.post(
                `/quotations/${quotation.id}/convert`,
                {},
                { preserveScroll: true, onFinish: finish },
            );
            return;
        }

        router.patch(
            `/quotations/${quotation.id}/cancel`,
            {},
            { preserveScroll: true, onFinish: finish },
        );
    };

    const confirmationIsConvert = confirmation.action === "convert";

    return (
        <>
            <Head title={`${title} ${quotation.quotation_no}`} />

            <div className="rr-quote-page rr-quote-show-page">
                <MasterNavigation
                    active="quotations"
                    title="Quotations"
                    subtitle="Quotation and proforma invoice details"
                />

                {flash.success && (
                    <Alert tone="success" icon="bi-check-circle-fill">
                        {flash.success}
                    </Alert>
                )}

                {flash.error && (
                    <Alert tone="danger" icon="bi-exclamation-triangle-fill">
                        {flash.error}
                    </Alert>
                )}

                {!flash.error && pageErrors.stock && (
                    <Alert tone="danger" icon="bi-box-seam-fill">
                        {pageErrors.stock}
                    </Alert>
                )}

                <header className="rr-quote-header rr-quote-show-header">
                    <div className="rr-quote-header-main">
                        <Link href="/quotations" className="rr-quote-back" title="Back">
                            <i className="bi bi-arrow-left"></i>
                        </Link>

                        <div className="rr-quote-header-icon">
                            <i
                                className={`bi ${title === "Proforma Invoice"
                                    ? "bi-receipt"
                                    : "bi-file-earmark-text"
                                    }`}
                            ></i>
                        </div>

                        <div>
                            <span className="rr-quote-eyebrow">{title}</span>
                            <h1>{quotation.quotation_no}</h1>
                            <div className="rr-quote-header-meta">
                                <span>{formatDate(quotation.quotation_date)}</span>
                                <span>{quotation.store?.store_name ?? "Current Branch"}</span>
                                <StatusBadge status={expired ? "Expired" : quotation.status} />
                            </div>
                        </div>
                    </div>

                    <div className="rr-quote-header-actions rr-quote-show-actions">
                        {canEdit && (
                            <Link
                                href={`/quotations/${quotation.id}/edit`}
                                className="rr-quote-btn edit"
                            >
                                <i className="bi bi-pencil-square"></i>
                                Edit
                            </Link>
                        )}

                        <a
                            href={`/quotations/${quotation.id}/print?paper=a4`}
                            target="_blank"
                            rel="noreferrer"
                            className="rr-quote-btn print"
                        >
                            <i className="bi bi-printer"></i>
                            Print
                        </a>

                        <div className="rr-quote-download-group">
                            <a
                                href={`/quotations/${quotation.id}/pdf?paper=a4`}
                                className="rr-quote-btn pdf"
                                title="Download A4 PDF"
                            >
                                <i className="bi bi-file-earmark-pdf"></i>
                                PDF A4
                            </a>
                            <a
                                href={`/quotations/${quotation.id}/pdf?paper=a5`}
                                className="rr-quote-btn compact pdf"
                                title="Download A5 PDF"
                            >
                                A5
                            </a>
                        </div>

                        {canConvert && (
                            <button
                                type="button"
                                className="rr-quote-btn primary convert"
                                disabled={stockModal.loading}
                                onClick={prepareConversion}
                            >
                                {stockModal.loading ? (
                                    <span className="spinner-border spinner-border-sm"></span>
                                ) : (
                                    <i className="bi bi-arrow-right-circle"></i>
                                )}
                                {stockModal.loading ? "Checking Stock..." : "Convert to Sale"}
                            </button>
                        )}
                    </div>
                </header>

                <div className="rr-quote-show-commandbar">
                    <div>
                        <span>Status</span>
                        <select
                            title="Change document status"
                            value={quotation.status}
                            disabled={statusProcessing || terminal || expired}
                            onChange={(event) => updateStatus(event.target.value)}
                        >
                            <option value="Draft">Draft</option>
                            <option value="Pending">Pending</option>
                            <option value="Approved">Approved</option>
                            <option value="Rejected">Rejected</option>
                            {quotation.status === "Converted" && (
                                <option value="Converted">Converted</option>
                            )}
                            {quotation.status === "Cancelled" && (
                                <option value="Cancelled">Cancelled</option>
                            )}
                        </select>
                        {statusProcessing && (
                            <span className="rr-quote-command-processing">
                                <span className="spinner-border spinner-border-sm"></span>
                                Updating...
                            </span>
                        )}
                    </div>

                    {!terminal && (
                        <button
                            type="button"
                            className="rr-quote-command-danger"
                            onClick={() => openConfirmation("cancel")}
                        >
                            <i className="bi bi-x-octagon"></i>
                            Cancel Document
                        </button>
                    )}
                </div>

                {expired && quotation.status !== "Expired" && (
                    <Alert tone="warning" icon="bi-clock-history">
                        This document passed its validity date and can no longer be edited
                        or converted.
                    </Alert>
                )}

                <div className="rr-quote-show-overview">
                    <section className="rr-quote-window">
                        <WindowTitle icon="bi-person" title="Customer Details" />
                        <div className="rr-quote-show-party">
                            <div className="rr-quote-party-identity">
                                <div className="rr-quote-party-avatar">
                                    <i className="bi bi-person"></i>
                                </div>
                                <div className="rr-quote-party-primary">
                                    <strong>
                                        {quotation.customer?.customer_name ?? "—"}
                                    </strong>
                                    <span>
                                        {quotation.customer?.customer_code ??
                                            "No customer code"}
                                    </span>
                                </div>
                            </div>

                            <div className="rr-quote-party-information">
                                <InfoLine
                                    icon="bi-telephone"
                                    label="Mobile"
                                    value={
                                        quotation.customer?.mobile ||
                                        quotation.customer?.phone ||
                                        "—"
                                    }
                                />
                                <InfoLine
                                    icon="bi-envelope"
                                    label="Email"
                                    value={quotation.customer?.email || "—"}
                                />
                                <InfoLine
                                    icon="bi-receipt"
                                    label="GSTIN"
                                    value={
                                        quotation.customer?.gst_number || "Not provided"
                                    }
                                />
                                <InfoLine
                                    wide
                                    icon="bi-geo-alt"
                                    label="Billing Address"
                                    value={customerAddress(quotation.customer)}
                                />
                            </div>
                        </div>
                    </section>

                    <section className="rr-quote-window">
                        <WindowTitle icon="bi-file-text" title="Document Details" />
                        <div className="rr-quote-detail-grid">
                            <Detail label="Document Type" value={title} />
                            <Detail
                                label="Billing"
                                value={isGst ? "GST Bill" : "Without GST"}
                            />
                            <Detail
                                label="Document Date"
                                value={formatDate(quotation.quotation_date)}
                            />
                            <Detail
                                label="Valid Until"
                                value={formatDate(quotation.valid_until)}
                                tone={expired ? "danger" : ""}
                            />
                            <Detail
                                label="Sale Source"
                                value={isMechanic ? "Mechanic Referral" : "Direct Customer"}
                            />
                            <Detail
                                label="Created By"
                                value={quotation.creator?.name ?? "—"}
                            />
                        </div>
                    </section>

                    <section className="rr-quote-window rr-quote-total-window">
                        <WindowTitle icon="bi-calculator" title="Document Value" />
                        <div className="rr-quote-total-hero">
                            <span>Grand Total</span>
                            <strong>{formatMoney(quotation.grand_total)}</strong>
                            <small>
                                {items.length} item{items.length === 1 ? "" : "s"} · Qty {" "}
                                {formatQty(itemTotals.quantity)}
                            </small>
                        </div>
                        <div className="rr-quote-total-mini">
                            <span>
                                Taxable <strong>{formatMoney(quotation.sub_total)}</strong>
                            </span>
                            {isGst && (
                                <span>
                                    GST <strong>{formatMoney(quotation.gst_total)}</strong>
                                </span>
                            )}
                        </div>
                    </section>
                </div>

                {isMechanic && (
                    <section className="rr-quote-window rr-quote-mechanic-strip">
                        <WindowTitle icon="bi-tools" title="Mechanic Referral" />
                        <div className="rr-quote-mechanic-details">
                            <Detail
                                label="Mechanic"
                                value={quotation.mechanic.mechanic_name}
                            />
                            <Detail
                                label="Code"
                                value={quotation.mechanic.mechanic_code || "—"}
                            />
                            <Detail
                                label="Mobile"
                                value={quotation.mechanic.mobile || "—"}
                            />
                            <Detail
                                label="Commission Type"
                                value={
                                    quotation.commission_type === "percentage"
                                        ? `${formatQty(quotation.commission_percent)}%`
                                        : "Fixed Amount"
                                }
                            />
                            <Detail
                                label="Estimated Commission"
                                value={formatMoney(quotation.commission_amount)}
                                tone="success"
                            />
                        </div>
                    </section>
                )}

                <section className="rr-quote-window rr-quote-show-items">
                    <div className="rr-quote-window-title">
                        <div>
                            <i className="bi bi-box-seam"></i>
                            <strong>Product Details</strong>
                        </div>
                        <span>{items.length} product(s)</span>
                    </div>

                    <div className="rr-quote-table-wrap">
                        <table className="rr-quote-items-table rr-quote-show-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Product / Part Number</th>
                                    <th>HSN</th>
                                    <th className="number">Qty</th>
                                    <th className="number">Free</th>
                                    <th className="number">Rate</th>
                                    <th>Discount</th>
                                    <th className="number">Taxable</th>
                                    {isGst && <th>GST</th>}
                                    <th className="number">Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                {items.map((item, index) => (
                                    <tr key={item.id ?? `${item.product_id}-${index}`}>
                                        <td>{index + 1}</td>
                                        <td className="rr-quote-product-cell">
                                            <strong>{item.product?.product_name ?? "Product"}</strong>
                                            <small>{productDetails(item.product)}</small>
                                        </td>
                                        <td>{item.product?.hsn_code || "—"}</td>
                                        <td className="number">{formatQty(item.quantity)}</td>
                                        <td className="number">{formatQty(item.free_quantity)}</td>
                                        <td className="number">{formatMoney(item.sale_price)}</td>
                                        <td>
                                            {item.discount_type === "percentage"
                                                ? `${formatQty(item.discount_value)}%`
                                                : formatMoney(item.discount_value)}
                                            <small className="rr-quote-cell-subtext">
                                                {formatMoney(item.discount_amount)}
                                            </small>
                                        </td>
                                        <td className="number">
                                            {formatMoney(item.taxable_amount)}
                                        </td>
                                        {isGst && (
                                            <td>
                                                {formatQty(item.gst_percent)}%
                                                <small className="rr-quote-cell-subtext">
                                                    {formatMoney(item.gst_amount)}
                                                </small>
                                            </td>
                                        )}
                                        <td className="number amount">
                                            {formatMoney(item.line_total)}
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                            <tfoot>
                                <tr>
                                    <td colSpan="3">Totals</td>
                                    <td className="number">{formatQty(itemTotals.quantity)}</td>
                                    <td className="number">
                                        {formatQty(itemTotals.freeQuantity)}
                                    </td>
                                    <td></td>
                                    <td>{formatMoney(itemTotals.discount)}</td>
                                    <td className="number">
                                        {formatMoney(quotation.sub_total)}
                                    </td>
                                    {isGst && (
                                        <td>{formatMoney(quotation.gst_total)}</td>
                                    )}
                                    <td className="number amount">
                                        {formatMoney(
                                            numberValue(quotation.sub_total) +
                                            numberValue(quotation.gst_total),
                                        )}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </section>

                <div className="rr-quote-show-bottom">
                    <section className="rr-quote-window">
                        <WindowTitle icon="bi-card-text" title="Terms and Notes" />
                        <div className="rr-quote-text-panels">
                            <TextBlock
                                title="Terms & Conditions"
                                value={quotation.terms_conditions}
                            />
                            <TextBlock title="Remarks" value={quotation.remarks} />
                        </div>
                    </section>

                    <section className="rr-quote-window">
                        <WindowTitle icon="bi-cash-stack" title="Amount Summary" />
                        <div className="rr-quote-summary rr-quote-show-summary">
                            <SummaryRow
                                label="Taxable Amount"
                                value={formatMoney(quotation.sub_total)}
                            />
                            {isGst && (
                                <SummaryRow
                                    label="GST Amount"
                                    value={formatMoney(quotation.gst_total)}
                                />
                            )}
                            <SummaryRow
                                label="Final Discount"
                                value={`- ${formatMoney(quotation.final_discount)}`}
                            />
                            <SummaryRow
                                label="Round Off"
                                value={formatSignedMoney(quotation.round_off)}
                            />
                            <SummaryRow
                                grand
                                label="Grand Total"
                                value={formatMoney(quotation.grand_total)}
                            />
                        </div>
                    </section>
                </div>

                <ConfirmModal
                    show={confirmation.show}
                    title={
                        confirmationIsConvert
                            ? "Convert to Sales Invoice"
                            : "Cancel Document"
                    }
                    message={
                        confirmationIsConvert
                            ? `Convert "${quotation.quotation_no}" into a Sales invoice? Current shop stock will be checked and reduced.`
                            : `Cancel "${quotation.quotation_no}"? The document will remain available for audit history.`
                    }
                    confirmText={
                        confirmationIsConvert ? "Convert to Sale" : "Cancel Document"
                    }
                    cancelText={confirmationIsConvert ? "Not Now" : "Keep Document"}
                    icon={
                        confirmationIsConvert ? "bi-arrow-right-circle" : "bi-x-octagon"
                    }
                    variant={confirmationIsConvert ? "primary" : "danger"}
                    processing={confirmation.processing}
                    onConfirm={confirmAction}
                    onCancel={closeConfirmation}
                />

                <StockTransferModal
                    state={stockModal}
                    onUpdate={updateTransfer}
                    onClose={() => {
                        if (stockModal.processing) return;
                        setStockModal((previous) => ({ ...previous, show: false }));
                    }}
                    onSubmit={transferSelectedStock}
                />
            </div>
        </>
    );
}

function Alert({ tone, icon, children }) {
    return (
        <div className={`rr-quote-alert ${tone}`}>
            <i className={`bi ${icon}`}></i>
            {children}
        </div>
    );
}

function WindowTitle({ icon, title }) {
    return (
        <div className="rr-quote-window-title">
            <div>
                <i className={`bi ${icon}`}></i>
                <strong>{title}</strong>
            </div>
        </div>
    );
}

function StatusBadge({ status }) {
    return (
        <span className={`rr-quote-status ${statusTone(status)}`}>
            {status || "Draft"}
        </span>
    );
}

function InfoLine({ icon, label, value, wide = false }) {
    return (
        <div className={`rr-quote-info-line ${wide ? "wide" : ""}`}>
            <i className={`bi ${icon}`}></i>
            <div>
                <span>{label}</span>
                <strong>{value}</strong>
            </div>
        </div>
    );
}

function Detail({ label, value, tone = "" }) {
    return (
        <div className={`rr-quote-detail ${tone}`}>
            <span>{label}</span>
            <strong>{value}</strong>
        </div>
    );
}

function TextBlock({ title, value }) {
    return (
        <div className="rr-quote-text-block">
            <strong>{title}</strong>
            <p>{value || "No details provided."}</p>
        </div>
    );
}

function SummaryRow({ label, value, grand = false }) {
    return (
        <div className={`rr-quote-summary-row ${grand ? "grand" : ""}`}>
            <span>{label}</span>
            <strong>{value}</strong>
        </div>
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

                <div className="rr-quote-stock-notice">
                    <i className="bi bi-info-circle"></i>
                    Select the Godown and confirm the transfer quantity for every item.
                    Nothing is transferred automatically.
                </div>

                {state.error && (
                    <div className="rr-quote-stock-error">{state.error}</div>
                )}

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
                                                <em>Total stock is still insufficient</em>
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
                                                min="1"
                                                step="1"
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

                <div className="rr-quote-modal-actions">
                    <button type="button" disabled={state.processing} onClick={onClose}>
                        Close
                    </button>
                    <button
                        type="submit"
                        className="primary"
                        disabled={state.processing}
                    >
                        {state.processing ? "Transferring..." : "Transfer Selected Stock"}
                    </button>
                </div>
            </form>
        </div>
    );
}

function defaultTransfers(shortages) {
    return shortages.map((shortage) => {
        const sufficient = (shortage.godowns ?? []).find(
            (godown) =>
                numberValue(godown.stock) >= numberValue(shortage.shortage_quantity),
        );
        const selected = sufficient ?? shortage.godowns?.[0] ?? null;

        return {
            product_id: shortage.product_id,
            godown_id: selected?.id ? String(selected.id) : "",
            quantity: String(
                Math.min(
                    numberValue(shortage.shortage_quantity),
                    numberValue(selected?.stock),
                ),
            ),
        };
    });
}

function apiError(error, fallback) {
    const errors = error?.response?.data?.errors;
    if (errors && typeof errors === "object") {
        const first = Object.values(errors).flat().find(Boolean);
        if (first) return String(first);
    }

    return error?.response?.data?.message ?? fallback;
}

function productDetails(product) {
    if (!product) return "Product details unavailable";

    return [
        product.product_code,
        product.part_number,
        product.manufacturer,
        product.unit?.unit_name || product.unit?.name,
    ]
        .filter(Boolean)
        .join(" · ");
}

function customerAddress(customer) {
    if (!customer) return "Not provided";

    return [
        customer.billing_address,
        customer.city,
        customer.state,
        customer.pincode,
    ]
        .filter(Boolean)
        .join(", ") || "Not provided";
}

function isExpired(quotation) {
    if (!quotation) return false;
    if (quotation.status === "Expired") return true;
    if (!quotation.valid_until) return false;

    const validity = String(quotation.valid_until).slice(0, 10);
    const now = new Date();
    const today = new Date(now.getTime() - now.getTimezoneOffset() * 60000)
        .toISOString()
        .slice(0, 10);

    return validity < today;
}

function statusTone(status) {
    return {
        Draft: "draft",
        Pending: "pending",
        Approved: "approved",
        Rejected: "rejected",
        Converted: "converted",
        Expired: "expired",
        Cancelled: "cancelled",
    }[status] ?? "draft";
}

function formatDate(value) {
    if (!value) return "—";

    const date = new Date(`${String(value).slice(0, 10)}T00:00:00`);
    if (Number.isNaN(date.getTime())) return String(value);

    return date.toLocaleDateString("en-IN", {
        day: "2-digit",
        month: "short",
        year: "numeric",
    });
}

function numberValue(value) {
    const number = Number.parseFloat(value);
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

function formatSignedMoney(value) {
    const number = numberValue(value);
    if (number === 0) return formatMoney(0);
    return `${number > 0 ? "+" : "-"} ${formatMoney(Math.abs(number))}`;
}

function formatQty(value) {
    return numberValue(value).toLocaleString("en-IN", {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    });
}

Show.layout = (page) => <BusinessLayout>{page}</BusinessLayout>;
