import { Head, Link, useForm } from '@inertiajs/react';
import {
    useMemo,
    useId,
    Children,
    cloneElement,
    isValidElement,
    useState,
} from 'react';
import '../../../css/register-business.css';
export default function RegisterBusiness() {
    /*
    |--------------------------------------------------------------------------
    | Step
    |--------------------------------------------------------------------------
    */
    const [step, setStep] = useState(1);
    /*
    |--------------------------------------------------------------------------
    | Form
    |--------------------------------------------------------------------------
    */
    const { data, setData, post, processing, errors, clearErrors } = useForm({
        /*
        |--------------------------------------------------------------------------
        | Administrator
        |--------------------------------------------------------------------------
        */
        name: '',
        email: '',
        mobile: '',
        password: '',
        password_confirmation: '',
        /*
        |--------------------------------------------------------------------------
        | Company
        |--------------------------------------------------------------------------
        */
        company_name: '',
        gst_number: '',
        pan_number: '',
        company_phone: '',
        company_email: '',
        /*
        |--------------------------------------------------------------------------
        | Main Branch
        |--------------------------------------------------------------------------
        */
        store_name: '',
        contact_person: '',
        store_phone: '',
        store_email: '',
        address: '',
        city: '',
        state: 'Tamil Nadu',
        country: 'India',
        pincode: '',
        /*
        |--------------------------------------------------------------------------
        | Agreement
        |--------------------------------------------------------------------------
        */
        terms: false,
    });
    /*
    |--------------------------------------------------------------------------
    | Step Configuration
    |--------------------------------------------------------------------------
    */
    const steps = [
        {
            id: 1,
            label: 'Account',
            title: 'Your Account',
            description: 'Create the administrator account for your business.',
            icon: 'bi-person',
        },
        {
            id: 2,
            label: 'Business',
            title: 'Business Details',
            description: 'Tell us about the company you want to manage.',
            icon: 'bi-buildings',
        },
        {
            id: 3,
            label: 'Main Branch',
            title: 'Main Branch',
            description: 'Set up the first operating location for your business.',
            icon: 'bi-shop',
        },
    ];
    /*
    |--------------------------------------------------------------------------
    | Current Step
    |--------------------------------------------------------------------------
    */
    const currentStep = useMemo(
        () => steps.find((item) => item.id === step) ?? steps[0],
        [step],
    );
    /*
    |--------------------------------------------------------------------------
    | Change Value
    |--------------------------------------------------------------------------
    */
    const updateField = (field, value) => {
        clearErrors(field);
        setData(field, value);
    };
    /*
    |--------------------------------------------------------------------------
    | Step Validation
    |--------------------------------------------------------------------------
    |
    | This is only frontend navigation validation.
    |
    | Laravel remains the final authority.
    |
    */
    const validateStep = () => {
        if (step === 1) {
            return (
                data.name.trim() &&
                data.email.trim() &&
                data.mobile.trim() &&
                data.password.length >= 8 &&
                data.password === data.password_confirmation
            );
        }
        if (step === 2) {
            return Boolean(data.company_name.trim());
        }
        if (step === 3) {
            return (
                data.store_name.trim() &&
                data.address.trim() &&
                data.city.trim() &&
                data.state.trim() &&
                data.country.trim() &&
                data.pincode.trim() &&
                data.terms
            );
        }
        return false;
    };
    /*
    |--------------------------------------------------------------------------
    | Next
    |--------------------------------------------------------------------------
    */
    const nextStep = () => {
        if (!validateStep()) {
            return;
        }
        setStep((current) => Math.min(current + 1, 3));
    };
    /*
    |--------------------------------------------------------------------------
    | Previous
    |--------------------------------------------------------------------------
    */
    const previousStep = () => {
        setStep((current) => Math.max(current - 1, 1));
    };
    /*
    |--------------------------------------------------------------------------
    | Submit
    |--------------------------------------------------------------------------
    */
    const submit = (event) => {
        event.preventDefault();
        if (processing) {
            return;
        }
        post('/register-business', {
            preserveScroll: true,
            onError: (serverErrors) => {
                /*
                            |--------------------------------------------------------------------------
                            | Move User To Relevant Step
                            |--------------------------------------------------------------------------
                            */
                const accountFields = [
                    'name',
                    'email',
                    'mobile',
                    'password',
                    'password_confirmation',
                ];
                const companyFields = [
                    'company_name',
                    'gst_number',
                    'pan_number',
                    'company_phone',
                    'company_email',
                ];
                if (accountFields.some((field) => serverErrors[field])) {
                    setStep(1);
                    return;
                }
                if (companyFields.some((field) => serverErrors[field])) {
                    setStep(2);
                    return;
                }
                setStep(3);
            },
        });
    };
    return (
        <>
            <Head title="Register Business - RR ERP" />
            <div className="rr-register-page">
                {/* =====================================================
                    BACKGROUND
                   ===================================================== */}
                <div className="rr-register-glow rr-register-glow-one"></div>
                <div className="rr-register-glow rr-register-glow-two"></div>
                {/* =====================================================
                    HEADER
                   ===================================================== */}
                <header className="rr-register-header">
                    <Link
                        href="/"
                        className="rr-register-brand"
                    >
                        <div className="rr-register-brand-mark">RR</div>
                        <div>
                            <div className="rr-register-brand-name">RR ERP</div>
                            <div className="rr-register-brand-caption">
                                Business Management Platform
                            </div>
                        </div>
                    </Link>
                    <div className="rr-register-header-right">
                        <span>Already registered?</span>
                        <Link
                            href="/login"
                            className="rr-register-login-link"
                        >
                            Sign In
                        </Link>
                    </div>
                </header>
                {/* =====================================================
                    MAIN
                   ===================================================== */}
                <main className="rr-register-main">
                    {/* =================================================
                        LEFT INFORMATION
                       ================================================= */}
                    <aside className="rr-register-info">
                        <div className="rr-register-info-content">
                            <div className="rr-register-badge">
                                <span></span>
                                START YOUR BUSINESS
                            </div>
                            <h1>
                                Set up your business workspace
                                <span>in a few simple steps.</span>
                            </h1>
                            <p>
                                Create your company, main branch and administrator
                                account. RR ERP will prepare the initial business
                                workspace automatically.
                            </p>
                            <div className="rr-register-benefits">
                                <RegistrationBenefit
                                    icon="bi-buildings"
                                    title="Company Workspace"
                                    text="Your business data stays organized under one company."
                                />
                                <RegistrationBenefit
                                    icon="bi-shop"
                                    title="Main Branch"
                                    text="Your first branch is created automatically during registration."
                                />
                                <RegistrationBenefit
                                    icon="bi-shield-check"
                                    title="Company Admin Access"
                                    text="Your account receives Company Admin access automatically."
                                />
                                <RegistrationBenefit
                                    icon="bi-box-seam"
                                    title="Ready for Operations"
                                    text="Start adding products, purchases, sales and inventory after setup."
                                />
                            </div>
                        </div>
                        <div className="rr-register-info-footer">
                            <i className="bi bi-shield-lock"></i>
                            <span>Secure account and business setup</span>
                        </div>
                    </aside>
                    {/* =================================================
                        FORM CARD
                       ================================================= */}
                    <section className="rr-register-card">
                        {/* =============================================
                            PROGRESS
                           ============================================= */}
                        <div className="rr-register-progress">
                            {steps.map((item, index) => {
                                const active = step === item.id;
                                const completed = step > item.id;
                                return (
                                    <div
                                        key={item.id}
                                        className="rr-register-progress-item"
                                    >
                                        <div
                                            className={`rr-register-progress-node ${active ? 'active' : ''
                                                } ${completed ? 'completed' : ''}`}
                                        >
                                            {completed ? (
                                                <i className="bi bi-check-lg"></i>
                                            ) : (
                                                <i className={`bi ${item.icon}`}></i>
                                            )}
                                        </div>
                                        <div className="rr-register-progress-text">
                                            <span>Step {item.id}</span>
                                            <strong>{item.label}</strong>
                                        </div>
                                        {index < steps.length - 1 && (
                                            <div
                                                className={`rr-register-progress-line ${completed ? 'completed' : ''
                                                    }`}
                                            ></div>
                                        )}
                                    </div>
                                );
                            })}
                        </div>
                        {/* =============================================
                            FORM HEADER
                           ============================================= */}
                        <div className="rr-register-form-heading">
                            <div className="rr-register-form-icon">
                                <i className={`bi ${currentStep.icon}`}></i>
                            </div>
                            <div>
                                <span>STEP {step} OF 3</span>
                                <h2>{currentStep.title}</h2>
                                <p>{currentStep.description}</p>
                            </div>
                        </div>
                        {/* =============================================
                            GENERAL REGISTRATION ERROR
                           ============================================= */}
                        {errors.registration && (
                            <div className="rr-register-alert">
                                <i className="bi bi-exclamation-circle-fill"></i>
                                <div>
                                    <strong>Registration failed</strong>
                                    <span>{errors.registration}</span>
                                </div>
                            </div>
                        )}
                        {/* =============================================
                            FORM
                           ============================================= */}
                        <form
                            onSubmit={submit}
                            className="rr-register-form"
                        >
                            {/* =========================================
                                STEP 1
                               ========================================= */}
                            {step === 1 && (
                                <div
                                    key="step-account"
                                    className="rr-register-step"
                                >
                                    <div className="rr-register-grid">
                                        <FormField
                                            label="Full Name"
                                            required
                                            error={errors.name}
                                        >
                                            <div className="rr-register-input-wrap">
                                                <i className="bi bi-person"></i>
                                                <input
                                                    type="text"
                                                    value={data.name}
                                                    onChange={(event) =>
                                                        updateField(
                                                            'name',
                                                            event.target.value,
                                                        )
                                                    }
                                                    placeholder="Enter your full name"
                                                    autoComplete="name"
                                                    autoFocus
                                                />
                                            </div>
                                        </FormField>
                                        <FormField
                                            label="Mobile Number"
                                            required
                                            error={errors.mobile}
                                        >
                                            <div className="rr-register-input-wrap">
                                                <i className="bi bi-phone"></i>
                                                <input
                                                    type="tel"
                                                    value={data.mobile}
                                                    onChange={(event) =>
                                                        updateField(
                                                            'mobile',
                                                            event.target.value,
                                                        )
                                                    }
                                                    placeholder="Enter mobile number"
                                                    autoComplete="tel"
                                                />
                                            </div>
                                        </FormField>
                                        <FormField
                                            label="Email Address"
                                            required
                                            error={errors.email}
                                            full
                                        >
                                            <div className="rr-register-input-wrap">
                                                <i className="bi bi-envelope"></i>
                                                <input
                                                    type="email"
                                                    value={data.email}
                                                    onChange={(event) =>
                                                        updateField(
                                                            'email',
                                                            event.target.value,
                                                        )
                                                    }
                                                    placeholder="name@business.com"
                                                    autoComplete="email"
                                                />
                                            </div>
                                        </FormField>
                                        <FormField
                                            label="Password"
                                            required
                                            error={errors.password}
                                        >
                                            <div className="rr-register-input-wrap">
                                                <i className="bi bi-lock"></i>
                                                <input
                                                    type="password"
                                                    value={data.password}
                                                    onChange={(event) =>
                                                        updateField(
                                                            'password',
                                                            event.target.value,
                                                        )
                                                    }
                                                    placeholder="Minimum 8 characters"
                                                    autoComplete="new-password"
                                                />
                                            </div>
                                        </FormField>
                                        <FormField
                                            label="Confirm Password"
                                            required
                                        >
                                            <div className="rr-register-input-wrap">
                                                <i className="bi bi-lock-fill"></i>
                                                <input
                                                    type="password"
                                                    value={data.password_confirmation}
                                                    onChange={(event) =>
                                                        updateField(
                                                            'password_confirmation',
                                                            event.target.value,
                                                        )
                                                    }
                                                    placeholder="Re-enter password"
                                                    autoComplete="new-password"
                                                />
                                            </div>
                                            {data.password_confirmation &&
                                                data.password !==
                                                data.password_confirmation && (
                                                    <div className="rr-register-local-error">
                                                        Passwords do not match.
                                                    </div>
                                                )}
                                        </FormField>
                                    </div>
                                    <div className="rr-register-password-note">
                                        <i className="bi bi-info-circle"></i>
                                        Password must contain at least 8 characters
                                        including letters and numbers.
                                    </div>
                                </div>
                            )}
                            {/* =========================================
                                STEP 2
                               ========================================= */}
                            {step === 2 && (
                                <div
                                    key="step-business"
                                    className="rr-register-step"
                                >
                                    <div className="rr-register-grid">
                                        <FormField
                                            label="Business / Company Name"
                                            required
                                            error={errors.company_name}
                                            full
                                        >
                                            <div className="rr-register-input-wrap">
                                                <i className="bi bi-buildings"></i>
                                                <input
                                                    type="text"
                                                    value={data.company_name}
                                                    onChange={(event) =>
                                                        updateField(
                                                            'company_name',
                                                            event.target.value,
                                                        )
                                                    }
                                                    placeholder="Enter registered or business name"
                                                    autoFocus
                                                />
                                            </div>
                                        </FormField>
                                        <FormField
                                            label="Company Phone"
                                            error={errors.company_phone}
                                        >
                                            <div className="rr-register-input-wrap">
                                                <i className="bi bi-telephone"></i>
                                                <input
                                                    type="tel"
                                                    value={data.company_phone}
                                                    onChange={(event) =>
                                                        updateField(
                                                            'company_phone',
                                                            event.target.value,
                                                        )
                                                    }
                                                    placeholder="Business phone"
                                                />
                                            </div>
                                        </FormField>
                                        <FormField
                                            label="Company Email"
                                            error={errors.company_email}
                                        >
                                            <div className="rr-register-input-wrap">
                                                <i className="bi bi-envelope"></i>
                                                <input
                                                    type="email"
                                                    value={data.company_email}
                                                    onChange={(event) =>
                                                        updateField(
                                                            'company_email',
                                                            event.target.value,
                                                        )
                                                    }
                                                    placeholder="Business email"
                                                />
                                            </div>
                                        </FormField>
                                        <FormField
                                            label="GST Number"
                                            hint="Optional"
                                            error={errors.gst_number}
                                        >
                                            <div className="rr-register-input-wrap">
                                                <i className="bi bi-file-earmark-text"></i>
                                                <input
                                                    type="text"
                                                    value={data.gst_number}
                                                    onChange={(event) =>
                                                        updateField(
                                                            'gst_number',
                                                            event.target.value.toUpperCase(),
                                                        )
                                                    }
                                                    placeholder="GSTIN"
                                                />
                                            </div>
                                        </FormField>
                                        <FormField
                                            label="PAN Number"
                                            hint="Optional"
                                            error={errors.pan_number}
                                        >
                                            <div className="rr-register-input-wrap">
                                                <i className="bi bi-card-text"></i>
                                                <input
                                                    type="text"
                                                    value={data.pan_number}
                                                    onChange={(event) =>
                                                        updateField(
                                                            'pan_number',
                                                            event.target.value.toUpperCase(),
                                                        )
                                                    }
                                                    placeholder="PAN"
                                                />
                                            </div>
                                        </FormField>
                                    </div>
                                    <div className="rr-register-business-note">
                                        <div className="rr-register-note-icon">
                                            <i className="bi bi-lightning-charge"></i>
                                        </div>
                                        <div>
                                            <strong>
                                                RR ERP generates your company code
                                                automatically
                                            </strong>
                                            <p>
                                                You don't need to create or remember an
                                                internal company code during
                                                registration.
                                            </p>
                                        </div>
                                    </div>
                                </div>
                            )}
                            {/* =========================================
                                STEP 3
                               ========================================= */}
                            {step === 3 && (
                                <div
                                    key="step-branch"
                                    className="rr-register-step"
                                >
                                    <div className="rr-register-grid">
                                        <FormField
                                            label="Main Branch Name"
                                            required
                                            error={errors.store_name}
                                            full
                                        >
                                            <div className="rr-register-input-wrap">
                                                <i className="bi bi-shop"></i>
                                                <input
                                                    type="text"
                                                    value={data.store_name}
                                                    onChange={(event) =>
                                                        updateField(
                                                            'store_name',
                                                            event.target.value,
                                                        )
                                                    }
                                                    placeholder="Example: Main Branch, Chennai Branch"
                                                    autoFocus
                                                />
                                            </div>
                                        </FormField>
                                        <FormField
                                            label="Contact Person"
                                            error={errors.contact_person}
                                        >
                                            <div className="rr-register-input-wrap">
                                                <i className="bi bi-person-lines-fill"></i>
                                                <input
                                                    type="text"
                                                    value={data.contact_person}
                                                    onChange={(event) =>
                                                        updateField(
                                                            'contact_person',
                                                            event.target.value,
                                                        )
                                                    }
                                                    placeholder="Defaults to your name"
                                                />
                                            </div>
                                        </FormField>
                                        <FormField
                                            label="Branch Phone"
                                            error={errors.store_phone}
                                        >
                                            <div className="rr-register-input-wrap">
                                                <i className="bi bi-telephone"></i>
                                                <input
                                                    type="tel"
                                                    value={data.store_phone}
                                                    onChange={(event) =>
                                                        updateField(
                                                            'store_phone',
                                                            event.target.value,
                                                        )
                                                    }
                                                    placeholder="Branch phone"
                                                />
                                            </div>
                                        </FormField>
                                        <FormField
                                            label="Branch Email"
                                            error={errors.store_email}
                                            full
                                        >
                                            <div className="rr-register-input-wrap">
                                                <i className="bi bi-envelope"></i>
                                                <input
                                                    type="email"
                                                    value={data.store_email}
                                                    onChange={(event) =>
                                                        updateField(
                                                            'store_email',
                                                            event.target.value,
                                                        )
                                                    }
                                                    placeholder="Optional branch email"
                                                />
                                            </div>
                                        </FormField>
                                        <FormField
                                            label="Address"
                                            required
                                            error={errors.address}
                                            full
                                        >
                                            <div className="rr-register-input-wrap rr-register-textarea-wrap">
                                                <i className="bi bi-geo-alt"></i>
                                                <textarea
                                                    rows="3"
                                                    value={data.address}
                                                    onChange={(event) =>
                                                        updateField(
                                                            'address',
                                                            event.target.value,
                                                        )
                                                    }
                                                    placeholder="Street, building, area..."
                                                ></textarea>
                                            </div>
                                        </FormField>
                                        <FormField
                                            label="City"
                                            required
                                            error={errors.city}
                                        >
                                            <div className="rr-register-input-wrap">
                                                <i className="bi bi-geo"></i>
                                                <input
                                                    type="text"
                                                    value={data.city}
                                                    onChange={(event) =>
                                                        updateField(
                                                            'city',
                                                            event.target.value,
                                                        )
                                                    }
                                                    placeholder="City"
                                                />
                                            </div>
                                        </FormField>
                                        <FormField
                                            label="State"
                                            required
                                            error={errors.state}
                                        >
                                            <div className="rr-register-input-wrap">
                                                <i className="bi bi-map"></i>
                                                <input
                                                    type="text"
                                                    value={data.state}
                                                    onChange={(event) =>
                                                        updateField(
                                                            'state',
                                                            event.target.value,
                                                        )
                                                    }
                                                    placeholder="State"
                                                />
                                            </div>
                                        </FormField>
                                        <FormField
                                            label="Pincode"
                                            required
                                            error={errors.pincode}
                                        >
                                            <div className="rr-register-input-wrap">
                                                <i className="bi bi-mailbox"></i>
                                                <input
                                                    type="text"
                                                    value={data.pincode}
                                                    onChange={(event) =>
                                                        updateField(
                                                            'pincode',
                                                            event.target.value,
                                                        )
                                                    }
                                                    placeholder="Pincode"
                                                />
                                            </div>
                                        </FormField>
                                        <FormField
                                            label="Country"
                                            required
                                            error={errors.country}
                                        >
                                            <div className="rr-register-input-wrap">
                                                <i className="bi bi-globe2"></i>
                                                <input
                                                    type="text"
                                                    value={data.country}
                                                    onChange={(event) =>
                                                        updateField(
                                                            'country',
                                                            event.target.value,
                                                        )
                                                    }
                                                />
                                            </div>
                                        </FormField>
                                    </div>
                                    {/* =================================
                                            TERMS
                                           ================================= */}
                                    <label className="rr-register-terms">
                                        <input
                                            type="checkbox"
                                            checked={data.terms}
                                            onChange={(event) =>
                                                updateField(
                                                    'terms',
                                                    event.target.checked,
                                                )
                                            }
                                        />
                                        <span className="rr-register-checkbox">
                                            <i className="bi bi-check"></i>
                                        </span>
                                        <span>
                                            I confirm that the information provided is
                                            correct and I agree to create this business
                                            workspace in RR ERP.
                                        </span>
                                    </label>
                                    {errors.terms && (
                                        <div className="rr-register-field-error rr-register-terms-error">
                                            <i className="bi bi-exclamation-circle"></i>
                                            {errors.terms}
                                        </div>
                                    )}
                                    {/* =================================
                                            SUMMARY
                                           ================================= */}
                                    <div className="rr-register-summary">
                                        <div className="rr-register-summary-title">
                                            <i className="bi bi-check2-circle"></i>
                                            Registration Summary
                                        </div>
                                        <div className="rr-register-summary-grid">
                                            <SummaryItem
                                                label="Administrator"
                                                value={data.name || '—'}
                                            />
                                            <SummaryItem
                                                label="Business"
                                                value={data.company_name || '—'}
                                            />
                                            <SummaryItem
                                                label="Main Branch"
                                                value={data.store_name || '—'}
                                            />
                                        </div>
                                    </div>
                                </div>
                            )}
                            {/* =============================================
                                ACTIONS
                               ============================================= */}
                            <div className="rr-register-actions">
                                <div>
                                    {step > 1 && (
                                        <button
                                            type="button"
                                            className="rr-register-back-btn"
                                            onClick={previousStep}
                                            disabled={processing}
                                        >
                                            <i className="bi bi-arrow-left"></i>
                                            Back
                                        </button>
                                    )}
                                </div>
                                <div>
                                    {step < 3 ? (
                                        <button
                                            type="button"
                                            className="rr-register-next-btn"
                                            onClick={nextStep}
                                            disabled={!validateStep()}
                                        >
                                            Continue
                                            <i className="bi bi-arrow-right"></i>
                                        </button>
                                    ) : (
                                        <button
                                            type="submit"
                                            className="rr-register-submit-btn"
                                            disabled={processing || !validateStep()}
                                        >
                                            {processing ? (
                                                <>
                                                    <span className="rr-register-spinner"></span>
                                                    Creating Workspace...
                                                </>
                                            ) : (
                                                <>
                                                    Create Business Workspace
                                                    <i className="bi bi-arrow-right"></i>
                                                </>
                                            )}
                                        </button>
                                    )}
                                </div>
                            </div>
                        </form>
                        {/* =============================================
                            SIGN IN MOBILE
                           ============================================= */}
                        <div className="rr-register-mobile-login">
                            Already have an account?
                            <Link href="/login">Sign In</Link>
                        </div>
                    </section>
                </main>
            </div>
        </>
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
    const fieldId = useId();
    const decorateControl = (element) => {
        if (!isValidElement(element)) return element;
        if (['input', 'textarea', 'select'].includes(element.type)) {
            return cloneElement(element, {
                id: element.props.id || fieldId,
                'aria-invalid': Boolean(error),
                'aria-describedby': error ? `${fieldId}-error` : undefined,
            });
        }
        return element.props.children
            ? cloneElement(
                element,
                {},
                Children.map(element.props.children, decorateControl),
            )
            : element;
    };
    return (
        <div className={`rr-register-field ${full ? 'rr-register-field-full' : ''}`}>
            <div className="rr-register-label-row">
                <label htmlFor={fieldId}>
                    {label}
                    {required && <span>*</span>}
                </label>
                {hint && <small>{hint}</small>}
            </div>
            {Children.map(children, decorateControl)}
            {error && (
                <div
                    className="rr-register-field-error"
                    id={`${fieldId}-error`}
                    role="alert"
                >
                    <i className="bi bi-exclamation-circle"></i>
                    {error}
                </div>
            )}
        </div>
    );
}
/*
|--------------------------------------------------------------------------
| Benefit
|--------------------------------------------------------------------------
*/
function RegistrationBenefit({ icon, title, text }) {
    return (
        <div className="rr-register-benefit">
            <div className="rr-register-benefit-icon">
                <i className={`bi ${icon}`}></i>
            </div>
            <div>
                <strong>{title}</strong>
                <p>{text}</p>
            </div>
        </div>
    );
}
/*
|--------------------------------------------------------------------------
| Summary Item
|--------------------------------------------------------------------------
*/
function SummaryItem({ label, value }) {
    return (
        <div className="rr-register-summary-item">
            <span>{label}</span>
            <strong>{value}</strong>
        </div>
    );
}
