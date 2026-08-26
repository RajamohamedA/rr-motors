import { useState } from 'react';

import { Head, Link, router } from '@inertiajs/react';
import SuperAdminLayout from '../../../Layouts/SuperAdminLayout';

import ConfirmModal from '../../../Components/ConfirmModal';

export default function Show({ company }) {

    const stores = company?.stores ?? [];

    const [showArchiveModal, setShowArchiveModal] =
        useState(false);

    const [archiving, setArchiving] =
        useState(false);

    const [branchAction, setBranchAction] =
        useState(null);

    const [processingBranch, setProcessingBranch] =
        useState(false);

    return (
        <SuperAdminLayout
            title={company?.company_name ?? 'Company'}
            subtitle="Company profile and branch management"
            breadcrumbs={[
                {
                    label: 'Companies',
                    href: '/super-admin/companies',
                },
                {
                    label: company?.company_name ?? 'Company',
                },
            ]}
        >

            <Head title={company?.company_name ?? 'Company'} />

            {/* COMPANY HEADER */}

            <div className="rr-admin-panel mb-4">

                <div className="rr-admin-company-header">

                    <div className="rr-admin-company-identity">

                        <div className="rr-admin-company-logo">

                            {company?.logo ? (
                                <img
                                    src={`/storage/${company.logo}`}
                                    alt={company.company_name}
                                />
                            ) : (
                                <i className="bi bi-building"></i>
                            )}

                        </div>

                        <div>

                            <div className="rr-admin-company-name">
                                {company?.company_name ?? '—'}
                            </div>

                            <div className="rr-admin-company-meta">

                                <span>
                                    <i className="bi bi-person me-1"></i>

                                    {company?.owner_name
                                        || 'Owner not specified'}
                                </span>

                                {company?.city && (
                                    <span>
                                        <i className="bi bi-geo-alt me-1"></i>

                                        {company.city}

                                        {company.state
                                            ? `, ${company.state}`
                                            : ''}
                                    </span>
                                )}

                            </div>

                        </div>

                    </div>


                    <div className="rr-admin-company-header-actions">

                        {/* Status */}
                        <span
                            className={`rr-status ${company?.status
                                    ? 'rr-status-active'
                                    : 'rr-status-inactive'
                                }`}
                        >
                            {company?.status ? 'Active' : 'Inactive'}
                        </span>

                        {/* Edit */}
                        <Link
                            href={`/super-admin/companies/${company.id}/edit`}
                            className="rr-btn rr-btn-secondary"
                        >
                            <i className="bi bi-pencil"></i>
                            Edit Company
                        </Link>


                        {/* ADD STEP 9 HERE */}

                        {company?.status ? (

                            <Link
                                href={`/super-admin/companies/${company.id}/deactivate`}
                                method="post"
                                as="button"
                                className="rr-btn rr-btn-secondary"
                                preserveScroll
                            >
                                <i className="bi bi-pause-circle"></i>
                                Deactivate
                            </Link>

                        ) : (

                            <Link
                                href={`/super-admin/companies/${company.id}/activate`}
                                method="post"
                                as="button"
                                className="rr-btn rr-btn-primary"
                                preserveScroll
                            >
                                <i className="bi bi-check-circle"></i>
                                Activate
                            </Link>

                        )}


                        {/* Archive */}

                        <button
                            type="button"
                            className="rr-btn rr-btn-danger"
                            onClick={() =>
                                setShowArchiveModal(true)
                            }
                        >
                            <i className="bi bi-archive"></i>

                            Archive
                        </button>

                    </div>

                </div>


                {/* COMPANY INFORMATION */}

                <div className="rr-company-info-grid">
                    
                    <InfoItem
                        label="Company Code"
                        value={company?.company_code}
                    />

                    <InfoItem
                        label="GST Number"
                        value={company?.gst_number}
                    />

                    <InfoItem
                        label="PAN Number"
                        value={company?.pan_number}
                    />

                    <InfoItem
                        label="Phone"
                        value={company?.phone}
                    />

                    <InfoItem
                        label="Email"
                        value={company?.email}
                    />

                    <InfoItem
                        label="Website"
                        value={company?.website}
                    />

                    <InfoItem
                        label="Branches"
                        value={stores.length}
                    />

                </div>

            </div>


            {/* BRANCHES */}

            <div className="rr-admin-panel">

                <div className="rr-admin-panel-header">

                    <div className="rr-admin-panel-heading">

                        <h2 className="rr-admin-panel-title">
                            Branches
                        </h2>

                        <div className="rr-admin-panel-description">
                            Manage company branches and enter the business application
                        </div>

                    </div>

                    <Link
                        href={`/super-admin/companies/${company.id}/branches/create`}
                        className="rr-btn rr-btn-primary"
                    >
                        <i className="bi bi-plus-lg"></i>
                        New Branch
                    </Link>

                </div>


                <div className="rr-admin-table-wrapper">

                    <table className="rr-admin-table">

                        <thead>
                            <tr>
                                <th>Branch</th>
                                <th>Branch Code</th>
                                <th>Contact Person</th>
                                <th>Contact</th>
                                <th>Location</th>
                                <th>Status</th>
                                <th className="text-end">
                                    Actions
                                </th>
                            </tr>
                        </thead>


                        <tbody>

                            {stores.length > 0 ? (

                                stores.map((store) => (

                                    <tr key={store.id}>

                                       
                                        <td>

                                            <div className="rr-branch-table-cell">


                                                <div className="rr-branch-table-icon">
                                                    <i className="bi bi-shop"></i>
                                                </div>

                                                <div>

                                                    <div className="rr-branch-table-name">
                                                        {store.store_name}
                                                    </div>

                                                </div>

                                            </div>

                                        </td>

                                        {/* Branch Code */}

                                        <td>
                                            <span className="rr-branch-code">
                                                {store.store_code || '-'}
                                            </span>
                                        </td>



                                        <td>
                                            {store.contact_person || '—'}
                                        </td>


                                        <td>

                                            <div>
                                                {store.phone || '—'}
                                            </div>

                                            {store.email && (
                                                <div className="rr-table-secondary">
                                                    {store.email}
                                                </div>
                                            )}

                                        </td>


                                        <td>

                                            <div>
                                                {store.city || '—'}
                                            </div>

                                            {store.state && (
                                                <div className="rr-table-secondary">
                                                    {store.state}
                                                </div>
                                            )}

                                        </td>


                                        <td>

                                            <span
                                                className={`rr-status ${store.status
                                                        ? 'rr-status-active'
                                                        : 'rr-status-inactive'
                                                    }`}
                                            >
                                                {store.status
                                                    ? 'Active'
                                                    : 'Inactive'}
                                            </span>

                                        </td>


                                        <td className="text-end">

                                            <div className="rr-table-actions">

                                                {store.status ? (

                                                    <Link
                                                        href={`/super-admin/companies/${company.id}/branches/${store.id}/enter`}
                                                        className="rr-btn rr-btn-primary"
                                                    >
                                                        <i className="bi bi-box-arrow-in-right"></i>
                                                        Enter Branch
                                                    </Link>

                                                ) : (

                                                    <button
                                                        type="button"
                                                        className="rr-btn rr-btn-secondary"
                                                        disabled
                                                    >
                                                        Inactive
                                                    </button>

                                                )}

                                                <Link
                                                    href={`/super-admin/companies/${company.id}/branches/${store.id}/edit`}
                                                    className="rr-icon-action"
                                                    title="Edit branch"
                                                >
                                                    <i className="bi bi-pencil"></i>
                                                </Link>

                                                {store.status ? (

                                                    <button
                                                        type="button"
                                                        className="rr-icon-action rr-icon-action-warning"
                                                        title="Deactivate branch"
                                                        onClick={() =>
                                                            setBranchAction({
                                                                store,
                                                                action: 'deactivate',
                                                            })
                                                        }
                                                    >
                                                        <i className="bi bi-pause-circle"></i>
                                                    </button>

                                                ) : (

                                                    <button
                                                        type="button"
                                                        className="rr-icon-action rr-icon-action-success"
                                                        title="Activate branch"
                                                        onClick={() =>
                                                            setBranchAction({
                                                                store,
                                                                action: 'activate',
                                                            })
                                                        }
                                                    >
                                                        <i className="bi bi-check-circle"></i>
                                                    </button>

                                                )}

                                            </div>

                                        </td>

                                    </tr>

                                ))

                            ) : (

                                <tr>

                                    <td colSpan="6">

                                        <div className="rr-empty-state">

                                            <div className="rr-empty-state-icon">
                                                <i className="bi bi-shop"></i>
                                            </div>

                                            <div className="rr-empty-state-title">
                                                No branches created
                                            </div>

                                            <div className="rr-empty-state-text">
                                                Create the first branch for this company.
                                            </div>

                                        </div>

                                    </td>

                                </tr>

                            )}

                        </tbody>

                    </table>

                </div>

            </div>

            {/* show archive confirmation */}

            <ConfirmModal
                show={showArchiveModal}

                title="Archive Company"

                message={
                    `Are you sure you want to archive "${company.company_name}"? ` +
                    `The company will be deactivated and moved to the archive.`
                }

                confirmText="Archive Company"

                icon="bi-archive"

                variant="danger"

                processing={archiving}

                onCancel={() => {

                    if (!archiving) {
                        setShowArchiveModal(false);
                    }

                }}

                onConfirm={() => {

                    setArchiving(true);

                    router.delete(
                        `/super-admin/companies/${company.id}`,
                        {
                            onFinish: () => {
                                setArchiving(false);
                            },

                            onError: () => {
                                setShowArchiveModal(false);
                            },
                        }
                    );

                }}
            />

            {/* branch activate confirmation */}

            <ConfirmModal
                show={Boolean(branchAction)}

                title={
                    branchAction?.action === 'deactivate'
                        ? 'Deactivate Branch'
                        : 'Activate Branch'
                }

                message={
                    branchAction
                        ? branchAction.action === 'deactivate'
                            ? `Deactivate "${branchAction.store.store_name}"? Users will no longer be able to enter this branch until it is activated again.`
                            : `Activate "${branchAction.store.store_name}"? Users with access will be able to enter this branch again.`
                        : ''
                }

                confirmText={
                    branchAction?.action === 'deactivate'
                        ? 'Deactivate Branch'
                        : 'Activate Branch'
                }

                icon={
                    branchAction?.action === 'deactivate'
                        ? 'bi-pause-circle'
                        : 'bi-check-circle'
                }

                variant={
                    branchAction?.action === 'deactivate'
                        ? 'danger'
                        : 'primary'
                }

                processing={processingBranch}

                onCancel={() => {
                    if (!processingBranch) {
                        setBranchAction(null);
                    }
                }}

                onConfirm={() => {

                    if (!branchAction) {
                        return;
                    }

                    setProcessingBranch(true);

                    const url =
                        `/super-admin/companies/${company.id}` +
                        `/branches/${branchAction.store.id}` +
                        `/${branchAction.action}`;

                    router.post(
                        url,
                        {},
                        {
                            preserveScroll: true,

                            onSuccess: () => {
                                setBranchAction(null);
                            },

                            onFinish: () => {
                                setProcessingBranch(false);
                            },
                        }
                    );
                }}
            />

        </SuperAdminLayout>
    );
}


function InfoItem({ label, value }) {

    return (
        <div className="rr-company-info-item">

            <div className="rr-company-info-label">
                {label}
            </div>

            <div className="rr-company-info-value">
                {value || '—'}
            </div>

        </div>
    );
}