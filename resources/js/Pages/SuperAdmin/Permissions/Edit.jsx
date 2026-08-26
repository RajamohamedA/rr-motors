import {
    Head,
    Link,
    useForm,
} from '@inertiajs/react';

import SuperAdminLayout
    from '../../../Layouts/SuperAdminLayout';


export default function Edit({
    permissionData,
}) {

    const canEdit =
        Boolean(
            permissionData?.can_edit
        );


    const {
        data,
        setData,
        put,
        processing,
        errors,
    } = useForm({

        module:
            permissionData?.module ?? '',

        action:
            permissionData?.action ?? '',

    });


    /*
    |--------------------------------------------------------------------------
    | Preview
    |--------------------------------------------------------------------------
    */

    const preview =
        buildPermissionName(
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

        if (!canEdit) {
            return;
        }

        put(
            `/super-admin/permissions/${permissionData.id}`,
            {
                preserveScroll: true,
            }
        );

    };


    return (

        <SuperAdminLayout
            title="Edit Permission"
            subtitle="Review or update a system permission"
            breadcrumbs={[
                {
                    label: 'Permissions',
                    href: '/super-admin/permissions',
                },
                {
                    label:
                        permissionData?.name,
                },
            ]}
        >

            <Head title="Edit Permission" />


            <form onSubmit={submit}>

                <div className="rr-admin-panel">

                    {/* Header */}

                    <div className="rr-admin-panel-header">

                        <div className="rr-admin-panel-heading">

                            <h2 className="rr-admin-panel-title">
                                Permission Information
                            </h2>

                            <div className="rr-admin-panel-description">
                                Review the permission identifier and usage
                            </div>

                        </div>


                        <span
                            className={
                                canEdit
                                    ? 'rr-permission-edit-status rr-permission-edit-status-editable'
                                    : 'rr-permission-edit-status rr-permission-edit-status-locked'
                            }
                        >

                            <i
                                className={
                                    canEdit
                                        ? 'bi bi-pencil'
                                        : 'bi bi-lock'
                                }
                            ></i>

                            {canEdit
                                ? 'Editable'
                                : 'Protected'}

                        </span>

                    </div>


                    <div className="rr-admin-panel-body">

                        {/* =============================================
                            PROTECTION WARNING
                           ============================================= */}

                        {!canEdit && (

                            <div className="rr-permission-protected-warning">

                                <div className="rr-permission-protected-icon">

                                    <i className="bi bi-lock"></i>

                                </div>


                                <div>

                                    <div className="rr-permission-protected-title">
                                        Permission is currently in use
                                    </div>

                                    <div className="rr-permission-protected-text">

                                        <strong>
                                            {permissionData.name}
                                        </strong>

                                        {' '}
                                        is assigned to
                                        {' '}

                                        <strong>
                                            {permissionData.roles_count}
                                        </strong>

                                        {' '}

                                        {
                                            Number(
                                                permissionData.roles_count
                                            ) === 1
                                                ? 'role'
                                                : 'roles'
                                        }.

                                        {' '}
                                        The permission identifier cannot be renamed while it is assigned.

                                    </div>


                                    <div className="rr-permission-protected-help">
                                        If you need a different permission identifier, create a new permission, assign it to the required roles, update application references, and then remove the old permission.
                                    </div>

                                </div>

                            </div>

                        )}


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
                                    disabled={!canEdit}
                                    onChange={(event) =>
                                        setData(
                                            'module',
                                            event.target.value
                                        )
                                    }
                                    placeholder="Example: sales"
                                />


                                <div className="rr-form-help">
                                    Application module such as Sales, Purchase or Stock Ledger.
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
                                    disabled={!canEdit}
                                    onChange={(event) =>
                                        setData(
                                            'action',
                                            event.target.value
                                        )
                                    }
                                    placeholder="Example: create"
                                />


                                <div className="rr-form-help">
                                    Action such as view, create, edit, delete, print or export.
                                </div>


                                {errors.action && (

                                    <FormError
                                        error={
                                            errors.action
                                        }
                                    />

                                )}

                            </div>


                            {/* Current Identifier */}

                            <div className="col-lg-6">

                                <div className="rr-permission-meta-card">

                                    <div className="rr-permission-meta-label">
                                        Current Identifier
                                    </div>

                                    <div className="rr-permission-meta-value">
                                        {permissionData.name}
                                    </div>

                                </div>

                            </div>


                            {/* Role Usage */}

                            <div className="col-lg-6">

                                <div className="rr-permission-meta-card">

                                    <div className="rr-permission-meta-label">
                                        Assigned Roles
                                    </div>

                                    <div className="rr-permission-meta-value">

                                        {permissionData.roles_count}

                                    </div>

                                </div>

                            </div>


                            {/* Preview */}

                            <div className="col-12">

                                <div className="rr-permission-preview">

                                    <div className="rr-permission-preview-icon">

                                        <i className="bi bi-shield-check"></i>

                                    </div>


                                    <div className="rr-permission-preview-content">

                                        <div className="rr-permission-preview-label">
                                            Permission Identifier
                                        </div>


                                        <div className="rr-permission-preview-value">

                                            {preview || 'module.action'}

                                        </div>


                                        <div className="rr-permission-preview-help">

                                            {canEdit
                                                ? 'This will become the permission identifier after saving.'
                                                : 'This identifier is protected because the permission is currently assigned to roles.'}

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
                            <i className="bi bi-arrow-left"></i>

                            Back
                        </Link>


                        {canEdit && (

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

                        )}

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