import React, { useMemo, useState } from "react";

import { Head, Link, router } from "@inertiajs/react";

import BusinessLayout from "@/Layouts/BusinessLayout";
import MasterNavigation from "../../Components/Business/MasterNavigation";
import ConfirmModal from "../../Components/ConfirmModal";

import "../../../css/business/stock-adjustment.css";

export default function Show({
    adjustment = {},
    totals = {},
}) {
    const items = Array.isArray(adjustment.items)
        ? adjustment.items
        : [];
    const completed = adjustment.status === "Completed";

    const [confirmation, setConfirmation] = useState({
        show: false,
        processing: false,
    });

    const locationSummary = useMemo(() => {
        const shopItems = items.filter(
            (item) => item.godown_id === null,
        ).length;
        const godownItems = items.length - shopItems;

        return {
            shopItems,
            godownItems,
        };
    }, [items]);

    const closeConfirmation = () => {
        if (confirmation.processing) {
            return;
        }

        setConfirmation({
            show: false,
            processing: false,
        });
    };

    const cancelAdjustment = () => {
        setConfirmation({
            show: true,
            processing: true,
        });

        router.patch(
            `/stock-adjustments/${adjustment.id}/cancel`,
            {},
            {
                preserveScroll: true,
                onFinish: () => {
                    setConfirmation({
                        show: false,
                        processing: false,
                    });
                },
            },
        );
    };

    return (
        <>
            <Head
                title={`Stock Adjustment ${adjustment.adjustment_no ?? ""}`}
            />

            <div className="rr-adjustment-page rr-adjustment-show-page">
                <MasterNavigation
                    active="stock-adjustments"
                    title="Inventory"
                    subtitle="Stock adjustment details and audit trail"
                />

                <div className="rr-adjustment-document-ribbon">
                    <div>
                        <i className="bi bi-grid-1x2"></i>
                        <span>Inventory</span>
                        <i className="bi bi-chevron-right"></i>
                        <span>Stock Adjustments</span>
                        <i className="bi bi-chevron-right"></i>
                        <strong>{adjustment.adjustment_no ?? "Details"}</strong>
                    </div>

                    <div>
                        <i className="bi bi-shield-check"></i>
                        Ledger-controlled document
                    </div>
                </div>

                <header className="rr-adjustment-header rr-adjustment-show-hero">
                    <div className="rr-adjustment-heading">
                        <div className="rr-adjustment-heading-icon">
                            <i className="bi bi-file-earmark-check"></i>
                        </div>

                        <div>
                            <span>Stock Adjustment Document</span>
                            <h1>
                                {adjustment.adjustment_no
                                    ?? "Adjustment Details"}
                            </h1>
                            <div className="rr-adjustment-show-heading-meta">
                                <TypeBadge type={adjustment.type} />
                                <StatusBadge status={adjustment.status} />
                            </div>
                        </div>
                    </div>

                    <div className="rr-adjustment-show-actions">
                        <Link
                            href="/stock-adjustments"
                            className="back"
                            title="Back to stock adjustment register"
                        >
                            <i className="bi bi-arrow-left"></i>
                            Back
                        </Link>

                        {completed && (
                            <Link
                                href={`/stock-adjustments/${adjustment.id}/edit`}
                                className="edit"
                                title="Edit stock adjustment"
                            >
                                <i className="bi bi-pencil-square"></i>
                                Edit
                            </Link>
                        )}

                        <a
                            href={`/stock-adjustments/${adjustment.id}/print`}
                            target="_blank"
                            rel="noreferrer"
                            className="print"
                            title="Open print preview"
                        >
                            <i className="bi bi-printer"></i>
                            Print
                        </a>

                        <a
                            href={`/stock-adjustments/${adjustment.id}/pdf`}
                            className="pdf"
                            title="Download PDF"
                        >
                            <i className="bi bi-file-earmark-pdf"></i>
                            PDF
                        </a>

                        {completed && (
                            <button
                                type="button"
                                className="cancel"
                                title="Cancel stock adjustment"
                                onClick={() =>
                                    setConfirmation({
                                        show: true,
                                        processing: false,
                                    })
                                }
                            >
                                <i className="bi bi-x-octagon"></i>
                                Cancel
                            </button>
                        )}
                    </div>
                </header>

                {!completed && (
                    <section className="rr-adjustment-cancel-banner">
                        <div>
                            <i className="bi bi-x-octagon"></i>
                        </div>

                        <div>
                            <strong>This adjustment has been cancelled</strong>
                            <span>
                                Reversing stock-ledger entries were created. The
                                original record remains visible for audit.
                            </span>
                        </div>
                    </section>
                )}

                <section className="rr-adjustment-show-summary">
                    <SummaryCard
                        icon="bi-calendar3"
                        label="Adjustment Date"
                        value={dateValue(adjustment.adjustment_date)}
                        tone="slate"
                        caption="Document date"
                    />

                    <SummaryCard
                        icon="bi-box-seam"
                        label="Product Items"
                        value={integer(items.length)}
                        tone="blue"
                        caption="Product lines"
                    />

                    <SummaryCard
                        icon={
                            adjustment.type === "increase"
                                ? "bi-plus-circle"
                                : "bi-dash-circle"
                        }
                        caption={
                            adjustment.type === "increase"
                                ? "Quantity added"
                                : "Quantity removed"
                        }
                        label="Adjusted Quantity"
                        value={quantity(totals.quantity)}
                        tone={
                            adjustment.type === "increase"
                                ? "green"
                                : "red"
                        }
                    />

                    <SummaryCard
                        icon="bi-currency-rupee"
                        label="Stock Value"
                        value={currency(totals.cost)}
                        tone="violet"
                        caption="Adjustment valuation"
                    />

                    <SummaryCard
                        icon="bi-geo-alt"
                        label="Locations"
                        value={`${locationSummary.shopItems} Shop / ${locationSummary.godownItems} Godown`}
                        tone="amber"
                        compact
                        caption="Stock locations"
                    />
                </section>

                <div className="rr-adjustment-show-grid">
                    <section className="rr-adjustment-window rr-adjustment-show-info">
                        <div className="rr-adjustment-window-title">
                            <div>
                                <i className="bi bi-file-text"></i>
                                <strong>Document Information</strong>
                            </div>
                        </div>

                        <div className="rr-adjustment-info-grid">
                            <InfoItem
                                label="Adjustment Number"
                                value={adjustment.adjustment_no}
                                icon="bi-hash"
                                featured
                            />
                            <InfoItem
                                label="Date"
                                value={dateValue(adjustment.adjustment_date)}
                                icon="bi-calendar3"
                            />
                            <InfoItem
                                label="Type"
                                icon="bi-arrow-left-right"
                            >
                                <TypeBadge type={adjustment.type} />
                            </InfoItem>
                            <InfoItem
                                label="Status"
                                icon="bi-check2-square"
                            >
                                <StatusBadge status={adjustment.status} />
                            </InfoItem>
                        </div>
                    </section>

                    <section className="rr-adjustment-window rr-adjustment-show-info">
                        <div className="rr-adjustment-window-title">
                            <div>
                                <i className="bi bi-building"></i>
                                <strong>Business Context</strong>
                            </div>
                        </div>

                        <div className="rr-adjustment-info-grid">
                            <InfoItem
                                label="Company"
                                value={companyName(adjustment.company)}
                                icon="bi-buildings"
                                featured
                            />
                            <InfoItem
                                label="Branch"
                                value={storeName(adjustment.store)}
                                icon="bi-shop"
                            />
                            <InfoItem
                                label="Created By"
                                value={adjustment.creator?.name ?? "System"}
                                icon="bi-person-circle"
                            />
                            <InfoItem
                                label="Created At"
                                value={dateTimeValue(adjustment.created_at)}
                                icon="bi-clock-history"
                            />
                        </div>
                    </section>
                </div>

                <section className="rr-adjustment-window rr-adjustment-show-items">
                    <div className="rr-adjustment-window-title">
                        <div>
                            <i className="bi bi-list-check"></i>
                            <strong>Stock Movement Details</strong>
                            <span className="rr-adjustment-record-count">
                                {items.length} items
                            </span>
                        </div>

                        <span className="rr-adjustment-show-table-hint">
                            Values recorded when adjustment was created
                        </span>
                    </div>

                    <div className="rr-adjustment-show-table-scroll">
                        <table className="rr-adjustment-show-table">
                            <thead>
                                <tr>
                                    <th className="serial">#</th>
                                    <th className="product">Product</th>
                                    <th>Location</th>
                                    <th className="number">Stock Before</th>
                                    <th className="number">Qty Adjusted</th>
                                    <th className="number">Stock After</th>
                                    <th className="number">Unit Cost</th>
                                    <th className="number">Total Cost</th>
                                </tr>
                            </thead>

                            <tbody>
                                {items.length > 0 ? (
                                    items.map((item, index) => (
                                        <StockRow
                                            key={item.id ?? index}
                                            item={item}
                                            index={index}
                                            type={adjustment.type}
                                        />
                                    ))
                                ) : (
                                    <tr>
                                        <td
                                            colSpan="8"
                                            className="rr-adjustment-show-empty"
                                        >
                                            <i className="bi bi-inbox"></i>
                                            No adjustment items found.
                                        </td>
                                    </tr>
                                )}
                            </tbody>

                            {items.length > 0 && (
                                <tfoot>
                                    <tr>
                                        <td colSpan="4">
                                            <strong>Document Total</strong>
                                        </td>
                                        <td className="number total-quantity">
                                            {quantity(totals.quantity)}
                                        </td>
                                        <td></td>
                                        <td></td>
                                        <td className="number total-cost">
                                            {currency(totals.cost)}
                                        </td>
                                    </tr>
                                </tfoot>
                            )}
                        </table>
                    </div>
                </section>

                <div className="rr-adjustment-show-notes-grid">
                    <section className="rr-adjustment-window rr-adjustment-show-note">
                        <div className="rr-adjustment-window-title">
                            <div>
                                <i className="bi bi-chat-left-text"></i>
                                <strong>Reason / Remarks</strong>
                            </div>
                        </div>

                        <div className="rr-adjustment-show-note-body">
                            <i className="bi bi-quote"></i>
                            <p>
                                {adjustment.remarks
                                    || "No remarks were entered for this adjustment."}
                            </p>
                        </div>
                    </section>

                    <section className="rr-adjustment-window rr-adjustment-audit-card">
                        <div className="rr-adjustment-window-title">
                            <div>
                                <i className="bi bi-shield-check"></i>
                                <strong>Audit Information</strong>
                            </div>
                        </div>

                        <div className="rr-adjustment-audit-body">
                            <AuditRow
                                icon="bi-person-check"
                                label="Created By"
                                value={adjustment.creator?.name ?? "System"}
                            />
                            <AuditRow
                                icon="bi-clock"
                                label="Created At"
                                value={dateTimeValue(adjustment.created_at)}
                            />
                            <AuditRow
                                icon="bi-arrow-repeat"
                                label="Last Updated"
                                value={dateTimeValue(adjustment.updated_at)}
                            />

                            {!completed && (
                                <>
                                    <AuditRow
                                        icon="bi-person-x"
                                        label="Cancelled By"
                                        value={
                                            adjustment.cancelled_by?.name
                                            ?? adjustment.cancelledBy?.name
                                            ?? "System"
                                        }
                                        danger
                                    />
                                    <AuditRow
                                        icon="bi-calendar-x"
                                        label="Cancelled At"
                                        value={dateTimeValue(
                                            adjustment.cancelled_at,
                                        )}
                                        danger
                                    />
                                </>
                            )}
                        </div>
                    </section>
                </div>

                {!completed && adjustment.cancel_reason && (
                    <section className="rr-adjustment-cancel-reason">
                        <i className="bi bi-exclamation-triangle"></i>
                        <div>
                            <strong>Cancellation Reason</strong>
                            <p>{adjustment.cancel_reason}</p>
                        </div>
                    </section>
                )}

                <footer className="rr-adjustment-show-footer">
                    <div>
                        <i className="bi bi-shield-lock"></i>
                        <span>
                            This document is linked to permanent stock-ledger
                            entries and retained for audit history.
                        </span>
                    </div>

                    <Link href="/stock-adjustments">
                        <i className="bi bi-arrow-left"></i>
                        Back to Register
                    </Link>
                </footer>

                <ConfirmModal
                    show={confirmation.show}
                    title="Cancel Stock Adjustment"
                    message={`Cancel "${adjustment.adjustment_no ?? ""}"? Reversing stock-ledger entries will be created for all ${items.length} items. This document cannot be edited afterward.`}
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

function SummaryCard({
    icon,
    label,
    value,
    tone,
    compact = false,
    caption = "",
}) {
    return (
        <article className={`rr-adjustment-show-card ${tone}`}>
            <div className="rr-adjustment-show-card-icon">
                <i className={`bi ${icon}`}></i>
            </div>

            <div className="rr-adjustment-show-card-content">
                <span>{label}</span>
                <strong className={compact ? "compact" : ""}>
                    {value}
                </strong>
                <small>{caption}</small>
            </div>

            <i className="bi bi-chevron-right rr-adjustment-card-arrow"></i>
        </article>
    );
}

function InfoItem({ label, value, icon, featured = false, children }) {
    return (
        <div className={`rr-adjustment-info-item ${featured ? "featured" : ""}`}>
            <i className={`bi ${icon}`}></i>

            <div>
                <span>{label}</span>
                {children || <strong>{value || "—"}</strong>}
            </div>
        </div>
    );
}

function StockRow({ item, index, type }) {
    const product = item.product ?? {};
    const increase = type === "increase";

    return (
        <tr className={increase ? "movement-in" : "movement-out"}>
            <td className="serial">{index + 1}</td>

            <td className="product">
                <strong>{product.product_name ?? "Unknown Product"}</strong>
                <small>
                    Code: {product.product_code || "—"}
                    <b>•</b>
                    Part: {product.part_number || "—"}
                </small>
            </td>

            <td>
                <span
                    className={`rr-adjustment-location-badge ${item.godown_id === null ? "shop" : "godown"
                        }`}
                >
                    <i
                        className={`bi ${item.godown_id === null
                                ? "bi-shop"
                                : "bi-building"
                            }`}
                    ></i>
                    {item.godown_id === null
                        ? "Shop"
                        : item.godown?.godown_name ?? "Godown"}
                </span>
            </td>

            <td className="number stock-before">
                {quantity(item.stock_before)}
            </td>

            <td
                className={`number adjusted-quantity ${increase ? "increase" : "decrease"
                    }`}
            >
                <span>{increase ? "+" : "−"}</span>
                {quantity(item.qty)}
            </td>

            <td className="number stock-after">
                {quantity(item.stock_after)}
            </td>

            <td className="number">
                {currency(item.unit_cost)}
            </td>

            <td className="number line-total">
                {currency(item.total_cost)}
            </td>
        </tr>
    );
}

function AuditRow({ icon, label, value, danger = false }) {
    return (
        <div className={danger ? "danger" : ""}>
            <i className={`bi ${icon}`}></i>
            <span>{label}</span>
            <strong>{value || "—"}</strong>
        </div>
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
            {status ?? "Unknown"}
        </span>
    );
}

function companyName(company) {
    return company?.company_name
        ?? company?.name
        ?? "—";
}

function storeName(store) {
    return store?.store_name
        ?? store?.name
        ?? "—";
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

function dateTimeValue(value) {
    if (!value) {
        return "—";
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return date.toLocaleString("en-IN", {
        day: "2-digit",
        month: "short",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    });
}

Show.layout = (page) => (
    <BusinessLayout title="Stock Adjustment Details">
        {page}
    </BusinessLayout>
);
