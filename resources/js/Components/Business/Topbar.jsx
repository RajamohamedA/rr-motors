import {
    Link,
    router,
    usePage,
} from '@inertiajs/react';

import {
    useEffect,
    useRef,
    useState,
} from 'react';


export default function BusinessTopbar({
    onMenuClick = null,
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
        auth?.user ?? null;


    const roles =
        user?.roles ?? [];


    const isSuperAdmin =
        Boolean(
            user?.is_super_user
        );


    const isCompanyAdmin =
        roles.includes(
            'Company Admin'
        );


    const canManageCompanyWorkspace =
        isSuperAdmin ||
        isCompanyAdmin;


    /*
    |--------------------------------------------------------------------------
    | Business Context
    |--------------------------------------------------------------------------
    */

    const companyName =
        business?.company_name
        ?? 'Company';


    const activeStoreId =
        business?.store_id
        ?? null;


    const activeStoreName =
        business?.store_name
        ?? null;


    const activeStoreCode =
        business?.store_code
        ?? null;


    const hasActiveBranch =
        Boolean(
            activeStoreId
        );


    const availableBranches =
        Array.isArray(
            business?.available_branches
        )
            ? business.available_branches
            : [];


    /*
    |--------------------------------------------------------------------------
    | Dropdown State
    |--------------------------------------------------------------------------
    */

    const [
        branchDropdownOpen,
        setBranchDropdownOpen,
    ] = useState(false);


    const [
        profileDropdownOpen,
        setProfileDropdownOpen,
    ] = useState(false);


    const branchDropdownRef =
        useRef(null);


    const profileDropdownRef =
        useRef(null);


    /*
    |--------------------------------------------------------------------------
    | Close Dropdowns
    |--------------------------------------------------------------------------
    */

    useEffect(
        () => {

            const handleOutsideClick =
                (event) => {

                    if (
                        branchDropdownRef.current &&
                        !branchDropdownRef.current.contains(
                            event.target
                        )
                    ) {
                        setBranchDropdownOpen(
                            false
                        );
                    }


                    if (
                        profileDropdownRef.current &&
                        !profileDropdownRef.current.contains(
                            event.target
                        )
                    ) {
                        setProfileDropdownOpen(
                            false
                        );
                    }
                };


            const handleEscape =
                (event) => {

                    if (
                        event.key === 'Escape'
                    ) {
                        setBranchDropdownOpen(
                            false
                        );

                        setProfileDropdownOpen(
                            false
                        );
                    }
                };


            document.addEventListener(
                'mousedown',
                handleOutsideClick
            );


            document.addEventListener(
                'keydown',
                handleEscape
            );


            return () => {

                document.removeEventListener(
                    'mousedown',
                    handleOutsideClick
                );


                document.removeEventListener(
                    'keydown',
                    handleEscape
                );
            };

        },
        []
    );


    /*
    |--------------------------------------------------------------------------
    | Switch Branch
    |--------------------------------------------------------------------------
    */

    const switchBranch =
        (branch) => {

            if (
                !branch?.id
            ) {
                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Already Active
            |--------------------------------------------------------------------------
            */

            if (
                branch.is_current ||
                Number(branch.id) ===
                Number(activeStoreId)
            ) {
                setBranchDropdownOpen(
                    false
                );

                return;
            }


            setBranchDropdownOpen(
                false
            );


            /*
            |--------------------------------------------------------------------------
            | Select Branch
            |--------------------------------------------------------------------------
            |
            | Uses:
            |
            | BranchContextController@select
            |
            */

            router.post(
                `/select-branch/${branch.id}`,
                {},
                {
                    preserveScroll: false,
                    preserveState: false,
                }
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Company Workspace
    |--------------------------------------------------------------------------
    */

    const goToCompanyWorkspace =
        () => {

            setBranchDropdownOpen(
                false
            );


            router.visit(
                '/company/dashboard'
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Exit Super Admin Business Context
    |--------------------------------------------------------------------------
    */

    const exitBusinessContext =
        () => {

            setProfileDropdownOpen(
                false
            );


            router.post(
                '/branch/exit'
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Logout
    |--------------------------------------------------------------------------
    */

    const logout =
        () => {

            setProfileDropdownOpen(
                false
            );


            router.post(
                '/logout'
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Initials
    |--------------------------------------------------------------------------
    */

    const userInitials =
        getInitials(
            user?.name
        );


    /*
    |--------------------------------------------------------------------------
    | Role Label
    |--------------------------------------------------------------------------
    */

    const roleLabel =
        isSuperAdmin
            ? 'Super Administrator'
            : (
                roles[0]
                ?? 'Business User'
            );


    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */

    return (

        <header className="rr-business-topbar">

            {/* =========================================================
                LEFT
               ========================================================= */}

            <div className="rr-business-topbar-left">

                {/* =====================================================
                    MOBILE MENU
                   ===================================================== */}

                <button
                    type="button"
                    className="rr-business-topbar-mobile-menu"
                    onClick={
                        onMenuClick
                    }
                    aria-label="Open navigation"
                    title="Menu"
                >
                    <i className="bi bi-list"></i>
                </button>


                {/* =====================================================
                    BRANCH SWITCHER
                   ===================================================== */}

                <div
                    className="rr-business-branch-switcher"
                    ref={
                        branchDropdownRef
                    }
                >

                    <button
                        type="button"
                        className={
                            `rr-business-branch-switcher-button ${branchDropdownOpen
                                ? 'open'
                                : ''
                            }`
                        }
                        onClick={
                            () => {

                                setBranchDropdownOpen(
                                    current =>
                                        !current
                                );


                                setProfileDropdownOpen(
                                    false
                                );
                            }
                        }
                        aria-expanded={
                            branchDropdownOpen
                        }
                    >

                        <div className="rr-business-branch-switcher-icon">

                            <i
                                className={
                                    hasActiveBranch
                                        ? 'bi bi-shop'
                                        : 'bi bi-buildings'
                                }
                            ></i>

                        </div>


                        <div className="rr-business-branch-switcher-info">

                            <span>
                                {
                                    hasActiveBranch
                                        ? 'Current Branch'
                                        : 'Workspace'
                                }
                            </span>


                            <strong>
                                {
                                    hasActiveBranch
                                        ? (
                                            activeStoreName
                                            ?? 'Branch'
                                        )
                                        : 'Company Workspace'
                                }
                            </strong>


                            {
                                hasActiveBranch &&
                                activeStoreCode && (

                                    <small>
                                        {activeStoreCode}
                                    </small>

                                )
                            }

                        </div>


                        <i
                            className={
                                `bi bi-chevron-down ` +
                                `rr-business-branch-switcher-chevron ${branchDropdownOpen
                                    ? 'open'
                                    : ''
                                }`
                            }
                        ></i>

                    </button>


                    {/* =================================================
                        BRANCH DROPDOWN
                       ================================================= */}

                    {
                        branchDropdownOpen && (

                            <div className="rr-business-branch-dropdown">

                                {/* =====================================
                                    HEADER
                                   ===================================== */}

                                <div className="rr-business-branch-dropdown-header">

                                    <div>

                                        <span>
                                            Switch Branch
                                        </span>

                                        <strong>
                                            {companyName}
                                        </strong>


                                        {
                                            isSuperAdmin && (

                                                <small className="rr-business-superadmin-context-label">

                                                    <i className="bi bi-shield-check"></i>

                                                    Super Admin Context

                                                </small>

                                            )
                                        }

                                    </div>


                                    <span className="rr-business-branch-count">

                                        {
                                            availableBranches.length
                                        }

                                    </span>

                                </div>


                                {/* =====================================
                                    COMPANY WORKSPACE
                                   ===================================== */}

                                {
                                    canManageCompanyWorkspace && (

                                        <button
                                            type="button"
                                            className={
                                                `rr-business-branch-dropdown-company ${!hasActiveBranch
                                                    ? 'active'
                                                    : ''
                                                }`
                                            }
                                            onClick={
                                                goToCompanyWorkspace
                                            }
                                        >

                                            <div className="rr-business-branch-item-icon">

                                                <i className="bi bi-buildings"></i>

                                            </div>


                                            <div className="rr-business-branch-item-info">

                                                <strong>
                                                    Company Workspace
                                                </strong>

                                                <span>
                                                    Manage company, branches and users
                                                </span>

                                            </div>


                                            {
                                                !hasActiveBranch && (

                                                    <i className="bi bi-check-circle-fill rr-business-branch-current-icon"></i>

                                                )
                                            }

                                        </button>

                                    )
                                }


                                {
                                    canManageCompanyWorkspace &&
                                    availableBranches.length > 0 && (

                                        <div className="rr-business-branch-dropdown-separator"></div>

                                    )
                                }


                                {/* =====================================
                                    BRANCHES
                                   ===================================== */}

                                <div className="rr-business-branch-dropdown-list">

                                    {
                                        availableBranches.length > 0
                                            ? (

                                                availableBranches.map(
                                                    branch => {

                                                        const isCurrent =
                                                            Boolean(
                                                                branch.is_current
                                                            ) ||
                                                            Number(branch.id) ===
                                                            Number(activeStoreId);


                                                        return (

                                                            <button
                                                                type="button"
                                                                key={
                                                                    branch.id
                                                                }
                                                                className={
                                                                    `rr-business-branch-dropdown-item ${isCurrent
                                                                        ? 'active'
                                                                        : ''
                                                                    }`
                                                                }
                                                                onClick={
                                                                    () =>
                                                                        switchBranch(
                                                                            branch
                                                                        )
                                                                }
                                                            >

                                                                <div className="rr-business-branch-item-icon">

                                                                    <i className="bi bi-shop"></i>

                                                                </div>


                                                                <div className="rr-business-branch-item-info">

                                                                    <strong>
                                                                        {
                                                                            branch.store_name
                                                                            ?? 'Branch'
                                                                        }
                                                                    </strong>


                                                                    <span>

                                                                        {
                                                                            [
                                                                                branch.store_code,
                                                                                branch.city,
                                                                            ]
                                                                                .filter(
                                                                                    Boolean
                                                                                )
                                                                                .join(
                                                                                    ' • '
                                                                                )
                                                                        }

                                                                    </span>

                                                                </div>


                                                                {
                                                                    isCurrent
                                                                        ? (

                                                                            <i className="bi bi-check-circle-fill rr-business-branch-current-icon"></i>

                                                                        )
                                                                        : (

                                                                            <i className="bi bi-chevron-right rr-business-branch-arrow"></i>

                                                                        )
                                                                }

                                                            </button>

                                                        );
                                                    }
                                                )

                                            )
                                            : (

                                                <div className="rr-business-branch-dropdown-empty">

                                                    <i className="bi bi-shop-window"></i>

                                                    <strong>
                                                        No active branches
                                                    </strong>

                                                    <span>
                                                        There are currently no active branches available.
                                                    </span>

                                                </div>

                                            )
                                    }

                                </div>


                                {/* =====================================
                                    MANAGE BRANCHES
                                   ===================================== */}

                                {
                                    canManageCompanyWorkspace && (

                                        <div className="rr-business-branch-dropdown-footer">

                                            <Link
                                                href="/company/branches"
                                                onClick={
                                                    () =>
                                                        setBranchDropdownOpen(
                                                            false
                                                        )
                                                }
                                            >

                                                <i className="bi bi-diagram-3"></i>

                                                Manage Branches

                                            </Link>

                                        </div>

                                    )
                                }

                            </div>

                        )
                    }

                </div>

            </div>


            {/* =========================================================
                RIGHT
               ========================================================= */}

            <div className="rr-business-topbar-right">

                {/* =====================================================
                    SUPER ADMIN EXIT
                   ===================================================== */}

                {
                    business?.is_super_admin_context && (

                        <button
                            type="button"
                            className="rr-business-topbar-exit-context"
                            onClick={
                                exitBusinessContext
                            }
                            title="Exit company workspace"
                        >

                            <i className="bi bi-arrow-left-circle"></i>

                            <span>
                                Exit Company
                            </span>

                        </button>

                    )
                }


                {/* =====================================================
                    PROFILE
                   ===================================================== */}

                <div
                    className="rr-business-profile-dropdown-wrapper"
                    ref={
                        profileDropdownRef
                    }
                >

                    <button
                        type="button"
                        className="rr-business-topbar-profile"
                        onClick={
                            () => {

                                setProfileDropdownOpen(
                                    current =>
                                        !current
                                );


                                setBranchDropdownOpen(
                                    false
                                );
                            }
                        }
                        aria-expanded={
                            profileDropdownOpen
                        }
                    >

                        <div className="rr-business-topbar-avatar">

                            {userInitials}

                        </div>


                        <div className="rr-business-topbar-user-info">

                            <strong>
                                {
                                    user?.name
                                    ?? 'User'
                                }
                            </strong>

                            <span>
                                {roleLabel}
                            </span>

                        </div>


                        <i
                            className={
                                `bi bi-chevron-down ${profileDropdownOpen
                                    ? 'open'
                                    : ''
                                }`
                            }
                        ></i>

                    </button>


                    {/* =================================================
                        PROFILE DROPDOWN
                       ================================================= */}

                    {
                        profileDropdownOpen && (

                            <div className="rr-business-profile-dropdown">

                                {/* =====================================
                                    USER
                                   ===================================== */}

                                <div className="rr-business-profile-dropdown-header">

                                    <div className="rr-business-profile-dropdown-avatar">

                                        {userInitials}

                                    </div>


                                    <div>

                                        <strong>
                                            {
                                                user?.name
                                                ?? 'User'
                                            }
                                        </strong>

                                        <span>
                                            {
                                                user?.email
                                                ?? ''
                                            }
                                        </span>

                                    </div>

                                </div>


                                {/* =====================================
                                    MENU
                                   ===================================== */}

                                <div className="rr-business-profile-dropdown-menu">

                                    <Link
                                        href="/profile"
                                        onClick={
                                            () =>
                                                setProfileDropdownOpen(
                                                    false
                                                )
                                        }
                                    >

                                        <i className="bi bi-person"></i>

                                        My Profile

                                    </Link>


                                    {
                                        canManageCompanyWorkspace && (

                                            <Link
                                                href="/company/dashboard"
                                                onClick={
                                                    () =>
                                                        setProfileDropdownOpen(
                                                            false
                                                        )
                                                }
                                            >

                                                <i className="bi bi-buildings"></i>

                                                Company Dashboard

                                            </Link>

                                        )
                                    }


                                    {
                                        business?.is_super_admin_context && (

                                            <button
                                                type="button"
                                                onClick={
                                                    exitBusinessContext
                                                }
                                            >

                                                <i className="bi bi-arrow-left-circle"></i>

                                                Back to Super Admin

                                            </button>

                                        )
                                    }


                                    <Link
                                        href="/settings"
                                        onClick={
                                            () =>
                                                setProfileDropdownOpen(
                                                    false
                                                )
                                        }
                                    >

                                        <i className="bi bi-gear"></i>

                                        Settings

                                    </Link>


                                    <button
                                        type="button"
                                        className="rr-business-profile-logout"
                                        onClick={
                                            logout
                                        }
                                    >

                                        <i className="bi bi-box-arrow-right"></i>

                                        Logout

                                    </button>

                                </div>

                            </div>

                        )
                    }

                </div>

            </div>

        </header>
    );
}


/*
|--------------------------------------------------------------------------
| Get Initials
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
        parts.length === 0
    ) {
        return 'U';
    }


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