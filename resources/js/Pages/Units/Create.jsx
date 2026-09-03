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

import '../../../css/business/units.css';


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

        unit_name:
            '',

        short_name:
            '',

        description:
            '',

        status:
            true,
    });


    const formTopRef =
        useRef(null);


    const unitNameRef =
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

                    unitNameRef
                        .current
                        ?.focus();
                }
            );

        },
        []
    );


    /*
    |--------------------------------------------------------------------------
    | Field Update
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
    | Validation Focus
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
                '/units'
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
                '/units',
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
                title="Add Unit"
            />


            <div
                className="rr-unit-form-page"
                ref={
                    formTopRef
                }
            >

                <MasterNavigation
                    active="units"
                    title="Units"
                    subtitle="Units of measurement for products"
                />


                {/* =====================================================
                    HEADER
                   ===================================================== */}

                <section className="rr-unit-form-header">

                    <div className="rr-unit-form-header-main">

                        <button
                            type="button"
                            className="rr-unit-form-back"
                            onClick={
                                goBack
                            }
                            title="Back to units"
                        >

                            <i className="bi bi-arrow-left"></i>

                        </button>


                        <div className="rr-unit-form-header-icon">

                            <i className="bi bi-rulers"></i>

                        </div>


                        <div>

                            <span className="rr-unit-form-eyebrow">
                                Unit Master
                            </span>


                            <h1>
                                Add Custom Unit
                            </h1>


                            <p>
                                Create a new unit of measurement for the active branch.
                            </p>

                        </div>

                    </div>


                    <div className="rr-unit-form-context">

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
                    INFORMATION
                   ===================================================== */}

                <section className="rr-unit-system-note">

                    <div className="rr-unit-system-note-icon">

                        <i className="bi bi-info-circle"></i>

                    </div>


                    <div>

                        <strong>
                            Creating a custom unit
                        </strong>


                        <span>
                            Standard units such as Nos, Pcs, Kg, Ltr and Mtr are managed as system units. Use this form only when your branch needs an additional measurement unit.
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
                            className="rr-unit-validation-summary"
                            ref={
                                validationSummaryRef
                            }
                        >

                            <div className="rr-unit-validation-summary-icon">

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
                    className="rr-unit-form-window"
                    onSubmit={
                        submit
                    }
                >

                    <div className="rr-unit-form-toolbar">

                        <div className="rr-unit-form-toolbar-status">

                            <i className="bi bi-circle-fill"></i>

                            New Custom Unit

                        </div>


                        <div className="rr-unit-form-toolbar-actions">

                            <button
                                type="button"
                                className="rr-unit-form-secondary-btn"
                                onClick={
                                    goBack
                                }
                            >

                                <i className="bi bi-x-lg"></i>

                                Cancel

                            </button>


                            <button
                                type="submit"
                                className="rr-unit-form-save-btn"
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
                                        : 'Save Unit'
                                }

                            </button>

                        </div>

                    </div>


                    <div className="rr-unit-form-body">

                        <section className="rr-unit-form-section">

                            <div className="rr-unit-form-section-head">

                                <div className="rr-unit-form-section-icon">

                                    <i className="bi bi-rulers"></i>

                                </div>


                                <div>

                                    <h2>
                                        Unit Details
                                    </h2>


                                    <p>
                                        Enter the full unit name and the abbreviation used in product screens and invoices.
                                    </p>

                                </div>

                            </div>


                            <div className="rr-unit-form-section-body">

                                <div className="rr-unit-form-grid">

                                    {/* =================================
                                        UNIT NAME
                                       ================================= */}

                                    <Field
                                        label="Unit Name"
                                        required
                                        error={
                                            errors.unit_name
                                        }
                                    >

                                        <input
                                            ref={
                                                unitNameRef
                                            }
                                            type="text"
                                            name="unit_name"
                                            value={
                                                data.unit_name
                                            }
                                            placeholder="Example: Kilometre"
                                            autoComplete="off"
                                            onChange={
                                                event =>
                                                    updateField(
                                                        'unit_name',
                                                        event.target.value
                                                    )
                                            }
                                        />

                                    </Field>


                                    {/* =================================
                                        SHORT NAME
                                       ================================= */}

                                    <Field
                                        label="Short Name"
                                        required
                                        error={
                                            errors.short_name
                                        }
                                    >

                                        <input
                                            type="text"
                                            name="short_name"
                                            value={
                                                data.short_name
                                            }
                                            placeholder="Example: Km"
                                            autoComplete="off"
                                            maxLength="20"
                                            onChange={
                                                event =>
                                                    updateField(
                                                        'short_name',
                                                        event.target.value
                                                    )
                                            }
                                        />


                                        <div className="rr-unit-field-help">

                                            Used in products, billing and stock reports.

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
                                        DESCRIPTION
                                       ================================= */}

                                    <Field
                                        className="span-3"
                                        label="Description"
                                        error={
                                            errors.description
                                        }
                                    >

                                        <textarea
                                            rows="5"
                                            name="description"
                                            value={
                                                data.description
                                            }
                                            placeholder="Optional explanation of how this unit is used..."
                                            onChange={
                                                event =>
                                                    updateField(
                                                        'description',
                                                        event.target.value
                                                    )
                                            }
                                        ></textarea>

                                    </Field>

                                </div>

                            </div>

                        </section>

                    </div>


                    <div className="rr-unit-form-footer">

                        <div className="rr-unit-form-footer-info">

                            <i className="bi bi-shield-check"></i>

                            This unit will be available only to {
                                business?.store_name
                                ?? 'the active branch'
                            }.

                        </div>


                        <div className="rr-unit-form-footer-actions">

                            <button
                                type="button"
                                className="rr-unit-form-secondary-btn"
                                onClick={
                                    goBack
                                }
                            >

                                Cancel

                            </button>


                            <button
                                type="submit"
                                className="rr-unit-form-save-btn"
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
                                        : 'Save Unit'
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
                `rr-unit-form-field ${className} ${error
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

                    <div className="rr-unit-form-field-error">

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