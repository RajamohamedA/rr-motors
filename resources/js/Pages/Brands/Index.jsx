import {
    Head,
    Link,
    router,
    usePage,
} from '@inertiajs/react';

import {
    useEffect,
    useMemo,
    useState,
} from 'react';

import BusinessLayout
    from '../../Layouts/BusinessLayout';

import MasterNavigation
    from '../../Components/Business/MasterNavigation';

import ConfirmModal
    from '../../Components/ConfirmModal';

import '../../../css/business/brands.css';


export default function Index({
    brands,
    summary,
    filters,
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
    | Filters
    |--------------------------------------------------------------------------
    */

    const [
        search,
        setSearch,
    ] =
        useState(
            filters?.search
            ?? ''
        );


    const [
        status,
        setStatus,
    ] =
        useState(
            filters?.status
            ?? ''
        );


    /*
    |--------------------------------------------------------------------------
    | Confirmation Modal
    |--------------------------------------------------------------------------
    */

    const [
        confirmState,
        setConfirmState,
    ] =
        useState({
            open:
                false,

            brand:
                null,

            action:
                null,
        });


    /*
    |--------------------------------------------------------------------------
    | Apply Filters
    |--------------------------------------------------------------------------
    */

    const applyFilters =
        (
            overrides = {}
        ) => {

            router.get(
                '/brands',
                {
                    search:
                        overrides.search
                        ?? search,

                    status:
                        overrides.status
                        ?? status,
                },
                {
                    preserveScroll:
                        true,

                    preserveState:
                        true,

                    replace:
                        true,
                }
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Search
    |--------------------------------------------------------------------------
    */

    const submitSearch =
        event => {

            event.preventDefault();

            applyFilters();
        };


    /*
    |--------------------------------------------------------------------------
    | Status Auto Filter
    |--------------------------------------------------------------------------
    */

    useEffect(
        () => {

            if (
                status ===
                (
                    filters?.status
                    ?? ''
                )
            ) {

                return;
            }


            const timer =
                window.setTimeout(
                    () => {

                        applyFilters({
                            status,
                        });

                    },
                    100
                );


            return () => {

                window.clearTimeout(
                    timer
                );
            };

        },
        [
            status,
        ]
    );


    /*
    |--------------------------------------------------------------------------
    | Clear Filters
    |--------------------------------------------------------------------------
    */

    const clearFilters =
        () => {

            setSearch(
                ''
            );

            setStatus(
                ''
            );


            router.get(
                '/brands',
                {},
                {
                    preserveScroll:
                        true,

                    preserveState:
                        true,

                    replace:
                        true,
                }
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Rows
    |--------------------------------------------------------------------------
    */

    const rows =
        brands?.data
        ?? [];


    const paginationLinks =
        brands?.links
        ?? [];


    const hasFilters =
        Boolean(
            search ||
            status !== ''
        );


    const pageInfo =
        useMemo(
            () => ({

                from:
                    brands?.from
                    ?? 0,

                to:
                    brands?.to
                    ?? 0,

                total:
                    brands?.total
                    ?? 0,
            }),
            [
                brands?.from,
                brands?.to,
                brands?.total,
            ]
        );


    /*
    |--------------------------------------------------------------------------
    | Row Click
    |--------------------------------------------------------------------------
    */

    const openBrand =
        brand => {

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
    | Status Confirmation
    |--------------------------------------------------------------------------
    */

    const requestStatusChange =
        brand => {

            if (
                !can(
                    'brand.edit'
                )
            ) {

                return;
            }


            setConfirmState({
                open:
                    true,

                brand,

                action:
                    brand.status
                        ? 'deactivate'
                        : 'activate',
            });
        };


    /*
    |--------------------------------------------------------------------------
    | Delete Confirmation
    |--------------------------------------------------------------------------
    */

    const requestDelete =
        brand => {

            if (
                !can(
                    'brand.delete'
                )
            ) {

                return;
            }


            setConfirmState({
                open:
                    true,

                brand,

                action:
                    'delete',
            });
        };


    /*
    |--------------------------------------------------------------------------
    | Close Confirmation
    |--------------------------------------------------------------------------
    */

    const closeConfirm =
        () => {

            setConfirmState({
                open:
                    false,

                brand:
                    null,

                action:
                    null,
            });
        };


    /*
    |--------------------------------------------------------------------------
    | Execute Confirmation
    |--------------------------------------------------------------------------
    */

    const confirmAction =
        () => {

            const brand =
                confirmState.brand;


            if (
                !brand
            ) {

                closeConfirm();

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Delete
            |--------------------------------------------------------------------------
            */

            if (
                confirmState.action ===
                'delete'
            ) {

                router.delete(
                    `/brands/${brand.id}`,
                    {
                        preserveScroll:
                            true,

                        onFinish:
                            closeConfirm,
                    }
                );


                return;
            }


            /*
            |--------------------------------------------------------------------------
            | Activate / Deactivate
            |--------------------------------------------------------------------------
            */

            router.patch(
                `/brands/${brand.id}/toggle-status`,
                {},
                {
                    preserveScroll:
                        true,

                    onFinish:
                        closeConfirm,
                }
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Confirmation Content
    |--------------------------------------------------------------------------
    */

    const confirmContent =
        useMemo(
            () => {

                const brand =
                    confirmState.brand;


                if (
                    !brand
                ) {

                    return {
                        title:
                            '',
                        message:
                            '',
                        confirmText:
                            'Confirm',
                        tone:
                            'warning',
                    };
                }


                if (
                    confirmState.action ===
                    'delete'
                ) {

                    return {
                        title:
                            'Delete Brand?',

                        message:
                            `Delete "${brand.brand_name}"? This action is only allowed when no products are using this brand.`,

                        confirmText:
                            'Delete Brand',

                        tone:
                            'danger',
                    };
                }


                if (
                    confirmState.action ===
                    'deactivate'
                ) {

                    return {
                        title:
                            'Deactivate Brand?',

                        message:
                            `Deactivate "${brand.brand_name}"? Existing product records remain unchanged.`,

                        confirmText:
                            'Deactivate',

                        tone:
                            'warning',
                    };
                }


                return {
                    title:
                        'Activate Brand?',

                    message:
                        `Activate "${brand.brand_name}" for the current branch?`,

                    confirmText:
                        'Activate',

                    tone:
                        'success',
                };
            },
            [
                confirmState,
            ]
        );


    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */

    return (

        <BusinessLayout>

            <Head
                title="Brands"
            />


            <div className="rr-brands-page">

                <MasterNavigation
                    active="brands"
                    title="Brands"
                    subtitle="Product manufacturer and brand master"
                />


                {/* =====================================================
                    HEADER
                   ===================================================== */}

                <section className="rr-brands-header">

                    <div className="rr-brands-header-main">

                        <div className="rr-brands-header-icon">

                            <i className="bi bi-tags"></i>

                        </div>


                        <div>

                            <span className="rr-brands-eyebrow">
                                Product Master
                            </span>


                            <h1>
                                Brands
                            </h1>


                            <p>
                                Manage brands available to {
                                    business?.store_name
                                    ?? 'the active branch'
                                }.
                            </p>

                        </div>

                    </div>


                    <div className="rr-brands-header-actions">

                        <button
                            type="button"
                            className="rr-brand-toolbar-btn"
                            onClick={
                                () =>
                                    router.reload({
                                        preserveScroll:
                                            true,
                                    })
                            }
                        >

                            <i className="bi bi-arrow-clockwise"></i>

                            Refresh

                        </button>


                        {
                            can(
                                'brand.create'
                            ) && (

                                <Link
                                    href="/brands/create"
                                    className="rr-brand-primary-btn"
                                >

                                    <i className="bi bi-plus-lg"></i>

                                    Add Brand

                                </Link>

                            )
                        }

                    </div>

                </section>


                {/* =====================================================
                    SUMMARY
                   ===================================================== */}

                <section className="rr-brands-summary">

                    <SummaryCard
                        label="Total Brands"
                        value={
                            summary?.total
                            ?? 0
                        }
                        icon="bi-tags"
                    />


                    <SummaryCard
                        label="Active"
                        value={
                            summary?.active
                            ?? 0
                        }
                        icon="bi-check-circle"
                        tone="success"
                    />


                    <SummaryCard
                        label="Inactive"
                        value={
                            summary?.inactive
                            ?? 0
                        }
                        icon="bi-dash-circle"
                        tone="muted"
                    />


                    <SummaryCard
                        label="With Products"
                        value={
                            summary?.with_products
                            ?? 0
                        }
                        icon="bi-box-seam"
                        tone="warning"
                    />

                </section>


                {/* =====================================================
                    PANEL
                   ===================================================== */}

                <section className="rr-brands-panel">

                    {/* =================================================
                        TOOLBAR
                       ================================================= */}

                    <div className="rr-brands-toolbar">

                        <form
                            className="rr-brands-search"
                            onSubmit={
                                submitSearch
                            }
                        >

                            <div className="rr-brands-search-box">

                                <i className="bi bi-search"></i>


                                <input
                                    type="text"
                                    value={
                                        search
                                    }
                                    placeholder="Search brand name, code or description..."
                                    onChange={
                                        event =>
                                            setSearch(
                                                event.target.value
                                            )
                                    }
                                />


                                {
                                    search && (

                                        <button
                                            type="button"
                                            title="Clear search"
                                            onClick={
                                                () => {

                                                    setSearch(
                                                        ''
                                                    );


                                                    applyFilters({
                                                        search:
                                                            '',
                                                    });
                                                }
                                            }
                                        >

                                            <i className="bi bi-x-lg"></i>

                                        </button>

                                    )
                                }

                            </div>


                            <button
                                type="submit"
                                className="rr-brand-search-btn"
                            >

                                Search

                            </button>

                        </form>


                        <div className="rr-brands-filter-group">

                            <div className="rr-brands-filter">

                                <label>
                                    Status
                                </label>


                                <select
                                    value={
                                        status
                                    }
                                    onChange={
                                        event =>
                                            setStatus(
                                                event.target.value
                                            )
                                    }
                                >

                                    <option value="">
                                        All
                                    </option>

                                    <option value="1">
                                        Active
                                    </option>

                                    <option value="0">
                                        Inactive
                                    </option>

                                </select>

                            </div>


                            {
                                hasFilters && (

                                    <button
                                        type="button"
                                        className="rr-brands-clear-btn"
                                        onClick={
                                            clearFilters
                                        }
                                    >

                                        <i className="bi bi-funnel"></i>

                                        Clear

                                    </button>

                                )
                            }

                        </div>

                    </div>


                    {/* =================================================
                        STATUS
                       ================================================= */}

                    <div className="rr-brands-grid-status">

                        <div>

                            <i className="bi bi-table"></i>

                            <span>
                                Showing
                            </span>

                            <strong>
                                {
                                    pageInfo.from
                                }–{
                                    pageInfo.to
                                }
                            </strong>

                            <span>
                                of
                            </span>

                            <strong>
                                {
                                    pageInfo.total
                                }
                            </strong>

                            <span>
                                brands
                            </span>

                        </div>


                        <span className="rr-brands-row-limit">

                            <i className="bi bi-layout-three-columns"></i>

                            15 rows per page

                        </span>

                    </div>


                    {/* =================================================
                        TABLE
                       ================================================= */}

                    <div className="rr-brands-table-wrap">

                        <table className="rr-brands-table">

                            <thead>

                                <tr>

                                    <th className="rr-brand-col-code">
                                        Code
                                    </th>

                                    <th>
                                        Brand
                                    </th>

                                    <th>
                                        Description
                                    </th>

                                    <th className="rr-brand-col-products">
                                        Products
                                    </th>

                                    <th className="rr-brand-col-status">
                                        Status
                                    </th>

                                    <th className="rr-brand-col-actions">
                                        Actions
                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                                {
                                    rows.length > 0
                                        ? (

                                            rows.map(
                                                brand => (

                                                    <BrandRow
                                                        key={
                                                            brand.id
                                                        }

                                                        brand={
                                                            brand
                                                        }

                                                        can={
                                                            can
                                                        }

                                                        onOpen={
                                                            () =>
                                                                openBrand(
                                                                    brand
                                                                )
                                                        }

                                                        onStatusChange={
                                                            () =>
                                                                requestStatusChange(
                                                                    brand
                                                                )
                                                        }

                                                        onDelete={
                                                            () =>
                                                                requestDelete(
                                                                    brand
                                                                )
                                                        }
                                                    />

                                                )
                                            )

                                        )
                                        : (

                                            <tr>

                                                <td
                                                    colSpan="6"
                                                >

                                                    <EmptyState
                                                        filtered={
                                                            hasFilters
                                                        }

                                                        canCreate={
                                                            can(
                                                                'brand.create'
                                                            )
                                                        }
                                                    />

                                                </td>

                                            </tr>

                                        )
                                }

                            </tbody>

                        </table>

                    </div>


                    {/* =================================================
                        PAGINATION
                       ================================================= */}

                    {
                        pageInfo.total > 0 && (

                            <div className="rr-brands-pagination">

                                <div className="rr-brands-pagination-info">

                                    Page

                                    <strong>
                                        {
                                            brands?.current_page
                                            ?? 1
                                        }
                                    </strong>

                                    of

                                    <strong>
                                        {
                                            brands?.last_page
                                            ?? 1
                                        }
                                    </strong>

                                </div>


                                <div className="rr-brands-pagination-links">

                                    {
                                        paginationLinks.map(
                                            (
                                                link,
                                                index
                                            ) => (

                                                <PaginationLink
                                                    key={
                                                        `${link.label}-${index}`
                                                    }

                                                    link={
                                                        link
                                                    }
                                                />

                                            )
                                        )
                                    }

                                </div>

                            </div>

                        )
                    }

                </section>


                {/* =====================================================
                    CONFIRM MODAL
                   ===================================================== */}

                <ConfirmModal
                    show={
                        confirmState.open
                    }

                    title={
                        confirmContent.title
                    }

                    message={
                        confirmContent.message
                    }

                    confirmText={
                        confirmContent.confirmText
                    }

                    variant={
                        confirmContent.tone
                    }

                    onConfirm={
                        confirmAction
                    }

                    onCancel={
                        closeConfirm
                    }
                />

            </div>

        </BusinessLayout>
    );
}


/*
|--------------------------------------------------------------------------
| Brand Row
|--------------------------------------------------------------------------
*/

function BrandRow({
    brand,
    can,
    onOpen,
    onStatusChange,
    onDelete,
}) {

    const canOpen =
        can(
            'product.view'
        );


    return (

        <tr
            className={
                canOpen
                    ? 'is-clickable'
                    : ''
            }
            onClick={
                canOpen
                    ? onOpen
                    : undefined
            }
        >

            <td>

                <div className="rr-brand-code">

                    {
                        brand.brand_code
                        ?? '—'
                    }

                </div>

            </td>


            <td>

                <div className="rr-brand-name-cell">

                    <div className="rr-brand-avatar">

                        {
                            getInitials(
                                brand.brand_name
                            )
                        }

                    </div>


                    <div>

                        <strong>
                            {
                                brand.brand_name
                            }
                        </strong>


                        <span>
                            Product Brand
                        </span>

                    </div>

                </div>

            </td>


            <td>

                <div className="rr-brand-description">

                    {
                        brand.description
                        ?? 'No description'
                    }

                </div>

            </td>


            <td>

                <div className="rr-brand-product-count">

                    <strong>
                        {
                            brand.products_count
                            ?? 0
                        }
                    </strong>

                    <span>
                        products
                    </span>

                </div>

            </td>


            <td>

                <span
                    className={
                        `rr-brand-status ${brand.status
                            ? 'active'
                            : 'inactive'
                        }`
                    }
                >

                    <span></span>

                    {
                        brand.status
                            ? 'Active'
                            : 'Inactive'
                    }

                </span>

            </td>


            <td
                onClick={
                    event =>
                        event.stopPropagation()
                }
            >

                <div className="rr-brand-actions">

                    {
                        can(
                            'product.view'
                        ) && (

                            <Link
                                href={
                                    `/brands/${brand.id}`
                                }
                                className="rr-brand-action-btn view"
                                title="View brand"
                            >

                                <i className="bi bi-eye"></i>

                            </Link>

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
                                className="rr-brand-action-btn edit"
                                title="Edit brand"
                            >

                                <i className="bi bi-pencil"></i>

                            </Link>

                        )
                    }


                    {
                        can(
                            'brand.edit'
                        ) && (

                            <button
                                type="button"
                                className={
                                    `rr-brand-action-btn ${brand.status
                                        ? 'deactivate'
                                        : 'activate'
                                    }`
                                }
                                title={
                                    brand.status
                                        ? 'Deactivate brand'
                                        : 'Activate brand'
                                }
                                onClick={
                                    onStatusChange
                                }
                            >

                                <i
                                    className={
                                        brand.status
                                            ? 'bi bi-toggle-on'
                                            : 'bi bi-toggle-off'
                                    }
                                ></i>

                            </button>

                        )
                    }


                    {
                        can(
                            'brand.delete'
                        )
                        &&
                        Number(
                            brand.products_count
                            ?? 0
                        ) === 0 && (

                            <button
                                type="button"
                                className="rr-brand-action-btn delete"
                                title="Delete brand"
                                onClick={
                                    onDelete
                                }
                            >

                                <i className="bi bi-trash"></i>

                            </button>

                        )
                    }

                </div>

            </td>

        </tr>
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
                `rr-brand-summary-card ${tone}`
            }
        >

            <div className="rr-brand-summary-icon">

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
| Pagination
|--------------------------------------------------------------------------
*/

function PaginationLink({
    link,
}) {

    const label =
        String(
            link?.label
            ?? ''
        )
            .replace(
                '&laquo;',
                '«'
            )
            .replace(
                '&raquo;',
                '»'
            );


    if (
        !link?.url
    ) {

        return (

            <span className="rr-brand-page-link disabled">

                {
                    label
                }

            </span>
        );
    }


    return (

        <Link
            href={
                link.url
            }
            preserveScroll
            preserveState
            className={
                `rr-brand-page-link ${link.active
                    ? 'active'
                    : ''
                }`
            }
        >

            {
                label
            }

        </Link>
    );
}


/*
|--------------------------------------------------------------------------
| Empty State
|--------------------------------------------------------------------------
*/

function EmptyState({
    filtered,
    canCreate,
}) {

    return (

        <div className="rr-brands-empty">

            <div className="rr-brands-empty-icon">

                <i className="bi bi-tags"></i>

            </div>


            <strong>

                {
                    filtered
                        ? 'No brands match your filters'
                        : 'No brands in this branch'
                }

            </strong>


            <span>

                {
                    filtered
                        ? 'Change the search or status filter and try again.'
                        : 'Add a brand to begin organizing products in this branch.'
                }

            </span>


            {
                canCreate &&
                !filtered && (

                    <Link
                        href="/brands/create"
                        className="rr-brand-primary-btn"
                    >

                        <i className="bi bi-plus-lg"></i>

                        Add Brand

                    </Link>

                )
            }

        </div>
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