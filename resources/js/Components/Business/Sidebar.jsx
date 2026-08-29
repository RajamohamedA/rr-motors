import {
    Link,
    usePage,
} from '@inertiajs/react';

import {
    useMemo,
    useState,
} from 'react';


export default function BusinessSidebar({
    open = false,
    collapsed = false,
    onClose = null,
    onMouseEnter = null,
    onMouseLeave = null,
}) {

    /*
    |--------------------------------------------------------------------------
    | Shared Props
    |--------------------------------------------------------------------------
    */

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

    const [
        openGroups,
        setOpenGroups,
    ] = useState({
        masters:
            false,

        purchase:
            false,

        sales:
            false,

        inventory:
            false,

        accounts:
            false,

        reports:
            false,

        administration:
            true,
    });


    const toggleGroup =
        (
            group
        ) => {

            setOpenGroups(
                (
                    current
                ) => ({

                    ...current,

                    [group]:
                        !current[group],

                })
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

                /*
                |--------------------------------------------------------------------------
                | Dashboard
                |--------------------------------------------------------------------------
                */

                {
                    type:
                        'link',

                    label:
                        'Dashboard',

                    icon:
                        'bi-grid-1x2',

                    href:
                        '/business/dashboard',

                    visible:
                        can(
                            'dashboard.view'
                        ),
                },


                /*
                |--------------------------------------------------------------------------
                | Company Administration
                |--------------------------------------------------------------------------
                */

                {
                    type:
                        'group',

                    key:
                        'administration',

                    label:
                        'Company',

                    icon:
                        'bi-buildings',

                    visible:
                        isCompanyAdmin,

                    children: [

                        {
                            label:
                                'Company Profile',

                            icon:
                                'bi-building-gear',

                            href:
                                '/company/profile',

                            visible:
                                true,
                        },

                        {
                            label:
                                'Branches',

                            icon:
                                'bi-shop-window',

                            href:
                                '/company/branches',

                            visible:
                                true,
                        },

                        {
                            label:
                                'Add Branch',

                            icon:
                                'bi-plus-circle',

                            href:
                                '/company/branches/create',

                            visible:
                                true,
                        },

                        {
                            label:
                                'Users & Access',

                            icon:
                                'bi-people',

                            href:
                                '/company/users',

                            visible:
                                true,
                        },

                        {
                            label:
                                'Add User',

                            icon:
                                'bi-person-plus',

                            href:
                                '/company/users/create',

                            visible:
                                true,
                        },

                    ],
                },


                /*
                |--------------------------------------------------------------------------
                | Masters
                |--------------------------------------------------------------------------
                */

                {
                    type:
                        'group',

                    key:
                        'masters',

                    label:
                        'Masters',

                    icon:
                        'bi-database',

                    visible:
                        canAny([
                            'customer.view',
                            'supplier.view',
                            'product.view',
                            'category.view',
                            'brand.view',
                            'unit.view',
                        ]),

                    children: [

                        {
                            label:
                                'Customers',

                            icon:
                                'bi-person-vcard',

                            href:
                                '/customers',

                            visible:
                                can(
                                    'customer.view'
                                ),
                        },

                        {
                            label:
                                'Suppliers',

                            icon:
                                'bi-truck',

                            href:
                                '/suppliers',

                            visible:
                                can(
                                    'supplier.view'
                                ),
                        },

                        {
                            label:
                                'Products',

                            icon:
                                'bi-box-seam',

                            href:
                                '/products',

                            visible:
                                can(
                                    'product.view'
                                ),
                        },

                        {
                            label:
                                'Categories',

                            icon:
                                'bi-tags',

                            href:
                                '/categories',

                            visible:
                                can(
                                    'category.view'
                                ),
                        },

                        {
                            label:
                                'Brands',

                            icon:
                                'bi-award',

                            href:
                                '/brands',

                            visible:
                                can(
                                    'brand.view'
                                ),
                        },

                        {
                            label:
                                'Units',

                            icon:
                                'bi-rulers',

                            href:
                                '/units',

                            visible:
                                can(
                                    'unit.view'
                                ),
                        },

                    ],
                },


                /*
                |--------------------------------------------------------------------------
                | Purchase
                |--------------------------------------------------------------------------
                */

                {
                    type:
                        'group',

                    key:
                        'purchase',

                    label:
                        'Purchase',

                    icon:
                        'bi-bag-check',

                    visible:
                        canAny([
                            'purchase.view',
                            'purchase.create',
                        ]),

                    children: [

                        {
                            label:
                                'Purchases',

                            icon:
                                'bi-receipt',

                            href:
                                '/purchases',

                            visible:
                                can(
                                    'purchase.view'
                                ),
                        },

                        {
                            label:
                                'New Purchase',

                            icon:
                                'bi-plus-square',

                            href:
                                '/purchases/create',

                            visible:
                                can(
                                    'purchase.create'
                                ),
                        },

                        {
                            label:
                                'Purchase Returns',

                            icon:
                                'bi-arrow-return-left',

                            href:
                                '/purchase-returns',

                            visible:
                                can(
                                    'purchase.view'
                                ),
                        },

                    ],
                },


                /*
                |--------------------------------------------------------------------------
                | Sales
                |--------------------------------------------------------------------------
                */

                {
                    type:
                        'group',

                    key:
                        'sales',

                    label:
                        'Sales',

                    icon:
                        'bi-cart-check',

                    visible:
                        canAny([
                            'sales.view',
                            'sales.create',
                            'quotation.view',
                        ]),

                    children: [

                        {
                            label:
                                'Sales',

                            icon:
                                'bi-receipt-cutoff',

                            href:
                                '/sales',

                            visible:
                                can(
                                    'sales.view'
                                ),
                        },

                        {
                            label:
                                'New Sale',

                            icon:
                                'bi-plus-square',

                            href:
                                '/sales/create',

                            visible:
                                can(
                                    'sales.create'
                                ),
                        },

                        {
                            label:
                                'Sales Returns',

                            icon:
                                'bi-arrow-return-right',

                            href:
                                '/sales-returns',

                            visible:
                                can(
                                    'sales.view'
                                ),
                        },

                        {
                            label:
                                'Quotations',

                            icon:
                                'bi-file-earmark-text',

                            href:
                                '/quotations',

                            visible:
                                can(
                                    'quotation.view'
                                ),
                        },

                    ],
                },


                /*
                |--------------------------------------------------------------------------
                | Inventory
                |--------------------------------------------------------------------------
                */

                {
                    type:
                        'group',

                    key:
                        'inventory',

                    label:
                        'Inventory',

                    icon:
                        'bi-boxes',

                    visible:
                        canAny([
                            'stock.view',
                            'stock.adjust',
                        ]),

                    children: [

                        {
                            label:
                                'Current Stock',

                            icon:
                                'bi-box-seam',

                            href:
                                '/current-stock',

                            visible:
                                can(
                                    'stock.view'
                                ),
                        },

                        {
                            label:
                                'Stock Ledger',

                            icon:
                                'bi-journal-text',

                            href:
                                '/stock-ledger',

                            visible:
                                can(
                                    'stock.view'
                                ),
                        },

                        {
                            label:
                                'Stock Adjustment',

                            icon:
                                'bi-sliders',

                            href:
                                '/stock-adjustments',

                            visible:
                                can(
                                    'stock.adjust'
                                ),
                        },

                        {
                            label:
                                'Stock Transfer',

                            icon:
                                'bi-arrow-left-right',

                            href:
                                '/stock-transfers',

                            visible:
                                can(
                                    'stock.view'
                                ),
                        },

                    ],
                },


                /*
                |--------------------------------------------------------------------------
                | Accounts
                |--------------------------------------------------------------------------
                */

                {
                    type:
                        'group',

                    key:
                        'accounts',

                    label:
                        'Accounts',

                    icon:
                        'bi-wallet2',

                    visible:
                        canAny([
                            'customer.view',
                            'sales.view',
                        ]),

                    children: [

                        {
                            label:
                                'Customer Payments',

                            icon:
                                'bi-cash-coin',

                            href:
                                '/customer-payments',

                            visible:
                                true,
                        },

                        {
                            label:
                                'Customer Ledger',

                            icon:
                                'bi-journal',

                            href:
                                '/customer-ledger',

                            visible:
                                true,
                        },

                    ],
                },


                /*
                |--------------------------------------------------------------------------
                | Reports
                |--------------------------------------------------------------------------
                */

                {
                    type:
                        'group',

                    key:
                        'reports',

                    label:
                        'Reports',

                    icon:
                        'bi-bar-chart-line',

                    visible:
                        canAny([
                            'purchase.view',
                            'sales.view',
                            'stock.view',
                        ]),

                    children: [

                        {
                            label:
                                'Purchase Report',

                            icon:
                                'bi-graph-down-arrow',

                            href:
                                '/purchase-reports',

                            visible:
                                can(
                                    'purchase.view'
                                ),
                        },

                        {
                            label:
                                'Sales Report',

                            icon:
                                'bi-graph-up-arrow',

                            href:
                                '/sales-reports',

                            visible:
                                can(
                                    'sales.view'
                                ),
                        },

                        {
                            label:
                                'Stock Report',

                            icon:
                                'bi-bar-chart',

                            href:
                                '/stock-reports',

                            visible:
                                can(
                                    'stock.view'
                                ),
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
                `${collapsed ? 'collapsed' : ''}`
            }
            onMouseEnter={
                onMouseEnter
            }
            onMouseLeave={
                onMouseLeave
            }
        >

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
                    !collapsed && (

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
                                                collapsed
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
                                            collapsed
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
                        !collapsed && (

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
        isCurrentPath(
            item.href
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
                isCurrentPath(
                    child.href
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
| Current Path
|--------------------------------------------------------------------------
*/

function isCurrentPath(
    href
) {

    if (
        typeof window ===
        'undefined'
    ) {

        return false;
    }


    const pathname =
        window.location.pathname;


    if (
        href ===
        '/business/dashboard'
    ) {

        return pathname ===
            href;
    }


    return pathname ===
        href ||
        pathname.startsWith(
            `${href}/`
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