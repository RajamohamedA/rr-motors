import {
    Head,
    Link,
    useForm,
} from '@inertiajs/react';

import SuperAdminLayout
    from '../../../Layouts/SuperAdminLayout';


export default function Edit({
    roleData,
    permissions = [],
}) {

    const {
        data,
        setData,
        put,
        processing,
        errors,
    } = useForm({

        name:
            roleData?.name ?? '',

        permissions:
            roleData?.permissions ?? [],
    });


    const togglePermission = (permissionName) => {

        const selected =
            data.permissions ?? [];

        if (selected.includes(permissionName)) {

            setData(
                'permissions',
                selected.filter(
                    (name) =>
                        name !== permissionName
                )
            );

            return;
        }


        setData(
            'permissions',
            [
                ...selected,
                permissionName,
            ]
        );
    };


    const getModulePermissionNames = (group) => {

        return (
            group.permissions ?? []
        ).map(
            (permission) =>
                permission.name
        );
    };


    const isModuleSelected = (group) => {

        const permissionNames =
            getModulePermissionNames(group);

        if (permissionNames.length === 0) {
            return false;
        }

        return permissionNames.every(
            (name) =>
                data.permissions.includes(name)
        );
    };


    const toggleModule = (group) => {

        const permissionNames =
            getModulePermissionNames(group);

        const allSelected =
            isModuleSelected(group);


        if (allSelected) {

            setData(
                'permissions',
                data.permissions.filter(
                    (name) =>
                        !permissionNames.includes(name)
                )
            );

            return;
        }


        setData(
            'permissions',
            Array.from(
                new Set([
                    ...data.permissions,
                    ...permissionNames,
                ])
            )
        );
    };


    const selectAll = () => {

        const allPermissions =
            permissions.flatMap(
                (group) =>
                    (
                        group.permissions ?? []
                    ).map(
                        (permission) =>
                            permission.name
                    )
            );

        setData(
            'permissions',
            allPermissions
        );
    };


    const clearAll = () => {

        setData(
            'permissions',
            []
        );
    };


    const submit = (event) => {

        event.preventDefault();

        put(
            `/super-admin/roles/${roleData.id}`,
            {
                preserveScroll: true,
            }
        );
    };


    return (

        <SuperAdminLayout
            title="Edit Role"
            subtitle={formatText(roleData.name)}
            breadcrumbs={[
                {
                    label: 'Roles',
                    href: '/super-admin/roles',
                },
                {
                    label: formatText(roleData.name),
                    href: `/super-admin/roles/${roleData.id}`,
                },
                {
                    label: 'Edit',
                },
            ]}
        >

            <Head
                title={`Edit ${formatText(roleData.name)}`}
            />


            <form onSubmit={submit}>

                {/* Role Information */}

                <div className="rr-admin-panel mb-4">

                    <div className="rr-admin-panel-header">

                        <div className="rr-admin-panel-heading">

                            <h2 className="rr-admin-panel-title">
                                Role Information
                            </h2>

                            <div className="rr-admin-panel-description">
                                Update the role name and permissions
                            </div>

                        </div>

                    </div>


                    <div className="rr-admin-panel-body">

                        <div className="row g-3">

                            <div className="col-lg-6">

                                <label className="rr-form-label">

                                    Role Name

                                    <span className="text-danger ms-1">
                                        *
                                    </span>

                                </label>


                                <input
                                    type="text"
                                    className={`rr-form-control ${errors.name
                                            ? 'rr-form-control-error'
                                            : ''
                                        }`}
                                    value={data.name}
                                    onChange={(event) =>
                                        setData(
                                            'name',
                                            event.target.value
                                        )
                                    }
                                    autoFocus
                                />


                                {errors.name && (
                                    <FormError
                                        error={errors.name}
                                    />
                                )}

                            </div>

                        </div>

                    </div>

                </div>


                {/* Permissions */}

                <div className="rr-admin-panel">

                    <div className="rr-admin-panel-header">

                        <div className="rr-admin-panel-heading">

                            <h2 className="rr-admin-panel-title">
                                Permissions
                            </h2>

                            <div className="rr-admin-panel-description">
                                Update the actions this role can perform
                            </div>

                        </div>


                        <div className="d-flex align-items-center gap-2">

                            <span className="rr-role-selected-count">
                                {data.permissions.length} selected
                            </span>


                            <button
                                type="button"
                                className="rr-btn rr-btn-secondary"
                                onClick={selectAll}
                            >
                                <i className="bi bi-check2-all"></i>
                                Select All
                            </button>


                            <button
                                type="button"
                                className="rr-btn rr-btn-secondary"
                                onClick={clearAll}
                            >
                                Clear
                            </button>

                        </div>

                    </div>


                    <div className="rr-admin-panel-body">

                        {errors.permissions && (
                            <FormError
                                error={errors.permissions}
                            />
                        )}


                        {permissions.length > 0 ? (

                            <div className="rr-permission-modules">

                                {permissions.map((group) => (

                                    <PermissionModule
                                        key={group.module}
                                        group={group}
                                        selectedPermissions={
                                            data.permissions
                                        }
                                        onTogglePermission={
                                            togglePermission
                                        }
                                        onToggleModule={
                                            toggleModule
                                        }
                                        allSelected={
                                            isModuleSelected(group)
                                        }
                                    />

                                ))}

                            </div>

                        ) : (

                            <div className="rr-empty-state">

                                <div className="rr-empty-state-icon">
                                    <i className="bi bi-shield-lock"></i>
                                </div>

                                <div className="rr-empty-state-title">
                                    No permissions available
                                </div>

                            </div>

                        )}

                    </div>


                    {/* Footer */}

                    <div className="rr-company-form-footer">

                        <Link
                            href={`/super-admin/roles/${roleData.id}`}
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


function PermissionModule({
    group,
    selectedPermissions,
    onTogglePermission,
    onToggleModule,
    allSelected,
}) {

    return (

        <div className="rr-permission-module">

            <div className="rr-permission-module-header">

                <div className="rr-permission-module-title">

                    <div className="rr-permission-module-icon">
                        <i className="bi bi-grid"></i>
                    </div>


                    <div>

                        <div className="rr-permission-module-name">
                            {formatText(group.module)}
                        </div>

                        <div className="rr-permission-module-count">
                            {group.permissions?.length ?? 0} permissions
                        </div>

                    </div>

                </div>


                <label className="rr-permission-module-toggle">

                    <input
                        type="checkbox"
                        checked={allSelected}
                        onChange={() =>
                            onToggleModule(group)
                        }
                    />

                    <span>
                        Select module
                    </span>

                </label>

            </div>


            <div className="rr-permission-grid">

                {(group.permissions ?? [])
                    .map((permission) => {

                        const selected =
                            selectedPermissions.includes(
                                permission.name
                            );


                        return (

                            <label
                                key={permission.id}
                                className={`rr-permission-option ${selected
                                        ? 'selected'
                                        : ''
                                    }`}
                            >

                                <input
                                    type="checkbox"
                                    checked={selected}
                                    onChange={() =>
                                        onTogglePermission(
                                            permission.name
                                        )
                                    }
                                />


                                <div className="rr-permission-option-check">

                                    {selected ? (
                                        <i className="bi bi-check-lg"></i>
                                    ) : (
                                        <i className="bi bi-dash"></i>
                                    )}

                                </div>


                                <div>

                                    <div className="rr-permission-option-action">
                                        {formatText(
                                            permission.action
                                        )}
                                    </div>

                                    <div className="rr-permission-option-name">
                                        {permission.name}
                                    </div>

                                </div>

                            </label>

                        );

                    })}

            </div>

        </div>

    );
}


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


function formatText(value) {

    return String(value ?? '')
        .replaceAll('-', ' ')
        .replaceAll('_', ' ')
        .replaceAll('.', ' ')
        .replace(
            /\b\w/g,
            (character) =>
                character.toUpperCase()
        );
}