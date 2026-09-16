import React, {
    useEffect,
    useMemo,
    useRef,
    useState,
} from "react";

import axios from "axios";

import {
    Head,
    Link,
    useForm,
} from "@inertiajs/react";

import BusinessLayout from "@/Layouts/BusinessLayout";
import MasterNavigation from "../../Components/Business/MasterNavigation";
import ConfirmModal from "../../Components/ConfirmModal";

import "../../../css/business/supplier-payment.css";

export default function Create({
    accounts = [],
    payment_modes = [],
    selected_supplier = null,
    today = "",
}) {
    const supplierBoxRef = useRef(null);
    const searchRequestRef = useRef(null);

    const [supplier, setSupplier] = useState(selected_supplier);
    const [supplierSearch, setSupplierSearch] = useState(
        selected_supplier?.supplier_name ?? "",
    );
    const [supplierResults, setSupplierResults] = useState([]);
    const [supplierSearching, setSupplierSearching] = useState(false);
    const [supplierDropdown, setSupplierDropdown] = useState(false);

    const [purchases, setPurchases] = useState([]);
    const [purchasesLoading, setPurchasesLoading] = useState(false);
    const [purchaseError, setPurchaseError] = useState("");
    const [confirmation, setConfirmation] = useState(false);

    const {
        data,
        setData,
        post,
        processing,
        errors,
        clearErrors,
    } = useForm({
        supplier_id: selected_supplier?.id ?? "",
        purchase_id: "",
        account_id: "",
        payment_date: today,
        amount: "",
        payment_mode: "Cash",
        reference_no: "",
        remarks: "",
    });

    const selectedPurchase = useMemo(
        () => purchases.find(
            (purchase) => String(purchase.id) === String(data.purchase_id),
        ) ?? null,
        [purchases, data.purchase_id],
    );

    const selectedAccount = useMemo(
        () => accounts.find(
            (account) => String(account.id) === String(data.account_id),
        ) ?? null,
        [accounts, data.account_id],
    );

    useEffect(() => {
        const closeDropdown = (event) => {
            if (
                supplierBoxRef.current
                && !supplierBoxRef.current.contains(event.target)
            ) {
                setSupplierDropdown(false);
            }
        };

        document.addEventListener("mousedown", closeDropdown);

        return () => {
            document.removeEventListener("mousedown", closeDropdown);
            searchRequestRef.current?.abort();
        };
    }, []);

    useEffect(() => {
        if (!selected_supplier?.id) {
            return;
        }

        loadPurchases(selected_supplier.id);
    }, []);

    useEffect(() => {
        const query = supplierSearch.trim();

        if (supplier && query === supplier.supplier_name) {
            setSupplierResults([]);
            setSupplierDropdown(false);
            return undefined;
        }

        if (query.length < 2) {
            setSupplierResults([]);
            setSupplierSearching(false);
            return undefined;
        }

        const timer = window.setTimeout(async () => {
            searchRequestRef.current?.abort();

            const controller = new AbortController();
            searchRequestRef.current = controller;
            setSupplierSearching(true);

            try {
                const response = await axios.get(
                    "/supplier-payments/suppliers/search",
                    {
                        params: { search: query },
                        signal: controller.signal,
                    },
                );

                setSupplierResults(
                    Array.isArray(response.data?.data)
                        ? response.data.data
                        : [],
                );
                setSupplierDropdown(true);
            } catch (error) {
                if (error?.code !== "ERR_CANCELED") {
                    setSupplierResults([]);
                    setSupplierDropdown(true);
                }
            } finally {
                if (!controller.signal.aborted) {
                    setSupplierSearching(false);
                }
            }
        }, 320);

        return () => window.clearTimeout(timer);
    }, [supplierSearch, supplier]);

    const loadPurchases = async (supplierId) => {
        setPurchasesLoading(true);
        setPurchaseError("");
        setPurchases([]);

        try {
            const response = await axios.get(
                `/supplier-payments/suppliers/${supplierId}/purchases`,
            );

            setPurchases(
                Array.isArray(response.data?.data)
                    ? response.data.data
                    : [],
            );
        } catch {
            setPurchaseError(
                "Outstanding purchases could not be loaded. You can still record an advance payment.",
            );
        } finally {
            setPurchasesLoading(false);
        }
    };

    const selectSupplier = (record) => {
        setSupplier(record);
        setSupplierSearch(record.supplier_name);
        setSupplierResults([]);
        setSupplierDropdown(false);
        setData((current) => ({
            ...current,
            supplier_id: record.id,
            purchase_id: "",
            amount: "",
        }));
        clearErrors("supplier_id", "purchase_id", "amount");
        loadPurchases(record.id);
    };

    const clearSupplier = () => {
        searchRequestRef.current?.abort();
        setSupplier(null);
        setSupplierSearch("");
        setSupplierResults([]);
        setSupplierDropdown(false);
        setPurchases([]);
        setPurchaseError("");
        setData((current) => ({
            ...current,
            supplier_id: "",
            purchase_id: "",
            amount: "",
        }));
    };

    const changePurchase = (value) => {
        const purchase = purchases.find(
            (record) => String(record.id) === String(value),
        );

        setData((current) => ({
            ...current,
            purchase_id: value,
            amount: purchase
                ? Number(purchase.outstanding).toFixed(2)
                : "",
        }));
        clearErrors("purchase_id", "amount");
    };

    const changeAccount = (value) => {
        const account = accounts.find(
            (record) => String(record.id) === String(value),
        );

        const suggestedMode = account?.payment_mode;

        setData((current) => ({
            ...current,
            account_id: value,
            payment_mode: payment_modes.includes(suggestedMode)
                ? suggestedMode
                : current.payment_mode,
        }));
        clearErrors("account_id", "payment_mode");
    };

    const requestSubmit = (event) => {
        event.preventDefault();

        clearErrors();

        if (!data.supplier_id || !data.account_id || numberValue(data.amount) <= 0) {
            return;
        }

        if (
            selectedPurchase
            && numberValue(data.amount) > numberValue(selectedPurchase.outstanding)
        ) {
            return;
        }

        setConfirmation(true);
    };

    const savePayment = () => {
        post("/supplier-payments", {
            preserveScroll: true,
            onSuccess: () => setConfirmation(false),
            onError: () => setConfirmation(false),
        });
    };

    const amountInvalid = selectedPurchase
        && numberValue(data.amount) > numberValue(selectedPurchase.outstanding);

    return (
        <>
            <Head title="New Supplier Payment" />

            <div className="rr-sp-page rr-sp-create-page">
                <MasterNavigation />

                <header className="rr-sp-header rr-sp-create-header">
                    <div>
                        <span className="rr-sp-eyebrow">
                            <i className="bi bi-cash-stack"></i>
                            Accounts / Supplier Payments
                        </span>
                        <h1>Record Supplier Payment</h1>
                        <p>Create a branch-isolated payment and its linked account entry.</p>
                    </div>

                    <Link href="/supplier-payments" className="rr-sp-back-button">
                        <i className="bi bi-arrow-left"></i>
                        Payment Register
                    </Link>
                </header>

                <form onSubmit={requestSubmit} className="rr-sp-create-layout">
                    <main className="rr-sp-create-main">
                        <section className="rr-sp-form-panel">
                            <PanelHeading
                                icon="bi-truck"
                                title="Supplier"
                                caption="Search a supplier available in the active branch"
                                step="01"
                            />

                            <div className="rr-sp-form-body">
                                <div className="rr-sp-supplier-picker" ref={supplierBoxRef}>
                                    <label>
                                        Supplier <Required />
                                    </label>

                                    {supplier ? (
                                        <div className="rr-sp-selected-supplier">
                                            <span className="avatar">
                                                {initials(supplier.supplier_name)}
                                            </span>

                                            <div>
                                                <strong>{supplier.supplier_name}</strong>
                                                <small>
                                                    {supplier.supplier_code}
                                                    {supplier.phone ? ` • ${supplier.phone}` : ""}
                                                </small>
                                                <em>
                                                    Opening: {supplier.balance_type} {currency(supplier.opening_balance)}
                                                </em>
                                            </div>

                                            <button
                                                type="button"
                                                title="Clear supplier"
                                                onClick={clearSupplier}
                                            >
                                                <i className="bi bi-x-lg"></i>
                                            </button>
                                        </div>
                                    ) : (
                                        <div className="rr-sp-autocomplete">
                                            <i className="bi bi-search"></i>

                                            <input
                                                type="search"
                                                autoComplete="off"
                                                value={supplierSearch}
                                                placeholder="Type supplier name, code, phone or GST..."
                                                onFocus={() => {
                                                    if (supplierSearch.trim().length >= 2) {
                                                        setSupplierDropdown(true);
                                                    }
                                                }}
                                                onChange={(event) => {
                                                    setSupplierSearch(event.target.value);
                                                    setSupplierDropdown(true);
                                                }}
                                            />

                                            {supplierSearching && (
                                                <span className="spinner-border spinner-border-sm"></span>
                                            )}

                                            {supplierDropdown && (
                                                <div className="rr-sp-search-results">
                                                    {supplierResults.length ? (
                                                        supplierResults.map((record) => (
                                                            <button
                                                                type="button"
                                                                key={record.id}
                                                                onClick={() => selectSupplier(record)}
                                                            >
                                                                <span>{initials(record.supplier_name)}</span>
                                                                <div>
                                                                    <strong>{record.supplier_name}</strong>
                                                                    <small>
                                                                        {record.supplier_code}
                                                                        {record.phone ? ` • ${record.phone}` : ""}
                                                                    </small>
                                                                </div>
                                                                <i className="bi bi-chevron-right"></i>
                                                            </button>
                                                        ))
                                                    ) : (
                                                        <div className="rr-sp-no-result">
                                                            <i className="bi bi-search"></i>
                                                            <strong>No supplier found</strong>
                                                            <span>Try another name, code, phone or GST number.</span>
                                                        </div>
                                                    )}
                                                </div>
                                            )}
                                        </div>
                                    )}

                                    <FieldError message={errors.supplier_id} />
                                    {!supplier && (
                                        <small className="rr-sp-field-help">
                                            Enter at least two characters to search.
                                        </small>
                                    )}
                                </div>
                            </div>
                        </section>

                        <section className="rr-sp-form-panel">
                            <PanelHeading
                                icon="bi-receipt"
                                title="Purchase allocation"
                                caption="Allocate the payment to an outstanding purchase or keep it as an advance"
                                step="02"
                            />

                            <div className="rr-sp-form-body">
                                <div className="rr-sp-form-grid two">
                                    <FormField
                                        label="Outstanding purchase"
                                        icon="bi-file-earmark-text"
                                        error={errors.purchase_id}
                                        help="Leave as advance if this payment is not for one specific purchase."
                                    >
                                        <select
                                            value={data.purchase_id}
                                            disabled={!supplier || purchasesLoading}
                                            onChange={(event) => changePurchase(event.target.value)}
                                        >
                                            <option value="">
                                                {purchasesLoading
                                                    ? "Loading purchases..."
                                                    : "Advance / unallocated payment"}
                                            </option>

                                            {purchases.map((purchase) => (
                                                <option key={purchase.id} value={purchase.id}>
                                                    {purchase.purchase_no} — {dateValue(purchase.purchase_date)} — Due {currency(purchase.outstanding)}
                                                </option>
                                            ))}
                                        </select>
                                    </FormField>

                                    <div className="rr-sp-outstanding-card">
                                        <span>
                                            <i className="bi bi-calculator"></i>
                                            Available Outstanding
                                        </span>
                                        <strong>
                                            {selectedPurchase
                                                ? currency(selectedPurchase.outstanding)
                                                : "Advance payment"}
                                        </strong>
                                        <small>
                                            {selectedPurchase
                                                ? `Purchase total ${currency(selectedPurchase.grand_total)}`
                                                : "Payment will remain against the supplier account"}
                                        </small>
                                    </div>
                                </div>

                                {purchaseError && (
                                    <div className="rr-sp-inline-warning">
                                        <i className="bi bi-exclamation-triangle"></i>
                                        {purchaseError}
                                    </div>
                                )}

                                {supplier && !purchasesLoading && !purchaseError && purchases.length === 0 && (
                                    <div className="rr-sp-inline-info">
                                        <i className="bi bi-info-circle"></i>
                                        No outstanding purchases were found. You may record this as an advance payment.
                                    </div>
                                )}
                            </div>
                        </section>

                        <section className="rr-sp-form-panel">
                            <PanelHeading
                                icon="bi-wallet2"
                                title="Payment details"
                                caption="Select the source account and enter the payment information"
                                step="03"
                            />

                            <div className="rr-sp-form-body">
                                <div className="rr-sp-form-grid three">
                                    <FormField
                                        label="Payment date"
                                        icon="bi-calendar3"
                                        required
                                        error={errors.payment_date}
                                    >
                                        <DateInput
                                            value={data.payment_date}
                                            onChange={(value) => setData("payment_date", value)}
                                        />
                                    </FormField>

                                    <FormField
                                        label="Payment account"
                                        icon="bi-bank"
                                        required
                                        error={errors.account_id}
                                    >
                                        <select
                                            value={data.account_id}
                                            onChange={(event) => changeAccount(event.target.value)}
                                        >
                                            <option value="">Select cash or bank account</option>
                                            {accounts.map((account) => (
                                                <option key={account.id} value={account.id}>
                                                    {account.account_name} ({account.account_code})
                                                </option>
                                            ))}
                                        </select>
                                    </FormField>

                                    <FormField
                                        label="Payment mode"
                                        icon="bi-credit-card"
                                        required
                                        error={errors.payment_mode}
                                    >
                                        <select
                                            value={data.payment_mode}
                                            onChange={(event) => setData("payment_mode", event.target.value)}
                                        >
                                            {payment_modes.map((mode) => (
                                                <option key={mode} value={mode}>{mode}</option>
                                            ))}
                                        </select>
                                    </FormField>

                                    <FormField
                                        label="Amount"
                                        icon="bi-currency-rupee"
                                        required
                                        error={errors.amount || (amountInvalid
                                            ? `Amount cannot exceed ${currency(selectedPurchase?.outstanding)}.`
                                            : "")}
                                    >
                                        <div className="rr-sp-amount-input">
                                            <span>₹</span>
                                            <input
                                                type="number"
                                                min="0.01"
                                                max={selectedPurchase?.outstanding ?? undefined}
                                                step="0.01"
                                                value={data.amount}
                                                placeholder="0.00"
                                                onChange={(event) => setData("amount", event.target.value)}
                                            />
                                        </div>
                                    </FormField>

                                    <FormField
                                        label="Reference number"
                                        icon="bi-hash"
                                        error={errors.reference_no}
                                    >
                                        <input
                                            type="text"
                                            maxLength={150}
                                            value={data.reference_no}
                                            placeholder="UTR, cheque or transaction number"
                                            onChange={(event) => setData("reference_no", event.target.value)}
                                        />
                                    </FormField>

                                    <div className="rr-sp-account-preview">
                                        <span className="icon">
                                            <i className={paymentModeIcon(data.payment_mode)}></i>
                                        </span>
                                        <div>
                                            <small>Paying from</small>
                                            <strong>{selectedAccount?.account_name ?? "Select an account"}</strong>
                                            <em>{selectedAccount?.bank_name ?? data.payment_mode}</em>
                                        </div>
                                    </div>
                                </div>

                                <FormField
                                    label="Remarks"
                                    icon="bi-chat-left-text"
                                    error={errors.remarks}
                                >
                                    <textarea
                                        rows={3}
                                        maxLength={2000}
                                        value={data.remarks}
                                        placeholder="Optional payment notes..."
                                        onChange={(event) => setData("remarks", event.target.value)}
                                    ></textarea>
                                </FormField>
                            </div>
                        </section>
                    </main>

                    <aside className="rr-sp-create-summary">
                        <div className="rr-sp-summary-card">
                            <div className="rr-sp-summary-title">
                                <i className="bi bi-clipboard-check"></i>
                                <span>
                                    <strong>Payment Summary</strong>
                                    <small>Review before saving</small>
                                </span>
                            </div>

                            <SummaryRow label="Supplier" value={supplier?.supplier_name ?? "Not selected"} />
                            <SummaryRow
                                label="Allocation"
                                value={selectedPurchase?.purchase_no ?? "Advance"}
                            />
                            <SummaryRow label="Date" value={dateValue(data.payment_date)} />
                            <SummaryRow label="Account" value={selectedAccount?.account_name ?? "Not selected"} />
                            <SummaryRow label="Mode" value={data.payment_mode || "—"} />

                            <div className="rr-sp-summary-total">
                                <span>Payment Amount</span>
                                <strong>{currency(data.amount)}</strong>
                            </div>

                            <div className="rr-sp-audit-note">
                                <i className="bi bi-shield-check"></i>
                                <span>
                                    Saving creates a permanent account transaction. Cancellation creates a reversing entry.
                                </span>
                            </div>

                            <button
                                type="submit"
                                className="rr-sp-save-button"
                                disabled={
                                    processing
                                    || !data.supplier_id
                                    || !data.account_id
                                    || numberValue(data.amount) <= 0
                                    || amountInvalid
                                }
                            >
                                {processing ? (
                                    <span className="spinner-border spinner-border-sm"></span>
                                ) : (
                                    <i className="bi bi-check2-circle"></i>
                                )}
                                {processing ? "Saving..." : "Save Payment"}
                            </button>

                            <Link href="/supplier-payments" className="rr-sp-cancel-button">
                                Cancel and return
                            </Link>
                        </div>
                    </aside>
                </form>

                <ConfirmModal
                    show={confirmation}
                    title="Confirm Supplier Payment"
                    message={`Record ${currency(data.amount)} payment to "${supplier?.supplier_name ?? "supplier"}" using ${data.payment_mode}?`}
                    confirmText="Record Payment"
                    cancelText="Review Again"
                    icon="bi-check2-circle"
                    variant="primary"
                    processing={processing}
                    onConfirm={savePayment}
                    onCancel={() => {
                        if (!processing) setConfirmation(false);
                    }}
                />
            </div>
        </>
    );
}

function PanelHeading({ icon, title, caption, step }) {
    return (
        <div className="rr-sp-form-heading">
            <span className="icon"><i className={`bi ${icon}`}></i></span>
            <span>
                <strong>{title}</strong>
                <small>{caption}</small>
            </span>
            <em>{step}</em>
        </div>
    );
}

function FormField({ label, icon, required = false, error = "", help = "", children }) {
    return (
        <label className={`rr-sp-form-field ${error ? "has-error" : ""}`}>
            <span>
                <i className={`bi ${icon}`}></i>
                {label}
                {required && <Required />}
            </span>
            {children}
            <FieldError message={error} />
            {!error && help && <small className="rr-sp-field-help">{help}</small>}
        </label>
    );
}

function FieldError({ message }) {
    return message ? (
        <small className="rr-sp-field-error">
            <i className="bi bi-exclamation-circle"></i>
            {message}
        </small>
    ) : null;
}

function Required() {
    return <b className="rr-sp-required">*</b>;
}

function SummaryRow({ label, value }) {
    return (
        <div className="rr-sp-summary-row">
            <span>{label}</span>
            <strong title={value}>{value}</strong>
        </div>
    );
}

function DateInput({ value, onChange }) {
    const openPicker = (event) => {
        if (typeof event.currentTarget.showPicker === "function") {
            event.currentTarget.showPicker();
        }
    };

    return (
        <input
            type="date"
            value={value}
            onClick={openPicker}
            onChange={(event) => onChange(event.target.value)}
        />
    );
}

function numberValue(value) {
    const parsed = Number(value);
    return Number.isFinite(parsed) ? parsed : 0;
}

function currency(value) {
    return `₹${numberValue(value).toLocaleString("en-IN", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })}`;
}

function dateValue(value) {
    if (!value) return "—";
    const date = new Date(`${String(value).slice(0, 10)}T00:00:00`);
    return Number.isNaN(date.getTime())
        ? value
        : date.toLocaleDateString("en-IN", {
            day: "2-digit",
            month: "short",
            year: "numeric",
        });
}

function initials(value) {
    return String(value ?? "Supplier")
        .trim()
        .split(/\s+/)
        .filter(Boolean)
        .slice(0, 2)
        .map((word) => word[0])
        .join("")
        .toUpperCase();
}

function paymentModeIcon(mode) {
    const normalized = String(mode ?? "").toLowerCase();
    if (normalized === "cash") return "bi bi-cash";
    if (normalized.includes("bank") || normalized === "cheque") return "bi bi-bank";
    if (normalized === "card") return "bi bi-credit-card";
    return "bi bi-phone";
}

Create.layout = (page) => (
    <BusinessLayout title="New Supplier Payment">
        {page}
    </BusinessLayout>
);
