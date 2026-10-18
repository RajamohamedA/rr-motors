import React, { useState } from 'react';
import { Head, Link, useForm } from '@inertiajs/react';
import BusinessLayout from '@/Layouts/BusinessLayout';
import '../../../css/business/settings.css';

const groups = [
    {
        title: 'Billing defaults', icon: 'bi-receipt', fields: [
            { key: 'default_billing_type', label: 'Default bill type', help: 'Initial selection when starting a new sale.', options: [['non_gst', 'Cash bill · Without GST'], ['gst', 'GST bill']] },
            { key: 'fallback_gst_percent', label: 'Fallback GST percentage', help: 'For the existing GST-bill flow when a product has no GST percentage.', type: 'number' },
            { key: 'rounding_mode', label: 'Final amount rounding', options: [['nearest_rupee', 'Nearest rupee'], ['none', 'Keep paise · No rounding']] },
        ]
    },
    {
        title: 'Print preferences', icon: 'bi-printer', fields: [
            { key: 'default_paper', label: 'Default paper', options: [['a4', 'A4'], ['a5', 'A5'], ['quick', 'Quick bill']] },
            { key: 'quick_bill_width', label: 'Quick bill paper width', options: [[80, '80 mm'], [58, '58 mm']], numeric: true },
            { key: 'auto_print', label: 'Open print dialog automatically', help: 'For the Complete & Print workflow.', type: 'boolean' },
            { key: 'show_company_email', label: 'Show company email on bill', type: 'boolean' },
            { key: 'footer_text', label: 'Bill footer message', type: 'textarea', help: 'Leave blank to hide the footer message.' },
        ]
    },
    {
        title: 'Counter preferences', icon: 'bi-keyboard', fields: [
            { key: 'quick_quantity_mode', label: 'Start with quantity 1 mode', help: 'Return to product search after adding a single-unit item.', type: 'boolean' },
            { key: 'shortcuts_expanded', label: 'Open billing shortcuts by default', type: 'boolean' },
        ]
    },
];
const has = (object, key) => Object.prototype.hasOwnProperty.call(object, key);

export default function BillingPrint(props) {
    return <SettingsForm key={`${props.scope}-${props.company.id}-${props.store.id}`} {...props} />;
}

function SettingsForm({ scope, company, store, defaults, companySettings, branchOverrides, canManageCompany, canEdit }) {
    const branch = scope === 'branch';
    const form = useForm({ settings: { ...(branch ? branchOverrides : companySettings) } });
    const [saved, setSaved] = useState(false);
    const effective = branch ? { ...companySettings, ...form.data.settings } : { ...defaults, ...form.data.settings };
    const count = Object.keys(form.data.settings).length;
    const endpoint = branch ? '/settings/billing-print' : '/settings/company/billing-print';
    const change = (key, value) => {
        setSaved(false);
        form.clearErrors(`settings.${key}`);
        form.setData('settings', { ...form.data.settings, [key]: value });
    };
    const inherit = (key, checked) => {
        const next = { ...form.data.settings };
        if (checked) delete next[key];
        else next[key] = companySettings[key];
        setSaved(false);
        form.clearErrors(`settings.${key}`);
        form.setData('settings', next);
    };
    const submit = (event) => {
        event.preventDefault();
        if (!canEdit || form.processing) return;
        setSaved(false);
        form.put(endpoint, {
            preserveScroll: true,
            onSuccess: () => { form.defaults({ settings: { ...form.data.settings } }); setSaved(true); },
            onError: (errors) => requestAnimationFrame(() => {
                const key = Object.keys(errors).find((name) => name.startsWith('settings.'))?.slice(9);
                document.getElementById(`setting-${key}`)?.focus();
            }),
        });
    };
    return <BusinessLayout>
        <Head title="Billing & Print Settings" />
        <div className="rr-settings-page">
            <header className="rr-settings-header">
                <div className="rr-settings-heading"><span className="rr-settings-hero-icon"><i className="bi bi-sliders" aria-hidden="true" /></span><div><span className="rr-settings-eyebrow">APPLICATION SETTINGS</span><h1>Billing & Print</h1><p>Manage company defaults and branch preferences.</p></div></div>
                <div className="rr-settings-context"><span><i className="bi bi-building" /> {company.company_name}</span><span><i className="bi bi-shop" /> {store.store_name}</span></div>
            </header>
            <nav className="rr-settings-tabs" aria-label="Settings scope">
                <Link href="/settings/billing-print" className={branch ? 'active' : ''} aria-current={branch ? 'page' : undefined}><i className="bi bi-shop" /> Branch settings</Link>
                {canManageCompany && <Link href="/settings/company/billing-print" className={!branch ? 'active' : ''} aria-current={!branch ? 'page' : undefined}><i className="bi bi-buildings" /> Company defaults</Link>}
            </nav>
            <div className="rr-settings-notice"><i className="bi bi-info-circle" /><span>{branch ? 'Use company default to inherit a setting. Disable it to customise this branch.' : 'Company defaults apply to branches that have not overridden the same setting.'}</span></div>
            {!canEdit && <div className="rr-settings-message" role="status"><i className="bi bi-lock" /> You have view-only access to these settings.</div>}
            <form onSubmit={submit} aria-busy={form.processing}>
                <div className="rr-settings-grid">
                    <main>
                        {groups.map((group) => <section className="rr-settings-card" key={group.title}>
                            <div className="rr-settings-card-title"><span><i className={`bi ${group.icon}`} /></span><h2>{group.title}</h2></div>
                            <div className="rr-settings-fields">{group.fields.map((field) => {
                                const inherited = branch && !has(form.data.settings, field.key);
                                const disabled = !canEdit || form.processing || inherited;
                                const value = effective[field.key] ?? '';
                                const error = form.errors[`settings.${field.key}`];
                                const id = `setting-${field.key}`;
                                const accessibility = { id, disabled, 'aria-invalid': Boolean(error), 'aria-describedby': `${id}-help${error ? ` ${id}-error` : ''}` };
                                return <div className={`rr-settings-field ${inherited ? 'inherited' : ''}`} key={field.key}>
                                    <div className="rr-settings-field-top"><label htmlFor={id}>{field.label}</label>{branch && <label className="rr-settings-inherit"><input type="checkbox" checked={inherited} disabled={!canEdit || form.processing} onChange={(e) => inherit(field.key, e.target.checked)} /> Use company default</label>}</div>
                                    {field.options ? <select {...accessibility} value={value} onChange={(e) => change(field.key, field.numeric ? Number(e.target.value) : e.target.value)}>{field.options.map(([option, label]) => <option value={option} key={option}>{label}</option>)}</select>
                                        : field.type === 'boolean' ? <label className="rr-settings-toggle"><input {...accessibility} type="checkbox" checked={Boolean(value)} onChange={(e) => change(field.key, e.target.checked)} /><span aria-hidden="true" className="rr-settings-toggle-track" /><strong>{value ? 'Enabled' : 'Disabled'}</strong></label>
                                            : field.type === 'textarea' ? <textarea {...accessibility} rows={2} maxLength={300} value={value} onChange={(e) => change(field.key, e.target.value)} />
                                                : <div className="rr-settings-percent"><input {...accessibility} type="number" min="0" max="100" step="0.01" value={value} onChange={(e) => change(field.key, e.target.value)} /><span>%</span></div>}
                                    <small id={`${id}-help`}>{field.help || (inherited ? 'Inherited from company defaults.' : 'Saved for the selected scope.')}</small>
                                    {error && <span className="rr-settings-error" id={`${id}-error`}>{error}</span>}
                                </div>;
                            })}</div>
                        </section>)}
                    </main>
                    <aside className="rr-settings-aside">
                        <section className="rr-settings-card rr-settings-summary"><div className="rr-settings-card-title"><span><i className="bi bi-check2-square" /></span><h2>Current preferences</h2></div><dl><div><dt>Editing</dt><dd>{branch ? 'This branch' : 'Company defaults'}</dd></div><div><dt>Bill type</dt><dd>{effective.default_billing_type === 'gst' ? 'GST bill' : 'Cash bill'}</dd></div><div><dt>Paper</dt><dd>{effective.default_paper === 'quick' ? `${effective.quick_bill_width} mm quick bill` : String(effective.default_paper).toUpperCase()}</dd></div><div><dt>Auto-print</dt><dd>{effective.auto_print ? 'Enabled' : 'Disabled'}</dd></div>{branch && <div><dt>Branch overrides</dt><dd>{count}</dd></div>}</dl><p>Existing invoices keep their saved amounts. These preferences need to be connected to the sales and print screens before they take effect there.</p></section>
                        <section className="rr-settings-card rr-settings-scope"><i className="bi bi-shield-check" /><h2>{branch ? store.store_name : company.company_name}</h2><p>{branch ? 'Changes apply to this branch only.' : 'Changes apply across this company unless a branch has its own override.'}</p></section>
                    </aside>
                </div>
                {Object.keys(form.errors).length > 0 && <div className="rr-settings-error-banner" role="alert">{form.errors.settings || 'Please correct the highlighted settings before saving.'}</div>}
                {saved && <div className="rr-settings-success" role="status"><i className="bi bi-check-circle-fill" /> Settings saved successfully.</div>}
                <footer className="rr-settings-savebar"><span aria-live="polite">{form.processing ? 'Saving settings…' : form.isDirty ? 'You have unsaved changes' : 'No unsaved changes'}</span><div><button className="rr-settings-btn secondary" type="button" disabled={!canEdit || form.processing || !form.isDirty} onClick={() => { form.reset(); form.clearErrors(); setSaved(false); }}>Discard changes</button><button className="rr-settings-btn primary" type="submit" disabled={!canEdit || form.processing || !form.isDirty}>{form.processing ? <span className="spinner-border spinner-border-sm" aria-hidden="true" /> : <i className="bi bi-check2" />} {form.processing ? 'Saving…' : 'Save settings'}</button></div></footer>
            </form>
        </div>
    </BusinessLayout>;
}
