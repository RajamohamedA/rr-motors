import React, { useEffect, useRef, useState } from 'react';
import {
    Head,
    Link,
    router,
    usePage,
} from '@inertiajs/react';
import BusinessLayout from '@/Layouts/BusinessLayout';
import MasterNavigation
    from '../../Components/Business/MasterNavigation';
import '../../../css/business/sales.css';
import '../../../css/business/sales-register.css';
export default function Index({
    sales,
    filters = {},
    summary = {},
    customers = [],
    mechanics = [],
}) {
    const {
        business,
    } = usePage().props;
    const [
        form,
        setForm,
    ] = useState({
        search:
            filters.search ?? '',
        quick_filter:
            filters.quick_filter ?? 'today',
        customer_id:
            filters.customer_id ?? '',
        mechanic_id:
            filters.mechanic_id ?? '',
        sale_source:
            filters.sale_source ?? '',
        status:
            filters.status ?? '',
        payment_status: filters.payment_status ?? '',
        billing_type: filters.billing_type ?? '',
        date_from:
            filters.date_from ?? '',
        date_to:
            filters.date_to ?? '',
    });
    const rows =
        sales?.data ?? [];
    const updateForm = (
        field,
        value
    ) => {
        setForm(
            previous => ({
                ...previous,
                [field]: value,
            })
        );
    };
    const applyFilters = event => {
        event.preventDefault();
        router.get(
            '/sales',
            cleanObject(form),
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            }
        );
    };
    const selectQuickFilter = value => {
        const next = {
            ...form,
            quick_filter: value,
            date_from: '',
            date_to: '',
        };
        setForm(next);
        router.get(
            '/sales',
            cleanObject(next),
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            }
        );
    };
    const resetFilters = () => {
        const reset = {
            search: '',
            quick_filter: 'today',
            customer_id: '',
            mechanic_id: '',
            sale_source: '',
            status: '',
            payment_status: '',
            billing_type: '',
            date_from: '',
            date_to: '',
        };
        setForm(reset);
        router.get(
            '/sales',
            reset,
            {
                preserveState: true,
                replace: true,
            }
        );
    };
    return (
        <>
            <Head title="Sales" />
            <div className="rr-sales-index-page">
                <MasterNavigation
                    active="sales"
                    title="Sales"
                    subtitle="Invoices, payments and customer balances"
                />
                <section className="rr-sales-index-header">
                    <div className="rr-sales-index-heading">
                        <div className="rr-sales-index-heading-icon">
                            <i className="bi bi-receipt-cutoff"></i>
                        </div>
                        <div>
                            <span className="rr-sales-index-eyebrow">
                                Counter Billing
                            </span>
                            <h1>
                                Sales Invoices
                            </h1>
                            <div className="rr-sales-index-context">
                                <span>
                                    <i className="bi bi-buildings"></i>
                                    {
                                        business?.company_name
                                        ?? 'Company'
                                    }
                                </span>
                                <span>
                                    <i className="bi bi-shop"></i>
                                    {
                                        business?.store_name
                                        ?? 'Branch'
                                    }
                                </span>
                            </div>
                        </div>
                    </div>
                    <Link
                        href="/sales/create"
                        className="rr-sales-new-button"
                    >
                        <i className="bi bi-plus-lg"></i>
                        New Sale
                    </Link>
                </section>
                <section className="rr-sales-summary-grid">
                    <SummaryCard
                        title="Today Sales"
                        value={formatMoney(summary.today_sales)}
                        detail={`${numberValue(summary.today_invoices)} invoices`}
                        icon="bi-currency-rupee"
                        tone="primary"
                    />
                    <SummaryCard
                        title="Yesterday Sales"
                        value={formatMoney(summary.yesterday_sales)}
                        detail={`${numberValue(summary.yesterday_invoices)} invoices`}
                        icon="bi-clock-history"
                        tone="yesterday"
                    />
                    <SummaryCard
                        title="Today Paid"
                        value={formatMoney(summary.today_paid)}
                        detail="Collected today"
                        icon="bi-check-circle"
                        tone="success"
                    />
                    <SummaryCard
                        title="Today Balance"
                        value={formatMoney(summary.today_balance)}
                        detail="Pending collection"
                        icon="bi-wallet2"
                        tone="warning"
                    />
                    <SummaryCard
                        title="Total Receivable"
                        value={formatMoney(summary.receivable)}
                        detail={`${numberValue(summary.sales_count)} total sales`}
                        icon="bi-hourglass-split"
                        tone="danger"
                    />
                    <SummaryCard
                        title="Mechanic Commission"
                        value={formatMoney(summary.mechanic_commission)}
                        detail="Commission generated"
                        icon="bi-tools"
                        tone="neutral"
                    />
                </section>
                <section className="rr-sales-window rr-sales-filter-window">
                    <div className="rr-sales-window-title">
                        <div>
                            <i className="bi bi-funnel"></i>
                            <strong>
                                Search & Filters
                            </strong>
                        </div>
                        <button
                            type="button"
                            className="rr-sales-clear-button"
                            onClick={resetFilters}
                        >
                            <i className="bi bi-x-circle"></i>
                            Clear
                        </button>
                    </div>
                    <form
                        className="rr-sales-filter-body"
                        onSubmit={applyFilters}
                    >
                        <div className="rr-sales-search-field">
                            <i className="bi bi-search"></i>
                            <input
                                type="search"
                                value={form.search}
                                placeholder="Invoice number, customer or mechanic..."
                                onChange={event =>
                                    updateForm(
                                        'search',
                                        event.target.value
                                    )
                                }
                            />
                        </div>
                        <PartySearch label="Customer" type="customer" value={form.customer_id} initialOptions={customers} onChange={value => updateForm('customer_id', value)} />
                        <FilterSelect
                            label="Sale Source"
                            value={form.sale_source}
                            onChange={value =>
                                updateForm(
                                    'sale_source',
                                    value
                                )
                            }
                        >
                            <option value="">
                                All Sources
                            </option>
                            <option value="customer">
                                Customer Only
                            </option>
                            <option value="mechanic">
                                Mechanic Referral
                            </option>
                        </FilterSelect>
                        <PartySearch label="Mechanic" type="mechanic" value={form.mechanic_id} initialOptions={mechanics} onChange={value => updateForm('mechanic_id', value)} />
                        <FilterSelect
                            label="Status"
                            value={form.status}
                            onChange={value =>
                                updateForm(
                                    'status',
                                    value
                                )
                            }
                        >
                            <option value="">
                                All Statuses
                            </option>
                            <option value="Completed">
                                Completed
                            </option>
                            <option value="Cancelled">
                                Cancelled
                            </option>
                        </FilterSelect>
                        <FilterSelect label="Payment" value={form.payment_status} onChange={value => updateForm('payment_status', value)}>
                            <option value="">All Payments</option>
                            <option value="Paid">Paid</option>
                            <option value="Unpaid">Unpaid</option>
                            <option value="Partially Paid">Partially Paid</option>
                        </FilterSelect>
                        <FilterSelect label="Bill Type" value={form.billing_type} onChange={value => updateForm('billing_type', value)}>
                            <option value="">All Bills</option>
                            <option value="gst">GST Bill</option>
                            <option value="non_gst">Cash / Without GST</option>
                        </FilterSelect>
                        <DateField
                            label="From Date"
                            value={form.date_from}
                            onChange={value =>
                                updateForm(
                                    'date_from',
                                    value
                                )
                            }
                        />
                        <DateField
                            label="To Date"
                            value={form.date_to}
                            onChange={value =>
                                updateForm(
                                    'date_to',
                                    value
                                )
                            }
                        />
                        <button
                            type="submit"
                            className="rr-sales-apply-button"
                        >
                            <i className="bi bi-search"></i>
                            Apply
                        </button>
                    </form>
                    <div className="rr-sales-quick-filters">
                        {[
                            ['today', 'Today'],
                            ['yesterday', 'Yesterday'],
                            ['this_week', 'This Week'],
                            ['this_month', 'This Month'],
                            ['all', 'All Sales'],
                        ].map(([
                            value,
                            label,
                        ]) => (
                            <button
                                key={value}
                                type="button"
                                className={
                                    form.quick_filter === value
                                        ? 'active'
                                        : ''
                                }
                                onClick={() =>
                                    selectQuickFilter(value)
                                }
                            >
                                {label}
                            </button>
                        ))}
                    </div>
                </section>
                <section className="rr-sales-window">
                    <div className="rr-sales-window-title">
                        <div>
                            <i className="bi bi-table"></i>
                            <strong>
                                Sales Register
                            </strong>
                        </div>
                        <span className="rr-sales-record-count">
                            {numberValue(sales?.total)} records
                        </span>
                    </div>
                    <div className="rr-sales-table-wrap">
                        <table className="rr-sales-table">
                            <thead>
                                <tr>
                                    <th>
                                        Invoice
                                    </th>
                                    <th>
                                        Date
                                    </th>
                                    <th>
                                        Customer
                                    </th>
                                    <th>
                                        Source
                                    </th>
                                    <th className="number">
                                        Total
                                    </th>
                                    <th className="number">
                                        Paid
                                    </th>
                                    <th className="number">
                                        Balance
                                    </th>
                                    <th>
                                        Payment
                                    </th>
                                    <th>
                                        Status
                                    </th>
                                    <th className="actions">
                                        Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                {rows.length > 0
                                    ? rows.map(sale => (
                                        <SaleRow
                                            key={sale.id}
                                            sale={sale}
                                        />
                                    ))
                                    : (
                                        <tr>
                                            <td colSpan="10">
                                                <div className="rr-sales-empty-state">
                                                    <i className="bi bi-receipt"></i>
                                                    <strong>
                                                        No sales found
                                                    </strong>
                                                    <span>
                                                        Change the filters or create a new sale.
                                                    </span>
                                                    <Link
                                                        href="/sales/create"
                                                        className="rr-sales-new-button"
                                                    >
                                                        <i className="bi bi-plus-lg"></i>
                                                        New Sale
                                                    </Link>
                                                </div>
                                            </td>
                                        </tr>
                                    )}
                            </tbody>
                        </table>
                    </div>
                    <Pagination
                        links={sales?.links ?? []}
                        from={sales?.from}
                        to={sales?.to}
                        total={sales?.total}
                    />
                </section>
            </div>
        </>
    );
}
function SaleRow({ sale }) {
    const mechanicSale =
        sale.sale_source === 'mechanic';
    const openSale = event => {
        if (
            event.target.closest(
                'a, button, input, select, textarea'
            )
        ) {
            return;
        }
        router.visit(
            `/sales/${sale.id}`
        );
    };
    const openSaleFromKeyboard = event => {
        if (
            event.key === 'Enter'
            || event.key === ' '
        ) {
            event.preventDefault();
            router.visit(
                `/sales/${sale.id}`
            );
        }
    };
    return (
        <tr
            className="rr-sales-clickable-row"
            role="link"
            tabIndex="0"
            title="Click to view sale details"
            onClick={openSale}
            onKeyDown={openSaleFromKeyboard}
        >
            <td>
                <Link
                    href={`/sales/${sale.id}`}
                    className="rr-sales-invoice-link"
                >
                    <i className="bi bi-receipt-cutoff"></i>
                    {sale.invoice_no}
                </Link>
            </td>
            <td className="nowrap">
                {formatDate(sale.invoice_date)}
            </td>
            <td>
                <div className="rr-sales-party-cell">
                    <strong>
                        {
                            sale.customer?.customer_name
                            ?? 'Customer'
                        }
                    </strong>
                </div>
            </td>
            <td>
                <span className={`rr-sales-source ${mechanicSale ? 'mechanic' : 'customer'}`}>
                    <i className={`bi ${mechanicSale ? 'bi-tools' : 'bi-person'}`}></i>
                    {
                        mechanicSale
                            ? sale.mechanic?.mechanic_name ?? 'Mechanic'
                            : 'Direct'
                    }
                </span>
            </td>
            <td className="number amount">
                {formatMoney(sale.grand_total)}
            </td>
            <td className="number paid">
                {formatMoney(sale.paid_amount)}
            </td>
            <td className="number balance">
                {formatMoney(sale.balance_amount)}
            </td>
            <td>
                <Badge
                    value={sale.payment_status ?? 'Unpaid'}
                    type="payment"
                />
            </td>
            <td>
                <Badge
                    value={sale.status ?? 'Completed'}
                    type="status"
                />
            </td>
            <td>
                <div className="rr-sales-row-actions">
                    <Link
                        href={`/sales/${sale.id}`}
                        title="View sale"
                        className="view"
                    >
                        <i className="bi bi-eye"></i>
                    </Link>
                    <Link
                        href={`/sales/${sale.id}/edit`}
                        title="Edit sale"
                        className="edit"
                    >
                        <i className="bi bi-pencil-square"></i>
                    </Link>
                    <a
                        href={`/sales/${sale.id}/print`}
                        target="_blank"
                        rel="noreferrer"
                        title="Print invoice"
                        className="print"
                    >
                        <i className="bi bi-printer"></i>
                    </a>
                </div>
            </td>
        </tr>
    );
}
function SummaryCard({
    title,
    value,
    detail,
    icon,
    tone,
}) {
    return (
        <div className={`rr-sales-summary-card ${tone}`}>
            <div className="rr-sales-summary-icon">
                <i className={`bi ${icon}`}></i>
            </div>
            <div>
                <span>
                    {title}
                </span>
                <strong>
                    {value}
                </strong>
                <small>
                    {detail}
                </small>
            </div>
        </div>
    );
}
function FilterSelect({
    label,
    value,
    onChange,
    children,
}) {
    return (
        <label className="rr-sales-filter-field">
            <span>
                {label}
            </span>
            <select
                value={value}
                onChange={event =>
                    onChange(event.target.value)
                }
            >
                {children}
            </select>
        </label>
    );
}
function DateField({
    label,
    value,
    onChange,
}) {
    const openDatePicker = event => {
        try {
            event.currentTarget.showPicker?.();
        } catch {
            event.currentTarget.focus();
        }
    };
    return (
        <label className="rr-sales-filter-field">
            <span>
                {label}
            </span>
            <div className="rr-sales-date-control">
                <i className="bi bi-calendar3"></i>
                <input
                    type="date"
                    value={value}
                    onClick={openDatePicker}
                    onChange={event =>
                        onChange(event.target.value)
                    }
                />
            </div>
        </label>
    );
}
function Badge({
    value,
    type,
}) {
    return (
        <span className={`rr-sales-badge ${type} ${slug(value)}`}>
            {value}
        </span>
    );
}
function Pagination({
    links,
    from,
    to,
    total,
}) {
    if (!links || links.length <= 3) {
        return null;
    }
    return (
        <div className="rr-sales-pagination-bar">
            <span>
                Showing {from ?? 0}–{to ?? 0} of {total ?? 0}
            </span>
            <div>
                {links.map((link, index) => (
                    link.url
                        ? (
                            <Link
                                key={`${link.label}-${index}`}
                                href={link.url}
                                preserveScroll
                                className={link.active ? 'active' : ''}
                                dangerouslySetInnerHTML={{
                                    __html: link.label,
                                }}
                            />
                        )
                        : (
                            <span
                                key={`${link.label}-${index}`}
                                className="disabled"
                                dangerouslySetInnerHTML={{
                                    __html: link.label,
                                }}
                            />
                        )
                ))}
            </div>
        </div>
    );
}
function cleanObject(values) {
    return Object.fromEntries(
        Object.entries(values).filter(([
            ,
            value,
        ]) => value !== '' && value !== null)
    );
}
function slug(value) {
    return String(value ?? '')
        .toLowerCase()
        .replaceAll(' ', '-');
}
function numberValue(value) {
    const number =
        Number(value ?? 0);
    return Number.isFinite(number)
        ? number
        : 0;
}
function formatMoney(value) {
    return new Intl.NumberFormat(
        'en-IN',
        {
            style: 'currency',
            currency: 'INR',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }
    ).format(
        numberValue(value)
    );
}
function formatDate(value) {
    if (!value) {
        return '—';
    }
    const raw =
        String(value);
    const date =
        raw.includes('T')
            ? new Date(raw)
            : new Date(`${raw}T00:00:00`);
    if (Number.isNaN(date.getTime())) {
        return '—';
    }
    return new Intl.DateTimeFormat(
        'en-IN',
        {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
        }
    ).format(date);
}
Index.layout =
    page => (
        <BusinessLayout>
            {page}
        </BusinessLayout>
    );

function PartySearch({ label, type, value, initialOptions = [], onChange }) {
    const nameKey = type === 'customer' ? 'customer_name' : 'mechanic_name';
    const [query, setQuery] = useState('');
    const [options, setOptions] = useState(initialOptions);
    const [selected, setSelected] = useState(initialOptions.find(item => String(item.id) === String(value)) ?? null);
    const [open, setOpen] = useState(false);
    const [loading, setLoading] = useState(false);
    const [error, setError] = useState('');
    const [highlight, setHighlight] = useState(0);
    const box = useRef(null);
    const version = useRef(0);
    useEffect(() => {
        if (!value) { setSelected(null); setQuery(''); }
        else {
            const item = initialOptions.find(item => String(item.id) === String(value));
            if (item) setSelected(item);
        }
    }, [value, initialOptions]);
    useEffect(() => {
        const close = event => { if (!box.current?.contains(event.target)) setOpen(false); };
        document.addEventListener('mousedown', close);
        return () => document.removeEventListener('mousedown', close);
    }, []);
    useEffect(() => {
        if (!open) return;
        const controller = new AbortController();
        const requestVersion = ++version.current;
        setLoading(true); setError(''); setOptions([]); setHighlight(0);
        const timer = setTimeout(async () => {
            try {
                const params = new URLSearchParams({ type, search: query.trim() });
                const response = await fetch(`/sales/register/parties?${params}`, { signal: controller.signal, credentials: 'same-origin', headers: { Accept: 'application/json' } });
                if (!response.ok) throw new Error('Unable to search. Please try again.');
                const result = await response.json();
                if (!controller.signal.aborted && requestVersion === version.current) setOptions(result.items ?? []);
            } catch (err) {
                if (!controller.signal.aborted && requestVersion === version.current) setError(err.message);
            } finally {
                if (!controller.signal.aborted && requestVersion === version.current) setLoading(false);
            }
        }, 250);
        return () => { clearTimeout(timer); controller.abort(); };
    }, [query, open, type]);
    const choose = item => { setSelected(item); onChange(String(item.id)); setQuery(''); setOpen(false); };
    return <div className="rr-register-party" ref={box}>
        <label htmlFor={`register-${type}`}>{label}</label>
        <div className="rr-register-party-input">
            <i className="bi bi-search" aria-hidden="true" />
            <input id={`register-${type}`} role="combobox" aria-expanded={open} aria-controls={open ? `register-${type}-options` : undefined}
                aria-activedescendant={open && options.length && !loading ? `register-${type}-${highlight}` : undefined}
                value={open ? query : (selected?.[nameKey] ?? '')} placeholder={`All ${label.toLowerCase()}s · Search name / mobile`}
                onFocus={() => setOpen(true)} onChange={event => { version.current += 1; setOptions([]); setQuery(event.target.value); setLoading(true); setOpen(true); }}
                onKeyDown={event => {
                    if (event.key === 'Escape') { event.preventDefault(); setOpen(false); }
                    if (event.key === 'ArrowDown' || event.key === 'ArrowUp') { event.preventDefault(); setOpen(true); setHighlight(previous => options.length ? (previous + (event.key === 'ArrowDown' ? 1 : -1) + options.length) % options.length : 0); }
                    if (event.key === 'Enter' && open) { event.preventDefault(); if (!loading && options[highlight]) choose(options[highlight]); }
                }} />
            {value && <button type="button" aria-label={`Clear ${label}`} onClick={() => { onChange(''); setSelected(null); setQuery(''); setOpen(false); }}><i className="bi bi-x-lg" /></button>}
        </div>
        {open && <div className="rr-register-party-results" id={`register-${type}-options`} role="listbox" aria-label={`${label} results`}>
            {loading ? <div className="rr-register-search-note" role="status"><span className="spinner-border spinner-border-sm" /> Searching…</div>
                : error ? <div className="rr-register-search-note" role="alert">{error}</div>
                    : options.length ? options.map((item, index) => <button type="button" role="option" aria-selected={highlight === index} id={`register-${type}-${index}`} key={item.id}
                        className={highlight === index ? 'active' : ''} onMouseDown={event => event.preventDefault()} onClick={() => choose(item)}>
                        <strong>{item[nameKey]}</strong><small>{item.mobile || item[`${type}_code`] || ''}</small>
                    </button>) : <div className="rr-register-search-note">No matches found.</div>}
            <div className="rr-register-search-note">Type to search more · ↑ ↓ choose · Enter select</div>
        </div>}
    </div>;
}
