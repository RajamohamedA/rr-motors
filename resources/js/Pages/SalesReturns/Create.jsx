import React, { useEffect, useMemo, useRef, useState } from "react";

import { createPortal } from "react-dom";

import axios from "axios";

import { Head, Link, useForm, usePage } from "@inertiajs/react";

import BusinessLayout from "@/Layouts/BusinessLayout";
import MasterNavigation from "../../Components/Business/MasterNavigation";

import "../../../css/business/sales-return.css";

export default function Create({ returnNo = "", accounts = [] }) {
    const { business } = usePage().props;

    const [invoiceSearch, setInvoiceSearch] = useState("");
    const [invoiceResults, setInvoiceResults] = useState([]);
    const [invoiceSearchOpen, setInvoiceSearchOpen] = useState(false);
    const [invoiceSearching, setInvoiceSearching] = useState(false);
    const [invoiceLoading, setInvoiceLoading] = useState(false);
    const [invoiceError, setInvoiceError] = useState("");
    const [selectedSale, setSelectedSale] = useState(null);
    const [rows, setRows] = useState([]);

    const [shortcutsOpen, setShortcutsOpen] = useState(false);
    const [activeInvoice, setActiveInvoice] = useState(0);
    const [saving, setSaving] = useState(false);
    const saveLock = useRef(false);
    const formRef = useRef(null);
    const tableRef = useRef(null);
    const printRequested = useRef(false);
    const submitRef = useRef(null);

    useEffect(() => {
        const onKey = (event) => {
            if (!["F2", "F9", "F10"].includes(event.key)) return;
            event.preventDefault();
            if (saveLock.current || event.repeat) return;
            if (event.key === "F2") { searchInputRef.current?.focus(); return; }
            tableRef.current?.querySelector("input:not(:disabled)")?.focus();
            printRequested.current = event.key === "F10";
            submitRef.current?.({ preventDefault() { } });
        };
        document.addEventListener("keydown", onKey);
        return () => document.removeEventListener("keydown", onKey);
    }, []);

    const moveQuantity = (event) => {
        const keys = ["Enter", "Escape", "ArrowLeft", "ArrowRight"];
        if (!keys.includes(event.key) || event.target.tagName !== "INPUT") return;
        if (event.altKey || event.ctrlKey || event.metaKey) return;
        event.preventDefault();
        const inputs = Array.from(tableRef.current.querySelectorAll("input:not(:disabled)"));
        const direction = event.key === "ArrowLeft" || (event.key === "Enter" && event.shiftKey) ? -1 : 1;
        const next = inputs[inputs.indexOf(event.target) + direction];
        if (next) { next.focus(); next.select(); }
        else if (direction > 0) formRef.current?.querySelector("[name='return_date']")?.focus();
    };

    const searchTimer = useRef(null);
    const searchBoxRef = useRef(null);
    const searchInputRef = useRef(null);

    const { data, setData, post, processing, errors, clearErrors } = useForm({
        sale_id: "",
        return_date: today(),
        account_id: "",
        refund_method: "cash",
        refund_status: "refunded",
        remarks: "",
        sale_item_id: [],
        qty: [],
        free_qty: [],
    });

    const moneyRefundMethods = ["cash", "bank", "original_payment"];
    const requiresRefundAccount =
        data.refund_status === "refunded" &&
        moneyRefundMethods.includes(data.refund_method);

    const availableAccounts = useMemo(() => {
        if (data.refund_method === "cash") {
            return accounts.filter(
                (account) =>
                    normalizeMode(account.payment_mode) === "cash" ||
                    String(account.account_type ?? "").toLowerCase() === "cash",
            );
        }

        if (data.refund_method === "bank") {
            return accounts.filter((account) => {
                const type = String(account.account_type ?? "").toLowerCase();
                const mode = normalizeMode(account.payment_mode);
                return ["bank", "upi", "card"].includes(type) ||
                    ["bank_transfer", "gpay", "upi", "card"].includes(mode);
            });
        }

        return accounts;
    }, [accounts, data.refund_method]);

    useEffect(() => {
        if (!requiresRefundAccount) {
            if (data.account_id !== "") {
                setData("account_id", "");
            }
            return;
        }

        const selectedIsAvailable = availableAccounts.some(
            (account) => String(account.id) === String(data.account_id),
        );

        if (!selectedIsAvailable) {
            setData(
                "account_id",
                availableAccounts.length ? String(availableAccounts[0].id) : "",
            );
        }
    }, [requiresRefundAccount, availableAccounts]);

    useEffect(() => {
        setData((previous) => ({
            ...previous,
            sale_item_id: rows.map((row) => row.sale_item_id),
            qty: rows.map((row) => row.return_qty),
            free_qty: rows.map((row) => row.return_free_qty),
        }));
    }, [rows]);

    useEffect(() => {
        if (searchTimer.current) {
            window.clearTimeout(searchTimer.current);
        }

        const search = invoiceSearch.trim();

        if (search.length < 2 || selectedSale) {
            setInvoiceResults([]);
            setInvoiceSearching(false);
            return;
        }

        const controller = new AbortController();
        setInvoiceResults([]);
        setInvoiceSearching(true);
        searchTimer.current = window.setTimeout(async () => {
            setInvoiceSearching(true);
            setInvoiceError("");

            try {
                const response = await axios.get("/sales-returns/search", {
                    params: { search },
                    signal: controller.signal,
                });

                if (controller.signal.aborted) return;
                setActiveInvoice(0);
                setInvoiceResults(
                    Array.isArray(response.data?.sales) ? response.data.sales : [],
                );
                setInvoiceSearchOpen(true);
            } catch (error) {
                if (controller.signal.aborted) return;
                setInvoiceResults([]);
                setInvoiceError(apiError(error, "Unable to search invoices."));
            } finally {
                if (!controller.signal.aborted) setInvoiceSearching(false);
            }
        }, 250);

        return () => {
            controller.abort();
            if (searchTimer.current) {
                window.clearTimeout(searchTimer.current);
            }
        };
    }, [invoiceSearch, selectedSale]);

    useEffect(() => {
        const closeResults = (event) => {
            if (
                searchBoxRef.current &&
                !searchBoxRef.current.contains(event.target)
            ) {
                setInvoiceSearchOpen(false);
            }
        };

        document.addEventListener("mousedown", closeResults);

        return () => document.removeEventListener("mousedown", closeResults);
    }, []);

    const loadInvoice = async (sale) => {
        if (invoiceLoading || saveLock.current) return;
        setInvoiceSearchOpen(false);
        setInvoiceLoading(true);
        setInvoiceError("");
        clearErrors();

        try {
            const response = await axios.get(`/sales-returns/invoice/${sale.id}`);
            const invoice = response.data?.sale;

            if (!invoice) {
                throw new Error("Invoice data was not returned.");
            }

            const returnRows = (invoice.items ?? []).map((item) => ({
                sale_item_id: item.id,
                product: item.product,
                sold_qty: numberValue(item.quantity),
                sold_free_qty: numberValue(item.free_quantity),
                returned_qty: numberValue(item.returned_qty),
                returned_free_qty: numberValue(item.returned_free_qty),
                balance_qty: numberValue(item.balance_qty),
                balance_free_qty: numberValue(item.balance_free_qty),
                return_qty: "0",
                return_free_qty: "0",
                sale_price: numberValue(item.sale_price),
                discount_type: item.discount_type ?? "fixed",
                discount_value: numberValue(item.discount_value),
                discount_amount: numberValue(item.discount),
                gst_percent: numberValue(item.gst_percent),
            }));

            if (
                !returnRows.some(
                    (row) => row.balance_qty > 0 || row.balance_free_qty > 0,
                )
            ) {
                setInvoiceError(
                    "Every item in this invoice has already been returned.",
                );
            }

            setSelectedSale(invoice);
            setRows(returnRows);
            setData("sale_id", String(invoice.id));
            setInvoiceSearch("");
            setInvoiceSearchOpen(false);
            window.setTimeout(() => tableRef.current?.querySelector("input:not(:disabled)")?.focus(), 0);
        } catch (error) {
            setInvoiceError(apiError(error, "Unable to load the invoice."));
        } finally {
            setInvoiceLoading(false);
        }
    };

    const clearInvoice = () => {
        setSelectedSale(null);
        setRows([]);
        setData("sale_id", "");
        setInvoiceSearch("");
        setInvoiceResults([]);
        setInvoiceError("");
        window.setTimeout(() => searchInputRef.current?.focus(), 50);
    };

    const updateQuantity = (index, field, value) => {
        setRows((previous) => {
            const next = [...previous];
            const row = { ...next[index] };
            const maximum =
                field === "return_free_qty" ? row.balance_free_qty : row.balance_qty;

            row[field] =
                value === ""
                    ? ""
                    : String(Math.min(maximum, Math.max(0, numberValue(value))));
            next[index] = row;
            return next;
        });
    };

    const returnAll = () => {
        setRows((previous) =>
            previous.map((row) => ({
                ...row,
                return_qty: String(row.balance_qty),
                return_free_qty: String(row.balance_free_qty),
            })),
        );
    };

    const clearQuantities = () => {
        setRows((previous) =>
            previous.map((row) => ({
                ...row,
                return_qty: "0",
                return_free_qty: "0",
            })),
        );
    };

    const changeRefundMethod = (method) => {
        if (method === "customer_credit") {
            setData((previous) => ({
                ...previous,
                refund_method: method,
                refund_status: "credited",
                account_id: "",
            }));
            return;
        }

        if (method === "none") {
            setData((previous) => ({
                ...previous,
                refund_method: method,
                refund_status: "pending",
                account_id: "",
            }));
            return;
        }

        setData((previous) => ({
            ...previous,
            refund_method: method,
            refund_status: "refunded",
        }));
    };

    const changeRefundStatus = (status) => {
        setData((previous) => ({
            ...previous,
            refund_status: status,
            refund_method:
                status === "credited"
                    ? "customer_credit"
                    : status === "pending" &&
                        previous.refund_method === "customer_credit"
                        ? "none"
                        : previous.refund_method,
            account_id: status === "refunded" ? previous.account_id : "",
        }));
    };

    const totals = useMemo(() => {
        return rows.reduce(
            (total, row) => {
                const calculation = calculateReturnRow(row);
                total.quantity += numberValue(row.return_qty);
                total.freeQuantity += numberValue(row.return_free_qty);
                total.discount += calculation.discount;
                total.taxable += calculation.taxable;
                total.gst += calculation.gst;
                total.amount += calculation.total;
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
        );
    }, [rows]);

    const hasReturnQuantity = rows.some(
        (row) =>
            numberValue(row.return_qty) > 0 || numberValue(row.return_free_qty) > 0,
    );

    const submit = (event) => {
        event.preventDefault();
        if (saveLock.current) return;

        if (!selectedSale) {
            setInvoiceError("Select an invoice first.");
            searchInputRef.current?.focus();
            return;
        }

        if (!hasReturnQuantity) {
            setInvoiceError("Enter a return quantity for at least one product.");
            return;
        }

        if (requiresRefundAccount && !data.account_id) {
            setInvoiceError("Select the cash or bank account used for the refund.");
            return;
        }

        const wantsPrint = printRequested.current;
        printRequested.current = false;
        const popup = wantsPrint ? window.open("", "_blank") : null;
        if (popup) {
            popup.document.write('<!doctype html><html><head><title>Preparing return print</title></head><body style="margin:0;background:#eff4fb;display:grid;place-items:center;min-height:100vh;font-family:Segoe UI,Arial;color:#23466e"><div style="text-align:center"><div style="font-size:32px">⏳</div><h3>Saving your return…</h3><p>Your print preview will open when ready.</p></div></body></html>');
            popup.document.close();
        }
        saveLock.current = true;
        setSaving(true);
        post("/sales-returns", {
            preserveScroll: true,
            onSuccess: async (page) => {
                if (!wantsPrint) return;
                if (!popup) { window.alert("Return saved. Allow popups, then print from the saved return page."); return; }
                const match = new URL(page.url, window.location.origin).pathname.match(/\/sales-returns\/(\d+)\/?$/);
                const id = page.props.salesReturn?.id ?? page.props.sales_return?.id ?? match?.[1];
                if (!id) { popup.document.body.textContent = "Return saved. Open the saved return from the register and select Print."; return; }
                try {
                    const url = new URL(`/sales-returns/${id}/print`, window.location.origin);
                    const response = await fetch(url, { credentials: "same-origin", headers: { Accept: "text/html" } });
                    if (!response.ok || new URL(response.url).pathname !== url.pathname) throw new Error("Print preview unavailable");
                    const html = await response.text();
                    if (popup.closed) return;
                    popup.history.replaceState(null, "", url.href);
                    popup.document.open(); popup.document.write(html); popup.document.close();
                } catch {
                    if (!popup.closed) popup.document.body.textContent = "Return saved. Print preview could not load. Open the saved return and try Print again; do not submit another return.";
                }
            },
            onError: () => { if (popup && !popup.closed) popup.close(); },
            onFinish: () => { saveLock.current = false; setSaving(false); },
        });
    };

    submitRef.current = submit;

    return (
        <>
            <Head title="Create Sales Return" />
            {(saving || processing || invoiceLoading) && typeof document !== "undefined" && createPortal(
                <div className="rr-return-navigation-overlay" role="status" aria-live="polite">
                    <div className="rr-return-navigation-card">
                        <div className="rr-return-navigation-mark"><i className="bi bi-arrow-counterclockwise"></i><span /></div>
                        <strong>{invoiceLoading ? "Loading invoice…" : "Completing sales return…"}</strong>
                        <p>{invoiceLoading ? "Preparing items and available return quantities." : "Please wait while the return is saved."}</p>
                        <div className="rr-return-navigation-track"><span /></div>
                    </div>
                </div>, document.body
            )}

            <div className="rr-return-page rr-return-create-page">
                <MasterNavigation
                    active="sales-returns"
                    title="Sales Return"
                    subtitle="Invoice-based returns, refunds and stock restoration"
                />

                <form ref={formRef} onSubmit={submit} aria-busy={saving || processing}>
                    <header className="rr-return-header">
                        <div className="rr-return-header-main">
                            <Link
                                href="/sales-returns"
                                className="rr-return-back"
                            >
                                <i className="bi bi-arrow-left"></i>
                            </Link>

                            <div className="rr-return-header-icon">
                                <i className="bi bi-arrow-counterclockwise"></i>
                            </div>

                            <div>
                                <span className="rr-return-eyebrow">SALES / COUNTER RETURN</span>
                                <h1>New Sales Return</h1>
                                <div className="rr-return-header-meta">
                                    <span>{returnNo || "New Return"}</span>
                                    <span>{business?.company_name ?? "Company"}</span>
                                    <span>{business?.store_name ?? "Branch"}</span>
                                </div>
                            </div>
                        </div>

                        <div className="rr-return-actions">
                            <Link
                                href="/sales-returns"
                                className="rr-return-btn secondary"
                            >
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                className="rr-return-btn primary"
                                disabled={processing || saving || !hasReturnQuantity}
                            >
                                <i className={processing || saving ? "bi bi-arrow-repeat rr-return-spin" : "bi bi-check2-circle"}></i>
                                {processing || saving ? "Saving…" : "Complete Return"} <kbd>F9</kbd>
                            </button>
                            <button type="button" className="rr-return-btn print" disabled={processing || saving || !hasReturnQuantity} onClick={() => { printRequested.current = true; submit({ preventDefault() { } }); }}>
                                <i className="bi bi-printer"></i> Complete & Print <kbd>F10</kbd>
                            </button>
                        </div>
                    </header>
                    <section className="rr-return-shortcuts">
                        <button type="button" onClick={() => setShortcutsOpen((value) => !value)} aria-expanded={shortcutsOpen} aria-controls="return-shortcut-notes"><i className="bi bi-keyboard"></i> Billing shortcuts <i className={`bi bi-chevron-${shortcutsOpen ? "up" : "down"}`}></i></button>
                        {shortcutsOpen && <div id="return-shortcut-notes">
                            <span><kbd className="blue">F2</kbd> Find invoice</span>
                            <span><kbd className="purple">Enter / Esc</kbd> Next quantity</span>
                            <span><kbd className="purple">Shift + Enter / ←</kbd> Previous quantity</span>
                            <span><kbd className="blue">→</kbd> Next quantity</span>
                            <span><kbd className="green">F9</kbd> Complete return</span>
                            <span><kbd className="orange">F10</kbd> Complete & print</span>
                        </div>}
                    </section>

                    <section className="rr-return-window rr-return-invoice-window">
                        <WindowTitle
                            icon="bi-receipt"
                            title="Find Sales Invoice"
                        />

                        <div className="rr-return-window-body">
                            {!selectedSale ? (
                                <div
                                    ref={searchBoxRef}
                                    className="rr-return-invoice-search"
                                >
                                    <div className="rr-return-search-input">
                                        <i className="bi bi-search"></i>
                                        <input
                                            ref={searchInputRef}
                                            type="search"
                                            value={invoiceSearch}
                                            placeholder="Search invoice number, customer name, mobile or code..."
                                            autoComplete="off"
                                            aria-label="Find sales invoice"
                                            role="combobox"
                                            aria-expanded={invoiceSearchOpen}
                                            aria-controls="return-invoice-results"
                                            aria-activedescendant={invoiceSearchOpen && invoiceResults[activeInvoice] ? `return-invoice-${invoiceResults[activeInvoice].id}` : undefined}
                                            onKeyDown={(event) => {
                                                if (event.key === "Escape") { event.preventDefault(); setInvoiceSearchOpen(false); }
                                                if (["ArrowDown", "ArrowUp"].includes(event.key)) {
                                                    event.preventDefault(); setInvoiceSearchOpen(true);
                                                    setActiveInvoice((previous) => Math.max(0, Math.min(invoiceResults.length - 1, previous + (event.key === "ArrowDown" ? 1 : -1))));
                                                }
                                                if (event.key === "Enter") { event.preventDefault(); if (!invoiceSearching && invoiceResults[activeInvoice]) loadInvoice(invoiceResults[activeInvoice]); }
                                            }}
                                            onFocus={() => setInvoiceSearchOpen(true)}
                                            onChange={(event) => {
                                                setInvoiceSearching(true);
                                                setInvoiceResults([]);
                                                setActiveInvoice(0);
                                                setInvoiceSearch(event.target.value);
                                                setInvoiceSearchOpen(true);
                                            }}
                                        />
                                        {invoiceSearching && (
                                            <span className="spinner-border spinner-border-sm"></span>
                                        )}
                                    </div>

                                    {invoiceSearchOpen && invoiceSearch.trim().length > 0 && (
                                        <div id="return-invoice-results" className="rr-return-invoice-results" role="listbox">
                                            {invoiceSearch.trim().length === 1 ? (
                                                <div className="rr-return-search-message">
                                                    Type one more character to search.
                                                </div>
                                            ) : invoiceResults.length ? (
                                                invoiceResults.map((sale, resultIndex) => (
                                                    <button
                                                        key={sale.id}
                                                        type="button"
                                                        id={`return-invoice-${sale.id}`}
                                                        role="option"
                                                        aria-selected={activeInvoice === resultIndex}
                                                        className={`rr-return-invoice-result ${activeInvoice === resultIndex ? "active" : ""}`}
                                                        onClick={() => loadInvoice(sale)}
                                                    >
                                                        <div>
                                                            <strong>{sale.invoice_no}</strong>
                                                            <span>
                                                                {sale.customer?.customer_name ?? "Customer"}
                                                                {sale.customer?.mobile
                                                                    ? ` · ${sale.customer.mobile}`
                                                                    : ""}
                                                            </span>
                                                        </div>
                                                        <div className="rr-return-result-date">
                                                            <span>{displayDate(sale.invoice_date)}</span>
                                                            <strong>{formatMoney(sale.grand_total)}</strong>
                                                        </div>
                                                        <i className="bi bi-chevron-right"></i>
                                                    </button>
                                                ))
                                            ) : !invoiceSearching ? (
                                                <div className="rr-return-search-message">
                                                    No matching invoice found.
                                                </div>
                                            ) : null}
                                        </div>
                                    )}
                                </div>
                            ) : (
                                <div className="rr-return-selected-invoice">
                                    <div>
                                        <span>Selected Invoice</span>
                                        <strong>{selectedSale.invoice_no}</strong>
                                    </div>
                                    <div>
                                        <span>Customer</span>
                                        <strong>
                                            {selectedSale.customer?.customer_name ?? "Customer"}
                                        </strong>
                                    </div>
                                    <div>
                                        <span>Invoice Date</span>
                                        <strong>{displayDate(selectedSale.invoice_date)}</strong>
                                    </div>
                                    <div>
                                        <span>Billing</span>
                                        <strong>
                                            {selectedSale.billing_type === "gst"
                                                ? "GST Bill"
                                                : "Without GST"}
                                        </strong>
                                    </div>
                                    <button
                                        type="button"
                                        onClick={clearInvoice}
                                    >
                                        Change Invoice
                                    </button>
                                </div>
                            )}

                            {invoiceLoading && (
                                <div className="rr-return-loading">Loading invoice...</div>
                            )}

                            <ErrorText message={invoiceError || errors.sale_id} />
                        </div>
                    </section>

                    {selectedSale && (
                        <>
                            <section className="rr-return-window">
                                <div className="rr-return-window-title">
                                    <div>
                                        <i className="bi bi-box-seam"></i>
                                        <strong>Return Items</strong>
                                    </div>
                                    <div className="rr-return-title-actions">
                                        <button
                                            type="button"
                                            onClick={clearQuantities}
                                        >
                                            Clear
                                        </button>
                                        <button
                                            type="button"
                                            className="primary"
                                            onClick={returnAll}
                                        >
                                            Return All Balance
                                        </button>
                                    </div>
                                </div>

                                <div ref={tableRef} className="rr-return-table-wrap" onKeyDown={moveQuantity}>
                                    <table className="rr-return-table">
                                        <thead>
                                            <tr>
                                                <th>#</th>
                                                <th>Product</th>
                                                <th className="number">Sold</th>
                                                <th className="number">Returned</th>
                                                <th className="number">Balance</th>
                                                <th className="number">Return Qty</th>
                                                <th className="number">Free Return</th>
                                                <th className="number">Net rate</th>
                                                {selectedSale.billing_type === "gst" && <th className="number">Rate excl. GST</th>}
                                                <th className="number">Discount</th>
                                                {selectedSale.billing_type === "gst" && (
                                                    <th className="number">GST</th>
                                                )}
                                                <th className="number">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {rows.map((row, index) => {
                                                const calculation = calculateReturnRow(row);
                                                const unavailable =
                                                    row.balance_qty <= 0 && row.balance_free_qty <= 0;

                                                return (
                                                    <tr
                                                        key={row.sale_item_id}
                                                        className={unavailable ? "unavailable" : numberValue(row.return_qty) > 0 || numberValue(row.return_free_qty) > 0 ? "return-selected" : ""}
                                                    >
                                                        <td>{index + 1}</td>
                                                        <td>
                                                            <strong>
                                                                {row.product?.product_name ?? "Product"}
                                                            </strong>
                                                            <span>
                                                                {row.product?.product_code ?? ""}
                                                                {row.product?.part_number
                                                                    ? ` · ${row.product.part_number}`
                                                                    : ""}
                                                            </span>
                                                        </td>
                                                        <td className="number">
                                                            {formatQty(row.sold_qty)}
                                                        </td>
                                                        <td className="number">
                                                            {formatQty(row.returned_qty)}
                                                        </td>
                                                        <td className="number balance-cell">
                                                            {formatQty(row.balance_qty)}
                                                        </td>
                                                        <td className="number">
                                                            <input
                                                                type="number"
                                                                aria-label={`Return quantity for ${row.product?.product_name ?? "product"}`}
                                                                min="0"
                                                                max={row.balance_qty}
                                                                step="1"
                                                                value={row.return_qty}
                                                                disabled={row.balance_qty <= 0}
                                                                onFocus={(event) => event.target.select()}
                                                                onChange={(event) =>
                                                                    updateQuantity(
                                                                        index,
                                                                        "return_qty",
                                                                        event.target.value,
                                                                    )
                                                                }
                                                            />
                                                            <ErrorText message={errors[`qty.${index}`]} />
                                                        </td>
                                                        <td className="number">
                                                            <input
                                                                type="number"
                                                                min="0"
                                                                max={row.balance_free_qty}
                                                                step="1"
                                                                value={row.return_free_qty}
                                                                disabled={row.balance_free_qty <= 0}
                                                                onFocus={(event) => event.target.select()}
                                                                onChange={(event) =>
                                                                    updateQuantity(
                                                                        index,
                                                                        "return_free_qty",
                                                                        event.target.value,
                                                                    )
                                                                }
                                                            />
                                                        </td>
                                                        <td className="number">
                                                            {formatMoney(row.sale_price * (selectedSale.billing_type === "gst" ? 1 + row.gst_percent / 100 : 1))}
                                                        </td>
                                                        {selectedSale.billing_type === "gst" && <td className="number">{formatMoney(row.sale_price)}</td>}
                                                        <td className="number">
                                                            {formatMoney(calculation.discount)}
                                                        </td>
                                                        {selectedSale.billing_type === "gst" && (
                                                            <td className="number">
                                                                {formatQty(row.gst_percent)}%
                                                                <span>{formatMoney(calculation.gst)}</span>
                                                            </td>
                                                        )}
                                                        <td className="number amount-cell">
                                                            {formatMoney(calculation.total)}
                                                        </td>
                                                    </tr>
                                                );
                                            })}
                                        </tbody>
                                    </table>
                                </div>
                            </section>

                            <div className="rr-return-bottom-grid">
                                <section className="rr-return-window">
                                    <WindowTitle
                                        icon="bi-wallet2"
                                        title="Refund Details"
                                    />
                                    <div className="rr-return-window-body rr-return-form-grid">
                                        <Field label="Return Date">
                                            <input
                                                type="date" name="return_date"
                                                value={data.return_date}
                                                onChange={(event) =>
                                                    setData("return_date", event.target.value)
                                                }
                                            />
                                            <ErrorText message={errors.return_date} />
                                        </Field>
                                        <Field label="Refund Method">
                                            <select
                                                value={data.refund_method}
                                                onChange={(event) =>
                                                    changeRefundMethod(event.target.value)
                                                }
                                            >
                                                <option value="cash">Cash</option>
                                                <option value="bank">Bank</option>
                                                <option value="original_payment">
                                                    Original Payment Method
                                                </option>
                                                <option value="customer_credit">Customer Credit</option>
                                                <option value="none">No Immediate Refund</option>
                                            </select>
                                            <ErrorText message={errors.refund_method} />
                                        </Field>
                                        <Field label="Refund Status">
                                            <select
                                                value={data.refund_status}
                                                onChange={(event) =>
                                                    changeRefundStatus(event.target.value)
                                                }
                                            >
                                                <option value="refunded">Refunded</option>
                                                <option value="pending">Pending</option>
                                                <option value="credited">Customer Credit</option>
                                            </select>
                                            <ErrorText message={errors.refund_status} />
                                        </Field>
                                        {requiresRefundAccount && (
                                            <Field label="Refund Account" wide>
                                                <select
                                                    value={data.account_id}
                                                    onChange={(event) =>
                                                        setData(
                                                            "account_id",
                                                            event.target.value,
                                                        )
                                                    }
                                                >
                                                    <option value="">
                                                        Select cash or bank account
                                                    </option>
                                                    {availableAccounts.map((account) => (
                                                        <option
                                                            key={account.id}
                                                            value={account.id}
                                                        >
                                                            {account.account_name}
                                                            {account.account_code
                                                                ? ` (${account.account_code})`
                                                                : ""}
                                                            {account.bank_name
                                                                ? ` · ${account.bank_name}`
                                                                : ""}
                                                        </option>
                                                    ))}
                                                </select>
                                                <ErrorText message={errors.account_id} />
                                                {!availableAccounts.length && (
                                                    <div className="rr-return-error">
                                                        No matching active refund account is
                                                        available.
                                                    </div>
                                                )}
                                            </Field>
                                        )}
                                        <Field label="Remarks" wide>
                                            <textarea
                                                rows="3"
                                                value={data.remarks}
                                                onChange={(event) =>
                                                    setData("remarks", event.target.value)
                                                }
                                            ></textarea>
                                        </Field>
                                    </div>
                                </section>

                                <section className="rr-return-window">
                                    <WindowTitle
                                        icon="bi-calculator"
                                        title="Return Summary"
                                    />
                                    <div className="rr-return-summary">
                                        <TotalRow
                                            label="Return Quantity"
                                            value={formatQty(totals.quantity)}
                                        />
                                        <TotalRow
                                            label="Free Quantity"
                                            value={formatQty(totals.freeQuantity)}
                                        />
                                        <TotalRow
                                            label="Item Discount"
                                            value={formatMoney(totals.discount)}
                                        />
                                        <TotalRow
                                            label="Taxable Amount"
                                            value={formatMoney(totals.taxable)}
                                        />
                                        {selectedSale.billing_type === "gst" && (
                                            <TotalRow
                                                label="GST"
                                                value={formatMoney(totals.gst)}
                                            />
                                        )}
                                        <TotalRow
                                            label="Refund Total"
                                            value={formatMoney(Math.round(totals.amount))}
                                            grand
                                        />
                                    </div>
                                </section>
                            </div>
                        </>
                    )}
                    <footer className="rr-return-create-footer">
                        <div className="rr-return-footer-total">
                            <span>Refund total</span>
                            <strong>{formatMoney(Math.round(totals.amount))}</strong>
                            <small>{formatQty(totals.quantity)} return qty · {formatQty(totals.freeQuantity)} free qty</small>
                        </div>
                        <div className="rr-return-actions">
                            <button type="submit" className="rr-return-btn primary" disabled={processing || saving || !hasReturnQuantity}>
                                <i className={processing || saving ? "bi bi-arrow-repeat rr-return-spin" : "bi bi-check2-circle"}></i>
                                {processing || saving ? "Saving…" : "Complete Return"}<kbd>F9</kbd>
                            </button>
                            <button type="button" className="rr-return-btn print" disabled={processing || saving || !hasReturnQuantity} onClick={() => { printRequested.current = true; submit({ preventDefault() { } }); }}>
                                <i className="bi bi-printer"></i> Complete & Print <kbd>F10</kbd>
                            </button>
                        </div>
                    </footer>
                </form>
            </div>
        </>
    );
}

function WindowTitle({ icon, title }) {
    return (
        <div className="rr-return-window-title">
            <div>
                <i className={`bi ${icon}`}></i>
                <strong>{title}</strong>
            </div>
        </div>
    );
}

function Field({ label, wide = false, children }) {
    return (
        <div className={`rr-return-field ${wide ? "wide" : ""}`}>
            <label>{label}</label>
            {children}
        </div>
    );
}

function TotalRow({ label, value, grand = false }) {
    return (
        <div className={`rr-return-total-row ${grand ? "grand" : ""}`}>
            <span>{label}</span>
            <strong>{value}</strong>
        </div>
    );
}

function ErrorText({ message }) {
    if (!message) return null;
    return <div className="rr-return-error">{message}</div>;
}

function calculateReturnRow(row) {
    const quantity = numberValue(row.return_qty);
    const gross = round2(quantity * numberValue(row.sale_price));
    let discount = 0;

    if (row.discount_type === "percentage") {
        discount = round2((gross * numberValue(row.discount_value)) / 100);
    } else if (numberValue(row.sold_qty) > 0) {
        discount = round2(
            (numberValue(row.discount_amount) * quantity) / numberValue(row.sold_qty),
        );
    }

    discount = Math.min(gross, discount);
    const taxable = round2(Math.max(0, gross - discount));
    const gst = round2((taxable * numberValue(row.gst_percent)) / 100);

    return {
        discount,
        taxable,
        gst,
        total: round2(taxable + gst),
    };
}

function apiError(error, fallback) {
    const validationErrors = error?.response?.data?.errors;
    const firstError = validationErrors
        ? Object.values(validationErrors).flat()[0]
        : null;
    return firstError || error?.response?.data?.message || fallback;
}

function numberValue(value) {
    const number = Number(value ?? 0);
    return Number.isFinite(number) ? number : 0;
}

function normalizeMode(value) {
    const mode = String(value ?? "")
        .trim()
        .toLowerCase()
        .replace(/[\s-]+/g, "_");

    if (["g_pay", "phonepe"].includes(mode)) return "gpay";
    if (["bank", "banktransfer", "cheque"].includes(mode)) {
        return "bank_transfer";
    }

    return mode;
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
    return new Intl.NumberFormat("en-IN", {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    }).format(numberValue(value));
}

function displayDate(value) {
    if (!value) return "-";
    return new Intl.DateTimeFormat("en-IN").format(new Date(value));
}

function today() {
    const now = new Date();
    const offset = now.getTimezoneOffset();
    return new Date(now.getTime() - offset * 60000).toISOString().slice(0, 10);
}

Create.layout = (page) => <BusinessLayout>{page}</BusinessLayout>;

