import {
    Link,
    usePage,
} from '@inertiajs/react';

import {
    useEffect,
    useMemo,
    useRef,
    useState,
} from 'react';


export default function BusinessSidebar({
    open = false,
    collapsed = false,
    onClose = null,
    onToggleCollapse = null,
}) {

    /*
    |--------------------------------------------------------------------------
    | Shared Props
    |--------------------------------------------------------------------------
    */

    const page =
        usePage();


    const {
        auth,
        business,
    } = page.props;


    const currentUrl =
        page.url
        ?? '/';


    const [
        locallyCollapsed,
        setLocallyCollapsed,
    ] = useState(
        Boolean(collapsed)
    );


    const [
        hoverExpanded,
        setHoverExpanded,
    ] = useState(false);


    const hoverLeaveTimer =
        useRef(null);


    useEffect(
        () => {
            setLocallyCollapsed(
                Boolean(collapsed)
            );

            setHoverExpanded(false);
        },
        [collapsed]
    );


    useEffect(
        () => {
            return () => {
                if (hoverLeaveTimer.current) {
                    window.clearTimeout(
                        hoverLeaveTimer.current
                    );
                }
            };
        },
        []
    );


    const pinnedCollapsed =
        locallyCollapsed;


    const displayCollapsed =
        pinnedCollapsed
        &&
        !hoverExpanded;


    const toggleSidebar =
        event => {
            event.preventDefault();
            event.stopPropagation();

            if (hoverLeaveTimer.current) {
                window.clearTimeout(
                    hoverLeaveTimer.current
                );

                hoverLeaveTimer.current = null;
            }

            const next =
                !pinnedCollapsed;

            setLocallyCollapsed(next);
            setHoverExpanded(false);

            if (
                typeof onToggleCollapse
                === 'function'
            ) {
                onToggleCollapse(next);
            }
        };


    const handleMouseEnter =
        () => {
            if (hoverLeaveTimer.current) {
                window.clearTimeout(
                    hoverLeaveTimer.current
                );

                hoverLeaveTimer.current = null;
            }

            if (pinnedCollapsed) {
                setHoverExpanded(true);
            }

        };


    const handleMouseLeave =
        () => {
            /*
            | Hover behavior is allowed only while permanently collapsed.
            | A sidebar opened by the toggle must ignore mouse leave.
            */
            if (!pinnedCollapsed) {
                return;
            }

            if (hoverLeaveTimer.current) {
                window.clearTimeout(
                    hoverLeaveTimer.current
                );
            }

            hoverLeaveTimer.current =
                window.setTimeout(
                    () => {
                        setHoverExpanded(false);
                        hoverLeaveTimer.current = null;
                    },
                    280
                );

        };


    const user =
        auth?.user;


    const permissions =
        user?.permissions
        ?? [];


    const roles =
        user?.roles
        ?? [];


    /*
    |--------------------------------------------------------------------------
    | Access Helpers
    |--------------------------------------------------------------------------
    */

    const isSuperAdmin =
        Boolean(
            user?.is_super_user
        );


    const isCompanyAdmin =
        roles.includes(
            'Company Admin'
        );


    const can =
        (
            permission
        ) => {

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


    const canAny =
        (
            permissionList
        ) => {

            if (
                isSuperAdmin ||
                isCompanyAdmin
            ) {

                return true;
            }


            return permissionList.some(
                (
                    permission
                ) =>
                    permissions.includes(
                        permission
                    )
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Menu State
    |--------------------------------------------------------------------------
    */

    const getGroupFromUrl =
        url => {

            const path =
                String(
                    url
                    ?? ''
                )
                    .split(
                        '?'
                    )[0];


            if (
                path.startsWith(
                    '/company/'
                )
                ||
                path.startsWith(
                    '/companies'
                )
                ||
                path.startsWith(
                    '/stores'
                )
            ) {

                return 'administration';
            }


            if (
                path.startsWith(
                    '/customers'
                )
                ||
                path.startsWith(
                    '/suppliers'
                )
                ||
                path.startsWith(
                    '/products'
                )
                ||
                path.startsWith(
                    '/categories'
                )
                ||
                path.startsWith(
                    '/brands'
                )
                ||
                path.startsWith(
                    '/units'
                )
                ||
                path.startsWith(
                    '/godowns'
                )
            ) {

                return 'masters';
            }


            if (
                path.startsWith(
                    '/purchases'
                )
                ||
                path.startsWith(
                    '/purchase-returns'
                )
            ) {

                return 'purchase';
            }


            if (
                path.startsWith(
                    '/sales'
                )
                ||
                path.startsWith(
                    '/sales-returns'
                )
                ||
                path.startsWith(
                    '/quotations'
                )
            ) {

                return 'sales';
            }


            if (
                path.startsWith(
                    '/inventory'
                )
                ||
                path.startsWith(
                    '/stock-ledgers'
                )
                ||
                path.startsWith(
                    '/stock-adjustments'
                )
                ||
                path.startsWith(
                    '/stock-transfers'
                )
            ) {

                return 'inventory';
            }


            if (
                path.startsWith(
                    '/customer-payments'
                )
                ||
                path.startsWith(
                    '/customer-ledger'
                )
                ||
                path.startsWith(
                    '/mechanic-accounts'
                )
                ||
                path.startsWith(
                    '/mechanic-ledgers'
                )
                ||
                path.startsWith(
                    '/supplier-payments'
                )
                ||
                path.startsWith(
                    '/supplier-ledger'
                )
                ||
                path.startsWith(
                    '/expenses'
                )
                ||
                path.startsWith(
                    '/accounts'
                )
            ) {

                return 'accounts';
            }


            if (
                path.startsWith(
                    '/reports'
                )
                ||
                path.startsWith(
                    '/purchase-reports'
                )
            ) {

                return 'reports';
            }


            return null;
        };


    const createGroupState =
        activeGroup => ({

            masters:
                activeGroup ===
                'masters',

            purchase:
                activeGroup ===
                'purchase',

            sales:
                activeGroup ===
                'sales',

            inventory:
                activeGroup ===
                'inventory',

            accounts:
                activeGroup ===
                'accounts',

            reports:
                activeGroup ===
                'reports',

            administration:
                activeGroup ===
                'administration',
        });


    const [
        openGroups,
        setOpenGroups,
    ] =
        useState(
            () =>
                createGroupState(
                    getGroupFromUrl(
                        currentUrl
                    )
                )
        );


    useEffect(
        () => {

            const activeGroup =
                getGroupFromUrl(
                    currentUrl
                );


            if (
                !activeGroup
            ) {

                return;
            }


            setOpenGroups(
                createGroupState(
                    activeGroup
                )
            );

        },
        [
            currentUrl,
        ]
    );


    const toggleGroup =
        group => {

            setOpenGroups(
                current => {

                    const willOpen =
                        !current[group];


                    if (
                        !willOpen
                    ) {

                        return createGroupState(
                            null
                        );
                    }


                    return createGroupState(
                        group
                    );
                }
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Menu Definition
    |--------------------------------------------------------------------------
    */

    const menu =
        useMemo(
            () => [

                {
                    type: 'link',
                    label: 'Dashboard',
                    icon: 'bi-grid-1x2',
                    href: '/business/dashboard',
                    visible: can('dashboard.view'),
                },

                {
                    type: 'group',
                    key: 'administration',
                    label: 'Company',
                    icon: 'bi-buildings',
                    visible:
                        isSuperAdmin ||
                        isCompanyAdmin,
                    children: [
                        {
                            label: 'Company Profile',
                            icon: 'bi-building-gear',
                            href: '/company/profile',
                            visible: can('company.view'),
                        },
                        {
                            label: 'Branches',
                            icon: 'bi-shop-window',
                            href: '/company/branches',
                            activePrefix: '/company/branches',
                            activeExclude: ['/company/branches/create'],
                            visible: can('store.view'),
                        },
                        {
                            label: 'Add Branch',
                            icon: 'bi-plus-circle',
                            href: '/company/branches/create',
                            exact: true,
                            visible: can('store.create'),
                        },
                        {
                            label: 'Users & Access',
                            icon: 'bi-people',
                            href: '/company/users',
                            activePrefix: '/company/users',
                            activeExclude: ['/company/users/create'],
                            visible: can('users.view'),
                        },
                        {
                            label: 'Add User',
                            icon: 'bi-person-plus',
                            href: '/company/users/create',
                            exact: true,
                            visible: can('users.create'),
                        },
                        {
                            label: 'Roles & Permissions',
                            icon: 'bi-shield-lock',
                            href: '/company/roles',
                            activePrefix: '/company/roles',
                            activeExclude: ['/company/roles/create'],
                            visible: can('roles.view'),
                        },
                        {
                            label: 'Add Role',
                            icon: 'bi-shield-plus',
                            href: '/company/roles/create',
                            exact: true,
                            visible: can('roles.create'),
                        },
                    ],
                },

                {
                    type: 'group',
                    key: 'masters',
                    label: 'Masters',
                    icon: 'bi-database',
                    visible: canAny([
                        'customer.view',
                        'supplier.view',
                        'product.view',
                        'category.view',
                        'brand.view',
                        'unit.view',
                        'godown.view',
                    ]),
                    children: [
                        {
                            label: 'Customers',
                            icon: 'bi-person-vcard',
                            href: '/customers',
                            visible: can('customer.view'),
                        },
                        {
                            label: 'Suppliers',
                            icon: 'bi-truck',
                            href: '/suppliers',
                            visible: can('supplier.view'),
                        },
                        {
                            label: 'Products',
                            icon: 'bi-box-seam',
                            href: '/products',
                            visible: can('product.view'),
                        },
                        {
                            label: 'Categories',
                            icon: 'bi-tags',
                            href: '/categories',
                            visible: can('category.view'),
                        },
                        {
                            label: 'Brands',
                            icon: 'bi-award',
                            href: '/brands',
                            visible: can('brand.view'),
                        },
                        {
                            label: 'Units',
                            icon: 'bi-rulers',
                            href: '/units',
                            visible: can('unit.view'),
                        },
                        {
                            label: 'Godowns',
                            icon: 'bi-building',
                            href: '/godowns',
                            visible: can('godown.view'),
                        },
                    ],
                },

                {
                    type: 'group',
                    key: 'purchase',
                    label: 'Purchase',
                    icon: 'bi-bag-check',
                    visible: canAny([
                        'purchase.view',
                        'purchase.create',
                        'purchase-return.view',
                        'purchase-return.create',
                    ]),
                    children: [
                        {
                            label: 'Purchases',
                            icon: 'bi-receipt',
                            href: '/purchases',
                            activePrefix: '/purchases',
                            activeExclude: ['/purchases/create'],
                            visible: can('purchase.view'),
                        },
                        {
                            label: 'New Purchase',
                            icon: 'bi-plus-square',
                            href: '/purchases/create',
                            exact: true,
                            visible: can('purchase.create'),
                        },
                        {
                            label: 'Purchase Returns',
                            icon: 'bi-arrow-return-left',
                            href: '/purchase-returns',
                            activePrefix: '/purchase-returns',
                            visible: can('purchase-return.view'),
                        },
                    ],
                },

                {
                    type: 'group',
                    key: 'sales',
                    label: 'Sales',
                    icon: 'bi-cart-check',
                    visible: canAny([
                        'sales.view',
                        'sales.create',
                        'sales-return.view',
                        'sales-return.create',
                        'quotation.view',
                        'quotation.create',
                    ]),
                    children: [
                        {
                            label: 'Sales',
                            icon: 'bi-receipt-cutoff',
                            href: '/sales',
                            activePrefix: '/sales',
                            activeExclude: [
                                '/sales/create',
                                '/sales-returns',
                                '/reports/sales',
                            ],
                            visible: can('sales.view'),
                        },
                        {
                            label: 'New Sale',
                            icon: 'bi-plus-square',
                            href: '/sales/create',
                            exact: true,
                            visible: can('sales.create'),
                        },
                        {
                            label: 'Sales Returns',
                            icon: 'bi-arrow-return-right',
                            href: '/sales-returns',
                            activePrefix: '/sales-returns',
                            visible: can('sales-return.view'),
                        },
                        {
                            label: 'Quotations',
                            icon: 'bi-file-earmark-text',
                            href: '/quotations',
                            activePrefix: '/quotations',
                            visible: can('quotation.view'),
                        },
                    ],
                },

                {
                    type: 'group',
                    key: 'inventory',
                    label: 'Inventory',
                    icon: 'bi-boxes',
                    visible: canAny([
                        'current-stock.view',
                        'stock-ledger.view',
                        'stock-adjustment.view',
                        'stock-adjustment.create',
                        'stock-transfer.view',
                        'stock-transfer.create',
                        'stock.view',
                        'stock.adjust',
                    ]),
                    children: [
                        {
                            label: 'Current Stock',
                            icon: 'bi-box-seam',
                            href: '/inventory/current-stock',
                            activePrefix: '/inventory/current-stock',
                            visible: can('current-stock.view'),
                        },
                        {
                            label: 'Stock Ledger',
                            icon: 'bi-journal-text',
                            href: '/stock-ledgers',
                            activePrefix: '/stock-ledgers',
                            visible: can('stock-ledger.view'),
                        },
                        {
                            label: 'Stock Adjustment',
                            icon: 'bi-sliders',
                            href: '/stock-adjustments',
                            activePrefix: '/stock-adjustments',
                            visible: can('stock-adjustment.view') || can('stock-adjustment.create'),
                        },
                        {
                            label: 'Stock Transfer',
                            icon: 'bi-arrow-left-right',
                            href: '/stock-transfers',
                            activePrefix: '/stock-transfers',
                            visible: can('stock-transfer.view') || can('stock-transfer.create'),
                        },
                    ],
                },

                {
                    type: 'group',
                    key: 'accounts',
                    label: 'Accounts',
                    icon: 'bi-wallet2',
                    visible: canAny([
                        'accounts.view',
                        'accounts.dashboard.view',
                        'customer-payment.view',
                        'customer-payment.create',
                        'customer-ledger.view',
                        'supplier-payment.view',
                        'supplier-ledger.view',
                        'mechanic-account.view',
                        'mechanic-ledger.view',
                        'account.view',
                        'voucher.view',
                        'expense.view',
                        'accounts-report.view',
                    ]),
                    children: [
                        {
                            label: 'Accounts Dashboard',
                            icon: 'bi-speedometer2',
                            href: '/accounts/dashboard',
                            exact: true,
                            visible:
                                can('accounts.dashboard.view')
                                || can('accounts.view'),
                        },
                        {
                            label: 'Customer Payments',
                            icon: 'bi-cash-coin',
                            href: '/customer-payments',
                            activePrefix: '/customer-payments',
                            visible: can('customer-payment.view') || can('customer-payment.create'),
                        },
                        {
                            label: 'Customer Ledger',
                            icon: 'bi-journal',
                            href: '/customer-ledger',
                            visible: can('customer-ledger.view'),
                        },
                        {
                            label: 'Supplier Payments',
                            icon: 'bi-cash-stack',
                            href: '/supplier-payments',
                            activePrefix: '/supplier-payments',
                            visible: can('supplier-payment.view'),
                        },
                        {
                            label: 'Supplier Ledger',
                            icon: 'bi-journal-bookmark',
                            href: '/supplier-ledger',
                            activePrefix: '/supplier-ledger',
                            visible: can('supplier-ledger.view'),
                        },
                        {
                            label: 'Mechanic Accounts',
                            icon: 'bi-person-workspace',
                            href: '/mechanic-accounts',
                            activePrefix: '/mechanic-accounts',
                            visible: can('mechanic-account.view'),
                        },
                        {
                            label: 'Mechanic Ledger',
                            icon: 'bi-journal-check',
                            href: '/mechanic-ledgers',
                            activePrefix: '/mechanic-ledgers',
                            visible: can('mechanic-ledger.view'),
                        },
                        {
                            label: 'Cash & Bank Accounts',
                            icon: 'bi-bank',
                            href: '/accounts/cash-bank',
                            activePrefix: '/accounts/cash-bank',
                            visible: can('account.view'),
                        },
                        {
                            label: 'Receipts & Payments',
                            icon: 'bi-arrow-left-right',
                            href: '/accounts/vouchers',
                            activePrefix: '/accounts/vouchers',
                            visible: can('voucher.view'),
                        },
                        {
                            label: 'Expenses',
                            icon: 'bi-wallet',
                            href: '/expenses',
                            activePrefix: '/expenses',
                            visible: can('expense.view'),
                        },
                        {
                            label: 'Outstanding Reports',
                            icon: 'bi-person-exclamation',
                            href: '/accounts/outstanding',
                            activePrefix: '/accounts/outstanding',
                            visible: can('accounts-report.view'),
                        },
                        {
                            label: 'Cash / Bank Book',
                            icon: 'bi-book',
                            href: '/accounts/cash-bank-book',
                            activePrefix: '/accounts/cash-bank-book',
                            visible: can('accounts-report.view'),
                        },
                        {
                            label: 'Day Book',
                            icon: 'bi-calendar2-week',
                            href: '/accounts/day-book',
                            activePrefix: '/accounts/day-book',
                            visible: can('accounts-report.view'),
                        },
                    ],
                },

                {
                    type: 'group',
                    key: 'reports',
                    label: 'Reports',
                    icon: 'bi-bar-chart-line',
                    visible: canAny([
                        'reports.view',
                        'purchase-report.view',
                        'sales-report.view',
                        'current-stock.view',
                    ]),
                    children: [
                        {
                            label: 'Purchase Report',
                            icon: 'bi-graph-down-arrow',
                            href: '/purchase-reports',
                            activePrefix: '/purchase-reports',
                            visible: can('purchase-report.view'),
                        },
                        {
                            label: 'Sales Report',
                            icon: 'bi-graph-up-arrow',
                            href: '/reports/sales',
                            activePrefix: '/reports/sales',
                            visible: can('sales-report.view'),
                        },
                        {
                            label: 'Current Stock Report',
                            icon: 'bi-bar-chart',
                            href: '/inventory/current-stock',
                            activePrefix: '/inventory/current-stock',
                            visible: can('reports.view') || can('current-stock.view'),
                        },
                    ],
                },

            ],
            [
                permissions,
                roles,
                isSuperAdmin,
                isCompanyAdmin,
            ]
        );


    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */

    return (

        <aside
            className={
                `rr-business-sidebar ` +
                `${open ? 'mobile-open' : ''} ` +
                `${displayCollapsed ? 'collapsed' : ''} ` +
                `${hoverExpanded ? 'hover-expanded' : ''}`
            }
            onMouseEnter={
                handleMouseEnter
            }
            onMouseLeave={
                handleMouseLeave
            }
        >

            <button
                type="button"
                className="rr-business-sidebar-collapse-btn"
                data-collapsed={
                    pinnedCollapsed
                        ? 'true'
                        : 'false'
                }
                title={
                    pinnedCollapsed
                        ? 'Keep sidebar expanded'
                        : 'Collapse sidebar'
                }
                aria-label={
                    pinnedCollapsed
                        ? 'Expand sidebar'
                        : 'Collapse sidebar'
                }
                aria-expanded={!pinnedCollapsed}
                onPointerDown={toggleSidebar}
                onClick={event => {
                    /* Keyboard-generated clicks have detail 0. */
                    if (event.detail === 0) {
                        toggleSidebar(event);
                    }
                }}
            >
                <i
                    className={
                        `bi ${pinnedCollapsed
                            ? 'bi-chevron-double-right'
                            : 'bi-chevron-double-left'
                        }`
                    }
                ></i>
            </button>

            {/* =========================================================
                BUSINESS IDENTITY
               ========================================================= */}

            <div className="rr-business-sidebar-company">

                <div className="rr-business-sidebar-company-icon">

                    {
                        getInitials(
                            business?.company_name
                        )
                    }

                </div>


                {
                    !displayCollapsed && (

                        <div className="rr-business-sidebar-company-info">

                            <strong>
                                {
                                    business?.company_name
                                    ?? 'Company'
                                }
                            </strong>


                            <span>

                                <i className="bi bi-shop"></i>

                                {
                                    business?.store_name
                                    ?? 'No Branch Selected'
                                }

                            </span>


                            {
                                (
                                    business?.company_code ||
                                    business?.store_code
                                ) && (

                                    <small>

                                        {
                                            business?.company_code
                                            ?? ''
                                        }

                                        {
                                            business?.company_code &&
                                                business?.store_code
                                                ? ' • '
                                                : ''
                                        }

                                        {
                                            business?.store_code
                                            ?? ''
                                        }

                                    </small>

                                )
                            }

                        </div>

                    )
                }

            </div>


            {/* =========================================================
                NAVIGATION
               ========================================================= */}

            <nav className="rr-business-sidebar-nav">

                {
                    menu
                        .filter(
                            (
                                item
                            ) =>
                                item.visible
                        )
                        .map(
                            (
                                item
                            ) => {

                                if (
                                    item.type ===
                                    'link'
                                ) {

                                    return (

                                        <SidebarLink
                                            key={
                                                item.label
                                            }
                                            item={
                                                item
                                            }
                                            collapsed={
                                                displayCollapsed
                                            }
                                            onClose={
                                                onClose
                                            }
                                        />

                                    );
                                }


                                const children =
                                    item.children
                                        ?.filter(
                                            (
                                                child
                                            ) =>
                                                child.visible
                                        )
                                    ?? [];


                                if (
                                    !children.length
                                ) {

                                    return null;
                                }


                                return (

                                    <SidebarGroup
                                        key={
                                            item.key
                                        }
                                        item={
                                            item
                                        }
                                        children={
                                            children
                                        }
                                        open={
                                            openGroups[
                                            item.key
                                            ]
                                        }
                                        collapsed={
                                            displayCollapsed
                                        }
                                        onToggle={
                                            () =>
                                                toggleGroup(
                                                    item.key
                                                )
                                        }
                                        onClose={
                                            onClose
                                        }
                                    />

                                );
                            }
                        )
                }

            </nav>


            {/* =========================================================
                PROFILE
               ========================================================= */}

            <div className="rr-business-sidebar-footer">

                <Link
                    href="/profile"
                    className="rr-business-sidebar-profile"
                    onClick={
                        onClose
                    }
                >

                    <div className="rr-business-sidebar-avatar">

                        {
                            getInitials(
                                user?.name
                            )
                        }

                    </div>


                    {
                        !displayCollapsed && (

                            <div className="rr-business-sidebar-profile-info">

                                <strong>
                                    {
                                        user?.name
                                        ?? 'User'
                                    }
                                </strong>


                                <span>
                                    {
                                        isSuperAdmin
                                            ? 'Super Administrator'
                                            : roles[0]
                                            ?? 'Business User'
                                    }
                                </span>

                            </div>

                        )
                    }

                </Link>

            </div>

        </aside>
    );
}


/*
|--------------------------------------------------------------------------
| Sidebar Link
|--------------------------------------------------------------------------
*/

function SidebarLink({
    item,
    collapsed,
    onClose,
}) {

    const active =
        isItemActive(
            item
        );


    return (

        <Link
            href={
                item.href
            }
            className={
                `rr-business-sidebar-link ${active
                    ? 'active'
                    : ''
                }`
            }
            title={
                collapsed
                    ? item.label
                    : undefined
            }
            onClick={
                onClose
            }
        >

            <i className={`bi ${item.icon}`}></i>


            {
                !collapsed && (

                    <span>
                        {item.label}
                    </span>

                )
            }

        </Link>
    );
}


/*
|--------------------------------------------------------------------------
| Sidebar Group
|--------------------------------------------------------------------------
*/

function SidebarGroup({
    item,
    children,
    open,
    collapsed,
    onToggle,
    onClose,
}) {

    const childActive =
        children.some(
            (
                child
            ) =>
                isItemActive(
                    child
                )
        );


    return (

        <div
            className={
                `rr-business-sidebar-group ${childActive
                    ? 'has-active'
                    : ''
                }`
            }
        >

            <button
                type="button"
                className={
                    `rr-business-sidebar-group-button ${childActive
                        ? 'active'
                        : ''
                    }`
                }
                onClick={
                    onToggle
                }
                title={
                    collapsed
                        ? item.label
                        : undefined
                }
            >

                <i className={`bi ${item.icon}`}></i>


                {
                    !collapsed && (

                        <>

                            <span>
                                {item.label}
                            </span>


                            <i
                                className={
                                    `bi bi-chevron-down rr-business-sidebar-chevron ${open
                                        ? 'open'
                                        : ''
                                    }`
                                }
                            ></i>

                        </>

                    )
                }

            </button>


            {
                !collapsed &&
                open && (

                    <div className="rr-business-sidebar-submenu">

                        {
                            children.map(
                                (
                                    child
                                ) => (

                                    <SidebarLink
                                        key={
                                            child.label
                                        }
                                        item={
                                            child
                                        }
                                        collapsed={
                                            false
                                        }
                                        onClose={
                                            onClose
                                        }
                                    />

                                )
                            )
                        }

                    </div>

                )
            }

        </div>
    );
}


/*
|--------------------------------------------------------------------------
| Menu Item Active State
|--------------------------------------------------------------------------
*/

function isItemActive(
    item
) {

    if (
        !item?.href
    ) {
        return false;
    }

    if (
        typeof window ===
        'undefined'
    ) {
        return false;
    }

    const pathname =
        window.location.pathname;

    if (
        item.exact
    ) {
        return pathname ===
            item.href;
    }

    const excluded =
        (
            item.activeExclude
            ?? []
        ).some(
            path =>
                pathname === path
                ||
                pathname.startsWith(
                    `${path}/`
                )
        );

    if (
        excluded
    ) {
        return false;
    }

    if (
        item.activePrefix
    ) {
        return pathname ===
            item.activePrefix
            ||
            pathname.startsWith(
                `${item.activePrefix}/`
            );
    }

    if (
        item.href ===
        '/business/dashboard'
        ||
        item.href ===
        '/company/dashboard'
    ) {
        return pathname ===
            item.href;
    }

    return pathname ===
        item.href
        ||
        pathname.startsWith(
            `${item.href}/`
        );
}


/*
|--------------------------------------------------------------------------
| User Initials
|--------------------------------------------------------------------------
*/

function getInitials(
    name
) {

    if (
        !name
    ) {

        return 'U';
    }


    const parts =
        String(
            name
        )
            .trim()
            .split(
                /\s+/
            )
            .filter(
                Boolean
            );


    if (
        parts.length === 1
    ) {

        return parts[0]
            .substring(
                0,
                2
            )
            .toUpperCase();
    }


    return (
        parts[0][0]
        +
        parts[
        parts.length - 1
        ][0]
    ).toUpperCase();
}
