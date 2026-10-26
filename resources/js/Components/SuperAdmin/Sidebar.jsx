import { Link, usePage } from '@inertiajs/react';

import { useState } from 'react';

export default function Sidebar({
    open = false,
    collapsed = false,
    onToggle = () => { },
    onClose = () => { },
}) {
    const page = usePage();

    const { auth } = page.props;

    const currentUrl = page.url;

    const user = auth?.user;

    /*
    |--------------------------------------------------------------------------
    | Hover State
    |--------------------------------------------------------------------------
    */

    const [hovered, setHovered] = useState(false);

    const [iconFailed, setIconFailed] = useState(false);
    const [wordmarkFailed, setWordmarkFailed] = useState(false);

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
        if (href === '/super-admin/dashboard') {
            return currentUrl === '/super-admin/dashboard';
        }

        return currentUrl === href || currentUrl.startsWith(`${href}/`);
    };

    /*
    |--------------------------------------------------------------------------
    | Mobile Navigation
    |--------------------------------------------------------------------------
    */

    const handleNavigation = () => {
        if (window.innerWidth < 992) {
            onClose();
        }
    };

    return (
        <>
            <style>{BRAND_STYLES}</style>

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

                    collapsed ? 'rr-admin-sidebar-collapsed' : '',

                    collapsed && hovered ? 'rr-admin-sidebar-hovered' : '',

                    open ? 'rr-admin-sidebar-open' : '',
                ]
                    .filter(Boolean)
                    .join(' ')}

                onMouseEnter={() => {
                    if (collapsed && window.innerWidth >= 992) {
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
                        aria-label="RR ERP Super Admin dashboard"
                        title="RR ERP"
                        onClick={handleNavigation}
                    >
                        <div
                            className="rr-admin-brand-mark rr-admin-logo-mark"
                            aria-hidden="true"
                        >
                            {iconFailed ? (
                                <span className="rr-admin-logo-fallback">RR</span>
                            ) : (
                                <img
                                    src="/images/rr-erp-icon.png?v=2"
                                    alt=""
                                    width="42"
                                    height="42"
                                    decoding="async"
                                    onError={() => setIconFailed(true)}
                                />
                            )}
                        </div>
                        <div className="rr-admin-brand-text rr-admin-logo-text">
                            <div className="rr-admin-brand-name rr-admin-logo-name">
                                {wordmarkFailed ? (
                                    <span>RR ERP</span>
                                ) : (
                                    <img
                                        src="/images/rr-erp-wordmark.png"
                                        alt="RR ERP"
                                        width="126"
                                        height="30"
                                        decoding="async"
                                        onError={() => setWordmarkFailed(true)}
                                    />
                                )}
                            </div>
                            <div className="rr-admin-brand-caption rr-admin-logo-caption">
                                Business Management Platform
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
                        title={collapsed ? 'Expand sidebar' : 'Collapse sidebar'}
                        aria-label={
                            collapsed ? 'Expand sidebar' : 'Collapse sidebar'
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
                    {menuSections.map((section) => (
                        <div
                            key={section.title}
                            className="rr-admin-menu-section"
                        >
                            <div className="rr-admin-section-title">
                                {section.title}
                            </div>

                            <div className="rr-admin-menu">
                                {section.items.map((item) => {
                                    const active = isActive(item.href);

                                    return (
                                        <Link
                                            key={item.label}
                                            href={item.href}
                                            onClick={handleNavigation}
                                            title={
                                                collapsed && !hovered
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
                                                .join(' ')}
                                        >
                                            <span className="rr-admin-menu-icon">
                                                <i className={`bi ${item.icon}`}></i>
                                            </span>

                                            <span className="rr-admin-menu-label">
                                                {item.label}
                                            </span>

                                            {active && (
                                                <span className="rr-admin-active-marker" />
                                            )}
                                        </Link>
                                    );
                                })}
                            </div>
                        </div>
                    ))}
                </nav>

                {/* =================================================
                    FOOTER
                   ================================================= */}

                <div className="rr-admin-sidebar-footer">
                    <div className="rr-admin-user-summary">
                        <div className="rr-admin-user-avatar">
                            {user?.name?.charAt(0)?.toUpperCase() ?? 'A'}
                        </div>

                        <div className="rr-admin-user-details">
                            <div className="rr-admin-user-name">
                                {user?.name ?? 'Administrator'}
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

const BRAND_STYLES = `
.rr-admin-sidebar .rr-admin-brand-link {
    gap: 10px;
}
.rr-admin-sidebar .rr-admin-brand-mark.rr-admin-logo-mark {
    flex: 0 0 42px;
    width: 42px;
    height: 42px;
    min-width: 42px;
    padding: 0;
    overflow: hidden;
    border: 0;
    border-radius: 10px;
    background: #101b32;
    box-shadow: 0 0 0 1px #ffffff1a, 0 3px 10px #00000020;
}
.rr-admin-sidebar .rr-admin-brand-mark.rr-admin-logo-mark::after {
    content: none;
}
.rr-admin-sidebar .rr-admin-logo-mark img {
    display: block;
    width: 42px;
    height: 42px;
    object-fit: contain;
    transition: transform .18s ease;
}
.rr-admin-sidebar .rr-admin-brand-link:hover .rr-admin-logo-mark img {
    transform: scale(1.04);
}
.rr-admin-sidebar .rr-admin-brand-text.rr-admin-logo-text {
    min-width: 0;
    flex: 1 1 auto;
}
.rr-admin-sidebar .rr-admin-logo-name {
    display: flex;
    align-items: center;
    min-height: 30px;
    line-height: 1;
}
.rr-admin-sidebar .rr-admin-logo-name img {
    display: block;
    width: 126px;
    max-width: 100%;
    height: 30px;
    object-fit: contain;
    object-position: left center;
}
.rr-admin-sidebar .rr-admin-brand-caption.rr-admin-logo-caption {
    margin-top: 2px;
    font-size: 9px;
    font-weight: 500;
    line-height: 1.35;
    color: #d6e2fa;
    white-space: normal;
    overflow-wrap: anywhere;
}
.rr-admin-sidebar .rr-admin-logo-fallback {
    font-size: 17px;
    font-weight: 800;
    color: #ffffff;
}
@media (prefers-reduced-motion: reduce) {
    .rr-admin-sidebar .rr-admin-logo-mark img { transition: none; }
    .rr-admin-sidebar .rr-admin-brand-link:hover .rr-admin-logo-mark img { transform: none; }
}
`;
