import {
    Head,
    Link,
    useForm,
} from '@inertiajs/react';

import SuperAdminLayout
    from '../../../Layouts/SuperAdminLayout';


export default function Edit({
    company,
    store,
}) {

    const {
        data,
        setData,
        put,
        processing,
        errors,
    } = useForm({

        store_name:
            store.store_name ?? '',

        contact_person:
            store.contact_person ?? '',

        phone:
            store.phone ?? '',

        email:
            store.email ?? '',

        address:
            store.address ?? '',

        city:
            store.city ?? '',

        state:
            store.state ?? '',

        country:
            store.country ?? 'India',

        pincode:
            store.pincode ?? '',

        status:
            Boolean(store.status),

    });


    const submit = (event) => {

        event.preventDefault();

        put(
            `/super-admin/companies/${company.id}/branches/${store.id}`,
            {
                preserveScroll: true,
            }
        );

    };


    return (

        <SuperAdminLayout
            title="Edit Branch"
            subtitle={`${store.store_name} • ${store.store_code}`}
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
                    label: store.store_name,
                },
                {
                    label: 'Edit',
                },
            ]}
        >

            <Head
                title={`Edit ${store.store_name}`}
            />


            <form onSubmit={submit}>

                <div className="rr-admin-panel">

                    <div className="rr-admin-panel-header">

                        <div className="rr-admin-panel-heading">

                            <h2 className="rr-admin-panel-title">
                                Branch Information
                            </h2>

                            <div className="rr-admin-panel-description">
                                Update branch contact and location details
                            </div>

                        </div>

                    </div>


                    <div className="rr-admin-panel-body">

                        {/* Permanent branch code */}

                        <div className="rr-admin-info-box mb-4">

                            <i className="bi bi-shield-check"></i>

                            <span>
                                Branch code
                                <strong className="mx-1">
                                    {store.store_code}
                                </strong>
                                is permanent and cannot be changed.
                            </span>

                        </div>


                        <div className="row g-3">

                            <Field
                                label="Branch Code"
                            >

                                <input
                                    type="text"
                                    className="rr-form-control"
                                    value={store.store_code ?? ''}
                                    disabled
                                />

                            </Field>


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
                                    autoFocus
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
                                    Saving...
                                </>
                            ) : (
                                <>
                                    <i className="bi bi-check-lg"></i>
                                    Save Changes
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