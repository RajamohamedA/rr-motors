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

import '../../../css/business/godowns.css';


export default function Show({
    godown,
}) {

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
                isSuperAdmin
                ||
                isCompanyAdmin
            ) {
                return true;
            }

            return permissions.includes(
                permission
            );
        };


    const goBack =
        () => {

            router.visit(
                '/godowns'
            );
        };


    return (

        <BusinessLayout>

            <Head
                title={
                    godown?.godown_name
                    ?? 'Godown'
                }
            />


            <div className="rr-godown-show-page">

                <MasterNavigation
                    active="godowns"
                    title="Godowns"
                    subtitle="Warehouse and branch stock locations"
                />


                {/* =====================================================
                    HEADER
                   ===================================================== */}

                <section className="rr-godown-show-header">

                    <div className="rr-godown-show-header-main">

                        <button
                            type="button"
                            className="rr-godown-show-back"
                            onClick={
                                goBack
                            }
                            title="Back to Godowns"
                        >

                            <i className="bi bi-arrow-left"></i>

                        </button>


                        <div className="rr-godown-show-avatar">

                            {
                                getInitials(
                                    godown?.godown_name
                                )
                            }

                        </div>


                        <div className="rr-godown-show-identity">

                            <span className="rr-godown-show-eyebrow">
                                Stock Location
                            </span>


                            <div className="rr-godown-show-title-line">

                                <h1>
                                    {
                                        godown?.godown_name
                                    }
                                </h1>


                                <span
                                    className={
                                        `rr-godown-status ${godown?.status
                                            ? 'active'
                                            : 'inactive'
                                        }`
                                    }
                                >

                                    <span></span>

                                    {
                                        godown?.status
                                            ? 'Active'
                                            : 'Inactive'
                                    }

                                </span>

                            </div>


                            <div className="rr-godown-show-subline">

                                <span>

                                    <i className="bi bi-upc-scan"></i>

                                    {
                                        godown?.godown_code
                                        ?? 'No Code'
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


                    <div className="rr-godown-show-header-actions">

                        {
                            can(
                                'godown.view'
                            ) && (

                                <Link
                                    href={
                                        `/godowns/${godown.id}/products`
                                    }
                                    className="rr-godown-inventory-btn"
                                >

                                    <i className="bi bi-box-seam"></i>

                                    View Inventory

                                </Link>

                            )
                        }


                        {
                            can(
                                'godown.edit'
                            ) && (

                                <Link
                                    href={
                                        `/godowns/${godown.id}/edit`
                                    }
                                    className="rr-godown-primary-btn"
                                >

                                    <i className="bi bi-pencil"></i>

                                    Edit Godown

                                </Link>

                            )
                        }

                    </div>

                </section>


                {/* =====================================================
                    INVENTORY SUMMARY
                   ===================================================== */}

                <section className="rr-godown-show-summary">

                    <SummaryCard
                        label="Products"
                        value={
                            formatNumber(
                                godown?.products_count
                            )
                        }
                        icon="bi-box-seam"
                    />


                    <SummaryCard
                        label="Stock Quantity"
                        value={
                            formatQty(
                                godown?.stock_quantity
                            )
                        }
                        icon="bi-stack"
                    />


                    <SummaryCard
                        label="Estimated Cost"
                        value={
                            formatMoney(
                                godown?.estimated_cost
                            )
                        }
                        icon="bi-currency-rupee"
                        tone="cost"
                    />


                    <SummaryCard
                        label="Selling Value"
                        value={
                            formatMoney(
                                godown?.estimated_selling_value
                            )
                        }
                        icon="bi-graph-up-arrow"
                        tone="success"
                    />


                    <SummaryCard
                        label="Potential Margin"
                        value={
                            formatMoney(
                                godown?.potential_margin
                            )
                        }
                        icon="bi-bar-chart-line"
                        tone="margin"
                    />


                    <SummaryCard
                        label="Low Stock"
                        value={
                            formatNumber(
                                godown?.low_stock_count
                            )
                        }
                        icon="bi-exclamation-triangle"
                        tone={
                            Number(
                                godown?.low_stock_count
                                ?? 0
                            ) > 0
                                ? 'warning'
                                : 'muted'
                        }
                    />

                </section>


                {/* =====================================================
                    DETAILS
                   ===================================================== */}

                <section className="rr-godown-show-grid">

                    <div className="rr-godown-show-card">

                        <CardHeader
                            icon="bi-building"
                            title="Godown Details"
                        />


                        <div className="rr-godown-show-card-body">

                            <InfoRow
                                label="Godown Name"
                                value={
                                    godown?.godown_name
                                }
                            />


                            <InfoRow
                                label="Godown Code"
                                value={
                                    godown?.godown_code
                                }
                            />


                            <InfoRow
                                label="Status"
                                value={
                                    godown?.status
                                        ? 'Active'
                                        : 'Inactive'
                                }
                            />

                        </div>

                    </div>


                    <div className="rr-godown-show-card">

                        <CardHeader
                            icon="bi-buildings"
                            title="Business Context"
                        />


                        <div className="rr-godown-show-card-body">

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


                    <div className="rr-godown-show-card rr-godown-show-card-wide">

                        <CardHeader
                            icon="bi-geo-alt"
                            title="Location"
                        />


                        <div className="rr-godown-show-card-body">

                            {
                                godown?.address
                                    ? (

                                        <p className="rr-godown-show-description">

                                            {
                                                godown.address
                                            }

                                        </p>

                                    )
                                    : (

                                        <div className="rr-godown-show-empty-text">

                                            <i className="bi bi-info-circle"></i>

                                            No address or location information has been added.

                                        </div>

                                    )
                            }

                        </div>

                    </div>


                    <div className="rr-godown-show-card rr-godown-show-card-wide">

                        <CardHeader
                            icon="bi-clock-history"
                            title="Record Information"
                        />


                        <div className="rr-godown-show-card-body rr-godown-show-audit-grid">

                            <InfoRow
                                label="Created"
                                value={
                                    formatDateTime(
                                        godown?.created_at
                                    )
                                }
                            />


                            <InfoRow
                                label="Last Updated"
                                value={
                                    formatDateTime(
                                        godown?.updated_at
                                    )
                                }
                            />

                        </div>

                    </div>

                </section>


                {/* =====================================================
                    INVENTORY BANNER
                   ===================================================== */}

                <section className="rr-godown-show-inventory-banner">

                    <div className="rr-godown-show-inventory-banner-icon">

                        <i className="bi bi-boxes"></i>

                    </div>


                    <div className="rr-godown-show-inventory-banner-content">

                        <span>
                            Godown Inventory
                        </span>


                        <strong>
                            View products, stock quantities and estimated inventory value
                        </strong>


                        <small>
                            Filter this Godown by category, brand, stock status, and product. You can also print or download the filtered inventory report.
                        </small>

                    </div>


                    {
                        can(
                            'godown.view'
                        ) && (

                            <Link
                                href={
                                    `/godowns/${godown.id}/products`
                                }
                                className="rr-godown-show-inventory-banner-btn"
                            >

                                <i className="bi bi-arrow-right"></i>

                                Open Inventory

                            </Link>

                        )
                    }

                </section>

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
                `rr-godown-show-stat ${tone}`
            }
        >

            <div className="rr-godown-show-stat-icon">

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

        <div className="rr-godown-show-card-header">

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
| Information Row
|--------------------------------------------------------------------------
*/

function InfoRow({
    label,
    value,
}) {

    return (

        <div className="rr-godown-show-info-row">

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
| Money
|--------------------------------------------------------------------------
*/

function formatMoney(
    value
) {

    const number =
        Number(
            value
            ?? 0
        );


    if (
        Number.isNaN(
            number
        )
    ) {
        return '₹0.00';
    }


    return new Intl.NumberFormat(
        'en-IN',
        {
            style:
                'currency',

            currency:
                'INR',

            minimumFractionDigits:
                2,

            maximumFractionDigits:
                2,
        }
    ).format(
        number
    );
}


/*
|--------------------------------------------------------------------------
| Quantity
|--------------------------------------------------------------------------
*/

function formatQty(
    value
) {

    const number =
        Number(
            value
            ?? 0
        );


    if (
        Number.isNaN(
            number
        )
    ) {
        return '0';
    }


    return new Intl.NumberFormat(
        'en-IN',
        {
            maximumFractionDigits:
                3,
        }
    ).format(
        number
    );
}


/*
|--------------------------------------------------------------------------
| Number
|--------------------------------------------------------------------------
*/

function formatNumber(
    value
) {

    const number =
        Number(
            value
            ?? 0
        );


    if (
        Number.isNaN(
            number
        )
    ) {
        return '0';
    }


    return new Intl.NumberFormat(
        'en-IN'
    ).format(
        number
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
        return 'GD';
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