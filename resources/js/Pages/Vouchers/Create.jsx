import React, { useEffect, useMemo, useRef, useState } from "react";
import axios from "axios";
import { Head, Link, useForm } from "@inertiajs/react";

import BusinessLayout from "@/Layouts/BusinessLayout";
import MasterNavigation from "../../Components/Business/MasterNavigation";

import "../../../css/business/vouchers.css";

export default function Create({
    accounts = [],
    defaults = {},
    payment_modes: paymentModes = {},
    party_types: partyTypes = {},
    context = {},
}) {
    const initialType = defaults.voucher_type === "payment" ? "payment" : "receipt";

    const { data, setData, post, processing, errors, clearErrors } = useForm({
        voucher_type: initialType,
        account_id: accounts[0]?.id ? String(accounts[0].id) : "",
        voucher_date: defaults.voucher_date ?? "",
        amount: "",
        payment_mode: defaults.payment_mode ?? accounts[0]?.payment_mode ?? "cash",
        reference_no: "",
        party_type: "other",
        party_id: "",
        party_name: "",
        remarks: "",
    });

    const [partySearch, setPartySearch] = useState("");
    const [partyResults, setPartyResults] = useState([]);
    const [partyLoading, setPartyLoading] = useState(false);
    const [partyOpen, setPartyOpen] = useState(false);
    const [selectedParty, setSelectedParty] = useState(null);
    const partyBoxRef = useRef(null);

    const isReceipt = data.voucher_type === "receipt";
    const selectedAccount = useMemo(
        () => accounts.find((account) => String(account.id) === String(data.account_id)),
        [accounts, data.account_id],
    );
    const amount = Number(data.amount || 0);
    const balance = Number(selectedAccount?.balance ?? 0);
    const projectedBalance = isReceipt ? balance + amount : balance - amount;
    const insufficient = !isReceipt && amount > balance;

    useEffect(() => {
        if (!selectedAccount?.payment_mode) return;
        setData("payment_mode", selectedAccount.payment_mode);
    }, [selectedAccount?.id]);

    useEffect(() => {
        const close = (event) => {
            if (!partyBoxRef.current?.contains(event.target)) {
                setPartyOpen(false);
            }
        };

        document.addEventListener("mousedown", close);
        return () => document.removeEventListener("mousedown", close);
    }, []);

    useEffect(() => {
        if (data.party_type === "other") {
            setPartyResults([]);
            setPartyOpen(false);
            return undefined;
        }

        const search = partySearch.trim();
        const controller = new AbortController();
        const timer = window.setTimeout(async () => {
            setPartyLoading(true);

            try {
                const response = await axios.get("/vouchers/parties/search", {
                    params: {
                        type: data.party_type,
                        search,
                    },
                    signal: controller.signal,
                });

                setPartyResults(response.data?.data ?? []);
                setPartyOpen(true);
            } catch (error) {
                if (error?.code !== "ERR_CANCELED") {
                    setPartyResults([]);
                }
            } finally {
                if (!controller.signal.aborted) setPartyLoading(false);
            }
        }, 300);

        return () => {
            window.clearTimeout(timer);
            controller.abort();
        };
    }, [data.party_type, partySearch]);

    const changeVoucherType = (type) => {
        clearErrors();
        setData("voucher_type", type);
    };

    const changePartyType = (type) => {
        setSelectedParty(null);
        setPartySearch("");
        setPartyResults([]);
        setPartyOpen(false);
        setData((current) => ({
            ...current,
            party_type: type,
            party_id: "",
            party_name: "",
        }));
    };

    const chooseParty = (party) => {
        setSelectedParty(party);
        setPartySearch(party.name);
        setPartyOpen(false);
        setData((current) => ({
            ...current,
            party_id: String(party.id),
            party_name: party.name,
        }));
        clearErrors("party_id", "party_name");
    };

    const clearParty = () => {
        setSelectedParty(null);
        setPartySearch("");
        setPartyResults([]);
        setData((current) => ({
            ...current,
            party_id: "",
            party_name: "",
        }));
    };

    const submit = (event) => {
        event.preventDefault();
        if (processing || insufficient) return;

        post("/vouchers", {
            preserveScroll: true,
        });
    };

    return (
        <>
            <Head title={`New ${isReceipt ? "Receipt" : "Payment"} Voucher`} />

            <main className={`rr-vch-page rr-vch-create-page ${isReceipt ? "receipt" : "payment"}`}>
                <MasterNavigation
                    active="vouchers"
                    title="Receipt & Payment Vouchers"
                    subtitle={context.store_name ? `Current branch: ${context.store_name}` : "Accounting Vouchers"}
                />

                <section className="rr-vch-create-head">
                    <div className="rr-vch-create-title">
                        <span className="rr-vch-create-icon">
                            <i className={`bi ${isReceipt ? "bi-arrow-down-left" : "bi-arrow-up-right"}`} />
                        </span>
                        <div>
                            <span>ACCOUNTING ENTRY</span>
                            <h1>New {isReceipt ? "Receipt" : "Payment"} Voucher</h1>
                            <p>
                                {isReceipt
                                    ? "Record money received into a Cash, Bank, UPI or Card account."
                                    : "Record money paid from a Cash, Bank, UPI or Card account."}
                            </p>
                        </div>
                    </div>

                    <Link href="/vouchers" className="rr-vch-btn back">
                        <i className="bi bi-arrow-left" />
                        Voucher Register
                    </Link>
                </section>

                <form onSubmit={submit} className="rr-vch-entry-form" noValidate>
                    <section className="rr-vch-type-switch" aria-label="Voucher type">
                        <button
                            type="button"
                            className={isReceipt ? "active receipt" : "receipt"}
                            onClick={() => changeVoucherType("receipt")}
                        >
                            <span><i className="bi bi-box-arrow-in-down" /></span>
                            <div>
                                <strong>Receipt Voucher</strong>
                                <small>Money comes into the business</small>
                            </div>
                            <i className={`bi ${isReceipt ? "bi-check-circle-fill" : "bi-circle"}`} />
                        </button>

                        <button
                            type="button"
                            className={!isReceipt ? "active payment" : "payment"}
                            onClick={() => changeVoucherType("payment")}
                        >
                            <span><i className="bi bi-box-arrow-up-right" /></span>
                            <div>
                                <strong>Payment Voucher</strong>
                                <small>Money goes out of the business</small>
                            </div>
                            <i className={`bi ${!isReceipt ? "bi-check-circle-fill" : "bi-circle"}`} />
                        </button>
                    </section>

                    <div className="rr-vch-create-grid">
                        <section className="rr-vch-form-card">
                            <CardHeader
                                number="01"
                                icon="bi-bank"
                                title="Account & Amount"
                                text="Select where the money is received or paid"
                            />

                            <div className="rr-vch-form-body rr-vch-form-grid two">
                                <FormField label="Account" required error={errors.account_id} wide>
                                    <select
                                        value={data.account_id}
                                        onChange={(event) => setData("account_id", event.target.value)}
                                    >
                                        <option value="">Select Cash / Bank Account</option>
                                        {accounts.map((account) => (
                                            <option key={account.id} value={account.id}>
                                                {account.name} ({account.code}) — {money(account.balance)}
                                            </option>
                                        ))}
                                    </select>
                                </FormField>

                                <FormField label="Voucher Date" required error={errors.voucher_date}>
                                    <input
                                        type="date"
                                        value={data.voucher_date}
                                        onChange={(event) => setData("voucher_date", event.target.value)}
                                        onClick={openDatePicker}
                                        onFocus={openDatePicker}
                                    />
                                </FormField>

                                <FormField label="Amount" required error={errors.amount}>
                                    <div className="rr-vch-money-input">
                                        <span>₹</span>
                                        <input
                                            type="number"
                                            min="0.01"
                                            step="0.01"
                                            value={data.amount}
                                            onChange={(event) => setData("amount", event.target.value)}
                                            placeholder="0.00"
                                            autoFocus
                                        />
                                    </div>
                                </FormField>

                                <FormField label="Payment Mode" required error={errors.payment_mode}>
                                    <select
                                        value={data.payment_mode}
                                        onChange={(event) => setData("payment_mode", event.target.value)}
                                    >
                                        {optionEntries(paymentModes).map(([value, text]) => (
                                            <option key={value} value={value}>{text}</option>
                                        ))}
                                    </select>
                                </FormField>

                                <FormField label="Bank / UPI Reference" error={errors.reference_no} wide>
                                    <input
                                        type="text"
                                        value={data.reference_no}
                                        onChange={(event) => setData("reference_no", event.target.value)}
                                        placeholder="Cheque no, UTR, transaction ID or reference"
                                        maxLength="150"
                                    />
                                </FormField>
                            </div>
                        </section>

                        <aside className={`rr-vch-balance-card ${insufficient ? "danger" : ""}`}>
                            <header>
                                <i className="bi bi-calculator" />
                                <span>
                                    <strong>Balance Preview</strong>
                                    <small>{selectedAccount?.name ?? "Select an account"}</small>
                                </span>
                            </header>

                            <div className="rr-vch-balance-lines">
                                <BalanceLine label="Current Balance" value={money(balance)} />
                                <BalanceLine
                                    label={isReceipt ? "Receipt Amount" : "Payment Amount"}
                                    value={`${isReceipt ? "+" : "−"} ${money(amount)}`}
                                    tone={isReceipt ? "receipt" : "payment"}
                                />
                                <BalanceLine
                                    label="Projected Balance"
                                    value={money(projectedBalance)}
                                    tone={projectedBalance < 0 ? "danger" : "projected"}
                                    total
                                />
                            </div>

                            {insufficient ? (
                                <div className="rr-vch-balance-warning">
                                    <i className="bi bi-exclamation-triangle-fill" />
                                    Payment exceeds the available account balance.
                                </div>
                            ) : (
                                <div className="rr-vch-balance-safe">
                                    <i className="bi bi-shield-check" />
                                    Account balance will be updated automatically.
                                </div>
                            )}
                        </aside>
                    </div>

                    <section className="rr-vch-form-card">
                        <CardHeader
                            number="02"
                            icon="bi-person-vcard"
                            title="Party / Particulars"
                            text="Link the voucher to a customer, supplier, mechanic or another party"
                        />

                        <div className="rr-vch-form-body rr-vch-party-layout">
                            <FormField label="Party Type" error={errors.party_type}>
                                <select
                                    value={data.party_type}
                                    onChange={(event) => changePartyType(event.target.value)}
                                >
                                    {optionEntries(partyTypes).map(([value, text]) => (
                                        <option key={value} value={value}>{text}</option>
                                    ))}
                                </select>
                            </FormField>

                            {data.party_type === "other" ? (
                                <FormField label="Party / Particular Name" error={errors.party_name} wide>
                                    <input
                                        type="text"
                                        value={data.party_name}
                                        onChange={(event) => setData("party_name", event.target.value)}
                                        placeholder="Enter person, business or purpose"
                                        maxLength="150"
                                    />
                                </FormField>
                            ) : (
                                <div className="rr-vch-party-search" ref={partyBoxRef}>
                                    <label>
                                        Search {label(data.party_type)} <b>*</b>
                                    </label>

                                    <div className={`rr-vch-party-input ${errors.party_id ? "invalid" : ""}`}>
                                        <i className="bi bi-search" />
                                        <input
                                            type="search"
                                            value={partySearch}
                                            onChange={(event) => {
                                                setPartySearch(event.target.value);
                                                setSelectedParty(null);
                                                setData((current) => ({
                                                    ...current,
                                                    party_id: "",
                                                    party_name: "",
                                                }));
                                                setPartyOpen(true);
                                            }}
                                            onFocus={() => setPartyOpen(true)}
                                            placeholder={`Search ${data.party_type} name, code or mobile`}
                                            autoComplete="off"
                                        />
                                        {partyLoading ? (
                                            <span className="spinner-border spinner-border-sm" />
                                        ) : partySearch ? (
                                            <button type="button" onClick={clearParty} aria-label="Clear party">
                                                <i className="bi bi-x-lg" />
                                            </button>
                                        ) : null}
                                    </div>

                                    {errors.party_id && <small className="rr-vch-error">{errors.party_id}</small>}

                                    {partyOpen && (
                                        <div className="rr-vch-party-results">
                                            {partyLoading ? (
                                                <div className="loading">Searching...</div>
                                            ) : partyResults.length ? (
                                                partyResults.map((party) => (
                                                    <button
                                                        type="button"
                                                        key={`${party.type}-${party.id}`}
                                                        onClick={() => chooseParty(party)}
                                                    >
                                                        <span className="avatar">{initials(party.name)}</span>
                                                        <span>
                                                            <strong>{party.name}</strong>
                                                            <small>{party.code || "No code"} {party.contact ? `• ${party.contact}` : ""}</small>
                                                        </span>
                                                        <i className="bi bi-plus-circle" />
                                                    </button>
                                                ))
                                            ) : (
                                                <div className="empty">
                                                    <i className="bi bi-search" />
                                                    No matching {data.party_type} found
                                                </div>
                                            )}
                                        </div>
                                    )}

                                    {selectedParty && (
                                        <div className="rr-vch-selected-party">
                                            <i className="bi bi-check-circle-fill" />
                                            <span><strong>{selectedParty.name}</strong><small>{selectedParty.code}</small></span>
                                            <button type="button" onClick={clearParty}><i className="bi bi-x" /></button>
                                        </div>
                                    )}
                                </div>
                            )}
                        </div>
                    </section>

                    <section className="rr-vch-form-card">
                        <CardHeader
                            number="03"
                            icon="bi-card-text"
                            title="Narration"
                            text="Add a clear description for audit and future reference"
                        />
                        <div className="rr-vch-form-body">
                            <FormField label="Remarks / Narration" error={errors.remarks} wide>
                                <textarea
                                    rows="3"
                                    value={data.remarks}
                                    onChange={(event) => setData("remarks", event.target.value)}
                                    placeholder={`Reason for this ${isReceipt ? "receipt" : "payment"} voucher`}
                                    maxLength="2000"
                                />
                            </FormField>
                        </div>
                    </section>

                    <footer className="rr-vch-form-footer">
                        <div>
                            <i className="bi bi-info-circle" />
                            Saving creates an account transaction. Posted vouchers cannot be edited.
                        </div>
                        <div className="rr-vch-footer-actions">
                            <Link href="/vouchers" className="rr-vch-btn back">
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                className={`rr-vch-btn ${isReceipt ? "receipt" : "payment"}`}
                                disabled={processing || insufficient || !data.account_id || amount <= 0}
                            >
                                {processing ? (
                                    <><span className="spinner-border spinner-border-sm" /> Saving...</>
                                ) : (
                                    <><i className="bi bi-check2-circle" /> Save {isReceipt ? "Receipt" : "Payment"}</>
                                )}
                            </button>
                        </div>
                    </footer>
                </form>
            </main>
        </>
    );
}

function CardHeader({ number, icon, title, text }) {
    return (
        <header className="rr-vch-form-card-head">
            <span className="number">{number}</span>
            <i className={`bi ${icon}`} />
            <span><strong>{title}</strong><small>{text}</small></span>
        </header>
    );
}

function FormField({ label: text, required = false, error, wide = false, children }) {
    return (
        <label className={`rr-vch-form-field ${wide ? "wide" : ""} ${error ? "has-error" : ""}`}>
            <span>{text} {required && <b>*</b>}</span>
            {children}
            {error && <small className="rr-vch-error">{error}</small>}
        </label>
    );
}

function BalanceLine({ label: text, value, tone = "", total = false }) {
    return (
        <div className={`${tone} ${total ? "total" : ""}`}>
            <span>{text}</span><strong>{value}</strong>
        </div>
    );
}

function optionEntries(options) {
    if (Array.isArray(options)) return options.map((value) => [value, label(value)]);
    return Object.entries(options ?? {});
}

function openDatePicker(event) {
    if (typeof event.currentTarget?.showPicker !== "function") return;
    try { event.currentTarget.showPicker(); } catch { /* Browser restriction. */ }
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

function initials(value) {
    return String(value ?? "P")
        .trim()
        .split(/\s+/)
        .slice(0, 2)
        .map((part) => part[0])
        .join("")
        .toUpperCase();
}

Create.layout = (page) => <BusinessLayout>{page}</BusinessLayout>;

