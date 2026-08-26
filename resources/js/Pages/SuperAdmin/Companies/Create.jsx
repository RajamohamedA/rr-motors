import { Head, Link, useForm } from '@inertiajs/react';

import SuperAdminLayout
    from '../../../Layouts/SuperAdminLayout';

export default function Create() {

    const {
        data,
        setData,
        post,
        processing,
        errors,
    } = useForm({

        company_name: '',
        owner_name: '',
        gst_number: '',
        pan_number: '',
        email: '',
        phone: '',
        website: '',
        address: '',
        city: '',
        state: 'Tamil Nadu',
        country: 'India',
        pincode: '',
        logo: null,
        status: true,

    });


    const submit = (event) => {

        event.preventDefault();

        post(
            '/super-admin/companies',
            {
                forceFormData: true,
                preserveScroll: true,
            }
        );

    };


    return (

        <SuperAdminLayout
            title="New Company"
            subtitle="Create a new company"
            breadcrumbs={[
                {
                    label: 'Companies',
                    href: '/super-admin/companies',
                },
                {
                    label: 'New Company',
                },
            ]}
        >

            <Head title="New Company" />


            <form onSubmit={submit}>

                <div className="rr-admin-panel">

                    {/* Header */}

                    <div className="rr-admin-panel-header">

                        <div className="rr-admin-panel-heading">

                            <h2 className="rr-admin-panel-title">
                                Company Information
                            </h2>

                            <div className="rr-admin-panel-description">
                                Enter legal, contact and address details
                            </div>

                        </div>

                    </div>


                    {/* Form */}

                    <div className="rr-admin-panel-body">

                        <div className="row g-3">

                            {/* Company Name */}

                            <Field
                                label="Company Name"
                                required
                                error={errors.company_name}
                            >

                                <input
                                    type="text"
                                    className="rr-form-control"
                                    value={data.company_name}
                                    onChange={(e) =>
                                        setData(
                                            'company_name',
                                            e.target.value
                                        )
                                    }
                                    autoFocus
                                />

                            </Field>


                            {/* Owner */}

                            <Field
                                label="Owner Name"
                                error={errors.owner_name}
                            >

                                <input
                                    type="text"
                                    className="rr-form-control"
                                    value={data.owner_name}
                                    onChange={(e) =>
                                        setData(
                                            'owner_name',
                                            e.target.value
                                        )
                                    }
                                />

                            </Field>


                            {/* GST */}

                            <Field
                                label="GST Number"
                                error={errors.gst_number}
                            >

                                <input
                                    type="text"
                                    className="rr-form-control text-uppercase"
                                    value={data.gst_number}
                                    onChange={(e) =>
                                        setData(
                                            'gst_number',
                                            e.target.value.toUpperCase()
                                        )
                                    }
                                />

                            </Field>


                            {/* PAN */}

                            <Field
                                label="PAN Number"
                                error={errors.pan_number}
                            >

                                <input
                                    type="text"
                                    className="rr-form-control text-uppercase"
                                    value={data.pan_number}
                                    onChange={(e) =>
                                        setData(
                                            'pan_number',
                                            e.target.value.toUpperCase()
                                        )
                                    }
                                />

                            </Field>


                            {/* Phone */}

                            <Field
                                label="Phone"
                                error={errors.phone}
                            >

                                <input
                                    type="text"
                                    className="rr-form-control"
                                    value={data.phone}
                                    onChange={(e) =>
                                        setData(
                                            'phone',
                                            e.target.value
                                        )
                                    }
                                />

                            </Field>


                            {/* Email */}

                            <Field
                                label="Email"
                                error={errors.email}
                            >

                                <input
                                    type="email"
                                    className="rr-form-control"
                                    value={data.email}
                                    onChange={(e) =>
                                        setData(
                                            'email',
                                            e.target.value
                                        )
                                    }
                                />

                            </Field>


                            {/* Website */}

                            <Field
                                label="Website"
                                error={errors.website}
                            >

                                <input
                                    type="text"
                                    className="rr-form-control"
                                    placeholder="https://example.com"
                                    value={data.website}
                                    onChange={(e) =>
                                        setData(
                                            'website',
                                            e.target.value
                                        )
                                    }
                                />

                            </Field>


                            {/* Pincode */}

                            <Field
                                label="Pincode"
                                error={errors.pincode}
                            >

                                <input
                                    type="text"
                                    className="rr-form-control"
                                    value={data.pincode}
                                    onChange={(e) =>
                                        setData(
                                            'pincode',
                                            e.target.value
                                        )
                                    }
                                />

                            </Field>


                            {/* Address */}

                            <Field
                                label="Address"
                                error={errors.address}
                                full
                            >

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

                            </Field>


                            {/* City */}

                            <Field
                                label="City"
                                error={errors.city}
                            >

                                <input
                                    type="text"
                                    className="rr-form-control"
                                    value={data.city}
                                    onChange={(e) =>
                                        setData(
                                            'city',
                                            e.target.value
                                        )
                                    }
                                />

                            </Field>


                            {/* State */}

                            <Field
                                label="State"
                                error={errors.state}
                            >

                                <input
                                    type="text"
                                    className="rr-form-control"
                                    value={data.state}
                                    onChange={(e) =>
                                        setData(
                                            'state',
                                            e.target.value
                                        )
                                    }
                                />

                            </Field>


                            {/* Country */}

                            <Field
                                label="Country"
                                error={errors.country}
                            >

                                <input
                                    type="text"
                                    className="rr-form-control"
                                    value={data.country}
                                    onChange={(e) =>
                                        setData(
                                            'country',
                                            e.target.value
                                        )
                                    }
                                />

                            </Field>


                            {/* Logo */}

                            <Field
                                label="Company Logo"
                                error={errors.logo}
                            >

                                <input
                                    type="file"
                                    accept=".jpg,.jpeg,.png,.webp"
                                    className="rr-form-control"
                                    onChange={(e) =>
                                        setData(
                                            'logo',
                                            e.target.files?.[0] ?? null
                                        )
                                    }
                                />

                                <div className="rr-form-help">
                                    JPG, PNG or WebP. Maximum 2 MB.
                                </div>

                            </Field>


                            {/* Status */}

                            <Field
                                label="Status"
                                error={errors.status}
                            >

                                <select
                                    className="rr-form-control"
                                    value={data.status ? '1' : '0'}
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

                            </Field>

                        </div>

                    </div>


                    {/* Footer */}

                    <div className="rr-company-form-footer">

                        <Link
                            href="/super-admin/companies"
                            className="rr-btn rr-btn-secondary"
                        >
                            <i className="bi bi-x-lg"></i>
                            Cancel
                        </Link>


                        <button
                            type="submit"
                            className="rr-btn rr-btn-primary"
                            disabled={processing}
                        >

                            {processing ? (

                                <>
                                    <span className="spinner-border spinner-border-sm"></span>
                                    Creating...
                                </>

                            ) : (

                                <>
                                    <i className="bi bi-check-lg"></i>
                                    Create Company
                                </>

                            )}

                        </button>

                    </div>

                </div>

            </form>

        </SuperAdminLayout>
    );
}


function Field({
    label,
    children,
    error,
    required = false,
    full = false,
}) {

    return (

        <div className={full ? 'col-12' : 'col-lg-6'}>

            <label className="rr-form-label">

                {label}

                {required && (
                    <span className="text-danger ms-1">
                        *
                    </span>
                )}

            </label>

            {children}

            {error && (
                <div className="rr-form-error">
                    <i className="bi bi-exclamation-circle me-1"></i>
                    {error}
                </div>
            )}

        </div>
    );
}