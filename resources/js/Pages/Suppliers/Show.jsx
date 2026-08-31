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

import ConfirmModal
    from '../../Components/ConfirmModal';

import '../../../css/business/suppliers.css';


export default function Show({
    supplier,
    account_summary: accountSummary,
    purchases,
    purchase_returns: purchaseReturns,
}) {

    const {
        auth,
        business,
    } = usePage().props;


    const user =
        auth?.user;


    const permissions =
        user?.permissions
        ?? auth?.permissions
        ?? [];


    const roles =
        user?.roles
        ?? auth?.roles
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


    /*
    |--------------------------------------------------------------------------
    | Tabs
    |--------------------------------------------------------------------------
    */

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
                        'bi-building',
                },
                {
                    key:
                        'purchases',

                    label:
                        'Purchases',

                    icon:
                        'bi-cart-check',

                    count:
                        purchases?.total
                        ?? 0,
                },
                {
                    key:
                        'returns',

                    label:
                        'Purchase Returns',

                    icon:
                        'bi-arrow-counterclockwise',

                    count:
                        purchaseReturns?.total
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
                        supplier?.stores_count
                        ?? 0,
                },
            ],
            [
                purchases?.total,
                purchaseReturns?.total,
                supplier?.stores_count,
            ]
        );


    /*
    |--------------------------------------------------------------------------
    | Confirmation
    |--------------------------------------------------------------------------
    */

    const [
        confirmAction,
        setConfirmAction,
    ] =
        useState(
            null
        );


    const [
        actionProcessing,
        setActionProcessing,
    ] =
        useState(
            false
        );


    /*
    |--------------------------------------------------------------------------
    | Navigation
    |--------------------------------------------------------------------------
    */

    const goBack =
        () => {

            if (
                window.history.length > 1
            ) {

                window.history.back();

                return;
            }


            router.visit(
                '/suppliers'
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Remove From Branch
    |--------------------------------------------------------------------------
    */

    const askRemoveFromBranch =
        () => {

            setConfirmAction({

                type:
                    'remove',

                title:
                    'Remove Supplier From This Branch?',

                message:
                    `${supplier?.supplier_name ?? 'This supplier'} will no longer be available in the current branch. The supplier master will remain available in other assigned branches.`,

                confirmText:
                    'Remove From Branch',

                icon:
                    'bi-diagram-2',

                variant:
                    'danger',
            });
        };


    /*
    |--------------------------------------------------------------------------
    | Delete Supplier
    |--------------------------------------------------------------------------
    */

    const askDeleteSupplier =
        () => {

            setConfirmAction({

                type:
                    'delete',

                title:
                    'Delete Supplier Permanently?',

                message:
                    `Are you sure you want to permanently delete ${supplier?.supplier_name ?? 'this supplier'}? The server will block deletion if purchase transactions exist.`,

                confirmText:
                    'Delete Supplier',

                icon:
                    'bi-trash3',

                variant:
                    'danger',
            });
        };


    /*
    |--------------------------------------------------------------------------
    | Confirm Action
    |--------------------------------------------------------------------------
    */

    const executeConfirmedAction =
        () => {

            if (
                !confirmAction ||
                actionProcessing
            ) {

                return;
            }


            setActionProcessing(
                true
            );


            const options = {

                preserveScroll:
                    true,

                onSuccess:
                    () => {

                        setConfirmAction(
                            null
                        );
                    },

                onFinish:
                    () => {

                        setActionProcessing(
                            false
                        );
                    },
            };


            if (
                confirmAction.type ===
                'remove'
            ) {

                router.delete(
                    `/suppliers/${supplier.id}/remove-from-branch`,
                    options
                );

                return;
            }


            if (
                confirmAction.type ===
                'delete'
            ) {

                router.delete(
                    `/suppliers/${supplier.id}`,
                    options
                );
            }
        };


    /*
    |--------------------------------------------------------------------------
    | Visibility
    |--------------------------------------------------------------------------
    */

    const canDelete =
        can(
            'supplier.delete'
        );


    const isShared =
        Boolean(
            supplier?.shared
        );


    return (

        <BusinessLayout>

            <Head
                title={
                    supplier?.supplier_name
                    ?? 'Supplier'
                }
            />


            <div className="rr-suppliers-page">

                <MasterNavigation
                    active="suppliers"
                    title="Supplier Account"
                    subtitle="Supplier profile, purchases, returns and branch assignments"
                />


                {/* =====================================================
                    HEADER
                   ===================================================== */}

                <section className="rr-supplier-account-header">

                    <div className="rr-supplier-account-header-main">

                        <button
                            type="button"
                            className="rr-supplier-account-back"
                            onClick={
                                goBack
                            }
                            title="Back"
                        >

                            <i className="bi bi-arrow-left"></i>

                        </button>


                        <div className="rr-supplier-account-avatar">

                            {
                                getInitials(
                                    supplier?.supplier_name
                                )
                            }

                        </div>


                        <div className="rr-supplier-account-identity">

                            <div className="rr-supplier-account-kicker">

                                <span>
                                    {
                                        supplier?.supplier_code
                                        ?? 'Supplier'
                                    }
                                </span>


                                {
                                    supplier?.shared && (

                                        <span className="rr-supplier-account-shared">

                                            <i className="bi bi-diagram-3"></i>

                                            {
                                                supplier?.stores_count
                                                ?? 0
                                            } Branches

                                        </span>

                                    )
                                }

                            </div>


                            <h1>
                                {
                                    supplier?.supplier_name
                                }
                            </h1>


                            <div className="rr-supplier-account-subline">

                                {
                                    supplier?.phone && (

                                        <span>

                                            <i className="bi bi-telephone"></i>

                                            {
                                                supplier.phone
                                            }

                                        </span>

                                    )
                                }


                                {
                                    supplier?.email && (

                                        <span>

                                            <i className="bi bi-envelope"></i>

                                            {
                                                supplier.email
                                            }

                                        </span>

                                    )
                                }


                                {
                                    (
                                        supplier?.city ||
                                        supplier?.state
                                    ) && (

                                        <span>

                                            <i className="bi bi-geo-alt"></i>

                                            {
                                                [
                                                    supplier?.city,
                                                    supplier?.state,
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


                    <div className="rr-supplier-account-header-actions">

                        <span
                            className={
                                `rr-supplier-status ${supplier?.status
                                    ? 'active'
                                    : 'inactive'
                                }`
                            }
                        >

                            <span></span>

                            {
                                supplier?.status
                                    ? 'Active'
                                    : 'Inactive'
                            }

                        </span>


                        {
                            can(
                                'supplier.edit'
                            ) && (

                                <Link
                                    href={
                                        `/suppliers/${supplier.id}/edit`
                                    }
                                    className="rr-supplier-primary-btn"
                                >

                                    <i className="bi bi-pencil"></i>

                                    Edit Supplier

                                </Link>

                            )
                        }


                        {
                            canDelete &&
                            isShared && (

                                <button
                                    type="button"
                                    className="rr-supplier-account-remove-btn"
                                    onClick={
                                        askRemoveFromBranch
                                    }
                                >

                                    <i className="bi bi-diagram-2"></i>

                                    Remove Branch

                                </button>

                            )
                        }


                        {
                            canDelete &&
                            !isShared && (

                                <button
                                    type="button"
                                    className="rr-supplier-account-delete-btn"
                                    onClick={
                                        askDeleteSupplier
                                    }
                                >

                                    <i className="bi bi-trash3"></i>

                                    Delete

                                </button>

                            )
                        }

                    </div>

                </section>


                {/* =====================================================
                    SHARED WARNING
                   ===================================================== */}

                {
                    supplier?.shared && (

                        <section className="rr-supplier-shared-warning">

                            <i className="bi bi-exclamation-triangle"></i>

                            <div>

                                <strong>
                                    Shared supplier
                                </strong>

                                <span>
                                    Supplier master details are shared across assigned branches, while purchases and returns shown here belong only to the active branch.
                                </span>

                            </div>

                        </section>

                    )
                }


                {/* =====================================================
                    SUMMARY
                   ===================================================== */}

                <section className="rr-supplier-account-summary">

                    <AccountStat
                        label="Current Payable"
                        value={
                            money(
                                accountSummary?.current_payable
                            )
                        }
                        prefix="₹"
                        icon="bi-wallet2"
                        tone="danger"
                    />

                    <AccountStat
                        label="Total Purchases"
                        value={
                            money(
                                accountSummary?.total_purchases
                            )
                        }
                        prefix="₹"
                        icon="bi-cart-check"
                        tone="primary"
                    />

                    <AccountStat
                        label="Purchase Returns"
                        value={
                            money(
                                accountSummary?.total_returns
                            )
                        }
                        prefix="₹"
                        icon="bi-arrow-counterclockwise"
                        tone="warning"
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
                        label="Credit Days"
                        value={
                            accountSummary?.credit_days
                            ?? 0
                        }
                        suffix="days"
                        icon="bi-calendar3"
                        tone="muted"
                    />

                </section>


                {/* =====================================================
                    TABS
                   ===================================================== */}

                <section className="rr-supplier-account-panel">

                    <div className="rr-supplier-account-tabs">

                        {
                            tabs.map(
                                tab => (

                                    <button
                                        key={
                                            tab.key
                                        }
                                        type="button"
                                        className={
                                            `rr-supplier-account-tab ${activeTab ===
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


                    <div className="rr-supplier-account-tab-content">

                        {
                            activeTab ===
                            'overview' && (

                                <OverviewTab
                                    supplier={
                                        supplier
                                    }
                                    accountSummary={
                                        accountSummary
                                    }
                                />

                            )
                        }


                        {
                            activeTab ===
                            'purchases' && (

                                <PurchasesTab
                                    purchases={
                                        purchases
                                    }
                                />

                            )
                        }


                        {
                            activeTab ===
                            'returns' && (

                                <ReturnsTab
                                    purchaseReturns={
                                        purchaseReturns
                                    }
                                />

                            )
                        }


                        {
                            activeTab ===
                            'branches' && (

                                <BranchesTab
                                    branches={
                                        supplier?.stores
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


                {/* =====================================================
                    COMMON CONFIRM MODAL
                   ===================================================== */}

                <ConfirmModal
                    show={
                        Boolean(
                            confirmAction
                        )
                    }
                    title={
                        confirmAction?.title
                        ?? 'Confirm Action'
                    }
                    message={
                        confirmAction?.message
                        ?? ''
                    }
                    confirmText={
                        confirmAction?.confirmText
                        ?? 'Confirm'
                    }
                    cancelText="Cancel"
                    icon={
                        confirmAction?.icon
                        ?? 'bi-exclamation-triangle'
                    }
                    variant={
                        confirmAction?.variant
                        ?? 'danger'
                    }
                    processing={
                        actionProcessing
                    }
                    onConfirm={
                        executeConfirmedAction
                    }
                    onCancel={
                        () => {

                            if (
                                !actionProcessing
                            ) {

                                setConfirmAction(
                                    null
                                );
                            }
                        }
                    }
                />

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
    supplier,
    accountSummary,
}) {

    return (

        <div className="rr-supplier-overview-grid">

            <section className="rr-supplier-account-card">

                <CardHeader
                    icon="bi-person"
                    title="Contact Details"
                />


                <div className="rr-supplier-account-card-body">

                    <InfoRow
                        label="Supplier Code"
                        value={
                            supplier?.supplier_code
                        }
                    />

                    <InfoRow
                        label="Contact Person"
                        value={
                            supplier?.contact_person
                        }
                    />

                    <InfoRow
                        label="Phone"
                        value={
                            supplier?.phone
                        }
                    />

                    <InfoRow
                        label="Alternate Phone"
                        value={
                            supplier?.alternate_phone
                        }
                    />

                    <InfoRow
                        label="Email"
                        value={
                            supplier?.email
                        }
                    />

                </div>

            </section>


            <section className="rr-supplier-account-card">

                <CardHeader
                    icon="bi-receipt"
                    title="Tax Details"
                />


                <div className="rr-supplier-account-card-body">

                    <InfoRow
                        label="GST Number"
                        value={
                            supplier?.gst_number
                        }
                    />

                    <InfoRow
                        label="PAN Number"
                        value={
                            supplier?.pan_number
                        }
                    />

                    <InfoRow
                        label="Status"
                        value={
                            supplier?.status
                                ? 'Active'
                                : 'Inactive'
                        }
                    />

                    <InfoRow
                        label="Assigned Branches"
                        value={
                            supplier?.stores_count
                            ?? 0
                        }
                    />

                </div>

            </section>


            <section className="rr-supplier-account-card">

                <CardHeader
                    icon="bi-geo-alt"
                    title="Registered Address"
                />


                <div className="rr-supplier-account-card-body">

                    <AddressBlock
                        supplier={
                            supplier
                        }
                    />

                </div>

            </section>


            <section className="rr-supplier-account-card">

                <CardHeader
                    icon="bi-wallet2"
                    title="Account Settings"
                />


                <div className="rr-supplier-account-card-body">

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
                        label="Current Payable"
                        value={
                            `₹${money(
                                accountSummary?.current_payable
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
                        label="Credit Days"
                        value={
                            `${accountSummary?.credit_days
                            ?? 0
                            } days`
                        }
                    />

                </div>

            </section>


            <section className="rr-supplier-account-card rr-supplier-account-card-wide">

                <CardHeader
                    icon="bi-clock-history"
                    title="Recent Purchase"
                />


                <div className="rr-supplier-account-card-body">

                    <InfoRow
                        label="Last Purchase"
                        value={
                            accountSummary?.last_purchase
                                ? (
                                    accountSummary.last_purchase.purchase_no
                                    ?? `Purchase #${accountSummary.last_purchase.id}`
                                )
                                : '—'
                        }
                    />

                    <InfoRow
                        label="Purchase Date"
                        value={
                            formatDate(
                                accountSummary?.last_purchase?.date
                            )
                        }
                    />

                    <InfoRow
                        label="Amount"
                        value={
                            accountSummary?.last_purchase
                                ? `₹${money(
                                    accountSummary.last_purchase.amount
                                )}`
                                : '—'
                        }
                    />

                </div>

            </section>


            {
                supplier?.remarks && (

                    <section className="rr-supplier-account-card rr-supplier-account-card-wide">

                        <CardHeader
                            icon="bi-chat-left-text"
                            title="Remarks"
                        />


                        <div className="rr-supplier-account-card-body">

                            <p className="rr-supplier-account-remarks">
                                {
                                    supplier.remarks
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
| Purchases
|--------------------------------------------------------------------------
*/

function PurchasesTab({
    purchases,
}) {

    const rows =
        purchases?.data
        ?? [];


    return (

        <DataSection
            title="Purchase History"
            subtitle="Purchases for this supplier in the active branch"
            icon="bi-cart-check"
            paginator={
                purchases
            }
        >

            <table className="rr-supplier-account-table">

                <thead>

                    <tr>

                        <th>
                            Purchase No
                        </th>

                        <th>
                            Date
                        </th>

                        <th className="text-end">
                            Sub Total
                        </th>

                        <th className="text-end">
                            GST
                        </th>

                        <th className="text-end">
                            Grand Total
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
                                purchase => (

                                    <tr
                                        key={
                                            purchase.id
                                        }
                                    >

                                        <td>

                                            <Link
                                                href={
                                                    `/purchases/${purchase.id}`
                                                }
                                                className="rr-supplier-reference-link"
                                            >

                                                {
                                                    purchase.purchase_no
                                                    ?? `Purchase #${purchase.id}`
                                                }

                                            </Link>

                                        </td>

                                        <td>
                                            {
                                                formatDate(
                                                    purchase.purchase_date
                                                )
                                            }
                                        </td>

                                        <td className="text-end">
                                            ₹{
                                                money(
                                                    purchase.sub_total
                                                )
                                            }
                                        </td>

                                        <td className="text-end">
                                            ₹{
                                                money(
                                                    purchase.gst_total
                                                )
                                            }
                                        </td>

                                        <td className="text-end rr-supplier-account-debit">
                                            ₹{
                                                money(
                                                    purchase.grand_total
                                                )
                                            }
                                        </td>

                                        <td>

                                            <span className="rr-supplier-purchase-status">

                                                {
                                                    purchase.status
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
                                    text="No purchases found for this supplier in the current branch."
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
| Purchase Returns
|--------------------------------------------------------------------------
*/

function ReturnsTab({
    purchaseReturns,
}) {

    const rows =
        purchaseReturns?.data
        ?? [];


    return (

        <DataSection
            title="Purchase Return History"
            subtitle="Returns linked through purchases in the active branch"
            icon="bi-arrow-counterclockwise"
            paginator={
                purchaseReturns
            }
        >

            <table className="rr-supplier-account-table">

                <thead>

                    <tr>

                        <th>
                            Return No
                        </th>

                        <th>
                            Date
                        </th>

                        <th className="text-end">
                            Sub Total
                        </th>

                        <th className="text-end">
                            GST
                        </th>

                        <th className="text-end">
                            Total Amount
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
                                row => (

                                    <tr
                                        key={
                                            row.id
                                        }
                                    >

                                        <td>

                                            <Link
                                                href={
                                                    `/purchase-returns/${row.id}`
                                                }
                                                className="rr-supplier-reference-link"
                                            >

                                                {
                                                    row.return_no
                                                    ?? `Return #${row.id}`
                                                }

                                            </Link>

                                        </td>

                                        <td>
                                            {
                                                formatDate(
                                                    row.return_date
                                                )
                                            }
                                        </td>

                                        <td className="text-end">
                                            ₹{
                                                money(
                                                    row.sub_total
                                                )
                                            }
                                        </td>

                                        <td className="text-end">
                                            ₹{
                                                money(
                                                    row.gst_total
                                                )
                                            }
                                        </td>

                                        <td className="text-end rr-supplier-account-credit">
                                            ₹{
                                                money(
                                                    row.total_amount
                                                )
                                            }
                                        </td>

                                        <td>
                                            {
                                                row.remarks
                                                ?? '—'
                                            }
                                        </td>

                                    </tr>

                                )
                            )
                            : (
                                <EmptyTable
                                    colspan="6"
                                    text="No purchase returns found for this supplier in the current branch."
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

        <div className="rr-supplier-branches-grid">

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
                                        `rr-supplier-branch-card ${current
                                            ? 'current'
                                            : ''
                                        }`
                                    }
                                >

                                    <div className="rr-supplier-branch-card-icon">

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


                                    <div className="rr-supplier-branch-card-status">

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
                        <div className="rr-supplier-empty-state">

                            <i className="bi bi-shop"></i>

                            <strong>
                                No branch assignments
                            </strong>

                            <span>
                                This supplier is not assigned to any branch.
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

        <div className="rr-supplier-data-section">

            <div className="rr-supplier-data-section-header">

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


            <div className="rr-supplier-account-table-wrap">

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

        <div className="rr-supplier-account-pagination">

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

            <span className="rr-supplier-account-page-link disabled">
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
                `rr-supplier-account-page-link ${link.active
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
                `rr-supplier-account-stat ${tone}`
            }
        >

            <div className="rr-supplier-account-stat-icon">

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

        <div className="rr-supplier-account-card-header">

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

        <div className="rr-supplier-info-row">

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
    supplier,
}) {

    const lines =
        [
            supplier?.address,

            [
                supplier?.city,
                supplier?.state,
            ]
                .filter(
                    Boolean
                )
                .join(
                    ', '
                ),

            supplier?.pincode,

            supplier?.country,
        ]
            .filter(
                Boolean
            );


    if (
        lines.length === 0
    ) {

        return (

            <span className="rr-supplier-account-muted">
                No address available.
            </span>
        );
    }


    return (

        <div className="rr-supplier-address-block">

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

                <div className="rr-supplier-table-empty">

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

        return 'SU';
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
