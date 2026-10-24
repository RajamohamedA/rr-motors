import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';
import BusinessLayout from '../../../Layouts/BusinessLayout';
import '../../../../css/business/branches.css';

export default function BranchEdit({ company = {}, branch = {} }) {
    const { business = {}, auth = {} } = usePage().props;
    const form = useForm({
        store_name: branch.store_name ?? '',
        contact_person: branch.contact_person ?? '',
        phone: branch.phone ?? '',
        email: branch.email ?? '',
        address: branch.address ?? '',
        city: branch.city ?? '',
        state: branch.state ?? '',
        country: branch.country ?? 'India',
        pincode: branch.pincode ?? '',
    });
    const roles = (auth.user?.roles ?? []).map((item) => typeof item === 'string' ? item : item?.name);
    const permissions = (auth.user?.permissions ?? []).map((item) => typeof item === 'string' ? item : item?.name);
    const assignedIds = business.assigned_branch_ids ?? (business.available_branches ?? []).map((item) => item.id);
    const assigned = Boolean(auth.user?.is_super_user) || assignedIds.map(String).includes(String(branch.id));
    const canEdit = assigned && (Boolean(auth.user?.is_super_user) || roles.includes('Company Admin') || permissions.includes('store.edit'));
    const isActive = [true, 1, '1'].includes(branch.status);
    const [failedLogo, setFailedLogo] = useState(null);
    const logo = logoUrl(business.company_logo ?? company.logo);
    const logoKey = `${company.id ?? business.company_id}:${logo}`;

    const update = (name, value) => {
        form.setData(name, value);
        form.clearErrors(name);
    };

    const submit = (event) => {
        event.preventDefault();
        if (form.processing || !canEdit) return;
        form.put(`/company/branches/${branch.id}`, { preserveScroll: true });
    };

    const field = (name, label, icon, options = {}) => (
        <Field key={name} name={name} label={label} icon={icon}
            value={form.data[name]} error={form.errors[name]}
            onChange={(value) => update(name, value)}
            disabled={form.processing || !canEdit} {...options} />
    );

    return (
        <BusinessLayout>
            <Head title={`Edit ${branch.store_name ?? 'Branch'} - RR ERP`} />
            <main className="rr-bx rr-bcreate">
                <header className="rr-bx-hero">
                    <div className="rr-bx-hero-copy">
                        <div className="rr-bx-eyebrow"><span /> BRANCH SETTINGS</div>
                        <h1>Branch details. <span>Always up to date.</span></h1>
                        <p>Update contact and location information. Branch code and approval remain protected.</p>
                        <nav aria-label="Breadcrumb"><Link href="/company/dashboard">Company</Link><i className="bi bi-chevron-right" /><Link href="/company/branches">Branches</Link><i className="bi bi-chevron-right" /><span>Edit Branch</span></nav>
                    </div>
                    <div className="rr-bx-hero-actions"><Link href="/company/branches" className="rr-bx-btn rr-bx-glass"><i className="bi bi-arrow-left" /> Back to Branches</Link></div>
                </header>

                <section className="rr-bx-company" aria-label="Branch company">
                    <div className="rr-bx-company-main">
                        <div className="rr-bx-logo">{logo && failedLogo !== logoKey ? <img key={logoKey} src={logo} alt={`${company.company_name ?? 'Company'} logo`} onError={() => setFailedLogo(logoKey)} /> : initials(company.company_name)}</div>
                        <div><small>CURRENT COMPANY</small><h2>{company.company_name ?? business.company_name ?? 'Company'}</h2><p>{company.company_code ?? business.company_code ?? 'Company workspace'}</p></div>
                    </div>
                    <div className="rr-bx-company-policy"><i className="bi bi-shield-lock" /><div><strong>{isActive ? 'Active branch' : 'Needs Approval'}</strong><span>{isActive ? 'Changes apply to this company branch.' : 'Super Admin must activate the branch before it can be used.'}</span></div></div>
                </section>

                {!canEdit && <div className="rr-bdetail-notice denied" role="status"><i className="bi bi-lock" /><span>{!assigned ? 'No Access — contact Super Admin or ERP support to request access to this branch.' : 'Your role does not have permission to edit this branch.'}</span></div>}
                <form onSubmit={submit} className="rr-bcreate-layout" aria-busy={form.processing}>
                    <div className="rr-bcreate-main">
                        <section className="rr-bcreate-panel">
                            <Section number="01" icon="bi-shop-window" title="Branch information" description="Give this location a name and a primary contact." />
                            <div className="rr-bcreate-fields">
                                {field('store_name', 'Branch name', 'bi-shop', { required: true, placeholder: 'e.g. TTP Main Branch', maxLength: 255, autoFocus: true, full: true })}
                                {field('contact_person', 'Contact person', 'bi-person', { placeholder: 'Branch manager or primary contact', maxLength: 255, full: true })}
                                {field('phone', 'Phone number', 'bi-telephone', { type: 'tel', placeholder: 'Branch contact number', maxLength: 30 })}
                                {field('email', 'Email address', 'bi-envelope', { type: 'email', placeholder: 'branch@company.com', maxLength: 255 })}
                            </div>
                        </section>
                        <section className="rr-bcreate-panel">
                            <Section number="02" icon="bi-geo-alt" title="Location & address" description="Where customers, suppliers and your team can find this branch." />
                            <div className="rr-bcreate-fields">
                                {field('address', 'Address', 'bi-geo-alt', { textarea: true, full: true, placeholder: 'Building, street and area', maxLength: 1000 })}
                                {field('city', 'City', 'bi-geo', { placeholder: 'City', maxLength: 100 })}
                                {field('state', 'State', 'bi-map', { placeholder: 'State', maxLength: 100 })}
                                {field('country', 'Country', 'bi-globe', { placeholder: 'Country', maxLength: 100 })}
                                {field('pincode', 'Pincode', 'bi-mailbox', { placeholder: 'Postal code', maxLength: 20 })}
                            </div>
                        </section>
                    </div>

                    <aside className="rr-bcreate-guide">
                        <div className="rr-bcreate-guide-icon"><i className="bi bi-shop-window" /></div>
                        <span className="rr-bcreate-kicker">BRANCH IDENTITY</span>
                        <h2>{branch.store_name ?? 'Branch'}</h2>
                        <p>{branch.store_code ?? 'Branch code'} · {company.company_code ?? 'Company'}</p>
                        <div className="rr-bdetail-badges">
                            <span className={`rr-bx-badge ${isActive ? 'green' : 'purple'}`}>{isActive ? 'Active' : 'Needs Approval'}</span>
                            <span className={`rr-bx-badge ${assigned ? 'blue' : 'rose'}`}>{assigned ? 'Assigned' : 'No Access'}</span>
                        </div>
                        <div className="rr-bcreate-preview">
                            <small>UPDATED DETAILS PREVIEW</small>
                            <strong>{form.data.store_name.trim() || 'Branch name'}</strong>
                            <span>{[form.data.city, form.data.state].filter(Boolean).join(', ') || 'Location not provided'}</span>
                            <span>{form.data.phone || 'Phone not provided'}</span>
                        </div>
                        <div className="rr-bcreate-system"><i className="bi bi-shield-lock" /><span>Branch number, branch code, company and activation are managed by the system.</span></div>
                        {!assigned && <div className="rr-bdetail-notice denied"><i className="bi bi-lock" /><span>Contact Super Admin or ERP support for branch access.</span></div>}
                        {assigned && !canEdit && <div className="rr-bdetail-notice denied"><i className="bi bi-lock" /><span>Your role does not have permission to edit this branch.</span></div>}
                    </aside>

                    <footer className="rr-bcreate-actions">
                        <div aria-live="polite">{form.processing ? <span><i className="bi bi-arrow-repeat rr-bx-spin" /> Saving branch details…</span> : form.isDirty ? <span className="dirty"><i className="bi bi-circle-fill" /> Unsaved changes</span> : <span><i className="bi bi-shield-check" /> Branch settings are up to date</span>}</div>
                        <div className="rr-bcreate-buttons">
                            {form.processing ? <button type="button" className="rr-bx-btn rr-bx-outline" disabled>Cancel</button> : <Link href="/company/branches" className="rr-bx-btn rr-bx-outline">Cancel</Link>}
                            <button type="button" className="rr-bx-btn rr-bx-outline" disabled={form.processing || !form.isDirty || !canEdit} onClick={() => { form.reset(); form.clearErrors(); }}><i className="bi bi-arrow-counterclockwise" /> Reset</button>
                            <button type="submit" className="rr-bx-btn rr-bx-primary" disabled={form.processing || !form.isDirty || !canEdit}>{form.processing ? <span className="spinner-border spinner-border-sm" aria-hidden="true" /> : <i className="bi bi-check2-circle" />}{form.processing ? 'Saving…' : 'Save Changes'}</button>
                        </div>
                    </footer>
                </form>
            </main>
        </BusinessLayout>
    );
}

function Section({ number, icon, title, description }) {
    return <header className="rr-bcreate-heading"><span><i className={`bi ${icon}`} /></span><div><h2>{title}</h2><p>{description}</p></div><small>{number}</small></header>;
}

function Field({ name, label, icon, value, onChange, error, required = false, full = false, disabled, textarea, type = 'text', placeholder, maxLength, autoFocus }) {
    const id = `branch-${name}`;
    const props = { id, value, required, disabled, placeholder, maxLength, autoFocus, 'aria-invalid': Boolean(error), 'aria-describedby': error ? `${id}-error` : undefined, onChange: (event) => onChange(event.target.value) };
    return <div className={`rr-bcreate-field ${full ? 'full' : ''}`}><label htmlFor={id}>{label}{required && <span> *</span>}</label><div className={`rr-bcreate-input ${textarea ? 'textarea' : ''} ${error ? 'invalid' : ''}`}><i className={`bi ${icon}`} />{textarea ? <textarea {...props} rows={3} /> : <input {...props} type={type} />}</div>{error && <small id={`${id}-error`} className="rr-bcreate-error" role="alert"><i className="bi bi-exclamation-circle" /> {error}</small>}</div>;
}

function initials(value) { const words = String(value || 'Company').trim().split(/\s+/).filter(Boolean); return words.length > 1 ? `${words[0][0]}${words.at(-1)[0]}`.toUpperCase() : (words[0] || 'CO').slice(0, 2).toUpperCase(); }
function logoUrl(value) { const path = typeof value === 'string' ? value.trim() : ''; if (!path) return ''; if (/^https?:\/\//i.test(path)) return path; const relative = path.replace(/^\/+/, ''); return relative.startsWith('storage/') ? `/${relative}` : `/storage/${relative}`; }
