import {
    Head,
    router,
    useForm,
    usePage,
} from '@inertiajs/react';

import {
    useRef,
} from 'react';

import BusinessLayout
    from '../../Layouts/BusinessLayout';

import MasterNavigation
    from '../../Components/Business/MasterNavigation';

import '../../../css/business/categories.css';


export default function Edit({
    category,
}) {

    /*
    |--------------------------------------------------------------------------
    | Shared Props
    |--------------------------------------------------------------------------
    */

    const {
        business,
        activeCompany,
        activeStore,
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

        name:
            category?.name
            ?? '',

        description:
            category?.description
            ?? '',

        status:
            Boolean(
                category?.status
                ?? true
            ),
    });


    /*
    |--------------------------------------------------------------------------
    | References
    |--------------------------------------------------------------------------
    */

    const pageTopRef =
        useRef(
            null
        );


    /*
    |--------------------------------------------------------------------------
    | Active Context
    |--------------------------------------------------------------------------
    */

    const companyName =
        business?.company_name
        ??
        activeCompany?.company_name
        ??
        activeCompany?.name
        ??
        'Active Company';


    const storeName =
        business?.store_name
        ??
        activeStore?.store_name
        ??
        'Active Branch';


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

            const errorKeys =
                Object.keys(
                    validationErrors
                    ?? {}
                );


            if (
                errorKeys.length === 0
            ) {

                scrollToElement(
                    pageTopRef.current
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


                        window.setTimeout(
                            () => {

                                element.focus?.();

                            },
                            350
                        );


                        return;
                    }


                    scrollToElement(
                        pageTopRef.current
                    );
                }
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Notification Scroll
    |--------------------------------------------------------------------------
    */

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
                '/categories'
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
                `/categories/${category.id}`,
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


    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */

    return (

        <BusinessLayout>

            <Head
                title="Edit Category"
            />


            <div
                className="rr-category-form-page"
                ref={
                    pageTopRef
                }
            >

                <MasterNavigation
                    active="categories"
                    title="Categories"
                    subtitle="Branch product category management"
                />


                {/* =====================================================
                    TITLE BAR
                   ===================================================== */}

                <section className="rr-category-form-titlebar">

                    <div className="rr-category-form-title-main">

                        <button
                            type="button"
                            className="rr-category-form-back"
                            onClick={
                                goBack
                            }
                            title="Back"
                        >

                            <i className="bi bi-arrow-left"></i>

                        </button>


                        <div className="rr-category-form-icon">

                            <i className="bi bi-grid"></i>

                        </div>


                        <div>

                            <span>
                                Product Master
                            </span>


                            <h1>
                                Edit Category
                            </h1>


                            <p>
                                Update this category for the active branch.
                            </p>

                        </div>

                    </div>


                    <div className="rr-category-form-context">

                        <div>

                            <span>
                                Company
                            </span>


                            <strong>
                                {
                                    companyName
                                }
                            </strong>

                        </div>


                        <div>

                            <span>
                                Branch
                            </span>


                            <strong>
                                {
                                    storeName
                                }
                            </strong>

                        </div>

                    </div>

                </section>


                {/* =====================================================
                    BRANCH SCOPE
                   ===================================================== */}

                <section className="rr-category-branch-context">

                    <div>

                        <i className="bi bi-shop"></i>


                        <div>

                            <span>
                                Category Scope
                            </span>


                            <strong>
                                {
                                    storeName
                                }
                            </strong>

                        </div>

                    </div>


                    <p>
                        This category belongs only to the active branch.
                        Changes here do not affect category masters in other branches.
                    </p>

                </section>


                {/* =====================================================
                    VALIDATION SUMMARY
                   ===================================================== */}

                {
                    Object.keys(
                        errors
                        ?? {}
                    ).length > 0 && (

                        <section className="rr-category-validation-summary">

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


                {/* =====================================================
                    FORM
                   ===================================================== */}

                <form
                    onSubmit={
                        submit
                    }
                    className="rr-category-form-window"
                >

                    {/* =================================================
                        TOOLBAR
                       ================================================= */}

                    <div className="rr-category-form-toolbar">

                        <div>

                            <span className="rr-category-form-toolbar-status">

                                <i className="bi bi-circle-fill"></i>

                                Edit Category

                            </span>

                        </div>


                        <div className="rr-category-form-toolbar-actions">

                            <button
                                type="button"
                                className="rr-category-secondary-btn"
                                onClick={
                                    goBack
                                }
                            >

                                <i className="bi bi-x-lg"></i>

                                Cancel

                            </button>


                            <button
                                type="submit"
                                className="rr-category-save-btn"
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
                                        : 'Update Category'
                                }

                            </button>

                        </div>

                    </div>


                    {/* =================================================
                        BODY
                       ================================================= */}

                    <div className="rr-category-form-body">

                        <section className="rr-category-form-section">

                            <div className="rr-category-form-section-header">

                                <i className="bi bi-grid"></i>


                                <div>

                                    <h2>
                                        Category Details
                                    </h2>


                                    <p>
                                        Update the category name, description and status.
                                    </p>

                                </div>

                            </div>


                            <div className="rr-category-form-section-body">

                                <div className="rr-category-form-grid">

                                    {/* =========================================
                                        CATEGORY NAME
                                       ========================================= */}

                                    <Field
                                        label="Category Name"
                                        required
                                        error={
                                            errors.name
                                        }
                                    >

                                        <input
                                            type="text"
                                            name="name"
                                            autoFocus
                                            maxLength="100"
                                            value={
                                                data.name
                                            }
                                            placeholder="Example: Engine Spares"
                                            onChange={
                                                event =>
                                                    setData(
                                                        'name',
                                                        event.target.value
                                                    )
                                            }
                                        />

                                    </Field>


                                    {/* =========================================
                                        STATUS
                                       ========================================= */}

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


                                    {/* =========================================
                                        DESCRIPTION
                                       ========================================= */}

                                    <Field
                                        className="span-2"
                                        label="Description"
                                        error={
                                            errors.description
                                        }
                                    >

                                        <textarea
                                            name="description"
                                            rows="4"
                                            maxLength="2000"
                                            value={
                                                data.description
                                            }
                                            placeholder="Optional description about this category..."
                                            onChange={
                                                event =>
                                                    setData(
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
                            CATEGORY INFORMATION
                           ================================================= */}

                        <section className="rr-category-form-section">

                            <div className="rr-category-form-section-header">

                                <i className="bi bi-info-circle"></i>


                                <div>

                                    <h2>
                                        Category Information
                                    </h2>


                                    <p>
                                        Code, product usage and branch ownership.
                                    </p>

                                </div>

                            </div>


                            <div className="rr-category-form-section-body">

                                <div className="rr-category-info-grid">

                                    <InfoBox
                                        icon="bi-upc-scan"
                                        label="Category Code"
                                        value={
                                            category?.code
                                            ?? '—'
                                        }
                                        description="Category code is generated automatically and cannot be changed."
                                    />


                                    <InfoBox
                                        icon="bi-box-seam"
                                        label="Products"
                                        value={
                                            `${Number(
                                                category?.products_count
                                                ?? 0
                                            )} ${Number(
                                                category?.products_count
                                                ?? 0
                                            ) === 1
                                                ? 'Product'
                                                : 'Products'
                                            }`
                                        }
                                        description="Products currently assigned to this category."
                                    />


                                    <InfoBox
                                        icon="bi-shop"
                                        label="Branch"
                                        value={
                                            storeName
                                        }
                                        description="This category is available only in the active branch."
                                    />

                                </div>

                            </div>

                        </section>

                    </div>


                    {/* =================================================
                        FOOTER
                       ================================================= */}

                    <div className="rr-category-form-footer">

                        <div>

                            <i className="bi bi-info-circle"></i>

                            Changes apply only to {
                                storeName
                            }.

                        </div>


                        <div>

                            <button
                                type="button"
                                className="rr-category-secondary-btn"
                                onClick={
                                    goBack
                                }
                            >

                                Cancel

                            </button>


                            <button
                                type="submit"
                                className="rr-category-save-btn"
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
                                        : 'Update Category'
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
                `rr-category-field ${className}`
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

                    <div className="rr-category-field-error">

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


/*
|--------------------------------------------------------------------------
| Information Box
|--------------------------------------------------------------------------
*/

function InfoBox({
    icon,
    label,
    value,
    description,
}) {

    return (

        <div className="rr-category-info-box">

            <div className="rr-category-info-icon">

                <i
                    className={
                        `bi ${icon}`
                    }
                ></i>

            </div>


            <div>

                <span>
                    {
                        label
                    }
                </span>


                <strong>
                    {
                        value
                    }
                </strong>


                <p>
                    {
                        description
                    }
                </p>

            </div>

        </div>
    );
}