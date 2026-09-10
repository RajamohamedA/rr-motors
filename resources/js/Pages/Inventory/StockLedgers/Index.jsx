import React, { useEffect, useMemo, useRef, useState } from "react";

import axios from "axios";
import { Head, Link, router } from "@inertiajs/react";

import BusinessLayout from "@/Layouts/BusinessLayout";
import MasterNavigation from "../../../Components/Business/MasterNavigation";

import "../../../../css/business/stock-ledger.css";

export default function Index({
    ledgers = { data: [], links: [] },
    filters = {},
    summary = {},
    godowns = [],
    transactionTypes = [],
    selectedProduct = null,
}) {
    const records = Array.isArray(ledgers.data) ? ledgers.data : [];
    const [form, setForm] = useState({
        search: filters.search ?? "",
        product_id: filters.product_id ? String(filters.product_id) : "",
        location: filters.location ?? "all",
        godown_id: filters.godown_id ? String(filters.godown_id) : "",
        transaction_type: filters.transaction_type ?? "",
        from_date: filters.from_date ?? "",
        to_date: filters.to_date ?? "",
        per_page: String(filters.per_page ?? 50),
    });
    const [productText, setProductText] = useState(
        selectedProduct
            ? productDisplay(selectedProduct)
            : "",
    );
    const [productResults, setProductResults] = useState([]);
    const [productLoading, setProductLoading] = useState(false);
    const [productOpen, setProductOpen] = useState(false);
    const productRequest = useRef(null);

    const queryString = useMemo(
        () => new URLSearchParams(cleanFilters(form)).toString(),
        [form],
    );

    useEffect(() => {
        if (form.product_id || productText.trim().length < 2) {
            setProductResults([]);
            return undefined;
        }

        const timer = window.setTimeout(async () => {
            productRequest.current?.abort();
            productRequest.current = new AbortController();
            setProductLoading(true);

            try {
                const response = await axios.get("/stock-ledgers/search", {
                    params: { search: productText.trim() },
                    signal: productRequest.current.signal,
                    headers: { Accept: "application/json" },
                });

                setProductResults(response.data?.products ?? []);
                setProductOpen(true);
            } catch (error) {
                if (error.code !== "ERR_CANCELED") {
                    setProductResults([]);
                }
            } finally {
                setProductLoading(false);
            }
        }, 300);

        return () => window.clearTimeout(timer);
    }, [productText, form.product_id]);

    const update = (field, value) => {
        const next = { ...form, [field]: value };

        if (field === "location" && value !== "godown") {
            next.godown_id = "";
        }

        setForm(next);
    };

    const applyFilters = (next = form) => {
        router.get("/stock-ledgers", cleanFilters(next), {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    };

    const selectLocation = (location) => {
        const next = {
            ...form,
            location,
            godown_id: location === "godown" ? form.godown_id : "",
        };

        setForm(next);
        applyFilters(next);
    };

    const selectProduct = (product) => {
        setForm((current) => ({
            ...current,
            product_id: String(product.id),
        }));
        setProductText(productDisplay(product));
        setProductResults([]);
        setProductOpen(false);
    };

    const clearProduct = () => {
        setForm((current) => ({ ...current, product_id: "" }));
        setProductText("");
        setProductResults([]);
        setProductOpen(false);
    };

    const reset = () => {
        const next = {
            search: "",
            product_id: "",
            location: "all",
            godown_id: "",
            transaction_type: "",
            from_date: "",
            to_date: "",
            per_page: "50",
        };

        setForm(next);
        setProductText("");
        setProductResults([]);
        applyFilters(next);
    };

    return (
        <>
            <Head title="Stock Ledger" />

            <div className="rr-ledger-page">
                <MasterNavigation
                    active="stock-ledgers"
                    title="Inventory"
                    subtitle="Complete stock transaction history"
                />

                <header className="rr-ledger-header">
                    <div className="rr-ledger-heading">
                        <div className="rr-ledger-heading-icon">
                            <i className="bi bi-journal-text"></i>
                        </div>

                        <div>
                            <span>Inventory Control</span>
                            <h1>Stock Ledger</h1>
                            <small>
                                Track every inward, outward and running balance
                            </small>
                        </div>
                    </div>

                    <div className="rr-ledger-actions">
                        <a
                            href={`/stock-ledgers/print?${queryString}`}
                            target="_blank"
                            rel="noreferrer"
                            className="print"
                        >
                            <i className="bi bi-printer"></i>
                            Print
                        </a>

                        <a
                            href={`/stock-ledgers/pdf?${queryString}`}
                            className="pdf"
                        >
                            <i className="bi bi-file-earmark-pdf"></i>
                            PDF
                        </a>

                        <a
                            href={`/stock-ledgers/excel?${queryString}`}
                            className="excel"
                        >
                            <i className="bi bi-file-earmark-spreadsheet"></i>
                            Excel
                        </a>
                    </div>
                </header>

                <section className="rr-ledger-metrics">
                    <Metric
                        icon="bi-box-arrow-in-right"
                        label="Opening Quantity"
                        value={quantity(summary.opening_quantity)}
                        tone="slate"
                    />
                    <Metric
                        icon="bi-arrow-down-circle"
                        label="Quantity In"
                        value={quantity(summary.quantity_in)}
                        tone="green"
                    />
                    <Metric
                        icon="bi-arrow-up-circle"
                        label="Quantity Out"
                        value={quantity(summary.quantity_out)}
                        tone="red"
                    />
                    <Metric
                        icon="bi-boxes"
                        label="Closing Quantity"
                        value={quantity(summary.closing_quantity)}
                        tone="blue"
                        featured
                    />
                    <Metric
                        icon="bi-currency-rupee"
                        label="Inward Value"
                        value={money(summary.inward_value)}
                        tone="violet"
                    />
                    <Metric
                        icon="bi-list-check"
                        label="Transactions"
                        value={integer(summary.transaction_count)}
                        tone="amber"
                    />
                </section>

                <section className="rr-ledger-window rr-ledger-filter-window">
                    <div className="rr-ledger-window-title">
                        <div>
                            <i className="bi bi-funnel"></i>
                            <strong>Search & Filters</strong>
                        </div>

                        <button type="button" onClick={reset}>
                            <i className="bi bi-arrow-counterclockwise"></i>
                            Reset All
                        </button>
                    </div>

                    <div className="rr-ledger-filter-body">
                        <div className="rr-ledger-location-tabs">
                            {[
                                ["all", "All Locations", "bi-grid"],
                                ["shop", "Shop", "bi-shop"],
                                ["godown", "Godowns", "bi-building"],
                            ].map(([value, label, icon]) => (
                                <button
                                    key={value}
                                    type="button"
                                    className={
                                        form.location === value ? "active" : ""
                                    }
                                    onClick={() => selectLocation(value)}
                                >
                                    <i className={`bi ${icon}`}></i>
                                    {label}
                                </button>
                            ))}
                        </div>

                        <form
                            className="rr-ledger-filter-grid"
                            onSubmit={(event) => {
                                event.preventDefault();
                                applyFilters();
                            }}
                        >
                            <Field label="Product">
                                <div className="rr-ledger-product-picker">
                                    <div className="rr-ledger-input-icon">
                                        <i className="bi bi-box-seam"></i>
                                        <input
                                            type="search"
                                            value={productText}
                                            placeholder="Search product, code or barcode..."
                                            autoComplete="off"
                                            onFocus={() => setProductOpen(true)}
                                            onChange={(event) => {
                                                setProductText(event.target.value);
                                                setForm((current) => ({
                                                    ...current,
                                                    product_id: "",
                                                }));
                                            }}
                                        />

                                        {productLoading && (
                                            <i className="bi bi-arrow-repeat rr-ledger-spin"></i>
                                        )}

                                        {(productText || form.product_id) && !productLoading && (
                                            <button
                                                type="button"
                                                title="Clear selected product"
                                                onClick={clearProduct}
                                            >
                                                <i className="bi bi-x-lg"></i>
                                            </button>
                                        )}
                                    </div>

                                    {productOpen &&
                                        !form.product_id &&
                                        productText.trim().length >= 2 && (
                                            <div className="rr-ledger-product-results">
                                                <div className="rr-ledger-results-head">
                                                    <strong>Product Results</strong>
                                                    <button
                                                        type="button"
                                                        onClick={() => setProductOpen(false)}
                                                    >
                                                        Close
                                                    </button>
                                                </div>

                                                {productResults.length ? (
                                                    productResults.map((product) => (
                                                        <button
                                                            key={product.id}
                                                            type="button"
                                                            className="rr-ledger-product-result"
                                                            onClick={() => selectProduct(product)}
                                                        >
                                                            <span>
                                                                <strong>{product.product_name}</strong>
                                                                <small>
                                                                    {[
                                                                        product.product_code,
                                                                        product.part_number,
                                                                        product.barcode,
                                                                    ]
                                                                        .filter(Boolean)
                                                                        .join(" · ") || "No code"}
                                                                </small>
                                                            </span>
                                                            <i className="bi bi-plus-circle"></i>
                                                        </button>
                                                    ))
                                                ) : (
                                                    <div className="rr-ledger-no-result">
                                                        {productLoading
                                                            ? "Searching products..."
                                                            : "No matching products found"}
                                                    </div>
                                                )}
                                            </div>
                                        )}
                                </div>
                            </Field>

                            <Field label="General Search">
                                <div className="rr-ledger-input-icon">
                                    <i className="bi bi-search"></i>
                                    <input
                                        type="search"
                                        value={form.search}
                                        placeholder="Reference or remarks..."
                                        onChange={(event) =>
                                            update("search", event.target.value)
                                        }
                                    />
                                </div>
                            </Field>

                            {form.location === "godown" && (
                                <Field label="Godown">
                                    <select
                                        value={form.godown_id}
                                        onChange={(event) =>
                                            update("godown_id", event.target.value)
                                        }
                                    >
                                        <option value="">All Godowns</option>
                                        {godowns.map((godown) => (
                                            <option key={godown.id} value={godown.id}>
                                                {godown.godown_name}
                                                {godown.godown_code
                                                    ? ` (${godown.godown_code})`
                                                    : ""}
                                            </option>
                                        ))}
                                    </select>
                                </Field>
                            )}

                            <Field label="Transaction Type">
                                <select
                                    value={form.transaction_type}
                                    onChange={(event) =>
                                        update("transaction_type", event.target.value)
                                    }
                                >
                                    <option value="">All Transactions</option>
                                    {transactionTypes.map((type) => (
                                        <option key={type} value={type}>
                                            {transactionLabel(type)}
                                        </option>
                                    ))}
                                </select>
                            </Field>

                            <Field label="From Date">
                                <input
                                    type="date"
                                    value={form.from_date}
                                    onClick={(event) => event.currentTarget.showPicker?.()}
                                    onChange={(event) =>
                                        update("from_date", event.target.value)
                                    }
                                />
                            </Field>

                            <Field label="To Date">
                                <input
                                    type="date"
                                    value={form.to_date}
                                    min={form.from_date || undefined}
                                    onClick={(event) => event.currentTarget.showPicker?.()}
                                    onChange={(event) =>
                                        update("to_date", event.target.value)
                                    }
                                />
                            </Field>

                            <Field label="Rows">
                                <select
                                    value={form.per_page}
                                    onChange={(event) =>
                                        update("per_page", event.target.value)
                                    }
                                >
                                    {[25, 50, 100, 200].map((size) => (
                                        <option key={size} value={size}>
                                            {size}
                                        </option>
                                    ))}
                                </select>
                            </Field>

                            <button type="submit" className="rr-ledger-apply">
                                <i className="bi bi-search"></i>
                                Apply Filters
                            </button>
                        </form>
                    </div>
                </section>

                <section className="rr-ledger-window">
                    <div className="rr-ledger-window-title">
                        <div>
                            <i className="bi bi-list-columns-reverse"></i>
                            <strong>Transaction Register</strong>
                        </div>

                        <span>
                            {ledgers.total ?? records.length} transaction(s) · Click row
                            to view details
                        </span>
                    </div>

                    <div className="rr-ledger-table-wrap">
                        <table className="rr-ledger-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Date</th>
                                    <th>Product</th>
                                    <th>Location</th>
                                    <th>Transaction</th>
                                    <th>Reference</th>
                                    <th className="number">Qty In</th>
                                    <th className="number">Qty Out</th>
                                    <th className="number">Balance</th>
                                    <th className="number">Unit Cost</th>
                                    <th className="number">Total Cost</th>
                                    <th>Remarks</th>
                                    <th className="action">Action</th>
                                </tr>
                            </thead>

                            <tbody>
                                {records.length ? (
                                    records.map((ledger, index) => (
                                        <tr
                                            key={ledger.id}
                                            className={movementClass(ledger)}
                                            tabIndex="0"
                                            onClick={(event) => {
                                                if (event.target.closest("a, button")) return;
                                                router.visit(`/stock-ledgers/${ledger.id}`);
                                            }}
                                            onKeyDown={(event) => {
                                                if (event.key === "Enter") {
                                                    router.visit(`/stock-ledgers/${ledger.id}`);
                                                }
                                            }}
                                        >
                                            <td>{(ledgers.from ?? 1) + index}</td>
                                            <td>
                                                <strong>{dateFormat(ledger.transaction_date)}</strong>
                                                <span>{timeFormat(ledger.created_at)}</span>
                                            </td>
                                            <td className="product">
                                                <strong>
                                                    {ledger.product?.product_name ?? "Product"}
                                                </strong>
                                                <span>
                                                    {[
                                                        ledger.product?.product_code,
                                                        ledger.product?.part_number,
                                                    ]
                                                        .filter(Boolean)
                                                        .join(" · ") || "No product code"}
                                                </span>
                                            </td>
                                            <td>
                                                <LocationBadge ledger={ledger} />
                                                {ledger.godown?.godown_code && (
                                                    <span>{ledger.godown.godown_code}</span>
                                                )}
                                            </td>
                                            <td>
                                                <TransactionBadge ledger={ledger} />
                                            </td>
                                            <td>
                                                <strong>{ledger.reference_no || "—"}</strong>
                                            </td>
                                            <td className="number quantity-in">
                                                {numberValue(ledger.qty_in) > 0
                                                    ? quantity(ledger.qty_in)
                                                    : "—"}
                                            </td>
                                            <td className="number quantity-out">
                                                {numberValue(ledger.qty_out) > 0
                                                    ? quantity(ledger.qty_out)
                                                    : "—"}
                                            </td>
                                            <td className="number balance">
                                                {quantity(ledger.balance_qty)}
                                            </td>
                                            <td className="number">
                                                {money(ledger.unit_cost)}
                                            </td>
                                            <td className="number value">
                                                {money(ledger.total_cost)}
                                            </td>
                                            <td className="remarks">
                                                {ledger.remarks || "—"}
                                            </td>
                                            <td className="action">
                                                <Link
                                                    href={`/stock-ledgers/${ledger.id}`}
                                                    title="View transaction"
                                                >
                                                    <i className="bi bi-eye"></i>
                                                </Link>
                                            </td>
                                        </tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td colSpan="13" className="rr-ledger-empty">
                                            <i className="bi bi-journal-x"></i>
                                            <strong>No ledger transactions found</strong>
                                            <span>
                                                Change the filters or verify stock activity.
                                            </span>
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>

                    <Pagination
                        links={ledgers.links ?? []}
                        from={ledgers.from}
                        to={ledgers.to}
                        total={ledgers.total}
                    />
                </section>
            </div>
        </>
    );
}

function Metric({ icon, label, value, tone, featured = false }) {
    return (
        <div className={`rr-ledger-metric ${tone} ${featured ? "featured" : ""}`}>
            <div className="rr-ledger-metric-icon">
                <i className={`bi ${icon}`}></i>
            </div>
            <div className="rr-ledger-metric-content">
                <span>{label}</span>
                <strong>{value}</strong>
            </div>
        </div>
    );
}

function Field({ label, children }) {
    return (
        <label className="rr-ledger-field">
            <span>{label}</span>
            {children}
        </label>
    );
}

function LocationBadge({ ledger }) {
    return (
        <span className={`rr-ledger-location ${ledger.location_type}`}>
            <i
                className={`bi ${ledger.location_type === "shop" ? "bi-shop" : "bi-building"
                    }`}
            ></i>
            {ledger.location_name}
        </span>
    );
}

function TransactionBadge({ ledger }) {
    const inward = numberValue(ledger.qty_in) > 0;
    const outward = numberValue(ledger.qty_out) > 0;
    const tone = inward && outward ? "adjustment" : inward ? "inward" : "outward";

    return (
        <span className={`rr-ledger-transaction ${tone}`}>
            <i
                className={`bi ${inward && !outward
                        ? "bi-arrow-down-left"
                        : outward && !inward
                            ? "bi-arrow-up-right"
                            : "bi-arrow-left-right"
                    }`}
            ></i>
            {transactionLabel(ledger.transaction_type)}
        </span>
    );
}

function Pagination({ links, from, to, total }) {
    const hasPages = Array.isArray(links) && links.length > 3;

    if (!total) return null;

    return (
        <div className="rr-ledger-pagination-bar">
            <div className="rr-ledger-pagination-info">
                Showing <strong>{integer(from)}</strong> to{" "}
                <strong>{integer(to)}</strong> of{" "}
                <strong>{integer(total)}</strong> transactions
            </div>

            {hasPages && (
                <nav className="rr-ledger-pagination">
                    {links.map((link, index) => (
                        <Link
                            key={`${link.label}-${index}`}
                            href={link.url ?? "#"}
                            preserveScroll
                            preserveState
                            className={`${link.active ? "active" : ""} ${!link.url ? "disabled" : ""
                                }`}
                        >
                            <span
                                dangerouslySetInnerHTML={{ __html: link.label }}
                            />
                        </Link>
                    ))}
                </nav>
            )}
        </div>
    );
}

function cleanFilters(values) {
    return Object.fromEntries(
        Object.entries(values).filter(([, value]) => value !== "" && value != null),
    );
}

function productDisplay(product) {
    const code = product.product_code ? ` (${product.product_code})` : "";
    return `${product.product_name ?? "Product"}${code}`;
}

function movementClass(ledger) {
    if (numberValue(ledger.qty_in) > 0 && numberValue(ledger.qty_out) > 0) {
        return "adjustment";
    }
    return numberValue(ledger.qty_in) > 0 ? "inward" : "outward";
}

function transactionLabel(value) {
    if (!value) return "Unknown";
    return String(value)
        .replaceAll("_", " ")
        .toLowerCase()
        .replace(/\b\w/g, (character) => character.toUpperCase());
}

function dateFormat(value) {
    if (!value) return "—";
    return new Intl.DateTimeFormat("en-IN", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
    }).format(new Date(`${String(value).slice(0, 10)}T00:00:00`));
}

function timeFormat(value) {
    if (!value) return "";
    return new Intl.DateTimeFormat("en-IN", {
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

function integer(value) {
    return Math.trunc(numberValue(value)).toLocaleString("en-IN");
}

function numberValue(value) {
    const parsed = Number(value ?? 0);
    return Number.isFinite(parsed) ? parsed : 0;
}

Index.layout = (page) => <BusinessLayout>{page}</BusinessLayout>;
