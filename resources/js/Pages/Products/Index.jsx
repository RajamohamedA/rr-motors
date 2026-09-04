import React, {
    useEffect,
    useMemo,
    useRef,
    useState,
} from 'react';

import {
    Head,
    Link,
    router,
    usePage,
} from '@inertiajs/react';

import BusinessLayout from '@/Layouts/BusinessLayout';

import MasterNavigation
    from '../../Components/Business/MasterNavigation';

import ConfirmModal
    from '../../Components/ConfirmModal';

import '../../../css/business/products.css';


export default function Index({
    products,
    categories = [],
    brands = [],
    summary = {},
    filters = {},
}) {

    /*
    |--------------------------------------------------------------------------
    | Shared Context
    |--------------------------------------------------------------------------
    */

    const {
        business,
    } = usePage().props;


    /*
    |--------------------------------------------------------------------------
    | Filters
    |--------------------------------------------------------------------------
    */

    const [
        search,
        setSearch,
    ] = useState(
        filters.search ?? ''
    );


    const [
        categoryId,
        setCategoryId,
    ] = useState(
        String(
            filters.category_id ?? ''
        )
    );


    const [
        brandId,
        setBrandId,
    ] = useState(
        String(
            filters.brand_id ?? ''
        )
    );


    const [
        status,
        setStatus,
    ] = useState(
        String(
            filters.status ?? ''
        )
    );


    const [
        stockFilter,
        setStockFilter,
    ] = useState(
        ''
    );


    /*
    |--------------------------------------------------------------------------
    | Confirmation Modal
    |--------------------------------------------------------------------------
    */

    const [
        confirmModal,
        setConfirmModal,
    ] = useState({

        show:
            false,

        action:
            null,

        product:
            null,

        processing:
            false,
    });


    const firstRender =
        useRef(
            true
        );


    /*
    |--------------------------------------------------------------------------
    | Current Page Stock Filter
    |--------------------------------------------------------------------------
    */

    const visibleProducts =
        useMemo(
            () => {

                const rows =
                    products?.data ?? [];


                if (
                    stockFilter === ''
                ) {
                    return rows;
                }


                if (
                    stockFilter === 'low'
                ) {

                    return rows.filter(
                        product =>
                            Boolean(
                                product.is_low_stock
                            )
                    );
                }


                if (
                    stockFilter === 'out'
                ) {

                    return rows.filter(
                        product =>
                            Boolean(
                                product.is_out_of_stock
                            )
                    );
                }


                if (
                    stockFilter === 'available'
                ) {

                    return rows.filter(
                        product =>
                            Number(
                                product.current_stock ?? 0
                            ) > 0
                    );
                }


                return rows;

            },
            [
                products?.data,
                stockFilter,
            ]
        );


    /*
    |--------------------------------------------------------------------------
    | Auto Filter
    |--------------------------------------------------------------------------
    */

    useEffect(
        () => {

            if (
                firstRender.current
            ) {

                firstRender.current =
                    false;

                return;
            }


            const timer =
                window.setTimeout(
                    () => {

                        router.get(
                            '/products',
                            {
                                search:
                                    search,

                                category_id:
                                    categoryId,

                                brand_id:
                                    brandId,

                                status:
                                    status,
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

                    },
                    350
                );


            return () =>
                window.clearTimeout(
                    timer
                );

        },
        [
            search,
            categoryId,
            brandId,
            status,
        ]
    );


    /*
    |--------------------------------------------------------------------------
    | Reset Filters
    |--------------------------------------------------------------------------
    */

    const resetFilters =
        () => {

            setSearch('');
            setCategoryId('');
            setBrandId('');
            setStatus('');
            setStockFilter('');


            router.get(
                '/products',
                {},
                {
                    preserveState:
                        false,

                    preserveScroll:
                        true,

                    replace:
                        true,
                }
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Open Product
    |--------------------------------------------------------------------------
    */

    const openProduct =
        product => {

            router.visit(
                `/products/${product.id}`
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Open Status Confirmation
    |--------------------------------------------------------------------------
    */

    const openStatusConfirm =
        (
            event,
            product
        ) => {

            event.stopPropagation();


            setConfirmModal({

                show:
                    true,

                action:
                    product.status
                        ? 'deactivate'
                        : 'activate',

                product:
                    product,

                processing:
                    false,
            });
        };


    /*
    |--------------------------------------------------------------------------
    | Open Delete Confirmation
    |--------------------------------------------------------------------------
    */

    const openDeleteConfirm =
        (
            event,
            product
        ) => {

            event.stopPropagation();


            setConfirmModal({

                show:
                    true,

                action:
                    'delete',

                product:
                    product,

                processing:
                    false,
            });
        };


    /*
    |--------------------------------------------------------------------------
    | Close Confirmation
    |--------------------------------------------------------------------------
    */

    const closeConfirmModal =
        () => {

            if (
                confirmModal.processing
            ) {
                return;
            }


            setConfirmModal({

                show:
                    false,

                action:
                    null,

                product:
                    null,

                processing:
                    false,
            });
        };


    /*
    |--------------------------------------------------------------------------
    | Finish Confirmation Request
    |--------------------------------------------------------------------------
    */

    const finishConfirmRequest =
        () => {

            setConfirmModal({

                show:
                    false,

                action:
                    null,

                product:
                    null,

                processing:
                    false,
            });
        };


    /*
    |--------------------------------------------------------------------------
    | Confirm Action
    |--------------------------------------------------------------------------
    */

    const confirmProductAction =
        () => {

            const product =
                confirmModal.product;


            if (
                !product
            ) {
                return;
            }


            setConfirmModal(
                previous => ({
                    ...previous,

                    processing:
                        true,
                })
            );


            /*
            |--------------------------------------------------------------------------
            | Delete
            |--------------------------------------------------------------------------
            */

            if (
                confirmModal.action ===
                'delete'
            ) {

                router.delete(
                    `/products/${product.id}`,
                    {
                        preserveScroll:
                            true,

                        onFinish:
                            finishConfirmRequest,
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
                `/products/${product.id}/toggle-status`,
                {},
                {
                    preserveScroll:
                        true,

                    onFinish:
                        finishConfirmRequest,
                }
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Confirmation Text
    |--------------------------------------------------------------------------
    */

    const confirmTitle =
        confirmModal.action === 'delete'
            ? 'Delete Product'
            : confirmModal.action === 'deactivate'
                ? 'Deactivate Product'
                : 'Activate Product';


    const confirmMessage =
        confirmModal.action === 'delete'
            ? `Are you sure you want to permanently delete "${confirmModal.product?.product_name ?? 'this product'}"? Products with stock or transaction history cannot be deleted.`
            : confirmModal.action === 'deactivate'
                ? `Are you sure you want to deactivate "${confirmModal.product?.product_name ?? 'this product'}"? Existing stock and transaction history will remain unchanged.`
                : `Are you sure you want to activate "${confirmModal.product?.product_name ?? 'this product'}"? It will become available for new transactions in this branch.`;


    const confirmText =
        confirmModal.action === 'delete'
            ? 'Delete Product'
            : confirmModal.action === 'deactivate'
                ? 'Deactivate'
                : 'Activate';


    const confirmIcon =
        confirmModal.action === 'delete'
            ? 'bi-trash3'
            : confirmModal.action === 'deactivate'
                ? 'bi-pause-circle'
                : 'bi-check-circle';


    /*
    |--------------------------------------------------------------------------
    | Common ConfirmModal currently has:
    |
    | danger = red
    | anything else = standard primary
    |--------------------------------------------------------------------------
    */

    const confirmVariant =
        confirmModal.action === 'delete'
            ? 'danger'
            : 'primary';


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
            stockFilter
        );


    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */

    return (

        <>

            <Head
                title="Products"
            />


            <div className="rr-product-page">

                {/* =====================================================
                    MASTER NAVIGATION
                   ===================================================== */}

                <MasterNavigation
                    active="products"
                    title="Products"
                    subtitle="Product master, pricing and branch inventory"
                />


                {/* =====================================================
                    HEADER
                   ===================================================== */}

                <section className="rr-product-header">

                    <div className="rr-product-header-main">

                        <Link
                            href="/business/dashboard"
                            className="rr-product-back-btn"
                            title="Back to Dashboard"
                        >

                            <i className="bi bi-arrow-left"></i>

                        </Link>


                        <div className="rr-product-header-icon">

                            <i className="bi bi-box-seam"></i>

                        </div>


                        <div className="rr-product-header-content">

                            <span className="rr-product-eyebrow">
                                Inventory Master
                            </span>


                            <h1>
                                Product Master
                            </h1>


                            <div className="rr-product-header-meta">

                                <span>

                                    <i className="bi bi-buildings"></i>

                                    {
                                        business?.company_name
                                        ?? 'Company'
                                    }

                                </span>


                                <span>

                                    <i className="bi bi-shop"></i>

                                    {
                                        business?.store_name
                                        ?? 'Branch'
                                    }

                                </span>

                            </div>

                        </div>

                    </div>


                    <div className="rr-product-header-actions">

                        <Link
                            href="/products/create"
                            className="rr-product-primary-btn"
                        >

                            <i className="bi bi-plus-lg"></i>

                            New Product

                        </Link>

                    </div>

                </section>


                {/* =====================================================
                    SUMMARY
                   ===================================================== */}

                <section className="rr-product-summary">

                    <SummaryCard
                        title="Total Products"
                        value={
                            summary.total ?? 0
                        }
                        icon="bi-box-seam"
                        tone="primary"
                    />


                    <SummaryCard
                        title="Active Products"
                        value={
                            summary.active ?? 0
                        }
                        icon="bi-check-circle"
                        tone="success"
                    />


                    <SummaryCard
                        title="Inactive"
                        value={
                            summary.inactive ?? 0
                        }
                        icon="bi-pause-circle"
                        tone="neutral"
                    />


                    <SummaryCard
                        title="Shown on Page"
                        value={
                            visibleProducts.length
                        }
                        icon="bi-list-ul"
                        tone="info"
                    />

                </section>


                {/* =====================================================
                    FILTER WINDOW
                   ===================================================== */}

                <section className="rr-product-filter-window">

                    <div className="rr-product-filter-header">

                        <div>

                            <i className="bi bi-funnel"></i>

                            <strong>
                                Product Search
                            </strong>

                        </div>


                        <span>

                            {
                                products?.total ?? 0
                            }

                            {' '}

                            product(s)

                        </span>

                    </div>


                    <div className="rr-product-filter-body">

                        {/* Search */}

                        <div className="rr-product-search-box">

                            <i className="bi bi-search"></i>


                            <input
                                type="search"
                                value={
                                    search
                                }
                                placeholder="Search product, code, barcode, part number, manufacturer..."
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
                                            () =>
                                                setSearch('')
                                        }
                                    >

                                        <i className="bi bi-x-lg"></i>

                                    </button>

                                )
                            }

                        </div>


                        {/* Category */}

                        <FilterBox
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

                        </FilterBox>


                        {/* Brand */}

                        <FilterBox
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
                                brands.map(
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

                        </FilterBox>


                        {/* Status */}

                        <FilterBox
                            label="Status"
                            value={
                                status
                            }
                            onChange={
                                setStatus
                            }
                        >

                            <option value="">
                                All Status
                            </option>

                            <option value="1">
                                Active
                            </option>

                            <option value="0">
                                Inactive
                            </option>

                        </FilterBox>


                        {/* Stock */}

                        <FilterBox
                            label="Stock"
                            value={
                                stockFilter
                            }
                            onChange={
                                setStockFilter
                            }
                        >

                            <option value="">
                                All Stock
                            </option>

                            <option value="available">
                                Available
                            </option>

                            <option value="low">
                                Low Stock
                            </option>

                            <option value="out">
                                Out of Stock
                            </option>

                        </FilterBox>


                        {/* Clear */}

                        <button
                            type="button"
                            className="rr-product-clear-btn"
                            disabled={
                                !hasFilters
                            }
                            onClick={
                                resetFilters
                            }
                        >

                            <i className="bi bi-arrow-counterclockwise"></i>

                            Clear

                        </button>

                    </div>

                </section>


                {/* =====================================================
                    PRODUCT GRID
                   ===================================================== */}

                <section className="rr-product-grid-window">

                    <div className="rr-product-grid-toolbar">

                        <div className="rr-product-grid-title">

                            <i className="bi bi-table"></i>


                            <div>

                                <strong>
                                    Product Catalogue
                                </strong>

                                <span>
                                    Click a row to view product details
                                </span>

                            </div>

                        </div>


                        <div className="rr-product-grid-count">

                            Showing

                            {' '}

                            <strong>
                                {
                                    products?.from ?? 0
                                }
                            </strong>

                            {' '}

                            to

                            {' '}

                            <strong>
                                {
                                    products?.to ?? 0
                                }
                            </strong>

                            {' '}

                            of

                            {' '}

                            <strong>
                                {
                                    products?.total ?? 0
                                }
                            </strong>

                        </div>

                    </div>


                    <div className="rr-product-table-wrap">

                        <table className="rr-product-table">

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
                                        Unit
                                    </th>

                                    <th className="number">
                                        MRP
                                    </th>

                                    <th className="number">
                                        Retail
                                    </th>

                                    <th className="number">
                                        Wholesale
                                    </th>

                                    <th className="number">
                                        Mechanic
                                    </th>

                                    <th className="number">
                                        Shop
                                    </th>

                                    <th className="number">
                                        Godown
                                    </th>

                                    <th className="number">
                                        Total
                                    </th>

                                    <th className="center">
                                        Status
                                    </th>

                                    <th className="center rr-product-action-head">
                                        Action
                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                                {
                                    visibleProducts.length > 0
                                        ? (

                                            visibleProducts.map(
                                                product => (

                                                    <tr
                                                        key={
                                                            product.id
                                                        }
                                                        className={
                                                            `rr-product-row ${product.is_out_of_stock
                                                                ? 'out-stock-row'
                                                                : product.is_low_stock
                                                                    ? 'low-stock-row'
                                                                    : ''
                                                            }`
                                                        }
                                                        onClick={
                                                            () =>
                                                                openProduct(
                                                                    product
                                                                )
                                                        }
                                                    >

                                                        {/* Product */}

                                                        <td>

                                                            <ProductIdentity
                                                                product={
                                                                    product
                                                                }
                                                            />

                                                        </td>


                                                        {/* Category */}

                                                        <td>

                                                            {
                                                                product.category?.name
                                                                ?? '—'
                                                            }

                                                        </td>


                                                        {/* Brand */}

                                                        <td>

                                                            {
                                                                product.brand?.brand_name
                                                                ?? '—'
                                                            }

                                                        </td>


                                                        {/* Unit */}

                                                        <td>

                                                            <span className="rr-product-unit">

                                                                {
                                                                    product.unit?.short_name
                                                                    ??
                                                                    product.unit?.unit_name
                                                                    ??
                                                                    '—'
                                                                }

                                                            </span>

                                                        </td>


                                                        {/* MRP */}

                                                        <PriceCell
                                                            value={
                                                                product.mrp
                                                            }
                                                        />


                                                        {/* Retail */}

                                                        <PriceCell
                                                            value={
                                                                product.selling_price
                                                            }
                                                            discount={
                                                                product.retail_discount
                                                            }
                                                        />


                                                        {/* Wholesale */}

                                                        <PriceCell
                                                            value={
                                                                product.wholesale_price
                                                            }
                                                            discount={
                                                                product.wholesale_discount
                                                            }
                                                        />


                                                        {/* Mechanic */}

                                                        <PriceCell
                                                            value={
                                                                product.mechanic_price
                                                            }
                                                            discount={
                                                                product.mechanic_discount
                                                            }
                                                        />


                                                        {/* Shop */}

                                                        <StockCell
                                                            value={
                                                                product.shop_stock
                                                            }
                                                        />


                                                        {/* Godown */}

                                                        <StockCell
                                                            value={
                                                                product.godown_stock
                                                            }
                                                        />


                                                        {/* Total */}

                                                        <td className="number">

                                                            <StockBadge
                                                                product={
                                                                    product
                                                                }
                                                            />

                                                        </td>


                                                        {/* Status */}

                                                        <td className="center">

                                                            <StatusBadge
                                                                active={
                                                                    product.status
                                                                }
                                                            />

                                                        </td>


                                                        {/* Actions */}

                                                        <td
                                                            className="center"
                                                            onClick={
                                                                event =>
                                                                    event.stopPropagation()
                                                            }
                                                        >

                                                            <div className="rr-product-actions">

                                                                <Link
                                                                    href={
                                                                        `/products/${product.id}`
                                                                    }
                                                                    className="rr-product-action-btn"
                                                                    title="View Product"
                                                                >

                                                                    <i className="bi bi-eye"></i>

                                                                </Link>


                                                                <Link
                                                                    href={
                                                                        `/products/${product.id}/edit`
                                                                    }
                                                                    className="rr-product-action-btn edit"
                                                                    title="Edit Product"
                                                                >

                                                                    <i className="bi bi-pencil"></i>

                                                                </Link>


                                                                <button
                                                                    type="button"
                                                                    className={
                                                                        `rr-product-action-btn ${product.status
                                                                            ? 'warning'
                                                                            : 'success'
                                                                        }`
                                                                    }
                                                                    title={
                                                                        product.status
                                                                            ? 'Deactivate Product'
                                                                            : 'Activate Product'
                                                                    }
                                                                    onClick={
                                                                        event =>
                                                                            openStatusConfirm(
                                                                                event,
                                                                                product
                                                                            )
                                                                    }
                                                                >

                                                                    <i
                                                                        className={
                                                                            product.status
                                                                                ? 'bi bi-pause-circle'
                                                                                : 'bi bi-check-circle'
                                                                        }
                                                                    ></i>

                                                                </button>


                                                                <button
                                                                    type="button"
                                                                    className="rr-product-action-btn danger"
                                                                    title="Delete Product"
                                                                    onClick={
                                                                        event =>
                                                                            openDeleteConfirm(
                                                                                event,
                                                                                product
                                                                            )
                                                                    }
                                                                >

                                                                    <i className="bi bi-trash"></i>

                                                                </button>

                                                            </div>

                                                        </td>

                                                    </tr>

                                                )
                                            )

                                        )
                                        : (

                                            <tr>

                                                <td
                                                    colSpan="13"
                                                >

                                                    <div className="rr-product-empty">

                                                        <div>

                                                            <i className="bi bi-box-seam"></i>

                                                        </div>


                                                        <strong>
                                                            No products found
                                                        </strong>


                                                        <span>

                                                            {
                                                                hasFilters
                                                                    ? 'Change or clear the selected filters.'
                                                                    : 'Create the first product for this branch.'
                                                            }

                                                        </span>


                                                        {
                                                            !hasFilters && (

                                                                <Link
                                                                    href="/products/create"
                                                                    className="rr-product-primary-btn"
                                                                >

                                                                    <i className="bi bi-plus-lg"></i>

                                                                    New Product

                                                                </Link>

                                                            )
                                                        }

                                                    </div>

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
                        products?.links?.length > 0 && (

                            <div className="rr-product-pagination">

                                <div>

                                    Page

                                    {' '}

                                    <strong>
                                        {
                                            products.current_page ?? 1
                                        }
                                    </strong>

                                    {' '}

                                    of

                                    {' '}

                                    <strong>
                                        {
                                            products.last_page ?? 1
                                        }
                                    </strong>

                                </div>


                                <div className="rr-product-pagination-buttons">

                                    {
                                        products.links.map(
                                            (
                                                link,
                                                index
                                            ) => (

                                                <button
                                                    key={
                                                        `${link.label}-${index}`
                                                    }
                                                    type="button"
                                                    disabled={
                                                        !link.url
                                                    }
                                                    className={
                                                        `rr-product-page-btn ${link.active
                                                            ? 'active'
                                                            : ''
                                                        }`
                                                    }
                                                    onClick={
                                                        () => {

                                                            if (
                                                                !link.url
                                                            ) {
                                                                return;
                                                            }


                                                            router.visit(
                                                                link.url,
                                                                {
                                                                    preserveState:
                                                                        true,

                                                                    preserveScroll:
                                                                        true,
                                                                }
                                                            );
                                                        }
                                                    }
                                                    dangerouslySetInnerHTML={{
                                                        __html:
                                                            link.label,
                                                    }}
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
                    COMMON CONFIRM MODAL
                   ===================================================== */}

                <ConfirmModal
                    show={
                        confirmModal.show
                    }

                    title={
                        confirmTitle
                    }

                    message={
                        confirmMessage
                    }

                    confirmText={
                        confirmText
                    }

                    cancelText="Cancel"

                    icon={
                        confirmIcon
                    }

                    variant={
                        confirmVariant
                    }

                    processing={
                        confirmModal.processing
                    }

                    confirmDisabled={
                        false
                    }

                    onConfirm={
                        confirmProductAction
                    }

                    onCancel={
                        closeConfirmModal
                    }
                />

            </div>

        </>
    );
}


/*
|--------------------------------------------------------------------------
| Summary Card
|--------------------------------------------------------------------------
*/

function SummaryCard({
    title,
    value,
    icon,
    tone,
}) {

    return (

        <div
            className={
                `rr-product-stat ${tone}`
            }
        >

            <div className="rr-product-stat-icon">

                <i
                    className={
                        `bi ${icon}`
                    }
                ></i>

            </div>


            <div>

                <span>
                    {title}
                </span>


                <strong>

                    {
                        Number(
                            value ?? 0
                        ).toLocaleString(
                            'en-IN'
                        )
                    }

                </strong>

            </div>

        </div>
    );
}


/*
|--------------------------------------------------------------------------
| Filter Box
|--------------------------------------------------------------------------
*/

function FilterBox({
    label,
    value,
    onChange,
    children,
}) {

    return (

        <div className="rr-product-filter">

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
| Product Identity
|--------------------------------------------------------------------------
*/

function ProductIdentity({
    product,
}) {

    return (

        <div className="rr-product-identity">

            <div className="rr-product-avatar">

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
                        ?? 'No Code'
                    }


                    {
                        product.part_number
                            ? ` • Part ${product.part_number}`
                            : ''
                    }

                </span>


                {
                    product.barcode && (

                        <small>

                            Barcode:
                            {' '}

                            {
                                product.barcode
                            }

                        </small>

                    )
                }

            </div>

        </div>
    );
}


/*
|--------------------------------------------------------------------------
| Price Cell
|--------------------------------------------------------------------------
*/

function PriceCell({
    value,
    discount = null,
}) {

    return (

        <td className="number rr-product-price">

            <strong>

                {
                    formatMoney(
                        value
                    )
                }

            </strong>


            {
                Number(
                    discount?.percent ?? 0
                ) > 0 && (

                    <span>

                        {
                            Number(
                                discount.percent
                            ).toFixed(
                                1
                            )
                        }

                        % off

                    </span>

                )
            }

        </td>
    );
}


/*
|--------------------------------------------------------------------------
| Stock Cell
|--------------------------------------------------------------------------
*/

function StockCell({
    value,
}) {

    return (

        <td className="number rr-product-stock-number">

            {
                formatQty(
                    value
                )
            }

        </td>
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

    const stock =
        Number(
            product.current_stock ?? 0
        );


    let className =
        'rr-product-stock-badge available';


    if (
        product.is_out_of_stock
    ) {

        className =
            'rr-product-stock-badge out';

    } else if (
        product.is_low_stock
    ) {

        className =
            'rr-product-stock-badge low';
    }


    return (

        <span className={className}>

            {
                formatQty(
                    stock
                )
            }

        </span>
    );
}


/*
|--------------------------------------------------------------------------
| Status Badge
|--------------------------------------------------------------------------
*/

function StatusBadge({
    active,
}) {

    return (

        <span
            className={
                `rr-product-status ${active
                    ? 'active'
                    : 'inactive'
                }`
            }
        >

            <span></span>


            {
                active
                    ? 'Active'
                    : 'Inactive'
            }

        </span>
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
            value ?? 0
        );


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
        Number.isFinite(
            number
        )
            ? number
            : 0
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
            value ?? 0
        );


    return new Intl.NumberFormat(
        'en-IN',
        {
            maximumFractionDigits:
                3,
        }
    ).format(
        Number.isFinite(
            number
        )
            ? number
            : 0
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
            value ?? ''
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
            item =>
                item
                    .charAt(
                        0
                    )
                    .toUpperCase()
        )
        .join(
            ''
        );
}


/*
|--------------------------------------------------------------------------
| Layout
|--------------------------------------------------------------------------
*/

Index.layout =
    page => (

        <BusinessLayout>
            {page}
        </BusinessLayout>

    );