import { Link, usePage } from '@inertiajs/react';
import BranchSwitcher from './BranchSwitcher';
import UserMenu from './UserMenu';

export default function Topbar({ onMenuClick }) {

    const {
        auth,
        activeCompany,
        activeStore,
    } = usePage().props;

    const user = auth?.user;


    /*
    |--------------------------------------------------------------------------
    | User initials
    |--------------------------------------------------------------------------
    */

    const getInitials = (name) => {

        if (!name) {
            return 'U';
        }

        const parts = name.trim().split(/\s+/);

        if (parts.length === 1) {
            return parts[0].charAt(0).toUpperCase();
        }

        return (
            parts[0].charAt(0) +
            parts[parts.length - 1].charAt(0)
        ).toUpperCase();
    };


    /*
    |--------------------------------------------------------------------------
    | Role display
    |--------------------------------------------------------------------------
    */

    const getRoleName = () => {

        if (user?.is_super_user) {
            return 'Super Admin';
        }

        if (auth?.roles?.includes('company-owner')) {
            return 'Company Owner';
        }

        if (auth?.roles?.includes('manager')) {
            return 'Manager';
        }

        return 'User';
    };


    return (
        <header className="rr-topbar">

            {/* =====================================================
                LEFT
               ===================================================== */}

            <div className="rr-topbar-left">

                <button
                    type="button"
                    className="rr-topbar-menu-button"
                    onClick={onMenuClick}
                    aria-label="Open navigation"
                >
                    <i className="bi bi-list"></i>
                </button>

                <BranchSwitcher />

            </div>


            {/* =====================================================
                RIGHT
               ===================================================== */}

            <div className="rr-topbar-right">

                {/* Search */}

                <button
                    type="button"
                    className="rr-topbar-icon-button"
                    title="Search"
                >
                    <i className="bi bi-search"></i>
                </button>


                {/* Notifications */}

                <button
                    type="button"
                    className="rr-topbar-icon-button rr-notification-button"
                    title="Notifications"
                >

                    <i className="bi bi-bell"></i>

                    <span className="rr-notification-badge">
                        0
                    </span>

                </button>


                {/* Divider */}

                <div className="rr-topbar-divider"></div>


                {/* User */}

                <UserMenu />

            </div>

        </header>
    );
}