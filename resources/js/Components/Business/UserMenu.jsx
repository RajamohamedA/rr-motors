import { Link, usePage } from '@inertiajs/react';

export default function UserMenu() {

    const { auth } = usePage().props;

    const user = auth?.user;
    const roles = auth?.roles ?? [];

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


    const getRoleName = () => {

        if (user?.is_super_user) {
            return 'Super Admin';
        }

        if (roles.includes('company-owner')) {
            return 'Company Owner';
        }

        if (roles.includes('branch-manager')) {
            return 'Branch Manager';
        }

        if (roles.length > 0) {
            return roles[0]
                .replaceAll('-', ' ')
                .replace(/\b\w/g, (char) => char.toUpperCase());
        }

        return 'User';
    };


    return (
        <div className="dropdown rr-user-menu">

            <button
                type="button"
                className="rr-user-menu-trigger"
                data-bs-toggle="dropdown"
                aria-expanded="false"
            >

                <div className="rr-user-menu-avatar">
                    {getInitials(user?.name)}
                </div>

                <div className="rr-user-menu-info">

                    <div className="rr-user-menu-name">
                        {user?.name ?? 'User'}
                    </div>

                    <div className="rr-user-menu-role">
                        {getRoleName()}
                    </div>

                </div>

                <i className="bi bi-chevron-down rr-user-menu-arrow"></i>

            </button>


            <div className="dropdown-menu dropdown-menu-end rr-user-dropdown">

                <div className="rr-user-dropdown-header">

                    <div className="rr-user-dropdown-avatar">
                        {getInitials(user?.name)}
                    </div>

                    <div>

                        <div className="rr-user-dropdown-name">
                            {user?.name}
                        </div>

                        <div className="rr-user-dropdown-email">
                            {user?.email}
                        </div>

                    </div>

                </div>


                <div className="dropdown-divider"></div>


                <Link
                    href="/profile"
                    className="dropdown-item rr-user-dropdown-item"
                >
                    <span className="rr-user-dropdown-icon">
                        <i className="bi bi-person"></i>
                    </span>

                    <span>
                        My Profile
                    </span>
                </Link>


                <Link
                    href="/profile"
                    className="dropdown-item rr-user-dropdown-item"
                >
                    <span className="rr-user-dropdown-icon">
                        <i className="bi bi-key"></i>
                    </span>

                    <span>
                        Account Security
                    </span>
                </Link>


                {user?.is_super_user && (
                    <>
                        <div className="dropdown-divider"></div>

                        <Link
                            href="/dashboard"
                            className="dropdown-item rr-user-dropdown-item"
                        >
                            <span className="rr-user-dropdown-icon">
                                <i className="bi bi-shield-lock"></i>
                            </span>

                            <span>
                                Super Admin Portal
                            </span>
                        </Link>
                    </>
                )}


                {roles.includes('company-owner') && (
                    <>
                        <div className="dropdown-divider"></div>

                        <Link
                            href="/select-branch"
                            className="dropdown-item rr-user-dropdown-item"
                        >
                            <span className="rr-user-dropdown-icon">
                                <i className="bi bi-shop"></i>
                            </span>

                            <span>
                                Branch Selection
                            </span>
                        </Link>
                    </>
                )}


                <div className="dropdown-divider"></div>


                <Link
                    href="/logout"
                    method="post"
                    as="button"
                    className="dropdown-item rr-user-dropdown-item rr-user-dropdown-logout"
                >

                    <span className="rr-user-dropdown-icon">
                        <i className="bi bi-box-arrow-right"></i>
                    </span>

                    <span>
                        Logout
                    </span>

                </Link>

            </div>

        </div>
    );
}