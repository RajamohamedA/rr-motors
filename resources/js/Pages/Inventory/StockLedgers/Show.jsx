import React, { useEffect, useState } from "react";

import { Head, Link } from "@inertiajs/react";

import BusinessLayout from "@/Layouts/BusinessLayout";
import MasterNavigation from "../../../Components/Business/MasterNavigation";

import "../../../../css/business/stock-ledger.css";

export default function Show({ ledger, openingBalance = 0 }) {
    const [showProduct, setShowProduct] = useState(false);
    const product = ledger?.product ?? {};
    const closingBalance = numberValue(ledger?.balance_qty);

    useEffect(() => {
        const closeOnEscape = (event) => {
            if (event.key === "Escape") setShowProduct(false);
        };

        window.addEventListener("keydown", closeOnEscape);
        document.body.classList.toggle("rr-ledger-modal-open", showProduct);

        return () => {
            window.removeEventListener("keydown", closeOnEscape);
            document.body.classList.remove("rr-ledger-modal-open");
        };
    }, [showProduct]);

    return (
        <>
            <Head title={`Stock Transaction #${ledger.id}`} />

            <div className="rr-ledger-page rr-ledger-show-page">
                <MasterNavigation
                    active="stock-ledgers"
                    title="Inventory"
                    subtitle="Stock transaction audit details"
                />

                <header className="rr-ledger-show-header">
                    <div className="rr-ledger-show-heading">
                        <div className={`rr-ledger-show-icon ${movementClass(ledger)}`}>
                            <i className={`bi ${movementIcon(ledger)}`}></i>
                        </div>

                        <div>
                            <span>Transaction #{ledger.id}</span>
                            <h1>{transactionLabel(ledger.transaction_type)}</h1>
                            <small>
                                {dateFormat(ledger.transaction_date)} · {ledger.location_name}
                            </small>
                        </div>
                    </div>

                    <div className="rr-ledger-show-actions">
                        <Link href="/stock-ledgers" className="back">
                            <i className="bi bi-arrow-left"></i>
                            Back to Ledger
                        </Link>

                        <button
                            type="button"
                            className="product"
                            onClick={() => setShowProduct(true)}
                        >
                            <i className="bi bi-box-seam"></i>
                            Product Details
                        </button>
                    </div>
                </header>

                <section className="rr-ledger-balance-flow">
                    <BalanceCard
                        label="Opening Balance"
                        value={openingBalance}
                        icon="bi-box-arrow-in-right"
                        tone="opening"
                    />

                    <div className="rr-ledger-flow-symbol">+</div>

                    <BalanceCard
                        label="Quantity In"
                        value={ledger.qty_in}
                        icon="bi-arrow-down-circle"
                        tone="inward"
                    />

                    <div className="rr-ledger-flow-symbol">−</div>

                    <BalanceCard
                        label="Quantity Out"
                        value={ledger.qty_out}
                        icon="bi-arrow-up-circle"
                        tone="outward"
                    />

                    <div className="rr-ledger-flow-symbol">=</div>

                    <BalanceCard
                        label="Closing Balance"
                        value={closingBalance}
                        icon="bi-boxes"
                        tone="closing"
                        featured
                    />
                </section>

                <div className="rr-ledger-show-grid">
                    <section className="rr-ledger-detail-window">
                        <WindowTitle icon="bi-receipt" title="Transaction Information" />

                        <div className="rr-ledger-detail-grid">
                            <Detail
                                label="Transaction Date"
                                value={dateFormat(ledger.transaction_date)}
                                icon="bi-calendar3"
                            />
                            <Detail
                                label="Transaction Type"
                                value={transactionLabel(ledger.transaction_type)}
                                icon="bi-arrow-left-right"
                            />
                            <Detail
                                label="Location"
                                value={ledger.location_name}
                                subvalue={
                                    ledger.godown?.godown_code ||
                                    (ledger.location_type === "shop" ? "Shop floor" : "Godown")
                                }
                                icon={ledger.location_type === "shop" ? "bi-shop" : "bi-building"}
                            />
                            <Detail
                                label="Reference Number"
                                value={ledger.reference_no || "Not available"}
                                subvalue={ledger.reference_id ? `Reference ID: ${ledger.reference_id}` : null}
                                icon="bi-hash"
                            />
                            <Detail
                                label="Unit Cost"
                                value={money(ledger.unit_cost)}
                                icon="bi-tag"
                            />
                            <Detail
                                label="Transaction Value"
                                value={money(ledger.total_cost)}
                                icon="bi-currency-rupee"
                                emphasis
                            />
                            <Detail
                                label="Recorded At"
                                value={dateTimeFormat(ledger.created_at)}
                                icon="bi-clock-history"
                            />
                            <Detail
                                label="Ledger ID"
                                value={`#${ledger.id}`}
                                icon="bi-fingerprint"
                            />
                        </div>
                    </section>

                    <button
                        type="button"
                        className="rr-ledger-product-card"
                        onClick={() => setShowProduct(true)}
                    >
                        <div className="rr-ledger-product-card-head">
                            <div className="rr-ledger-product-avatar">
                                <i className="bi bi-box-seam"></i>
                            </div>

                            <div>
                                <span>Product</span>
                                <h2>{product.product_name || "Product"}</h2>
                                <small>
                                    {product.product_code || "No product code"}
                                </small>
                            </div>

                            <i className="bi bi-chevron-right"></i>
                        </div>

                        <div className="rr-ledger-product-card-body">
                            <MiniDetail label="Part Number" value={product.part_number} />
                            <MiniDetail label="Category" value={product.category?.name} />
                            <MiniDetail label="Brand" value={product.brand?.brand_name} />
                            <MiniDetail label="Rack" value={product.rack_location} />
                        </div>

                        <div className="rr-ledger-product-card-footer">
                            Click to view complete product details
                            <i className="bi bi-arrows-angle-expand"></i>
                        </div>
                    </button>
                </div>

                <section className="rr-ledger-detail-window rr-ledger-remarks-window">
                    <WindowTitle icon="bi-chat-left-text" title="Remarks & Audit Note" />

                    <div className="rr-ledger-remarks">
                        <i className="bi bi-info-circle"></i>
                        <p>{ledger.remarks || "No remarks were recorded for this transaction."}</p>
                    </div>
                </section>

                <div className="rr-ledger-audit-strip">
                    <i className="bi bi-shield-check"></i>
                    <div>
                        <strong>Stock audit record</strong>
                        <span>
                            This entry forms part of the permanent stock movement history.
                        </span>
                    </div>
                </div>
            </div>

            {showProduct && (
                <ProductModal
                    product={product}
                    ledger={ledger}
                    onClose={() => setShowProduct(false)}
                />
            )}
        </>
    );
}

function BalanceCard({ label, value, icon, tone, featured = false }) {
    return (
        <div className={`rr-ledger-balance-card ${tone} ${featured ? "featured" : ""}`}>
            <div>
                <i className={`bi ${icon}`}></i>
            </div>
            <span>{label}</span>
            <strong>{quantity(value)}</strong>
        </div>
    );
}

function WindowTitle({ icon, title }) {
    return (
        <div className="rr-ledger-detail-title">
            <i className={`bi ${icon}`}></i>
            <strong>{title}</strong>
        </div>
    );
}

function Detail({ label, value, subvalue, icon, emphasis = false }) {
    return (
        <div className={`rr-ledger-detail ${emphasis ? "emphasis" : ""}`}>
            <div className="rr-ledger-detail-icon">
                <i className={`bi ${icon}`}></i>
            </div>
            <div>
                <span>{label}</span>
                <strong>{value || "—"}</strong>
                {subvalue && <small>{subvalue}</small>}
            </div>
        </div>
    );
}

function MiniDetail({ label, value }) {
    return (
        <div>
            <span>{label}</span>
            <strong>{value || "—"}</strong>
        </div>
    );
}

function ProductModal({ product, ledger, onClose }) {
    return (
        <div
            className="rr-ledger-modal-backdrop"
            role="presentation"
            onMouseDown={(event) => {
                if (event.target === event.currentTarget) onClose();
            }}
        >
            <section
                className="rr-ledger-product-modal"
                role="dialog"
                aria-modal="true"
                aria-labelledby="rr-ledger-product-title"
            >
                <header>
                    <div className="rr-ledger-modal-product-icon">
                        <i className="bi bi-box-seam"></i>
                    </div>
                    <div>
                        <span>Product Master</span>
                        <h2 id="rr-ledger-product-title">
                            {product.product_name || "Product Details"}
                        </h2>
                        <small>{product.product_code || "No product code"}</small>
                    </div>
                    <button type="button" title="Close" onClick={onClose}>
                        <i className="bi bi-x-lg"></i>
                    </button>
                </header>

                <div className="rr-ledger-modal-location">
                    <div>
                        <span>Transaction Location</span>
                        <strong>{ledger.location_name}</strong>
                    </div>
                    <div>
                        <span>Balance After Transaction</span>
                        <strong>{quantity(ledger.balance_qty)}</strong>
                    </div>
                </div>

                <div className="rr-ledger-product-details-grid">
                    <ProductDetail label="Product Code" value={product.product_code} icon="bi-upc" />
                    <ProductDetail label="Barcode" value={product.barcode} icon="bi-upc-scan" />
                    <ProductDetail label="Part Number" value={product.part_number} icon="bi-gear" />
                    <ProductDetail label="Manufacturer" value={product.manufacturer} icon="bi-building-gear" />
                    <ProductDetail label="Category" value={product.category?.name} icon="bi-grid" />
                    <ProductDetail label="Brand" value={product.brand?.brand_name} icon="bi-award" />
                    <ProductDetail label="Unit" value={product.unit?.unit_name} icon="bi-rulers" />
                    <ProductDetail label="Rack Location" value={product.rack_location} icon="bi-bookshelf" />
                    <ProductDetail label="HSN Code" value={product.hsn_code} icon="bi-file-earmark-text" />
                    <ProductDetail label="GST" value={percent(product.gst_percent)} icon="bi-percent" />
                    <ProductDetail label="Purchase Price" value={money(product.purchase_price)} icon="bi-cart-check" />
                    <ProductDetail label="Selling Price" value={money(product.selling_price)} icon="bi-cash-coin" />
                    <ProductDetail label="MRP" value={money(product.mrp)} icon="bi-tag" />
                    <ProductDetail label="Minimum Stock" value={quantity(product.minimum_stock)} icon="bi-exclamation-triangle" />
                </div>

                {product.description && (
                    <div className="rr-ledger-product-description">
                        <span>Description</span>
                        <p>{product.description}</p>
                    </div>
                )}

                <footer>
                    <button type="button" className="close" onClick={onClose}>
                        Close
                    </button>
                    <Link href={`/products/${product.id}`} className="open-product">
                        <i className="bi bi-box-arrow-up-right"></i>
                        Open Full Product
                    </Link>
                </footer>
            </section>
        </div>
    );
}

function ProductDetail({ label, value, icon }) {
    return (
        <div className="rr-ledger-product-detail">
            <i className={`bi ${icon}`}></i>
            <div>
                <span>{label}</span>
                <strong>{value || value === 0 ? value : "—"}</strong>
            </div>
        </div>
    );
}

function movementClass(ledger) {
    if (numberValue(ledger?.qty_in) > 0 && numberValue(ledger?.qty_out) > 0) {
        return "adjustment";
    }
    return numberValue(ledger?.qty_in) > 0 ? "inward" : "outward";
}

function movementIcon(ledger) {
    if (movementClass(ledger) === "adjustment") return "bi-arrow-left-right";
    return movementClass(ledger) === "inward"
        ? "bi-arrow-down-left"
        : "bi-arrow-up-right";
}

function transactionLabel(value) {
    if (!value) return "Stock Transaction";
    return String(value)
        .replaceAll("_", " ")
        .toLowerCase()
        .replace(/\b\w/g, (character) => character.toUpperCase());
}

function dateFormat(value) {
    if (!value) return "—";
    return new Intl.DateTimeFormat("en-IN", {
        day: "2-digit",
        month: "long",
        year: "numeric",
    }).format(new Date(`${String(value).slice(0, 10)}T00:00:00`));
}

function dateTimeFormat(value) {
    if (!value) return "—";
    return new Intl.DateTimeFormat("en-IN", {
        day: "2-digit",
        month: "short",
        year: "numeric",
        hour: "2-digit",
        minute: "2-digit",
    }).format(new Date(value));
}

function money(value) {
    return new Intl.NumberFormat("en-IN", {
        style: "currency",
        currency: "INR",
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(numberValue(value));
}

function quantity(value) {
    return numberValue(value).toLocaleString("en-IN", {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    });
}

function percent(value) {
    return `${quantity(value)}%`;
}

function numberValue(value) {
    const parsed = Number(value ?? 0);
    return Number.isFinite(parsed) ? parsed : 0;
}

Show.layout = (page) => <BusinessLayout>{page}</BusinessLayout>;
