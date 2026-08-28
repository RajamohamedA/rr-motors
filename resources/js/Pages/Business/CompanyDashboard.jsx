import {
    Head,
    Link,
    router,
    usePage,
} from '@inertiajs/react';

import BusinessLayout
    from '../../Layouts/BusinessLayout';

import '../../../css/business/company-dashboard.css';


export default function CompanyDashboard({
    company,
    branchSummary,
    userSummary,
    branches = [],
}) {

    const {
        auth,
    } = usePage().props;


    /*
    |--------------------------------------------------------------------------
    | Enter Branch
    |--------------------------------------------------------------------------
    */

    const enterBranch = (
        storeId
    ) => {

        router.post(
            `/select-branch/${storeId}`,
            {},
            {
                preserveScroll:
                    true,
            }
        );
    };


    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */

    const locationText =
        [
            company?.city,
            company?.state,
        ]
            .filter(Boolean)
            .join(', ');


    return (
        <BusinessLayout>

            <Head title="Company Dashboard" />


            <div className="rr-company-dashboard">


                {/* =========================================================
                    HEADER
                   ========================================================= */}

                <section className="rr-company-dashboard-hero">

                    <div className="rr-company-dashboard-hero-content">

                        <div className="rr-company-dashboard-company-icon">

                            {getInitials(
                                company?.company_name
                            )}

                        </div>


                        <div className="rr-company-dashboard-company-info">

                            <div className="rr-company-dashboard-eyebrow">

                                Company Workspace

                            </div>


                            <h1>

                                {company?.company_name
                                    ?? 'Company'}

                            </h1>


                            <div className="rr-company-dashboard-meta">

                                {company?.company_code && (

                                    <span>

                                        <i className="bi bi-upc-scan"></i>

                                        {
                                            company.company_code
                                        }

                                    </span>
                                )}


                                {company?.owner_name && (

                                    <span>

                                        <i className="bi bi-person"></i>

                                        {
                                            company.owner_name
                                        }

                                    </span>
                                )}


                                {locationText && (

                                    <span>

                                        <i className="bi bi-geo-alt"></i>

                                        {
                                            locationText
                                        }

                                    </span>
                                )}

                            </div>

                        </div>

                    </div>


                    <div className="rr-company-dashboard-actions">

                        <Link
                            href="/company/profile"
                            className="rr-company-btn rr-company-btn-secondary"
                        >

                            <i className="bi bi-building-gear"></i>

                            Company Profile

                        </Link>


                        <Link
                            href="/company/branches/create"
                            className="rr-company-btn rr-company-btn-primary"
                        >

                            <i className="bi bi-plus-circle"></i>

                            Add Branch

                        </Link>

                    </div>

                </section>


                {/* =========================================================
                    SUMMARY
                   ========================================================= */}

                <section className="rr-company-dashboard-summary-grid">


                    <SummaryCard
                        icon="bi-shop-window"
                        label="Total Branches"
                        value={
                            branchSummary?.total
                            ?? 0
                        }
                    />


                    <SummaryCard
                        icon="bi-check-circle"
                        label="Active Branches"
                        value={
                            branchSummary?.active
                            ?? 0
                        }
                        tone="success"
                    />


                    <SummaryCard
                        icon="bi-hourglass-split"
                        label="Pending Approval"
                        value={
                            branchSummary?.pending
                            ?? 0
                        }
                        tone="warning"
                    />


                    <SummaryCard
                        icon="bi-people"
                        label="Company Users"
                        value={
                            userSummary?.total
                            ?? 0
                        }
                    />


                    <SummaryCard
                        icon="bi-person-check"
                        label="Active Users"
                        value={
                            userSummary?.active
                            ?? 0
                        }
                        tone="success"
                    />

                </section>


                {/* =========================================================
                    NO ACTIVE BRANCH ALERT
                   ========================================================= */}

                {(
                    branchSummary?.active
                    ?? 0
                ) === 0 && (

                        <section className="rr-company-empty-alert">

                            <div className="rr-company-empty-alert-icon">

                                <i className="bi bi-shop-window"></i>

                            </div>


                            <div>

                                <h2>
                                    No active branch available
                                </h2>


                                <p>

                                    You can create a new branch.
                                    The branch will remain pending
                                    until Super Admin activates it.

                                </p>

                            </div>


                            <Link
                                href="/company/branches/create"
                                className="rr-company-btn rr-company-btn-primary"
                            >

                                <i className="bi bi-plus-lg"></i>

                                Create Branch

                            </Link>

                        </section>
                    )}


                {/* =========================================================
                    MAIN GRID
                   ========================================================= */}

                <div className="rr-company-dashboard-main-grid">


                    {/* =====================================================
                        BRANCHES
                       ===================================================== */}

                    <section className="rr-company-dashboard-panel rr-company-dashboard-branches-panel">


                        <div className="rr-company-dashboard-panel-header">

                            <div>

                                <span className="rr-company-dashboard-section-label">

                                    Branch Management

                                </span>


                                <h2>

                                    Your Branches

                                </h2>


                                <p>

                                    Enter an active branch to access
                                    sales, purchase, inventory and
                                    other branch operations.

                                </p>

                            </div>


                            <Link
                                href="/company/branches"
                                className="rr-company-text-link"
                            >

                                View All

                                <i className="bi bi-arrow-right"></i>

                            </Link>

                        </div>


                        {branches.length > 0 ? (

                            <div className="rr-company-branch-list">

                                {branches.map(
                                    (
                                        branch
                                    ) => (

                                        <BranchCard
                                            key={
                                                branch.id
                                            }

                                            branch={
                                                branch
                                            }

                                            onEnter={
                                                enterBranch
                                            }
                                        />

                                    )
                                )}

                            </div>

                        ) : (

                            <div className="rr-company-no-data">

                                <div className="rr-company-no-data-icon">

                                    <i className="bi bi-building-add"></i>

                                </div>


                                <h3>
                                    No branches created yet
                                </h3>


                                <p>

                                    Create your first branch to
                                    start using the business workspace.

                                </p>


                                <Link
                                    href="/company/branches/create"
                                    className="rr-company-btn rr-company-btn-primary"
                                >

                                    <i className="bi bi-plus-circle"></i>

                                    Add First Branch

                                </Link>

                            </div>
                        )}

                    </section>


                    {/* =====================================================
                        QUICK ACCESS
                       ===================================================== */}

                    <aside className="rr-company-dashboard-side-column">


                        <section className="rr-company-dashboard-panel">

                            <div className="rr-company-dashboard-panel-header">

                                <div>

                                    <span className="rr-company-dashboard-section-label">

                                        Administration

                                    </span>


                                    <h2>

                                        Company Management

                                    </h2>

                                </div>

                            </div>


                            <div className="rr-company-quick-links">

                                <QuickLink
                                    href="/company/profile"
                                    icon="bi-building-gear"
                                    title="Company Profile"
                                    description="Update company information"
                                />


                                <QuickLink
                                    href="/company/branches"
                                    icon="bi-shop-window"
                                    title="Branches"
                                    description="Manage company branches"
                                />


                                <QuickLink
                                    href="/company/branches/create"
                                    icon="bi-building-add"
                                    title="Add Branch"
                                    description="Create a new business branch"
                                />


                                <QuickLink
                                    href="/company/users"
                                    icon="bi-people"
                                    title="Users & Access"
                                    description="Manage company users"
                                />


                                <QuickLink
                                    href="/company/users/create"
                                    icon="bi-person-plus"
                                    title="Add User"
                                    description="Create a company user"
                                />

                            </div>

                        </section>


                        {/* =================================================
                            USER
                           ================================================= */}

                        <section className="rr-company-dashboard-panel">

                            <div className="rr-company-account-card">

                                <div className="rr-company-account-avatar">

                                    {getInitials(
                                        auth?.user?.name
                                    )}

                                </div>


                                <div>

                                    <span>
                                        Signed in as
                                    </span>


                                    <strong>

                                        {
                                            auth?.user?.name
                                            ?? 'Company Admin'
                                        }

                                    </strong>


                                    <small>
                                        Company Admin
                                    </small>

                                </div>

                            </div>

                        </section>

                    </aside>

                </div>

            </div>

        </BusinessLayout>
    );
}


/*
|--------------------------------------------------------------------------
| Summary Card
|--------------------------------------------------------------------------
*/

function SummaryCard({
    icon,
    label,
    value,
    tone = 'default',
}) {

    return (

        <div
            className={
                `rr-company-summary-card ` +
                `rr-company-summary-card-${tone}`
            }
        >

            <div className="rr-company-summary-icon">

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
| Branch Card
|--------------------------------------------------------------------------
*/

function BranchCard({
    branch,
    onEnter,
}) {

    const active =
        Boolean(
            branch?.status
        );


    const branchLocation =
        [
            branch?.city,
            branch?.state,
        ]
            .filter(Boolean)
            .join(', ');


    return (

        <article className="rr-company-branch-card">


            <div className="rr-company-branch-card-main">

                <div className="rr-company-branch-icon">

                    <i className="bi bi-shop"></i>

                </div>


                <div className="rr-company-branch-info">

                    <div className="rr-company-branch-title-row">

                        <h3>

                            {
                                branch?.store_name
                                ?? 'Branch'
                            }

                        </h3>


                        <span
                            className={
                                active
                                    ? 'rr-company-status rr-company-status-active'
                                    : 'rr-company-status rr-company-status-pending'
                            }
                        >

                            <i
                                className={
                                    active
                                        ? 'bi bi-check-circle-fill'
                                        : 'bi bi-clock-fill'
                                }
                            ></i>


                            {
                                branch?.status_label
                                ?? (
                                    active
                                        ? 'Active'
                                        : 'Pending Approval'
                                )
                            }

                        </span>

                    </div>


                    <div className="rr-company-branch-meta">

                        {branch?.store_code && (

                            <span>

                                <i className="bi bi-upc-scan"></i>

                                {
                                    branch.store_code
                                }

                            </span>
                        )}


                        {branchLocation && (

                            <span>

                                <i className="bi bi-geo-alt"></i>

                                {
                                    branchLocation
                                }

                            </span>
                        )}


                        {branch?.created_at && (

                            <span>

                                <i className="bi bi-calendar3"></i>

                                Created {
                                    branch.created_at
                                }

                            </span>
                        )}

                    </div>

                </div>

            </div>


            <div className="rr-company-branch-actions">

                {active ? (

                    <button
                        type="button"
                        className="rr-company-btn rr-company-btn-enter"
                        onClick={
                            () =>
                                onEnter(
                                    branch.id
                                )
                        }
                    >

                        Enter Branch

                        <i className="bi bi-arrow-right"></i>

                    </button>

                ) : (

                    <span className="rr-company-pending-message">

                        <i className="bi bi-shield-lock"></i>

                        Waiting for Super Admin approval

                    </span>

                )}

            </div>

        </article>
    );
}


/*
|--------------------------------------------------------------------------
| Quick Link
|--------------------------------------------------------------------------
*/

function QuickLink({
    href,
    icon,
    title,
    description,
}) {

    return (

        <Link
            href={href}
            className="rr-company-quick-link"
        >

            <div className="rr-company-quick-link-icon">

                <i className={`bi ${icon}`}></i>

            </div>


            <div>

                <strong>
                    {title}
                </strong>


                <span>
                    {description}
                </span>

            </div>


            <i className="bi bi-chevron-right rr-company-quick-arrow"></i>

        </Link>
    );
}


/*
|--------------------------------------------------------------------------
| Initials
|--------------------------------------------------------------------------
*/

function getInitials(
    value
) {

    if (!value) {
        return 'CO';
    }


    return value
        .trim()
        .split(/\s+/)
        .slice(0, 2)
        .map(
            word =>
                word
                    .charAt(0)
                    .toUpperCase()
        )
        .join('');
}