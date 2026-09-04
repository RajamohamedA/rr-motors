import React, {
    useMemo,
    useState,
} from 'react';

import {
    Head,
    Link,
    useForm,
    usePage,
} from '@inertiajs/react';

import BusinessLayout from '@/Layouts/BusinessLayout';

import MasterNavigation
    from '../../Components/Business/MasterNavigation';

import '../../../css/business/products.css';


export default function Create({
    categories = [],
    brands = [],
    units = [],
    suppliers = [],
    godowns = [],
}) {

    /*
    |--------------------------------------------------------------------------
    | Shared Business Context
    |--------------------------------------------------------------------------
    */

    const {
        business,
    } = usePage().props;


    /*
    |--------------------------------------------------------------------------
    | Form
    |--------------------------------------------------------------------------
    */

    const {
        data,
        setData,
        post,
        processing,
        errors,
        clearErrors,
    } = useForm({

        product_code:
            '',

        barcode:
            '',

        product_name:
            '',

        category_id:
            '',

        brand_id:
            '',

        unit_id:
            '',

        supplier_id:
            '',


        part_number:
            '',

        manufacturer:
            '',

        rack_location:
            '',


        hsn_code:
            '',

        gst_percent:
            '18',


        purchase_price:
            '',

        mrp:
            '',

        selling_price:
            '',

        wholesale_price:
            '',

        mechanic_price:
            '',


        opening_stock:
            '0',

        opening_location:
            'shop',

        godown_id:
            '',


        minimum_stock:
            '0',

        maximum_stock:
            '',


        description:
            '',

        image:
            null,

        status:
            true,
    });


    /*
    |--------------------------------------------------------------------------
    | Image Preview
    |--------------------------------------------------------------------------
    */

    const [
        imagePreview,
        setImagePreview,
    ] = useState(
        null
    );


    /*
    |--------------------------------------------------------------------------
    | Number Helper
    |--------------------------------------------------------------------------
    */

    const numberValue =
        value => {

            const number =
                Number(
                    value
                );


            return Number.isFinite(
                number
            )
                ? number
                : 0;
        };


    /*
    |--------------------------------------------------------------------------
    | Discount
    |--------------------------------------------------------------------------
    */

    const calculateDiscount =
        price => {

            const mrp =
                numberValue(
                    data.mrp
                );


            const sellingPrice =
                numberValue(
                    price
                );


            if (
                mrp <= 0
                ||
                sellingPrice <= 0
            ) {

                return {

                    amount:
                        0,

                    percent:
                        0,
                };
            }


            const amount =
                Math.max(
                    0,
                    mrp - sellingPrice
                );


            return {

                amount:
                    amount,

                percent:
                    (
                        amount
                        /
                        mrp
                    ) * 100,
            };
        };


    /*
    |--------------------------------------------------------------------------
    | Discounts
    |--------------------------------------------------------------------------
    */

    const retailDiscount =
        useMemo(
            () =>
                calculateDiscount(
                    data.selling_price
                ),
            [
                data.mrp,
                data.selling_price,
            ]
        );


    const wholesaleDiscount =
        useMemo(
            () =>
                calculateDiscount(
                    data.wholesale_price
                ),
            [
                data.mrp,
                data.wholesale_price,
            ]
        );


    const mechanicDiscount =
        useMemo(
            () =>
                calculateDiscount(
                    data.mechanic_price
                ),
            [
                data.mrp,
                data.mechanic_price,
            ]
        );


    /*
    |--------------------------------------------------------------------------
    | Update Field
    |--------------------------------------------------------------------------
    */

    const updateField =
        (
            field,
            value
        ) => {

            setData(
                field,
                value
            );


            if (
                errors?.[field]
            ) {

                clearErrors(
                    field
                );
            }
        };


    /*
    |--------------------------------------------------------------------------
    | Opening Location
    |--------------------------------------------------------------------------
    */

    const setOpeningLocation =
        location => {

            updateField(
                'opening_location',
                location
            );


            if (
                location ===
                'shop'
            ) {

                updateField(
                    'godown_id',
                    ''
                );
            }
        };


    /*
    |--------------------------------------------------------------------------
    | Image
    |--------------------------------------------------------------------------
    */

    const handleImageChange =
        event => {

            const file =
                event.target.files?.[0]
                ?? null;


            updateField(
                'image',
                file
            );


            if (
                imagePreview
            ) {

                URL.revokeObjectURL(
                    imagePreview
                );
            }


            if (
                file
            ) {

                setImagePreview(
                    URL.createObjectURL(
                        file
                    )
                );

            } else {

                setImagePreview(
                    null
                );
            }
        };


    /*
    |--------------------------------------------------------------------------
    | Submit
    |--------------------------------------------------------------------------
    */

    const submit =
        event => {

            event.preventDefault();


            post(
                '/products',
                {
                    forceFormData:
                        true,

                    preserveScroll:
                        true,
                }
            );
        };


    /*
    |--------------------------------------------------------------------------
    | CSS Helpers
    |--------------------------------------------------------------------------
    */

    const fieldClass =
        field =>
            `form-control form-control-sm ${errors[field]
                ? 'is-invalid'
                : ''
            }`;


    const selectClass =
        field =>
            `form-select form-select-sm ${errors[field]
                ? 'is-invalid'
                : ''
            }`;


    /*
    |--------------------------------------------------------------------------
    | Selected Godown Name
    |--------------------------------------------------------------------------
    */

    const selectedGodown =
        godowns.find(
            godown =>
                String(
                    godown.id
                )
                ===
                String(
                    data.godown_id
                )
        );


    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */

    return (

        <>

            <Head
                title="Create Product"
            />


            <div className="rr-product-page">

                {/* =====================================================
                    MASTER NAVIGATION
                   ===================================================== */}

                <MasterNavigation
                    active="products"
                    title="Products"
                    subtitle="Create product, pricing and opening stock"
                />


                <form
                    onSubmit={
                        submit
                    }
                    encType="multipart/form-data"
                >

                    {/* =================================================
                        HEADER
                       ================================================= */}

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
                                    Product Entry
                                </span>


                                <h1>
                                    Create Product
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
                                href="/products"
                                className="rr-product-secondary-btn"
                            >

                                Cancel

                            </Link>


                            <button
                                type="submit"
                                className="rr-product-primary-btn"
                                disabled={
                                    processing
                                }
                            >

                                {
                                    processing
                                        ? (
                                            <>
                                                <span className="spinner-border spinner-border-sm"></span>

                                                Saving...
                                            </>
                                        )
                                        : (
                                            <>
                                                <i className="bi bi-floppy"></i>

                                                Save Product
                                            </>
                                        )
                                }

                            </button>

                        </div>

                    </section>


                    {/* =================================================
                        MAIN WORKSPACE
                       ================================================= */}

                    <div className="rr-product-form-layout">

                        {/* =============================================
                            LEFT MAIN FORM
                           ============================================= */}

                        <div className="rr-product-form-main">

                            {/* =========================================
                                GENERAL
                               ========================================= */}

                            <ProductWindow
                                title="General Details"
                                icon="bi-box-seam"
                            >

                                <div className="row g-2">

                                    <Field
                                        label="Product Name"
                                        required
                                        error={
                                            errors.product_name
                                        }
                                        className="col-xl-6 col-lg-6"
                                    >

                                        <input
                                            autoFocus
                                            type="text"
                                            className={
                                                fieldClass(
                                                    'product_name'
                                                )
                                            }
                                            value={
                                                data.product_name
                                            }
                                            placeholder="Enter product name"
                                            onChange={
                                                event =>
                                                    updateField(
                                                        'product_name',
                                                        event.target.value
                                                    )
                                            }
                                        />

                                    </Field>


                                    <Field
                                        label="Product Code"
                                        error={
                                            errors.product_code
                                        }
                                        className="col-xl-3 col-lg-3"
                                    >

                                        <input
                                            type="text"
                                            className={
                                                fieldClass(
                                                    'product_code'
                                                )
                                            }
                                            value={
                                                data.product_code
                                            }
                                            placeholder="Auto if blank"
                                            onChange={
                                                event =>
                                                    updateField(
                                                        'product_code',
                                                        event.target.value
                                                            .toUpperCase()
                                                    )
                                            }
                                        />


                                        <div className="product-field-help">
                                            Leave blank for automatic code.
                                        </div>

                                    </Field>


                                    <Field
                                        label="Barcode"
                                        error={
                                            errors.barcode
                                        }
                                        className="col-xl-3 col-lg-3"
                                    >

                                        <input
                                            type="text"
                                            className={
                                                fieldClass(
                                                    'barcode'
                                                )
                                            }
                                            value={
                                                data.barcode
                                            }
                                            placeholder="Scan or enter barcode"
                                            onChange={
                                                event =>
                                                    updateField(
                                                        'barcode',
                                                        event.target.value
                                                    )
                                            }
                                        />

                                    </Field>


                                    <Field
                                        label="Category"
                                        required
                                        error={
                                            errors.category_id
                                        }
                                        className="col-xl-3 col-lg-3"
                                    >

                                        <select
                                            className={
                                                selectClass(
                                                    'category_id'
                                                )
                                            }
                                            value={
                                                data.category_id
                                            }
                                            onChange={
                                                event =>
                                                    updateField(
                                                        'category_id',
                                                        event.target.value
                                                    )
                                            }
                                        >

                                            <option value="">
                                                Select Category
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

                                    </Field>


                                    <Field
                                        label="Brand"
                                        error={
                                            errors.brand_id
                                        }
                                        className="col-xl-3 col-lg-3"
                                    >

                                        <select
                                            className={
                                                selectClass(
                                                    'brand_id'
                                                )
                                            }
                                            value={
                                                data.brand_id
                                            }
                                            onChange={
                                                event =>
                                                    updateField(
                                                        'brand_id',
                                                        event.target.value
                                                    )
                                            }
                                        >

                                            <option value="">
                                                No Brand
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

                                        </select>

                                    </Field>


                                    <Field
                                        label="Unit"
                                        required
                                        error={
                                            errors.unit_id
                                        }
                                        className="col-xl-3 col-lg-3"
                                    >

                                        <select
                                            className={
                                                selectClass(
                                                    'unit_id'
                                                )
                                            }
                                            value={
                                                data.unit_id
                                            }
                                            onChange={
                                                event =>
                                                    updateField(
                                                        'unit_id',
                                                        event.target.value
                                                    )
                                            }
                                        >

                                            <option value="">
                                                Select Unit
                                            </option>


                                            {
                                                units.map(
                                                    unit => (

                                                        <option
                                                            key={
                                                                unit.id
                                                            }
                                                            value={
                                                                unit.id
                                                            }
                                                        >

                                                            {
                                                                unit.unit_name
                                                            }

                                                            {
                                                                unit.short_name
                                                                    ? ` (${unit.short_name})`
                                                                    : ''
                                                            }

                                                        </option>

                                                    )
                                                )
                                            }

                                        </select>

                                    </Field>


                                    <Field
                                        label="Preferred Supplier"
                                        error={
                                            errors.supplier_id
                                        }
                                        className="col-xl-3 col-lg-3"
                                    >

                                        <select
                                            className={
                                                selectClass(
                                                    'supplier_id'
                                                )
                                            }
                                            value={
                                                data.supplier_id
                                            }
                                            onChange={
                                                event =>
                                                    updateField(
                                                        'supplier_id',
                                                        event.target.value
                                                    )
                                            }
                                        >

                                            <option value="">
                                                No Preferred Supplier
                                            </option>


                                            {
                                                suppliers.map(
                                                    supplier => (

                                                        <option
                                                            key={
                                                                supplier.id
                                                            }
                                                            value={
                                                                supplier.id
                                                            }
                                                        >

                                                            {
                                                                supplier.supplier_name
                                                            }

                                                        </option>

                                                    )
                                                )
                                            }

                                        </select>

                                    </Field>


                                    <Field
                                        label="Part Number"
                                        error={
                                            errors.part_number
                                        }
                                        className="col-xl-3 col-lg-3"
                                    >

                                        <input
                                            type="text"
                                            className={
                                                fieldClass(
                                                    'part_number'
                                                )
                                            }
                                            value={
                                                data.part_number
                                            }
                                            onChange={
                                                event =>
                                                    updateField(
                                                        'part_number',
                                                        event.target.value
                                                    )
                                            }
                                        />

                                    </Field>


                                    <Field
                                        label="Manufacturer"
                                        error={
                                            errors.manufacturer
                                        }
                                        className="col-xl-3 col-lg-3"
                                    >

                                        <input
                                            type="text"
                                            className={
                                                fieldClass(
                                                    'manufacturer'
                                                )
                                            }
                                            value={
                                                data.manufacturer
                                            }
                                            onChange={
                                                event =>
                                                    updateField(
                                                        'manufacturer',
                                                        event.target.value
                                                    )
                                            }
                                        />

                                    </Field>


                                    <Field
                                        label="Rack Location"
                                        error={
                                            errors.rack_location
                                        }
                                        className="col-xl-3 col-lg-3"
                                    >

                                        <input
                                            type="text"
                                            className={
                                                fieldClass(
                                                    'rack_location'
                                                )
                                            }
                                            value={
                                                data.rack_location
                                            }
                                            placeholder="Example: A-12"
                                            onChange={
                                                event =>
                                                    updateField(
                                                        'rack_location',
                                                        event.target.value
                                                    )
                                            }
                                        />

                                    </Field>


                                    <Field
                                        label="HSN Code"
                                        error={
                                            errors.hsn_code
                                        }
                                        className="col-xl-3 col-lg-3"
                                    >

                                        <input
                                            type="text"
                                            className={
                                                fieldClass(
                                                    'hsn_code'
                                                )
                                            }
                                            value={
                                                data.hsn_code
                                            }
                                            onChange={
                                                event =>
                                                    updateField(
                                                        'hsn_code',
                                                        event.target.value
                                                    )
                                            }
                                        />

                                    </Field>

                                </div>

                            </ProductWindow>


                            {/* =========================================
                                PRICING
                               ========================================= */}

                            <ProductWindow
                                title="Pricing"
                                icon="bi-currency-rupee"
                            >

                                <div className="rr-product-price-grid">

                                    <PriceBox
                                        title="Purchase"
                                        subtitle="Buying Cost"
                                        value={
                                            data.purchase_price
                                        }
                                        error={
                                            errors.purchase_price
                                        }
                                        onChange={
                                            value =>
                                                updateField(
                                                    'purchase_price',
                                                    value
                                                )
                                        }
                                    />


                                    <PriceBox
                                        title="MRP"
                                        subtitle="Printed / Reference"
                                        value={
                                            data.mrp
                                        }
                                        error={
                                            errors.mrp
                                        }
                                        highlight
                                        onChange={
                                            value =>
                                                updateField(
                                                    'mrp',
                                                    value
                                                )
                                        }
                                    />


                                    <PriceBox
                                        title="Retail"
                                        subtitle="Normal Customer"
                                        value={
                                            data.selling_price
                                        }
                                        error={
                                            errors.selling_price
                                        }
                                        discount={
                                            retailDiscount
                                        }
                                        onChange={
                                            value =>
                                                updateField(
                                                    'selling_price',
                                                    value
                                                )
                                        }
                                    />


                                    <PriceBox
                                        title="Wholesale"
                                        subtitle="Bulk Customer"
                                        value={
                                            data.wholesale_price
                                        }
                                        error={
                                            errors.wholesale_price
                                        }
                                        discount={
                                            wholesaleDiscount
                                        }
                                        onChange={
                                            value =>
                                                updateField(
                                                    'wholesale_price',
                                                    value
                                                )
                                        }
                                    />


                                    <PriceBox
                                        title="Mechanic"
                                        subtitle="Workshop / Mechanic"
                                        value={
                                            data.mechanic_price
                                        }
                                        error={
                                            errors.mechanic_price
                                        }
                                        discount={
                                            mechanicDiscount
                                        }
                                        onChange={
                                            value =>
                                                updateField(
                                                    'mechanic_price',
                                                    value
                                                )
                                        }
                                    />

                                </div>


                                <div className="rr-product-tax-row">

                                    <Field
                                        label="GST %"
                                        required
                                        error={
                                            errors.gst_percent
                                        }
                                        className="col-xl-3 col-lg-4"
                                    >

                                        <select
                                            className={
                                                selectClass(
                                                    'gst_percent'
                                                )
                                            }
                                            value={
                                                data.gst_percent
                                            }
                                            onChange={
                                                event =>
                                                    updateField(
                                                        'gst_percent',
                                                        event.target.value
                                                    )
                                            }
                                        >

                                            <option value="0">
                                                0%
                                            </option>

                                            <option value="5">
                                                5%
                                            </option>

                                            <option value="12">
                                                12%
                                            </option>

                                            <option value="18">
                                                18%
                                            </option>

                                            <option value="28">
                                                28%
                                            </option>

                                        </select>

                                    </Field>

                                </div>

                            </ProductWindow>


                            {/* =========================================
                                STOCK
                               ========================================= */}

                            <ProductWindow
                                title="Stock Settings"
                                icon="bi-stack"
                            >

                                <div className="row g-2">

                                    <Field
                                        label="Opening Stock"
                                        required
                                        error={
                                            errors.opening_stock
                                        }
                                        className="col-xl-2 col-lg-3"
                                    >

                                        <input
                                            type="number"
                                            step="0.001"
                                            min="0"
                                            className={
                                                fieldClass(
                                                    'opening_stock'
                                                )
                                            }
                                            value={
                                                data.opening_stock
                                            }
                                            onChange={
                                                event =>
                                                    updateField(
                                                        'opening_stock',
                                                        event.target.value
                                                    )
                                            }
                                        />

                                    </Field>


                                    <Field
                                        label="Opening Location"
                                        required
                                        error={
                                            errors.opening_location
                                        }
                                        className="col-xl-4 col-lg-5"
                                    >

                                        <div className="rr-product-location-choice">

                                            <button
                                                type="button"
                                                className={
                                                    data.opening_location ===
                                                        'shop'
                                                        ? 'active'
                                                        : ''
                                                }
                                                onClick={
                                                    () =>
                                                        setOpeningLocation(
                                                            'shop'
                                                        )
                                                }
                                            >

                                                <i className="bi bi-shop"></i>

                                                <span>
                                                    Shop
                                                </span>

                                            </button>


                                            <button
                                                type="button"
                                                disabled={
                                                    godowns.length === 0
                                                }
                                                className={
                                                    data.opening_location ===
                                                        'godown'
                                                        ? 'active'
                                                        : ''
                                                }
                                                onClick={
                                                    () =>
                                                        setOpeningLocation(
                                                            'godown'
                                                        )
                                                }
                                            >

                                                <i className="bi bi-boxes"></i>

                                                <span>
                                                    Godown
                                                </span>

                                            </button>

                                        </div>

                                    </Field>


                                    {
                                        data.opening_location ===
                                        'godown' && (

                                            <Field
                                                label="Godown"
                                                required
                                                error={
                                                    errors.godown_id
                                                }
                                                className="col-xl-3 col-lg-4"
                                            >

                                                <select
                                                    className={
                                                        selectClass(
                                                            'godown_id'
                                                        )
                                                    }
                                                    value={
                                                        data.godown_id
                                                    }
                                                    onChange={
                                                        event =>
                                                            updateField(
                                                                'godown_id',
                                                                event.target.value
                                                            )
                                                    }
                                                >

                                                    <option value="">
                                                        Select Godown
                                                    </option>


                                                    {
                                                        godowns.map(
                                                            godown => (

                                                                <option
                                                                    key={
                                                                        godown.id
                                                                    }
                                                                    value={
                                                                        godown.id
                                                                    }
                                                                >

                                                                    {
                                                                        godown.godown_name
                                                                    }

                                                                    {
                                                                        godown.godown_code
                                                                            ? ` (${godown.godown_code})`
                                                                            : ''
                                                                    }

                                                                </option>

                                                            )
                                                        )
                                                    }

                                                </select>

                                            </Field>

                                        )
                                    }


                                    <Field
                                        label="Minimum Stock"
                                        required
                                        error={
                                            errors.minimum_stock
                                        }
                                        className="col-xl-2 col-lg-3"
                                    >

                                        <input
                                            type="number"
                                            step="0.001"
                                            min="0"
                                            className={
                                                fieldClass(
                                                    'minimum_stock'
                                                )
                                            }
                                            value={
                                                data.minimum_stock
                                            }
                                            onChange={
                                                event =>
                                                    updateField(
                                                        'minimum_stock',
                                                        event.target.value
                                                    )
                                            }
                                        />

                                    </Field>


                                    <Field
                                        label="Maximum Stock"
                                        error={
                                            errors.maximum_stock
                                        }
                                        className="col-xl-2 col-lg-3"
                                    >

                                        <input
                                            type="number"
                                            step="0.001"
                                            min="0"
                                            className={
                                                fieldClass(
                                                    'maximum_stock'
                                                )
                                            }
                                            value={
                                                data.maximum_stock
                                            }
                                            onChange={
                                                event =>
                                                    updateField(
                                                        'maximum_stock',
                                                        event.target.value
                                                    )
                                            }
                                        />

                                    </Field>

                                </div>


                                {
                                    godowns.length === 0 && (

                                        <div className="rr-product-notice">

                                            <i className="bi bi-info-circle"></i>


                                            <span>
                                                This branch has no Godown.
                                                Opening stock will be stored in Shop stock.
                                            </span>

                                        </div>

                                    )
                                }


                                {
                                    Number(
                                        data.opening_stock
                                    ) > 0 && (

                                        <div className="rr-product-stock-preview">

                                            <div>

                                                <span>
                                                    Opening Transaction
                                                </span>


                                                <strong>

                                                    {
                                                        formatQty(
                                                            data.opening_stock
                                                        )
                                                    }

                                                    {' '}

                                                    {
                                                        getUnitName(
                                                            units,
                                                            data.unit_id
                                                        )
                                                    }

                                                </strong>

                                            </div>


                                            <div className="rr-product-stock-arrow">

                                                <i className="bi bi-arrow-right"></i>

                                            </div>


                                            <div>

                                                <span>
                                                    Destination
                                                </span>


                                                <strong>

                                                    {
                                                        data.opening_location ===
                                                            'shop'
                                                            ? 'Shop'
                                                            : selectedGodown?.godown_name
                                                            ?? 'Select Godown'
                                                    }

                                                </strong>

                                            </div>

                                        </div>

                                    )
                                }

                            </ProductWindow>


                            {/* =========================================
                                DESCRIPTION
                               ========================================= */}

                            <ProductWindow
                                title="Additional Information"
                                icon="bi-card-text"
                            >

                                <Field
                                    label="Description"
                                    error={
                                        errors.description
                                    }
                                    className="col-12"
                                >

                                    <textarea
                                        rows="4"
                                        className={
                                            fieldClass(
                                                'description'
                                            )
                                        }
                                        value={
                                            data.description
                                        }
                                        placeholder="Product notes, technical details or additional information..."
                                        onChange={
                                            event =>
                                                updateField(
                                                    'description',
                                                    event.target.value
                                                )
                                        }
                                    />

                                </Field>

                            </ProductWindow>

                        </div>


                        {/* =============================================
                            RIGHT INFORMATION PANEL
                           ============================================= */}

                        <aside className="rr-product-form-side">

                            {/* =========================================
                                IMAGE
                               ========================================= */}

                            <div className="rr-product-side-card">

                                <div className="rr-product-side-title">

                                    <i className="bi bi-image"></i>

                                    Product Image

                                </div>


                                <div className="rr-product-image-box">

                                    {
                                        imagePreview
                                            ? (

                                                <img
                                                    src={
                                                        imagePreview
                                                    }
                                                    alt="Product Preview"
                                                />

                                            )
                                            : (

                                                <div className="rr-product-image-empty">

                                                    <i className="bi bi-image"></i>

                                                    <span>
                                                        No Image
                                                    </span>

                                                </div>

                                            )
                                    }

                                </div>


                                <label className="rr-product-upload-btn">

                                    <i className="bi bi-upload"></i>

                                    Choose Image


                                    <input
                                        type="file"
                                        accept="image/png,image/jpeg,image/webp"
                                        onChange={
                                            handleImageChange
                                        }
                                    />

                                </label>


                                {
                                    errors.image && (

                                        <div className="invalid-feedback d-block">
                                            {errors.image}
                                        </div>

                                    )
                                }

                            </div>


                            {/* =========================================
                                STATUS
                               ========================================= */}

                            <div className="rr-product-side-card">

                                <div className="rr-product-side-title">

                                    <i className="bi bi-toggle-on"></i>

                                    Product Status

                                </div>


                                <div className="rr-product-status-selector">

                                    <button
                                        type="button"
                                        className={
                                            data.status
                                                ? 'active'
                                                : ''
                                        }
                                        onClick={
                                            () =>
                                                updateField(
                                                    'status',
                                                    true
                                                )
                                        }
                                    >

                                        <i className="bi bi-check-circle"></i>

                                        Active

                                    </button>


                                    <button
                                        type="button"
                                        className={
                                            !data.status
                                                ? 'inactive active'
                                                : 'inactive'
                                        }
                                        onClick={
                                            () =>
                                                updateField(
                                                    'status',
                                                    false
                                                )
                                        }
                                    >

                                        <i className="bi bi-pause-circle"></i>

                                        Inactive

                                    </button>

                                </div>

                            </div>


                            {/* =========================================
                                BUSINESS CONTEXT
                               ========================================= */}

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
                                        data.product_code
                                        || 'Auto Generate'
                                    }
                                />


                                <InfoRow
                                    label="GST"
                                    value={
                                        `${data.gst_percent}%`
                                    }
                                />

                            </div>


                            {/* =========================================
                                PRICE SNAPSHOT
                               ========================================= */}

                            <div className="rr-product-side-card">

                                <div className="rr-product-side-title">

                                    <i className="bi bi-cash-stack"></i>

                                    Price Snapshot

                                </div>


                                <PriceInfo
                                    label="MRP"
                                    value={
                                        data.mrp
                                    }
                                />


                                <PriceInfo
                                    label="Retail"
                                    value={
                                        data.selling_price
                                    }
                                    discount={
                                        retailDiscount
                                    }
                                />


                                <PriceInfo
                                    label="Wholesale"
                                    value={
                                        data.wholesale_price
                                    }
                                    discount={
                                        wholesaleDiscount
                                    }
                                />


                                <PriceInfo
                                    label="Mechanic"
                                    value={
                                        data.mechanic_price
                                    }
                                    discount={
                                        mechanicDiscount
                                    }
                                />

                            </div>

                        </aside>

                    </div>


                    {/* =================================================
                        STICKY COMMAND BAR
                       ================================================= */}

                    <div className="rr-product-command-bar">

                        <div>

                            <i className="bi bi-info-circle"></i>

                            Fields marked * are required.

                        </div>


                        <div>

                            <Link
                                href="/products"
                                className="rr-product-secondary-btn"
                            >

                                Cancel

                            </Link>


                            <button
                                type="submit"
                                className="rr-product-primary-btn"
                                disabled={
                                    processing
                                }
                            >

                                {
                                    processing
                                        ? (
                                            <>
                                                <span className="spinner-border spinner-border-sm"></span>

                                                Saving...
                                            </>
                                        )
                                        : (
                                            <>
                                                <i className="bi bi-check-lg"></i>

                                                Save Product
                                            </>
                                        )
                                }

                            </button>

                        </div>

                    </div>

                </form>

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
| Field
|--------------------------------------------------------------------------
*/

function Field({
    label,
    required = false,
    error,
    className = '',
    children,
}) {

    return (

        <div className={className}>

            <label className="product-field-label">

                {label}


                {
                    required && (

                        <span className="text-danger ms-1">
                            *
                        </span>

                    )
                }

            </label>


            {children}


            {
                error && (

                    <div className="invalid-feedback d-block">

                        <i className="bi bi-exclamation-circle me-1"></i>

                        {error}

                    </div>

                )
            }

        </div>
    );
}


/*
|--------------------------------------------------------------------------
| Price Box
|--------------------------------------------------------------------------
*/

function PriceBox({
    title,
    subtitle,
    value,
    error,
    discount = null,
    highlight = false,
    onChange,
}) {

    return (

        <div
            className={
                `rr-product-price-box ${highlight
                    ? 'highlight'
                    : ''
                }`
            }
        >

            <div className="rr-product-price-box-title">

                <strong>
                    {title}
                </strong>


                <span>
                    {subtitle}
                </span>

            </div>


            <div className="rr-product-price-box-input">

                <span>
                    ₹
                </span>


                <input
                    type="number"
                    min="0"
                    step="0.01"
                    className={
                        error
                            ? 'is-invalid'
                            : ''
                    }
                    value={
                        value
                    }
                    onChange={
                        event =>
                            onChange(
                                event.target.value
                            )
                    }
                />

            </div>


            {
                discount?.amount > 0 && (

                    <div className="rr-product-price-discount">

                        Save ₹
                        {
                            discount.amount.toFixed(
                                2
                            )
                        }

                        {' • '}

                        {
                            discount.percent.toFixed(
                                2
                            )
                        }

                        %

                    </div>

                )
            }


            {
                error && (

                    <div className="invalid-feedback d-block">

                        {error}

                    </div>

                )
            }

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

        <div className="rr-product-info-row">

            <span>
                {label}
            </span>


            <strong>
                {value}
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
                                discount.percent.toFixed(
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
| Qty
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
| Unit Name
|--------------------------------------------------------------------------
*/

function getUnitName(
    units,
    id
) {

    const unit =
        units.find(
            item =>
                String(
                    item.id
                )
                ===
                String(
                    id
                )
        );


    return unit?.short_name
        ??
        unit?.unit_name
        ??
        'units';
}


/*
|--------------------------------------------------------------------------
| Layout
|--------------------------------------------------------------------------
*/

Create.layout =
    page => (

        <BusinessLayout>
            {page}
        </BusinessLayout>

    );