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

import '../../../css/business/brands.css';


export default function Edit({
    brand,
}) {

    /*
    |--------------------------------------------------------------------------
    | Shared Props
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
        put,
        processing,
        errors,
        clearErrors,
    } = useForm({

        brand_name:
            brand?.brand_name
            ?? '',

        description:
            brand?.description
            ?? '',

        status:
            Boolean(
                brand?.status
            ),
    });


    /*
    |--------------------------------------------------------------------------
    | References
    |--------------------------------------------------------------------------
    */

    const formTopRef =
        useRef(null);

    const brandNameRef =
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

                    brandNameRef
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
                            300
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
                '/brands'
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Products
    |--------------------------------------------------------------------------
    */

    const openProducts =
        () => {

            router.visit(
                `/brands/${brand.id}/products`
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
                `/brands/${brand.id}`,
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
                title={`Edit ${brand.brand_name}`}
            />


            <div
                className="rr-brand-form-page"
                ref={
                    formTopRef
                }
            >

                <MasterNavigation
                    active="brands"
                    title="Brands"
                    subtitle="Product manufacturer and brand master"
                />


                {/* =====================================================
                    HEADER
                   ===================================================== */}

                <section className="rr-brand-form-header">

                    <div className="rr-brand-form-header-main">

                        <button
                            type="button"
                            className="rr-brand-form-back"
                            onClick={
                                goBack
                            }
                            title="Back to brands"
                        >

                            <i className="bi bi-arrow-left"></i>

                        </button>


                        <div className="rr-brand-form-header-icon">

                            <i className="bi bi-pencil-square"></i>

                        </div>


                        <div>

                            <span className="rr-brand-form-eyebrow">
                                Brand Master
                            </span>


                            <h1>
                                Edit Brand
                            </h1>


                            <p>
                                Update {brand.brand_name} for the active branch.
                            </p>

                        </div>

                    </div>


                    <div className="rr-brand-form-context">

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
                    VALIDATION SUMMARY
                   ===================================================== */}

                {
                    Object.keys(
                        errors
                        ?? {}
                    ).length > 0 && (

                        <section
                            className="rr-brand-validation-summary"
                            ref={
                                validationSummaryRef
                            }
                        >

                            <div className="rr-brand-validation-summary-icon">

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
                    className="rr-brand-form-window"
                    onSubmit={
                        submit
                    }
                >

                    {/* =================================================
                        TOOLBAR
                       ================================================= */}

                    <div className="rr-brand-form-toolbar">

                        <div className="rr-brand-form-toolbar-status">

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
                                    ? 'Active Brand'
                                    : 'Inactive Brand'
                            }

                        </div>


                        <div className="rr-brand-form-toolbar-actions">

                            {
                                Number(
                                    brand?.products_count
                                    ?? 0
                                ) > 0 && (

                                    <button
                                        type="button"
                                        className="rr-brand-form-secondary-btn"
                                        onClick={
                                            openProducts
                                        }
                                    >

                                        <i className="bi bi-box-seam"></i>

                                        Products

                                        <span className="rr-brand-form-count">

                                            {
                                                brand.products_count
                                            }

                                        </span>

                                    </button>

                                )
                            }


                            <button
                                type="button"
                                className="rr-brand-form-secondary-btn"
                                onClick={
                                    goBack
                                }
                            >

                                <i className="bi bi-x-lg"></i>

                                Cancel

                            </button>


                            <button
                                type="submit"
                                className="rr-brand-form-save-btn"
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
                                        : 'Update Brand'
                                }

                            </button>

                        </div>

                    </div>


                    {/* =================================================
                        BODY
                       ================================================= */}

                    <div className="rr-brand-form-body">

                        <section className="rr-brand-form-section">

                            <div className="rr-brand-form-section-head">

                                <div className="rr-brand-form-section-icon">

                                    <i className="bi bi-tag"></i>

                                </div>


                                <div>

                                    <h2>
                                        Brand Details
                                    </h2>

                                    <p>
                                        Update the brand name, description and availability.
                                    </p>

                                </div>

                            </div>


                            <div className="rr-brand-form-section-body">

                                <div className="rr-brand-form-grid">

                                    {/* =================================
                                        BRAND CODE
                                       ================================= */}

                                    <Field
                                        label="Brand Code"
                                    >

                                        <div className="rr-brand-code-input">

                                            <i className="bi bi-upc-scan"></i>


                                            <input
                                                type="text"
                                                value={
                                                    brand?.brand_code
                                                    ?? ''
                                                }
                                                readOnly
                                                tabIndex="-1"
                                            />

                                        </div>


                                        <div className="rr-brand-field-help">

                                            <i className="bi bi-lock"></i>

                                            Brand code is generated automatically and cannot be changed.

                                        </div>

                                    </Field>


                                    {/* =================================
                                        BRAND NAME
                                       ================================= */}

                                    <Field
                                        label="Brand Name"
                                        required
                                        error={
                                            errors.brand_name
                                        }
                                    >

                                        <input
                                            ref={
                                                brandNameRef
                                            }
                                            type="text"
                                            name="brand_name"
                                            value={
                                                data.brand_name
                                            }
                                            placeholder="Enter brand name"
                                            autoComplete="off"
                                            onChange={
                                                event =>
                                                    updateField(
                                                        'brand_name',
                                                        event.target.value
                                                    )
                                            }
                                        />

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
                                            placeholder="Optional notes about this brand..."
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
                            USAGE INFORMATION
                           ================================================= */}

                        <section className="rr-brand-edit-usage">

                            <div className="rr-brand-edit-usage-icon">

                                <i className="bi bi-box-seam"></i>

                            </div>


                            <div className="rr-brand-edit-usage-content">

                                <span>
                                    Product Usage
                                </span>

                                <strong>

                                    {
                                        Number(
                                            brand?.products_count
                                            ?? 0
                                        )
                                    }

                                    {
                                        Number(
                                            brand?.products_count
                                            ?? 0
                                        ) === 1
                                            ? ' product'
                                            : ' products'
                                    }

                                </strong>

                                <small>

                                    {
                                        Number(
                                            brand?.products_count
                                            ?? 0
                                        ) > 0
                                            ? 'This brand is currently assigned to products in this branch.'
                                            : 'This brand is not currently assigned to any products.'
                                    }

                                </small>

                            </div>


                            {
                                Number(
                                    brand?.products_count
                                    ?? 0
                                ) > 0 && (

                                    <button
                                        type="button"
                                        className="rr-brand-view-products-btn"
                                        onClick={
                                            openProducts
                                        }
                                    >

                                        View Products

                                        <i className="bi bi-arrow-right"></i>

                                    </button>

                                )
                            }

                        </section>

                    </div>


                    {/* =================================================
                        FOOTER
                       ================================================= */}

                    <div className="rr-brand-form-footer">

                        <div className="rr-brand-form-footer-info">

                            <i className="bi bi-shield-check"></i>

                            Company and branch ownership cannot be changed from this form.

                        </div>


                        <div className="rr-brand-form-footer-actions">

                            <button
                                type="button"
                                className="rr-brand-form-secondary-btn"
                                onClick={
                                    goBack
                                }
                            >

                                Cancel

                            </button>


                            <button
                                type="submit"
                                className="rr-brand-form-save-btn"
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
                                        : 'Update Brand'
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
                `rr-brand-form-field ${className} ${error
                    ? 'has-error'
                    : ''
                }`
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

                    <div className="rr-brand-form-field-error">

                        <i className="bi bi-exclamation-circle"></i>

                        <span>
                            {
                                error
                            }
                        </span>

                    </div>

                )
            }

        </div>
    );
}