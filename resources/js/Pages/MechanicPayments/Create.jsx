import React, { useEffect, useMemo, useRef, useState } from "react";

import axios from "axios";
import { Head, Link, useForm } from "@inertiajs/react";

import BusinessLayout from "@/Layouts/BusinessLayout";
import MasterNavigation from "../../Components/Business/MasterNavigation";

import "../../../css/business/customer-payments.css";

export default function Create({
    accounts = [],
    payment_modes = [],
    selected_mechanic = null,
    default_date = "",
}) {
    const [mechanic, setMechanic] = useState(selected_mechanic);
    const [search, setSearch] = useState("");
    const [results, setResults] = useState([]);
    const [searching, setSearching] = useState(false);
    const [searchOpen, setSearchOpen] = useState(false);
    const [commissions, setCommissions] = useState([]);
    const [loadingCommissions, setLoadingCommissions] = useState(false);
    const searchRequest = useRef(0);

    const { data, setData, post, processing, errors } = useForm({
        mechanic_id: selected_mechanic?.id ?? "",
        account_id: "",
        payment_date: default_date,
        amount: "",
        payment_mode: "",
        reference_no: "",
        remarks: "",
        allocations: [],
    });

    useEffect(() => {
        if (!mechanic?.id) {
            setCommissions([]);
            setData("allocations", []);
            return;
        }

        loadCommissions(mechanic.id);
    }, [mechanic?.id]);

    useEffect(() => {
        const term = search.trim();

        if (!searchOpen || mechanic || term.length < 1) {
            setResults([]);
            setSearching(false);
            return undefined;
        }

        const timer = window.setTimeout(async () => {
            const requestId = ++searchRequest.current;
            setSearching(true);

            try {
                const response = await axios.get(
                    "/mechanic-payments/mechanics/search",
                    { params: { search: term } },
                );

                if (requestId === searchRequest.current) {
                    setResults(response.data?.data ?? []);
                }
            } catch {
                if (requestId === searchRequest.current) {
                    setResults([]);
                }
            } finally {
                if (requestId === searchRequest.current) {
                    setSearching(false);
                }
            }
        }, 280);

        return () => window.clearTimeout(timer);
    }, [search, searchOpen, mechanic]);

    const allocationMap = useMemo(
        () =>
            new Map(
                data.allocations.map((allocation) => [
                    Number(allocation.mechanic_commission_id),
                    numberValue(allocation.allocated_amount),
                ]),
            ),
        [data.allocations],
    );

    const paymentAmount = numberValue(data.amount);

    const allocatedAmount = useMemo(
        () =>
            data.allocations.reduce(
                (total, allocation) =>
                    total + numberValue(allocation.allocated_amount),
                0,
            ),
        [data.allocations],
    );

    const remainingAmount = Math.max(
        0,
        paymentAmount - allocatedAmount,
    );

    const overAllocated = allocatedAmount > paymentAmount;

    const outstandingTotal = commissions.reduce(
        (total, commission) =>
            total + numberValue(commission.balance_amount),
        0,
    );

    const loadCommissions = async (mechanicId) => {
        setLoadingCommissions(true);

        try {
            const response = await axios.get(
                `/mechanic-payments/mechanics/${mechanicId}/commissions`,
            );

            setCommissions(response.data?.commissions ?? []);

            setMechanic((current) =>
                current
                    ? {
                        ...current,
                        balance: response.data?.ledger_balance ?? 0,
                        payable_amount:
                            response.data?.ledger_balance > 0
                                ? response.data.ledger_balance
                                : 0,
                        advance_amount:
                            response.data?.ledger_balance < 0
                                ? Math.abs(response.data.ledger_balance)
                                : 0,
                    }
                    : current,
            );
        } catch {
            setCommissions([]);
        } finally {
            setLoadingCommissions(false);
        }
    };

    const selectMechanic = (selected) => {
        setMechanic(selected);
        setData((current) => ({
            ...current,
            mechanic_id: selected.id,
            allocations: [],
        }));
        setSearch("");
        setResults([]);
        setSearchOpen(false);
    };

    const clearMechanic = () => {
        setMechanic(null);
        setSearch("");
        setResults([]);
        setCommissions([]);
        setSearchOpen(true);
        setData((current) => ({
            ...current,
            mechanic_id: "",
            allocations: [],
        }));
    };

    const selectAccount = (accountId) => {
        const account = accounts.find(
            (item) => String(item.id) === String(accountId),
        );

        setData((current) => ({
            ...current,
            account_id: accountId,
            payment_mode:
                account?.payment_mode || current.payment_mode || "",
        }));
    };

    const updateAllocation = (commission, rawValue) => {
        const value = Math.max(0, numberValue(rawValue));
        const limited = Math.min(
            value,
            numberValue(commission.balance_amount),
        );

        setData(
            "allocations",
            replaceAllocation(
                data.allocations,
                commission.id,
                limited,
            ),
        );
    };

    const allocateCommission = (commission) => {
        const alreadyAllocated = allocatedAmount;
        const available = Math.max(0, paymentAmount - alreadyAllocated);
        const amount = Math.min(
            available,
            numberValue(commission.balance_amount),
        );

        updateAllocation(commission, amount);
    };

    const autoAllocate = () => {
        let available = paymentAmount;
        const next = [];

        commissions.forEach((commission) => {
            if (available <= 0) {
                return;
            }

            const amount = Math.min(
                available,
                numberValue(commission.balance_amount),
            );

            if (amount > 0) {
                next.push({
                    mechanic_commission_id: commission.id,
                    allocated_amount: moneyValue(amount),
                });

                available -= amount;
            }
        });

        setData("allocations", next);
    };

    const clearAllocations = () => {
        setData("allocations", []);
    };

    const submit = (event) => {
        event.preventDefault();

        if (!mechanic || paymentAmount <= 0 || overAllocated) {
            return;
        }

        post("/mechanic-payments", {
            preserveScroll: true,
        });
    };

    return (
        <>
            <Head title="New Mechanic Payment" />

            <div className="rr-cp-page">
                <MasterNavigation
                    active="mechanic-payments"
                    title="Accounts"
                    subtitle="Record mechanic commission payments"
                />

                <header className="rr-cp-header">
                    <div>
                        <span className="rr-cp-eyebrow">
                            <i className="bi bi-wrench-adjustable-circle" />
                            Accounts Payable
                        </span>

                        <h1>New Mechanic Payment</h1>

                        <p>
                            Allocate payment against commissions or retain the
                            remaining amount as mechanic advance.
                        </p>
                    </div>

                    <Link
                        href="/mechanic-payments"
                        className="rr-cp-back-button"
                    >
                        <i className="bi bi-arrow-left" />
                        Back to Register
                    </Link>
                </header>

                <form onSubmit={submit}>
                    <div className="rr-cp-create-grid">
                        <main className="rr-cp-create-main">
                            <FormCard
                                icon="bi-person-gear"
                                title="Select Mechanic"
                                subtitle="Search mechanics assigned to this branch"
                            >
                                <div className="rr-cp-customer-picker">
                                    {!mechanic ? (
                                        <>
                                            <div
                                                className={`rr-cp-live-search ${errors.mechanic_id
                                                        ? "invalid"
                                                        : ""
                                                    }`}
                                            >
                                                <i className="bi bi-search" />

                                                <input
                                                    type="search"
                                                    value={search}
                                                    placeholder="Search mechanic name, code or mobile"
                                                    autoComplete="off"
                                                    onFocus={() =>
                                                        setSearchOpen(true)
                                                    }
                                                    onChange={(event) => {
                                                        setSearch(
                                                            event.target.value,
                                                        );
                                                        setSearchOpen(true);
                                                    }}
                                                />

                                                {search && (
                                                    <button
                                                        type="button"
                                                        title="Clear search"
                                                        onClick={() =>
                                                            setSearch("")
                                                        }
                                                    >
                                                        <i className="bi bi-x-lg" />
                                                    </button>
                                                )}
                                            </div>

                                            {searchOpen && search.trim() && (
                                                <div className="rr-cp-search-results">
                                                    {searching ? (
                                                        <SearchState
                                                            icon="bi-arrow-repeat"
                                                            text="Searching mechanics…"
                                                        />
                                                    ) : results.length > 0 ? (
                                                        results.map((result) => (
                                                            <button
                                                                key={result.id}
                                                                type="button"
                                                                onClick={() =>
                                                                    selectMechanic(
                                                                        result,
                                                                    )
                                                                }
                                                            >
                                                                <span className="avatar">
                                                                    {initials(
                                                                        result.mechanic_name,
                                                                    )}
                                                                </span>

                                                                <span className="identity">
                                                                    <strong>
                                                                        {
                                                                            result.mechanic_name
                                                                        }
                                                                    </strong>
                                                                    <small>
                                                                        {
                                                                            result.mechanic_code
                                                                        }
                                                                        {result.mobile
                                                                            ? ` · ${result.mobile}`
                                                                            : ""}
                                                                    </small>
                                                                </span>

                                                                <span
                                                                    className={`balance ${numberValue(
                                                                        result.balance,
                                                                    ) > 0
                                                                            ? "due"
                                                                            : ""
                                                                        }`}
                                                                >
                                                                    <small>
                                                                        Payable
                                                                    </small>
                                                                    <strong>
                                                                        {currency(
                                                                            Math.max(
                                                                                0,
                                                                                numberValue(
                                                                                    result.balance,
                                                                                ),
                                                                            ),
                                                                        )}
                                                                    </strong>
                                                                </span>
                                                            </button>
                                                        ))
                                                    ) : (
                                                        <SearchState
                                                            icon="bi-person-x"
                                                            text="No mechanics found"
                                                        />
                                                    )}
                                                </div>
                                            )}
                                        </>
                                    ) : (
                                        <div className="rr-cp-selected-customer">
                                            <span className="avatar">
                                                {initials(
                                                    mechanic.mechanic_name,
                                                )}
                                            </span>

                                            <span className="identity">
                                                <small>Selected Mechanic</small>
                                                <strong>
                                                    {mechanic.mechanic_name}
                                                </strong>
                                                <span>
                                                    {mechanic.mechanic_code}
                                                    {mechanic.mobile
                                                        ? ` · ${mechanic.mobile}`
                                                        : ""}
                                                </span>
                                            </span>

                                            <span
                                                className={`balance ${numberValue(
                                                    mechanic.advance_amount,
                                                ) > 0
                                                        ? "advance"
                                                        : ""
                                                    }`}
                                            >
                                                <small>
                                                    {numberValue(
                                                        mechanic.advance_amount,
                                                    ) > 0
                                                        ? "Mechanic Advance"
                                                        : "Branch Payable"}
                                                </small>
                                                <strong>
                                                    {currency(
                                                        numberValue(
                                                            mechanic.advance_amount,
                                                        ) > 0
                                                            ? mechanic.advance_amount
                                                            : mechanic.payable_amount,
                                                    )}
                                                </strong>
                                            </span>

                                            <button
                                                type="button"
                                                title="Change mechanic"
                                                onClick={clearMechanic}
                                            >
                                                <i className="bi bi-x-lg" />
                                            </button>
                                        </div>
                                    )}

                                    <FieldError message={errors.mechanic_id} />
                                </div>
                            </FormCard>

                            <FormCard
                                icon="bi-list-check"
                                title="Outstanding Commissions"
                                subtitle="Allocate this payment to one or more invoices"
                                action={
                                    <div className="rr-cp-panel-actions">
                                        <button
                                            type="button"
                                            disabled={
                                                !mechanic ||
                                                paymentAmount <= 0 ||
                                                commissions.length === 0
                                            }
                                            onClick={autoAllocate}
                                        >
                                            <i className="bi bi-magic" />
                                            Auto Allocate
                                        </button>

                                        {allocatedAmount > 0 && (
                                            <button
                                                type="button"
                                                onClick={clearAllocations}
                                            >
                                                <i className="bi bi-eraser" />
                                                Clear
                                            </button>
                                        )}
                                    </div>
                                }
                            >
                                {!mechanic ? (
                                    <EmptyState
                                        icon="bi-person-check"
                                        title="Select a mechanic first"
                                        text="Outstanding commissions will appear here."
                                    />
                                ) : loadingCommissions ? (
                                    <EmptyState
                                        icon="bi-arrow-repeat"
                                        title="Loading commissions"
                                        text="Please wait a moment…"
                                    />
                                ) : commissions.length === 0 ? (
                                    <EmptyState
                                        icon="bi-check2-circle"
                                        title="No outstanding commissions"
                                        text="The mechanic has no pending commission in this branch. You can still record an advance payment."
                                    />
                                ) : (
                                    <div className="rr-cp-invoice-wrap">
                                        <table className="rr-cp-invoice-table">
                                            <thead>
                                                <tr>
                                                    <th>Invoice</th>
                                                    <th>Date</th>
                                                    <th className="money">
                                                        Original
                                                    </th>
                                                    <th className="money">
                                                        Returned
                                                    </th>
                                                    <th className="money">
                                                        Effective
                                                    </th>
                                                    <th className="money">
                                                        Paid
                                                    </th>
                                                    <th className="money">
                                                        Balance
                                                    </th>
                                                    <th className="allocation">
                                                        Allocate
                                                    </th>
                                                </tr>
                                            </thead>

                                            <tbody>
                                                {commissions.map(
                                                    (commission) => {
                                                        const allocated =
                                                            allocationMap.get(
                                                                Number(
                                                                    commission.id,
                                                                ),
                                                            ) ?? 0;

                                                        return (
                                                            <tr
                                                                key={
                                                                    commission.id
                                                                }
                                                                className={
                                                                    allocated > 0
                                                                        ? "selected"
                                                                        : ""
                                                                }
                                                            >
                                                                <td>
                                                                    <strong>
                                                                        {commission.invoice_no ??
                                                                            `Commission #${commission.id}`}
                                                                    </strong>
                                                                    <small>
                                                                        {
                                                                            commission.status
                                                                        }
                                                                    </small>
                                                                </td>

                                                                <td>
                                                                    {dateValue(
                                                                        commission.invoice_date,
                                                                    )}
                                                                </td>

                                                                <td className="money">
                                                                    {currency(
                                                                        commission.commission_amount,
                                                                    )}
                                                                </td>

                                                                <td className="money returned">
                                                                    {currency(
                                                                        commission.returned_commission_amount,
                                                                    )}
                                                                </td>

                                                                <td className="money effective">
                                                                    {currency(
                                                                        commission.effective_commission_amount,
                                                                    )}
                                                                </td>

                                                                <td className="money">
                                                                    {currency(
                                                                        commission.paid_amount,
                                                                    )}
                                                                </td>

                                                                <td className="money outstanding">
                                                                    {currency(
                                                                        commission.balance_amount,
                                                                    )}
                                                                </td>

                                                                <td className="allocation">
                                                                    <div className="rr-cp-allocation-input">
                                                                        <span>₹</span>
                                                                        <input
                                                                            type="number"
                                                                            min="0"
                                                                            step="0.01"
                                                                            max={
                                                                                commission.balance_amount
                                                                            }
                                                                            value={
                                                                                allocated ||
                                                                                ""
                                                                            }
                                                                            onChange={(
                                                                                event,
                                                                            ) =>
                                                                                updateAllocation(
                                                                                    commission,
                                                                                    event
                                                                                        .target
                                                                                        .value,
                                                                                )
                                                                            }
                                                                        />
                                                                        <button
                                                                            type="button"
                                                                            disabled={
                                                                                paymentAmount <=
                                                                                allocatedAmount ||
                                                                                numberValue(
                                                                                    commission.balance_amount,
                                                                                ) <=
                                                                                allocated
                                                                            }
                                                                            onClick={() =>
                                                                                allocateCommission(
                                                                                    commission,
                                                                                )
                                                                            }
                                                                        >
                                                                            MAX
                                                                        </button>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        );
                                                    },
                                                )}
                                            </tbody>
                                        </table>
                                    </div>
                                )}

                                <FieldError message={errors.allocations} />
                            </FormCard>

                            <FormCard
                                icon="bi-chat-left-text"
                                title="Reference & Remarks"
                                subtitle="Optional bank reference and payment notes"
                            >
                                <div className="rr-cp-form-grid two">
                                    <InputField
                                        label="Reference Number"
                                        value={data.reference_no}
                                        error={errors.reference_no}
                                        placeholder="Cheque / UTR / transaction ID"
                                        onChange={(value) =>
                                            setData("reference_no", value)
                                        }
                                    />

                                    <label className="rr-cp-field">
                                        <span>Remarks</span>
                                        <input
                                            type="text"
                                            value={data.remarks}
                                            placeholder="Optional payment notes"
                                            onChange={(event) =>
                                                setData(
                                                    "remarks",
                                                    event.target.value,
                                                )
                                            }
                                        />
                                        <FieldError message={errors.remarks} />
                                    </label>
                                </div>
                            </FormCard>
                        </main>

                        <aside className="rr-cp-payment-sidebar">
                            <FormCard
                                icon="bi-cash-stack"
                                title="Payment Details"
                                subtitle="Select the source account and amount"
                            >
                                <div className="rr-cp-form-stack">
                                    <label className="rr-cp-field">
                                        <span>Payment Date *</span>
                                        <input
                                            type="date"
                                            value={data.payment_date}
                                            onChange={(event) =>
                                                setData(
                                                    "payment_date",
                                                    event.target.value,
                                                )
                                            }
                                        />
                                        <FieldError
                                            message={errors.payment_date}
                                        />
                                    </label>

                                    <label className="rr-cp-field">
                                        <span>Cash / Bank Account *</span>
                                        <select
                                            value={data.account_id}
                                            onChange={(event) =>
                                                selectAccount(
                                                    event.target.value,
                                                )
                                            }
                                        >
                                            <option value="">
                                                Select account
                                            </option>
                                            {accounts.map((account) => (
                                                <option
                                                    key={account.id}
                                                    value={account.id}
                                                >
                                                    {account.account_name}
                                                </option>
                                            ))}
                                        </select>
                                        <FieldError
                                            message={errors.account_id}
                                        />
                                    </label>

                                    <label className="rr-cp-field">
                                        <span>Payment Mode *</span>
                                        <select
                                            value={data.payment_mode}
                                            onChange={(event) =>
                                                setData(
                                                    "payment_mode",
                                                    event.target.value,
                                                )
                                            }
                                        >
                                            <option value="">
                                                Select mode
                                            </option>
                                            {payment_modes.map((mode) => (
                                                <option key={mode} value={mode}>
                                                    {mode}
                                                </option>
                                            ))}
                                        </select>
                                        <FieldError
                                            message={errors.payment_mode}
                                        />
                                    </label>

                                    <label className="rr-cp-field">
                                        <span>Payment Amount *</span>
                                        <div className="rr-cp-amount-input">
                                            <span>₹</span>
                                            <input
                                                type="number"
                                                min="0.01"
                                                step="0.01"
                                                value={data.amount}
                                                placeholder="0.00"
                                                onChange={(event) =>
                                                    setData(
                                                        "amount",
                                                        event.target.value,
                                                    )
                                                }
                                            />
                                        </div>
                                        <FieldError message={errors.amount} />
                                    </label>
                                </div>
                            </FormCard>

                            <section className="rr-cp-allocation-summary">
                                <header>
                                    <i className="bi bi-calculator" />
                                    Payment Summary
                                </header>

                                <dl>
                                    <div>
                                        <dt>Outstanding commissions</dt>
                                        <dd>{currency(outstandingTotal)}</dd>
                                    </div>
                                    <div>
                                        <dt>Payment amount</dt>
                                        <dd>{currency(paymentAmount)}</dd>
                                    </div>
                                    <div>
                                        <dt>Allocated</dt>
                                        <dd>{currency(allocatedAmount)}</dd>
                                    </div>
                                    <div className="advance">
                                        <dt>Advance / Unallocated</dt>
                                        <dd>{currency(remainingAmount)}</dd>
                                    </div>
                                </dl>

                                {overAllocated && (
                                    <div className="rr-cp-summary-error">
                                        <i className="bi bi-exclamation-triangle" />
                                        Allocation exceeds payment amount.
                                    </div>
                                )}
                            </section>

                            <div className="rr-cp-submit-actions">
                                <Link
                                    href="/mechanic-payments"
                                    className="secondary"
                                >
                                    Cancel
                                </Link>

                                <button
                                    type="submit"
                                    disabled={
                                        processing ||
                                        !mechanic ||
                                        !data.account_id ||
                                        !data.payment_mode ||
                                        paymentAmount <= 0 ||
                                        overAllocated
                                    }
                                >
                                    {processing ? (
                                        <>
                                            <i className="bi bi-arrow-repeat" />
                                            Saving Payment…
                                        </>
                                    ) : (
                                        <>
                                            <i className="bi bi-check2-circle" />
                                            Save Mechanic Payment
                                        </>
                                    )}
                                </button>
                            </div>
                        </aside>
                    </div>
                </form>
            </div>
        </>
    );
}

function FormCard({ icon, title, subtitle, action, children }) {
    return (
        <section className="rr-cp-form-card">
            <header>
                <div>
                    <span>
                        <i className={`bi ${icon}`} />
                    </span>
                    <div>
                        <strong>{title}</strong>
                        <small>{subtitle}</small>
                    </div>
                </div>
                {action}
            </header>
            <div className="rr-cp-form-card-body">{children}</div>
        </section>
    );
}

function InputField({ label, value, placeholder, error, onChange }) {
    return (
        <label className="rr-cp-field">
            <span>{label}</span>
            <input
                type="text"
                value={value}
                placeholder={placeholder}
                onChange={(event) => onChange(event.target.value)}
            />
            <FieldError message={error} />
        </label>
    );
}

function SearchState({ icon, text }) {
    return (
        <div className="rr-cp-search-state">
            <i className={`bi ${icon}`} />
            {text}
        </div>
    );
}

function EmptyState({ icon, title, text }) {
    return (
        <div className="rr-cp-empty">
            <i className={`bi ${icon}`} />
            <strong>{title}</strong>
            <span>{text}</span>
        </div>
    );
}

function FieldError({ message }) {
    if (!message) {
        return null;
    }

    return <small className="rr-cp-field-error">{message}</small>;
}

function replaceAllocation(allocations, commissionId, amount) {
    const filtered = allocations.filter(
        (item) =>
            Number(item.mechanic_commission_id) !== Number(commissionId),
    );

    if (amount <= 0) {
        return filtered;
    }

    return [
        ...filtered,
        {
            mechanic_commission_id: commissionId,
            allocated_amount: moneyValue(amount),
        },
    ];
}

function numberValue(value) {
    const number = Number(value);
    return Number.isFinite(number) ? number : 0;
}

function moneyValue(value) {
    return numberValue(value).toFixed(2);
}

function currency(value) {
    return `₹${numberValue(value).toLocaleString("en-IN", {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    })}`;
}

function dateValue(value) {
    if (!value) {
        return "—";
    }

    const date = new Date(`${String(value).slice(0, 10)}T00:00:00`);

    return Number.isNaN(date.getTime())
        ? value
        : date.toLocaleDateString("en-IN", {
            day: "2-digit",
            month: "short",
            year: "numeric",
        });
}

function initials(name) {
    return (
        String(name ?? "M")
            .trim()
            .split(/\s+/)
            .filter(Boolean)
            .slice(0, 2)
            .map((part) => part[0]?.toUpperCase())
            .join("") || "M"
    );
}

Create.layout = (page) => (
    <BusinessLayout title="New Mechanic Payment">
        {page}
    </BusinessLayout>
);
