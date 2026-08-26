import {
    Head,
    router,
} from '@inertiajs/react';

import {
    useEffect,
    useState,
} from 'react';

import SuperAdminLayout
    from '../../../Layouts/SuperAdminLayout';


export default function Index({
    activities,
    filters = {},
    filterOptions = {},
}) {

    const [search, setSearch] =
        useState(filters.search ?? '');

    const [module, setModule] =
        useState(filters.module ?? '');

    const [action, setAction] =
        useState(filters.action ?? '');

    const [userId, setUserId] =
        useState(filters.user_id ?? '');

    const [from, setFrom] =
        useState(filters.from ?? '');

    const [to, setTo] =
        useState(filters.to ?? '');

    const [selectedActivity, setSelectedActivity] =
        useState(null);


    /*
    |--------------------------------------------------------------------------
    | Search Delay
    |--------------------------------------------------------------------------
    */

    useEffect(() => {

        const timer =
            setTimeout(() => {

                if (
                    search !==
                    (filters.search ?? '')
                ) {
                    applyFilters({
                        search,
                        module,
                        action,
                        user_id: userId,
                        from,
                        to,
                    });
                }

            }, 450);


        return () =>
            clearTimeout(timer);

    }, [search]);


    /*
    |--------------------------------------------------------------------------
    | Apply Filters
    |--------------------------------------------------------------------------
    */

    function applyFilters(values = {}) {

        const params = {
            search,
            module,
            action,
            user_id: userId,
            from,
            to,
            ...values,
        };


        Object.keys(params).forEach(
            (key) => {

                if (
                    params[key] === '' ||
                    params[key] === null ||
                    params[key] === undefined
                ) {
                    delete params[key];
                }

            }
        );


        router.get(
            '/super-admin/activity-logs',
            params,
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | Reset
    |--------------------------------------------------------------------------
    */

    function resetFilters() {

        setSearch('');
        setModule('');
        setAction('');
        setUserId('');
        setFrom('');
        setTo('');

        router.get(
            '/super-admin/activity-logs',
            {},
            {
                preserveState: false,
                preserveScroll: true,
                replace: true,
            }
        );
    }


    const hasFilters =
        search ||
        module ||
        action ||
        userId ||
        from ||
        to;


    return (

        <SuperAdminLayout
            title="Activity Logs"
            subtitle="Review administrative actions and system audit history"
            breadcrumbs={[
                {
                    label: 'Activity Logs',
                },
            ]}
        >

            <Head title="Activity Logs" />


            {/* =====================================================
                HEADER / SUMMARY
               ===================================================== */}

            <div className="row g-3 mb-4">

                <ActivityStatCard
                    title="Total Records"
                    value={
                        activities?.total ?? 0
                    }
                    icon="bi-clock-history"
                    description="Matching audit records"
                />

                <ActivityStatCard
                    title="Modules"
                    value={
                        filterOptions.modules
                            ?.length ?? 0
                    }
                    icon="bi-grid"
                    description="Logged application modules"
                />

                <ActivityStatCard
                    title="Actions"
                    value={
                        filterOptions.actions
                            ?.length ?? 0
                    }
                    icon="bi-lightning"
                    description="Logged action types"
                />

                <ActivityStatCard
                    title="Administrators"
                    value={
                        filterOptions.users
                            ?.length ?? 0
                    }
                    icon="bi-people"
                    description="Users appearing in logs"
                />

            </div>


            {/* =====================================================
                MAIN PANEL
               ===================================================== */}

            <div className="rr-admin-panel">

                <div className="rr-admin-panel-header">

                    <div className="rr-admin-panel-heading">

                        <h2 className="rr-admin-panel-title">
                            Audit Trail
                        </h2>

                        <div className="rr-admin-panel-description">
                            Activity logs are read-only and retained for auditing
                        </div>

                    </div>


                    <span className="rr-count-badge">

                        {activities?.total ?? 0}

                    </span>

                </div>


                {/* =================================================
                    SEARCH
                   ================================================= */}

                <div className="rr-activity-toolbar">

                    <div className="rr-activity-search">

                        <i className="bi bi-search"></i>

                        <input
                            type="text"
                            value={search}
                            onChange={(event) =>
                                setSearch(
                                    event.target.value
                                )
                            }
                            placeholder="Search activity, administrator or IP..."
                        />


                        {search && (

                            <button
                                type="button"
                                className="rr-activity-search-clear"
                                onClick={() => {

                                    setSearch('');

                                    applyFilters({
                                        search: '',
                                    });

                                }}
                            >
                                <i className="bi bi-x-lg"></i>
                            </button>

                        )}

                    </div>


                    {hasFilters && (

                        <button
                            type="button"
                            className="rr-btn rr-btn-secondary"
                            onClick={resetFilters}
                        >
                            <i className="bi bi-arrow-counterclockwise"></i>

                            Reset
                        </button>

                    )}

                </div>


                {/* =================================================
                    FILTERS
                   ================================================= */}

                <div className="rr-activity-filters">

                    {/* Module */}

                    <div className="rr-activity-filter">

                        <label>
                            Module
                        </label>

                        <select
                            value={module}
                            onChange={(event) => {

                                const value =
                                    event.target.value;

                                setModule(value);

                                applyFilters({
                                    module: value,
                                });

                            }}
                        >

                            <option value="">
                                All Modules
                            </option>

                            {(filterOptions.modules ?? [])
                                .map(
                                    (item) => (

                                        <option
                                            key={item}
                                            value={item}
                                        >
                                            {formatText(item)}
                                        </option>

                                    )
                                )}

                        </select>

                    </div>


                    {/* Action */}

                    <div className="rr-activity-filter">

                        <label>
                            Action
                        </label>

                        <select
                            value={action}
                            onChange={(event) => {

                                const value =
                                    event.target.value;

                                setAction(value);

                                applyFilters({
                                    action: value,
                                });

                            }}
                        >

                            <option value="">
                                All Actions
                            </option>

                            {(filterOptions.actions ?? [])
                                .map(
                                    (item) => (

                                        <option
                                            key={item}
                                            value={item}
                                        >
                                            {formatText(item)}
                                        </option>

                                    )
                                )}

                        </select>

                    </div>


                    {/* Administrator */}

                    <div className="rr-activity-filter">

                        <label>
                            Administrator
                        </label>

                        <select
                            value={userId}
                            onChange={(event) => {

                                const value =
                                    event.target.value;

                                setUserId(value);

                                applyFilters({
                                    user_id: value,
                                });

                            }}
                        >

                            <option value="">
                                All Administrators
                            </option>

                            {(filterOptions.users ?? [])
                                .map(
                                    (user) => (

                                        <option
                                            key={user.id}
                                            value={user.id}
                                        >
                                            {user.name}
                                        </option>

                                    )
                                )}

                        </select>

                    </div>


                    {/* From */}

                    <div className="rr-activity-filter">

                        <label>
                            From
                        </label>

                        <input
                            type="date"
                            value={from}
                            onChange={(event) => {

                                const value =
                                    event.target.value;

                                setFrom(value);

                                applyFilters({
                                    from: value,
                                });

                            }}
                        />

                    </div>


                    {/* To */}

                    <div className="rr-activity-filter">

                        <label>
                            To
                        </label>

                        <input
                            type="date"
                            value={to}
                            min={from || undefined}
                            onChange={(event) => {

                                const value =
                                    event.target.value;

                                setTo(value);

                                applyFilters({
                                    to: value,
                                });

                            }}
                        />

                    </div>

                </div>


                {/* =================================================
                    TABLE
                   ================================================= */}

                <div className="rr-admin-table-wrapper">

                    <table className="rr-admin-table rr-activity-table">

                        <thead>

                            <tr>

                                <th>
                                    Date / Time
                                </th>

                                <th>
                                    Administrator
                                </th>

                                <th>
                                    Module
                                </th>

                                <th>
                                    Action
                                </th>

                                <th>
                                    Activity
                                </th>

                                <th>
                                    IP Address
                                </th>

                                <th className="text-end">
                                    Details
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            {activities?.data?.length > 0 ? (

                                activities.data.map(
                                    (activity) => (

                                        <tr key={activity.id}>

                                            <td>

                                                <div className="rr-activity-date">

                                                    {formatDate(
                                                        activity.created_at
                                                    )}

                                                </div>

                                                <div className="rr-activity-time">

                                                    {formatTime(
                                                        activity.created_at
                                                    )}

                                                </div>

                                            </td>


                                            <td>

                                                <div className="rr-activity-user">

                                                    <div className="rr-activity-avatar">

                                                        {getInitials(
                                                            activity.user
                                                                ?.name
                                                        )}

                                                    </div>


                                                    <div>

                                                        <div className="rr-activity-user-name">

                                                            {
                                                                activity.user
                                                                    ?.name
                                                                || 'System'
                                                            }

                                                        </div>


                                                        {activity.user?.email && (

                                                            <div className="rr-activity-user-email">

                                                                {
                                                                    activity.user
                                                                        .email
                                                                }

                                                            </div>

                                                        )}

                                                    </div>

                                                </div>

                                            </td>


                                            <td>

                                                <span className="rr-activity-module">

                                                    {formatText(
                                                        activity.module
                                                    )}

                                                </span>

                                            </td>


                                            <td>

                                                <ActionBadge
                                                    action={
                                                        activity.action
                                                    }
                                                />

                                            </td>


                                            <td>

                                                <div className="rr-activity-description">

                                                    {
                                                        activity.description
                                                    }

                                                </div>

                                            </td>


                                            <td>

                                                <span className="rr-activity-ip">

                                                    {
                                                        activity.ip_address
                                                        || '—'
                                                    }

                                                </span>

                                            </td>


                                            <td className="text-end">

                                                <button
                                                    type="button"
                                                    className="rr-icon-action"
                                                    title="View activity details"
                                                    onClick={() =>
                                                        setSelectedActivity(
                                                            activity
                                                        )
                                                    }
                                                >
                                                    <i className="bi bi-eye"></i>
                                                </button>

                                            </td>

                                        </tr>

                                    )
                                )

                            ) : (

                                <tr>

                                    <td colSpan="7">

                                        <div className="rr-empty-state">

                                            <div className="rr-empty-state-icon">
                                                <i className="bi bi-clock-history"></i>
                                            </div>

                                            <div className="rr-empty-state-title">
                                                No activity found
                                            </div>

                                            <div className="rr-empty-state-text">

                                                {hasFilters
                                                    ? 'Try changing or resetting the current filters.'
                                                    : 'Administrative activity will appear here.'}

                                            </div>

                                        </div>

                                    </td>

                                </tr>

                            )}

                        </tbody>

                    </table>

                </div>


                {/* =================================================
                    PAGINATION
                   ================================================= */}

                <Pagination
                    paginator={activities}
                />

            </div>


            {/* =====================================================
                DETAILS MODAL
               ===================================================== */}

            <ActivityDetailsModal
                activity={selectedActivity}
                onClose={() =>
                    setSelectedActivity(null)
                }
            />

        </SuperAdminLayout>

    );
}


/*
|--------------------------------------------------------------------------
| Activity Details Modal
|--------------------------------------------------------------------------
*/

function ActivityDetailsModal({
    activity,
    onClose,
}) {

    if (!activity) {
        return null;
    }


    return (

        <>

            <div
                className="rr-modal-backdrop"
                onClick={onClose}
            ></div>


            <div
                className="rr-modal-container"
                role="dialog"
                aria-modal="true"
            >

                <div className="rr-activity-modal">

                    {/* Header */}

                    <div className="rr-activity-modal-header">

                        <div className="rr-activity-modal-heading">

                            <div className="rr-activity-modal-icon">

                                <i className="bi bi-clock-history"></i>

                            </div>


                            <div>

                                <div className="rr-activity-modal-title">
                                    Activity Details
                                </div>

                                <div className="rr-activity-modal-subtitle">
                                    Audit record #{activity.id}
                                </div>

                            </div>

                        </div>


                        <button
                            type="button"
                            className="rr-confirm-close"
                            onClick={onClose}
                        >
                            <i className="bi bi-x-lg"></i>
                        </button>

                    </div>


                    {/* Body */}

                    <div className="rr-activity-modal-body">

                        <div className="rr-activity-detail-grid">

                            <DetailItem
                                label="Administrator"
                                value={
                                    activity.user?.name
                                    || 'System'
                                }
                            />

                            <DetailItem
                                label="Date & Time"
                                value={
                                    `${formatDate(
                                        activity.created_at
                                    )} ${formatTime(
                                        activity.created_at
                                    )}`
                                }
                            />

                            <DetailItem
                                label="Module"
                                value={
                                    formatText(
                                        activity.module
                                    )
                                }
                            />

                            <DetailItem
                                label="Action"
                                value={
                                    formatText(
                                        activity.action
                                    )
                                }
                            />

                            <DetailItem
                                label="IP Address"
                                value={
                                    activity.ip_address
                                    || '—'
                                }
                            />

                            <DetailItem
                                label="Record ID"
                                value={
                                    activity.subject_id
                                    ?? '—'
                                }
                            />

                        </div>


                        <div className="rr-activity-detail-section">

                            <div className="rr-activity-detail-section-title">
                                Description
                            </div>

                            <div className="rr-activity-detail-description">
                                {activity.description}
                            </div>

                        </div>


                        {hasValues(activity.old_values) && (

                            <AuditValues
                                title="Previous Values"
                                values={
                                    activity.old_values
                                }
                                type="old"
                            />

                        )}


                        {hasValues(activity.new_values) && (

                            <AuditValues
                                title="New Values"
                                values={
                                    activity.new_values
                                }
                                type="new"
                            />

                        )}


                        {activity.user_agent && (

                            <div className="rr-activity-detail-section">

                                <div className="rr-activity-detail-section-title">
                                    User Agent
                                </div>

                                <div className="rr-activity-user-agent">
                                    {activity.user_agent}
                                </div>

                            </div>

                        )}

                    </div>


                    {/* Footer */}

                    <div className="rr-activity-modal-footer">

                        <button
                            type="button"
                            className="rr-btn rr-btn-secondary"
                            onClick={onClose}
                        >
                            Close
                        </button>

                    </div>

                </div>

            </div>

        </>

    );
}


/*
|--------------------------------------------------------------------------
| Audit Values
|--------------------------------------------------------------------------
*/

function AuditValues({
    title,
    values,
    type,
}) {

    return (

        <div className="rr-activity-detail-section">

            <div className="rr-activity-detail-section-title">
                {title}
            </div>


            <div className="rr-activity-values">

                {Object.entries(
                    values ?? {}
                ).map(
                    ([key, value]) => (

                        <div
                            key={key}
                            className="rr-activity-value-row"
                        >

                            <div className="rr-activity-value-key">

                                {formatText(key)}

                            </div>


                            <div
                                className={
                                    `rr-activity-value-data ` +
                                    `rr-activity-value-${type}`
                                }
                            >

                                {formatValue(value)}

                            </div>

                        </div>

                    )
                )}

            </div>

        </div>

    );
}


/*
|--------------------------------------------------------------------------
| Detail Item
|--------------------------------------------------------------------------
*/

function DetailItem({
    label,
    value,
}) {

    return (

        <div className="rr-activity-detail-item">

            <div className="rr-activity-detail-label">
                {label}
            </div>

            <div className="rr-activity-detail-value">
                {value}
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

    const normalized =
        String(
            action ?? ''
        ).toLowerCase();


    let type = 'default';


    if (
        [
            'created',
            'restored',
            'activated',
        ].includes(normalized)
    ) {
        type = 'success';
    }


    if (
        [
            'updated',
            'edited',
        ].includes(normalized)
    ) {
        type = 'primary';
    }


    if (
        [
            'deleted',
            'archived',
            'deactivated',
        ].includes(normalized)
    ) {
        type = 'danger';
    }


    return (

        <span
            className={
                `rr-activity-action ` +
                `rr-activity-action-${type}`
            }
        >
            {formatText(action)}
        </span>

    );
}


/*
|--------------------------------------------------------------------------
| Pagination
|--------------------------------------------------------------------------
*/

function Pagination({
    paginator,
}) {

    if (
        !paginator ||
        !paginator.links ||
        paginator.last_page <= 1
    ) {
        return null;
    }


    return (

        <div className="rr-activity-pagination">

            <div className="rr-activity-pagination-info">

                Showing
                {' '}
                {paginator.from ?? 0}
                {' '}
                to
                {' '}
                {paginator.to ?? 0}
                {' '}
                of
                {' '}
                {paginator.total ?? 0}

            </div>


            <div className="rr-activity-pagination-links">

                {paginator.links.map(
                    (link, index) => (

                        <button
                            key={index}
                            type="button"
                            disabled={!link.url}
                            className={
                                `rr-activity-page-button ${link.active
                                    ? 'active'
                                    : ''
                                }`
                            }
                            onClick={() => {

                                if (!link.url) {
                                    return;
                                }

                                router.visit(
                                    link.url,
                                    {
                                        preserveState: true,
                                        preserveScroll: true,
                                    }
                                );

                            }}
                            dangerouslySetInnerHTML={{
                                __html:
                                    cleanPaginationLabel(
                                        link.label
                                    ),
                            }}
                        />

                    )
                )}

            </div>

        </div>

    );
}


/*
|--------------------------------------------------------------------------
| Summary Card
|--------------------------------------------------------------------------
*/

function ActivityStatCard({
    title,
    value,
    icon,
    description,
}) {

    return (

        <div className="col-xl-3 col-md-6">

            <div className="rr-user-stat-card">

                <div className="rr-user-stat-top">

                    <div className="rr-user-stat-title">
                        {title}
                    </div>

                    <div className="rr-user-stat-icon rr-user-stat-icon-primary">

                        <i className={`bi ${icon}`}></i>

                    </div>

                </div>

                <div className="rr-user-stat-value">
                    {value}
                </div>

                <div className="rr-user-stat-description">
                    {description}
                </div>

            </div>

        </div>

    );
}


/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function hasValues(value) {

    return (
        value &&
        typeof value === 'object' &&
        Object.keys(value).length > 0
    );
}


function formatValue(value) {

    if (
        value === null ||
        value === undefined ||
        value === ''
    ) {
        return '—';
    }

    if (value === true) {
        return 'Yes';
    }

    if (value === false) {
        return 'No';
    }

    if (typeof value === 'object') {

        return JSON.stringify(
            value
        );

    }

    return String(value);
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


function formatDate(value) {

    if (!value) {
        return '—';
    }

    return new Intl.DateTimeFormat(
        'en-IN',
        {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
        }
    ).format(
        new Date(value)
    );
}


function formatTime(value) {

    if (!value) {
        return '';
    }

    return new Intl.DateTimeFormat(
        'en-IN',
        {
            hour: '2-digit',
            minute: '2-digit',
            hour12: true,
        }
    ).format(
        new Date(value)
    );
}


function getInitials(name) {

    if (!name) {
        return 'SY';
    }

    const parts =
        String(name)
            .trim()
            .split(/\s+/)
            .filter(Boolean);


    if (parts.length === 1) {

        return parts[0]
            .substring(0, 2)
            .toUpperCase();

    }


    return (
        parts[0][0] +
        parts[parts.length - 1][0]
    ).toUpperCase();
}


function cleanPaginationLabel(label) {

    return String(label ?? '')
        .replace('&laquo;', '‹')
        .replace('&raquo;', '›')
        .replace('Previous', '')
        .replace('Next', '');
}