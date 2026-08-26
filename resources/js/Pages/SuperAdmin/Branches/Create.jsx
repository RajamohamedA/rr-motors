import {
    Head,
    Link,
    useForm,
} from '@inertiajs/react';

import SuperAdminLayout
    from '../../../Layouts/SuperAdminLayout';


export default function Create({ company }) {

    const {
        data,
        setData,
        post,
        processing,
        errors,
    } = useForm({

        store_code: '',
        store_name: '',
        contact_person: '',
        phone: '',
        email: '',
        address: '',
        city: '',
        state: 'Tamil Nadu',
        country: 'India',
        pincode: '',
        status: true,

    });


    const submit = (event) => {

        event.preventDefault();

        post(
            `/super-admin/companies/${company.id}/branches`,
            {
                preserveScroll: true,
            }
        );
    };


    return (

        <SuperAdminLayout
            title="New Branch"
            subtitle={`Create branch for ${company.company_name}`}
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
                    label: 'New Branch',
                },
            ]}
        >

            <Head title="New Branch" />


            <form onSubmit={submit}>

                <div className="rr-admin-panel">

                    <div className="rr-admin-panel-header">

                        <div className="rr-admin-panel-heading">

                            <h2 className="rr-admin-panel-title">
                                Branch Information
                            </h2>

                            <div className="rr-admin-panel-description">
                                Add a new branch under {company.company_name}
                            </div>

                        </div>

                    </div>


                    <div className="rr-admin-panel-body">

                        <div className="row g-3">


                            <Field
                                label="Branch Name"
                                required
                                error={errors.store_name}
                            >
                                <input
                                    type="text"
                                    className={`rr-form-control ${errors.store_name
                                            ? 'rr-form-control-error'
                                            : ''
                                        }`}
                                    value={data.store_name}
                                    onChange={(e) =>
                                        setData(
                                            'store_name',
                                            e.target.value
                                        )
                                    }
                                />
                            </Field>


                            <Field
                                label="Contact Person"
                                error={errors.contact_person}
                            >
                                <input
                                    type="text"
                                    className="rr-form-control"
                                    value={data.contact_person}
                                    onChange={(e) =>
                                        setData(
                                            'contact_person',
                                            e.target.value
                                        )
                                    }
                                />
                            </Field>


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


                            <Field
                                label="Address"
                                full
                                error={errors.address}
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


                    <div className="rr-company-form-footer">

                        <Link
                            href={`/super-admin/companies/${company.id}`}
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
                                    Create Branch
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

                    <i className="bi bi-exclamation-circle"></i>

                    <span>
                        {error}
                    </span>

                </div>
            )}

        </div>
    );
}