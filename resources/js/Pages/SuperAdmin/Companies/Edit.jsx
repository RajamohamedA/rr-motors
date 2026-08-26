import { Head, useForm } from '@inertiajs/react';

import SuperAdminLayout
    from '../../../Layouts/SuperAdminLayout';


export default function Edit({ company }) {

    const {
        data,
        setData,
        post,
        processing,
        errors,
    } = useForm({

        _method: 'PUT',

        company_name:
            company.company_name ?? '',

        owner_name:
            company.owner_name ?? '',

        gst_number:
            company.gst_number ?? '',

        pan_number:
            company.pan_number ?? '',

        email:
            company.email ?? '',

        phone:
            company.phone ?? '',

        website:
            company.website ?? '',

        address:
            company.address ?? '',

        city:
            company.city ?? '',

        state:
            company.state ?? '',

        country:
            company.country ?? 'India',

        pincode:
            company.pincode ?? '',

        logo: null,

        status:
            Boolean(company.status),
    });


    const submit = (event) => {

        event.preventDefault();

        post(
            `/super-admin/companies/${company.id}`,
            {
                forceFormData: true,

                preserveScroll: true,
            }
        );
    };


    return (
        <SuperAdminLayout
            title="Edit Company"
            subtitle={company.company_name}
            breadcrumbs={[
                {
                    label: 'Companies',
                    href: '/super-admin/companies',
                },
                {
                    label: company.company_name,
                    href: `/super-admin/companies/${company.id}`,
                },
                {
                    label: 'Edit',
                },
            ]}
        >

            <Head
                title={`Edit ${company.company_name}`}
            />


            <form onSubmit={submit}>

                <div className="rr-admin-panel">

                    <div className="rr-admin-panel-header">

                        <div className="rr-admin-panel-heading">

                            <h2 className="rr-admin-panel-title">
                                Company Information
                            </h2>

                            <div className="rr-admin-panel-description">
                                Update company information
                            </div>

                        </div>

                    </div>


                    <div className="rr-admin-panel-body">

                        <div className="col-md-6">

                            <label className="rr-form-label">
                                Company Code
                            </label>

                            <input
                                type="text"
                                className="rr-form-control"
                                value={company.company_code ?? ''}
                                disabled
                            />

                            <div className="rr-form-help">
                                Generated automatically and cannot be changed.
                            </div>

                        </div>

                        <div className="row g-3">

                            {[
                                ['company_name', 'Company Name'],
                                ['owner_name', 'Owner Name'],
                                ['gst_number', 'GST Number'],
                                ['pan_number', 'PAN Number'],
                                ['phone', 'Phone'],
                                ['email', 'Email'],
                                ['website', 'Website'],
                                ['pincode', 'Pincode'],
                                ['city', 'City'],
                                ['state', 'State'],
                                ['country', 'Country'],
                            ].map(([field, label]) => (

                                <div
                                    className="col-md-6"
                                    key={field}
                                >

                                    <label className="rr-form-label">
                                        {label}
                                    </label>

                                    <input
                                        className="rr-form-control"
                                        value={data[field]}
                                        onChange={(e) =>
                                            setData(
                                                field,
                                                e.target.value
                                            )
                                        }
                                    />

                                    {errors[field] && (
                                        <div className="text-danger small mt-1">
                                            {errors[field]}
                                        </div>
                                    )}

                                </div>

                            ))}


                            <div className="col-12">

                                <label className="rr-form-label">
                                    Address
                                </label>

                                <textarea
                                    rows="3"
                                    className="rr-form-control"
                                    value={data.address}
                                    onChange={(e) =>
                                        setData(
                                            'address',
                                            e.target.value
                                        )
                                    }
                                />

                            </div>


                            <div className="col-md-6">

                                <label className="rr-form-label">
                                    Logo
                                </label>

                                <input
                                    type="file"
                                    className="rr-form-control"
                                    accept="image/png,image/jpeg,image/webp"
                                    onChange={(e) =>
                                        setData(
                                            'logo',
                                            e.target.files[0]
                                        )
                                    }
                                />

                            </div>


                            <div className="col-md-6">

                                <label className="rr-form-label">
                                    Status
                                </label>

                                <select
                                    className="rr-form-control"
                                    value={
                                        data.status
                                            ? '1'
                                            : '0'
                                    }
                                    onChange={(e) =>
                                        setData(
                                            'status',
                                            e.target.value === '1'
                                        )
                                    }
                                >

                                    <option value="1">
                                        Active
                                    </option>

                                    <option value="0">
                                        Inactive
                                    </option>

                                </select>

                            </div>

                        </div>

                    </div>


                    <div className="rr-company-form-footer">

                        <button
                            type="submit"
                            className="rr-btn rr-btn-primary"
                            disabled={processing}
                        >

                            <i className="bi bi-check-lg"></i>

                            Save Changes

                        </button>

                    </div>

                </div>

            </form>

        </SuperAdminLayout>
    );
}