import {
    Head,
    Link,
} from '@inertiajs/react';

import {
    useState,
} from 'react';


const slidingFeatures = [

    {
        icon: 'bi-box-seam',
        title: 'Real-Time Inventory',
        text: 'Track product stock by company, branch, shop and godown.',
    },

    {
        icon: 'bi-arrow-left-right',
        title: 'Quick Stock Transfer',
        text: 'Move stock between shop and godown with controlled transactions.',
    },

    {
        icon: 'bi-cart-check',
        title: 'Purchase Management',
        text: 'Manage suppliers, purchases, returns and incoming inventory.',
    },

    {
        icon: 'bi-receipt',
        title: 'Sales & Billing',
        text: 'Fast billing with GST, discounts, customers and stock control.',
    },

    {
        icon: 'bi-arrow-counterclockwise',
        title: 'Sales Returns',
        text: 'Process invoice-based returns while maintaining accurate stock.',
    },

    {
        icon: 'bi-buildings',
        title: 'Multi Branch',
        text: 'Operate multiple branches under one company with isolated data.',
    },

    {
        icon: 'bi-house-gear',
        title: 'Godown Management',
        text: 'Maintain separate shop and godown inventory balances.',
    },

    {
        icon: 'bi-shield-check',
        title: 'Roles & Permissions',
        text: 'Control what every owner, manager and employee can access.',
    },

    {
        icon: 'bi-clock-history',
        title: 'Activity Tracking',
        text: 'Audit important administrative and security changes.',
    },

    {
        icon: 'bi-graph-up-arrow',
        title: 'Business Reports',
        text: 'Review sales, purchase, stock and operational reports.',
    },

];


export default function Welcome() {

    const [
        mobileMenuOpen,
        setMobileMenuOpen,
    ] = useState(false);

    return (

        <>
            <Head title="RR ERP - Business Management Platform" />


            <div className="rr-home">

                {/* =====================================================
                    NAVBAR
                   ===================================================== */}

                <header className="rr-home-navbar">

                    <Link
                        href="/"
                        className="rr-home-brand"
                        onClick={() =>
                            setMobileMenuOpen(false)
                        }
                    >

                        <div className="rr-home-brand-mark">
                            <span>
                                RR
                            </span>
                        </div>


                        <div className="rr-home-brand-content">

                            <div className="rr-home-brand-name">
                                RR ERP
                            </div>

                            <div className="rr-home-brand-caption">
                                Business Management Platform
                            </div>

                        </div>

                    </Link>


                    {/* Desktop Menu */}

                    <nav className="rr-home-nav">

                        <a href="#features">
                            Features
                        </a>

                        <a href="#modules">
                            Modules
                        </a>

                        <a href="#multi-branch">
                            Multi Branch
                        </a>

                        <a href="#security">
                            Security
                        </a>

                    </nav>


                    {/* Desktop Actions */}

                    <div className="rr-home-navbar-actions">

                        <Link
                            href="/login"
                            className="rr-home-login-link"
                        >
                            Sign In
                        </Link>


                        <Link
                            href="/register-business"
                            className="rr-home-register-btn"
                        >

                            Register Business

                            <i className="bi bi-arrow-right"></i>

                        </Link>


                        {/* Mobile Toggle */}

                        <button
                            type="button"
                            className={
                                `rr-home-mobile-toggle ${mobileMenuOpen
                                    ? 'open'
                                    : ''
                                }`
                            }
                            onClick={() =>
                                setMobileMenuOpen(
                                    (current) =>
                                        !current
                                )
                            }
                            aria-label="Toggle navigation"
                        >

                            <span></span>
                            <span></span>
                            <span></span>

                        </button>

                    </div>


                    {/* Mobile Menu */}

                    <div
                        className={
                            `rr-home-mobile-menu ${mobileMenuOpen
                                ? 'open'
                                : ''
                            }`
                        }
                    >

                        <div className="rr-home-mobile-menu-inner">

                            <a
                                href="#features"
                                onClick={() =>
                                    setMobileMenuOpen(false)
                                }
                            >

                                <i className="bi bi-stars"></i>

                                Features

                            </a>


                            <a
                                href="#modules"
                                onClick={() =>
                                    setMobileMenuOpen(false)
                                }
                            >

                                <i className="bi bi-grid"></i>

                                Modules

                            </a>


                            <a
                                href="#multi-branch"
                                onClick={() =>
                                    setMobileMenuOpen(false)
                                }
                            >

                                <i className="bi bi-buildings"></i>

                                Multi Branch

                            </a>


                            <a
                                href="#security"
                                onClick={() =>
                                    setMobileMenuOpen(false)
                                }
                            >

                                <i className="bi bi-shield-check"></i>

                                Security

                            </a>


                            <div className="rr-home-mobile-menu-divider"></div>


                            <Link
                                href="/login"
                                className="rr-home-mobile-login"
                                onClick={() =>
                                    setMobileMenuOpen(false)
                                }
                            >

                                <i className="bi bi-box-arrow-in-right"></i>

                                Sign In

                            </Link>


                            <Link
                                href="/register-business"
                                className="rr-home-mobile-register"
                                onClick={() =>
                                    setMobileMenuOpen(false)
                                }
                            >

                                Register Your Business

                                <i className="bi bi-arrow-right"></i>

                            </Link>

                        </div>

                    </div>

                </header>


                <main>

                    {/* =====================================================
                        HERO
                       ===================================================== */}

                    <section className="rr-home-hero">

                        <div className="rr-home-hero-glow rr-home-glow-left"></div>

                        <div className="rr-home-hero-glow rr-home-glow-right"></div>


                        {/* LEFT */}

                        <div className="rr-home-hero-content">

                            <div className="rr-home-badge">

                                <span className="rr-home-badge-dot"></span>

                                Modern ERP for Growing Businesses

                            </div>


                            <h1 className="rr-home-hero-title">

                                One ERP.

                                <span>
                                    Every Branch.
                                </span>

                                Complete Control.

                            </h1>


                            <p className="rr-home-hero-description">

                                RR ERP brings inventory, purchases, sales,
                                stock transfers, customers, suppliers,
                                accounts, users and reports together in
                                one secure business platform.

                            </p>


                            <div className="rr-home-hero-actions">

                                <Link
                                    href="/register-business"
                                    className="rr-home-primary-btn"
                                >

                                    Start Your Business

                                    <i className="bi bi-arrow-right"></i>

                                </Link>


                                <Link
                                    href="/login"
                                    className="rr-home-secondary-btn"
                                >

                                    <i className="bi bi-box-arrow-in-right"></i>

                                    Sign In

                                </Link>

                            </div>


                            <div className="rr-home-trust-row">

                                <TrustItem text="Multi Company" />

                                <TrustItem text="Multi Branch" />

                                <TrustItem text="Godown Stock" />

                                <TrustItem text="Secure Access" />

                            </div>

                        </div>


                        {/* RIGHT — ERP PREVIEW */}

                        <div className="rr-home-product-preview">

                            <div className="rr-preview-window">

                                {/* WINDOW HEADER */}

                                <div className="rr-preview-window-header">

                                    <div className="rr-preview-window-dots">

                                        <span></span>
                                        <span></span>
                                        <span></span>

                                    </div>


                                    <div className="rr-preview-window-title">

                                        RR ERP

                                    </div>


                                    <div className="rr-preview-window-status">

                                        <span></span>

                                        Online

                                    </div>

                                </div>


                                {/* APP BODY */}

                                <div className="rr-preview-app">

                                    {/* SIDEBAR */}

                                    <div className="rr-preview-sidebar">

                                        <div className="rr-preview-sidebar-logo">
                                            RR
                                        </div>


                                        <PreviewMenu
                                            icon="bi-grid"
                                            active
                                        />

                                        <PreviewMenu
                                            icon="bi-buildings"
                                        />

                                        <PreviewMenu
                                            icon="bi-box-seam"
                                        />

                                        <PreviewMenu
                                            icon="bi-cart-check"
                                        />

                                        <PreviewMenu
                                            icon="bi-receipt"
                                        />

                                        <PreviewMenu
                                            icon="bi-graph-up-arrow"
                                        />

                                    </div>


                                    {/* WORKSPACE */}

                                    <div className="rr-preview-workspace">

                                        {/* TOP BAR */}

                                        <div className="rr-preview-topbar">

                                            <div>

                                                <div className="rr-preview-heading-line"></div>

                                                <div className="rr-preview-heading-line small"></div>

                                            </div>


                                            <div className="rr-preview-user">

                                                <div className="rr-preview-user-dot"></div>

                                                R

                                            </div>

                                        </div>


                                        {/* STATS */}

                                        <div className="rr-preview-stats">

                                            <PreviewStat
                                                icon="bi-buildings"
                                                label="Companies"
                                                value="04"
                                            />

                                            <PreviewStat
                                                icon="bi-shop"
                                                label="Branches"
                                                value="12"
                                            />

                                            <PreviewStat
                                                icon="bi-box-seam"
                                                label="Products"
                                                value="2.8K"
                                            />

                                        </div>


                                        {/* CONTENT */}

                                        <div className="rr-preview-content-grid">

                                            {/* CHART */}

                                            <div className="rr-preview-card rr-preview-chart-card">

                                                <div className="rr-preview-card-heading">

                                                    <div>

                                                        <strong>
                                                            Business Overview
                                                        </strong>

                                                        <span>
                                                            Monthly activity
                                                        </span>

                                                    </div>


                                                    <span className="rr-preview-period">
                                                        30 Days
                                                    </span>

                                                </div>


                                                <div className="rr-preview-chart">

                                                    {[

                                                        41,
                                                        61,
                                                        49,
                                                        76,
                                                        59,
                                                        88,
                                                        72,
                                                        96,
                                                        68,

                                                    ].map(
                                                        (
                                                            height,
                                                            index
                                                        ) => (

                                                            <div
                                                                key={
                                                                    index
                                                                }
                                                                className="rr-preview-chart-column"
                                                            >

                                                                <span
                                                                    style={{
                                                                        height:
                                                                            `${height}%`,
                                                                    }}
                                                                ></span>

                                                            </div>

                                                        )
                                                    )}

                                                </div>

                                            </div>


                                            {/* ACTIVITY */}

                                            <div className="rr-preview-card">

                                                <div className="rr-preview-card-heading">

                                                    <div>

                                                        <strong>
                                                            Recent Activity
                                                        </strong>

                                                        <span>
                                                            Live updates
                                                        </span>

                                                    </div>

                                                </div>


                                                <PreviewActivity
                                                    icon="bi-receipt"
                                                    title="New Sale"
                                                />

                                                <PreviewActivity
                                                    icon="bi-box-seam"
                                                    title="Stock Updated"
                                                />

                                                <PreviewActivity
                                                    icon="bi-cart-check"
                                                    title="Purchase Added"
                                                />

                                                <PreviewActivity
                                                    icon="bi-arrow-left-right"
                                                    title="Stock Transfer"
                                                />

                                            </div>

                                        </div>

                                    </div>

                                </div>

                            </div>


                            {/* FLOATING CARDS */}

                            <div className="rr-preview-floating-card rr-preview-floating-stock">

                                <div className="rr-preview-floating-icon">

                                    <i className="bi bi-box-seam"></i>

                                </div>

                                <div>

                                    <span>
                                        Stock Control
                                    </span>

                                    <strong>
                                        Live Inventory
                                    </strong>

                                </div>

                            </div>


                            <div className="rr-preview-floating-card rr-preview-floating-security">

                                <div className="rr-preview-floating-icon success">

                                    <i className="bi bi-shield-check"></i>

                                </div>

                                <div>

                                    <span>
                                        Security
                                    </span>

                                    <strong>
                                        Role Protected
                                    </strong>

                                </div>

                            </div>

                        </div>

                    </section>


                    {/* =====================================================
                        FEATURE SLIDER
                       ===================================================== */}

                    <section
                        id="features"
                        className="rr-home-slider-section"
                    >

                        <div className="rr-home-section-heading">

                            <div className="rr-home-section-label">

                                RR ERP FEATURES

                            </div>


                            <h2>

                                Powerful tools for daily business operations

                            </h2>


                            <p>

                                Everything required to manage stock,
                                transactions, locations and business users
                                from one connected system.

                            </p>

                        </div>


                        <div className="rr-feature-slider">

                            <div className="rr-feature-slider-fade left"></div>

                            <div className="rr-feature-slider-fade right"></div>


                            <div className="rr-feature-track">

                                {[
                                    ...slidingFeatures,
                                    ...slidingFeatures,
                                ].map(
                                    (
                                        feature,
                                        index
                                    ) => (

                                        <FeatureSlide
                                            key={
                                                `${feature.title}-${index}`
                                            }
                                            feature={
                                                feature
                                            }
                                        />

                                    )
                                )}

                            </div>

                        </div>

                    </section>


                    {/* =====================================================
                        MODULES
                       ===================================================== */}

                    <section
                        id="modules"
                        className="rr-home-modules-section"
                    >

                        <div className="rr-home-modules-container">

                            <div className="rr-home-modules-intro">

                                <div className="rr-home-section-label">
                                    ONE CONNECTED PLATFORM
                                </div>


                                <h2>

                                    Every important business module
                                    works together.

                                </h2>


                                <p>

                                    Enter data once and let RR ERP maintain
                                    connected stock, transaction and
                                    operational records throughout your
                                    business.

                                </p>


                                <Link
                                    href="/register-business"
                                    className="rr-home-text-link"
                                >

                                    Create business workspace

                                    <i className="bi bi-arrow-right"></i>

                                </Link>

                            </div>


                            <div className="rr-home-module-grid">

                                <ModuleCard
                                    icon="bi-grid"
                                    number="01"
                                    title="Masters"
                                    text="Products, categories, brands, units, suppliers and customers."
                                />

                                <ModuleCard
                                    icon="bi-cart-check"
                                    number="02"
                                    title="Purchase"
                                    text="Purchases, purchase returns and supplier stock movement."
                                />

                                <ModuleCard
                                    icon="bi-receipt"
                                    number="03"
                                    title="Sales"
                                    text="Invoices, quotations, returns and customer payments."
                                />

                                <ModuleCard
                                    icon="bi-box-seam"
                                    number="04"
                                    title="Inventory"
                                    text="Stock ledger, adjustments, godowns and stock transfers."
                                />

                                <ModuleCard
                                    icon="bi-wallet2"
                                    number="05"
                                    title="Accounts"
                                    text="Customer payments and outstanding transaction tracking."
                                />

                                <ModuleCard
                                    icon="bi-bar-chart"
                                    number="06"
                                    title="Reports"
                                    text="Purchase, sales, inventory and operational reporting."
                                />

                            </div>

                        </div>

                    </section>


                    {/* =====================================================
                        MULTI BRANCH
                       ===================================================== */}

                    <section
                        id="multi-branch"
                        className="rr-home-branch-section"
                    >

                        <div className="rr-home-branch-content">

                            <div className="rr-home-section-label">
                                MULTI-BRANCH CONTROL
                            </div>


                            <h2>

                                Your company, branches and godowns —
                                clearly separated.

                            </h2>


                            <p>

                                RR ERP keeps company and branch operations
                                properly isolated while giving authorized
                                owners complete control over their business.

                            </p>


                            <div className="rr-home-check-list">

                                <CheckItem text="Separate branch transactions" />

                                <CheckItem text="Shop and godown stock balances" />

                                <CheckItem text="Branch-specific user access" />

                                <CheckItem text="Controlled stock transfers" />

                            </div>

                        </div>


                        {/* ORGANIZATION TREE */}

                        <div className="rr-org-chart">

                            <div className="rr-org-company">

                                <div className="rr-org-node-icon">

                                    <i className="bi bi-buildings"></i>

                                </div>

                                <div>

                                    <span>
                                        COMPANY
                                    </span>

                                    <strong>
                                        RR Business
                                    </strong>

                                </div>

                            </div>


                            <div className="rr-org-line-main"></div>


                            <div className="rr-org-branches">

                                <OrganizationBranch
                                    name="Main Branch"
                                    code="B001"
                                />

                                <OrganizationBranch
                                    name="Branch 02"
                                    code="B002"
                                />

                            </div>

                        </div>

                    </section>


                    {/* =====================================================
                        SECURITY
                       ===================================================== */}

                    <section
                        id="security"
                        className="rr-home-security-section"
                    >

                        <div className="rr-home-security-card">

                            <div className="rr-home-security-visual">

                                <div className="rr-security-shield">

                                    <i className="bi bi-shield-check"></i>

                                </div>


                                <SecurityRole
                                    icon="bi-person-badge"
                                    title="Company Owner"
                                    text="Full company control"
                                />

                                <SecurityRole
                                    icon="bi-person-workspace"
                                    title="Branch Manager"
                                    text="Assigned branch access"
                                />

                                <SecurityRole
                                    icon="bi-person"
                                    title="Staff User"
                                    text="Permission based access"
                                />

                            </div>


                            <div className="rr-home-security-content">

                                <div className="rr-home-section-label">
                                    SECURITY & ACCESS
                                </div>


                                <h2>

                                    Give every user exactly the access
                                    they need.

                                </h2>


                                <p>

                                    RR ERP uses role and permission based
                                    access so business owners can control
                                    who can view, create, edit or manage
                                    sensitive operations.

                                </p>


                                <div className="rr-home-security-points">

                                    <SecurityPoint
                                        icon="bi-person-lock"
                                        title="Role Based Access"
                                        text="Assign responsibilities based on each employee's role."
                                    />

                                    <SecurityPoint
                                        icon="bi-shop"
                                        title="Branch Restrictions"
                                        text="Users access only branches they are authorized to use."
                                    />

                                    <SecurityPoint
                                        icon="bi-clock-history"
                                        title="Activity Logs"
                                        text="Review important administrative and security changes."
                                    />

                                </div>

                            </div>

                        </div>

                    </section>


                    {/* =====================================================
                        WHY RR ERP
                       ===================================================== */}

                    <section className="rr-home-benefits-section">

                        <div className="rr-home-section-heading">

                            <div className="rr-home-section-label">
                                WHY RR ERP
                            </div>

                            <h2>
                                Built for practical business management
                            </h2>

                            <p>
                                Designed to stay simple for daily users
                                while giving owners the control they need.
                            </p>

                        </div>


                        <div className="rr-home-benefit-grid">

                            <BenefitCard
                                number="01"
                                title="Easy to operate"
                                text="A clean interface designed for daily billing and inventory operations."
                            />

                            <BenefitCard
                                number="02"
                                title="Accurate stock"
                                text="Stock balances are maintained by company, branch and godown location."
                            />

                            <BenefitCard
                                number="03"
                                title="Controlled access"
                                text="Owners decide what each employee can access inside the ERP."
                            />

                            <BenefitCard
                                number="04"
                                title="Ready to grow"
                                text="Start from one branch and expand your organization when required."
                            />

                        </div>

                    </section>


                    {/* =====================================================
                        FINAL CTA
                       ===================================================== */}

                    <section className="rr-home-final-cta">

                        <div className="rr-home-final-glow"></div>


                        <div className="rr-home-final-content">

                            <div className="rr-home-final-badge">

                                START WITH RR ERP

                            </div>


                            <h2>

                                Bring your entire business into
                                one organized workspace.

                            </h2>


                            <p>

                                Create your business, set up your main
                                branch and start managing operations
                                with RR ERP.

                            </p>


                            <div className="rr-home-final-actions">

                                <Link
                                    href="/register-business"
                                    className="rr-home-primary-btn"
                                >

                                    Register Your Business

                                    <i className="bi bi-arrow-right"></i>

                                </Link>


                                <Link
                                    href="/login"
                                    className="rr-home-final-login"
                                >

                                    Already registered? Sign in

                                </Link>

                            </div>

                        </div>

                    </section>

                </main>


                {/* =====================================================
                    FOOTER
                   ===================================================== */}

                <footer className="rr-home-footer">

                    <div className="rr-home-footer-main">

                        <div className="rr-home-brand">

                            <div className="rr-home-brand-mark">
                                RR
                            </div>

                            <div>

                                <div className="rr-home-brand-name">
                                    RR ERP
                                </div>

                                <div className="rr-home-brand-caption">
                                    Business Management Platform
                                </div>

                            </div>

                        </div>


                        <div className="rr-home-footer-links">

                            <a href="#features">
                                Features
                            </a>

                            <a href="#modules">
                                Modules
                            </a>

                            <a href="#security">
                                Security
                            </a>

                            <Link href="/login">
                                Sign In
                            </Link>

                        </div>

                    </div>


                    <div className="rr-home-footer-bottom">

                        <span>

                            © {new Date().getFullYear()} RR ERP.
                            All rights reserved.

                        </span>


                        <span>

                            Business Management Platform

                        </span>

                    </div>

                </footer>

            </div>

        </>
    );
}


/*
|--------------------------------------------------------------------------
| Trust Item
|--------------------------------------------------------------------------
*/

function TrustItem({
    text,
}) {

    return (

        <div className="rr-home-trust-item">

            <i className="bi bi-check-circle-fill"></i>

            {text}

        </div>
    );
}


/*
|--------------------------------------------------------------------------
| Preview Sidebar Menu
|--------------------------------------------------------------------------
*/

function PreviewMenu({
    icon,
    active = false,
}) {

    return (

        <div
            className={
                `rr-preview-menu ${active
                    ? 'active'
                    : ''
                }`
            }
        >

            <i className={`bi ${icon}`}></i>

        </div>
    );
}


/*
|--------------------------------------------------------------------------
| Preview Stat
|--------------------------------------------------------------------------
*/

function PreviewStat({
    icon,
    label,
    value,
}) {

    return (

        <div className="rr-preview-stat">

            <div className="rr-preview-stat-icon">

                <i className={`bi ${icon}`}></i>

            </div>


            <div className="rr-preview-stat-content">

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
| Preview Activity
|--------------------------------------------------------------------------
*/

function PreviewActivity({
    icon,
    title,
}) {

    return (

        <div className="rr-preview-activity-row">

            <div className="rr-preview-activity-icon">

                <i className={`bi ${icon}`}></i>

            </div>


            <div className="rr-preview-activity-content">

                <strong>
                    {title}
                </strong>

                <span>
                    Just now
                </span>

            </div>


            <i className="bi bi-chevron-right"></i>

        </div>
    );
}


/*
|--------------------------------------------------------------------------
| Feature Slide
|--------------------------------------------------------------------------
*/

function FeatureSlide({
    feature,
}) {

    return (

        <div className="rr-feature-slide">

            <div className="rr-feature-slide-icon">

                <i className={`bi ${feature.icon}`}></i>

            </div>


            <div>

                <h3>
                    {feature.title}
                </h3>

                <p>
                    {feature.text}
                </p>

            </div>

        </div>
    );
}


/*
|--------------------------------------------------------------------------
| Module Card
|--------------------------------------------------------------------------
*/

function ModuleCard({
    icon,
    number,
    title,
    text,
}) {

    return (

        <div className="rr-home-module-card">

            <div className="rr-home-module-card-top">

                <div className="rr-home-module-card-icon">

                    <i className={`bi ${icon}`}></i>

                </div>


                <span>
                    {number}
                </span>

            </div>


            <h3>
                {title}
            </h3>


            <p>
                {text}
            </p>


            <div className="rr-home-module-arrow">

                <i className="bi bi-arrow-up-right"></i>

            </div>

        </div>
    );
}


/*
|--------------------------------------------------------------------------
| Check Item
|--------------------------------------------------------------------------
*/

function CheckItem({
    text,
}) {

    return (

        <div className="rr-home-check-item">

            <span>

                <i className="bi bi-check-lg"></i>

            </span>

            {text}

        </div>
    );
}


/*
|--------------------------------------------------------------------------
| Organization Branch
|--------------------------------------------------------------------------
*/

function OrganizationBranch({
    name,
    code,
}) {

    return (

        <div className="rr-org-branch-wrapper">

            <div className="rr-org-branch-line"></div>


            <div className="rr-org-branch">

                <div className="rr-org-node-icon branch">

                    <i className="bi bi-shop"></i>

                </div>

                <div>

                    <span>
                        {code}
                    </span>

                    <strong>
                        {name}
                    </strong>

                </div>

            </div>


            <div className="rr-org-location-row">

                <div className="rr-org-location">

                    <i className="bi bi-shop-window"></i>

                    <span>
                        Shop
                    </span>

                </div>


                <div className="rr-org-location">

                    <i className="bi bi-house-gear"></i>

                    <span>
                        Godown
                    </span>

                </div>

            </div>

        </div>
    );
}


/*
|--------------------------------------------------------------------------
| Security Role
|--------------------------------------------------------------------------
*/

function SecurityRole({
    icon,
    title,
    text,
}) {

    return (

        <div className="rr-security-role">

            <div className="rr-security-role-icon">

                <i className={`bi ${icon}`}></i>

            </div>


            <div>

                <strong>
                    {title}
                </strong>

                <span>
                    {text}
                </span>

            </div>


            <i className="bi bi-check-circle-fill"></i>

        </div>
    );
}


/*
|--------------------------------------------------------------------------
| Security Point
|--------------------------------------------------------------------------
*/

function SecurityPoint({
    icon,
    title,
    text,
}) {

    return (

        <div className="rr-security-point">

            <div className="rr-security-point-icon">

                <i className={`bi ${icon}`}></i>

            </div>


            <div>

                <strong>
                    {title}
                </strong>

                <p>
                    {text}
                </p>

            </div>

        </div>
    );
}


/*
|--------------------------------------------------------------------------
| Benefit Card
|--------------------------------------------------------------------------
*/

function BenefitCard({
    number,
    title,
    text,
}) {

    return (

        <div className="rr-home-benefit-card">

            <div className="rr-home-benefit-number">

                {number}

            </div>


            <h3>
                {title}
            </h3>


            <p>
                {text}
            </p>

        </div>
    );
}