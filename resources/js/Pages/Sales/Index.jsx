import React, { useState } from 'react';

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


                        <FilterSelect
                            label="Customer"
                            value={form.customer_id}
                            onChange={value =>
                                updateForm(
                                    'customer_id',
                                    value
                                )
                            }
                        >
                            <option value="">
                                All Customers
                            </option>

                            {customers.map(customer => (
                                <option
                                    key={customer.id}
                                    value={customer.id}
                                >
                                    {customer.customer_name}
                                </option>
                            ))}
                        </FilterSelect>


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


                        <FilterSelect
                            label="Mechanic"
                            value={form.mechanic_id}
                            onChange={value =>
                                updateForm(
                                    'mechanic_id',
                                    value
                                )
                            }
                        >
                            <option value="">
                                All Mechanics
                            </option>

                            {mechanics.map(mechanic => (
                                <option
                                    key={mechanic.id}
                                    value={mechanic.id}
                                >
                                    {mechanic.mechanic_name}
                                </option>
                            ))}
                        </FilterSelect>


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
