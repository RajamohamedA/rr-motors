import {
    Head,
    Link,
    useForm,
} from '@inertiajs/react';

import BusinessLayout
    from '../../../Layouts/BusinessLayout';

import PageShell
    from '../../../Components/Business/PageShell';


export default function BranchEdit({
    company,
    branch,
}) {

    const {
        data,
        setData,
        put,
        processing,
        errors,
    } = useForm({

        store_name:
            branch?.store_name
            ?? '',

        contact_person:
            branch?.contact_person
            ?? '',

        phone:
            branch?.phone
            ?? '',

        email:
            branch?.email
            ?? '',

        address:
            branch?.address
            ?? '',

        city:
            branch?.city
            ?? '',

        state:
            branch?.state
            ?? '',

        country:
            branch?.country
            ?? 'India',

        pincode:
            branch?.pincode
            ?? '',
    });


    /*
    |--------------------------------------------------------------------------
    | Submit
    |--------------------------------------------------------------------------
    */

    const submit =
        (event) => {

            event.preventDefault();


            put(
                `/company/branches/${branch.id}`,
                {
                    preserveScroll:
                        true,
                }
            );
        };


    return (

        <BusinessLayout>

            <Head
                title={
                    `Edit ${branch?.store_name
                    ?? 'Branch'
                    }`
                }
            />


            <PageShell

                eyebrow="Company Management"

                title="Edit Branch"

                description={
                    `Update branch details for ${branch?.store_name
                    ?? 'this branch'
                    }.`
                }

                icon="bi-pencil-square"

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
                            branch?.store_name
                            ?? 'Branch',
                        href:
                            `/company/branches/${branch.id}`,
                    },

                    {
                        label:
                            'Edit',
                    },
                ]}

                actions={

                    <>

                        <Link
                            href={
                                `/company/branches/${branch.id}`
                            }
                            className="rr-page-secondary-btn"
                        >

                            <i className="bi bi-eye"></i>

                            View

                        </Link>


                        <Link
                            href="/company/branches"
                            className="rr-page-secondary-btn"
                        >

                            <i className="bi bi-arrow-left"></i>

                            Back

                        </Link>

                    </>

                }
            >

                {/* =====================================================
                    BRANCH CONTEXT
                   ===================================================== */}

                <section className="rr-branch-edit-context">

                    <div className="rr-branch-edit-context-main">

                        <div
                            className={
                                `rr-branch-edit-context-icon ${branch?.status
                                    ? 'active'
                                    : 'pending'
                                }`
                            }
                        >

                            <i className="bi bi-shop-window"></i>

                        </div>


                        <div>

                            <span>
                                Branch
                            </span>

                            <strong>

                                {
                                    branch?.store_name
                                    ?? 'Branch'
                                }

                            </strong>

                            <small>

                                {
                                    branch?.store_code
                                    ?? '—'
                                }

                            </small>

                        </div>

                    </div>


                    <div className="rr-branch-edit-context-status">

                        <span
                            className={
                                branch?.status
                                    ? 'active'
                                    : 'pending'
                            }
                        >

                            <i
                                className={
                                    branch?.status
                                        ? 'bi bi-check-circle-fill'
                                        : 'bi bi-clock-fill'
                                }
                            ></i>

                            {
                                branch?.status
                                    ? 'Active'
                                    : 'Pending Approval'
                            }

                        </span>

                    </div>

                </section>


                {/* =====================================================
                    READ ONLY SYSTEM INFO
                   ===================================================== */}

                <section className="rr-branch-edit-system-grid">

                    <ReadOnlyItem
                        label="Company"
                        value={
                            company?.company_name
                        }
                        icon="bi-building"
                    />

                    <ReadOnlyItem
                        label="Company Code"
                        value={
                            company?.company_code
                        }
                        icon="bi-upc"
                    />

                    <ReadOnlyItem
                        label="Branch Number"
                        value={
                            branch?.branch_number
                                ? `#${String(
                                    branch.branch_number
                                ).padStart(
                                    3,
                                    '0'
                                )}`
                                : '—'
                        }
                        icon="bi-list-ol"
                    />

                    <ReadOnlyItem
                        label="Branch Code"
                        value={
                            branch?.store_code
                        }
                        icon="bi-upc-scan"
                    />

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
                                Update contact and location details.
                            </p>

                        </div>

                    </div>


                    <form
                        className="rr-branch-form"
                        onSubmit={
                            submit
                        }
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
                                        Branch name and primary contact information.
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
                                        Branch location and postal details.
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
                            SYSTEM PROTECTION
                           ================================================= */}

                        <div className="rr-branch-form-system-note">

                            <div>

                                <i className="bi bi-shield-lock"></i>

                            </div>


                            <div>

                                <strong>
                                    Protected system fields
                                </strong>

                                <p>
                                    Company, branch number, branch code and approval
                                    status cannot be changed from the Company Portal.
                                    Branch activation is controlled by Super Admin.
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

                                                Saving...
                                            </>
                                        )
                                        : (
                                            <>
                                                <i className="bi bi-check2-circle"></i>

                                                Save Changes
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
| Read Only System Item
|--------------------------------------------------------------------------
*/

function ReadOnlyItem({
    label,
    value,
    icon,
}) {

    return (

        <div className="rr-branch-edit-system-item">

            <div className="rr-branch-edit-system-icon">

                <i
                    className={
                        `bi ${icon}`
                    }
                ></i>

            </div>


            <div>

                <span>
                    {label}
                </span>

                <strong>
                    {
                        value
                        || '—'
                    }
                </strong>

            </div>

        </div>
    );
}