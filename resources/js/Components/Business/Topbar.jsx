import { Link, router, usePage } from '@inertiajs/react';
import { useEffect, useMemo, useRef, useState } from 'react';

export default function BusinessTopbar({ onMenuClick = null }) {
    const { auth = {}, business = {} } = usePage().props;
    const user = auth?.user ?? null;

    const roleNames = useMemo(() => {
        const rawRoles = Array.isArray(user?.roles) ? user.roles : [];

        return rawRoles
            .map((role) => (typeof role === 'string' ? role : role?.name))
            .filter(Boolean)
            .map((role) => String(role).trim().toLowerCase().replaceAll('_', ' '));
    }, [user?.roles]);

    const permissionNames = useMemo(() => {
        const rawPermissions = Array.isArray(user?.permissions)
            ? user.permissions
            : Array.isArray(auth?.permissions)
                ? auth.permissions
                : [];

        return rawPermissions
            .map((permission) => (
                typeof permission === 'string' ? permission : permission?.name
            ))
            .filter(Boolean)
            .map((permission) => String(permission).trim().toLowerCase());
    }, [auth?.permissions, user?.permissions]);

    const isSuperAdmin = Boolean(user?.is_super_user || business?.is_super_admin_context);
    const isCompanyAdmin = roleNames.includes('company admin');
    const hasCompanyDashboardPermission = permissionNames.some((permission) => [
        'company-dashboard.view',
        'company.dashboard.view',
        'company.view',
    ].includes(permission));

    const canManageCompanyWorkspace = Boolean(
        isSuperAdmin
        || isCompanyAdmin
        || hasCompanyDashboardPermission
        || business?.can_manage_company_workspace
        || business?.can_access_company_dashboard
    );

    const companyName = business?.company_name ?? 'Company';
    const activeStoreId = business?.store_id ?? null;
    const activeStoreName = business?.store_name ?? null;
    const activeStoreCode = business?.store_code ?? null;
    const hasActiveBranch = Boolean(activeStoreId);
    const availableBranches = Array.isArray(business?.available_branches)
        ? business.available_branches
        : [];

    const [branchDropdownOpen, setBranchDropdownOpen] = useState(false);
    const [profileDropdownOpen, setProfileDropdownOpen] = useState(false);
    const branchDropdownRef = useRef(null);
    const profileDropdownRef = useRef(null);

    useEffect(() => {
        const closeOutside = (event) => {
            if (
                branchDropdownRef.current
                && !branchDropdownRef.current.contains(event.target)
            ) {
                setBranchDropdownOpen(false);
            }

            if (
                profileDropdownRef.current
                && !profileDropdownRef.current.contains(event.target)
            ) {
                setProfileDropdownOpen(false);
            }
        };

        const closeOnEscape = (event) => {
            if (event.key === 'Escape') {
                setBranchDropdownOpen(false);
                setProfileDropdownOpen(false);
            }
        };

        document.addEventListener('mousedown', closeOutside);
        document.addEventListener('keydown', closeOnEscape);

        return () => {
            document.removeEventListener('mousedown', closeOutside);
            document.removeEventListener('keydown', closeOnEscape);
        };
    }, []);

    const switchBranch = (branch) => {
        if (!branch?.id) return;

        if (branch.is_current || Number(branch.id) === Number(activeStoreId)) {
            setBranchDropdownOpen(false);
            return;
        }

        setBranchDropdownOpen(false);
        router.post(`/select-branch/${branch.id}`, {}, {
            preserveScroll: false,
            preserveState: false,
        });
    };

    const exitBusinessContext = () => {
        setProfileDropdownOpen(false);
        router.post('/branch/exit');
    };

    const logout = () => {
        setProfileDropdownOpen(false);
        router.post('/logout');
    };

    const userInitials = getInitials(user?.name);
    const roleLabel = isSuperAdmin
        ? 'Super Administrator'
        : (roleNames[0] ? titleCase(roleNames[0]) : 'Business User');

    return (
        <header className="rr-business-topbar">
            <div className="rr-business-topbar-left">
                <button
                    type="button"
                    className="rr-business-topbar-mobile-menu"
                    onClick={onMenuClick}
                    aria-label="Open navigation"
                    title="Menu"
                >
                    <i className="bi bi-list" />
                </button>

                <div className="rr-business-branch-switcher" ref={branchDropdownRef}>
                    <button
                        type="button"
                        className={`rr-business-branch-switcher-button ${branchDropdownOpen ? 'open' : ''}`}
                        onClick={() => {
                            setBranchDropdownOpen((current) => !current);
                            setProfileDropdownOpen(false);
                        }}
                        aria-expanded={branchDropdownOpen}
                    >
                        <div className="rr-business-branch-switcher-icon">
                            <i className={hasActiveBranch ? 'bi bi-shop' : 'bi bi-buildings'} />
                        </div>

                        <div className="rr-business-branch-switcher-info">
                            <span>{hasActiveBranch ? 'Current Branch' : 'Workspace'}</span>
                            <strong>{hasActiveBranch ? (activeStoreName ?? 'Branch') : 'Company Workspace'}</strong>
                            {hasActiveBranch && activeStoreCode && <small>{activeStoreCode}</small>}
                        </div>

                        <i className={`bi bi-chevron-down rr-business-branch-switcher-chevron ${branchDropdownOpen ? 'open' : ''}`} />
                    </button>

                    {branchDropdownOpen && (
                        <div className="rr-business-branch-dropdown">
                            <div className="rr-business-branch-dropdown-header">
                                <div>
                                    <span>Switch Branch</span>
                                    <strong>{companyName}</strong>
                                    {isSuperAdmin && (
                                        <small className="rr-business-superadmin-context-label">
                                            <i className="bi bi-shield-check" />
                                            Super Admin Context
                                        </small>
                                    )}
                                </div>
                                <span className="rr-business-branch-count">{availableBranches.length}</span>
                            </div>

                            {canManageCompanyWorkspace && (
                                <Link
                                    href="/company/dashboard"
                                    className={`rr-business-branch-dropdown-company ${!hasActiveBranch ? 'active' : ''}`}
                                    onClick={() => setBranchDropdownOpen(false)}
                                >
                                    <div className="rr-business-branch-item-icon">
                                        <i className="bi bi-speedometer2" />
                                    </div>
                                    <div className="rr-business-branch-item-info">
                                        <strong>Company Dashboard</strong>
                                        <span>Manage company, branches and users</span>
                                    </div>
                                    {!hasActiveBranch && (
                                        <i className="bi bi-check-circle-fill rr-business-branch-current-icon" />
                                    )}
                                </Link>
                            )}

                            {canManageCompanyWorkspace && availableBranches.length > 0 && (
                                <div className="rr-business-branch-dropdown-separator" />
                            )}

                            <div className="rr-business-branch-dropdown-list">
                                {availableBranches.length > 0 ? (
                                    availableBranches.map((branch) => {
                                        const isCurrent = Boolean(branch.is_current)
                                            || Number(branch.id) === Number(activeStoreId);

                                        return (
                                            <button
                                                type="button"
                                                key={branch.id}
                                                className={`rr-business-branch-dropdown-item ${isCurrent ? 'active' : ''}`}
                                                onClick={() => switchBranch(branch)}
                                            >
                                                <div className="rr-business-branch-item-icon">
                                                    <i className="bi bi-shop" />
                                                </div>
                                                <div className="rr-business-branch-item-info">
                                                    <strong>{branch.store_name ?? 'Branch'}</strong>
                                                    <span>
                                                        {[branch.store_code, branch.city]
                                                            .filter(Boolean)
                                                            .join(' • ')}
                                                    </span>
                                                </div>
                                                <i className={`bi ${isCurrent ? 'bi-check-circle-fill rr-business-branch-current-icon' : 'bi-chevron-right rr-business-branch-arrow'}`} />
                                            </button>
                                        );
                                    })
                                ) : (
                                    <div className="rr-business-branch-dropdown-empty">
                                        <i className="bi bi-shop-window" />
                                        <strong>No active branches</strong>
                                        <span>There are currently no active branches available.</span>
                                    </div>
                                )}
                            </div>

                            {canManageCompanyWorkspace && (
                                <div className="rr-business-branch-dropdown-footer">
                                    <Link
                                        href="/company/branches"
                                        onClick={() => setBranchDropdownOpen(false)}
                                    >
                                        <i className="bi bi-diagram-3" />
                                        Manage Branches
                                    </Link>
                                </div>
                            )}
                        </div>
                    )}
                </div>
            </div>

            <div className="rr-business-topbar-right">
                {canManageCompanyWorkspace && (
                    <Link
                        href="/company/dashboard"
                        className="rr-business-topbar-exit-context rr-business-topbar-company-dashboard"
                        title="Open Company Dashboard"
                    >
                        <i className="bi bi-speedometer2" />
                        <span>Company Dashboard</span>
                    </Link>
                )}

                {business?.is_super_admin_context && (
                    <button
                        type="button"
                        className="rr-business-topbar-exit-context"
                        onClick={exitBusinessContext}
                        title="Exit company workspace"
                    >
                        <i className="bi bi-arrow-left-circle" />
                        <span>Exit Company</span>
                    </button>
                )}

                <div className="rr-business-profile-dropdown-wrapper" ref={profileDropdownRef}>
                    <button
                        type="button"
                        className="rr-business-topbar-profile"
                        onClick={() => {
                            setProfileDropdownOpen((current) => !current);
                            setBranchDropdownOpen(false);
                        }}
                        aria-expanded={profileDropdownOpen}
                    >
                        <div className="rr-business-topbar-avatar">{userInitials}</div>
                        <div className="rr-business-topbar-user-info">
                            <strong>{user?.name ?? 'User'}</strong>
                            <span>{roleLabel}</span>
                        </div>
                        <i className={`bi bi-chevron-down ${profileDropdownOpen ? 'open' : ''}`} />
                    </button>

                    {profileDropdownOpen && (
                        <div className="rr-business-profile-dropdown">
                            <div className="rr-business-profile-dropdown-header">
                                <div className="rr-business-profile-dropdown-avatar">{userInitials}</div>
                                <div>
                                    <strong>{user?.name ?? 'User'}</strong>
                                    <span>{user?.email ?? ''}</span>
                                </div>
                            </div>

                            <div className="rr-business-profile-dropdown-menu">
                                <Link href="/profile" onClick={() => setProfileDropdownOpen(false)}>
                                    <i className="bi bi-person" />
                                    My Profile
                                </Link>

                                {canManageCompanyWorkspace && (
                                    <Link
                                        href="/company/dashboard"
                                        onClick={() => setProfileDropdownOpen(false)}
                                    >
                                        <i className="bi bi-speedometer2" />
                                        Company Dashboard
                                    </Link>
                                )}

                                {business?.is_super_admin_context && (
                                    <button type="button" onClick={exitBusinessContext}>
                                        <i className="bi bi-arrow-left-circle" />
                                        Back to Super Admin
                                    </button>
                                )}

                                <Link href="/settings" onClick={() => setProfileDropdownOpen(false)}>
                                    <i className="bi bi-gear" />
                                    Settings
                                </Link>

                                <button
                                    type="button"
                                    className="rr-business-profile-logout"
                                    onClick={logout}
                                >
                                    <i className="bi bi-box-arrow-right" />
                                    Logout
                                </button>
                            </div>
                        </div>
                    )}
                </div>
            </div>
        </header>
    );
}

function getInitials(name) {
    const parts = String(name ?? '')
        .trim()
        .split(/\s+/)
        .filter(Boolean);

    if (parts.length === 0) return 'U';
    if (parts.length === 1) return parts[0].substring(0, 2).toUpperCase();

    return `${parts[0][0]}${parts.at(-1)[0]}`.toUpperCase();
}

function titleCase(value) {
    return String(value)
        .split(' ')
        .filter(Boolean)
        .map((word) => word.charAt(0).toUpperCase() + word.slice(1))
        .join(' ');
}
