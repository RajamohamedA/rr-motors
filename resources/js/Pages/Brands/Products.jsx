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

import '../../../css/business/brands.css';


export default function Products({
    brand,
    products,
    summary,
    filters,
    categories = [],
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
    ] = useState(
        filters?.search
        ?? ''
    );


    const [
        status,
        setStatus,
    ] = useState(
        filters?.status
        ?? ''
    );


    const [
        categoryId,
        setCategoryId,
    ] = useState(
        filters?.category_id
        ?? ''
    );


    /*
    |--------------------------------------------------------------------------
    | URL
    |--------------------------------------------------------------------------
    */

    const baseUrl =
        `/brands/${brand.id}/products`;


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
                baseUrl,
                {
                    search:
                        overrides.search
                        ?? search,

                    status:
                        overrides.status
                        ?? status,

                    category_id:
                        overrides.category_id
                        ?? categoryId,
                },
                {
                    preserveState:
                        true,

                    preserveScroll:
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
    | Auto Filters
    |--------------------------------------------------------------------------
    */

    useEffect(
        () => {

            const currentStatus =
                String(
                    filters?.status
                    ?? ''
                );


            const currentCategory =
                String(
                    filters?.category_id
                    ?? ''
                );


            if (
                String(status) === currentStatus
                &&
                String(categoryId) === currentCategory
            ) {
                return;
            }


            const timer =
                window.setTimeout(
                    () => {

                        applyFilters();

                    },
                    150
                );


            return () =>
                window.clearTimeout(
                    timer
                );

        },
        [
            status,
            categoryId,
        ]
    );


    /*
    |--------------------------------------------------------------------------
    | Clear Filters
    |--------------------------------------------------------------------------
    */

    const clearFilters =
        () => {

            setSearch('');
            setStatus('');
            setCategoryId('');


            router.get(
                baseUrl,
                {},
                {
                    preserveState:
                        true,

                    preserveScroll:
                        true,

                    replace:
                        true,
                }
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Query String
    |--------------------------------------------------------------------------
    */

    const exportQuery =
        useMemo(
            () => {

                const params =
                    new URLSearchParams();


                if (search) {
                    params.set(
                        'search',
                        search
                    );
                }


                if (status !== '') {
                    params.set(
                        'status',
                        status
                    );
                }


                if (categoryId !== '') {
                    params.set(
                        'category_id',
                        categoryId
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
                categoryId,
            ]
        );


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
            categoryId !== ''
        );


    /*
    |--------------------------------------------------------------------------
    | Product View
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
    | Print
    |--------------------------------------------------------------------------
    */

    const printProducts =
        () => {

            window.open(
                `/brands/${brand.id}/products/print${exportQuery}`,
                '_blank',
                'noopener,noreferrer'
            );
        };


    /*
    |--------------------------------------------------------------------------
    | PDF
    |--------------------------------------------------------------------------
    */

    const openPdf =
        () => {

            window.open(
                `/brands/${brand.id}/products/export/pdf${exportQuery}`,
                '_blank',
                'noopener,noreferrer'
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
                title={`${brand.brand_name} Products`}
            />


            <div className="rr-brand-products-page">

                <MasterNavigation
                    active="brands"
                    title="Brands"
                    subtitle="Product manufacturer and brand master"
                />


                {/* =====================================================
                    HEADER
                   ===================================================== */}

                <section className="rr-brand-products-header">

                    <div className="rr-brand-products-header-main">

                        <Link
                            href="/brands"
                            className="rr-brand-products-back"
                            title="Back to brands"
                        >

                            <i className="bi bi-arrow-left"></i>

                        </Link>


                        <div className="rr-brand-products-header-icon">

                            <i className="bi bi-tags"></i>

                        </div>


                        <div>

                            <span className="rr-brand-products-eyebrow">
                                Brand Products
                            </span>


                            <h1>
                                {brand.brand_name}
                            </h1>


                            <p>

                                <span>
                                    {brand.brand_code}
                                </span>

                                <span className="rr-brand-products-divider">
                                    •
                                </span>

                                <span>
                                    {
                                        business?.store_name
                                        ?? 'Active Branch'
                                    }
                                </span>

                            </p>

                        </div>

                    </div>


                    <div className="rr-brand-products-header-actions">

                        <button
                            type="button"
                            className="rr-brand-export-btn"
                            onClick={
                                printProducts
                            }
                        >

                            <i className="bi bi-printer"></i>

                            Print

                        </button>


                        <button
                            type="button"
                            className="rr-brand-export-btn pdf"
                            onClick={
                                openPdf
                            }
                        >

                            <i className="bi bi-file-earmark-pdf"></i>

                            PDF

                        </button>


                        <a
                            href={
                                `/brands/${brand.id}/products/export/csv${exportQuery}`
                            }
                            className="rr-brand-export-btn csv"
                        >

                            <i className="bi bi-file-earmark-spreadsheet"></i>

                            CSV

                        </a>

                    </div>

                </section>


                {/* =====================================================
                    BRAND INFORMATION
                   ===================================================== */}

                <section className="rr-brand-products-info">

                    <div>

                        <span>
                            Brand
                        </span>

                        <strong>
                            {brand.brand_name}
                        </strong>

                    </div>


                    <div>

                        <span>
                            Code
                        </span>

                        <strong>
                            {brand.brand_code}
                        </strong>

                    </div>


                    <div>

                        <span>
                            Status
                        </span>

                        <strong
                            className={
                                brand.status
                                    ? 'active'
                                    : 'inactive'
                            }
                        >

                            {
                                brand.status
                                    ? 'Active'
                                    : 'Inactive'
                            }

                        </strong>

                    </div>


                    <div className="description">

                        <span>
                            Description
                        </span>

                        <strong>
                            {
                                brand.description
                                || 'No description'
                            }
                        </strong>

                    </div>

                </section>


                {/* =====================================================
                    SUMMARY
                   ===================================================== */}

                <section className="rr-brand-products-summary">

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

                </section>


                {/* =====================================================
                    PANEL
                   ===================================================== */}

                <section className="rr-brand-products-panel">

                    {/* =================================================
                        TOOLBAR
                       ================================================= */}

                    <div className="rr-brand-products-toolbar">

                        <form
                            className="rr-brand-products-search"
                            onSubmit={
                                submitSearch
                            }
                        >

                            <div className="rr-brand-products-search-box">

                                <i className="bi bi-search"></i>


                                <input
                                    type="text"
                                    value={
                                        search
                                    }
                                    placeholder="Search product, code, barcode, part number..."
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

                                                    setSearch('');

                                                    applyFilters({
                                                        search: '',
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
                                className="rr-brand-product-search-btn"
                            >

                                Search

                            </button>

                        </form>


                        <div className="rr-brand-products-filters">

                            {
                                categories.length > 0 && (

                                    <div className="rr-brand-product-filter">

                                        <label>
                                            Category
                                        </label>


                                        <select
                                            value={
                                                categoryId
                                            }
                                            onChange={
                                                event =>
                                                    setCategoryId(
                                                        event.target.value
                                                    )
                                            }
                                        >

                                            <option value="">
                                                All Categories
                                            </option>


                                            {
                                                categories.map(
                                                    category => (

                                                        <option
                                                            key={
                                                                category.id
                                                            }
                                                            value={
                                                                category.id
                                                            }
                                                        >

                                                            {
                                                                category.name
                                                            }

                                                        </option>

                                                    )
                                                )
                                            }

                                        </select>

                                    </div>

                                )
                            }


                            <div className="rr-brand-product-filter">

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
                                        className="rr-brand-products-clear"
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
                        TABLE INFO
                       ================================================= */}

                    <div className="rr-brand-products-table-info">

                        <div>

                            <i className="bi bi-box-seam"></i>

                            Showing

                            <strong>
                                {
                                    products?.from
                                    ?? 0
                                }–{
                                    products?.to
                                    ?? 0
                                }
                            </strong>

                            of

                            <strong>
                                {
                                    products?.total
                                    ?? 0
                                }
                            </strong>

                            products

                        </div>


                        {
                            hasFilters && (

                                <span>
                                    Filters applied
                                </span>

                            )
                        }

                    </div>


                    {/* =================================================
                        TABLE
                       ================================================= */}

                    <div className="rr-brand-products-table-wrap">

                        <table className="rr-brand-products-table">

                            <thead>

                                <tr>

                                    <th>
                                        Code
                                    </th>

                                    <th>
                                        Product
                                    </th>

                                    <th>
                                        Category
                                    </th>

                                    <th>
                                        Part No.
                                    </th>

                                    <th>
                                        Unit
                                    </th>

                                    <th className="number">
                                        Purchase
                                    </th>

                                    <th className="number">
                                        Selling
                                    </th>

                                    <th className="number">
                                        MRP
                                    </th>

                                    <th className="number">
                                        GST
                                    </th>

                                    <th className="center">
                                        Status
                                    </th>

                                    {
                                        can(
                                            'product.view'
                                        ) && (

                                            <th className="center action">
                                                Action
                                            </th>

                                        )
                                    }

                                </tr>

                            </thead>


                            <tbody>

                                {
                                    rows.length > 0
                                        ? (

                                            rows.map(
                                                product => (

                                                    <ProductRow
                                                        key={
                                                            product.id
                                                        }

                                                        product={
                                                            product
                                                        }

                                                        canView={
                                                            can(
                                                                'product.view'
                                                            )
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

                                        )
                                        : (

                                            <tr>

                                                <td
                                                    colSpan={
                                                        can(
                                                            'product.view'
                                                        )
                                                            ? 11
                                                            : 10
                                                    }
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
                        Number(
                            products?.total
                            ?? 0
                        ) > 0 && (

                            <div className="rr-brand-products-pagination">

                                <div>

                                    Page

                                    <strong>
                                        {
                                            products?.current_page
                                            ?? 1
                                        }
                                    </strong>

                                    of

                                    <strong>
                                        {
                                            products?.last_page
                                            ?? 1
                                        }
                                    </strong>

                                </div>


                                <div className="rr-brand-products-pages">

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
    canView,
    onOpen,
}) {

    return (

        <tr
            className={
                canView
                    ? 'is-clickable'
                    : ''
            }
            onClick={
                canView
                    ? onOpen
                    : undefined
            }
        >

            <td>

                <span className="rr-brand-product-code">

                    {
                        product.product_code
                        || '—'
                    }

                </span>

            </td>


            <td>

                <div className="rr-brand-product-name">

                    <strong>
                        {
                            product.product_name
                        }
                    </strong>


                    {
                        product.manufacturer && (

                            <span>
                                {
                                    product.manufacturer
                                }
                            </span>

                        )
                    }

                </div>

            </td>


            <td>

                {
                    product.category?.name
                    ?? '—'
                }

            </td>


            <td>

                {
                    product.part_number
                    || '—'
                }

            </td>


            <td>

                {
                    product.unit?.short_name
                    ??
                    product.unit?.unit_name
                    ??
                    '—'
                }

            </td>


            <td className="number">

                {
                    formatMoney(
                        product.purchase_price
                    )
                }

            </td>


            <td className="number">

                <strong>

                    {
                        formatMoney(
                            product.selling_price
                        )
                    }

                </strong>

            </td>


            <td className="number">

                {
                    formatMoney(
                        product.mrp
                    )
                }

            </td>


            <td className="number">

                {
                    formatPercent(
                        product.gst_percent
                    )
                }

            </td>


            <td className="center">

                <span
                    className={
                        `rr-brand-product-status ${product.status
                            ? 'active'
                            : 'inactive'
                        }`
                    }
                >

                    {
                        product.status
                            ? 'Active'
                            : 'Inactive'
                    }

                </span>

            </td>


            {
                canView && (

                    <td
                        className="center"
                        onClick={
                            event =>
                                event.stopPropagation()
                        }
                    >

                        <Link
                            href={
                                `/products/${product.id}`
                            }
                            className="rr-brand-product-view"
                            title="View product"
                        >

                            <i className="bi bi-eye"></i>

                        </Link>

                    </td>

                )
            }

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
                `rr-brand-product-summary-card ${tone}`
            }
        >

            <div className="rr-brand-product-summary-icon">

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

            <span className="rr-brand-product-page disabled">

                {label}

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
                `rr-brand-product-page ${link.active
                    ? 'active'
                    : ''
                }`
            }
        >

            {label}

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

        <div className="rr-brand-products-empty">

            <div>

                <i className="bi bi-box-seam"></i>

            </div>


            <strong>

                {
                    filtered
                        ? 'No products match your filters'
                        : 'No products found for this brand'
                }

            </strong>


            <span>

                {
                    filtered
                        ? 'Change or clear the search filters and try again.'
                        : 'Products assigned to this brand will appear here.'
                }

            </span>

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
| Percentage
|--------------------------------------------------------------------------
*/

function formatPercent(
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
        return '0%';
    }


    return `${number}%`;
}