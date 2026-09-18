import { Head, Link, useForm } from '@inertiajs/react';
import axios from 'axios';
import { useEffect, useMemo, useRef, useState } from 'react';

import BusinessLayout from '../../Layouts/BusinessLayout';
import MasterNavigation from '../../Components/Business/MasterNavigation';
import '../../../css/business/customer-payments.css';

export default function Create({
    accounts = [],
    payment_modes: paymentModes = [],
    selected_customer: initialCustomer = null,
    today,
}) {
    const [customer, setCustomer] = useState(initialCustomer);
    const [customerSearch, setCustomerSearch] = useState('');
    const [customerResults, setCustomerResults] = useState([]);
    const [searching, setSearching] = useState(false);
    const [showResults, setShowResults] = useState(false);
    const [sales, setSales] = useState([]);
    const [loadingSales, setLoadingSales] = useState(false);
    const searchTimer = useRef(null);

    const { data, setData, post, processing, errors, clearErrors } = useForm({
        customer_id: initialCustomer?.id ?? '',
        account_id: '',
        payment_date: today ?? localDate(),
        amount: '',
        payment_mode: '',
        reference_no: '',
        remarks: '',
        allocations: [],
    });

    const selectedAccount = useMemo(
        () => accounts.find(account => Number(account.id) === Number(data.account_id)),
        [accounts, data.account_id]
    );
    const allocatedTotal = useMemo(
        () => roundMoney(data.allocations.reduce((sum, row) => sum + number(row.amount), 0)),
        [data.allocations]
    );
    const receiptAmount = number(data.amount);
    const unallocatedAmount = Math.max(0, roundMoney(receiptAmount - allocatedTotal));
    const allocationExceeded = allocatedTotal > receiptAmount && receiptAmount >= 0;

    useEffect(() => {
        if (initialCustomer?.id) loadSales(initialCustomer.id);
    }, []);

    useEffect(() => () => clearTimeout(searchTimer.current), []);

    const searchCustomers = value => {
        setCustomerSearch(value);
        setShowResults(true);
        clearTimeout(searchTimer.current);

        if (value.trim().length < 2) {
            setCustomerResults([]);
            setSearching(false);
            return;
        }

        setSearching(true);
        searchTimer.current = setTimeout(async () => {
            try {
                const response = await axios.get('/customer-payments/customers/search', {
                    params: { search: value.trim() },
                });
                setCustomerResults(response.data?.data ?? []);
            } catch {
                setCustomerResults([]);
            } finally {
                setSearching(false);
            }
        }, 300);
    };

    const loadSales = async customerId => {
        setLoadingSales(true);
        try {
            const response = await axios.get(`/customer-payments/customers/${customerId}/sales`);
            setSales(response.data?.data ?? []);
        } catch {
            setSales([]);
        } finally {
            setLoadingSales(false);
        }
    };

    const selectCustomer = selected => {
        setCustomer(selected);
        setCustomerSearch('');
        setCustomerResults([]);
        setShowResults(false);
        setSales([]);
        setData(current => ({ ...current, customer_id: selected.id, allocations: [] }));
        clearErrors('customer_id', 'allocations');
        loadSales(selected.id);
    };

    const clearCustomer = () => {
        setCustomer(null);
        setCustomerSearch('');
        setCustomerResults([]);
        setSales([]);
        setData(current => ({ ...current, customer_id: '', allocations: [] }));
    };

    const selectAccount = accountId => {
        const account = accounts.find(row => Number(row.id) === Number(accountId));
        setData(current => ({
            ...current,
            account_id: accountId,
            payment_mode: account?.payment_mode || current.payment_mode || '',
        }));
        clearErrors('account_id', 'payment_mode');
    };

    const allocationFor = saleId => data.allocations.find(
        row => Number(row.sale_id) === Number(saleId)
    )?.amount ?? '';

    const setAllocation = (sale, value) => {
        const amount = value === '' ? '' : Math.max(0, number(value));
        const rest = data.allocations.filter(row => Number(row.sale_id) !== Number(sale.id));
        setData('allocations', amount === '' || amount === 0
            ? rest
            : [...rest, { sale_id: sale.id, amount }]
        );
        clearErrors('allocations');
    };

    const autoAllocate = () => {
        let remaining = receiptAmount;
        const allocations = [];
        sales.forEach(sale => {
            const amount = roundMoney(Math.min(number(sale.outstanding), remaining));
            if (amount > 0) allocations.push({ sale_id: sale.id, amount });
            remaining = roundMoney(remaining - amount);
        });
        setData('allocations', allocations);
    };

    const allocateFullInvoice = sale => {
        const otherTotal = data.allocations
            .filter(row => Number(row.sale_id) !== Number(sale.id))
            .reduce((sum, row) => sum + number(row.amount), 0);
        const available = Math.max(0, receiptAmount - otherTotal);
        setAllocation(sale, roundMoney(Math.min(number(sale.outstanding), available)));
    };

    const submit = event => {
        event.preventDefault();
        if (allocationExceeded) return;
        post('/customer-payments', { preserveScroll: true });
    };

    return (
        <BusinessLayout>
            <Head title="New Customer Receipt" />
            <div className="rr-cp-page rr-cp-create-page">
                <MasterNavigation active="customer-payments" title="Customer Payments" subtitle="Record customer receipt" />

                <section className="rr-cp-header rr-cp-create-header">
                    <div>
                        <span className="rr-cp-eyebrow"><i className="bi bi-receipt"></i> Accounts Receivable</span>
                        <h1>New Customer Receipt</h1>
                        <p>Receive payment and allocate it against outstanding invoices</p>
                    </div>
                    <Link href="/customer-payments" className="rr-cp-back-button">
                        <i className="bi bi-arrow-left"></i> Back to Register
                    </Link>
                </section>

                <form onSubmit={submit} className="rr-cp-create-grid">
                    <main className="rr-cp-create-main">
                        <FormCard icon="bi-person-search" title="Select Customer" note="Search by customer name, code, mobile or GST number">
                            {!customer ? (
                                <div className="rr-cp-customer-picker">
                                    <div className={`rr-cp-live-search ${errors.customer_id ? 'invalid' : ''}`}>
                                        <i className="bi bi-search"></i>
                                        <input
                                            autoFocus
                                            value={customerSearch}
                                            onChange={event => searchCustomers(event.target.value)}
                                            onFocus={() => setShowResults(true)}
                                            placeholder="Type at least 2 characters to search customer..."
                                            autoComplete="off"
                                        />
                                        {searching && <span className="spinner-border spinner-border-sm"></span>}
                                        {customerSearch && !searching && (
                                            <button type="button" onClick={() => searchCustomers('')}><i className="bi bi-x-lg"></i></button>
                                        )}
                                    </div>
                                    {errors.customer_id && <FieldError message={errors.customer_id} />}

                                    {showResults && customerSearch.trim().length >= 2 && (
                                        <div className="rr-cp-search-results">
                                            {searching ? (
                                                <div className="rr-cp-search-state"><span className="spinner-border spinner-border-sm"></span> Searching...</div>
                                            ) : customerResults.length ? customerResults.map(result => (
                                                <button type="button" key={result.id} onClick={() => selectCustomer(result)}>
                                                    <span className="avatar">{initials(result.customer_name)}</span>
                                                    <span className="identity">
                                                        <strong>{result.customer_name}</strong>
                                                        <small>{result.customer_code}{result.mobile ? ` · ${result.mobile}` : ''}</small>
                                                    </span>
                                                    <span className={`balance ${number(result.current_balance) > 0 ? 'due' : ''}`}>
                                                        <small>Current balance</small>
                                                        <strong>{balanceText(result.current_balance)}</strong>
                                                    </span>
                                                </button>
                                            )) : (
                                                <div className="rr-cp-search-state"><i className="bi bi-person-x"></i> No customer found</div>
                                            )}
                                        </div>
                                    )}
                                </div>
                            ) : (
                                <div className="rr-cp-selected-customer">
                                    <span className="avatar">{initials(customer.customer_name)}</span>
                                    <div className="identity">
                                        <small>Selected customer</small>
                                        <strong>{customer.customer_name}</strong>
                                        <span>{customer.customer_code}{customer.mobile ? ` · ${customer.mobile}` : ''}</span>
                                    </div>
                                    <div className="balance">
                                        <small>Ledger balance</small>
                                        <strong>{balanceText(customer.current_balance)}</strong>
                                    </div>
                                    <button type="button" onClick={clearCustomer} title="Change customer"><i className="bi bi-x-lg"></i></button>
                                </div>
                            )}
                        </FormCard>

                        <FormCard icon="bi-files" title="Invoice Allocation" note="Allocate the receipt or leave an amount as customer advance"
                            action={customer && sales.length > 0 ? <button type="button" className="rr-cp-card-action" onClick={autoAllocate} disabled={!receiptAmount}><i className="bi bi-magic"></i> Auto Allocate</button> : null}>
                            {!customer ? (
                                <EmptyInvoices icon="bi-person-check" text="Select a customer to view outstanding invoices." />
                            ) : loadingSales ? (
                                <EmptyInvoices loading text="Loading outstanding invoices..." />
                            ) : sales.length ? (
                                <div className="rr-cp-invoice-wrap">
                                    <table className="rr-cp-invoice-table">
                                        <thead><tr><th>Invoice</th><th>Date</th><th className="money">Bill Amount</th><th className="money">Paid</th><th className="money">Outstanding</th><th className="allocation">Allocate</th></tr></thead>
                                        <tbody>{sales.map(sale => {
                                            const current = allocationFor(sale.id);
                                            const tooHigh = number(current) > number(sale.outstanding);
                                            return (
                                                <tr key={sale.id} className={number(current) > 0 ? 'selected' : ''}>
                                                    <td><strong>{sale.invoice_no}</strong><small>{sale.payment_status}</small></td>
                                                    <td>{formatDate(sale.invoice_date)}</td>
                                                    <td className="money">₹{money(sale.grand_total)}</td>
                                                    <td className="money">₹{money(sale.paid_amount)}</td>
                                                    <td className="money outstanding">₹{money(sale.outstanding)}</td>
                                                    <td className="allocation">
                                                        <div className={`rr-cp-allocation-input ${tooHigh ? 'invalid' : ''}`}>
                                                            <span>₹</span>
                                                            <input type="number" min="0" max={sale.outstanding} step="0.01" value={current}
                                                                onChange={event => setAllocation(sale, event.target.value)} placeholder="0.00" />
                                                            <button type="button" onClick={() => allocateFullInvoice(sale)} disabled={!receiptAmount} title="Allocate available amount">Max</button>
                                                        </div>
                                                    </td>
                                                </tr>
                                            );
                                        })}</tbody>
                                    </table>
                                </div>
                            ) : (
                                <EmptyInvoices icon="bi-check-circle" text="This customer has no outstanding invoices. The receipt will be saved as an advance." success />
                            )}
                            {errors.allocations && <FieldError message={errors.allocations} />}
                        </FormCard>

                        <FormCard icon="bi-chat-left-text" title="Reference & Notes" note="Optional payment reference and internal remarks">
                            <div className="rr-cp-form-grid two">
                                <FormField label="Reference Number" icon="bi-hash" error={errors.reference_no}>
                                    <input value={data.reference_no} onChange={e => setData('reference_no', e.target.value)} maxLength="150" placeholder="Cheque / UTR / transaction number" />
                                </FormField>
                                <FormField label="Remarks" icon="bi-pencil" error={errors.remarks}>
                                    <textarea value={data.remarks} onChange={e => setData('remarks', e.target.value)} rows="2" maxLength="2000" placeholder="Optional notes" />
                                </FormField>
                            </div>
                        </FormCard>
                    </main>

                    <aside className="rr-cp-payment-sidebar">
                        <FormCard icon="bi-wallet2" title="Receipt Details" note="Enter collection information">
                            <div className="rr-cp-form-stack">
                                <FormField label="Payment Date" icon="bi-calendar3" required error={errors.payment_date}>
                                    <input type="date" value={data.payment_date} max={today ?? localDate()} onChange={e => setData('payment_date', e.target.value)} />
                                </FormField>
                                <FormField label="Receipt Amount" icon="bi-currency-rupee" required error={errors.amount}>
                                    <div className="rr-cp-amount-input"><span>₹</span><input type="number" min="0.01" step="0.01" value={data.amount} onChange={e => setData('amount', e.target.value)} placeholder="0.00" /></div>
                                </FormField>
                                <FormField label="Deposit Account" icon="bi-bank" required error={errors.account_id}>
                                    <select value={data.account_id} onChange={e => selectAccount(e.target.value)}>
                                        <option value="">Select account</option>
                                        {accounts.map(account => <option key={account.id} value={account.id}>{account.account_name} ({account.account_code})</option>)}
                                    </select>
                                </FormField>
                                <FormField label="Payment Mode" icon="bi-credit-card" required error={errors.payment_mode}>
                                    <select value={data.payment_mode} onChange={e => setData('payment_mode', e.target.value)}>
                                        <option value="">Select payment mode</option>
                                        {paymentModes.map(mode => <option key={mode} value={mode}>{mode}</option>)}
                                    </select>
                                    {selectedAccount?.payment_mode && <small className="rr-cp-field-hint">Account mode: {selectedAccount.payment_mode}</small>}
                                </FormField>
                            </div>
                        </FormCard>

                        <section className="rr-cp-allocation-summary">
                            <header><i className="bi bi-calculator"></i><strong>Receipt Summary</strong></header>
                            <dl>
                                <div><dt>Receipt amount</dt><dd>₹{money(receiptAmount)}</dd></div>
                                <div><dt>Invoice allocation</dt><dd>₹{money(allocatedTotal)}</dd></div>
                                <div className={unallocatedAmount > 0 ? 'advance' : ''}><dt>Customer advance</dt><dd>₹{money(unallocatedAmount)}</dd></div>
                            </dl>
                            {allocationExceeded && <div className="rr-cp-summary-error"><i className="bi bi-exclamation-circle"></i> Allocation exceeds receipt by ₹{money(allocatedTotal - receiptAmount)}.</div>}
                        </section>

                        <div className="rr-cp-submit-actions">
                            <Link href="/customer-payments" className="secondary">Cancel</Link>
                            <button type="submit" disabled={processing || allocationExceeded || !customer}>
                                {processing ? <><span className="spinner-border spinner-border-sm"></span> Saving...</> : <><i className="bi bi-check2-circle"></i> Save Receipt</>}
                            </button>
                        </div>
                    </aside>
                </form>
            </div>
        </BusinessLayout>
    );
}

function FormCard({ icon, title, note, action, children }) {
    return <section className="rr-cp-form-card"><header><div><span><i className={`bi ${icon}`}></i></span><div><strong>{title}</strong><small>{note}</small></div></div>{action}</header><div className="rr-cp-form-card-body">{children}</div></section>;
}
function FormField({ label, icon, required, error, children }) {
    return <label className={`rr-cp-form-field ${error ? 'has-error' : ''}`}><span><i className={`bi ${icon}`}></i>{label}{required && <b>*</b>}</span>{children}{error && <FieldError message={error} />}</label>;
}
function FieldError({ message }) { return <small className="rr-cp-field-error"><i className="bi bi-exclamation-circle"></i>{message}</small>; }
function EmptyInvoices({ icon, text, loading, success }) { return <div className={`rr-cp-invoice-empty ${success ? 'success' : ''}`}>{loading ? <span className="spinner-border spinner-border-sm"></span> : <i className={`bi ${icon}`}></i>}<span>{text}</span></div>; }
function number(value) { const parsed = Number(value); return Number.isFinite(parsed) ? parsed : 0; }
function roundMoney(value) { return Math.round((number(value) + Number.EPSILON) * 100) / 100; }
function money(value) { return number(value).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
function balanceText(value) { const amount = number(value); return `₹${money(Math.abs(amount))} ${amount >= 0 ? 'Dr' : 'Cr'}`; }
function initials(value) { return String(value ?? 'C').trim().split(/\s+/).slice(0, 2).map(word => word[0]).join('').toUpperCase(); }
function formatDate(value) { if (!value) return '—'; const [y, m, d] = String(value).slice(0, 10).split('-'); return y && m && d ? `${d}-${m}-${y}` : value; }
function localDate() { const now = new Date(); return new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().slice(0, 10); }

Create.layout = page => page;
