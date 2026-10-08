import React, { useMemo, useState } from "react";
import { Head, Link, useForm } from "@inertiajs/react";

import BusinessLayout from "@/Layouts/BusinessLayout";
import MasterNavigation from "../../Components/Business/MasterNavigation";
import ConfirmModal from "../../Components/ConfirmModal";

import "../../../css/business/expenses.css";

export default function Create({
    accounts = [],
    categories = [],
    payment_modes: paymentModes = {},
    defaults = {},
    context = {},
}) {
    const firstAccount = accounts[0] ?? null;
    const { data, setData, post, processing, errors } = useForm({
        account_id: firstAccount ? String(firstAccount.id) : "",
        expense_date: defaults.expense_date ?? "",
        expense_category: "",
        payee_name: "",
        amount: "",
        payment_mode: firstAccount?.payment_mode ?? "cash",
        reference_no: "",
        remarks: "",
    });

    const [confirmOpen, setConfirmOpen] = useState(false);
    const selectedAccount = useMemo(
        () =>
            accounts.find(
                (account) => String(account.id) === String(data.account_id),
            ) ?? null,
        [accounts, data.account_id],
    );

    const numericAmount = Number(data.amount || 0);
    const availableBalance = Number(selectedAccount?.balance ?? 0);
    const projectedBalance = availableBalance - numericAmount;
    const amountExceedsBalance =
        numericAmount > 0 && numericAmount > availableBalance;

    const updateAccount = (accountId) => {
        const account = accounts.find(
            (item) => String(item.id) === String(accountId),
        );

        setData((current) => ({
            ...current,
            account_id: accountId,
            payment_mode: account?.payment_mode || current.payment_mode || "cash",
        }));
    };

    const requestSubmit = (event) => {
        event.preventDefault();
        if (processing || amountExceedsBalance) return;
        setConfirmOpen(true);
    };

    const submit = () => {
        if (processing || amountExceedsBalance) return;

        post("/expenses", {
            preserveScroll: true,
            onSuccess: () => setConfirmOpen(false),
            onError: () => setConfirmOpen(false),
        });
    };

    return (
        <>
            <Head title="Record Expense" />

            <main className="rr-exp-page rr-exp-create-page">
                <MasterNavigation
                    active="expenses"
                    title="Record Expense"
                    subtitle={
                        context.store_name
                            ? `Cash/Bank expense • ${context.store_name}`
                            : "Record a branch operating expense"
                    }
                />

                <section className="rr-exp-commandbar compact">
                    <div className="rr-exp-command-copy">
                        <span className="rr-exp-eyebrow">NEW EXPENSE</span>
                        <h1>Expense Payment Entry</h1>
                        <p>
                            Select the paying account and enter the expense details. Posting
                            this document reduces the selected account balance.
                        </p>
                    </div>

                    <Link href="/expenses" className="rr-exp-btn secondary">
                        <i className="bi bi-arrow-left" />
                        Expense Register
                    </Link>
                </section>

                <form className="rr-exp-form-shell" onSubmit={requestSubmit}>
                    <div className="rr-exp-form-main">
                        <section className="rr-exp-form-card">
                            <CardHeader
                                icon="bi-receipt-cutoff"
                                title="Expense Information"
                                text="Required document and payment details"
                                tone="orange"
                            />

                            <div className="rr-exp-form-grid">
                                <FormField
                                    label="Expense Date"
                                    icon="bi-calendar3"
                                    error={errors.expense_date}
                                    required
                                >
                                    <input
                                        type="date"
                                        value={data.expense_date}
                                        onChange={(event) =>
                                            setData("expense_date", event.target.value)
                                        }
                                        onClick={openDatePicker}
                                        onFocus={openDatePicker}
                                        required
                                    />
                                </FormField>

                                <FormField
                                    label="Expense Category"
                                    icon="bi-tags"
                                    error={errors.expense_category}
                                    required
                                >
                                    <input
                                        type="text"
                                        list="expense-category-options"
                                        value={data.expense_category}
                                        onChange={(event) =>
                                            setData("expense_category", event.target.value)
                                        }
                                        placeholder="Select or enter category"
                                        maxLength={100}
                                        autoComplete="off"
                                        required
                                    />
                                    <datalist id="expense-category-options">
                                        {categories.map((category) => (
                                            <option key={category} value={category} />
                                        ))}
                                    </datalist>
                                </FormField>

                                <FormField
                                    label="Paid To"
                                    icon="bi-person"
                                    error={errors.payee_name}
                                >
                                    <input
                                        type="text"
                                        value={data.payee_name}
                                        onChange={(event) =>
                                            setData("payee_name", event.target.value)
                                        }
                                        placeholder="Person or business name"
                                        maxLength={150}
                                    />
                                </FormField>

                                <FormField
                                    label="Amount"
                                    icon="bi-currency-rupee"
                                    error={errors.amount}
                                    required
                                >
                                    <div className="rr-exp-money-input">
                                        <span>₹</span>
                                        <input
                                            type="number"
                                            min="0.01"
                                            max="9999999999999.99"
                                            step="0.01"
                                            value={data.amount}
                                            onChange={(event) => setData("amount", event.target.value)}
                                            placeholder="0.00"
                                            required
                                        />
                                    </div>
                                </FormField>
                            </div>
                        </section>

                        <section className="rr-exp-form-card">
                            <CardHeader
                                icon="bi-bank"
                                title="Payment Information"
                                text="Cash, bank, card or UPI account posting"
                                tone="blue"
                            />

                            <div className="rr-exp-form-grid">
                                <FormField
                                    label="Payment Account"
                                    icon="bi-wallet2"
                                    error={errors.account_id}
                                    required
                                    wide
                                >
                                    <select
                                        value={data.account_id}
                                        onChange={(event) => updateAccount(event.target.value)}
                                        required
                                    >
                                        <option value="">Select payment account</option>
                                        {accounts.map((account) => (
                                            <option key={account.id} value={account.id}>
                                                {account.name} ({account.code}) — {money(account.balance)}
                                            </option>
                                        ))}
                                    </select>
                                </FormField>

                                <FormField
                                    label="Payment Mode"
                                    icon="bi-credit-card"
                                    error={errors.payment_mode}
                                    required
                                >
                                    <select
                                        value={data.payment_mode}
                                        onChange={(event) =>
                                            setData("payment_mode", event.target.value)
                                        }
                                        required
                                    >
                                        {Object.entries(paymentModes).map(([value, text]) => (
                                            <option key={value} value={value}>
                                                {text}
                                            </option>
                                        ))}
                                    </select>
                                </FormField>

                                <FormField
                                    label="Reference Number"
                                    icon="bi-hash"
                                    error={errors.reference_no}
                                >
                                    <input
                                        type="text"
                                        value={data.reference_no}
                                        onChange={(event) =>
                                            setData("reference_no", event.target.value)
                                        }
                                        placeholder="UTR, cheque or transaction no."
                                        maxLength={150}
                                    />
                                </FormField>
                            </div>
                        </section>

                        <section className="rr-exp-form-card">
                            <CardHeader
                                icon="bi-chat-left-text"
                                title="Narration"
                                text="Optional details for reports and audit"
                                tone="green"
                            />

                            <div className="rr-exp-form-grid one-column">
                                <FormField
                                    label="Remarks"
                                    icon="bi-pencil-square"
                                    error={errors.remarks}
                                >
                                    <textarea
                                        rows="4"
                                        value={data.remarks}
                                        onChange={(event) => setData("remarks", event.target.value)}
                                        placeholder="Purpose, bill details or any additional note"
                                        maxLength={2000}
                                    />
                                </FormField>
                            </div>
                        </section>
                    </div>

                    <aside className="rr-exp-form-aside">
                        <section className="rr-exp-balance-card">
                            <header>
                                <span>
                                    <i className="bi bi-bank" />
                                </span>
                                <div>
                                    <small>PAYMENT ACCOUNT</small>
                                    <strong>{selectedAccount?.name ?? "Not selected"}</strong>
                                </div>
                            </header>

                            <dl>
                                <div>
                                    <dt>Account Code</dt>
                                    <dd>{selectedAccount?.code ?? "—"}</dd>
                                </div>
                                <div>
                                    <dt>Account Type</dt>
                                    <dd>{label(selectedAccount?.type) || "—"}</dd>
                                </div>
                                <div>
                                    <dt>Available Balance</dt>
                                    <dd className="positive">{money(availableBalance)}</dd>
                                </div>
                                <div>
                                    <dt>Expense Amount</dt>
                                    <dd className="expense">− {money(numericAmount)}</dd>
                                </div>
                            </dl>

                            <div
                                className={`rr-exp-projected ${projectedBalance < 0 ? "negative" : ""
                                    }`}
                            >
                                <span>Projected Balance</span>
                                <strong>{money(projectedBalance)}</strong>
                            </div>

                            {amountExceedsBalance && (
                                <div className="rr-exp-balance-warning">
                                    <i className="bi bi-exclamation-triangle" />
                                    Expense exceeds the available account balance.
                                </div>
                            )}
                        </section>

                        <section className="rr-exp-posting-note">
                            <i className="bi bi-shield-check" />
                            <div>
                                <strong>Accounting Effect</strong>
                                <p>
                                    The selected Cash/Bank account will be credited. Cancellation
                                    creates a separate debit reversal entry.
                                </p>
                            </div>
                        </section>
                    </aside>

                    <footer className="rr-exp-form-footer">
                        <Link href="/expenses" className="rr-exp-btn secondary">
                            <i className="bi bi-x-lg" />
                            Cancel
                        </Link>

                        <button
                            type="submit"
                            className="rr-exp-btn primary save"
                            disabled={processing || amountExceedsBalance || !accounts.length}
                        >
                            {processing ? (
                                <>
                                    <span className="spinner-border spinner-border-sm" />
                                    Saving...
                                </>
                            ) : (
                                <>
                                    <i className="bi bi-check2-circle" />
                                    Record Expense
                                </>
                            )}
                        </button>
                    </footer>
                </form>
            </main>

            <ConfirmModal
                show={confirmOpen}
                title="Post Expense"
                message={`Post ${money(numericAmount)} as ${data.expense_category || "an expense"
                    } from ${selectedAccount?.name ?? "the selected account"}?`}
                confirmText="Post Expense"
                cancelText="Review Entry"
                icon="bi-check2-circle"
                variant="primary"
                processing={processing}
                confirmDisabled={amountExceedsBalance || numericAmount <= 0}
                onConfirm={submit}
                onCancel={() => !processing && setConfirmOpen(false)}
            />
        </>
    );
}

function CardHeader({ icon, title, text, tone }) {
    return (
        <header className={`rr-exp-form-card-head ${tone}`}>
            <span>
                <i className={`bi ${icon}`} />
            </span>
            <div>
                <strong>{title}</strong>
                <small>{text}</small>
            </div>
        </header>
    );
}

function FormField({
    label: fieldLabel,
    icon,
    error,
    required = false,
    wide = false,
    children,
}) {
    return (
        <label
            className={`rr-exp-form-field ${wide ? "wide" : ""} ${error ? "has-error" : ""
                }`}
        >
            <span className="rr-exp-form-label">
                <i className={`bi ${icon}`} />
                {fieldLabel}
                {required && <em>*</em>}
            </span>
            {children}
            {error && (
                <small className="rr-exp-error">
                    <i className="bi bi-exclamation-circle" />
                    {error}
                </small>
            )}
        </label>
    );
}

function openDatePicker(event) {
    if (typeof event.currentTarget?.showPicker !== "function") return;

    try {
        event.currentTarget.showPicker();
    } catch {
        // Some browsers permit showPicker only during a direct pointer action.
    }
}

function money(value) {
    return new Intl.NumberFormat("en-IN", {
        style: "currency",
        currency: "INR",
        minimumFractionDigits: 2,
    }).format(Number(value ?? 0));
}

function label(value) {
    return String(value ?? "")
        .replaceAll("_", " ")
        .replace(/\b\w/g, (character) => character.toUpperCase());
}

Create.layout = (page) => <BusinessLayout>{page}</BusinessLayout>;
