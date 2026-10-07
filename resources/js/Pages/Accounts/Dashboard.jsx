import React, { useState } from "react";
import { Head, Link, router } from "@inertiajs/react";

import BusinessLayout from "@/Layouts/BusinessLayout";
import MasterNavigation from "../../Components/Business/MasterNavigation";

import "../../../css/business/accounts-dashboard.css";

export default function Dashboard({
    selectedDate,
    isToday = false,
    summary = {},
    collections = {},
    payments = {},
    unpaidInvoices = [],
    accountBalances = [],
    recentTransactions = [],
    context = {},
}) {
    const [showUnpaid, setShowUnpaid] = useState(false);

    const visitDate = (date) => {
        if (!date) return;
        router.get("/accounts/dashboard", { date }, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    };

    const shiftDate = (days) => {
        const date = parseDate(selectedDate);
        date.setDate(date.getDate() + days);
        visitDate(toDateInput(date));
    };

    return (
        <>
            <Head title="Accounts Dashboard" />

            <main className="rr-ad-page">
                <MasterNavigation
                    active="accounts-dashboard"
                    title="Accounts"
                    subtitle="Daily cash flow, profit and outstanding control"
                />

                <section className="rr-ad-commandbar">
                    <div className="rr-ad-crumb">
                        <i className="bi bi-window-stack" />
                        <span>Accounts</span>
                        <i className="bi bi-chevron-right" />
                        <strong>Dashboard</strong>
                    </div>

                    <div className="rr-ad-context">
                        <span><i className="bi bi-building" />{context.company_name || "Active company"}</span>
                        <span><i className="bi bi-shop" />{context.store_name || "Active branch"}</span>
                    </div>
                </section>

                <header className="rr-ad-header">
                    <div className="rr-ad-title">
                        <span className="rr-ad-title-icon"><i className="bi bi-bar-chart-line-fill" /></span>
                        <div>
                            <small>{isToday ? "TODAY'S CONTROL CENTRE" : "HISTORICAL DAY SUMMARY"}</small>
                            <h1>Accounts & Cash Flow</h1>
                            <p>Sales, profit, collections, payments and party balances in one view.</p>
                        </div>
                    </div>

                    <div className="rr-ad-datebox">
                        <button type="button" title="Previous day" onClick={() => shiftDate(-1)}>
                            <i className="bi bi-chevron-left" />
                        </button>
                        <label>
                            <i className="bi bi-calendar3" />
                            <span><small>Working date</small><strong>{displayDate(selectedDate)}</strong></span>
                            <input
                                type="date"
                                value={selectedDate || ""}
                                max={today()}
                                onChange={(event) => visitDate(event.target.value)}
                            />
                        </label>
                        <button type="button" title="Next day" disabled={isToday} onClick={() => shiftDate(1)}>
                            <i className="bi bi-chevron-right" />
                        </button>
                        {!isToday && <button className="today" type="button" onClick={() => visitDate(today())}>Today</button>}
                    </div>
                </header>

                {number(summary.missing_cost_lines) > 0 && (
                    <div className="rr-ad-alert">
                        <i className="bi bi-exclamation-triangle-fill" />
                        <span><strong>Profit attention:</strong> {integer(summary.missing_cost_lines)} sale item(s) have no cost snapshot. Their profit is excluded.</span>
                    </div>
                )}

                <section className="rr-ad-kpi-grid">
                    <Kpi tone="sales" icon="bi-receipt-cutoff" label="Gross Sales" value={money(summary.sales_total)} meta={`${integer(summary.bill_count)} invoices`} />
                    <Kpi tone="returns" icon="bi-arrow-return-left" label="Sales Returns" value={money(summary.sales_return_total)} meta={`${integer(summary.sales_return_count)} returns`} />
                    <Kpi tone="net-sales" icon="bi-graph-up-arrow" label="Net Sales" value={money(summary.net_sales_total)} meta="Sales less returns" />
                    <Kpi tone="profit" icon="bi-piggy-bank-fill" label="Net Profit" value={money(summary.net_profit)} meta={`Gross ${money(summary.gross_profit)}`} />
                    <button className="rr-ad-kpi-button" type="button" onClick={() => setShowUnpaid(true)}>
                        <Kpi tone="outstanding" icon="bi-hourglass-split" label="Bill Outstanding" value={money(summary.outstanding_total)} meta={`${integer(summary.unpaid_bill_count + summary.partial_bill_count)} pending bills`} />
                    </button>
                </section>

                <section className="rr-ad-flow-grid">
                    <Panel tone="inflow" icon="bi-arrow-down-left-circle-fill" title="Money In" badge={money(collections.total)}>
                        <div className="rr-ad-flow-list">
                            <FlowRow icon="bi-cash-stack" label="Cash" value={collections.cash} color="cash" />
                            <FlowRow icon="bi-phone-fill" label="UPI / GPay / PhonePe" value={collections.upi} color="upi" />
                            <FlowRow icon="bi-bank2" label="Bank / Cheque" value={collections.bank} color="bank" />
                            <FlowRow icon="bi-credit-card-2-front-fill" label="Card" value={collections.card} color="card" />
                            <FlowRow icon="bi-three-dots" label="Other / Unclassified" value={collections.other} color="other" />
                        </div>
                    </Panel>

                    <Panel tone="outflow" icon="bi-arrow-up-right-circle-fill" title="Money Out" badge={money(payments.total)}>
                        <div className="rr-ad-flow-list">
                            <FlowRow icon="bi-truck" label="Supplier Payments" value={payments.supplier} color="supplier" />
                            <FlowRow icon="bi-person-gear" label="Mechanic Payments" value={payments.mechanic} color="mechanic" />
                            <FlowRow icon="bi-arrow-counterclockwise" label="Customer Refunds" value={payments.customer_refund} color="refund" />
                            <FlowRow icon="bi-receipt" label="Expenses" value={payments.expense} color="expense" />
                            <FlowRow icon="bi-journal-arrow-up" label="Payment Vouchers" value={payments.payment_voucher} color="voucher" />
                        </div>
                    </Panel>

                    <Panel tone="profit" icon="bi-calculator-fill" title="Profit Calculation" badge={money(summary.net_profit)}>
                        <div className="rr-ad-calc">
                            <CalcRow label="Gross profit" value={summary.gross_profit} />
                            <CalcRow label="Final discount" value={-number(summary.final_discount)} negative />
                            <CalcRow label="Mechanic commission" value={-number(summary.mechanic_commission)} negative />
                            <CalcRow label="Round off" value={summary.round_off} />
                            <CalcRow label="Cost of goods" value={summary.cost_total} muted />
                            <CalcRow label="Net profit" value={summary.net_profit} total />
                        </div>
                    </Panel>
                </section>

                <section className="rr-ad-two-column">
                    <Panel tone="balances" icon="bi-wallet2" title="Cash & Bank Position" badge={money(summary.total_account_balance)}>
                        <div className="rr-ad-account-summary">
                            <MiniBalance icon="bi-cash-stack" label="Cash" value={summary.cash_balance} tone="cash" />
                            <MiniBalance icon="bi-bank" label="Bank" value={summary.bank_balance} tone="bank" />
                            <MiniBalance icon="bi-phone" label="UPI" value={summary.upi_balance} tone="upi" />
                            <MiniBalance icon="bi-credit-card" label="Card" value={summary.card_balance} tone="card" />
                        </div>

                        <div className="rr-ad-account-list">
                            {accountBalances.length ? accountBalances.map((account) => (
                                <Link href={`/accounts/${account.id}`} key={account.id} className="rr-ad-account-row">
                                    <span className={`type ${account.account_type}`}><i className={`bi ${accountIcon(account.account_type)}`} /></span>
                                    <span className="name"><strong>{account.account_name}</strong><small>{account.account_code} · {label(account.account_type)}</small></span>
                                    <strong className={number(account.balance) < 0 ? "negative" : ""}>{money(account.balance)}</strong>
                                    <i className="bi bi-chevron-right" />
                                </Link>
                            )) : <Empty text="No active Cash/Bank accounts found." />}
                        </div>
                    </Panel>

                    <Panel tone="parties" icon="bi-people-fill" title="Party Balances" badge="As of selected date">
                        <div className="rr-ad-party-grid">
                            <PartyCard href="/customer-ledger" icon="bi-person-fill" title="Customers" dueLabel="Receivable" due={summary.customer_outstanding} advance={summary.customer_advance} tone="customer" />
                            <PartyCard href="/supplier-ledger" icon="bi-truck-front-fill" title="Suppliers" dueLabel="Payable" due={summary.supplier_outstanding} advance={summary.supplier_advance} tone="supplier" />
                            <PartyCard href="/mechanic-ledger" icon="bi-person-gear" title="Mechanics" dueLabel="Payable" due={summary.mechanic_payable} advance={summary.mechanic_advance} tone="mechanic" />
                        </div>

                        <div className="rr-ad-bill-status">
                            <div><span className="dot paid" /><small>Paid bills</small><strong>{integer(summary.paid_bill_count)}</strong></div>
                            <div><span className="dot partial" /><small>Partial bills</small><strong>{integer(summary.partial_bill_count)}</strong></div>
                            <div><span className="dot unpaid" /><small>Unpaid bills</small><strong>{integer(summary.unpaid_bill_count)}</strong></div>
                            <div><span className="dot advance" /><small>Advance used</small><strong>{money(summary.advance_used)}</strong></div>
                        </div>
                    </Panel>
                </section>

                <section className="rr-ad-panel rr-ad-transactions">
                    <div className="rr-ad-panel-head transactions">
                        <span><i className="bi bi-clock-history" /><strong>Recent Account Transactions</strong></span>
                        <Link href="/accounts"><i className="bi bi-journal-text" /> Account Register</Link>
                    </div>
                    <div className="rr-ad-table-wrap">
                        <table>
                            <thead><tr><th>Type</th><th>Account</th><th>Reference</th><th>Mode</th><th>Remarks</th><th className="num inflow">Money In</th><th className="num outflow">Money Out</th></tr></thead>
                            <tbody>
                                {recentTransactions.length ? recentTransactions.map((entry) => (
                                    <tr key={entry.id} className={entry.is_reversal ? "reversal" : ""}>
                                        <td><span className={`rr-ad-type-tag ${transactionTone(entry.transaction_type)}`}>{entry.type_label}</span></td>
                                        <td><strong>{entry.account?.account_name || "—"}</strong><small>{entry.account?.account_code || ""}</small></td>
                                        <td>{entry.reference_no || "—"}</td>
                                        <td>{label(entry.payment_mode || "—")}</td>
                                        <td className="remarks" title={entry.remarks || ""}>{entry.remarks || "—"}</td>
                                        <td className="num money-in">{number(entry.debit) > 0 ? money(entry.debit) : "—"}</td>
                                        <td className="num money-out">{number(entry.credit) > 0 ? money(entry.credit) : "—"}</td>
                                    </tr>
                                )) : <tr><td colSpan="7"><Empty text="No account transactions for this date." /></td></tr>}
                            </tbody>
                        </table>
                    </div>
                </section>

                {showUnpaid && <UnpaidDrawer date={selectedDate} invoices={unpaidInvoices} total={summary.outstanding_total} onClose={() => setShowUnpaid(false)} />}
            </main>
        </>
    );
}

function Panel({ tone, icon, title, badge, children }) {
    return <section className={`rr-ad-panel ${tone}`}><div className="rr-ad-panel-head"><span><i className={`bi ${icon}`} /><strong>{title}</strong></span>{badge && <b>{badge}</b>}</div><div className="rr-ad-panel-body">{children}</div></section>;
}

function Kpi({ tone, icon, label: text, value, meta }) {
    return <article className={`rr-ad-kpi ${tone}`}><span className="rr-ad-kpi-icon"><i className={`bi ${icon}`} /></span><div><small>{text}</small><strong>{value}</strong><p>{meta}</p></div><span className="rr-ad-kpi-line" /></article>;
}

function FlowRow({ icon, label: text, value, color }) {
    return <div className="rr-ad-flow-row"><span className={`icon ${color}`}><i className={`bi ${icon}`} /></span><span><small>{text}</small><strong>{money(value)}</strong></span><div className={`bar ${color}`}><i style={{ width: `${Math.min(100, Math.max(4, number(value) > 0 ? 68 : 4))}%` }} /></div></div>;
}

function CalcRow({ label: text, value, negative = false, muted = false, total = false }) {
    return <div className={`${muted ? "muted" : ""} ${total ? "total" : ""}`}><span>{text}</span><strong className={negative ? "negative" : ""}>{number(value) < 0 ? "−" : ""}{money(Math.abs(number(value)))}</strong></div>;
}

function MiniBalance({ icon, label: text, value, tone }) {
    return <div className={`rr-ad-mini-balance ${tone}`}><i className={`bi ${icon}`} /><span><small>{text}</small><strong>{money(value)}</strong></span></div>;
}

function PartyCard({ href, icon, title, dueLabel, due, advance, tone }) {
    return <Link href={href} className={`rr-ad-party-card ${tone}`}><header><span><i className={`bi ${icon}`} /></span><strong>{title}</strong><i className="bi bi-box-arrow-up-right" /></header><div><span><small>{dueLabel}</small><strong>{money(due)}</strong></span><span><small>Advance</small><strong>{money(advance)}</strong></span></div></Link>;
}

function Empty({ text }) {
    return <div className="rr-ad-empty"><i className="bi bi-inbox" /><span>{text}</span></div>;
}

function UnpaidDrawer({ date, invoices, total, onClose }) {
    return <div className="rr-ad-drawer-layer" role="dialog" aria-modal="true"><button className="rr-ad-drawer-backdrop" type="button" onClick={onClose} aria-label="Close" /><aside className="rr-ad-drawer"><header><div><small>Selected-day invoices</small><h2>Outstanding Bills</h2><p>{displayDate(date)}</p></div><button type="button" onClick={onClose}><i className="bi bi-x-lg" /></button></header><div className="rr-ad-drawer-total"><span>Total outstanding</span><strong>{money(total)}</strong></div><div className="rr-ad-drawer-table"><table><thead><tr><th>Customer / Invoice</th><th className="num">Bill</th><th className="num">Paid</th><th className="num">Advance</th><th className="num">Balance</th><th /></tr></thead><tbody>{invoices.length ? invoices.map((invoice) => <tr key={invoice.id}><td><strong>{invoice.customer?.customer_name || "Customer"}</strong><small>{invoice.invoice_no} · {invoice.customer?.mobile || "No mobile"}</small></td><td className="num">{money(invoice.grand_total)}</td><td className="num">{money(invoice.paid_amount)}</td><td className="num">{money(invoice.advance_used)}</td><td className="num balance">{money(invoice.balance_amount)}</td><td><Link href={`/sales/${invoice.id}`} title="Open invoice"><i className="bi bi-box-arrow-up-right" /></Link></td></tr>) : <tr><td colSpan="6"><Empty text="No outstanding invoices for this day." /></td></tr>}</tbody></table></div></aside></div>;
}

function number(value) { const parsed = Number(value); return Number.isFinite(parsed) ? parsed : 0; }
function integer(value) { return Math.trunc(number(value)).toLocaleString("en-IN"); }
function money(value) { const amount = number(value); return `${amount < 0 ? "−" : ""}₹${Math.abs(amount).toLocaleString("en-IN", { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`; }
function parseDate(value) { const [year, month, day] = String(value || today()).split("-").map(Number); return new Date(year, month - 1, day); }
function toDateInput(date) { return `${date.getFullYear()}-${String(date.getMonth() + 1).padStart(2, "0")}-${String(date.getDate()).padStart(2, "0")}`; }
function today() { return toDateInput(new Date()); }
function displayDate(value) { return value ? parseDate(value).toLocaleDateString("en-IN", { day: "2-digit", month: "short", year: "numeric" }) : "—"; }
function label(value) { return String(value || "").replaceAll("_", " ").replace(/\b\w/g, (letter) => letter.toUpperCase()); }
function accountIcon(type) { return { cash: "bi-cash-stack", bank: "bi-bank", upi: "bi-phone", card: "bi-credit-card-2-front" }[type] || "bi-wallet2"; }
function transactionTone(type) { if (["sale", "customer_receipt", "receipt_voucher"].includes(type)) return "in"; if (["supplier_payment", "mechanic_payment", "customer_refund", "expense", "payment_voucher"].includes(type)) return "out"; if (type === "reversal") return "reversal"; return "neutral"; }

Dashboard.layout = (page) => <BusinessLayout title="Accounts Dashboard">{page}</BusinessLayout>;
