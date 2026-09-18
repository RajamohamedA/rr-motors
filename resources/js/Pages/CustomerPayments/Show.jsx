import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';

import BusinessLayout from '../../Layouts/BusinessLayout';
import MasterNavigation from '../../Components/Business/MasterNavigation';
import '../../../css/business/customer-payments.css';

export default function Show({
    payment,
    account_transaction: accountTransaction = null,
    customer_ledger_entries: ledgerEntries = [],
}) {
    const [showCancel, setShowCancel] = useState(false);
    const [cancelling, setCancelling] = useState(false);
    const cancelled = payment?.status === 'Cancelled';

    const cancelReceipt = () => {
        if (cancelling) return;
        setCancelling(true);
        router.patch(`/customer-payments/${payment.id}/cancel`, {}, {
            preserveScroll: true,
            onSuccess: () => setShowCancel(false),
            onFinish: () => setCancelling(false),
        });
    };

    return (
        <BusinessLayout>
            <Head title={`Receipt ${payment.receipt_no}`} />
            <div className="rr-cp-page rr-cp-show-page">
                <MasterNavigation active="customer-payments" title="Customer Payments" subtitle="Receipt details and audit trail" />

                <section className="rr-cp-header rr-cp-show-header">
                    <div>
                        <span className="rr-cp-eyebrow"><i className="bi bi-receipt"></i> Customer Receipt</span>
                        <h1>{payment.receipt_no}</h1>
                        <p>Recorded on {formatDate(payment.payment_date)} · {payment.payment_mode}</p>
                    </div>
                    <div className="rr-cp-document-actions">
                        <Link href="/customer-payments" className="back"><i className="bi bi-arrow-left"></i> Register</Link>
                        <a href={`/customer-payments/${payment.id}/print`} target="_blank" rel="noreferrer" className="print"><i className="bi bi-printer"></i> Print</a>
                        <a href={`/customer-payments/${payment.id}/pdf`} className="pdf"><i className="bi bi-file-earmark-pdf"></i> PDF</a>
                        {payment.can_cancel && <button type="button" className="cancel" onClick={() => setShowCancel(true)}><i className="bi bi-x-circle"></i> Cancel</button>}
                    </div>
                </section>

                {cancelled && (
                    <div className="rr-cp-cancelled-banner">
                        <i className="bi bi-exclamation-octagon"></i>
                        <div><strong>This receipt is cancelled</strong><span>All accounting and customer ledger effects were reversed. The document remains available for audit.</span></div>
                    </div>
                )}

                <section className="rr-cp-show-metrics">
                    <Metric label="Receipt Amount" value={`₹${money(payment.amount)}`} icon="bi-cash-stack" tone="green" />
                    <Metric label="Allocated" value={`₹${money(payment.allocated_amount)}`} icon="bi-files" tone="blue" />
                    <Metric label="Customer Advance" value={`₹${money(payment.unallocated_amount)}`} icon="bi-wallet2" tone="amber" />
                    <Metric label="Status" value={payment.status} icon={cancelled ? 'bi-x-circle' : 'bi-check2-circle'} tone={cancelled ? 'red' : 'green'} />
                </section>

                <div className="rr-cp-show-grid">
                    <main className="rr-cp-show-main">
                        <ShowCard icon="bi-files" title="Invoice Allocations" note={`${payment.allocations?.length ?? 0} allocated invoice(s)`}>
                            {payment.allocations?.length ? (
                                <div className="rr-cp-show-table-wrap">
                                    <table className="rr-cp-show-table">
                                        <thead><tr><th>#</th><th>Invoice</th><th>Invoice Date</th><th className="amount">Bill Amount</th><th className="amount">Allocated</th></tr></thead>
                                        <tbody>{payment.allocations.map((allocation, index) => (
                                            <tr key={allocation.id}>
                                                <td>{index + 1}</td>
                                                <td><strong>{allocation.sale?.invoice_no ?? `Sale #${allocation.sale_id}`}</strong></td>
                                                <td>{formatDate(allocation.sale?.invoice_date)}</td>
                                                <td className="amount">₹{money(allocation.sale?.grand_total)}</td>
                                                <td className="amount allocated">₹{money(allocation.allocated_amount)}</td>
                                            </tr>
                                        ))}</tbody>
                                        <tfoot><tr><td colSpan="4">Total Allocated</td><td className="amount">₹{money(payment.allocated_amount)}</td></tr></tfoot>
                                    </table>
                                </div>
                            ) : (
                                <div className="rr-cp-advance-message"><i className="bi bi-wallet2"></i><div><strong>Advance Customer Receipt</strong><span>No invoice was selected. The full amount is available as customer advance.</span></div><b>₹{money(payment.amount)}</b></div>
                            )}
                        </ShowCard>

                        <ShowCard icon="bi-journal-text" title="Accounting Audit" note="Posted account and customer ledger entries">
                            <div className="rr-cp-audit-grid">
                                <AuditBlock title="Account Transaction" icon="bi-bank" empty={!accountTransaction}>
                                    {accountTransaction && <>
                                        <InfoRow label="Transaction Type" value={accountTransaction.transaction_type} />
                                        <InfoRow label="Transaction Date" value={formatDate(accountTransaction.transaction_date)} />
                                        <InfoRow label="Debit" value={`₹${money(accountTransaction.debit)}`} moneyValue />
                                        <InfoRow label="Credit" value={`₹${money(accountTransaction.credit)}`} moneyValue />
                                        <InfoRow label="Reversal" value={accountTransaction.is_reversal ? 'Yes' : 'No'} />
                                    </>}
                                </AuditBlock>
                                <AuditBlock title="Customer Ledger" icon="bi-person-lines-fill" empty={!ledgerEntries.length}>
                                    {ledgerEntries.map(entry => (
                                        <div className="rr-cp-ledger-entry" key={entry.id}>
                                            <div><strong>{entry.transaction_type}</strong><small>{formatDate(entry.transaction_date)} · {entry.reference_no || payment.receipt_no}</small></div>
                                            <span className={number(entry.credit) > 0 ? 'credit' : 'debit'}>{number(entry.credit) > 0 ? 'Cr' : 'Dr'} ₹{money(number(entry.credit) || number(entry.debit))}</span>
                                        </div>
                                    ))}
                                </AuditBlock>
                            </div>
                        </ShowCard>
                    </main>

                    <aside className="rr-cp-show-sidebar">
                        <ShowCard icon="bi-person" title="Customer" note="Receipt party">
                            <div className="rr-cp-show-customer"><span>{initials(payment.customer?.customer_name)}</span><div><strong>{payment.customer?.customer_name ?? '—'}</strong><small>{payment.customer?.customer_code ?? '—'}</small></div></div>
                            <InfoList rows={[
                                ['Mobile', payment.customer?.mobile, 'bi-telephone'],
                                ['GST Number', payment.customer?.gst_number, 'bi-file-text'],
                                ['Email', payment.customer?.email, 'bi-envelope'],
                            ]} />
                        </ShowCard>

                        <ShowCard icon="bi-credit-card" title="Payment Information" note="Collection and deposit details">
                            <InfoList rows={[
                                ['Receipt Number', payment.receipt_no, 'bi-receipt'],
                                ['Payment Date', formatDate(payment.payment_date), 'bi-calendar3'],
                                ['Payment Mode', payment.payment_mode, 'bi-credit-card'],
                                ['Deposit Account', payment.account?.account_name, 'bi-bank'],
                                ['Account Code', payment.account?.account_code, 'bi-hash'],
                                ['Reference Number', payment.reference_no, 'bi-link-45deg'],
                            ]} />
                        </ShowCard>

                        <ShowCard icon="bi-shield-check" title="Document Audit" note="Creation and cancellation details">
                            <InfoList rows={[
                                ['Created By', payment.creator?.name, 'bi-person-check'],
                                ['Status', payment.status, 'bi-check2-circle'],
                                ['Cancelled By', payment.canceller?.name, 'bi-person-x'],
                                ['Cancelled At', formatDateTime(payment.cancelled_at), 'bi-clock-history'],
                            ]} />
                            {payment.remarks && <div className="rr-cp-show-remarks"><small>Remarks</small><p>{payment.remarks}</p></div>}
                        </ShowCard>
                    </aside>
                </div>
            </div>

            {showCancel && (
                <div className="rr-cp-modal-backdrop" onMouseDown={() => !cancelling && setShowCancel(false)}>
                    <section className="rr-cp-modal" role="dialog" aria-modal="true" onMouseDown={event => event.stopPropagation()}>
                        <header><span><i className="bi bi-exclamation-triangle"></i></span><div><strong>Cancel Customer Receipt?</strong><small>This creates reversing entries; it does not delete records.</small></div></header>
                        <div className="rr-cp-modal-body"><dl><div><dt>Receipt</dt><dd>{payment.receipt_no}</dd></div><div><dt>Customer</dt><dd>{payment.customer?.customer_name}</dd></div><div><dt>Amount</dt><dd>₹{money(payment.amount)}</dd></div></dl><p>Invoice balances and the customer's advance position will be restored automatically.</p></div>
                        <footer><button type="button" className="secondary" disabled={cancelling} onClick={() => setShowCancel(false)}>Keep Receipt</button><button type="button" className="danger" disabled={cancelling} onClick={cancelReceipt}>{cancelling ? <><span className="spinner-border spinner-border-sm"></span> Cancelling...</> : <><i className="bi bi-x-circle"></i> Cancel Receipt</>}</button></footer>
                    </section>
                </div>
            )}
        </BusinessLayout>
    );
}

function Metric({ label, value, icon, tone }) { return <article className={`rr-cp-show-metric ${tone}`}><span><i className={`bi ${icon}`}></i></span><div><small>{label}</small><strong>{value}</strong></div></article>; }
function ShowCard({ icon, title, note, children }) { return <section className="rr-cp-show-card"><header><span><i className={`bi ${icon}`}></i></span><div><strong>{title}</strong><small>{note}</small></div></header><div className="rr-cp-show-card-body">{children}</div></section>; }
function AuditBlock({ title, icon, empty, children }) { return <section className="rr-cp-audit-block"><header><i className={`bi ${icon}`}></i>{title}</header><div>{empty ? <span className="rr-cp-no-audit">No accounting entry found</span> : children}</div></section>; }
function InfoRow({ label, value, moneyValue }) { return <div className="rr-cp-info-row"><span>{label}</span><strong className={moneyValue ? 'money' : ''}>{value ?? '—'}</strong></div>; }
function InfoList({ rows }) { return <div className="rr-cp-info-list">{rows.filter(([, value]) => value !== null && value !== undefined && value !== '').map(([label, value, icon]) => <div key={label}><i className={`bi ${icon}`}></i><span><small>{label}</small><strong>{value}</strong></span></div>)}</div>; }
function number(value) { const result = Number(value); return Number.isFinite(result) ? result : 0; }
function money(value) { return number(value).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); }
function formatDate(value) { if (!value) return '—'; const [y, m, d] = String(value).slice(0, 10).split('-'); return y && m && d ? `${d}-${m}-${y}` : value; }
function formatDateTime(value) { if (!value) return '—'; const date = new Date(value); return Number.isNaN(date.getTime()) ? value : date.toLocaleString('en-IN'); }
function initials(value) { return String(value ?? 'C').trim().split(/\s+/).slice(0, 2).map(word => word[0]).join('').toUpperCase(); }

Show.layout = page => page;
