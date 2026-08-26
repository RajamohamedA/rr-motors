import {
    Head,
    Link,
    useForm,
} from '@inertiajs/react';

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
        module: '',
        action: '',
    });


    /*
    |--------------------------------------------------------------------------
    | Preview
    |--------------------------------------------------------------------------
    */

    const preview = buildPermissionName(
        data.module,
        data.action
    );


    /*
    |--------------------------------------------------------------------------
    | Submit
    |--------------------------------------------------------------------------
    */

    const submit = (event) => {

        event.preventDefault();

        post(
            '/super-admin/permissions',
            {
                preserveScroll: true,
            }
        );
    };


    return (

        <SuperAdminLayout
            title="New Permission"
            subtitle="Create a system permission definition"
            breadcrumbs={[
                {
                    label: 'Permissions',
                    href: '/super-admin/permissions',
                },
                {
                    label: 'New Permission',
                },
            ]}
        >

            <Head title="New Permission" />


            <form onSubmit={submit}>

                <div className="rr-admin-panel">

                    {/* Header */}

                    <div className="rr-admin-panel-header">

                        <div className="rr-admin-panel-heading">

                            <h2 className="rr-admin-panel-title">
                                Permission Information
                            </h2>

                            <div className="rr-admin-panel-description">
                                Define the module and action used by the application
                            </div>

                        </div>

                    </div>


                    {/* Body */}

                    <div className="rr-admin-panel-body">

                        <div className="row g-4">

                            {/* Module */}

                            <div className="col-lg-6">

                                <label className="rr-form-label">

                                    Module

                                    <span className="text-danger ms-1">
                                        *
                                    </span>

                                </label>


                                <input
                                    type="text"
                                    className={
                                        fieldClass(
                                            errors.module
                                        )
                                    }
                                    value={data.module}
                                    onChange={(event) =>
                                        setData(
                                            'module',
                                            event.target.value
                                        )
                                    }
                                    placeholder="Example: sales"
                                    autoFocus
                                />


                                <div className="rr-form-help">
                                    Use the application module name, for example Sales, Purchase or Stock Ledger.
                                </div>


                                {errors.module && (
                                    <FormError
                                        error={
                                            errors.module
                                        }
                                    />
                                )}

                            </div>


                            {/* Action */}

                            <div className="col-lg-6">

                                <label className="rr-form-label">

                                    Action

                                    <span className="text-danger ms-1">
                                        *
                                    </span>

                                </label>


                                <input
                                    type="text"
                                    className={
                                        fieldClass(
                                            errors.action
                                        )
                                    }
                                    value={data.action}
                                    onChange={(event) =>
                                        setData(
                                            'action',
                                            event.target.value
                                        )
                                    }
                                    placeholder="Example: create"
                                />


                                <div className="rr-form-help">
                                    Common actions include view, create, edit, delete, print and export.
                                </div>


                                {errors.action && (
                                    <FormError
                                        error={
                                            errors.action
                                        }
                                    />
                                )}

                            </div>


                            {/* Preview */}

                            <div className="col-12">

                                <div className="rr-permission-preview">

                                    <div className="rr-permission-preview-icon">

                                        <i className="bi bi-shield-check"></i>

                                    </div>


                                    <div className="rr-permission-preview-content">

                                        <div className="rr-permission-preview-label">
                                            Permission Name
                                        </div>


                                        <div className="rr-permission-preview-value">

                                            {preview || 'module.action'}

                                        </div>


                                        <div className="rr-permission-preview-help">
                                            This value is generated automatically and stored as the permission identifier.
                                        </div>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </div>


                    {/* Footer */}

                    <div className="rr-company-form-footer">

                        <Link
                            href="/super-admin/permissions"
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
                                    <i className="bi bi-plus-lg"></i>

                                    Create Permission
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
| Build Permission Name
|--------------------------------------------------------------------------
*/

function buildPermissionName(
    module,
    action
) {

    const normalizedModule =
        normalizeValue(
            module
        );

    const normalizedAction =
        normalizeValue(
            action
        );


    if (
        !normalizedModule ||
        !normalizedAction
    ) {
        return '';
    }


    return `${normalizedModule}.${normalizedAction}`;
}


/*
|--------------------------------------------------------------------------
| Normalize
|--------------------------------------------------------------------------
*/

function normalizeValue(value) {

    return String(
        value ?? ''
    )
        .trim()
        .toLowerCase()
        .replace(
            /[^a-z0-9]+/g,
            '-'
        )
        .replace(
            /^-+|-+$/g,
            ''
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