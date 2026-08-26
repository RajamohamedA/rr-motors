import {
    Head,
    Link,
    router,
} from '@inertiajs/react';

import {
    useMemo,
    useState,
} from 'react';

import SuperAdminLayout
    from '../../../Layouts/SuperAdminLayout';

import SearchableSelect
    from '../../../Components/SearchableSelect';

import ConfirmModal
    from '../../../Components/ConfirmModal';


export default function Index({
    branches = [],
}) {

    /*
    |--------------------------------------------------------------------------
    | State
    |--------------------------------------------------------------------------
    */

    const [
        search,
        setSearch,
    ] = useState('');


    const [
        statusFilter,
        setStatusFilter,
    ] = useState('all');


    const [
        companyFilter,
        setCompanyFilter,
    ] = useState('all');


    const [
        branchAction,
        setBranchAction,
    ] = useState(null);


    const [
        processingBranch,
        setProcessingBranch,
    ] = useState(false);


    /*
    |--------------------------------------------------------------------------
    | Branch Summary Statistics
    |--------------------------------------------------------------------------
    */

    const summary =
        useMemo(
            () => {

                const total =
                    branches.length;


                const active =
                    branches.filter(
                        (branch) =>
                            branch.status
                    ).length;


                const inactive =
                    total -
                    active;


                const companyIds =
                    new Set(
                        branches
                            .map(
                                (branch) =>
                                    branch.company_id
                            )
                            .filter(
                                Boolean
                            )
                    );


                return {

                    total,

                    active,

                    inactive,

                    companies:
                        companyIds.size,
                };

            },
            [
                branches,
            ]
        );


    /*
    |--------------------------------------------------------------------------
    | Companies For Filter
    |--------------------------------------------------------------------------
    */

    const companies =
        useMemo(
            () => {

                const map =
                    new Map();


                branches.forEach(
                    (branch) => {

                        if (
                            branch.company?.id
                        ) {

                            map.set(
                                branch.company.id,
                                branch.company
                            );
                        }
                    }
                );


                return Array.from(
                    map.values()
                );

            },
            [
                branches,
            ]
        );


    /*
    |--------------------------------------------------------------------------
    | Filter Branches
    |--------------------------------------------------------------------------
    */

    const filteredBranches =
        useMemo(
            () => {

                const term =
                    search
                        .trim()
                        .toLowerCase();


                return branches.filter(
                    (branch) => {

                        /*
                        |--------------------------------------------------------------------------
                        | Status
                        |--------------------------------------------------------------------------
                        */

                        if (
                            statusFilter ===
                            'active' &&
                            !branch.status
                        ) {

                            return false;
                        }


                        if (
                            statusFilter ===
                            'inactive' &&
                            branch.status
                        ) {

                            return false;
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Company
                        |--------------------------------------------------------------------------
                        */

                        if (
                            companyFilter !==
                            'all' &&
                            Number(
                                branch.company_id
                            ) !==
                            Number(
                                companyFilter
                            )
                        ) {

                            return false;
                        }


                        /*
                        |--------------------------------------------------------------------------
                        | Search
                        |--------------------------------------------------------------------------
                        */

                        if (
                            !term
                        ) {

                            return true;
                        }


                        return [

                            branch.store_code,

                            branch.store_name,

                            branch.contact_person,

                            branch.phone,

                            branch.email,

                            branch.city,

                            branch.state,

                            branch.company
                                ?.company_name,

                            branch.company
                                ?.company_code,

                        ]
                            .filter(
                                Boolean
                            )
                            .some(
                                (value) =>
                                    String(
                                        value
                                    )
                                        .toLowerCase()
                                        .includes(
                                            term
                                        )
                            );
                    }
                );

            },
            [
                branches,
                search,
                statusFilter,
                companyFilter,
            ]
        );


    /*
    |--------------------------------------------------------------------------
    | Branch Action
    |--------------------------------------------------------------------------
    */

    const handleBranchAction =
        () => {

            if (
                !branchAction ||
                processingBranch
            ) {

                return;
            }


            const branch =
                branchAction.branch;


            const action =
                branchAction.action;


            /*
            |--------------------------------------------------------------------------
            | Build URL
            |--------------------------------------------------------------------------
            */

            const url =
                `/super-admin/companies/${branch.company_id}` +
                `/branches/${branch.id}` +
                `/${action}`;


            /*
            |--------------------------------------------------------------------------
            | Processing
            |--------------------------------------------------------------------------
            */

            setProcessingBranch(
                true
            );


            /*
            |--------------------------------------------------------------------------
            | Request
            |--------------------------------------------------------------------------
            */

            router.post(
                url,
                {},
                {
                    preserveScroll:
                        true,

                    /*
                    |--------------------------------------------------------------------------
                    | Success
                    |--------------------------------------------------------------------------
                    */

                    onSuccess:
                        () => {

                            setBranchAction(
                                null
                            );
                        },

                    /*
                    |--------------------------------------------------------------------------
                    | Finish
                    |--------------------------------------------------------------------------
                    */

                    onFinish:
                        () => {

                            setProcessingBranch(
                                false
                            );
                        },
                }
            );
        };


    /*
    |--------------------------------------------------------------------------
    | Render
    |--------------------------------------------------------------------------
    */

    return (

        <SuperAdminLayout

            title="Branches"

            subtitle="Manage branches across all companies"

            breadcrumbs={[
                {
                    label:
                        'Branches',
                },
            ]}
        >

            <Head
                title="Branches"
            />


            {/* =============================================================
                SUMMARY
               ============================================================= */}

            <div className="row g-3 mb-4">

                <BranchStatCard

                    title="Total Branches"

                    value={
                        summary.total
                    }

                    description="Across all companies"

                    icon="bi-shop"
                />


                <BranchStatCard

                    title="Active Branches"

                    value={
                        summary.active
                    }

                    description="Available for business"

                    icon="bi-check-circle"

                    type="success"
                />


                <BranchStatCard

                    title="Inactive Branches"

                    value={
                        summary.inactive
                    }

                    description="Currently disabled"

                    icon="bi-pause-circle"

                    type="warning"
                />


                <BranchStatCard

                    title="Companies"

                    value={
                        summary.companies
                    }

                    description="Companies with branches"

                    icon="bi-buildings"
                />

            </div>


            {/* =============================================================
                PANEL
               ============================================================= */}

            <div className="rr-admin-panel">

                {/* =========================================================
                    HEADER
                   ========================================================= */}

                <div className="rr-admin-panel-header">

                    <div className="rr-admin-panel-heading">

                        <h2 className="rr-admin-panel-title">

                            Branch Directory

                        </h2>


                        <div className="rr-admin-panel-description">

                            View and manage all company branches

                        </div>

                    </div>

                </div>


                {/* =========================================================
                    TOOLBAR
                   ========================================================= */}

                <div className="rr-branch-toolbar">

                    {/* Search */}

                    <div className="rr-company-search">

                        <i className="bi bi-search"></i>


                        <input

                            type="text"

                            value={
                                search
                            }

                            onChange={
                                (
                                    event
                                ) =>
                                    setSearch(
                                        event
                                            .target
                                            .value
                                    )
                            }

                            placeholder="Search branch, code, company, city..."

                        />

                    </div>


                    {/* Filters */}

                    <div className="rr-branch-toolbar-filters">

                        {/* Company */}

                        <SearchableSelect

                            value={
                                companyFilter
                            }

                            onChange={
                                setCompanyFilter
                            }

                            allLabel="All Companies"

                            placeholder="Select Company"

                            searchPlaceholder="Search companies..."

                            options={
                                companies.map(
                                    (
                                        company
                                    ) => ({

                                        value:
                                            company.id,

                                        label:
                                            company.company_code
                                                ? `${company.company_name} (${company.company_code})`
                                                : company.company_name,
                                    })
                                )
                            }

                        />


                        {/* Status */}

                        <select

                            className="rr-company-filter"

                            value={
                                statusFilter
                            }

                            onChange={
                                (
                                    event
                                ) =>
                                    setStatusFilter(
                                        event
                                            .target
                                            .value
                                    )
                            }
                        >

                            <option value="all">

                                All Status

                            </option>


                            <option value="active">

                                Active

                            </option>


                            <option value="inactive">

                                Inactive

                            </option>

                        </select>


                        {/* Result Count */}

                        <span className="rr-company-result-count">

                            {
                                filteredBranches.length
                            } branches

                        </span>

                    </div>

                </div>


                {/* =========================================================
                    TABLE
                   ========================================================= */}

                <div className="rr-admin-table-wrapper">

                    <table className="rr-admin-table">

                        <thead>

                            <tr>

                                <th>
                                    Branch Code
                                </th>

                                <th>
                                    Branch
                                </th>

                                <th>
                                    Company
                                </th>

                                <th>
                                    Location
                                </th>

                                <th>
                                    Contact
                                </th>

                                <th>
                                    Status
                                </th>

                                <th className="text-end">
                                    Actions
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            {
                                filteredBranches.length > 0
                                    ? (

                                        filteredBranches.map(
                                            (
                                                branch
                                            ) => (

                                                <tr
                                                    key={
                                                        branch.id
                                                    }
                                                >

                                                    {/* =================================
                                                        CODE
                                                       ================================= */}

                                                    <td>

                                                        <span className="rr-branch-code">

                                                            {
                                                                branch.store_code
                                                            }

                                                        </span>

                                                    </td>


                                                    {/* =================================
                                                        BRANCH
                                                       ================================= */}

                                                    <td>

                                                        <div className="rr-branch-table-cell">

                                                            <div className="rr-branch-table-icon">

                                                                <i className="bi bi-shop"></i>

                                                            </div>


                                                            <div>

                                                                <div className="rr-branch-table-name">

                                                                    {
                                                                        branch.store_name
                                                                    }

                                                                </div>


                                                                {
                                                                    branch.contact_person && (

                                                                        <div className="rr-table-secondary">

                                                                            {
                                                                                branch.contact_person
                                                                            }

                                                                        </div>

                                                                    )
                                                                }

                                                            </div>

                                                        </div>

                                                    </td>


                                                    {/* =================================
                                                        COMPANY
                                                       ================================= */}

                                                    <td>

                                                        <Link

                                                            href={
                                                                `/super-admin/companies/${branch.company_id}`
                                                            }

                                                            className="rr-branch-company-link"
                                                        >

                                                            {
                                                                branch.company
                                                                    ?.company_name ||
                                                                '—'
                                                            }

                                                        </Link>


                                                        <div className="rr-table-secondary">

                                                            {
                                                                branch.company
                                                                    ?.company_code ||
                                                                '—'
                                                            }


                                                            {
                                                                !branch.company
                                                                    ?.status && (

                                                                    <span className="rr-company-inactive-text">

                                                                        {' '}
                                                                        • Company Inactive

                                                                    </span>

                                                                )
                                                            }

                                                        </div>

                                                    </td>


                                                    {/* =================================
                                                        LOCATION
                                                       ================================= */}

                                                    <td>

                                                        <div>

                                                            {
                                                                branch.city ||
                                                                '—'
                                                            }

                                                        </div>


                                                        {
                                                            branch.state && (

                                                                <div className="rr-table-secondary">

                                                                    {
                                                                        branch.state
                                                                    }

                                                                </div>

                                                            )
                                                        }

                                                    </td>


                                                    {/* =================================
                                                        CONTACT
                                                       ================================= */}

                                                    <td>

                                                        <div>

                                                            {
                                                                branch.phone ||
                                                                '—'
                                                            }

                                                        </div>


                                                        {
                                                            branch.email && (

                                                                <div className="rr-table-secondary">

                                                                    {
                                                                        branch.email
                                                                    }

                                                                </div>

                                                            )
                                                        }

                                                    </td>


                                                    {/* =================================
                                                        STATUS
                                                       ================================= */}

                                                    <td>

                                                        <span
                                                            className={
                                                                `rr-status ${branch.status
                                                                    ? 'rr-status-active'
                                                                    : 'rr-status-inactive'
                                                                }`
                                                            }
                                                        >

                                                            {
                                                                branch.status
                                                                    ? 'Active'
                                                                    : 'Inactive'
                                                            }

                                                        </span>

                                                    </td>


                                                    {/* =================================
                                                        ACTIONS
                                                       ================================= */}

                                                    <td className="text-end">

                                                        <div className="rr-table-actions">

                                                            {/* =============================
                                                                ENTER BRANCH
                                                               ============================= */}

                                                            {
                                                                branch.status &&
                                                                branch.company
                                                                    ?.status && (

                                                                    <Link

                                                                        href={
                                                                            `/super-admin/companies/${branch.company_id}` +
                                                                            `/branches/${branch.id}/enter`
                                                                        }

                                                                        className="rr-btn rr-btn-primary"
                                                                    >

                                                                        <i className="bi bi-box-arrow-in-right"></i>

                                                                        Enter

                                                                    </Link>

                                                                )
                                                            }


                                                            {/* =============================
                                                                EDIT
                                                               ============================= */}

                                                            <Link

                                                                href={
                                                                    `/super-admin/companies/${branch.company_id}` +
                                                                    `/branches/${branch.id}/edit`
                                                                }

                                                                className="rr-icon-action"

                                                                title="Edit branch"
                                                            >

                                                                <i className="bi bi-pencil"></i>

                                                            </Link>


                                                            {/* =============================
                                                                ACTIVATE / DEACTIVATE
                                                               ============================= */}

                                                            {
                                                                branch.status
                                                                    ? (

                                                                        <button

                                                                            type="button"

                                                                            className="rr-icon-action rr-icon-action-warning"

                                                                            title="Deactivate branch"

                                                                            disabled={
                                                                                processingBranch
                                                                            }

                                                                            onClick={
                                                                                () =>
                                                                                    setBranchAction({
                                                                                        branch,
                                                                                        action:
                                                                                            'deactivate',
                                                                                    })
                                                                            }
                                                                        >

                                                                            <i className="bi bi-pause-circle"></i>

                                                                        </button>

                                                                    )
                                                                    : (

                                                                        <button

                                                                            type="button"

                                                                            className="rr-icon-action rr-icon-action-success"

                                                                            title={
                                                                                branch.company
                                                                                    ?.status
                                                                                    ? 'Activate branch'
                                                                                    : 'Activate the company first'
                                                                            }

                                                                            /*
                                                                            |--------------------------------------------------------------------------
                                                                            | IMPORTANT
                                                                            |--------------------------------------------------------------------------
                                                                            |
                                                                            | Do NOT disable this button because of an inactive company.
                                                                            |
                                                                            | Let Laravel receive the request and return your standard
                                                                            | AppResponse error message:
                                                                            |
                                                                            | "The company is inactive. Activate the company before
                                                                            | activating this branch."
                                                                            |
                                                                            */

                                                                            disabled={
                                                                                processingBranch
                                                                            }

                                                                            onClick={
                                                                                () =>
                                                                                    setBranchAction({
                                                                                        branch,
                                                                                        action:
                                                                                            'activate',
                                                                                    })
                                                                            }
                                                                        >

                                                                            <i className="bi bi-check-circle"></i>

                                                                        </button>

                                                                    )
                                                            }


                                                            {/* =============================
                                                                OPEN COMPANY
                                                               ============================= */}

                                                            <Link

                                                                href={
                                                                    `/super-admin/companies/${branch.company_id}`
                                                                }

                                                                className="rr-icon-action"

                                                                title="Open company"
                                                            >

                                                                <i className="bi bi-building"></i>

                                                            </Link>

                                                        </div>

                                                    </td>

                                                </tr>

                                            )
                                        )

                                    )
                                    : (

                                        <tr>

                                            <td colSpan="7">

                                                <div className="rr-empty-state">

                                                    <div className="rr-empty-state-icon">

                                                        <i className="bi bi-shop"></i>

                                                    </div>


                                                    <div className="rr-empty-state-title">

                                                        No branches found

                                                    </div>


                                                    <div className="rr-empty-state-text">

                                                        Try changing the search or filters.

                                                    </div>

                                                </div>

                                            </td>

                                        </tr>

                                    )
                            }

                        </tbody>

                    </table>

                </div>

            </div>


            {/* =============================================================
                ACTIVATE / DEACTIVATE CONFIRMATION
               ============================================================= */}

            <ConfirmModal

                show={
                    Boolean(
                        branchAction
                    )
                }

                title={
                    branchAction?.action ===
                        'deactivate'
                        ? 'Deactivate Branch'
                        : 'Activate Branch'
                }

                message={
                    branchAction
                        ? branchAction.action ===
                            'deactivate'
                            ? `Deactivate "${branchAction.branch.store_name}"? Users will not be able to enter this branch until it is activated again.`
                            : `Activate "${branchAction.branch.store_name}"? Users with branch access will be able to use it again.`
                        : ''
                }

                confirmText={
                    branchAction?.action ===
                        'deactivate'
                        ? 'Deactivate Branch'
                        : 'Activate Branch'
                }

                icon={
                    branchAction?.action ===
                        'deactivate'
                        ? 'bi-pause-circle'
                        : 'bi-check-circle'
                }

                variant={
                    branchAction?.action ===
                        'deactivate'
                        ? 'danger'
                        : 'primary'
                }

                processing={
                    processingBranch
                }

                onCancel={
                    () => {

                        if (
                            !processingBranch
                        ) {

                            setBranchAction(
                                null
                            );
                        }
                    }
                }

                onConfirm={
                    handleBranchAction
                }

            />

        </SuperAdminLayout>
    );
}


/*
|--------------------------------------------------------------------------
| Branch Summary Card
|--------------------------------------------------------------------------
*/

function BranchStatCard({
    title,
    value,
    description,
    icon,
    type = 'primary',
}) {

    return (

        <div className="col-xl-3 col-md-6">

            <div className="rr-branch-stat-card">

                <div className="rr-branch-stat-top">

                    <div className="rr-branch-stat-title">

                        {title}

                    </div>


                    <div
                        className={
                            `rr-branch-stat-icon ` +
                            `rr-branch-stat-icon-${type}`
                        }
                    >

                        <i
                            className={
                                `bi ${icon}`
                            }
                        ></i>

                    </div>

                </div>


                <div className="rr-branch-stat-value">

                    {value}

                </div>


                <div className="rr-branch-stat-description">

                    {description}

                </div>

            </div>

        </div>
    );
}