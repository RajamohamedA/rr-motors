import React from "react";
import { Link } from "@inertiajs/react";

export default function AccountForm({
    data,
    setData,
    errors = {},
    accountTypes = {},
    paymentModes = {},
    processing = false,
    submitDisabled = false,
    mode = "create",
    isSystem = false,
    onSubmit,
}) {
    const isBank = data.account_type === "bank";
    const isUpi = data.account_type === "upi";

    const updateType = (type) => {
        const suggestedMode = {
            cash: "cash",
            bank: "bank_transfer",
            upi: "gpay",
            card: "card",
            other: "other",
        }[type];

        setData((current) => ({
            ...current,
            account_type: type,
            payment_mode: suggestedMode ?? current.payment_mode,
            bank_name: type === "bank" ? current.bank_name : "",
            account_number: type === "bank" ? current.account_number : "",
            upi_id: type === "upi" ? current.upi_id : "",
        }));
    };

    return (
        <form onSubmit={onSubmit} noValidate>
            <div className="rr-acc-form-layout">
                <section className="rr-acc-form-card">
                    <CardHeading
                        icon="bi-bank"
                        title="Account Information"
                        text="Enter the branch payment account and its opening balance."
                    />

                    {isSystem && (
                        <div className="rr-acc-alert success rr-acc-inline-note">
                            <i className="bi bi-shield-check" />
                            <span>
                                This is a protected system account. Code, type and payment
                                channel cannot be changed.
                            </span>
                        </div>
                    )}

                    <div className="rr-acc-form-grid">
                        <FormField
                            label="Account Code"
                            icon="bi-upc-scan"
                            error={errors.account_code}
                            required
                        >
                            <input
                                type="text"
                                value={data.account_code}
                                maxLength={30}
                                autoFocus={mode === "create"}
                                disabled={isSystem}
                                placeholder="Example: BNK0001"
                                onChange={(event) =>
                                    setData("account_code", event.target.value.toUpperCase())
                                }
                            />
                        </FormField>

                        <FormField
                            label="Account Name"
                            icon="bi-card-heading"
                            error={errors.account_name}
                            required
                        >
                            <input
                                type="text"
                                value={data.account_name}
                                maxLength={150}
                                placeholder="Example: Indian Bank Current Account"
                                onChange={(event) =>
                                    setData("account_name", event.target.value)
                                }
                            />
                        </FormField>

                        <FormField
                            label="Account Type"
                            icon="bi-grid"
                            error={errors.account_type}
                            required
                        >
                            <select
                                value={data.account_type}
                                disabled={isSystem}
                                onChange={(event) => updateType(event.target.value)}
                            >
                                {Object.entries(accountTypes).map(([value, label]) => (
                                    <option key={value} value={value}>
                                        {label}
                                    </option>
                                ))}
                            </select>
                        </FormField>

                        <FormField
                            label="Payment Channel"
                            icon="bi-credit-card-2-front"
                            error={errors.payment_mode}
                        >
                            <select
                                value={data.payment_mode ?? ""}
                                disabled={isSystem}
                                onChange={(event) =>
                                    setData("payment_mode", event.target.value)
                                }
                            >
                                <option value="">Select payment channel</option>
                                {Object.entries(paymentModes).map(([value, label]) => (
                                    <option key={value} value={value}>
                                        {label}
                                    </option>
                                ))}
                            </select>
                        </FormField>

                        {isBank && (
                            <>
                                <FormField
                                    label="Bank Name"
                                    icon="bi-building"
                                    error={errors.bank_name}
                                    required
                                >
                                    <input
                                        type="text"
                                        value={data.bank_name ?? ""}
                                        maxLength={150}
                                        placeholder="Enter bank name"
                                        onChange={(event) =>
                                            setData("bank_name", event.target.value)
                                        }
                                    />
                                </FormField>

                                <FormField
                                    label="Account Number"
                                    icon="bi-hash"
                                    error={errors.account_number}
                                    required
                                >
                                    <input
                                        type="text"
                                        value={data.account_number ?? ""}
                                        maxLength={50}
                                        inputMode="numeric"
                                        autoComplete="off"
                                        placeholder="Enter bank account number"
                                        onChange={(event) =>
                                            setData("account_number", event.target.value)
                                        }
                                    />
                                </FormField>
                            </>
                        )}

                        {isUpi && (
                            <FormField
                                label="UPI ID"
                                icon="bi-phone"
                                error={errors.upi_id}
                                required
                            >
                                <input
                                    type="text"
                                    value={data.upi_id ?? ""}
                                    maxLength={100}
                                    autoComplete="off"
                                    placeholder="Example: shopname@okaxis"
                                    onChange={(event) => setData("upi_id", event.target.value)}
                                />
                            </FormField>
                        )}

                        <FormField
                            label="Opening Balance"
                            icon="bi-currency-rupee"
                            error={errors.opening_balance}
                            required
                        >
                            <input
                                type="number"
                                value={data.opening_balance}
                                min="0"
                                step="0.01"
                                inputMode="decimal"
                                placeholder="0.00"
                                onChange={(event) =>
                                    setData("opening_balance", event.target.value)
                                }
                            />
                        </FormField>

                        <FormField
                            label="Opening Balance Date"
                            icon="bi-calendar3"
                            error={errors.opening_balance_date}
                        >
                            <input
                                type="date"
                                value={data.opening_balance_date ?? ""}
                                onClick={openDatePicker}
                                onFocus={openDatePicker}
                                onChange={(event) =>
                                    setData("opening_balance_date", event.target.value)
                                }
                            />
                        </FormField>

                        <div className="rr-acc-field full">
                            <span>
                                <i className="bi bi-toggle-on" />
                                Account Status
                            </span>
                            <label className="rr-acc-status-switch">
                                <input
                                    type="checkbox"
                                    checked={Boolean(data.status)}
                                    onChange={(event) => setData("status", event.target.checked)}
                                />
                                <span className="rr-acc-switch-track" aria-hidden="true">
                                    <span />
                                </span>
                                <strong>{data.status ? "Active" : "Inactive"}</strong>
                                <small>
                                    {data.status
                                        ? "Available for new receipts and payments"
                                        : "Hidden from new transaction forms"}
                                </small>
                            </label>
                            {errors.status && (
                                <small className="rr-acc-error">{errors.status}</small>
                            )}
                        </div>
                    </div>

                    <footer className="rr-acc-form-footer">
                        <Link className="rr-acc-btn secondary" href="/accounts">
                            <i className="bi bi-x-lg" />
                            Cancel
                        </Link>
                        <button
                            type="submit"
                            className="rr-acc-btn primary"
                            disabled={processing || submitDisabled}
                        >
                            {processing ? (
                                <>
                                    <span className="spinner-border spinner-border-sm" />
                                    Saving...
                                </>
                            ) : (
                                <>
                                    <i className="bi bi-floppy" />
                                    {mode === "edit" ? "Update Account" : "Save Account"}
                                </>
                            )}
                        </button>
                    </footer>
                </section>

                <aside className="rr-acc-form-card rr-acc-help-card">
                    <CardHeading
                        icon="bi-info-circle"
                        title="Account Setup Guide"
                        text="Use one account for each collection channel."
                    />

                    <ul className="rr-acc-help-list">
                        <HelpItem
                            icon="bi-cash-stack"
                            title="Cash"
                            text="Use Cash in Hand for physical cash received or paid at this branch."
                        />
                        <HelpItem
                            icon="bi-bank"
                            title="Bank"
                            text="Create a separate account for every bank account used by the branch."
                        />
                        <HelpItem
                            icon="bi-phone"
                            title="UPI / GPay"
                            text="Use a separate UPI account when you need digital collections reported independently."
                        />
                        <HelpItem
                            icon="bi-calculator"
                            title="Opening Balance"
                            text="Enter the actual available amount as of the selected opening date."
                        />
                        <HelpItem
                            icon="bi-shield-lock"
                            title="Branch Isolation"
                            text="The account is created only for the currently selected company and branch."
                        />
                    </ul>
                </aside>
            </div>
        </form>
    );
}

function CardHeading({ icon, title, text }) {
    return (
        <header className="rr-acc-card-heading">
            <div>
                <i className={`bi ${icon}`} />
                <div>
                    <h2>{title}</h2>
                    <p>{text}</p>
                </div>
            </div>
        </header>
    );
}

function FormField({ label, icon, error, required = false, children }) {
    return (
        <label className="rr-acc-field">
            <span>
                <i className={`bi ${icon}`} />
                {label}
                {required && <b className="rr-acc-required">*</b>}
            </span>
            <div className="rr-acc-input-shell">{children}</div>
            {error && <small className="rr-acc-error">{error}</small>}
        </label>
    );
}

function HelpItem({ icon, title, text }) {
    return (
        <li>
            <i className={`bi ${icon}`} />
            <span>
                <strong>{title}</strong>
                <small>{text}</small>
            </span>
        </li>
    );
}

function openDatePicker(event) {
    if (typeof event.currentTarget.showPicker !== "function") return;

    try {
        event.currentTarget.showPicker();
    } catch {
        // Normal browser click remains available when showPicker is unsupported.
    }
}
