import {
    useState,
} from 'react';

import {
    Link,
} from '@inertiajs/react';

import FlashNotification
    from '../Components/FlashNotification';

import Sidebar
    from '../Components/SuperAdmin/Sidebar';

import Topbar
    from '../Components/SuperAdmin/Topbar';


export default function SuperAdminLayout({
    children,

    title = 'Dashboard',

    subtitle = null,

    breadcrumbs = [],

    actions = null,
}) {

    /*
    |--------------------------------------------------------------------------
    | Mobile Sidebar State
    |--------------------------------------------------------------------------
    */

    const [
        sidebarOpen,
        setSidebarOpen,
    ] = useState(false);


    /*
    |--------------------------------------------------------------------------
    | Desktop Sidebar State
    |--------------------------------------------------------------------------
    |
    | false = permanently expanded
    | true  = manually collapsed
    |
    */

    const [
        sidebarCollapsed,
        setSidebarCollapsed,
    ] = useState(false);


    /*
    |--------------------------------------------------------------------------
    | Desktop Hover State
    |--------------------------------------------------------------------------
    |
    | When sidebar is collapsed:
    |
    | true  = temporarily expanded by mouse hover
    | false = collapsed
    |
    */

    const [
        sidebarHovered,
        setSidebarHovered,
    ] = useState(false);


    /*
    |--------------------------------------------------------------------------
    | Mobile Close
    |--------------------------------------------------------------------------
    */

    const closeSidebar = () => {

        setSidebarOpen(
            false
        );

    };


    /*
    |--------------------------------------------------------------------------
    | Mobile Open
    |--------------------------------------------------------------------------
    */

    const openSidebar = () => {

        setSidebarOpen(
            true
        );

    };


    /*
    |--------------------------------------------------------------------------
    | Desktop Toggle
    |--------------------------------------------------------------------------
    */

    const toggleSidebar = () => {

        /*
        |--------------------------------------------------------------------------
        | Clear Temporary Hover State
        |--------------------------------------------------------------------------
        */

        setSidebarHovered(
            false
        );


        /*
        |--------------------------------------------------------------------------
        | Toggle Permanent State
        |--------------------------------------------------------------------------
        */

        setSidebarCollapsed(
            (current) =>
                !current
        );

    };


    /*
    |--------------------------------------------------------------------------
    | Sidebar Hover
    |--------------------------------------------------------------------------
    */

    const handleSidebarHover = (
        hovered
    ) => {

        /*
        |--------------------------------------------------------------------------
        | Hover Expansion Only Matters When Collapsed
        |--------------------------------------------------------------------------
        */

        if (
            !sidebarCollapsed
        ) {

            setSidebarHovered(
                false
            );

            return;
        }


        setSidebarHovered(
            hovered
        );

    };


    /*
    |--------------------------------------------------------------------------
    | Effective Layout Expansion
    |--------------------------------------------------------------------------
    |
    | This controls the position of:
    |
    | Topbar
    | Workspace
    | Content
    |
    | When collapsed sidebar is hovered, all of them move together.
    |
    */

    const sidebarTemporarilyExpanded =
        sidebarCollapsed &&
        sidebarHovered;


    return (

        <div
            className={[
                'rr-admin-layout',

                sidebarCollapsed
                    ? 'sidebar-collapsed'
                    : 'sidebar-expanded',

                sidebarTemporarilyExpanded
                    ? 'sidebar-hover-expanded'
                    : '',
            ]
                .filter(
                    Boolean
                )
                .join(' ')
            }
        >

            {/* =====================================================
                GLOBAL FLASH NOTIFICATIONS
               ===================================================== */}

            <FlashNotification />


            {/* =====================================================
                SIDEBAR
               ===================================================== */}

            <Sidebar
                open={
                    sidebarOpen
                }

                collapsed={
                    sidebarCollapsed
                }

                onToggle={
                    toggleSidebar
                }

                onHoverChange={
                    handleSidebarHover
                }

                onClose={
                    closeSidebar
                }
            />


            {/* =====================================================
                MAIN APPLICATION
               ===================================================== */}

            <div className="rr-admin-main">

                {/* =================================================
                    TOPBAR
                   ================================================= */}

                <Topbar
                    title={
                        title
                    }

                    subtitle={
                        subtitle
                    }

                    onMenuClick={
                        openSidebar
                    }

                    sidebarCollapsed={
                        sidebarCollapsed
                    }

                    onSidebarToggle={
                        toggleSidebar
                    }
                />


                {/* =================================================
                    WORKSPACE
                   ================================================= */}

                <main className="rr-admin-workspace">

                    {/* =================================================
                        COMMAND BAR
                       ================================================= */}

                    {(
                        breadcrumbs.length > 0 ||
                        actions
                    ) && (

                            <div className="rr-admin-commandbar">

                                {/* =========================================
                                BREADCRUMBS
                               ========================================= */}

                                <div className="rr-admin-breadcrumb">

                                    {/* Dashboard */}

                                    <Link
                                        href="/super-admin/dashboard"
                                        className="rr-admin-breadcrumb-home"
                                        title="Dashboard"
                                    >

                                        <i className="bi bi-house"></i>

                                    </Link>


                                    {/* Breadcrumb Items */}

                                    {breadcrumbs.map(
                                        (
                                            item,
                                            index
                                        ) => {

                                            const last =
                                                index ===
                                                breadcrumbs.length - 1;


                                            return (

                                                <div
                                                    key={
                                                        `${item.label}-${index}`
                                                    }
                                                    className="rr-admin-breadcrumb-item"
                                                >

                                                    <i className="bi bi-chevron-right"></i>


                                                    {item.href && !last ? (

                                                        <Link
                                                            href={
                                                                item.href
                                                            }
                                                        >

                                                            {
                                                                item.label
                                                            }

                                                        </Link>

                                                    ) : (

                                                        <span>

                                                            {
                                                                item.label
                                                            }

                                                        </span>

                                                    )}

                                                </div>

                                            );

                                        }
                                    )}

                                </div>


                                {/* =========================================
                                PAGE ACTIONS
                               ========================================= */}

                                {actions && (

                                    <div className="rr-admin-page-actions">

                                        {actions}

                                    </div>

                                )}

                            </div>

                        )}


                    {/* =================================================
                        PAGE CONTENT
                       ================================================= */}

                    <div className="rr-admin-content">

                        {children}

                    </div>

                </main>

            </div>

        </div>

    );
}