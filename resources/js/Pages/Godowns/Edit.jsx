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


export default function Edit({
    godown,
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

        godown_name:
            godown?.godown_name
            ?? '',

        godown_code:
            godown?.godown_code
            ?? '',

        address:
            godown?.address
            ?? '',

        status:
            Boolean(
                godown?.status
            ),
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
                '/godowns'
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Open Godown
    |--------------------------------------------------------------------------
    */

    const openGodown =
        () => {

            router.visit(
                `/godowns/${godown.id}`
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Open Inventory
    |--------------------------------------------------------------------------
    */

    const openInventory =
        () => {

            router.visit(
                `/godowns/${godown.id}/products`
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
                `/godowns/${godown.id}`,
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


    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */

    return (

        <BusinessLayout>

            <Head
                title={
                    `Edit ${godown?.godown_name ?? 'Godown'}`
                }
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

                            <i className="bi bi-pencil-square"></i>

                        </div>


                        <div>

                            <span className="rr-godown-form-eyebrow">
                                Inventory Master
                            </span>


                            <h1>
                                Edit Godown
                            </h1>


                            <p>
                                Update {
                                    godown?.godown_name
                                    ?? 'Godown'
                                } for the active branch.
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
                    BRANCH PROTECTION
                   ===================================================== */}

                <section className="rr-godown-form-note">

                    <div className="rr-godown-form-note-icon">

                        <i className="bi bi-shield-lock"></i>

                    </div>


                    <div>

                        <strong>
                            Branch ownership is protected
                        </strong>


                        <span>
                            Company and branch cannot be changed from this form. This Godown remains permanently associated with the branch where it was created.
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
                                    ? 'Active Godown'
                                    : 'Inactive Godown'
                            }

                        </div>


                        <div className="rr-godown-form-toolbar-actions">

                            <button
                                type="button"
                                className="rr-godown-form-secondary-btn"
                                onClick={
                                    openInventory
                                }
                            >

                                <i className="bi bi-box-seam"></i>

                                Inventory

                            </button>


                            <button
                                type="button"
                                className="rr-godown-form-secondary-btn"
                                onClick={
                                    openGodown
                                }
                            >

                                <i className="bi bi-eye"></i>

                                View

                            </button>


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
                                        ? 'Updating...'
                                        : 'Update Godown'
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
                                        Update the Godown identity and physical location details used in inventory transactions.
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
                                            maxLength="255"
                                            autoComplete="off"
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
                                            maxLength="255"
                                            autoComplete="off"
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

                                            Must remain unique within this branch.

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
                                            maxLength="255"
                                            placeholder="Godown address, landmark, floor, rack area or location notes..."
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
                                                Optional physical location information.
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
                            INVENTORY SAFETY
                           ================================================= */}

                        <section className="rr-godown-edit-info">

                            <div className="rr-godown-edit-info-icon">

                                <i className="bi bi-database-check"></i>

                            </div>


                            <div className="rr-godown-edit-info-content">

                                <span>
                                    Inventory Safety
                                </span>


                                <strong>
                                    Editing this Godown does not change its stock
                                </strong>


                                <small>
                                    Current stock continues to be calculated from the Stock Ledger. Changing the name, code, address, or status does not alter product quantities or inventory valuation.
                                </small>

                            </div>

                        </section>

                    </div>


                    {/* =================================================
                        FOOTER
                       ================================================= */}

                    <div className="rr-godown-form-footer">

                        <div className="rr-godown-form-footer-info">

                            <i className="bi bi-shield-check"></i>

                            Company and branch ownership are protected.

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
                                        ? 'Updating...'
                                        : 'Update Godown'
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