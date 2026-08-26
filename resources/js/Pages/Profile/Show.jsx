import {
    useState,
} from 'react';

import {
    Head,
    useForm,
} from '@inertiajs/react';

import SuperAdminLayout
    from '../../Layouts/SuperAdminLayout';


export default function Show({
    userData,
}) {

    /*
    |--------------------------------------------------------------------------
    | Profile Form
    |--------------------------------------------------------------------------
    */

    const profileForm = useForm({
        name: userData?.name ?? '',
        email: userData?.email ?? '',
        mobile: userData?.mobile ?? '',
    });


    /*
    |--------------------------------------------------------------------------
    | Password Form
    |--------------------------------------------------------------------------
    */

    const passwordForm = useForm({
        current_password: '',
        password: '',
        password_confirmation: '',
    });


    /*
    |--------------------------------------------------------------------------
    | Update Profile
    |--------------------------------------------------------------------------
    */

    const updateProfile = (event) => {

        event.preventDefault();

        profileForm.put(
            '/profile',
            {
                preserveScroll: true,
            }
        );
    };


    /*
    |--------------------------------------------------------------------------
    | Update Password
    |--------------------------------------------------------------------------
    */

    const updatePassword = (event) => {

        event.preventDefault();

        passwordForm.put(
            '/profile/password',
            {
                preserveScroll: true,

                onSuccess: () => {

                    passwordForm.reset(
                        'current_password',
                        'password',
                        'password_confirmation'
                    );

                },
            }
        );
    };


    return (

        <SuperAdminLayout
            title="My Profile"
            subtitle="Account information, access and security"
            breadcrumbs={[
                {
                    label: 'My Profile',
                },
            ]}
        >

            <Head title="My Profile" />


            {/* =====================================================
                PROFILE HERO
               ===================================================== */}

            <section className="rr-profile-hero">

                <div className="rr-profile-hero-identity">

                    <div className="rr-profile-hero-avatar">

                        {getInitials(
                            userData?.name
                        )}

                    </div>


                    <div className="rr-profile-hero-details">

                        <div className="rr-profile-hero-name">

                            {userData?.name
                                ?? 'Administrator'}

                        </div>


                        <div className="rr-profile-hero-email">

                            {userData?.email
                                ?? ''}

                        </div>


                        <div className="rr-profile-hero-meta">

                            <span className="rr-profile-hero-role">

                                <i className="bi bi-shield-check"></i>

                                {userData?.is_super_user
                                    ? 'Super Administrator'
                                    : getPrimaryRole(
                                        userData?.roles
                                    )}

                            </span>


                            <span
                                className={
                                    userData?.status
                                        ? 'rr-profile-hero-status active'
                                        : 'rr-profile-hero-status inactive'
                                }
                            >

                                <span className="rr-profile-status-dot"></span>

                                {userData?.status
                                    ? 'Active'
                                    : 'Inactive'}

                            </span>

                        </div>

                    </div>

                </div>


                {/* Last Login */}

                <div className="rr-profile-login-info">

                    <div className="rr-profile-login-icon">

                        <i className="bi bi-clock-history"></i>

                    </div>

                    <div>

                        <div className="rr-profile-login-label">
                            Last Login
                        </div>

                        <div className="rr-profile-login-value">

                            {formatDateTime(
                                userData?.last_login_at
                            )}

                        </div>

                    </div>

                </div>

            </section>


            {/* =====================================================
                ACCOUNT SUMMARY
               ===================================================== */}

            <div className="rr-profile-summary-grid">

                <SummaryItem
                    icon="bi-person-badge"
                    label="Account Type"
                    value={
                        userData?.is_super_user
                            ? 'Super Administrator'
                            : getPrimaryRole(
                                userData?.roles
                            )
                    }
                />

                <SummaryItem
                    icon="bi-buildings"
                    label="Organization"
                    value={
                        userData?.is_super_user
                            ? 'Platform Administration'
                            : userData?.company?.company_name
                            ?? 'Not Assigned'
                    }
                />

                <SummaryItem
                    icon="bi-shop"
                    label="Branch Access"
                    value={
                        userData?.is_super_user
                            ? 'All Branches'
                            : `${userData?.stores?.length ?? 0} Assigned`
                    }
                />

                <SummaryItem
                    icon="bi-shield-lock"
                    label="Account Status"
                    value={
                        userData?.status
                            ? 'Active'
                            : 'Inactive'
                    }
                    status={
                        userData?.status
                    }
                />

            </div>


            {/* =====================================================
                PROFILE + ACCESS
               ===================================================== */}

            <div className="rr-profile-main-grid">

                {/* =================================================
                    PROFILE DETAILS
                   ================================================= */}

                <section className="rr-profile-section">

                    <SectionHeader
                        icon="bi-person"
                        title="Profile Details"
                        description="Personal account information"
                    />


                    <form
                        onSubmit={
                            updateProfile
                        }
                    >

                        <div className="rr-profile-section-body">

                            <div className="rr-profile-form-grid">

                                {/* Name */}

                                <ProfileField
                                    label="Full Name"
                                    required
                                    error={
                                        profileForm.errors.name
                                    }
                                >

                                    <div className="rr-profile-input-wrapper">

                                        <i className="bi bi-person"></i>

                                        <input
                                            type="text"
                                            value={
                                                profileForm.data.name
                                            }
                                            onChange={(event) =>
                                                profileForm.setData(
                                                    'name',
                                                    event.target.value
                                                )
                                            }
                                            className={
                                                inputClass(
                                                    profileForm.errors.name
                                                )
                                            }
                                            placeholder="Enter full name"
                                        />

                                    </div>

                                </ProfileField>


                                {/* Email */}

                                <ProfileField
                                    label="Email Address"
                                    required
                                    error={
                                        profileForm.errors.email
                                    }
                                >

                                    <div className="rr-profile-input-wrapper">

                                        <i className="bi bi-envelope"></i>

                                        <input
                                            type="email"
                                            value={
                                                profileForm.data.email
                                            }
                                            onChange={(event) =>
                                                profileForm.setData(
                                                    'email',
                                                    event.target.value
                                                )
                                            }
                                            className={
                                                inputClass(
                                                    profileForm.errors.email
                                                )
                                            }
                                            placeholder="Enter email address"
                                        />

                                    </div>

                                </ProfileField>


                                {/* Mobile */}

                                <ProfileField
                                    label="Mobile Number"
                                    error={
                                        profileForm.errors.mobile
                                    }
                                >

                                    <div className="rr-profile-input-wrapper">

                                        <i className="bi bi-phone"></i>

                                        <input
                                            type="text"
                                            value={
                                                profileForm.data.mobile
                                            }
                                            onChange={(event) =>
                                                profileForm.setData(
                                                    'mobile',
                                                    event.target.value
                                                )
                                            }
                                            className={
                                                inputClass(
                                                    profileForm.errors.mobile
                                                )
                                            }
                                            placeholder="Enter mobile number"
                                        />

                                    </div>

                                </ProfileField>

                            </div>

                        </div>


                        <div className="rr-profile-section-footer">

                            {profileForm.isDirty && (

                                <span className="rr-profile-unsaved">

                                    <i className="bi bi-circle-fill"></i>

                                    Unsaved changes

                                </span>

                            )}


                            <button
                                type="submit"
                                className="rr-profile-primary-button"
                                disabled={
                                    profileForm.processing ||
                                    !profileForm.isDirty
                                }
                            >

                                {profileForm.processing ? (

                                    <>
                                        <span className="spinner-border spinner-border-sm"></span>

                                        Saving...
                                    </>

                                ) : (

                                    <>
                                        <i className="bi bi-check-lg"></i>

                                        Save Changes
                                    </>

                                )}

                            </button>

                        </div>

                    </form>

                </section>


                {/* =================================================
                    ACCOUNT ACCESS
                   ================================================= */}

                <section className="rr-profile-section">

                    <SectionHeader
                        icon="bi-shield-check"
                        title="Account Access"
                        description="Organization, role and branch assignments"
                    />


                    <div className="rr-profile-section-body">

                        {/* Company */}

                        <AccessRow
                            icon="bi-buildings"
                            label="Organization"
                        >

                            {userData?.is_super_user ? (

                                <div>

                                    <div className="rr-profile-access-value">
                                        Platform Administration
                                    </div>

                                    <div className="rr-profile-access-subvalue">
                                        System-wide access
                                    </div>

                                </div>

                            ) : userData?.company ? (

                                <div>

                                    <div className="rr-profile-access-value">

                                        {
                                            userData.company
                                                .company_name
                                        }

                                    </div>

                                    <div className="rr-profile-access-subvalue">

                                        {
                                            userData.company
                                                .company_code
                                        }

                                    </div>

                                </div>

                            ) : (

                                <span className="rr-profile-not-assigned">
                                    Not Assigned
                                </span>

                            )}

                        </AccessRow>


                        {/* Roles */}

                        <AccessRow
                            icon="bi-person-badge"
                            label="Role"
                        >

                            {userData?.is_super_user ? (

                                <span className="rr-profile-role-badge">

                                    <i className="bi bi-shield-fill-check"></i>

                                    Super Administrator

                                </span>

                            ) : userData?.roles?.length > 0 ? (

                                <div className="rr-profile-role-list">

                                    {userData.roles.map(
                                        (role) => (

                                            <span
                                                key={
                                                    role.id
                                                }
                                                className="rr-profile-role-badge"
                                            >

                                                {formatText(
                                                    role.name
                                                )}

                                            </span>

                                        )
                                    )}

                                </div>

                            ) : (

                                <span className="rr-profile-not-assigned">
                                    No Role Assigned
                                </span>

                            )}

                        </AccessRow>


                        {/* Branches */}

                        <div className="rr-profile-branch-section">

                            <div className="rr-profile-branch-heading">

                                <div>

                                    <div className="rr-profile-branch-title">
                                        Branch Access
                                    </div>

                                    <div className="rr-profile-branch-description">
                                        Locations available to this account
                                    </div>

                                </div>


                                {!userData?.is_super_user && (

                                    <span className="rr-profile-count">

                                        {userData?.stores?.length ?? 0}

                                    </span>

                                )}

                            </div>


                            {userData?.is_super_user ? (

                                <div className="rr-profile-global-access">

                                    <div className="rr-profile-global-icon">

                                        <i className="bi bi-globe2"></i>

                                    </div>

                                    <div>

                                        <div className="rr-profile-global-title">
                                            Global Access
                                        </div>

                                        <div className="rr-profile-global-text">
                                            Access to all companies and branches
                                        </div>

                                    </div>

                                </div>

                            ) : userData?.stores?.length > 0 ? (

                                <div className="rr-profile-branch-list">

                                    {userData.stores.map(
                                        (store) => (

                                            <div
                                                key={
                                                    store.id
                                                }
                                                className="rr-profile-branch-item"
                                            >

                                                <div className="rr-profile-branch-icon">

                                                    <i className="bi bi-shop"></i>

                                                </div>


                                                <div className="rr-profile-branch-data">

                                                    <div className="rr-profile-branch-name">

                                                        {
                                                            store.store_name
                                                        }

                                                    </div>

                                                    <div className="rr-profile-branch-code">

                                                        {
                                                            store.store_code
                                                        }

                                                    </div>

                                                </div>


                                                <span
                                                    className={
                                                        store.status
                                                            ? 'rr-profile-mini-status active'
                                                            : 'rr-profile-mini-status inactive'
                                                    }
                                                >

                                                    {store.status
                                                        ? 'Active'
                                                        : 'Inactive'}

                                                </span>

                                            </div>

                                        )
                                    )}

                                </div>

                            ) : (

                                <div className="rr-profile-empty">

                                    <i className="bi bi-shop"></i>

                                    <span>
                                        No branches assigned
                                    </span>

                                </div>

                            )}

                        </div>

                    </div>

                </section>

            </div>


            {/* =====================================================
                SECURITY
               ===================================================== */}

            <section className="rr-profile-section rr-profile-security-section">

                <SectionHeader
                    icon="bi-lock"
                    title="Security"
                    description="Update your account password"
                />


                <form
                    onSubmit={
                        updatePassword
                    }
                >

                    <div className="rr-profile-section-body">

                        <div className="rr-profile-security-message">

                            <div className="rr-profile-security-message-icon">

                                <i className="bi bi-shield-check"></i>

                            </div>

                            <div>

                                <div className="rr-profile-security-message-title">
                                    Password Security
                                </div>

                                <div className="rr-profile-security-message-text">
                                    Use at least 8 characters and avoid reusing passwords from other accounts.
                                </div>

                            </div>

                        </div>


                        <div className="rr-profile-password-grid">

                            <PasswordField
                                label="Current Password"
                                value={
                                    passwordForm.data
                                        .current_password
                                }
                                error={
                                    passwordForm.errors
                                        .current_password
                                }
                                autoComplete="current-password"
                                onChange={(value) =>
                                    passwordForm.setData(
                                        'current_password',
                                        value
                                    )
                                }
                            />


                            <PasswordField
                                label="New Password"
                                value={
                                    passwordForm.data
                                        .password
                                }
                                error={
                                    passwordForm.errors
                                        .password
                                }
                                autoComplete="new-password"
                                onChange={(value) =>
                                    passwordForm.setData(
                                        'password',
                                        value
                                    )
                                }
                            />


                            <PasswordField
                                label="Confirm Password"
                                value={
                                    passwordForm.data
                                        .password_confirmation
                                }
                                error={
                                    passwordForm.errors
                                        .password_confirmation
                                }
                                autoComplete="new-password"
                                onChange={(value) =>
                                    passwordForm.setData(
                                        'password_confirmation',
                                        value
                                    )
                                }
                            />

                        </div>

                    </div>


                    <div className="rr-profile-section-footer">

                        <div className="rr-profile-password-hint">

                            <i className="bi bi-info-circle"></i>

                            Your password is never displayed or stored in activity logs.

                        </div>


                        <button
                            type="submit"
                            className="rr-profile-primary-button"
                            disabled={
                                passwordForm.processing
                            }
                        >

                            {passwordForm.processing ? (

                                <>
                                    <span className="spinner-border spinner-border-sm"></span>

                                    Updating...
                                </>

                            ) : (

                                <>
                                    <i className="bi bi-shield-lock"></i>

                                    Update Password
                                </>

                            )}

                        </button>

                    </div>

                </form>

            </section>

        </SuperAdminLayout>

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

        <div className="rr-profile-section-header">

            <div className="rr-profile-section-heading">

                <div className="rr-profile-section-icon">

                    <i className={`bi ${icon}`}></i>

                </div>

                <div>

                    <div className="rr-profile-section-title">
                        {title}
                    </div>

                    <div className="rr-profile-section-description">
                        {description}
                    </div>

                </div>

            </div>

        </div>

    );
}


/*
|--------------------------------------------------------------------------
| Summary
|--------------------------------------------------------------------------
*/

function SummaryItem({
    icon,
    label,
    value,
    status = null,
}) {

    return (

        <div className="rr-profile-summary-item">

            <div className="rr-profile-summary-icon">

                <i className={`bi ${icon}`}></i>

            </div>

            <div className="rr-profile-summary-data">

                <div className="rr-profile-summary-label">
                    {label}
                </div>

                <div
                    className={
                        status === null
                            ? 'rr-profile-summary-value'
                            : status
                                ? 'rr-profile-summary-value status-active'
                                : 'rr-profile-summary-value status-inactive'
                    }
                >
                    {value}
                </div>

            </div>

        </div>

    );
}


/*
|--------------------------------------------------------------------------
| Form Field
|--------------------------------------------------------------------------
*/

function ProfileField({
    label,
    required = false,
    error,
    children,
}) {

    return (

        <div className="rr-profile-form-group">

            <label className="rr-profile-label">

                {label}

                {required && (
                    <span>*</span>
                )}

            </label>

            {children}

            {error && (

                <div className="rr-profile-error">

                    <i className="bi bi-exclamation-circle"></i>

                    {error}

                </div>

            )}

        </div>

    );
}


/*
|--------------------------------------------------------------------------
| Password Field
|--------------------------------------------------------------------------
*/

function PasswordField({
    label,
    value,
    error,
    onChange,
    autoComplete,
}) {

    const [visible, setVisible] =
        usePasswordVisibility();


    return (

        <ProfileField
            label={label}
            required
            error={error}
        >

            <div className="rr-profile-input-wrapper">

                <i className="bi bi-key"></i>

                <input
                    type={
                        visible
                            ? 'text'
                            : 'password'
                    }
                    value={value}
                    onChange={(event) =>
                        onChange(
                            event.target.value
                        )
                    }
                    className={
                        inputClass(
                            error
                        )
                    }
                    autoComplete={
                        autoComplete
                    }
                />


                <button
                    type="button"
                    className="rr-profile-password-toggle"
                    onClick={
                        setVisible
                    }
                    title={
                        visible
                            ? 'Hide password'
                            : 'Show password'
                    }
                >

                    <i
                        className={
                            visible
                                ? 'bi bi-eye-slash'
                                : 'bi bi-eye'
                        }
                    ></i>

                </button>

            </div>

        </ProfileField>

    );
}


/*
|--------------------------------------------------------------------------
| Access Row
|--------------------------------------------------------------------------
*/

function AccessRow({
    icon,
    label,
    children,
}) {

    return (

        <div className="rr-profile-access-row">

            <div className="rr-profile-access-icon">

                <i className={`bi ${icon}`}></i>

            </div>


            <div className="rr-profile-access-content">

                <div className="rr-profile-access-label">
                    {label}
                </div>

                <div className="rr-profile-access-content-value">
                    {children}
                </div>

            </div>

        </div>

    );
}


/*
|--------------------------------------------------------------------------
| Password Visibility
|--------------------------------------------------------------------------
*/

function usePasswordVisibility() {

    const [visible, setVisible] =
        useState(false);

    return [
        visible,
        () =>
            setVisible(
                (current) => !current
            ),
    ];
}


/*
|--------------------------------------------------------------------------
| Helpers
|--------------------------------------------------------------------------
*/

function inputClass(error) {

    return error
        ? 'rr-profile-input error'
        : 'rr-profile-input';
}


function getInitials(name) {

    if (!name) {
        return 'U';
    }

    const parts =
        String(name)
            .trim()
            .split(/\s+/)
            .filter(Boolean);

    if (parts.length === 1) {

        return parts[0]
            .substring(0, 2)
            .toUpperCase();
    }

    return (
        parts[0][0] +
        parts[
        parts.length - 1
        ][0]
    ).toUpperCase();
}


function getPrimaryRole(roles) {

    if (!roles?.length) {
        return 'No Role Assigned';
    }

    return formatText(
        roles[0].name
    );
}


function formatText(value) {

    return String(value ?? '')
        .replaceAll('-', ' ')
        .replaceAll('_', ' ')
        .replaceAll('.', ' ')
        .replace(
            /\b\w/g,
            (character) =>
                character.toUpperCase()
        );
}


function formatDateTime(value) {

    if (!value) {
        return 'Never';
    }

    return new Intl.DateTimeFormat(
        'en-IN',
        {
            day: '2-digit',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            hour12: true,
        }
    ).format(
        new Date(value)
    );
}