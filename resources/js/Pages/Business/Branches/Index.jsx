import {
    Head,
    Link,
    router,
    useForm,
} from '@inertiajs/react';

import BusinessLayout
    from '../../../Layouts/BusinessLayout';

import PageShell
    from '../../../Components/Business/PageShell';


export default function BranchIndex({
    company,
    branches,
    summary,
    filters,
    activeStoreId,
}) {

    /*
    |--------------------------------------------------------------------------
    | Search Form
    |--------------------------------------------------------------------------
    */

    const {
        data,
        setData,
    } = useForm({
        search:
            filters?.search
            ?? '',
    });


    /*
    |--------------------------------------------------------------------------
    | Branch Collection
    |--------------------------------------------------------------------------
    */

    const branchItems =
        branches?.data
        ?? [];


    /*
    |--------------------------------------------------------------------------
    | Search
    |--------------------------------------------------------------------------
    */

    const submitSearch =
        (event) => {

            event.preventDefault();


            router.get(
                '/company/branches',
                {
                    search:
                        data.search,
                },
                {
                    preserveState:
                        true,

                    preserveScroll:
                        true,

                    replace:
                        true,
                }
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Clear Search
    |--------------------------------------------------------------------------
    */

    const clearSearch =
        () => {

            setData(
                'search',
                ''
            );


            router.get(
                '/company/branches',
                {},
                {
                    preserveState:
                        true,

                    preserveScroll:
                        true,

                    replace:
                        true,
                }
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Refresh
    |--------------------------------------------------------------------------
    */

    const refreshBranches =
        () => {

            router.reload({
                only: [
                    'branches',
                    'summary',
                ],

                preserveScroll:
                    true,
            });
        };


    /*
    |--------------------------------------------------------------------------
    | Enter Branch
    |--------------------------------------------------------------------------
    */

    const enterBranch =
        (branch) => {

            if (
                !branch?.id ||
                !Boolean(
                    branch.status
                )
            ) {
                return;
            }


            router.post(
                `/select-branch/${branch.id}`,
                {},
                {
                    preserveState:
                        false,

                    preserveScroll:
                        false,
                }
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Current Search
    |--------------------------------------------------------------------------
    */

    const hasSearch =
        Boolean(
            String(
                filters?.search
                ?? ''
            ).trim()
        );


    return (

        <BusinessLayout>

            <Head title="Branches" />


            <PageShell

                eyebrow="Company Management"

                title="Branch Management"

                description={
                    `Manage locations, branch access and approval status for ${company?.company_name
                    ?? 'your company'
                    }.`
                }

                icon="bi-diagram-3"

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
                    },
                ]}

                actions={

                    <Link
                        href="/company/branches/create"
                        className="rr-page-primary-btn"
                    >

                        <i className="bi bi-plus-lg"></i>

                        Add Branch

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
                                Current Company
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

                        <i className="bi bi-shield-check"></i>

                        <div>

                            <strong>
                                Approval controlled by Super Admin
                            </strong>

                            <span>
                                New branches remain pending until approved.
                            </span>

                        </div>

                    </div>

                </section>


                {/* =====================================================
                    KPI SUMMARY
                   ===================================================== */}

                <section className="rr-branches-premium-summary">

                    <PremiumSummaryCard
                        label="Total Branches"
                        value={
                            summary?.total
                            ?? 0
                        }
                        description="All registered locations"
                        icon="bi-buildings"
                        tone="primary"
                    />


                    <PremiumSummaryCard
                        label="Active Branches"
                        value={
                            summary?.active
                            ?? 0
                        }
                        description="Available for business"
                        icon="bi-check-circle-fill"
                        tone="success"
                    />


                    <PremiumSummaryCard
                        label="Pending Approval"
                        value={
                            summary?.pending
                            ?? 0
                        }
                        description="Waiting for activation"
                        icon="bi-hourglass-split"
                        tone="warning"
                    />

                </section>


                {/* =====================================================
                    MANAGEMENT PANEL
                   ===================================================== */}

                <section className="rr-page-panel rr-branches-management-panel">

                    {/* =================================================
                        PANEL HEADER
                       ================================================= */}

                    <div className="rr-page-panel-header rr-branches-panel-header">

                        <div className="rr-page-panel-title">

                            <h2>
                                Company Branches
                            </h2>

                            <p>

                                {
                                    summary?.total
                                    ?? 0
                                } branch{
                                    (
                                        summary?.total
                                        ?? 0
                                    ) === 1
                                        ? ''
                                        : 'es'
                                } registered

                            </p>

                        </div>


                        <div className="rr-branches-panel-header-actions">

                            <button
                                type="button"
                                className="rr-branches-refresh-btn"
                                onClick={
                                    refreshBranches
                                }
                                title="Refresh branches"
                            >

                                <i className="bi bi-arrow-clockwise"></i>

                                Refresh

                            </button>

                        </div>

                    </div>


                    {/* =================================================
                        TOOLBAR
                       ================================================= */}

                    <div className="rr-branches-professional-toolbar">

                        <form
                            className="rr-branches-professional-search"
                            onSubmit={
                                submitSearch
                            }
                        >

                            <div className="rr-branches-professional-search-box">

                                <i className="bi bi-search"></i>


                                <input
                                    type="text"
                                    value={
                                        data.search
                                    }
                                    placeholder="Search branch name, code, city or phone..."
                                    onChange={
                                        (
                                            event
                                        ) =>
                                            setData(
                                                'search',
                                                event.target.value
                                            )
                                    }
                                />


                                {
                                    data.search && (

                                        <button
                                            type="button"
                                            className="rr-branches-professional-search-clear"
                                            onClick={
                                                clearSearch
                                            }
                                            title="Clear search"
                                        >

                                            <i className="bi bi-x-lg"></i>

                                        </button>

                                    )
                                }

                            </div>


                            <button
                                type="submit"
                                className="rr-branches-professional-search-btn"
                            >

                                <i className="bi bi-search"></i>

                                Search

                            </button>

                        </form>


                        <div className="rr-branches-toolbar-status">

                            <span className="rr-branches-toolbar-status-dot"></span>

                            Company-level branch management

                        </div>

                    </div>


                    {/* =================================================
                        SEARCH INFORMATION
                       ================================================= */}

                    {
                        hasSearch && (

                            <div className="rr-branches-search-result-bar">

                                <div>

                                    <i className="bi bi-funnel"></i>

                                    Showing results for

                                    <strong>
                                        “{filters.search}”
                                    </strong>

                                </div>


                                <button
                                    type="button"
                                    onClick={
                                        clearSearch
                                    }
                                >

                                    Clear Filter

                                </button>

                            </div>

                        )
                    }


                    {/* =================================================
                        BRANCH LIST
                       ================================================= */}

                    {
                        branchItems.length > 0
                            ? (

                                <div className="rr-branches-premium-list">

                                    {
                                        branchItems.map(
                                            branch => (

                                                <BranchCard
                                                    key={
                                                        branch.id
                                                    }

                                                    branch={
                                                        branch
                                                    }

                                                    activeStoreId={
                                                        activeStoreId
                                                    }

                                                    onEnter={
                                                        enterBranch
                                                    }
                                                />

                                            )
                                        )
                                    }

                                </div>

                            )
                            : (

                                <EmptyState
                                    hasSearch={
                                        hasSearch
                                    }
                                />

                            )
                    }

                </section>


                {/* =====================================================
                    PAGINATION
                   ===================================================== */}

                {
                    (
                        branches?.links
                        ?? []
                    ).length > 3 && (

                        <div className="rr-branches-premium-pagination">

                            {
                                branches.links.map(
                                    (
                                        link,
                                        index
                                    ) => (

                                        link.url
                                            ? (

                                                <Link
                                                    key={
                                                        index
                                                    }
                                                    href={
                                                        link.url
                                                    }
                                                    preserveScroll
                                                    className={
                                                        link.active
                                                            ? 'active'
                                                            : ''
                                                    }
                                                >

                                                    <span
                                                        dangerouslySetInnerHTML={{
                                                            __html:
                                                                link.label,
                                                        }}
                                                    />

                                                </Link>

                                            )
                                            : (

                                                <span
                                                    key={
                                                        index
                                                    }
                                                    className="disabled"
                                                    dangerouslySetInnerHTML={{
                                                        __html:
                                                            link.label,
                                                    }}
                                                />

                                            )

                                    )
                                )
                            }

                        </div>

                    )
                }

            </PageShell>

        </BusinessLayout>
    );
}


/*
|--------------------------------------------------------------------------
| Premium Summary Card
|--------------------------------------------------------------------------
*/

function PremiumSummaryCard({
    label,
    value,
    description,
    icon,
    tone,
}) {

    return (

        <article
            className={
                `rr-branches-premium-summary-card ` +
                `rr-branches-premium-summary-card-${tone}`
            }
        >

            <div className="rr-branches-premium-summary-top">

                <div className="rr-branches-premium-summary-icon">

                    <i
                        className={
                            `bi ${icon}`
                        }
                    ></i>

                </div>


                <span className="rr-branches-premium-summary-label">

                    {label}

                </span>

            </div>


            <div className="rr-branches-premium-summary-value">

                {
                    formatNumber(
                        value
                    )
                }

            </div>


            <div className="rr-branches-premium-summary-description">

                {description}

            </div>


            <div className="rr-branches-premium-summary-decoration"></div>

        </article>
    );
}


/*
|--------------------------------------------------------------------------
| Branch Card
|--------------------------------------------------------------------------
*/

function BranchCard({
    branch,
    activeStoreId,
    onEnter,
}) {

    /*
    |--------------------------------------------------------------------------
    | Status
    |--------------------------------------------------------------------------
    */

    const isActive =
        Boolean(
            branch?.status
        );


    const isCurrent =
        Number(
            activeStoreId
        ) ===
        Number(
            branch?.id
        );


    /*
    |--------------------------------------------------------------------------
    | Location
    |--------------------------------------------------------------------------
    */

    const location =
        [
            branch?.city,
            branch?.state,
        ]
            .filter(
                Boolean
            )
            .join(
                ', '
            );


    return (

        <article
            className={
                `rr-branches-premium-card ` +
                `${isCurrent
                    ? 'current'
                    : ''
                }`
            }
        >

            {/* =========================================================
                STATUS STRIPE
               ========================================================= */}

            <div
                className={
                    `rr-branches-premium-card-stripe ` +
                    `${isActive
                        ? 'active'
                        : 'pending'
                    }`
                }
            ></div>


            {/* =========================================================
                CARD CONTENT
               ========================================================= */}

            <div className="rr-branches-premium-card-content">

                {/* =====================================================
                    LEFT
                   ===================================================== */}

                <div className="rr-branches-premium-card-main">

                    <div
                        className={
                            `rr-branches-premium-card-icon ` +
                            `${isActive
                                ? 'active'
                                : 'pending'
                            }`
                        }
                    >

                        <i className="bi bi-shop-window"></i>

                    </div>


                    <div className="rr-branches-premium-card-info">

                        {/* =============================================
                            TITLE
                           ============================================= */}

                        <div className="rr-branches-premium-card-title-row">

                            <div className="rr-branches-premium-card-title">

                                <h3>

                                    {
                                        branch?.store_name
                                        ?? 'Branch'
                                    }

                                </h3>


                                {
                                    branch?.store_code && (

                                        <span>

                                            <i className="bi bi-upc-scan"></i>

                                            {
                                                branch.store_code
                                            }

                                        </span>

                                    )
                                }

                            </div>


                            <div className="rr-branches-premium-statuses">

                                {
                                    isCurrent && (

                                        <span className="rr-branches-premium-current-badge">

                                            <i className="bi bi-pin-angle-fill"></i>

                                            Current Branch

                                        </span>

                                    )
                                }


                                <span
                                    className={
                                        isActive
                                            ? 'rr-branches-premium-status rr-branches-premium-status-active'
                                            : 'rr-branches-premium-status rr-branches-premium-status-pending'
                                    }
                                >

                                    <i
                                        className={
                                            isActive
                                                ? 'bi bi-check-circle-fill'
                                                : 'bi bi-clock-fill'
                                        }
                                    ></i>


                                    {
                                        isActive
                                            ? 'Active'
                                            : 'Pending Approval'
                                    }

                                </span>

                            </div>

                        </div>


                        {/* =============================================
                            META
                           ============================================= */}

                        <div className="rr-branches-premium-meta">

                            {
                                branch?.contact_person && (

                                    <MetaItem
                                        icon="bi-person"
                                        label="Contact"
                                        value={
                                            branch.contact_person
                                        }
                                    />

                                )
                            }


                            {
                                branch?.phone && (

                                    <MetaItem
                                        icon="bi-telephone"
                                        label="Phone"
                                        value={
                                            branch.phone
                                        }
                                    />

                                )
                            }


                            {
                                location && (

                                    <MetaItem
                                        icon="bi-geo-alt"
                                        label="Location"
                                        value={
                                            location
                                        }
                                    />

                                )
                            }


                            {
                                branch?.email && (

                                    <MetaItem
                                        icon="bi-envelope"
                                        label="Email"
                                        value={
                                            branch.email
                                        }
                                    />

                                )
                            }

                        </div>


                        {/* =============================================
                            PENDING MESSAGE
                           ============================================= */}

                        {
                            !isActive && (

                                <div className="rr-branches-premium-approval-note">

                                    <i className="bi bi-shield-lock"></i>

                                    <div>

                                        <strong>
                                            Waiting for Super Admin approval
                                        </strong>

                                        <span>
                                            This branch cannot access ERP operations until it is activated.
                                        </span>

                                    </div>

                                </div>

                            )
                        }

                    </div>

                </div>


                {/* =====================================================
                    ACTIONS
                   ===================================================== */}

                <div className="rr-branches-premium-card-actions">

                    {
                        isActive
                            ? (

                                isCurrent
                                    ? (

                                        <div className="rr-branches-premium-current-state">

                                            <i className="bi bi-check2-circle"></i>

                                            Active Workspace

                                        </div>

                                    )
                                    : (

                                        <button
                                            type="button"
                                            className="rr-branches-premium-enter-btn"
                                            onClick={
                                                () =>
                                                    onEnter(
                                                        branch
                                                    )
                                            }
                                        >

                                            <i className="bi bi-box-arrow-in-right"></i>

                                            Enter Branch

                                        </button>

                                    )

                            )
                            : (

                                <div className="rr-branches-premium-pending-state">

                                    <i className="bi bi-hourglass-split"></i>

                                    Approval Pending

                                </div>

                            )
                    }


                    <div className="rr-branches-premium-action-divider"></div>


                    <Link
                        href={
                            `/company/branches/${branch.id}`
                        }
                        className="rr-branches-premium-action-btn"
                    >

                        <i className="bi bi-eye"></i>

                        View

                    </Link>


                    <Link
                        href={
                            `/company/branches/${branch.id}/edit`
                        }
                        className="rr-branches-premium-action-btn"
                    >

                        <i className="bi bi-pencil-square"></i>

                        Edit

                    </Link>

                </div>

            </div>

        </article>
    );
}


/*
|--------------------------------------------------------------------------
| Metadata Item
|--------------------------------------------------------------------------
*/

function MetaItem({
    icon,
    label,
    value,
}) {

    return (

        <div className="rr-branches-premium-meta-item">

            <div className="rr-branches-premium-meta-icon">

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
                    {value}
                </strong>

            </div>

        </div>
    );
}


/*
|--------------------------------------------------------------------------
| Empty State
|--------------------------------------------------------------------------
*/

function EmptyState({
    hasSearch,
}) {

    return (

        <div className="rr-branches-premium-empty">

            <div className="rr-branches-premium-empty-graphic">

                <div>

                    <i
                        className={
                            hasSearch
                                ? 'bi bi-search'
                                : 'bi bi-building-add'
                        }
                    ></i>

                </div>

            </div>


            <span className="rr-branches-premium-empty-eyebrow">

                {
                    hasSearch
                        ? 'Search Results'
                        : 'Branch Management'
                }

            </span>


            <h2>

                {
                    hasSearch
                        ? 'No matching branches found'
                        : 'Create your first branch'
                }

            </h2>


            <p>

                {
                    hasSearch
                        ? 'We could not find a branch matching your current search. Try another branch name, code, city or phone number.'
                        : 'Add a branch to start building your company workspace. New branches require Super Admin approval before they can be used.'
                }

            </p>


            {
                !hasSearch && (

                    <Link
                        href="/company/branches/create"
                        className="rr-page-primary-btn"
                    >

                        <i className="bi bi-plus-lg"></i>

                        Add First Branch

                    </Link>

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


/*
|--------------------------------------------------------------------------
| Number Formatter
|--------------------------------------------------------------------------
*/

function formatNumber(
    value
) {

    return Number(
        value
        ?? 0
    ).toLocaleString(
        'en-IN'
    );
}