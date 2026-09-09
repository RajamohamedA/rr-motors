import React, { useEffect, useMemo, useRef, useState } from "react";

import axios from "axios";

import { Head, Link, useForm, usePage } from "@inertiajs/react";

import BusinessLayout from "@/Layouts/BusinessLayout";
import MasterNavigation from "../../Components/Business/MasterNavigation";

import "../../../css/business/quotations.css";

export default function Create({
    documentType = "quotation",
    documentNo = "",
    quotation = null,
    mode = "create",
}) {
    const editing = mode === "edit" || Boolean(quotation?.id);
    const { business } = usePage().props;
    const [customer, setCustomer] = useState(quotation?.customer ?? null);
    const [customerSearch, setCustomerSearch] = useState("");
    const [customerResults, setCustomerResults] = useState([]);
    const [customerOpen, setCustomerOpen] = useState(false);
    const [customerLoading, setCustomerLoading] = useState(false);
    const [customerError, setCustomerError] = useState("");

    const [mechanic, setMechanic] = useState(quotation?.mechanic ?? null);
    const [mechanicSearch, setMechanicSearch] = useState("");
    const [mechanicResults, setMechanicResults] = useState([]);
    const [mechanicOpen, setMechanicOpen] = useState(false);
    const [mechanicLoading, setMechanicLoading] = useState(false);

    const [productSearch, setProductSearch] = useState("");
    const [productResults, setProductResults] = useState([]);
    const [productOpen, setProductOpen] = useState(false);
    const [productLoading, setProductLoading] = useState(false);
    const [productError, setProductError] = useState("");
    const [rows, setRows] = useState(() => initialRows(quotation));

    const [quickModal, setQuickModal] = useState(null);
    const [quickProcessing, setQuickProcessing] = useState(false);
    const [quickError, setQuickError] = useState("");
    const [quickCustomer, setQuickCustomer] = useState(emptyCustomer());
    const [quickMechanic, setQuickMechanic] = useState(emptyMechanic());

    const customerTimer = useRef(null);
    const mechanicTimer = useRef(null);
    const productTimer = useRef(null);
    const customerBox = useRef(null);
    const mechanicBox = useRef(null);
    const productBox = useRef(null);
    const productInput = useRef(null);
    const formRef = useRef(null);

    const { data, setData, transform, post, put, processing, errors } = useForm({
        customer_id: quotation?.customer_id
            ? String(quotation.customer_id)
            : "",
        document_type: quotation?.document_type ?? documentType,
        sale_source: quotation?.sale_source ?? "customer",
        mechanic_id: quotation?.mechanic_id
            ? String(quotation.mechanic_id)
            : "",
        commission_type: quotation?.commission_type ?? "percentage",
        commission_percent: String(quotation?.commission_percent ?? "10"),
        commission_amount: String(quotation?.commission_amount ?? "0"),
        quotation_date: dateInput(quotation?.quotation_date) || today(),
        valid_until: dateInput(quotation?.valid_until) || dateAfter(15),
        billing_type: quotation?.billing_type ?? "gst",
        final_discount: String(quotation?.final_discount ?? "0"),
        terms_conditions:
            quotation?.terms_conditions ??
            "Prices are valid until the validity date. Availability is subject to confirmation.",
        remarks: quotation?.remarks ?? "",
        status: quotation?.status ?? "Draft",
        product_id: [],
        qty: [],
        free_qty: [],
        sale_price: [],
        discount_type: [],
        discount_value: [],
        gst_percent: [],
    });

    useEffect(() => {
        setData((previous) => ({
            ...previous,
            product_id: rows.map((row) => row.product.id),
            qty: rows.map((row) => row.quantity),
            free_qty: rows.map((row) => row.free_quantity),
            sale_price: rows.map((row) => row.sale_price),
            discount_type: rows.map((row) => row.discount_type),
            discount_value: rows.map((row) => row.discount_value),
            gst_percent: rows.map((row) => row.gst_percent),
        }));
    }, [rows]);

    useDebouncedSearch({
        value: customerSearch,
        selected: customer,
        timer: customerTimer,
        endpoint: "/quotations/customers/search",
        resultKey: "customers",
        setResults: setCustomerResults,
        setOpen: setCustomerOpen,
        setLoading: setCustomerLoading,
        setError: setCustomerError,
    });

    useDebouncedSearch({
        value: mechanicSearch,
        selected: mechanic,
        timer: mechanicTimer,
        endpoint: "/quotations/mechanics/search",
        resultKey: "mechanics",
        setResults: setMechanicResults,
        setOpen: setMechanicOpen,
        setLoading: setMechanicLoading,
        enabled: data.sale_source === "mechanic",
    });

    useDebouncedSearch({
        value: productSearch,
        selected: null,
        timer: productTimer,
        endpoint: "/quotations/products/search",
        resultKey: "products",
        setResults: setProductResults,
        setOpen: setProductOpen,
        setLoading: setProductLoading,
        setError: setProductError,
    });

    useEffect(() => {
        const closeDropdowns = (event) => {
            if (!customerBox.current?.contains(event.target)) setCustomerOpen(false);
            if (!mechanicBox.current?.contains(event.target)) setMechanicOpen(false);
            if (!productBox.current?.contains(event.target)) setProductOpen(false);
        };

        document.addEventListener("mousedown", closeDropdowns);
        return () => document.removeEventListener("mousedown", closeDropdowns);
    }, []);

    useEffect(() => {
        if (!Object.keys(errors).length) return;

        window.setTimeout(() => {
            const firstError = formRef.current?.querySelector(".rr-quote-error");
            if (!firstError) return;

            const area = firstError.closest(
                "label, td, .rr-quote-summary, .rr-quote-window-body, .rr-quote-product-window",
            );
            const input = area?.querySelector(
                ".rr-quote-lookup input, input, select, textarea, button",
            );

            firstError.scrollIntoView({ behavior: "smooth", block: "center" });
            input?.focus({ preventScroll: true });
        }, 80);
    }, [errors]);

    const totals = useMemo(
        () =>
            rows.reduce(
                (total, row) => {
                    const value = calculateRow(row, data.billing_type);
                    total.quantity += numberValue(row.quantity);
                    total.freeQuantity += numberValue(row.free_quantity);
                    total.discount += value.discount;
                    total.taxable += value.taxable;
                    total.gst += value.gst;
                    total.amount += value.total;
                    return total;
                },
                {
                    quantity: 0,
                    freeQuantity: 0,
                    discount: 0,
                    taxable: 0,
                    gst: 0,
                    amount: 0,
                },
            ),
        [rows, data.billing_type],
    );

    const beforeRound = Math.max(
        0,
        round2(totals.amount - numberValue(data.final_discount)),
    );
    const grandTotal = Math.round(beforeRound);
    const roundOff = round2(grandTotal - beforeRound);
    const commissionAmount = calculateCommission(data, totals.taxable);

    const selectCustomer = (selected) => {
        setCustomer(selected);
        setData("customer_id", String(selected.id));
        setCustomerSearch("");
        setCustomerResults([]);
        setCustomerOpen(false);
        setCustomerError("");
    };

    const clearCustomer = () => {
        setCustomer(null);
        setData("customer_id", "");
        setCustomerSearch("");
    };

    const selectMechanic = (selected) => {
        setMechanic(selected);
        setData("mechanic_id", String(selected.id));
        setMechanicSearch("");
        setMechanicOpen(false);
    };

    const changeSaleSource = (source) => {
        setData("sale_source", source);
        if (source === "customer") {
            setMechanic(null);
            setMechanicSearch("");
            setData("mechanic_id", "");
        }
    };

    const addProduct = (product) => {
        if (rows.some((row) => Number(row.product.id) === Number(product.id))) {
            setProductError("This product is already added.");
            return;
        }

        setRows((previous) => [
            ...previous,
            {
                product,
                quantity: "1",
                free_quantity: "0",
                sale_price: String(productPrice(product)),
                discount_type: "fixed",
                discount_value: "0",
                gst_percent: String(productGst(product)),
            },
        ]);
        setProductSearch("");
        setProductResults([]);
        setProductOpen(false);
        setProductError("");
        window.setTimeout(() => productInput.current?.focus(), 40);
    };

    const updateRow = (index, field, value) => {
        setRows((previous) => {
            const next = [...previous];
            next[index] = { ...next[index], [field]: value };
            return next;
        });
    };

    const removeRow = (index) => {
        setRows((previous) => previous.filter((_, rowIndex) => rowIndex !== index));
    };

    const saveQuickCustomer = async (event) => {
        event.preventDefault();
        setQuickProcessing(true);
        setQuickError("");

        try {
            const response = await axios.post(
                "/quotations/quick-customer",
                quickCustomer,
            );
            selectCustomer(response.data.customer);
            setQuickCustomer(emptyCustomer());
            setQuickModal(null);
        } catch (error) {
            setQuickError(apiError(error, "Unable to create customer."));
        } finally {
            setQuickProcessing(false);
        }
    };

    const saveQuickMechanic = async (event) => {
        event.preventDefault();
        setQuickProcessing(true);
        setQuickError("");

        try {
            const response = await axios.post(
                "/quotations/quick-mechanic",
                quickMechanic,
            );
            selectMechanic(response.data.mechanic);
            setQuickMechanic(emptyMechanic());
            setQuickModal(null);
        } catch (error) {
            setQuickError(apiError(error, "Unable to create mechanic."));
        } finally {
            setQuickProcessing(false);
        }
    };

    const submit = (event, status = data.status) => {
        event.preventDefault();

        if (!customer) {
            setCustomerError("Select a customer before saving.");
            customerBox.current?.scrollIntoView({ behavior: "smooth", block: "center" });
            window.setTimeout(
                () => customerBox.current?.querySelector("input")?.focus(),
                250,
            );
            return;
        }

        if (data.sale_source === "mechanic" && !mechanic) {
            mechanicBox.current?.scrollIntoView({ behavior: "smooth", block: "center" });
            window.setTimeout(
                () => mechanicBox.current?.querySelector("input")?.focus(),
                250,
            );
            return;
        }
        if (!rows.length) {
            setProductError("Add at least one product.");
            productInput.current?.focus();
            return;
        }

        transform((payload) => ({
            ...payload,
            status,
        }));

        if (editing) {
            put(`/quotations/${quotation.id}`, { preserveScroll: true });
            return;
        }

        post("/quotations", { preserveScroll: true });
    };

    const documentTitle =
        data.document_type === "proforma_invoice"
            ? "Proforma Invoice"
            : "Quotation";
    const title = editing ? `Edit ${documentTitle}` : `New ${documentTitle}`;
    const backUrl = editing ? `/quotations/${quotation.id}` : "/quotations";

    return (
        <>
            <Head title={title} />

            <div className="rr-quote-page rr-quote-create-page">
                <MasterNavigation
                    active="quotations"
                    title="Quotations"
                    subtitle={
                        editing
                            ? "Update quotation or proforma invoice"
                            : "Create quotation or proforma invoice"
                    }
                />

                <form ref={formRef} onSubmit={(event) => submit(event)}>
                    <header className="rr-quote-header">
                        <div className="rr-quote-header-main">
                            <Link href={backUrl} className="rr-quote-back">
                                <i className="bi bi-arrow-left"></i>
                            </Link>
                            <div className="rr-quote-header-icon">
                                <i className="bi bi-file-earmark-plus"></i>
                            </div>
                            <div>
                                <span className="rr-quote-eyebrow">
                                    {editing ? "Edit Sales Document" : "New Sales Document"}
                                </span>
                                <h1>{title}</h1>
                                <div className="rr-quote-header-meta">
                                    <span>
                                        {quotation?.quotation_no || documentNo || "New Document"}
                                    </span>
                                    <span>{business?.company_name ?? "Company"}</span>
                                    <span>{business?.store_name ?? "Branch"}</span>
                                </div>
                            </div>
                        </div>

                        <div className="rr-quote-header-actions">
                            <Link href={backUrl} className="rr-quote-btn cancel">
                                Cancel
                            </Link>
                            <button
                                type="button"
                                className="rr-quote-btn draft"
                                disabled={processing}
                                onClick={(event) => submit(event, "Draft")}
                            >
                                {editing ? "Save as Draft" : "Save Draft"}
                            </button>
                            <button
                                type="button"
                                className="rr-quote-btn primary save"
                                disabled={processing}
                                onClick={(event) =>
                                    submit(event, editing ? data.status : "Pending")
                                }
                            >
                                {processing
                                    ? "Saving..."
                                    : editing
                                        ? "Update Document"
                                        : "Save & Submit"}
                            </button>
                        </div>
                    </header>

                    <div className="rr-quote-create-grid">
                        <section className="rr-quote-window rr-quote-party-window">
                            <WindowTitle icon="bi-person" title="Customer" />
                            <div className="rr-quote-window-body">
                                {customer ? (
                                    <SelectedParty
                                        icon="bi-person-check"
                                        name={customer.customer_name}
                                        meta={[customer.customer_code, customer.mobile]
                                            .filter(Boolean)
                                            .join(" · ")}
                                        onClear={clearCustomer}
                                    />
                                ) : (
                                    <div ref={customerBox} className="rr-quote-lookup">
                                        <SearchInput
                                            value={customerSearch}
                                            placeholder="Search customer name, mobile or code..."
                                            loading={customerLoading}
                                            onFocus={() => setCustomerOpen(true)}
                                            onChange={(value) => {
                                                setCustomerSearch(value);
                                                setCustomerOpen(true);
                                            }}
                                        />
                                        {customerOpen && customerSearch.trim() && (
                                            <LookupResults
                                                results={customerResults}
                                                loading={customerLoading}
                                                minimumReached={customerSearch.trim().length >= 2}
                                                render={(item) => ({
                                                    title: item.customer_name,
                                                    meta: [item.customer_code, item.mobile]
                                                        .filter(Boolean)
                                                        .join(" · "),
                                                })}
                                                onSelect={selectCustomer}
                                            />
                                        )}
                                    </div>
                                )}

                                <button
                                    type="button"
                                    className="rr-quote-quick-add"
                                    onClick={() => {
                                        setQuickError("");
                                        setQuickModal("customer");
                                    }}
                                >
                                    <i className="bi bi-person-plus"></i>
                                    Quick Add Customer
                                </button>
                                <ErrorText message={customerError || errors.customer_id} />
                            </div>
                        </section>

                        <section className="rr-quote-window">
                            <WindowTitle icon="bi-file-earmark-text" title="Document" />
                            <div className="rr-quote-window-body rr-quote-mini-grid">
                                <Field label="Document Type" error={errors.document_type}>
                                    <select
                                        value={data.document_type}
                                        disabled={editing}
                                        onChange={(event) =>
                                            setData("document_type", event.target.value)
                                        }
                                    >
                                        <option value="quotation">Quotation</option>
                                        <option value="proforma_invoice">Proforma Invoice</option>
                                    </select>
                                </Field>
                                <Field label="Billing Type" error={errors.billing_type}>
                                    <select
                                        value={data.billing_type}
                                        onChange={(event) =>
                                            setData("billing_type", event.target.value)
                                        }
                                    >
                                        <option value="gst">GST</option>
                                        <option value="non_gst">Without GST</option>
                                    </select>
                                </Field>
                                <Field label="Document Date" error={errors.quotation_date}>
                                    <input
                                        type="date"
                                        value={data.quotation_date}
                                        onChange={(event) =>
                                            setData("quotation_date", event.target.value)
                                        }
                                    />
                                </Field>
                                <Field label="Valid Until" error={errors.valid_until}>
                                    <input
                                        type="date"
                                        min={data.quotation_date}
                                        value={data.valid_until}
                                        onChange={(event) =>
                                            setData("valid_until", event.target.value)
                                        }
                                    />
                                </Field>
                            </div>
                        </section>

                        <section className="rr-quote-window">
                            <WindowTitle icon="bi-tools" title="Sale Source" />
                            <div className="rr-quote-window-body">
                                <div className="rr-quote-source-toggle">
                                    <button
                                        type="button"
                                        className={data.sale_source === "customer" ? "active" : ""}
                                        onClick={() => changeSaleSource("customer")}
                                    >
                                        Direct Customer
                                    </button>
                                    <button
                                        type="button"
                                        className={data.sale_source === "mechanic" ? "active" : ""}
                                        onClick={() => changeSaleSource("mechanic")}
                                    >
                                        Mechanic Referral
                                    </button>
                                </div>

                                {data.sale_source === "mechanic" && (
                                    <>
                                        {mechanic ? (
                                            <SelectedParty
                                                icon="bi-tools"
                                                name={mechanic.mechanic_name}
                                                meta={[mechanic.mechanic_code, mechanic.mobile]
                                                    .filter(Boolean)
                                                    .join(" · ")}
                                                onClear={() => {
                                                    setMechanic(null);
                                                    setData("mechanic_id", "");
                                                }}
                                            />
                                        ) : (
                                            <div ref={mechanicBox} className="rr-quote-lookup">
                                                <SearchInput
                                                    value={mechanicSearch}
                                                    placeholder="Search mechanic..."
                                                    loading={mechanicLoading}
                                                    onFocus={() => setMechanicOpen(true)}
                                                    onChange={(value) => {
                                                        setMechanicSearch(value);
                                                        setMechanicOpen(true);
                                                    }}
                                                />
                                                {mechanicOpen && mechanicSearch.trim() && (
                                                    <LookupResults
                                                        results={mechanicResults}
                                                        loading={mechanicLoading}
                                                        minimumReached={mechanicSearch.trim().length >= 2}
                                                        render={(item) => ({
                                                            title: item.mechanic_name,
                                                            meta: [item.mechanic_code, item.mobile]
                                                                .filter(Boolean)
                                                                .join(" · "),
                                                        })}
                                                        onSelect={selectMechanic}
                                                    />
                                                )}
                                            </div>
                                        )}

                                        <button
                                            type="button"
                                            className="rr-quote-quick-add"
                                            onClick={() => {
                                                setQuickError("");
                                                setQuickModal("mechanic");
                                            }}
                                        >
                                            <i className="bi bi-person-plus"></i>
                                            Quick Add Mechanic
                                        </button>
                                        <ErrorText message={errors.mechanic_id} />
                                    </>
                                )}
                            </div>
                        </section>
                    </div>

                    {data.sale_source === "mechanic" && mechanic && (
                        <section className="rr-quote-window">
                            <WindowTitle icon="bi-percent" title="Commission Preview" />
                            <div className="rr-quote-window-body rr-quote-commission-grid">
                                <Field
                                    label="Commission Type"
                                    error={errors.commission_type}
                                >
                                    <select
                                        value={data.commission_type}
                                        onChange={(event) =>
                                            setData("commission_type", event.target.value)
                                        }
                                    >
                                        <option value="percentage">Percentage</option>
                                        <option value="fixed">Fixed Amount</option>
                                    </select>
                                </Field>
                                {data.commission_type === "percentage" ? (
                                    <Field
                                        label="Percentage"
                                        error={errors.commission_percent}
                                    >
                                        <input
                                            type="number"
                                            min="0"
                                            max="100"
                                            step="0.01"
                                            value={data.commission_percent}
                                            onChange={(event) =>
                                                setData("commission_percent", event.target.value)
                                            }
                                        />
                                    </Field>
                                ) : (
                                    <Field
                                        label="Fixed Amount"
                                        error={errors.commission_amount}
                                    >
                                        <input
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            value={data.commission_amount}
                                            onChange={(event) =>
                                                setData("commission_amount", event.target.value)
                                            }
                                        />
                                    </Field>
                                )}
                                <div className="rr-quote-commission-value">
                                    <span>Estimated Commission</span>
                                    <strong>{formatMoney(commissionAmount)}</strong>
                                </div>
                            </div>
                        </section>
                    )}

                    <section className="rr-quote-window rr-quote-product-window">
                        <div className="rr-quote-window-title">
                            <div>
                                <i className="bi bi-box-seam"></i>
                                <strong>Add Products</strong>
                            </div>
                            <span>{rows.length} product(s)</span>
                        </div>
                        <div className="rr-quote-window-body">
                            <div ref={productBox} className="rr-quote-product-search">
                                <SearchInput
                                    inputRef={productInput}
                                    value={productSearch}
                                    placeholder="Search product, code, barcode, model or part number..."
                                    loading={productLoading}
                                    onFocus={() => setProductOpen(true)}
                                    onChange={(value) => {
                                        setProductSearch(value);
                                        setProductOpen(true);
                                        setProductError("");
                                    }}
                                    onClear={() => {
                                        setProductSearch("");
                                        setProductResults([]);
                                        setProductOpen(false);
                                    }}
                                />
                                {productOpen && productSearch.trim() && (
                                    <LookupResults
                                        product
                                        results={productResults}
                                        loading={productLoading}
                                        minimumReached={productSearch.trim().length >= 2}
                                        render={(item) => ({
                                            title: item.product_name,
                                            meta: productMeta(item),
                                            extra: `${formatMoney(productPrice(item))} · GST ${productGst(item)}%`,
                                        })}
                                        onSelect={addProduct}
                                        onCancel={() => {
                                            setProductSearch("");
                                            setProductResults([]);
                                            setProductOpen(false);
                                            productInput.current?.focus();
                                        }}
                                    />
                                )}
                            </div>
                            <ErrorText message={productError || errors.product_id} />
                        </div>

                        <div className="rr-quote-table-wrap">
                            <table className="rr-quote-items-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Product / Model</th>
                                        <th className="number">Qty</th>
                                        <th className="number">Free</th>
                                        <th className="number">Rate</th>
                                        <th>Discount</th>
                                        <th className="number">Taxable</th>
                                        {data.billing_type === "gst" && <th>GST</th>}
                                        <th className="number">Amount</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {rows.length ? (
                                        rows.map((row, index) => {
                                            const value = calculateRow(row, data.billing_type);
                                            return (
                                                <tr key={row.product.id}>
                                                    <td>{index + 1}</td>
                                                    <td>
                                                        <strong>{row.product.product_name}</strong>
                                                        <span>{productMeta(row.product)}</span>
                                                    </td>
                                                    <NumberCell
                                                        value={row.quantity}
                                                        min="1"
                                                        step="1"
                                                        onChange={(value) =>
                                                            updateRow(index, "quantity", value)
                                                        }
                                                        error={errors[`qty.${index}`]}
                                                    />
                                                    <NumberCell
                                                        value={row.free_quantity}
                                                        min="0"
                                                        step="1"
                                                        onChange={(value) =>
                                                            updateRow(index, "free_quantity", value)
                                                        }
                                                    />
                                                    <NumberCell
                                                        value={row.sale_price}
                                                        min="0"
                                                        onChange={(value) =>
                                                            updateRow(index, "sale_price", value)
                                                        }
                                                        error={errors[`sale_price.${index}`]}
                                                    />
                                                    <td>
                                                        <div className="rr-quote-discount-input">
                                                            <select
                                                                value={row.discount_type}
                                                                onChange={(event) =>
                                                                    updateRow(
                                                                        index,
                                                                        "discount_type",
                                                                        event.target.value,
                                                                    )
                                                                }
                                                            >
                                                                <option value="fixed">₹</option>
                                                                <option value="percentage">%</option>
                                                            </select>
                                                            <input
                                                                type="number"
                                                                min="0"
                                                                step="0.01"
                                                                value={row.discount_value}
                                                                onFocus={(event) => event.target.select()}
                                                                onChange={(event) =>
                                                                    updateRow(
                                                                        index,
                                                                        "discount_value",
                                                                        event.target.value,
                                                                    )
                                                                }
                                                            />
                                                        </div>
                                                        <ErrorText
                                                            message={errors[`discount_value.${index}`]}
                                                        />
                                                    </td>
                                                    <td className="number">
                                                        {formatMoney(value.taxable)}
                                                    </td>
                                                    {data.billing_type === "gst" && (
                                                        <td>
                                                            <div className="rr-quote-gst-input">
                                                                <input
                                                                    type="number"
                                                                    min="0"
                                                                    max="100"
                                                                    step="0.01"
                                                                    value={row.gst_percent}
                                                                    onChange={(event) =>
                                                                        updateRow(
                                                                            index,
                                                                            "gst_percent",
                                                                            event.target.value,
                                                                        )
                                                                    }
                                                                />
                                                                <span>{formatMoney(value.gst)}</span>
                                                            </div>
                                                            <ErrorText
                                                                message={errors[`gst_percent.${index}`]}
                                                            />
                                                        </td>
                                                    )}
                                                    <td className="number amount">
                                                        {formatMoney(value.total)}
                                                    </td>
                                                    <td>
                                                        <button
                                                            type="button"
                                                            className="rr-quote-remove"
                                                            title="Remove product"
                                                            onClick={() => removeRow(index)}
                                                        >
                                                            <i className="bi bi-x-lg"></i>
                                                        </button>
                                                    </td>
                                                </tr>
                                            );
                                        })
                                    ) : (
                                        <tr>
                                            <td
                                                colSpan={data.billing_type === "gst" ? 10 : 9}
                                                className="rr-quote-items-empty"
                                            >
                                                Search and select products to prepare the document.
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <div className="rr-quote-bottom-grid">
                        <section className="rr-quote-window">
                            <WindowTitle icon="bi-card-text" title="Terms & Notes" />
                            <div className="rr-quote-window-body rr-quote-notes-grid">
                                <Field
                                    label="Terms and Conditions"
                                    error={errors.terms_conditions}
                                >
                                    <textarea
                                        rows="4"
                                        value={data.terms_conditions}
                                        onChange={(event) =>
                                            setData("terms_conditions", event.target.value)
                                        }
                                    ></textarea>
                                </Field>
                                <Field
                                    label="Internal / Customer Remarks"
                                    error={errors.remarks}
                                >
                                    <textarea
                                        rows="4"
                                        value={data.remarks}
                                        onChange={(event) =>
                                            setData("remarks", event.target.value)
                                        }
                                    ></textarea>
                                </Field>
                            </div>
                        </section>

                        <section className="rr-quote-window">
                            <WindowTitle icon="bi-calculator" title="Document Summary" />
                            <div className="rr-quote-summary">
                                <SummaryRow label="Quantity" value={formatQty(totals.quantity)} />
                                <SummaryRow
                                    label="Free Quantity"
                                    value={formatQty(totals.freeQuantity)}
                                />
                                <SummaryRow
                                    label="Item Discount"
                                    value={formatMoney(totals.discount)}
                                />
                                <SummaryRow
                                    label="Taxable Amount"
                                    value={formatMoney(totals.taxable)}
                                />
                                {data.billing_type === "gst" && (
                                    <SummaryRow label="GST" value={formatMoney(totals.gst)} />
                                )}
                                <div className="rr-quote-final-discount">
                                    <label>Final Discount</label>
                                    <input
                                        type="number"
                                        min="0"
                                        max={totals.amount}
                                        step="0.01"
                                        value={data.final_discount}
                                        onFocus={(event) => event.target.select()}
                                        onChange={(event) =>
                                            setData("final_discount", event.target.value)
                                        }
                                    />
                                </div>
                                <ErrorText message={errors.final_discount} />
                                <SummaryRow label="Round Off" value={formatMoney(roundOff)} />
                                <SummaryRow
                                    label="Grand Total"
                                    value={formatMoney(grandTotal)}
                                    grand
                                />
                            </div>
                        </section>
                    </div>

                    <footer className="rr-quote-form-actions">
                        <div>
                            <i className="bi bi-info-circle"></i>
                            Review customer, products and totals before saving.
                        </div>

                        <div className="rr-quote-form-action-buttons">
                            <Link href={backUrl} className="rr-quote-btn cancel">
                                <i className="bi bi-x-lg"></i>
                                Cancel
                            </Link>

                            <button
                                type="button"
                                className="rr-quote-btn draft"
                                disabled={processing}
                                onClick={(event) => submit(event, "Draft")}
                            >
                                <i className="bi bi-file-earmark"></i>
                                {editing ? "Save as Draft" : "Save Draft"}
                            </button>

                            <button
                                type="button"
                                className="rr-quote-btn save"
                                disabled={processing}
                                onClick={(event) =>
                                    submit(event, editing ? data.status : "Pending")
                                }
                            >
                                <i className="bi bi-check2-circle"></i>
                                {processing
                                    ? "Saving..."
                                    : editing
                                        ? "Update Document"
                                        : "Save & Submit"}
                            </button>
                        </div>
                    </footer>
                </form>

                {quickModal === "customer" && (
                    <QuickModal
                        title="Quick Add Customer"
                        icon="bi-person-plus"
                        processing={quickProcessing}
                        error={quickError}
                        onClose={() => !quickProcessing && setQuickModal(null)}
                        onSubmit={saveQuickCustomer}
                    >
                        <QuickFields
                            type="customer"
                            data={quickCustomer}
                            setData={setQuickCustomer}
                        />
                    </QuickModal>
                )}

                {quickModal === "mechanic" && (
                    <QuickModal
                        title="Quick Add Mechanic"
                        icon="bi-tools"
                        processing={quickProcessing}
                        error={quickError}
                        onClose={() => !quickProcessing && setQuickModal(null)}
                        onSubmit={saveQuickMechanic}
                    >
                        <QuickFields
                            type="mechanic"
                            data={quickMechanic}
                            setData={setQuickMechanic}
                        />
                    </QuickModal>
                )}
            </div>
        </>
    );
}

function useDebouncedSearch({
    value,
    selected,
    timer,
    endpoint,
    resultKey,
    setResults,
    setOpen,
    setLoading,
    setError = () => { },
    enabled = true,
}) {
    useEffect(() => {
        if (timer.current) window.clearTimeout(timer.current);
        const search = value.trim();

        if (!enabled || selected || search.length < 2) {
            setResults([]);
            setLoading(false);
            return;
        }

        timer.current = window.setTimeout(async () => {
            setLoading(true);
            setError("");
            try {
                const response = await axios.get(endpoint, { params: { search } });
                setResults(
                    Array.isArray(response.data?.[resultKey])
                        ? response.data[resultKey]
                        : [],
                );
                setOpen(true);
            } catch (error) {
                setResults([]);
                setError(apiError(error, "Search failed."));
            } finally {
                setLoading(false);
            }
        }, 300);

        return () => window.clearTimeout(timer.current);
    }, [value, selected, enabled]);
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

function SearchInput({
    inputRef,
    value,
    placeholder,
    loading,
    onFocus,
    onChange,
    onClear,
}) {
    return (
        <div className="rr-quote-lookup-input">
            <i className="bi bi-search"></i>
            <input
                ref={inputRef}
                type="search"
                value={value}
                placeholder={placeholder}
                autoComplete="off"
                onFocus={onFocus}
                onChange={(event) => onChange(event.target.value)}
            />
            {loading && <span className="spinner-border spinner-border-sm"></span>}
            {value && onClear && (
                <button type="button" title="Clear search" onClick={onClear}>
                    <i className="bi bi-x-lg"></i>
                </button>
            )}
        </div>
    );
}

function LookupResults({
    results,
    loading,
    minimumReached,
    render,
    onSelect,
    onCancel,
    product = false,
}) {
    return (
        <div className={`rr-quote-results ${product ? "product" : ""}`}>
            {product && (
                <div className="rr-quote-product-result-title">
                    <span>
                        <i className="bi bi-box-seam"></i>
                        Search Results
                    </span>
                    <button type="button" onClick={onCancel}>
                        <i className="bi bi-x-lg"></i>
                        Cancel
                    </button>
                </div>
            )}
            {!minimumReached ? (
                <div className="rr-quote-result-message">
                    Type at least two characters.
                </div>
            ) : results.length ? (
                results.map((item) => {
                    const content = render(item);
                    return (
                        <button key={item.id} type="button" onClick={() => onSelect(item)}>
                            <div>
                                <strong>{content.title}</strong>
                                <span>{content.meta || "No additional details"}</span>
                            </div>
                            {content.extra && <em>{content.extra}</em>}
                            <i className="bi bi-plus-circle"></i>
                        </button>
                    );
                })
            ) : !loading ? (
                <div className="rr-quote-result-message">No matching result.</div>
            ) : null}
        </div>
    );
}

function SelectedParty({ icon, name, meta, onClear }) {
    return (
        <div className="rr-quote-selected-party">
            <i className={`bi ${icon}`}></i>
            <div>
                <strong>{name}</strong>
                <span>{meta || "Selected"}</span>
            </div>
            <button type="button" title="Change selection" onClick={onClear}>
                Change
            </button>
        </div>
    );
}

function Field({ label, error, children }) {
    return (
        <label className="rr-quote-create-field">
            <span>{label}</span>
            {children}
            <ErrorText message={error} />
        </label>
    );
}

function NumberCell({ value, min, step = "0.01", onChange, error }) {
    return (
        <td className="number">
            <input
                type="number"
                min={min}
                step={step}
                value={value}
                onFocus={(event) => event.target.select()}
                onChange={(event) => onChange(event.target.value)}
            />
            <ErrorText message={error} />
        </td>
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

function QuickModal({
    title,
    icon,
    processing,
    error,
    onClose,
    onSubmit,
    children,
}) {
    return (
        <div className="rr-quote-modal-backdrop" role="presentation">
            <form className="rr-quote-modal" onSubmit={onSubmit}>
                <div className="rr-quote-modal-title">
                    <div>
                        <i className={`bi ${icon}`}></i>
                        <strong>{title}</strong>
                    </div>
                    <button type="button" disabled={processing} onClick={onClose}>
                        <i className="bi bi-x-lg"></i>
                    </button>
                </div>
                <div className="rr-quote-modal-body">
                    {children}
                    <ErrorText message={error} />
                </div>
                <div className="rr-quote-modal-actions">
                    <button type="button" disabled={processing} onClick={onClose}>
                        Cancel
                    </button>
                    <button type="submit" className="primary" disabled={processing}>
                        {processing ? "Saving..." : "Save & Select"}
                    </button>
                </div>
            </form>
        </div>
    );
}

function QuickFields({ type, data, setData }) {
    const update = (field, value) =>
        setData((previous) => ({ ...previous, [field]: value }));

    return (
        <div className="rr-quote-quick-grid">
            <Field label={type === "customer" ? "Customer Name *" : "Mechanic Name *"}>
                <input
                    required
                    value={
                        type === "customer" ? data.customer_name : data.mechanic_name
                    }
                    onChange={(event) =>
                        update(
                            type === "customer" ? "customer_name" : "mechanic_name",
                            event.target.value,
                        )
                    }
                />
            </Field>
            <Field label="Mobile">
                <input
                    value={data.mobile}
                    onChange={(event) => update("mobile", event.target.value)}
                />
            </Field>
            <Field label="Email">
                <input
                    type="email"
                    value={data.email}
                    onChange={(event) => update("email", event.target.value)}
                />
            </Field>
            {type === "customer" ? (
                <Field label="GST Number">
                    <input
                        value={data.gst_number}
                        onChange={(event) => update("gst_number", event.target.value)}
                    />
                </Field>
            ) : (
                <Field label="City">
                    <input
                        value={data.city}
                        onChange={(event) => update("city", event.target.value)}
                    />
                </Field>
            )}
            <Field label="Address">
                <textarea
                    rows="2"
                    value={data.address}
                    onChange={(event) => update("address", event.target.value)}
                ></textarea>
            </Field>
        </div>
    );
}

function ErrorText({ message }) {
    return message ? <div className="rr-quote-error">{message}</div> : null;
}

function initialRows(quotation) {
    const items = Array.isArray(quotation?.items) ? quotation.items : [];

    return items
        .filter((item) => item?.product)
        .map((item) => ({
            item_id: item.id,
            product: item.product,
            quantity: String(item.quantity ?? "1"),
            free_quantity: String(item.free_quantity ?? "0"),
            sale_price: String(item.sale_price ?? "0"),
            discount_type: item.discount_type ?? "fixed",
            discount_value: String(item.discount_value ?? "0"),
            gst_percent: String(
                item.gst_percent ?? item.product?.gst_percent ?? "0",
            ),
        }));
}

function dateInput(value) {
    return value ? String(value).slice(0, 10) : "";
}

function calculateRow(row, billingType) {
    const gross = round2(numberValue(row.quantity) * numberValue(row.sale_price));
    let discount =
        row.discount_type === "percentage"
            ? round2((gross * numberValue(row.discount_value)) / 100)
            : numberValue(row.discount_value);
    discount = Math.min(gross, discount);
    const taxable = round2(Math.max(0, gross - discount));
    const gstPercent = billingType === "gst" ? numberValue(row.gst_percent) : 0;
    const gst = round2((taxable * gstPercent) / 100);

    return { gross, discount, taxable, gst, total: round2(taxable + gst) };
}

function calculateCommission(data, base) {
    if (data.sale_source !== "mechanic") return 0;
    return data.commission_type === "percentage"
        ? round2((base * numberValue(data.commission_percent)) / 100)
        : numberValue(data.commission_amount);
}

function productPrice(product) {
    return numberValue(
        product.sale_price ?? product.selling_price ?? product.mrp ?? 0,
    );
}

function productGst(product) {
    return numberValue(product.gst_percent ?? product.gst_rate ?? 0);
}

function productMeta(product) {
    return [
        product.product_code,
        product.model,
        product.model_name,
        product.part_number,
        product.brand?.brand_name ?? product.brand?.name,
    ]
        .filter(Boolean)
        .join(" · ");
}

function emptyCustomer() {
    return { customer_name: "", mobile: "", email: "", gst_number: "", address: "" };
}

function emptyMechanic() {
    return { mechanic_name: "", mobile: "", email: "", address: "", city: "" };
}

function apiError(error, fallback) {
    const validation = error?.response?.data?.errors;
    const first = validation ? Object.values(validation).flat()[0] : null;
    return first || error?.response?.data?.message || fallback;
}

function numberValue(value) {
    const number = Number(value ?? 0);
    return Number.isFinite(number) ? number : 0;
}

function round2(value) {
    return Math.round((numberValue(value) + Number.EPSILON) * 100) / 100;
}

function formatMoney(value) {
    return new Intl.NumberFormat("en-IN", {
        style: "currency",
        currency: "INR",
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(numberValue(value));
}

function formatQty(value) {
    return numberValue(value).toLocaleString("en-IN", {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    });
}

function today() {
    const now = new Date();
    return new Date(now.getTime() - now.getTimezoneOffset() * 60000)
        .toISOString()
        .slice(0, 10);
}

function dateAfter(days) {
    const date = new Date();
    date.setDate(date.getDate() + days);
    return new Date(date.getTime() - date.getTimezoneOffset() * 60000)
        .toISOString()
        .slice(0, 10);
}

Create.layout = (page) => <BusinessLayout>{page}</BusinessLayout>;
