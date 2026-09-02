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

import '../../../css/business/brands.css';


export default function Show({
    brand,
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
                '/brands'
            );
        };


    const openProducts =
        () => {

            if (
                !can(
                    'product.view'
                )
            ) {

                return;
            }


            router.visit(
                `/brands/${brand.id}/products`
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
                    brand?.brand_name
                    ?? 'Brand'
                }
            />


            <div className="rr-brand-show-page">

                <MasterNavigation
                    active="brands"
                    title="Brands"
                    subtitle="Product manufacturer and brand master"
                />


                {/* =====================================================
                    HEADER
                   ===================================================== */}

                <section className="rr-brand-show-header">

                    <div className="rr-brand-show-header-main">

                        <button
                            type="button"
                            className="rr-brand-show-back"
                            onClick={
                                goBack
                            }
                            title="Back to brands"
                        >

                            <i className="bi bi-arrow-left"></i>

                        </button>


                        <div className="rr-brand-show-avatar">

                            {
                                getInitials(
                                    brand?.brand_name
                                )
                            }

                        </div>


                        <div className="rr-brand-show-identity">

                            <span className="rr-brand-show-eyebrow">
                                Brand Master
                            </span>


                            <div className="rr-brand-show-title-line">

                                <h1>
                                    {
                                        brand?.brand_name
                                    }
                                </h1>


                                <span
                                    className={
                                        `rr-brand-status ${brand?.status
                                            ? 'active'
                                            : 'inactive'
                                        }`
                                    }
                                >

                                    <span></span>

                                    {
                                        brand?.status
                                            ? 'Active'
                                            : 'Inactive'
                                    }

                                </span>

                            </div>


                            <div className="rr-brand-show-subline">

                                <span>

                                    <i className="bi bi-upc-scan"></i>

                                    {
                                        brand?.brand_code
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


                    <div className="rr-brand-show-header-actions">

                        {
                            can(
                                'product.view'
                            ) && (

                                <button
                                    type="button"
                                    className="rr-brand-show-secondary-btn"
                                    onClick={
                                        openProducts
                                    }
                                >

                                    <i className="bi bi-box-seam"></i>

                                    View Products

                                </button>

                            )
                        }


                        {
                            can(
                                'brand.edit'
                            ) && (

                                <Link
                                    href={
                                        `/brands/${brand.id}/edit`
                                    }
                                    className="rr-brand-primary-btn"
                                >

                                    <i className="bi bi-pencil"></i>

                                    Edit Brand

                                </Link>

                            )
                        }

                    </div>

                </section>


                {/* =====================================================
                    SUMMARY
                   ===================================================== */}

                <section className="rr-brand-show-summary">

                    <BrandStat
                        label="Products"
                        value={
                            brand?.products_count
                            ?? 0
                        }
                        icon="bi-box-seam"
                        tone="primary"
                    />


                    <BrandStat
                        label="Active Products"
                        value={
                            brand?.active_products_count
                            ?? 0
                        }
                        icon="bi-check-circle"
                        tone="success"
                    />


                    <BrandStat
                        label="Inactive Products"
                        value={
                            brand?.inactive_products_count
                            ?? 0
                        }
                        icon="bi-dash-circle"
                        tone="muted"
                    />


                    <BrandStat
                        label="Status"
                        value={
                            brand?.status
                                ? 'Active'
                                : 'Inactive'
                        }
                        icon={
                            brand?.status
                                ? 'bi-toggle-on'
                                : 'bi-toggle-off'
                        }
                        tone={
                            brand?.status
                                ? 'success'
                                : 'muted'
                        }
                    />

                </section>


                {/* =====================================================
                    DETAILS GRID
                   ===================================================== */}

                <section className="rr-brand-show-grid">

                    {/* =================================================
                        BRAND DETAILS
                       ================================================= */}

                    <div className="rr-brand-show-card">

                        <CardHeader
                            icon="bi-tag"
                            title="Brand Details"
                        />


                        <div className="rr-brand-show-card-body">

                            <InfoRow
                                label="Brand Code"
                                value={
                                    brand?.brand_code
                                }
                            />


                            <InfoRow
                                label="Brand Name"
                                value={
                                    brand?.brand_name
                                }
                            />


                            <InfoRow
                                label="Status"
                                value={
                                    brand?.status
                                        ? 'Active'
                                        : 'Inactive'
                                }
                            />


                            <InfoRow
                                label="Products"
                                value={
                                    brand?.products_count
                                    ?? 0
                                }
                            />

                        </div>

                    </div>


                    {/* =================================================
                        BUSINESS CONTEXT
                       ================================================= */}

                    <div className="rr-brand-show-card">

                        <CardHeader
                            icon="bi-buildings"
                            title="Business Context"
                        />


                        <div className="rr-brand-show-card-body">

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


                    {/* =================================================
                        DESCRIPTION
                       ================================================= */}

                    <div className="rr-brand-show-card rr-brand-show-card-wide">

                        <CardHeader
                            icon="bi-card-text"
                            title="Description"
                        />


                        <div className="rr-brand-show-card-body">

                            {
                                brand?.description
                                    ? (

                                        <p className="rr-brand-show-description">

                                            {
                                                brand.description
                                            }

                                        </p>

                                    )
                                    : (

                                        <div className="rr-brand-show-empty-text">

                                            <i className="bi bi-info-circle"></i>

                                            No description has been added for this brand.

                                        </div>

                                    )
                            }

                        </div>

                    </div>


                    {/* =================================================
                        AUDIT
                       ================================================= */}

                    <div className="rr-brand-show-card rr-brand-show-card-wide">

                        <CardHeader
                            icon="bi-clock-history"
                            title="Record Information"
                        />


                        <div className="rr-brand-show-card-body rr-brand-show-audit-grid">

                            <InfoRow
                                label="Created"
                                value={
                                    formatDateTime(
                                        brand?.created_at
                                    )
                                }
                            />


                            <InfoRow
                                label="Last Updated"
                                value={
                                    formatDateTime(
                                        brand?.updated_at
                                    )
                                }
                            />

                        </div>

                    </div>

                </section>


                {/* =====================================================
                    PRODUCT ACTION
                   ===================================================== */}

                {
                    can(
                        'product.view'
                    ) && (

                        <section className="rr-brand-show-products-banner">

                            <div className="rr-brand-show-products-banner-icon">

                                <i className="bi bi-boxes"></i>

                            </div>


                            <div className="rr-brand-show-products-banner-content">

                                <span>
                                    Related Products
                                </span>


                                <strong>

                                    {
                                        Number(
                                            brand?.products_count
                                            ?? 0
                                        )
                                    }

                                    {
                                        Number(
                                            brand?.products_count
                                            ?? 0
                                        ) === 1
                                            ? ' product uses this brand'
                                            : ' products use this brand'
                                    }

                                </strong>


                                <small>
                                    Search, filter, print and export all products assigned to this brand.
                                </small>

                            </div>


                            <button
                                type="button"
                                className="rr-brand-show-products-btn"
                                onClick={
                                    openProducts
                                }
                            >

                                Open Products

                                <i className="bi bi-arrow-right"></i>

                            </button>

                        </section>

                    )
                }

            </div>

        </BusinessLayout>
    );
}


/*
|--------------------------------------------------------------------------
| Stat
|--------------------------------------------------------------------------
*/

function BrandStat({
    label,
    value,
    icon,
    tone = 'primary',
}) {

    return (

        <div
            className={
                `rr-brand-show-stat ${tone}`
            }
        >

            <div className="rr-brand-show-stat-icon">

                <i
                    className={
                        `bi ${icon}`
                    }
                ></i>

            </div>


            <div>

                <span>
                    {
                        label
                    }
                </span>


                <strong>
                    {
                        value
                    }
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

        <div className="rr-brand-show-card-header">

            <div>

                <i
                    className={
                        `bi ${icon}`
                    }
                ></i>

            </div>


            <strong>
                {
                    title
                }
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

        <div className="rr-brand-show-info-row">

            <span>
                {
                    label
                }
            </span>


            <strong>
                {
                    value
                    ??
                    '—'
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

        return 'BR';
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