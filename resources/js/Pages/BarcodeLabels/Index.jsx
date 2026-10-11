import React, { useMemo, useState } from "react";
import { Head, Link, router } from "@inertiajs/react";

import BusinessLayout from "@/Layouts/BusinessLayout";

import "../../../css/business/barcode-labels.css";

export default function Index({
    products,
    filters = {},
    options = {},
    initial_product: initialProduct = null,
}) {
    const rows = products?.data ?? [];
    const [search, setSearch] = useState(filters.search ?? "");
    const [withoutBarcode, setWithoutBarcode] = useState(
        Boolean(filters.without_barcode),
    );
    const [selected, setSelected] = useState(() => (
        initialProduct
            ? {
                [initialProduct.id]: {
                    ...initialProduct,
                    quantity: 1,
                },
            }
            : {}
    ));
    const [paper, setPaper] = useState("a4");
    const [priceDisplay, setPriceDisplay] = useState("selling_price");
    const [showBranch, setShowBranch] = useState(true);
    const [showProductCode, setShowProductCode] = useState(true);
    const [showPartNumber, setShowPartNumber] = useState(true);
    const [generating, setGenerating] = useState(false);
    const [message, setMessage] = useState("");

    const selectedProducts = useMemo(
        () => Object.values(selected),
        [selected],
    );

    const totalLabels = useMemo(
        () => selectedProducts.reduce(
            (total, product) => total + Number(product.quantity || 0),
            0,
        ),
        [selectedProducts],
    );

    const selectedOnPage = rows.filter((product) => selected[product.id]).length;
    const allVisibleSelected = rows.length > 0 && selectedOnPage === rows.length;

    const applyFilters = (event) => {
        event.preventDefault();

        router.get(
            "/barcode-labels",
            {
                search: search.trim() || undefined,
                without_barcode: withoutBarcode ? 1 : undefined,
            },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            },
        );
    };

    const clearFilters = () => {
        setSearch("");
        setWithoutBarcode(false);

        router.get(
            "/barcode-labels",
            {},
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            },
        );
    };

    const toggleProduct = (product) => {
        setSelected((current) => {
            const next = { ...current };

            if (next[product.id]) {
                delete next[product.id];
            } else {
                next[product.id] = {
                    ...product,
                    quantity: 1,
                };
            }

            return next;
        });
        setMessage("");
    };

    const toggleVisibleProducts = () => {
        setSelected((current) => {
            const next = { ...current };

            if (allVisibleSelected) {
                rows.forEach((product) => delete next[product.id]);
            } else {
                rows.forEach((product) => {
                    if (!next[product.id]) {
                        next[product.id] = {
                            ...product,
                            quantity: 1,
                        };
                    }
                });
            }

            return next;
        });
        setMessage("");
    };

    const changeQuantity = (productId, value) => {
        const quantity = Math.min(500, Math.max(1, Number(value) || 1));

        setSelected((current) => ({
            ...current,
            [productId]: {
                ...current[productId],
                quantity,
            },
        }));
    };

    const clearSelection = () => {
        setSelected({});
        setMessage("");
    };

    const generateMissingBarcodes = () => {
        if (!selectedProducts.length) {
            setMessage("Select at least one product first.");
            return;
        }

        setGenerating(true);
        setMessage("");

        router.post(
            "/barcode-labels/generate-missing",
            {
                product_ids: selectedProducts.map((product) => product.id),
            },
            {
                preserveScroll: true,
                onFinish: () => setGenerating(false),
            },
        );
    };

    const openDocument = (action) => {
        if (!selectedProducts.length) {
            setMessage("Select at least one product to prepare labels.");
            return;
        }

        submitLabelForm(`/barcode-labels/${action}`, {
            items: selectedProducts.map((product) => ({
                product_id: product.id,
                quantity: product.quantity,
            })),
            paper,
            price_display: priceDisplay,
            show_branch: showBranch ? 1 : 0,
            show_product_code: showProductCode ? 1 : 0,
            show_part_number: showPartNumber ? 1 : 0,
        });
    };

    return (
        <>
            <Head title="Barcode Labels" />

            <main className="rr-barcode-page">
                <PageHeader />

                {message && (
                    <div className="rr-barcode-alert" role="alert">
                        <i className="bi bi-exclamation-circle" />
                        <span>{message}</span>
                        <button
                            type="button"
                            onClick={() => setMessage("")}
                            aria-label="Close message"
                        >
                            <i className="bi bi-x-lg" />
                        </button>
                    </div>
                )}

                <section className="rr-barcode-stats">
                    <StatCard
                        tone="blue"
                        icon="bi-box-seam"
                        label="Products Found"
                        value={products?.total ?? rows.length}
                    />
                    <StatCard
                        tone="violet"
                        icon="bi-check2-square"
                        label="Selected Products"
                        value={selectedProducts.length}
                    />
                    <StatCard
                        tone="green"
                        icon="bi-upc-scan"
                        label="Labels to Print"
                        value={totalLabels}
                    />
                    <StatCard
                        tone="orange"
                        icon="bi-file-earmark"
                        label="Paper"
                        value={options?.papers?.[paper] ?? paper.toUpperCase()}
                    />
                </section>

                <section className="rr-barcode-workspace">
                    <div className="rr-barcode-main-column">
                        <FilterPanel
                            search={search}
                            setSearch={setSearch}
                            withoutBarcode={withoutBarcode}
                            setWithoutBarcode={setWithoutBarcode}
                            onSubmit={applyFilters}
                            onClear={clearFilters}
                        />

                        <section className="rr-barcode-panel rr-barcode-product-panel">
                            <div className="rr-barcode-panel-head">
                                <div>
                                    <span className="rr-barcode-eyebrow">PRODUCT REGISTER</span>
                                    <h2>Select Products</h2>
                                    <p>Choose products and set the number of labels required.</p>
                                </div>

                                <div className="rr-barcode-table-actions">
                                    <button
                                        type="button"
                                        className="rr-barcode-btn soft"
                                        onClick={toggleVisibleProducts}
                                        disabled={!rows.length}
                                    >
                                        <i className={`bi ${allVisibleSelected ? "bi-square" : "bi-check2-square"}`} />
                                        {allVisibleSelected ? "Clear Page" : "Select Page"}
                                    </button>
                                    <button
                                        type="button"
                                        className="rr-barcode-btn ghost-danger"
                                        onClick={clearSelection}
                                        disabled={!selectedProducts.length}
                                    >
                                        <i className="bi bi-x-circle" />
                                        Clear Selected
                                    </button>
                                </div>
                            </div>

                            <ProductTable
                                rows={rows}
                                selected={selected}
                                allVisibleSelected={allVisibleSelected}
                                onToggleAll={toggleVisibleProducts}
                                onToggle={toggleProduct}
                                onQuantityChange={changeQuantity}
                            />

                            <Pagination links={products?.links ?? []} />
                        </section>
                    </div>

                    <aside className="rr-barcode-side-column">
                        <LabelSettings
                            options={options}
                            paper={paper}
                            setPaper={setPaper}
                            priceDisplay={priceDisplay}
                            setPriceDisplay={setPriceDisplay}
                            showBranch={showBranch}
                            setShowBranch={setShowBranch}
                            showProductCode={showProductCode}
                            setShowProductCode={setShowProductCode}
                            showPartNumber={showPartNumber}
                            setShowPartNumber={setShowPartNumber}
                        />

                        <section className="rr-barcode-panel rr-barcode-output-panel">
                            <div className="rr-barcode-panel-head compact">
                                <div>
                                    <span className="rr-barcode-eyebrow">OUTPUT</span>
                                    <h2>Prepare Labels</h2>
                                </div>
                            </div>

                            <div className="rr-barcode-selection-summary">
                                <SummaryRow label="Products" value={selectedProducts.length} />
                                <SummaryRow label="Total labels" value={totalLabels} strong />
                            </div>

                            <button
                                type="button"
                                className="rr-barcode-btn generate"
                                onClick={generateMissingBarcodes}
                                disabled={generating || !selectedProducts.length}
                            >
                                {generating ? (
                                    <span className="spinner-border spinner-border-sm" />
                                ) : (
                                    <i className="bi bi-magic" />
                                )}
                                Generate Missing Barcodes
                            </button>

                            <div className="rr-barcode-output-grid">
                                <button
                                    type="button"
                                    className="rr-barcode-btn preview"
                                    onClick={() => openDocument("preview")}
                                >
                                    <i className="bi bi-eye" />
                                    Preview
                                </button>
                                <button
                                    type="button"
                                    className="rr-barcode-btn print"
                                    onClick={() => openDocument("print")}
                                >
                                    <i className="bi bi-printer" />
                                    Print
                                </button>
                                <button
                                    type="button"
                                    className="rr-barcode-btn pdf"
                                    onClick={() => openDocument("pdf")}
                                >
                                    <i className="bi bi-file-earmark-pdf" />
                                    PDF
                                </button>
                            </div>
                        </section>
                    </aside>
                </section>
            </main>
        </>
    );
}

function PageHeader() {
    return (
        <header className="rr-barcode-header">
            <div className="rr-barcode-header-icon">
                <i className="bi bi-upc-scan" />
            </div>
            <div>
                <span>INVENTORY UTILITIES</span>
                <h1>Barcode Label Studio</h1>
                <p>Generate and print branch-specific product price labels.</p>
            </div>
            <Link href="/products" className="rr-barcode-btn products">
                <i className="bi bi-box-seam" />
                Product Master
            </Link>
        </header>
    );
}

function StatCard({ tone, icon, label, value }) {
    return (
        <article className={`rr-barcode-stat ${tone}`}>
            <span className="rr-barcode-stat-icon">
                <i className={`bi ${icon}`} />
            </span>
            <div>
                <small>{label}</small>
                <strong>{value}</strong>
            </div>
        </article>
    );
}

function FilterPanel({
    search,
    setSearch,
    withoutBarcode,
    setWithoutBarcode,
    onSubmit,
    onClear,
}) {
    return (
        <form className="rr-barcode-panel rr-barcode-filter" onSubmit={onSubmit}>
            <div className="rr-barcode-filter-title">
                <span><i className="bi bi-funnel" /></span>
                <div>
                    <strong>Product Filter</strong>
                    <small>Search by name, code, barcode or part number</small>
                </div>
            </div>

            <label className="rr-barcode-search">
                <i className="bi bi-search" />
                <input
                    type="search"
                    value={search}
                    onChange={(event) => setSearch(event.target.value)}
                    placeholder="Search products..."
                />
                {search && (
                    <button type="button" onClick={() => setSearch("")}>
                        <i className="bi bi-x-lg" />
                    </button>
                )}
            </label>

            <label className="rr-barcode-check-card">
                <input
                    type="checkbox"
                    checked={withoutBarcode}
                    onChange={(event) => setWithoutBarcode(event.target.checked)}
                />
                <span><i className="bi bi-exclamation-diamond" /></span>
                Missing barcode only
            </label>

            <button type="submit" className="rr-barcode-btn filter">
                <i className="bi bi-search" />
                Apply
            </button>
            <button type="button" className="rr-barcode-btn reset" onClick={onClear}>
                <i className="bi bi-arrow-counterclockwise" />
                Reset
            </button>
        </form>
    );
}

function ProductTable({
    rows,
    selected,
    allVisibleSelected,
    onToggleAll,
    onToggle,
    onQuantityChange,
}) {
    return (
        <div className="rr-barcode-table-wrap">
            <table className="rr-barcode-table">
                <thead>
                    <tr>
                        <th className="check">
                            <input
                                type="checkbox"
                                checked={allVisibleSelected}
                                onChange={onToggleAll}
                                aria-label="Select all products on this page"
                            />
                        </th>
                        <th>Product</th>
                        <th>Barcode</th>
                        <th>Category / Brand</th>
                        <th className="number">Stock</th>
                        <th className="number">MRP</th>
                        <th className="number">Selling</th>
                        <th className="quantity">Labels</th>
                    </tr>
                </thead>
                <tbody>
                    {rows.length ? rows.map((product) => {
                        const isSelected = Boolean(selected[product.id]);

                        return (
                            <tr
                                key={product.id}
                                className={isSelected ? "selected" : ""}
                                onClick={() => onToggle(product)}
                            >
                                <td className="check" onClick={(event) => event.stopPropagation()}>
                                    <input
                                        type="checkbox"
                                        checked={isSelected}
                                        onChange={() => onToggle(product)}
                                    />
                                </td>
                                <td>
                                    <div className="rr-barcode-product-name">
                                        <span className="rr-barcode-product-icon">
                                            <i className="bi bi-box" />
                                        </span>
                                        <div>
                                            <strong>{product.product_name}</strong>
                                            <small>
                                                {product.product_code}
                                                {product.part_number ? ` • ${product.part_number}` : ""}
                                            </small>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    {product.has_barcode ? (
                                        <span className="rr-barcode-code ready">
                                            <i className="bi bi-upc" />
                                            {product.barcode}
                                        </span>
                                    ) : (
                                        <span className="rr-barcode-code missing">
                                            <i className="bi bi-exclamation-circle" />
                                            Not generated
                                        </span>
                                    )}
                                </td>
                                <td>
                                    <strong className="rr-barcode-muted-value">
                                        {product.category || "Uncategorized"}
                                    </strong>
                                    <small className="rr-barcode-sub-value">
                                        {product.brand || "No brand"}
                                    </small>
                                </td>
                                <td className="number">
                                    <span className={Number(product.current_stock) <= 0 ? "stock empty" : "stock"}>
                                        {number(product.current_stock)}
                                    </span>
                                </td>
                                <td className="number">{money(product.mrp)}</td>
                                <td className="number selling">{money(product.selling_price)}</td>
                                <td
                                    className="quantity"
                                    onClick={(event) => event.stopPropagation()}
                                >
                                    <input
                                        type="number"
                                        min="1"
                                        max="500"
                                        value={selected[product.id]?.quantity ?? 1}
                                        disabled={!isSelected}
                                        onChange={(event) => onQuantityChange(product.id, event.target.value)}
                                    />
                                </td>
                            </tr>
                        );
                    }) : (
                        <tr>
                            <td colSpan="8">
                                <div className="rr-barcode-empty">
                                    <i className="bi bi-search" />
                                    <strong>No products found</strong>
                                    <span>Change the search or barcode filter.</span>
                                </div>
                            </td>
                        </tr>
                    )}
                </tbody>
            </table>
        </div>
    );
}

function LabelSettings({
    options,
    paper,
    setPaper,
    priceDisplay,
    setPriceDisplay,
    showBranch,
    setShowBranch,
    showProductCode,
    setShowProductCode,
    showPartNumber,
    setShowPartNumber,
}) {
    return (
        <section className="rr-barcode-panel rr-barcode-settings">
            <div className="rr-barcode-panel-head compact">
                <div>
                    <span className="rr-barcode-eyebrow">LABEL SETTINGS</span>
                    <h2>Print Configuration</h2>
                </div>
                <span className="rr-barcode-settings-icon">
                    <i className="bi bi-sliders" />
                </span>
            </div>

            <label className="rr-barcode-field">
                <span>Paper / Label Size</span>
                <select value={paper} onChange={(event) => setPaper(event.target.value)}>
                    {Object.entries(options?.papers ?? {}).map(([value, label]) => (
                        <option key={value} value={value}>{label}</option>
                    ))}
                </select>
            </label>

            <label className="rr-barcode-field">
                <span>Price Display</span>
                <select
                    value={priceDisplay}
                    onChange={(event) => setPriceDisplay(event.target.value)}
                >
                    {Object.entries(options?.prices ?? {}).map(([value, label]) => (
                        <option key={value} value={value}>{label}</option>
                    ))}
                </select>
            </label>

            <div className="rr-barcode-switches">
                <SettingSwitch
                    label="Branch name"
                    text="Print the active branch on each label"
                    checked={showBranch}
                    onChange={setShowBranch}
                />
                <SettingSwitch
                    label="Product code"
                    text="Include the internal product code"
                    checked={showProductCode}
                    onChange={setShowProductCode}
                />
                <SettingSwitch
                    label="Part number"
                    text="Include part number when available"
                    checked={showPartNumber}
                    onChange={setShowPartNumber}
                />
            </div>
        </section>
    );
}

function SettingSwitch({ label, text, checked, onChange }) {
    return (
        <label className="rr-barcode-switch-row">
            <div>
                <strong>{label}</strong>
                <small>{text}</small>
            </div>
            <span className="rr-barcode-switch">
                <input
                    type="checkbox"
                    checked={checked}
                    onChange={(event) => onChange(event.target.checked)}
                />
                <i />
            </span>
        </label>
    );
}

function SummaryRow({ label, value, strong = false }) {
    return (
        <div className={strong ? "strong" : ""}>
            <span>{label}</span>
            <b>{value}</b>
        </div>
    );
}

function Pagination({ links }) {
    if (!links?.length || links.length <= 3) {
        return null;
    }

    return (
        <nav className="rr-barcode-pagination" aria-label="Product pages">
            {links.map((link, index) => (
                link.url ? (
                    <Link
                        key={`${link.label}-${index}`}
                        href={link.url}
                        preserveState
                        preserveScroll
                        className={link.active ? "active" : ""}
                    >
                        {paginationLabel(link.label)}
                    </Link>
                ) : (
                    <span key={`${link.label}-${index}`} className="disabled">
                        {paginationLabel(link.label)}
                    </span>
                )
            ))}
        </nav>
    );
}

function submitLabelForm(action, payload) {
    const form = document.createElement("form");
    form.method = "POST";
    form.action = action;
    form.target = "_blank";
    form.style.display = "none";

    const csrfToken = document
        .querySelector('meta[name="csrf-token"]')
        ?.getAttribute("content");

    if (csrfToken) {
        appendHiddenInput(form, "_token", csrfToken);
    }

    payload.items.forEach((item, index) => {
        appendHiddenInput(form, `items[${index}][product_id]`, item.product_id);
        appendHiddenInput(form, `items[${index}][quantity]`, item.quantity);
    });

    appendHiddenInput(form, "paper", payload.paper);
    appendHiddenInput(form, "price_display", payload.price_display);
    appendHiddenInput(form, "show_branch", payload.show_branch);
    appendHiddenInput(form, "show_product_code", payload.show_product_code);
    appendHiddenInput(form, "show_part_number", payload.show_part_number);

    document.body.appendChild(form);
    form.submit();
    form.remove();
}

function appendHiddenInput(form, name, value) {
    const input = document.createElement("input");
    input.type = "hidden";
    input.name = name;
    input.value = String(value ?? "");
    form.appendChild(input);
}

function paginationLabel(label) {
    return String(label)
        .replace("&laquo;", "«")
        .replace("&raquo;", "»");
}

function money(value) {
    return new Intl.NumberFormat("en-IN", {
        style: "currency",
        currency: "INR",
        maximumFractionDigits: 2,
    }).format(Number(value ?? 0));
}

function number(value) {
    return new Intl.NumberFormat("en-IN", {
        maximumFractionDigits: 2,
    }).format(Number(value ?? 0));
}

Index.layout = (page) => <BusinessLayout>{page}</BusinessLayout>;
