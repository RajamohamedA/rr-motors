import React, { useMemo, useState } from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';

import BusinessLayout from '../../Layouts/BusinessLayout';

import '../../../css/business/dashboard.css';

export default function Dashboard({
    context = {},
    performance = {},
    branchSummary = {},
    userSummary = {},
    branches = [],
    recentSales = [],
    recentPurchases = [],
}) {
    const { auth = {}, business = {} } = usePage().props;
    const [failedLogo, setFailedLogo] = useState(null);
    const [branchPage, setBranchPage] = useState(1);
    const [switchingBranch, setSwitchingBranch] = useState(null);

    const company = context?.company ?? {};
    const branch = context?.branch ?? {};
    const isSuperAdminContext = Boolean(context?.is_super_admin_context);

    const roleNames = useMemo(() => (
        Array.isArray(auth?.user?.roles) ? auth.user.roles : []
    ).map((role) => (
        typeof role === 'string' ? role : role?.name
    )).filter(Boolean).map((role) => (
        String(role).toLowerCase().replaceAll('_', ' ').trim()
    )), [auth?.user?.roles]);

    const isCompanyAdmin = Boolean(
        auth?.user?.is_super_user
        || roleNames.includes('company admin')
        || roleNames.includes('company administrator')
        || roleNames.includes('company owner')
    );

    const permissions = (auth?.user?.permissions ?? []).map((permission) =>
        typeof permission === 'string' ? permission : permission?.name,
    );
    const can = (permission) => isCompanyAdmin || permissions.includes(permission);
    const rawLogo = business.company_logo ?? company.logo ?? '';
    const logoPath = String(rawLogo).replace(/^\/+/, '');
    const logoUrl = /^https?:\/\//i.test(rawLogo)
        ? rawLogo
        : logoPath ? (logoPath.startsWith('storage/') ? `/${logoPath}` : `/storage/${logoPath}`) : '';
    const logoKey = `${company.id ?? business.company_id}:${logoUrl}`;

    // Branch details come from the company dashboard; access comes only
    // from the server-authorized active branch list.
    const authorizedBranches = Array.isArray(business.available_branches)
        ? business.available_branches : [];
    const authorizedIds = new Set(authorizedBranches.map((item) => String(item.id)));
    const branchRows = Array.isArray(branches) && branches.length
        ? branches : authorizedBranches;
    const availableBranches = branchRows.map((item) => {
        const hasAccess = Boolean(auth?.user?.is_super_user)
            || authorizedIds.has(String(item.id));
        const active = item.status === undefined
            ? authorizedIds.has(String(item.id))
            : [true, 1, '1'].includes(item.status);
        return {
            ...item,
            name: item.name ?? item.store_name,
            code: item.code ?? item.store_code,
            status: active,
            hasAccess,
            is_current: hasAccess && Number(item.id) === Number(branch.id ?? business.store_id),
        };
    });
    const accessibleCount = availableBranches.filter((item) => item.hasAccess && item.status).length;
    const noAccessCount = availableBranches.filter((item) => !item.hasAccess).length;
    const pageCount = Math.max(1, Math.ceil(availableBranches.length / 5));
    const currentPage = Math.min(branchPage, pageCount);
    const visibleBranches = availableBranches.slice((currentPage - 1) * 5, currentPage * 5);

    const selectBranch = (item) => {
        if (switchingBranch !== null || !item?.id || !item.hasAccess || item.is_current || !item.status) return;

        setSwitchingBranch(item.id);
        router.post(`/select-branch/${item.id}`, {}, {
            preserveScroll: false,
            preserveState: false,
            onFinish: () => setSwitchingBranch(null),
        });
    };

    return (
        <BusinessLayout
            title="Dashboard"
            subtitle={`${company.name ?? ''} • ${branch.name ?? ''}`}
        >
            <Head title={`${branch.name ?? 'Branch'} Dashboard - RR ERP`} />

            <main className="rr-branch-dash" aria-busy={switchingBranch !== null}>
                {switchingBranch !== null && (
                    <div role="status" aria-live="polite" style={{ position: 'fixed', inset: 0, zIndex: 20000, display: 'grid', placeItems: 'center', background: '#172f5066', backdropFilter: 'blur(2px)' }}>
                        <div style={{ padding: '24px 32px', borderRadius: 12, background: '#fff', color: '#172f50', boxShadow: '0 12px 40px #172f5033', display: 'flex', alignItems: 'center', gap: 12 }}>
                            <span className="spinner-border spinner-border-sm text-primary" aria-hidden="true" />
                            <strong>Opening branch workspace…</strong>
                        </div>
                    </div>
                )}
                <section className="rr-branch-hero">
                    <div className="rr-branch-hero-grid" />
                    <span className="rr-branch-orb orb-one" />
                    <span className="rr-branch-orb orb-two" />

                    <div className="rr-branch-hero-main">
                        <div className="rr-branch-hero-icon">
                            {logoUrl && failedLogo !== logoKey ? (
                                <img
                                    key={logoKey}
                                    src={logoUrl}
                                    alt={`${company.name ?? business.company_name ?? 'Company'} logo`}
                                    onError={() => setFailedLogo(logoKey)}
                                    style={{ width: '100%', height: '100%', objectFit: 'contain', borderRadius: 'inherit', background: '#fff', padding: 4, boxSizing: 'border-box' }}
                                />
                            ) : <i className="bi bi-shop-window" />}
                        </div>

                        <div className="rr-branch-hero-copy">
                            <span className="rr-branch-eyebrow">
                                <i className="bi bi-broadcast-pin" />
                                LIVE BRANCH WORKSPACE
                            </span>
                            <h1>{branch.name || 'Business Dashboard'}</h1>
                            <p>{company.name || 'Company'}</p>

                            <div className="rr-branch-context-chips">
                                <span><i className="bi bi-upc-scan" /> {branch.code || 'No branch code'}</span>
                                {branch.city && <span><i className="bi bi-geo-alt" /> {branch.city}</span>}
                                <span><i className="bi bi-building" /> {company.code || 'No company code'}</span>
                            </div>
                        </div>
                    </div>

                    <div className="rr-branch-hero-actions">
                        {isCompanyAdmin && (
                            <Link href="/company/dashboard" className="rr-branch-btn glass">
                                <i className="bi bi-speedometer2" />
                                Company Dashboard
                            </Link>
                        )}
                        {can('sales.create') && <Link href="/sales/create" className="rr-branch-btn bright">
                            <i className="bi bi-plus-circle" />
                            New Sale
                        </Link>}
                        {isSuperAdminContext && (
                            <button
                                type="button"
                                className="rr-branch-btn danger"
                                onClick={() => router.post('/branch/exit')}
                            >
                                <i className="bi bi-box-arrow-left" />
                                Exit Company
                            </button>
                        )}
                    </div>
                </section>

                <section className="rr-branch-section-heading">
                    <div>
                        <span>TODAY</span>
                        <h2>Branch Performance</h2>
                        <p>Live operational overview for the active branch.</p>
                    </div>
                    <div className="rr-branch-live-indicator"><i /> Live data</div>
                </section>

                <section className="rr-branch-kpi-grid">
                    {can('sales.view') && <KpiCard tone="blue" icon="bi-receipt-cutoff" label="Today's Sales" value={money(performance.today_sales)} hint="Branch sales today" href="/sales" />}
                    {can('purchase.view') && <KpiCard tone="violet" icon="bi-bag-check" label="Today's Purchases" value={money(performance.today_purchases)} hint="Branch purchases today" href="/purchases" />}
                    {can('customer-ledger.view') && <KpiCard tone="cyan" icon="bi-wallet2" label="Receivables" value={money(performance.receivables)} hint="Customer outstanding" href="/customer-ledger" />}
                    {can('current-stock.view') && <KpiCard tone="green" icon="bi-box-seam" label="Stock Value" value={money(performance.stock_value)} hint={`${number(performance.stock_quantity)} units available`} href="/inventory/current-stock" />}
                    {can('current-stock.view') && <KpiCard tone="amber" icon="bi-exclamation-triangle" label="Low Stock" value={number(performance.low_stock_count)} hint="Items need attention" href="/inventory/current-stock?stock_status=low_stock" />}
                    {can('customer.view') && <KpiCard tone="rose" icon="bi-people" label="Customers" value={number(performance.customers_count)} hint="Registered customers" href="/customers" />}
                    {can('supplier.view') && <KpiCard tone="indigo" icon="bi-truck" label="Suppliers" value={number(performance.suppliers_count)} hint="Registered suppliers" href="/suppliers" />}
                    {can('product.view') && <KpiCard tone="slate" icon="bi-boxes" label="Products" value={number(performance.products_count)} hint="Products in catalogue" href="/products" />}
                </section>

                <section className="rr-branch-workspace-grid">
                    <article className="rr-branch-panel rr-branch-quick-panel">
                        <PanelTitle icon="bi-lightning-charge-fill" tone="amber" eyebrow="WORKSPACE" title="Quick Actions" description="Frequently used branch operations" />
                        <div className="rr-branch-quick-grid">
                            {can('sales.create') && <QuickAction href="/sales/create" icon="bi-receipt" tone="blue" title="Create Sale" text="New customer invoice" />}
                            {can('purchase.create') && <QuickAction href="/purchases/create" icon="bi-bag-plus" tone="violet" title="Create Purchase" text="Record supplier bill" />}
                            {can('stock-transfer.create') && <QuickAction href="/stock-transfers/create" icon="bi-arrow-left-right" tone="cyan" title="Stock Transfer" text="Move stock to godown" />}
                            {can('customer-payment.create') && <QuickAction href="/customer-payments/create" icon="bi-cash-coin" tone="green" title="Receive Payment" text="Customer collection" />}
                            {can('expense.create') && <QuickAction href="/expenses/create" icon="bi-wallet2" tone="rose" title="Add Expense" text="Record branch expense" />}
                            {can('current-stock.view') && <QuickAction href="/inventory/current-stock" icon="bi-box-seam" tone="slate" title="Current Stock" text="View availability" />}
                        </div>
                    </article>

                    <article className="rr-branch-panel rr-branch-health-panel">
                        <PanelTitle icon="bi-activity" tone="green" eyebrow="SYSTEM STATUS" title="Branch Health" description="Operational readiness at a glance" />
                        <div className="rr-branch-health-list">
                            {can('product.view') && <HealthRow label="Inventory catalogue" value={`${number(performance.products_count)} products`} icon="bi-boxes" tone="blue" />}
                            {can('customer.view') && <HealthRow label="Customer network" value={`${number(performance.customers_count)} customers`} icon="bi-people" tone="rose" />}
                            {can('supplier.view') && <HealthRow label="Supplier network" value={`${number(performance.suppliers_count)} suppliers`} icon="bi-truck" tone="violet" />}
                            {can('current-stock.view') && <HealthRow label="Stock attention" value={`${number(performance.low_stock_count)} low-stock items`} icon="bi-exclamation-circle" tone={Number(performance.low_stock_count) > 0 ? 'amber' : 'green'} />}
                        </div>
                    </article>
                </section>

                {isCompanyAdmin && (
                    <section className="rr-branch-admin-grid">
                        <article className="rr-branch-panel">
                            <PanelTitle icon="bi-diagram-3" tone="blue" eyebrow="COMPANY CONTROL" title="Branch Network" description="Open assigned branches. Other branches require access approval." />

                            <div className="rr-branch-summary-strip">
                                <SummaryMetric label="Branches" value={availableBranches.length} tone="blue" />
                                <SummaryMetric label="Accessible" value={accessibleCount} tone="green" />
                                <SummaryMetric label="No Access" value={noAccessCount} tone="rose" />

                            </div>

                            <div className="rr-branch-list">
                                {visibleBranches.length > 0 ? visibleBranches.map((item) => (
                                    <button
                                        type="button"
                                        key={item.id}
                                        className={`rr-branch-list-item ${item.is_current ? 'current' : ''}`}
                                        disabled={!item.hasAccess || !item.status || item.is_current || switchingBranch !== null}
                                        title={!item.hasAccess ? 'Contact Super Admin or the ERP support team to request branch access.' : undefined}
                                        style={!item.hasAccess ? { opacity: 1, color: '#475569', background: '#f8fafc', cursor: 'not-allowed' } : undefined}
                                        onClick={() => selectBranch(item)}
                                    >
                                        <span className="rr-branch-list-icon"><i className="bi bi-shop" /></span>
                                        <span className="rr-branch-list-data">
                                            <strong>{item.name}</strong>
                                            <small>{[item.code, item.city].filter(Boolean).join(' • ')}</small>
                                            {!item.hasAccess && (
                                                <small style={{ color: '#9f1239', whiteSpace: 'normal', lineHeight: 1.4 }}>
                                                    Contact Super Admin or the ERP support team for access.
                                                </small>
                                            )}
                                        </span>
                                        <span
                                            className={`rr-branch-status ${item.hasAccess && item.status ? 'active' : 'pending'}`}
                                            style={!item.hasAccess ? { color: '#9f1239', background: '#fff1f2', borderColor: '#fecdd3', whiteSpace: 'nowrap' } : undefined}
                                        >
                                            <i /> {!item.hasAccess ? 'No Access' : item.is_current ? 'Current' : item.status ? 'Active' : 'Inactive'}
                                        </span>
                                        {item.hasAccess && !item.is_current && item.status && (
                                            <i className={`bi ${switchingBranch === item.id ? 'bi-arrow-repeat rr-spin' : 'bi-chevron-right'} rr-branch-list-arrow`} />
                                        )}
                                    </button>
                                )) : (
                                    <EmptyState icon="bi-shop-window" text="No branches available." />
                                )}
                            </div>

                            {pageCount > 1 && (
                                <div className="rr-branch-panel-footer" aria-label="Branch pagination">
                                    <button type="button" className="btn btn-sm btn-outline-primary" disabled={currentPage === 1 || switchingBranch !== null} onClick={() => setBranchPage(currentPage - 1)}>Previous</button>
                                    <span>Page {currentPage} of {pageCount}</span>
                                    <button type="button" className="btn btn-sm btn-outline-primary" disabled={currentPage === pageCount || switchingBranch !== null} onClick={() => setBranchPage(currentPage + 1)}>Next</button>
                                </div>
                            )}
                            <div className="rr-branch-panel-footer">
                                <Link href="/company/branches">Manage Branches <i className="bi bi-arrow-right" /></Link>
                            </div>
                        </article>

                        <article className="rr-branch-panel">
                            <PanelTitle icon="bi-people-fill" tone="violet" eyebrow="ACCESS CONTROL" title="Users & Permissions" description="Company users and branch access" />

                            <div className="rr-branch-summary-strip">
                                <SummaryMetric label="Total Users" value={userSummary.total} tone="violet" />
                                <SummaryMetric label="Active" value={userSummary.active} tone="green" />
                                <SummaryMetric label="Inactive" value={userSummary.inactive} tone="rose" />
                            </div>

                            <div className="rr-branch-admin-actions">
                                <QuickAdmin href="/company/users" icon="bi-people" title="Manage Users" text="Roles, status and branch access" />
                                <QuickAdmin href="/company/users/create" icon="bi-person-plus" title="Add User" text="Create and assign permissions" />
                                <QuickAdmin href="/company/profile" icon="bi-building-gear" title="Company Profile" text="Update company information" />
                            </div>
                        </article>
                    </section>
                )}

                <section className="rr-branch-activity-grid">
                    {can('sales.view') && <TransactionPanel title="Recent Sales" icon="bi-receipt" tone="blue" href="/sales" empty="No recent sales recorded.">
                        {recentSales.map((sale) => (
                            <TransactionRow key={sale.id} href={`/sales/${sale.id}`} number={sale.invoice_no} party={sale.customer} amount={money(sale.grand_total)} tone="in" />
                        ))}
                    </TransactionPanel>}

                    {can('purchase.view') && <TransactionPanel title="Recent Purchases" icon="bi-bag" tone="violet" href="/purchases" empty="No recent purchases recorded.">
                        {recentPurchases.map((purchase) => (
                            <TransactionRow key={purchase.id} href={`/purchases/${purchase.id}`} number={purchase.purchase_no} party={purchase.supplier} amount={money(purchase.grand_total)} tone="out" />
                        ))}
                    </TransactionPanel>}
                </section>
            </main>
        </BusinessLayout>
    );
}

function KpiCard({ tone, icon, label, value, hint, href }) {
    return (
        <Link href={href} className={`rr-branch-kpi ${tone}`}>
            <span className="rr-branch-kpi-icon"><i className={`bi ${icon}`} /></span>
            <span className="rr-branch-kpi-data">
                <small>{label}</small>
                <strong>{value}</strong>
                <em>{hint}</em>
            </span>
            <i className={`bi ${icon} rr-branch-kpi-watermark`} />
            <span className="rr-branch-kpi-arrow"><i className="bi bi-arrow-up-right" /></span>
        </Link>
    );
}

function PanelTitle({ icon, tone, eyebrow, title, description }) {
    return (
        <header className="rr-branch-panel-title">
            <span className={`rr-branch-panel-icon ${tone}`}><i className={`bi ${icon}`} /></span>
            <div><small>{eyebrow}</small><h2>{title}</h2><p>{description}</p></div>
        </header>
    );
}

function QuickAction({ href, icon, tone, title, text }) {
    return (
        <Link href={href} className="rr-branch-quick-action">
            <span className={`rr-branch-quick-icon ${tone}`}><i className={`bi ${icon}`} /></span>
            <span><strong>{title}</strong><small>{text}</small></span>
            <i className="bi bi-chevron-right" />
        </Link>
    );
}

function HealthRow({ label, value, icon, tone }) {
    return (
        <div className="rr-branch-health-row">
            <span className={`rr-branch-health-icon ${tone}`}><i className={`bi ${icon}`} /></span>
            <span><small>{label}</small><strong>{value}</strong></span>
            <i className="bi bi-check-circle-fill" />
        </div>
    );
}

function SummaryMetric({ label, value, tone }) {
    return <div className={`rr-branch-summary ${tone}`}><strong>{number(value)}</strong><span>{label}</span></div>;
}

function QuickAdmin({ href, icon, title, text }) {
    return (
        <Link href={href} className="rr-branch-admin-action">
            <i className={`bi ${icon}`} />
            <span><strong>{title}</strong><small>{text}</small></span>
            <i className="bi bi-chevron-right" />
        </Link>
    );
}

function TransactionPanel({ title, icon, tone, href, empty, children }) {
    const items = React.Children.toArray(children);

    return (
        <article className="rr-branch-panel rr-branch-transactions">
            <div className="rr-branch-transaction-title">
                <PanelTitle icon={icon} tone={tone} eyebrow="LATEST ACTIVITY" title={title} description="Most recent branch transactions" />
                <Link href={href}>View All <i className="bi bi-arrow-right" /></Link>
            </div>
            <div className="rr-branch-transaction-list">
                {items.length > 0 ? items : <EmptyState icon={icon} text={empty} />}
            </div>
        </article>
    );
}

function TransactionRow({ href, number: reference, party, amount, tone }) {
    return (
        <Link href={href} className="rr-branch-transaction-row">
            <span className={`rr-branch-transaction-mark ${tone}`}><i className={`bi ${tone === 'in' ? 'bi-arrow-down-left' : 'bi-arrow-up-right'}`} /></span>
            <span><strong>{reference || '—'}</strong><small>{party || 'Walk-in / Not specified'}</small></span>
            <strong className={tone}>{amount}</strong>
            <i className="bi bi-chevron-right" />
        </Link>
    );
}

function EmptyState({ icon, text }) {
    return <div className="rr-branch-empty"><i className={`bi ${icon}`} /><span>{text}</span></div>;
}

function money(value) {
    return new Intl.NumberFormat('en-IN', {
        style: 'currency', currency: 'INR', minimumFractionDigits: 2, maximumFractionDigits: 2,
    }).format(Number.isFinite(Number(value)) ? Number(value) : 0);
}

function number(value) {
    return new Intl.NumberFormat('en-IN').format(Number.isFinite(Number(value)) ? Number(value) : 0);
}
