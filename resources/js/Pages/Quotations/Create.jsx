import React, { useEffect, useMemo, useRef, useState } from "react";

import { createPortal } from "react-dom";

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
    const [activeCustomer, setActiveCustomer] = useState(0);
    const [activeMechanic, setActiveMechanic] = useState(0);
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

    const [shortcutsOpen, setShortcutsOpen] = useState(false);
    const [activeProductRow, setActiveProductRow] = useState(null);
    const lookupAnchor = useRef(null);
    const queuedProductEnter = useRef(false);
    const [activeProduct, setActiveProduct] = useState(0);
    const [saving, setSaving] = useState(false);
    const saveLock = useRef(false);
    const submitRef = useRef(null);
    const tableRef = useRef(null);
    const printRequested = useRef(false);
    const [oneQuantity, setOneQuantity] = useState(true);
    useEffect(() => {
        const onKey = (event) => {
            if (!["F2", "F9", "F10"].includes(event.key)) return;
            event.preventDefault();
            if (event.repeat || saveLock.current || document.querySelector(".rr-quote-modal-backdrop")) return;
            if (event.key === "F2") {
                const input = productInput.current;
                const row = input?.closest("tr");
                row?.scrollIntoView({ behavior: "smooth", block: "center", inline: "nearest" });
                input?.focus({ preventScroll: true });
                input?.select();
                return;
            }
            tableRef.current?.querySelector("input:not(:disabled)")?.focus();
            printRequested.current = event.key === "F10";
            submitRef.current?.({ preventDefault() { } });
        };
        document.addEventListener("keydown", onKey);
        return () => document.removeEventListener("keydown", onKey);
    }, []);
    const moveCell = (event) => {
        if (event.target.type === "search" || !["INPUT", "SELECT"].includes(event.target.tagName)) return;
        if (!["Enter", "Escape", "ArrowLeft", "ArrowRight"].includes(event.key)) return;
        if (event.ctrlKey || event.metaKey || event.altKey) return;
        event.preventDefault();
        const controls = Array.from(tableRef.current.querySelectorAll("tbody input:not(:disabled), tbody select:not(:disabled)")).filter((input) => input.type !== "search");
        const direction = event.key === "ArrowLeft" || (event.key === "Enter" && event.shiftKey) ? -1 : 1;
        const next = controls[controls.indexOf(event.target) + direction];
        if (next) { next.focus(); next.select?.(); }
        else productInput.current?.focus();
    };

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
        allowEmpty: true,
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
            if (!productBox.current?.contains(event.target) && !event.target.closest(".rr-quote-results.product, .rr-quote-row-search")) setProductOpen(false);
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
        if (activeProductRow !== null && rows.some((row, index) => index !== activeProductRow && Number(row.product.id) === Number(product.id))) {
            setProductError("This product is already in another row. Change its quantity there.");
            return;
        }
        const chosenRow = activeProductRow;
        setRows((previous) => {
            const replacement = { product, quantity: "1", free_quantity: "0", sale_price: String(productPrice(product)), discount_type: "fixed", discount_value: "0", gst_percent: String(effectiveGst(productGst(product))) };
            if (chosenRow !== null) return previous.map((row, index) => index === chosenRow ? replacement : row);
            const existing = previous.findIndex((row) => Number(row.product.id) === Number(product.id));
            if (existing >= 0) return previous.map((row, index) => index === existing ? { ...row, quantity: String(numberValue(row.quantity) + 1) } : row);
            return [...previous, { product, quantity: "1", free_quantity: "0", sale_price: String(productPrice(product)), discount_type: "fixed", discount_value: "0", gst_percent: String(effectiveGst(productGst(product))) }];
        });
        setActiveProductRow(null);
        setProductSearch("");
        setProductResults([]);
        setProductOpen(false);
        setProductError("");
        window.setTimeout(() => {
            if (oneQuantity) productInput.current?.focus();
            else { const targetRows = tableRef.current?.querySelectorAll("tbody tr[data-product]"); const target = chosenRow !== null ? targetRows?.[chosenRow] : targetRows?.[targetRows.length - 1]; target?.querySelector("input[type='number']")?.focus(); }
        }, 0);
    };

    useEffect(() => {
        if (!queuedProductEnter.current || productLoading) return;
        queuedProductEnter.current = false;
        if (productResults.length === 1) addProduct(productResults[0]);
    }, [productResults, productLoading]);

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
        if (saveLock.current || processing) return;

        if (!customer) {
            printRequested.current = false;
            setCustomerError("Select a customer before saving.");
            customerBox.current?.scrollIntoView({ behavior: "smooth", block: "center" });
            window.setTimeout(
                () => customerBox.current?.querySelector("input")?.focus(),
                250,
            );
            return;
        }

        if (data.sale_source === "mechanic" && !mechanic) {
            printRequested.current = false;
            mechanicBox.current?.scrollIntoView({ behavior: "smooth", block: "center" });
            window.setTimeout(
                () => mechanicBox.current?.querySelector("input")?.focus(),
                250,
            );
            return;
        }
        if (!rows.length) {
            printRequested.current = false;
            setProductError("Add at least one product.");
            productInput.current?.focus();
            return;
        }

        transform((payload) => ({
            ...payload,
            status,
            product_id: rows.map((row) => row.product.id),
            qty: rows.map((row) => row.quantity),
            free_qty: rows.map((row) => row.free_quantity),
            price_includes_gst: true,
            sale_price: rows.map((row) => row.sale_price),
            discount_type: rows.map((row) => row.discount_type),
            discount_value: rows.map((row) => row.discount_value),
            gst_percent: rows.map((row) => data.billing_type === "gst" ? effectiveGst(row.gst_percent) : 0),
        }));

        const wantsPrint = printRequested.current;
        printRequested.current = false;
        const popup = wantsPrint ? window.open("", "_blank") : null;
        if (popup) {
            popup.document.write('<!doctype html><html><head><title>Preparing document</title><style>@keyframes spin{to{transform:rotate(360deg)}}body{margin:0;display:grid;place-items:center;min-height:100vh;background:#f0f5fc;color:#25486d;font-family:Segoe UI,Arial}.spinner{width:42px;height:42px;border:4px solid #dae7f7;border-top-color:#2868ae;border-radius:50%;animation:spin .8s linear infinite;margin:0 auto}</style></head><body><div style="text-align:center"><div class="spinner"></div><h3>Saving your document…</h3><p>Your print preview will open when ready.</p></div></body></html>'); popup.document.close();
        }
        saveLock.current = true; setSaving(true);
        const options = {
            preserveScroll: true,
            onSuccess: async (page) => {
                if (!wantsPrint) return;
                if (!popup) { window.alert("Document saved. Allow popups and print from the saved document."); return; }
                const match = new URL(page.url, window.location.origin).pathname.match(/\/quotations\/(\d+)\/?$/);
                const id = quotation?.id ?? page.props.quotation?.id ?? match?.[1];
                if (!id) { popup.document.body.textContent = "Document saved. Open it from the register and select Print."; return; }
                try {
                    const url = new URL(`/quotations/${id}/print?paper=a4`, window.location.origin);
                    const response = await fetch(url, { credentials: "same-origin", headers: { Accept: "text/html" } });
                    if (!response.ok || new URL(response.url).pathname !== url.pathname) throw new Error("Print unavailable");
                    const html = await response.text(); if (popup.closed) return;
                    popup.history.replaceState(null, "", url.href); popup.document.open(); popup.document.write(html); popup.document.close();
                } catch { if (!popup.closed) popup.document.body.textContent = "Document saved. Print could not load. Open the saved document and try Print again; do not save another copy."; }
            },
            onError: () => { if (popup && !popup.closed) popup.close(); },
            onFinish: () => { saveLock.current = false; setSaving(false); },
        };
        if (editing) put(`/quotations/${quotation.id}`, options);
        else post("/quotations", options);
    };

    submitRef.current = (event) => submit(event, editing ? data.status : "Pending");

    const documentTitle =
        data.document_type === "proforma_invoice"
            ? "Proforma Invoice"
            : "Quotation";
    const title = editing ? `Edit ${documentTitle}` : `New ${documentTitle}`;
    const backUrl = editing ? `/quotations/${quotation.id}` : "/quotations";

    return (
        <>
            <Head title={title} />

            {(saving || processing) && typeof document !== "undefined" && createPortal(<div className="rr-quote-save-overlay" role="status" aria-live="polite"><div><span className="rr-quote-save-spinner" /><strong>{editing ? "Updating document…" : "Saving document…"}</strong><p>Please wait while your quotation is saved.</p></div></div>, document.body)}
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

                <form ref={formRef} onSubmit={(event) => submit(event)} onKeyDown={(event) => {
                    if (event.defaultPrevented || event.key !== "Enter" || event.target.tagName === "BUTTON" || event.target.tagName === "A") return;
                    if (event.target.type === "search" || event.target.type === "checkbox") return;
                    if (event.target.tagName === "TEXTAREA" && event.shiftKey) return;
                    event.preventDefault();
                    const controls = Array.from(formRef.current.querySelectorAll("input:not(:disabled), select:not(:disabled), textarea:not(:disabled), button.save:not(:disabled)"))
                        .filter((element) => element.offsetParent !== null && element.type !== "checkbox" && element.type !== "hidden");
                    const position = controls.indexOf(event.target);
                    let next = controls[position + (event.shiftKey ? -1 : 1)];
                    if (!next || next.matches("button.save")) next = formRef.current.querySelector(".rr-quote-form-actions button.save") || formRef.current.querySelector("button.save");
                    next?.focus(); next?.select?.();
                }}>
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
                        <button type="button" className="rr-quote-btn print" disabled={processing || saving} onClick={(event) => { printRequested.current = true; submit(event, editing ? data.status : "Pending"); }}><i className="bi bi-printer"></i> Save & Print <kbd>F10</kbd></button>
                    </header>

                    <section className="rr-quote-shortcuts"><button type="button" onClick={() => setShortcutsOpen((value) => !value)} aria-expanded={shortcutsOpen} aria-controls="quotation-shortcut-notes"><i className="bi bi-keyboard"></i> Billing shortcuts <span className={`rr-quote-shortcut-switch ${shortcutsOpen ? "on" : ""}`}><i className={`bi ${shortcutsOpen ? "bi-toggle-on" : "bi-toggle-off"}`}></i>{shortcutsOpen ? "On" : "Off"}</span></button>{shortcutsOpen && <div id="quotation-shortcut-notes"><span><kbd>F2</kbd> Product search</span><span><kbd className="purple">Enter / Esc / →</kbd> Next field</span><span><kbd className="purple">← / Shift+Enter</kbd> Previous field</span><span><kbd className="green">F9</kbd> Save</span><span><kbd className="orange">F10</kbd> Save & print</span></div>}</section>
                    <label className="rr-quote-fast-mode"><input type="checkbox" checked={oneQuantity} onChange={(event) => setOneQuantity(event.target.checked)} /> Fast billing: quantity 1, return to product search</label>
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
                                            onKeyDown={(event) => {
                                                if (["ArrowDown", "ArrowUp"].includes(event.key)) { event.preventDefault(); setCustomerOpen(true); setActiveCustomer((value) => Math.max(0, Math.min(customerResults.length - 1, value + (event.key === "ArrowDown" ? 1 : -1)))); }
                                                if (event.key === "Enter") { event.preventDefault(); if (!customerLoading && customerResults[activeCustomer]) selectCustomer(customerResults[activeCustomer]); }
                                                if (event.key === "Escape") { event.preventDefault(); setCustomerOpen(false); }
                                            }}
                                            onChange={(value) => {
                                                setActiveCustomer(0); setCustomerResults([]); setCustomerLoading(true);
                                                setCustomerSearch(value);
                                                setCustomerOpen(true);
                                            }}
                                        />
                                        {customerOpen && (
                                            <LookupResults
                                                activeIndex={activeCustomer}
                                                results={customerResults}
                                                loading={customerLoading}
                                                minimumReached={customerSearch.trim().length === 0 || customerSearch.trim().length >= 2}
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
                                        type="date" onClick={(event) => { try { event.currentTarget.showPicker?.(); } catch { } }}
                                        value={data.quotation_date}
                                        onChange={(event) =>
                                            setData("quotation_date", event.target.value)
                                        }
                                    />
                                </Field>
                                <Field label="Valid Until" error={errors.valid_until}>
                                    <input
                                        type="date" onClick={(event) => { try { event.currentTarget.showPicker?.(); } catch { } }}
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
                                                    onKeyDown={(event) => {
                                                        if (["ArrowDown", "ArrowUp"].includes(event.key)) { event.preventDefault(); setMechanicOpen(true); setActiveMechanic((value) => Math.max(0, Math.min(mechanicResults.length - 1, value + (event.key === "ArrowDown" ? 1 : -1)))); }
                                                        if (event.key === "Enter") { event.preventDefault(); if (!mechanicLoading && mechanicResults[activeMechanic]) selectMechanic(mechanicResults[activeMechanic]); }
                                                        if (event.key === "Escape") { event.preventDefault(); setMechanicOpen(false); }
                                                    }}
                                                    onChange={(value) => {
                                                        setActiveMechanic(0); setMechanicResults([]); setMechanicLoading(true);
                                                        setMechanicSearch(value);
                                                        setMechanicOpen(true);
                                                    }}
                                                />
                                                {mechanicOpen && mechanicSearch.trim() && (
                                                    <LookupResults
                                                        activeIndex={activeMechanic}
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


                        <div ref={tableRef} className="rr-quote-table-wrap" onKeyDown={moveCell}>
                            <table className="rr-quote-items-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Product / Model</th>
                                        <th className="number">Qty</th>
                                        <th className="number">Free</th>
                                        <th className="number">Net rate</th>
                                        {data.billing_type === "gst" && <th className="number">Rate excl. GST</th>}
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
                                                <tr key={row.product.id} data-product={row.product.id}>
                                                    <td>{index + 1}</td>
                                                    <td>
                                                        <div className="rr-quote-product-identity"><strong>{row.product.product_name}</strong>
                                                            <div>{row.product.part_number && <span className="rr-quote-part-tag"><i className="bi bi-upc"></i>{row.product.part_number}</span>}{row.product.product_code && <span className="rr-quote-code-tag">{row.product.product_code}</span>}</div>
                                                            {(row.product.model || row.product.model_name) && <small>{row.product.model || row.product.model_name}</small>}</div>
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
                                                    {data.billing_type === "gst" && <td className="number">{formatMoney(numberValue(row.sale_price) / (1 + effectiveGst(row.gst_percent) / 100))}</td>}
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
                                    ) : null}
                                    <tr className="rr-quote-draft-row">
                                        <td className="serial">{rows.length + 1}</td>
                                        <td className="rr-quote-draft-product">
                                            <div ref={productBox} className="rr-quote-product-search">
                                                <SearchInput
                                                    inputRef={productInput}
                                                    onKeyDown={(event) => {
                                                        if (["ArrowDown", "ArrowUp"].includes(event.key)) { event.preventDefault(); setProductOpen(true); setActiveProduct((value) => Math.max(0, Math.min(productResults.length - 1, value + (event.key === "ArrowDown" ? 1 : -1)))); }
                                                        if (event.key === "Enter") { event.preventDefault(); if (!productSearch.trim()) { formRef.current?.querySelector(".rr-quote-bottom-grid textarea, .rr-quote-bottom-grid input")?.focus(); return; } if (productLoading) { queuedProductEnter.current = true; return; } if (productOpen && productResults[activeProduct]) addProduct(productResults[activeProduct]); }
                                                        if (event.key === "Escape") { event.preventDefault(); setProductOpen(false); }
                                                        if (event.key === "ArrowLeft" && !productSearch) { event.preventDefault(); const controls = tableRef.current?.querySelectorAll("tr[data-product] input, tr[data-product] select"); controls?.[controls.length - 1]?.focus(); }
                                                    }}
                                                    value={activeProductRow === null ? productSearch : ""}
                                                    placeholder="Search product, code, barcode, model or part number..."
                                                    loading={productLoading}
                                                    onFocus={(event) => { lookupAnchor.current = productBox.current; if (activeProductRow !== null) { setActiveProductRow(null); setProductSearch(""); setProductResults([]); setProductOpen(false); } else setProductOpen(true); }}
                                                    onChange={(value) => {
                                                        lookupAnchor.current = productBox.current;
                                                        setActiveProductRow(null);
                                                        queuedProductEnter.current = false; setProductResults([]); setProductLoading(true); setActiveProduct(0);
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
                                                        activeIndex={activeProduct}
                                                        anchorRef={lookupAnchor}
                                                        results={productResults}
                                                        loading={productLoading}
                                                        minimumReached={productSearch.trim().length >= 2}
                                                        render={(item) => ({
                                                            title: item.product_name,
                                                            meta: productMeta(item),
                                                            extra: productSearchExtra(item),
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
                                        </td>
                                        <td className="number"><span className="rr-quote-draft-value">1</span></td>
                                        <td className="number">0</td>
                                        <td className="number">—</td>
                                        {data.billing_type === "gst" && <td className="number">—</td>}
                                        <td className="number">—</td>
                                        <td className="number">—</td>
                                        {data.billing_type === "gst" && <td className="number">—</td>}
                                        <td className="number">—</td>
                                        <td className="rr-quote-draft-plus"><i className="bi bi-plus-circle" aria-hidden="true"></i></td>
                                    </tr>
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
                        <button type="button" className="rr-quote-btn print" disabled={processing || saving} onClick={(event) => { printRequested.current = true; submit(event, editing ? data.status : "Pending"); }}><i className="bi bi-printer"></i> Save & Print <kbd>F10</kbd></button>
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
    allowEmpty = false,
}) {
    useEffect(() => {
        if (timer.current) window.clearTimeout(timer.current);
        const search = value.trim();

        if (!enabled || selected || (search.length < 2 && !(allowEmpty && search.length === 0))) {
            setResults([]);
            setLoading(false);
            return;
        }

        const controller = new AbortController();
        setResults([]); setLoading(true);
        timer.current = window.setTimeout(async () => {
            setLoading(true);
            setError("");
            try {
                const response = await axios.get(endpoint, { params: { search }, signal: controller.signal });
                if (controller.signal.aborted) return;
                setResults(
                    Array.isArray(response.data?.[resultKey])
                        ? response.data[resultKey]
                        : [],
                );
                if (search.length) setOpen(true);
            } catch (error) {
                if (controller.signal.aborted) return;
                setResults([]);
                setError(apiError(error, "Search failed."));
            } finally {
                if (!controller.signal.aborted) setLoading(false);
            }
        }, 180);

        return () => { controller.abort(); window.clearTimeout(timer.current); };
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
    onKeyDown,
}) {
    return (
        <div className="rr-quote-lookup-input rr-quote-search-control">
            <i className="bi bi-search"></i>
            <input
                ref={inputRef}
                type="search"
                value={value}
                placeholder={placeholder}
                autoComplete="off"
                onFocus={onFocus}
                onKeyDown={onKeyDown}
                onChange={(event) => onChange(event.target.value)}
            />
            {loading && <span className="spinner-border spinner-border-sm rr-quote-lookup-spinner" aria-label="Searching"></span>}
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
    activeIndex = -1,
    anchorRef,
}) {
    const [bounds, setBounds] = useState(null);
    useEffect(() => {
        if (!product || !anchorRef) return;
        const position = () => {
            const box = anchorRef.current?.getBoundingClientRect();
            if (!box) return;
            const width = Math.min(Math.max(box.width, 600), window.innerWidth - 24);
            const left = Math.max(12, Math.min(box.left, window.innerWidth - width - 12));
            const below = window.innerHeight - box.bottom - 18;
            const above = box.top - 18;
            const openAbove = below < 200 && above > below;
            const maxHeight = Math.max(80, Math.min(350, openAbove ? above : below));
            setBounds({ position: "fixed", top: openAbove ? Math.max(12, box.top - maxHeight - 5) : box.bottom + 5, left, width, maxHeight, zIndex: 1200, overflowY: "auto" });
        };
        position(); window.addEventListener("resize", position); window.addEventListener("scroll", position, true);
        return () => { window.removeEventListener("resize", position); window.removeEventListener("scroll", position, true); };
    }, [product, anchorRef]);
    const content = (
        <div style={product ? bounds ?? { display: "none" } : undefined} className={`rr-quote-results ${product ? "product" : ""}`}>
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
            {loading ? <div className="rr-quote-result-message rr-quote-result-loading"><span className="spinner-border spinner-border-sm"></span> Searching…</div> : !minimumReached ? (
                <div className="rr-quote-result-message">
                    Type at least two characters.
                </div>
            ) : results.length ? (
                results.map((item, resultIndex) => {
                    const content = render(item);
                    return (
                        <button key={item.id} type="button" className={activeIndex === resultIndex ? "active" : ""} onMouseDown={(event) => event.preventDefault()} onClick={() => onSelect(item)}>
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
    return product && typeof document !== "undefined" ? createPortal(content, document.body) : content;
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
    const modalRef = useRef(null);
    useEffect(() => {
        const previous = document.activeElement;
        modalRef.current?.querySelector("input, select, textarea")?.focus();
        return () => previous?.focus?.();
    }, []);
    return (
        <div className="rr-quote-modal-backdrop rr-quote-quick-backdrop" role="presentation">
            <form ref={modalRef} className="rr-quote-modal rr-quote-quick-modal" role="dialog" aria-modal="true" aria-label={title} onSubmit={onSubmit} onKeyDown={(event) => {
                if (event.key === "Escape" && !processing) { event.preventDefault(); onClose(); }
                if (event.key === "Tab") {
                    const controls = Array.from(modalRef.current.querySelectorAll("button:not(:disabled), input:not(:disabled), select:not(:disabled), textarea:not(:disabled)"));
                    const first = controls[0], last = controls[controls.length - 1];
                    if (event.shiftKey && document.activeElement === first) { event.preventDefault(); last?.focus(); }
                    if (!event.shiftKey && document.activeElement === last) { event.preventDefault(); first?.focus(); }
                }
            }}>
                <div className="rr-quote-modal-title">
                    <div>
                        <i className={`bi ${icon}`}></i>
                        <strong>{title}</strong>
                    </div>
                    <button type="button" aria-label="Close quick add" disabled={processing} onClick={onClose}>
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
                        <i className={processing ? "bi bi-arrow-repeat rr-quote-index-spin" : "bi bi-check2-circle"}></i>{processing ? "Saving…" : "Save & Select"}
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

function savedNetRate(item, billingType) {
    const quantity = numberValue(item.quantity);
    const factor = billingType === "gst" ? 1 + numberValue(item.gst_percent) / 100 : 1;
    if (quantity > 0 && item.line_total != null) {
        const total = numberValue(item.line_total);
        if (item.discount_type === "percentage") {
            const percent = numberValue(item.discount_value);
            if (percent < 100) return Number((total / (1 - percent / 100) / quantity).toFixed(6));
        } else return Number(((total + numberValue(item.discount_value) * factor) / quantity).toFixed(6));
    }
    return numberValue(item.sale_price) * factor;
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
            sale_price: String(savedNetRate(item, quotation?.billing_type)),
            discount_type: item.discount_type ?? "fixed",
            discount_value: String(numberValue(item.discount_value) * (quotation?.billing_type === "gst" && item.discount_type !== "percentage" ? 1 + numberValue(item.gst_percent) / 100 : 1)),
            gst_percent: String(
                item.gst_percent ?? item.product?.gst_percent ?? "0",
            ),
        }));
}

function dateInput(value) {
    return value ? String(value).slice(0, 10) : "";
}

function effectiveGst(value) { return numberValue(value) > 0 ? numberValue(value) : 18; }

function calculateRow(row, billingType) {
    const gross = round2(Math.max(0, numberValue(row.quantity)) * Math.max(0, numberValue(row.sale_price)));
    const discount = Math.min(gross, Math.max(0, row.discount_type === "percentage" ? round2(gross * numberValue(row.discount_value) / 100) : numberValue(row.discount_value)));
    const total = round2(gross - discount);
    const taxable = billingType === "gst" ? round2(total / (1 + effectiveGst(row.gst_percent) / 100)) : total;
    const gst = round2(total - taxable);
    return { gross, discount, taxable, gst, total };
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

function productSearchExtra(product) {
    const details = [
        formatMoney(productPrice(product)),
        `GST ${productGst(product)}%`,
    ];

    const stock =
        product.shop_stock
        ?? product.available_stock
        ?? product.current_stock;

    if (stock !== undefined && stock !== null) {
        details.push(`Available ${formatQty(stock)}`);
    }

    return details.join(" · ");
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
