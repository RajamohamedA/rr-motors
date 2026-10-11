import React from 'react';



import {

    Head,

    Link,

    usePage,

} from '@inertiajs/react';



import BusinessLayout from '@/Layouts/BusinessLayout';



import MasterNavigation

    from '../../Components/Business/MasterNavigation';



import '../../../css/business/products.css';





export default function Show({

    product,

}) {



    const {

        business,

    } = usePage().props;





    const locations =

        product?.locations ?? [];





    return (



        <>



            <Head

                title={

                    product?.product_name

                    ?? 'Product'

                }

            />





            <div className="rr-product-page">



                {/* =====================================================

                    MASTER NAVIGATION

                   ===================================================== */}



                <MasterNavigation

                    active="products"

                    title="Products"

                    subtitle="Product details, pricing and branch stock"

                />





                {/* =====================================================

                    HEADER

                   ===================================================== */}



                <section className="rr-product-header">



                    <div className="rr-product-header-main">



                        <Link

                            href="/products"

                            className="rr-product-back-btn"

                            title="Back to Products"

                        >



                            <i className="bi bi-arrow-left"></i>



                        </Link>





                        <div className="rr-product-header-icon">



                            <i className="bi bi-box-seam"></i>



                        </div>





                        <div className="rr-product-header-content">



                            <span className="rr-product-eyebrow">

                                Product Information

                            </span>





                            <h1>



                                {

                                    product?.product_name

                                    ?? 'Product'

                                }



                            </h1>





                            <div className="rr-product-header-meta">



                                <span>



                                    <i className="bi bi-upc-scan"></i>



                                    {

                                        product?.product_code

                                        ?? 'No Code'

                                    }



                                </span>





                                <span>



                                    <i className="bi bi-shop"></i>



                                    {

                                        business?.store_name

                                        ?? 'Branch'

                                    }



                                </span>





                                <span>



                                    <i className="bi bi-buildings"></i>



                                    {

                                        business?.company_name

                                        ?? 'Company'

                                    }



                                </span>



                            </div>



                        </div>



                    </div>





                    <div className="rr-product-header-actions">



                        <Link
                            href={`/barcode-labels?product_id=${product.id}`}
                            className="rr-product-primary-btn label"
                            title="Print Barcode Label"
                        >
                            <i className="bi bi-upc-scan"></i>
                            Print Label
                        </Link>



                        <Link

                            href={`/products/${product.id}/edit`}

                            className="rr-product-primary-btn"

                        >



                            <i className="bi bi-pencil"></i>



                            Edit Product



                        </Link>



                    </div>



                </section>





                {/* =====================================================

                    SUMMARY CARDS

                   ===================================================== */}



                <section className="rr-product-summary">



                    <SummaryCard

                        title="Total Stock"

                        value={

                            formatQty(

                                product?.current_stock

                            )

                        }

                        icon="bi-stack"

                        tone={

                            product?.is_out_of_stock

                                ? 'danger'

                                : product?.is_low_stock

                                    ? 'warning'

                                    : 'success'

                        }

                    />





                    <SummaryCard

                        title="Shop Stock"

                        value={

                            formatQty(

                                product?.shop_stock

                            )

                        }

                        icon="bi-shop"

                        tone="primary"

                    />





                    <SummaryCard

                        title="Godown Stock"

                        value={

                            formatQty(

                                product?.godown_stock

                            )

                        }

                        icon="bi-boxes"

                        tone="info"

                    />





                    <SummaryCard

                        title="MRP"

                        value={

                            formatMoney(

                                product?.mrp

                            )

                        }

                        icon="bi-currency-rupee"

                        tone="neutral"

                    />



                </section>





                {/* =====================================================

                    MAIN LAYOUT

                   ===================================================== */}



                <div className="rr-product-show-layout">



                    {/* =================================================

                        LEFT SIDE

                       ================================================= */}



                    <div className="rr-product-show-main">



                        {/* =============================================

                            GENERAL

                           ============================================= */}



                        <ProductWindow

                            title="General Details"

                            icon="bi-info-circle"

                        >



                            <div className="rr-product-detail-grid">



                                <DetailItem

                                    label="Product Code"

                                    value={

                                        product?.product_code

                                    }

                                />





                                <DetailItem

                                    label="Barcode"

                                    value={

                                        product?.barcode

                                    }

                                />





                                <DetailItem

                                    label="Product Name"

                                    value={

                                        product?.product_name

                                    }

                                />





                                <DetailItem

                                    label="Part Number"

                                    value={

                                        product?.part_number

                                    }

                                />





                                <DetailItem

                                    label="Category"

                                    value={

                                        product?.category?.name

                                    }

                                />





                                <DetailItem

                                    label="Brand"

                                    value={

                                        product?.brand?.brand_name

                                    }

                                />





                                <DetailItem

                                    label="Unit"

                                    value={

                                        product?.unit?.short_name

                                        ??

                                        product?.unit?.unit_name

                                    }

                                />





                                <DetailItem

                                    label="Supplier"

                                    value={

                                        product?.supplier?.supplier_name

                                    }

                                />





                                <DetailItem

                                    label="Manufacturer"

                                    value={

                                        product?.manufacturer

                                    }

                                />





                                <DetailItem

                                    label="Rack Location"

                                    value={

                                        product?.rack_location

                                    }

                                />





                                <DetailItem

                                    label="HSN Code"

                                    value={

                                        product?.hsn_code

                                    }

                                />





                                <DetailItem

                                    label="GST"

                                    value={

                                        `${Number(

                                            product?.gst_percent ?? 0

                                        ).toFixed(2)}%`

                                    }

                                />



                            </div>



                        </ProductWindow>





                        {/* =============================================

                            PRICING

                           ============================================= */}



                        <ProductWindow

                            title="Pricing"

                            icon="bi-cash-stack"

                        >



                            <div className="rr-product-show-price-grid">



                                <ShowPriceCard

                                    title="Purchase Price"

                                    value={

                                        product?.purchase_price

                                    }

                                    type="purchase"

                                />





                                <ShowPriceCard

                                    title="MRP"

                                    value={

                                        product?.mrp

                                    }

                                    type="mrp"

                                />





                                <ShowPriceCard

                                    title="Retail Price"

                                    value={

                                        product?.selling_price

                                    }

                                    discount={

                                        product?.retail_discount

                                    }

                                />





                                <ShowPriceCard

                                    title="Wholesale Price"

                                    value={

                                        product?.wholesale_price

                                    }

                                    discount={

                                        product?.wholesale_discount

                                    }

                                />





                                <ShowPriceCard

                                    title="Mechanic Price"

                                    value={

                                        product?.mechanic_price

                                    }

                                    discount={

                                        product?.mechanic_discount

                                    }

                                />



                            </div>



                        </ProductWindow>





                        {/* =============================================

                            STOCK

                           ============================================= */}



                        <ProductWindow

                            title="Stock By Location"

                            icon="bi-boxes"

                        >



                            <div className="rr-product-stock-summary-row">



                                <StockSummary

                                    label="Shop"

                                    icon="bi-shop"

                                    value={

                                        product?.shop_stock

                                    }

                                />





                                <StockSummary

                                    label="Godown Total"

                                    icon="bi-boxes"

                                    value={

                                        product?.godown_stock

                                    }

                                />





                                <StockSummary

                                    label="Grand Total"

                                    icon="bi-stack"

                                    value={

                                        product?.current_stock

                                    }

                                    highlight

                                />



                            </div>





                            <div className="rr-product-location-table-wrap">



                                <table className="rr-product-location-table">



                                    <thead>



                                        <tr>



                                            <th>

                                                Location

                                            </th>



                                            <th>

                                                Code

                                            </th>



                                            <th className="number">

                                                Quantity

                                            </th>



                                        </tr>



                                    </thead>





                                    <tbody>



                                        {

                                            locations.length > 0

                                                ? (



                                                    locations.map(

                                                        (

                                                            location,

                                                            index

                                                        ) => (



                                                            <tr

                                                                key={

                                                                    `${location.location_type}-${location.godown_id ?? index}`

                                                                }

                                                            >



                                                                <td>



                                                                    <div className="rr-product-location-name">



                                                                        <span

                                                                            className={

                                                                                location.location_type ===

                                                                                    'shop'

                                                                                    ? 'shop'

                                                                                    : 'godown'

                                                                            }

                                                                        >



                                                                            <i

                                                                                className={

                                                                                    location.location_type ===

                                                                                        'shop'

                                                                                        ? 'bi bi-shop'

                                                                                        : 'bi bi-boxes'

                                                                                }

                                                                            ></i>



                                                                        </span>





                                                                        <strong>



                                                                            {

                                                                                location.name

                                                                                ?? 'Location'

                                                                            }



                                                                        </strong>



                                                                    </div>



                                                                </td>





                                                                <td>



                                                                    {

                                                                        location.code

                                                                        ?? '—'

                                                                    }



                                                                </td>





                                                                <td className="number">



                                                                    <strong>



                                                                        {

                                                                            formatQty(

                                                                                location.quantity

                                                                            )

                                                                        }



                                                                    </strong>



                                                                </td>



                                                            </tr>



                                                        )

                                                    )



                                                )

                                                : (



                                                    <tr>



                                                        <td

                                                            colSpan="3"

                                                            className="rr-product-location-empty"

                                                        >



                                                            No stock ledger entries found.



                                                        </td>



                                                    </tr>



                                                )

                                        }



                                    </tbody>



                                </table>



                            </div>





                            <div className="rr-product-stock-limits">



                                <DetailItem

                                    label="Opening Stock"

                                    value={

                                        formatQty(

                                            product?.opening_stock

                                        )

                                    }

                                />





                                <DetailItem

                                    label="Minimum Stock"

                                    value={

                                        formatQty(

                                            product?.minimum_stock

                                        )

                                    }

                                />





                                <DetailItem

                                    label="Maximum Stock"

                                    value={

                                        product?.maximum_stock !== null

                                            ? formatQty(

                                                product?.maximum_stock

                                            )

                                            : 'Not Set'

                                    }

                                />





                                <DetailItem

                                    label="Stock Status"

                                    value={

                                        stockStatusLabel(

                                            product

                                        )

                                    }

                                    badge={

                                        stockStatusType(

                                            product

                                        )

                                    }

                                />



                            </div>



                        </ProductWindow>





                        {/* =============================================

                            DESCRIPTION

                           ============================================= */}



                        <ProductWindow

                            title="Description"

                            icon="bi-card-text"

                        >



                            <div className="rr-product-description">



                                {

                                    product?.description

                                    ||

                                    'No description available.'

                                }



                            </div>



                        </ProductWindow>



                    </div>





                    {/* =================================================

                        RIGHT SIDE

                       ================================================= */}



                    <aside className="rr-product-show-side">



                        {/* =============================================

                            IMAGE

                           ============================================= */}



                        <div className="rr-product-side-card">



                            <div className="rr-product-side-title">



                                <i className="bi bi-image"></i>



                                Product Image



                            </div>





                            <div className="rr-product-image-box">



                                {

                                    product?.image

                                        ? (



                                            <img

                                                src={`/storage/${product.image}`}

                                                alt={

                                                    product.product_name

                                                }

                                            />



                                        )

                                        : (



                                            <div className="rr-product-image-empty">



                                                <i className="bi bi-image"></i>



                                                <span>

                                                    No Product Image

                                                </span>



                                            </div>



                                        )

                                }



                            </div>



                        </div>





                        {/* =============================================

                            STATUS

                           ============================================= */}



                        <div className="rr-product-side-card">



                            <div className="rr-product-side-title">



                                <i className="bi bi-activity"></i>



                                Status



                            </div>





                            <div className="rr-product-show-status-box">



                                <StatusBadge

                                    active={

                                        product?.status

                                    }

                                />





                                <StockStatusBadge

                                    product={

                                        product

                                    }

                                />



                            </div>



                        </div>





                        {/* =============================================

                            BUSINESS CONTEXT

                           ============================================= */}



                        <div className="rr-product-side-card">



                            <div className="rr-product-side-title">



                                <i className="bi bi-building"></i>



                                Business Context



                            </div>





                            <InfoRow

                                label="Company"

                                value={

                                    business?.company_name

                                    ?? '—'

                                }

                            />





                            <InfoRow

                                label="Branch"

                                value={

                                    business?.store_name

                                    ?? '—'

                                }

                            />





                            <InfoRow

                                label="Product Code"

                                value={

                                    product?.product_code

                                    ?? '—'

                                }

                            />





                            <InfoRow

                                label="Created"

                                value={

                                    formatDate(

                                        product?.created_at

                                    )

                                }

                            />





                            <InfoRow

                                label="Updated"

                                value={

                                    formatDate(

                                        product?.updated_at

                                    )

                                }

                            />



                        </div>





                        {/* =============================================

                            QUICK PRICES

                           ============================================= */}



                        <div className="rr-product-side-card">



                            <div className="rr-product-side-title">



                                <i className="bi bi-currency-rupee"></i>



                                Price Snapshot



                            </div>





                            <PriceInfo

                                label="MRP"

                                value={

                                    product?.mrp

                                }

                            />





                            <PriceInfo

                                label="Retail"

                                value={

                                    product?.selling_price

                                }

                                discount={

                                    product?.retail_discount

                                }

                            />





                            <PriceInfo

                                label="Wholesale"

                                value={

                                    product?.wholesale_price

                                }

                                discount={

                                    product?.wholesale_discount

                                }

                            />





                            <PriceInfo

                                label="Mechanic"

                                value={

                                    product?.mechanic_price

                                }

                                discount={

                                    product?.mechanic_discount

                                }

                            />



                        </div>



                    </aside>



                </div>





                {/* =====================================================

                    COMMAND BAR

                   ===================================================== */}



                <div className="rr-product-command-bar">



                    <div>



                        <i className="bi bi-info-circle"></i>



                        Product details for the active branch.



                    </div>





                    <div>



                        <Link

                            href="/products"

                            className="rr-product-secondary-btn"

                        >



                            <i className="bi bi-arrow-left"></i>



                            Back



                        </Link>





                        <Link

                            href={`/products/${product.id}/edit`}

                            className="rr-product-primary-btn"

                        >



                            <i className="bi bi-pencil"></i>



                            Edit Product



                        </Link>



                    </div>



                </div>



            </div>



        </>

    );

}





/*

|--------------------------------------------------------------------------

| Window

|--------------------------------------------------------------------------

*/



function ProductWindow({

    title,

    icon,

    children,

}) {



    return (



        <section className="rr-product-form-window">



            <div className="rr-product-form-window-title">



                <div>



                    <i

                        className={

                            `bi ${icon}`

                        }

                    ></i>





                    <strong>

                        {title}

                    </strong>



                </div>



            </div>





            <div className="rr-product-form-window-body">



                {children}



            </div>



        </section>

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

    tone = 'primary',

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

                    {value}

                </strong>



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

    badge = null,

}) {



    return (



        <div className="rr-product-detail-item">



            <span>

                {label}

            </span>





            {

                badge

                    ? (



                        <span

                            className={

                                `rr-product-detail-badge ${badge}`

                            }

                        >



                            {

                                value

                                ?? '—'

                            }



                        </span>



                    )

                    : (



                        <strong>



                            {

                                value

                                ||

                                '—'

                            }



                        </strong>



                    )

            }



        </div>

    );

}





/*

|--------------------------------------------------------------------------

| Price Card

|--------------------------------------------------------------------------

*/



function ShowPriceCard({

    title,

    value,

    discount = null,

    type = '',

}) {



    return (



        <div

            className={

                `rr-product-show-price-card ${type}`

            }

        >



            <span>

                {title}

            </span>





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



                    <small>



                        Save



                        {' '}



                        {

                            formatMoney(

                                discount.amount

                            )

                        }



                        {' • '}



                        {

                            Number(

                                discount.percent

                            ).toFixed(

                                2

                            )

                        }



                        %



                    </small>



                )

            }



        </div>

    );

}





/*

|--------------------------------------------------------------------------

| Stock Summary

|--------------------------------------------------------------------------

*/



function StockSummary({

    label,

    icon,

    value,

    highlight = false,

}) {



    return (



        <div

            className={

                `rr-product-stock-summary ${highlight

                    ? 'highlight'

                    : ''

                }`

            }

        >



            <div>



                <i

                    className={

                        `bi ${icon}`

                    }

                ></i>



            </div>





            <span>

                {label}

            </span>





            <strong>



                {

                    formatQty(

                        value

                    )

                }



            </strong>



        </div>

    );

}





/*

|--------------------------------------------------------------------------

| Status

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

| Stock Status

|--------------------------------------------------------------------------

*/



function StockStatusBadge({

    product,

}) {



    const type =

        stockStatusType(

            product

        );





    return (



        <span

            className={

                `rr-product-stock-status ${type}`

            }

        >



            {

                stockStatusLabel(

                    product

                )

            }



        </span>

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



        <div className="rr-product-info-row">



            <span>

                {label}

            </span>





            <strong>

                {

                    value

                    || '—'

                }

            </strong>



        </div>

    );

}





/*

|--------------------------------------------------------------------------

| Price Info

|--------------------------------------------------------------------------

*/



function PriceInfo({

    label,

    value,

    discount = null,

}) {



    return (



        <div className="rr-product-price-info">



            <div>



                <span>

                    {label}

                </span>





                {

                    Number(

                        discount?.percent ?? 0

                    ) > 0 && (



                        <small>



                            {

                                Number(

                                    discount.percent

                                ).toFixed(

                                    1

                                )

                            }



                            % off



                        </small>



                    )

                }



            </div>





            <strong>



                {

                    formatMoney(

                        value

                    )

                }



            </strong>



        </div>

    );

}





/*

|--------------------------------------------------------------------------

| Stock Status Helpers

|--------------------------------------------------------------------------

*/



function stockStatusType(

    product

) {



    if (

        product?.is_out_of_stock

    ) {



        return 'out';

    }





    if (

        product?.is_low_stock

    ) {



        return 'low';

    }





    return 'available';

}





function stockStatusLabel(

    product

) {



    if (

        product?.is_out_of_stock

    ) {



        return 'Out of Stock';

    }





    if (

        product?.is_low_stock

    ) {



        return 'Low Stock';

    }





    return 'Stock Available';

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

| Date

|--------------------------------------------------------------------------

*/



function formatDate(

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



        return '—';

    }





    return new Intl.DateTimeFormat(

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

    ).format(

        date

    );

}





/*

|--------------------------------------------------------------------------

| Layout

|--------------------------------------------------------------------------

*/



Show.layout =

    page => (



        <BusinessLayout>

            {page}

        </BusinessLayout>



    );
