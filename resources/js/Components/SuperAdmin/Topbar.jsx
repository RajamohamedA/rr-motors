import {
    Link,
    router,
    usePage,
} from '@inertiajs/react';

import {
    useEffect,
    useMemo,
    useRef,
    useState,
} from 'react';


export default function Topbar({
    title = 'Dashboard',
    subtitle = null,

    /*
    |--------------------------------------------------------------------------
    | Mobile Sidebar
    |--------------------------------------------------------------------------
    */

    onMenuClick = () => { },

    /*
    |--------------------------------------------------------------------------
    | Desktop Sidebar
    |--------------------------------------------------------------------------
    */

    sidebarCollapsed = false,

    onSidebarToggle = () => { },
}) {

    /*
    |--------------------------------------------------------------------------
    | Shared Inertia Data
    |--------------------------------------------------------------------------
    */

    const {
        auth,
        notifications,
    } = usePage().props;


    const user =
        auth?.user ?? null;


    const notificationData =
        notifications ?? {
            unread_count: 0,
            items: [],
        };


    /*
    |--------------------------------------------------------------------------
    | Search
    |--------------------------------------------------------------------------
    */

    const [
        searchOpen,
        setSearchOpen,
    ] = useState(false);


    const [
        search,
        setSearch,
    ] = useState('');


    const searchInputRef =
        useRef(null);


    /*
    |--------------------------------------------------------------------------
    | Notifications
    |--------------------------------------------------------------------------
    */

    const [
        notificationOpen,
        setNotificationOpen,
    ] = useState(false);


    /*
    |--------------------------------------------------------------------------
    | Help
    |--------------------------------------------------------------------------
    */

    const [
        helpOpen,
        setHelpOpen,
    ] = useState(false);


    /*
    |--------------------------------------------------------------------------
    | Popup References
    |--------------------------------------------------------------------------
    */

    const notificationRef =
        useRef(null);


    const helpRef =
        useRef(null);


    /*
    |--------------------------------------------------------------------------
    | Search Commands
    |--------------------------------------------------------------------------
    */

    const commands = [

        {
            title:
                'Dashboard',

            description:
                'Super Admin overview',

            icon:
                'bi-speedometer2',

            href:
                '/super-admin/dashboard',

            group:
                'Navigation',

            keywords:
                'dashboard home overview',
        },

        {
            title:
                'Companies',

            description:
                'Manage companies',

            icon:
                'bi-buildings',

            href:
                '/super-admin/companies',

            group:
                'Organization',

            keywords:
                'companies company organization',
        },

        {
            title:
                'Branches',

            description:
                'Manage company branches',

            icon:
                'bi-shop',

            href:
                '/super-admin/branches',

            group:
                'Organization',

            keywords:
                'branch branches store stores',
        },

        {
            title:
                'Users',

            description:
                'Manage company users',

            icon:
                'bi-people',

            href:
                '/super-admin/users',

            group:
                'Access Control',

            keywords:
                'users user employee staff',
        },

        {
            title:
                'Roles',

            description:
                'Manage roles and access',

            icon:
                'bi-person-badge',

            href:
                '/super-admin/roles',

            group:
                'Access Control',

            keywords:
                'roles role access',
        },

        {
            title:
                'Permissions',

            description:
                'Manage system permissions',

            icon:
                'bi-shield-check',

            href:
                '/super-admin/permissions',

            group:
                'Access Control',

            keywords:
                'permission permissions security access',
        },

        {
            title:
                'Activity Logs',

            description:
                'View audit history',

            icon:
                'bi-clock-history',

            href:
                '/super-admin/activity-logs',

            group:
                'Analytics',

            keywords:
                'activity audit history logs',
        },

        {
            title:
                'Settings',

            description:
                'System configuration',

            icon:
                'bi-gear',

            href:
                '/super-admin/settings',

            group:
                'System',

            keywords:
                'settings system configuration',
        },

        {
            title:
                'My Profile',

            description:
                'Account and security',

            icon:
                'bi-person-circle',

            href:
                '/profile',

            group:
                'Account',

            keywords:
                'profile account password security',
        },

    ];


    /*
    |--------------------------------------------------------------------------
    | Filter Commands
    |--------------------------------------------------------------------------
    */

    const filteredCommands =
        useMemo(
            () => {

                const term =
                    search
                        .trim()
                        .toLowerCase();


                if (!term) {

                    return commands.slice(
                        0,
                        7
                    );

                }


                return commands.filter(
                    (command) => {

                        const searchable =
                            [
                                command.title,
                                command.description,
                                command.group,
                                command.keywords,
                            ]
                                .join(' ')
                                .toLowerCase();


                        return searchable.includes(
                            term
                        );

                    }
                );

            },
            [search]
        );


    /*
    |--------------------------------------------------------------------------
    | Keyboard Shortcuts
    |--------------------------------------------------------------------------
    */

    useEffect(
        () => {

            const handleKeyDown = (
                event
            ) => {

                /*
                |--------------------------------------------------------------------------
                | Ctrl / Cmd + K
                |--------------------------------------------------------------------------
                */

                if (
                    (
                        event.ctrlKey ||
                        event.metaKey
                    ) &&
                    event.key
                        .toLowerCase() === 'k'
                ) {

                    event.preventDefault();


                    setSearchOpen(
                        true
                    );


                    setNotificationOpen(
                        false
                    );


                    setHelpOpen(
                        false
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Escape
                |--------------------------------------------------------------------------
                */

                if (
                    event.key ===
                    'Escape'
                ) {

                    setSearchOpen(
                        false
                    );


                    setNotificationOpen(
                        false
                    );


                    setHelpOpen(
                        false
                    );

                }

            };


            document.addEventListener(
                'keydown',
                handleKeyDown
            );


            return () => {

                document.removeEventListener(
                    'keydown',
                    handleKeyDown
                );

            };

        },
        []
    );


    /*
    |--------------------------------------------------------------------------
    | Close Notification / Help Outside
    |--------------------------------------------------------------------------
    */

    useEffect(
        () => {

            const handleOutsideClick = (
                event
            ) => {

                /*
                |--------------------------------------------------------------------------
                | Notification
                |--------------------------------------------------------------------------
                */

                if (
                    notificationOpen &&
                    notificationRef.current &&
                    !notificationRef.current.contains(
                        event.target
                    )
                ) {

                    setNotificationOpen(
                        false
                    );

                }


                /*
                |--------------------------------------------------------------------------
                | Help
                |--------------------------------------------------------------------------
                */

                if (
                    helpOpen &&
                    helpRef.current &&
                    !helpRef.current.contains(
                        event.target
                    )
                ) {

                    setHelpOpen(
                        false
                    );

                }

            };


            document.addEventListener(
                'mousedown',
                handleOutsideClick
            );


            document.addEventListener(
                'touchstart',
                handleOutsideClick
            );


            return () => {

                document.removeEventListener(
                    'mousedown',
                    handleOutsideClick
                );


                document.removeEventListener(
                    'touchstart',
                    handleOutsideClick
                );

            };

        },
        [
            notificationOpen,
            helpOpen,
        ]
    );


    /*
    |--------------------------------------------------------------------------
    | Focus Search
    |--------------------------------------------------------------------------
    */

    useEffect(
        () => {

            if (
                !searchOpen
            ) {

                return;

            }


            const timer =
                window.setTimeout(
                    () => {

                        searchInputRef
                            .current
                            ?.focus();

                    },
                    50
                );


            return () => {

                window.clearTimeout(
                    timer
                );

            };

        },
        [searchOpen]
    );


    /*
    |--------------------------------------------------------------------------
    | Navigate
    |--------------------------------------------------------------------------
    */

    const goToCommand = (
        command
    ) => {

        setSearchOpen(
            false
        );


        setSearch(
            ''
        );


        router.visit(
            command.href
        );

    };


    return (

        <>

            {/* =====================================================
                TOPBAR
               ===================================================== */}

            <header className="rr-admin-topbar">

                {/* =================================================
                    LEFT
                   ================================================= */}

                <div className="rr-admin-topbar-left">

                    {/* =============================================
                        MOBILE MENU
                       ============================================= */}

                    <button
                        type="button"
                        className="rr-admin-mobile-menu"
                        onClick={
                            onMenuClick
                        }
                        aria-label="Open navigation"
                        title="Open navigation"
                    >

                        <i className="bi bi-list"></i>

                    </button>


                    {/* =============================================
                        DESKTOP SIDEBAR TOGGLE
                       ============================================= */}

                    <button
                        type="button"
                        className="rr-admin-topbar-sidebar-toggle"
                        onClick={
                            onSidebarToggle
                        }
                        aria-label={
                            sidebarCollapsed
                                ? 'Expand navigation'
                                : 'Collapse navigation'
                        }
                        title={
                            sidebarCollapsed
                                ? 'Expand navigation'
                                : 'Collapse navigation'
                        }
                    >

                        <i
                            className={
                                sidebarCollapsed
                                    ? 'bi bi-layout-sidebar-inset'
                                    : 'bi bi-layout-sidebar'
                            }
                        ></i>

                    </button>


                    {/* =============================================
                        PAGE HEADING
                       ============================================= */}

                    <div className="rr-admin-page-heading">

                        <h1 className="rr-admin-topbar-title">

                            {title}

                        </h1>


                        {subtitle && (

                            <div className="rr-admin-topbar-subtitle">

                                {subtitle}

                            </div>

                        )}

                    </div>

                </div>


                {/* =================================================
                    RIGHT
                   ================================================= */}

                <div className="rr-admin-topbar-right">

                    {/* =============================================
                        SEARCH
                       ============================================= */}

                    <button
                        type="button"
                        className="
                            rr-admin-command-button
                            rr-admin-search-button
                        "
                        title="Search"
                        onClick={() => {

                            setSearchOpen(
                                true
                            );


                            setNotificationOpen(
                                false
                            );


                            setHelpOpen(
                                false
                            );

                        }}
                    >

                        <i className="bi bi-search"></i>


                        <span>
                            Search
                        </span>


                        <kbd>
                            Ctrl K
                        </kbd>

                    </button>


                    {/* =============================================
                        NOTIFICATIONS
                       ============================================= */}

                    <div
                        ref={
                            notificationRef
                        }
                        className="rr-topbar-popup-wrapper"
                    >

                        <button
                            type="button"
                            className="rr-admin-icon-button"
                            title="Notifications"
                            aria-label="Notifications"
                            aria-expanded={
                                notificationOpen
                            }
                            onClick={() => {

                                setNotificationOpen(
                                    current =>
                                        !current
                                );


                                setHelpOpen(
                                    false
                                );

                            }}
                        >

                            <i className="bi bi-bell"></i>


                            {notificationData
                                .unread_count > 0 && (

                                    <span className="rr-admin-notification-badge">

                                        {
                                            notificationData
                                                .unread_count > 99
                                                ? '99+'
                                                : notificationData
                                                    .unread_count
                                        }

                                    </span>

                                )}

                        </button>


                        {notificationOpen && (

                            <NotificationDropdown
                                notifications={
                                    notificationData
                                        .items ?? []
                                }
                                unreadCount={
                                    notificationData
                                        .unread_count ?? 0
                                }
                                onClose={() =>
                                    setNotificationOpen(
                                        false
                                    )
                                }
                            />

                        )}

                    </div>


                    {/* =============================================
                        HELP
                       ============================================= */}

                    <div
                        ref={
                            helpRef
                        }
                        className="rr-topbar-popup-wrapper"
                    >

                        <button
                            type="button"
                            className="rr-admin-icon-button"
                            title="Help"
                            aria-label="Help"
                            aria-expanded={
                                helpOpen
                            }
                            onClick={() => {

                                setHelpOpen(
                                    current =>
                                        !current
                                );


                                setNotificationOpen(
                                    false
                                );

                            }}
                        >

                            <i className="bi bi-question-circle"></i>

                        </button>


                        {helpOpen && (

                            <HelpDropdown
                                onClose={() =>
                                    setHelpOpen(
                                        false
                                    )
                                }
                                onSearch={() => {

                                    setHelpOpen(
                                        false
                                    );


                                    setSearchOpen(
                                        true
                                    );

                                }}
                            />

                        )}

                    </div>


                    {/* =============================================
                        DIVIDER
                       ============================================= */}

                    <div className="rr-admin-topbar-divider"></div>


                    {/* =============================================
                        PROFILE
                       ============================================= */}

                    <UserMenu
                        user={
                            user
                        }
                    />

                </div>

            </header>


            {/* =====================================================
                SEARCH / COMMAND PALETTE
               ===================================================== */}

            {searchOpen && (

                <SearchPalette
                    search={
                        search
                    }
                    setSearch={
                        setSearch
                    }
                    inputRef={
                        searchInputRef
                    }
                    commands={
                        filteredCommands
                    }
                    onSelect={
                        goToCommand
                    }
                    onClose={() => {

                        setSearchOpen(
                            false
                        );


                        setSearch(
                            ''
                        );

                    }}
                />

            )}

        </>

    );
}


/*
|--------------------------------------------------------------------------
| Search Palette
|--------------------------------------------------------------------------
*/

function SearchPalette({
    search,
    setSearch,
    inputRef,
    commands,
    onSelect,
    onClose,
}) {

    return (

        <div className="rr-command-overlay">

            <div
                className="rr-command-backdrop"
                onClick={
                    onClose
                }
            ></div>


            <div
                className="rr-command-dialog"
                role="dialog"
                aria-modal="true"
                aria-label="Command search"
            >

                <div className="rr-command-search">

                    <i className="bi bi-search"></i>


                    <input
                        ref={
                            inputRef
                        }
                        type="text"
                        value={
                            search
                        }
                        onChange={
                            event =>
                                setSearch(
                                    event.target.value
                                )
                        }
                        placeholder="Search pages and administration..."
                    />


                    <kbd>
                        ESC
                    </kbd>

                </div>


                <div className="rr-command-results">

                    {commands.length > 0 ? (

                        commands.map(
                            command => (

                                <button
                                    key={
                                        command.href
                                    }
                                    type="button"
                                    className="rr-command-item"
                                    onClick={() =>
                                        onSelect(
                                            command
                                        )
                                    }
                                >

                                    <div className="rr-command-item-icon">

                                        <i
                                            className={
                                                `bi ${command.icon}`
                                            }
                                        ></i>

                                    </div>


                                    <div className="rr-command-item-content">

                                        <div className="rr-command-item-title">

                                            {command.title}

                                        </div>


                                        <div className="rr-command-item-description">

                                            {command.description}

                                        </div>

                                    </div>


                                    <div className="rr-command-item-group">

                                        {command.group}

                                    </div>


                                    <i className="bi bi-chevron-right rr-command-item-arrow"></i>

                                </button>

                            )
                        )

                    ) : (

                        <div className="rr-command-empty">

                            <div className="rr-command-empty-icon">

                                <i className="bi bi-search"></i>

                            </div>


                            <div className="rr-command-empty-title">

                                No results found

                            </div>


                            <div className="rr-command-empty-text">

                                Try another search term.

                            </div>

                        </div>

                    )}

                </div>


                <div className="rr-command-footer">

                    <span>

                        <kbd>
                            Ctrl K
                        </kbd>

                        Search

                    </span>


                    <span>

                        <kbd>
                            Esc
                        </kbd>

                        Close

                    </span>

                </div>

            </div>

        </div>

    );

}


/*
|--------------------------------------------------------------------------
| Notification Dropdown
|--------------------------------------------------------------------------
*/

function NotificationDropdown({
    notifications = [],
    unreadCount = 0,
    onClose,
}) {

    /*
    |--------------------------------------------------------------------------
    | Open Notification
    |--------------------------------------------------------------------------
    */

    const openNotification = (
        notification
    ) => {

        onClose();


        router.post(
            `/super-admin/notifications/${notification.id}/read`,
            {},
            {
                preserveScroll:
                    true,
            }
        );

    };


    /*
    |--------------------------------------------------------------------------
    | Mark All Read
    |--------------------------------------------------------------------------
    */

    const markAllRead = () => {

        router.patch(
            '/super-admin/notifications/read-all',
            {},
            {
                preserveScroll:
                    true,

                onSuccess: () => {

                    onClose();

                },
            }
        );

    };


    return (

        <div className="rr-topbar-popup rr-notification-popup">

            {/* =====================================================
                HEADER
               ===================================================== */}

            <div className="rr-popup-header">

                <div>

                    <div className="rr-popup-title">

                        Notifications

                    </div>


                    <div className="rr-popup-subtitle">

                        System alerts and administration

                    </div>

                </div>


                <div className="rr-popup-header-actions">

                    {unreadCount > 0 && (

                        <button
                            type="button"
                            className="rr-notification-read-all"
                            onClick={
                                markAllRead
                            }
                        >

                            Mark all read

                        </button>

                    )}


                    {unreadCount > 0 && (

                        <span className="rr-popup-count">

                            {
                                unreadCount > 99
                                    ? '99+'
                                    : unreadCount
                            }

                        </span>

                    )}

                </div>

            </div>


            {/* =====================================================
                LIST
               ===================================================== */}

            <div className="rr-notification-list">

                {notifications.length > 0 ? (

                    notifications.map(
                        notification => (

                            <button
                                key={
                                    notification.id
                                }
                                type="button"
                                className={
                                    !notification.read
                                        ? 'rr-notification-item unread'
                                        : 'rr-notification-item'
                                }
                                onClick={() =>
                                    openNotification(
                                        notification
                                    )
                                }
                            >

                                <div
                                    className={
                                        `rr-notification-icon ` +
                                        `rr-notification-icon-${notificationLevel(
                                            notification.level
                                        )}`
                                    }
                                >

                                    <i
                                        className={
                                            `bi ${notification.icon
                                            ?? 'bi-bell'
                                            }`
                                        }
                                    ></i>

                                </div>


                                <div className="rr-notification-content">

                                    <div className="rr-notification-title">

                                        {
                                            notification.title
                                            ?? 'Notification'
                                        }

                                    </div>


                                    <div className="rr-notification-text">

                                        {
                                            notification.message
                                            ?? ''
                                        }

                                    </div>


                                    <div className="rr-notification-meta">

                                        <span>

                                            {
                                                formatNotificationTime(
                                                    notification.created_at
                                                )
                                            }

                                        </span>


                                        {notification.category && (

                                            <span className="rr-notification-category">

                                                {
                                                    formatText(
                                                        notification.category
                                                    )
                                                }

                                            </span>

                                        )}

                                    </div>

                                </div>


                                {!notification.read && (

                                    <span className="rr-notification-unread-dot"></span>

                                )}

                            </button>

                        )
                    )

                ) : (

                    <div className="rr-notification-empty">

                        <div className="rr-notification-empty-icon">

                            <i className="bi bi-bell-check"></i>

                        </div>


                        <div className="rr-notification-empty-title">

                            You're all caught up

                        </div>


                        <div className="rr-notification-empty-text">

                            No notifications need your attention.

                        </div>

                    </div>

                )}

            </div>


            {/* =====================================================
                FOOTER
               ===================================================== */}

            <Link
                href="/super-admin/activity-logs"
                className="rr-popup-footer-link"
                onClick={
                    onClose
                }
            >

                <span>

                    View Activity Logs

                </span>


                <i className="bi bi-arrow-right"></i>

            </Link>

        </div>

    );

}


/*
|--------------------------------------------------------------------------
| Help Dropdown
|--------------------------------------------------------------------------
*/

function HelpDropdown({
    onSearch,
    onClose,
}) {

    return (

        <div className="rr-topbar-popup rr-help-popup">

            <div className="rr-popup-header">

                <div>

                    <div className="rr-popup-title">

                        Help & Support

                    </div>


                    <div className="rr-popup-subtitle">

                        RR ERP Administration Console

                    </div>

                </div>

            </div>


            <div className="rr-help-menu">

                {/* =================================================
                    COMMAND SEARCH
                   ================================================= */}

                <button
                    type="button"
                    className="rr-help-item"
                    onClick={
                        onSearch
                    }
                >

                    <div className="rr-help-icon">

                        <i className="bi bi-command"></i>

                    </div>


                    <div>

                        <div className="rr-help-title">

                            Command Search

                        </div>


                        <div className="rr-help-description">

                            Quickly navigate using Ctrl + K

                        </div>

                    </div>

                </button>


                {/* =================================================
                    KEYBOARD
                   ================================================= */}

                <div className="rr-help-item">

                    <div className="rr-help-icon">

                        <i className="bi bi-keyboard"></i>

                    </div>


                    <div>

                        <div className="rr-help-title">

                            Keyboard Shortcuts

                        </div>


                        <div className="rr-help-description">

                            Ctrl + K — Open search

                        </div>

                    </div>

                </div>


                {/* =================================================
                    AUDIT
                   ================================================= */}

                <Link
                    href="/super-admin/activity-logs"
                    className="rr-help-item rr-help-link"
                    onClick={
                        onClose
                    }
                >

                    <div className="rr-help-icon">

                        <i className="bi bi-clock-history"></i>

                    </div>


                    <div>

                        <div className="rr-help-title">

                            Audit History

                        </div>


                        <div className="rr-help-description">

                            Review administrative actions

                        </div>

                    </div>

                </Link>


                {/* =================================================
                    SECURITY
                   ================================================= */}

                <Link
                    href="/profile"
                    className="rr-help-item rr-help-link"
                    onClick={
                        onClose
                    }
                >

                    <div className="rr-help-icon">

                        <i className="bi bi-shield-lock"></i>

                    </div>


                    <div>

                        <div className="rr-help-title">

                            Account Security

                        </div>


                        <div className="rr-help-description">

                            Profile and password settings

                        </div>

                    </div>

                </Link>


                {/* =================================================
                    APPLICATION
                   ================================================= */}

                <div className="rr-help-item">

                    <div className="rr-help-icon">

                        <i className="bi bi-info-circle"></i>

                    </div>


                    <div>

                        <div className="rr-help-title">

                            RR ERP

                        </div>


                        <div className="rr-help-description">

                            Super Administrator Console

                        </div>

                    </div>

                </div>

            </div>


            <div className="rr-popup-footer-static">

                <i className="bi bi-lightbulb"></i>

                Use Ctrl + K for faster navigation.

            </div>

        </div>

    );

}


/*
|--------------------------------------------------------------------------
| Professional User Menu
|--------------------------------------------------------------------------
*/

function UserMenu({
    user,
}) {

    return (

        <div className="dropdown">

            {/* =====================================================
                PROFILE TRIGGER
               ===================================================== */}

            <button
                type="button"
                className="rr-admin-profile-trigger"
                data-bs-toggle="dropdown"
                aria-expanded="false"
            >

                <div className="rr-admin-profile-avatar">

                    {
                        getInitials(
                            user?.name
                        )
                    }

                    <span className="rr-admin-profile-online"></span>

                </div>


                <div className="rr-admin-profile-info">

                    <div className="rr-admin-profile-name">

                        {
                            user?.name
                            ?? 'Administrator'
                        }

                    </div>


                    <div className="rr-admin-profile-role">

                        <i className="bi bi-shield-check"></i>

                        Super Administrator

                    </div>

                </div>


                <div className="rr-admin-profile-chevron">

                    <i className="bi bi-chevron-down"></i>

                </div>

            </button>


            {/* =====================================================
                PROFILE MENU
               ===================================================== */}

            <div
                className="
                    dropdown-menu
                    dropdown-menu-end
                    rr-admin-profile-menu
                "
            >

                {/* =================================================
                    HEADER
                   ================================================= */}

                <div className="rr-profile-popup-header">

                    <div className="rr-profile-popup-cover">

                        <div className="rr-profile-popup-brand">

                            <i className="bi bi-shield-lock-fill"></i>


                            <span>

                                Administrator Account

                            </span>

                        </div>

                    </div>


                    <div className="rr-profile-popup-account">

                        <div className="rr-profile-popup-avatar">

                            {
                                getInitials(
                                    user?.name
                                )
                            }

                            <span className="rr-profile-popup-status"></span>

                        </div>


                        <div className="rr-profile-popup-account-info">

                            <div className="rr-profile-popup-name">

                                {
                                    user?.name
                                    ?? 'Administrator'
                                }

                            </div>


                            <div className="rr-profile-popup-email">

                                {
                                    user?.email
                                    ?? ''
                                }

                            </div>


                            <div className="rr-profile-popup-badge">

                                <i className="bi bi-patch-check-fill"></i>

                                Super Administrator

                            </div>

                        </div>

                    </div>

                </div>


                {/* =================================================
                    ACCOUNT
                   ================================================= */}

                <div className="rr-profile-popup-section">

                    <div className="rr-profile-popup-section-title">

                        Account

                    </div>


                    {/* =============================================
                        PROFILE
                       ============================================= */}

                    <Link
                        href="/profile"
                        className="rr-profile-popup-item"
                    >

                        <span className="rr-profile-popup-item-icon primary">

                            <i className="bi bi-person"></i>

                        </span>


                        <span className="rr-profile-popup-item-content">

                            <span className="rr-profile-popup-item-title">

                                My Profile

                            </span>


                            <span className="rr-profile-popup-item-text">

                                Profile details and password

                            </span>

                        </span>


                        <i className="bi bi-chevron-right rr-profile-popup-item-arrow"></i>

                    </Link>


                    {/* =============================================
                        ACTIVITY
                       ============================================= */}

                    <Link
                        href="/super-admin/activity-logs"
                        className="rr-profile-popup-item"
                    >

                        <span className="rr-profile-popup-item-icon purple">

                            <i className="bi bi-clock-history"></i>

                        </span>


                        <span className="rr-profile-popup-item-content">

                            <span className="rr-profile-popup-item-title">

                                Activity Logs

                            </span>


                            <span className="rr-profile-popup-item-text">

                                Review administrative activity

                            </span>

                        </span>


                        <i className="bi bi-chevron-right rr-profile-popup-item-arrow"></i>

                    </Link>


                    {/* =============================================
                        SETTINGS
                       ============================================= */}

                    <Link
                        href="/super-admin/settings"
                        className="rr-profile-popup-item"
                    >

                        <span className="rr-profile-popup-item-icon slate">

                            <i className="bi bi-sliders"></i>

                        </span>


                        <span className="rr-profile-popup-item-content">

                            <span className="rr-profile-popup-item-title">

                                System Settings

                            </span>


                            <span className="rr-profile-popup-item-text">

                                Administration configuration

                            </span>

                        </span>


                        <i className="bi bi-chevron-right rr-profile-popup-item-arrow"></i>

                    </Link>

                </div>


                {/* =================================================
                    SESSION SECURITY
                   ================================================= */}

                <div className="rr-profile-session">

                    <div className="rr-profile-session-icon">

                        <i className="bi bi-shield-check"></i>

                    </div>


                    <div className="rr-profile-session-content">

                        <div className="rr-profile-session-title">

                            Secure administrator session

                        </div>


                        <div className="rr-profile-session-text">

                            Your account has elevated system privileges.

                        </div>

                    </div>


                    <span className="rr-profile-session-status">

                        Active

                    </span>

                </div>


                {/* =================================================
                    SIGN OUT
                   ================================================= */}

                <div className="rr-profile-popup-footer">

                    <Link
                        href="/logout"
                        method="post"
                        as="button"
                        className="rr-profile-signout"
                    >

                        <span className="rr-profile-signout-icon">

                            <i className="bi bi-box-arrow-right"></i>

                        </span>


                        <span className="rr-profile-signout-content">

                            <span className="rr-profile-signout-title">

                                Sign Out

                            </span>


                            <span className="rr-profile-signout-text">

                                End this administrator session

                            </span>

                        </span>


                        <i className="bi bi-arrow-right rr-profile-signout-arrow"></i>

                    </Link>

                </div>

            </div>

        </div>

    );

}


/*
|--------------------------------------------------------------------------
| Notification Level
|--------------------------------------------------------------------------
*/

function notificationLevel(
    value
) {

    const level =
        String(
            value ?? 'info'
        )
            .toLowerCase();


    if (
        [
            'success',
            'warning',
            'danger',
            'error',
            'info',
        ].includes(
            level
        )
    ) {

        return level === 'error'
            ? 'danger'
            : level;

    }


    return 'info';

}


/*
|--------------------------------------------------------------------------
| Notification Time
|--------------------------------------------------------------------------
*/

function formatNotificationTime(
    value
) {

    if (
        !value
    ) {

        return '';

    }


    const date =
        new Date(
            value
        );


    const now =
        new Date();


    const difference =
        now.getTime() -
        date.getTime();


    const minutes =
        Math.floor(
            difference / 60000
        );


    if (
        minutes < 1
    ) {

        return 'Just now';

    }


    if (
        minutes < 60
    ) {

        return `${minutes} min ago`;

    }


    const hours =
        Math.floor(
            minutes / 60
        );


    if (
        hours < 24
    ) {

        return `${hours} hr ago`;

    }


    return new Intl.DateTimeFormat(
        'en-IN',
        {
            day:
                '2-digit',

            month:
                'short',

            year:
                'numeric',
        }
    ).format(
        date
    );

}


/*
|--------------------------------------------------------------------------
| Format Text
|--------------------------------------------------------------------------
*/

function formatText(
    value
) {

    return String(
        value ?? ''
    )
        .replaceAll(
            '-',
            ' '
        )
        .replaceAll(
            '_',
            ' '
        )
        .replaceAll(
            '.',
            ' '
        )
        .replace(
            /\b\w/g,
            character =>
                character.toUpperCase()
        );

}


/*
|--------------------------------------------------------------------------
| Initials
|--------------------------------------------------------------------------
*/

function getInitials(
    name
) {

    if (
        !name
    ) {

        return 'SA';

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
        parts[0][0] +
        parts[
        parts.length - 1
        ][0]
    ).toUpperCase();

}