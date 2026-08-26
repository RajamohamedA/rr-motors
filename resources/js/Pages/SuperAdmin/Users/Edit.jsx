import {
    Head,
    Link,
    useForm,
} from '@inertiajs/react';

import {
    useEffect,
    useRef,
    useState,
} from 'react';

import axios from 'axios';

import SuperAdminLayout
    from '../../../Layouts/SuperAdminLayout';

import SearchableSelect
    from '../../../Components/SearchableSelect';


export default function Edit({
    userData,
    companies = [],
    stores = [],
    roles = [],
}) {

    /*
    |--------------------------------------------------------------------------
    | Local State
    |--------------------------------------------------------------------------
    */

    const [branches, setBranches] =
        useState(stores ?? []);

    const [loadingBranches, setLoadingBranches] =
        useState(false);

    /*
    |--------------------------------------------------------------------------
    | First Render Flag
    |--------------------------------------------------------------------------
    |
    | Laravel already provides the existing company's branches.
    | We do not want to reload them immediately and clear the user's
    | currently assigned stores.
    |
    */

    const firstRender = useRef(true);


    /*
    |--------------------------------------------------------------------------
    | Form
    |--------------------------------------------------------------------------
    */

    const {
        data,
        setData,
        put,
        processing,
        errors,
    } = useForm({

        name:
            userData?.name ?? '',

        email:
            userData?.email ?? '',

        mobile:
            userData?.mobile ?? '',

        company_id:
            userData?.company_id
                ? String(userData.company_id)
                : '',

        store_ids:
            (userData?.store_ids ?? [])
                .map(Number),

        role:
            userData?.role ?? '',

        password: '',

        password_confirmation: '',

        status:
            Boolean(userData?.status),

    });


    /*
    |--------------------------------------------------------------------------
    | Load Branches When Company Changes
    |--------------------------------------------------------------------------
    */

    useEffect(() => {

        /*
        |--------------------------------------------------------------------------
        | First Page Load
        |--------------------------------------------------------------------------
        |
        | Keep the branches passed from Laravel and preserve the
        | existing branch assignments.
        |
        */

        if (firstRender.current) {

            firstRender.current = false;

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Company Cleared
        |--------------------------------------------------------------------------
        */

        if (!data.company_id) {

            setBranches([]);

            setData(
                'store_ids',
                []
            );

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | Fetch Selected Company Branches
        |--------------------------------------------------------------------------
        */

        let cancelled = false;


        const loadBranches = async () => {

            setLoadingBranches(true);


            /*
            |--------------------------------------------------------------------------
            | Remove Old Company Assignments
            |--------------------------------------------------------------------------
            */

            setBranches([]);

            setData(
                'store_ids',
                []
            );


            try {

                const response =
                    await axios.get(
                        `/super-admin/companies/${data.company_id}/branches/options`
                    );


                if (cancelled) {
                    return;
                }


                setBranches(
                    response.data?.branches ?? []
                );

            } catch (error) {

                if (cancelled) {
                    return;
                }

                console.error(
                    'Unable to load branches:',
                    error
                );

                setBranches([]);

            } finally {

                if (!cancelled) {

                    setLoadingBranches(false);

                }

            }

        };


        loadBranches();


        /*
        |--------------------------------------------------------------------------
        | Cleanup
        |--------------------------------------------------------------------------
        |
        | Prevent an older AJAX request from overwriting a newer company
        | selection if the user changes companies quickly.
        |
        */

        return () => {

            cancelled = true;

        };

    }, [data.company_id]);


    /*
    |--------------------------------------------------------------------------
    | Toggle Branch
    |--------------------------------------------------------------------------
    */

    const toggleBranch = (branchId) => {

        const id =
            Number(branchId);

        const selected =
            (data.store_ids ?? [])
                .map(Number);


        if (selected.includes(id)) {

            setData(
                'store_ids',
                selected.filter(
                    (storeId) =>
                        storeId !== id
                )
            );

            return;
        }


        setData(
            'store_ids',
            [
                ...selected,
                id,
            ]
        );

    };


    /*
    |--------------------------------------------------------------------------
    | Select All Branches
    |--------------------------------------------------------------------------
    */

    const selectAllBranches = () => {

        setData(
            'store_ids',
            branches.map(
                (branch) =>
                    Number(branch.id)
            )
        );

    };


    /*
    |--------------------------------------------------------------------------
    | Clear Branches
    |--------------------------------------------------------------------------
    */

    const clearBranches = () => {

        setData(
            'store_ids',
            []
        );

    };


    /*
    |--------------------------------------------------------------------------
    | Submit
    |--------------------------------------------------------------------------
    */

    const submit = (event) => {

        event.preventDefault();


        put(
            `/super-admin/users/${userData.id}`,
            {
                preserveScroll: true,

                onError: (errors) => {

                    console.log(
                        'USER UPDATE VALIDATION ERRORS:',
                        errors
                    );

                },
            }
        );

    };


    return (

        <SuperAdminLayout
            title="Edit User"
            subtitle={userData?.name ?? 'User'}
            breadcrumbs={[
                {
                    label: 'Users',
                    href: '/super-admin/users',
                },
                {
                    label:
                        userData?.name ?? 'User',
                },
                {
                    label: 'Edit',
                },
            ]}
        >

            <Head
                title={
                    `Edit ${userData?.name ?? 'User'}`
                }
            />


            <form onSubmit={submit}>

                {/* =====================================================
                    USER ACCOUNT
                   ===================================================== */}

                <div className="rr-admin-panel mb-4">

                    <div className="rr-admin-panel-header">

                        <div className="rr-admin-panel-heading">

                            <h2 className="rr-admin-panel-title">
                                User Account
                            </h2>

                            <div className="rr-admin-panel-description">
                                Update login and contact information
                            </div>

                        </div>

                    </div>


                    <div className="rr-admin-panel-body">

                        <div className="row g-3">


                            {/* Full Name */}

                            <Field
                                label="Full Name"
                                required
                                error={errors.name}
                            >

                                <input
                                    type="text"
                                    className={
                                        fieldClass(
                                            errors.name
                                        )
                                    }
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


                            {/* Mobile */}

                            <Field
                                label="Mobile"
                                error={errors.mobile}
                            >

                                <input
                                    type="text"
                                    className={
                                        fieldClass(
                                            errors.mobile
                                        )
                                    }
                                    value={data.mobile}
                                    onChange={(event) =>
                                        setData(
                                            'mobile',
                                            event.target.value
                                        )
                                    }
                                />

                            </Field>


                            {/* Email */}

                            <Field
                                label="Email"
                                required
                                error={errors.email}
                            >

                                <input
                                    type="email"
                                    className={
                                        fieldClass(
                                            errors.email
                                        )
                                    }
                                    value={data.email}
                                    onChange={(event) =>
                                        setData(
                                            'email',
                                            event.target.value
                                        )
                                    }
                                />

                            </Field>


                            {/* Status */}

                            <Field
                                label="Status"
                                error={errors.status}
                            >

                                <select
                                    className={
                                        fieldClass(
                                            errors.status
                                        )
                                    }
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


                            {/* New Password */}

                            <Field
                                label="New Password"
                                error={errors.password}
                            >

                                <input
                                    type="password"
                                    className={
                                        fieldClass(
                                            errors.password
                                        )
                                    }
                                    value={data.password}
                                    onChange={(event) =>
                                        setData(
                                            'password',
                                            event.target.value
                                        )
                                    }
                                    autoComplete="new-password"
                                />

                                <div className="rr-form-help">
                                    Leave blank to keep the current password.
                                </div>

                            </Field>


                            {/* Confirm Password */}

                            <Field
                                label="Confirm New Password"
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
                    COMPANY & ACCESS
                   ===================================================== */}

                <div className="rr-admin-panel">

                    <div className="rr-admin-panel-header">

                        <div className="rr-admin-panel-heading">

                            <h2 className="rr-admin-panel-title">
                                Company & Access
                            </h2>

                            <div className="rr-admin-panel-description">
                                Manage company, branch access and role
                            </div>

                        </div>

                    </div>


                    <div className="rr-admin-panel-body">

                        <div className="row g-4">


                            {/* =================================================
                                COMPANY
                               ================================================= */}

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
                                    }
                                    onChange={(value) => {

                                        setData(
                                            'company_id',
                                            value === 'all'
                                                ? ''
                                                : String(value)
                                        );

                                    }}
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


                            {/* =================================================
                                ROLE
                               ================================================= */}

                            <Field
                                label="Role"
                                required
                                error={errors.role}
                            >

                                <select
                                    className={
                                        fieldClass(
                                            errors.role
                                        )
                                    }
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


                            {/* =================================================
                                BRANCH ACCESS
                               ================================================= */}

                            <div className="col-12">

                                <div className="rr-user-access-header">

                                    <div>

                                        <label className="rr-form-label mb-1">
                                            Branch Access
                                        </label>

                                        <div className="rr-user-access-help">
                                            Select one or more branches this user can access.
                                        </div>

                                    </div>


                                    {branches.length > 0 &&
                                        !loadingBranches && (

                                            <div className="d-flex gap-2">

                                                <button
                                                    type="button"
                                                    className="rr-btn rr-btn-secondary"
                                                    onClick={
                                                        selectAllBranches
                                                    }
                                                >
                                                    <i className="bi bi-check2-square"></i>

                                                    Select All
                                                </button>


                                                <button
                                                    type="button"
                                                    className="rr-btn rr-btn-secondary"
                                                    onClick={
                                                        clearBranches
                                                    }
                                                >
                                                    <i className="bi bi-x-lg"></i>

                                                    Clear
                                                </button>

                                            </div>

                                        )}

                                </div>


                                {/* No Company */}

                                {!data.company_id ? (

                                    <div className="rr-user-branch-empty">

                                        <i className="bi bi-building"></i>

                                        <span>
                                            Select a company first to view its branches.
                                        </span>

                                    </div>

                                ) : loadingBranches ? (

                                    /* Loading */

                                    <div className="rr-user-branch-empty">

                                        <span
                                            className="
                                                spinner-border
                                                spinner-border-sm
                                            "
                                        ></span>

                                        <span>
                                            Loading branches...
                                        </span>

                                    </div>

                                ) : branches.length === 0 ? (

                                    /* No branches */

                                    <div className="rr-user-branch-empty">

                                        <i className="bi bi-shop"></i>

                                        <span>
                                            No active branches are available for this company.
                                        </span>

                                    </div>

                                ) : (

                                    /* Branches */

                                    <div className="rr-user-branch-selector">

                                        {branches.map(
                                            (branch) => {

                                                const checked =
                                                    (
                                                        data.store_ids ??
                                                        []
                                                    )
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
                                                        className={
                                                            `rr-user-branch-option ${checked
                                                                ? 'selected'
                                                                : ''
                                                            }`
                                                        }
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

                                                            <i
                                                                className="
                                                                    bi
                                                                    bi-check-circle-fill
                                                                    rr-user-branch-selected-icon
                                                                "
                                                            ></i>

                                                        )}

                                                    </label>

                                                );

                                            }
                                        )}

                                    </div>

                                )}


                                {/* Branch validation */}

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


                    {/* =================================================
                        FOOTER
                       ================================================= */}

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
                                    <span
                                        className="
                                            spinner-border
                                            spinner-border-sm
                                        "
                                    ></span>

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


/*
|--------------------------------------------------------------------------
| Field
|--------------------------------------------------------------------------
*/

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

                <FormError
                    error={error}
                />

            )}

        </div>

    );
}


/*
|--------------------------------------------------------------------------
| Form Error
|--------------------------------------------------------------------------
*/

function FormError({
    error,
}) {

    return (

        <div className="rr-form-error">

            <i className="bi bi-exclamation-circle"></i>

            <span>
                {error}
            </span>

        </div>

    );
}


/*
|--------------------------------------------------------------------------
| Field Class
|--------------------------------------------------------------------------
*/

function fieldClass(error) {

    return `rr-form-control ${error
            ? 'rr-form-control-error'
            : ''
        }`;

}


/*
|--------------------------------------------------------------------------
| Format Role
|--------------------------------------------------------------------------
*/

function formatRole(role) {

    return String(
        role ?? ''
    )
        .replaceAll(
            '-',
            ' '
        )
        .replaceAll(
            '_',
            ' '
        )
        .replace(
            /\b\w/g,
            (character) =>
                character.toUpperCase()
        );

}