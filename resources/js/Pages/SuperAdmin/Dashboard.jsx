import {
    Head,
    Link,
} from '@inertiajs/react';

import SuperAdminLayout
    from '../../Layouts/SuperAdminLayout';


export default function Dashboard({
    stats = {},
    recentCompanies = [],
    recentBranches = [],
    recentUsers = [],
    recentActivities = [],
}) {

    const inactiveTotal =
        (stats.companies?.inactive ?? 0) +
        (stats.branches?.inactive ?? 0) +
        (stats.users?.inactive ?? 0);


    return (

        <SuperAdminLayout
            title="Dashboard"
            subtitle="Platform administration overview"
            breadcrumbs={[
                {
                    label: 'Dashboard',
                },
            ]}
        >

            <Head title="Super Admin Dashboard" />


            {/* =====================================================
                PRIMARY SUMMARY
               ===================================================== */}

            <div className="row g-3 mb-4">

                <DashboardCard
                    title="Companies"
                    value={
                        stats.companies?.total ?? 0
                    }
                    description={
                        `${stats.companies?.active ?? 0} active companies`
                    }
                    icon="bi-buildings"
                    type="primary"
                    href="/super-admin/companies"
                />


                <DashboardCard
                    title="Branches"
                    value={
                        stats.branches?.total ?? 0
                    }
                    description={
                        `${stats.branches?.active ?? 0} active branches`
                    }
                    icon="bi-shop"
                    type="success"
                    href="/super-admin/branches"
                />


                <DashboardCard
                    title="Users"
                    value={
                        stats.users?.total ?? 0
                    }
                    description={
                        `${stats.users?.active ?? 0} active users`
                    }
                    icon="bi-people"
                    type="info"
                    href="/super-admin/users"
                />


                <DashboardCard
                    title="Needs Attention"
                    value={
                        inactiveTotal
                    }
                    description="Inactive records"
                    icon="bi-exclamation-triangle"
                    type="warning"
                />

            </div>


            {/* =====================================================
                PLATFORM STATUS
               ===================================================== */}

            <div className="rr-admin-panel mb-4">

                <div className="rr-admin-panel-header">

                    <div className="rr-admin-panel-heading">

                        <h2 className="rr-admin-panel-title">
                            Platform Status
                        </h2>

                        <div className="rr-admin-panel-description">
                            Current company, branch and user availability
                        </div>

                    </div>

                </div>


                <div className="rr-dashboard-status-grid">

                    <StatusMetric
                        label="Active Companies"
                        value={
                            stats.companies?.active ?? 0
                        }
                        icon="bi-building-check"
                        type="success"
                    />


                    <StatusMetric
                        label="Inactive Companies"
                        value={
                            stats.companies?.inactive ?? 0
                        }
                        icon="bi-building-x"
                        type="danger"
                    />


                    <StatusMetric
                        label="Active Branches"
                        value={
                            stats.branches?.active ?? 0
                        }
                        icon="bi-shop-window"
                        type="success"
                    />


                    <StatusMetric
                        label="Inactive Branches"
                        value={
                            stats.branches?.inactive ?? 0
                        }
                        icon="bi-shop"
                        type="danger"
                    />


                    <StatusMetric
                        label="Active Users"
                        value={
                            stats.users?.active ?? 0
                        }
                        icon="bi-person-check"
                        type="success"
                    />


                    <StatusMetric
                        label="Inactive Users"
                        value={
                            stats.users?.inactive ?? 0
                        }
                        icon="bi-person-dash"
                        type="danger"
                    />

                </div>

            </div>


            {/* =====================================================
                RECENT ACTIVITY
               ===================================================== */}

            <div className="rr-admin-panel mb-4">

                <div className="rr-admin-panel-header">

                    <div className="rr-admin-panel-heading">

                        <h2 className="rr-admin-panel-title">
                            Recent Activity
                        </h2>

                        <div className="rr-admin-panel-description">
                            Latest Super Admin and system audit actions
                        </div>

                    </div>


                    <Link
                        href="/super-admin/activity-logs"
                        className="rr-btn rr-btn-secondary"
                    >
                        <i className="bi bi-clock-history"></i>

                        View All Activity
                    </Link>

                </div>


                <div className="rr-dashboard-activity-list">

                    {recentActivities.length > 0 ? (

                        recentActivities.map(
                            (activity) => (

                                <ActivityItem
                                    key={
                                        activity.id
                                    }
                                    activity={
                                        activity
                                    }
                                />

                            )
                        )

                    ) : (

                        <EmptyState
                            icon="bi-clock-history"
                            title="No activity yet"
                            text="Administrative activity will appear here."
                        />

                    )}

                </div>

            </div>


            {/* =====================================================
                RECENT COMPANIES + BRANCHES
               ===================================================== */}

            <div className="row g-3 mb-4">

                {/* Companies */}

                <div className="col-xl-6">

                    <div className="rr-admin-panel h-100">

                        <div className="rr-admin-panel-header">

                            <div className="rr-admin-panel-heading">

                                <h2 className="rr-admin-panel-title">
                                    Recent Companies
                                </h2>

                                <div className="rr-admin-panel-description">
                                    Recently created companies
                                </div>

                            </div>


                            <Link
                                href="/super-admin/companies"
                                className="rr-btn rr-btn-secondary"
                            >
                                View All
                            </Link>

                        </div>


                        <div className="rr-dashboard-list">

                            {recentCompanies.length > 0 ? (

                                recentCompanies.map(
                                    (company) => (

                                        <Link
                                            key={
                                                company.id
                                            }
                                            href={
                                                `/super-admin/companies/${company.id}`
                                            }
                                            className="rr-dashboard-list-item"
                                        >

                                            <div className="rr-dashboard-list-icon">

                                                <i className="bi bi-building"></i>

                                            </div>


                                            <div className="rr-dashboard-list-content">

                                                <div className="rr-dashboard-list-title">

                                                    {
                                                        company.company_name
                                                    }

                                                </div>


                                                <div className="rr-dashboard-list-meta">

                                                    <span>
                                                        {
                                                            company.company_code
                                                            || 'No code'
                                                        }
                                                    </span>

                                                    <span className="rr-dashboard-separator">
                                                        •
                                                    </span>

                                                    <span>
                                                        {
                                                            company.stores_count
                                                            ?? 0
                                                        }
                                                        {' '}
                                                        branches
                                                    </span>

                                                </div>

                                            </div>


                                            <Status
                                                active={
                                                    company.status
                                                }
                                            />

                                        </Link>

                                    )
                                )

                            ) : (

                                <EmptyState
                                    icon="bi-buildings"
                                    title="No companies"
                                    text="No company records are available."
                                    compact
                                />

                            )}

                        </div>

                    </div>

                </div>


                {/* Branches */}

                <div className="col-xl-6">

                    <div className="rr-admin-panel h-100">

                        <div className="rr-admin-panel-header">

                            <div className="rr-admin-panel-heading">

                                <h2 className="rr-admin-panel-title">
                                    Recent Branches
                                </h2>

                                <div className="rr-admin-panel-description">
                                    Recently added company branches
                                </div>

                            </div>


                            <Link
                                href="/super-admin/branches"
                                className="rr-btn rr-btn-secondary"
                            >
                                View All
                            </Link>

                        </div>


                        <div className="rr-dashboard-list">

                            {recentBranches.length > 0 ? (

                                recentBranches.map(
                                    (branch) => (

                                        <Link
                                            key={
                                                branch.id
                                            }
                                            href={
                                                `/super-admin/companies/${branch.company_id}`
                                            }
                                            className="rr-dashboard-list-item"
                                        >

                                            <div className="rr-dashboard-list-icon">

                                                <i className="bi bi-shop"></i>

                                            </div>


                                            <div className="rr-dashboard-list-content">

                                                <div className="rr-dashboard-list-title">

                                                    {
                                                        branch.store_name
                                                    }

                                                </div>


                                                <div className="rr-dashboard-list-meta">

                                                    <span>
                                                        {
                                                            branch.store_code
                                                            || 'No code'
                                                        }
                                                    </span>

                                                    <span className="rr-dashboard-separator">
                                                        •
                                                    </span>

                                                    <span>
                                                        {
                                                            branch.company
                                                                ?.company_name
                                                            || 'No company'
                                                        }
                                                    </span>

                                                </div>

                                            </div>


                                            <Status
                                                active={
                                                    branch.status
                                                }
                                            />

                                        </Link>

                                    )
                                )

                            ) : (

                                <EmptyState
                                    icon="bi-shop"
                                    title="No branches"
                                    text="No branch records are available."
                                    compact
                                />

                            )}

                        </div>

                    </div>

                </div>

            </div>


            {/* =====================================================
                RECENT USERS
               ===================================================== */}

            <div className="rr-admin-panel">

                <div className="rr-admin-panel-header">

                    <div className="rr-admin-panel-heading">

                        <h2 className="rr-admin-panel-title">
                            Recent Users
                        </h2>

                        <div className="rr-admin-panel-description">
                            Recently created company users
                        </div>

                    </div>


                    <Link
                        href="/super-admin/users"
                        className="rr-btn rr-btn-secondary"
                    >
                        View All
                    </Link>

                </div>


                <div className="rr-admin-table-wrapper">

                    <table className="rr-admin-table">

                        <thead>

                            <tr>

                                <th>
                                    User
                                </th>

                                <th>
                                    Company
                                </th>

                                <th>
                                    Role
                                </th>

                                <th>
                                    Status
                                </th>

                                <th className="text-end">
                                    Action
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            {recentUsers.length > 0 ? (

                                recentUsers.map(
                                    (user) => (

                                        <tr key={user.id}>

                                            <td>

                                                <div className="rr-dashboard-user-cell">

                                                    <div className="rr-dashboard-user-avatar">

                                                        {getInitials(
                                                            user.name
                                                        )}

                                                    </div>


                                                    <div>

                                                        <div className="rr-dashboard-user-name">

                                                            {
                                                                user.name
                                                            }

                                                        </div>

                                                        <div className="rr-table-secondary">

                                                            {
                                                                user.email
                                                            }

                                                        </div>

                                                    </div>

                                                </div>

                                            </td>


                                            <td>

                                                <div className="rr-dashboard-company-cell">

                                                    <div className="rr-dashboard-company-name">

                                                        {
                                                            user.company
                                                                ?.company_name
                                                            || '—'
                                                        }

                                                    </div>


                                                    {user.company?.company_code && (

                                                        <div className="rr-table-secondary">

                                                            {
                                                                user.company
                                                                    .company_code
                                                            }

                                                        </div>

                                                    )}

                                                </div>

                                            </td>


                                            <td>

                                                {user.roles?.length ? (

                                                    <span className="rr-dashboard-role-badge">

                                                        {formatText(
                                                            user.roles[0]
                                                                .name
                                                        )}

                                                    </span>

                                                ) : (

                                                    <span className="rr-table-secondary">
                                                        No role
                                                    </span>

                                                )}

                                            </td>


                                            <td>

                                                <Status
                                                    active={
                                                        user.status
                                                    }
                                                />

                                            </td>


                                            <td className="text-end">

                                                <Link
                                                    href={
                                                        `/super-admin/users/${user.id}`
                                                    }
                                                    className="rr-icon-action"
                                                    title="View user"
                                                >

                                                    <i className="bi bi-eye"></i>

                                                </Link>

                                            </td>

                                        </tr>

                                    )
                                )

                            ) : (

                                <tr>

                                    <td colSpan="5">

                                        <EmptyState
                                            icon="bi-people"
                                            title="No users"
                                            text="No company users are available."
                                            compact
                                        />

                                    </td>

                                </tr>

                            )}

                        </tbody>

                    </table>

                </div>

            </div>

        </SuperAdminLayout>
    );
}


/*
|--------------------------------------------------------------------------
| Primary Dashboard Card
|--------------------------------------------------------------------------
*/

function DashboardCard({
    title,
    value,
    description,
    icon,
    type = 'primary',
    href = null,
}) {

    const content = (

        <div className="rr-dashboard-stat-card">

            <div className="rr-dashboard-stat-top">

                <div>

                    <div className="rr-dashboard-stat-title">
                        {title}
                    </div>

                    <div className="rr-dashboard-stat-value">
                        {value}
                    </div>

                </div>


                <div
                    className={
                        `rr-dashboard-stat-icon ` +
                        `rr-dashboard-stat-icon-${type}`
                    }
                >

                    <i className={`bi ${icon}`}></i>

                </div>

            </div>


            <div className="rr-dashboard-stat-description">

                {description}

            </div>

        </div>

    );


    return (

        <div className="col-xl-3 col-md-6">

            {href ? (

                <Link
                    href={href}
                    className="rr-dashboard-stat-link"
                >
                    {content}
                </Link>

            ) : content}

        </div>

    );
}


/*
|--------------------------------------------------------------------------
| Platform Status Metric
|--------------------------------------------------------------------------
*/

function StatusMetric({
    label,
    value,
    icon,
    type = 'primary',
}) {

    return (

        <div className="rr-dashboard-status-item">

            <div
                className={
                    `rr-dashboard-status-icon ` +
                    `rr-dashboard-status-icon-${type}`
                }
            >

                <i className={`bi ${icon}`}></i>

            </div>


            <div>

                <div className="rr-dashboard-status-value">

                    {value}

                </div>

                <div className="rr-dashboard-status-label">

                    {label}

                </div>

            </div>

        </div>

    );
}


/*
|--------------------------------------------------------------------------
| Activity Item
|--------------------------------------------------------------------------
*/

function ActivityItem({
    activity,
}) {

    return (

        <div className="rr-dashboard-activity-item">

            <div
                className={
                    `rr-dashboard-activity-icon ` +
                    `rr-dashboard-activity-icon-${activityType(
                        activity.action
                    )}`
                }
            >

                <i
                    className={
                        `bi ${activityIcon(
                            activity.action
                        )}`
                    }
                ></i>

            </div>


            <div className="rr-dashboard-activity-main">

                <div className="rr-dashboard-activity-top">

                    <div className="rr-dashboard-activity-description">

                        {
                            activity.description
                        }

                    </div>


                    <div className="rr-dashboard-activity-time">

                        {formatRelativeDate(
                            activity.created_at
                        )}

                    </div>

                </div>


                <div className="rr-dashboard-activity-meta">

                    <span className="rr-dashboard-activity-module">

                        {formatText(
                            activity.module
                        )}

                    </span>


                    <ActionBadge
                        action={
                            activity.action
                        }
                    />


                    <span className="rr-dashboard-activity-user">

                        <i className="bi bi-person"></i>

                        {
                            activity.user?.name
                            || 'System'
                        }

                    </span>


                    {activity.ip_address && (

                        <span className="rr-dashboard-activity-ip">

                            <i className="bi bi-globe2"></i>

                            {
                                activity.ip_address
                            }

                        </span>

                    )}

                </div>

            </div>

        </div>

    );
}


/*
|--------------------------------------------------------------------------
| Action Badge
|--------------------------------------------------------------------------
*/

function ActionBadge({
    action,
}) {

    return (

        <span
            className={
                `rr-dashboard-action-badge ` +
                `rr-dashboard-action-${activityType(
                    action
                )}`
            }
        >

            {formatText(action)}

        </span>

    );
}


/*
|--------------------------------------------------------------------------
| Status
|--------------------------------------------------------------------------
*/

function Status({
    active,
}) {

    return (

        <span
            className={
                `rr-status ${active
                    ? 'rr-status-active'
                    : 'rr-status-inactive'
                }`
            }
        >

            {active
                ? 'Active'
                : 'Inactive'}

        </span>

    );
}


/*
|--------------------------------------------------------------------------
| Empty State
|--------------------------------------------------------------------------
*/

function EmptyState({
    icon,
    title,
    text,
    compact = false,
}) {

    return (

        <div
            className={
                compact
                    ? 'rr-dashboard-empty rr-dashboard-empty-compact'
                    : 'rr-dashboard-empty'
            }
        >

            <div className="rr-dashboard-empty-icon">

                <i className={`bi ${icon}`}></i>

            </div>

            <div className="rr-dashboard-empty-title">

                {title}

            </div>

            <div className="rr-dashboard-empty-text">

                {text}

            </div>

        </div>

    );
}


/*
|--------------------------------------------------------------------------
| Activity Type
|--------------------------------------------------------------------------
*/

function activityType(action) {

    const value =
        String(
            action ?? ''
        ).toLowerCase();


    if (
        [
            'created',
            'activated',
            'restored',
        ].includes(value)
    ) {
        return 'success';
    }


    if (
        [
            'updated',
            'edited',
        ].includes(value)
    ) {
        return 'primary';
    }


    if (
        [
            'deleted',
            'deactivated',
            'archived',
        ].includes(value)
    ) {
        return 'danger';
    }


    return 'neutral';
}


/*
|--------------------------------------------------------------------------
| Activity Icon
|--------------------------------------------------------------------------
*/

function activityIcon(action) {

    const value =
        String(
            action ?? ''
        ).toLowerCase();


    const icons = {
        created:
            'bi-plus-lg',

        updated:
            'bi-pencil',

        edited:
            'bi-pencil',

        activated:
            'bi-check-lg',

        deactivated:
            'bi-dash-lg',

        archived:
            'bi-archive',

        restored:
            'bi-arrow-counterclockwise',

        deleted:
            'bi-trash',
    };


    return (
        icons[value]
        || 'bi-activity'
    );
}


/*
|--------------------------------------------------------------------------
| Initials
|--------------------------------------------------------------------------
*/

function getInitials(name) {

    if (!name) {
        return 'U';
    }


    const parts =
        String(name)
            .trim()
            .split(/\s+/)
            .filter(Boolean);


    if (
        parts.length === 1
    ) {

        return parts[0]
            .substring(
                0,
                2
            )
            .toUpperCase();
    }


    return (
        parts[0][0] +
        parts[
        parts.length - 1
        ][0]
    ).toUpperCase();
}


/*
|--------------------------------------------------------------------------
| Display Text
|--------------------------------------------------------------------------
*/

function formatText(value) {

    return String(
        value ?? ''
    )
        .replaceAll(
            '-',
            ' '
        )
        .replaceAll(
            '_',
            ' '
        )
        .replaceAll(
            '.',
            ' '
        )
        .replace(
            /\b\w/g,
            (character) =>
                character.toUpperCase()
        );
}


/*
|--------------------------------------------------------------------------
| Relative Date
|--------------------------------------------------------------------------
*/

function formatRelativeDate(value) {

    if (!value) {
        return '—';
    }


    const date =
        new Date(value);

    const now =
        new Date();


    const diff =
        now.getTime() -
        date.getTime();


    const minutes =
        Math.floor(
            diff / 60000
        );


    if (minutes < 1) {
        return 'Just now';
    }


    if (minutes < 60) {

        return `${minutes} min ago`;

    }


    const hours =
        Math.floor(
            minutes / 60
        );


    if (hours < 24) {

        return `${hours} hr ago`;

    }


    return new Intl.DateTimeFormat(
        'en-IN',
        {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
        }
    ).format(date);
}