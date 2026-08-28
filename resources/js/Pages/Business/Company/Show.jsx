import {
    Head,
    useForm,
} from '@inertiajs/react';

import BusinessLayout
    from '../../../Layouts/BusinessLayout';

import '../../../../css/business/company.css';


export default function Show({
    company,
}) {

    /*
    |--------------------------------------------------------------------------
    | Form
    |--------------------------------------------------------------------------
    */

    const form =
        useForm({

            company_name:
                company?.company_name
                ?? '',

            owner_name:
                company?.owner_name
                ?? '',

            gst_number:
                company?.gst_number
                ?? '',

            pan_number:
                company?.pan_number
                ?? '',

            email:
                company?.email
                ?? '',

            phone:
                company?.phone
                ?? '',

            website:
                company?.website
                ?? '',

            address:
                company?.address
                ?? '',

            city:
                company?.city
                ?? '',

            state:
                company?.state
                ?? '',

            country:
                company?.country
                ?? '',

            pincode:
                company?.pincode
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


            if (
                form.processing
            ) {

                return;
            }


            form.put(
                '/company/profile',
                {
                    preserveScroll:
                        true,
                }
            );
        };


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

            form.setData(
                field,
                value
            );


            if (
                form.errors[
                field
                ]
            ) {

                form.clearErrors(
                    field
                );
            }
        };


    return (

        <BusinessLayout
            title="Company Profile"
            subtitle="Manage your business information"
            breadcrumbs={[
                {
                    label:
                        'Dashboard',

                    href:
                        '/business/dashboard',
                },

                {
                    label:
                        'Company Profile',
                },
            ]}
        >

            <Head title="Company Profile - RR ERP" />


            <div className="rr-company-profile-page">

                {/* =====================================================
                    HERO
                   ===================================================== */}

                <section className="rr-company-profile-hero">

                    <div className="rr-company-profile-hero-main">

                        <div className="rr-company-profile-logo">

                            {
                                company?.logo
                                    ? (

                                        <img
                                            src={
                                                company.logo
                                            }
                                            alt={
                                                company.company_name
                                            }
                                        />

                                    )
                                    : (

                                        <span>
                                            {
                                                getInitials(
                                                    company?.company_name
                                                )
                                            }
                                        </span>

                                    )
                            }

                        </div>


                        <div className="rr-company-profile-hero-content">

                            <div className="rr-company-profile-eyebrow">
                                COMPANY PROFILE
                            </div>


                            <h1>
                                {
                                    company?.company_name
                                    ?? 'Company'
                                }
                            </h1>


                            <p>
                                Manage the official business information
                                used throughout RR ERP.
                            </p>


                            <div className="rr-company-profile-meta">

                                <MetaBadge
                                    icon="bi-hash"
                                    label="Company Code"
                                    value={
                                        company?.company_code
                                        ?? '—'
                                    }
                                />


                                <MetaBadge
                                    icon={
                                        company?.status
                                            ? 'bi-check-circle-fill'
                                            : 'bi-x-circle-fill'
                                    }
                                    label="Status"
                                    value={
                                        company?.status
                                            ? 'Active'
                                            : 'Inactive'
                                    }
                                    status={
                                        company?.status
                                    }
                                />


                                <MetaBadge
                                    icon="bi-calendar3"
                                    label="Created"
                                    value={
                                        formatDate(
                                            company?.created_at
                                        )
                                    }
                                />

                            </div>

                        </div>

                    </div>


                    <div className="rr-company-profile-security">

                        <div className="rr-company-profile-security-icon">

                            <i className="bi bi-shield-check"></i>

                        </div>


                        <div>

                            <strong>
                                Protected Company Settings
                            </strong>

                            <span>
                                Company code, activation and subscription
                                controls are managed by the RR ERP Super
                                Administrator.
                            </span>

                        </div>

                    </div>

                </section>


                {/* =====================================================
                    SUMMARY
                   ===================================================== */}

                <div className="rr-company-profile-summary">

                    <SummaryCard
                        icon="bi-person"
                        label="Owner / Contact"
                        value={
                            company?.owner_name
                            ?? 'Not provided'
                        }
                    />


                    <SummaryCard
                        icon="bi-telephone"
                        label="Phone"
                        value={
                            company?.phone
                            ?? 'Not provided'
                        }
                    />


                    <SummaryCard
                        icon="bi-envelope"
                        label="Email"
                        value={
                            company?.email
                            ?? 'Not provided'
                        }
                    />


                    <SummaryCard
                        icon="bi-geo-alt"
                        label="Location"
                        value={
                            formatLocation(
                                company
                            )
                        }
                    />

                </div>


                {/* =====================================================
                    FORM
                   ===================================================== */}

                <form
                    onSubmit={
                        submit
                    }
                    className="rr-company-profile-form"
                >

                    {/* =================================================
                        BUSINESS INFORMATION
                       ================================================= */}

                    <section className="rr-company-profile-section">

                        <SectionHeader
                            icon="bi-buildings"
                            title="Business Information"
                            description="Core company and registration details"
                        />


                        <div className="rr-company-profile-section-body">

                            <div className="rr-company-profile-grid">

                                <FormField
                                    label="Company Name"
                                    required
                                    error={
                                        form.errors
                                            .company_name
                                    }
                                    full
                                >

                                    <InputWrapper
                                        icon="bi-buildings"
                                    >

                                        <input
                                            type="text"
                                            value={
                                                form.data
                                                    .company_name
                                            }
                                            onChange={
                                                (
                                                    event
                                                ) =>
                                                    updateField(
                                                        'company_name',
                                                        event.target.value
                                                    )
                                            }
                                            placeholder="Company name"
                                        />

                                    </InputWrapper>

                                </FormField>


                                <FormField
                                    label="Owner / Contact Name"
                                    required
                                    error={
                                        form.errors
                                            .owner_name
                                    }
                                >

                                    <InputWrapper
                                        icon="bi-person"
                                    >

                                        <input
                                            type="text"
                                            value={
                                                form.data
                                                    .owner_name
                                            }
                                            onChange={
                                                (
                                                    event
                                                ) =>
                                                    updateField(
                                                        'owner_name',
                                                        event.target.value
                                                    )
                                            }
                                            placeholder="Owner or contact person"
                                        />

                                    </InputWrapper>

                                </FormField>


                                <FormField
                                    label="Company Code"
                                    hint="Managed by Super Admin"
                                >

                                    <InputWrapper
                                        icon="bi-hash"
                                        readOnly
                                    >

                                        <input
                                            type="text"
                                            value={
                                                company?.company_code
                                                ?? ''
                                            }
                                            readOnly
                                        />

                                    </InputWrapper>

                                </FormField>


                                <FormField
                                    label="GST Number"
                                    error={
                                        form.errors
                                            .gst_number
                                    }
                                >

                                    <InputWrapper
                                        icon="bi-file-earmark-text"
                                    >

                                        <input
                                            type="text"
                                            value={
                                                form.data
                                                    .gst_number
                                            }
                                            onChange={
                                                (
                                                    event
                                                ) =>
                                                    updateField(
                                                        'gst_number',
                                                        event.target.value
                                                            .toUpperCase()
                                                    )
                                            }
                                            placeholder="GSTIN"
                                        />

                                    </InputWrapper>

                                </FormField>


                                <FormField
                                    label="PAN Number"
                                    error={
                                        form.errors
                                            .pan_number
                                    }
                                >

                                    <InputWrapper
                                        icon="bi-card-text"
                                    >

                                        <input
                                            type="text"
                                            value={
                                                form.data
                                                    .pan_number
                                            }
                                            onChange={
                                                (
                                                    event
                                                ) =>
                                                    updateField(
                                                        'pan_number',
                                                        event.target.value
                                                            .toUpperCase()
                                                    )
                                            }
                                            placeholder="PAN number"
                                        />

                                    </InputWrapper>

                                </FormField>

                            </div>

                        </div>

                    </section>


                    {/* =================================================
                        CONTACT INFORMATION
                       ================================================= */}

                    <section className="rr-company-profile-section">

                        <SectionHeader
                            icon="bi-person-lines-fill"
                            title="Contact Information"
                            description="Business communication details"
                        />


                        <div className="rr-company-profile-section-body">

                            <div className="rr-company-profile-grid">

                                <FormField
                                    label="Email Address"
                                    error={
                                        form.errors
                                            .email
                                    }
                                >

                                    <InputWrapper
                                        icon="bi-envelope"
                                    >

                                        <input
                                            type="email"
                                            value={
                                                form.data
                                                    .email
                                            }
                                            onChange={
                                                (
                                                    event
                                                ) =>
                                                    updateField(
                                                        'email',
                                                        event.target.value
                                                    )
                                            }
                                            placeholder="company@example.com"
                                        />

                                    </InputWrapper>

                                </FormField>


                                <FormField
                                    label="Phone Number"
                                    error={
                                        form.errors
                                            .phone
                                    }
                                >

                                    <InputWrapper
                                        icon="bi-telephone"
                                    >

                                        <input
                                            type="text"
                                            value={
                                                form.data
                                                    .phone
                                            }
                                            onChange={
                                                (
                                                    event
                                                ) =>
                                                    updateField(
                                                        'phone',
                                                        event.target.value
                                                    )
                                            }
                                            placeholder="Business phone"
                                        />

                                    </InputWrapper>

                                </FormField>


                                <FormField
                                    label="Website"
                                    error={
                                        form.errors
                                            .website
                                    }
                                    full
                                >

                                    <InputWrapper
                                        icon="bi-globe2"
                                    >

                                        <input
                                            type="text"
                                            value={
                                                form.data
                                                    .website
                                            }
                                            onChange={
                                                (
                                                    event
                                                ) =>
                                                    updateField(
                                                        'website',
                                                        event.target.value
                                                    )
                                            }
                                            placeholder="https://example.com"
                                        />

                                    </InputWrapper>

                                </FormField>

                            </div>

                        </div>

                    </section>


                    {/* =================================================
                        ADDRESS
                       ================================================= */}

                    <section className="rr-company-profile-section">

                        <SectionHeader
                            icon="bi-geo-alt"
                            title="Business Address"
                            description="Registered or primary business location"
                        />


                        <div className="rr-company-profile-section-body">

                            <div className="rr-company-profile-grid">

                                <FormField
                                    label="Address"
                                    error={
                                        form.errors
                                            .address
                                    }
                                    full
                                >

                                    <InputWrapper
                                        icon="bi-geo-alt"
                                        textarea
                                    >

                                        <textarea
                                            rows="4"
                                            value={
                                                form.data
                                                    .address
                                            }
                                            onChange={
                                                (
                                                    event
                                                ) =>
                                                    updateField(
                                                        'address',
                                                        event.target.value
                                                    )
                                            }
                                            placeholder="Street, building, area..."
                                        ></textarea>

                                    </InputWrapper>

                                </FormField>


                                <FormField
                                    label="City"
                                    error={
                                        form.errors
                                            .city
                                    }
                                >

                                    <InputWrapper
                                        icon="bi-geo"
                                    >

                                        <input
                                            type="text"
                                            value={
                                                form.data
                                                    .city
                                            }
                                            onChange={
                                                (
                                                    event
                                                ) =>
                                                    updateField(
                                                        'city',
                                                        event.target.value
                                                    )
                                            }
                                            placeholder="City"
                                        />

                                    </InputWrapper>

                                </FormField>


                                <FormField
                                    label="State"
                                    error={
                                        form.errors
                                            .state
                                    }
                                >

                                    <InputWrapper
                                        icon="bi-map"
                                    >

                                        <input
                                            type="text"
                                            value={
                                                form.data
                                                    .state
                                            }
                                            onChange={
                                                (
                                                    event
                                                ) =>
                                                    updateField(
                                                        'state',
                                                        event.target.value
                                                    )
                                            }
                                            placeholder="State"
                                        />

                                    </InputWrapper>

                                </FormField>


                                <FormField
                                    label="Country"
                                    error={
                                        form.errors
                                            .country
                                    }
                                >

                                    <InputWrapper
                                        icon="bi-globe"
                                    >

                                        <input
                                            type="text"
                                            value={
                                                form.data
                                                    .country
                                            }
                                            onChange={
                                                (
                                                    event
                                                ) =>
                                                    updateField(
                                                        'country',
                                                        event.target.value
                                                    )
                                            }
                                            placeholder="Country"
                                        />

                                    </InputWrapper>

                                </FormField>


                                <FormField
                                    label="Pincode"
                                    error={
                                        form.errors
                                            .pincode
                                    }
                                >

                                    <InputWrapper
                                        icon="bi-mailbox"
                                    >

                                        <input
                                            type="text"
                                            value={
                                                form.data
                                                    .pincode
                                            }
                                            onChange={
                                                (
                                                    event
                                                ) =>
                                                    updateField(
                                                        'pincode',
                                                        event.target.value
                                                    )
                                            }
                                            placeholder="Pincode"
                                        />

                                    </InputWrapper>

                                </FormField>

                            </div>

                        </div>

                    </section>


                    {/* =================================================
                        FORM ACTIONS
                       ================================================= */}

                    <div className="rr-company-profile-actions">

                        <div>

                            {
                                form.isDirty && (

                                    <span className="rr-company-profile-unsaved">

                                        <i className="bi bi-circle-fill"></i>

                                        Unsaved changes

                                    </span>

                                )
                            }

                        </div>


                        <div className="rr-company-profile-action-buttons">

                            <button
                                type="button"
                                className="rr-company-profile-reset-btn"
                                disabled={
                                    form.processing ||
                                    !form.isDirty
                                }
                                onClick={
                                    () =>
                                        form.reset()
                                }
                            >

                                <i className="bi bi-arrow-counterclockwise"></i>

                                Reset

                            </button>


                            <button
                                type="submit"
                                className="rr-company-profile-save-btn"
                                disabled={
                                    form.processing ||
                                    !form.isDirty
                                }
                            >

                                {
                                    form.processing
                                        ? (
                                            <>
                                                <span className="spinner-border spinner-border-sm"></span>

                                                Saving...
                                            </>
                                        )
                                        : (
                                            <>
                                                <i className="bi bi-check-lg"></i>

                                                Save Company Profile
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
| Section Header
|--------------------------------------------------------------------------
*/

function SectionHeader({
    icon,
    title,
    description,
}) {

    return (

        <div className="rr-company-profile-section-header">

            <div className="rr-company-profile-section-icon">

                <i className={`bi ${icon}`}></i>

            </div>


            <div>

                <h2>
                    {title}
                </h2>

                <p>
                    {description}
                </p>

            </div>

        </div>
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
    hint = null,
    error = null,
    full = false,
    children,
}) {

    return (

        <div
            className={
                `rr-company-profile-field ${full
                    ? 'full'
                    : ''
                }`
            }
        >

            <div className="rr-company-profile-label-row">

                <label>

                    {label}

                    {
                        required && (
                            <span>*</span>
                        )
                    }

                </label>


                {
                    hint && (

                        <small>
                            {hint}
                        </small>

                    )
                }

            </div>


            {children}


            {
                error && (

                    <div className="rr-company-profile-error">

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
| Input Wrapper
|--------------------------------------------------------------------------
*/

function InputWrapper({
    icon,
    readOnly = false,
    textarea = false,
    children,
}) {

    return (

        <div
            className={
                `rr-company-profile-input-wrapper ${readOnly
                    ? 'readonly'
                    : ''
                } ${textarea
                    ? 'textarea'
                    : ''
                }`
            }
        >

            <i className={`bi ${icon}`}></i>

            {children}

        </div>
    );
}


/*
|--------------------------------------------------------------------------
| Meta Badge
|--------------------------------------------------------------------------
*/

function MetaBadge({
    icon,
    label,
    value,
    status = null,
}) {

    return (

        <div
            className={
                `rr-company-profile-meta-badge ${status === true
                    ? 'active'
                    : status === false
                        ? 'inactive'
                        : ''
                }`
            }
        >

            <i className={`bi ${icon}`}></i>


            <div>

                <span>
                    {label}
                </span>

                <strong>
                    {value}
                </strong>

            </div>

        </div>
    );
}


/*
|--------------------------------------------------------------------------
| Summary
|--------------------------------------------------------------------------
*/

function SummaryCard({
    icon,
    label,
    value,
}) {

    return (

        <div className="rr-company-profile-summary-card">

            <div className="rr-company-profile-summary-icon">

                <i className={`bi ${icon}`}></i>

            </div>


            <div>

                <span>
                    {label}
                </span>

                <strong>
                    {value}
                </strong>

            </div>

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


/*
|--------------------------------------------------------------------------
| Date
|--------------------------------------------------------------------------
*/

function formatDate(
    value
) {

    if (
        !value
    ) {
        return '—';
    }


    return new Intl.DateTimeFormat(
        'en-IN',
        {
            day:
                '2-digit',

            month:
                'short',

            year:
                'numeric',
        }
    ).format(
        new Date(
            value
        )
    );
}


/*
|--------------------------------------------------------------------------
| Location
|--------------------------------------------------------------------------
*/

function formatLocation(
    company
) {

    const parts =
        [
            company?.city,
            company?.state,
        ].filter(
            Boolean
        );


    return parts.length
        ? parts.join(', ')
        : 'Not provided';
}