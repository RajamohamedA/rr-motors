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
    useForm,
    usePage,
} from '@inertiajs/react';

import BusinessLayout from '@/Layouts/BusinessLayout';

import MasterNavigation
    from '../../Components/Business/MasterNavigation';

import '../../../css/business/purchase-returns.css';


export default function Create({
    returnNo = '',
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

        purchase_id:
            '',

        return_date:
            today(),

        remarks:
            '',

        product_id:
            [],

        godown_id:
            [],

        qty:
            [],

        price:
            [],

        gst:
            [],
    });


    /*
    |--------------------------------------------------------------------------
    | Purchase Search
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


    const [
        selectedPurchase,
        setSelectedPurchase,
    ] = useState(null);


    const [
        loadingInvoice,
        setLoadingInvoice,
    ] = useState(false);


    const searchInputRef =
        useRef(null);


    const searchTimer =
        useRef(null);


    /*
    |--------------------------------------------------------------------------
    | Auto Focus First Validation Error
    |--------------------------------------------------------------------------
    */

    useEffect(
        () => {

            const keys =
                Object.keys(
                    errors ?? {}
                );


            if (
                keys.length === 0
            ) {
                return;
            }


            window.setTimeout(
                () => {

                    focusValidationError(
                        keys[0],
                        searchInputRef
                    );

                },
                100
            );

        },
        [
            errors,
        ]
    );


    /*
    |--------------------------------------------------------------------------
    | Search Purchase
    |--------------------------------------------------------------------------
    */

    useEffect(
        () => {

            if (
                searchTimer.current
            ) {

                window.clearTimeout(
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
                window.setTimeout(
                    async () => {

                        setSearching(
                            true
                        );


                        try {

                            const response =
                                await fetch(
                                    `/purchase-returns/search?search=${encodeURIComponent(value)}`,
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
                                    'Unable to search purchases.'
                                );
                            }


                            const result =
                                await response.json();


                            setSearchResults(
                                Array.isArray(
                                    result
                                )
                                    ? result
                                    : []
                            );


                            setSearchOpen(
                                true
                            );

                        } catch (
                        error
                        ) {

                            console.error(
                                error
                            );


                            setSearchResults([]);

                            setSearchOpen(true);

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

                    window.clearTimeout(
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
    | Select Purchase
    |--------------------------------------------------------------------------
    */

    const selectPurchase =
        async purchase => {

            setSearchOpen(
                false
            );


            setSearchText(
                purchase.purchase_no
            );


            setLoadingInvoice(
                true
            );


            clearErrors();


            try {

                const response =
                    await fetch(
                        `/purchase-returns/purchase/${purchase.id}`,
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
                        'Unable to load purchase invoice.'
                    );
                }


                const invoice =
                    await response.json();


                setSelectedPurchase(
                    invoice
                );


                const items =
                    invoice.items
                    ?? [];


                setData(
                    previous => ({
                        ...previous,

                        purchase_id:
                            invoice.id,

                        product_id:
                            items.map(
                                item =>
                                    item.product_id
                            ),

                        godown_id:
                            items.map(
                                item =>
                                    item.godown_id
                                    ?? ''
                            ),

                        qty:
                            items.map(
                                () =>
                                    '0'
                            ),

                        price:
                            items.map(
                                item =>
                                    String(
                                        item.return_unit_price
                                        ??
                                        item.purchase_price
                                        ??
                                        0
                                    )
                            ),

                        gst:
                            items.map(
                                item =>
                                    String(
                                        invoice.tax_type === 'gst'
                                            ? item.gst_percent
                                            ?? 0
                                            : 0
                                    )
                            ),
                    })
                );

            } catch (
            error
            ) {

                console.error(
                    error
                );


                setSelectedPurchase(
                    null
                );

            } finally {

                setLoadingInvoice(
                    false
                );
            }
        };


    /*
    |--------------------------------------------------------------------------
    | Clear Selected Purchase
    |--------------------------------------------------------------------------
    */

    const clearPurchase =
        () => {

            setSelectedPurchase(
                null
            );


            setSearchText('');

            setSearchResults([]);

            setSearchOpen(false);


            setData(
                previous => ({
                    ...previous,

                    purchase_id:
                        '',

                    product_id:
                        [],

                    godown_id:
                        [],

                    qty:
                        [],

                    price:
                        [],

                    gst:
                        [],
                })
            );


            window.setTimeout(
                () =>
                    searchInputRef.current?.focus(),
                0
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Update Quantity
    |--------------------------------------------------------------------------
    */

    const updateQty =
        (
            index,
            value
        ) => {

            const available =
                numberValue(
                    selectedPurchase
                        ?.items
                        ?.[index]
                        ?.balance_qty
                );


            let next =
                value;


            if (
                next !== ''
            ) {

                const numeric =
                    Math.max(
                        0,
                        numberValue(
                            next
                        )
                    );


                next =
                    String(
                        Math.min(
                            numeric,
                            available
                        )
                    );
            }


            const qty =
                [
                    ...data.qty,
                ];


            qty[index] =
                next;


            setData(
                'qty',
                qty
            );


            clearErrors(
                `qty.${index}`
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Calculated Items
    |--------------------------------------------------------------------------
    */

    const calculatedItems =
        useMemo(
            () => {

                const items =
                    selectedPurchase?.items
                    ?? [];


                return items.map(
                    (
                        item,
                        index
                    ) => {

                        const qty =
                            numberValue(
                                data.qty[index]
                                ?? 0
                            );


                        const price =
                            money(
                                data.price[index]
                                ??
                                item.return_unit_price
                                ??
                                item.purchase_price
                                ??
                                0
                            );


                        const gstPercent =
                            selectedPurchase?.tax_type === 'gst'
                                ? numberValue(
                                    data.gst[index]
                                    ??
                                    item.gst_percent
                                    ??
                                    0
                                )
                                : 0;


                        const basic =
                            round2(
                                qty
                                *
                                price
                            );


                        const gstAmount =
                            round2(
                                basic
                                *
                                gstPercent
                                /
                                100
                            );


                        const total =
                            round2(
                                basic
                                +
                                gstAmount
                            );


                        return {
                            qty,
                            price,
                            gst_percent:
                                gstPercent,

                            basic,
                            gst_amount:
                                gstAmount,

                            total,
                        };
                    }
                );

            },
            [
                selectedPurchase,
                data.qty,
                data.price,
                data.gst,
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

                let quantity =
                    0;

                let subtotal =
                    0;

                let gst =
                    0;

                let total =
                    0;


                calculatedItems.forEach(
                    item => {

                        quantity +=
                            item.qty;

                        subtotal +=
                            item.basic;

                        gst +=
                            item.gst_amount;

                        total +=
                            item.total;
                    }
                );


                return {
                    quantity:
                        round2(
                            quantity
                        ),

                    subtotal:
                        round2(
                            subtotal
                        ),

                    gst:
                        round2(
                            gst
                        ),

                    total:
                        round2(
                            total
                        ),
                };

            },
            [
                calculatedItems,
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
                '/purchase-returns',
                {
                    preserveScroll:
                        true,
                }
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
                title="Create Purchase Return"
            />


            <div className="rr-pr-page">

                <MasterNavigation
                    active="purchase-returns"
                    title="Purchase Returns"
                    subtitle="Return purchased stock to supplier"
                />


                <form
                    onSubmit={
                        submit
                    }
                >

                    {/* =================================================
                        HEADER
                       ================================================= */}

                    <section className="rr-pr-header">

                        <div className="rr-pr-header-main">

                            <Link
                                href="/purchase-returns"
                                className="rr-pr-back-btn"
                            >
                                <i className="bi bi-arrow-left"></i>
                            </Link>


                            <div className="rr-pr-header-icon">

                                <i className="bi bi-arrow-return-left"></i>

                            </div>


                            <div className="rr-pr-header-content">

                                <span className="rr-pr-eyebrow">
                                    Supplier Stock Return
                                </span>


                                <h1>
                                    New Purchase Return
                                </h1>


                                <div className="rr-pr-header-meta">

                                    <span>

                                        <i className="bi bi-receipt"></i>

                                        {
                                            returnNo
                                            || 'New Return'
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


                        <div className="rr-pr-header-actions">

                            <Link
                                href="/purchase-returns"
                                className="rr-pr-secondary-btn"
                            >
                                Cancel
                            </Link>


                            <button
                                type="submit"
                                className="rr-pr-primary-btn"
                                disabled={
                                    processing
                                    ||
                                    !selectedPurchase
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

                                                Save Return
                                            </>
                                        )
                                }

                            </button>

                        </div>

                    </section>


                    {/* =================================================
                        PURCHASE SEARCH
                       ================================================= */}

                    <section className="rr-pr-entry-window rr-pr-search-window">

                        <WindowTitle
                            icon="bi-search"
                            title="Select Original Purchase"
                        />


                        <div className="rr-pr-entry-body">

                            <div className="rr-pr-purchase-search">

                                <div
                                    className={
                                        `rr-pr-purchase-search-box ${searchOpen
                                            ? 'is-open'
                                            : ''
                                        }`
                                    }
                                >

                                    <div className="rr-pr-purchase-search-icon">

                                        <i className="bi bi-search"></i>

                                    </div>


                                    <input
                                        ref={
                                            searchInputRef
                                        }
                                        data-validation-key="purchase_id"
                                        type="search"
                                        value={
                                            searchText
                                        }
                                        autoComplete="off"
                                        placeholder="Search purchase no, invoice no or supplier..."
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
                                            event => {

                                                setSearchText(
                                                    event.target.value
                                                );


                                                if (
                                                    selectedPurchase
                                                ) {

                                                    clearPurchase();
                                                }
                                            }
                                        }
                                    />


                                    {
                                        searching
                                            ||
                                            loadingInvoice
                                            ? (
                                                <span className="rr-pr-search-spinner">

                                                    <span className="spinner-border spinner-border-sm"></span>

                                                </span>
                                            )
                                            : (
                                                searchText && (

                                                    <button
                                                        type="button"
                                                        className="rr-pr-search-clear"
                                                        onClick={
                                                            clearPurchase
                                                        }
                                                    >
                                                        <i className="bi bi-x-lg"></i>
                                                    </button>

                                                )
                                            )
                                    }

                                </div>


                                {
                                    searchOpen && (

                                        <div className="rr-pr-search-results">

                                            <div className="rr-pr-search-results-header">

                                                <div>

                                                    <i className="bi bi-receipt"></i>

                                                    Purchase Search

                                                </div>


                                                <span>
                                                    {
                                                        searchResults.length
                                                    }

                                                    {' '}

                                                    result(s)
                                                </span>

                                            </div>


                                            <div className="rr-pr-search-results-list">

                                                {
                                                    searchResults.length > 0
                                                        ? (

                                                            searchResults.map(
                                                                purchase => (

                                                                    <button
                                                                        key={
                                                                            purchase.id
                                                                        }
                                                                        type="button"
                                                                        className="rr-pr-search-result"
                                                                        onClick={
                                                                            () =>
                                                                                selectPurchase(
                                                                                    purchase
                                                                                )
                                                                        }
                                                                    >

                                                                        <div className="rr-pr-search-result-icon">

                                                                            <i className="bi bi-receipt"></i>

                                                                        </div>


                                                                        <div className="rr-pr-search-result-info">

                                                                            <strong>
                                                                                {
                                                                                    purchase.purchase_no
                                                                                }
                                                                            </strong>


                                                                            <span>

                                                                                {
                                                                                    purchase.supplier?.supplier_name
                                                                                    ?? 'Supplier'
                                                                                }

                                                                                {
                                                                                    purchase.invoice_no
                                                                                        ? ` • Invoice ${purchase.invoice_no}`
                                                                                        : ''
                                                                                }

                                                                            </span>

                                                                        </div>


                                                                        <div className="rr-pr-search-result-date">

                                                                            <span>
                                                                                Purchase Date
                                                                            </span>

                                                                            <strong>
                                                                                {
                                                                                    formatDate(
                                                                                        purchase.purchase_date
                                                                                    )
                                                                                }
                                                                            </strong>

                                                                        </div>


                                                                        <div className="rr-pr-search-result-amount">

                                                                            <span>
                                                                                Total
                                                                            </span>

                                                                            <strong>
                                                                                {
                                                                                    formatMoney(
                                                                                        purchase.grand_total
                                                                                    )
                                                                                }
                                                                            </strong>

                                                                        </div>


                                                                        <div className="rr-pr-search-result-select">

                                                                            <i className="bi bi-chevron-right"></i>

                                                                        </div>

                                                                    </button>

                                                                )
                                                            )

                                                        )
                                                        : (
                                                            !searching && (

                                                                <div className="rr-pr-search-empty">

                                                                    <i className="bi bi-search"></i>

                                                                    <strong>
                                                                        No purchases found
                                                                    </strong>

                                                                    <span>
                                                                        Search by purchase number, supplier invoice or supplier name.
                                                                    </span>

                                                                </div>

                                                            )
                                                        )
                                                }

                                            </div>

                                        </div>

                                    )
                                }

                            </div>


                            <ErrorText
                                message={
                                    errors.purchase_id
                                }
                            />

                        </div>

                    </section>


                    {/* =================================================
                        SELECTED PURCHASE
                       ================================================= */}

                    {
                        selectedPurchase && (

                            <>
                                <section className="rr-pr-entry-window">

                                    <WindowTitle
                                        icon="bi-file-earmark-text"
                                        title="Purchase Information"
                                    />


                                    <div className="rr-pr-entry-body">

                                        <div className="rr-pr-purchase-info-grid">

                                            <InfoBox
                                                label="Purchase No."
                                                value={
                                                    selectedPurchase.purchase_no
                                                }
                                            />


                                            <InfoBox
                                                label="Purchase Date"
                                                value={
                                                    formatDate(
                                                        selectedPurchase.purchase_date
                                                    )
                                                }
                                            />


                                            <InfoBox
                                                label="Supplier"
                                                value={
                                                    selectedPurchase.supplier?.supplier_name
                                                    ?? '—'
                                                }
                                            />


                                            <InfoBox
                                                label="Invoice No."
                                                value={
                                                    selectedPurchase.invoice_no
                                                    || '—'
                                                }
                                            />


                                            <InfoBox
                                                label="Invoice Date"
                                                value={
                                                    selectedPurchase.invoice_date
                                                        ? formatDate(
                                                            selectedPurchase.invoice_date
                                                        )
                                                        : '—'
                                                }
                                            />


                                            <InfoBox
                                                label="Purchase Type"
                                            >

                                                <TaxBadge
                                                    type={
                                                        selectedPurchase.tax_type
                                                    }
                                                />

                                            </InfoBox>

                                        </div>

                                    </div>

                                </section>


                                {/* =========================================
                                    RETURN DATE
                                   ========================================= */}

                                <section className="rr-pr-entry-window">

                                    <WindowTitle
                                        icon="bi-calendar3"
                                        title="Return Details"
                                    />


                                    <div className="rr-pr-entry-body">

                                        <div className="rr-pr-return-detail-grid">

                                            <div className="rr-pr-field">

                                                <label>
                                                    Return Date

                                                    <span className="text-danger ms-1">
                                                        *
                                                    </span>
                                                </label>


                                                <DateInput
                                                    validationKey="return_date"
                                                    value={
                                                        data.return_date
                                                    }
                                                    onChange={
                                                        value =>
                                                            setData(
                                                                'return_date',
                                                                value
                                                            )
                                                    }
                                                />


                                                <ErrorText
                                                    message={
                                                        errors.return_date
                                                    }
                                                />

                                            </div>


                                            <div className="rr-pr-field rr-pr-return-number-field">

                                                <label>
                                                    Return Number
                                                </label>


                                                <div className="rr-pr-readonly-field">

                                                    <i className="bi bi-lock"></i>

                                                    <strong>
                                                        {
                                                            returnNo
                                                        }
                                                    </strong>

                                                </div>

                                            </div>

                                        </div>

                                    </div>

                                </section>


                                {/* =========================================
                                    ITEMS
                                   ========================================= */}

                                <section className="rr-pr-entry-window">

                                    <div className="rr-pr-entry-title">

                                        <div>

                                            <i className="bi bi-table"></i>

                                            <strong>
                                                Return Items
                                            </strong>

                                        </div>


                                        <span className="rr-pr-item-count">

                                            {
                                                selectedPurchase.items?.length
                                                ?? 0
                                            }

                                            {' '}

                                            item(s)

                                        </span>

                                    </div>


                                    <div className="rr-pr-items-wrap">

                                        <table className="rr-pr-items-table">

                                            <thead>

                                                <tr>

                                                    <th className="serial">
                                                        #
                                                    </th>

                                                    <th className="product">
                                                        Product
                                                    </th>

                                                    <th>
                                                        Location
                                                    </th>

                                                    <th className="number">
                                                        Purchased
                                                    </th>

                                                    <th className="number">
                                                        Returned
                                                    </th>

                                                    <th className="number">
                                                        Available
                                                    </th>

                                                    <th className="number">
                                                        Return Qty
                                                    </th>

                                                    <th className="number">
                                                        Rate
                                                    </th>

                                                    {
                                                        selectedPurchase.tax_type === 'gst' && (

                                                            <th className="number">
                                                                GST %
                                                            </th>

                                                        )
                                                    }

                                                    <th className="number">
                                                        Amount
                                                    </th>

                                                </tr>

                                            </thead>


                                            <tbody>

                                                {
                                                    selectedPurchase.items?.map(
                                                        (
                                                            item,
                                                            index
                                                        ) => {

                                                            const calc =
                                                                calculatedItems[index];


                                                            return (

                                                                <tr
                                                                    key={
                                                                        item.id
                                                                        ?? index
                                                                    }
                                                                >

                                                                    <td className="serial">
                                                                        {
                                                                            index + 1
                                                                        }
                                                                    </td>


                                                                    <td>

                                                                        <div className="rr-pr-product">

                                                                            <div className="rr-pr-product-icon">

                                                                                <i className="bi bi-box-seam"></i>

                                                                            </div>


                                                                            <div>

                                                                                <strong>
                                                                                    {
                                                                                        item.product?.product_name
                                                                                        ?? 'Product'
                                                                                    }
                                                                                </strong>


                                                                                <span>

                                                                                    {
                                                                                        item.product?.product_code
                                                                                        ?? ''
                                                                                    }

                                                                                    {
                                                                                        item.product?.part_number
                                                                                            ? ` • ${item.product.part_number}`
                                                                                            : ''
                                                                                    }

                                                                                    {
                                                                                        item.product?.unit?.short_name
                                                                                            ? ` • ${item.product.unit.short_name}`
                                                                                            : item.product?.unit?.unit_name
                                                                                                ? ` • ${item.product.unit.unit_name}`
                                                                                                : ''
                                                                                    }

                                                                                </span>

                                                                            </div>

                                                                        </div>

                                                                    </td>


                                                                    <td>

                                                                        <LocationBadge
                                                                            godown={
                                                                                item.godown
                                                                            }
                                                                        />

                                                                    </td>


                                                                    <td className="number">
                                                                        {
                                                                            formatQty(
                                                                                item.quantity
                                                                            )
                                                                        }
                                                                    </td>


                                                                    <td className="number rr-pr-returned-qty">
                                                                        {
                                                                            formatQty(
                                                                                item.returned_qty
                                                                            )
                                                                        }
                                                                    </td>


                                                                    <td className="number rr-pr-available-qty">
                                                                        {
                                                                            formatQty(
                                                                                item.balance_qty
                                                                            )
                                                                        }
                                                                    </td>


                                                                    <td className="number">

                                                                        <input
                                                                            type="number"
                                                                            data-item-index={
                                                                                index
                                                                            }
                                                                            data-item-field="qty"
                                                                            min="0"
                                                                            step="1"
                                                                            max={
                                                                                item.balance_qty
                                                                            }
                                                                            value={
                                                                                data.qty[index]
                                                                                ?? '0'
                                                                            }
                                                                            disabled={
                                                                                numberValue(
                                                                                    item.balance_qty
                                                                                ) <= 0
                                                                            }
                                                                            className={
                                                                                errors[
                                                                                    `qty.${index}`
                                                                                ]
                                                                                    ? 'is-invalid'
                                                                                    : ''
                                                                            }
                                                                            onChange={
                                                                                event =>
                                                                                    updateQty(
                                                                                        index,
                                                                                        event.target.value
                                                                                    )
                                                                            }
                                                                        />


                                                                        <ItemError
                                                                            error={
                                                                                errors[
                                                                                `qty.${index}`
                                                                                ]
                                                                            }
                                                                        />

                                                                    </td>


                                                                    <td className="number rr-pr-rate">

                                                                        {
                                                                            formatMoney(
                                                                                calc?.price
                                                                            )
                                                                        }

                                                                    </td>


                                                                    {
                                                                        selectedPurchase.tax_type === 'gst' && (

                                                                            <td className="number">

                                                                                {
                                                                                    numberValue(
                                                                                        calc?.gst_percent
                                                                                    ).toFixed(2)
                                                                                }

                                                                                %

                                                                            </td>

                                                                        )
                                                                    }


                                                                    <td className="number rr-pr-line-total">

                                                                        {
                                                                            formatMoney(
                                                                                calc?.total
                                                                            )
                                                                        }

                                                                    </td>

                                                                </tr>

                                                            );
                                                        }
                                                    )
                                                }

                                            </tbody>

                                        </table>

                                    </div>


                                    <ErrorText
                                        message={
                                            errors.items
                                        }
                                    />

                                </section>


                                {/* =========================================
                                    BOTTOM
                                   ========================================= */}

                                <div className="rr-pr-bottom-layout">

                                    <section className="rr-pr-entry-window">

                                        <WindowTitle
                                            icon="bi-card-text"
                                            title="Remarks"
                                        />


                                        <div className="rr-pr-entry-body">

                                            <textarea
                                                data-validation-key="remarks"
                                                rows="5"
                                                value={
                                                    data.remarks
                                                }
                                                placeholder="Optional purchase return remarks..."
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


                                    <section className="rr-pr-total-window">

                                        <div className="rr-pr-total-title">

                                            <i className="bi bi-calculator"></i>

                                            Return Summary

                                        </div>


                                        <div className="rr-pr-total-body">

                                            <TotalRow
                                                label="Return Quantity"
                                                value={
                                                    formatQty(
                                                        totals.quantity
                                                    )
                                                }
                                                raw
                                            />


                                            <TotalRow
                                                label="Subtotal"
                                                value={
                                                    totals.subtotal
                                                }
                                            />


                                            {
                                                selectedPurchase.tax_type === 'gst' && (

                                                    <TotalRow
                                                        label="GST"
                                                        value={
                                                            totals.gst
                                                        }
                                                    />

                                                )
                                            }


                                            <div className="rr-pr-grand-total">

                                                <span>
                                                    Return Total
                                                </span>


                                                <strong>
                                                    {
                                                        formatMoney(
                                                            totals.total
                                                        )
                                                    }
                                                </strong>

                                            </div>

                                        </div>

                                    </section>

                                </div>


                                {/* =========================================
                                    COMMAND BAR
                                   ========================================= */}

                                <div className="rr-pr-command-bar">

                                    <div>

                                        <i className="bi bi-info-circle"></i>

                                        Returning stock will reduce quantity from the original Shop/Godown location.

                                    </div>


                                    <div>

                                        <Link
                                            href="/purchase-returns"
                                            className="rr-pr-secondary-btn"
                                        >
                                            Cancel
                                        </Link>


                                        <button
                                            type="submit"
                                            className="rr-pr-primary-btn"
                                            disabled={
                                                processing
                                                ||
                                                totals.quantity <= 0
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

                                                            Save Purchase Return
                                                        </>
                                                    )
                                            }

                                        </button>

                                    </div>

                                </div>

                            </>

                        )
                    }

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

        <div className="rr-pr-entry-title">

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
| Info Box
|--------------------------------------------------------------------------
*/

function InfoBox({
    label,
    value,
    children,
}) {

    return (

        <div className="rr-pr-info-box">

            <span>
                {label}
            </span>


            {
                children
                    ? children
                    : (
                        <strong>
                            {
                                value
                                ?? '—'
                            }
                        </strong>
                    )
            }

        </div>
    );
}


/*
|--------------------------------------------------------------------------
| Tax Badge
|--------------------------------------------------------------------------
*/

function TaxBadge({
    type,
}) {

    const gst =
        type === 'gst';


    return (

        <span
            className={
                gst
                    ? 'rr-pr-tax-badge gst'
                    : 'rr-pr-tax-badge non-gst'
            }
        >

            <i
                className={
                    gst
                        ? 'bi bi-percent'
                        : 'bi bi-slash-circle'
                }
            ></i>


            {
                gst
                    ? 'GST Purchase'
                    : 'Without GST'
            }

        </span>
    );
}


/*
|--------------------------------------------------------------------------
| Location
|--------------------------------------------------------------------------
*/

function LocationBadge({
    godown,
}) {

    if (
        !godown
    ) {

        return (

            <span className="rr-pr-location-badge shop">

                <i className="bi bi-shop"></i>

                Shop

            </span>
        );
    }


    return (

        <span className="rr-pr-location-badge godown">

            <i className="bi bi-boxes"></i>

            {
                godown.godown_name
                ?? 'Godown'
            }

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
        useRef(null);


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

        <div className="rr-pr-date-entry">

            <button
                type="button"
                tabIndex="-1"
                onClick={
                    openPicker
                }
            >
                <i className="bi bi-calendar3"></i>
            </button>


            <input
                ref={
                    inputRef
                }
                type="date"
                data-validation-key={
                    validationKey
                    || undefined
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

        <div className="rr-pr-field-error">

            <i className="bi bi-exclamation-circle"></i>

            {message}

        </div>
    );
}


function ItemError({
    error,
}) {

    if (
        !error
    ) {
        return null;
    }


    return (

        <span className="rr-pr-item-error">

            {error}

        </span>
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
    raw = false,
}) {

    return (

        <div className="rr-pr-total-row">

            <span>
                {label}
            </span>


            <strong>
                {
                    raw
                        ? value
                        : formatMoney(
                            value
                        )
                }
            </strong>

        </div>
    );
}


/*
|--------------------------------------------------------------------------
| Validation Focus
|--------------------------------------------------------------------------
*/

function focusValidationError(
    errorKey,
    searchInputRef
) {

    let target =
        null;


    const qtyMatch =
        String(
            errorKey
        ).match(
            /^qty\.(\d+)$/
        );


    if (
        qtyMatch
    ) {

        target =
            document.querySelector(
                `[data-item-index="${qtyMatch[1]}"][data-item-field="qty"]`
            );

    } else if (
        errorKey === 'items'
        ||
        errorKey === 'purchase_id'
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

            target.focus?.({
                preventScroll:
                    true,
            });

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
| Helpers
|--------------------------------------------------------------------------
*/

function numberValue(
    value
) {

    const number =
        Number(
            value
            ?? 0
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


function formatQty(
    value
) {

    return new Intl.NumberFormat(
        'en-IN',
        {
            minimumFractionDigits:
                0,

            maximumFractionDigits:
                2,
        }
    ).format(
        numberValue(
            value
        )
    );
}


function formatDate(
    value
) {

    if (
        !value
    ) {
        return '—';
    }


    const raw =
        String(
            value
        );


    const date =
        raw.includes('T')
            ? new Date(
                raw
            )
            : new Date(
                `${raw}T00:00:00`
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
        }
    ).format(
        date
    );
}


function today() {
    const date =
        new Date();


    const offset =
        date.getTimezoneOffset();


    const local =
        new Date(
            date.getTime()
            -
            offset * 60000
        );


    return local
        .toISOString()
        .slice(
            0,
            10
        );
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