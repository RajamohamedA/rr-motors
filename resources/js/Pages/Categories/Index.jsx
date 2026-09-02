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

import '../../../css/business/categories.css';


export default function Index({
    categories,
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
        ?? auth?.permissions
        ?? [];


    const roles =
        user?.roles
        ?? auth?.roles
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
    | Delete Confirmation
    |--------------------------------------------------------------------------
    */

    const [
        selectedCategory,
        setSelectedCategory,
    ] =
        useState(
            null
        );


    const [
        deleteProcessing,
        setDeleteProcessing,
    ] =
        useState(
            false
        );


    /*
    |--------------------------------------------------------------------------
    | Status Confirmation
    |--------------------------------------------------------------------------
    */

    const [
        statusCategory,
        setStatusCategory,
    ] =
        useState(
            null
        );


    const [
        statusProcessing,
        setStatusProcessing,
    ] =
        useState(
            false
        );


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
                '/categories',
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
                '/categories',
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
    | Automatic Status Filter
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
                    150
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
    | Open Related Products
    |--------------------------------------------------------------------------
    */

    const openCategoryProducts =
        category => {

            if (
                !can(
                    'product.view'
                )
            ) {

                return;
            }


            router.visit(
                `/categories/${category.id}/products`
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Delete
    |--------------------------------------------------------------------------
    */

    const requestDelete =
        category => {

            if (
                !can(
                    'category.delete'
                )
            ) {

                return;
            }


            setSelectedCategory(
                category
            );
        };


    const closeDelete =
        () => {

            if (
                deleteProcessing
            ) {

                return;
            }


            setSelectedCategory(
                null
            );
        };


    const confirmDelete =
        () => {

            if (
                !selectedCategory ||
                deleteProcessing
            ) {

                return;
            }


            setDeleteProcessing(
                true
            );


            router.delete(
                `/categories/${selectedCategory.id}`,
                {
                    preserveScroll:
                        true,

                    onSuccess:
                        () => {

                            setSelectedCategory(
                                null
                            );


                            window.scrollTo({
                                top:
                                    0,

                                behavior:
                                    'smooth',
                            });
                        },

                    onError:
                        () => {

                            window.scrollTo({
                                top:
                                    0,

                                behavior:
                                    'smooth',
                            });
                        },

                    onFinish:
                        () => {

                            setDeleteProcessing(
                                false
                            );
                        },
                }
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Activate / Deactivate
    |--------------------------------------------------------------------------
    */

    const requestStatusChange =
        category => {

            if (
                !can(
                    'category.edit'
                )
            ) {

                return;
            }


            setStatusCategory(
                category
            );
        };


    const closeStatusChange =
        () => {

            if (
                statusProcessing
            ) {

                return;
            }


            setStatusCategory(
                null
            );
        };


    const confirmStatusChange =
        () => {

            if (
                !statusCategory ||
                statusProcessing
            ) {

                return;
            }


            setStatusProcessing(
                true
            );


            router.patch(
                `/categories/${statusCategory.id}/toggle-status`,
                {},
                {
                    preserveScroll:
                        true,

                    onSuccess:
                        () => {

                            setStatusCategory(
                                null
                            );


                            window.scrollTo({
                                top:
                                    0,

                                behavior:
                                    'smooth',
                            });
                        },

                    onError:
                        () => {

                            window.scrollTo({
                                top:
                                    0,

                                behavior:
                                    'smooth',
                            });
                        },

                    onFinish:
                        () => {

                            setStatusProcessing(
                                false
                            );
                        },
                }
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Data
    |--------------------------------------------------------------------------
    */

    const rows =
        categories?.data
        ?? [];


    const paginationLinks =
        categories?.links
        ?? [];


    const hasFilters =
        Boolean(
            search ||
            status !== ''
        );


    const pageInfo =
        useMemo(
            () => {

                return {

                    from:
                        categories?.from
                        ?? 0,

                    to:
                        categories?.to
                        ?? 0,

                    total:
                        categories?.total
                        ?? 0,
                };

            },
            [
                categories?.from,
                categories?.to,
                categories?.total,
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
                title="Categories"
            />


            <div className="rr-categories-page">

                <MasterNavigation
                    active="categories"
                    title="Categories"
                    subtitle="Branch product category management"
                />


                {/* =====================================================
                    HEADER
                   ===================================================== */}

                <section className="rr-categories-header">

                    <div className="rr-categories-header-main">

                        <div className="rr-categories-header-icon">

                            <i className="bi bi-grid"></i>

                        </div>


                        <div>

                            <span className="rr-categories-eyebrow">
                                Product Master
                            </span>


                            <h1>
                                Categories
                            </h1>


                            <p>
                                Manage product categories available to{' '}

                                <strong>
                                    {
                                        business?.store_name
                                        ?? 'the active branch'
                                    }
                                </strong>.
                            </p>

                        </div>

                    </div>


                    <div className="rr-categories-header-actions">

                        <button
                            type="button"
                            className="rr-category-toolbar-btn"
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
                                'category.create'
                            ) && (

                                <Link
                                    href="/categories/create"
                                    className="rr-category-primary-btn"
                                >

                                    <i className="bi bi-plus-lg"></i>

                                    Add Category

                                </Link>

                            )
                        }

                    </div>

                </section>


                {/* =====================================================
                    BRANCH INFORMATION
                   ===================================================== */}

                <section className="rr-category-branch-context">

                    <div>

                        <i className="bi bi-shop"></i>


                        <div>

                            <span>
                                Category Scope
                            </span>

                            <strong>
                                {
                                    business?.store_name
                                    ?? 'Active Branch'
                                }
                            </strong>

                        </div>

                    </div>


                    <p>
                        Categories are branch-specific. Categories created
                        here are available only to the currently selected
                        branch.
                    </p>

                </section>


                {/* =====================================================
                    SUMMARY
                   ===================================================== */}

                <section className="rr-categories-summary">

                    <SummaryCard
                        label="Total Categories"
                        value={
                            summary?.total
                            ?? 0
                        }
                        icon="bi-grid"
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
                        label="Used by Products"
                        value={
                            summary?.with_products
                            ?? 0
                        }
                        icon="bi-box-seam"
                        tone="warning"
                    />

                </section>


                {/* =====================================================
                    MAIN PANEL
                   ===================================================== */}

                <section className="rr-categories-panel">

                    {/* =================================================
                        TOOLBAR
                       ================================================= */}

                    <div className="rr-categories-toolbar">

                        <form
                            className="rr-categories-search"
                            onSubmit={
                                submitSearch
                            }
                        >

                            <div className="rr-categories-search-box">

                                <i className="bi bi-search"></i>


                                <input
                                    type="text"
                                    value={
                                        search
                                    }
                                    placeholder="Search category name, code or description..."
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
                                className="rr-category-search-btn"
                            >

                                Search

                            </button>

                        </form>


                        <div className="rr-categories-filter-group">

                            <div className="rr-categories-filter">

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
                                        className="rr-categories-clear-btn"
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
                        TABLE INFORMATION
                       ================================================= */}

                    <div className="rr-categories-grid-status">

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
                                categories
                            </span>

                        </div>


                        <span className="rr-categories-row-limit">

                            <i className="bi bi-layout-three-columns"></i>

                            15 rows per page

                        </span>

                    </div>


                    {/* =================================================
                        TABLE
                       ================================================= */}

                    <div className="rr-categories-table-wrap">

                        <table className="rr-categories-table">

                            <thead>

                                <tr>

                                    <th className="rr-category-col-code">
                                        Code
                                    </th>

                                    <th>
                                        Category
                                    </th>

                                    <th>
                                        Description
                                    </th>

                                    <th className="rr-category-col-products">
                                        Products
                                    </th>

                                    <th className="rr-category-col-status">
                                        Status
                                    </th>

                                    <th className="rr-category-col-actions">
                                        Actions
                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                                {
                                    rows.length > 0
                                        ? rows.map(
                                            category => (

                                                <CategoryRow
                                                    key={
                                                        category.id
                                                    }
                                                    category={
                                                        category
                                                    }
                                                    can={
                                                        can
                                                    }
                                                    onOpen={
                                                        () =>
                                                            openCategoryProducts(
                                                                category
                                                            )
                                                    }
                                                    onDelete={
                                                        () =>
                                                            requestDelete(
                                                                category
                                                            )
                                                    }
                                                    onStatusChange={
                                                        () =>
                                                            requestStatusChange(
                                                                category
                                                            )
                                                    }
                                                />

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
                                                                'category.create'
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

                            <div className="rr-categories-pagination">

                                <div className="rr-categories-pagination-info">

                                    Page{' '}

                                    <strong>
                                        {
                                            categories?.current_page
                                            ?? 1
                                        }
                                    </strong>

                                    {' '}of{' '}

                                    <strong>
                                        {
                                            categories?.last_page
                                            ?? 1
                                        }
                                    </strong>

                                </div>


                                <div className="rr-categories-pagination-links">

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
                    COMMON CONFIRMATION MODAL
                   ===================================================== */}

                <ConfirmModal
                    show={
                        Boolean(
                            selectedCategory
                        )
                    }
                    title="Delete Category?"
                    message={
                        selectedCategory
                            ? `Are you sure you want to delete "${selectedCategory.name}"?`
                            : ''
                    }
                    confirmText="Delete Category"
                    cancelText="Cancel"
                    icon="bi-trash3"
                    variant="danger"
                    processing={
                        deleteProcessing
                    }
                    onConfirm={
                        confirmDelete
                    }
                    onCancel={
                        closeDelete
                    }
                />


                <ConfirmModal
                    show={
                        Boolean(
                            statusCategory
                        )
                    }
                    title={
                        statusCategory?.status
                            ? 'Deactivate Category?'
                            : 'Activate Category?'
                    }
                    message={
                        statusCategory
                            ? (
                                statusCategory.status
                                    ? `Are you sure you want to deactivate "${statusCategory.name}"? It will no longer be available for new product selection in this branch.`
                                    : `Are you sure you want to activate "${statusCategory.name}"? It will become available for product selection in this branch.`
                            )
                            : ''
                    }
                    confirmText={
                        statusCategory?.status
                            ? 'Deactivate Category'
                            : 'Activate Category'
                    }
                    cancelText="Cancel"
                    icon={
                        statusCategory?.status
                            ? 'bi-pause-circle'
                            : 'bi-play-circle'
                    }
                    variant={
                        statusCategory?.status
                            ? 'danger'
                            : 'primary'
                    }
                    processing={
                        statusProcessing
                    }
                    onConfirm={
                        confirmStatusChange
                    }
                    onCancel={
                        closeStatusChange
                    }
                />

            </div>

        </BusinessLayout>
    );
}


/*
|--------------------------------------------------------------------------
| Category Row
|--------------------------------------------------------------------------
*/

function CategoryRow({
    category,
    can,
    onOpen,
    onDelete,
    onStatusChange,
}) {

    const productsCount =
        Number(
            category.products_count
            ?? 0
        );


    const clickable =
        can(
            'product.view'
        );


    return (

        <tr
            className={
                clickable
                    ? 'is-clickable'
                    : ''
            }
            onClick={
                clickable
                    ? onOpen
                    : undefined
            }
        >

            {/* =========================================================
                CODE
               ========================================================= */}

            <td>

                <span className="rr-category-code">

                    {
                        category.code
                        ?? '—'
                    }

                </span>

            </td>


            {/* =========================================================
                CATEGORY
               ========================================================= */}

            <td>

                <div className="rr-category-name-cell">

                    <div className="rr-category-avatar">

                        {
                            getInitials(
                                category.name
                            )
                        }

                    </div>


                    <div>

                        <strong>
                            {
                                category.name
                            }
                        </strong>


                        <span>
                            Product Category
                        </span>

                    </div>

                </div>

            </td>


            {/* =========================================================
                DESCRIPTION
               ========================================================= */}

            <td>

                <span className="rr-category-description">

                    {
                        category.description
                        || 'No description'
                    }

                </span>

            </td>


            {/* =========================================================
                PRODUCTS
               ========================================================= */}

            <td>

                {
                    can(
                        'product.view'
                    )
                        ? (

                            <Link
                                href={
                                    `/categories/${category.id}/products`
                                }
                                className={
                                    `rr-category-product-count ${productsCount > 0
                                        ? 'has-products'
                                        : ''
                                    }`
                                }
                                title="View related products"
                                onClick={
                                    event =>
                                        event.stopPropagation()
                                }
                            >

                                <i className="bi bi-box-seam"></i>


                                <strong>
                                    {
                                        productsCount
                                    }
                                </strong>


                                <span>
                                    {
                                        productsCount === 1
                                            ? 'product'
                                            : 'products'
                                    }
                                </span>

                            </Link>

                        )
                        : (

                            <div
                                className={
                                    `rr-category-product-count ${productsCount > 0
                                        ? 'has-products'
                                        : ''
                                    }`
                                }
                            >

                                <i className="bi bi-box-seam"></i>


                                <strong>
                                    {
                                        productsCount
                                    }
                                </strong>


                                <span>
                                    {
                                        productsCount === 1
                                            ? 'product'
                                            : 'products'
                                    }
                                </span>

                            </div>

                        )
                }

            </td>


            {/* =========================================================
                STATUS
               ========================================================= */}

            <td>

                <span
                    className={
                        `rr-category-status ${category.status
                            ? 'active'
                            : 'inactive'
                        }`
                    }
                >

                    <span></span>


                    {
                        category.status
                            ? 'Active'
                            : 'Inactive'
                    }

                </span>

            </td>


            {/* =========================================================
                ACTIONS
               ========================================================= */}

            <td
                onClick={
                    event =>
                        event.stopPropagation()
                }
            >

                <div className="rr-category-actions">

                    {
                        can(
                            'product.view'
                        ) && (

                            <Link
                                href={
                                    `/categories/${category.id}/products`
                                }
                                className="rr-category-action-btn view"
                                title="Show related products"
                            >

                                <i className="bi bi-eye"></i>

                            </Link>

                        )
                    }


                    {
                        can(
                            'category.edit'
                        ) && (

                            <Link
                                href={
                                    `/categories/${category.id}/edit`
                                }
                                className="rr-category-action-btn edit"
                                title="Edit category"
                            >

                                <i className="bi bi-pencil"></i>

                            </Link>

                        )
                    }


                    {
                        can(
                            'category.edit'
                        ) && (

                            <button
                                type="button"
                                className={
                                    `rr-category-action-btn ${category.status
                                        ? 'deactivate'
                                        : 'activate'
                                    }`
                                }
                                title={
                                    category.status
                                        ? 'Deactivate category'
                                        : 'Activate category'
                                }
                                onClick={
                                    onStatusChange
                                }
                            >

                                <i
                                    className={
                                        category.status
                                            ? 'bi bi-pause-circle'
                                            : 'bi bi-play-circle'
                                    }
                                ></i>

                            </button>

                        )
                    }


                    {
                        can(
                            'category.delete'
                        ) && (

                            <button
                                type="button"
                                className="rr-category-action-btn delete"
                                title={
                                    productsCount > 0
                                        ? 'Category is used by products'
                                        : 'Delete category'
                                }
                                onClick={
                                    onDelete
                                }
                            >

                                <i className="bi bi-trash3"></i>

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
                `rr-category-summary-card ${tone}`
            }
        >

            <div className="rr-category-summary-icon">

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

            <span className="rr-category-page-link disabled">

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
                `rr-category-page-link ${link.active
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

        <div className="rr-categories-empty">

            <div className="rr-categories-empty-icon">

                <i className="bi bi-grid"></i>

            </div>


            <strong>

                {
                    filtered
                        ? 'No categories match your filters'
                        : 'No categories in this branch'
                }

            </strong>


            <span>

                {
                    filtered
                        ? 'Change the search or status filter and try again.'
                        : 'Create your first product category for this branch.'
                }

            </span>


            {
                canCreate &&
                !filtered && (

                    <Link
                        href="/categories/create"
                        className="rr-category-primary-btn"
                    >

                        <i className="bi bi-plus-lg"></i>

                        Add Category

                    </Link>

                )
            }

        </div>
    );
}


/*
|--------------------------------------------------------------------------
| Helpers
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

        return 'CA';
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