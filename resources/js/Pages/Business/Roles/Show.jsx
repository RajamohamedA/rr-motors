import {
    Head,
    Link,
} from '@inertiajs/react';

import BusinessLayout
    from '../../../Layouts/BusinessLayout';

import PageShell
    from '../../../Components/Business/PageShell';


export default function RoleShow({
    company,
    managedRole,
}) {

    /*
    |--------------------------------------------------------------------------
    | Permission Groups
    |--------------------------------------------------------------------------
    */

    const groupedPermissions =
        groupPermissions(
            managedRole?.permissions
            ?? []
        );


    return (

        <BusinessLayout>

            <Head
                title={
                    managedRole?.name
                    ?? 'Role'
                }
            />


            <PageShell
                eyebrow="Company Management"
                title="Role Details"
                description="View role information, assigned users and permissions."
                icon="bi-shield-check"
                breadcrumb={[
                    {
                        label:
                            'Company',
                        href:
                            '/company/dashboard',
                    },

                    {
                        label:
                            'Roles & Permissions',
                        href:
                            '/company/roles',
                    },

                    {
                        label:
                            managedRole?.name
                            ?? 'Role',
                    },
                ]}
                actions={
                    <>

                        <Link
                            href="/company/roles"
                            className="rr-page-secondary-btn"
                        >
                            <i className="bi bi-arrow-left"></i>

                            Back
                        </Link>


                        <Link
                            href={
                                `/company/roles/${managedRole.id}/edit`
                            }
                            className="rr-page-primary-btn"
                        >
                            <i className="bi bi-pencil-square"></i>

                            Edit Role
                        </Link>

                    </>
                }
            >

                {/* =====================================================
                    ROLE HEADER
                   ===================================================== */}

                <section className="rr-role-view-header">

                    <div className="rr-role-view-header-main">

                        <div className="rr-role-view-icon">

                            <i className="bi bi-person-badge"></i>

                        </div>


                        <div className="rr-role-view-identity">

                            <span>
                                Company Role
                            </span>

                            <strong>
                                {
                                    managedRole?.name
                                    ?? 'Role'
                                }
                            </strong>

                            <small>
                                {
                                    managedRole?.description
                                    || 'No description'
                                }
                            </small>

                        </div>

                    </div>


                    <div className="rr-role-view-header-status">

                        <span
                            className={
                                managedRole?.status
                                    ? 'rr-role-view-status active'
                                    : 'rr-role-view-status inactive'
                            }
                        >

                            <span></span>

                            {
                                managedRole?.status
                                    ? 'Active'
                                    : 'Inactive'
                            }

                        </span>

                    </div>

                </section>


                {/* =====================================================
                    SUMMARY
                   ===================================================== */}

                <section className="rr-role-view-summary">

                    <SummaryItem
                        label="Users"
                        value={
                            managedRole?.users_count
                            ?? 0
                        }
                        icon="bi-people"
                    />

                    <SummaryItem
                        label="Permissions"
                        value={
                            managedRole?.permissions_count
                            ?? 0
                        }
                        icon="bi-key"
                    />

                    <SummaryItem
                        label="Guard"
                        value={
                            managedRole?.guard_name
                            ?? 'web'
                        }
                        icon="bi-shield-lock"
                    />

                    <SummaryItem
                        label="Company"
                        value={
                            company?.company_code
                            ?? company?.company_name
                            ?? '—'
                        }
                        icon="bi-building"
                    />

                </section>


                {/* =====================================================
                    ROLE INFO
                   ===================================================== */}

                <div className="rr-role-view-grid">

                    <section className="rr-page-panel">

                        <div className="rr-page-panel-header">

                            <div className="rr-page-panel-title">

                                <h2>
                                    Role Information
                                </h2>

                                <p>
                                    Main role configuration
                                </p>

                            </div>


                            <div className="rr-role-view-panel-icon">

                                <i className="bi bi-info-circle"></i>

                            </div>

                        </div>


                        <div className="rr-role-view-detail-list">

                            <DetailRow
                                icon="bi-person-badge"
                                label="Role Name"
                                value={
                                    managedRole?.name
                                }
                            />

                            <DetailRow
                                icon="bi-card-text"
                                label="Description"
                                value={
                                    managedRole?.description
                                }
                            />

                            <DetailRow
                                icon="bi-toggle-on"
                                label="Status"
                                value={
                                    managedRole?.status
                                        ? 'Active'
                                        : 'Inactive'
                                }
                            />

                        </div>

                    </section>


                    <section className="rr-page-panel">

                        <div className="rr-page-panel-header">

                            <div className="rr-page-panel-title">

                                <h2>
                                    System Information
                                </h2>

                                <p>
                                    Role ownership and timestamps
                                </p>

                            </div>


                            <div className="rr-role-view-panel-icon">

                                <i className="bi bi-database"></i>

                            </div>

                        </div>


                        <div className="rr-role-view-detail-list">

                            <DetailRow
                                icon="bi-building"
                                label="Company"
                                value={
                                    company?.company_name
                                }
                            />

                            <DetailRow
                                icon="bi-calendar-plus"
                                label="Created"
                                value={
                                    formatDate(
                                        managedRole?.created_at
                                    )
                                }
                            />

                            <DetailRow
                                icon="bi-clock-history"
                                label="Updated"
                                value={
                                    formatDate(
                                        managedRole?.updated_at
                                    )
                                }
                            />

                        </div>

                    </section>

                </div>


                {/* =====================================================
                    PERMISSIONS
                   ===================================================== */}

                <section className="rr-page-panel rr-role-view-permission-panel">

                    <div className="rr-page-panel-header">

                        <div className="rr-page-panel-title">

                            <h2>
                                Permissions
                            </h2>

                            <p>
                                Access granted to users assigned to this role
                            </p>

                        </div>


                        <div className="rr-role-view-panel-icon">

                            <i className="bi bi-key"></i>

                        </div>

                    </div>


                    {
                        groupedPermissions.length > 0
                            ? (

                                <div className="rr-role-view-permission-groups">

                                    {
                                        groupedPermissions.map(
                                            group => (

                                                <PermissionGroup
                                                    key={
                                                        group.module
                                                    }
                                                    group={
                                                        group
                                                    }
                                                />

                                            )
                                        )
                                    }

                                </div>

                            )
                            : (

                                <div className="rr-role-view-no-permissions">

                                    <i className="bi bi-shield-x"></i>

                                    <div>

                                        <strong>
                                            No permissions assigned
                                        </strong>

                                        <span>
                                            Users with this role currently have no module permissions.
                                        </span>

                                    </div>

                                </div>

                            )
                    }

                </section>


                {/* =====================================================
                    FOOTER INFO
                   ===================================================== */}

                <section className="rr-role-view-footer">

                    <div>

                        <i className="bi bi-info-circle"></i>

                        <span>
                            Permission changes affect every user assigned to this role.
                        </span>

                    </div>


                    <Link
                        href={
                            `/company/roles/${managedRole.id}/edit`
                        }
                        className="rr-page-secondary-btn"
                    >

                        <i className="bi bi-pencil-square"></i>

                        Edit Permissions

                    </Link>

                </section>

            </PageShell>

        </BusinessLayout>
    );
}


/*
|--------------------------------------------------------------------------
| Summary Item
|--------------------------------------------------------------------------
*/

function SummaryItem({
    label,
    value,
    icon,
}) {

    return (

        <div className="rr-role-view-summary-item">

            <div>

                <i
                    className={
                        `bi ${icon}`
                    }
                ></i>

            </div>


            <section>

                <span>
                    {label}
                </span>

                <strong>
                    {
                        value
                        ?? '—'
                    }
                </strong>

            </section>

        </div>
    );
}


/*
|--------------------------------------------------------------------------
| Detail Row
|--------------------------------------------------------------------------
*/

function DetailRow({
    icon,
    label,
    value,
}) {

    return (

        <div className="rr-role-view-detail-row">

            <div>

                <i
                    className={
                        `bi ${icon}`
                    }
                ></i>

            </div>


            <section>

                <span>
                    {label}
                </span>

                <strong
                    className={
                        !value
                            ? 'empty'
                            : ''
                    }
                >
                    {
                        value
                        || 'Not provided'
                    }
                </strong>

            </section>

        </div>
    );
}


/*
|--------------------------------------------------------------------------
| Permission Group
|--------------------------------------------------------------------------
*/

function PermissionGroup({
    group,
}) {

    return (

        <div className="rr-role-view-permission-group">

            <div className="rr-role-view-permission-group-header">

                <div>

                    <i className="bi bi-grid"></i>

                </div>


                <section>

                    <strong>
                        {
                            group.label
                        }
                    </strong>

                    <span>
                        {
                            group.permissions.length
                        } permission{
                            group.permissions.length === 1
                                ? ''
                                : 's'
                        }
                    </span>

                </section>

            </div>


            <div className="rr-role-view-permission-list">

                {
                    group.permissions.map(
                        permission => (

                            <span
                                key={
                                    permission.id
                                }
                                className="rr-role-view-permission-item"
                            >

                                <i className="bi bi-check2"></i>

                                {
                                    permission.label
                                }

                            </span>

                        )
                    )
                }

            </div>

        </div>
    );
}


/*
|--------------------------------------------------------------------------
| Group Permissions
|--------------------------------------------------------------------------
*/

function groupPermissions(
    permissions
) {

    const groups =
        {};


    permissions.forEach(
        permission => {

            const parts =
                String(
                    permission?.name
                    ?? ''
                )
                    .split(
                        '.'
                    );


            const module =
                parts[0]
                || 'other';


            const action =
                parts
                    .slice(1)
                    .join(' ')
                || permission?.name;


            if (
                !groups[module]
            ) {

                groups[module] = {
                    module,

                    label:
                        formatLabel(
                            module
                        ),

                    permissions:
                        [],
                };
            }


            groups[module]
                .permissions
                .push({

                    id:
                        permission.id,

                    name:
                        permission.name,

                    label:
                        formatLabel(
                            action
                        ),
                });
        }
    );


    return Object
        .values(
            groups
        )
        .sort(
            (
                a,
                b
            ) =>
                a.label.localeCompare(
                    b.label
                )
        );
}


/*
|--------------------------------------------------------------------------
| Label Formatter
|--------------------------------------------------------------------------
*/

function formatLabel(
    value
) {

    return String(
        value
        ?? ''
    )
        .replace(
            /[-_]/g,
            ' '
        )
        .replace(
            /\b\w/g,
            character =>
                character.toUpperCase()
        );
}


/*
|--------------------------------------------------------------------------
| Date Formatter
|--------------------------------------------------------------------------
*/

function formatDate(
    value
) {

    if (
        !value
    ) {

        return '—';
    }


    const date =
        new Date(
            value
        );


    if (
        Number.isNaN(
            date.getTime()
        )
    ) {

        return '—';
    }


    return date.toLocaleDateString(
        'en-IN',
        {
            day:
                '2-digit',

            month:
                'short',

            year:
                'numeric',
        }
    );
}