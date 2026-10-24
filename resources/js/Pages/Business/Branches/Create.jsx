import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { useState } from 'react';
import BusinessLayout from '../../../Layouts/BusinessLayout';
import '../../../../css/business/branches.css';

export default function BranchCreate({ company = {} }) {
    const { business = {} } = usePage().props;
    const form = useForm({
        store_name: '',
        contact_person: '',
        phone: '',
        email: '',
        address: '',
        city: '',
        state: '',
        country: 'India',
        pincode: '',
    });
    const [failedLogo, setFailedLogo] = useState(null);
    const logo = logoUrl(business.company_logo ?? company.logo);
    const logoKey = `${company.id ?? business.company_id}:${logo}`;

    const update = (name, value) => {
        form.setData(name, value);
        form.clearErrors(name);
    };

    const submit = (event) => {
        event.preventDefault();
        if (form.processing) return;
        form.post('/company/branches', { preserveScroll: true });
    };

    const field = (name, label, icon, options = {}) => (
        <Field key={name} name={name} label={label} icon={icon}
            value={form.data[name]} error={form.errors[name]}
            onChange={(value) => update(name, value)}
            disabled={form.processing} {...options} />
    );

    return (
        <BusinessLayout>
            <Head title="Add Branch - RR ERP" />
            <main className="rr-bx rr-bcreate">
                <header className="rr-bx-hero">
                    <div className="rr-bx-hero-copy">
                        <div className="rr-bx-eyebrow"><span /> GROW YOUR BUSINESS</div>
                        <h1>A new location. <span>A connected workspace.</span></h1>
                        <p>Add your branch details. Super Admin approval is required before operations begin.</p>
                        <nav aria-label="Breadcrumb"><Link href="/company/dashboard">Company</Link><i className="bi bi-chevron-right" /><Link href="/company/branches">Branches</Link><i className="bi bi-chevron-right" /><span>Add Branch</span></nav>
                    </div>
                    <div className="rr-bx-hero-actions"><Link href="/company/branches" className="rr-bx-btn rr-bx-glass"><i className="bi bi-arrow-left" /> Back to Branches</Link></div>
                </header>

                <section className="rr-bx-company" aria-label="Branch company">
                    <div className="rr-bx-company-main">
                        <div className="rr-bx-logo">{logo && failedLogo !== logoKey ? <img key={logoKey} src={logo} alt={`${company.company_name ?? 'Company'} logo`} onError={() => setFailedLogo(logoKey)} /> : initials(company.company_name)}</div>
                        <div><small>CURRENT COMPANY</small><h2>{company.company_name ?? business.company_name ?? 'Company'}</h2><p>{company.company_code ?? business.company_code ?? 'Company workspace'}</p></div>
                    </div>
                    <div className="rr-bx-company-policy"><i className="bi bi-shield-lock" /><div><strong>Approval required</strong><span>New branches remain pending until Super Admin activates them.</span></div></div>
                </section>

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
                        <div className="rr-bcreate-guide-icon"><i className="bi bi-building-add" /></div>
                        <span className="rr-bcreate-kicker">NEW LOCATION</span>
                        <h2>Ready for your next branch.</h2>
                        <p>A few details now. A dedicated workspace after approval.</p>
                        <ol className="rr-bcreate-steps">
                            <li><span>1</span><div><strong>Create the branch</strong><small>Save contact and address details.</small></div></li>
                            <li><span>2</span><div><strong>Super Admin approval</strong><small>The new branch starts as pending.</small></div></li>
                            <li><span>3</span><div><strong>Assign access</strong><small>Confirm which users can work here.</small></div></li>
                        </ol>
                        <div className="rr-bcreate-preview"><small>BRANCH PREVIEW</small><strong>{form.data.store_name.trim() || 'Your new branch'}</strong><span>{[form.data.city, form.data.state].filter(Boolean).join(', ') || 'Location details appear here'}</span><em><i className="bi bi-clock" /> Pending approval</em></div>
                        <div className="rr-bcreate-system"><i className="bi bi-info-circle" /><span>Company, branch code and approval status are managed by the system.</span></div>
                    </aside>

                    <footer className="rr-bcreate-actions">
                        <div aria-live="polite">{form.processing ? <span><i className="bi bi-arrow-repeat rr-bx-spin" /> Creating your branch…</span> : form.isDirty ? <span className="dirty"><i className="bi bi-circle-fill" /> Unsaved branch details</span> : <span><i className="bi bi-shield-check" /> Secure company workspace</span>}</div>
                        <div className="rr-bcreate-buttons">
                            {form.processing ? <button type="button" className="rr-bx-btn rr-bx-outline" disabled>Cancel</button> : <Link href="/company/branches" className="rr-bx-btn rr-bx-outline">Cancel</Link>}
                            <button type="button" className="rr-bx-btn rr-bx-outline" disabled={form.processing || !form.isDirty} onClick={() => { form.reset(); form.clearErrors(); }}><i className="bi bi-arrow-counterclockwise" /> Reset</button>
                            <button type="submit" className="rr-bx-btn rr-bx-primary" disabled={form.processing}>{form.processing ? <span className="spinner-border spinner-border-sm" aria-hidden="true" /> : <i className="bi bi-check2-circle" />}{form.processing ? 'Creating…' : 'Create Branch'}</button>
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
