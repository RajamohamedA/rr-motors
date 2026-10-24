import { Head, Link, router, usePage } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import BusinessLayout from '../../../Layouts/BusinessLayout';
import '../../../../css/business/branches.css';

export default function BranchIndex({ company = {}, branches = {}, summary = {}, filters = {}, activeStoreId }) {
    const { auth = {}, business = {} } = usePage().props;
    const [search, setSearch] = useState(filters.search ?? '');
    const [busy, setBusy] = useState(null);
    const [logoError, setLogoError] = useState(null);
    const user = auth.user;
    const roles = (user?.roles ?? []).map((item) => typeof item === 'string' ? item : item?.name);
    const permissions = (user?.permissions ?? []).map((item) => typeof item === 'string' ? item : item?.name);
    const superAdmin = Boolean(user?.is_super_user);
    const can = (permission) => superAdmin || roles.includes('Company Admin') || permissions.includes(permission);
    const allowedIds = new Set((business.available_branches ?? []).map((item) => String(item.id)));
    const hasAccess = (item) => superAdmin || allowedIds.has(String(item.id));
    const currentId = activeStoreId ?? business.store_id;
    const items = branches.data ?? [];
    const logo = logoUrl(business.company_logo ?? company.logo);
    const logoKey = `${company.id ?? business.company_id}:${logo}`;
    const pageLinks = (branches.links ?? []).filter((item) => /^\d+$/.test(String(item.label).trim()));
    const currentPage = Number(branches.current_page ?? 1);
    const lastPage = Number(branches.last_page ?? Math.max(1, pageLinks.length));

    useEffect(() => setSearch(filters.search ?? ''), [filters.search]);

    const visit = (url, params = {}, action = 'search') => {
        if (busy) return;
        setBusy(action);
        router.get(url, params, {
            preserveState: true,
            preserveScroll: true,
            replace: action === 'search',
            onFinish: () => setBusy(null),
        });
    };

    const enterBranch = (item) => {
        if (busy || !hasAccess(item) || !active(item.status) || String(item.id) === String(currentId)) return;
        setBusy(`enter:${item.id}`);
        router.post(`/select-branch/${item.id}`, {}, {
            preserveState: false,
            onFinish: () => setBusy(null),
        });
    };

    return (
        <BusinessLayout>
            <Head title="Branch Management" />
            <main className="rr-bx" aria-busy={Boolean(busy)}>
                <header className="rr-bx-hero">
                    <div className="rr-bx-hero-copy">
                        <div className="rr-bx-eyebrow"><span /> COMPANY WORKSPACE</div>
                        <h1>Your branches. <span>One connected business.</span></h1>
                        <p>Manage locations and open the workspaces assigned to your account.</p>
                        <nav aria-label="Breadcrumb"><Link href="/company/dashboard">Company</Link><i className="bi bi-chevron-right" /><span>Branches</span></nav>
                    </div>
                    <div className="rr-bx-hero-actions">
                        <button type="button" className="rr-bx-btn rr-bx-glass" disabled={Boolean(busy)} onClick={() => visit('/company/branches', { search: filters.search ?? '' }, 'refresh')}>
                            <i className={`bi bi-arrow-clockwise ${busy === 'refresh' ? 'rr-bx-spin' : ''}`} /> Refresh
                        </button>
                        {can('store.create') && <Link href="/company/branches/create" className="rr-bx-btn rr-bx-white"><i className="bi bi-plus-lg" /> Add Branch</Link>}
                    </div>
                </header>

                <section className="rr-bx-company" aria-label="Current company">
                    <div className="rr-bx-company-main">
                        <div className="rr-bx-logo">
                            {logo && logoError !== logoKey ? <img key={logoKey} src={logo} alt={`${company.company_name ?? 'Company'} logo`} onError={() => setLogoError(logoKey)} /> : initials(company.company_name)}
                        </div>
                        <div><small>CURRENT COMPANY</small><h2>{company.company_name ?? business.company_name ?? 'Company'}</h2><p>{company.company_code ?? business.company_code ?? 'Company workspace'}{company.city ? ` · ${company.city}` : ''}</p></div>
                    </div>
                    <div className="rr-bx-company-policy"><i className="bi bi-shield-check" /><div><strong>Access stays in your control</strong><span>Assigned branches only. New locations require Super Admin approval.</span></div></div>
                </section>

                <section className="rr-bx-stats" aria-label="Branch summary">
                    <Stat label="Total branches" value={summary.total ?? branches.total ?? 0} detail="Registered company locations" icon="bi-buildings" tone="blue" />
                    <Stat label="Active branches" value={summary.active ?? 0} detail="Activated locations; assignment required" icon="bi-check2-circle" tone="green" />
                    <Stat label="Pending approval" value={summary.pending ?? 0} detail="Awaiting Super Admin activation" icon="bi-hourglass-split" tone="purple" />
                </section>

                <section className="rr-bx-register">
                    <div className="rr-bx-register-heading"><div><span className="rr-bx-section-label">LOCATION DIRECTORY</span><h2>Branch register <span>{format(branches.total ?? summary.total ?? 0)}</span></h2></div><span className="rr-bx-access-note"><i className="bi bi-lock" /> Access checked per branch</span></div>
                    <form className="rr-bx-toolbar" onSubmit={(event) => { event.preventDefault(); visit('/company/branches', { search }); }}>
                        <div className="rr-bx-search"><i className="bi bi-search" /><input aria-label="Search branches" placeholder="Search name, code, city or phone…" value={search} onChange={(event) => setSearch(event.target.value)} />{search && <button type="button" aria-label="Clear search" disabled={Boolean(busy)} onClick={() => { setSearch(''); visit('/company/branches'); }}><i className="bi bi-x-lg" /></button>}</div>
                        <button type="submit" className="rr-bx-btn rr-bx-primary" disabled={Boolean(busy)}><i className={`bi ${busy === 'search' ? 'bi-arrow-repeat rr-bx-spin' : 'bi-search'}`} /> Search</button>
                        <span className="rr-bx-result-note">{filters.search ? <>Results for <strong>“{filters.search}”</strong></> : 'All company locations'}</span>
                    </form>

                    <div className="rr-bx-table-wrap" aria-label="Branch register table">
                        <table className="rr-bx-table">
                            <caption className="visually-hidden">Company branches, status, access and available actions</caption>
                            <thead><tr><th scope="col">Branch</th><th scope="col">Location</th><th scope="col">Contact</th><th scope="col">Status & access</th><th scope="col" className="rr-bx-actions-heading">Actions</th></tr></thead>
                            <tbody>
                                {items.map((item) => {
                                    const allowed = hasAccess(item);
                                    const enabled = active(item.status);
                                    const current = allowed && enabled && String(currentId) === String(item.id);
                                    return (
                                        <tr key={item.id} className={current ? 'rr-bx-current' : ''}>
                                            <td data-label="Branch"><div className="rr-bx-branch"><span className="rr-bx-shop"><i className={`bi ${current ? 'bi-shop-window' : 'bi-shop'}`} /></span><div><strong>{item.store_name ?? 'Branch'}</strong><small>{item.store_code ?? '—'}</small></div></div></td>
                                            <td data-label="Location"><div className="rr-bx-cell"><span>{[item.city, item.state].filter(Boolean).join(', ') || '—'}</span>{item.pincode && <small>{item.pincode}</small>}</div></td>
                                            <td data-label="Contact"><div className="rr-bx-cell"><span>{item.contact_person || '—'}</span><small>{item.phone || item.email || 'No contact provided'}</small></div></td>
                                            <td data-label="Status & access"><div className="rr-bx-badges"><span className={`rr-bx-badge ${enabled ? 'green' : 'purple'}`}><i className={`bi ${enabled ? 'bi-check-circle' : 'bi-clock'}`} />{enabled ? 'Active' : 'Pending'}</span><span className={`rr-bx-badge ${!allowed ? 'rose' : current ? 'blue' : 'neutral'}`}><i className={`bi ${!allowed ? 'bi-lock' : current ? 'bi-pin-angle' : 'bi-unlock'}`} />{!allowed ? 'No Access' : current ? 'Current' : 'Assigned'}</span></div>{!allowed && <small className="rr-bx-support">Contact Super Admin or ERP support for access.</small>}</td>
                                            <td data-label="Actions"><div className="rr-bx-row-actions"><button type="button" className={`rr-bx-enter ${current ? 'current' : ''}`} disabled={Boolean(busy) || !allowed || !enabled || current} onClick={() => enterBranch(item)}><i className={`bi ${busy === `enter:${item.id}` ? 'bi-arrow-repeat rr-bx-spin' : !allowed ? 'bi-lock' : current ? 'bi-check2' : 'bi-arrow-up-right'}`} />{busy === `enter:${item.id}` ? 'Opening…' : !allowed ? 'No Access' : current ? 'Current' : !enabled ? 'Pending' : 'Open'}</button>{allowed && can('store.show') && <Link className="rr-bx-icon-btn" href={`/company/branches/${item.id}`} title="View branch" aria-label={`View ${item.store_name}`}><i className="bi bi-eye" /></Link>}{allowed && can('store.edit') && <Link className="rr-bx-icon-btn edit" href={`/company/branches/${item.id}/edit`} title="Edit branch" aria-label={`Edit ${item.store_name}`}><i className="bi bi-pencil-square" /></Link>}</div></td>
                                        </tr>
                                    );
                                })}
                                {!items.length && <tr><td colSpan={5}><div className="rr-bx-empty"><i className={`bi ${filters.search ? 'bi-search' : 'bi-buildings'}`} /><h3>{filters.search ? 'No matching branches' : 'No branches yet'}</h3><p>{filters.search ? 'Try another name, branch code, city or phone.' : 'Your company branches will appear here.'}</p>{filters.search ? <button type="button" className="rr-bx-btn rr-bx-outline" onClick={() => { setSearch(''); visit('/company/branches'); }}>Clear search</button> : can('store.create') && <Link href="/company/branches/create" className="rr-bx-btn rr-bx-primary">Add your first branch</Link>}</div></td></tr>}
                            </tbody>
                        </table>
                    </div>

                    <footer className="rr-bx-pagination">
                        <span>Showing <strong>{branches.from ?? (items.length ? 1 : 0)}–{branches.to ?? items.length}</strong> of <strong>{format(branches.total ?? items.length)}</strong> branches</span>
                        <nav aria-label="Branch pagination"><PageButton label="Previous" icon="bi-chevron-left" url={branches.prev_page_url} disabled={Boolean(busy)} onVisit={visit} />{pageLinks.map((item, index) => <button key={index} type="button" className={item.active ? 'selected' : ''} aria-current={item.active ? 'page' : undefined} aria-label={`Page ${item.label}`} disabled={!item.url || item.active || Boolean(busy)} onClick={() => visit(item.url, {}, 'page')}>{item.label}</button>)}<PageButton label="Next" icon="bi-chevron-right" url={branches.next_page_url} disabled={Boolean(busy)} onVisit={visit} /></nav>
                        <span className="rr-bx-page-count">Page {currentPage} of {lastPage}</span>
                    </footer>
                </section>
                {busy?.startsWith('enter:') && <div className="rr-bx-overlay" role="status" aria-live="polite"><div><span className="spinner-border spinner-border-sm" aria-hidden="true" /><strong>Opening branch workspace…</strong><small>Please wait while your branch loads.</small></div></div>}
            </main>
        </BusinessLayout>
    );
}

function Stat({ label, value, detail, icon, tone }) {
    return <article className={`rr-bx-stat ${tone}`}><span className="rr-bx-stat-icon"><i className={`bi ${icon}`} /></span><div><small>{label}</small><strong>{format(value)}</strong><p>{detail}</p></div><i className={`bi ${icon} rr-bx-stat-watermark`} /></article>;
}

function PageButton({ label, icon, url, disabled, onVisit }) {
    return <button type="button" disabled={!url || disabled} onClick={() => url && onVisit(url, {}, 'page')} aria-label={`${label} page`}>{label === 'Previous' && <i className={`bi ${icon}`} />}<span>{label}</span>{label === 'Next' && <i className={`bi ${icon}`} />}</button>;
}

function active(value) { return [true, 1, '1'].includes(value); }
function format(value) { return Number(value ?? 0).toLocaleString('en-IN'); }
function initials(name) { const words = String(name || 'Company').trim().split(/\s+/).filter(Boolean); return words.length > 1 ? `${words[0][0]}${words.at(-1)[0]}`.toUpperCase() : (words[0] || 'CO').slice(0, 2).toUpperCase(); }
function logoUrl(value) { const path = typeof value === 'string' ? value.trim() : ''; if (!path) return ''; if (/^https?:\/\//i.test(path)) return path; const relative = path.replace(/^\/+/, ''); return relative.startsWith('storage/') ? `/${relative}` : `/storage/${relative}`; }
