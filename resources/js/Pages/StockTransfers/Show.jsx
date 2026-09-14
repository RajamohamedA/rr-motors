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

import "../../../css/business/stock-transfer.css";

export default function Show({
    transfer,
}) {
    const [showCancel, setShowCancel] =
        useState(false);

    const [cancelling, setCancelling] =
        useState(false);

    const completed =
        Number(transfer?.status) === 1;

    const items =
        Array.isArray(transfer?.items)
            ? transfer.items
            : [];

    const totals = useMemo(
        () =>
            items.reduce(
                (result, item) => {
                    const quantity =
                        numberValue(
                            item.quantity,
                        );

                    const cost =
                        numberValue(
                            item.unit_cost,
                        );

                    result.quantity +=
                        quantity;

                    result.value +=
                        quantity * cost;

                    return result;
                },
                {
                    quantity: 0,
                    value: 0,
                },
            ),
        [items],
    );

    const cancelTransfer = () => {
        setCancelling(true);

        router.patch(
            `/stock-transfers/${transfer.id}/cancel`,
            {},
            {
                preserveScroll: true,

                onFinish: () => {
                    setCancelling(false);
                    setShowCancel(false);
                },
            },
        );
    };

    return (
        <>
            <Head
                title={`Stock Transfer ${transfer.transfer_no}`}
            />

            <div className="rr-transfer-page rr-transfer-show-page">
                <MasterNavigation
                    active="stock-transfers"
                    title="Inventory"
                    subtitle="Stock transfer details"
                />

                <div className="rr-transfer-index-ribbon">
                    <div>
                        <i className="bi bi-grid-1x2"></i>

                        <span>Inventory</span>

                        <i className="bi bi-chevron-right"></i>

                        <Link href="/stock-transfers">
                            Stock Transfers
                        </Link>

                        <i className="bi bi-chevron-right"></i>

                        <strong>
                            {transfer.transfer_no}
                        </strong>
                    </div>

                    <StatusBadge
                        completed={completed}
                    />
                </div>

                <header className="rr-transfer-header">
                    <div className="rr-transfer-heading">
                        <div className="rr-transfer-heading-icon">
                            <i className="bi bi-arrow-left-right"></i>
                        </div>

                        <div>
                            <span>Transfer Document</span>

                            <h1>
                                {transfer.transfer_no}
                            </h1>

                            <small>
                                Created on {dateTimeValue(
                                    transfer.created_at,
                                )}
                            </small>
                        </div>
                    </div>

                    <div className="rr-transfer-show-actions">
                        <Link
                            href="/stock-transfers"
                            className="rr-transfer-secondary-button"
                        >
                            <i className="bi bi-arrow-left"></i>
                            Back
                        </Link>

                        <a
                            href={`/stock-transfers/${transfer.id}/print`}
                            target="_blank"
                            rel="noreferrer"
                            className="rr-transfer-show-button print"
                        >
                            <i className="bi bi-printer"></i>
                            Print
                        </a>

                        <a
                            href={`/stock-transfers/${transfer.id}/pdf`}
                            className="rr-transfer-show-button pdf"
                        >
                            <i className="bi bi-file-earmark-pdf"></i>
                            PDF
                        </a>

                        {completed && (
                            <button
                                type="button"
                                className="rr-transfer-show-button cancel"
                                onClick={() =>
                                    setShowCancel(true)
                                }
                            >
                                <i className="bi bi-x-octagon"></i>
                                Cancel Transfer
                            </button>
                        )}
                    </div>
                </header>

                {!completed && (
                    <div className="rr-transfer-cancelled-notice">
                        <i className="bi bi-exclamation-octagon-fill"></i>

                        <div>
                            <strong>
                                This transfer is cancelled
                            </strong>

                            <span>
                                Reversing stock-ledger entries have been recorded. This document remains available for audit.
                            </span>
                        </div>
                    </div>
                )}

                <section className="rr-transfer-show-summary">
                    <SummaryCard
                        icon="bi-calendar3"
                        label="Transfer Date"
                        value={dateValue(
                            transfer.transfer_date,
                        )}
                        tone="blue"
                    />

                    <SummaryCard
                        icon="bi-box-seam"
                        label="Product Lines"
                        value={integer(
                            items.length,
                        )}
                        tone="violet"
                    />

                    <SummaryCard
                        icon="bi-box-arrow-right"
                        label="Total Quantity"
                        value={quantityValue(
                            totals.quantity,
                        )}
                        tone="green"
                    />

                    <SummaryCard
                        icon="bi-currency-rupee"
                        label="Stock Value"
                        value={currency(
                            totals.value,
                        )}
                        tone="amber"
                    />
                </section>

                <section className="rr-transfer-window rr-transfer-show-document">
                    <div className="rr-transfer-window-title">
                        <div>
                            <i className="bi bi-file-earmark-text"></i>

                            <strong>
                                Document Information
                            </strong>
                        </div>

                        <StatusBadge
                            completed={completed}
                            compact
                        />
                    </div>

                    <div className="rr-transfer-show-details">
                        <Detail
                            label="Transfer Number"
                            value={transfer.transfer_no}
                        />

                        <Detail
                            label="Transfer Date"
                            value={dateValue(
                                transfer.transfer_date,
                            )}
                        />

                        <Detail
                            label="Company"
                            value={
                                transfer.company
                                    ?.company_name ||
                                "—"
                            }
                        />

                        <Detail
                            label="Branch"
                            value={branchName(
                                transfer.store,
                            )}
                        />

                        <Detail
                            label="Created By"
                            value={
                                transfer.creator?.name ||
                                "—"
                            }
                        />

                        <Detail
                            label="Created At"
                            value={dateTimeValue(
                                transfer.created_at,
                            )}
                        />
                    </div>
                </section>

                <section className="rr-transfer-window rr-transfer-show-items">
                    <div className="rr-transfer-window-title">
                        <div>
                            <i className="bi bi-table"></i>

                            <strong>
                                Transfer Items
                            </strong>
                        </div>

                        <span className="rr-transfer-record-count">
                            {items.length} items
                        </span>
                    </div>

                    <div className="rr-transfer-table-scroll">
                        <table className="rr-transfer-table rr-transfer-show-table">
                            <thead>
                                <tr>
                                    <th className="serial">#</th>
                                    <th>Product</th>
                                    <th>From Location</th>
                                    <th>To Location</th>
                                    <th>Unit</th>
                                    <th className="number">From Before</th>
                                    <th className="number">From After</th>
                                    <th className="number">To Before</th>
                                    <th className="number">To After</th>
                                    <th className="number">Quantity</th>
                                    <th className="number">Unit Cost</th>
                                    <th className="number">Total</th>
                                </tr>
                            </thead>

                            <tbody>
                                {items.length > 0 ? (
                                    items.map(
                                        (item, index) => (
                                            <tr key={item.id}>
                                                <td className="serial">
                                                    {index + 1}
                                                </td>

                                                <td>
                                                    <strong className="rr-transfer-product-name">
                                                        {item.product
                                                            ?.product_name ||
                                                            "Product unavailable"}
                                                    </strong>

                                                    <small>
                                                        {productReference(
                                                            item.product,
                                                        )}
                                                    </small>
                                                </td>

                                                <td>
                                                    <LocationBadge
                                                        type="source"
                                                        godown={
                                                            item.from_godown
                                                        }
                                                    />
                                                </td>

                                                <td>
                                                    <LocationBadge
                                                        type="destination"
                                                        godown={
                                                            item.to_godown
                                                        }
                                                    />
                                                </td>

                                                <td>
                                                    {item.product?.unit
                                                        ?.short_name ||
                                                        item.product?.unit
                                                            ?.unit_name ||
                                                        "—"}
                                                </td>

                                                <StockCell
                                                    value={item.from_stock_before}
                                                />

                                                <StockCell
                                                    value={item.from_stock_after}
                                                />

                                                <StockCell
                                                    value={item.to_stock_before}
                                                />

                                                <StockCell
                                                    value={item.to_stock_after}
                                                />

                                                <td className="number strong">
                                                    {quantityValue(
                                                        item.quantity,
                                                    )}
                                                </td>

                                                <td className="number money">
                                                    {currency(
                                                        item.unit_cost,
                                                    )}
                                                </td>

                                                <td className="number money strong">
                                                    {currency(
                                                        item.total_cost ??
                                                        numberValue(
                                                            item.quantity,
                                                        ) *
                                                        numberValue(
                                                            item.unit_cost,
                                                        ),
                                                    )}
                                                </td>
                                            </tr>
                                        ),
                                    )
                                ) : (
                                    <tr>
                                        <td
                                            colSpan="12"
                                            className="rr-transfer-empty rr-transfer-entry-empty"
                                        >
                                            <i className="bi bi-box-seam"></i>
                                            <strong>No transfer items found</strong>
                                        </td>
                                    </tr>
                                )}
                            </tbody>

                            <tfoot>
                                <tr>
                                    <td
                                        colSpan="9"
                                        className="number"
                                    >
                                        Grand Total
                                    </td>

                                    <td className="number strong">
                                        {quantityValue(
                                            totals.quantity,
                                        )}
                                    </td>

                                    <td></td>

                                    <td className="number money strong">
                                        {currency(
                                            totals.value,
                                        )}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </section>

                <section className="rr-transfer-window rr-transfer-show-remarks">
                    <div className="rr-transfer-window-title">
                        <div>
                            <i className="bi bi-chat-left-text"></i>
                            <strong>Remarks</strong>
                        </div>
                    </div>

                    <div>
                        {transfer.remarks ||
                            "No remarks were entered for this transfer."}
                    </div>
                </section>

                <footer className="rr-transfer-show-footer">
                    <span>
                        <i className="bi bi-shield-check"></i>
                        Company and branch isolated inventory document
                    </span>

                    <Link
                        href="/stock-transfers"
                        className="rr-transfer-secondary-button"
                    >
                        <i className="bi bi-arrow-left"></i>
                        Return to Register
                    </Link>
                </footer>

                <ConfirmModal
                    show={showCancel}
                    title="Cancel Stock Transfer"
                    message={`Cancel transfer ${transfer.transfer_no}? Reversing stock-ledger entries will be created.`}
                    confirmText="Cancel Transfer"
                    cancelText="Keep Transfer"
                    icon="bi-x-octagon"
                    variant="danger"
                    processing={cancelling}
                    onConfirm={cancelTransfer}
                    onCancel={() =>
                        setShowCancel(false)
                    }
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
}) {
    return (
        <article className={`rr-transfer-show-card ${tone}`}>
            <div>
                <i className={`bi ${icon}`}></i>
            </div>

            <span>
                {label}
                <strong>{value}</strong>
            </span>
        </article>
    );
}

function Detail({ label, value }) {
    return (
        <div className="rr-transfer-show-detail">
            <span>{label}</span>
            <strong>{value || "—"}</strong>
        </div>
    );
}

function StatusBadge({
    completed,
    compact = false,
}) {
    return (
        <span
            className={`rr-transfer-show-status ${completed
                    ? "completed"
                    : "cancelled"
                } ${compact ? "compact" : ""}`}
        >
            <i
                className={`bi ${completed
                        ? "bi-check-circle-fill"
                        : "bi-x-circle-fill"
                    }`}
            ></i>

            {completed
                ? "Completed"
                : "Cancelled"}
        </span>
    );
}

function LocationBadge({ type, godown }) {
    const shop = !godown;

    return (
        <span className={`rr-transfer-location ${type}`}>
            <i
                className={`bi ${shop
                        ? "bi-shop"
                        : "bi-building"
                    }`}
            ></i>

            {shop
                ? "Shop"
                : godown.godown_name}
        </span>
    );
}

function StockCell({ value }) {
    return (
        <td className="number rr-transfer-stock-snapshot">
            {value === null ||
                value === undefined
                ? "—"
                : quantityValue(value)}
        </td>
    );
}

function branchName(store) {
    if (!store) {
        return "—";
    }

    return store.store_code
        ? `${store.store_name} [${store.store_code}]`
        : store.store_name;
}

function productReference(product) {
    if (!product) {
        return "Reference unavailable";
    }

    const references = [
        product.product_code,
        product.part_number,
    ].filter(Boolean);

    return references.length > 0
        ? references.join(" • ")
        : "No code";
}

function numberValue(value) {
    const parsed = Number(value);

    return Number.isFinite(parsed)
        ? parsed
        : 0;
}

function integer(value) {
    return Math.trunc(
        numberValue(value),
    ).toLocaleString("en-IN");
}

function quantityValue(value) {
    return numberValue(value).toLocaleString(
        "en-IN",
        {
            minimumFractionDigits: 0,
            maximumFractionDigits: 3,
        },
    );
}

function currency(value) {
    return `₹${numberValue(value).toLocaleString(
        "en-IN",
        {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        },
    )}`;
}

function dateValue(value) {
    if (!value) {
        return "—";
    }

    const parts = String(value)
        .slice(0, 10)
        .split("-");

    if (parts.length !== 3) {
        return value;
    }

    return `${parts[2]}-${parts[1]}-${parts[0]}`;
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
    <BusinessLayout title="Stock Transfer Details">
        {page}
    </BusinessLayout>
);
