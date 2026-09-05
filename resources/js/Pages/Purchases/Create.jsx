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


/*
|--------------------------------------------------------------------------
| Empty Item
|--------------------------------------------------------------------------
*/

const emptyItem = () => ({
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
});


export default function Create({
    purchaseNo = '',
    suppliers = [],
    godowns = [],
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

        supplier_id:
            '',

        purchase_date:
            today(),

        invoice_no:
            '',

        invoice_date:
            '',

        tax_type:
            'gst',

        other_charges:
            '0',

        paid_amount:
            '0',

        remarks:
            '',

        items: [
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
        searching,
        setSearching,
    ] = useState(false);


    const [
        searchOpen,
        setSearchOpen,
    ] = useState(false);


    const searchTimer =
        useRef(null);


    const searchInputRef =
        useRef(null);


    /*
    |--------------------------------------------------------------------------
    | Search Products
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

                            /*
                            |--------------------------------------------------------------------------
                            | Purchase Product Search
                            |--------------------------------------------------------------------------
                            |
                            | Route should point to:
                            |
                            | PurchaseController@search
                            |
                            */

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
                                    'Product search failed.'
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


                            setSearchResults(
                                []
                            );

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

            /*
            |--------------------------------------------------------------------------
            | If blank initial row exists, replace it.
            |--------------------------------------------------------------------------
            */

            const blankIndex =
                data.items.findIndex(
                    item =>
                        !item.product_id
                );


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
                        product.purchase_price
                        ?? 0
                    ),

                discount:
                    '0',

                gst_percent:
                    data.tax_type === 'gst'
                        ? String(
                            product.gst_percent
                            ?? 0
                        )
                        : '0',
            };


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
                () => {

                    searchInputRef.current?.focus();

                },
                50
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Add Empty Row
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
    | Remove Row
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


            const errorKey =
                `items.${index}.${field}`;


            if (
                errors?.[errorKey]
            ) {

                clearErrors(
                    errorKey
                );
            }
        };


    /*
    |--------------------------------------------------------------------------
    | Change Tax Type
    |--------------------------------------------------------------------------
    */

    const changeTaxType =
        type => {

            const items =
                data.items.map(
                    item => ({
                        ...item,

                        gst_percent:
                            type === 'gst'
                                ? item.gst_percent
                                : '0',
                    })
                );


            setData(previous => ({
                ...previous,

                tax_type:
                    type,

                items:
                    items,
            }));
        };


    /*
    |--------------------------------------------------------------------------
    | Calculated Items
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


    /*
    |--------------------------------------------------------------------------
    | Totals
    |--------------------------------------------------------------------------
    */

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

                    other_charges:
                        otherCharges,

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


            /*
            |--------------------------------------------------------------------------
            | Remove UI-only values before submitting
            |--------------------------------------------------------------------------
            */

            const items =
                data.items
                    .filter(
                        item =>
                            item.product_id
                    )
                    .map(
                        item => ({

                            product_id:
                                item.product_id,

                            godown_id:
                                item.godown_id
                                || null,

                            quantity:
                                item.quantity,

                            free_quantity:
                                item.free_quantity,

                            purchase_price:
                                item.purchase_price,

                            discount:
                                item.discount,

                            gst_percent:
                                data.tax_type === 'gst'
                                    ? item.gst_percent
                                    : 0,
                        })
                    );


            setData(
                'items',
                items
            );


            /*
            |--------------------------------------------------------------------------
            | useForm state update is asynchronous.
            | Send after React updates.
            |--------------------------------------------------------------------------
            */

            window.setTimeout(
                () => {

                    post(
                        '/purchases',
                        {
                            preserveScroll:
                                true,
                        }
                    );

                },
                0
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */

    return (
        <>
            <Head
                title="Create Purchase"
            />


            <div className="rr-purchase-page">

                {/* =====================================================
                    MASTER MENU
                   ===================================================== */}

                <MasterNavigation
                    active="purchases"
                    title="Purchases"
                    subtitle="Create supplier purchase and receive branch stock"
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
                                title="Back to Purchases"
                            >

                                <i className="bi bi-arrow-left"></i>

                            </Link>


                            <div className="rr-purchase-header-icon">

                                <i className="bi bi-cart-plus"></i>

                            </div>


                            <div className="rr-purchase-header-content">

                                <span className="rr-purchase-eyebrow">
                                    Purchase Entry
                                </span>


                                <h1>
                                    New Purchase
                                </h1>


                                <div className="rr-purchase-header-meta">

                                    <span>

                                        <i className="bi bi-receipt"></i>

                                        {
                                            purchaseNo
                                            || 'Auto Number'
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

                                                Saving...
                                            </>
                                        )
                                        : (
                                            <>
                                                <i className="bi bi-floppy"></i>

                                                Save Purchase
                                            </>
                                        )
                                }

                            </button>

                        </div>

                    </section>


                    {/* =================================================
                        TAX MODE
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
                                            Calculate GST for purchase items
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
                                            Purchase without tax calculation
                                        </span>

                                    </div>

                                </button>

                            </div>


                            {
                                errors.tax_type && (

                                    <ErrorText
                                        message={
                                            errors.tax_type
                                        }
                                    />

                                )
                            }

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

                                {/* Supplier */}

                                <FormField
                                    label="Supplier"
                                    required
                                    error={
                                        errors.supplier_id
                                    }
                                    className="supplier"
                                >

                                    <select
                                        id="purchase-supplier"
                                        data-validation-key="supplier_id"
                                        value={
                                            data.supplier_id
                                        }
                                        className={
                                            errors.supplier_id
                                                ? 'is-invalid'
                                                : ''
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


                                {/* Purchase Number */}

                                <FormField
                                    label="Purchase No."
                                >

                                    <div className="rr-purchase-readonly-field">

                                        <i className="bi bi-receipt"></i>

                                        <strong>

                                            {
                                                purchaseNo
                                                || 'Auto'
                                            }

                                        </strong>

                                    </div>

                                </FormField>


                                {/* Purchase Date */}

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


                                {/* Invoice No */}

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
                                        className={
                                            errors.invoice_no
                                                ? 'is-invalid'
                                                : ''
                                        }
                                        placeholder="Supplier invoice number"
                                        onChange={
                                            event =>
                                                setData(
                                                    'invoice_no',
                                                    event.target.value
                                                )
                                        }
                                    />

                                </FormField>


                                {/* Invoice Date */}

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
                        PRODUCT GRID
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
                                                    calculatedItems[index]
                                                    ??
                                                    calculateItem(
                                                        item,
                                                        data.tax_type
                                                    );


                                                return (

                                                    <tr
                                                        key={
                                                            item.row_id
                                                            ?? index
                                                        }
                                                    >

                                                        {/* Serial */}

                                                        <td className="serial">

                                                            {
                                                                index + 1
                                                            }

                                                        </td>


                                                        {/* Product */}

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


                                                        {/* Location */}

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


                                                            <ItemError
                                                                error={
                                                                    errors[
                                                                    `items.${index}.godown_id`
                                                                    ]
                                                                }
                                                            />

                                                        </td>


                                                        {/* Qty */}

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


                                                            <ItemError
                                                                error={
                                                                    errors[
                                                                    `items.${index}.quantity`
                                                                    ]
                                                                }
                                                            />

                                                        </td>


                                                        {/* Free */}

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


                                                        {/* Rate */}

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


                                                        {/* Discount */}

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


                                                        {/* GST */}

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


                                                        {/* Taxable */}

                                                        <td className="money calculated">

                                                            {
                                                                formatMoney(
                                                                    calc.taxable
                                                                )
                                                            }

                                                        </td>


                                                        {/* GST Amount */}

                                                        {
                                                            data.tax_type === 'gst' && (

                                                                <td className="money calculated gst-value">

                                                                    {
                                                                        formatMoney(
                                                                            calc.gst_amount
                                                                        )
                                                                    }

                                                                </td>

                                                            )
                                                        }


                                                        {/* Total */}

                                                        <td className="money calculated total">

                                                            {
                                                                formatMoney(
                                                                    calc.line_total
                                                                )
                                                            }

                                                        </td>


                                                        {/* Remove */}

                                                        <td className="action">

                                                            <button
                                                                type="button"
                                                                className="rr-purchase-remove-item"
                                                                title="Remove Item"
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


                        {
                            errors.items && (

                                <div className="rr-purchase-items-error">

                                    <i className="bi bi-exclamation-circle"></i>

                                    {
                                        errors.items
                                    }

                                </div>

                            )
                        }

                    </section>


                    {/* =================================================
                        BOTTOM AREA
                       ================================================= */}

                    <div className="rr-purchase-bottom-layout">

                        {/* Remarks */}

                        <section className="rr-purchase-entry-window">

                            <WindowTitle
                                icon="bi-card-text"
                                title="Remarks"
                            />


                            <div className="rr-purchase-entry-body">

                                <textarea
                                    data-validation-key="remarks"
                                    className={
                                        errors.remarks
                                            ? 'is-invalid'
                                            : ''
                                    }
                                    rows="5"
                                    value={
                                        data.remarks
                                    }
                                    placeholder="Purchase notes, delivery details or other information..."
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


                        {/* Totals */}

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


                                {
                                    errors.paid_amount && (

                                        <ErrorText
                                            message={
                                                errors.paid_amount
                                            }
                                        />

                                    )
                                }


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

                            <i className="bi bi-info-circle"></i>

                            {
                                data.tax_type === 'gst'
                                    ? 'GST Purchase • Tax is calculated from each purchase item.'
                                    : 'Without GST • All purchase item GST values will be stored as zero.'
                            }

                        </div>


                        <div>

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

                                                Saving Purchase...
                                            </>
                                        )
                                        : (
                                            <>
                                                <i className="bi bi-check-lg"></i>

                                                Save Purchase
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


            <ErrorText
                message={
                    error
                }
            />

        </div>
    );
}


/*
|--------------------------------------------------------------------------
| Error
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
                typeof input.showPicker === 'function'
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
                    negative && Number(value) > 0
                        ? 'negative'
                        : ''
                }
            >

                {
                    negative && Number(value) > 0
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


    const requestedDiscount =
        money(
            item.discount
        );


    const discount =
        Math.min(
            gross,
            requestedDiscount
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


    const lineTotal =
        round2(
            taxable
            +
            gstAmount
        );


    return {

        gross:
            gross,

        discount:
            discount,

        taxable:
            taxable,

        gst_percent:
            gstPercent,

        gst_amount:
            gstAmount,

        line_total:
            lineTotal,
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


    /*
    |--------------------------------------------------------------------------
    | Item Error
    |--------------------------------------------------------------------------
    |
    | items.0.quantity
    | items.1.godown_id
    |
    */

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


    /*
    |--------------------------------------------------------------------------
    | Scroll Into View
    |--------------------------------------------------------------------------
    */

    target.scrollIntoView({
        behavior:
            'smooth',

        block:
            'center',
    });


    /*
    |--------------------------------------------------------------------------
    | Highlight
    |--------------------------------------------------------------------------
    */

    target.classList.add(
        'rr-validation-focus'
    );


    /*
    |--------------------------------------------------------------------------
    | Focus
    |--------------------------------------------------------------------------
    */

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


    /*
    |--------------------------------------------------------------------------
    | Remove Highlight
    |--------------------------------------------------------------------------
    */

    window.setTimeout(
        () => {

            target.classList.remove(
                'rr-validation-focus'
            );

        },
        2200
    );
}


function today() {
    const date =
        new Date();


    const year =
        date.getFullYear();


    const month =
        String(
            date.getMonth() + 1
        ).padStart(
            2,
            '0'
        );


    const day =
        String(
            date.getDate()
        ).padStart(
            2,
            '0'
        );


    return `${year}-${month}-${day}`;
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