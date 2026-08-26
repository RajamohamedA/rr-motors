import { Link, usePage } from '@inertiajs/react';
import { useMemo } from 'react';

export default function Sidebar({ open = false, onClose = () => { } }) {

    const { auth } = usePage().props;

    const permissions = auth?.permissions ?? [];
    const user = auth?.user;
    const roles = auth?.roles ?? [];

    /*
    |--------------------------------------------------------------------------
    | Permission helper
    |--------------------------------------------------------------------------
    */

    const can = (permission) => {

        // Super admin can access everything
        if (user?.is_super_user) {
            return true;
        }

        // No permission requirement
        if (!permission) {
            return true;
        }

        return permissions.includes(permission);
    };


    /*
    |--------------------------------------------------------------------------
    | Role helper
    |--------------------------------------------------------------------------
    */

    const hasRole = (role) => {
        return roles.includes(role);
    };


    /*
    |--------------------------------------------------------------------------
    | Current URL
    |--------------------------------------------------------------------------
    */

    const currentUrl = usePage().url;


    /*
    |--------------------------------------------------------------------------
    | Menu definition
    |--------------------------------------------------------------------------
    |
    | Keep menu definitions here.
    | Actual authorization is still handled by Laravel middleware.
    |
    */

    const sections = useMemo(() => [

        {
            title: 'MAIN',

            items: [
                {
                    label: 'Dashboard',
                    icon: 'bi-grid-1x2-fill',
                    href: '/business/dashboard',
                },
            ],
        },


        {
            title: 'TRANSACTIONS',

            items: [
                {
                    label: 'Sales',
                    icon: 'bi-cart-check-fill',
                    href: '/sales',
                    permission: 'sales.view',
                },

                {
                    label: 'Purchases',
                    icon: 'bi-bag-check-fill',
                    href: '/purchases',
                    permission: 'purchases.view',
                },

                {
                    label: 'Customer Payments',
                    icon: 'bi-cash-stack',
                    href: '/customer-payments',
                    permission: 'customer-payments.view',
                },
            ],
        },


        {
            title: 'INVENTORY',

            items: [
                {
                    label: 'Stock',
                    icon: 'bi-box-seam-fill',
                    href: '/inventory',
                    permission: 'stock.view',
                },

                {
                    label: 'Stock Transfer',
                    icon: 'bi-arrow-left-right',
                    href: '/stock-transfers',
                    permission: 'stock-transfers.view',
                },

                {
                    label: 'Stock Adjustment',
                    icon: 'bi-sliders',
                    href: '/stock-adjustments',
                    permission: 'stock-adjustments.view',
                },
            ],
        },


        {
            title: 'MASTERS',

            items: [
                {
                    label: 'Products',
                    icon: 'bi-box-fill',
                    href: '/products',
                    permission: 'products.view',
                },

                {
                    label: 'Customers',
                    icon: 'bi-people-fill',
                    href: '/customers',
                    permission: 'customers.view',
                },

                {
                    label: 'Suppliers',
                    icon: 'bi-truck',
                    href: '/suppliers',
                    permission: 'suppliers.view',
                },

                {
                    label: 'Categories',
                    icon: 'bi-tags-fill',
                    href: '/categories',
                    permission: 'categories.view',
                },

                {
                    label: 'Brands',
                    icon: 'bi-bookmark-star-fill',
                    href: '/brands',
                    permission: 'brands.view',
                },

                {
                    label: 'Units',
                    icon: 'bi-rulers',
                    href: '/units',
                    permission: 'units.view',
                },
            ],
        },


        {
            title: 'REPORTS',

            items: [
                {
                    label: 'Reports',
                    icon: 'bi-bar-chart-fill',
                    href: '/reports',
                    permission: 'reports.view',
                },
            ],
        },


        {
            title: 'SETTINGS',

            items: [
                {
                    label: 'My Profile',
                    icon: 'bi-person-circle',
                    href: '/profile',
                },
            ],
        },

    ], [permissions, user, roles]);


    /*
    |--------------------------------------------------------------------------
    | Filter menu according to permissions
    |--------------------------------------------------------------------------
    */

    const visibleSections = sections
        .map((section) => {

            const visibleItems = section.items.filter((item) => {

                return can(item.permission);

            });

            return {
                ...section,
                items: visibleItems,
            };

        })
        .filter((section) => section.items.length > 0);


    /*
    |--------------------------------------------------------------------------
    | Active URL
    |--------------------------------------------------------------------------
    */

    const isActive = (href) => {

        if (href === '/business/dashboard') {
            return currentUrl === href;
        }

        return (
            currentUrl === href ||
            currentUrl.startsWith(`${href}/`)
        );
    };


    /*
    |--------------------------------------------------------------------------
    | Close sidebar on mobile
    |--------------------------------------------------------------------------
    */

    const handleNavigation = () => {

        if (window.innerWidth < 992) {
            onClose();
        }

    };


    return (
        <>
            {/* Mobile overlay */}

            {open && (
                <div
                    className="rr-sidebar-overlay"
                    onClick={onClose}
                    aria-hidden="true"
                />
            )}


            {/* Sidebar */}

            <aside
                className={`rr-sidebar ${open ? 'rr-sidebar-open' : ''
                    }`}
            >

                {/* =====================================================
                    BRAND
                   ===================================================== */}

                <div className="rr-sidebar-brand">

                    <Link
                        href="/business/dashboard"
                        className="rr-brand-link"
                        onClick={handleNavigation}
                    >

                        <div className="rr-brand-logo">
                            RR
                        </div>

                        <div className="rr-brand-content">

                            <div className="rr-brand-name">
                                RR MOTORS
                            </div>

                            <div className="rr-brand-subtitle">
                                Business Management
                            </div>

                        </div>

                    </Link>


                    {/* Mobile close */}

                    <button
                        type="button"
                        className="rr-sidebar-close"
                        onClick={onClose}
                        aria-label="Close sidebar"
                    >

                        <i className="bi bi-x-lg"></i>

                    </button>

                </div>


                {/* =====================================================
                    NAVIGATION
                   ===================================================== */}

                <nav className="rr-sidebar-navigation">

                    {visibleSections.map((section) => (

                        <div
                            className="rr-sidebar-section"
                            key={section.title}
                        >

                            <div className="rr-sidebar-section-title">
                                {section.title}
                            </div>


                            <div className="rr-sidebar-menu">

                                {section.items.map((item) => {

                                    const active = isActive(item.href);

                                    return (

                                        <Link
                                            key={item.label}
                                            href={item.href}
                                            onClick={handleNavigation}
                                            className={`rr-sidebar-item ${active
                                                    ? 'rr-sidebar-item-active'
                                                    : ''
                                                }`}
                                        >

                                            <span className="rr-sidebar-item-icon">

                                                <i
                                                    className={`bi ${item.icon}`}
                                                />

                                            </span>

                                            <span className="rr-sidebar-item-label">
                                                {item.label}
                                            </span>

                                            {active && (
                                                <span className="rr-sidebar-active-indicator" />
                                            )}

                                        </Link>

                                    );

                                })}

                            </div>

                        </div>

                    ))}

                </nav>


                {/* =====================================================
                    BOTTOM
                   ===================================================== */}

                <div className="rr-sidebar-bottom">

                    <div className="rr-sidebar-version">
                        RR Motors
                        <span>v1.0</span>
                    </div>

                </div>

            </aside>
        </>
    );
}