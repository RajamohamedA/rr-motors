import { Head, Link, router } from '@inertiajs/react';
import { useMemo, useState } from 'react';

import SuperAdminLayout from '../../../Layouts/SuperAdminLayout';

export default function Index({ companies = [] }) {
    const [search, setSearch] = useState('');
    const [statusFilter, setStatusFilter] = useState('active');

    const filteredCompanies = useMemo(() => {

        const term = search.trim().toLowerCase();

        return companies.filter((company) => {

            /*
            |--------------------------------------------------------------------------
            | Archive filter
            |--------------------------------------------------------------------------
            */

            if (
                statusFilter === 'active' &&
                company.deleted_at
            ) {
                return false;
            }

            if (
                statusFilter === 'archived' &&
                !company.deleted_at
            ) {
                return false;
            }


            /*
            |--------------------------------------------------------------------------
            | Search
            |--------------------------------------------------------------------------
            */

            if (!term) {
                return true;
            }

            return [
                company.company_name,
                company.owner_name,
                company.email,
                company.phone,
                company.gst_number,
                company.city,
            ]
                .filter(Boolean)
                .some((value) =>
                    String(value)
                        .toLowerCase()
                        .includes(term)
                );

        });

    }, [
        companies,
        search,
        statusFilter,
    ]);

    return (
        <>
            <Head title="Companies" />

            <div className="rr-admin-panel">

                <div className="rr-admin-panel-header">

                    <div className="rr-admin-panel-heading">
                        <h2 className="rr-admin-panel-title">
                            Companies
                        </h2>

                        <div className="rr-admin-panel-description">
                            Manage companies and access their branches
                        </div>
                    </div>

                    <div className="d-flex gap-2">

                        <button
                            type="button"
                            className="rr-btn rr-btn-secondary"
                            onClick={() => router.reload()}
                        >
                            <i className="bi bi-arrow-clockwise"></i>
                            Refresh
                        </button>

                        <Link
                            href="/super-admin/companies/create"
                            className="rr-btn rr-btn-primary"
                        >
                            <i className="bi bi-plus-lg"></i>
                            New Company
                        </Link>

                    </div>

                </div>


                {/* Toolbar */}

                <div className="rr-company-toolbar">

                    <div className="rr-company-search">

                        <i className="bi bi-search"></i>

                        <input
                            type="text"
                            value={search}
                            onChange={(event) =>
                                setSearch(event.target.value)
                            }
                            placeholder="Search company, owner, GST, city..."
                        />

                    </div>

                    <select
                        className="rr-company-filter"
                        value={statusFilter}
                        onChange={(event) =>
                            setStatusFilter(event.target.value)
                        }
                    >
                        <option value="active">
                            Current Companies
                        </option>

                        <option value="archived">
                            Archived Companies
                        </option>

                        <option value="all">
                            All Companies
                        </option>
                    </select>


                    <div className="rr-company-result-count">
                        {filteredCompanies.length} companies
                    </div>

                </div>


                {/* Table */}

                <div className="rr-admin-table-wrapper">

                    <table className="rr-admin-table rr-company-table">

                        <thead>

                            <tr>
                                <th>Company</th>
                                <th>Company Code</th>
                                <th>Owner</th>
                                <th>Contact</th>
                                <th>Location</th>
                                <th className="text-center">
                                    Branches
                                </th>
                                <th>Status</th>
                                <th className="text-end">
                                    Actions
                                </th>
                            </tr>

                        </thead>


                        <tbody>

                            {filteredCompanies.length > 0 ? (

                                filteredCompanies.map((company) => (

                                    <tr key={company.id}>

                                        <td>

                                            <div className="rr-company-list-cell">

                                                <div className="rr-company-list-logo">

                                                    {company.logo ? (
                                                        <img
                                                            src={`/storage/${company.logo}`}
                                                            alt={company.company_name}
                                                        />
                                                    ) : (
                                                        <i className="bi bi-building"></i>
                                                    )}

                                                </div>

                                                <div className="rr-company-list-content">

                                                    <div className="rr-company-list-name">
                                                        {company.company_name}
                                                    </div>

                                                    <div className="rr-company-list-meta">
                                                        {company.gst_number
                                                            ? `GST: ${company.gst_number}`
                                                            : 'GST not available'}
                                                    </div>

                                                </div>

                                            </div>

                                        </td>

                                        <td>
                                            <span className="rr-company-code">
                                                {company.company_code || '-'}
                                            </span>
                                        </td>


                                        <td>
                                            {company.owner_name || '—'}
                                        </td>


                                        <td>

                                            <div>
                                                {company.phone || '—'}
                                            </div>

                                            {company.email && (
                                                <div className="rr-table-secondary">
                                                    {company.email}
                                                </div>
                                            )}

                                        </td>


                                        <td>

                                            <div>
                                                {company.city || '—'}
                                            </div>

                                            {company.state && (
                                                <div className="rr-table-secondary">
                                                    {company.state}
                                                </div>
                                            )}

                                        </td>


                                        <td className="text-center">

                                            <span className="rr-count-badge">
                                                {company.stores_count ?? 0}
                                            </span>

                                        </td>


                                        <td>

                                            {company.deleted_at ? (

                                                <span className="rr-status rr-status-danger">
                                                    Archived
                                                </span>

                                            ) : (

                                                <span
                                                    className={`rr-status ${company.status
                                                            ? 'rr-status-active'
                                                            : 'rr-status-inactive'
                                                        }`}
                                                >
                                                    {company.status
                                                        ? 'Active'
                                                        : 'Inactive'}
                                                </span>

                                            )}

                                        </td>


                                        <td className="text-end">

                                            <div className="rr-table-actions">

                                                {company.deleted_at ? (

                                                    <Link
                                                        href={`/super-admin/companies/${company.id}/restore`}
                                                        method="post"
                                                        as="button"
                                                        className="rr-btn rr-btn-primary"
                                                        preserveScroll
                                                    >
                                                        <i className="bi bi-arrow-counterclockwise"></i>

                                                        Restore
                                                    </Link>

                                                ) : (

                                                    <>
                                                        <Link
                                                            href={`/super-admin/companies/${company.id}`}
                                                            className="rr-icon-action rr-icon-action-primary"
                                                            title="Open company"
                                                        >
                                                            <i className="bi bi-box-arrow-up-right"></i>
                                                        </Link>

                                                        <Link
                                                            href={`/super-admin/companies/${company.id}/edit`}
                                                            className="rr-icon-action"
                                                            title="Edit company"
                                                        >
                                                            <i className="bi bi-pencil"></i>
                                                        </Link>
                                                    </>

                                                )}

                                            </div>

                                        </td>

                                    </tr>

                                ))

                            ) : (

                                <tr>

                                    <td colSpan="7">

                                        <div className="rr-empty-state">

                                            <div className="rr-empty-state-icon">
                                                <i className="bi bi-buildings"></i>
                                            </div>

                                            <div className="rr-empty-state-title">
                                                No companies found
                                            </div>

                                            <div className="rr-empty-state-text">
                                                Try another search or create a new company.
                                            </div>

                                        </div>

                                    </td>

                                </tr>

                            )}

                        </tbody>

                    </table>

                </div>

            </div>
        </>
    );
}

Index.layout = (page) => (
    <SuperAdminLayout
        title="Companies"
        subtitle="Company management and branch access"
        breadcrumbs={[
            {
                label: 'Companies',
            },
        ]}
    >
        {page}
    </SuperAdminLayout>
);