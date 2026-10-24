import '../../../../css/business/users.css';
import { Head, Link } from '@inertiajs/react';
import BusinessLayout from '../../../Layouts/BusinessLayout';
import PageShell from '../../../Components/Business/PageShell';
export default function UserShow({ company, managedUser }) {
    const branches = managedUser?.branches ?? [];
    /*
    |--------------------------------------------------------------------------
    | Role Information
    |--------------------------------------------------------------------------
    */
    const roleType =
        managedUser?.role_type ??
        (managedUser?.is_system_role
            ? 'system'
            : managedUser?.is_company_admin
                ? 'system'
                : 'company');
    const roleTypeLabel = managedUser?.is_company_admin
        ? 'Protected System Role'
        : roleType === 'system'
            ? 'System Role'
            : 'Company Role';
    const roleDescription =
        managedUser?.role_description ??
        (managedUser?.is_company_admin
            ? 'Protected company administrator role. Branch access follows the account assignments.'
            : roleType === 'system'
                ? 'Predefined employee role managed by the system.'
                : 'Custom role created for this company.');
    return (
        <BusinessLayout>
            <Head title={managedUser?.name ?? 'User'} />
            <div className="rr-users-refresh">
                <PageShell
                    eyebrow="Company Management"
                    title="User Details"
                    description="View employee role, branch access and account information."
                    icon="bi-person-vcard"
                    breadcrumb={[
                        {
                            label: 'Company',
                            href: '/company/dashboard',
                        },
                        {
                            label: 'Users & Access',
                            href: '/company/users',
                        },
                        {
                            label: managedUser?.name ?? 'User',
                        },
                    ]}
                    actions={
                        <>
                            <Link
                                href="/company/users"
                                className="rr-page-secondary-btn"
                            >
                                <i className="bi bi-arrow-left"></i>
                                Back
                            </Link>
                            {!managedUser?.is_company_admin && (
                                <Link
                                    href={`/company/users/${managedUser.id}/edit`}
                                    className="rr-page-primary-btn"
                                >
                                    <i className="bi bi-pencil-square"></i>
                                    Edit User
                                </Link>
                            )}
                        </>
                    }
                >
                    {/* =====================================================
                    USER HEADER
                   ===================================================== */}
                    <section className="rr-user-view-header">
                        <div className="rr-user-view-header-main">
                            <div className="rr-user-view-avatar">
                                {getInitials(managedUser?.name)}
                            </div>
                            <div className="rr-user-view-identity">
                                <span>Employee Account</span>
                                <strong>{managedUser?.name ?? 'User'}</strong>
                                <small>
                                    <i className="bi bi-envelope"></i>
                                    {managedUser?.email ?? '—'}
                                </small>
                            </div>
                        </div>
                        <div className="rr-user-view-header-status">
                            {managedUser?.is_current_user && (
                                <span className="rr-user-view-you">You</span>
                            )}
                            <span
                                className={
                                    managedUser?.status
                                        ? 'rr-user-view-status active'
                                        : 'rr-user-view-status inactive'
                                }
                            >
                                <span></span>
                                {managedUser?.status ? 'Active' : 'Inactive'}
                            </span>
                        </div>
                    </section>
                    {/* =====================================================
                    SUMMARY STRIP
                   ===================================================== */}
                    <section className="rr-user-view-summary">
                        <SummaryBox
                            label="Role"
                            value={managedUser?.role ?? 'No Role'}
                            icon={
                                roleType === 'system'
                                    ? 'bi-shield-check'
                                    : 'bi-person-badge'
                            }
                        />
                        <SummaryBox
                            label="Branch Access"
                            value={
                                managedUser?.all_branches
                                    ? 'All Branches'
                                    : `${branches.length} Branch${branches.length === 1 ? '' : 'es'
                                    }`
                            }
                            icon="bi-diagram-3"
                        />
                        <SummaryBox
                            label="Account"
                            value={managedUser?.status ? 'Active' : 'Inactive'}
                            icon={
                                managedUser?.status
                                    ? 'bi-person-check'
                                    : 'bi-person-dash'
                            }
                        />
                        <SummaryBox
                            label="Company"
                            value={
                                company?.company_code ?? company?.company_name ?? '—'
                            }
                            icon="bi-building"
                        />
                    </section>
                    {/* =====================================================
                    ROLE CLASSIFICATION
                   ===================================================== */}
                    <section
                        className={`rr-user-view-role-classification ${roleType === 'system' ? 'system' : 'company'
                            }`}
                    >
                        <div className="rr-user-view-role-classification-icon">
                            <i
                                className={
                                    roleType === 'system'
                                        ? 'bi bi-shield-check'
                                        : 'bi bi-building-lock'
                                }
                            ></i>
                        </div>
                        <div className="rr-user-view-role-classification-main">
                            <span>Access Role</span>
                            <strong>{managedUser?.role ?? 'No Role'}</strong>
                            <small>{roleDescription}</small>
                        </div>
                        <span
                            className={`rr-user-view-role-type-badge ${roleType === 'system' ? 'system' : 'company'
                                }`}
                        >
                            {roleTypeLabel}
                        </span>
                    </section>
                    {/* =====================================================
                    DETAILS GRID
                   ===================================================== */}
                    <div className="rr-user-view-grid">
                        {/* CONTACT */}
                        <section className="rr-page-panel">
                            <div className="rr-page-panel-header">
                                <div className="rr-page-panel-title">
                                    <h2>Contact Information</h2>
                                    <p>Employee contact details</p>
                                </div>
                                <div className="rr-user-view-panel-icon">
                                    <i className="bi bi-person-lines-fill"></i>
                                </div>
                            </div>
                            <div className="rr-user-view-detail-list">
                                <DetailRow
                                    icon="bi-person"
                                    label="Name"
                                    value={managedUser?.name}
                                />
                                <DetailRow
                                    icon="bi-envelope"
                                    label="Email"
                                    value={managedUser?.email}
                                />
                                <DetailRow
                                    icon="bi-telephone"
                                    label="Mobile"
                                    value={managedUser?.mobile}
                                />
                            </div>
                        </section>
                        {/* ACCESS */}
                        <section className="rr-page-panel">
                            <div className="rr-page-panel-header">
                                <div className="rr-page-panel-title">
                                    <h2>Access Information</h2>
                                    <p>Role and access configuration</p>
                                </div>
                                <div className="rr-user-view-panel-icon">
                                    <i className="bi bi-shield-lock"></i>
                                </div>
                            </div>
                            <div className="rr-user-view-detail-list">
                                <DetailRow
                                    icon={
                                        roleType === 'system'
                                            ? 'bi-shield-check'
                                            : 'bi-person-badge'
                                    }
                                    label="Role"
                                    value={managedUser?.role}
                                />
                                <DetailRow
                                    icon={
                                        roleType === 'system'
                                            ? 'bi-shield-lock'
                                            : 'bi-building-lock'
                                    }
                                    label="Role Type"
                                    value={roleTypeLabel}
                                />
                                <DetailRow
                                    icon="bi-card-text"
                                    label="Role Description"
                                    value={roleDescription}
                                />
                                <DetailRow
                                    icon="bi-toggle-on"
                                    label="Account Status"
                                    value={
                                        managedUser?.status ? 'Active' : 'Inactive'
                                    }
                                />
                                <DetailRow
                                    icon="bi-building"
                                    label="Company"
                                    value={company?.company_name}
                                />
                            </div>
                        </section>
                    </div>
                    {/* =====================================================
                    BRANCH ACCESS
                   ===================================================== */}
                    <section className="rr-page-panel rr-user-view-branch-panel">
                        <div className="rr-page-panel-header">
                            <div className="rr-page-panel-title">
                                <h2>Branch Access</h2>
                                <p>
                                    {managedUser?.all_branches
                                        ? 'Company Admin has access to every active company branch.'
                                        : 'Branches assigned to this user.'}
                                </p>
                            </div>
                            <div className="rr-user-view-panel-icon">
                                <i className="bi bi-diagram-3"></i>
                            </div>
                        </div>
                        {managedUser?.all_branches ? (
                            <div className="rr-user-view-all-branches">
                                <div>
                                    <i className="bi bi-shield-check"></i>
                                </div>
                                <section>
                                    <strong>All Active Branches</strong>
                                    <span>
                                        Company Admin access is automatically
                                        available across the company.
                                    </span>
                                </section>
                            </div>
                        ) : branches.length > 0 ? (
                            <div className="rr-user-view-branch-list">
                                {branches.map((branch) => (
                                    <div
                                        key={branch.id}
                                        className="rr-user-view-branch-item"
                                    >
                                        <div className="rr-user-view-branch-icon">
                                            <i className="bi bi-shop"></i>
                                        </div>
                                        <div className="rr-user-view-branch-info">
                                            <strong>{branch.store_name}</strong>
                                            <span>{branch.store_code ?? '—'}</span>
                                        </div>
                                        <span
                                            className={
                                                branch.status
                                                    ? 'rr-user-view-branch-status active'
                                                    : 'rr-user-view-branch-status inactive'
                                            }
                                        >
                                            {branch.status ? 'Active' : 'Inactive'}
                                        </span>
                                    </div>
                                ))}
                            </div>
                        ) : (
                            <div className="rr-user-view-no-branch">
                                <i className="bi bi-exclamation-circle"></i>
                                No branches are currently assigned to this user.
                            </div>
                        )}
                    </section>
                    {/* =====================================================
                    SYSTEM INFORMATION
                   ===================================================== */}
                    <section className="rr-user-view-system">
                        <SystemItem
                            label="User ID"
                            value={managedUser?.id ? `#${managedUser.id}` : '—'}
                            icon="bi-hash"
                        />
                        <SystemItem
                            label="Company Code"
                            value={company?.company_code ?? '—'}
                            icon="bi-upc"
                        />
                        <SystemItem
                            label="Created"
                            value={formatDate(managedUser?.created_at)}
                            icon="bi-calendar-plus"
                        />
                        <SystemItem
                            label="Last Updated"
                            value={formatDate(managedUser?.updated_at)}
                            icon="bi-clock-history"
                        />
                    </section>
                </PageShell>
            </div>
        </BusinessLayout>
    );
}
/*
|--------------------------------------------------------------------------
| Summary Box
|--------------------------------------------------------------------------
*/
function SummaryBox({ label, value, icon }) {
    return (
        <div className="rr-user-view-summary-box">
            <div>
                <i className={`bi ${icon}`}></i>
            </div>
            <section>
                <span>{label}</span>
                <strong>{value || '—'}</strong>
            </section>
        </div>
    );
}
/*
|--------------------------------------------------------------------------
| Detail Row
|--------------------------------------------------------------------------
*/
function DetailRow({ icon, label, value }) {
    return (
        <div className="rr-user-view-detail-row">
            <div className="rr-user-view-detail-icon">
                <i className={`bi ${icon}`}></i>
            </div>
            <div>
                <span>{label}</span>
                <strong className={!value ? 'empty' : ''}>
                    {value || 'Not provided'}
                </strong>
            </div>
        </div>
    );
}
/*
|--------------------------------------------------------------------------
| System Item
|--------------------------------------------------------------------------
*/
function SystemItem({ label, value, icon }) {
    return (
        <div className="rr-user-view-system-item">
            <div>
                <i className={`bi ${icon}`}></i>
            </div>
            <section>
                <span>{label}</span>
                <strong>{value || '—'}</strong>
            </section>
        </div>
    );
}
/*
|--------------------------------------------------------------------------
| Initials
|--------------------------------------------------------------------------
*/
function getInitials(name) {
    if (!name) {
        return 'U';
    }
    const parts = String(name).trim().split(/\s+/).filter(Boolean);
    if (parts.length === 0) {
        return 'U';
    }
    if (parts.length === 1) {
        return parts[0].substring(0, 2).toUpperCase();
    }
    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
}
/*
|--------------------------------------------------------------------------
| Date
|--------------------------------------------------------------------------
*/
function formatDate(value) {
    if (!value) {
        return '—';
    }
    const date = new Date(value);
    if (Number.isNaN(date.getTime())) {
        return '—';
    }
    return date.toLocaleDateString('en-IN', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
}
