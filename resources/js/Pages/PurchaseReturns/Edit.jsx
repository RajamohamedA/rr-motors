import React, {
    useEffect,
    useMemo,
    useRef,
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

import '../../../css/business/purchase-returns.css';


export default function Edit({
    purchaseReturn,
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
    | Original Purchase
    |--------------------------------------------------------------------------
    */

    const purchase =
        purchaseReturn?.purchase
        ?? null;


    const returnItems =
        purchaseReturn?.items
        ?? [];


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

        return_date:
            normalizeDate(
                purchaseReturn?.return_date
            ),

        remarks:
            purchaseReturn?.remarks
            ?? '',

        product_id:
            returnItems.map(
                item =>
                    item.product_id
            ),

        godown_id:
            returnItems.map(
                item =>
                    item.godown_id
                    ?? ''
            ),

        qty:
            returnItems.map(
                item =>
                    String(
                        item.quantity
                        ?? 0
                    )
            ),

        price:
            returnItems.map(
                item =>
                    String(
                        item.purchase_price
                        ?? 0
                    )
            ),

        gst:
            returnItems.map(
                item =>
                    String(
                        item.gst_percent
                        ?? 0
                    )
            ),
    });


    /*
    |--------------------------------------------------------------------------
    | Validation Focus
    |--------------------------------------------------------------------------
    */

    const firstFieldRef =
        useRef(null);


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
                        firstFieldRef
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
    | Update Qty
    |--------------------------------------------------------------------------
    */

    const updateQty =
        (
            index,
            value
        ) => {

            const item =
                returnItems[index];


            const available =
                numberValue(
                    item?.purchase_item?.balance_qty
                    ??
                    item?.purchaseItem?.balance_qty
                    ??
                    item?.quantity
                    ??
                    0
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
    | Calculations
    |--------------------------------------------------------------------------
    */

    const calculatedItems =
        useMemo(
            () => {

                return returnItems.map(
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
                                item.purchase_price
                                ??
                                0
                            );


                        const gstPercent =
                            purchase?.tax_type === 'gst'
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
                returnItems,
                data.qty,
                data.price,
                data.gst,
                purchase?.tax_type,
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
                `/purchase-returns/${purchaseReturn.id}`,
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
                title={
                    `Edit ${purchaseReturn?.return_no ?? 'Purchase Return'}`
                }
            />


            <div className="rr-pr-page">

                <MasterNavigation
                    active="purchase-returns"
                    title="Purchase Returns"
                    subtitle="Edit supplier return and stock reversal"
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

                                <i className="bi bi-pencil-square"></i>

                            </div>


                            <div className="rr-pr-header-content">

                                <span className="rr-pr-eyebrow">
                                    Purchase Return Maintenance
                                </span>


                                <h1>
                                    Edit Purchase Return
                                </h1>


                                <div className="rr-pr-header-meta">

                                    <span>

                                        <i className="bi bi-arrow-return-left"></i>

                                        {
                                            purchaseReturn?.return_no
                                        }

                                    </span>


                                    <span>

                                        <i className="bi bi-buildings"></i>

                                        {
                                            business?.company_name
                                            ?? purchase?.company?.company_name
                                            ?? 'Company'
                                        }

                                    </span>


                                    <span>

                                        <i className="bi bi-shop"></i>

                                        {
                                            business?.store_name
                                            ?? purchase?.store?.store_name
                                            ?? 'Branch'
                                        }

                                    </span>

                                </div>

                            </div>

                        </div>


                        <div className="rr-pr-header-actions">

                            <Link
                                href={
                                    `/purchase-returns/${purchaseReturn.id}`
                                }
                                className="rr-pr-secondary-btn"
                            >
                                View
                            </Link>


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

                                                Updating...
                                            </>
                                        )
                                        : (
                                            <>
                                                <i className="bi bi-floppy"></i>

                                                Update Return
                                            </>
                                        )
                                }

                            </button>

                        </div>

                    </section>


                    {/* =================================================
                        STOCK REVERSAL INFO
                       ================================================= */}

                    <div className="rr-pr-edit-warning">

                        <div>
                            <i className="bi bi-arrow-repeat"></i>
                        </div>


                        <div>

                            <strong>
                                Stock will be recalculated
                            </strong>


                            <span>
                                Saving this return restores the previous return quantity first, then posts the updated quantity to the same Shop/Godown location.
                            </span>

                        </div>

                    </div>


                    {/* =================================================
                        PURCHASE INFO
                       ================================================= */}

                    <section className="rr-pr-entry-window">

                        <WindowTitle
                            icon="bi-file-earmark-text"
                            title="Original Purchase"
                        />


                        <div className="rr-pr-entry-body">

                            <div className="rr-pr-purchase-info-grid">

                                <InfoBox
                                    label="Purchase No."
                                    value={
                                        purchase?.purchase_no
                                    }
                                />


                                <InfoBox
                                    label="Purchase Date"
                                    value={
                                        formatDate(
                                            purchase?.purchase_date
                                        )
                                    }
                                />


                                <InfoBox
                                    label="Supplier"
                                    value={
                                        purchaseReturn?.supplier?.supplier_name
                                        ?? '—'
                                    }
                                />


                                <InfoBox
                                    label="Invoice No."
                                    value={
                                        purchase?.invoice_no
                                        || '—'
                                    }
                                />


                                <InfoBox
                                    label="Invoice Date"
                                    value={
                                        purchase?.invoice_date
                                            ? formatDate(
                                                purchase.invoice_date
                                            )
                                            : '—'
                                    }
                                />


                                <InfoBox
                                    label="Purchase Type"
                                >

                                    <TaxBadge
                                        type={
                                            purchase?.tax_type
                                        }
                                    />

                                </InfoBox>

                            </div>

                        </div>

                    </section>


                    {/* =================================================
                        RETURN DETAILS
                       ================================================= */}

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


                                <div className="rr-pr-field">

                                    <label>
                                        Return Number
                                    </label>


                                    <div className="rr-pr-readonly-field">

                                        <i className="bi bi-lock"></i>

                                        <strong>
                                            {
                                                purchaseReturn.return_no
                                            }
                                        </strong>

                                    </div>

                                </div>

                            </div>

                        </div>

                    </section>


                    {/* =================================================
                        ITEMS
                       ================================================= */}

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
                                    returnItems.length
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

                                        <th>
                                            Product
                                        </th>

                                        <th>
                                            Location
                                        </th>

                                        <th className="number">
                                            Purchased
                                        </th>

                                        <th className="number">
                                            Previous Returned
                                        </th>

                                        <th className="number">
                                            Max This Return
                                        </th>

                                        <th className="number">
                                            Return Qty
                                        </th>

                                        <th className="number">
                                            Rate
                                        </th>

                                        {
                                            purchase?.tax_type === 'gst' && (

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
                                        returnItems.map(
                                            (
                                                item,
                                                index
                                            ) => {

                                                const purchaseInfo =
                                                    item.purchase_item
                                                    ??
                                                    item.purchaseItem
                                                    ??
                                                    {};


                                                const calc =
                                                    calculatedItems[index];


                                                const maxQty =
                                                    numberValue(
                                                        purchaseInfo.balance_qty
                                                        ??
                                                        item.quantity
                                                        ??
                                                        0
                                                    );


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
                                                                    purchaseInfo.quantity
                                                                    ?? 0
                                                                )
                                                            }

                                                        </td>


                                                        <td className="number rr-pr-returned-qty">

                                                            {
                                                                formatQty(
                                                                    purchaseInfo.returned_qty
                                                                    ?? 0
                                                                )
                                                            }

                                                        </td>


                                                        <td className="number rr-pr-available-qty">

                                                            {
                                                                formatQty(
                                                                    maxQty
                                                                )
                                                            }

                                                        </td>


                                                        <td className="number">

                                                            <input
                                                                ref={
                                                                    index === 0
                                                                        ? firstFieldRef
                                                                        : null
                                                                }
                                                                type="number"
                                                                data-item-index={
                                                                    index
                                                                }
                                                                data-item-field="qty"
                                                                min="0"
                                                                step="1"
                                                                max={
                                                                    maxQty
                                                                }
                                                                value={
                                                                    data.qty[index]
                                                                    ?? '0'
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
                                                            purchase?.tax_type === 'gst' && (

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


                    {/* =================================================
                        BOTTOM
                       ================================================= */}

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
                                    purchase?.tax_type === 'gst' && (

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


                    {/* =================================================
                        COMMAND BAR
                       ================================================= */}

                    <div className="rr-pr-command-bar">

                        <div>

                            <i className="bi bi-arrow-repeat"></i>

                            Existing return stock will be restored before the updated return is posted.

                        </div>


                        <div>

                            <Link
                                href={
                                    `/purchase-returns/${purchaseReturn.id}`
                                }
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

                                                Updating...
                                            </>
                                        )
                                        : (
                                            <>
                                                <i className="bi bi-check-lg"></i>

                                                Update Purchase Return
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
    fallbackRef
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

    } else {

        target =
            document.querySelector(
                `[data-validation-key="${errorKey}"]`
            );
    }


    target =
        target
        ??
        fallbackRef.current;


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