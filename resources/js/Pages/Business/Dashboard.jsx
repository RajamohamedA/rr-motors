import {
    Head,
    Link,
    router,
    usePage,
} from '@inertiajs/react';

import BusinessLayout
    from '../../Layouts/BusinessLayout';


export default function Dashboard({
    context,
    performance,
    branchSummary,
    userSummary,
    branches,
    recentSales,
    recentPurchases,
}) {

    const {
        auth,
    } = usePage().props;


    const company =
        context?.company ?? {};


    const branch =
        context?.branch ?? {};


    const isSuperAdminContext =
        Boolean(
            context?.is_super_admin_context
        );


    const isCompanyAdmin =
        auth?.user?.roles?.includes(
            'Company Admin'
        );


    const exitBusiness =
        () => {

            router.post(
                '/branch/exit'
            );
        };


    const formatCurrency =
        (value) =>
            new Intl.NumberFormat(
                'en-IN',
                {
                    style:
                        'currency',

                    currency:
                        'INR',

                    maximumFractionDigits:
                        0,
                }
            ).format(
                Number(
                    value ?? 0
                )
            );


    return (

        <BusinessLayout
            title="Dashboard"
            subtitle={`${company.name ?? ''} • ${branch.name ?? ''}`}
        >

            <Head title="Business Dashboard - RR ERP" />


            <div className="rr-business-dashboard">

                {/* =====================================================
                    BUSINESS HERO
                   ===================================================== */}

                <section className="rr-business-hero">

                    <div className="rr-business-hero-main">

                        <div className="rr-business-company-icon">

                            <i className="bi bi-buildings"></i>

                        </div>


                        <div>

                            <div className="rr-business-eyebrow">
                                BUSINESS WORKSPACE
                            </div>


                            <h1>
                                {company.name}
                            </h1>


                            <div className="rr-business-branch-name">

                                <i className="bi bi-shop"></i>

                                {branch.name}

                            </div>


                            <div className="rr-business-context-meta">

                                <span>

                                    <strong>
                                        Company Code
                                    </strong>

                                    {company.code}

                                </span>


                                <span>

                                    <strong>
                                        Branch Code
                                    </strong>

                                    {branch.code}

                                </span>


                                {
                                    branch.city && (

                                        <span>

                                            <strong>
                                                Location
                                            </strong>

                                            {branch.city}

                                        </span>

                                    )
                                }

                            </div>

                        </div>

                    </div>


                    <div className="rr-business-hero-actions">

                        {
                            isCompanyAdmin && (

                                <>

                                    <Link
                                        href="/company/profile"
                                        className="rr-business-secondary-btn"
                                    >

                                        <i className="bi bi-building-gear"></i>

                                        Company Profile

                                    </Link>


                                    <Link
                                        href="/company/branches/create"
                                        className="rr-business-primary-btn"
                                    >

                                        <i className="bi bi-plus-lg"></i>

                                        Add Branch

                                    </Link>

                                </>

                            )
                        }


                        {
                            isSuperAdminContext && (

                                <button
                                    type="button"
                                    className="rr-business-exit-btn"
                                    onClick={
                                        exitBusiness
                                    }
                                >

                                    <i className="bi bi-box-arrow-left"></i>

                                    Exit Company

                                </button>

                            )
                        }

                    </div>

                </section>


                {/* =====================================================
                    PERFORMANCE
                   ===================================================== */}

                <section className="rr-business-section">

                    <div className="rr-business-section-header">

                        <div>

                            <span>
                                TODAY
                            </span>

                            <h2>
                                Business Performance
                            </h2>

                            <p>
                                Live summary for the currently active branch.
                            </p>

                        </div>

                    </div>


                    <div className="rr-business-kpi-grid">

                        <KpiCard
                            icon="bi-receipt"
                            label="Today's Sales"
                            value={
                                formatCurrency(
                                    performance?.today_sales
                                )
                            }
                            hint="Branch sales today"
                        />

                        <KpiCard
                            icon="bi-bag-check"
                            label="Today's Purchases"
                            value={
                                formatCurrency(
                                    performance?.today_purchases
                                )
                            }
                            hint="Branch purchases today"
                        />

                        <KpiCard
                            icon="bi-wallet2"
                            label="Receivables"
                            value={
                                formatCurrency(
                                    performance?.receivables
                                )
                            }
                            hint="Outstanding customer balance"
                        />

                        <KpiCard
                            icon="bi-box-seam"
                            label="Stock Value"
                            value={
                                formatCurrency(
                                    performance?.stock_value
                                )
                            }
                            hint={`${performance?.stock_quantity ?? 0} units`}
                        />

                        <KpiCard
                            icon="bi-exclamation-triangle"
                            label="Low Stock"
                            value={
                                performance?.low_stock_count ?? 0
                            }
                            hint="Items requiring attention"
                            warning
                        />

                        <KpiCard
                            icon="bi-people"
                            label="Customers"
                            value={
                                performance?.customers_count ?? 0
                            }
                            hint="Registered customers"
                        />

                        <KpiCard
                            icon="bi-truck"
                            label="Suppliers"
                            value={
                                performance?.suppliers_count ?? 0
                            }
                            hint="Registered suppliers"
                        />

                        <KpiCard
                            icon="bi-boxes"
                            label="Products"
                            value={
                                performance?.products_count ?? 0
                            }
                            hint="Products in catalog"
                        />

                    </div>

                </section>


                {/* =====================================================
                    COMPANY ADMIN SUMMARY
                   ===================================================== */}

                {
                    isCompanyAdmin && (

                        <section className="rr-business-admin-grid">

                            {/* BRANCHES */}

                            <div className="rr-business-panel">

                                <PanelHeader
                                    icon="bi-shop-window"
                                    title="Branch Management"
                                    description="Your company branches and approval status"
                                />


                                <div className="rr-business-summary-row">

                                    <SummaryMetric
                                        label="Total"
                                        value={
                                            branchSummary?.total ?? 0
                                        }
                                    />

                                    <SummaryMetric
                                        label="Active"
                                        value={
                                            branchSummary?.active ?? 0
                                        }
                                    />

                                    <SummaryMetric
                                        label="Pending"
                                        value={
                                            branchSummary?.pending ?? 0
                                        }
                                        warning
                                    />

                                </div>


                                <div className="rr-business-branch-list">

                                    {
                                        branches?.map(
                                            (
                                                item
                                            ) => (

                                                <div
                                                    key={
                                                        item.id
                                                    }
                                                    className={
                                                        `rr-business-branch-item ${item.is_current
                                                            ? 'current'
                                                            : ''
                                                        }`
                                                    }
                                                >

                                                    <div className="rr-business-branch-item-icon">

                                                        <i className="bi bi-shop"></i>

                                                    </div>


                                                    <div className="rr-business-branch-item-data">

                                                        <strong>
                                                            {item.name}
                                                        </strong>

                                                        <span>

                                                            {item.code}

                                                            {
                                                                item.city
                                                                    ? ` • ${item.city}`
                                                                    : ''
                                                            }

                                                        </span>

                                                    </div>


                                                    <div
                                                        className={
                                                            item.status
                                                                ? 'rr-business-status active'
                                                                : 'rr-business-status pending'
                                                        }
                                                    >

                                                        {
                                                            item.status
                                                                ? 'Active'
                                                                : 'Pending Approval'
                                                        }

                                                    </div>

                                                </div>

                                            )
                                        )
                                    }

                                </div>


                                <div className="rr-business-panel-footer">

                                    <Link
                                        href="/company/branches"
                                        className="rr-business-link-btn"
                                    >
                                        View All Branches
                                    </Link>

                                </div>

                            </div>


                            {/* USERS */}

                            <div className="rr-business-panel">

                                <PanelHeader
                                    icon="bi-people"
                                    title="Users & Access"
                                    description="Company users and role access"
                                />


                                <div className="rr-business-summary-row">

                                    <SummaryMetric
                                        label="Total Users"
                                        value={
                                            userSummary?.total ?? 0
                                        }
                                    />

                                    <SummaryMetric
                                        label="Active"
                                        value={
                                            userSummary?.active ?? 0
                                        }
                                    />

                                    <SummaryMetric
                                        label="Inactive"
                                        value={
                                            userSummary?.inactive ?? 0
                                        }
                                    />

                                </div>


                                <div className="rr-business-admin-actions">

                                    <Link
                                        href="/company/users"
                                        className="rr-business-admin-action"
                                    >

                                        <i className="bi bi-people"></i>

                                        <div>

                                            <strong>
                                                Manage Users
                                            </strong>

                                            <span>
                                                View company users and access
                                            </span>

                                        </div>

                                    </Link>


                                    <Link
                                        href="/company/users/create"
                                        className="rr-business-admin-action"
                                    >

                                        <i className="bi bi-person-plus"></i>

                                        <div>

                                            <strong>
                                                Add User
                                            </strong>

                                            <span>
                                                Assign role and branch access
                                            </span>

                                        </div>

                                    </Link>


                                    <Link
                                        href="/company/profile"
                                        className="rr-business-admin-action"
                                    >

                                        <i className="bi bi-building-gear"></i>

                                        <div>

                                            <strong>
                                                Edit Company
                                            </strong>

                                            <span>
                                                Update company profile details
                                            </span>

                                        </div>

                                    </Link>

                                </div>

                            </div>

                        </section>

                    )
                }


                {/* =====================================================
                    RECENT ACTIVITY
                   ===================================================== */}

                <section className="rr-business-transaction-grid">

                    <TransactionPanel
                        title="Recent Sales"
                        icon="bi-receipt"
                        emptyText="No recent sales."
                    >

                        {
                            recentSales?.map(
                                (
                                    sale
                                ) => (

                                    <TransactionRow
                                        key={
                                            sale.id
                                        }
                                        number={
                                            sale.invoice_no
                                        }
                                        party={
                                            sale.customer
                                        }
                                        amount={
                                            formatCurrency(
                                                sale.grand_total
                                            )
                                        }
                                    />

                                )
                            )
                        }

                    </TransactionPanel>


                    <TransactionPanel
                        title="Recent Purchases"
                        icon="bi-bag"
                        emptyText="No recent purchases."
                    >

                        {
                            recentPurchases?.map(
                                (
                                    purchase
                                ) => (

                                    <TransactionRow
                                        key={
                                            purchase.id
                                        }
                                        number={
                                            purchase.purchase_no
                                        }
                                        party={
                                            purchase.supplier
                                        }
                                        amount={
                                            formatCurrency(
                                                purchase.grand_total
                                            )
                                        }
                                    />

                                )
                            )
                        }

                    </TransactionPanel>

                </section>

            </div>

        </BusinessLayout>
    );
}


/*
|--------------------------------------------------------------------------
| KPI Card
|--------------------------------------------------------------------------
*/

function KpiCard({
    icon,
    label,
    value,
    hint,
    warning = false,
}) {

    return (

        <div
            className={
                `rr-business-kpi-card ${warning
                    ? 'warning'
                    : ''
                }`
            }
        >

            <div className="rr-business-kpi-icon">

                <i className={`bi ${icon}`}></i>

            </div>


            <div className="rr-business-kpi-label">
                {label}
            </div>


            <div className="rr-business-kpi-value">
                {value}
            </div>


            <div className="rr-business-kpi-hint">
                {hint}
            </div>

        </div>
    );
}


/*
|--------------------------------------------------------------------------
| Panel Header
|--------------------------------------------------------------------------
*/

function PanelHeader({
    icon,
    title,
    description,
}) {

    return (

        <div className="rr-business-panel-header">

            <div className="rr-business-panel-icon">

                <i className={`bi ${icon}`}></i>

            </div>


            <div>

                <h3>
                    {title}
                </h3>

                <p>
                    {description}
                </p>

            </div>

        </div>
    );
}


/*
|--------------------------------------------------------------------------
| Summary Metric
|--------------------------------------------------------------------------
*/

function SummaryMetric({
    label,
    value,
    warning = false,
}) {

    return (

        <div
            className={
                `rr-business-summary-metric ${warning
                    ? 'warning'
                    : ''
                }`
            }
        >

            <strong>
                {value}
            </strong>

            <span>
                {label}
            </span>

        </div>
    );
}


/*
|--------------------------------------------------------------------------
| Transaction Panel
|--------------------------------------------------------------------------
*/

function TransactionPanel({
    title,
    icon,
    emptyText,
    children,
}) {

    const hasChildren =
        Array.isArray(
            children
        )
            ? children.length > 0
            : Boolean(
                children
            );


    return (

        <div className="rr-business-panel">

            <PanelHeader
                icon={icon}
                title={title}
                description="Latest branch transactions"
            />


            <div className="rr-business-transaction-list">

                {
                    hasChildren
                        ? children
                        : (

                            <div className="rr-business-empty">
                                {emptyText}
                            </div>

                        )
                }

            </div>

        </div>
    );
}


/*
|--------------------------------------------------------------------------
| Transaction Row
|--------------------------------------------------------------------------
*/

function TransactionRow({
    number,
    party,
    amount,
}) {

    return (

        <div className="rr-business-transaction-row">

            <div>

                <strong>
                    {number}
                </strong>

                <span>
                    {party}
                </span>

            </div>


            <strong className="rr-business-transaction-amount">
                {amount}
            </strong>

        </div>
    );
}