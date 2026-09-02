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

import '../../../css/business/categories.css';


export default function Products({
    category,
    products,
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


    const [
        brandId,
        setBrandId,
    ] =
        useState(
            filters?.brand_id
            ?? ''
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
                `/categories/${category.id}/products`,
                {
                    search:
                        overrides.search
                        ?? search,

                    status:
                        overrides.status
                        ?? status,

                    brand_id:
                        overrides.brand_id
                        ?? brandId,
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
    | Search Submit
    |--------------------------------------------------------------------------
    */

    const submitSearch =
        event => {

            event.preventDefault();

            applyFilters();
        };


    /*
    |--------------------------------------------------------------------------
    | Auto Filters
    |--------------------------------------------------------------------------
    */

    useEffect(
        () => {

            const currentStatus =
                filters?.status
                ?? '';


            const currentBrand =
                filters?.brand_id
                ?? '';


            if (
                status ===
                currentStatus
                &&
                brandId ===
                currentBrand
            ) {

                return;
            }


            const timer =
                window.setTimeout(
                    () => {

                        applyFilters({
                            status,
                            brand_id:
                                brandId,
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
            brandId,
        ]
    );


    /*
    |--------------------------------------------------------------------------
    | Clear
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

            setBrandId(
                ''
            );


            router.get(
                `/categories/${category.id}/products`,
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
    | Query String For Export
    |--------------------------------------------------------------------------
    */

    const exportQuery =
        useMemo(
            () => {

                const params =
                    new URLSearchParams();


                if (
                    search
                ) {

                    params.set(
                        'search',
                        search
                    );
                }


                if (
                    status !== ''
                ) {

                    params.set(
                        'status',
                        status
                    );
                }


                if (
                    brandId !== ''
                ) {

                    params.set(
                        'brand_id',
                        brandId
                    );
                }


                const query =
                    params.toString();


                return query
                    ? `?${query}`
                    : '';

            },
            [
                search,
                status,
                brandId,
            ]
        );


    /*
    |--------------------------------------------------------------------------
    | Downloads
    |--------------------------------------------------------------------------
    */

    const downloadCsv =
        () => {

            window.location.href =
                `/categories/${category.id}/products/export/csv${exportQuery}`;
        };


    const downloadPdf =
        () => {

            window.open(
                `/categories/${category.id}/products/export/pdf${exportQuery}`,
                '_blank',
                'noopener,noreferrer'
            );
        };


    const printPage =
        () => {

            window.open(
                `/categories/${category.id}/products/print${exportQuery}`,
                '_blank'
            );
        };

    /*
    |--------------------------------------------------------------------------
    | Back
    |--------------------------------------------------------------------------
    */

    const goBack =
        () => {

            router.visit(
                '/categories'
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Product Row
    |--------------------------------------------------------------------------
    */

    const openProduct =
        product => {

            if (
                !can(
                    'product.view'
                )
            ) {

                return;
            }


            router.visit(
                `/products/${product.id}`
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Data
    |--------------------------------------------------------------------------
    */

    const rows =
        products?.data
        ?? [];


    const paginationLinks =
        products?.links
        ?? [];


    const hasFilters =
        Boolean(
            search
            ||
            status !== ''
            ||
            brandId !== ''
        );


    const pageInfo =
        useMemo(
            () => ({

                from:
                    products?.from
                    ?? 0,

                to:
                    products?.to
                    ?? 0,

                total:
                    products?.total
                    ?? 0,

            }),
            [
                products?.from,
                products?.to,
                products?.total,
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
                title={
                    `${category?.name ?? 'Category'} Products`
                }
            />


            <div className="rr-category-products-page">

                <MasterNavigation
                    active="categories"
                    title="Category Products"
                    subtitle="Products belonging to the selected branch category"
                />


                {/* =====================================================
                    HEADER
                   ===================================================== */}

                <section className="rr-category-products-header">

                    <div className="rr-category-products-header-main">

                        <button
                            type="button"
                            className="rr-category-products-back"
                            onClick={
                                goBack
                            }
                            title="Back to Categories"
                        >

                            <i className="bi bi-arrow-left"></i>

                        </button>


                        <div className="rr-category-products-icon">

                            <i className="bi bi-box-seam"></i>

                        </div>


                        <div>

                            <span className="rr-category-products-eyebrow">

                                {
                                    category?.code
                                    ?? 'Category'
                                }

                            </span>


                            <h1>
                                {
                                    category?.name
                                }
                            </h1>


                            <p>
                                Products available in{' '}

                                <strong>
                                    {
                                        business?.store_name
                                        ?? 'the active branch'
                                    }
                                </strong>.
                            </p>

                        </div>

                    </div>


                    <div className="rr-category-products-header-actions">

                        <button
                            type="button"
                            className="rr-category-products-tool-btn"
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


                        <button
                            type="button"
                            className="rr-category-products-tool-btn"
                            onClick={
                                printPage
                            }
                        >

                            <i className="bi bi-printer"></i>

                            Print

                        </button>


                        <button
                            type="button"
                            className="rr-category-products-tool-btn success"
                            onClick={
                                downloadCsv
                            }
                        >

                            <i className="bi bi-filetype-csv"></i>

                            CSV

                        </button>


                        <button
                            type="button"
                            className="rr-category-products-tool-btn danger"
                            onClick={
                                downloadPdf
                            }
                        >

                            <i className="bi bi-file-earmark-pdf"></i>

                            PDF

                        </button>

                    </div>

                </section>


                {/* =====================================================
                    CATEGORY CONTEXT
                   ===================================================== */}

                <section className="rr-category-products-context">

                    <div>

                        <i className="bi bi-grid"></i>


                        <div>

                            <span>
                                Category
                            </span>

                            <strong>
                                {
                                    category?.name
                                }
                            </strong>

                        </div>

                    </div>


                    <div>

                        <i className="bi bi-shop"></i>


                        <div>

                            <span>
                                Branch
                            </span>

                            <strong>
                                {
                                    business?.store_name
                                    ?? 'Active Branch'
                                }
                            </strong>

                        </div>

                    </div>


                    <div>

                        <i className="bi bi-circle-fill"></i>


                        <div>

                            <span>
                                Category Status
                            </span>

                            <strong>
                                {
                                    category?.status
                                        ? 'Active'
                                        : 'Inactive'
                                }
                            </strong>

                        </div>

                    </div>

                </section>


                {/* =====================================================
                    SUMMARY
                   ===================================================== */}

                <section className="rr-category-products-summary">

                    <ProductSummary
                        label="Total Products"
                        value={
                            summary?.total
                            ?? 0
                        }
                        icon="bi-box-seam"
                    />


                    <ProductSummary
                        label="Active Products"
                        value={
                            summary?.active
                            ?? 0
                        }
                        icon="bi-check-circle"
                        tone="success"
                    />


                    <ProductSummary
                        label="Inactive Products"
                        value={
                            summary?.inactive
                            ?? 0
                        }
                        icon="bi-dash-circle"
                        tone="muted"
                    />


                    <ProductSummary
                        label="Stock Value"
                        value={
                            `₹${money(
                                summary?.stock_value
                            )}`
                        }
                        icon="bi-currency-rupee"
                        tone="warning"
                    />

                </section>


                {/* =====================================================
                    PANEL
                   ===================================================== */}

                <section className="rr-category-products-panel">

                    {/* =================================================
                        TOOLBAR
                       ================================================= */}

                    <div className="rr-category-products-toolbar">

                        <form
                            className="rr-category-products-search"
                            onSubmit={
                                submitSearch
                            }
                        >

                            <div className="rr-category-products-search-box">

                                <i className="bi bi-search"></i>


                                <input
                                    type="text"
                                    value={
                                        search
                                    }
                                    placeholder="Search product, code, barcode, part number, manufacturer or HSN..."
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
                                className="rr-category-products-search-btn"
                            >

                                Search

                            </button>

                        </form>


                        <div className="rr-category-products-filters">

                            <div>

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


                            <div>

                                <label>
                                    Brand
                                </label>


                                <select
                                    value={
                                        brandId
                                    }
                                    onChange={
                                        event =>
                                            setBrandId(
                                                event.target.value
                                            )
                                    }
                                >

                                    <option value="">
                                        All Brands
                                    </option>


                                    {
                                        (
                                            brands
                                            ?? []
                                        ).map(
                                            brand => (

                                                <option
                                                    key={
                                                        brand.id
                                                    }
                                                    value={
                                                        brand.id
                                                    }
                                                >

                                                    {
                                                        brand.brand_name
                                                    }

                                                </option>

                                            )
                                        )
                                    }

                                </select>

                            </div>


                            {
                                hasFilters && (

                                    <button
                                        type="button"
                                        className="rr-category-products-clear"
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
                        GRID STATUS
                       ================================================= */}

                    <div className="rr-category-products-grid-status">

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
                                products
                            </span>

                        </div>


                        <span>

                            <i className="bi bi-layout-three-columns"></i>

                            15 rows per page

                        </span>

                    </div>


                    {/* =================================================
                        TABLE
                       ================================================= */}

                    <div className="rr-category-products-table-wrap">

                        <table className="rr-category-products-table">

                            <thead>

                                <tr>

                                    <th>
                                        Code
                                    </th>

                                    <th>
                                        Product
                                    </th>

                                    <th>
                                        Brand / Part
                                    </th>

                                    <th>
                                        Unit / HSN
                                    </th>

                                    <th className="text-end">
                                        Purchase
                                    </th>

                                    <th className="text-end">
                                        Selling
                                    </th>

                                    <th className="text-end">
                                        MRP
                                    </th>

                                    <th className="text-end">
                                        GST
                                    </th>

                                    <th>
                                        Status
                                    </th>

                                    <th className="text-center">
                                        Show
                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                                {
                                    rows.length > 0
                                        ? rows.map(
                                            product => (

                                                <ProductRow
                                                    key={
                                                        product.id
                                                    }
                                                    product={
                                                        product
                                                    }
                                                    can={
                                                        can
                                                    }
                                                    onOpen={
                                                        () =>
                                                            openProduct(
                                                                product
                                                            )
                                                    }
                                                />

                                            )
                                        )
                                        : (

                                            <tr>

                                                <td
                                                    colSpan="10"
                                                >

                                                    <EmptyProducts
                                                        filtered={
                                                            hasFilters
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

                            <div className="rr-category-products-pagination">

                                <span>

                                    Page{' '}

                                    <strong>
                                        {
                                            products?.current_page
                                            ?? 1
                                        }
                                    </strong>

                                    {' '}of{' '}

                                    <strong>
                                        {
                                            products?.last_page
                                            ?? 1
                                        }
                                    </strong>

                                </span>


                                <div>

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

            </div>

        </BusinessLayout>
    );
}


/*
|--------------------------------------------------------------------------
| Product Row
|--------------------------------------------------------------------------
*/

function ProductRow({
    product,
    can,
    onOpen,
}) {

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

            <td>

                <span className="rr-category-product-code">

                    {
                        product.product_code
                        ?? '—'
                    }

                </span>

            </td>


            <td>

                <div className="rr-category-product-name">

                    <div className="rr-category-product-avatar">

                        {
                            initials(
                                product.product_name
                            )
                        }

                    </div>


                    <div>

                        <strong>
                            {
                                product.product_name
                            }
                        </strong>


                        <span>
                            {
                                product.barcode
                                ?? product.manufacturer
                                ?? 'Product'
                            }
                        </span>

                    </div>

                </div>

            </td>


            <td>

                <div className="rr-category-product-meta">

                    <strong>
                        {
                            product.brand?.brand_name
                            ?? 'No Brand'
                        }
                    </strong>

                    <span>
                        {
                            product.part_number
                            ?? 'No Part Number'
                        }
                    </span>

                </div>

            </td>


            <td>

                <div className="rr-category-product-meta">

                    <strong>
                        {
                            product.unit?.unit_name
                            ?? '—'
                        }
                    </strong>

                    <span>
                        HSN: {
                            product.hsn_code
                            ?? '—'
                        }
                    </span>

                </div>

            </td>


            <td className="text-end">

                ₹{
                    money(
                        product.purchase_price
                    )
                }

            </td>


            <td className="text-end rr-category-product-selling">

                ₹{
                    money(
                        product.selling_price
                    )
                }

            </td>


            <td className="text-end">

                ₹{
                    money(
                        product.mrp
                    )
                }

            </td>


            <td className="text-end">

                {
                    Number(
                        product.gst_percent
                        ?? 0
                    )
                }%

            </td>


            <td>

                <span
                    className={
                        `rr-category-product-status ${product.status
                            ? 'active'
                            : 'inactive'
                        }`
                    }
                >

                    <span></span>

                    {
                        product.status
                            ? 'Active'
                            : 'Inactive'
                    }

                </span>

            </td>


            <td
                className="text-center"
                onClick={
                    event =>
                        event.stopPropagation()
                }
            >

                {
                    can(
                        'product.view'
                    ) && (

                        <Link
                            href={
                                `/products/${product.id}`
                            }
                            className="rr-category-product-show-btn"
                            title="Show product"
                        >

                            <i className="bi bi-eye"></i>

                        </Link>

                    )
                }

            </td>

        </tr>
    );
}


/*
|--------------------------------------------------------------------------
| Summary
|--------------------------------------------------------------------------
*/

function ProductSummary({
    label,
    value,
    icon,
    tone = 'primary',
}) {

    return (

        <div
            className={
                `rr-category-product-summary ${tone}`
            }
        >

            <div>

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

            <span className="rr-category-products-page-link disabled">

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
                `rr-category-products-page-link ${link.active
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
| Empty
|--------------------------------------------------------------------------
*/

function EmptyProducts({
    filtered,
}) {

    return (

        <div className="rr-category-products-empty">

            <i className="bi bi-box-seam"></i>


            <strong>

                {
                    filtered
                        ? 'No products match your filters'
                        : 'No products in this category'
                }

            </strong>


            <span>

                {
                    filtered
                        ? 'Change the search or filter values and try again.'
                        : 'Products assigned to this category will appear here.'
                }

            </span>

        </div>
    );
}


/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function money(
    value
) {

    return Number(
        value
        ?? 0
    )
        .toLocaleString(
            'en-IN',
            {
                minimumFractionDigits:
                    2,

                maximumFractionDigits:
                    2,
            }
        );
}


function initials(
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

        return 'PR';
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