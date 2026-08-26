import { useForm } from '@inertiajs/react';

export default function CompanyForm({
    company = null,
    submitUrl,
    method = 'post',
    submitLabel = 'Save Company',
}) {

    const { data, setData, post, processing, errors } =
        useForm({

            company_name:
                company?.company_name ?? '',

            owner_name:
                company?.owner_name ?? '',

            gst_number:
                company?.gst_number ?? '',

            pan_number:
                company?.pan_number ?? '',

            email:
                company?.email ?? '',

            phone:
                company?.phone ?? '',

            website:
                company?.website ?? '',

            address:
                company?.address ?? '',

            city:
                company?.city ?? '',

            state:
                company?.state ?? '',

            country:
                company?.country ?? 'India',

            pincode:
                company?.pincode ?? '',

            logo: null,

            status:
                company
                    ? Boolean(company.status)
                    : true,
        });


    const submit = (event) => {

        event.preventDefault();

        post(submitUrl, {

            forceFormData: true,

            method,

            preserveScroll: true,

        });

    };


    return (
        <form onSubmit={submit}>

            <div className="rr-admin-panel">

                <div className="rr-admin-panel-header">

                    <div className="rr-admin-panel-heading">

                        <h2 className="rr-admin-panel-title">
                            Company Information
                        </h2>

                        <div className="rr-admin-panel-description">
                            Legal and contact information
                        </div>

                    </div>

                </div>


                <div className="rr-admin-panel-body">

                    <div className="row g-3">


                        <Field
                            label="Company Name"
                            required
                            error={errors.company_name}
                        >

                            <input
                                className="rr-form-control"
                                value={data.company_name}
                                onChange={(e) =>
                                    setData(
                                        'company_name',
                                        e.target.value
                                    )
                                }
                            />

                        </Field>


                        <Field
                            label="Owner Name"
                            error={errors.owner_name}
                        >

                            <input
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


                        <Field
                            label="GST Number"
                            error={errors.gst_number}
                        >

                            <input
                                className="rr-form-control"
                                value={data.gst_number}
                                onChange={(e) =>
                                    setData(
                                        'gst_number',
                                        e.target.value.toUpperCase()
                                    )
                                }
                            />

                        </Field>


                        <Field
                            label="PAN Number"
                            error={errors.pan_number}
                        >

                            <input
                                className="rr-form-control"
                                value={data.pan_number}
                                onChange={(e) =>
                                    setData(
                                        'pan_number',
                                        e.target.value.toUpperCase()
                                    )
                                }
                            />

                        </Field>


                        <Field
                            label="Phone"
                            error={errors.phone}
                        >

                            <input
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


                        <Field
                            label="Website"
                            error={errors.website}
                        >

                            <input
                                className="rr-form-control"
                                value={data.website}
                                onChange={(e) =>
                                    setData(
                                        'website',
                                        e.target.value
                                    )
                                }
                            />

                        </Field>


                        <Field
                            label="Pincode"
                            error={errors.pincode}
                        >

                            <input
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


                        <Field
                            label="City"
                            error={errors.city}
                        >

                            <input
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


                        <Field
                            label="State"
                            error={errors.state}
                        >

                            <input
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


                        <Field
                            label="Country"
                            error={errors.country}
                        >

                            <input
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


                        <Field
                            label="Company Logo"
                            error={errors.logo}
                        >

                            <input
                                type="file"
                                accept="image/png,image/jpeg,image/webp"
                                className="rr-form-control"
                                onChange={(e) =>
                                    setData(
                                        'logo',
                                        e.target.files[0]
                                    )
                                }
                            />

                        </Field>


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
                        disabled={processing}
                        className="rr-btn rr-btn-primary"
                    >

                        {processing && (
                            <span className="spinner-border spinner-border-sm" />
                        )}

                        <i className="bi bi-check-lg"></i>

                        {submitLabel}

                    </button>

                </div>

            </div>

        </form>
    );
}


function Field({
    label,
    error,
    children,
    required = false,
    full = false,
}) {

    return (
        <div
            className={
                full
                    ? 'col-12'
                    : 'col-md-6'
            }
        >

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
                <div className="text-danger small mt-1">
                    {error}
                </div>
            )}

        </div>
    );
}