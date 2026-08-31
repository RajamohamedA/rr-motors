import {
    Head,
    router,
    useForm,
    usePage,
} from '@inertiajs/react';

import {
    useEffect,
    useState,
} from 'react';

import axios from 'axios';

import BusinessLayout
    from '../../Layouts/BusinessLayout';

import MasterNavigation
    from '../../Components/Business/MasterNavigation';

import '../../../css/business/customers.css';


export default function Edit({ customer }) {

    const {
        auth,
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





    /*
    |--------------------------------------------------------------------------
    | Form
    |--------------------------------------------------------------------------
    */

    const {
        data,
        setData,
        processing,
        errors,
    } = useForm({

        customer_name:
            customer?.customer_name
            ?? '',

        contact_person:
            customer?.contact_person
            ?? '',

        mobile:
            customer?.mobile
            ?? '',

        phone:
            customer?.phone
            ?? '',

        email:
            customer?.email
            ?? '',

        billing_address:
            customer?.billing_address
            ?? '',

        shipping_address:
            customer?.shipping_address
            ?? '',

        city:
            customer?.city
            ?? '',

        state:
            customer?.state
            ?? '',

        country:
            customer?.country
            ?? 'India',

        pincode:
            customer?.pincode
            ?? '',

        gst_number:
            customer?.gst_number
            ?? '',

        pan_number:
            customer?.pan_number
            ?? '',

        credit_limit:
            customer?.credit_limit
            ?? 0,

        credit_days:
            customer?.credit_days
            ?? 0,

        opening_balance:
            customer?.opening_balance
            ?? 0,

        balance_type:
            customer?.balance_type
            ?? 'Dr',

        status:
            Boolean(
                customer?.status
                ?? true
            ),

        remarks:
            customer?.remarks
            ?? '',
    });


    /*
    |--------------------------------------------------------------------------
    | GST
    |--------------------------------------------------------------------------
    */

    const [
        gstLoading,
        setGstLoading,
    ] =
        useState(
            false
        );


    const [
        gstMessage,
        setGstMessage,
    ] =
        useState(
            null
        );


    const [
        gstResult,
        setGstResult,
    ] =
        useState(
            null
        );


    /*
    |--------------------------------------------------------------------------
    | Same Billing / Shipping Address
    |--------------------------------------------------------------------------
    */

    const [
        sameAsBilling,
        setSameAsBilling,
    ] =
        useState(
            Boolean(
                customer?.billing_address
                &&
                customer?.shipping_address
                &&
                customer.billing_address ===
                customer.shipping_address
            )
        );


    useEffect(
        () => {

            if (
                sameAsBilling
            ) {

                setData(
                    'shipping_address',
                    data.billing_address
                );
            }

        },
        [
            sameAsBilling,
            data.billing_address,
        ]
    );


    /*
    |--------------------------------------------------------------------------
    | GST Lookup
    |--------------------------------------------------------------------------
    */

    const lookupGst =
        async () => {

            const gstNumber =
                normalizeUpper(
                    data.gst_number
                );


            if (
                gstNumber.length !==
                15
            ) {

                setGstMessage({
                    type:
                        'error',

                    text:
                        'Enter a valid 15-character GST number.',
                });

                return;
            }


            try {

                setGstLoading(
                    true
                );

                setGstMessage(
                    null
                );

                setGstResult(
                    null
                );


                const response =
                    await axios.post(
                        '/customers/gst-lookup',
                        {
                            gst_number:
                                gstNumber,
                        }
                    );


                const result =
                    response.data?.data
                    ?? null;


                if (
                    !result
                ) {

                    throw new Error(
                        'GST data was not returned.'
                    );
                }


                setGstResult(
                    result
                );


                setGstMessage({
                    type:
                        'success',

                    text:
                        'GST details fetched successfully. Review them before applying.',
                });

            } catch (
            error
            ) {

                setGstMessage({
                    type:
                        'error',

                    text:
                        error?.response?.data?.message
                        ??
                        'Unable to fetch GST details. You can continue manually.',
                });

            } finally {

                setGstLoading(
                    false
                );
            }
        };


    const applyGstDetails =
        () => {

            if (
                !gstResult
            ) {

                return;
            }


            const legalName =
                gstResult.legal_name
                ??
                gstResult.trade_name
                ??
                data.customer_name;


            const address =
                gstResult.address
                ?? {};


            setData(
                current => ({
                    ...current,

                    customer_name:
                        legalName
                        ?? '',

                    gst_number:
                        normalizeUpper(
                            gstResult.gst_number
                            ??
                            data.gst_number
                        ),

                    pan_number:
                        normalizeUpper(
                            gstResult.pan_number
                            ??
                            data.pan_number
                        ),

                    billing_address:
                        address.address_line
                        ??
                        data.billing_address,

                    shipping_address:
                        data.shipping_address
                        ||
                        address.address_line
                        ||
                        '',

                    city:
                        address.city
                        ??
                        data.city,

                    state:
                        address.state
                        ??
                        data.state,

                    pincode:
                        address.pincode
                        ??
                        data.pincode,

                    country:
                        address.country
                        ??
                        'India',
                })
            );


            setGstMessage({
                type:
                    'success',

                text:
                    'GST details applied to the customer form.',
            });
        };


    /*
    |--------------------------------------------------------------------------
    | Submit
    |--------------------------------------------------------------------------
    */

    const submit =
        event => {

            event.preventDefault();


            router.put(
                `/customers/${customer.id}`,
                data,
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

        <BusinessLayout>

            <Head
                title="Edit Customer"
            />


            <div className="rr-customers-page">

                <MasterNavigation
                    active="customers"
                    title="Edit Customer"
                    subtitle="Update customer details for all assigned branches"
                />


                <section className="rr-customer-form-header">

                    <div>

                        <span>
                            Customer Master
                        </span>

                        <h1>
                            Edit Customer
                        </h1>

                        <p>
                            Update this customer profile. Changes apply to every branch where this customer is assigned.
                        </p>

                    </div>


                    <div className="rr-customer-form-header-actions">

                        <button
                            type="button"
                            className="rr-customer-toolbar-btn"
                            onClick={
                                () =>
                                    window.history.back()
                            }
                        >

                            <i className="bi bi-arrow-left"></i>

                            Back

                        </button>

                    </div>

                </section>






                {
                    customer?.shared && (

                        <section className="rr-customer-shared-warning">

                            <i className="bi bi-exclamation-triangle"></i>

                            <div>

                                <strong>
                                    Shared customer
                                </strong>

                                <span>
                                    This customer is assigned to {
                                        customer?.stores_count
                                        ?? 0
                                    } branches. Saving changes updates the shared customer profile for all of them.
                                </span>

                            </div>

                        </section>

                    )
                }


                <form
                    onSubmit={
                        submit
                    }
                    className="rr-customer-form-shell"
                >

                    {/* =================================================
                        BASIC DETAILS
                       ================================================= */}

                    <FormSection
                        icon="bi-person-badge"
                        eyebrow="Identity"
                        title="Customer Details"
                        description="Basic contact and business identity."
                    >

                        <div className="rr-customer-form-grid">

                            <Field
                                label="Customer Name"
                                required
                                error={
                                    errors.customer_name
                                }
                            >

                                <input
                                    type="text"
                                    value={
                                        data.customer_name
                                    }
                                    onChange={
                                        event =>
                                            setData(
                                                'customer_name',
                                                event.target.value
                                            )
                                    }
                                    placeholder="Enter customer or company name"
                                    autoFocus
                                />

                            </Field>


                            <Field
                                label="Contact Person"
                                error={
                                    errors.contact_person
                                }
                            >

                                <input
                                    type="text"
                                    value={
                                        data.contact_person
                                    }
                                    onChange={
                                        event =>
                                            setData(
                                                'contact_person',
                                                event.target.value
                                            )
                                    }
                                    placeholder="Contact person"
                                />

                            </Field>


                            <Field
                                label="Mobile"
                                error={
                                    errors.mobile
                                }
                                hint="Used to identify the customer"
                            >

                                <input
                                    type="text"
                                    value={
                                        data.mobile
                                    }
                                    onChange={
                                        event =>
                                            setData(
                                                'mobile',
                                                event.target.value
                                            )
                                    }
                                    placeholder="Mobile number"
                                />

                            </Field>


                            <Field
                                label="Phone"
                                error={
                                    errors.phone
                                }
                            >

                                <input
                                    type="text"
                                    value={
                                        data.phone
                                    }
                                    onChange={
                                        event =>
                                            setData(
                                                'phone',
                                                event.target.value
                                            )
                                    }
                                    placeholder="Landline / alternate number"
                                />

                            </Field>


                            <Field
                                label="Email"
                                error={
                                    errors.email
                                }
                                hint="Also checked for existing company customers"
                            >

                                <input
                                    type="email"
                                    value={
                                        data.email
                                    }
                                    onChange={
                                        event =>
                                            setData(
                                                'email',
                                                event.target.value
                                            )
                                    }
                                    placeholder="customer@example.com"
                                />

                            </Field>


                            <Field
                                label="Status"
                                required
                                error={
                                    errors.status
                                }
                            >

                                <select
                                    value={
                                        data.status
                                            ? '1'
                                            : '0'
                                    }
                                    onChange={
                                        event =>
                                            setData(
                                                'status',
                                                event.target.value ===
                                                '1'
                                            )
                                    }
                                >

                                    <option value="1">
                                        Active
                                    </option>

                                    <option value="0">
                                        Inactive
                                    </option>

                                </select>

                            </Field>

                        </div>

                    </FormSection>


                    {/* =================================================
                        GST / TAX
                       ================================================= */}

                    <FormSection
                        icon="bi-receipt"
                        eyebrow="Tax"
                        title="GST & PAN"
                        description="Fetch GST registration details and apply them to the form."
                    >

                        <div className="rr-customer-gst-grid">

                            <Field
                                label="GST Number"
                                error={
                                    errors.gst_number
                                }
                                hint="15-character GSTIN"
                            >

                                <div className="rr-customer-gst-input">

                                    <input
                                        type="text"
                                        maxLength="15"
                                        value={
                                            data.gst_number
                                        }
                                        onChange={
                                            event =>
                                                setData(
                                                    'gst_number',
                                                    normalizeUpper(
                                                        event.target.value
                                                    )
                                                )
                                        }
                                        placeholder="33ABCDE1234F1Z5"
                                    />


                                    <button
                                        type="button"
                                        onClick={
                                            lookupGst
                                        }
                                        disabled={
                                            gstLoading
                                        }
                                    >

                                        {
                                            gstLoading
                                                ? (
                                                    <>
                                                        <span className="rr-customer-spinner"></span>
                                                        Fetching
                                                    </>
                                                )
                                                : (
                                                    <>
                                                        <i className="bi bi-cloud-download"></i>
                                                        Fetch GST
                                                    </>
                                                )
                                        }

                                    </button>

                                </div>

                            </Field>


                            <Field
                                label="PAN Number"
                                error={
                                    errors.pan_number
                                }
                            >

                                <input
                                    type="text"
                                    maxLength="10"
                                    value={
                                        data.pan_number
                                    }
                                    onChange={
                                        event =>
                                            setData(
                                                'pan_number',
                                                normalizeUpper(
                                                    event.target.value
                                                )
                                            )
                                    }
                                    placeholder="ABCDE1234F"
                                />

                            </Field>

                        </div>


                        {
                            gstMessage && (

                                <div
                                    className={
                                        `rr-customer-inline-message ${gstMessage.type}`
                                    }
                                >

                                    <i
                                        className={
                                            gstMessage.type ===
                                                'success'
                                                ? 'bi bi-check-circle'
                                                : 'bi bi-exclamation-circle'
                                        }
                                    ></i>

                                    <span>
                                        {
                                            gstMessage.text
                                        }
                                    </span>

                                </div>

                            )
                        }


                        {
                            gstResult && (

                                <GstPreview
                                    data={
                                        gstResult
                                    }
                                    onApply={
                                        applyGstDetails
                                    }
                                />

                            )
                        }

                    </FormSection>


                    {/* =================================================
                        ADDRESS
                       ================================================= */}

                    <FormSection
                        icon="bi-geo-alt"
                        eyebrow="Address"
                        title="Billing & Shipping Address"
                        description="GST lookup can prefill the registered billing address."
                    >

                        <div className="rr-customer-form-grid">

                            <Field
                                label="Billing Address"
                                error={
                                    errors.billing_address
                                }
                                wide
                            >

                                <textarea
                                    rows="3"
                                    value={
                                        data.billing_address
                                    }
                                    onChange={
                                        event =>
                                            setData(
                                                'billing_address',
                                                event.target.value
                                            )
                                    }
                                    placeholder="Billing address"
                                />

                            </Field>


                            <Field
                                label="Shipping Address"
                                error={
                                    errors.shipping_address
                                }
                                wide
                            >

                                <div className="rr-same-address-option">

                                    <label>

                                        <input
                                            type="checkbox"
                                            checked={
                                                sameAsBilling
                                            }
                                            onChange={
                                                event => {

                                                    const checked =
                                                        event.target.checked;


                                                    setSameAsBilling(
                                                        checked
                                                    );


                                                    if (
                                                        checked
                                                    ) {

                                                        setData(
                                                            'shipping_address',
                                                            data.billing_address
                                                        );
                                                    }
                                                }
                                            }
                                        />

                                        <span>
                                            Same as Billing Address
                                        </span>

                                    </label>

                                </div>


                                <textarea
                                    rows="3"
                                    value={
                                        data.shipping_address
                                    }
                                    disabled={
                                        sameAsBilling
                                    }
                                    onChange={
                                        event =>
                                            setData(
                                                'shipping_address',
                                                event.target.value
                                            )
                                    }
                                    placeholder={
                                        sameAsBilling
                                            ? 'Same as billing address'
                                            : 'Enter shipping address'
                                    }
                                />

                            </Field>


                            <Field
                                label="City"
                                error={
                                    errors.city
                                }
                            >

                                <input
                                    type="text"
                                    value={
                                        data.city
                                    }
                                    onChange={
                                        event =>
                                            setData(
                                                'city',
                                                event.target.value
                                            )
                                    }
                                />

                            </Field>


                            <Field
                                label="State"
                                error={
                                    errors.state
                                }
                            >

                                <input
                                    type="text"
                                    value={
                                        data.state
                                    }
                                    onChange={
                                        event =>
                                            setData(
                                                'state',
                                                event.target.value
                                            )
                                    }
                                />

                            </Field>


                            <Field
                                label="Pincode"
                                error={
                                    errors.pincode
                                }
                            >

                                <input
                                    type="text"
                                    value={
                                        data.pincode
                                    }
                                    onChange={
                                        event =>
                                            setData(
                                                'pincode',
                                                event.target.value
                                            )
                                    }
                                />

                            </Field>


                            <Field
                                label="Country"
                                error={
                                    errors.country
                                }
                            >

                                <input
                                    type="text"
                                    value={
                                        data.country
                                    }
                                    onChange={
                                        event =>
                                            setData(
                                                'country',
                                                event.target.value
                                            )
                                    }
                                />

                            </Field>

                        </div>

                    </FormSection>


                    {/* =================================================
                        CREDIT
                       ================================================= */}

                    <FormSection
                        icon="bi-credit-card"
                        eyebrow="Accounts"
                        title="Credit Settings"
                        description="Configure opening balance and customer credit terms."
                    >

                        <div className="rr-customer-form-grid">

                            <Field
                                label="Credit Limit"
                                error={
                                    errors.credit_limit
                                }
                            >

                                <input
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    value={
                                        data.credit_limit
                                    }
                                    onChange={
                                        event =>
                                            setData(
                                                'credit_limit',
                                                event.target.value
                                            )
                                    }
                                />

                            </Field>


                            <Field
                                label="Credit Days"
                                error={
                                    errors.credit_days
                                }
                            >

                                <input
                                    type="number"
                                    min="0"
                                    step="1"
                                    value={
                                        data.credit_days
                                    }
                                    onChange={
                                        event =>
                                            setData(
                                                'credit_days',
                                                event.target.value
                                            )
                                    }
                                />

                            </Field>


                            <Field
                                label="Opening Balance"
                                error={
                                    errors.opening_balance
                                }
                            >

                                <input
                                    type="number"
                                    min="0"
                                    step="0.01"
                                    value={
                                        data.opening_balance
                                    }
                                    onChange={
                                        event =>
                                            setData(
                                                'opening_balance',
                                                event.target.value
                                            )
                                    }
                                />

                            </Field>


                            <Field
                                label="Balance Type"
                                error={
                                    errors.balance_type
                                }
                            >

                                <select
                                    value={
                                        data.balance_type
                                    }
                                    onChange={
                                        event =>
                                            setData(
                                                'balance_type',
                                                event.target.value
                                            )
                                    }
                                >

                                    <option value="Dr">
                                        Dr - Customer owes us
                                    </option>

                                    <option value="Cr">
                                        Cr - We owe customer
                                    </option>

                                </select>

                            </Field>

                        </div>

                    </FormSection>


                    {/* =================================================
                        REMARKS
                       ================================================= */}

                    <FormSection
                        icon="bi-chat-left-text"
                        eyebrow="Notes"
                        title="Remarks"
                        description="Optional internal notes for this customer."
                    >

                        <Field
                            label="Remarks"
                            error={
                                errors.remarks
                            }
                            wide
                        >

                            <textarea
                                rows="4"
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
                                placeholder="Enter customer remarks..."
                            />

                        </Field>

                    </FormSection>


                    {/* =================================================
                        FOOTER
                       ================================================= */}

                    <div className="rr-customer-form-footer">

                        <div className="rr-customer-form-footer-note">

                            <i className="bi bi-shield-check"></i>

                            Changes to this profile affect all branches where this customer is assigned.

                        </div>


                        <div className="rr-customer-form-footer-actions">

                            <button
                                type="button"
                                className="rr-customer-toolbar-btn"
                                onClick={
                                    () =>
                                        window.history.back()
                                }
                            >
                                Cancel
                            </button>


                            <button
                                type="submit"
                                className="rr-customer-primary-btn"
                                disabled={
                                    processing
                                }
                            >

                                {
                                    processing
                                        ? (
                                            <>
                                                <span className="rr-customer-spinner"></span>
                                                Saving
                                            </>
                                        )
                                        : (
                                            <>
                                                <i className="bi bi-check2-circle"></i>
                                                Update Customer
                                            </>
                                        )
                                }

                            </button>

                        </div>

                    </div>

                </form>

            </div>

        </BusinessLayout>
    );
}


/*
|--------------------------------------------------------------------------
| GST Preview
|--------------------------------------------------------------------------
*/

function GstPreview({
    data,
    onApply,
}) {

    const address =
        data?.address
        ?? {};


    return (

        <div className="rr-customer-gst-preview">

            <div className="rr-customer-gst-preview-header">

                <div>

                    <span>
                        GST Verification Result
                    </span>

                    <strong>
                        {
                            data.trade_name
                            ??
                            data.legal_name
                            ??
                            'Registered Business'
                        }
                    </strong>

                </div>


                <button
                    type="button"
                    onClick={
                        onApply
                    }
                >

                    <i className="bi bi-check2-square"></i>

                    Use GST Details

                </button>

            </div>


            <div className="rr-customer-gst-preview-grid">

                <PreviewItem
                    label="Legal Name"
                    value={
                        data.legal_name
                    }
                />

                <PreviewItem
                    label="Trade Name"
                    value={
                        data.trade_name
                    }
                />

                <PreviewItem
                    label="GST Number"
                    value={
                        data.gst_number
                    }
                />

                <PreviewItem
                    label="PAN"
                    value={
                        data.pan_number
                    }
                />

                <PreviewItem
                    label="GST Status"
                    value={
                        data.status
                    }
                />

                <PreviewItem
                    label="Registered Address"
                    value={
                        [
                            address.address_line,
                            address.city,
                            address.state,
                            address.pincode,
                        ]
                            .filter(
                                Boolean
                            )
                            .join(
                                ', '
                            )
                    }
                    wide
                />

            </div>

        </div>
    );
}


/*
|--------------------------------------------------------------------------
| Form Section
|--------------------------------------------------------------------------
*/

function FormSection({
    icon,
    eyebrow,
    title,
    description,
    children,
}) {

    return (

        <section className="rr-customer-form-section">

            <div className="rr-customer-form-section-header">

                <div className="rr-customer-form-section-icon">

                    <i
                        className={
                            `bi ${icon}`
                        }
                    ></i>

                </div>


                <div>

                    <span>
                        {
                            eyebrow
                        }
                    </span>

                    <strong>
                        {
                            title
                        }
                    </strong>

                    <p>
                        {
                            description
                        }
                    </p>

                </div>

            </div>


            <div className="rr-customer-form-section-body">

                {
                    children
                }

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
    error = null,
    hint = null,
    wide = false,
    children,
}) {

    return (

        <label
            className={
                `rr-customer-field ${wide
                    ? 'wide'
                    : ''
                }`
            }
        >

            <span className="rr-customer-field-label">

                {
                    label
                }

                {
                    required && (

                        <em>
                            *
                        </em>

                    )
                }

            </span>


            {
                children
            }


            {
                error
                    ? (
                        <small className="error">
                            {
                                error
                            }
                        </small>
                    )
                    : (
                        hint && (

                            <small>
                                {
                                    hint
                                }
                            </small>

                        )
                    )
            }

        </label>
    );
}


/*
|--------------------------------------------------------------------------
| Preview Item
|--------------------------------------------------------------------------
*/

function PreviewItem({
    label,
    value,
    wide = false,
}) {

    return (

        <div
            className={
                `rr-customer-preview-item ${wide
                    ? 'wide'
                    : ''
                }`
            }
        >

            <span>
                {
                    label
                }
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
| Helpers
|--------------------------------------------------------------------------
*/

function normalizeUpper(
    value
) {

    return String(
        value
        ?? ''
    )
        .trim()
        .toUpperCase();
}
