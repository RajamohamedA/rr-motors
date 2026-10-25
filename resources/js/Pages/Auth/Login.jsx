import PublicHeader from '../../Components/PublicHeader';
import { Head, Link, useForm, usePage } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import '../../../css/login.css';
export default function Login({ canResetPassword = false }) {
    /*
    |--------------------------------------------------------------------------
    | Shared Props
    |--------------------------------------------------------------------------
    */
    const { app_message = null } = usePage().props;
    /*
    |--------------------------------------------------------------------------
    | Password Visibility
    |--------------------------------------------------------------------------
    */
    const [showPassword, setShowPassword] = useState(false);
    /*
    |--------------------------------------------------------------------------
    | Form
    |--------------------------------------------------------------------------
    */
    const { data, setData, post, processing, errors, clearErrors } = useForm({
        email: '',
        password: '',
        remember: false,
    });
    /*
    |--------------------------------------------------------------------------
    | Notification
    |--------------------------------------------------------------------------
    */
    const notification = useMemo(() => {
        if (!app_message?.message) {
            return null;
        }
        return {
            id: app_message.id ?? 'login-message',
            type: app_message.type ?? 'info',
            message: app_message.message,
        };
    }, [app_message?.id, app_message?.type, app_message?.message]);
    /*
    |--------------------------------------------------------------------------
    | Update Field
    |--------------------------------------------------------------------------
    */
    const updateField = (field, value) => {
        clearErrors(field);
        setData(field, value);
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
        post('/login', {
            preserveScroll: true,
        });
    };
    /*
    |--------------------------------------------------------------------------
    | Alert Configuration
    |--------------------------------------------------------------------------
    */
    const alertConfig = {
        success: {
            icon: 'bi-check-circle-fill',
            title: 'Success',
        },
        warning: {
            icon: 'bi-exclamation-triangle-fill',
            title: 'Account Notice',
        },
        error: {
            icon: 'bi-x-circle-fill',
            title: 'Sign In Error',
        },
        info: {
            icon: 'bi-info-circle-fill',
            title: 'Information',
        },
    };
    const currentAlert = notification
        ? (alertConfig[notification.type] ?? alertConfig.info)
        : null;
    return (
        <>
            <Head title="Sign In - RR ERP" />
            <div className="rr-react-login-page">
                {/* =====================================================
                    BACKGROUND
                   ===================================================== */}
                <div className="rr-react-login-glow rr-react-login-glow-one"></div>
                <div className="rr-react-login-glow rr-react-login-glow-two"></div>
                {/* =====================================================
                    HEADER
                   ===================================================== */}
                <PublicHeader page="login" />
                {/* =====================================================
                    MAIN
                   ===================================================== */}
                <main className="rr-react-login-main">
                    {/* =================================================
                        SHOWCASE
                       ================================================= */}
                    <section className="rr-react-login-showcase">
                        <div className="rr-react-login-showcase-content">
                            <div className="rr-react-login-showcase-badge">
                                <span></span>
                                RR ERP BUSINESS PLATFORM
                            </div>
                            <h1>
                                Your business.
                                <span>One connected workspace.</span>
                            </h1>
                            <p>
                                Sign in to manage your branches, inventory,
                                purchases, sales, stock, users and reports from one
                                secure ERP platform.
                            </p>
                            <div className="rr-react-login-features">
                                <Feature
                                    icon="bi-buildings"
                                    title="Multi Branch"
                                    text="Manage all authorized business locations."
                                />
                                <Feature
                                    icon="bi-box-seam"
                                    title="Inventory Control"
                                    text="Track shop and godown stock in one system."
                                />
                                <Feature
                                    icon="bi-shield-check"
                                    title="Secure Access"
                                    text="Role and permission controlled access."
                                />
                            </div>
                        </div>
                        {/* =============================================
                            ERP PREVIEW
                           ============================================= */}
                        <div className="rr-react-login-preview">
                            <div className="rr-react-login-preview-window">
                                <div className="rr-react-login-preview-header">
                                    <div className="rr-react-login-preview-dots">
                                        <span></span>
                                        <span></span>
                                        <span></span>
                                    </div>
                                    <div>RR ERP</div>
                                    <div className="rr-react-login-preview-status">
                                        <span></span>
                                        Sample preview
                                    </div>
                                </div>
                                <div className="rr-react-login-preview-body">
                                    <div className="rr-react-login-preview-sidebar">
                                        <PreviewMenu
                                            icon="bi-grid"
                                            active
                                        />
                                        <PreviewMenu icon="bi-buildings" />
                                        <PreviewMenu icon="bi-box-seam" />
                                        <PreviewMenu icon="bi-receipt" />
                                        <PreviewMenu icon="bi-graph-up" />
                                    </div>
                                    <div className="rr-react-login-preview-workspace">
                                        <div className="rr-react-login-preview-topbar">
                                            <div>
                                                <span></span>
                                                <span></span>
                                            </div>
                                            <div className="rr-react-login-preview-avatar">
                                                R
                                            </div>
                                        </div>
                                        <div className="rr-react-login-preview-stats">
                                            <PreviewStat icon="bi-shop" />
                                            <PreviewStat icon="bi-box-seam" />
                                            <PreviewStat icon="bi-receipt" />
                                        </div>
                                        <div className="rr-react-login-preview-chart">
                                            {[42, 68, 54, 81, 63, 90, 75].map(
                                                (height, index) => (
                                                    <span
                                                        key={index}
                                                        style={{
                                                            height: `${height}%`,
                                                        }}
                                                    ></span>
                                                ),
                                            )}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </section>
                    {/* =================================================
                        LOGIN CARD
                       ================================================= */}
                    <section className="rr-react-login-card">
                        <div className="rr-react-login-card-header">
                            <div className="rr-react-login-card-icon">
                                <i className="bi bi-person-lock"></i>
                            </div>
                            <div>
                                <span>WELCOME BACK</span>
                                <h2>Sign in to RR ERP</h2>
                                <p>Enter your account credentials to continue.</p>
                            </div>
                        </div>
                        {/* =============================================
                            APP MESSAGE
                           ============================================= */}
                        {notification && (
                            <div
                                key={notification.id}
                                className={
                                    `rr-react-login-alert ` +
                                    `rr-react-login-alert-${notification.type}`
                                }
                            >
                                <div className="rr-react-login-alert-icon">
                                    <i className={`bi ${currentAlert.icon}`}></i>
                                </div>
                                <div className="rr-react-login-alert-content">
                                    <strong>{currentAlert.title}</strong>
                                    <span>{notification.message}</span>
                                </div>
                            </div>
                        )}
                        {/* =============================================
                            FORM
                           ============================================= */}
                        <form
                            className="rr-react-login-form"
                            aria-busy={processing}
                            onSubmit={submit}
                        >
                            {/* =========================================
                                EMAIL
                               ========================================= */}
                            <div className="rr-react-login-field">
                                <label htmlFor="email">
                                    Email Address
                                    <span>*</span>
                                </label>
                                <div
                                    className={`rr-react-login-input ${errors.email
                                            ? 'rr-react-login-input-error'
                                            : ''
                                        }`}
                                >
                                    <i className="bi bi-envelope"></i>
                                    <input
                                        id="email"
                                        name="email"
                                        disabled={processing}
                                        aria-invalid={Boolean(errors.email)}
                                        aria-describedby={
                                            errors.email
                                                ? 'login-email-error'
                                                : undefined
                                        }
                                        type="email"
                                        value={data.email}
                                        onChange={(event) =>
                                            updateField('email', event.target.value)
                                        }
                                        placeholder="Enter your email address"
                                        autoComplete="username"
                                        autoFocus
                                        required
                                    />
                                </div>
                                {errors.email && (
                                    <div
                                        className="rr-react-login-field-error"
                                        id="login-email-error"
                                        role="alert"
                                    >
                                        <i className="bi bi-exclamation-circle"></i>
                                        {errors.email}
                                    </div>
                                )}
                            </div>
                            {/* =========================================
                                PASSWORD
                               ========================================= */}
                            <div className="rr-react-login-field">
                                <div className="rr-react-login-label-row">
                                    <label htmlFor="password">
                                        Password
                                        <span>*</span>
                                    </label>
                                    {canResetPassword && (
                                        <Link
                                            href="/forgot-password"
                                            className="rr-react-login-forgot"
                                        >
                                            Forgot password?
                                        </Link>
                                    )}
                                </div>
                                <div
                                    className={`rr-react-login-input ${errors.password
                                            ? 'rr-react-login-input-error'
                                            : ''
                                        }`}
                                >
                                    <i className="bi bi-lock"></i>
                                    <input
                                        id="password"
                                        name="password"
                                        disabled={processing}
                                        aria-invalid={Boolean(errors.password)}
                                        aria-describedby={
                                            errors.password
                                                ? 'login-password-error'
                                                : undefined
                                        }
                                        type={showPassword ? 'text' : 'password'}
                                        value={data.password}
                                        onChange={(event) =>
                                            updateField(
                                                'password',
                                                event.target.value,
                                            )
                                        }
                                        placeholder="Enter your password"
                                        autoComplete="current-password"
                                        required
                                    />
                                    <button
                                        type="button"
                                        className="rr-react-login-password-toggle"
                                        aria-pressed={showPassword}
                                        disabled={processing}
                                        onClick={() =>
                                            setShowPassword((current) => !current)
                                        }
                                        aria-label={
                                            showPassword
                                                ? 'Hide password'
                                                : 'Show password'
                                        }
                                    >
                                        <i
                                            className={
                                                showPassword
                                                    ? 'bi bi-eye-slash'
                                                    : 'bi bi-eye'
                                            }
                                        ></i>
                                    </button>
                                </div>
                                {errors.password && (
                                    <div
                                        className="rr-react-login-field-error"
                                        id="login-password-error"
                                        role="alert"
                                    >
                                        <i className="bi bi-exclamation-circle"></i>
                                        {errors.password}
                                    </div>
                                )}
                            </div>
                            {/* =========================================
                                REMEMBER
                               ========================================= */}
                            <label className="rr-react-login-remember">
                                <input
                                    type="checkbox"
                                    checked={data.remember}
                                    onChange={(event) =>
                                        updateField('remember', event.target.checked)
                                    }
                                />
                                <span className="rr-react-login-checkbox">
                                    <i className="bi bi-check"></i>
                                </span>
                                <span>Keep me signed in</span>
                            </label>
                            {/* =========================================
                                SUBMIT
                               ========================================= */}
                            <button
                                type="submit"
                                className="rr-react-login-submit"
                                disabled={processing}
                            >
                                {processing ? (
                                    <>
                                        <span className="rr-react-login-spinner"></span>
                                        Signing In...
                                    </>
                                ) : (
                                    <>
                                        Sign In
                                        <i className="bi bi-arrow-right"></i>
                                    </>
                                )}
                            </button>
                        </form>
                        {/* =============================================
                            REGISTER BUSINESS
                           ============================================= */}
                        <div className="rr-react-login-register-box">
                            <div className="rr-react-login-register-icon">
                                <i className="bi bi-buildings"></i>
                            </div>
                            <div>
                                <strong>Starting a new business?</strong>
                                <span>Create your company workspace in RR ERP.</span>
                            </div>
                            <Link href="/register-business">
                                Register
                                <i className="bi bi-arrow-right"></i>
                            </Link>
                        </div>
                        {/* =============================================
                            SECURITY
                           ============================================= */}
                        <div className="rr-react-login-security">
                            <i className="bi bi-shield-lock"></i>
                            Secure authentication protected by RR ERP
                        </div>
                    </section>
                </main>
                {/* =====================================================
                    FOOTER
                   ===================================================== */}
                <footer className="rr-react-login-footer">
                    <span>
                        © {new Date().getFullYear()} RR ERP. All rights reserved.
                    </span>
                    <Link href="/">
                        <i className="bi bi-arrow-left"></i>
                        Back to RR ERP Home
                    </Link>
                </footer>
            </div>
        </>
    );
}
/*
|--------------------------------------------------------------------------
| Feature
|--------------------------------------------------------------------------
*/
function Feature({ icon, title, text }) {
    return (
        <div className="rr-react-login-feature">
            <div className="rr-react-login-feature-icon">
                <i className={`bi ${icon}`}></i>
            </div>
            <div>
                <strong>{title}</strong>
                <span>{text}</span>
            </div>
        </div>
    );
}
/*
|--------------------------------------------------------------------------
| Preview Menu
|--------------------------------------------------------------------------
*/
function PreviewMenu({ icon, active = false }) {
    return (
        <span className={active ? 'active' : ''}>
            <i className={`bi ${icon}`}></i>
        </span>
    );
}
/*
|--------------------------------------------------------------------------
| Preview Stat
|--------------------------------------------------------------------------
*/
function PreviewStat({ icon }) {
    return (
        <div className="rr-react-login-preview-stat">
            <div>
                <i className={`bi ${icon}`}></i>
            </div>
            <span></span>
        </div>
    );
}
