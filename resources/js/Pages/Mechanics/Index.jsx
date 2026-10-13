import React, { useMemo, useState } from "react";

import { Head, Link, router, usePage } from "@inertiajs/react";

import BusinessLayout from "../../Layouts/BusinessLayout";

import MasterNavigation from "../../Components/Business/MasterNavigation";

import ConfirmModal from "../../Components/ConfirmModal";

/* Use the established professional master-index design system. */

import "../../../css/business/customers.css";

import "../../../css/business/mechanic-import.css";

export default function Index({

    mechanics,

    summary = {},

    filters = {},

}) {

    const { auth, business } = usePage().props;

    const user = auth?.user;

    const permissions = user?.permissions ?? [];

    const roles = user?.roles ?? [];

    const isSuperAdmin = Boolean(user?.is_super_user);

    const isCompanyAdmin = roles.includes("Company Admin");

    const can = (permission) => {

        if (isSuperAdmin || isCompanyAdmin) {

            return true;

        }

        return permissions.includes(permission);

    };

    const [search, setSearch] = useState(filters.search ?? "");

    const [status, setStatus] = useState(filters.status ?? "");

    const [perPage, setPerPage] = useState(

        String(filters.per_page ?? 20)

    );

    const [confirmation, setConfirmation] = useState(null);

    const [processing, setProcessing] = useState(false);

    const rows = mechanics?.data ?? [];

    const pageInfo = useMemo(

        () => ({

            from: mechanics?.from ?? 0,

            to: mechanics?.to ?? 0,

            total: mechanics?.total ?? 0,

        }),

        [mechanics]

    );

    const hasFilters = Boolean(

        String(search).trim() || status || perPage !== "20"

    );

    const applyFilters = (overrides = {}) => {

        const next = {

            search,

            status,

            per_page: perPage,

            ...overrides,

        };

        router.get("/mechanics", clean(next), {

            preserveState: true,

            preserveScroll: true,

            replace: true,

        });

    };

    const submitSearch = (event) => {

        event.preventDefault();

        applyFilters();

    };

    const changeStatus = (event) => {

        const value = event.target.value;

        setStatus(value);

        applyFilters({ status: value });

    };

    const changePerPage = (event) => {

        const value = event.target.value;

        setPerPage(value);

        applyFilters({ per_page: value });

    };

    const clearFilters = () => {

        setSearch("");

        setStatus("");

        setPerPage("20");

        router.get("/mechanics", {}, {

            preserveState: true,

            preserveScroll: true,

            replace: true,

        });

    };

    const openMechanic = (mechanic) => {

        if (can("mechanic.show")) {

            router.visit(`/mechanics/${mechanic.id}`);

        }

    };

    const askStatusChange = (mechanic) => {

        setConfirmation({

            type: "toggle-status",

            mechanic,

        });

    };

    const askRemove = (mechanic) => {

        setConfirmation({

            type: "remove-from-branch",

            mechanic,

        });

    };

    const closeConfirmation = () => {

        if (!processing) {

            setConfirmation(null);

        }

    };

    const confirmAction = () => {

        if (!confirmation || processing) {

            return;

        }

        const mechanic = confirmation.mechanic;

        const options = {

            preserveScroll: true,

            onStart: () => setProcessing(true),

            onFinish: () => setProcessing(false),

            onSuccess: () => setConfirmation(null),

        };

        if (confirmation.type === "toggle-status") {

            router.patch(

                `/mechanics/${mechanic.id}/toggle-status`,

                {},

                options

            );

            return;

        }

        router.delete(

            `/mechanics/${mechanic.id}/remove-from-branch`,

            options

        );

    };

    const modal = confirmationDetails(confirmation);

    return (

        <BusinessLayout>

            <Head title="Mechanics" />

            <div className="rr-customers-page">

                <MasterNavigation

                    active="mechanics"

                    title="Mechanics"

                    subtitle="Mechanic accounts, commissions and branch management"

                />

                <section className="rr-customers-header">

                    <div className="rr-customers-header-main">

                        <div className="rr-customers-header-icon">

                            <i className="bi bi-person-gear" />

                        </div>

                        <div>

                            <span className="rr-customers-eyebrow">

                                Mechanic Management

                            </span>

                            <h1>Mechanics</h1>

                            <p>

                                Manage mechanics available to {" "}

                                {business?.store_name ?? "the active branch"}.

                            </p>

                        </div>

                    </div>

                    <div className="rr-customers-header-actions">

                        <button

                            type="button"

                            className="rr-customer-toolbar-btn"

                            onClick={() =>

                                router.reload({ preserveScroll: true })

                            }

                        >

                            <i className="bi bi-arrow-clockwise" />

                            Refresh

                        </button>

                        {can("mechanic.create") && (

                            <a

                                href="/mechanics/import/template"

                                className="rr-customer-toolbar-btn rr-mechanic-template-btn"

                                title="Download mechanic Excel import template"

                            >

                                <i className="bi bi-file-earmark-excel" />

                                Download Template

                            </a>

                        )}

                        {can("mechanic.create") && (

                            <Link

                                href="/mechanics/import"

                                className="rr-customer-toolbar-btn rr-mechanic-import-btn"

                            >

                                <i className="bi bi-file-earmark-arrow-up" />

                                Import Mechanics

                            </Link>

                        )}

                        {can("mechanic.create") && (

                            <Link

                                href="/mechanics/create"

                                className="rr-customer-primary-btn"

                            >

                                <i className="bi bi-plus-lg" />

                                Add Mechanic

                            </Link>

                        )}

                    </div>

                </section>

                <section className="rr-customers-summary">

                    <SummaryCard

                        label="Total Mechanics"

                        value={summary.total ?? 0}

                        icon="bi-people"

                    />

                    <SummaryCard

                        label="Active"

                        value={summary.active ?? 0}

                        icon="bi-person-check"

                        tone="success"

                    />

                    <SummaryCard

                        label="Inactive"

                        value={summary.inactive ?? 0}

                        icon="bi-person-dash"

                        tone="muted"

                    />

                    <SummaryCard

                        label="Total Payable"

                        value={balance(summary.total_payable)}

                        icon="bi-wallet2"

                        tone="warning"

                    />

                </section>

                <section className="rr-customers-panel">

                    <div className="rr-customers-toolbar">

                        <form

                            className="rr-customers-search"

                            onSubmit={submitSearch}

                        >

                            <div className="rr-customers-search-box">

                                <i className="bi bi-search" />

                                <input

                                    type="text"

                                    value={search}

                                    placeholder="Search code, mechanic, mobile or city..."

                                    onChange={(event) =>

                                        setSearch(event.target.value)

                                    }

                                />

                                {search && (

                                    <button

                                        type="button"

                                        title="Clear search"

                                        onClick={() => {

                                            setSearch("");

                                            applyFilters({ search: "" });

                                        }}

                                    >

                                        <i className="bi bi-x-lg" />

                                    </button>

                                )}

                            </div>

                            <button

                                type="submit"

                                className="rr-customer-search-btn"

                            >

                                Search

                            </button>

                        </form>

                        <div className="rr-customers-filter-group">

                            <div className="rr-customers-filter">

                                <label>Status</label>

                                <select value={status} onChange={changeStatus}>

                                    <option value="">All</option>

                                    <option value="active">Active</option>

                                    <option value="inactive">Inactive</option>

                                </select>

                            </div>

                            <div className="rr-customers-filter">

                                <label>Rows</label>

                                <select value={perPage} onChange={changePerPage}>

                                    <option value="20">20</option>

                                    <option value="50">50</option>

                                    <option value="100">100</option>

                                </select>

                            </div>

                            {hasFilters && (

                                <button

                                    type="button"

                                    className="rr-customers-clear-btn"

                                    onClick={clearFilters}

                                >

                                    <i className="bi bi-funnel" />

                                    Clear

                                </button>

                            )}

                        </div>

                    </div>

                    <div className="rr-customers-grid-status">

                        <div>

                            <i className="bi bi-table" />

                            <span>Showing</span>

                            <strong>{pageInfo.from}–{pageInfo.to}</strong>

                            <span>of</span>

                            <strong>{pageInfo.total}</strong>

                            <span>mechanics</span>

                        </div>

                        <span className="rr-customers-row-limit">

                            <i className="bi bi-layout-three-columns" />

                            {perPage} rows per page

                        </span>

                    </div>

                    <div className="rr-customers-table-wrap">

                        <table className="rr-customers-table">

                            <thead>

                                <tr>

                                    <th className="rr-col-code">Code</th>

                                    <th>Mechanic</th>

                                    <th>Contact</th>

                                    <th>Location</th>

                                    <th>Commission</th>

                                    <th className="rr-col-credit">Payable</th>

                                    <th className="rr-col-status">Status</th>

                                    <th className="rr-col-actions">Actions</th>

                                </tr>

                            </thead>

                            <tbody>

                                {rows.length > 0 ? (

                                    rows.map((mechanic) => (

                                        <MechanicRow

                                            key={mechanic.id}

                                            mechanic={mechanic}

                                            can={can}

                                            onOpen={() => openMechanic(mechanic)}

                                            onRemove={() => askRemove(mechanic)}

                                            onStatusChange={() =>

                                                askStatusChange(mechanic)

                                            }

                                        />

                                    ))

                                ) : (

                                    <tr>

                                        <td colSpan="8">

                                            <EmptyState

                                                filtered={hasFilters}

                                                canCreate={can("mechanic.create")}

                                            />

                                        </td>

                                    </tr>

                                )}

                            </tbody>

                        </table>

                    </div>

                    {pageInfo.total > 0 && (

                        <div className="rr-customers-pagination">

                            <div className="rr-customers-pagination-info">

                                Page

                                <strong>{mechanics?.current_page ?? 1}</strong>

                                of

                                <strong>{mechanics?.last_page ?? 1}</strong>

                            </div>

                            <div className="rr-customers-pagination-links">

                                {(mechanics?.links ?? []).map((link, index) => (

                                    <PaginationLink

                                        key={`${link.label}-${index}`}

                                        link={link}

                                    />

                                ))}

                            </div>

                        </div>

                    )}

                </section>

                <ConfirmModal

                    show={Boolean(confirmation)}

                    title={modal.title}

                    message={modal.message}

                    confirmText={modal.confirmText}

                    cancelText="Cancel"

                    icon={modal.icon}

                    variant={modal.variant}

                    processing={processing}

                    confirmDisabled={!confirmation}

                    onConfirm={confirmAction}

                    onCancel={closeConfirmation}

                />

            </div>

        </BusinessLayout>

    );

}

function MechanicRow({

    mechanic,

    can,

    onOpen,

    onRemove,

    onStatusChange,

}) {

    const clickable = can("mechanic.show");

    return (

        <tr

            className={clickable ? "is-clickable" : ""}

            onClick={clickable ? onOpen : undefined}

        >

            <td>

                <div className="rr-customer-code">

                    {mechanic.mechanic_code ?? "—"}

                </div>

            </td>

            <td>

                <div className="rr-customer-name-cell">

                    <div className="rr-customer-avatar">

                        {initials(mechanic.mechanic_name)}

                    </div>

                    <div>

                        <strong>{mechanic.mechanic_name}</strong>

                        <div className="rr-customer-meta-line">

                            <span>Mechanic account</span>

                            {Number(mechanic.stores_count ?? 0) > 1 && (

                                <span className="rr-customer-shared-badge">

                                    <i className="bi bi-diagram-3" />

                                    {mechanic.stores_count} branches

                                </span>

                            )}

                        </div>

                    </div>

                </div>

            </td>

            <td>

                <div className="rr-customer-contact">

                    <strong>{mechanic.mobile ?? "—"}</strong>

                    {mechanic.email && <span>{mechanic.email}</span>}

                </div>

            </td>

            <td>

                <div className="rr-customer-location">

                    <strong>{mechanic.city ?? "No city"}</strong>

                    <span>{mechanic.state ?? "—"}</span>

                </div>

            </td>

            <td>

                <div className="rr-customer-credit">

                    <strong>{mechanic.pending_commissions_count ?? 0}</strong>

                    <span>pending entries</span>

                </div>

            </td>

            <td>

                <div className="rr-customer-credit">

                    <strong>

                        {mechanic.balance_label ?? balance(mechanic.balance)}

                    </strong>

                    <span>current payable</span>

                </div>

            </td>

            <td>

                <span

                    className={`rr-customer-status ${mechanic.status ? "active" : "inactive"

                        }`}

                >

                    <span />

                    {mechanic.status ? "Active" : "Inactive"}

                </span>

            </td>

            <td onClick={(event) => event.stopPropagation()}>

                <div className="rr-customer-actions">

                    {can("mechanic.show") && (

                        <Link

                            href={`/mechanics/${mechanic.id}`}

                            className="rr-customer-action-btn view"

                            title="View mechanic"

                        >

                            <i className="bi bi-eye" />

                        </Link>

                    )}

                    {can("mechanic.edit") && (

                        <Link

                            href={`/mechanics/${mechanic.id}/edit`}

                            className="rr-customer-action-btn edit"

                            title="Edit mechanic"

                        >

                            <i className="bi bi-pencil" />

                        </Link>

                    )}

                    {can("mechanic-ledger.view") && (

                        <Link

                            href={`/mechanic-ledger?mechanic_id=${mechanic.id}`}

                            className="rr-customer-action-btn view"

                            title="Open mechanic ledger"

                            style={{

                                borderColor: "#6642a1",

                                background: "#754db6",

                                color: "#ffffff",

                            }}

                        >

                            <i className="bi bi-journal-text" />

                        </Link>

                    )}

                    {can("mechanic.edit") && (

                        <button

                            type="button"

                            className={`rr-customer-action-btn ${mechanic.status ? "deactivate" : "activate"

                                }`}

                            title={

                                mechanic.status

                                    ? "Deactivate mechanic"

                                    : "Activate mechanic"

                            }

                            onClick={onStatusChange}

                        >

                            <i

                                className={

                                    mechanic.status

                                        ? "bi bi-pause-circle"

                                        : "bi bi-play-circle"

                                }

                            />

                        </button>

                    )}

                    {can("mechanic.delete") && (

                        <button

                            type="button"

                            className="rr-customer-action-btn remove"

                            title="Remove from this branch"

                            onClick={onRemove}

                        >

                            <i className="bi bi-person-dash" />

                        </button>

                    )}

                </div>

            </td>

        </tr>

    );

}

function SummaryCard({ label, value, icon, tone = "primary" }) {

    return (

        <article className={`rr-customer-summary-card ${tone}`}>

            <div className="rr-customer-summary-icon">

                <i className={`bi ${icon}`} />

            </div>

            <div>

                <span>{label}</span>

                <strong>{value}</strong>

            </div>

        </article>

    );

}

function PaginationLink({ link }) {

    if (!link.url) {

        return (

            <span

                className="rr-customer-page-link disabled"

                dangerouslySetInnerHTML={{ __html: link.label }}

            />

        );

    }

    return (

        <Link

            href={link.url}

            className={`rr-customer-page-link ${link.active ? "active" : ""}`}

            preserveState

            preserveScroll

            dangerouslySetInnerHTML={{ __html: link.label }}

        />

    );

}

function EmptyState({ filtered, canCreate }) {

    return (

        <div className="rr-customers-empty">

            <div className="rr-customers-empty-icon">

                <i className="bi bi-person-gear" />

            </div>

            <strong>No mechanics found</strong>

            <span>

                {filtered

                    ? "Change or clear the current filters."

                    : "Add the first mechanic for this branch."}

            </span>

            {!filtered && canCreate && (

                <Link

                    href="/mechanics/create"

                    className="rr-customer-primary-btn"

                >

                    <i className="bi bi-plus-lg" />

                    Add Mechanic

                </Link>

            )}

        </div>

    );

}

function confirmationDetails(confirmation) {

    if (!confirmation) {

        return {

            title: "Confirm Action",

            message: "Please confirm before continuing.",

            confirmText: "Confirm",

            icon: "bi-exclamation-triangle",

            variant: "danger",

        };

    }

    const name = confirmation.mechanic?.mechanic_name ?? "this mechanic";

    if (confirmation.type === "remove-from-branch") {

        return {

            title: "Remove Mechanic From This Branch?",

            message: `${name} will be removed from this branch. The mechanic master will remain available in other assigned branches.`,

            confirmText: "Remove From Branch",

            icon: "bi-person-dash",

            variant: "danger",

        };

    }

    const active = Boolean(confirmation.mechanic?.status);

    return {

        title: active ? "Deactivate Mechanic?" : "Activate Mechanic?",

        message: active

            ? `${name} will no longer be available for new branch transactions.`

            : `${name} will become available for new branch transactions.`,

        confirmText: active ? "Deactivate" : "Activate",

        icon: active ? "bi-pause-circle" : "bi-play-circle",

        variant: active ? "danger" : "primary",

    };

}

function clean(values) {

    return Object.fromEntries(

        Object.entries(values).filter(([, value]) => value !== "")

    );

}

function initials(name) {

    return String(name ?? "M")

        .trim()

        .split(/\s+/)

        .slice(0, 2)

        .map((part) => part[0])

        .join("")

        .toUpperCase();

}

function balance(value) {

    const amount = Number(value ?? 0);

    if (amount > 0.009) {

        return `₹${Math.abs(amount).toLocaleString("en-IN", {

            minimumFractionDigits: 2,

            maximumFractionDigits: 2,

        })} Cr`;

    }

    if (amount < -0.009) {

        return `₹${Math.abs(amount).toLocaleString("en-IN", {

            minimumFractionDigits: 2,

            maximumFractionDigits: 2,

        })} Dr`;

    }

    return "₹0.00";

}
