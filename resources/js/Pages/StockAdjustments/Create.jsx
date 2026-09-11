import React, { useEffect, useMemo, useRef, useState } from "react";

import axios from "axios";
import { Head, Link, router, useForm } from "@inertiajs/react";

import BusinessLayout from "@/Layouts/BusinessLayout";
import MasterNavigation from "../../Components/Business/MasterNavigation";
import ConfirmModal from "../../Components/ConfirmModal";

import "../../../css/business/stock-adjustment.css";

export default function Create({
    adjustment_no: adjustmentNo = "",
    today = "",
    godowns = [],
    selected_product: selectedProduct = null,
    adjustment = null,
    mode = "create",
}) {
    const isEdit = mode === "edit" && Boolean(adjustment?.id);
    const originalItems = isEdit && Array.isArray(adjustment.items)
        ? adjustment.items
        : [];
    const initialItems = isEdit
        ? originalItems.map((item) => editItem(item))
        : selectedProduct
            ? [newItem(selectedProduct, null, 0, true)]
            : [];

    const {
        data,
        setData,
        post,
        put,
        processing,
        errors,
        clearErrors,
    } = useForm({
        adjustment_date: isEdit
            ? dateInputValue(adjustment.adjustment_date)
            : today,
        type: isEdit ? adjustment.type : "increase",
        remarks: isEdit ? adjustment.remarks ?? "" : "",
        items: initialItems,
    });

    const [searchText, setSearchText] = useState("");
    const [searchLocation, setSearchLocation] = useState("shop");
    const [searchResults, setSearchResults] = useState([]);
    const [searching, setSearching] = useState(false);
    const [resultsOpen, setResultsOpen] = useState(false);
    const [searchMessage, setSearchMessage] = useState("");
    const [confirmationOpen, setConfirmationOpen] = useState(false);
    const [cancelConfirmation, setCancelConfirmation] = useState({
        show: false,
        processing: false,
    });

    const searchInputRef = useRef(null);
    const searchBoxRef = useRef(null);
    const searchRequestRef = useRef(null);

    const selectedGodownId = searchLocation === "shop"
        ? null
        : Number(searchLocation);

    const totalQuantity = useMemo(
        () =>
            data.items.reduce(
                (total, item) => total + numberValue(item.qty),
                0,
            ),
        [data.items],
    );

    const totalCost = useMemo(
        () =>
            data.items.reduce(
                (total, item) =>
                    total
                    + numberValue(item.qty)
                    * numberValue(item.unit_cost),
                0,
            ),
        [data.items],
    );

    useEffect(() => {
        if (initialItems.length === 0) {
            return;
        }

        Promise.all(
            initialItems.map((item) =>
                axios.get("/stock-adjustments/product-stock", {
                    params: {
                        product_id: item.product_id,
                        godown_id: item.godown_id,
                    },
                    headers: { Accept: "application/json" },
                }),
            ),
        )
            .then((responses) => {
                setData(
                    "items",
                    initialItems.map((item, index) => ({
                        ...item,
                        current_stock: stockAfterOriginalReversal(
                            responses[index]?.data?.stock,
                            item.product_id,
                            item.godown_id,
                            originalItems,
                            adjustment?.type,
                        ),
                        stock_loading: false,
                    })),
                );
            })
            .catch(() => {
                setData(
                    "items",
                    initialItems.map((item) => ({
                        ...item,
                        stock_loading: false,
                    })),
                );
                setSearchMessage(
                    "One or more current stock balances could not be loaded.",
                );
            });

        // Initial stock is loaded once; later location changes load separately.
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, []);

    useEffect(() => {
        if (searchText.trim().length < 2) {
            searchRequestRef.current?.abort();
            setSearchResults([]);
            setSearching(false);
            return undefined;
        }

        const timer = window.setTimeout(async () => {
            searchRequestRef.current?.abort();
            searchRequestRef.current = new AbortController();
            setSearching(true);
            setSearchMessage("");

            try {
                const response = await axios.get(
                    "/stock-adjustments/products/search",
                    {
                        params: {
                            q: searchText.trim(),
                            godown_id: selectedGodownId,
                        },
                        signal: searchRequestRef.current.signal,
                        headers: {
                            Accept: "application/json",
                        },
                    },
                );

                setSearchResults(response.data?.products ?? []);
                setResultsOpen(true);
            } catch (error) {
                if (error.code !== "ERR_CANCELED") {
                    setSearchResults([]);
                    setSearchMessage(
                        error.response?.data?.message
                        ?? "Product search could not be completed.",
                    );
                }
            } finally {
                setSearching(false);
            }
        }, 280);

        return () => window.clearTimeout(timer);
    }, [searchText, selectedGodownId]);

    useEffect(() => {
        const closeResults = (event) => {
            if (!searchBoxRef.current?.contains(event.target)) {
                setResultsOpen(false);
            }
        };

        document.addEventListener("mousedown", closeResults);

        return () => document.removeEventListener("mousedown", closeResults);
    }, []);

    const updateItem = (index, field, value) => {
        const items = data.items.map((item, itemIndex) =>
            itemIndex === index
                ? { ...item, [field]: value }
                : item,
        );

        setData("items", items);
        clearErrors(`items.${index}.${field}`);
    };

    const selectProduct = (product) => {
        const duplicateIndex = data.items.findIndex(
            (item) =>
                Number(item.product_id) === Number(product.id)
                && normalizedLocation(item.godown_id)
                === normalizedLocation(selectedGodownId),
        );

        if (duplicateIndex >= 0) {
            setSearchMessage(
                `${product.product_name} is already added for ${locationName(
                    selectedGodownId,
                    godowns,
                )}.`,
            );
            setResultsOpen(true);
            focusRowQuantity(duplicateIndex);
            return;
        }

        setData("items", [
            ...data.items,
            newItem(
                product,
                selectedGodownId,
                stockAfterOriginalReversal(
                    product.stock,
                    product.id,
                    selectedGodownId,
                    originalItems,
                    adjustment?.type,
                ),
                false,
            ),
        ]);
        clearErrors("items");
        setSearchText("");
        setSearchResults([]);
        setResultsOpen(false);
        setSearchMessage("");

        window.setTimeout(() => searchInputRef.current?.focus(), 0);
    };

    const removeItem = (index) => {
        setData(
            "items",
            data.items.filter((_, itemIndex) => itemIndex !== index),
        );
        setSearchMessage("");
    };

    const changeItemLocation = (index, value) => {
        const godownId = value === "shop" ? null : Number(value);
        const item = data.items[index];

        const duplicate = data.items.some(
            (candidate, candidateIndex) =>
                candidateIndex !== index
                && Number(candidate.product_id) === Number(item.product_id)
                && normalizedLocation(candidate.godown_id)
                === normalizedLocation(godownId),
        );

        if (duplicate) {
            setSearchMessage(
                `${item.product_name} is already listed for ${locationName(
                    godownId,
                    godowns,
                )}.`,
            );
            return;
        }

        const items = data.items.map((candidate, candidateIndex) =>
            candidateIndex === index
                ? {
                    ...candidate,
                    godown_id: godownId,
                    current_stock: 0,
                    stock_loading: true,
                }
                : candidate,
        );

        setData("items", items);
        setSearchMessage("");
        clearErrors(`items.${index}.godown_id`);
        loadRowStock(index, item.product_id, godownId, items);
    };

    async function loadRowStock(
        index,
        productId,
        godownId,
        sourceItems = data.items,
    ) {
        try {
            const response = await axios.get(
                "/stock-adjustments/product-stock",
                {
                    params: {
                        product_id: productId,
                        godown_id: godownId,
                    },
                    headers: {
                        Accept: "application/json",
                    },
                },
            );

            setData(
                "items",
                sourceItems.map((item, itemIndex) =>
                    itemIndex === index
                        ? {
                            ...item,
                            current_stock: stockAfterOriginalReversal(
                                response.data?.stock,
                                productId,
                                godownId,
                                originalItems,
                                adjustment?.type,
                            ),
                            unit_cost:
                                numberValue(item.unit_cost) > 0
                                    ? item.unit_cost
                                    : decimal(response.data?.unit_cost),
                            stock_loading: false,
                        }
                        : item,
                ),
            );
        } catch (error) {
            setData(
                "items",
                sourceItems.map((item, itemIndex) =>
                    itemIndex === index
                        ? { ...item, stock_loading: false }
                        : item,
                ),
            );
            setSearchMessage(
                error.response?.data?.message
                ?? "Unable to load current stock for this location.",
            );
        }
    }

    const handleSearchKeyDown = (event) => {
        if (event.key === "Escape") {
            setResultsOpen(false);
            return;
        }

        if (
            event.key === "Enter"
            && resultsOpen
            && searchResults.length > 0
        ) {
            event.preventDefault();
            selectProduct(searchResults[0]);
        }
    };

    const requestSave = (event) => {
        event?.preventDefault();

        if (data.items.length === 0) {
            setSearchMessage("Search and add at least one product.");
            searchInputRef.current?.focus();
            return;
        }

        setConfirmationOpen(true);
    };

    const saveAdjustment = () => {
        const options = {
            preserveScroll: true,
            onError: (validationErrors) => {
                setConfirmationOpen(false);
                window.setTimeout(
                    () => focusFirstError(validationErrors),
                    20,
                );
            },
        };

        if (isEdit) {
            put(`/stock-adjustments/${adjustment.id}`, options);
        } else {
            post("/stock-adjustments", options);
        }
    };

    const cancelAdjustment = () => {
        setCancelConfirmation({
            show: true,
            processing: true,
        });

        router.patch(
            `/stock-adjustments/${adjustment.id}/cancel`,
            {},
            {
                preserveScroll: true,
                onFinish: () => {
                    setCancelConfirmation({
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
                title={
                    isEdit
                        ? `Edit ${adjustment.adjustment_no}`
                        : "Add Stock Adjustment"
                }
            />

            <div className="rr-adjustment-page rr-adjustment-form-page">
                <MasterNavigation
                    active="stock-adjustments"
                    title="Inventory"
                    subtitle="Create a controlled stock correction"
                />

                <div className="rr-adjustment-form-ribbon">
                    <div>
                        <i className="bi bi-grid-1x2"></i>
                        <span>Inventory</span>
                        <i className="bi bi-chevron-right"></i>
                        <span>Stock Adjustments</span>
                        <i className="bi bi-chevron-right"></i>
                        <strong>{isEdit ? "Edit Document" : "New Document"}</strong>
                    </div>

                    <div>
                        <i className="bi bi-shield-check"></i>
                        Controlled stock entry
                    </div>
                </div>

                <header className="rr-adjustment-header rr-adjustment-form-hero">
                    <div className="rr-adjustment-heading">
                        <div className="rr-adjustment-heading-icon">
                            <i className="bi bi-sliders"></i>
                        </div>

                        <div>
                            <span>Inventory Control</span>
                            <h1>
                                {isEdit
                                    ? "Edit Stock Adjustment"
                                    : "Add Stock Adjustment"}
                            </h1>
                            <small>
                                {isEdit
                                    ? "Reverse the original entry and apply corrected values"
                                    : "Record Shop or Godown stock differences"}
                            </small>
                        </div>
                    </div>

                    <div className="rr-adjustment-form-actions">
                        <Link
                            href={
                                isEdit
                                    ? `/stock-adjustments/${adjustment.id}`
                                    : "/stock-adjustments"
                            }
                            className="back"
                        >
                            <i className="bi bi-arrow-left"></i>
                            Back
                        </Link>

                        {isEdit && (
                            <button
                                type="button"
                                className="document-cancel"
                                onClick={() =>
                                    setCancelConfirmation({
                                        show: true,
                                        processing: false,
                                    })
                                }
                            >
                                <i className="bi bi-x-octagon"></i>
                                Cancel Adjustment
                            </button>
                        )}

                        <button
                            type="button"
                            className="save"
                            disabled={processing}
                            onClick={requestSave}
                        >
                            <i className="bi bi-check2-circle"></i>
                            {isEdit ? "Update Adjustment" : "Save Adjustment"}
                        </button>
                    </div>
                </header>

                <div className="rr-adjustment-workflow-strip">
                    <div className="active">
                        <span>1</span>
                        <div>
                            <strong>Document</strong>
                            <small>Date and adjustment type</small>
                        </div>
                    </div>

                    <i className="bi bi-chevron-right"></i>

                    <div className={data.items.length > 0 ? "active" : ""}>
                        <span>2</span>
                        <div>
                            <strong>Products</strong>
                            <small>{data.items.length} items selected</small>
                        </div>
                    </div>

                    <i className="bi bi-chevron-right"></i>

                    <div className={data.items.length > 0 ? "active" : ""}>
                        <span>3</span>
                        <div>
                            <strong>Review &amp; Save</strong>
                            <small>{currency(totalCost)} stock value</small>
                        </div>
                    </div>
                </div>

                <form onSubmit={requestSave} noValidate>
                    <section className="rr-adjustment-window rr-adjustment-document-window">
                        <div className="rr-adjustment-window-title">
                            <div>
                                <i className="bi bi-file-earmark-text"></i>
                                <strong>Adjustment Information</strong>
                            </div>

                            <span className="rr-adjustment-draft-label">
                                {isEdit ? "Editing" : "New Entry"}
                            </span>
                        </div>

                        <div className="rr-adjustment-document-grid">
                            <FormField
                                label="Adjustment Number"
                                icon="bi-hash"
                            >
                                <div className="rr-adjustment-readonly">
                                    {isEdit
                                        ? adjustment.adjustment_no
                                        : adjustmentNo || "Generated on save"}
                                </div>
                            </FormField>

                            <FormField
                                label="Adjustment Date"
                                icon="bi-calendar3"
                                required
                                error={errors.adjustment_date}
                            >
                                <input
                                    type="date"
                                    value={data.adjustment_date}
                                    data-error-key="adjustment_date"
                                    className={
                                        errors.adjustment_date
                                            ? "is-invalid"
                                            : ""
                                    }
                                    onClick={openDatePicker}
                                    onFocus={openDatePicker}
                                    onChange={(event) => {
                                        setData(
                                            "adjustment_date",
                                            event.target.value,
                                        );
                                        clearErrors("adjustment_date");
                                    }}
                                />
                            </FormField>

                            <FormField
                                label="Adjustment Type"
                                icon="bi-arrow-left-right"
                                required
                                error={errors.type}
                            >
                                <div
                                    className="rr-adjustment-type-switch"
                                    data-error-key="type"
                                >
                                    <button
                                        type="button"
                                        className={
                                            data.type === "increase"
                                                ? "increase active"
                                                : "increase"
                                        }
                                        onClick={() => {
                                            setData("type", "increase");
                                            clearErrors("type");
                                        }}
                                    >
                                        <i className="bi bi-plus-circle"></i>
                                        Increase Stock
                                    </button>

                                    <button
                                        type="button"
                                        className={
                                            data.type === "decrease"
                                                ? "decrease active"
                                                : "decrease"
                                        }
                                        onClick={() => {
                                            setData("type", "decrease");
                                            clearErrors("type");
                                        }}
                                    >
                                        <i className="bi bi-dash-circle"></i>
                                        Decrease Stock
                                    </button>
                                </div>
                            </FormField>

                            <div className="rr-adjustment-document-help">
                                <i
                                    className={`bi ${data.type === "increase"
                                            ? "bi-info-circle"
                                            : "bi-exclamation-triangle"
                                        }`}
                                ></i>
                                <span>
                                    {data.type === "increase"
                                        ? "Use when physical stock is higher than system stock."
                                        : "Use when physical stock is lower. Quantity cannot exceed available stock."}
                                </span>
                            </div>
                        </div>
                    </section>

                    <section className="rr-adjustment-window rr-adjustment-product-window">
                        <div className="rr-adjustment-window-title">
                            <div>
                                <i className="bi bi-box-seam"></i>
                                <strong>Add Products</strong>
                                <span className="rr-adjustment-record-count">
                                    {data.items.length} selected
                                </span>
                            </div>

                            <span className="rr-adjustment-key-hint">
                                <kbd>Enter</kbd> adds first result
                            </span>
                        </div>

                        <div className="rr-adjustment-product-search-area">
                            <div className="rr-adjustment-search-location">
                                <label htmlFor="adjustment-search-location">
                                    Add To Location
                                </label>

                                <select
                                    id="adjustment-search-location"
                                    value={searchLocation}
                                    onChange={(event) => {
                                        setSearchLocation(event.target.value);
                                        setSearchResults([]);
                                        setSearchMessage("");
                                        searchInputRef.current?.focus();
                                    }}
                                >
                                    <option value="shop">Shop Stock</option>

                                    {godowns.map((godown) => (
                                        <option
                                            key={godown.id}
                                            value={godown.id}
                                        >
                                            {godown.godown_name}
                                        </option>
                                    ))}
                                </select>
                            </div>

                            <div
                                className="rr-adjustment-product-search"
                                ref={searchBoxRef}
                            >
                                <label htmlFor="adjustment-product-search">
                                    Search Product
                                </label>

                                <div className="rr-adjustment-search-input-wrap">
                                    <i className="bi bi-search"></i>

                                    <input
                                        id="adjustment-product-search"
                                        ref={searchInputRef}
                                        type="search"
                                        autoComplete="off"
                                        value={searchText}
                                        placeholder="Type product name, code, barcode or part number"
                                        className={
                                            errors.items && data.items.length === 0
                                                ? "is-invalid"
                                                : ""
                                        }
                                        data-error-key="items"
                                        onFocus={() =>
                                            setResultsOpen(
                                                searchText.trim().length >= 2,
                                            )
                                        }
                                        onKeyDown={handleSearchKeyDown}
                                        onChange={(event) => {
                                            setSearchText(event.target.value);
                                            setResultsOpen(true);
                                            setSearchMessage("");
                                        }}
                                    />

                                    {searching && (
                                        <span className="rr-adjustment-search-spinner"></span>
                                    )}

                                    {searchText && !searching && (
                                        <button
                                            type="button"
                                            title="Clear search"
                                            onClick={() => {
                                                setSearchText("");
                                                setSearchResults([]);
                                                setResultsOpen(false);
                                                setSearchMessage("");
                                                searchInputRef.current?.focus();
                                            }}
                                        >
                                            <i className="bi bi-x-lg"></i>
                                        </button>
                                    )}
                                </div>

                                {resultsOpen && searchText.trim().length >= 2 && (
                                    <ProductResults
                                        products={searchResults}
                                        searching={searching}
                                        location={locationName(
                                            selectedGodownId,
                                            godowns,
                                        )}
                                        onSelect={selectProduct}
                                        onClose={() => setResultsOpen(false)}
                                    />
                                )}
                            </div>
                        </div>

                        {(searchMessage || errors.items) && (
                            <div className="rr-adjustment-inline-alert">
                                <i className="bi bi-exclamation-circle"></i>
                                <span>{searchMessage || errors.items}</span>
                                <button
                                    type="button"
                                    onClick={() => {
                                        setSearchMessage("");
                                        clearErrors("items");
                                    }}
                                >
                                    <i className="bi bi-x"></i>
                                </button>
                            </div>
                        )}
                    </section>

                    <section className="rr-adjustment-window rr-adjustment-entry-window">
                        <div className="rr-adjustment-window-title">
                            <div>
                                <i className="bi bi-list-check"></i>
                                <strong>Adjustment Items</strong>
                            </div>

                            <span className="rr-adjustment-entry-mode">
                                <i
                                    className={`bi ${data.type === "increase"
                                            ? "bi-plus-circle"
                                            : "bi-dash-circle"
                                        }`}
                                ></i>
                                {data.type === "increase"
                                    ? "Increasing Stock"
                                    : "Decreasing Stock"}
                            </span>
                        </div>

                        <div className="rr-adjustment-entry-scroll">
                            <table className="rr-adjustment-entry-table">
                                <thead>
                                    <tr>
                                        <th className="serial">#</th>
                                        <th className="product">Product</th>
                                        <th className="location">Location</th>
                                        <th className="number">Available</th>
                                        <th className="quantity">Quantity</th>
                                        <th className="number">After Stock</th>
                                        <th className="cost">Unit Cost</th>
                                        <th className="number">Total Cost</th>
                                        <th className="remove"></th>
                                    </tr>
                                </thead>

                                <tbody>
                                    {data.items.length > 0 ? (
                                        data.items.map((item, index) => (
                                            <AdjustmentRow
                                                key={item.row_key}
                                                item={item}
                                                index={index}
                                                type={data.type}
                                                godowns={godowns}
                                                errors={errors}
                                                onUpdate={updateItem}
                                                onLocationChange={
                                                    changeItemLocation
                                                }
                                                onRemove={removeItem}
                                            />
                                        ))
                                    ) : (
                                        <tr>
                                            <td
                                                colSpan="9"
                                                className="rr-adjustment-entry-empty"
                                            >
                                                <i className="bi bi-search"></i>
                                                <strong>No products added</strong>
                                                <span>
                                                    Use the product search above to add adjustment items.
                                                </span>
                                            </td>
                                        </tr>
                                    )}
                                </tbody>

                                {data.items.length > 0 && (
                                    <tfoot>
                                        <tr>
                                            <td colSpan="4">
                                                <strong>
                                                    {data.items.length} product
                                                    {data.items.length === 1
                                                        ? ""
                                                        : "s"}
                                                </strong>
                                            </td>
                                            <td className="number">
                                                {quantity(totalQuantity)}
                                            </td>
                                            <td></td>
                                            <td></td>
                                            <td className="number total-cost">
                                                {currency(totalCost)}
                                            </td>
                                            <td></td>
                                        </tr>
                                    </tfoot>
                                )}
                            </table>
                        </div>
                    </section>

                    <section className="rr-adjustment-window rr-adjustment-remarks-window">
                        <div className="rr-adjustment-window-title">
                            <div>
                                <i className="bi bi-chat-left-text"></i>
                                <strong>Reason / Remarks</strong>
                            </div>

                            <span>Optional, but recommended for audit</span>
                        </div>

                        <div className="rr-adjustment-remarks-body">
                            <textarea
                                rows="3"
                                maxLength="1000"
                                value={data.remarks}
                                data-error-key="remarks"
                                className={errors.remarks ? "is-invalid" : ""}
                                placeholder="Example: Physical stock verification difference, damaged item correction..."
                                onChange={(event) => {
                                    setData("remarks", event.target.value);
                                    clearErrors("remarks");
                                }}
                            ></textarea>

                            <div>
                                <span>{data.remarks.length}/1000</span>
                                {errors.remarks && (
                                    <small>{errors.remarks}</small>
                                )}
                            </div>
                        </div>
                    </section>

                    <footer className="rr-adjustment-form-footer">
                        <div>
                            <i className="bi bi-shield-check"></i>
                            <span>
                                Saving creates permanent stock-ledger entries for audit.
                            </span>
                        </div>

                        <div className="rr-adjustment-form-actions">
                            <Link
                                href={
                                    isEdit
                                        ? `/stock-adjustments/${adjustment.id}`
                                        : "/stock-adjustments"
                                }
                                className="cancel"
                            >
                                <i className="bi bi-x-lg"></i>
                                Discard Changes
                            </Link>

                            <button
                                type="submit"
                                className="save"
                                disabled={processing}
                            >
                                {processing ? (
                                    <span className="rr-adjustment-button-spinner"></span>
                                ) : (
                                    <i className="bi bi-check2-circle"></i>
                                )}
                                {processing
                                    ? "Saving..."
                                    : isEdit
                                        ? "Update Adjustment"
                                        : "Save Adjustment"}
                            </button>
                        </div>
                    </footer>
                </form>

                <ConfirmModal
                    show={confirmationOpen}
                    title={
                        isEdit
                            ? "Update Stock Adjustment"
                            : "Save Stock Adjustment"
                    }
                    message={
                        isEdit
                            ? `Update "${adjustment.adjustment_no}" with ${data.items.length} item${data.items.length === 1 ? "" : "s"}? The original stock effect will be reversed before the corrected values are applied.`
                            : `Create this ${data.type} adjustment with ${data.items.length} product item${data.items.length === 1 ? "" : "s"} and total quantity ${quantity(totalQuantity)}? Stock balances will be updated immediately.`
                    }
                    confirmText={
                        isEdit ? "Update Adjustment" : "Save Adjustment"
                    }
                    cancelText="Review Again"
                    icon="bi-check2-circle"
                    variant="primary"
                    processing={processing}
                    onConfirm={saveAdjustment}
                    onCancel={() => {
                        if (!processing) {
                            setConfirmationOpen(false);
                        }
                    }}
                />

                {isEdit && (
                    <ConfirmModal
                        show={cancelConfirmation.show}
                        title="Cancel Stock Adjustment"
                        message={`Cancel "${adjustment.adjustment_no}"? Reversing ledger entries will be created and this adjustment cannot be edited afterward.`}
                        confirmText="Cancel Adjustment"
                        cancelText="Keep Adjustment"
                        icon="bi-x-octagon"
                        variant="danger"
                        processing={cancelConfirmation.processing}
                        onConfirm={cancelAdjustment}
                        onCancel={() => {
                            if (!cancelConfirmation.processing) {
                                setCancelConfirmation({
                                    show: false,
                                    processing: false,
                                });
                            }
                        }}
                    />
                )}
            </div>
        </>
    );
}

function ProductResults({
    products,
    searching,
    location,
    onSelect,
    onClose,
}) {
    return (
        <div className="rr-adjustment-search-results">
            <div className="rr-adjustment-results-header">
                <div>
                    <i className="bi bi-box-seam"></i>
                    <strong>Product Results</strong>
                    <span>{location}</span>
                </div>

                <button type="button" onClick={onClose}>
                    <i className="bi bi-x-lg"></i>
                    Close
                </button>
            </div>

            <div className="rr-adjustment-results-body">
                {searching ? (
                    <div className="rr-adjustment-result-state">
                        <span className="rr-adjustment-search-spinner"></span>
                        Searching products...
                    </div>
                ) : products.length > 0 ? (
                    products.map((product, index) => (
                        <button
                            key={product.id}
                            type="button"
                            className="rr-adjustment-result-row"
                            onClick={() => onSelect(product)}
                        >
                            <span className="rr-adjustment-result-index">
                                {index + 1}
                            </span>

                            <span className="rr-adjustment-result-product">
                                <strong>{product.product_name}</strong>
                                <small>
                                    Code: {product.product_code || "—"}
                                    <b>•</b>
                                    Part: {product.part_number || "—"}
                                    {product.barcode && (
                                        <>
                                            <b>•</b>
                                            Barcode: {product.barcode}
                                        </>
                                    )}
                                </small>
                            </span>

                            <span className="rr-adjustment-result-stock">
                                <small>Available</small>
                                <strong>{quantity(product.stock)}</strong>
                            </span>

                            <span className="rr-adjustment-result-cost">
                                <small>Purchase Cost</small>
                                <strong>
                                    {currency(product.purchase_price)}
                                </strong>
                            </span>

                            <span className="rr-adjustment-result-add">
                                <i className="bi bi-plus-lg"></i>
                                Add
                            </span>
                        </button>
                    ))
                ) : (
                    <div className="rr-adjustment-result-state empty">
                        <i className="bi bi-search"></i>
                        <strong>No matching products</strong>
                        <span>Try a different name, code or part number.</span>
                    </div>
                )}
            </div>

            {products.length > 0 && !searching && (
                <div className="rr-adjustment-results-footer">
                    <span>{products.length} results</span>
                    <span>
                        Press <kbd>Enter</kbd> to add the first result
                    </span>
                </div>
            )}
        </div>
    );
}

function AdjustmentRow({
    item,
    index,
    type,
    godowns,
    errors,
    onUpdate,
    onLocationChange,
    onRemove,
}) {
    const available = numberValue(item.current_stock);
    const qty = numberValue(item.qty);
    const after = type === "increase"
        ? available + qty
        : available - qty;
    const insufficient = type === "decrease" && qty > available;
    const rowError = firstItemError(errors, index);

    return (
        <tr className={rowError || insufficient ? "has-error" : ""}>
            <td className="serial">{index + 1}</td>

            <td className="product">
                <strong>{item.product_name}</strong>
                <small>
                    {item.product_code || "No code"}
                    <b>•</b>
                    {item.part_number || "No part number"}
                </small>
                {errors[`items.${index}.product_id`] && (
                    <em>{errors[`items.${index}.product_id`]}</em>
                )}
            </td>

            <td className="location">
                <select
                    value={
                        item.godown_id === null
                            ? "shop"
                            : String(item.godown_id)
                    }
                    data-error-key={`items.${index}.godown_id`}
                    className={
                        errors[`items.${index}.godown_id`]
                            ? "is-invalid"
                            : ""
                    }
                    onChange={(event) =>
                        onLocationChange(index, event.target.value)
                    }
                >
                    <option value="shop">Shop</option>

                    {godowns.map((godown) => (
                        <option key={godown.id} value={godown.id}>
                            {godown.godown_name}
                        </option>
                    ))}
                </select>
            </td>

            <td className="number available">
                {item.stock_loading ? (
                    <span className="rr-adjustment-cell-spinner"></span>
                ) : (
                    quantity(available)
                )}
            </td>

            <td className="quantity">
                <input
                    id={`adjustment-qty-${index}`}
                    type="number"
                    min="1"
                    step="1"
                    value={item.qty}
                    data-error-key={`items.${index}.qty`}
                    className={
                        errors[`items.${index}.qty`] || insufficient
                            ? "is-invalid"
                            : ""
                    }
                    onFocus={(event) => event.target.select()}
                    onChange={(event) =>
                        onUpdate(index, "qty", event.target.value)
                    }
                />
                {insufficient && (
                    <small>Maximum {quantity(available)}</small>
                )}
                {errors[`items.${index}.qty`] && (
                    <small>{errors[`items.${index}.qty`]}</small>
                )}
            </td>

            <td
                className={`number after ${after < 0 ? "negative" : ""}`}
            >
                {quantity(after)}
            </td>

            <td className="cost">
                <div className="rr-adjustment-money-input">
                    <span>₹</span>
                    <input
                        type="number"
                        min="0"
                        step="0.01"
                        value={item.unit_cost}
                        data-error-key={`items.${index}.unit_cost`}
                        className={
                            errors[`items.${index}.unit_cost`]
                                ? "is-invalid"
                                : ""
                        }
                        onFocus={(event) => event.target.select()}
                        onChange={(event) =>
                            onUpdate(
                                index,
                                "unit_cost",
                                event.target.value,
                            )
                        }
                    />
                </div>
            </td>

            <td className="number line-total">
                {currency(qty * numberValue(item.unit_cost))}
            </td>

            <td className="remove">
                <button
                    type="button"
                    title="Remove product"
                    onClick={() => onRemove(index)}
                >
                    <i className="bi bi-trash3"></i>
                </button>
            </td>
        </tr>
    );
}

function FormField({ label, icon, required = false, error, children }) {
    return (
        <label className="rr-adjustment-form-field">
            <span>
                <i className={`bi ${icon}`}></i>
                {label}
                {required && <b>*</b>}
            </span>
            {children}
            {error && <small>{error}</small>}
        </label>
    );
}

function newItem(product, godownId, stock, stockLoading) {
    return {
        row_key: `${product.id}-${Date.now()}-${Math.random()}`,
        product_id: product.id,
        product_name: product.product_name,
        product_code: product.product_code,
        part_number: product.part_number,
        godown_id: godownId,
        qty: "1",
        unit_cost: decimal(product.purchase_price),
        current_stock: numberValue(stock),
        stock_loading: stockLoading,
    };
}

function editItem(item) {
    const product = item.product ?? {};

    return {
        row_key: `existing-${item.id}`,
        product_id: item.product_id,
        product_name: product.product_name ?? "Unknown Product",
        product_code: product.product_code,
        part_number: product.part_number,
        godown_id: item.godown_id,
        qty: String(item.qty ?? 1),
        unit_cost: decimal(item.unit_cost),
        current_stock: numberValue(item.stock_after),
        stock_loading: true,
    };
}

function stockAfterOriginalReversal(
    currentStock,
    productId,
    godownId,
    originalItems,
    originalType,
) {
    const originalQuantity = originalItems
        .filter(
            (item) =>
                Number(item.product_id) === Number(productId)
                && normalizedLocation(item.godown_id)
                === normalizedLocation(godownId),
        )
        .reduce(
            (total, item) => total + numberValue(item.qty),
            0,
        );

    return originalType === "increase"
        ? numberValue(currentStock) - originalQuantity
        : numberValue(currentStock) + originalQuantity;
}

function dateInputValue(value) {
    return value ? String(value).slice(0, 10) : "";
}

function firstItemError(errors, index) {
    return Object.keys(errors).find((key) =>
        key.startsWith(`items.${index}.`),
    );
}

function focusFirstError(errors) {
    const firstKey = Object.keys(errors)[0];

    if (!firstKey) {
        return;
    }

    const element = document.querySelector(
        `[data-error-key="${firstKey}"]`,
    );

    if (element) {
        element.scrollIntoView({
            behavior: "smooth",
            block: "center",
        });
        window.setTimeout(() => element.focus(), 250);
    }
}

function focusRowQuantity(index) {
    window.setTimeout(() => {
        const input = document.getElementById(`adjustment-qty-${index}`);
        input?.scrollIntoView({ behavior: "smooth", block: "center" });
        input?.focus();
        input?.select();
    }, 0);
}

function openDatePicker(event) {
    if (typeof event.currentTarget.showPicker === "function") {
        event.currentTarget.showPicker();
    }
}

function normalizedLocation(value) {
    return value === null || value === "" || value === undefined
        ? "shop"
        : String(value);
}

function locationName(godownId, godowns) {
    if (godownId === null) {
        return "Shop";
    }

    return godowns.find(
        (godown) => Number(godown.id) === Number(godownId),
    )?.godown_name ?? "Godown";
}

function numberValue(value) {
    const parsed = Number(value);

    return Number.isFinite(parsed) ? parsed : 0;
}

function decimal(value) {
    return numberValue(value).toFixed(2);
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

Create.layout = (page) => (
    <BusinessLayout title="Add Stock Adjustment">
        {page}
    </BusinessLayout>
);
