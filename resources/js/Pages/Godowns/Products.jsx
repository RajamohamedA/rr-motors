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

import '../../../css/business/godowns.css';


export default function Products({
    godown,
    products,
    summary,
    categories,
    brands,
    filters,
}) {

    /*
    |--------------------------------------------------------------------------
    | Shared Props
    |--------------------------------------------------------------------------
    */

    const {
        business,
    } = usePage().props;


    /*
    |--------------------------------------------------------------------------
    | Filter State
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
        categoryId,
        setCategoryId,
    ] = useState(
        String(
            filters?.category_id
            ?? ''
        )
    );


    const [
        brandId,
        setBrandId,
    ] = useState(
        String(
            filters?.brand_id
            ?? ''
        )
    );


    const [
        status,
        setStatus,
    ] = useState(
        String(
            filters?.status
            ?? ''
        )
    );


    const [
        stockStatus,
        setStockStatus,
    ] = useState(
        String(
            filters?.stock_status
            ?? ''
        )
    );


    /*
    |--------------------------------------------------------------------------
    | Product Data
    |--------------------------------------------------------------------------
    */

    const rows =
        products?.data
        ?? [];


    const paginationLinks =
        products?.links
        ?? [];


    /*
    |--------------------------------------------------------------------------
    | Has Filters
    |--------------------------------------------------------------------------
    */

    const hasFilters =
        Boolean(
            search
            ||
            categoryId
            ||
            brandId
            ||
            status !== ''
            ||
            stockStatus
        );


    /*
    |--------------------------------------------------------------------------
    | Query Parameters
    |--------------------------------------------------------------------------
    */

    const queryParams =
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
                    categoryId
                ) {
                    params.set(
                        'category_id',
                        categoryId
                    );
                }


                if (
                    brandId
                ) {
                    params.set(
                        'brand_id',
                        brandId
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
                    stockStatus
                ) {
                    params.set(
                        'stock_status',
                        stockStatus
                    );
                }


                return params.toString();

            },
            [
                search,
                categoryId,
                brandId,
                status,
                stockStatus,
            ]
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
                `/godowns/${godown.id}/products`,
                {
                    search:
                        overrides.search
                        ?? search,

                    category_id:
                        overrides.category_id
                        ?? categoryId,

                    brand_id:
                        overrides.brand_id
                        ?? brandId,

                    status:
                        overrides.status
                        ?? status,

                    stock_status:
                        overrides.stock_status
                        ?? stockStatus,
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

            const currentCategory =
                String(
                    filters?.category_id
                    ?? ''
                );


            const currentBrand =
                String(
                    filters?.brand_id
                    ?? ''
                );


            const currentStatus =
                String(
                    filters?.status
                    ?? ''
                );


            const currentStockStatus =
                String(
                    filters?.stock_status
                    ?? ''
                );


            if (
                categoryId ===
                currentCategory
                &&
                brandId ===
                currentBrand
                &&
                status ===
                currentStatus
                &&
                stockStatus ===
                currentStockStatus
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
            categoryId,
            brandId,
            status,
            stockStatus,
        ]
    );


    /*
    |--------------------------------------------------------------------------
    | Clear
    |--------------------------------------------------------------------------
    */

    const clearFilters =
        () => {

            setSearch('');
            setCategoryId('');
            setBrandId('');
            setStatus('');
            setStockStatus('');


            router.get(
                `/godowns/${godown.id}/products`,
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
    | Export URL
    |--------------------------------------------------------------------------
    */

    const exportUrl =
        path => {

            const base =
                `/godowns/${godown.id}/products/${path}`;


            return queryParams
                ? `${base}?${queryParams}`
                : base;
        };


    /*
    |--------------------------------------------------------------------------
    | Open Print
    |--------------------------------------------------------------------------
    */

    const openPrint =
        () => {

            window.open(
                exportUrl(
                    'print'
                ),
                '_blank',
                'noopener,noreferrer'
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Open PDF
    |--------------------------------------------------------------------------
    */

    const openPdf =
        () => {

            window.open(
                exportUrl(
                    'export/pdf'
                ),
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
                title={
                    `${godown?.godown_name ?? 'Godown'} Inventory`
                }
            />


            <div className="rr-godown-inventory-page">

                <MasterNavigation
                    active="godowns"
                    title="Godowns"
                    subtitle="Warehouse and branch stock locations"
                />


                {/* =====================================================
                    HEADER
                   ===================================================== */}

                <section className="rr-godown-inventory-header">

                    <div className="rr-godown-inventory-header-main">

                        <Link
                            href={
                                `/godowns/${godown.id}`
                            }
                            className="rr-godown-inventory-back"
                            title="Back to Godown"
                        >

                            <i className="bi bi-arrow-left"></i>

                        </Link>


                        <div className="rr-godown-inventory-header-icon">

                            <i className="bi bi-box-seam"></i>

                        </div>


                        <div className="rr-godown-inventory-identity">

                            <span className="rr-godown-inventory-eyebrow">
                                Godown Inventory
                            </span>


                            <div className="rr-godown-inventory-title-line">

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


                            <div className="rr-godown-inventory-subline">

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


                    {/* =================================================
                        EXPORTS
                       ================================================= */}

                    <div className="rr-godown-inventory-header-actions">

                        <a
                            href={
                                exportUrl(
                                    'export/csv'
                                )
                            }
                            className="rr-godown-export-btn csv"
                        >

                            <i className="bi bi-filetype-csv"></i>

                            CSV

                        </a>


                        <button
                            type="button"
                            className="rr-godown-export-btn pdf"
                            onClick={
                                openPdf
                            }
                        >

                            <i className="bi bi-file-earmark-pdf"></i>

                            PDF

                        </button>


                        <button
                            type="button"
                            className="rr-godown-export-btn print"
                            onClick={
                                openPrint
                            }
                        >

                            <i className="bi bi-printer"></i>

                            Print

                        </button>

                    </div>

                </section>


                {/* =====================================================
                    VALUATION SUMMARY
                   ===================================================== */}

                <section className="rr-godown-inventory-summary">

                    <InventorySummaryCard
                        label="Products"
                        value={
                            formatNumber(
                                summary?.products_count
                            )
                        }
                        icon="bi-box-seam"
                    />


                    <InventorySummaryCard
                        label="Stock Qty"
                        value={
                            formatQty(
                                summary?.stock_quantity
                            )
                        }
                        icon="bi-stack"
                    />


                    <InventorySummaryCard
                        label="Estimated Cost"
                        value={
                            formatMoney(
                                summary?.estimated_cost
                            )
                        }
                        icon="bi-currency-rupee"
                        tone="cost"
                    />


                    <InventorySummaryCard
                        label="Selling Value"
                        value={
                            formatMoney(
                                summary?.estimated_selling_value
                            )
                        }
                        icon="bi-graph-up-arrow"
                        tone="selling"
                    />


                    <InventorySummaryCard
                        label="Potential Margin"
                        value={
                            formatMoney(
                                summary?.potential_margin
                            )
                        }
                        icon="bi-bar-chart-line"
                        tone="margin"
                    />


                    <InventorySummaryCard
                        label="Low Stock"
                        value={
                            formatNumber(
                                summary?.low_stock_count
                            )
                        }
                        icon="bi-exclamation-triangle"
                        tone={
                            Number(
                                summary?.low_stock_count
                                ?? 0
                            ) > 0
                                ? 'warning'
                                : 'muted'
                        }
                    />


                    <InventorySummaryCard
                        label="Out of Stock"
                        value={
                            formatNumber(
                                summary?.out_of_stock_count
                            )
                        }
                        icon="bi-x-circle"
                        tone={
                            Number(
                                summary?.out_of_stock_count
                                ?? 0
                            ) > 0
                                ? 'danger'
                                : 'muted'
                        }
                    />

                </section>


                {/* =====================================================
                    INVENTORY PANEL
                   ===================================================== */}

                <section className="rr-godown-inventory-panel">

                    {/* =================================================
                        FILTER TOOLBAR
                       ================================================= */}

                    <div className="rr-godown-inventory-toolbar">

                        <form
                            className="rr-godown-inventory-search"
                            onSubmit={
                                submitSearch
                            }
                        >

                            <div className="rr-godown-inventory-search-box">

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
                                className="rr-godown-inventory-search-btn"
                            >

                                Search

                            </button>

                        </form>


                        <div className="rr-godown-inventory-filter-row">

                            {/* =========================================
                                CATEGORY
                               ========================================= */}

                            <FilterSelect
                                label="Category"
                                value={
                                    categoryId
                                }
                                onChange={
                                    setCategoryId
                                }
                            >

                                <option value="">
                                    All Categories
                                </option>


                                {
                                    (
                                        categories
                                        ?? []
                                    ).map(
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

                            </FilterSelect>


                            {/* =========================================
                                BRAND
                               ========================================= */}

                            <FilterSelect
                                label="Brand"
                                value={
                                    brandId
                                }
                                onChange={
                                    setBrandId
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

                            </FilterSelect>


                            {/* =========================================
                                STOCK
                               ========================================= */}

                            <FilterSelect
                                label="Stock"
                                value={
                                    stockStatus
                                }
                                onChange={
                                    setStockStatus
                                }
                            >

                                <option value="">
                                    All Stock
                                </option>

                                <option value="in_stock">
                                    In Stock
                                </option>

                                <option value="low_stock">
                                    Low Stock
                                </option>

                                <option value="out_of_stock">
                                    Out of Stock
                                </option>

                            </FilterSelect>


                            {/* =========================================
                                STATUS
                               ========================================= */}

                            <FilterSelect
                                label="Product Status"
                                value={
                                    status
                                }
                                onChange={
                                    setStatus
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

                            </FilterSelect>


                            {
                                hasFilters && (

                                    <button
                                        type="button"
                                        className="rr-godown-inventory-clear"
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

                    <div className="rr-godown-inventory-grid-status">

                        <div>

                            <i className="bi bi-table"></i>

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


                        <span>
                            Current stock from latest Stock Ledger balance
                        </span>

                    </div>


                    {/* =================================================
                        TABLE
                       ================================================= */}

                    <div className="rr-godown-inventory-table-wrap">

                        <table className="rr-godown-inventory-table">

                            <thead>

                                <tr>

                                    <th>
                                        Product
                                    </th>

                                    <th>
                                        Category
                                    </th>

                                    <th>
                                        Brand
                                    </th>

                                    <th>
                                        Part No.
                                    </th>

                                    <th>
                                        Unit
                                    </th>

                                    <th className="number">
                                        Qty
                                    </th>

                                    <th className="number">
                                        Min Stock
                                    </th>

                                    <th className="number">
                                        Unit Cost
                                    </th>

                                    <th className="number">
                                        Est. Cost
                                    </th>

                                    <th className="number">
                                        Sale Price
                                    </th>

                                    <th className="number">
                                        Est. Selling
                                    </th>

                                    <th className="number">
                                        Margin
                                    </th>

                                    <th className="center">
                                        Stock
                                    </th>

                                    <th className="center">
                                        Status
                                    </th>

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
                                                    />

                                                )
                                            )

                                        )
                                        : (

                                            <tr>

                                                <td
                                                    colSpan="14"
                                                >

                                                    <InventoryEmpty
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

                            <div className="rr-godown-inventory-pagination">

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


                                <div className="rr-godown-inventory-pagination-links">

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
}) {

    return (

        <tr
            className={
                product.is_out_of_stock
                    ? 'is-out-of-stock'
                    : product.is_low_stock
                        ? 'is-low-stock'
                        : ''
            }
        >

            <td>

                <div className="rr-godown-product-cell">

                    <div className="rr-godown-product-avatar">

                        {
                            getInitials(
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
                                product.product_code
                                || 'No Code'
                            }


                            {
                                product.barcode
                                    ? ` • ${product.barcode}`
                                    : ''
                            }

                        </span>

                    </div>

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
                    product.brand?.brand_name
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

                <span className="rr-godown-product-unit">

                    {
                        product.unit?.short_name
                        ??
                        product.unit?.unit_name
                        ??
                        '—'
                    }

                </span>

            </td>


            <td className="number">

                <strong
                    className={
                        product.is_out_of_stock
                            ? 'stock-zero'
                            : product.is_low_stock
                                ? 'stock-low'
                                : ''
                    }
                >

                    {
                        formatQty(
                            product.balance_qty
                        )
                    }

                </strong>

            </td>


            <td className="number">

                {
                    formatQty(
                        product.minimum_stock
                    )
                }

            </td>


            <td className="number">

                {
                    formatMoney(
                        product.unit_cost
                    )
                }

            </td>


            <td className="number">

                <strong>

                    {
                        formatMoney(
                            product.estimated_cost
                        )
                    }

                </strong>

            </td>


            <td className="number">

                {
                    formatMoney(
                        product.selling_price
                    )
                }

            </td>


            <td className="number">

                <strong>

                    {
                        formatMoney(
                            product.estimated_selling_value
                        )
                    }

                </strong>

            </td>


            <td className="number">

                <span
                    className={
                        `rr-godown-margin ${Number(
                            product.potential_margin
                            ?? 0
                        ) >= 0
                            ? 'positive'
                            : 'negative'
                        }`
                    }
                >

                    {
                        formatMoney(
                            product.potential_margin
                        )
                    }

                </span>

            </td>


            <td className="center">

                <StockBadge
                    product={
                        product
                    }
                />

            </td>


            <td className="center">

                <span
                    className={
                        `rr-godown-product-status ${product.status
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

        </tr>
    );
}


/*
|--------------------------------------------------------------------------
| Stock Badge
|--------------------------------------------------------------------------
*/

function StockBadge({
    product,
}) {

    if (
        product.is_out_of_stock
    ) {

        return (

            <span className="rr-godown-stock-badge out">

                <i className="bi bi-x-circle"></i>

                Out

            </span>
        );
    }


    if (
        product.is_low_stock
    ) {

        return (

            <span className="rr-godown-stock-badge low">

                <i className="bi bi-exclamation-triangle"></i>

                Low

            </span>
        );
    }


    return (

        <span className="rr-godown-stock-badge good">

            <i className="bi bi-check-circle"></i>

            In Stock

        </span>
    );
}


/*
|--------------------------------------------------------------------------
| Filter
|--------------------------------------------------------------------------
*/

function FilterSelect({
    label,
    value,
    onChange,
    children,
}) {

    return (

        <div className="rr-godown-inventory-filter">

            <label>
                {label}
            </label>


            <select
                value={
                    value
                }
                onChange={
                    event =>
                        onChange(
                            event.target.value
                        )
                }
            >

                {children}

            </select>

        </div>
    );
}


/*
|--------------------------------------------------------------------------
| Summary Card
|--------------------------------------------------------------------------
*/

function InventorySummaryCard({
    label,
    value,
    icon,
    tone = 'primary',
}) {

    return (

        <div
            className={
                `rr-godown-inventory-stat ${tone}`
            }
        >

            <div className="rr-godown-inventory-stat-icon">

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
| Empty
|--------------------------------------------------------------------------
*/

function InventoryEmpty({
    filtered,
}) {

    return (

        <div className="rr-godown-inventory-empty">

            <div>

                <i className="bi bi-box-seam"></i>

            </div>


            <strong>

                {
                    filtered
                        ? 'No products match the selected filters'
                        : 'No inventory found in this Godown'
                }

            </strong>


            <span>

                {
                    filtered
                        ? 'Change or clear the filters and try again.'
                        : 'Products will appear after stock is posted to this Godown.'
                }

            </span>

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

            <span className="rr-godown-inventory-page-link disabled">

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
                `rr-godown-inventory-page-link ${link.active
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