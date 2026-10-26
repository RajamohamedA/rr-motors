import { Link, useForm } from '@inertiajs/react';
import { useEffect, useId, useState } from 'react';
import IndiaAddressFields from '../../../Components/IndiaAddressFields';

const FIELDS = [
    ['company_name', 'Company Name', 'text', true],
    ['owner_name', 'Owner Name', 'text'],
    ['gst_number', 'GST Number', 'text'],
    ['pan_number', 'PAN Number', 'text'],
    ['phone', 'Phone', 'tel'],
    ['email', 'Email', 'email'],
    ['website', 'Website', 'text'],
    ['pincode', 'Pincode', 'text'],
];

export default function CompanyForm({
    company = null,
    addressSelection = {},
    submitUrl,
    method = 'post',
    submitLabel = 'Save Company',
}) {
    const prefix = useId();
    const editing = Boolean(company);
    const [preview, setPreview] = useState('');
    const { data, setData, post, processing, errors } = useForm({
        _method: method.toLowerCase() === 'post' ? 'POST' : method.toUpperCase(),
        company_name: company?.company_name ?? '',
        owner_name: company?.owner_name ?? '',
        gst_number: company?.gst_number ?? '',
        pan_number: company?.pan_number ?? '',
        phone: company?.phone ?? '',
        email: company?.email ?? '',
        website: company?.website ?? '',
        address: company?.address ?? '',
        city: company?.city ?? '',
        district: company?.district ?? '',
        state: company?.state ?? '',
        country: company?.country ?? 'India',
        state_id: addressSelection.state_id ?? '',
        district_id: addressSelection.district_id ?? '',
        city_id: addressSelection.city_id ?? '',
        pincode: company?.pincode ?? '',
        branch_name: '',
        logo: null,
        status: company ? Boolean(company.status) : true,
    });

    useEffect(() => {
        if (!data.logo) {
            setPreview('');
            return;
        }
        const url = URL.createObjectURL(data.logo);
        setPreview(url);
        return () => URL.revokeObjectURL(url);
    }, [data.logo]);

    const storedLogo = company?.logo
        ? company.logo.startsWith('/storage/')
            ? company.logo
            : `/storage/${company.logo.replace(/^\/+/, '')}`
        : '';

    const submit = (event) => {
        event.preventDefault();
        if (processing) return;
        post(submitUrl, { forceFormData: true, preserveScroll: true });
    };

    return (
        <form onSubmit={submit}>
            <div className="rr-admin-panel">
                <div className="rr-admin-panel-header">
                    <div className="rr-admin-panel-heading">
                        <h2 className="rr-admin-panel-title">Company Information</h2>
                        <div className="rr-admin-panel-description">
                            {editing
                                ? 'Update business details and company location.'
                                : 'Enter business details. Your first branch is created automatically.'}
                        </div>
                    </div>
                </div>
                <div className="rr-admin-panel-body">
                    <fieldset
                        disabled={processing}
                        className="border-0 p-0 m-0 w-100"
                    >
                        <div className="row g-3">
                            {editing && (
                                <Field
                                    id={`${prefix}-code`}
                                    label="Company Code"
                                >
                                    <input
                                        id={`${prefix}-code`}
                                        className="rr-form-control"
                                        value={company.company_code ?? ''}
                                        disabled
                                    />
                                    <div className="rr-form-help">
                                        Generated automatically and cannot be
                                        changed.
                                    </div>
                                </Field>
                            )}
                            {FIELDS.map(([field, label, type, required]) => (
                                <Field
                                    key={field}
                                    id={`${prefix}-${field}`}
                                    label={label}
                                    error={errors[field]}
                                    required={required}
                                >
                                    <input
                                        id={`${prefix}-${field}`}
                                        type={type}
                                        className="rr-form-control"
                                        value={data[field]}
                                        required={Boolean(required)}
                                        maxLength={
                                            field === 'pincode'
                                                ? 10
                                                : field === 'phone'
                                                    ? 20
                                                    : 255
                                        }
                                        aria-invalid={Boolean(errors[field])}
                                        onChange={(event) =>
                                            setData(
                                                field,
                                                [
                                                    'gst_number',
                                                    'pan_number',
                                                ].includes(field)
                                                    ? event.target.value.toUpperCase()
                                                    : event.target.value,
                                            )
                                        }
                                    />
                                </Field>
                            ))}
                            {!editing && (
                                <Field
                                    id={`${prefix}-branch`}
                                    label="Branch / Location Name"
                                    error={errors.branch_name}
                                >
                                    <input
                                        id={`${prefix}-branch`}
                                        className="rr-form-control"
                                        value={data.branch_name}
                                        placeholder={
                                            data.city.trim() || 'Main Branch'
                                        }
                                        maxLength={100}
                                        onChange={(event) =>
                                            setData(
                                                'branch_name',
                                                event.target.value,
                                            )
                                        }
                                    />
                                    <div className="rr-form-help">
                                        Leave empty to use the selected city as the
                                        first branch name.
                                    </div>
                                </Field>
                            )}
                            <Field
                                id={`${prefix}-address`}
                                label="Address"
                                error={errors.address}
                                full
                            >
                                <textarea
                                    id={`${prefix}-address`}
                                    rows={3}
                                    className="rr-form-control"
                                    value={data.address}
                                    onChange={(event) =>
                                        setData('address', event.target.value)
                                    }
                                />
                            </Field>
                            {editing && !company.district && (
                                <div className="col-12">
                                    <div className="alert alert-info py-2 mb-0 small">
                                        This company has no saved district. Its
                                        current address is preserved. When changing
                                        the location, select a state, district and
                                        city/town.
                                    </div>
                                </div>
                            )}
                            <IndiaAddressFields
                                data={data}
                                setData={setData}
                                errors={errors}
                                disabled={processing}
                            />
                            <Field
                                id={`${prefix}-logo`}
                                label="Company Logo"
                                error={errors.logo}
                            >
                                {(preview || storedLogo) && (
                                    <img
                                        src={preview || storedLogo}
                                        alt={`${data.company_name || 'Company'} logo`}
                                        style={{
                                            display: 'block',
                                            width: 100,
                                            height: 75,
                                            objectFit: 'contain',
                                            background: '#fff',
                                            border: '1px solid #cbd8e8',
                                            borderRadius: 6,
                                            padding: 5,
                                            marginBottom: 10,
                                        }}
                                    />
                                )}
                                <input
                                    id={`${prefix}-logo`}
                                    type="file"
                                    className="rr-form-control"
                                    accept="image/png,image/jpeg,image/webp"
                                    onChange={(event) =>
                                        setData(
                                            'logo',
                                            event.target.files?.[0] ?? null,
                                        )
                                    }
                                />
                                <div className="rr-form-help">
                                    JPG, PNG or WebP. Maximum 2 MB. Leave empty to
                                    keep the current logo.
                                </div>
                            </Field>
                            <Field
                                id={`${prefix}-status`}
                                label="Status"
                                error={errors.status}
                            >
                                <select
                                    id={`${prefix}-status`}
                                    className="rr-form-control"
                                    value={data.status ? '1' : '0'}
                                    onChange={(event) =>
                                        setData('status', event.target.value === '1')
                                    }
                                >
                                    <option value="1">Active</option>
                                    <option value="0">Inactive</option>
                                </select>
                            </Field>
                        </div>
                    </fieldset>
                </div>
                <div className="rr-company-form-footer">
                    <Link
                        href={
                            editing
                                ? `/super-admin/companies/${company.id}`
                                : '/super-admin/companies'
                        }
                        className="rr-btn rr-btn-secondary"
                    >
                        <i
                            className="bi bi-arrow-left"
                            aria-hidden="true"
                        />
                        Cancel
                    </Link>
                    <button
                        type="submit"
                        className="rr-btn rr-btn-primary"
                        disabled={processing}
                    >
                        {processing ? (
                            <span
                                className="spinner-border spinner-border-sm"
                                aria-hidden="true"
                            />
                        ) : (
                            <i
                                className="bi bi-check-lg"
                                aria-hidden="true"
                            />
                        )}
                        {processing ? 'Saving…' : submitLabel}
                    </button>
                </div>
            </div>
        </form>
    );
}

function Field({ id, label, children, error, required = false, full = false }) {
    return (
        <div className={full ? 'col-12' : 'col-md-6'}>
            <label
                className="rr-form-label"
                htmlFor={id}
            >
                {label}
                {required && <span className="text-danger ms-1">*</span>}
            </label>
            {children}
            {error && <div className="text-danger small mt-1">{error}</div>}
        </div>
    );
}
