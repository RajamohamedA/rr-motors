import {
    Head,
    Link,
} from '@inertiajs/react';

import SuperAdminLayout
    from '../../../Layouts/SuperAdminLayout';


export default function Index({
    summary = {},
    sections = [],
}) {

    /*
    |--------------------------------------------------------------------------
    | Safe Summary Values
    |--------------------------------------------------------------------------
    */

    const access =
        summary.access ?? {};

    const users =
        summary.users ?? {};

    const companies =
        summary.companies ?? {};

    const branches =
        summary.branches ?? {};

    const activity =
        summary.activity ?? {};

    const notifications =
        summary.notifications ?? {};


    /*
    |--------------------------------------------------------------------------
    | Main Settings
    |--------------------------------------------------------------------------
    */

    const settings = [

        {
            title:
                'Roles',

            description:
                'Manage user roles and access groups.',

            icon:
                'bi-person-badge',

            href:
                '/super-admin/roles',

            value:
                access.roles ?? 0,

            valueLabel:
                'Roles',

            available:
                true,
        },

        {
            title:
                'Permissions',

            description:
                'Manage module-level permissions and security rules.',

            icon:
                'bi-shield-check',

            href:
                '/super-admin/permissions',

            value:
                access.permissions ?? 0,

            valueLabel:
                'Permissions',

            available:
                true,
        },

        {
            title:
                'Users',

            description:
                'Manage company users, branch assignments and role access.',

            icon:
                'bi-people',

            href:
                '/super-admin/users',

            value:
                users.total ?? 0,

            valueLabel:
                'Users',

            available:
                true,
        },

        {
            title:
                'Activity Logs',

            description:
                'Review administrative actions and security history.',

            icon:
                'bi-clock-history',

            href:
                '/super-admin/activity-logs',

            value:
                activity.total ?? 0,

            valueLabel:
                'Events',

            available:
                true,
        },

        {
            title:
                'My Profile',

            description:
                'Manage your administrator profile and account security.',

            icon:
                'bi-person-circle',

            href:
                '/profile',

            value:
                null,

            valueLabel:
                null,

            available:
                true,
        },

        {
            title:
                'System Settings',

            description:
                'Configure general ERP behaviour and application defaults.',

            icon:
                'bi-sliders',

            href:
                null,

            value:
                null,

            valueLabel:
                null,

            available:
                false,
        },

        {
            title:
                'Document Numbering',

            description:
                'Configure invoice, purchase and transaction numbering rules.',

            icon:
                'bi-123',

            href:
                null,

            value:
                null,

            valueLabel:
                null,

            available:
                false,
        },

        {
            title:
                'Backup & Maintenance',

            description:
                'Manage database backups and system maintenance tools.',

            icon:
                'bi-database-check',

            href:
                null,

            value:
                null,

            valueLabel:
                null,

            available:
                false,
        },

    ];


    return (

        <SuperAdminLayout
            title="Settings"
            subtitle="System administration and access configuration"
            breadcrumbs={[
                {
                    label: 'Settings',
                },
            ]}
        >

            <Head title="Settings" />


            {/* =====================================================
                INTRO
               ===================================================== */}

            <section className="rr-settings-hero">

                <div className="rr-settings-hero-left">

                    <div className="rr-settings-hero-icon">

                        <i className="bi bi-gear-wide-connected"></i>

                    </div>


                    <div>

                        <div className="rr-settings-hero-title">
                            Administration Console
                        </div>

                        <div className="rr-settings-hero-text">
                            Manage security, access control and system-level ERP configuration.
                        </div>

                    </div>

                </div>


                <div className="rr-settings-hero-status">

                    <div className="rr-settings-status-item">

                        <span className="rr-settings-status-label">
                            Unread Alerts
                        </span>

                        <span
                            className={
                                notifications.unread > 0
                                    ? 'rr-settings-status-value warning'
                                    : 'rr-settings-status-value'
                            }
                        >
                            {notifications.unread ?? 0}
                        </span>

                    </div>


                    <div className="rr-settings-status-divider"></div>


                    <div className="rr-settings-status-item">

                        <span className="rr-settings-status-label">
                            Today's Activity
                        </span>

                        <span className="rr-settings-status-value">
                            {activity.today ?? 0}
                        </span>

                    </div>

                </div>

            </section>


            {/* =====================================================
                SYSTEM OVERVIEW
               ===================================================== */}

            <section className="rr-admin-panel">

                <div className="rr-admin-panel-header">

                    <div className="rr-admin-panel-heading">

                        <h2 className="rr-admin-panel-title">
                            System Overview
                        </h2>

                        <div className="rr-admin-panel-description">
                            Current administration status
                        </div>

                    </div>

                </div>


                <div className="rr-admin-panel-body">

                    <div className="rr-settings-overview-grid">

                        <OverviewCard
                            title="Companies"
                            value={
                                companies.total ?? 0
                            }
                            meta={`${companies.active ?? 0} active`}
                            icon="bi-buildings"
                            href="/super-admin/companies"
                        />

                        <OverviewCard
                            title="Branches"
                            value={
                                branches.total ?? 0
                            }
                            meta={`${branches.active ?? 0} active`}
                            icon="bi-shop"
                            href="/super-admin/branches"
                        />

                        <OverviewCard
                            title="Users"
                            value={
                                users.total ?? 0
                            }
                            meta={`${users.active ?? 0} active`}
                            icon="bi-people"
                            href="/super-admin/users"
                        />

                        <OverviewCard
                            title="Roles"
                            value={
                                access.roles ?? 0
                            }
                            meta={`${access.permissions ?? 0} permissions`}
                            icon="bi-person-badge"
                            href="/super-admin/roles"
                        />

                        <OverviewCard
                            title="Activity"
                            value={
                                activity.total ?? 0
                            }
                            meta={`${activity.today ?? 0} today`}
                            icon="bi-clock-history"
                            href="/super-admin/activity-logs"
                        />

                        <OverviewCard
                            title="Notifications"
                            value={
                                notifications.total ?? 0
                            }
                            meta={`${notifications.unread ?? 0} unread`}
                            icon="bi-bell"
                            warning={
                                (notifications.unread ?? 0) > 0
                            }
                        />

                    </div>

                </div>

            </section>


            {/* =====================================================
                CONFIGURATION AREAS
               ===================================================== */}

            <section className="rr-admin-panel mt-3">

                <div className="rr-admin-panel-header">

                    <div className="rr-admin-panel-heading">

                        <h2 className="rr-admin-panel-title">
                            Administration
                        </h2>

                        <div className="rr-admin-panel-description">
                            Select an area to manage
                        </div>

                    </div>

                </div>


                <div className="rr-admin-panel-body">

                    <div className="rr-settings-grid">

                        {settings.map(
                            (setting) => (

                                <SettingCard
                                    key={
                                        setting.title
                                    }
                                    setting={
                                        setting
                                    }
                                />

                            )
                        )}

                    </div>

                </div>

            </section>


            {/* =====================================================
                SECURITY SUMMARY
               ===================================================== */}

            <section className="rr-admin-panel mt-3">

                <div className="rr-admin-panel-header">

                    <div className="rr-admin-panel-heading">

                        <h2 className="rr-admin-panel-title">
                            Security & Access
                        </h2>

                        <div className="rr-admin-panel-description">
                            Current access-control configuration
                        </div>

                    </div>

                </div>


                <div className="rr-admin-panel-body">

                    <div className="rr-settings-security-grid">

                        <SecuritySummary
                            icon="bi-person-badge"
                            label="Roles"
                            value={
                                access.roles ?? 0
                            }
                            description="Configured access groups"
                        />

                        <SecuritySummary
                            icon="bi-shield-check"
                            label="Permissions"
                            value={
                                access.permissions ?? 0
                            }
                            description="Granular access rules"
                        />

                        <SecuritySummary
                            icon="bi-person-check"
                            label="Active Users"
                            value={
                                users.active ?? 0
                            }
                            description="Enabled company accounts"
                        />

                        <SecuritySummary
                            icon="bi-person-x"
                            label="Inactive Users"
                            value={
                                users.inactive ?? 0
                            }
                            description="Disabled company accounts"
                        />

                    </div>

                </div>

            </section>


            {/* =====================================================
                ORGANIZATION SUMMARY
               ===================================================== */}

            <section className="rr-admin-panel mt-3">

                <div className="rr-admin-panel-header">

                    <div className="rr-admin-panel-heading">

                        <h2 className="rr-admin-panel-title">
                            Organization
                        </h2>

                        <div className="rr-admin-panel-description">
                            Platform company and branch status
                        </div>

                    </div>

                </div>


                <div className="rr-admin-panel-body">

                    <div className="rr-settings-organization-grid">

                        <OrganizationRow
                            icon="bi-buildings"
                            title="Companies"
                            total={
                                companies.total ?? 0
                            }
                            active={
                                companies.active ?? 0
                            }
                            inactive={
                                companies.inactive ?? 0
                            }
                            archived={
                                companies.archived ?? 0
                            }
                        />


                        <OrganizationRow
                            icon="bi-shop"
                            title="Branches"
                            total={
                                branches.total ?? 0
                            }
                            active={
                                branches.active ?? 0
                            }
                            inactive={
                                branches.inactive ?? 0
                            }
                        />

                    </div>

                </div>

            </section>

        </SuperAdminLayout>

    );
}


/*
|--------------------------------------------------------------------------
| Setting Card
|--------------------------------------------------------------------------
*/

function SettingCard({
    setting,
}) {

    const content = (

        <>

            <div className="rr-setting-card-top">

                <div className="rr-setting-card-icon">

                    <i
                        className={
                            `bi ${setting.icon}`
                        }
                    ></i>

                </div>


                {!setting.available && (

                    <span className="rr-setting-coming-soon">
                        Coming Soon
                    </span>

                )}

            </div>


            <div className="rr-setting-card-title">
                {setting.title}
            </div>


            <div className="rr-setting-card-description">
                {setting.description}
            </div>


            <div className="rr-setting-card-footer">

                {setting.value !== null ? (

                    <span className="rr-setting-card-count">

                        {setting.value}

                        {' '}

                        {setting.valueLabel}

                    </span>

                ) : (

                    <span></span>

                )}


                {setting.available && (

                    <span className="rr-setting-card-open">

                        Open

                        <i className="bi bi-chevron-right"></i>

                    </span>

                )}

            </div>

        </>

    );


    if (
        setting.available &&
        setting.href
    ) {

        return (

            <Link
                href={
                    setting.href
                }
                className="rr-setting-card"
            >

                {content}

            </Link>

        );
    }


    return (

        <div className="rr-setting-card rr-setting-card-disabled">

            {content}

        </div>

    );
}


/*
|--------------------------------------------------------------------------
| Overview Card
|--------------------------------------------------------------------------
*/

function OverviewCard({
    title,
    value,
    meta,
    icon,
    href = null,
    warning = false,
}) {

    const content = (

        <>

            <div className="rr-settings-overview-icon">

                <i
                    className={
                        `bi ${icon}`
                    }
                ></i>

            </div>


            <div className="rr-settings-overview-data">

                <div className="rr-settings-overview-label">
                    {title}
                </div>

                <div
                    className={
                        warning
                            ? 'rr-settings-overview-value warning'
                            : 'rr-settings-overview-value'
                    }
                >
                    {value}
                </div>

                <div className="rr-settings-overview-meta">
                    {meta}
                </div>

            </div>

        </>

    );


    if (href) {

        return (

            <Link
                href={
                    href
                }
                className="rr-settings-overview-card"
            >
                {content}
            </Link>

        );
    }


    return (

        <div className="rr-settings-overview-card">
            {content}
        </div>

    );
}


/*
|--------------------------------------------------------------------------
| Security Summary
|--------------------------------------------------------------------------
*/

function SecuritySummary({
    icon,
    label,
    value,
    description,
}) {

    return (

        <div className="rr-settings-security-item">

            <div className="rr-settings-security-icon">

                <i
                    className={
                        `bi ${icon}`
                    }
                ></i>

            </div>


            <div className="rr-settings-security-data">

                <div className="rr-settings-security-label">
                    {label}
                </div>

                <div className="rr-settings-security-value">
                    {value}
                </div>

                <div className="rr-settings-security-description">
                    {description}
                </div>

            </div>

        </div>

    );
}


/*
|--------------------------------------------------------------------------
| Organization Row
|--------------------------------------------------------------------------
*/

function OrganizationRow({
    icon,
    title,
    total,
    active,
    inactive,
    archived = null,
}) {

    return (

        <div className="rr-settings-organization-row">

            <div className="rr-settings-organization-title">

                <div className="rr-settings-organization-icon">

                    <i
                        className={
                            `bi ${icon}`
                        }
                    ></i>

                </div>

                <span>
                    {title}
                </span>

            </div>


            <OrganizationMetric
                label="Total"
                value={
                    total
                }
            />

            <OrganizationMetric
                label="Active"
                value={
                    active
                }
                type="success"
            />

            <OrganizationMetric
                label="Inactive"
                value={
                    inactive
                }
                type="warning"
            />


            {archived !== null && (

                <OrganizationMetric
                    label="Archived"
                    value={
                        archived
                    }
                    type="muted"
                />

            )}

        </div>

    );
}


/*
|--------------------------------------------------------------------------
| Organization Metric
|--------------------------------------------------------------------------
*/

function OrganizationMetric({
    label,
    value,
    type = '',
}) {

    return (

        <div className="rr-settings-organization-metric">

            <span className="rr-settings-organization-metric-label">
                {label}
            </span>

            <span
                className={
                    `rr-settings-organization-metric-value ${type}`
                }
            >
                {value}
            </span>

        </div>

    );
}