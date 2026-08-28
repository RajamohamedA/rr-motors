import {
    Head,
    Link,
    useForm,
    usePage,
} from '@inertiajs/react';

import '../../../css/forgot-password.css';


export default function ForgotPassword({
    status,
}) {

    /*
    |--------------------------------------------------------------------------
    | Shared Props
    |--------------------------------------------------------------------------
    */

    const {
        app_message = null,
    } = usePage().props;


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
        clearErrors,
    } = useForm({
        email: '',
    });


    /*
    |--------------------------------------------------------------------------
    | Submit
    |--------------------------------------------------------------------------
    */

    const submit = (event) => {

        event.preventDefault();

        clearErrors();

        post(
            '/forgot-password',
            {
                preserveScroll: true,
            }
        );
    };


    /*
    |--------------------------------------------------------------------------
    | Notification
    |--------------------------------------------------------------------------
    */

    const notification =
        app_message?.message
            ? app_message
            : status
                ? {
                    type:
                        'success',

                    message:
                        status,
                }
                : null;


    /*
    |--------------------------------------------------------------------------
    | Notification Config
    |--------------------------------------------------------------------------
    */

    const notificationConfig = {

        success: {

            icon:
                'bi-check-circle-fill',

            title:
                'Email Sent',
        },

        error: {

            icon:
                'bi-x-circle-fill',

            title:
                'Unable to Continue',
        },

        warning: {

            icon:
                'bi-exclamation-triangle-fill',

            title:
                'Attention',
        },

        info: {

            icon:
                'bi-info-circle-fill',

            title:
                'Information',
        },
    };


    /*
    |--------------------------------------------------------------------------
    | Active Notification Config
    |--------------------------------------------------------------------------
    */

    const config =
        notificationConfig[
        notification?.type
        ] ??
        notificationConfig.info;


    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */

    return (
        <>

            <Head title="Forgot Password | RR ERP" />


            <div className="rr-forgot-page">

                {/* =========================================================
                    HEADER
                   ========================================================= */}

                <header className="rr-forgot-header">

                    {/* BRAND */}

                    <Link
                        href="/"
                        className="rr-forgot-brand"
                    >

                        <div className="rr-forgot-brand-icon">
                            RR
                        </div>


                        <div>

                            <div className="rr-forgot-brand-name">
                                RR ERP
                            </div>


                            <div className="rr-forgot-brand-caption">
                                Business Management Platform
                            </div>

                        </div>

                    </Link>


                    {/* LOGIN */}

                    <div className="rr-forgot-header-right">

                        <span>
                            Remember your password?
                        </span>


                        <Link
                            href="/login"
                            className="rr-forgot-login-link"
                        >
                            Sign In
                        </Link>

                    </div>

                </header>


                {/* =========================================================
                    MAIN
                   ========================================================= */}

                <main className="rr-forgot-main">

                    <section className="rr-forgot-card">

                        {/* =================================================
                            ICON
                           ================================================= */}

                        <div className="rr-forgot-main-icon">

                            <i className="bi bi-shield-lock"></i>

                        </div>


                        {/* =================================================
                            HEADING
                           ================================================= */}

                        <div className="rr-forgot-heading">

                            <span>
                                ACCOUNT RECOVERY
                            </span>


                            <h1>
                                Forgot your password?
                            </h1>


                            <p>
                                Enter the email address associated with
                                your RR ERP account. We will send you a
                                secure password reset link.
                            </p>

                        </div>


                        {/* =================================================
                            NOTIFICATION
                           ================================================= */}

                        {
                            notification && (

                                <div
                                    className={
                                        `rr-forgot-alert ` +
                                        `rr-forgot-alert-${notification.type}`
                                    }
                                    role="alert"
                                >

                                    <div className="rr-forgot-alert-icon">

                                        <i
                                            className={
                                                `bi ${config.icon}`
                                            }
                                        ></i>

                                    </div>


                                    <div className="rr-forgot-alert-content">

                                        <strong>
                                            {config.title}
                                        </strong>


                                        <span>
                                            {notification.message}
                                        </span>

                                    </div>

                                </div>

                            )
                        }


                        {/* =================================================
                            FORM
                           ================================================= */}

                        <form
                            onSubmit={submit}
                            className="rr-forgot-form"
                            noValidate
                        >

                            {/* EMAIL */}

                            <div className="rr-forgot-field">

                                <label htmlFor="email">
                                    Email Address
                                </label>


                                <div
                                    className={
                                        `rr-forgot-input ` +
                                        (
                                            errors.email
                                                ? 'has-error'
                                                : ''
                                        )
                                    }
                                >

                                    <i className="bi bi-envelope"></i>


                                    <input
                                        id="email"
                                        name="email"
                                        type="email"
                                        value={data.email}
                                        autoComplete="email"
                                        autoFocus
                                        placeholder="Enter your registered email"
                                        disabled={processing}
                                        onChange={
                                            (event) => {

                                                setData(
                                                    'email',
                                                    event.target.value
                                                );


                                                if (
                                                    errors.email
                                                ) {

                                                    clearErrors(
                                                        'email'
                                                    );
                                                }
                                            }
                                        }
                                    />

                                </div>


                                {/* VALIDATION ERROR */}

                                {
                                    errors.email && (

                                        <div className="rr-forgot-field-error">

                                            <i className="bi bi-exclamation-circle"></i>


                                            <span>
                                                {errors.email}
                                            </span>

                                        </div>

                                    )
                                }

                            </div>


                            {/* =================================================
                                SUBMIT
                               ================================================= */}

                            <button
                                type="submit"
                                className="rr-forgot-submit"
                                disabled={processing}
                            >

                                {
                                    processing
                                        ? (
                                            <>

                                                <span
                                                    className="
                                                        spinner-border
                                                        spinner-border-sm
                                                    "
                                                    role="status"
                                                    aria-hidden="true"
                                                ></span>

                                                Sending...

                                            </>
                                        )
                                        : (
                                            <>

                                                Send Password Reset Link

                                                <i className="bi bi-arrow-right"></i>

                                            </>
                                        )
                                }

                            </button>

                        </form>


                        {/* =================================================
                            BACK TO LOGIN
                           ================================================= */}

                        <div className="rr-forgot-back">

                            <Link href="/login">

                                <i className="bi bi-arrow-left"></i>

                                Back to Sign In

                            </Link>

                        </div>


                        {/* =================================================
                            SECURITY INFORMATION
                           ================================================= */}

                        <div className="rr-forgot-security">

                            <i className="bi bi-lock-fill"></i>


                            <span>
                                For your security, password reset links
                                are temporary and can only be used to
                                reset the account associated with the
                                requested email address.
                            </span>

                        </div>

                    </section>

                </main>


                {/* =========================================================
                    FOOTER
                   ========================================================= */}

                <footer className="rr-forgot-footer">

                    <span>
                        © {new Date().getFullYear()} RR ERP
                    </span>


                    <span className="rr-forgot-footer-dot">
                        •
                    </span>


                    <span>
                        Secure Business Management
                    </span>

                </footer>

            </div>

        </>
    );
}