import { Head, Link, router } from '@inertiajs/react';
import axios from 'axios';
import { useMemo, useRef, useState } from 'react';

import BusinessLayout from '../../Layouts/BusinessLayout';
import MasterNavigation from '../../Components/Business/MasterNavigation';
import '../../../css/business/customer-ledgers.css';

export default function Index({
    entries = null,
    customer = null,
    summary = {},
    filters = {},
    transaction_types: transactionTypes = {},
}) {
    const [form, setForm] = useState({
        customer_id: filters?.customer_id ?? '',
        from_date: filters?.from_date ?? '',
        to_date: filters?.to_date ?? '',
        transaction_type: filters?.transaction_type ?? '',
        search: filters?.search ?? '',
        per_page: filters?.per_page ?? 20,
    });
    const [selectedCustomer, setSelectedCustomer] = useState(customer);
    const [customerSearch, setCustomerSearch] = useState('');
    const [results, setResults] = useState([]);
    const [searching, setSearching] = useState(false);
    const [showResults, setShowResults] = useState(false);
    const [filtersCollapsed, setFiltersCollapsed] = useState(false);
    const timer = useRef(null);

    const query = useMemo(() => cleanQuery(form), [form]);
    const hasCustomer = Boolean(form.customer_id && selectedCustomer);
    const rows = entries?.data ?? [];

    const update = (key, value) => setForm(current => ({ ...current, [key]: value }));

    const searchCustomers = value => {
        setCustomerSearch(value);
        setShowResults(true);
        clearTimeout(timer.current);

        if (value.trim().length < 2) {
            setResults([]);
            setSearching(false);
            return;
        }

        setSearching(true);
        timer.current = setTimeout(async () => {
            try {
                const response = await axios.get('/customer-ledger/customers/search', {
                    params: { search: value.trim() },
                });
                setResults(response.data?.data ?? []);
            } catch {
                setResults([]);
            } finally {
                setSearching(false);
            }
        }, 300);
    };

    const chooseCustomer = item => {
        setSelectedCustomer(item);
        setForm(current => ({ ...current, customer_id: item.id }));
        setCustomerSearch('');
        setResults([]);
        setShowResults(false);
    };

    const clearCustomer = () => {
        setSelectedCustomer(null);
        setCustomerSearch('');
        setResults([]);
        setForm(current => ({ ...current, customer_id: '' }));
    };

    const submit = event => {
        event.preventDefault();
        router.get('/customer-ledger', cleanQuery(form), {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    };

    const reset = () => {
        const next = {
            customer_id: selectedCustomer?.id ?? '',
            from_date: firstDayOfMonth(),
            to_date: localDate(),
            transaction_type: '',
            search: '',
            per_page: 20,
        };
        setForm(next);
        router.get('/customer-ledger', cleanQuery(next), { replace: true });
    };

    return (
        <BusinessLayout>
            <Head title="Customer Ledger" />
            <div className="rr-cl-page">
                <MasterNavigation active="customer-ledger" title="Customer Ledger" subtitle="Customer receivables and advance register" />

                <section className="rr-cl-header">
                    <div>
                        <span className="rr-cl-eyebrow"><i className="bi bi-person-lines-fill"></i> Accounts Receivable</span>
                        <h1>Customer Ledger</h1>
                        <p>Review invoices, returns, receipts, reversals and running balances</p>
                    </div>
                    <div className="rr-cl-export-actions">
                        <ExportLink href={hasCustomer ? `/customer-ledger/print?${toQuery(query)}` : null} icon="bi-printer" label="Print" tone="print" />
                        <ExportLink href={hasCustomer ? `/customer-ledger/pdf?${toQuery(query)}` : null} icon="bi-file-earmark-pdf" label="PDF" tone="pdf" />
                        <ExportLink href={hasCustomer ? `/customer-ledger/excel?${toQuery(query)}` : null} icon="bi-file-earmark-excel" label="Excel" tone="excel" />
                    </div>
                </section>

                <section className="rr-cl-customer-panel">
                    <header><span><i className="bi bi-person-search"></i></span><div><strong>Select Customer</strong><small>Search by customer name, code, mobile or GST number</small></div></header>
                    <div className="rr-cl-customer-body">
                        {selectedCustomer ? (
                            <div className="rr-cl-selected-customer">
                                <span className="avatar">{initials(selectedCustomer.customer_name)}</span>
                                <div className="identity"><small>Selected customer</small><strong>{selectedCustomer.customer_name}</strong><span>{selectedCustomer.customer_code}{selectedCustomer.mobile ? ` · ${selectedCustomer.mobile}` : ''}</span></div>
                                <div className="balance"><small>Current displayed balance</small><strong className={number(selectedCustomer.current_balance) < 0 ? 'credit' : 'debit'}>₹{money(Math.abs(number(selectedCustomer.current_balance)))} {number(selectedCustomer.current_balance) < 0 ? 'Cr' : 'Dr'}</strong></div>
                                <button type="button" onClick={clearCustomer} title="Change customer"><i className="bi bi-x-lg"></i></button>
                            </div>
                        ) : (
                            <div className="rr-cl-customer-search">
                                <div className="rr-cl-search-input"><i className="bi bi-search"></i><input value={customerSearch} onChange={event => searchCustomers(event.target.value)} onFocus={() => setShowResults(true)} placeholder="Type at least 2 characters..." autoComplete="off" />{searching ? <span className="spinner-border spinner-border-sm"></span> : customerSearch && <button type="button" onClick={() => searchCustomers('')}><i className="bi bi-x-lg"></i></button>}</div>
                                {showResults && customerSearch.trim().length >= 2 && (
                                    <div className="rr-cl-search-results">
                                        {searching ? <SearchState icon="bi-hourglass-split" text="Searching customers..." /> : results.length ? results.map(item => (
                                            <button type="button" key={item.id} onClick={() => chooseCustomer(item)}>
                                                <span className="avatar">{initials(item.customer_name)}</span>
                                                <span className="identity"><strong>{item.customer_name}</strong><small>{item.customer_code}{item.mobile ? ` · ${item.mobile}` : ''}</small></span>
                                                <span className={`balance ${number(item.current_balance) < 0 ? 'credit' : 'debit'}`}><small>Balance</small><strong>₹{money(Math.abs(number(item.current_balance)))} {number(item.current_balance) < 0 ? 'Cr' : 'Dr'}</strong></span>
                                            </button>
                                        )) : <SearchState icon="bi-person-x" text="No customer found" />}
                                    </div>
                                )}
                            </div>
                        )}
                    </div>
                </section>

                <form className={`rr-cl-filter-panel ${filtersCollapsed ? 'collapsed' : ''}`} onSubmit={submit}>
                    <header>
                        <div><i className="bi bi-funnel"></i><span><strong>Ledger Filters</strong><small>Select period and transaction type</small></span></div>
                        <div className="actions"><button type="button" onClick={reset}><i className="bi bi-arrow-counterclockwise"></i> Reset</button><button type="button" className="icon" onClick={() => setFiltersCollapsed(value => !value)}><i className={`bi ${filtersCollapsed ? 'bi-chevron-down' : 'bi-chevron-up'}`}></i></button></div>
                    </header>
                    <div className="rr-cl-filter-body">
                        <label><span><i className="bi bi-calendar3"></i> From Date</span><input type="date" value={form.from_date} max={form.to_date || localDate()} onChange={e => update('from_date', e.target.value)} /></label>
                        <label><span><i className="bi bi-calendar3"></i> To Date</span><input type="date" value={form.to_date} min={form.from_date} max={localDate()} onChange={e => update('to_date', e.target.value)} /></label>
                        <label><span><i className="bi bi-arrow-left-right"></i> Transaction</span><select value={form.transaction_type} onChange={e => update('transaction_type', e.target.value)}><option value="">All transactions</option>{Object.entries(transactionTypes).map(([value, label]) => <option key={value} value={value}>{label}</option>)}</select></label>
                        <label className="search"><span><i className="bi bi-search"></i> Reference / Remarks</span><input type="search" value={form.search} onChange={e => update('search', e.target.value)} placeholder="Invoice, receipt or remarks" /></label>
                        <label className="rows"><span><i className="bi bi-list-ol"></i> Rows</span><select value={form.per_page} onChange={e => update('per_page', e.target.value)}><option value="20">20</option><option value="50">50</option><option value="100">100</option></select></label>
                        <button className="apply" type="submit" disabled={!form.customer_id}><i className="bi bi-search"></i> View Ledger</button>
                    </div>
                </form>

                {hasCustomer && <SummaryCards summary={summary} />}

                <LedgerTable entries={entries} rows={rows} hasCustomer={hasCustomer} summary={summary} fromDate={form.from_date} />
            </div>
        </BusinessLayout>
    );
}

function SummaryCards({ summary }) {
    const cards = [
        ['Opening Balance', summary.opening_balance, 'bi-box-arrow-in-right', 'navy'],
        ['Period Debit', summary.period_debit, 'bi-arrow-up-right', 'red'],
        ['Period Credit', summary.period_credit, 'bi-arrow-down-left', 'green'],
        ['Closing Balance', summary.closing_balance, 'bi-calculator', number(summary.closing_balance) < 0 ? 'amber' : 'blue'],
    ];
    return <section className="rr-cl-metrics">{cards.map(([label, value, icon, tone]) => <article className={`rr-cl-metric ${tone}`} key={label}><span><i className={`bi ${icon}`}></i></span><div><small>{label}</small><strong>₹{money(Math.abs(number(value)))} <em>{number(value) < 0 ? 'Cr' : 'Dr'}</em></strong></div></article>)}</section>;
}

function LedgerTable({ entries, rows, hasCustomer, summary, fromDate }) {
    return <section className="rr-cl-table-panel">
        <header><div><span><i className="bi bi-journal-text"></i></span><div><strong>Ledger Transactions</strong><small>{entries?.total ?? 0} records in selected period</small></div></div></header>
        {!hasCustomer ? <Empty icon="bi-person-check" title="Select a customer" text="Search and select a customer to display their ledger." /> : (
            <div className="rr-cl-table-wrap"><table className="rr-cl-table">
                <thead><tr><th className="serial">#</th><th>Date</th><th>Transaction</th><th>Reference</th><th>Remarks</th><th className="amount">Debit</th><th className="amount">Credit</th><th className="amount">Balance</th></tr></thead>
                <tbody>
                    <tr className="opening"><td className="serial">—</td><td>{formatDate(fromDate)}</td><td><span className="rr-cl-type opening-balance">Opening Balance</span></td><td>—</td><td>Balance brought forward</td><td className="amount">—</td><td className="amount">—</td><td className="amount balance">{balance(summary.opening_balance)}</td></tr>
                    {rows.length ? rows.map((entry, index) => <LedgerRow key={entry.id} entry={entry} serial={(entries?.from ?? 1) + index} />) : <tr><td colSpan="8"><Empty icon="bi-journal-x" title="No ledger entries found" text="No transactions match the selected date and filters." compact /></td></tr>}
                </tbody>
            </table></div>
        )}
        {hasCustomer && <Pagination paginator={entries} />}
    </section>;
}

function LedgerRow({ entry, serial }) {
    return <tr className={entry.is_reversal ? 'reversal' : ''}>
        <td className="serial">{serial}</td><td><strong className="date">{formatDate(entry.transaction_date)}</strong></td>
        <td><span className={`rr-cl-type ${entry.transaction_type}`}>{entry.type_label}</span>{entry.is_reversal && <small className="rr-cl-reversal-label"><i className="bi bi-arrow-counterclockwise"></i> Reversal</small>}</td>
        <td>{entry.reference_url ? <a href={entry.reference_url} className="reference">{entry.reference_no || `#${entry.reference_id}`} <i className="bi bi-box-arrow-up-right"></i></a> : <span>{entry.reference_no || '—'}</span>}</td>
        <td><span className="remarks" title={entry.remarks || ''}>{entry.remarks || '—'}</span>{entry.creator?.name && <small className="creator">By {entry.creator.name}</small>}</td>
        <td className="amount debit">{number(entry.debit) ? `₹${money(entry.debit)}` : '—'}</td><td className="amount credit">{number(entry.credit) ? `₹${money(entry.credit)}` : '—'}</td><td className={`amount balance ${number(entry.running_balance) < 0 ? 'cr' : 'dr'}`}>{balance(entry.running_balance)}</td>
    </tr>;
}

function Pagination({ paginator }) { if (!paginator || number(paginator.last_page) <= 1) return null; return <footer className="rr-cl-pagination"><span>Showing {paginator.from ?? 0}–{paginator.to ?? 0} of {paginator.total ?? 0}</span><nav>{(paginator.links ?? []).map((link, index) => link.url ? <Link key={index} href={link.url} preserveScroll preserveState className={link.active ? 'active' : ''} dangerouslySetInnerHTML={{ __html: link.label }} /> : <span key={index} className="disabled" dangerouslySetInnerHTML={{ __html: link.label }} />)}</nav></footer>; }
function ExportLink({ href, icon, label, tone }) { return href ? <a href={href} target={tone === 'print' ? '_blank' : undefined} rel="noreferrer" className={tone}><i className={`bi ${icon}`}></i>{label}</a> : <span className={`disabled ${tone}`} title="Select a customer first"><i className={`bi ${icon}`}></i>{label}</span>; }
function Empty({ icon, title, text, compact }) { return <div className={`rr-cl-empty ${compact ? 'compact' : ''}`}><i className={`bi ${icon}`}></i><strong>{title}</strong><span>{text}</span></div>; }
function SearchState({ icon, text }) { return <div className="rr-cl-search-state"><i className={`bi ${icon}`}></i>{text}</div>; }
function number(value) { const result = Number(value); return Number.isFinite(result) ? result : 0; }
function money(value) { return number(value).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
function balance(value) { const amount = number(value); return `₹${money(Math.abs(amount))} ${amount < 0 ? 'Cr' : 'Dr'}`; }
function initials(value) { return String(value ?? 'C').trim().split(/\s+/).slice(0, 2).map(word => word[0]).join('').toUpperCase(); }
function formatDate(value) { if (!value) return '—'; const [y, m, d] = String(value).slice(0, 10).split('-'); return y && m && d ? `${d}-${m}-${y}` : value; }
function cleanQuery(values) { return Object.fromEntries(Object.entries(values).filter(([, value]) => value !== '' && value !== null && value !== undefined)); }
function toQuery(values) { return new URLSearchParams(Object.entries(values).map(([key, value]) => [key, String(value)])).toString(); }
function localDate() { const now = new Date(); return new Date(now.getTime() - now.getTimezoneOffset() * 60000).toISOString().slice(0, 10); }
function firstDayOfMonth() { return `${localDate().slice(0, 8)}01`; }

Index.layout = page => page;
