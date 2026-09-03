import {
    Head,
    Link,
    router,
    usePage,
} from '@inertiajs/react';

import BusinessLayout
    from '../../Layouts/BusinessLayout';

import MasterNavigation
    from '../../Components/Business/MasterNavigation';

import '../../../css/business/units.css';


export default function Show({
    unit,
}) {

    /*
    |--------------------------------------------------------------------------
    | Shared Props
    |--------------------------------------------------------------------------
    */

    const {
        auth,
        business,
    } = usePage().props;


    const user =
        auth?.user;


    const permissions =
        user?.permissions
        ?? [];


    const roles =
        user?.roles
        ?? [];


    const isSuperAdmin =
        Boolean(
            user?.is_super_user
        );


    const isCompanyAdmin =
        roles.includes(
            'Company Admin'
        );


    const can =
        permission => {

            if (
                isSuperAdmin ||
                isCompanyAdmin
            ) {
                return true;
            }

            return permissions.includes(
                permission
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    const goBack =
        () => {

            router.visit(
                '/units'
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */

    return (

        <BusinessLayout>

            <Head
                title={
                    unit?.unit_name
                    ?? 'Unit'
                }
            />


            <div className="rr-unit-show-page">

                <MasterNavigation
                    active="units"
                    title="Units"
                    subtitle="Units of measurement for products"
                />


                {/* =====================================================
                    HEADER
                   ===================================================== */}

                <section className="rr-unit-show-header">

                    <div className="rr-unit-show-header-main">

                        <button
                            type="button"
                            className="rr-unit-show-back"
                            onClick={
                                goBack
                            }
                            title="Back to units"
                        >

                            <i className="bi bi-arrow-left"></i>

                        </button>


                        <div
                            className={
                                `rr-unit-show-avatar ${unit?.is_system_unit
                                    ? 'system'
                                    : 'custom'
                                }`
                            }
                        >

                            {
                                getInitials(
                                    unit?.unit_name
                                )
                            }

                        </div>


                        <div className="rr-unit-show-identity">

                            <span className="rr-unit-show-eyebrow">

                                {
                                    unit?.is_system_unit
                                        ? 'System Unit'
                                        : 'Custom Unit'
                                }

                            </span>


                            <div className="rr-unit-show-title-line">

                                <h1>
                                    {
                                        unit?.unit_name
                                    }
                                </h1>


                                <span
                                    className={
                                        `rr-unit-status ${unit?.status
                                            ? 'active'
                                            : 'inactive'
                                        }`
                                    }
                                >

                                    <span></span>

                                    {
                                        unit?.status
                                            ? 'Active'
                                            : 'Inactive'
                                    }

                                </span>

                            </div>


                            <div className="rr-unit-show-subline">

                                <span>

                                    <i className="bi bi-type"></i>

                                    {
                                        unit?.short_name
                                        ?? '—'
                                    }

                                </span>


                                {
                                    business?.store_name && (

                                        <span>

                                            <i className="bi bi-shop"></i>

                                            {
                                                business.store_name
                                            }

                                        </span>

                                    )
                                }


                                {
                                    business?.company_name && (

                                        <span>

                                            <i className="bi bi-buildings"></i>

                                            {
                                                business.company_name
                                            }

                                        </span>

                                    )
                                }

                            </div>

                        </div>

                    </div>


                    <div className="rr-unit-show-header-actions">

                        {
                            can(
                                'unit.edit'
                            ) && (

                                <Link
                                    href={
                                        `/units/${unit.id}/edit`
                                    }
                                    className="rr-unit-primary-btn"
                                >

                                    <i className="bi bi-pencil"></i>

                                    Edit Unit

                                </Link>

                            )
                        }

                    </div>

                </section>


                {/* =====================================================
                    SUMMARY
                   ===================================================== */}

                <section className="rr-unit-show-summary">

                    <SummaryCard
                        label="Short Name"
                        value={
                            unit?.short_name
                            ?? '—'
                        }
                        icon="bi-type"
                    />


                    <SummaryCard
                        label="Unit Type"
                        value={
                            unit?.is_system_unit
                                ? 'System'
                                : 'Custom'
                        }
                        icon={
                            unit?.is_system_unit
                                ? 'bi-shield-check'
                                : 'bi-pencil-square'
                        }
                        tone={
                            unit?.is_system_unit
                                ? 'system'
                                : 'warning'
                        }
                    />


                    <SummaryCard
                        label="Status"
                        value={
                            unit?.status
                                ? 'Active'
                                : 'Inactive'
                        }
                        icon={
                            unit?.status
                                ? 'bi-check-circle'
                                : 'bi-dash-circle'
                        }
                        tone={
                            unit?.status
                                ? 'success'
                                : 'muted'
                        }
                    />

                </section>


                {/* =====================================================
                    DETAILS
                   ===================================================== */}

                <section className="rr-unit-show-grid">

                    <div className="rr-unit-show-card">

                        <CardHeader
                            icon="bi-rulers"
                            title="Unit Details"
                        />


                        <div className="rr-unit-show-card-body">

                            <InfoRow
                                label="Unit Name"
                                value={
                                    unit?.unit_name
                                }
                            />


                            <InfoRow
                                label="Short Name"
                                value={
                                    unit?.short_name
                                }
                            />


                            <InfoRow
                                label="Type"
                                value={
                                    unit?.is_system_unit
                                        ? 'System Unit'
                                        : 'Custom Unit'
                                }
                            />


                            <InfoRow
                                label="Status"
                                value={
                                    unit?.status
                                        ? 'Active'
                                        : 'Inactive'
                                }
                            />

                        </div>

                    </div>


                    <div className="rr-unit-show-card">

                        <CardHeader
                            icon="bi-buildings"
                            title="Business Context"
                        />


                        <div className="rr-unit-show-card-body">

                            <InfoRow
                                label="Company"
                                value={
                                    business?.company_name
                                }
                            />


                            <InfoRow
                                label="Company Code"
                                value={
                                    business?.company_code
                                }
                            />


                            <InfoRow
                                label="Branch"
                                value={
                                    business?.store_name
                                }
                            />


                            <InfoRow
                                label="Branch Code"
                                value={
                                    business?.store_code
                                }
                            />

                        </div>

                    </div>


                    <div className="rr-unit-show-card rr-unit-show-card-wide">

                        <CardHeader
                            icon="bi-card-text"
                            title="Description"
                        />


                        <div className="rr-unit-show-card-body">

                            {
                                unit?.description
                                    ? (

                                        <p className="rr-unit-show-description">

                                            {
                                                unit.description
                                            }

                                        </p>

                                    )
                                    : (

                                        <div className="rr-unit-show-empty-text">

                                            <i className="bi bi-info-circle"></i>

                                            No description has been added for this unit.

                                        </div>

                                    )
                            }

                        </div>

                    </div>


                    <div className="rr-unit-show-card rr-unit-show-card-wide">

                        <CardHeader
                            icon="bi-clock-history"
                            title="Record Information"
                        />


                        <div className="rr-unit-show-card-body rr-unit-show-audit-grid">

                            <InfoRow
                                label="Created"
                                value={
                                    formatDateTime(
                                        unit?.created_at
                                    )
                                }
                            />


                            <InfoRow
                                label="Last Updated"
                                value={
                                    formatDateTime(
                                        unit?.updated_at
                                    )
                                }
                            />

                        </div>

                    </div>

                </section>


                {/* =====================================================
                    SYSTEM UNIT NOTICE
                   ===================================================== */}

                {
                    unit?.is_system_unit && (

                        <section className="rr-unit-show-system-banner">

                            <div className="rr-unit-show-system-banner-icon">

                                <i className="bi bi-shield-lock"></i>

                            </div>


                            <div className="rr-unit-show-system-banner-content">

                                <span>
                                    Protected System Unit
                                </span>


                                <strong>
                                    Standard unit identity is protected
                                </strong>


                                <small>
                                    System units cannot be deleted and their unit name or short name should remain unchanged. They may still be activated or deactivated for this branch.
                                </small>

                            </div>

                        </section>

                    )
                }

            </div>

        </BusinessLayout>
    );
}


/*
|--------------------------------------------------------------------------
| Summary Card
|--------------------------------------------------------------------------
*/

function SummaryCard({
    label,
    value,
    icon,
    tone = 'primary',
}) {

    return (

        <div
            className={
                `rr-unit-show-stat ${tone}`
            }
        >

            <div className="rr-unit-show-stat-icon">

                <i
                    className={
                        `bi ${icon}`
                    }
                ></i>

            </div>


            <div>

                <span>
                    {label}
                </span>

                <strong>
                    {value}
                </strong>

            </div>

        </div>
    );
}


/*
|--------------------------------------------------------------------------
| Card Header
|--------------------------------------------------------------------------
*/

function CardHeader({
    icon,
    title,
}) {

    return (

        <div className="rr-unit-show-card-header">

            <div>

                <i
                    className={
                        `bi ${icon}`
                    }
                ></i>

            </div>


            <strong>
                {title}
            </strong>

        </div>
    );
}


/*
|--------------------------------------------------------------------------
| Info Row
|--------------------------------------------------------------------------
*/

function InfoRow({
    label,
    value,
}) {

    return (

        <div className="rr-unit-show-info-row">

            <span>
                {label}
            </span>


            <strong>
                {
                    value
                    ?? '—'
                }
            </strong>

        </div>
    );
}


/*
|--------------------------------------------------------------------------
| Date
|--------------------------------------------------------------------------
*/

function formatDateTime(
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
        return value;
    }


    return date.toLocaleString(
        'en-IN',
        {
            day:
                '2-digit',

            month:
                'short',

            year:
                'numeric',

            hour:
                '2-digit',

            minute:
                '2-digit',
        }
    );
}


/*
|--------------------------------------------------------------------------
| Initials
|--------------------------------------------------------------------------
*/

function getInitials(
    value
) {

    const text =
        String(
            value
            ?? ''
        )
            .trim();


    if (
        !text
    ) {
        return 'UN';
    }


    return text
        .split(
            /\s+/
        )
        .slice(
            0,
            2
        )
        .map(
            part =>
                part
                    .charAt(
                        0
                    )
                    .toUpperCase()
        )
        .join(
            ''
        );
}