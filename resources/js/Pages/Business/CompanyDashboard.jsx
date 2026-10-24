import React, { useMemo, useRef, useState } from 'react';
import { Head, Link, router, usePage } from '@inertiajs/react';
import BusinessLayout from '../../Layouts/BusinessLayout';
import '../../../css/business/company-dashboard.css';
export default function CompanyDashboard({
    company,
    activeBranch = null,
    branchSummary = {},
    userSummary = {},
    branches = [],
    context = {},
}) {
    const { auth = {}, business = {} } = usePage().props;
    const [failedLogo, setFailedLogo] = useState(null);
    const allowedIds = new Set((business.assigned_branch_ids
        ?? (business.available_branches ?? []).map((item) => item.id)).map(String));
    const hasAccess = (item) => Boolean(auth.user?.is_super_user) || allowedIds.has(String(item?.id));
    const rawLogo = business.company_logo ?? company?.logo ?? '';
    const logoPath = String(rawLogo).replace(/^\/+/, '');
    const companyLogo = /^https?:\/\//i.test(rawLogo) ? rawLogo
        : logoPath ? (logoPath.startsWith('storage/') ? `/${logoPath}` : `/storage/${logoPath}`) : '';
    const logoKey = `${company?.id ?? business.company_id}:${companyLogo}`;
    const [search, setSearch] = useState('');
    const [status, setStatus] = useState('all');
    const [switchingId, setSwitchingId] = useState(null);
    const [switchError, setSwitchError] = useState('');
    const pendingRequest = useRef(false);
    const isActive = (value) => value === true || value === 1 || value === '1';
    const filteredBranches = useMemo(() => {
        const needle = search.trim().toLowerCase();
        return branches.filter((branch) => {
            const matchesStatus = status === 'all'
                || (status === 'active' && isActive(branch.status))
                || (status === 'pending' && !isActive(branch.status));
            const searchable = [
                branch.store_name,
                branch.store_code,
                branch.city,
                branch.state,
            ].filter(Boolean).join(' ').toLowerCase();
            return matchesStatus && (!needle || searchable.includes(needle));
        });
    }, [branches, search, status]);
    const selectBranch = (branch) => {
        if (pendingRequest.current || !branch?.id || !hasAccess(branch) || !isActive(branch.status) || Number(activeBranch?.id) === Number(branch.id)) {
            return;
        }
        pendingRequest.current = true;
        setSwitchError('');
        setSwitchingId(branch.id);
        router.post(`/select-branch/${branch.id}`, {}, {
            preserveScroll: false,
            preserveState: false,
            onError: () => setSwitchError('Unable to switch branches. Please try again.'),
            onFinish: () => {
                pendingRequest.current = false;
                setSwitchingId(null);
            },
        });
    };
    return (
        <BusinessLayout
            title="Company Dashboard"
            subtitle={company?.company_name ?? 'Company Workspace'}
        >
            <Head title={`${company?.company_name ?? 'Company'} Dashboard`} />
            <main className="rr-company-dashboard">
                <section className="rr-company-hero">
                    <div className="rr-company-hero-orb orb-one" />
                    <div className="rr-company-hero-orb orb-two" />
                    <div className="rr-company-hero-content">
                        <div className="rr-company-hero-icon">
                            {companyLogo && failedLogo !== logoKey ? (
                                <img key={logoKey} src={companyLogo}
                                    alt={`${company?.company_name ?? 'Company'} logo`}
                                    onError={() => setFailedLogo(logoKey)} />
                            ) : <i className="bi bi-buildings-fill" />}
                        </div>
                        <div>
                            <span className="rr-company-eyebrow">
                                <i className="bi bi-shield-check" />
                                {context.is_super_admin ? 'Super Admin Workspace' : 'Company Administration'}
                            </span>
                            <h1>{company?.company_name ?? 'Company Dashboard'}</h1>
                            <p>
                                Monitor branches and users, then enter the branch you want to operate.
                            </p>
                            <div className="rr-company-identity">
                                <span><i className="bi bi-upc-scan" /> {company?.company_code || 'No code'}</span>
                                <span><i className="bi bi-geo-alt" /> {[company?.city, company?.state].filter(Boolean).join(', ') || 'Location not set'}</span>
                            </div>
                        </div>
                    </div>
                    <div className="rr-company-hero-actions">
                        <Link className="rr-company-action light" href="/company/branches">
                            <i className="bi bi-diagram-3" />
                            Manage Branches
                        </Link>
                        <Link className="rr-company-action accent" href="/company/users">
                            <i className="bi bi-people" />
                            Manage Users
                        </Link>
                    </div>
                </section>
                <section className="rr-company-metrics" aria-label="Company summary">
                    <MetricCard
                        tone="blue"
                        icon="bi-shop-window"
                        label="Total Branches"
                        value={branchSummary.total ?? branches.length}
                        note="Registered locations"
                    />
                    <MetricCard
                        tone="green"
                        icon="bi-patch-check"
                        label="Active Branches"
                        value={branchSummary.active ?? branches.filter((branch) => isActive(branch.status)).length}
                        note="Ready for operation"
                    />
                    <MetricCard
                        tone="amber"
                        icon="bi-hourglass-split"
                        label="Pending Branches"
                        value={branchSummary.pending ?? branches.filter((branch) => !isActive(branch.status)).length}
                        note="Awaiting activation"
                    />
                    <MetricCard
                        tone="violet"
                        icon="bi-people-fill"
                        label="Company Users"
                        value={userSummary.total}
                        note={`${number(userSummary.active)} active • ${number(userSummary.inactive)} inactive`}
                    />
                </section>
                <section className="rr-company-command-grid">
                    <article className="rr-company-active-card">
                        <div className="rr-company-section-heading">
                            <div className="rr-company-heading-icon cyan">
                                <i className="bi bi-pin-map-fill" />
                            </div>
                            <div>
                                <span>ACTIVE CONTEXT</span>
                                <h2>Current Branch</h2>
                            </div>
                        </div>
                        {activeBranch && hasAccess(activeBranch) ? (
                            <div className="rr-company-current-branch">
                                <div className="rr-company-current-mark">
                                    <i className="bi bi-shop" />
                                </div>
                                <div className="rr-company-current-info">
                                    <small>{activeBranch.store_code || 'BRANCH'}</small>
                                    <strong>{activeBranch.store_name}</strong>
                                    <span>
                                        <i className="bi bi-geo-alt" />
                                        {[activeBranch.city, activeBranch.state].filter(Boolean).join(', ') || 'Location not set'}
                                    </span>
                                </div>
                                <span className={`rr-company-live ${isActive(activeBranch.status) ? '' : 'offline'}`}>
                                    <i />
                                    {isActive(activeBranch.status) ? 'Active' : 'Inactive'}
                                </span>
                            </div>
                        ) : (
                            <div className="rr-company-no-context">
                                <i className="bi bi-shop-window" />
                                <div>
                                    <strong>No branch selected</strong>
                                    <span>Select an active branch below to enter its business workspace.</span>
                                </div>
                            </div>
                        )}
                        {activeBranch && hasAccess(activeBranch) && isActive(activeBranch.status) && (
                            <Link className="rr-company-enter-button" href="/business/dashboard">
                                <span>
                                    <small>CONTINUE WORKING</small>
                                    Open Business Dashboard
                                </span>
                                <i className="bi bi-arrow-right" />
                            </Link>
                        )}
                    </article>
                    <article className="rr-company-profile-card">
                        <div className="rr-company-section-heading">
                            <div className="rr-company-heading-icon violet">
                                <i className="bi bi-building-gear" />
                            </div>
                            <div>
                                <span>COMPANY PROFILE</span>
                                <h2>Business Information</h2>
                            </div>
                        </div>
                        <div className="rr-company-profile-list">
                            <ProfileItem icon="bi-person-badge" label="Owner" value={company?.owner_name} />
                            <ProfileItem icon="bi-telephone" label="Phone" value={company?.phone} />
                            <ProfileItem icon="bi-envelope" label="Email" value={company?.email} />
                            <ProfileItem
                                icon="bi-shield-check"
                                label="Company Status"
                                value={isActive(company?.status) ? 'Active' : 'Inactive'}
                                status={isActive(company?.status)}
                            />
                        </div>
                    </article>
                </section>
                <section className="rr-company-branches-panel">
                    <header className="rr-company-panel-header">
                        <div className="rr-company-section-heading">
                            <div className="rr-company-heading-icon blue">
                                <i className="bi bi-diagram-3-fill" />
                            </div>
                            <div>
                                <span>BRANCH DIRECTORY</span>
                                <h2>Company Branches</h2>
                                <p>Select an active branch to switch the current workspace.</p>
                            </div>
                        </div>
                        <Link href="/company/branches/create" className="rr-company-add-button">
                            <i className="bi bi-plus-lg" />
                            Add Branch
                        </Link>
                    </header>
                    <div className="rr-company-filterbar">
                        <label className="rr-company-search">
                            <i className="bi bi-search" />
                            <input
                                type="search"
                                aria-label="Search company branches"
                                value={search}
                                onChange={(event) => setSearch(event.target.value)}
                                placeholder="Search branch, code or location..."
                            />
                            {search && (
                                <button type="button" onClick={() => setSearch('')} aria-label="Clear search">
                                    <i className="bi bi-x-lg" />
                                </button>
                            )}
                        </label>
                        <div className="rr-company-status-tabs" role="group" aria-label="Branch status filter">
                            {[
                                ['all', 'All', branchSummary.total ?? branches.length],
                                ['active', 'Active', branchSummary.active ?? branches.filter((branch) => isActive(branch.status)).length],
                                ['pending', 'Pending', branchSummary.pending ?? branches.filter((branch) => !isActive(branch.status)).length],
                            ].map(([value, label, count]) => (
                                <button
                                    type="button"
                                    key={value}
                                    className={status === value ? 'active' : ''}
                                    aria-pressed={status === value}
                                    onClick={() => setStatus(value)}
                                >
                                    {label}
                                    <span>{number(count)}</span>
                                </button>
                            ))}
                        </div>
                    </div>
                    {switchError && (
                        <div className="rr-company-switch-error" role="alert">
                            <i className="bi bi-exclamation-triangle" aria-hidden="true" />
                            {switchError}
                        </div>
                    )}
                    <div className="rr-company-table-wrap">
                        <table className="rr-company-table">
                            <caption className="visually-hidden">Company branches and workspace actions</caption>
                            <thead>
                                <tr>
                                    <th scope="col">Branch</th>
                                    <th scope="col">Code</th>
                                    <th scope="col">Location</th>
                                    <th scope="col">Created</th>
                                    <th scope="col">Status</th>
                                    <th scope="col" className="action">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                {filteredBranches.length > 0 ? filteredBranches.map((branch) => {
                                    const allowed = hasAccess(branch);
                                    const current = allowed && Number(activeBranch?.id) === Number(branch.id);
                                    const switching = Number(switchingId) === Number(branch.id);
                                    return (
                                        <tr key={branch.id} className={current ? 'current' : ''} aria-busy={switching}>
                                            <td>
                                                <div className="rr-company-branch-cell">
                                                    <span className={`rr-company-branch-avatar ${isActive(branch.status) ? '' : 'pending'}`}>
                                                        <i className="bi bi-shop" />
                                                    </span>
                                                    <div>
                                                        <strong>{branch.store_name}</strong>
                                                        <small>{!allowed ? 'Contact Super Admin or ERP support for access.' : current ? 'Current workspace' : 'Company branch'}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><code>{branch.store_code || '—'}</code></td>
                                            <td>{[branch.city, branch.state].filter(Boolean).join(', ') || '—'}</td>
                                            <td>{branch.created_at || '—'}</td>
                                            <td>
                                                <div className="rr-company-status-stack">
                                                    <span className={`rr-company-status ${isActive(branch.status) ? 'active' : 'pending'}`}>
                                                        <i /> {isActive(branch.status) ? 'Active' : 'Needs Approval'}
                                                    </span>
                                                    <span className={`rr-company-status ${allowed ? 'assigned' : 'no-access'}`}>
                                                        <i /> {allowed ? 'Assigned' : 'No Access'}
                                                    </span>
                                                </div>
                                                {!isActive(branch.status) && <small className="rr-company-status-help">Contact Super Admin for approval.</small>}
                                                {!allowed && <small className="rr-company-status-help access">Contact Super Admin or ERP support for access.</small>}
                                            </td>
                                            <td className="action">
                                                {current && isActive(branch.status) ? (
                                                    <Link className="rr-company-row-action current" href="/business/dashboard">
                                                        Open <i className="bi bi-arrow-right" />
                                                    </Link>
                                                ) : (
                                                    <button
                                                        type="button"
                                                        className={`rr-company-row-action ${!allowed ? 'no-access' : ''}`}
                                                        title={!allowed ? 'Contact Super Admin or the ERP support team to request branch access.' : undefined}
                                                        disabled={!allowed || !isActive(branch.status) || switchingId !== null}
                                                        onClick={() => selectBranch(branch)}
                                                    >
                                                        {!allowed ? (
                                                            <>No Access <i className="bi bi-lock-fill" /></>
                                                        ) : switching ? (
                                                            <><span className="spinner-border spinner-border-sm" /> Switching</>
                                                        ) : isActive(branch.status) ? (
                                                            <>Select <i className="bi bi-arrow-right" /></>
                                                        ) : (
                                                            <>Needs Approval <i className="bi bi-clock" /></>
                                                        )}
                                                    </button>
                                                )}
                                            </td>
                                        </tr>
                                    );
                                }) : (
                                    <tr>
                                        <td colSpan="6">
                                            <div className="rr-company-empty">
                                                <i className="bi bi-search" />
                                                <strong>No branches found</strong>
                                                <span>Change the search text or status filter.</span>
                                            </div>
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                    <footer className="rr-company-panel-footer" role="status" aria-live="polite">
                        Showing <strong>{filteredBranches.length}</strong> of <strong>{branches.length}</strong> branches
                    </footer>
                </section>
            </main>
        </BusinessLayout>
    );
}
function MetricCard({ tone, icon, label, value, note }) {
    return (
        <article className={`rr-company-metric ${tone}`}>
            <div className="rr-company-metric-icon"><i className={`bi ${icon}`} /></div>
            <div>
                <span>{label}</span>
                <strong>{number(value)}</strong>
                <small>{note}</small>
            </div>
            <i className={`bi ${icon} rr-company-metric-watermark`} />
        </article>
    );
}
function ProfileItem({ icon, label, value, status = null }) {
    return (
        <div className="rr-company-profile-item">
            <i className={`bi ${icon}`} />
            <div>
                <span>{label}</span>
                {status === null ? (
                    <strong>{value || '—'}</strong>
                ) : (
                    <strong className={status ? 'positive' : 'negative'}>{value}</strong>
                )}
            </div>
        </div>
    );
}
function number(value) {
    return new Intl.NumberFormat('en-IN').format(Number(value ?? 0));
}
