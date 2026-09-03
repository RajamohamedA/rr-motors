import {
    Head,
    router,
    useForm,
    usePage,
} from '@inertiajs/react';

import {
    useEffect,
    useRef,
} from 'react';

import BusinessLayout
    from '../../Layouts/BusinessLayout';

import MasterNavigation
    from '../../Components/Business/MasterNavigation';

import '../../../css/business/godowns.css';


export default function Create() {

    const {
        business,
    } = usePage().props;


    const {
        data,
        setData,
        post,
        processing,
        errors,
        clearErrors,
    } = useForm({

        godown_name:
            '',

        godown_code:
            '',

        address:
            '',

        status:
            true,
    });


    const formTopRef =
        useRef(null);


    const godownNameRef =
        useRef(null);


    const validationSummaryRef =
        useRef(null);


    /*
    |--------------------------------------------------------------------------
    | Initial Focus
    |--------------------------------------------------------------------------
    */

    useEffect(
        () => {

            window.requestAnimationFrame(
                () => {

                    godownNameRef
                        .current
                        ?.focus();
                }
            );

        },
        []
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
    | Scroll Helper
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


    /*
    |--------------------------------------------------------------------------
    | Focus First Validation Error
    |--------------------------------------------------------------------------
    */

    const focusFirstValidationError =
        validationErrors => {

            const keys =
                Object.keys(
                    validationErrors
                    ?? {}
                );


            if (
                keys.length === 0
            ) {
                return;
            }


            const firstField =
                keys[0];


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


                        window.setTimeout(
                            () => {

                                element.focus?.();

                            },
                            250
                        );


                        return;
                    }


                    scrollToElement(
                        validationSummaryRef.current
                        ??
                        formTopRef.current
                    );
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

            router.visit(
                '/godowns'
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Submit
    |--------------------------------------------------------------------------
    */

    const submit =
        event => {

            event.preventDefault();


            clearErrors();


            post(
                '/godowns',
                {
                    preserveScroll:
                        true,

                    onError:
                        validationErrors => {

                            focusFirstValidationError(
                                validationErrors
                            );
                        },
                }
            );
        };


    return (

        <BusinessLayout>

            <Head
                title="Add Godown"
            />


            <div
                className="rr-godown-form-page"
                ref={
                    formTopRef
                }
            >

                <MasterNavigation
                    active="godowns"
                    title="Godowns"
                    subtitle="Warehouse and branch stock locations"
                />


                {/* =====================================================
                    HEADER
                   ===================================================== */}

                <section className="rr-godown-form-header">

                    <div className="rr-godown-form-header-main">

                        <button
                            type="button"
                            className="rr-godown-form-back"
                            onClick={
                                goBack
                            }
                            title="Back to Godowns"
                        >

                            <i className="bi bi-arrow-left"></i>

                        </button>


                        <div className="rr-godown-form-header-icon">

                            <i className="bi bi-boxes"></i>

                        </div>


                        <div>

                            <span className="rr-godown-form-eyebrow">
                                Inventory Master
                            </span>


                            <h1>
                                Add Godown
                            </h1>


                            <p>
                                Create a stock location for the active branch.
                            </p>

                        </div>

                    </div>


                    <div className="rr-godown-form-context">

                        <div>

                            <span>
                                Company
                            </span>

                            <strong>
                                {
                                    business?.company_name
                                    ?? 'Active Company'
                                }
                            </strong>

                        </div>


                        <div>

                            <span>
                                Branch
                            </span>

                            <strong>
                                {
                                    business?.store_name
                                    ?? 'Active Branch'
                                }
                            </strong>

                        </div>

                    </div>

                </section>


                {/* =====================================================
                    INFO NOTE
                   ===================================================== */}

                <section className="rr-godown-form-note">

                    <div className="rr-godown-form-note-icon">

                        <i className="bi bi-info-circle"></i>

                    </div>


                    <div>

                        <strong>
                            Branch-isolated stock location
                        </strong>


                        <span>
                            This Godown will belong only to the currently selected branch. Products and stock from other branches will not be shown here.
                        </span>

                    </div>

                </section>


                {/* =====================================================
                    VALIDATION
                   ===================================================== */}

                {
                    Object.keys(
                        errors
                        ?? {}
                    ).length > 0 && (

                        <section
                            className="rr-godown-validation-summary"
                            ref={
                                validationSummaryRef
                            }
                        >

                            <div className="rr-godown-validation-summary-icon">

                                <i className="bi bi-exclamation-triangle"></i>

                            </div>


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


                {/* =====================================================
                    FORM
                   ===================================================== */}

                <form
                    className="rr-godown-form-window"
                    onSubmit={
                        submit
                    }
                >

                    <div className="rr-godown-form-toolbar">

                        <div className="rr-godown-form-toolbar-status">

                            <i className="bi bi-circle-fill"></i>

                            New Godown

                        </div>


                        <div className="rr-godown-form-toolbar-actions">

                            <button
                                type="button"
                                className="rr-godown-form-secondary-btn"
                                onClick={
                                    goBack
                                }
                            >

                                <i className="bi bi-x-lg"></i>

                                Cancel

                            </button>


                            <button
                                type="submit"
                                className="rr-godown-form-save-btn"
                                disabled={
                                    processing
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
                                        : 'Save Godown'
                                }

                            </button>

                        </div>

                    </div>


                    <div className="rr-godown-form-body">

                        <section className="rr-godown-form-section">

                            <div className="rr-godown-form-section-head">

                                <div className="rr-godown-form-section-icon">

                                    <i className="bi bi-building"></i>

                                </div>


                                <div>

                                    <h2>
                                        Godown Details
                                    </h2>


                                    <p>
                                        Enter the Godown identity and location details used in stock transactions.
                                    </p>

                                </div>

                            </div>


                            <div className="rr-godown-form-section-body">

                                <div className="rr-godown-form-grid">

                                    {/* =================================
                                        NAME
                                       ================================= */}

                                    <Field
                                        label="Godown Name"
                                        required
                                        error={
                                            errors.godown_name
                                        }
                                    >

                                        <input
                                            ref={
                                                godownNameRef
                                            }
                                            type="text"
                                            name="godown_name"
                                            value={
                                                data.godown_name
                                            }
                                            placeholder="Example: Main Spare Godown"
                                            autoComplete="off"
                                            maxLength="255"
                                            onChange={
                                                event =>
                                                    updateField(
                                                        'godown_name',
                                                        event.target.value
                                                    )
                                            }
                                        />

                                    </Field>


                                    {/* =================================
                                        CODE
                                       ================================= */}

                                    <Field
                                        label="Godown Code"
                                        error={
                                            errors.godown_code
                                        }
                                    >

                                        <input
                                            type="text"
                                            name="godown_code"
                                            value={
                                                data.godown_code
                                            }
                                            placeholder="Leave blank to auto-generate"
                                            autoComplete="off"
                                            maxLength="255"
                                            onChange={
                                                event =>
                                                    updateField(
                                                        'godown_code',
                                                        event.target.value
                                                            .toUpperCase()
                                                    )
                                            }
                                        />


                                        <div className="rr-godown-field-help">

                                            If left blank, the system will generate a branch-specific Godown code.

                                        </div>

                                    </Field>


                                    {/* =================================
                                        STATUS
                                       ================================= */}

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
                                                    updateField(
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


                                    {/* =================================
                                        ADDRESS
                                       ================================= */}

                                    <Field
                                        className="span-3"
                                        label="Address / Location"
                                        error={
                                            errors.address
                                        }
                                    >

                                        <textarea
                                            rows="5"
                                            name="address"
                                            value={
                                                data.address
                                            }
                                            placeholder="Godown address, landmark, floor, rack area or location notes..."
                                            maxLength="255"
                                            onChange={
                                                event =>
                                                    updateField(
                                                        'address',
                                                        event.target.value
                                                    )
                                            }
                                        ></textarea>


                                        <div className="rr-godown-field-help rr-godown-field-help-between">

                                            <span>
                                                Optional. Useful for physical stock location identification.
                                            </span>


                                            <span>
                                                {
                                                    data.address.length
                                                } / 255
                                            </span>

                                        </div>

                                    </Field>

                                </div>

                            </div>

                        </section>


                        {/* =================================================
                            INVENTORY INFORMATION
                           ================================================= */}

                        <section className="rr-godown-create-info">

                            <div className="rr-godown-create-info-icon">

                                <i className="bi bi-box-seam"></i>

                            </div>


                            <div>

                                <span>
                                    After Creation
                                </span>


                                <strong>
                                    Inventory will be calculated from Stock Ledger
                                </strong>


                                <small>
                                    When purchase, stock transfer, adjustment, or other inventory transactions are posted to this Godown, its product quantity and estimated stock value will automatically appear in the Godown inventory screen.
                                </small>

                            </div>

                        </section>

                    </div>


                    <div className="rr-godown-form-footer">

                        <div className="rr-godown-form-footer-info">

                            <i className="bi bi-shield-check"></i>

                            This Godown will be created for {
                                business?.store_name
                                ?? 'the active branch'
                            } only.

                        </div>


                        <div className="rr-godown-form-footer-actions">

                            <button
                                type="button"
                                className="rr-godown-form-secondary-btn"
                                onClick={
                                    goBack
                                }
                            >

                                Cancel

                            </button>


                            <button
                                type="submit"
                                className="rr-godown-form-save-btn"
                                disabled={
                                    processing
                                }
                            >

                                {
                                    processing
                                        ? (
                                            <span className="spinner-border spinner-border-sm"></span>
                                        )
                                        : (
                                            <i className="bi bi-check-lg"></i>
                                        )
                                }

                                {
                                    processing
                                        ? 'Saving...'
                                        : 'Save Godown'
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
                `rr-godown-form-field ${className} ${error
                    ? 'has-error'
                    : ''
                }`
            }
        >

            <label>

                {label}


                {
                    required && (

                        <span className="required">
                            *
                        </span>

                    )
                }

            </label>


            {children}


            {
                error && (

                    <div className="rr-godown-form-field-error">

                        <i className="bi bi-exclamation-circle"></i>

                        <span>
                            {error}
                        </span>

                    </div>

                )
            }

        </div>
    );
}