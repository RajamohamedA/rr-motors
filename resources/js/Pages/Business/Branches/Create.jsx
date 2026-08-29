import {
    Head,
    Link,
    useForm,
} from '@inertiajs/react';

import BusinessLayout
    from '../../../Layouts/BusinessLayout';

import PageShell
    from '../../../Components/Business/PageShell';


export default function BranchCreate({
    company,
}) {

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
        reset,
    } = useForm({

        store_name:
            '',

        contact_person:
            '',

        phone:
            '',

        email:
            '',

        address:
            '',

        city:
            '',

        state:
            '',

        country:
            'India',

        pincode:
            '',
    });


    /*
    |--------------------------------------------------------------------------
    | Submit
    |--------------------------------------------------------------------------
    */

    const submit =
        (event) => {

            event.preventDefault();


            post(
                '/company/branches',
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

            <Head title="Add Branch" />


            <PageShell

                eyebrow="Company Management"

                title="Add Branch"

                description="Create a new company branch. New branches remain pending until Super Admin approval."

                icon="bi-building-add"

                breadcrumb={[
                    {
                        label:
                            'Company',
                        href:
                            '/company/dashboard',
                    },

                    {
                        label:
                            'Branches',
                        href:
                            '/company/branches',
                    },

                    {
                        label:
                            'Add Branch',
                    },
                ]}

                actions={

                    <Link
                        href="/company/branches"
                        className="rr-page-secondary-btn"
                    >

                        <i className="bi bi-arrow-left"></i>

                        Back to Branches

                    </Link>
                }
            >

                {/* =====================================================
                    COMPANY CONTEXT
                   ===================================================== */}

                <section className="rr-branches-context-card">

                    <div className="rr-branches-context-main">

                        <div className="rr-branches-context-logo">

                            {
                                getInitials(
                                    company?.company_name
                                )
                            }

                        </div>


                        <div className="rr-branches-context-info">

                            <span>
                                Company
                            </span>

                            <strong>

                                {
                                    company?.company_name
                                    ?? 'Company'
                                }

                            </strong>


                            {
                                company?.company_code && (

                                    <small>

                                        <i className="bi bi-upc-scan"></i>

                                        {
                                            company.company_code
                                        }

                                    </small>

                                )
                            }

                        </div>

                    </div>


                    <div className="rr-branches-context-note">

                        <i className="bi bi-shield-lock"></i>

                        <div>

                            <strong>
                                Pending approval
                            </strong>

                            <span>
                                Super Admin must activate this branch before it can be used.
                            </span>

                        </div>

                    </div>

                </section>


                {/* =====================================================
                    FORM PANEL
                   ===================================================== */}

                <section className="rr-page-panel">

                    <div className="rr-page-panel-header">

                        <div className="rr-page-panel-title">

                            <h2>
                                Branch Information
                            </h2>

                            <p>
                                Enter the branch contact and location details.
                            </p>

                        </div>

                    </div>


                    <form
                        onSubmit={
                            submit
                        }
                        className="rr-branch-form"
                    >

                        {/* =================================================
                            BASIC DETAILS
                           ================================================= */}

                        <div className="rr-branch-form-section">

                            <div className="rr-branch-form-section-heading">

                                <div className="rr-branch-form-section-icon">

                                    <i className="bi bi-shop-window"></i>

                                </div>

                                <div>

                                    <strong>
                                        Basic Details
                                    </strong>

                                    <span>
                                        Main branch identification and contact person.
                                    </span>

                                </div>

                            </div>


                            <div className="rr-branch-form-grid">

                                <FormField
                                    label="Branch Name"
                                    required
                                    error={
                                        errors.store_name
                                    }
                                >

                                    <input
                                        type="text"
                                        value={
                                            data.store_name
                                        }
                                        onChange={
                                            event =>
                                                setData(
                                                    'store_name',
                                                    event.target.value
                                                )
                                        }
                                        placeholder="Enter branch name"
                                        autoFocus
                                    />

                                </FormField>


                                <FormField
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
                                        placeholder="Enter contact person"
                                    />

                                </FormField>


                                <FormField
                                    label="Phone"
                                    error={
                                        errors.phone
                                    }
                                >

                                    <div className="rr-branch-input-icon-wrap">

                                        <i className="bi bi-telephone"></i>

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
                                            placeholder="Enter phone number"
                                        />

                                    </div>

                                </FormField>


                                <FormField
                                    label="Email"
                                    error={
                                        errors.email
                                    }
                                >

                                    <div className="rr-branch-input-icon-wrap">

                                        <i className="bi bi-envelope"></i>

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
                                            placeholder="Enter email address"
                                        />

                                    </div>

                                </FormField>

                            </div>

                        </div>


                        {/* =================================================
                            ADDRESS
                           ================================================= */}

                        <div className="rr-branch-form-section">

                            <div className="rr-branch-form-section-heading">

                                <div className="rr-branch-form-section-icon">

                                    <i className="bi bi-geo-alt"></i>

                                </div>

                                <div>

                                    <strong>
                                        Address
                                    </strong>

                                    <span>
                                        Branch location and postal information.
                                    </span>

                                </div>

                            </div>


                            <div className="rr-branch-form-grid">

                                <FormField
                                    label="Address"
                                    error={
                                        errors.address
                                    }
                                    wide
                                >

                                    <textarea
                                        rows="3"
                                        value={
                                            data.address
                                        }
                                        onChange={
                                            event =>
                                                setData(
                                                    'address',
                                                    event.target.value
                                                )
                                        }
                                        placeholder="Enter branch address"
                                    />

                                </FormField>


                                <FormField
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
                                        placeholder="Enter city"
                                    />

                                </FormField>


                                <FormField
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
                                        placeholder="Enter state"
                                    />

                                </FormField>


                                <FormField
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
                                        placeholder="Enter country"
                                    />

                                </FormField>


                                <FormField
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
                                        placeholder="Enter pincode"
                                    />

                                </FormField>

                            </div>

                        </div>


                        {/* =================================================
                            SYSTEM INFO
                           ================================================= */}

                        <div className="rr-branch-form-system-note">

                            <div>

                                <i className="bi bi-info-circle"></i>

                            </div>

                            <div>

                                <strong>
                                    System controlled fields
                                </strong>

                                <p>
                                    Company, branch code and approval status are automatically managed by the system.
                                </p>

                            </div>

                        </div>


                        {/* =================================================
                            ACTIONS
                           ================================================= */}

                        <div className="rr-branch-form-actions">

                            <Link
                                href="/company/branches"
                                className="rr-page-secondary-btn"
                            >

                                <i className="bi bi-x-lg"></i>

                                Cancel

                            </Link>


                            <button
                                type="button"
                                className="rr-page-secondary-btn"
                                disabled={
                                    processing
                                }
                                onClick={
                                    () =>
                                        reset()
                                }
                            >

                                <i className="bi bi-arrow-counterclockwise"></i>

                                Reset

                            </button>


                            <button
                                type="submit"
                                className="rr-page-primary-btn"
                                disabled={
                                    processing
                                }
                            >

                                {
                                    processing
                                        ? (
                                            <>
                                                <span className="spinner-border spinner-border-sm"></span>
                                                Creating...
                                            </>
                                        )
                                        : (
                                            <>
                                                <i className="bi bi-check2-circle"></i>
                                                Create Branch
                                            </>
                                        )
                                }

                            </button>

                        </div>

                    </form>

                </section>

            </PageShell>

        </BusinessLayout>
    );
}


/*
|--------------------------------------------------------------------------
| Form Field
|--------------------------------------------------------------------------
*/

function FormField({
    label,
    required = false,
    error = null,
    wide = false,
    children,
}) {

    return (

        <div
            className={
                `rr-branch-form-field ${wide
                    ? 'rr-branch-form-field-wide'
                    : ''
                }`
            }
        >

            <label>

                {label}

                {
                    required && (

                        <span className="rr-branch-required">
                            *
                        </span>

                    )
                }

            </label>


            <div
                className={
                    `rr-branch-form-control ${error
                        ? 'has-error'
                        : ''
                    }`
                }
            >

                {children}

            </div>


            {
                error && (

                    <div className="rr-branch-form-error">

                        <i className="bi bi-exclamation-circle"></i>

                        {error}

                    </div>

                )
            }

        </div>
    );
}


/*
|--------------------------------------------------------------------------
| Initials
|--------------------------------------------------------------------------
*/

function getInitials(
    name
) {

    if (
        !name
    ) {

        return 'CO';
    }


    const parts =
        String(
            name
        )
            .trim()
            .split(
                /\s+/
            )
            .filter(
                Boolean
            );


    if (
        parts.length === 0
    ) {

        return 'CO';
    }


    if (
        parts.length === 1
    ) {

        return parts[0]
            .substring(
                0,
                2
            )
            .toUpperCase();
    }


    return (
        parts[0][0]
        +
        parts[
        parts.length - 1
        ][0]
    ).toUpperCase();
}