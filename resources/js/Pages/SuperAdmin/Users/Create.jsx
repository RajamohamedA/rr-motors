import {
    Head,
    Link,
    useForm,
} from '@inertiajs/react';

import {
    useEffect,
    useState,
} from 'react';

import axios from 'axios';

import SuperAdminLayout
    from '../../../Layouts/SuperAdminLayout';

import SearchableSelect
    from '../../../Components/SearchableSelect';


export default function Create({
    companies = [],
    roles = [],
}) {

    const [branches, setBranches] =
        useState([]);

    const [loadingBranches, setLoadingBranches] =
        useState(false);


    const {
        data,
        setData,
        post,
        processing,
        errors,
    } = useForm({

        name: '',
        email: '',
        mobile: '',

        company_id: '',

        store_ids: [],

        role: '',

        password: '',
        password_confirmation: '',

        status: true,
    });


    /*
    |--------------------------------------------------------------------------
    | Load Branches
    |--------------------------------------------------------------------------
    */

    useEffect(() => {

        if (!data.company_id) {

            setBranches([]);

            setData(
                'store_ids',
                []
            );

            return;
        }


        const loadBranches =
            async () => {

                setLoadingBranches(true);

                try {

                    const response =
                        await axios.get(
                            `/super-admin/companies/${data.company_id}/branches/options`
                        );

                    setBranches(
                        response.data.branches ?? []
                    );


                    /*
                    |--------------------------------------------------------------------------
                    | Clear previous company branch selection
                    |--------------------------------------------------------------------------
                    */

                    setData(
                        'store_ids',
                        []
                    );

                } catch (error) {

                    console.error(
                        'Unable to load branches:',
                        error
                    );

                    setBranches([]);

                } finally {

                    setLoadingBranches(false);

                }

            };


        loadBranches();

    }, [data.company_id]);


    /*
    |--------------------------------------------------------------------------
    | Branch Selection
    |--------------------------------------------------------------------------
    */

    const toggleBranch = (branchId) => {

        const id =
            Number(branchId);

        const selected =
            data.store_ids.map(Number);

        if (selected.includes(id)) {

            setData(
                'store_ids',
                selected.filter(
                    (storeId) =>
                        storeId !== id
                )
            );

        } else {

            setData(
                'store_ids',
                [
                    ...selected,
                    id,
                ]
            );

        }

    };


    /*
    |--------------------------------------------------------------------------
    | Submit
    |--------------------------------------------------------------------------
    */

    const submit = (event) => {

        event.preventDefault();

        post(
            '/super-admin/users',
            {
                preserveScroll: true,
            }
        );

    };


    return (

        <SuperAdminLayout
            title="New User"
            subtitle="Create a company user and assign branch access"
            breadcrumbs={[
                {
                    label: 'Users',
                    href: '/super-admin/users',
                },
                {
                    label: 'New User',
                },
            ]}
        >

            <Head title="New User" />


            <form onSubmit={submit}>

                {/* =====================================================
                    ACCOUNT
                   ===================================================== */}

                <div className="rr-admin-panel mb-4">

                    <div className="rr-admin-panel-header">

                        <div className="rr-admin-panel-heading">

                            <h2 className="rr-admin-panel-title">
                                User Account
                            </h2>

                            <div className="rr-admin-panel-description">
                                Basic login and contact information
                            </div>

                        </div>

                    </div>


                    <div className="rr-admin-panel-body">

                        <div className="row g-3">

                            <Field
                                label="Full Name"
                                required
                                error={errors.name}
                            >

                                <input
                                    type="text"
                                    className={fieldClass(
                                        errors.name
                                    )}
                                    value={data.name}
                                    onChange={(event) =>
                                        setData(
                                            'name',
                                            event.target.value
                                        )
                                    }
                                    autoFocus
                                />

                            </Field>


                            <Field
                                label="Mobile"
                                error={errors.mobile}
                            >

                                <input
                                    type="text"
                                    className={fieldClass(
                                        errors.mobile
                                    )}
                                    value={data.mobile}
                                    onChange={(event) =>
                                        setData(
                                            'mobile',
                                            event.target.value
                                        )
                                    }
                                />

                            </Field>


                            <Field
                                label="Email"
                                required
                                error={errors.email}
                            >

                                <input
                                    type="email"
                                    className={fieldClass(
                                        errors.email
                                    )}
                                    value={data.email}
                                    onChange={(event) =>
                                        setData(
                                            'email',
                                            event.target.value
                                        )
                                    }
                                />

                            </Field>


                            <Field
                                label="Status"
                                error={errors.status}
                            >

                                <select
                                    className={fieldClass(
                                        errors.status
                                    )}
                                    value={
                                        data.status
                                            ? '1'
                                            : '0'
                                    }
                                    onChange={(event) =>
                                        setData(
                                            'status',
                                            event.target.value === '1'
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


                            <Field
                                label="Password"
                                required
                                error={errors.password}
                            >

                                <input
                                    type="password"
                                    className={fieldClass(
                                        errors.password
                                    )}
                                    value={data.password}
                                    onChange={(event) =>
                                        setData(
                                            'password',
                                            event.target.value
                                        )
                                    }
                                    autoComplete="new-password"
                                />

                            </Field>


                            <Field
                                label="Confirm Password"
                                required
                            >

                                <input
                                    type="password"
                                    className="rr-form-control"
                                    value={
                                        data.password_confirmation
                                    }
                                    onChange={(event) =>
                                        setData(
                                            'password_confirmation',
                                            event.target.value
                                        )
                                    }
                                    autoComplete="new-password"
                                />

                            </Field>

                        </div>

                    </div>

                </div>


                {/* =====================================================
                    ACCESS
                   ===================================================== */}

                <div className="rr-admin-panel">

                    <div className="rr-admin-panel-header">

                        <div className="rr-admin-panel-heading">

                            <h2 className="rr-admin-panel-title">
                                Company & Access
                            </h2>

                            <div className="rr-admin-panel-description">
                                Assign company, branches and role
                            </div>

                        </div>

                    </div>


                    <div className="rr-admin-panel-body">

                        <div className="row g-4">

                            {/* Company */}

                            <div className="col-lg-6">

                                <label className="rr-form-label">
                                    Company
                                    <span className="text-danger ms-1">
                                        *
                                    </span>
                                </label>

                                <SearchableSelect
                                    value={
                                        data.company_id
                                            ? String(data.company_id)
                                            : ''
                                    }
                                    onChange={(value) =>
                                        setData(
                                            'company_id',
                                            value === 'all'
                                                ? ''
                                                : value
                                        )
                                    }
                                    placeholder="Select Company"
                                    allLabel="Select Company"
                                    searchPlaceholder="Search companies..."
                                    options={
                                        companies.map(
                                            (company) => ({
                                                value:
                                                    company.id,

                                                label:
                                                    company.company_code
                                                        ? `${company.company_name} (${company.company_code})`
                                                        : company.company_name,
                                            })
                                        )
                                    }
                                />

                                {errors.company_id && (
                                    <FormError
                                        error={
                                            errors.company_id
                                        }
                                    />
                                )}

                            </div>


                            {/* Role */}

                            <Field
                                label="Role"
                                required
                                error={errors.role}
                            >

                                <select
                                    className={fieldClass(
                                        errors.role
                                    )}
                                    value={data.role}
                                    onChange={(event) =>
                                        setData(
                                            'role',
                                            event.target.value
                                        )
                                    }
                                >

                                    <option value="">
                                        Select Role
                                    </option>

                                    {roles.map((role) => (

                                        <option
                                            key={role.id}
                                            value={role.name}
                                        >
                                            {formatRole(
                                                role.name
                                            )}
                                        </option>

                                    ))}

                                </select>

                            </Field>


                            {/* Branches */}

                            <div className="col-12">

                                <div className="rr-user-access-header">

                                    <div>

                                        <label className="rr-form-label mb-1">
                                            Branch Access
                                        </label>

                                        <div className="rr-user-access-help">
                                            Assign one or more branches this user can access.
                                        </div>

                                    </div>

                                    {branches.length > 0 && (

                                        <div className="d-flex gap-2">

                                            <button
                                                type="button"
                                                className="rr-btn rr-btn-secondary"
                                                onClick={() =>
                                                    setData(
                                                        'store_ids',
                                                        branches.map(
                                                            (branch) =>
                                                                branch.id
                                                        )
                                                    )
                                                }
                                            >
                                                Select All
                                            </button>

                                            <button
                                                type="button"
                                                className="rr-btn rr-btn-secondary"
                                                onClick={() =>
                                                    setData(
                                                        'store_ids',
                                                        []
                                                    )
                                                }
                                            >
                                                Clear
                                            </button>

                                        </div>

                                    )}

                                </div>


                                {!data.company_id ? (

                                    <div className="rr-user-branch-empty">
                                        <i className="bi bi-building"></i>

                                        <span>
                                            Select a company first to view its branches.
                                        </span>
                                    </div>

                                ) : loadingBranches ? (

                                    <div className="rr-user-branch-empty">

                                        <span className="spinner-border spinner-border-sm"></span>

                                        <span>
                                            Loading branches...
                                        </span>

                                    </div>

                                ) : branches.length === 0 ? (

                                    <div className="rr-user-branch-empty">

                                        <i className="bi bi-shop"></i>

                                        <span>
                                            No active branches are available for this company.
                                        </span>

                                    </div>

                                ) : (

                                    <div className="rr-user-branch-selector">

                                        {branches.map(
                                            (branch) => {

                                                const checked =
                                                    data.store_ids
                                                        .map(Number)
                                                        .includes(
                                                            Number(
                                                                branch.id
                                                            )
                                                        );

                                                return (

                                                    <label
                                                        key={
                                                            branch.id
                                                        }
                                                        className={`rr-user-branch-option ${checked
                                                                ? 'selected'
                                                                : ''
                                                            }`}
                                                    >

                                                        <input
                                                            type="checkbox"
                                                            checked={
                                                                checked
                                                            }
                                                            onChange={() =>
                                                                toggleBranch(
                                                                    branch.id
                                                                )
                                                            }
                                                        />

                                                        <div className="rr-user-branch-option-icon">
                                                            <i className="bi bi-shop"></i>
                                                        </div>

                                                        <div className="rr-user-branch-option-info">

                                                            <div className="rr-user-branch-option-name">
                                                                {branch.store_name}
                                                            </div>

                                                            <div className="rr-user-branch-option-code">
                                                                {branch.store_code}
                                                            </div>

                                                        </div>

                                                        {checked && (
                                                            <i className="bi bi-check-circle-fill rr-user-branch-selected-icon"></i>
                                                        )}

                                                    </label>

                                                );

                                            }
                                        )}

                                    </div>

                                )}


                                {errors.store_ids && (
                                    <FormError
                                        error={
                                            errors.store_ids
                                        }
                                    />
                                )}

                            </div>

                        </div>

                    </div>


                    {/* Footer */}

                    <div className="rr-company-form-footer">

                        <Link
                            href="/super-admin/users"
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
                                    <i className="bi bi-person-plus"></i>
                                    Create User
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
}) {

    return (
        <div className="col-lg-6">

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
                <FormError error={error} />
            )}

        </div>
    );
}


function FormError({ error }) {

    return (
        <div className="rr-form-error">
            <i className="bi bi-exclamation-circle"></i>

            <span>
                {error}
            </span>
        </div>
    );
}


function fieldClass(error) {

    return `rr-form-control ${error
            ? 'rr-form-control-error'
            : ''
        }`;
}


function formatRole(role) {

    return String(role ?? '')
        .replaceAll('-', ' ')
        .replaceAll('_', ' ')
        .replace(
            /\b\w/g,
            (character) =>
                character.toUpperCase()
        );
}