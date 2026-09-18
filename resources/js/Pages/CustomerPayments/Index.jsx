import {
    Head,
    Link,
    router,
} from '@inertiajs/react';

import {
    useMemo,
    useState,
} from 'react';

import BusinessLayout from '../../Layouts/BusinessLayout';
import MasterNavigation from '../../Components/Business/MasterNavigation';

import '../../../css/business/customer-payments.css';

export default function Index({
    payments = {},
    summary = {},
    filters = {},
    payment_modes: paymentModes = [],
}) {
    const [form, setForm] = useState({
        search: filters?.search ?? '',
        status: filters?.status ?? '',
        payment_mode: filters?.payment_mode ?? '',
        from_date: filters?.from_date ?? '',
        to_date: filters?.to_date ?? '',
        per_page: filters?.per_page ?? 20,
    });

    const [filtersCollapsed, setFiltersCollapsed] = useState(false);
    const [cancelPayment, setCancelPayment] = useState(null);
    const [cancelling, setCancelling] = useState(false);

    const rows = payments?.data ?? [];

    const activeFilterCount = useMemo(
        () => Object.entries(form).filter(
            ([key, value]) => key !== 'per_page' && value !== '' && value !== null
        ).length,
        [form]
    );

    const update = (field, value) => {
        setForm(current => ({
            ...current,
            [field]: value,
        }));
    };

    const submit = event => {
        event.preventDefault();

        router.get(
            '/customer-payments',
            cleanQuery(form),
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            }
        );
    };

    const reset = () => {
        const cleared = {
            search: '',
            status: '',
            payment_mode: '',
            from_date: '',
            to_date: '',
            per_page: 20,
        };

        setForm(cleared);

        router.get(
            '/customer-payments',
            {},
            {
                preserveState: false,
                replace: true,
            }
        );
    };

    const confirmCancellation = () => {
        if (!cancelPayment || cancelling) {
            return;
        }

        setCancelling(true);

        router.patch(
            `/customer-payments/${cancelPayment.id}/cancel`,
            {},
            {
                preserveScroll: true,
                onSuccess: () => setCancelPayment(null),
                onFinish: () => setCancelling(false),
            }
        );
    };

    return (
        <BusinessLayout>
            <Head title="Customer Payments" />

            <div className="rr-cp-page">
                <MasterNavigation
                    active="customer-payments"
                    title="Customer Payments"
                    subtitle="Receipts, bill allocations and customer advances"
                />

                <section className="rr-cp-header">
                    <div>
                        <span className="rr-cp-eyebrow">
                            <i className="bi bi-cash-coin"></i>
                            Accounts Receivable
                        </span>

                        <h1>Customer Payments</h1>
                        <p>Receive, allocate and audit branch customer collections</p>
                    </div>

                    <Link
                        href="/customer-payments/create"
                        className="rr-cp-new-button"
                    >
                        <i className="bi bi-plus-lg"></i>
                        New Receipt
                    </Link>
                </section>

                <SummaryCards summary={summary} />

                <form
                    className={`rr-cp-filter-panel ${filtersCollapsed ? 'collapsed' : ''}`}
                    onSubmit={submit}
                >
                    <header className="rr-cp-panel-title">
                        <div>
                            <i className="bi bi-funnel"></i>
                            <span>
                                <strong>Search & Filters</strong>
                                <small>{activeFilterCount} active filter{activeFilterCount === 1 ? '' : 's'}</small>
                            </span>
                        </div>

                        <div className="rr-cp-panel-actions">
                            <button type="button" onClick={reset}>
                                <i className="bi bi-arrow-counterclockwise"></i>
                                Reset
                            </button>

                            <button
                                type="button"
                                className="icon-only"
                                onClick={() => setFiltersCollapsed(current => !current)}
                                title={filtersCollapsed ? 'Expand filters' : 'Collapse filters'}
                            >
                                <i className={`bi ${filtersCollapsed ? 'bi-chevron-down' : 'bi-chevron-up'}`}></i>
                            </button>
                        </div>
                    </header>

                    <div className="rr-cp-filter-body">
                        <div className="rr-cp-filter-grid">
                            <label className="rr-cp-field search">
                                <span><i className="bi bi-search"></i> Search</span>
                                <div className="rr-cp-search-input">
                                    <i className="bi bi-search"></i>
                                    <input
                                        type="search"
                                        value={form.search}
                                        onChange={event => update('search', event.target.value)}
                                        placeholder="Receipt, customer, mobile or reference"
                                    />
                                    {form.search && (
                                        <button type="button" onClick={() => update('search', '')}>
                                            <i className="bi bi-x-lg"></i>
                                        </button>
                                    )}
                                </div>
                            </label>

                            <FilterSelect
                                label="Status"
                                icon="bi-check2-circle"
                                value={form.status}
                                onChange={value => update('status', value)}
                                options={[
                                    ['Completed', 'Completed'],
                                    ['Cancelled', 'Cancelled'],
                                ]}
                                placeholder="All statuses"
                            />

                            <FilterSelect
                                label="Payment Mode"
                                icon="bi-credit-card"
                                value={form.payment_mode}
                                onChange={value => update('payment_mode', value)}
                                options={paymentModes.map(mode => [mode, mode])}
                                placeholder="All modes"
                            />

                            <FilterInput
                                label="From Date"
                                icon="bi-calendar3"
                                type="date"
                                value={form.from_date}
                                onChange={value => update('from_date', value)}
                            />

                            <FilterInput
                                label="To Date"
                                icon="bi-calendar3"
                                type="date"
                                value={form.to_date}
                                onChange={value => update('to_date', value)}
                            />

                            <label className="rr-cp-field rows">
                                <span><i className="bi bi-list-ol"></i> Rows</span>
                                <select
                                    value={form.per_page}
                                    onChange={event => update('per_page', event.target.value)}
                                >
                                    <option value="20">20</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </label>

                            <button type="submit" className="rr-cp-apply-button">
                                <i className="bi bi-search"></i>
                                Apply
                            </button>
                        </div>
                    </div>
                </form>

                <PaymentTable
                    payments={payments}
                    rows={rows}
                    onCancel={setCancelPayment}
                />
            </div>

            <CancelModal
                payment={cancelPayment}
                processing={cancelling}
                onClose={() => !cancelling && setCancelPayment(null)}
                onConfirm={confirmCancellation}
            />
        </BusinessLayout>
    );
}

function SummaryCards({ summary }) {
    const cards = [
        {
            label: 'Collected Amount',
            value: `₹${money(summary?.completed_amount)}`,
            note: `${summary?.completed_count ?? 0} completed receipts`,
            icon: 'bi-cash-stack',
            tone: 'green',
        },
        {
            label: 'Completed Receipts',
            value: summary?.completed_count ?? 0,
            note: 'Successfully posted',
            icon: 'bi-check2-circle',
            tone: 'blue',
        },
        {
            label: 'Customer Advance',
            value: `₹${money(summary?.advance_amount)}`,
            note: 'Not yet allocated',
            icon: 'bi-wallet2',
            tone: 'amber',
        },
        {
            label: 'Cancelled Amount',
            value: `₹${money(summary?.cancelled_amount)}`,
            note: 'Reversed receipts',
            icon: 'bi-arrow-counterclockwise',
            tone: 'red',
        },
    ];

    return (
        <section className="rr-cp-metrics">
            {cards.map(card => (
                <article key={card.label} className={`rr-cp-metric ${card.tone}`}>
                    <span className="rr-cp-metric-icon">
                        <i className={`bi ${card.icon}`}></i>
                    </span>
                    <div>
                        <small>{card.label}</small>
                        <strong>{card.value}</strong>
                        <em>{card.note}</em>
                    </div>
                </article>
            ))}
        </section>
    );
}

function PaymentTable({ payments, rows, onCancel }) {
    return (
        <section className="rr-cp-table-panel">
            <header className="rr-cp-table-heading">
                <div>
                    <span className="rr-cp-table-icon">
                        <i className="bi bi-receipt"></i>
                    </span>
                    <span>
                        <strong>Receipt Register</strong>
                        <small>{payments?.total ?? 0} payment records</small>
                    </span>
                </div>
            </header>

            <div className="rr-cp-table-wrap">
                <table className="rr-cp-table">
                    <thead>
                        <tr>
                            <th className="serial">#</th>
                            <th>Receipt / Date</th>
                            <th>Customer</th>
                            <th>Allocation</th>
                            <th>Account / Mode</th>
                            <th className="amount">Amount</th>
                            <th>Status</th>
                            <th className="actions">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        {rows.length ? rows.map((payment, index) => (
                            <PaymentRow
                                key={payment.id}
                                payment={payment}
                                serial={(payments?.from ?? 1) + index}
                                onCancel={onCancel}
                            />
                        )) : (
                            <tr>
                                <td colSpan="8">
                                    <div className="rr-cp-empty">
                                        <i className="bi bi-receipt-cutoff"></i>
                                        <strong>No customer payments found</strong>
                                        <span>Change the filters or record a new customer receipt.</span>
                                        <Link href="/customer-payments/create">
                                            <i className="bi bi-plus-lg"></i>
                                            New Receipt
                                        </Link>
                                    </div>
                                </td>
                            </tr>
                        )}
                    </tbody>
                </table>
            </div>

            <Pagination paginator={payments} />
        </section>
    );
}

function PaymentRow({ payment, serial, onCancel }) {
    const allocations = payment?.allocations ?? [];
    const cancelled = payment?.status === 'Cancelled';

    return (
        <tr className={cancelled ? 'cancelled-row' : ''}>
            <td className="serial">{serial}</td>
            <td>
                <Link
                    href={`/customer-payments/${payment.id}`}
                    className="rr-cp-receipt-number"
                >
                    {payment.receipt_no}
                </Link>
                <span className="rr-cp-date">
                    <i className="bi bi-calendar3"></i>
                    {formatDate(payment.payment_date)}
                </span>
                {payment.reference_no && (
                    <small className="rr-cp-reference">Ref: {payment.reference_no}</small>
                )}
            </td>
            <td>
                <div className="rr-cp-customer">
                    <span>{initials(payment?.customer?.customer_name)}</span>
                    <div>
                        <strong>{payment?.customer?.customer_name ?? 'Unknown customer'}</strong>
                        <small>
                            {payment?.customer?.customer_code ?? '—'}
                            {payment?.customer?.mobile ? ` · ${payment.customer.mobile}` : ''}
                        </small>
                    </div>
                </div>
            </td>
            <td>
                {allocations.length ? (
                    <div className="rr-cp-allocation">
                        <strong>{allocations.length} invoice{allocations.length === 1 ? '' : 's'}</strong>
                        <small>₹{money(payment.allocated_amount)} allocated</small>
                        {Number(payment.unallocated_amount) > 0 && (
                            <em>₹{money(payment.unallocated_amount)} advance</em>
                        )}
                    </div>
                ) : (
                    <span className="rr-cp-advance">
                        <i className="bi bi-wallet2"></i>
                        Advance
                    </span>
                )}
            </td>
            <td>
                <div className="rr-cp-account">
                    <strong>{payment?.account?.account_name ?? payment.payment_mode}</strong>
                    <small>
                        <i className="bi bi-credit-card"></i>
                        {payment.payment_mode}
                    </small>
                </div>
            </td>
            <td className="amount">
                <strong>₹{money(payment.amount)}</strong>
            </td>
            <td>
                <span className={`rr-cp-status ${String(payment.status).toLowerCase()}`}>
                    <i className={`bi ${cancelled ? 'bi-x-circle' : 'bi-check-circle'}`}></i>
                    {payment.status}
                </span>
            </td>
            <td className="actions">
                <div className="rr-cp-row-actions">
                    <Link
                        href={`/customer-payments/${payment.id}`}
                        className="view"
                        title="View receipt"
                    >
                        <i className="bi bi-eye"></i>
                    </Link>
                    <a
                        href={`/customer-payments/${payment.id}/print`}
                        target="_blank"
                        rel="noreferrer"
                        className="print"
                        title="Print receipt"
                    >
                        <i className="bi bi-printer"></i>
                    </a>
                    <a
                        href={`/customer-payments/${payment.id}/pdf`}
                        className="pdf"
                        title="Download PDF"
                    >
                        <i className="bi bi-file-earmark-pdf"></i>
                    </a>
                    {payment.can_cancel && (
                        <button
                            type="button"
                            className="cancel"
                            onClick={() => onCancel(payment)}
                            title="Cancel receipt"
                        >
                            <i className="bi bi-x-lg"></i>
                        </button>
                    )}
                </div>
            </td>
        </tr>
    );
}

function FilterInput({ label, icon, type, value, onChange }) {
    return (
        <label className="rr-cp-field">
            <span><i className={`bi ${icon}`}></i> {label}</span>
            <input
                type={type}
                value={value}
                max={type === 'date' ? todayString() : undefined}
                onChange={event => onChange(event.target.value)}
            />
        </label>
    );
}

function FilterSelect({ label, icon, value, onChange, options, placeholder }) {
    return (
        <label className="rr-cp-field">
            <span><i className={`bi ${icon}`}></i> {label}</span>
            <select value={value} onChange={event => onChange(event.target.value)}>
                <option value="">{placeholder}</option>
                {options.map(([optionValue, optionLabel]) => (
                    <option key={optionValue} value={optionValue}>{optionLabel}</option>
                ))}
            </select>
        </label>
    );
}

function Pagination({ paginator }) {
    if (Number(paginator?.last_page ?? 1) <= 1) {
        return null;
    }

    return (
        <footer className="rr-cp-table-footer">
            <span>
                Showing {paginator?.from ?? 0}–{paginator?.to ?? 0} of {paginator?.total ?? 0}
            </span>
            <nav>
                {(paginator?.links ?? []).map((link, index) => link.url ? (
                    <Link
                        key={index}
                        href={link.url}
                        preserveScroll
                        preserveState
                        className={link.active ? 'active' : ''}
                        dangerouslySetInnerHTML={{ __html: link.label }}
                    />
                ) : (
                    <span
                        key={index}
                        className="disabled"
                        dangerouslySetInnerHTML={{ __html: link.label }}
                    />
                ))}
            </nav>
        </footer>
    );
}

function CancelModal({ payment, processing, onClose, onConfirm }) {
    if (!payment) {
        return null;
    }

    return (
        <div className="rr-cp-modal-backdrop" role="presentation" onMouseDown={onClose}>
            <section
                className="rr-cp-modal"
                role="dialog"
                aria-modal="true"
                aria-labelledby="cancel-payment-title"
                onMouseDown={event => event.stopPropagation()}
            >
                <header>
                    <span><i className="bi bi-exclamation-triangle"></i></span>
                    <div>
                        <strong id="cancel-payment-title">Cancel Customer Receipt?</strong>
                        <small>This action creates reversing accounting entries.</small>
                    </div>
                </header>
                <div className="rr-cp-modal-body">
                    <dl>
                        <div><dt>Receipt</dt><dd>{payment.receipt_no}</dd></div>
                        <div><dt>Customer</dt><dd>{payment?.customer?.customer_name}</dd></div>
                        <div><dt>Amount</dt><dd>₹{money(payment.amount)}</dd></div>
                    </dl>
                    <p>
                        The receipt, allocations, and original ledger entries will remain for audit.
                        Invoice balances will be restored.
                    </p>
                </div>
                <footer>
                    <button type="button" className="secondary" onClick={onClose} disabled={processing}>
                        Keep Receipt
                    </button>
                    <button type="button" className="danger" onClick={onConfirm} disabled={processing}>
                        {processing ? (
                            <><span className="spinner-border spinner-border-sm"></span> Cancelling...</>
                        ) : (
                            <><i className="bi bi-x-circle"></i> Cancel Receipt</>
                        )}
                    </button>
                </footer>
            </section>
        </div>
    );
}

function cleanQuery(values) {
    return Object.fromEntries(
        Object.entries(values).filter(([, value]) => value !== '' && value !== null && value !== undefined)
    );
}

function money(value) {
    return Number(value ?? 0).toLocaleString('en-IN', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    });
}

function formatDate(value) {
    if (!value) return '—';
    const [year, month, day] = String(value).slice(0, 10).split('-');
    return year && month && day ? `${day}-${month}-${year}` : value;
}

function initials(value) {
    return String(value ?? 'C')
        .trim()
        .split(/\s+/)
        .slice(0, 2)
        .map(word => word[0])
        .join('')
        .toUpperCase();
}

function todayString() {
    const today = new Date();
    const offset = today.getTimezoneOffset();
    return new Date(today.getTime() - offset * 60000).toISOString().slice(0, 10);
}

Index.layout = page => page;
