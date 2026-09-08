import React, { useEffect, useMemo, useState } from "react";

import axios from "axios";

import { Head, Link, useForm } from "@inertiajs/react";

import BusinessLayout from "@/Layouts/BusinessLayout";
import MasterNavigation from "../../Components/Business/MasterNavigation";
import ConfirmModal from "../../Components/ConfirmModal";

import "../../../css/business/sales-return.css";

export default function Edit({ salesReturn }) {
    const sale = salesReturn?.sale ?? {};
    const currentItems = Array.isArray(salesReturn?.items)
        ? salesReturn.items
        : [];

    const [rows, setRows] = useState(() => buildInitialRows(sale, currentItems));
    const [loading, setLoading] = useState(true);
    const [pageError, setPageError] = useState("");
    const [updateConfirmation, setUpdateConfirmation] = useState(false);

    const { data, setData, put, processing, errors } = useForm({
        sale_id: String(salesReturn?.sale_id ?? sale?.id ?? ""),
        return_date: dateInputValue(salesReturn?.return_date),
        refund_method: salesReturn?.refund_method ?? "cash",
        refund_status: salesReturn?.refund_status ?? "refunded",
        remarks: salesReturn?.remarks ?? "",
        sale_item_id: [],
        qty: [],
        free_qty: [],
    });

    useEffect(() => {
        let active = true;

        const refreshInvoiceBalance = async () => {
            if (!sale?.id) {
                setLoading(false);
                setPageError("The original invoice is unavailable.");
                return;
            }

            try {
                const response = await axios.get(`/sales-returns/invoice/${sale.id}`);
                const invoice = response.data?.sale;

                if (!active || !invoice) return;

                setRows(buildEditableRows(invoice, currentItems));
            } catch (error) {
                if (active) {
                    setPageError(
                        apiError(
                            error,
                            "Could not refresh invoice balances. Existing values are shown.",
                        ),
                    );
                }
            } finally {
                if (active) setLoading(false);
            }
        };

        refreshInvoiceBalance();

        return () => {
            active = false;
        };
    }, [sale?.id]);

    useEffect(() => {
        setData((previous) => ({
            ...previous,
            sale_item_id: rows.map((row) => row.sale_item_id),
            qty: rows.map((row) => row.return_qty),
            free_qty: rows.map((row) => row.return_free_qty),
        }));
    }, [rows]);

    const totals = useMemo(
        () =>
            rows.reduce(
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
            ),
        [rows],
    );

    const hasReturnQuantity = rows.some(
        (row) =>
            numberValue(row.return_qty) > 0 ||
            numberValue(row.return_free_qty) > 0,
    );

    const updateQuantity = (index, field, value) => {
        setRows((previous) => {
            const next = [...previous];
            const row = { ...next[index] };
            const maximum =
                field === "return_free_qty"
                    ? row.editable_free_balance
                    : row.editable_balance;

            row[field] =
                value === ""
                    ? ""
                    : String(Math.min(maximum, Math.max(0, numberValue(value))));

            next[index] = row;
            return next;
        });
    };

    const restoreOriginal = () => {
        setRows((previous) =>
            previous.map((row) => ({
                ...row,
                return_qty: String(row.original_return_qty),
                return_free_qty: String(row.original_return_free_qty),
            })),
        );
    };

    const returnAllAvailable = () => {
        setRows((previous) =>
            previous.map((row) => ({
                ...row,
                return_qty: String(row.editable_balance),
                return_free_qty: String(row.editable_free_balance),
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

    const submit = (event) => {
        event.preventDefault();
        setPageError("");

        if (!hasReturnQuantity) {
            setPageError("Enter a return quantity for at least one product.");
            return;
        }

        setUpdateConfirmation(true);
    };

    const confirmUpdate = () => {
        put(`/sales-returns/${salesReturn.id}`, {
            preserveScroll: true,
            onFinish: () => setUpdateConfirmation(false),
        });
    };

    const closeUpdateConfirmation = () => {
        if (processing) return;
        setUpdateConfirmation(false);
    };

    return (
        <>
            <Head title={`Edit ${salesReturn?.return_no ?? "Sales Return"}`} />

            <div className="rr-return-page">
                <MasterNavigation
                    active="sales-returns"
                    title="Sales Return"
                    subtitle="Edit returned quantities and refund information"
                />

                <form onSubmit={submit}>
                    <header className="rr-return-header">
                        <div className="rr-return-header-main">
                            <Link
                                href={`/sales-returns/${salesReturn.id}`}
                                className="rr-return-back"
                                title="Back to return details"
                            >
                                <i className="bi bi-arrow-left"></i>
                            </Link>

                            <div className="rr-return-header-icon">
                                <i className="bi bi-pencil-square"></i>
                            </div>

                            <div>
                                <span className="rr-return-eyebrow">Edit Sales Return</span>
                                <h1>{salesReturn?.return_no ?? "Sales Return"}</h1>
                                <div className="rr-return-header-meta">
                                    <span>
                                        Invoice: {sale?.invoice_no ?? `#${salesReturn?.sale_id}`}
                                    </span>
                                    <span>{sale?.customer?.customer_name ?? "Customer"}</span>
                                    <span>
                                        {sale?.billing_type === "gst"
                                            ? "GST Bill"
                                            : "Without GST"}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div className="rr-return-actions">
                            <Link
                                href={`/sales-returns/${salesReturn.id}`}
                                className="rr-return-btn secondary"
                            >
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                className="rr-return-btn primary"
                                disabled={processing || loading || !hasReturnQuantity}
                            >
                                {processing ? "Updating..." : "Update Return"}
                            </button>
                        </div>
                    </header>

                    {(pageError || errors.items || errors.sale_id) && (
                        <div className="rr-return-alert danger">
                            <i className="bi bi-exclamation-triangle-fill"></i>
                            {pageError || errors.items || errors.sale_id}
                        </div>
                    )}

                    <section className="rr-return-window">
                        <WindowTitle
                            icon="bi-receipt"
                            title="Original Invoice — Locked"
                        />

                        <div className="rr-return-selected-invoice rr-return-edit-invoice">
                            <div>
                                <span>Invoice Number</span>
                                <strong>{sale?.invoice_no ?? "-"}</strong>
                            </div>
                            <div>
                                <span>Customer</span>
                                <strong>{sale?.customer?.customer_name ?? "Customer"}</strong>
                            </div>
                            <div>
                                <span>Invoice Date</span>
                                <strong>{displayDate(sale?.invoice_date)}</strong>
                            </div>
                            <div>
                                <span>Invoice Amount</span>
                                <strong>{formatMoney(sale?.grand_total)}</strong>
                            </div>
                            <div className="rr-return-locked-indicator">
                                <i className="bi bi-lock-fill"></i>
                                Cannot be changed
                            </div>
                        </div>
                    </section>

                    <section className="rr-return-window">
                        <div className="rr-return-window-title">
                            <div>
                                <i className="bi bi-box-seam"></i>
                                <strong>Return Items</strong>
                            </div>
                            <div className="rr-return-title-actions">
                                <button type="button" onClick={restoreOriginal}>
                                    Original Values
                                </button>
                                <button type="button" onClick={clearQuantities}>
                                    Clear
                                </button>
                                <button
                                    type="button"
                                    className="primary"
                                    onClick={returnAllAvailable}
                                >
                                    Return All Available
                                </button>
                            </div>
                        </div>

                        {loading && (
                            <div className="rr-return-loading">
                                <span className="spinner-border spinner-border-sm"></span>
                                Refreshing available quantities...
                            </div>
                        )}

                        <div className="rr-return-table-wrap">
                            <table className="rr-return-table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Product</th>
                                        <th className="number">Sold</th>
                                        <th className="number">Other Returns</th>
                                        <th className="number">Available</th>
                                        <th className="number">Return Qty</th>
                                        <th className="number">Free Return</th>
                                        <th className="number">Rate</th>
                                        <th className="number">Discount</th>
                                        {sale?.billing_type === "gst" && (
                                            <th className="number">GST</th>
                                        )}
                                        <th className="number">Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {rows.map((row, index) => {
                                        const calculation = calculateReturnRow(row);
                                        const unavailable =
                                            row.editable_balance <= 0 &&
                                            row.editable_free_balance <= 0;

                                        return (
                                            <tr
                                                key={row.sale_item_id}
                                                className={unavailable ? "unavailable" : ""}
                                            >
                                                <td>{index + 1}</td>
                                                <td>
                                                    <strong>
                                                        {row.product?.product_name ?? "Product"}
                                                    </strong>
                                                    <span>{productMeta(row.product)}</span>
                                                </td>
                                                <td className="number">
                                                    {formatQty(row.sold_qty)}
                                                </td>
                                                <td className="number">
                                                    {formatQty(row.other_returned_qty)}
                                                </td>
                                                <td className="number balance-cell">
                                                    {formatQty(row.editable_balance)}
                                                </td>
                                                <td className="number">
                                                    <input
                                                        type="number"
                                                        min="0"
                                                        max={row.editable_balance}
                                                        step="1"
                                                        value={row.return_qty}
                                                        disabled={row.editable_balance <= 0}
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
                                                        max={row.editable_free_balance}
                                                        step="1"
                                                        value={row.return_free_qty}
                                                        disabled={row.editable_free_balance <= 0}
                                                        onFocus={(event) => event.target.select()}
                                                        onChange={(event) =>
                                                            updateQuantity(
                                                                index,
                                                                "return_free_qty",
                                                                event.target.value,
                                                            )
                                                        }
                                                    />
                                                    <ErrorText message={errors[`free_qty.${index}`]} />
                                                </td>
                                                <td className="number">
                                                    {formatMoney(row.sale_price)}
                                                </td>
                                                <td className="number">
                                                    {formatMoney(calculation.discount)}
                                                </td>
                                                {sale?.billing_type === "gst" && (
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
                            <WindowTitle icon="bi-wallet2" title="Refund Details" />
                            <div className="rr-return-window-body rr-return-form-grid">
                                <Field label="Return Date">
                                    <input
                                        type="date"
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
                                            setData("refund_method", event.target.value)
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
                                            setData("refund_status", event.target.value)
                                        }
                                    >
                                        <option value="refunded">Refunded</option>
                                        <option value="pending">Pending</option>
                                        <option value="credited">Customer Credit</option>
                                    </select>
                                    <ErrorText message={errors.refund_status} />
                                </Field>

                                <Field label="Remarks" wide>
                                    <textarea
                                        rows="3"
                                        value={data.remarks}
                                        onChange={(event) =>
                                            setData("remarks", event.target.value)
                                        }
                                    ></textarea>
                                    <ErrorText message={errors.remarks} />
                                </Field>
                            </div>
                        </section>

                        <section className="rr-return-window">
                            <WindowTitle icon="bi-calculator" title="Updated Summary" />
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
                                {sale?.billing_type === "gst" && (
                                    <TotalRow label="GST" value={formatMoney(totals.gst)} />
                                )}
                                <TotalRow
                                    label="Refund Total"
                                    value={formatMoney(Math.round(totals.amount))}
                                    grand
                                />
                            </div>
                        </section>
                    </div>
                </form>

                <ConfirmModal
                    show={updateConfirmation}
                    title="Update Sales Return"
                    message={`Update sales return "${salesReturn?.return_no ?? ""}"? The previous return stock entry will be reversed and the updated quantities will be applied.`}
                    confirmText="Update Return"
                    cancelText="Continue Editing"
                    icon="bi-pencil-square"
                    variant="primary"
                    processing={processing}
                    onConfirm={confirmUpdate}
                    onCancel={closeUpdateConfirmation}
                />
            </div>
        </>
    );
}

function buildInitialRows(sale, currentItems) {
    const currentBySaleItem = new Map(
        currentItems.map((item) => [Number(item.sale_item_id), item]),
    );

    return (sale?.items ?? []).map((saleItem) => {
        const current = currentBySaleItem.get(Number(saleItem.id));
        const currentQty = numberValue(current?.quantity);
        const currentFreeQty = numberValue(current?.free_quantity);

        return {
            sale_item_id: saleItem.id,
            product: saleItem.product,
            sold_qty: numberValue(saleItem.quantity),
            sold_free_qty: numberValue(saleItem.free_quantity),
            other_returned_qty: 0,
            editable_balance: numberValue(saleItem.quantity),
            editable_free_balance: numberValue(saleItem.free_quantity),
            original_return_qty: currentQty,
            original_return_free_qty: currentFreeQty,
            return_qty: String(currentQty),
            return_free_qty: String(currentFreeQty),
            sale_price: numberValue(saleItem.sale_price),
            discount_type: saleItem.discount_type ?? "fixed",
            discount_value: numberValue(saleItem.discount_value),
            discount_amount: numberValue(saleItem.discount),
            gst_percent: numberValue(saleItem.gst_percent),
        };
    });
}

function buildEditableRows(invoice, currentItems) {
    const currentBySaleItem = new Map(
        currentItems.map((item) => [Number(item.sale_item_id), item]),
    );

    return (invoice?.items ?? []).map((saleItem) => {
        const current = currentBySaleItem.get(Number(saleItem.id));
        const currentQty = numberValue(current?.quantity);
        const currentFreeQty = numberValue(current?.free_quantity);
        const totalReturned = numberValue(saleItem.returned_qty);
        const totalReturnedFree = numberValue(saleItem.returned_free_qty);
        const otherReturned = Math.max(0, totalReturned - currentQty);
        const otherReturnedFree = Math.max(0, totalReturnedFree - currentFreeQty);

        return {
            sale_item_id: saleItem.id,
            product: saleItem.product,
            sold_qty: numberValue(saleItem.quantity),
            sold_free_qty: numberValue(saleItem.free_quantity),
            other_returned_qty: otherReturned,
            other_returned_free_qty: otherReturnedFree,
            editable_balance: Math.max(
                0,
                numberValue(saleItem.quantity) - otherReturned,
            ),
            editable_free_balance: Math.max(
                0,
                numberValue(saleItem.free_quantity) - otherReturnedFree,
            ),
            original_return_qty: currentQty,
            original_return_free_qty: currentFreeQty,
            return_qty: String(currentQty),
            return_free_qty: String(currentFreeQty),
            sale_price: numberValue(saleItem.sale_price),
            discount_type: saleItem.discount_type ?? "fixed",
            discount_value: numberValue(saleItem.discount_value),
            discount_amount: numberValue(saleItem.discount),
            gst_percent: numberValue(saleItem.gst_percent),
        };
    });
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
            (numberValue(row.discount_amount) * quantity) /
            numberValue(row.sold_qty),
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

function productMeta(product) {
    return [
        product?.product_code,
        product?.model,
        product?.model_name,
        product?.part_number,
    ]
        .filter(Boolean)
        .join(" · ");
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

function dateInputValue(value) {
    if (!value) return "";
    return String(value).slice(0, 10);
}

Edit.layout = (page) => <BusinessLayout>{page}</BusinessLayout>;
