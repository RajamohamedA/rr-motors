import {
    Head,
    Link,
    router,
} from '@inertiajs/react';

import {
    useMemo,
    useState,
} from 'react';

import BusinessLayout
    from '../../Layouts/BusinessLayout';

import MasterNavigation
    from '../../Components/Business/MasterNavigation';

import '../../../css/business/supplier-ledgers.css';


export default function Index({
    filters = {},
    suppliers = [],
    selected_supplier: selectedSupplier,
    entries = {},
    summary = {},
    transaction_types: transactionTypes = [],
}) {

    const [
        form,
        setForm,
    ] = useState({
        supplier_id:
            filters?.supplier_id
            ?? '',

        from_date:
            filters?.from_date
            ?? '',

        to_date:
            filters?.to_date
            ?? '',

        transaction_type:
            filters?.transaction_type
            ?? '',

        search:
            filters?.search
            ?? '',

        per_page:
            filters?.per_page
            ?? 50,
    });


    const [
        supplierSearch,
        setSupplierSearch,
    ] = useState('');

    const [
        filtersCollapsed,
        setFiltersCollapsed,
    ] = useState(false);


    const visibleSuppliers =
        useMemo(
            () => {

                const term =
                    supplierSearch
                        .trim()
                        .toLowerCase();


                if (!term) {
                    return suppliers;
                }


                return suppliers.filter(
                    supplier => [
                        supplier?.supplier_name,
                        supplier?.supplier_code,
                        supplier?.phone,
                    ]
                        .filter(Boolean)
                        .some(
                            value =>
                                String(value)
                                    .toLowerCase()
                                    .includes(term)
                        )
                );
            },
            [
                suppliers,
                supplierSearch,
            ]
        );


    const rows =
        entries?.data
        ?? [];


    const update =
        (
            field,
            value
        ) => {

            setForm(
                current => ({
                    ...current,
                    [field]: value,
                })
            );
        };


    const submit =
        event => {

            event.preventDefault();

            router.get(
                '/supplier-ledger',
                cleanQuery(form),
                {
                    preserveState: true,
                    preserveScroll: true,
                    replace: true,
                }
            );
        };


    const clearFilters =
        () => {

            const cleared = {
                supplier_id: '',
                from_date: '',
                to_date: '',
                transaction_type: '',
                search: '',
                per_page: 50,
            };

            setForm(cleared);
            setSupplierSearch('');

            router.get(
                '/supplier-ledger',
                {},
                {
                    preserveState: false,
                    replace: true,
                }
            );
        };


    const applyDatePreset =
        preset => {

            const range = datePreset(preset);

            setForm(
                current => ({
                    ...current,
                    from_date: range.from,
                    to_date: range.to,
                })
            );
        };


    const reportQuery =
        cleanQuery(form);


    return (

        <BusinessLayout>

            <Head title="Supplier Ledger" />


            <div className="rr-sl-page">

                <MasterNavigation
                    active="supplier-ledger"
                    title="Supplier Ledger"
                    subtitle="Purchases, returns, payments and running supplier balances"
                />


                <section className="rr-sl-toolbar">

                    <div>

                        <span className="rr-sl-kicker">
                            ACCOUNTS
                        </span>

                        <h1>
                            Supplier Ledger
                        </h1>

                        <p>
                            Branch-isolated supplier payable register
                        </p>

                    </div>


                    <div className="rr-sl-toolbar-actions">

                        {
                            selectedSupplier && (
                                <>
                                    <a
                                        href={buildUrl('/supplier-ledger/print', reportQuery)}
                                        target="_blank"
                                        rel="noreferrer"
                                        className="rr-sl-btn print"
                                        title="Open printable supplier ledger"
                                    >
                                        <i className="bi bi-printer"></i>
                                        Print
                                    </a>

                                    <a
                                        href={buildUrl('/supplier-ledger/pdf', reportQuery)}
                                        className="rr-sl-btn pdf"
                                        title="Download supplier ledger PDF"
                                    >
                                        <i className="bi bi-file-earmark-pdf"></i>
                                        PDF
                                    </a>

                                    <a
                                        href={buildUrl('/supplier-ledger/excel', reportQuery)}
                                        className="rr-sl-btn excel"
                                        title="Download Excel-compatible CSV"
                                    >
                                        <i className="bi bi-file-earmark-spreadsheet"></i>
                                        Excel
                                    </a>
                                </>
                            )
                        }

                        {
                            selectedSupplier && (

                                <Link
                                    href={
                                        `/suppliers/${selectedSupplier.id}`
                                    }
                                    className="rr-sl-btn secondary"
                                >
                                    <i className="bi bi-person-vcard"></i>
                                    Supplier Account
                                </Link>

                            )
                        }

                    </div>

                </section>


                <form
                    className={`rr-sl-filter-panel ${filtersCollapsed ? 'collapsed' : ''}`}
                    onSubmit={submit}
                >

                    <div className="rr-sl-filter-heading">

                        <div>
                            <i className="bi bi-funnel"></i>

                            <div>
                                <strong>Search & Filters</strong>
                                <span>Select a supplier to display its ledger</span>
                            </div>
                        </div>

                        <div className="rr-sl-filter-heading-actions">
                            <button
                                type="button"
                                className="rr-sl-clear"
                                onClick={clearFilters}
                            >
                                <i className="bi bi-arrow-counterclockwise"></i>
                                Reset
                            </button>

                            <button
                                type="button"
                                className="rr-sl-collapse-button"
                                onClick={
                                    () =>
                                        setFiltersCollapsed(
                                            current => !current
                                        )
                                }
                                title={filtersCollapsed ? 'Expand filters' : 'Collapse filters'}
                                aria-expanded={!filtersCollapsed}
                            >
                                <i className={`bi ${filtersCollapsed ? 'bi-chevron-down' : 'bi-chevron-up'}`}></i>
                            </button>
                        </div>

                    </div>


                    <div className="rr-sl-filter-body">

                        <div className="rr-sl-date-presets">
                            <span>Quick period</span>

                            <button type="button" onClick={() => applyDatePreset('today')}>
                                Today
                            </button>

                            <button type="button" onClick={() => applyDatePreset('month')}>
                                This Month
                            </button>

                            <button type="button" onClick={() => applyDatePreset('previous_month')}>
                                Previous Month
                            </button>

                            <button type="button" onClick={() => applyDatePreset('financial_year')}>
                                Financial Year
                            </button>
                        </div>


                        <div className="rr-sl-filter-grid">

                            <label className="rr-sl-field supplier">
                                <span>Find Supplier</span>

                                <div className="rr-sl-input-icon">
                                    <i className="bi bi-search"></i>

                                    <input
                                        type="search"
                                        value={supplierSearch}
                                        onChange={
                                            event =>
                                                setSupplierSearch(
                                                    event.target.value
                                                )
                                        }
                                        placeholder="Name, code or phone"
                                        autoComplete="off"
                                    />

                                    {
                                        supplierSearch && (
                                            <button
                                                type="button"
                                                onClick={
                                                    () =>
                                                        setSupplierSearch('')
                                                }
                                                title="Clear supplier search"
                                            >
                                                <i className="bi bi-x-lg"></i>
                                            </button>
                                        )
                                    }
                                </div>
                            </label>


                            <label className="rr-sl-field supplier-select">
                                <span>Supplier</span>

                                <select
                                    value={form.supplier_id}
                                    onChange={
                                        event =>
                                            update(
                                                'supplier_id',
                                                event.target.value
                                            )
                                    }
                                >
                                    <option value="">
                                        Select supplier
                                    </option>

                                    {
                                        visibleSuppliers.map(
                                            supplier => (
                                                <option
                                                    key={supplier.id}
                                                    value={supplier.id}
                                                >
                                                    {supplier.supplier_name}
                                                    {supplier.supplier_code
                                                        ? ` — ${supplier.supplier_code}`
                                                        : ''}
                                                </option>
                                            )
                                        )
                                    }
                                </select>

                                {
                                    supplierSearch && (
                                        <small className="rr-sl-field-help">
                                            {visibleSuppliers.length} matching supplier{visibleSuppliers.length === 1 ? '' : 's'}
                                        </small>
                                    )
                                }
                            </label>


                            <FilterInput
                                label="From Date"
                                type="date"
                                value={form.from_date}
                                onChange={
                                    value =>
                                        update('from_date', value)
                                }
                            />

                            <FilterInput
                                label="To Date"
                                type="date"
                                value={form.to_date}
                                onChange={
                                    value =>
                                        update('to_date', value)
                                }
                            />


                            <label className="rr-sl-field">
                                <span>Transaction</span>

                                <select
                                    value={form.transaction_type}
                                    onChange={
                                        event =>
                                            update(
                                                'transaction_type',
                                                event.target.value
                                            )
                                    }
                                >
                                    <option value="">All transactions</option>

                                    {
                                        transactionTypes.map(
                                            type => (
                                                <option
                                                    key={type.value}
                                                    value={type.value}
                                                >
                                                    {type.label}
                                                </option>
                                            )
                                        )
                                    }
                                </select>
                            </label>


                            <label className="rr-sl-field reference">
                                <span>Reference / Remarks</span>

                                <input
                                    type="search"
                                    value={form.search}
                                    onChange={
                                        event =>
                                            update(
                                                'search',
                                                event.target.value
                                            )
                                    }
                                    placeholder="Search voucher number"
                                />
                            </label>


                            <label className="rr-sl-field per-page">
                                <span>Rows</span>

                                <select
                                    value={form.per_page}
                                    onChange={
                                        event =>
                                            update(
                                                'per_page',
                                                event.target.value
                                            )
                                    }
                                >
                                    <option value="25">25</option>
                                    <option value="50">50</option>
                                    <option value="100">100</option>
                                </select>
                            </label>


                            <button
                                type="submit"
                                className="rr-sl-btn primary apply"
                                disabled={!form.supplier_id}
                            >
                                <i className="bi bi-search"></i>
                                View Ledger
                            </button>

                        </div>

                    </div>

                </form>


                {
                    selectedSupplier
                        ? (
                            <>
                                <SupplierBanner
                                    supplier={selectedSupplier}
                                    summary={summary}
                                    filters={filters}
                                />

                                <SummaryCards
                                    summary={summary}
                                />

                                <LedgerTable
                                    entries={entries}
                                    rows={rows}
                                />
                            </>
                        )
                        : (
                            <EmptySelection />
                        )
                }

            </div>

        </BusinessLayout>
    );
}


function FilterInput({
    label,
    type,
    value,
    onChange,
}) {
    return (
        <label className="rr-sl-field">
            <span>{label}</span>
            <input
                type={type}
                value={value}
                max={type === 'date' ? todayString() : undefined}
                onChange={
                    event =>
                        onChange(event.target.value)
                }
            />
        </label>
    );
}


function SupplierBanner({
    supplier,
    summary,
    filters,
}) {
    return (
        <section className="rr-sl-supplier-banner">
            <div className="rr-sl-avatar">
                {initials(supplier?.supplier_name)}
            </div>

            <div>
                <span>{supplier?.supplier_code ?? 'SUPPLIER'}</span>
                <strong>{supplier?.supplier_name}</strong>
                <small>{supplier?.phone ?? 'No phone number'}</small>
            </div>

            <div className="rr-sl-supplier-period">
                <span>Statement Period</span>
                <strong>
                    {formatShortDate(filters?.from_date)}
                    <i className="bi bi-arrow-right"></i>
                    {formatShortDate(filters?.to_date)}
                </strong>
                <small>
                    Current balance: {summary?.current_balance_label ?? '0.00'}
                </small>
            </div>
        </section>
    );
}


function SummaryCards({
    summary,
}) {
    const cards = [
        ['Opening Balance', summary?.opening_balance_label, 'bi-box-arrow-in-right', 'opening'],
        ['Period Debit', `₹${money(summary?.total_debit)}`, 'bi-arrow-up-right', 'debit'],
        ['Period Credit', `₹${money(summary?.total_credit)}`, 'bi-arrow-down-left', 'credit'],
        ['Closing Balance', summary?.closing_balance_label, 'bi-calculator', 'closing'],
        ['Current Balance', summary?.current_balance_label, 'bi-wallet2', 'current'],
    ];

    return (
        <section className="rr-sl-summary">
            {
                cards.map(
                    ([label, value, icon, tone]) => (
                        <div
                            key={label}
                            className={`rr-sl-stat ${tone}`}
                        >
                            <div>
                                <i className={`bi ${icon}`}></i>
                            </div>

                            <span>{label}</span>
                            <strong>{value ?? '0.00'}</strong>
                        </div>
                    )
                )
            }
        </section>
    );
}


function LedgerTable({
    entries,
    rows,
}) {
    return (
        <section className="rr-sl-ledger-panel">
            <header>
                <div>
                    <i className="bi bi-journal-text"></i>
                    <div>
                        <strong>Ledger Transactions</strong>
                        <span>{entries?.total ?? 0} records</span>
                    </div>
                </div>

                <div className="rr-sl-table-legend">
                    <span className="debit"><i></i> Debit</span>
                    <span className="credit"><i></i> Credit</span>
                    <span className="reversal"><i></i> Reversal</span>
                </div>
            </header>

            <div className="rr-sl-table-wrap">
                <table className="rr-sl-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Transaction</th>
                            <th>Reference</th>
                            <th>Remarks</th>
                            <th className="number">Debit</th>
                            <th className="number">Credit</th>
                            <th className="number">Balance</th>
                        </tr>
                    </thead>

                    <tbody>
                        {
                            rows.length
                                ? rows.map(
                                    entry => (
                                        <tr
                                            key={entry.id}
                                            className={
                                                entry.is_reversal
                                                    ? 'reversal'
                                                    : ''
                                            }
                                        >
                                            <td>{formatDate(entry.transaction_date)}</td>
                                            <td>
                                                <span className={`rr-sl-type ${entry.transaction_type}`}>
                                                    {entry.transaction_label}
                                                </span>

                                                {
                                                    entry.is_reversal && (
                                                        <span className="rr-sl-reversal-badge">
                                                            Reversed
                                                        </span>
                                                    )
                                                }
                                            </td>
                                            <td>
                                                {
                                                    entry.reference_url
                                                        ? (
                                                            <Link
                                                                href={entry.reference_url}
                                                                className="rr-sl-reference"
                                                            >
                                                                {entry.reference_no ?? `#${entry.reference_id}`}
                                                            </Link>
                                                        )
                                                        : (entry.reference_no ?? '—')
                                                }
                                            </td>
                                            <td className="remarks">{entry.remarks ?? '—'}</td>
                                            <td className="number debit">
                                                {Number(entry.debit) > 0 ? `₹${money(entry.debit)}` : '—'}
                                            </td>
                                            <td className="number credit">
                                                {Number(entry.credit) > 0 ? `₹${money(entry.credit)}` : '—'}
                                            </td>
                                            <td className="number balance">
                                                ₹{money(Math.abs(Number(entry.running_balance ?? 0)))}
                                                <small>
                                                    {Number(entry.running_balance) > 0
                                                        ? 'Cr'
                                                        : Number(entry.running_balance) < 0
                                                            ? 'Dr'
                                                            : ''}
                                                </small>
                                            </td>
                                        </tr>
                                    )
                                )
                                : (
                                    <tr>
                                        <td colSpan="7">
                                            <div className="rr-sl-empty-table">
                                                <i className="bi bi-inbox"></i>
                                                <strong>No ledger entries found</strong>
                                                <span>Change the selected date or transaction filters.</span>
                                            </div>
                                        </td>
                                    </tr>
                                )
                        }
                    </tbody>
                </table>
            </div>

            <Pagination paginator={entries} />
        </section>
    );
}


function Pagination({
    paginator,
}) {
    if (Number(paginator?.last_page ?? 1) <= 1) {
        return null;
    }

    return (
        <footer className="rr-sl-pagination">
            <span>
                Showing {paginator?.from ?? 0}–{paginator?.to ?? 0} of {paginator?.total ?? 0}
            </span>

            <div>
                {
                    (paginator?.links ?? []).map(
                        (link, index) =>
                            link.url
                                ? (
                                    <Link
                                        key={index}
                                        href={link.url}
                                        preserveScroll
                                        preserveState
                                        className={`rr-sl-page-link ${link.active ? 'active' : ''}`}
                                        dangerouslySetInnerHTML={{
                                            __html: link.label,
                                        }}
                                    />
                                )
                                : (
                                    <span
                                        key={index}
                                        className="rr-sl-page-link disabled"
                                        dangerouslySetInnerHTML={{
                                            __html: link.label,
                                        }}
                                    />
                                )
                    )
                }
            </div>
        </footer>
    );
}


function EmptySelection() {
    return (
        <section className="rr-sl-empty-selection">
            <div>
                <i className="bi bi-person-check"></i>
            </div>
            <strong>Select a supplier</strong>
            <span>Choose a supplier and click View Ledger to display transactions and balances.</span>
        </section>
    );
}


function cleanQuery(values) {
    return Object.fromEntries(
        Object.entries(values).filter(
            ([, value]) =>
                value !== ''
                && value !== null
                && value !== undefined
        )
    );
}


function buildUrl(path, query) {
    const params = new URLSearchParams();

    Object.entries(query ?? {}).forEach(
        ([key, value]) =>
            params.set(key, String(value))
    );

    const queryString = params.toString();

    return queryString
        ? `${path}?${queryString}`
        : path;
}


function datePreset(preset) {
    const today = new Date();
    let from = new Date(today);
    let to = new Date(today);

    if (preset === 'month') {
        from = new Date(today.getFullYear(), today.getMonth(), 1);
    }

    if (preset === 'previous_month') {
        from = new Date(today.getFullYear(), today.getMonth() - 1, 1);
        to = new Date(today.getFullYear(), today.getMonth(), 0);
    }

    if (preset === 'financial_year') {
        const startYear = today.getMonth() >= 3
            ? today.getFullYear()
            : today.getFullYear() - 1;

        from = new Date(startYear, 3, 1);
        to = new Date(startYear + 1, 2, 31);

        if (to > today) {
            to = new Date(today);
        }
    }

    return {
        from: localDateString(from),
        to: localDateString(to),
    };
}


function localDateString(date) {
    const offset = date.getTimezoneOffset();
    const localDate = new Date(date.getTime() - offset * 60000);

    return localDate.toISOString().slice(0, 10);
}


function todayString() {
    return localDateString(new Date());
}


function formatShortDate(value) {
    if (!value) {
        return '—';
    }

    const [year, month, day] = String(value).split('-');

    if (!year || !month || !day) {
        return value;
    }

    return `${day}-${month}-${year}`;
}


function money(value) {
    return Number(value ?? 0).toLocaleString(
        'en-IN',
        {
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        }
    );
}


function formatDate(value) {
    if (!value) {
        return '—';
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) {
        return value;
    }

    return date.toLocaleDateString(
        'en-IN',
        {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
        }
    );
}


function initials(value) {
    return String(value ?? 'S')
        .trim()
        .split(/\s+/)
        .slice(0, 2)
        .map(word => word[0])
        .join('')
        .toUpperCase();
}


Index.layout =
    page => page;
