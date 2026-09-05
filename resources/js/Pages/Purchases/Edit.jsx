import React, {
    useEffect,
    useMemo,
    useRef,
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

import '../../../css/business/purchases.css';


export default function Edit({
    purchase,
    suppliers = [],
    godowns = [],
}) {

    const {
        business,
    } = usePage().props;


    /*
    |--------------------------------------------------------------------------
    | Convert Existing Items
    |--------------------------------------------------------------------------
    */

    const initialItems =
        (purchase?.items ?? []).map(
            item => ({
                row_id:
                    `existing-${item.id}`,

                product_id:
                    item.product_id,

                product_code:
                    item.product?.product_code ?? '',

                product_name:
                    item.product?.product_name ?? '',

                unit_name:
                    item.product?.unit?.short_name
                    ??
                    item.product?.unit?.unit_name
                    ??
                    '',

                godown_id:
                    item.godown_id
                    ?? '',

                quantity:
                    String(
                        item.quantity ?? 1
                    ),

                free_quantity:
                    String(
                        item.free_quantity ?? 0
                    ),

                purchase_price:
                    String(
                        item.purchase_price ?? 0
                    ),

                discount:
                    String(
                        item.discount ?? 0
                    ),

                gst_percent:
                    String(
                        item.gst_percent ?? 0
                    ),
            })
        );


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

        _method:
            'PUT',

        supplier_id:
            purchase?.supplier_id ?? '',

        purchase_date:
            normalizeDate(
                purchase?.purchase_date
            ),

        invoice_no:
            purchase?.invoice_no ?? '',

        invoice_date:
            normalizeDate(
                purchase?.invoice_date
            ),

        tax_type:
            purchase?.tax_type ?? 'gst',

        other_charges:
            String(
                purchase?.other_charges ?? 0
            ),

        paid_amount:
            String(
                purchase?.paid_amount ?? 0
            ),

        remarks:
            purchase?.remarks ?? '',

        items:
            initialItems.length > 0
                ? initialItems
                : [
                    emptyItem(),
                ],
    });


    /*
    |--------------------------------------------------------------------------
    | Auto Focus First Validation Error
    |--------------------------------------------------------------------------
    */

    useEffect(
        () => {

            const errorKeys =
                Object.keys(
                    errors ?? {}
                );


            if (
                errorKeys.length === 0
            ) {
                return;
            }


            const firstErrorKey =
                errorKeys[0];


            window.setTimeout(
                () => {

                    focusValidationError(
                        firstErrorKey,
                        searchInputRef
                    );

                },
                80
            );

        },
        [
            errors,
        ]
    );


    /*
    |--------------------------------------------------------------------------
    | Product Search
    |--------------------------------------------------------------------------
    */

    const [
        searchText,
        setSearchText,
    ] = useState('');


    const [
        searchResults,
        setSearchResults,
    ] = useState([]);


    const [
        searchOpen,
        setSearchOpen,
    ] = useState(false);


    const [
        searching,
        setSearching,
    ] = useState(false);


    const searchTimer =
        useRef(null);


    const searchInputRef =
        useRef(null);


    /*
    |--------------------------------------------------------------------------
    | Product Search
    |--------------------------------------------------------------------------
    */

    useEffect(
        () => {

            if (
                searchTimer.current
            ) {
                clearTimeout(
                    searchTimer.current
                );
            }


            const value =
                searchText.trim();


            if (
                value.length < 1
            ) {
                setSearchResults([]);
                setSearchOpen(false);

                return;
            }


            searchTimer.current =
                setTimeout(
                    async () => {

                        setSearching(
                            true
                        );


                        try {

                            const response =
                                await fetch(
                                    `/purchases/search?search=${encodeURIComponent(value)}`,
                                    {
                                        headers: {
                                            Accept:
                                                'application/json',
                                        },
                                    }
                                );


                            if (
                                !response.ok
                            ) {
                                throw new Error(
                                    'Unable to search products.'
                                );
                            }


                            const result =
                                await response.json();


                            setSearchResults(
                                Array.isArray(result)
                                    ? result
                                    : []
                            );


                            setSearchOpen(
                                true
                            );

                        } catch (error) {

                            console.error(
                                error
                            );


                            setSearchResults([]);

                        } finally {

                            setSearching(
                                false
                            );
                        }

                    },
                    300
                );


            return () => {

                if (
                    searchTimer.current
                ) {
                    clearTimeout(
                        searchTimer.current
                    );
                }
            };

        },
        [
            searchText,
        ]
    );


    /*
    |--------------------------------------------------------------------------
    | Add Product
    |--------------------------------------------------------------------------
    */

    const addProduct =
        product => {

            const newItem = {

                row_id:
                    `${Date.now()}-${Math.random()}`,

                product_id:
                    product.id,

                product_code:
                    product.product_code ?? '',

                product_name:
                    product.product_name ?? '',

                unit_name:
                    product.unit?.short_name
                    ??
                    product.unit?.unit_name
                    ??
                    '',

                godown_id:
                    '',

                quantity:
                    '1',

                free_quantity:
                    '0',

                purchase_price:
                    String(
                        product.purchase_price ?? 0
                    ),

                discount:
                    '0',

                gst_percent:
                    data.tax_type === 'gst'
                        ? String(
                            product.gst_percent ?? 0
                        )
                        : '0',
            };


            const blankIndex =
                data.items.findIndex(
                    item =>
                        !item.product_id
                );


            const items =
                [...data.items];


            if (
                blankIndex >= 0
            ) {
                items[blankIndex] =
                    newItem;
            } else {
                items.push(
                    newItem
                );
            }


            setData(
                'items',
                items
            );


            setSearchText('');
            setSearchResults([]);
            setSearchOpen(false);


            window.setTimeout(
                () =>
                    searchInputRef.current?.focus(),
                50
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Update Item
    |--------------------------------------------------------------------------
    */

    const updateItem =
        (
            index,
            field,
            value
        ) => {

            const items =
                [...data.items];


            items[index] = {
                ...items[index],
                [field]:
                    value,
            };


            setData(
                'items',
                items
            );


            clearErrors(
                `items.${index}.${field}`
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Add Row
    |--------------------------------------------------------------------------
    */

    const addEmptyRow =
        () => {

            setData(
                'items',
                [
                    ...data.items,
                    emptyItem(),
                ]
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Remove Item
    |--------------------------------------------------------------------------
    */

    const removeItem =
        index => {

            const items =
                [...data.items];


            items.splice(
                index,
                1
            );


            setData(
                'items',
                items.length > 0
                    ? items
                    : [
                        emptyItem(),
                    ]
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Change Tax Type
    |--------------------------------------------------------------------------
    */

    const changeTaxType =
        type => {

            setData(
                previous => ({
                    ...previous,

                    tax_type:
                        type,

                    items:
                        previous.items.map(
                            item => ({
                                ...item,

                                gst_percent:
                                    type === 'gst'
                                        ? item.gst_percent
                                        : '0',
                            })
                        ),
                })
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Calculations
    |--------------------------------------------------------------------------
    */

    const calculatedItems =
        useMemo(
            () =>
                data.items.map(
                    item =>
                        calculateItem(
                            item,
                            data.tax_type
                        )
                ),
            [
                data.items,
                data.tax_type,
            ]
        );


    const totals =
        useMemo(
            () => {

                let subtotal =
                    0;

                let discount =
                    0;

                let taxable =
                    0;

                let tax =
                    0;

                let lineTotal =
                    0;


                calculatedItems.forEach(
                    item => {

                        subtotal +=
                            item.gross;

                        discount +=
                            item.discount;

                        taxable +=
                            item.taxable;

                        tax +=
                            item.gst_amount;

                        lineTotal +=
                            item.line_total;
                    }
                );


                const otherCharges =
                    money(
                        data.other_charges
                    );


                const grandTotal =
                    round2(
                        lineTotal
                        +
                        otherCharges
                    );


                const paidAmount =
                    money(
                        data.paid_amount
                    );


                const balance =
                    round2(
                        grandTotal
                        -
                        paidAmount
                    );


                return {
                    subtotal:
                        round2(
                            subtotal
                        ),

                    discount:
                        round2(
                            discount
                        ),

                    taxable:
                        round2(
                            taxable
                        ),

                    tax:
                        round2(
                            tax
                        ),

                    grand_total:
                        grandTotal,

                    paid_amount:
                        paidAmount,

                    balance:
                        balance,
                };

            },
            [
                calculatedItems,
                data.other_charges,
                data.paid_amount,
            ]
        );


    /*
    |--------------------------------------------------------------------------
    | Submit
    |--------------------------------------------------------------------------
    */

    const submit =
        event => {

            event.preventDefault();


            post(
                `/purchases/${purchase.id}`,
                {
                    preserveScroll:
                        true,
                }
            );
        };


    return (
        <>
            <Head
                title={
                    `Edit ${purchase?.purchase_no ?? 'Purchase'}`
                }
            />


            <div className="rr-purchase-page">

                <MasterNavigation
                    active="purchases"
                    title="Purchases"
                    subtitle="Edit supplier purchase and branch stock"
                />


                <form
                    onSubmit={
                        submit
                    }
                >

                    {/* =================================================
                        HEADER
                       ================================================= */}

                    <section className="rr-purchase-header">

                        <div className="rr-purchase-header-main">

                            <Link
                                href="/purchases"
                                className="rr-purchase-back-btn"
                            >
                                <i className="bi bi-arrow-left"></i>
                            </Link>


                            <div className="rr-purchase-header-icon">
                                <i className="bi bi-pencil-square"></i>
                            </div>


                            <div className="rr-purchase-header-content">

                                <span className="rr-purchase-eyebrow">
                                    Purchase Maintenance
                                </span>


                                <h1>
                                    Edit Purchase
                                </h1>


                                <div className="rr-purchase-header-meta">

                                    <span>
                                        <i className="bi bi-receipt"></i>

                                        {
                                            purchase.purchase_no
                                        }
                                    </span>


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


                        <div className="rr-purchase-header-actions">

                            <Link
                                href={`/purchases/${purchase.id}`}
                                className="rr-purchase-secondary-btn"
                            >
                                View
                            </Link>


                            <Link
                                href="/purchases"
                                className="rr-purchase-secondary-btn"
                            >
                                Cancel
                            </Link>


                            <button
                                type="submit"
                                className="rr-purchase-primary-btn"
                                disabled={
                                    processing
                                }
                            >
                                {
                                    processing
                                        ? (
                                            <>
                                                <span className="spinner-border spinner-border-sm"></span>

                                                Updating...
                                            </>
                                        )
                                        : (
                                            <>
                                                <i className="bi bi-floppy"></i>

                                                Update Purchase
                                            </>
                                        )
                                }
                            </button>

                        </div>

                    </section>


                    {/* =================================================
                        STOCK REVERSAL NOTICE
                       ================================================= */}

                    <div className="rr-purchase-edit-warning">

                        <div>
                            <i className="bi bi-arrow-repeat"></i>
                        </div>


                        <div>
                            <strong>
                                Stock will be recalculated
                            </strong>

                            <span>
                                Saving this purchase reverses the previous Shop/Godown quantities and posts the updated purchased quantity plus free quantity.
                            </span>
                        </div>

                    </div>


                    {/* =================================================
                        TAX TYPE
                       ================================================= */}

                    <section className="rr-purchase-entry-window">

                        <WindowTitle
                            icon="bi-receipt-cutoff"
                            title="Purchase Type"
                        />


                        <div className="rr-purchase-entry-body">

                            <div className="rr-purchase-tax-selector">

                                <button
                                    type="button"
                                    className={
                                        data.tax_type === 'gst'
                                            ? 'active gst'
                                            : ''
                                    }
                                    onClick={
                                        () =>
                                            changeTaxType(
                                                'gst'
                                            )
                                    }
                                >

                                    <div className="rr-purchase-tax-selector-icon">
                                        <i className="bi bi-percent"></i>
                                    </div>


                                    <div>

                                        <strong>
                                            GST Purchase
                                        </strong>

                                        <span>
                                            GST calculation enabled
                                        </span>

                                    </div>

                                </button>


                                <button
                                    type="button"
                                    className={
                                        data.tax_type === 'non_gst'
                                            ? 'active non-gst'
                                            : ''
                                    }
                                    onClick={
                                        () =>
                                            changeTaxType(
                                                'non_gst'
                                            )
                                    }
                                >

                                    <div className="rr-purchase-tax-selector-icon">
                                        <i className="bi bi-slash-circle"></i>
                                    </div>


                                    <div>

                                        <strong>
                                            Without GST
                                        </strong>

                                        <span>
                                            GST amount will be zero
                                        </span>

                                    </div>

                                </button>

                            </div>

                        </div>

                    </section>


                    {/* =================================================
                        PURCHASE HEADER
                       ================================================= */}

                    <section className="rr-purchase-entry-window">

                        <WindowTitle
                            icon="bi-file-earmark-text"
                            title="Purchase Details"
                        />


                        <div className="rr-purchase-entry-body">

                            <div className="rr-purchase-entry-grid">

                                <FormField
                                    label="Supplier"
                                    required
                                    error={
                                        errors.supplier_id
                                    }
                                    className="supplier"
                                >

                                    <select
                                        data-validation-key="supplier_id"
                                        value={
                                            data.supplier_id
                                        }
                                        onChange={
                                            event =>
                                                setData(
                                                    'supplier_id',
                                                    event.target.value
                                                )
                                        }
                                    >

                                        <option value="">
                                            Select Supplier
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

                                </FormField>


                                <FormField
                                    label="Purchase No."
                                >

                                    <div className="rr-purchase-readonly-field">

                                        <i className="bi bi-lock"></i>

                                        <strong>
                                            {
                                                purchase.purchase_no
                                            }
                                        </strong>

                                    </div>

                                </FormField>


                                <FormField
                                    label="Purchase Date"
                                    required
                                    error={
                                        errors.purchase_date
                                    }
                                >

                                    <DateInput
                                        validationKey="purchase_date"
                                        value={
                                            data.purchase_date
                                        }
                                        onChange={
                                            value =>
                                                setData(
                                                    'purchase_date',
                                                    value
                                                )
                                        }
                                    />

                                </FormField>


                                <FormField
                                    label="Supplier Invoice No."
                                    error={
                                        errors.invoice_no
                                    }
                                >

                                    <input
                                        type="text"
                                        data-validation-key="invoice_no"
                                        value={
                                            data.invoice_no
                                        }
                                        onChange={
                                            event =>
                                                setData(
                                                    'invoice_no',
                                                    event.target.value
                                                )
                                        }
                                    />

                                </FormField>


                                <FormField
                                    label="Invoice Date"
                                    error={
                                        errors.invoice_date
                                    }
                                >

                                    <DateInput
                                        validationKey="invoice_date"
                                        value={
                                            data.invoice_date
                                        }
                                        onChange={
                                            value =>
                                                setData(
                                                    'invoice_date',
                                                    value
                                                )
                                        }
                                    />

                                </FormField>

                            </div>

                        </div>

                    </section>


                    {/* =================================================
                        PRODUCT SEARCH
                       ================================================= */}

                    <section className="rr-purchase-entry-window rr-purchase-search-window">

                        <WindowTitle
                            icon="bi-search"
                            title="Add Products"
                        />


                        <div className="rr-purchase-entry-body">

                            <div className="rr-purchase-product-search">

                                {/* Search Box */}

                                <div
                                    className={
                                        `rr-purchase-product-search-box ${searchOpen
                                            ? 'is-open'
                                            : ''
                                        }`
                                    }
                                >

                                    <div className="rr-purchase-product-search-icon">

                                        <i className="bi bi-search"></i>

                                    </div>


                                    <input
                                        ref={
                                            searchInputRef
                                        }
                                        data-validation-key="items"
                                        type="search"
                                        value={
                                            searchText
                                        }
                                        autoComplete="off"
                                        placeholder="Search by product name, code, barcode or part number..."
                                        onFocus={
                                            () => {

                                                if (
                                                    searchResults.length > 0
                                                    ||
                                                    searchText.trim().length > 0
                                                ) {

                                                    setSearchOpen(
                                                        true
                                                    );
                                                }
                                            }
                                        }
                                        onChange={
                                            event =>
                                                setSearchText(
                                                    event.target.value
                                                )
                                        }
                                    />


                                    {
                                        searching && (

                                            <span className="rr-purchase-search-spinner">

                                                <span className="spinner-border spinner-border-sm"></span>

                                            </span>

                                        )
                                    }


                                    {
                                        !searching
                                        &&
                                        searchText && (

                                            <button
                                                type="button"
                                                className="rr-purchase-search-clear"
                                                title="Clear Search"
                                                aria-label="Clear Search"
                                                onClick={
                                                    () => {

                                                        setSearchText('');

                                                        setSearchResults([]);

                                                        setSearchOpen(false);

                                                        window.setTimeout(
                                                            () =>
                                                                searchInputRef.current?.focus(),
                                                            0
                                                        );
                                                    }
                                                }
                                            >

                                                <i className="bi bi-x-lg"></i>

                                            </button>

                                        )
                                    }


                                    {
                                        !searchText
                                        &&
                                        !searching && (

                                            <div className="rr-purchase-search-hint">

                                                <i className="bi bi-keyboard"></i>

                                                Search Product

                                            </div>

                                        )
                                    }

                                </div>


                                {/* Search Results */}

                                {
                                    searchOpen && (

                                        <div className="rr-purchase-search-results">

                                            {/* Results Header */}

                                            <div className="rr-purchase-search-results-header">

                                                <div>

                                                    <i className="bi bi-box-seam"></i>

                                                    <span>
                                                        Product Search
                                                    </span>

                                                </div>


                                                <span className="rr-purchase-search-count">

                                                    {
                                                        searchResults.length
                                                    }

                                                    {
                                                        searchResults.length === 1
                                                            ? ' result'
                                                            : ' results'
                                                    }

                                                </span>

                                            </div>


                                            {/* Result List */}

                                            <div className="rr-purchase-search-results-list">

                                                {
                                                    searchResults.length > 0
                                                        ? (

                                                            searchResults.map(
                                                                product => (

                                                                    <button
                                                                        key={
                                                                            product.id
                                                                        }
                                                                        type="button"
                                                                        className="rr-purchase-search-result"
                                                                        onClick={
                                                                            () =>
                                                                                addProduct(
                                                                                    product
                                                                                )
                                                                        }
                                                                    >

                                                                        <div className="rr-purchase-search-result-icon">

                                                                            <i className="bi bi-box-seam"></i>

                                                                        </div>


                                                                        <div className="rr-purchase-search-result-product">

                                                                            <strong>

                                                                                {
                                                                                    product.product_name
                                                                                    ?? 'Unnamed Product'
                                                                                }

                                                                            </strong>


                                                                            <div className="rr-purchase-search-result-meta">

                                                                                <span className="rr-search-product-code">

                                                                                    <i className="bi bi-upc"></i>

                                                                                    {
                                                                                        product.product_code
                                                                                        ?? 'No Code'
                                                                                    }

                                                                                </span>


                                                                                {
                                                                                    product.part_number && (

                                                                                        <span>

                                                                                            <i className="bi bi-hash"></i>

                                                                                            {
                                                                                                product.part_number
                                                                                            }

                                                                                        </span>

                                                                                    )
                                                                                }


                                                                                {
                                                                                    product.brand?.brand_name && (

                                                                                        <span>

                                                                                            <i className="bi bi-tag"></i>

                                                                                            {
                                                                                                product.brand.brand_name
                                                                                            }

                                                                                        </span>

                                                                                    )
                                                                                }


                                                                                {
                                                                                    product.category?.name && (

                                                                                        <span>

                                                                                            <i className="bi bi-grid"></i>

                                                                                            {
                                                                                                product.category.name
                                                                                            }

                                                                                        </span>

                                                                                    )
                                                                                }

                                                                            </div>

                                                                        </div>


                                                                        <div className="rr-purchase-search-result-tax">

                                                                            <span>
                                                                                GST
                                                                            </span>


                                                                            <strong>

                                                                                {
                                                                                    Number(
                                                                                        product.gst_percent
                                                                                        ?? 0
                                                                                    ).toFixed(
                                                                                        2
                                                                                    )
                                                                                }

                                                                                %

                                                                            </strong>

                                                                        </div>


                                                                        <div className="rr-purchase-search-result-price">

                                                                            <span>
                                                                                Purchase Rate
                                                                            </span>


                                                                            <strong>

                                                                                {
                                                                                    formatMoney(
                                                                                        product.purchase_price
                                                                                    )
                                                                                }

                                                                            </strong>

                                                                        </div>


                                                                        <div className="rr-purchase-search-result-add">

                                                                            <i className="bi bi-plus-lg"></i>

                                                                        </div>

                                                                    </button>

                                                                )
                                                            )

                                                        )
                                                        : (
                                                            !searching && (

                                                                <div className="rr-purchase-search-empty">

                                                                    <div className="rr-purchase-search-empty-icon">

                                                                        <i className="bi bi-search"></i>

                                                                    </div>


                                                                    <strong>
                                                                        No products found
                                                                    </strong>


                                                                    <span>
                                                                        Try another product name, code, barcode or part number.
                                                                    </span>

                                                                </div>

                                                            )
                                                        )
                                                }

                                            </div>


                                            <div className="rr-purchase-search-results-footer">

                                                <span>

                                                    <i className="bi bi-info-circle"></i>

                                                    Click a product to add it to this purchase.

                                                </span>


                                                <button
                                                    type="button"
                                                    onClick={
                                                        () => {

                                                            setSearchText('');

                                                            setSearchResults([]);

                                                            setSearchOpen(false);

                                                            window.setTimeout(
                                                                () =>
                                                                    searchInputRef.current?.focus(),
                                                                0
                                                            );
                                                        }
                                                    }
                                                >

                                                    <i className="bi bi-x-lg"></i>

                                                    Cancel

                                                </button>

                                            </div>

                                        </div>

                                    )
                                }

                            </div>

                        </div>

                    </section>


                    {/* =================================================
                        ITEMS
                       ================================================= */}

                    <section className="rr-purchase-entry-window">

                        <div className="rr-purchase-entry-title">

                            <div>
                                <i className="bi bi-table"></i>

                                <strong>
                                    Purchase Items
                                </strong>
                            </div>


                            <button
                                type="button"
                                className="rr-purchase-add-row-btn"
                                onClick={
                                    addEmptyRow
                                }
                            >
                                <i className="bi bi-plus-lg"></i>

                                Add Row
                            </button>

                        </div>


                        <div className="rr-purchase-items-wrap">

                            <table className="rr-purchase-items-table">

                                <thead>

                                    <tr>

                                        <th className="serial">
                                            #
                                        </th>

                                        <th className="product">
                                            Product
                                        </th>

                                        <th className="location">
                                            Location
                                        </th>

                                        <th className="qty">
                                            Qty
                                        </th>

                                        <th className="qty">
                                            Free
                                        </th>

                                        <th className="money">
                                            Rate
                                        </th>

                                        <th className="money">
                                            Discount
                                        </th>

                                        {
                                            data.tax_type === 'gst' && (
                                                <th className="gst">
                                                    GST %
                                                </th>
                                            )
                                        }

                                        <th className="money">
                                            Taxable
                                        </th>

                                        {
                                            data.tax_type === 'gst' && (
                                                <th className="money">
                                                    GST
                                                </th>
                                            )
                                        }

                                        <th className="money">
                                            Total
                                        </th>

                                        <th className="action"></th>

                                    </tr>

                                </thead>


                                <tbody>

                                    {
                                        data.items.map(
                                            (
                                                item,
                                                index
                                            ) => {

                                                const calc =
                                                    calculatedItems[index];


                                                return (

                                                    <tr
                                                        key={
                                                            item.row_id
                                                            ?? index
                                                        }
                                                    >

                                                        <td className="serial">
                                                            {
                                                                index + 1
                                                            }
                                                        </td>


                                                        <td className="product">

                                                            {
                                                                item.product_id
                                                                    ? (
                                                                        <div className="rr-purchase-item-product">

                                                                            <strong>
                                                                                {
                                                                                    item.product_name
                                                                                }
                                                                            </strong>

                                                                            <span>
                                                                                {
                                                                                    item.product_code
                                                                                }

                                                                                {
                                                                                    item.unit_name
                                                                                        ? ` • ${item.unit_name}`
                                                                                        : ''
                                                                                }
                                                                            </span>

                                                                        </div>
                                                                    )
                                                                    : (
                                                                        <button
                                                                            type="button"
                                                                            className="rr-purchase-select-product-btn"
                                                                            data-item-index={index}
                                                                            data-item-field="product_id"
                                                                            onClick={
                                                                                () =>
                                                                                    searchInputRef.current?.focus()
                                                                            }
                                                                        >
                                                                            <i className="bi bi-search"></i>

                                                                            Select Product
                                                                        </button>
                                                                    )
                                                            }

                                                            <ItemError
                                                                error={
                                                                    errors[
                                                                    `items.${index}.product_id`
                                                                    ]
                                                                }
                                                            />

                                                        </td>


                                                        <td className="location">

                                                            <select
                                                                data-item-index={index}
                                                                data-item-field="godown_id"
                                                                value={
                                                                    item.godown_id
                                                                }
                                                                onChange={
                                                                    event =>
                                                                        updateItem(
                                                                            index,
                                                                            'godown_id',
                                                                            event.target.value
                                                                        )
                                                                }
                                                            >

                                                                <option value="">
                                                                    Shop
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
                                                                            </option>
                                                                        )
                                                                    )
                                                                }

                                                            </select>

                                                        </td>


                                                        <td className="qty">

                                                            <input
                                                                type="number"
                                                                data-item-index={index}
                                                                data-item-field="quantity"
                                                                min="1"
                                                                step="1"
                                                                value={
                                                                    item.quantity
                                                                }
                                                                onChange={
                                                                    event =>
                                                                        updateItem(
                                                                            index,
                                                                            'quantity',
                                                                            event.target.value
                                                                        )
                                                                }
                                                            />

                                                        </td>


                                                        <td className="qty">

                                                            <input
                                                                type="number"
                                                                data-item-index={index}
                                                                data-item-field="free_quantity"
                                                                min="0"
                                                                step="1"
                                                                value={
                                                                    item.free_quantity
                                                                }
                                                                onChange={
                                                                    event =>
                                                                        updateItem(
                                                                            index,
                                                                            'free_quantity',
                                                                            event.target.value
                                                                        )
                                                                }
                                                            />

                                                        </td>


                                                        <td className="money">

                                                            <MoneyInput
                                                                itemIndex={index}
                                                                itemField="purchase_price"
                                                                value={
                                                                    item.purchase_price
                                                                }
                                                                onChange={
                                                                    value =>
                                                                        updateItem(
                                                                            index,
                                                                            'purchase_price',
                                                                            value
                                                                        )
                                                                }
                                                            />

                                                        </td>


                                                        <td className="money">

                                                            <MoneyInput
                                                                itemIndex={index}
                                                                itemField="discount"
                                                                value={
                                                                    item.discount
                                                                }
                                                                onChange={
                                                                    value =>
                                                                        updateItem(
                                                                            index,
                                                                            'discount',
                                                                            value
                                                                        )
                                                                }
                                                            />

                                                        </td>


                                                        {
                                                            data.tax_type === 'gst' && (

                                                                <td className="gst">

                                                                    <select
                                                                        data-item-index={index}
                                                                        data-item-field="gst_percent"
                                                                        value={
                                                                            item.gst_percent
                                                                        }
                                                                        onChange={
                                                                            event =>
                                                                                updateItem(
                                                                                    index,
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

                                                                </td>
                                                            )
                                                        }


                                                        <td className="money calculated">
                                                            {
                                                                formatMoney(
                                                                    calc?.taxable
                                                                )
                                                            }
                                                        </td>


                                                        {
                                                            data.tax_type === 'gst' && (

                                                                <td className="money calculated gst-value">
                                                                    {
                                                                        formatMoney(
                                                                            calc?.gst_amount
                                                                        )
                                                                    }
                                                                </td>
                                                            )
                                                        }


                                                        <td className="money calculated total">
                                                            {
                                                                formatMoney(
                                                                    calc?.line_total
                                                                )
                                                            }
                                                        </td>


                                                        <td className="action">

                                                            <button
                                                                type="button"
                                                                className="rr-purchase-remove-item"
                                                                onClick={
                                                                    () =>
                                                                        removeItem(
                                                                            index
                                                                        )
                                                                }
                                                            >
                                                                <i className="bi bi-trash3"></i>
                                                            </button>

                                                        </td>

                                                    </tr>
                                                );
                                            }
                                        )
                                    }

                                </tbody>

                            </table>

                        </div>

                    </section>


                    {/* =================================================
                        BOTTOM
                       ================================================= */}

                    <div className="rr-purchase-bottom-layout">

                        <section className="rr-purchase-entry-window">

                            <WindowTitle
                                icon="bi-card-text"
                                title="Remarks"
                            />


                            <div className="rr-purchase-entry-body">

                                <textarea
                                    data-validation-key="remarks"
                                    rows="5"
                                    value={
                                        data.remarks
                                    }
                                    onChange={
                                        event =>
                                            setData(
                                                'remarks',
                                                event.target.value
                                            )
                                    }
                                ></textarea>

                                <ErrorText
                                    message={
                                        errors.remarks
                                    }
                                />

                            </div>

                        </section>


                        <section className="rr-purchase-total-window">

                            <div className="rr-purchase-total-title">

                                <i className="bi bi-calculator"></i>

                                Purchase Summary

                            </div>


                            <div className="rr-purchase-total-body">

                                <TotalRow
                                    label="Subtotal"
                                    value={
                                        totals.subtotal
                                    }
                                />


                                <TotalRow
                                    label="Discount"
                                    value={
                                        totals.discount
                                    }
                                    negative
                                />


                                <TotalRow
                                    label="Taxable Amount"
                                    value={
                                        totals.taxable
                                    }
                                />


                                {
                                    data.tax_type === 'gst' && (

                                        <TotalRow
                                            label="GST"
                                            value={
                                                totals.tax
                                            }
                                        />

                                    )
                                }


                                <div className="rr-purchase-total-input-row">

                                    <label>
                                        Other Charges
                                    </label>


                                    <MoneyInput
                                        validationKey="other_charges"
                                        value={
                                            data.other_charges
                                        }
                                        onChange={
                                            value =>
                                                setData(
                                                    'other_charges',
                                                    value
                                                )
                                        }
                                    />

                                </div>


                                <div className="rr-purchase-grand-total">

                                    <span>
                                        Grand Total
                                    </span>

                                    <strong>
                                        {
                                            formatMoney(
                                                totals.grand_total
                                            )
                                        }
                                    </strong>

                                </div>


                                <div className="rr-purchase-total-input-row paid">

                                    <label>
                                        Paid Amount
                                    </label>


                                    <MoneyInput
                                        validationKey="paid_amount"
                                        value={
                                            data.paid_amount
                                        }
                                        onChange={
                                            value =>
                                                setData(
                                                    'paid_amount',
                                                    value
                                                )
                                        }
                                    />

                                </div>


                                <ErrorText
                                    message={
                                        errors.paid_amount
                                    }
                                />


                                <div
                                    className={
                                        `rr-purchase-balance-summary ${totals.balance > 0
                                            ? 'pending'
                                            : 'paid'
                                        }`
                                    }
                                >

                                    <span>
                                        Balance
                                    </span>

                                    <strong>
                                        {
                                            formatMoney(
                                                Math.max(
                                                    0,
                                                    totals.balance
                                                )
                                            )
                                        }
                                    </strong>

                                </div>

                            </div>

                        </section>

                    </div>


                    {/* =================================================
                        COMMAND BAR
                       ================================================= */}

                    <div className="rr-purchase-command-bar">

                        <div>
                            <i className="bi bi-arrow-repeat"></i>

                            Existing stock will be reversed and the updated purchase stock will be posted.
                        </div>


                        <div>

                            <Link
                                href={`/purchases/${purchase.id}`}
                                className="rr-purchase-secondary-btn"
                            >
                                Cancel
                            </Link>


                            <button
                                type="submit"
                                className="rr-purchase-primary-btn"
                                disabled={
                                    processing
                                }
                            >
                                {
                                    processing
                                        ? (
                                            <>
                                                <span className="spinner-border spinner-border-sm"></span>

                                                Updating...
                                            </>
                                        )
                                        : (
                                            <>
                                                <i className="bi bi-check-lg"></i>

                                                Update Purchase
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
| Empty Item
|--------------------------------------------------------------------------
*/

function emptyItem() {
    return {
        row_id:
            `${Date.now()}-${Math.random()}`,

        product_id:
            '',

        product_code:
            '',

        product_name:
            '',

        unit_name:
            '',

        godown_id:
            '',

        quantity:
            '1',

        free_quantity:
            '0',

        purchase_price:
            '0',

        discount:
            '0',

        gst_percent:
            '0',
    };
}


/*
|--------------------------------------------------------------------------
| Window Title
|--------------------------------------------------------------------------
*/

function WindowTitle({
    icon,
    title,
}) {

    return (

        <div className="rr-purchase-entry-title">

            <div>

                <i className={`bi ${icon}`}></i>

                <strong>
                    {title}
                </strong>

            </div>

        </div>
    );
}


/*
|--------------------------------------------------------------------------
| Form Field
|--------------------------------------------------------------------------
*/

function FormField({
    label,
    required = false,
    error,
    className = '',
    children,
}) {

    return (

        <div
            className={
                `rr-purchase-entry-field ${className}`
            }
        >

            <label>

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
                    <div className="rr-purchase-field-error">
                        <i className="bi bi-exclamation-circle"></i>

                        {error}
                    </div>
                )
            }

        </div>
    );
}


/*
|--------------------------------------------------------------------------
| Error Text
|--------------------------------------------------------------------------
*/

function ErrorText({
    message,
}) {

    if (
        !message
    ) {
        return null;
    }


    return (

        <div className="rr-purchase-field-error">

            <i className="bi bi-exclamation-circle"></i>

            {message}

        </div>
    );
}


/*
|--------------------------------------------------------------------------
| Item Error
|--------------------------------------------------------------------------
*/

function ItemError({
    error,
}) {

    if (
        !error
    ) {
        return null;
    }


    return (
        <span className="rr-purchase-item-error">
            {error}
        </span>
    );
}


/*
|--------------------------------------------------------------------------
| Date Input
|--------------------------------------------------------------------------
*/

function DateInput({
    value,
    onChange,
    validationKey = '',
}) {

    const inputRef =
        useRef(
            null
        );


    const openPicker =
        () => {

            const input =
                inputRef.current;


            if (
                !input
            ) {
                return;
            }


            if (
                typeof input.showPicker ===
                'function'
            ) {

                try {
                    input.showPicker();

                    return;
                } catch {
                    // fallback
                }
            }


            input.focus();
        };


    return (

        <div
            className="rr-purchase-entry-date"
            onClick={
                openPicker
            }
        >

            <input
                ref={
                    inputRef
                }
                type="date"
                data-validation-key={
                    validationKey || undefined
                }
                value={
                    value
                }
                onClick={
                    event => {

                        event.stopPropagation();

                        openPicker();
                    }
                }
                onChange={
                    event =>
                        onChange(
                            event.target.value
                        )
                }
            />


            <button
                type="button"
                tabIndex="-1"
                onClick={
                    event => {

                        event.stopPropagation();

                        openPicker();
                    }
                }
            >
                <i className="bi bi-calendar3"></i>
            </button>

        </div>
    );
}


/*
|--------------------------------------------------------------------------
| Money Input
|--------------------------------------------------------------------------
*/

function MoneyInput({
    value,
    onChange,
    validationKey = '',
    itemIndex = null,
    itemField = '',
}) {

    return (

        <div className="rr-purchase-money-input">

            <span>
                ₹
            </span>


            <input
                type="number"
                data-validation-key={
                    validationKey || undefined
                }
                data-item-index={
                    itemIndex ?? undefined
                }
                data-item-field={
                    itemField || undefined
                }
                min="0"
                step="1"
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
    );
}


/*
|--------------------------------------------------------------------------
| Total Row
|--------------------------------------------------------------------------
*/

function TotalRow({
    label,
    value,
    negative = false,
}) {

    return (

        <div className="rr-purchase-total-row">

            <span>
                {label}
            </span>


            <strong
                className={
                    negative && numberValue(value) > 0
                        ? 'negative'
                        : ''
                }
            >

                {
                    negative && numberValue(value) > 0
                        ? '- '
                        : ''
                }

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
| Calculate Item
|--------------------------------------------------------------------------
*/

function calculateItem(
    item,
    taxType
) {

    const quantity =
        numberValue(
            item.quantity
        );


    const rate =
        money(
            item.purchase_price
        );


    const gross =
        round2(
            quantity
            *
            rate
        );


    const discount =
        Math.min(
            gross,
            money(
                item.discount
            )
        );


    const taxable =
        round2(
            Math.max(
                0,
                gross
                -
                discount
            )
        );


    const gstPercent =
        taxType === 'gst'
            ? numberValue(
                item.gst_percent
            )
            : 0;


    const gstAmount =
        taxType === 'gst'
            ? round2(
                taxable
                *
                gstPercent
                /
                100
            )
            : 0;


    return {
        gross,

        discount,

        taxable,

        gst_amount:
            gstAmount,

        line_total:
            round2(
                taxable
                +
                gstAmount
            ),
    };
}


/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function numberValue(
    value
) {

    const number =
        Number(
            value ?? 0
        );


    return Number.isFinite(
        number
    )
        ? number
        : 0;
}


function money(
    value
) {

    return round2(
        numberValue(
            value
        )
    );
}


function round2(
    value
) {

    return Math.round(
        (
            numberValue(
                value
            )
            +
            Number.EPSILON
        )
        *
        100
    ) / 100;
}


function formatMoney(
    value
) {

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
        numberValue(
            value
        )
    );
}


/*
|--------------------------------------------------------------------------
| Validation Focus Helper
|--------------------------------------------------------------------------
*/

function focusValidationError(
    errorKey,
    searchInputRef
) {

    let target =
        null;


    const itemMatch =
        String(
            errorKey
        ).match(
            /^items\.(\d+)\.([a-zA-Z0-9_]+)$/
        );


    if (
        itemMatch
    ) {

        const index =
            itemMatch[1];

        const field =
            itemMatch[2];


        if (
            field === 'product_id'
        ) {

            target =
                document.querySelector(
                    `[data-item-index="${index}"][data-item-field="product_id"]`
                )
                ??
                searchInputRef.current;

        } else {

            target =
                document.querySelector(
                    `[data-item-index="${index}"][data-item-field="${field}"]`
                );
        }

    } else if (
        errorKey === 'items'
    ) {

        target =
            searchInputRef.current;

    } else {

        target =
            document.querySelector(
                `[data-validation-key="${errorKey}"]`
            );
    }


    if (
        !target
    ) {
        return;
    }


    target.scrollIntoView({
        behavior:
            'smooth',

        block:
            'center',
    });


    target.classList.add(
        'rr-validation-focus'
    );


    window.setTimeout(
        () => {

            if (
                typeof target.focus === 'function'
            ) {

                target.focus({
                    preventScroll:
                        true,
                });
            }

        },
        280
    );


    window.setTimeout(
        () => {

            target.classList.remove(
                'rr-validation-focus'
            );

        },
        2200
    );
}


/*
|--------------------------------------------------------------------------
| Normalize Laravel Date
|--------------------------------------------------------------------------
*/

function normalizeDate(
    value
) {

    if (
        !value
    ) {
        return '';
    }


    return String(
        value
    ).substring(
        0,
        10
    );
}


/*
|--------------------------------------------------------------------------
| Layout
|--------------------------------------------------------------------------
*/

Edit.layout =
    page => (

        <BusinessLayout>
            {page}
        </BusinessLayout>

    );