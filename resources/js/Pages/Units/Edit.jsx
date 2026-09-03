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


export default function Edit({
    unit,
}) {

    const {
        business,
    } = usePage().props;


    const {
        data,
        setData,
        put,
        processing,
        errors,
        clearErrors,
    } = useForm({

        unit_name:
            unit?.unit_name
            ?? '',

        short_name:
            unit?.short_name
            ?? '',

        description:
            unit?.description
            ?? '',

        status:
            Boolean(
                unit?.status
            ),
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

                    if (
                        !unit?.is_system_unit
                    ) {

                        unitNameRef
                            .current
                            ?.focus();
                    }
                }
            );

        },
        [
            unit?.is_system_unit,
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
    | First Validation Error
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


            put(
                `/units/${unit.id}`,
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
                title={`Edit ${unit.unit_name}`}
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

                            <i
                                className={
                                    unit?.is_system_unit
                                        ? 'bi bi-shield-check'
                                        : 'bi bi-pencil-square'
                                }
                            ></i>

                        </div>


                        <div>

                            <span className="rr-unit-form-eyebrow">

                                {
                                    unit?.is_system_unit
                                        ? 'System Unit'
                                        : 'Custom Unit'
                                }

                            </span>


                            <h1>
                                Edit Unit
                            </h1>


                            <p>
                                Update {unit.unit_name} for the active branch.
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
                    SYSTEM UNIT NOTICE
                   ===================================================== */}

                {
                    unit?.is_system_unit && (

                        <section className="rr-unit-system-note">

                            <div className="rr-unit-system-note-icon">

                                <i className="bi bi-shield-lock"></i>

                            </div>


                            <div>

                                <strong>
                                    Protected system unit
                                </strong>


                                <span>
                                    The standard unit name and abbreviation are protected to keep product data consistent. You may update the description or change its active status.
                                </span>

                            </div>

                        </section>

                    )
                }


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

                            <i
                                className={
                                    `bi bi-circle-fill ${data.status
                                        ? 'is-active'
                                        : 'is-inactive'
                                    }`
                                }
                            ></i>

                            {
                                data.status
                                    ? 'Active Unit'
                                    : 'Inactive Unit'
                            }

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
                                        ? 'Updating...'
                                        : 'Update Unit'
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
                                        Manage the measurement unit used in product, billing and inventory screens.
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
                                            readOnly={
                                                Boolean(
                                                    unit?.is_system_unit
                                                )
                                            }
                                            onChange={
                                                event => {

                                                    if (
                                                        unit?.is_system_unit
                                                    ) {
                                                        return;
                                                    }


                                                    updateField(
                                                        'unit_name',
                                                        event.target.value
                                                    );
                                                }
                                            }
                                        />


                                        {
                                            unit?.is_system_unit && (

                                                <div className="rr-unit-system-lock">

                                                    <i className="bi bi-lock"></i>

                                                    Unit name is protected for system units.

                                                </div>

                                            )
                                        }

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
                                            maxLength="20"
                                            readOnly={
                                                Boolean(
                                                    unit?.is_system_unit
                                                )
                                            }
                                            onChange={
                                                event => {

                                                    if (
                                                        unit?.is_system_unit
                                                    ) {
                                                        return;
                                                    }


                                                    updateField(
                                                        'short_name',
                                                        event.target.value
                                                    );
                                                }
                                            }
                                        />


                                        {
                                            unit?.is_system_unit
                                                ? (

                                                    <div className="rr-unit-system-lock">

                                                        <i className="bi bi-lock"></i>

                                                        Short name is protected for system units.

                                                    </div>

                                                )
                                                : (

                                                    <div className="rr-unit-field-help">

                                                        Used in products, invoices and stock reports.

                                                    </div>

                                                )
                                        }

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
                                            placeholder="Optional description..."
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


                        {/* =================================================
                            TYPE INFORMATION
                           ================================================= */}

                        <section className="rr-unit-edit-info">

                            <div className="rr-unit-edit-info-icon">

                                <i
                                    className={
                                        unit?.is_system_unit
                                            ? 'bi bi-shield-check'
                                            : 'bi bi-pencil-square'
                                    }
                                ></i>

                            </div>


                            <div className="rr-unit-edit-info-content">

                                <span>
                                    Unit Type
                                </span>


                                <strong>

                                    {
                                        unit?.is_system_unit
                                            ? 'System Unit'
                                            : 'Custom Unit'
                                    }

                                </strong>


                                <small>

                                    {
                                        unit?.is_system_unit
                                            ? 'System units are protected from deletion and identity changes.'
                                            : 'Custom units belong only to the active branch and can be fully managed.'
                                    }

                                </small>

                            </div>

                        </section>

                    </div>


                    <div className="rr-unit-form-footer">

                        <div className="rr-unit-form-footer-info">

                            <i className="bi bi-shield-check"></i>

                            Company and branch ownership cannot be changed from this form.

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
                                        ? 'Updating...'
                                        : 'Update Unit'
                                }

                            </button>

                        </div>

                    </div>

                </form>

            </div>

        </BusinessLayout>
    );
}


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