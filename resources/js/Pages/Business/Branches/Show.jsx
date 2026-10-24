import { Head, Link, router, usePage } from '@inertiajs/react';
import { useRef, useState } from 'react';
import BusinessLayout from '../../../Layouts/BusinessLayout';
import '../../../../css/business/branches.css';

export default function BranchShow({ company = {}, branch = {} }) {
    const { auth = {}, business = {} } = usePage().props;
    const [failedLogo, setFailedLogo] = useState(null);
    const [opening, setOpening] = useState(false);
    const pending = useRef(false);
    const roles = (auth.user?.roles ?? []).map((item) => typeof item === 'string' ? item : item?.name);
    const permissions = (auth.user?.permissions ?? []).map((item) => typeof item === 'string' ? item : item?.name);
    const superAdmin = Boolean(auth.user?.is_super_user);
    const assignedIds = business.assigned_branch_ids ?? (business.available_branches ?? []).map((item) => item.id);
    const assigned = superAdmin || assignedIds.map(String).includes(String(branch.id));
    const canEdit = assigned && (superAdmin || roles.includes('Company Admin') || permissions.includes('store.edit'));
    const active = [true, 1, '1'].includes(branch.status);
    const current = assigned && active && String(business.store_id) === String(branch.id);
    const logo = logoUrl(business.company_logo ?? company.logo);
    const logoKey = `${company.id ?? business.company_id}:${logo}`;

    const enter = () => {
        if (pending.current || !assigned || !active || current || !branch.id) return;
        pending.current = true;
        setOpening(true);
        router.post(`/select-branch/${branch.id}`, {}, {
            preserveState: false,
            preserveScroll: false,
            onFinish: () => { pending.current = false; setOpening(false); },
        });
    };

    const workspaceAction = current ? (
        <Link href="/business/dashboard" className="rr-bx-btn rr-bx-primary"><i className="bi bi-arrow-up-right" /> Open Workspace</Link>
    ) : (
        <button type="button" className={`rr-bx-btn ${assigned && active ? 'rr-bx-primary' : 'rr-bx-outline'}`} disabled={opening || !assigned || !active} onClick={enter}>
            {opening ? <span className="spinner-border spinner-border-sm" aria-hidden="true" /> : <i className={`bi ${!assigned ? 'bi-lock' : !active ? 'bi-clock' : 'bi-box-arrow-in-right'}`} />}
            {opening ? 'Opening…' : !assigned ? 'No Access' : !active ? 'Needs Approval' : 'Enter Branch'}
        </button>
    );

    return (
        <BusinessLayout>
            <Head title={`${branch.store_name ?? 'Branch'} - RR ERP`} />
            <main className="rr-bx rr-bdetail" aria-busy={opening}>
                <header className="rr-bx-hero">
                    <div className="rr-bx-hero-copy">
                        <div className="rr-bx-eyebrow"><span /> BRANCH OVERVIEW</div>
                        <h1>{branch.store_name ?? 'Branch Details'}</h1>
                        <p>Contact details, location and workspace access in one place.</p>
                        <nav aria-label="Breadcrumb"><Link href="/company/dashboard">Company</Link><i className="bi bi-chevron-right" /><Link href="/company/branches">Branches</Link><i className="bi bi-chevron-right" /><span>Details</span></nav>
                    </div>
                    <div className="rr-bx-hero-actions"><Link href="/company/branches" className="rr-bx-btn rr-bx-glass"><i className="bi bi-arrow-left" /> Back</Link>{canEdit && <Link href={`/company/branches/${branch.id}/edit`} className="rr-bx-btn rr-bx-white"><i className="bi bi-pencil-square" /> Edit Branch</Link>}</div>
                </header>

                <section className="rr-bx-company">
                    <div className="rr-bx-company-main">
                        <div className="rr-bx-logo">{logo && failedLogo !== logoKey ? <img key={logoKey} src={logo} alt={`${company.company_name ?? 'Company'} logo`} onError={() => setFailedLogo(logoKey)} /> : initials(company.company_name)}</div>
                        <div><small>COMPANY WORKSPACE</small><h2>{company.company_name ?? business.company_name ?? 'Company'}</h2><p>{company.company_code ?? business.company_code ?? 'Company'}</p></div>
                    </div>
                    <div className="rr-bdetail-badges">
                        <span className={`rr-bx-badge ${active ? 'green' : 'purple'}`}><i className={`bi ${active ? 'bi-check-circle' : 'bi-clock'}`} /> {active ? 'Active' : 'Needs Approval'}</span>
                        <span className={`rr-bx-badge ${assigned ? 'blue' : 'rose'}`}><i className={`bi ${assigned ? 'bi-unlock' : 'bi-lock'}`} /> {assigned ? 'Assigned' : 'No Access'}</span>
                        {current && <span className="rr-bx-badge blue"><i className="bi bi-pin-angle" /> Current Branch</span>}
                    </div>
                </section>

                {!active && <div className="rr-bdetail-notice pending"><i className="bi bi-shield-lock" /><div><strong>Needs Super Admin approval</strong><span>This branch cannot be used for operations until it is activated.</span></div></div>}
                {!assigned && <div className="rr-bdetail-notice denied"><i className="bi bi-lock" /><div><strong>No branch access</strong><span>Contact Super Admin or the ERP support team to request access.</span></div></div>}

                <section className="rr-bdetail-metrics" aria-label="Branch identifiers">
                    <SystemItem icon="bi-upc-scan" label="Branch code" value={branch.store_code} />
                    <SystemItem icon="bi-list-ol" label="Branch number" value={branch.branch_number ? `#${String(branch.branch_number).padStart(3, '0')}` : null} />
                    <SystemItem icon="bi-upc" label="Company code" value={company.company_code} />
                    <SystemItem icon="bi-calendar3" label="Created" value={formatDate(branch.created_at)} />
                </section>

                <div className="rr-bdetail-grid">
                    <section className="rr-bcreate-panel">
                        <PanelHeading icon="bi-person-lines-fill" title="Contact information" text="People and contact details for this location" />
                        <div className="rr-bdetail-rows"><Detail icon="bi-person" label="Contact person" value={branch.contact_person} /><Detail icon="bi-telephone" label="Phone number" value={branch.phone} /><Detail icon="bi-envelope" label="Email address" value={branch.email} /></div>
                    </section>
                    <section className="rr-bcreate-panel">
                        <PanelHeading icon="bi-geo-alt" title="Location & address" text="Branch address and postal information" />
                        <div className="rr-bdetail-rows"><Detail icon="bi-signpost" label="Address" value={branch.address} /><Detail icon="bi-geo" label="City / State" value={[branch.city, branch.state].filter(Boolean).join(', ')} /><Detail icon="bi-globe" label="Country / Pincode" value={[branch.country, branch.pincode].filter(Boolean).join(' · ')} /></div>
                    </section>
                </div>

                <section className="rr-bdetail-workspace">
                    <div className="rr-bdetail-workspace-copy"><span><i className="bi bi-shop-window" /></span><div><h2>{current ? 'Your current workspace' : 'Branch workspace'}</h2><p>{!assigned ? 'Branch access must be assigned before you can enter.' : !active ? 'Activation is required before operations can begin.' : current ? 'Continue working with this branch’s business records.' : 'Open this branch to manage its business operations.'}</p></div></div>
                    <div className="rr-bcreate-buttons">{canEdit && <Link href={`/company/branches/${branch.id}/edit`} className="rr-bx-btn rr-bx-outline"><i className="bi bi-pencil-square" /> Edit Details</Link>}{workspaceAction}</div>
                </section>
                {opening && <div className="rr-bx-overlay" role="status" aria-live="polite"><div><span className="spinner-border spinner-border-sm" aria-hidden="true" /><strong>Opening branch workspace…</strong><small>Please wait while your branch loads.</small></div></div>}
            </main>
        </BusinessLayout>
    );
}

function PanelHeading({ icon, title, text }) {
    return <header className="rr-bcreate-heading"><span><i className={`bi ${icon}`} /></span><div><h2>{title}</h2><p>{text}</p></div></header>;
}
function SystemItem({ icon, label, value }) {
    return <article className="rr-bdetail-metric"><span><i className={`bi ${icon}`} /></span><div><small>{label}</small><strong>{value || '—'}</strong></div></article>;
}
function Detail({ icon, label, value }) {
    return <div className="rr-bdetail-row"><span><i className={`bi ${icon}`} /></span><div><small>{label}</small><strong>{value || 'Not provided'}</strong></div></div>;
}
function formatDate(value) { if (!value) return null; const date = new Date(value); return Number.isNaN(date.getTime()) ? String(value) : new Intl.DateTimeFormat('en-IN', { day: '2-digit', month: 'short', year: 'numeric' }).format(date); }
function initials(value) { const words = String(value || 'Company').trim().split(/\s+/).filter(Boolean); return words.length > 1 ? `${words[0][0]}${words.at(-1)[0]}`.toUpperCase() : (words[0] || 'CO').slice(0, 2).toUpperCase(); }
function logoUrl(value) { const path = typeof value === 'string' ? value.trim() : ''; if (!path) return ''; if (/^https?:\/\//i.test(path)) return path; const relative = path.replace(/^\/+/, ''); return relative.startsWith('storage/') ? `/${relative}` : `/storage/${relative}`; }
