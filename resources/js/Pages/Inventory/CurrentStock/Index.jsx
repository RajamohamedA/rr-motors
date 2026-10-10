import React, { useMemo, useState } from "react";
import { Head, Link, router } from "@inertiajs/react";
import BusinessLayout from "@/Layouts/BusinessLayout";
import MasterNavigation from "../../../Components/Business/MasterNavigation";
import "../../../../css/business/current-stock.css";
export default function Index({
    stocks = { data: [], links: [] },
    filters = {},
    summary = {},
    categories = [],
    brands = [],
    godowns = [],
}) {
    const records = Array.isArray(stocks.data) ? stocks.data : [];
    const [form, setForm] = useState({
        search: filters.search ?? "",
        location: filters.location ?? "all",
        godown_id: filters.godown_id ? String(filters.godown_id) : "",
        category_id: filters.category_id ? String(filters.category_id) : "",
        brand_id: filters.brand_id ? String(filters.brand_id) : "",
        stock_status: filters.stock_status ?? "all",
        per_page: String(filters.per_page ?? 25),
    });
    const queryString = useMemo(
        () => new URLSearchParams(cleanFilters(form)).toString(),
        [form],
    );
    const applyFilters = (next = form) => {
        router.get("/inventory/current-stock", cleanFilters(next), {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    };
    const update = (field, value, apply = false) => {
        const next = { ...form, [field]: value };
        if (field === "location" && value !== "godown") {
            next.godown_id = "";
        }
        setForm(next);
        if (apply) applyFilters(next);
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
    const reset = () => {
        const next = {
            search: "",
            location: "all",
            godown_id: "",
            category_id: "",
            brand_id: "",
            stock_status: "all",
            per_page: "25",
        };
        setForm(next);
        applyFilters(next);
    };
    const openLedger = (stock) => {
        const params = new URLSearchParams({
            product_id: String(stock.product_id),
        });
        if (stock.godown_id) params.set("godown_id", String(stock.godown_id));
        else params.set("location", "shop");
        router.visit(`/stock-ledgers?${params.toString()}`);
    };
    return (
        <>
            <Head title="Current Stock" />
            <div className="rr-current-stock-page">
                <MasterNavigation
                    active="current-stock"
                    title="Inventory"
                    subtitle="Current Shop and Godown stock"
                />
                <header className="rr-current-stock-header">
                    <div className="rr-current-stock-heading">
                        <div className="rr-current-stock-heading-icon">
                            <i className="bi bi-boxes"></i>
                        </div>
                        <div>
                            <span>Inventory Register</span>
                            <h1>Current Stock</h1>
                            <small>
                                Latest balance for every product and storage location
                            </small>
                        </div>
                    </div>
                    <div className="rr-current-stock-actions">
                        <a
                            href={`/inventory/current-stock/print?${queryString}`}
                            target="_blank"
                            rel="noreferrer"
                            className="print"
                        >
                            <i className="bi bi-printer"></i>
                            Print
                        </a>
                        <a
                            href={`/inventory/current-stock/pdf?${queryString}`}
                            className="pdf"
                        >
                            <i className="bi bi-file-earmark-pdf"></i>
                            PDF
                        </a>
                        <a
                            href={`/inventory/current-stock/excel?${queryString}`}
                            className="excel"
                        >
                            <i className="bi bi-file-earmark-spreadsheet"></i>
                            Excel
                        </a>
                    </div>
                </header>
                <section className="rr-current-stock-metrics">
                    <Metric
                        icon="bi-box-seam"
                        label="Products"
                        value={numberFormat(summary.product_count)}
                        tone="blue"
                    />
                    <Metric
                        icon="bi-stack"
                        label="Total Quantity"
                        value={quantityFormat(summary.total_quantity)}
                        tone="slate"
                    />
                    <Metric
                        icon="bi-currency-rupee"
                        label="Stock Value"
                        value={moneyFormat(summary.stock_value)}
                        tone="violet"
                    />
                    <Metric
                        icon="bi-exclamation-triangle"
                        label="Low Stock"
                        value={numberFormat(summary.low_stock_count)}
                        tone="amber"
                    />
                    <Metric
                        icon="bi-x-circle"
                        label="Out of Stock"
                        value={numberFormat(summary.out_of_stock_count)}
                        tone="red"
                    />
                    <Metric
                        icon="bi-dash-circle"
                        label="Negative Stock"
                        value={numberFormat(summary.negative_stock_count)}
                        tone="dark-red"
                    />
                </section>
                <section className="rr-current-stock-window rr-current-stock-filter-window">
                    <div className="rr-current-stock-window-title">
                        <div>
                            <i className="bi bi-funnel"></i>
                            <strong>Search & Filters</strong>
                        </div>
                        <button type="button" onClick={reset}>
                            <i className="bi bi-arrow-counterclockwise"></i>
                            Reset
                        </button>
                    </div>
                    <div className="rr-current-stock-filter-body">
                        <div className="rr-current-stock-location-tabs">
                            {[
                                ["all", "All Locations", "bi-grid"],
                                ["shop", "Shop", "bi-shop"],
                                ["godown", "Godowns", "bi-building"],
                            ].map(([value, label, icon]) => (
                                <button
                                    key={value}
                                    type="button"
                                    className={form.location === value ? "active" : ""}
                                    onClick={() => selectLocation(value)}
                                >
                                    <i className={`bi ${icon}`}></i>
                                    {label}
                                </button>
                            ))}
                        </div>
                        <form
                            className="rr-current-stock-filter-grid"
                            onSubmit={(event) => {
                                event.preventDefault();
                                applyFilters();
                            }}
                        >
                            <Field label="Search Product">
                                <div className="rr-current-stock-search">
                                    <i className="bi bi-search"></i>
                                    <input
                                        type="search"
                                        value={form.search}
                                        placeholder="Name, code, barcode, part number or rack..."
                                        onChange={(event) => update("search", event.target.value)}
                                    />
                                    {form.search && (
                                        <button
                                            type="button"
                                            title="Clear search"
                                            onClick={() => update("search", "")}
                                        >
                                            <i className="bi bi-x-lg"></i>
                                        </button>
                                    )}
                                </div>
                            </Field>
                            <Field label="Category">
                                <select
                                    value={form.category_id}
                                    onChange={(event) => update("category_id", event.target.value)}
                                >
                                    <option value="">All Categories</option>
                                    {categories.map((category) => (
                                        <option key={category.id} value={category.id}>
                                            {category.name}
                                        </option>
                                    ))}
                                </select>
                            </Field>
                            <Field label="Brand">
                                <select
                                    value={form.brand_id}
                                    onChange={(event) => update("brand_id", event.target.value)}
                                >
                                    <option value="">All Brands</option>
                                    {brands.map((brand) => (
                                        <option key={brand.id} value={brand.id}>
                                            {brand.brand_name}
                                        </option>
                                    ))}
                                </select>
                            </Field>
                            {form.location === "godown" && (
                                <Field label="Godown">
                                    <select
                                        value={form.godown_id}
                                        onChange={(event) => update("godown_id", event.target.value)}
                                    >
                                        <option value="">All Godowns</option>
                                        {godowns.map((godown) => (
                                            <option key={godown.id} value={godown.id}>
                                                {godown.godown_name}
                                                {godown.godown_code ? ` (${godown.godown_code})` : ""}
                                            </option>
                                        ))}
                                    </select>
                                </Field>
                            )}
                            <Field label="Stock Status">
                                <select
                                    value={form.stock_status}
                                    onChange={(event) => update("stock_status", event.target.value)}
                                >
                                    <option value="all">All Stock</option>
                                    <option value="in_stock">In Stock</option>
                                    <option value="low_stock">Low Stock</option>
                                    <option value="out_of_stock">Out of Stock</option>
                                    <option value="negative">Negative Stock</option>
                                </select>
                            </Field>
                            <Field label="Rows">
                                <select
                                    value={form.per_page}
                                    onChange={(event) => update("per_page", event.target.value)}
                                >
                                    {[15, 25, 50, 100].map((size) => (
                                        <option key={size} value={size}>
                                            {size}
                                        </option>
                                    ))}
                                </select>
                            </Field>
                            <button type="submit" className="rr-current-stock-apply">
                                <i className="bi bi-search"></i>
                                Apply
                            </button>
                        </form>
                    </div>
                </section>
                <section className="rr-current-stock-window">
                    <div className="rr-current-stock-window-title">
                        <div>
                            <i className="bi bi-list-ul"></i>
                            <strong>Stock Register</strong>
                        </div>
                        <span>
                            {stocks.total ?? records.length} location record(s) · Click row
                            for ledger
                        </span>
                    </div>
                    <div className="rr-current-stock-table-wrap">
                        <table className="rr-current-stock-table">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Product</th>
                                    <th>Category / Brand</th>
                                    <th>Location</th>
                                    <th>Rack</th>
                                    <th className="number">Current Qty</th>
                                    <th className="number">Minimum</th>
                                    <th className="number">Unit Cost</th>
                                    <th className="number">Stock Value</th>
                                    <th>Status</th>
                                    <th>Last Activity</th>
                                    <th className="action">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                {records.length ? (
                                    records.map((stock, index) => (
                                        <tr
                                            key={stock.ledger_id}
                                            className={stock.stock_status}
                                            tabIndex="0"
                                            onClick={(event) => {
                                                if (event.target.closest("a, button")) return;
                                                openLedger(stock);
                                            }}
                                            onKeyDown={(event) => {
                                                if (event.key === "Enter") openLedger(stock);
                                            }}
                                        >
                                            <td>{(stocks.from ?? 1) + index}</td>
                                            <td className="product">
                                                <strong>{stock.product?.product_name ?? "Product"}</strong>
                                                <span>
                                                    {[stock.product?.product_code, stock.product?.part_number]
                                                        .filter(Boolean)
                                                        .join(" · ") || "No product code"}
                                                </span>
                                            </td>
                                            <td>
                                                <strong>
                                                    {stock.product?.category?.name ?? "—"}
                                                </strong>
                                                <span>{stock.product?.brand?.brand_name ?? "No brand"}</span>
                                            </td>
                                            <td>
                                                <LocationBadge stock={stock} />
                                                {stock.godown?.godown_code && (
                                                    <span>{stock.godown.godown_code}</span>
                                                )}
                                            </td>
                                            <td>{stock.product?.rack_location || "—"}</td>
                                            <td className="number quantity">
                                                {quantityFormat(stock.quantity)}
                                                <span>{stock.product?.unit?.unit_name ?? ""}</span>
                                            </td>
                                            <td className="number">
                                                {quantityFormat(stock.minimum_stock)}
                                            </td>
                                            <td className="number">{moneyFormat(stock.unit_cost)}</td>
                                            <td className="number value">
                                                {moneyFormat(stock.stock_value)}
                                            </td>
                                            <td>
                                                <StockBadge status={stock.stock_status} />
                                            </td>
                                            <td>
                                                <strong>{dateFormat(stock.last_transaction_date)}</strong>
                                                <span>{transactionLabel(stock.last_transaction_type)}</span>
                                            </td>
                                            <td className="action">
                                                <Link
                                                    href={ledgerUrl(stock)}
                                                    title="View stock ledger"
                                                >
                                                    <i className="bi bi-journal-text"></i>
                                                </Link>
                                            </td>
                                        </tr>
                                    ))
                                ) : (
                                    <tr>
                                        <td colSpan="12" className="rr-current-stock-empty">
                                            <i className="bi bi-inbox"></i>
                                            <strong>No current stock records found</strong>
                                            <span>Change the filters or verify stock transactions.</span>
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                    <Pagination links={stocks.links ?? []} />
                </section>
            </div>
        </>
    );
}
function Metric({ icon, label, value, tone }) {
    const featured = ["slate", "violet"].includes(tone);
    return (
        <div
            className={`rr-current-stock-metric ${tone} ${featured ? "featured" : ""
                }`}
        >
            <div>
                <i className={`bi ${icon}`}></i>
            </div>
            <div className="rr-current-stock-metric-content">
                <span>{label}</span>
                <strong>{value}</strong>
            </div>
        </div>
    );
}
function Field({ label, children }) {
    return (
        <label className="rr-current-stock-field">
            <span>{label}</span>
            {children}
        </label>
    );
}
function LocationBadge({ stock }) {
    return (
        <span className={`rr-current-stock-location ${stock.location_type}`}>
            <i className={`bi ${stock.location_type === "shop" ? "bi-shop" : "bi-building"}`}></i>
            {stock.location_name}
        </span>
    );
}
function StockBadge({ status }) {
    return (
        <span className={`rr-current-stock-status ${status}`}>
            <i className={`bi ${statusIcon(status)}`}></i>
            {statusLabel(status)}
        </span>
    );
}
function Pagination({ links }) {
    if (!Array.isArray(links) || links.length <= 3) return null;
    return (
        <nav className="rr-current-stock-pagination">
            {links.map((link, index) => (
                <Link
                    key={`${link.label}-${index}`}
                    href={link.url ?? "#"}
                    preserveScroll
                    preserveState
                    className={`${link.active ? "active" : ""} ${!link.url ? "disabled" : ""}`}
                >
                    <span dangerouslySetInnerHTML={{ __html: link.label }} />
                </Link>
            ))}
        </nav>
    );
}
function cleanFilters(values) {
    return Object.fromEntries(
        Object.entries(values).filter(([, value]) => value !== "" && value != null),
    );
}
function ledgerUrl(stock) {
    const params = new URLSearchParams({ product_id: String(stock.product_id) });
    if (stock.godown_id) params.set("godown_id", String(stock.godown_id));
    else params.set("location", "shop");
    return `/stock-ledgers?${params.toString()}`;
}
function statusLabel(status) {
    return {
        in_stock: "In Stock",
        low_stock: "Low Stock",
        out_of_stock: "Out of Stock",
        negative: "Negative",
    }[status] ?? "Unknown";
}
function statusIcon(status) {
    return {
        in_stock: "bi-check-circle",
        low_stock: "bi-exclamation-triangle",
        out_of_stock: "bi-x-circle",
        negative: "bi-dash-circle",
    }[status] ?? "bi-question-circle";
}
function transactionLabel(value) {
    return value ? String(value).replaceAll("_", " ") : "No activity";
}
function dateFormat(value) {
    if (!value) return "—";
    return new Intl.DateTimeFormat("en-IN", {
        day: "2-digit",
        month: "2-digit",
        year: "numeric",
    }).format(new Date(`${String(value).slice(0, 10)}T00:00:00`));
}
function moneyFormat(value) {
    return new Intl.NumberFormat("en-IN", {
        style: "currency",
        currency: "INR",
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(numberValue(value));
}
function quantityFormat(value) {
    return numberValue(value).toLocaleString("en-IN", {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    });
}
function numberFormat(value) {
    return Math.trunc(numberValue(value)).toLocaleString("en-IN");
}
function numberValue(value) {
    const number = Number(value ?? 0);
    return Number.isFinite(number) ? number : 0;
}
Index.layout = (page) => <BusinessLayout>{page}</BusinessLayout>;
