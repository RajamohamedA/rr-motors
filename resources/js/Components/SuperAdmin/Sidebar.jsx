import {
    Link,
    usePage,
} from '@inertiajs/react';

import {
    useState,
} from 'react';


export default function Sidebar({
    open = false,
    collapsed = false,
    onToggle = () => { },
    onClose = () => { },
}) {

    const page = usePage();

    const {
        auth,
    } = page.props;

    const currentUrl =
        page.url;

    const user =
        auth?.user;


    /*
    |--------------------------------------------------------------------------
    | Hover State
    |--------------------------------------------------------------------------
    */

    const [
        hovered,
        setHovered,
    ] = useState(false);


    /*
    |--------------------------------------------------------------------------
    | Menu
    |--------------------------------------------------------------------------
    */

    const menuSections = [

        {
            title: 'OVERVIEW',

            items: [

                {
                    label: 'Dashboard',
                    href: '/super-admin/dashboard',
                    icon: 'bi-grid-1x2',
                },

            ],
        },

        {
            title: 'ORGANIZATION',

            items: [

                {
                    label: 'Companies',
                    href: '/super-admin/companies',
                    icon: 'bi-buildings',
                },

                {
                    label: 'Branches',
                    href: '/super-admin/branches',
                    icon: 'bi-shop',
                },

            ],
        },

        {
            title: 'ACCESS CONTROL',

            items: [

                {
                    label: 'Users',
                    href: '/super-admin/users',
                    icon: 'bi-people',
                },

                {
                    label: 'Roles',
                    href: '/super-admin/roles',
                    icon: 'bi-person-badge',
                },

                {
                    label: 'Permissions',
                    href: '/super-admin/permissions',
                    icon: 'bi-shield-check',
                },

            ],
        },

        {
            title: 'ANALYTICS',

            items: [

                {
                    label: 'Activity Logs',
                    href: '/super-admin/activity-logs',
                    icon: 'bi-clock-history',
                },

            ],
        },

        {
            title: 'SYSTEM',

            items: [

                {
                    label: 'Settings',
                    href: '/super-admin/settings',
                    icon: 'bi-gear',
                },

                {
                    label: 'My Profile',
                    href: '/profile',
                    icon: 'bi-person-circle',
                },

            ],
        },

    ];


    /*
    |--------------------------------------------------------------------------
    | Active Route
    |--------------------------------------------------------------------------
    */

    const isActive = (href) => {

        if (
            href ===
            '/super-admin/dashboard'
        ) {

            return (
                currentUrl ===
                '/super-admin/dashboard'
            );

        }

        return (
            currentUrl === href ||
            currentUrl.startsWith(
                `${href}/`
            )
        );

    };


    /*
    |--------------------------------------------------------------------------
    | Mobile Navigation
    |--------------------------------------------------------------------------
    */

    const handleNavigation = () => {

        if (
            window.innerWidth < 992
        ) {

            onClose();

        }

    };


    return (

        <>

            {/* =====================================================
                MOBILE OVERLAY
               ===================================================== */}

            {open && (

                <div
                    className="rr-admin-sidebar-overlay"
                    onClick={onClose}
                />

            )}


            {/* =====================================================
                SIDEBAR
               ===================================================== */}

            <aside
                className={[
                    'rr-admin-sidebar',

                    collapsed
                        ? 'rr-admin-sidebar-collapsed'
                        : '',

                    collapsed && hovered
                        ? 'rr-admin-sidebar-hovered'
                        : '',

                    open
                        ? 'rr-admin-sidebar-open'
                        : '',
                ]
                    .filter(Boolean)
                    .join(' ')
                }

                onMouseEnter={() => {

                    if (
                        collapsed &&
                        window.innerWidth >= 992
                    ) {

                        setHovered(true);

                    }

                }}

                onMouseLeave={() => {

                    if (collapsed) {

                        setHovered(false);

                    }

                }}
            >

                {/* =================================================
                    BRAND
                   ================================================= */}

                <div className="rr-admin-brand">

                    <Link
                        href="/super-admin/dashboard"
                        className="rr-admin-brand-link"
                        onClick={handleNavigation}
                    >

                        <div className="rr-admin-brand-mark">

                            RR

                        </div>


                        <div className="rr-admin-brand-text">

                            <div className="rr-admin-brand-name">

                                RR ERP

                            </div>

                            <div className="rr-admin-brand-caption">

                                Business Management System

                            </div>

                        </div>

                    </Link>


                    {/* =============================================
                        DESKTOP COLLAPSE / EXPAND
                       ============================================= */}

                    <button
                        type="button"
                        className="rr-admin-sidebar-toggle"
                        onClick={(event) => {

                            /*
                             * Prevent the mouse hover state from
                             * interfering with manual toggle.
                             */

                            event.stopPropagation();

                            setHovered(false);

                            onToggle();

                        }}
                        title={
                            collapsed
                                ? 'Expand sidebar'
                                : 'Collapse sidebar'
                        }
                        aria-label={
                            collapsed
                                ? 'Expand sidebar'
                                : 'Collapse sidebar'
                        }
                    >

                        <i
                            className={
                                collapsed
                                    ? 'bi bi-chevron-right'
                                    : 'bi bi-chevron-left'
                            }
                        ></i>

                    </button>


                    {/* =============================================
                        MOBILE CLOSE
                       ============================================= */}

                    <button
                        type="button"
                        className="rr-admin-sidebar-close"
                        onClick={onClose}
                        aria-label="Close navigation"
                    >

                        <i className="bi bi-x-lg"></i>

                    </button>

                </div>


                {/* =================================================
                    NAVIGATION
                   ================================================= */}

                <nav className="rr-admin-navigation">

                    {menuSections.map(
                        (section) => (

                            <div
                                key={section.title}
                                className="rr-admin-menu-section"
                            >

                                <div className="rr-admin-section-title">

                                    {section.title}

                                </div>


                                <div className="rr-admin-menu">

                                    {section.items.map(
                                        (item) => {

                                            const active =
                                                isActive(
                                                    item.href
                                                );


                                            return (

                                                <Link
                                                    key={item.label}
                                                    href={item.href}
                                                    onClick={
                                                        handleNavigation
                                                    }
                                                    title={
                                                        collapsed &&
                                                            !hovered
                                                            ? item.label
                                                            : undefined
                                                    }
                                                    className={[
                                                        'rr-admin-menu-item',

                                                        active
                                                            ? 'rr-admin-menu-item-active'
                                                            : '',
                                                    ]
                                                        .filter(Boolean)
                                                        .join(' ')
                                                    }
                                                >

                                                    <span className="rr-admin-menu-icon">

                                                        <i
                                                            className={
                                                                `bi ${item.icon}`
                                                            }
                                                        ></i>

                                                    </span>


                                                    <span className="rr-admin-menu-label">

                                                        {item.label}

                                                    </span>


                                                    {active && (

                                                        <span
                                                            className="rr-admin-active-marker"
                                                        />

                                                    )}

                                                </Link>

                                            );

                                        }
                                    )}

                                </div>

                            </div>

                        )
                    )}

                </nav>


                {/* =================================================
                    FOOTER
                   ================================================= */}

                <div className="rr-admin-sidebar-footer">

                    <div className="rr-admin-user-summary">

                        <div className="rr-admin-user-avatar">

                            {user?.name
                                ?.charAt(0)
                                ?.toUpperCase()
                                ?? 'A'}

                        </div>


                        <div className="rr-admin-user-details">

                            <div className="rr-admin-user-name">

                                {user?.name
                                    ?? 'Administrator'}

                            </div>

                            <div className="rr-admin-user-role">

                                Super Administrator

                            </div>

                        </div>

                    </div>

                </div>

            </aside>

        </>

    );

}