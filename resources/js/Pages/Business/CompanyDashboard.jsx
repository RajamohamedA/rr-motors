import React, { useMemo, useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';

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
    const [search, setSearch] = useState('');
    const [status, setStatus] = useState('all');
    const [switchingId, setSwitchingId] = useState(null);

    const filteredBranches = useMemo(() => {
        const needle = search.trim().toLowerCase();

        return branches.filter((branch) => {
            const matchesStatus = status === 'all'
                || (status === 'active' && branch.status)
                || (status === 'pending' && !branch.status);
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
        if (!branch?.id || !branch.status || Number(activeBranch?.id) === Number(branch.id)) {
            return;
        }

        setSwitchingId(branch.id);
        router.post(`/select-branch/${branch.id}`, {}, {
            preserveScroll: false,
            preserveState: false,
            onFinish: () => setSwitchingId(null),
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
                            <i className="bi bi-buildings-fill" />
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
                        value={branchSummary.total}
                        note="Registered locations"
                    />
                    <MetricCard
                        tone="green"
                        icon="bi-patch-check"
                        label="Active Branches"
                        value={branchSummary.active}
                        note="Ready for operation"
                    />
                    <MetricCard
                        tone="amber"
                        icon="bi-hourglass-split"
                        label="Pending Branches"
                        value={branchSummary.pending}
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

                        {activeBranch ? (
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
                                <span className={`rr-company-live ${activeBranch.status ? '' : 'offline'}`}>
                                    <i />
                                    {activeBranch.status ? 'Active' : 'Inactive'}
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

                        {activeBranch && (
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
                                value={company?.status ? 'Active' : 'Inactive'}
                                status={company?.status}
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
                                ['all', 'All', branchSummary.total],
                                ['active', 'Active', branchSummary.active],
                                ['pending', 'Pending', branchSummary.pending],
                            ].map(([value, label, count]) => (
                                <button
                                    type="button"
                                    key={value}
                                    className={status === value ? 'active' : ''}
                                    onClick={() => setStatus(value)}
                                >
                                    {label}
                                    <span>{number(count)}</span>
                                </button>
                            ))}
                        </div>
                    </div>

                    <div className="rr-company-table-wrap">
                        <table className="rr-company-table">
                            <thead>
                                <tr>
                                    <th>Branch</th>
                                    <th>Code</th>
                                    <th>Location</th>
                                    <th>Created</th>
                                    <th>Status</th>
                                    <th className="action">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                {filteredBranches.length > 0 ? filteredBranches.map((branch) => {
                                    const current = Number(activeBranch?.id) === Number(branch.id);
                                    const switching = Number(switchingId) === Number(branch.id);

                                    return (
                                        <tr key={branch.id} className={current ? 'current' : ''}>
                                            <td>
                                                <div className="rr-company-branch-cell">
                                                    <span className={`rr-company-branch-avatar ${branch.status ? '' : 'pending'}`}>
                                                        <i className="bi bi-shop" />
                                                    </span>
                                                    <div>
                                                        <strong>{branch.store_name}</strong>
                                                        <small>{current ? 'Current workspace' : 'Company branch'}</small>
                                                    </div>
                                                </div>
                                            </td>
                                            <td><code>{branch.store_code || '—'}</code></td>
                                            <td>{[branch.city, branch.state].filter(Boolean).join(', ') || '—'}</td>
                                            <td>{branch.created_at || '—'}</td>
                                            <td>
                                                <span className={`rr-company-status ${branch.status ? 'active' : 'pending'}`}>
                                                    <i />
                                                    {branch.status_label || (branch.status ? 'Active' : 'Pending')}
                                                </span>
                                            </td>
                                            <td className="action">
                                                {current ? (
                                                    <Link className="rr-company-row-action current" href="/business/dashboard">
                                                        Open <i className="bi bi-arrow-right" />
                                                    </Link>
                                                ) : (
                                                    <button
                                                        type="button"
                                                        className="rr-company-row-action"
                                                        disabled={!branch.status || switching}
                                                        onClick={() => selectBranch(branch)}
                                                    >
                                                        {switching ? (
                                                            <><span className="spinner-border spinner-border-sm" /> Switching</>
                                                        ) : branch.status ? (
                                                            <>Select <i className="bi bi-arrow-right" /></>
                                                        ) : (
                                                            <>Unavailable <i className="bi bi-lock" /></>
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

                    <footer className="rr-company-panel-footer">
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

