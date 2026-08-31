import {
    Head,
    Link,
    router,
    useForm,
    usePage,
} from '@inertiajs/react';

import {
    useEffect,
    useRef,
    useState,
} from 'react';

import axios from 'axios';

import BusinessLayout
    from '../../Layouts/BusinessLayout';

import MasterNavigation
    from '../../Components/Business/MasterNavigation';

import '../../../css/business/suppliers.css';


export default function Edit({ supplier }) {

    const page =
        usePage();


    const {
        auth,
        activeCompany,
        activeStore,
    } = page.props;


    const permissions =
        auth?.permissions
        ?? [];


    const roles =
        auth?.roles
        ?? [];


    const user =
        auth?.user;


    const isSuperAdmin =
        Boolean(
            user?.is_super_user
        );


    const isCompanyOwner =
        roles.includes(
            'Company Admin'
        );


    const can =
        permission =>
            isSuperAdmin ||
            isCompanyOwner ||
            permissions.includes(
                permission
            );


    const {
        data,
        setData,
        put,
        processing,
        errors,
        clearErrors,
    } = useForm({

        supplier_name:
            supplier?.supplier_name
            ?? '',

        contact_person:
            supplier?.contact_person
            ?? '',

        phone:
            supplier?.phone
            ?? '',

        alternate_phone:
            supplier?.alternate_phone
            ?? '',

        email:
            supplier?.email
            ?? '',

        gst_number:
            supplier?.gst_number
            ?? '',

        pan_number:
            supplier?.pan_number
            ?? '',

        address:
            supplier?.address
            ?? '',

        city:
            supplier?.city
            ?? '',

        state:
            supplier?.state
            ?? '',

        country:
            supplier?.country
            ?? 'India',

        pincode:
            supplier?.pincode
            ?? '',

        opening_balance:
            supplier?.opening_balance
            ?? '0',

        credit_limit:
            supplier?.credit_limit
            ?? '0',

        credit_days:
            supplier?.credit_days
            ?? '0',

        balance_type:
            supplier?.balance_type
            ?? 'Cr',

        status:
            Boolean(
                supplier?.status
                ?? true
            ),

        remarks:
            supplier?.remarks
            ?? '',
    });


    const [
        duplicateSupplier,
        setDuplicateSupplier,
    ] =
        useState(
            null
        );


    const [
        duplicateLoading,
        setDuplicateLoading,
    ] =
        useState(
            false
        );


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


    const duplicateTimer =
        useRef(
            null
        );


    const formTopRef =
        useRef(
            null
        );


    const duplicatePanelRef =
        useRef(
            null
        );


    const gstMessageRef =
        useRef(
            null
        );


    /*
    |--------------------------------------------------------------------------
    | Scroll / Focus Helpers
    |--------------------------------------------------------------------------
    */

    const scrollToElement =
        element => {

            if (
                !element
            ) {

                return;
            }


            element.scrollIntoView({
                behavior:
                    'smooth',

                block:
                    'center',
            });
        };


    const focusFirstValidationError =
        validationErrors => {

            const errorKeys =
                Object.keys(
                    validationErrors
                    ?? {}
                );


            if (
                errorKeys.length === 0
            ) {

                scrollToElement(
                    formTopRef.current
                );

                return;
            }


            const firstField =
                errorKeys[0];


            window.requestAnimationFrame(
                () => {

                    const element =
                        document.querySelector(
                            `[name="${firstField}"]`
                        );


                    if (
                        element
                    ) {

                        scrollToElement(
                            element
                        );


                        setTimeout(
                            () => {

                                element.focus?.();

                            },
                            350
                        );


                        return;
                    }


                    scrollToElement(
                        formTopRef.current
                    );
                }
            );
        };


    const scrollToGlobalNotification =
        () => {

            window.requestAnimationFrame(
                () => {

                    const notification =
                        document.querySelector(
                            '.rr-notification-container'
                        );


                    if (
                        notification
                    ) {

                        notification.scrollIntoView({
                            behavior:
                                'smooth',

                            block:
                                'start',
                        });


                        return;
                    }


                    window.scrollTo({
                        top:
                            0,

                        behavior:
                            'smooth',
                    });
                }
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Back
    |--------------------------------------------------------------------------
    */

    const goBack =
        () => {

            if (
                window.history.length > 1
            ) {

                window.history.back();

                return;
            }


            router.visit(
                '/suppliers'
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Normalize Inputs
    |--------------------------------------------------------------------------
    */

    const changeUppercase =
        (
            field,
            value
        ) => {

            setData(
                field,
                String(
                    value
                    ?? ''
                )
                    .toUpperCase()
                    .replace(
                        /\s+/g,
                        ''
                    )
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Duplicate Lookup
    |--------------------------------------------------------------------------
    */

    const checkDuplicate =
        async () => {

            const supplierName =
                data.supplier_name
                    .trim();


            const gst =
                data.gst_number
                    .trim();


            const phone =
                data.phone
                    .trim();


            const email =
                data.email
                    .trim();


            if (
                !supplierName &&
                !gst &&
                !phone &&
                !email
            ) {

                setDuplicateSupplier(
                    null
                );

                return;
            }


            setDuplicateLoading(
                true
            );


            try {

                const response =
                    await axios.post(
                        '/suppliers/duplicate-lookup',
                        {
                            supplier_name:
                                supplierName || null,

                            gst_number:
                                gst || null,

                            phone:
                                phone || null,

                            email:
                                email || null,

                            ignore_supplier_id:
                                supplier?.id
                                ?? null,
                        }
                    );


                if (
                    response.data?.found
                ) {

                    setDuplicateSupplier({
                        ...response.data.supplier,

                        already_in_branch:
                            Boolean(
                                response.data
                                    ?.already_in_branch
                            ),
                    });


                    window.requestAnimationFrame(
                        () => {

                            scrollToElement(
                                duplicatePanelRef.current
                            );
                        }
                    );

                } else {

                    setDuplicateSupplier(
                        null
                    );
                }

            } catch (
            error
            ) {

                /*
                 * Backend validation remains authoritative.
                 */

            } finally {

                setDuplicateLoading(
                    false
                );
            }
        };


    useEffect(
        () => {

            clearTimeout(
                duplicateTimer.current
            );


            duplicateTimer.current =
                setTimeout(
                    () => {

                        checkDuplicate();

                    },
                    550
                );


            return () =>
                clearTimeout(
                    duplicateTimer.current
                );

        },
        [
            data.supplier_name,
            data.gst_number,
            data.phone,
            data.email,
        ]
    );


    /*
    |--------------------------------------------------------------------------
    | GST Lookup
    |--------------------------------------------------------------------------
    */

    const fetchGst =
        async () => {

            const gstNumber =
                data.gst_number
                    .trim()
                    .toUpperCase();


            if (
                gstNumber.length !== 15
            ) {

                setGstMessage({
                    type:
                        'error',

                    text:
                        'Enter a valid 15-character GSTIN first.',
                });


                window.requestAnimationFrame(
                    () => {

                        scrollToElement(
                            gstMessageRef.current
                        );
                    }
                );


                return;
            }


            setGstLoading(
                true
            );

            setGstMessage(
                null
            );


            try {

                const response =
                    await axios.post(
                        '/suppliers/gst-lookup',
                        {
                            gst_number:
                                gstNumber,
                        }
                    );


                const result =
                    response.data?.data
                    ?? {};


                const address =
                    result.address
                    ?? {};


                setData(
                    current => ({

                        ...current,

                        gst_number:
                            result.gst_number
                            ?? gstNumber,

                        supplier_name:
                            result.trade_name
                            ||
                            result.legal_name
                            ||
                            current.supplier_name,

                        contact_person:
                            current.contact_person,

                        pan_number:
                            result.pan_number
                            ||
                            current.pan_number,

                        address:
                            address.address_line
                            ||
                            address.address
                            ||
                            current.address,

                        city:
                            address.city
                            ||
                            current.city,

                        state:
                            address.state
                            ||
                            current.state,

                        country:
                            address.country
                            ||
                            current.country
                            ||
                            'India',

                        pincode:
                            address.pincode
                            ||
                            current.pincode,
                    })
                );


                setGstMessage({
                    type:
                        'success',

                    text:
                        'GST details fetched. Please verify the supplier name and address before saving.',
                });


                window.requestAnimationFrame(
                    () => {

                        scrollToElement(
                            gstMessageRef.current
                        );
                    }
                );


                setTimeout(
                    checkDuplicate,
                    100
                );

            } catch (
            error
            ) {

                setGstMessage({
                    type:
                        'error',

                    text:
                        error.response?.data?.message
                        ??
                        'GST details could not be fetched. You can continue entering the supplier manually.',
                });


                window.requestAnimationFrame(
                    () => {

                        scrollToElement(
                            gstMessageRef.current
                        );
                    }
                );

            } finally {

                setGstLoading(
                    false
                );
            }
        };


    /*
    |--------------------------------------------------------------------------
    | Save
    |--------------------------------------------------------------------------
    */

    const submit =
        event => {

            event.preventDefault();


            if (
                duplicateSupplier
            ) {

                return;
            }


            clearErrors();


            put(
                `/suppliers/${supplier.id}`,
                {
                    preserveScroll:
                        true,

                    onError:
                        validationErrors => {

                            focusFirstValidationError(
                                validationErrors
                            );
                        },

                    onSuccess:
                        () => {

                            scrollToGlobalNotification();
                        },
                }
            );
        };


    return (

        <BusinessLayout>

            <Head
                title="Edit Supplier"
            />


            <div
                className="rr-supplier-create-page"
                ref={
                    formTopRef
                }
            >

                <MasterNavigation
                    active="suppliers"
                    title="Suppliers"
                    subtitle="Supplier master, GST and credit management"
                />


                <section className="rr-supplier-create-titlebar">

                    <div className="rr-supplier-create-title">

                        <button
                            type="button"
                            className="rr-supplier-back-btn"
                            onClick={
                                goBack
                            }
                            title="Back"
                        >

                            <i className="bi bi-arrow-left"></i>

                        </button>


                        <div className="rr-supplier-create-icon">

                            <i className="bi bi-truck"></i>

                        </div>


                        <div>

                            <span>
                                Supplier Master
                            </span>

                            <h1>
                                Edit Supplier
                            </h1>

                            <p>
                                Update the supplier master. Changes apply to every branch where this supplier is assigned.
                            </p>

                        </div>

                    </div>


                    <div className="rr-supplier-create-context">

                        <div>

                            <span>
                                Company
                            </span>

                            <strong>
                                {
                                    activeCompany?.company_name
                                    ??
                                    activeCompany?.name
                                    ??
                                    'Active Company'
                                }
                            </strong>

                        </div>


                        <div>

                            <span>
                                Branch
                            </span>

                            <strong>
                                {
                                    activeStore?.store_name
                                    ??
                                    'Active Branch'
                                }
                            </strong>

                        </div>

                    </div>

                </section>


                {
                    supplier?.shared && (

                        <section className="rr-supplier-shared-warning">

                            <i className="bi bi-exclamation-triangle"></i>

                            <div>

                                <strong>
                                    Shared supplier
                                </strong>

                                <span>
                                    This supplier is assigned to {
                                        supplier?.stores_count
                                        ?? 0
                                    } branches. Saving changes updates the supplier master for all of them.
                                </span>

                            </div>

                        </section>

                    )
                }


                {
                    duplicateSupplier && (

                        <div
                            ref={
                                duplicatePanelRef
                            }
                        >

                            <DuplicateSupplierPanel
                                supplier={
                                    duplicateSupplier
                                }
                            />

                        </div>

                    )
                }


                {
                    Object.keys(
                        errors
                        ?? {}
                    ).length > 0 && (

                        <section className="rr-supplier-validation-summary">

                            <i className="bi bi-exclamation-triangle"></i>

                            <div>

                                <strong>
                                    Please correct the highlighted fields.
                                </strong>

                                <span>
                                    {
                                        Object.values(
                                            errors
                                        )[0]
                                    }
                                </span>

                            </div>

                        </section>

                    )
                }


                <form
                    onSubmit={
                        submit
                    }
                    className="rr-supplier-create-window"
                >

                    <div className="rr-supplier-create-toolbar">

                        <div>

                            <span className="rr-supplier-form-status">

                                <i className="bi bi-circle-fill"></i>

                                Edit Supplier

                            </span>


                            {
                                duplicateLoading && (

                                    <span className="rr-supplier-lookup-status">

                                        <span className="spinner-border spinner-border-sm"></span>

                                        Checking supplier name / GST / phone / email...

                                    </span>

                                )
                            }

                        </div>


                        <div className="rr-supplier-create-toolbar-actions">

                            <button
                                type="button"
                                className="rr-supplier-secondary-btn"
                                onClick={
                                    goBack
                                }
                            >

                                <i className="bi bi-x-lg"></i>

                                Cancel

                            </button>


                            <button
                                type="submit"
                                className="rr-supplier-save-btn"
                                disabled={
                                    processing ||
                                    Boolean(
                                        duplicateSupplier
                                    )
                                }
                            >

                                {
                                    processing
                                        ? (
                                            <span className="spinner-border spinner-border-sm"></span>
                                        )
                                        : (
                                            <i className="bi bi-floppy"></i>
                                        )
                                }

                                {
                                    processing
                                        ? 'Saving...'
                                        : 'Update Supplier'
                                }

                            </button>

                        </div>

                    </div>


                    <div className="rr-supplier-form-body">

                        <FormSection
                            icon="bi-receipt"
                            title="GST & Business Identity"
                            subtitle="Use GST lookup to fill registered supplier details"
                        >

                            <div className="rr-supplier-form-grid">

                                <Field
                                    className="span-2"
                                    label="GST Number"
                                    error={
                                        errors.gst_number
                                    }
                                >

                                    <div className="rr-supplier-gst-control">

                                        <input
                                            type="text"
                                            name="gst_number"
                                            value={
                                                data.gst_number
                                            }
                                            maxLength="15"
                                            placeholder="33ABCDE1234F1Z5"
                                            onChange={
                                                event =>
                                                    changeUppercase(
                                                        'gst_number',
                                                        event.target.value
                                                    )
                                            }
                                            onBlur={
                                                checkDuplicate
                                            }
                                        />


                                        <button
                                            type="button"
                                            onClick={
                                                fetchGst
                                            }
                                            disabled={
                                                gstLoading ||
                                                data.gst_number
                                                    .trim()
                                                    .length !== 15
                                            }
                                        >

                                            {
                                                gstLoading
                                                    ? (
                                                        <span className="spinner-border spinner-border-sm"></span>
                                                    )
                                                    : (
                                                        <i className="bi bi-cloud-download"></i>
                                                    )
                                            }

                                            {
                                                gstLoading
                                                    ? 'Fetching'
                                                    : 'Fetch GST'
                                            }

                                        </button>

                                    </div>


                                    <small className="rr-supplier-field-help">
                                        Optional. GST lookup is handled securely on the Laravel server.
                                    </small>

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
                                        name="pan_number"
                                        value={
                                            data.pan_number
                                        }
                                        placeholder="ABCDE1234F"
                                        onChange={
                                            event =>
                                                changeUppercase(
                                                    'pan_number',
                                                    event.target.value
                                                )
                                        }
                                    />

                                </Field>


                                <div
                                    className="rr-supplier-gst-result-cell"
                                    ref={
                                        gstMessageRef
                                    }
                                >

                                    {
                                        gstMessage && (

                                            <div
                                                className={
                                                    `rr-supplier-gst-message ${gstMessage.type}`
                                                }
                                            >

                                                <i
                                                    className={
                                                        gstMessage.type ===
                                                            'success'
                                                            ? 'bi bi-check-circle'
                                                            : 'bi bi-exclamation-triangle'
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

                                </div>

                            </div>

                        </FormSection>


                        <FormSection
                            icon="bi-building"
                            title="Supplier Details"
                            subtitle="Primary supplier and contact information"
                        >

                            <div className="rr-supplier-form-grid">

                                <Field
                                    className="span-2"
                                    label="Supplier Name"
                                    required
                                    error={
                                        errors.supplier_name
                                    }
                                >

                                    <input
                                        type="text"
                                        autoFocus
                                        name="supplier_name"
                                        value={
                                            data.supplier_name
                                        }
                                        placeholder="Enter supplier / company name"
                                        onChange={
                                            event =>
                                                setData(
                                                    'supplier_name',
                                                    event.target.value
                                                )
                                        }
                                        onBlur={
                                            checkDuplicate
                                        }
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
                                        name="contact_person"
                                        value={
                                            data.contact_person
                                        }
                                        placeholder="Contact person"
                                        onChange={
                                            event =>
                                                setData(
                                                    'contact_person',
                                                    event.target.value
                                                )
                                        }
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
                                        name="phone"
                                        value={
                                            data.phone
                                        }
                                        placeholder="Primary phone"
                                        onChange={
                                            event =>
                                                setData(
                                                    'phone',
                                                    event.target.value
                                                )
                                        }
                                        onBlur={
                                            checkDuplicate
                                        }
                                    />

                                </Field>


                                <Field
                                    label="Alternate Phone"
                                    error={
                                        errors.alternate_phone
                                    }
                                >

                                    <input
                                        type="text"
                                        name="alternate_phone"
                                        value={
                                            data.alternate_phone
                                        }
                                        placeholder="Alternate phone"
                                        onChange={
                                            event =>
                                                setData(
                                                    'alternate_phone',
                                                    event.target.value
                                                )
                                        }
                                    />

                                </Field>


                                <Field
                                    className="span-2"
                                    label="Email"
                                    error={
                                        errors.email
                                    }
                                >

                                    <input
                                        type="email"
                                        name="email"
                                        value={
                                            data.email
                                        }
                                        placeholder="supplier@example.com"
                                        onChange={
                                            event =>
                                                setData(
                                                    'email',
                                                    event.target.value
                                                )
                                        }
                                        onBlur={
                                            checkDuplicate
                                        }
                                    />

                                </Field>

                            </div>

                        </FormSection>


                        <FormSection
                            icon="bi-geo-alt"
                            title="Registered Address"
                            subtitle="Supplier billing / registered business address"
                        >

                            <div className="rr-supplier-form-grid">

                                <Field
                                    className="span-4"
                                    label="Address"
                                    error={
                                        errors.address
                                    }
                                >

                                    <textarea
                                        rows="3"
                                        name="address"
                                        value={
                                            data.address
                                        }
                                        placeholder="Door number, street, area..."
                                        onChange={
                                            event =>
                                                setData(
                                                    'address',
                                                    event.target.value
                                                )
                                        }
                                    ></textarea>

                                </Field>


                                <Field
                                    label="City"
                                    error={
                                        errors.city
                                    }
                                >

                                    <input
                                        type="text"
                                        name="city"
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
                                        name="state"
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
                                    label="Country"
                                    error={
                                        errors.country
                                    }
                                >

                                    <input
                                        type="text"
                                        name="country"
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


                                <Field
                                    label="Pincode"
                                    error={
                                        errors.pincode
                                    }
                                >

                                    <input
                                        type="text"
                                        maxLength="10"
                                        name="pincode"
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

                            </div>

                        </FormSection>


                        <FormSection
                            icon="bi-wallet2"
                            title="Account & Credit Settings"
                            subtitle="Opening payable and purchase credit controls"
                        >

                            <div className="rr-supplier-form-grid">

                                <Field
                                    label="Opening Balance"
                                    error={
                                        errors.opening_balance
                                    }
                                >

                                    <div className="rr-supplier-money-input">

                                        <span>
                                            ₹
                                        </span>

                                        <input
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            name="opening_balance"
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

                                    </div>

                                </Field>


                                <Field
                                    label="Balance Type"
                                    error={
                                        errors.balance_type
                                    }
                                >

                                    <select
                                        name="balance_type"
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

                                        <option value="Cr">
                                            Cr - We owe supplier
                                        </option>

                                        <option value="Dr">
                                            Dr - Supplier owes us
                                        </option>

                                    </select>

                                </Field>


                                <Field
                                    label="Credit Limit"
                                    error={
                                        errors.credit_limit
                                    }
                                >

                                    <div className="rr-supplier-money-input">

                                        <span>
                                            ₹
                                        </span>

                                        <input
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            name="credit_limit"
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

                                    </div>

                                </Field>


                                <Field
                                    label="Credit Days"
                                    error={
                                        errors.credit_days
                                    }
                                >

                                    <div className="rr-supplier-days-input">

                                        <input
                                            type="number"
                                            min="0"
                                            max="3650"
                                            name="credit_days"
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

                                        <span>
                                            days
                                        </span>

                                    </div>

                                </Field>


                                <Field
                                    label="Status"
                                    error={
                                        errors.status
                                    }
                                >

                                    <select
                                        name="status"
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


                                <Field
                                    className="span-3"
                                    label="Remarks"
                                    error={
                                        errors.remarks
                                    }
                                >

                                    <textarea
                                        rows="2"
                                        name="remarks"
                                        value={
                                            data.remarks
                                        }
                                        placeholder="Optional notes about this supplier..."
                                        onChange={
                                            event =>
                                                setData(
                                                    'remarks',
                                                    event.target.value
                                                )
                                        }
                                    ></textarea>

                                </Field>

                            </div>

                        </FormSection>

                    </div>


                    <div className="rr-supplier-form-footer">

                        <div>

                            <i className="bi bi-info-circle"></i>

                            Changes update the shared supplier master for all assigned branches.

                        </div>


                        <div>

                            <button
                                type="button"
                                className="rr-supplier-secondary-btn"
                                onClick={
                                    goBack
                                }
                            >
                                Cancel
                            </button>


                            <button
                                type="submit"
                                className="rr-supplier-save-btn"
                                disabled={
                                    processing ||
                                    Boolean(
                                        duplicateSupplier
                                    )
                                }
                            >

                                <i className="bi bi-check-lg"></i>

                                Update Supplier

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
| Form Section
|--------------------------------------------------------------------------
*/

function FormSection({
    icon,
    title,
    subtitle,
    children,
}) {

    return (

        <section className="rr-supplier-form-section">

            <div className="rr-supplier-form-section-head">

                <div className="rr-supplier-form-section-icon">

                    <i
                        className={
                            `bi ${icon}`
                        }
                    ></i>

                </div>


                <div>

                    <h2>
                        {
                            title
                        }
                    </h2>

                    <p>
                        {
                            subtitle
                        }
                    </p>

                </div>

            </div>


            <div className="rr-supplier-form-section-body">

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
    error,
    className = '',
    children,
}) {

    return (

        <div
            className={
                `rr-supplier-field ${className}`
            }
        >

            <label>

                {
                    label
                }

                {
                    required && (

                        <span className="required">
                            *
                        </span>

                    )
                }

            </label>


            {
                children
            }


            {
                error && (

                    <div className="rr-supplier-field-error">

                        <i className="bi bi-exclamation-circle"></i>

                        {
                            error
                        }

                    </div>

                )
            }

        </div>
    );
}


/*
|--------------------------------------------------------------------------
| Duplicate Supplier Panel
|--------------------------------------------------------------------------
*/

function DuplicateSupplierPanel({
    supplier,
}) {

    const alreadyInBranch =
        Boolean(
            supplier.already_in_branch
        );


    return (

        <section
            className={
                `rr-supplier-duplicate-panel ${alreadyInBranch
                    ? 'danger'
                    : 'warning'
                }`
            }
        >

            <div className="rr-supplier-duplicate-icon">

                <i
                    className={
                        alreadyInBranch
                            ? 'bi bi-exclamation-octagon'
                            : 'bi bi-diagram-3'
                    }
                ></i>

            </div>


            <div className="rr-supplier-duplicate-content">

                <strong>

                    {
                        alreadyInBranch
                            ? 'Supplier already exists in this branch'
                            : 'Another supplier matches these details'
                    }

                </strong>


                <p>

                    {
                        alreadyInBranch
                            ? 'Another supplier in this branch uses the same name, GST number, phone or email. Update the details before saving.'
                            : 'Another supplier in this company uses the same name, GST number, phone or email. Update the details before saving.'
                    }

                </p>


                <div className="rr-supplier-duplicate-details">

                    <span>

                        <b>
                            {
                                supplier.supplier_code
                                ?? 'Supplier'
                            }
                        </b>

                    </span>

                    <span>
                        {
                            supplier.supplier_name
                        }
                    </span>

                    {
                        supplier.gst_number && (

                            <span>
                                GST: {
                                    supplier.gst_number
                                }
                            </span>

                        )
                    }

                    {
                        supplier.phone && (

                            <span>
                                Phone: {
                                    supplier.phone
                                }
                            </span>

                        )
                    }

                </div>

            </div>

        </section>
    );
}
