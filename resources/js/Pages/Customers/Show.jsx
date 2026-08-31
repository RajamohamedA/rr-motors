import {
    Head,
    Link,
    router,
    usePage,
} from '@inertiajs/react';

import {
    useMemo,
    useState,
} from 'react';

import BusinessLayout
    from '../../Layouts/BusinessLayout';

import MasterNavigation
    from '../../Components/Business/MasterNavigation';

import '../../../css/business/customers.css';
import '../../../css/business/customer-show.css';


export default function Show({
    customer,
    account_summary: accountSummary,
    transactions,
    sales,
    payments,
}) {

    const {
        auth,
        business,
    } = usePage().props;


    const user =
        auth?.user;


    const permissions =
        user?.permissions
        ?? [];


    const roles =
        user?.roles
        ?? [];


    const isSuperAdmin =
        Boolean(
            user?.is_super_user
        );


    const isCompanyAdmin =
        roles.includes(
            'Company Admin'
        );


    const can =
        permission => {

            if (
                isSuperAdmin ||
                isCompanyAdmin
            ) {

                return true;
            }


            return permissions.includes(
                permission
            );
        };


    const [
        activeTab,
        setActiveTab,
    ] =
        useState(
            'overview'
        );


    const tabs =
        useMemo(
            () => [
                {
                    key:
                        'overview',

                    label:
                        'Overview',

                    icon:
                        'bi-person-vcard',
                },
                {
                    key:
                        'transactions',

                    label:
                        'Transactions',

                    icon:
                        'bi-journal-text',

                    count:
                        transactions?.total
                        ?? 0,
                },
                {
                    key:
                        'sales',

                    label:
                        'Sales',

                    icon:
                        'bi-receipt',

                    count:
                        sales?.total
                        ?? 0,
                },
                {
                    key:
                        'payments',

                    label:
                        'Payments',

                    icon:
                        'bi-cash-coin',

                    count:
                        payments?.total
                        ?? 0,
                },
                {
                    key:
                        'branches',

                    label:
                        'Branches',

                    icon:
                        'bi-diagram-3',

                    count:
                        customer?.stores_count
                        ?? 0,
                },
            ],
            [
                transactions?.total,
                sales?.total,
                payments?.total,
                customer?.stores_count,
            ]
        );


    const isShared =
        Boolean(
            customer?.shared
        );


    const isSystemCustomer =
        Boolean(
            customer?.is_system_customer
        );


    return (

        <BusinessLayout>

            <Head
                title={
                    customer?.customer_name
                    ?? 'Customer'
                }
            />


            <div className="rr-customers-page">

                <MasterNavigation
                    active="customers"
                    title="Customer Account"
                    subtitle="Customer profile, balances and transaction history"
                />


                {/* =====================================================
                    ACCOUNT HEADER
                   ===================================================== */}

                <section className="rr-customer-account-header">

                    <div className="rr-customer-account-header-main">

                        <button
                            type="button"
                            className="rr-customer-account-back"
                            onClick={
                                () =>
                                    window.history.back()
                            }
                            title="Back"
                        >

                            <i className="bi bi-arrow-left"></i>

                        </button>


                        <div className="rr-customer-account-avatar">

                            {
                                getInitials(
                                    customer?.customer_name
                                )
                            }

                        </div>


                        <div className="rr-customer-account-identity">

                            <div className="rr-customer-account-kicker">

                                <span>
                                    {
                                        customer?.customer_code
                                        ?? 'Customer'
                                    }
                                </span>


                                {
                                    isSystemCustomer && (

                                        <span className="rr-customer-system-badge">

                                            <i className="bi bi-shield-check"></i>

                                            System Customer

                                        </span>

                                    )
                                }


                                {
                                    isShared && (

                                        <span className="rr-customer-shared-badge">

                                            <i className="bi bi-diagram-3"></i>

                                            Shared across {
                                                customer?.stores_count
                                                ?? 0
                                            } branches

                                        </span>

                                    )
                                }

                            </div>


                            <h1>
                                {
                                    customer?.customer_name
                                }
                            </h1>


                            <div className="rr-customer-account-subline">

                                {
                                    customer?.mobile && (

                                        <span>

                                            <i className="bi bi-phone"></i>

                                            {
                                                customer.mobile
                                            }

                                        </span>

                                    )
                                }


                                {
                                    customer?.email && (

                                        <span>

                                            <i className="bi bi-envelope"></i>

                                            {
                                                customer.email
                                            }

                                        </span>

                                    )
                                }


                                {
                                    (
                                        customer?.city ||
                                        customer?.state
                                    ) && (

                                        <span>

                                            <i className="bi bi-geo-alt"></i>

                                            {
                                                [
                                                    customer?.city,
                                                    customer?.state,
                                                ]
                                                    .filter(
                                                        Boolean
                                                    )
                                                    .join(
                                                        ', '
                                                    )
                                            }

                                        </span>

                                    )
                                }

                            </div>

                        </div>

                    </div>


                    <div className="rr-customer-account-header-actions">

                        <span
                            className={
                                `rr-customer-status ${customer?.status
                                    ? 'active'
                                    : 'inactive'
                                }`
                            }
                        >

                            <span></span>

                            {
                                customer?.status
                                    ? 'Active'
                                    : 'Inactive'
                            }

                        </span>


                        {
                            can(
                                'customer.edit'
                            )
                            &&
                            !isSystemCustomer
                            && (

                                <Link
                                    href={
                                        `/customers/${customer.id}/edit`
                                    }
                                    className="rr-customer-primary-btn"
                                >

                                    <i className="bi bi-pencil"></i>

                                    Edit Customer

                                </Link>

                            )
                        }

                    </div>

                </section>


                {/* =====================================================
                    SHARED WARNING
                   ===================================================== */}

                {
                    isShared && (

                        <section className="rr-customer-shared-warning">

                            <i className="bi bi-exclamation-triangle"></i>


                            <div>

                                <strong>
                                    Shared customer
                                </strong>

                                <span>
                                    Changes to this customer profile affect all branches where this customer is assigned.
                                </span>

                            </div>

                        </section>

                    )
                }


                {/* =====================================================
                    ACCOUNT SUMMARY
                   ===================================================== */}

                <section className="rr-customer-account-summary">

                    <AccountStat
                        label="Current Balance"
                        value={
                            money(
                                accountSummary?.current_balance
                            )
                        }
                        prefix="₹"
                        icon="bi-wallet2"
                        tone={
                            Number(
                                accountSummary?.current_balance
                                ?? 0
                            ) > 0
                                ? 'danger'
                                : 'success'
                        }
                    />


                    <AccountStat
                        label="Credit Limit"
                        value={
                            money(
                                accountSummary?.credit_limit
                            )
                        }
                        prefix="₹"
                        icon="bi-credit-card"
                    />


                    <AccountStat
                        label="Available Credit"
                        value={
                            money(
                                accountSummary?.available_credit
                            )
                        }
                        prefix="₹"
                        icon="bi-check2-circle"
                        tone="success"
                    />


                    <AccountStat
                        label="Total Sales"
                        value={
                            money(
                                accountSummary?.total_sales
                            )
                        }
                        prefix="₹"
                        icon="bi-receipt-cutoff"
                        tone="primary"
                    />


                    <AccountStat
                        label="Opening Balance"
                        value={
                            money(
                                accountSummary?.opening_balance
                            )
                        }
                        prefix="₹"
                        suffix={
                            accountSummary?.balance_type
                            ?? 'Dr'
                        }
                        icon="bi-journal-plus"
                        tone="muted"
                    />


                    <AccountStat
                        label="Credit Days"
                        value={
                            accountSummary?.credit_days
                            ?? 0
                        }
                        suffix="days"
                        icon="bi-calendar3"
                        tone="warning"
                    />

                </section>


                {/* =====================================================
                    TABS
                   ===================================================== */}

                <section className="rr-customer-account-panel">

                    <div className="rr-customer-account-tabs">

                        {
                            tabs.map(
                                tab => (

                                    <button
                                        key={
                                            tab.key
                                        }
                                        type="button"
                                        className={
                                            `rr-customer-account-tab ${activeTab ===
                                                tab.key
                                                ? 'active'
                                                : ''
                                            }`
                                        }
                                        onClick={
                                            () =>
                                                setActiveTab(
                                                    tab.key
                                                )
                                        }
                                    >

                                        <i
                                            className={
                                                `bi ${tab.icon}`
                                            }
                                        ></i>

                                        <span>
                                            {
                                                tab.label
                                            }
                                        </span>


                                        {
                                            tab.count !==
                                            undefined && (

                                                <small>
                                                    {
                                                        tab.count
                                                    }
                                                </small>

                                            )
                                        }

                                    </button>

                                )
                            )
                        }

                    </div>


                    <div className="rr-customer-account-tab-content">

                        {
                            activeTab ===
                            'overview' && (

                                <OverviewTab
                                    customer={
                                        customer
                                    }
                                    accountSummary={
                                        accountSummary
                                    }
                                />

                            )
                        }


                        {
                            activeTab ===
                            'transactions' && (

                                <TransactionsTab
                                    transactions={
                                        transactions
                                    }
                                />

                            )
                        }


                        {
                            activeTab ===
                            'sales' && (

                                <SalesTab
                                    sales={
                                        sales
                                    }
                                />

                            )
                        }


                        {
                            activeTab ===
                            'payments' && (

                                <PaymentsTab
                                    payments={
                                        payments
                                    }
                                />

                            )
                        }


                        {
                            activeTab ===
                            'branches' && (

                                <BranchesTab
                                    branches={
                                        customer?.stores
                                        ?? []
                                    }
                                    activeStoreId={
                                        business?.store_id
                                    }
                                />

                            )
                        }

                    </div>

                </section>

            </div>

        </BusinessLayout>
    );
}


/*
|--------------------------------------------------------------------------
| Overview
|--------------------------------------------------------------------------
*/

function OverviewTab({
    customer,
    accountSummary,
}) {

    return (

        <div className="rr-customer-overview-grid">

            <section className="rr-customer-account-card">

                <CardHeader
                    icon="bi-person"
                    title="Contact Details"
                />


                <div className="rr-customer-account-card-body">

                    <InfoRow
                        label="Customer Code"
                        value={
                            customer?.customer_code
                        }
                    />

                    <InfoRow
                        label="Contact Person"
                        value={
                            customer?.contact_person
                        }
                    />

                    <InfoRow
                        label="Mobile"
                        value={
                            customer?.mobile
                        }
                    />

                    <InfoRow
                        label="Phone"
                        value={
                            customer?.phone
                        }
                    />

                    <InfoRow
                        label="Email"
                        value={
                            customer?.email
                        }
                    />

                    <InfoRow
                        label="Customer Type"
                        value={
                            customer?.customer_type ===
                                'walk_in'
                                ? 'Walk-in Customer'
                                : 'Regular Customer'
                        }
                    />

                </div>

            </section>


            <section className="rr-customer-account-card">

                <CardHeader
                    icon="bi-receipt"
                    title="Tax Details"
                />


                <div className="rr-customer-account-card-body">

                    <InfoRow
                        label="GST Number"
                        value={
                            customer?.gst_number
                        }
                    />

                    <InfoRow
                        label="PAN Number"
                        value={
                            customer?.pan_number
                        }
                    />

                    <InfoRow
                        label="Status"
                        value={
                            customer?.status
                                ? 'Active'
                                : 'Inactive'
                        }
                    />

                    <InfoRow
                        label="Branches"
                        value={
                            customer?.stores_count
                            ?? 0
                        }
                    />

                </div>

            </section>


            <section className="rr-customer-account-card">

                <CardHeader
                    icon="bi-geo-alt"
                    title="Billing Address"
                />


                <div className="rr-customer-account-card-body">

                    <AddressBlock
                        address={
                            customer?.billing_address
                        }
                        city={
                            customer?.city
                        }
                        state={
                            customer?.state
                        }
                        pincode={
                            customer?.pincode
                        }
                        country={
                            customer?.country
                        }
                    />

                </div>

            </section>


            <section className="rr-customer-account-card">

                <CardHeader
                    icon="bi-truck"
                    title="Shipping Address"
                />


                <div className="rr-customer-account-card-body">

                    <AddressBlock
                        address={
                            customer?.shipping_address
                        }
                        city={
                            customer?.city
                        }
                        state={
                            customer?.state
                        }
                        pincode={
                            customer?.pincode
                        }
                        country={
                            customer?.country
                        }
                    />

                </div>

            </section>


            <section className="rr-customer-account-card">

                <CardHeader
                    icon="bi-cash-stack"
                    title="Account Details"
                />


                <div className="rr-customer-account-card-body">

                    <InfoRow
                        label="Current Balance"
                        value={
                            `₹${money(
                                accountSummary?.current_balance
                            )}`
                        }
                    />

                    <InfoRow
                        label="Credit Limit"
                        value={
                            `₹${money(
                                accountSummary?.credit_limit
                            )}`
                        }
                    />

                    <InfoRow
                        label="Available Credit"
                        value={
                            `₹${money(
                                accountSummary?.available_credit
                            )}`
                        }
                    />

                    <InfoRow
                        label="Opening Balance"
                        value={
                            `₹${money(
                                accountSummary?.opening_balance
                            )} ${accountSummary?.balance_type
                            ?? ''
                            }`
                        }
                    />

                    <InfoRow
                        label="Credit Days"
                        value={
                            `${accountSummary?.credit_days
                            ?? 0
                            } days`
                        }
                    />

                </div>

            </section>


            <section className="rr-customer-account-card">

                <CardHeader
                    icon="bi-clock-history"
                    title="Recent Activity"
                />


                <div className="rr-customer-account-card-body">

                    <InfoRow
                        label="Last Sale"
                        value={
                            accountSummary?.last_sale
                                ? `${accountSummary.last_sale.invoice_no} · ₹${money(
                                    accountSummary.last_sale.amount
                                )}`
                                : '—'
                        }
                    />

                    <InfoRow
                        label="Last Sale Date"
                        value={
                            formatDate(
                                accountSummary?.last_sale?.date
                            )
                        }
                    />

                    <InfoRow
                        label="Last Payment"
                        value={
                            accountSummary?.last_payment
                                ? `₹${money(
                                    accountSummary.last_payment.amount
                                )}`
                                : '—'
                        }
                    />

                    <InfoRow
                        label="Last Payment Date"
                        value={
                            formatDate(
                                accountSummary?.last_payment?.date
                            )
                        }
                    />

                </div>

            </section>


            {
                customer?.remarks && (

                    <section className="rr-customer-account-card rr-customer-account-card-wide">

                        <CardHeader
                            icon="bi-chat-left-text"
                            title="Remarks"
                        />


                        <div className="rr-customer-account-card-body">

                            <p className="rr-customer-account-remarks">
                                {
                                    customer.remarks
                                }
                            </p>

                        </div>

                    </section>

                )
            }

        </div>
    );
}


/*
|--------------------------------------------------------------------------
| Transactions
|--------------------------------------------------------------------------
*/

function TransactionsTab({
    transactions,
}) {

    const rows =
        transactions?.data
        ?? [];


    return (

        <DataSection
            title="Customer Transactions"
            subtitle="Sales and payments combined"
            icon="bi-journal-text"
            paginator={
                transactions
            }
        >

            <table className="rr-customer-account-table">

                <thead>

                    <tr>

                        <th>
                            Date
                        </th>

                        <th>
                            Type
                        </th>

                        <th>
                            Reference
                        </th>

                        <th className="text-end">
                            Debit
                        </th>

                        <th className="text-end">
                            Credit
                        </th>

                    </tr>

                </thead>


                <tbody>

                    {
                        rows.length > 0
                            ? rows.map(
                                row => (

                                    <tr
                                        key={
                                            `${row.type}-${row.id}-${row.date}`
                                        }
                                    >

                                        <td>
                                            {
                                                formatDate(
                                                    row.date
                                                )
                                            }
                                        </td>

                                        <td>

                                            <TransactionBadge
                                                type={
                                                    row.type
                                                }
                                            />

                                        </td>

                                        <td>
                                            <strong>
                                                {
                                                    row.reference
                                                    ?? '—'
                                                }
                                            </strong>
                                        </td>

                                        <td className="text-end rr-account-debit">
                                            {
                                                Number(
                                                    row.debit
                                                    ?? 0
                                                ) > 0
                                                    ? `₹${money(
                                                        row.debit
                                                    )}`
                                                    : '—'
                                            }
                                        </td>

                                        <td className="text-end rr-account-credit">
                                            {
                                                Number(
                                                    row.credit
                                                    ?? 0
                                                ) > 0
                                                    ? `₹${money(
                                                        row.credit
                                                    )}`
                                                    : '—'
                                            }
                                        </td>

                                    </tr>

                                )
                            )
                            : (
                                <EmptyTable
                                    colspan="5"
                                    text="No transactions found."
                                />
                            )
                    }

                </tbody>

            </table>

        </DataSection>
    );
}


/*
|--------------------------------------------------------------------------
| Sales
|--------------------------------------------------------------------------
*/

function SalesTab({
    sales,
}) {

    const rows =
        sales?.data
        ?? [];


    return (

        <DataSection
            title="Sales History"
            subtitle="Customer invoices"
            icon="bi-receipt"
            paginator={
                sales
            }
        >

            <table className="rr-customer-account-table">

                <thead>

                    <tr>

                        <th>
                            Invoice
                        </th>

                        <th>
                            Date
                        </th>

                        <th className="text-end">
                            Grand Total
                        </th>

                        <th className="text-end">
                            Paid
                        </th>

                        <th className="text-end">
                            Balance
                        </th>

                        <th>
                            Status
                        </th>

                    </tr>

                </thead>


                <tbody>

                    {
                        rows.length > 0
                            ? rows.map(
                                sale => (

                                    <tr
                                        key={
                                            sale.id
                                        }
                                    >

                                        <td>

                                            <Link
                                                href={
                                                    `/sales/${sale.id}`
                                                }
                                                className="rr-account-reference-link"
                                            >

                                                {
                                                    sale.invoice_no
                                                    ?? `Sale #${sale.id}`
                                                }

                                            </Link>

                                        </td>

                                        <td>
                                            {
                                                formatDate(
                                                    sale.invoice_date
                                                )
                                            }
                                        </td>

                                        <td className="text-end">
                                            ₹{
                                                money(
                                                    sale.grand_total
                                                )
                                            }
                                        </td>

                                        <td className="text-end rr-account-credit">
                                            ₹{
                                                money(
                                                    sale.paid_amount
                                                )
                                            }
                                        </td>

                                        <td className="text-end rr-account-debit">
                                            ₹{
                                                money(
                                                    sale.balance_amount
                                                )
                                            }
                                        </td>

                                        <td>

                                            <span
                                                className="rr-account-sale-status"
                                            >
                                                {
                                                    sale.status
                                                    ?? 'Recorded'
                                                }
                                            </span>

                                        </td>

                                    </tr>

                                )
                            )
                            : (
                                <EmptyTable
                                    colspan="6"
                                    text="No sales found for this customer."
                                />
                            )
                    }

                </tbody>

            </table>

        </DataSection>
    );
}


/*
|--------------------------------------------------------------------------
| Payments
|--------------------------------------------------------------------------
*/

function PaymentsTab({
    payments,
}) {

    const rows =
        payments?.data
        ?? [];


    return (

        <DataSection
            title="Payment History"
            subtitle="Receipts and customer payments"
            icon="bi-cash-coin"
            paginator={
                payments
            }
        >

            <table className="rr-customer-account-table">

                <thead>

                    <tr>

                        <th>
                            Date
                        </th>

                        <th>
                            Reference
                        </th>

                        <th>
                            Method
                        </th>

                        <th className="text-end">
                            Amount
                        </th>

                        <th>
                            Remarks
                        </th>

                    </tr>

                </thead>


                <tbody>

                    {
                        rows.length > 0
                            ? rows.map(
                                payment => (

                                    <tr
                                        key={
                                            payment.id
                                        }
                                    >

                                        <td>
                                            {
                                                formatDate(
                                                    payment.date
                                                )
                                            }
                                        </td>

                                        <td>

                                            <strong>
                                                {
                                                    payment.reference_no
                                                    ?? `Payment #${payment.id}`
                                                }
                                            </strong>

                                        </td>

                                        <td>
                                            {
                                                payment.payment_method
                                                ?? '—'
                                            }
                                        </td>

                                        <td className="text-end rr-account-credit">
                                            ₹{
                                                money(
                                                    payment.amount
                                                )
                                            }
                                        </td>

                                        <td>
                                            {
                                                payment.remarks
                                                ?? '—'
                                            }
                                        </td>

                                    </tr>

                                )
                            )
                            : (
                                <EmptyTable
                                    colspan="5"
                                    text="No customer payments found."
                                />
                            )
                    }

                </tbody>

            </table>

        </DataSection>
    );
}


/*
|--------------------------------------------------------------------------
| Branches
|--------------------------------------------------------------------------
*/

function BranchesTab({
    branches,
    activeStoreId,
}) {

    return (

        <div className="rr-customer-branches-grid">

            {
                branches.length > 0
                    ? branches.map(
                        branch => {

                            const current =
                                Number(
                                    activeStoreId
                                ) ===
                                Number(
                                    branch.id
                                );


                            return (

                                <div
                                    key={
                                        branch.id
                                    }
                                    className={
                                        `rr-customer-branch-card ${current
                                            ? 'current'
                                            : ''
                                        }`
                                    }
                                >

                                    <div className="rr-customer-branch-card-icon">

                                        <i className="bi bi-shop"></i>

                                    </div>


                                    <div>

                                        <strong>
                                            {
                                                branch.store_name
                                            }
                                        </strong>

                                        <span>
                                            {
                                                branch.store_code
                                                ?? '—'
                                            }
                                        </span>

                                    </div>


                                    <div className="rr-customer-branch-card-status">

                                        {
                                            current && (

                                                <span className="current">
                                                    Current Branch
                                                </span>

                                            )
                                        }


                                        <span
                                            className={
                                                branch.status
                                                    ? 'active'
                                                    : 'inactive'
                                            }
                                        >
                                            {
                                                branch.status
                                                    ? 'Active'
                                                    : 'Inactive'
                                            }
                                        </span>

                                    </div>

                                </div>

                            );
                        }
                    )
                    : (
                        <div className="rr-account-empty-state">

                            <i className="bi bi-shop"></i>

                            <strong>
                                No branch assignments
                            </strong>

                            <span>
                                This customer is not assigned to any branch.
                            </span>

                        </div>
                    )
            }

        </div>
    );
}


/*
|--------------------------------------------------------------------------
| Data Section
|--------------------------------------------------------------------------
*/

function DataSection({
    title,
    subtitle,
    icon,
    paginator,
    children,
}) {

    return (

        <div className="rr-customer-data-section">

            <div className="rr-customer-data-section-header">

                <div>

                    <i
                        className={
                            `bi ${icon}`
                        }
                    ></i>

                    <div>

                        <strong>
                            {
                                title
                            }
                        </strong>

                        <span>
                            {
                                subtitle
                            }
                        </span>

                    </div>

                </div>


                <span>
                    {
                        paginator?.total
                        ?? 0
                    } records
                </span>

            </div>


            <div className="rr-customer-account-table-wrap">

                {
                    children
                }

            </div>


            <Pagination
                paginator={
                    paginator
                }
            />

        </div>
    );
}


/*
|--------------------------------------------------------------------------
| Pagination
|--------------------------------------------------------------------------
*/

function Pagination({
    paginator,
}) {

    if (
        !paginator
        ||
        Number(
            paginator.last_page
            ?? 1
        ) <= 1
    ) {

        return null;
    }


    return (

        <div className="rr-customer-account-pagination">

            <span>

                Showing {
                    paginator.from
                    ?? 0
                }–{
                    paginator.to
                    ?? 0
                } of {
                    paginator.total
                    ?? 0
                }

            </span>


            <div>

                {
                    (
                        paginator.links
                        ?? []
                    ).map(
                        (
                            link,
                            index
                        ) => (

                            <PaginationLink
                                key={
                                    `${link.label}-${index}`
                                }
                                link={
                                    link
                                }
                            />

                        )
                    )
                }

            </div>

        </div>
    );
}


function PaginationLink({
    link,
}) {

    const label =
        String(
            link?.label
            ?? ''
        )
            .replace(
                '&laquo;',
                '«'
            )
            .replace(
                '&raquo;',
                '»'
            );


    if (
        !link?.url
    ) {

        return (

            <span className="rr-customer-account-page-link disabled">
                {
                    label
                }
            </span>
        );
    }


    return (

        <Link
            href={
                link.url
            }
            preserveScroll
            preserveState
            className={
                `rr-customer-account-page-link ${link.active
                    ? 'active'
                    : ''
                }`
            }
        >

            {
                label
            }

        </Link>
    );
}


/*
|--------------------------------------------------------------------------
| Components
|--------------------------------------------------------------------------
*/

function AccountStat({
    label,
    value,
    prefix = '',
    suffix = '',
    icon,
    tone = 'primary',
}) {

    return (

        <div
            className={
                `rr-customer-account-stat ${tone}`
            }
        >

            <div className="rr-customer-account-stat-icon">

                <i
                    className={
                        `bi ${icon}`
                    }
                ></i>

            </div>


            <div>

                <span>
                    {
                        label
                    }
                </span>

                <strong>

                    {
                        prefix
                    }{
                        value
                    }

                    {
                        suffix && (
                            <small>
                                {
                                    suffix
                                }
                            </small>
                        )
                    }

                </strong>

            </div>

        </div>
    );
}


function CardHeader({
    icon,
    title,
}) {

    return (

        <div className="rr-customer-account-card-header">

            <i
                className={
                    `bi ${icon}`
                }
            ></i>

            <strong>
                {
                    title
                }
            </strong>

        </div>
    );
}


function InfoRow({
    label,
    value,
}) {

    return (

        <div className="rr-customer-info-row">

            <span>
                {
                    label
                }
            </span>

            <strong>
                {
                    value
                    ?? '—'
                }
            </strong>

        </div>
    );
}


function AddressBlock({
    address,
    city,
    state,
    pincode,
    country,
}) {

    const lines =
        [
            address,
            [
                city,
                state,
            ]
                .filter(
                    Boolean
                )
                .join(
                    ', '
                ),
            pincode,
            country,
        ]
            .filter(
                Boolean
            );


    if (
        lines.length === 0
    ) {

        return (

            <span className="rr-account-muted">
                No address available.
            </span>
        );
    }


    return (

        <div className="rr-customer-address-block">

            {
                lines.map(
                    (
                        line,
                        index
                    ) => (

                        <span
                            key={
                                `${line}-${index}`
                            }
                        >
                            {
                                line
                            }
                        </span>

                    )
                )
            }

        </div>
    );
}


function TransactionBadge({
    type,
}) {

    const normalized =
        String(
            type
            ?? ''
        )
            .toLowerCase();


    return (

        <span
            className={
                `rr-transaction-badge ${normalized
                }`
            }
        >

            <i
                className={
                    normalized ===
                        'payment'
                        ? 'bi bi-arrow-down-left-circle'
                        : 'bi bi-arrow-up-right-circle'
                }
            ></i>

            {
                normalized ===
                    'payment'
                    ? 'Payment'
                    : 'Sale'
            }

        </span>
    );
}


function EmptyTable({
    colspan,
    text,
}) {

    return (

        <tr>

            <td
                colSpan={
                    colspan
                }
            >

                <div className="rr-account-table-empty">

                    <i className="bi bi-inbox"></i>

                    <span>
                        {
                            text
                        }
                    </span>

                </div>

            </td>

        </tr>
    );
}


/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function money(
    value
) {

    return Number(
        value
        ?? 0
    )
        .toLocaleString(
            'en-IN',
            {
                minimumFractionDigits:
                    2,

                maximumFractionDigits:
                    2,
            }
        );
}


function formatDate(
    value
) {

    if (
        !value
    ) {

        return '—';
    }


    const date =
        new Date(
            value
        );


    if (
        Number.isNaN(
            date.getTime()
        )
    ) {

        return value;
    }


    return date.toLocaleDateString(
        'en-IN',
        {
            day:
                '2-digit',

            month:
                'short',

            year:
                'numeric',
        }
    );
}


function getInitials(
    value
) {

    const text =
        String(
            value
            ?? ''
        )
            .trim();


    if (
        !text
    ) {

        return 'CU';
    }


    return text
        .split(
            /\s+/
        )
        .slice(
            0,
            2
        )
        .map(
            part =>
                part
                    .charAt(
                        0
                    )
                    .toUpperCase()
        )
        .join(
            ''
        );
}
