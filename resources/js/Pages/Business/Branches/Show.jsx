import {
    Head,
    Link,
    router,
} from '@inertiajs/react';

import BusinessLayout
    from '../../../Layouts/BusinessLayout';

import PageShell
    from '../../../Components/Business/PageShell';


export default function BranchShow({
    company,
    branch,
}) {

    /*
    |--------------------------------------------------------------------------
    | Enter Branch
    |--------------------------------------------------------------------------
    */

    const enterBranch = () => {

        if (
            !branch?.id ||
            !branch?.status ||
            branch?.is_current
        ) {
            return;
        }


        router.post(
            `/select-branch/${branch.id}`,
            {},
            {
                preserveState: false,
                preserveScroll: false,
            }
        );
    };


    /*
    |--------------------------------------------------------------------------
    | Location
    |--------------------------------------------------------------------------
    */

    const location = [
        branch?.city,
        branch?.state,
        branch?.country,
    ]
        .filter(Boolean)
        .join(', ');


    return (

        <BusinessLayout>

            <Head
                title={
                    branch?.store_name
                    ?? 'Branch'
                }
            />


            <PageShell

                eyebrow="Company Management"

                title={
                    branch?.store_name
                    ?? 'Branch Details'
                }

                description={
                    branch?.store_code
                        ? `Branch ${branch.store_code}`
                        : 'View branch information.'
                }

                icon="bi-shop-window"

                breadcrumb={[
                    {
                        label: 'Company',
                        href: '/company/dashboard',
                    },
                    {
                        label: 'Branches',
                        href: '/company/branches',
                    },
                    {
                        label:
                            branch?.store_name
                            ?? 'View',
                    },
                ]}

                actions={

                    <>

                        <Link
                            href="/company/branches"
                            className="rr-page-secondary-btn"
                        >
                            <i className="bi bi-arrow-left"></i>

                            Back
                        </Link>


                        <Link
                            href={
                                `/company/branches/${branch.id}/edit`
                            }
                            className="rr-page-secondary-btn"
                        >
                            <i className="bi bi-pencil-square"></i>

                            Edit
                        </Link>


                        {
                            branch?.status &&
                            !branch?.is_current && (

                                <button
                                    type="button"
                                    className="rr-page-primary-btn"
                                    onClick={
                                        enterBranch
                                    }
                                >
                                    <i className="bi bi-box-arrow-in-right"></i>

                                    Enter Branch
                                </button>

                            )
                        }

                    </>

                }
            >

                {/* =====================================================
                    BRANCH IDENTITY
                   ===================================================== */}

                <section className="rr-branch-view-identity">

                    <div className="rr-branch-view-identity-main">

                        <div
                            className={
                                `rr-branch-view-icon ${branch?.status
                                    ? 'active'
                                    : 'pending'
                                }`
                            }
                        >
                            <i className="bi bi-shop-window"></i>
                        </div>


                        <div className="rr-branch-view-identity-info">

                            <span>
                                Branch
                            </span>

                            <strong>
                                {
                                    branch?.store_name
                                    ?? 'Branch'
                                }
                            </strong>

                            <div className="rr-branch-view-code">

                                <i className="bi bi-upc-scan"></i>

                                {
                                    branch?.store_code
                                    ?? '—'
                                }

                            </div>

                        </div>

                    </div>


                    <div className="rr-branch-view-status-area">

                        {
                            branch?.is_current && (

                                <span className="rr-branch-view-current">

                                    <i className="bi bi-pin-angle-fill"></i>

                                    Current Branch

                                </span>

                            )
                        }


                        <span
                            className={
                                branch?.status
                                    ? 'rr-branch-view-status active'
                                    : 'rr-branch-view-status pending'
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
                    PENDING APPROVAL
                   ===================================================== */}

                {
                    !branch?.status && (

                        <section className="rr-branch-view-approval">

                            <i className="bi bi-shield-lock"></i>

                            <div>

                                <strong>
                                    Waiting for Super Admin approval
                                </strong>

                                <p>
                                    This branch has been created successfully,
                                    but business operations cannot use it until
                                    Super Admin activates the branch.
                                </p>

                            </div>

                        </section>

                    )
                }


                {/* =====================================================
                    DETAILS GRID
                   ===================================================== */}

                <div className="rr-branch-view-grid">

                    {/* =================================================
                        CONTACT
                       ================================================= */}

                    <section className="rr-page-panel">

                        <div className="rr-page-panel-header">

                            <div className="rr-page-panel-title">

                                <h2>
                                    Contact Information
                                </h2>

                                <p>
                                    Branch contact details
                                </p>

                            </div>


                            <div className="rr-branch-view-panel-icon">

                                <i className="bi bi-person-lines-fill"></i>

                            </div>

                        </div>


                        <div className="rr-branch-view-details">

                            <DetailRow
                                icon="bi-person"
                                label="Contact Person"
                                value={
                                    branch?.contact_person
                                }
                            />

                            <DetailRow
                                icon="bi-telephone"
                                label="Phone"
                                value={
                                    branch?.phone
                                }
                            />

                            <DetailRow
                                icon="bi-envelope"
                                label="Email"
                                value={
                                    branch?.email
                                }
                            />

                        </div>

                    </section>


                    {/* =================================================
                        LOCATION
                       ================================================= */}

                    <section className="rr-page-panel">

                        <div className="rr-page-panel-header">

                            <div className="rr-page-panel-title">

                                <h2>
                                    Location
                                </h2>

                                <p>
                                    Branch address information
                                </p>

                            </div>


                            <div className="rr-branch-view-panel-icon">

                                <i className="bi bi-geo-alt"></i>

                            </div>

                        </div>


                        <div className="rr-branch-view-details">

                            <DetailRow
                                icon="bi-geo"
                                label="Address"
                                value={
                                    branch?.address
                                }
                            />

                            <DetailRow
                                icon="bi-buildings"
                                label="City / State"
                                value={
                                    location
                                }
                            />

                            <DetailRow
                                icon="bi-mailbox"
                                label="Pincode"
                                value={
                                    branch?.pincode
                                }
                            />

                        </div>

                    </section>

                </div>


                {/* =====================================================
                    SYSTEM INFORMATION
                   ===================================================== */}

                <section className="rr-page-panel rr-branch-view-system-panel">

                    <div className="rr-page-panel-header">

                        <div className="rr-page-panel-title">

                            <h2>
                                System Information
                            </h2>

                            <p>
                                Company and branch identifiers
                            </p>

                        </div>


                        <div className="rr-branch-view-panel-icon">

                            <i className="bi bi-database"></i>

                        </div>

                    </div>


                    <div className="rr-branch-view-system-grid">

                        <SystemItem
                            label="Company"
                            value={
                                company?.company_name
                            }
                            icon="bi-building"
                        />

                        <SystemItem
                            label="Company Code"
                            value={
                                company?.company_code
                            }
                            icon="bi-upc"
                        />

                        <SystemItem
                            label="Branch Number"
                            value={
                                branch?.branch_number
                                    ? `#${String(
                                        branch.branch_number
                                    ).padStart(
                                        3,
                                        '0'
                                    )}`
                                    : null
                            }
                            icon="bi-list-ol"
                        />

                        <SystemItem
                            label="Branch Code"
                            value={
                                branch?.store_code
                            }
                            icon="bi-upc-scan"
                        />

                    </div>

                </section>


                {/* =====================================================
                    BOTTOM ACTION BAR
                   ===================================================== */}

                <section className="rr-branch-view-action-bar">

                    <div>

                        <i className="bi bi-info-circle"></i>

                        <span>

                            {
                                branch?.status
                                    ? (
                                        branch?.is_current
                                            ? 'You are currently working in this branch.'
                                            : 'This branch is active and ready for business operations.'
                                    )
                                    : 'This branch is waiting for Super Admin approval.'
                            }

                        </span>

                    </div>


                    <div className="rr-branch-view-action-buttons">

                        <Link
                            href={
                                `/company/branches/${branch.id}/edit`
                            }
                            className="rr-page-secondary-btn"
                        >

                            <i className="bi bi-pencil-square"></i>

                            Edit Branch

                        </Link>


                        {
                            branch?.status &&
                            !branch?.is_current && (

                                <button
                                    type="button"
                                    className="rr-page-primary-btn"
                                    onClick={
                                        enterBranch
                                    }
                                >

                                    <i className="bi bi-box-arrow-in-right"></i>

                                    Enter Branch

                                </button>

                            )
                        }

                    </div>

                </section>

            </PageShell>

        </BusinessLayout>
    );
}


/*
|--------------------------------------------------------------------------
| Detail Row
|--------------------------------------------------------------------------
*/

function DetailRow({
    icon,
    label,
    value,
}) {

    return (

        <div className="rr-branch-view-detail-row">

            <div className="rr-branch-view-detail-icon">

                <i
                    className={
                        `bi ${icon}`
                    }
                ></i>

            </div>


            <div className="rr-branch-view-detail-content">

                <span>
                    {label}
                </span>

                <strong
                    className={
                        !value
                            ? 'empty'
                            : ''
                    }
                >
                    {
                        value
                        || 'Not provided'
                    }
                </strong>

            </div>

        </div>

    );
}


/*
|--------------------------------------------------------------------------
| System Item
|--------------------------------------------------------------------------
*/

function SystemItem({
    label,
    value,
    icon,
}) {

    return (

        <div className="rr-branch-view-system-item">

            <div>

                <i
                    className={
                        `bi ${icon}`
                    }
                ></i>

            </div>


            <section>

                <span>
                    {label}
                </span>

                <strong>
                    {
                        value
                        || '—'
                    }
                </strong>

            </section>

        </div>

    );
}