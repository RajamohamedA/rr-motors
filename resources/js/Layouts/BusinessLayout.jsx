import {
    useState,
} from 'react';

import Sidebar
    from '../Components/Business/Sidebar';

import Topbar
    from '../Components/Business/Topbar';

import FlashNotification
    from '../Components/FlashNotification';


export default function BusinessLayout({
    children,
}) {

    /*
    |--------------------------------------------------------------------------
    | Mobile Sidebar
    |--------------------------------------------------------------------------
    */

    const [
        sidebarOpen,
        setSidebarOpen,
    ] = useState(false);


    /*
    |--------------------------------------------------------------------------
    | Desktop Sidebar
    |--------------------------------------------------------------------------
    |
    | false = EXPANDED
    | true  = COLLAPSED
    |
    */

    const [
        sidebarCollapsed,
        setSidebarCollapsed,
    ] = useState(false);


    /*
    |--------------------------------------------------------------------------
    | Collapsed Sidebar Hover
    |--------------------------------------------------------------------------
    */

    const [
        sidebarHovered,
        setSidebarHovered,
    ] = useState(false);


    /*
    |--------------------------------------------------------------------------
    | Effective Visual State
    |--------------------------------------------------------------------------
    */

    const visuallyCollapsed =
        sidebarCollapsed &&
        !sidebarHovered;


    /*
    |--------------------------------------------------------------------------
    | Toggle Permanent State
    |--------------------------------------------------------------------------
    */

    const toggleSidebar =
        () => {

            setSidebarCollapsed(
                current =>
                    !current
            );

            setSidebarHovered(
                false
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */

    return (

        <div
            className={
                `rr-business-layout ${sidebarCollapsed
                    ? 'sidebar-collapsed'
                    : ''
                }`
            }
        >

            <Sidebar
                open={
                    sidebarOpen
                }

                collapsed={
                    visuallyCollapsed
                }

                permanentlyCollapsed={
                    sidebarCollapsed
                }

                onToggleCollapse={
                    toggleSidebar
                }

                onClose={
                    () =>
                        setSidebarOpen(
                            false
                        )
                }

                onMouseEnter={
                    () => {

                        if (
                            sidebarCollapsed
                        ) {

                            setSidebarHovered(
                                true
                            );
                        }
                    }
                }

                onMouseLeave={
                    () => {

                        setSidebarHovered(
                            false
                        );
                    }
                }
            />


            <div className="rr-business-main">

                <Topbar
                    onMenuClick={
                        () =>
                            setSidebarOpen(
                                true
                            )
                    }
                />


                {/* ================================================
                    GLOBAL FLASH NOTIFICATION
                   ================================================ */}

                <FlashNotification />


                <main className="rr-business-content">

                    {children}

                </main>

            </div>

        </div>
    );
}