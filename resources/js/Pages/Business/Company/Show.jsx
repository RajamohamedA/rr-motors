import { Head, Link, useForm } from '@inertiajs/react';
import { useEffect, useRef, useState } from 'react';
import BusinessLayout from '../../../Layouts/BusinessLayout';
import '../../../../css/business/company.css';

export default function Show({ company = {} }) {
    const form = useForm({
        company_name: company.company_name ?? '',
        owner_name: company.owner_name ?? '',
        gst_number: company.gst_number ?? '',
        pan_number: company.pan_number ?? '',
        email: company.email ?? '',
        phone: company.phone ?? '',
        website: company.website ?? '',
        address: company.address ?? '',
        city: company.city ?? '',
        state: company.state ?? '',
        country: company.country ?? '',
        pincode: company.pincode ?? '',
        logo: null,
    });
    const picker = useRef(null);
    const [preview, setPreview] = useState('');
    const [failedLogo, setFailedLogo] = useState(null);
    const [saved, setSaved] = useState(false);
    const storedLogo = logoUrl(company.logo);
    const displayLogo = preview || storedLogo;
    const logoKey = `${company.id}:${displayLogo}`;
    const active = [true, 1, '1'].includes(company.status);

    useEffect(() => {
        if (!form.data.logo) {
            setPreview('');
            return;
        }
        const url = URL.createObjectURL(form.data.logo);
        setPreview(url);
        return () => URL.revokeObjectURL(url);
    }, [form.data.logo]);

    const update = (field, value) => {
        form.setData(field, value);
        form.clearErrors(field);
        setSaved(false);
    };

    const chooseLogo = (event) => {
        const file = event.target.files?.[0];
        if (!file) return;
        if (!['image/jpeg', 'image/png', 'image/webp'].includes(file.type)) {
            form.setError('logo', 'Choose a JPG, PNG or WebP image.');
            event.target.value = '';
            return;
        }
        if (file.size > 2 * 1024 * 1024) {
            form.setError('logo', 'The logo must be 2 MB or smaller.');
            event.target.value = '';
            return;
        }
        update('logo', file);
    };

    const clearSelection = () => {
        update('logo', null);
        if (picker.current) picker.current.value = '';
    };

    const reset = () => {
        form.reset();
        form.clearErrors();
        setSaved(false);
        if (picker.current) picker.current.value = '';
    };

    const submit = (event) => {
        event.preventDefault();
        if (form.processing) return;
        form.transform((data) => ({ ...data, _method: 'put' }));
        form.post('/company/profile', {
            forceFormData: true,
            preserveScroll: true,
            onSuccess: () => {
                form.defaults({ ...form.data, logo: null });
                form.reset();
                form.clearErrors();
                if (picker.current) picker.current.value = '';
                setSaved(true);
            },
        });
    };

    const field = (name, label, icon, options = {}) => (
        <Field
            key={name}
            name={name}
            label={label}
            icon={icon}
            value={form.data[name]}
            error={form.errors[name]}
            onChange={(value) => update(name, options.uppercase ? value.toUpperCase() : value)}
            disabled={form.processing}
            {...options}
        />
    );

    const logoContent = displayLogo && failedLogo !== logoKey ? (
        <img key={logoKey} src={displayLogo} alt={`${company.company_name ?? 'Company'} logo`} onError={() => setFailedLogo(logoKey)} />
    ) : <span>{initials(company.company_name)}</span>;

    return (
        <BusinessLayout title="Company Profile" subtitle="Business identity and contact details">
            <Head title="Company Profile - RR ERP" />
            <main className="rr-cp">
                <header className="rr-cp-hero">
                    <div className="rr-cp-hero-main">
                        <div className="rr-cp-hero-logo">{logoContent}</div>
                        <div className="rr-cp-hero-copy">
                            <span className="rr-cp-eyebrow"><i className="bi bi-building-check" /> COMPANY IDENTITY</span>
                            <h1>{company.company_name || 'Company Profile'}</h1>
                            <p>Your business identity, contact details and registered address.</p>
                            <div className="rr-cp-chips">
                                <span><i className="bi bi-upc-scan" /> {company.company_code || 'Company'}</span>
                                <span><i className={`bi ${active ? 'bi-check-circle' : 'bi-pause-circle'}`} /> {active ? 'Active company' : 'Inactive company'}</span>
                                {company.created_at && <span><i className="bi bi-calendar3" /> Since {date(company.created_at)}</span>}
                            </div>
                        </div>
                    </div>
                    <Link href="/company/dashboard" className="rr-cp-back"><i className="bi bi-arrow-left" /> Company Dashboard</Link>
                </header>

                <section className="rr-cp-stats" aria-label="Company overview">
                    <Summary icon="bi-person-badge" label="Owner / Contact" value={company.owner_name} tone="blue" />
                    <Summary icon="bi-telephone" label="Phone" value={company.phone} tone="green" />
                    <Summary icon="bi-envelope" label="Email" value={company.email} tone="violet" />
                    <Summary icon="bi-geo-alt" label="Location" value={[company.city, company.state].filter(Boolean).join(', ')} tone="cyan" />
                </section>

                <form onSubmit={submit} className="rr-cp-form" encType="multipart/form-data">
                    <section className="rr-cp-panel rr-cp-branding">
                        <Section icon="bi-palette2" title="Company branding" description="A recognizable identity across your workspace" />
                        <div className="rr-cp-brand-body">
                            <div className="rr-cp-logo-preview">{logoContent}</div>
                            <div className="rr-cp-logo-controls">
                                <label htmlFor="company-logo" className="rr-cp-label">Company logo</label>
                                <p>Upload a clear JPG, PNG or WebP image up to 2 MB. A square logo works best.</p>
                                <div className="rr-cp-logo-actions">
                                    <input ref={picker} id="company-logo" type="file" accept="image/jpeg,image/png,image/webp" disabled={form.processing} onChange={chooseLogo} className="rr-cp-file" />
                                    <button type="button" className="rr-cp-btn rr-cp-secondary" disabled={form.processing} onClick={() => picker.current?.click()}><i className="bi bi-cloud-arrow-up" /> Change logo</button>
                                    {form.data.logo && <button type="button" className="rr-cp-btn rr-cp-plain" disabled={form.processing} onClick={clearSelection}><i className="bi bi-x-lg" /> Undo selection</button>}
                                </div>
                                <small className="rr-cp-file-name">{form.data.logo ? form.data.logo.name : 'Your existing logo is kept until you save a replacement.'}</small>
                                {form.errors.logo && <div className="rr-cp-error" role="alert"><i className="bi bi-exclamation-circle" /> {form.errors.logo}</div>}
                            </div>
                        </div>
                    </section>

                    <section className="rr-cp-panel">
                        <Section icon="bi-buildings" title="Business information" description="Official company and registration details" />
                        <div className="rr-cp-fields">
                            {field('company_name', 'Company name', 'bi-buildings', { required: true, full: true, placeholder: 'Registered company name', maxLength: 255 })}
                            {field('owner_name', 'Owner / Contact name', 'bi-person', { required: true, placeholder: 'Owner or primary contact', maxLength: 255 })}
                            <Field name="company_code" label="Company code" icon="bi-hash" value={company.company_code ?? ''} readOnly hint="Managed by Super Admin" />
                            {field('gst_number', 'GST number', 'bi-file-earmark-text', { uppercase: true, placeholder: 'GSTIN', maxLength: 50 })}
                            {field('pan_number', 'PAN number', 'bi-card-text', { uppercase: true, placeholder: 'PAN', maxLength: 50 })}
                        </div>
                    </section>

                    <section className="rr-cp-panel">
                        <Section icon="bi-chat-square-text" title="Contact information" description="How customers and partners reach your business" />
                        <div className="rr-cp-fields">
                            {field('email', 'Email address', 'bi-envelope', { type: 'email', placeholder: 'accounts@company.com', maxLength: 255 })}
                            {field('phone', 'Phone number', 'bi-telephone', { type: 'tel', placeholder: 'Business contact number', maxLength: 30 })}
                            {field('website', 'Website', 'bi-globe', { full: true, placeholder: 'https://yourcompany.com', maxLength: 255 })}
                        </div>
                    </section>

                    <section className="rr-cp-panel">
                        <Section icon="bi-geo-alt" title="Business address" description="Registered or primary business location" />
                        <div className="rr-cp-fields">
                            {field('address', 'Address', 'bi-geo-alt', { textarea: true, full: true, placeholder: 'Building, street and area', maxLength: 1000 })}
                            {field('city', 'City', 'bi-geo', { placeholder: 'City', maxLength: 100 })}
                            {field('state', 'State', 'bi-map', { placeholder: 'State', maxLength: 100 })}
                            {field('country', 'Country', 'bi-globe', { placeholder: 'Country', maxLength: 100 })}
                            {field('pincode', 'Pincode', 'bi-mailbox', { placeholder: 'Postal code', maxLength: 20 })}
                        </div>
                    </section>

                    <div className="rr-cp-protected"><i className="bi bi-shield-lock" /><span>Company code, activation and subscription controls are managed by Super Admin.</span></div>
                    <footer className="rr-cp-actions">
                        <div aria-live="polite">
                            {form.processing ? <span className="rr-cp-save-state">{form.progress ? `Uploading ${form.progress.percentage}%` : 'Saving company profile…'}</span>
                                : form.isDirty ? <span className="rr-cp-dirty"><i className="bi bi-circle-fill" /> Unsaved changes</span>
                                    : saved ? <span className="rr-cp-saved"><i className="bi bi-check-circle" /> Company profile saved</span>
                                        : <span className="rr-cp-save-state">Your company details are up to date.</span>}
                        </div>
                        <div className="rr-cp-action-buttons">
                            <button type="button" className="rr-cp-btn rr-cp-secondary" onClick={reset} disabled={form.processing || !form.isDirty}><i className="bi bi-arrow-counterclockwise" /> Reset</button>
                            <button type="submit" className="rr-cp-btn rr-cp-primary" disabled={form.processing || !form.isDirty}>{form.processing ? <span className="spinner-border spinner-border-sm" aria-hidden="true" /> : <i className="bi bi-check2" />}{form.processing ? 'Saving…' : 'Save Company Profile'}</button>
                        </div>
                    </footer>
                </form>
            </main>
        </BusinessLayout>
    );
}

function Section({ icon, title, description }) {
    return <header className="rr-cp-panel-heading"><span><i className={`bi ${icon}`} /></span><div><h2>{title}</h2><p>{description}</p></div></header>;
}

function Summary({ icon, label, value, tone }) {
    return <article className={`rr-cp-stat ${tone}`}><span className="rr-cp-stat-icon"><i className={`bi ${icon}`} /></span><div><small>{label}</small><strong>{value || 'Not provided'}</strong></div><i className={`bi ${icon} rr-cp-watermark`} /></article>;
}

function Field({ name, label, icon, value, onChange, error, hint, required = false, full = false, readOnly = false, disabled = false, textarea = false, type = 'text', placeholder = '', maxLength }) {
    const id = `company-${name}`;
    const props = {
        id, value, readOnly, disabled, required, placeholder, maxLength,
        'aria-invalid': Boolean(error),
        'aria-describedby': error ? `${id}-error` : hint ? `${id}-hint` : undefined,
        onChange: (event) => onChange?.(event.target.value),
    };
    return (
        <div className={`rr-cp-field ${full ? 'full' : ''}`}>
            <div className="rr-cp-label-row"><label htmlFor={id}>{label}{required && <span> *</span>}</label>{hint && <small id={`${id}-hint`}>{hint}</small>}</div>
            <div className={`rr-cp-input ${textarea ? 'textarea' : ''} ${readOnly ? 'readonly' : ''} ${error ? 'invalid' : ''}`}><i className={`bi ${icon}`} />{textarea ? <textarea {...props} rows={3} /> : <input {...props} type={type} />}</div>
            {error && <div id={`${id}-error`} className="rr-cp-error" role="alert"><i className="bi bi-exclamation-circle" /> {error}</div>}
        </div>
    );
}

function logoUrl(value) { const path = typeof value === 'string' ? value.trim() : ''; if (!path) return ''; if (/^https?:\/\//i.test(path)) return path; const relative = path.replace(/^\/+/, ''); return relative.startsWith('storage/') ? `/${relative}` : `/storage/${relative}`; }
function initials(value) { const words = String(value || 'Company').trim().split(/\s+/).filter(Boolean); return words.length > 1 ? `${words[0][0]}${words.at(-1)[0]}`.toUpperCase() : (words[0] || 'CO').slice(0, 2).toUpperCase(); }
function date(value) { const result = new Date(value); return Number.isNaN(result.getTime()) ? '—' : new Intl.DateTimeFormat('en-IN', { day: '2-digit', month: 'short', year: 'numeric' }).format(result); }
