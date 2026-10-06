import React, { useState } from "react";
import { Head, Link, router, usePage } from "@inertiajs/react";

import BusinessLayout from "@/Layouts/BusinessLayout";
import MasterNavigation from "../../Components/Business/MasterNavigation";
import ConfirmModal from "../../Components/ConfirmModal";

import "../../../css/business/mechanics.css";

export default function Show({
    mechanic,
    recent_entries: entries = [],
}) {
    const { auth } = usePage().props;

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

    const [showStatusConfirmation, setShowStatusConfirmation] =
        useState(false);

    const [processing, setProcessing] =
        useState(false);

    const confirmStatusChange = () => {
        if (processing) {
            return;
        }

        router.patch(
            `/mechanics/${mechanic.id}/toggle-status`,
            {},
            {
                preserveScroll: true,
                onStart: () => setProcessing(true),
                onFinish: () => setProcessing(false),
                onSuccess: () => setShowStatusConfirmation(false),
            }
        );
    };

    return (
        <>
            <Head title={mechanic.mechanic_name} />

            <div className="rr-mec-page rr-mec-show-page">
                <style>{`
                    .rr-mec-show-page {
                        width: 100%;
                        min-width: 0;
                        padding: 4px 6px 10px !important;
                    }

                    .rr-mec-show-page .rr-mec-workspace {
                        width: 100% !important;
                        max-width: none !important;
                        margin: 0 !important;
                    }

                    .rr-mec-show-page .rr-master-nav-shell,
                    .rr-mec-show-page .rr-mec-profile-head,
                    .rr-mec-show-page .rr-mec-summary,
                    .rr-mec-show-page .rr-mec-show-grid,
                    .rr-mec-show-page .rr-mec-table-card,
                    .rr-mec-show-page .rr-mec-detail-card {
                        width: 100% !important;
                        max-width: none !important;
                        margin-left: 0 !important;
                        margin-right: 0 !important;
                    }

                    .rr-mec-show-page .rr-master-nav-shell {
                        margin-bottom: 8px !important;
                    }

                    .rr-mec-show-page .rr-mec-profile-head,
                    .rr-mec-show-page .rr-mec-summary,
                    .rr-mec-show-page .rr-mec-show-grid,
                    .rr-mec-show-page .rr-mec-table-card {
                        margin-bottom: 8px !important;
                    }

                    .rr-mec-show-page .rr-mec-profile-head {
                        padding: 12px 14px !important;
                    }

                    .rr-mec-show-page .rr-mec-summary {
                        gap: 8px !important;
                    }

                    .rr-mec-show-page .rr-mec-show-grid {
                        gap: 8px !important;
                    }

                    @media (max-width: 760px) {
                        .rr-mec-show-page {
                            padding: 3px !important;
                        }
                    }
                `}</style>

                <div
                    className="rr-mec-workspace"
                    style={{
                        width: "100%",
                        maxWidth: "none",
                        margin: 0,
                    }}
                >
                    <MasterNavigation
                        active="mechanics"
                        title="Mechanic Details"
                        subtitle="Profile, commission payable and recent ledger activity"
                    />

                    <section className="rr-mec-profile-head">
                        <div className="rr-mec-large-avatar">
                            {initials(mechanic.mechanic_name)}
                        </div>

                        <div className="rr-mec-profile-name">
                            <span>{mechanic.mechanic_code}</span>
                            <h1>{mechanic.mechanic_name}</h1>
                            <p>
                                {mechanic.mobile || "No mobile number"}
                                {mechanic.city ? ` • ${mechanic.city}` : ""}
                            </p>
                        </div>

                        <div className="rr-mec-profile-actions">
                            <Link
                                className="rr-mec-btn secondary"
                                href="/mechanics"
                            >
                                <i className="bi bi-arrow-left" />
                                Back
                            </Link>

                            {can("mechanic.edit") && (
                                <button
                                    className={`rr-mec-btn ${mechanic.status ? "warning" : "success"
                                        }`}
                                    type="button"
                                    onClick={() =>
                                        setShowStatusConfirmation(true)
                                    }
                                >
                                    <i
                                        className={`bi ${mechanic.status
                                                ? "bi-pause-circle"
                                                : "bi-play-circle"
                                            }`}
                                    />
                                    {mechanic.status
                                        ? "Deactivate"
                                        : "Activate"}
                                </button>
                            )}

                            {can("mechanic.edit") && (
                                <Link
                                    className="rr-mec-btn primary"
                                    href={`/mechanics/${mechanic.id}/edit`}
                                >
                                    <i className="bi bi-pencil-square" />
                                    Edit Mechanic
                                </Link>
                            )}
                        </div>
                    </section>

                    <section className="rr-mec-summary">
                        <Summary
                            label="Current Payable"
                            value={mechanic.balance_label ?? "₹0.00"}
                            icon="bi-wallet2"
                            tone="purple"
                        />

                        <Summary
                            label="Sales"
                            value={mechanic.sales_count ?? 0}
                            icon="bi-receipt"
                            tone="blue"
                        />

                        <Summary
                            label="Commissions"
                            value={mechanic.commissions_count ?? 0}
                            icon="bi-percent"
                            tone="green"
                        />

                        <Summary
                            label="Payments"
                            value={mechanic.payments_count ?? 0}
                            icon="bi-cash-stack"
                            tone="red"
                        />
                    </section>

                    <div className="rr-mec-show-grid">
                        <section className="rr-mec-detail-card">
                            <CardTitle
                                icon="bi-person-vcard"
                                text="Mechanic Information"
                            />

                            <dl>
                                <Detail
                                    label="Mechanic Code"
                                    value={mechanic.mechanic_code}
                                />
                                <Detail
                                    label="Mobile"
                                    value={mechanic.mobile}
                                />
                                <Detail
                                    label="Email"
                                    value={mechanic.email}
                                />
                                <Detail
                                    label="Address"
                                    value={mechanic.address}
                                />
                                <Detail
                                    label="City"
                                    value={mechanic.city}
                                />
                                <Detail
                                    label="State"
                                    value={mechanic.state}
                                />
                                <Detail
                                    label="Pincode"
                                    value={mechanic.pincode}
                                />

                                <div>
                                    <dt>Status</dt>
                                    <dd>
                                        <span
                                            className={`rr-mec-status ${mechanic.status
                                                    ? "active"
                                                    : "inactive"
                                                }`}
                                        >
                                            {mechanic.status
                                                ? "Active"
                                                : "Inactive"}
                                        </span>
                                    </dd>
                                </div>
                            </dl>
                        </section>

                        <section className="rr-mec-detail-card">
                            <CardTitle
                                icon="bi-lightning-charge"
                                text="Quick Actions"
                            />

                            <div className="rr-mec-quick-actions">
                                {can("mechanic-payment.create") && (
                                    <Link
                                        href={`/mechanic-payments/create?mechanic_id=${mechanic.id}`}
                                        style={QUICK_ACTION_STYLES.payment}
                                    >
                                        <i className="bi bi-cash-stack" />
                                        <span>
                                            <strong>Record Payment</strong>
                                            <small>Pay commission balance</small>
                                        </span>
                                        <i className="bi bi-chevron-right ms-auto" />
                                    </Link>
                                )}

                                {can("mechanic-ledger.view") && (
                                    <Link
                                        href={`/mechanic-ledger?mechanic_id=${mechanic.id}`}
                                        style={QUICK_ACTION_STYLES.ledger}
                                    >
                                        <i className="bi bi-journal-text" />
                                        <span>
                                            <strong>Open Ledger</strong>
                                            <small>View all transactions</small>
                                        </span>
                                        <i className="bi bi-chevron-right ms-auto" />
                                    </Link>
                                )}

                                <Link
                                    href="/mechanics"
                                    style={QUICK_ACTION_STYLES.list}
                                >
                                    <i className="bi bi-people" />
                                    <span>
                                        <strong>Mechanic Register</strong>
                                        <small>Return to mechanic list</small>
                                    </span>
                                    <i className="bi bi-chevron-right ms-auto" />
                                </Link>
                            </div>
                        </section>
                    </div>

                    <section className="rr-mec-table-card">
                        <CardTitle
                            icon="bi-clock-history"
                            text="Recent Ledger Entries"
                        />

                        <div className="rr-mec-table-wrap">
                            <table>
                                <thead>
                                    <tr>
                                        <th>Date</th>
                                        <th>Type</th>
                                        <th>Reference</th>
                                        <th>Remarks</th>
                                        <th className="number">Debit</th>
                                        <th className="number">Credit</th>
                                    </tr>
                                </thead>

                                <tbody>
                                    {entries.length ? (
                                        entries.map((entry) => (
                                            <tr key={entry.id}>
                                                <td>
                                                    {formatDate(
                                                        entry.date ??
                                                        entry.transaction_date
                                                    )}
                                                </td>
                                                <td>
                                                    <span className="rr-mec-ledger-type">
                                                        {formatType(
                                                            entry.type ??
                                                            entry.transaction_type
                                                        )}
                                                    </span>
                                                </td>
                                                <td>
                                                    {entry.reference_no || "—"}
                                                </td>
                                                <td>{entry.remarks || "—"}</td>
                                                <td className="number debit">
                                                    {Number(entry.debit) > 0
                                                        ? money(entry.debit)
                                                        : "—"}
                                                </td>
                                                <td className="number credit">
                                                    {Number(entry.credit) > 0
                                                        ? money(entry.credit)
                                                        : "—"}
                                                </td>
                                            </tr>
                                        ))
                                    ) : (
                                        <tr>
                                            <td colSpan="6">
                                                <div className="rr-mec-empty">
                                                    <i className="bi bi-journal-x" />
                                                    <strong>
                                                        No ledger entries available
                                                    </strong>
                                                    <span>
                                                        Transactions will appear here
                                                        after commissions or payments.
                                                    </span>
                                                </div>
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <ConfirmModal
                        show={showStatusConfirmation}
                        title={
                            mechanic.status
                                ? "Deactivate Mechanic?"
                                : "Activate Mechanic?"
                        }
                        message={
                            mechanic.status
                                ? `${mechanic.mechanic_name} will no longer be available for new branch transactions.`
                                : `${mechanic.mechanic_name} will become available for new branch transactions.`
                        }
                        confirmText={
                            mechanic.status ? "Deactivate" : "Activate"
                        }
                        cancelText="Cancel"
                        icon={
                            mechanic.status
                                ? "bi-pause-circle"
                                : "bi-play-circle"
                        }
                        variant={mechanic.status ? "danger" : "primary"}
                        processing={processing}
                        onConfirm={confirmStatusChange}
                        onCancel={() => {
                            if (!processing) {
                                setShowStatusConfirmation(false);
                            }
                        }}
                    />
                </div>
            </div>
        </>
    );
}

const QUICK_ACTION_STYLES = {
    payment: {
        borderLeft: "4px solid #1f8a56",
        background: "#eef9f3",
        color: "#185f3e",
    },
    ledger: {
        borderLeft: "4px solid #7050b1",
        background: "#f4f0fb",
        color: "#52358d",
    },
    list: {
        borderLeft: "4px solid #2670b8",
        background: "#eef5fc",
        color: "#20598f",
    },
};

function Summary({ label, value, icon, tone }) {
    return (
        <article className={`rr-mec-summary-card ${tone}`}>
            <span>
                <i className={`bi ${icon}`} />
            </span>
            <div>
                <small>{label}</small>
                <strong>{value ?? 0}</strong>
            </div>
        </article>
    );
}

function CardTitle({ icon, text }) {
    return (
        <div className="rr-mec-card-title">
            <i className={`bi ${icon}`} />
            <strong>{text}</strong>
        </div>
    );
}

function Detail({ label, value }) {
    return (
        <div>
            <dt>{label}</dt>
            <dd>{value || "—"}</dd>
        </div>
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

function money(value) {
    return new Intl.NumberFormat("en-IN", {
        style: "currency",
        currency: "INR",
        minimumFractionDigits: 2,
    }).format(Number(value ?? 0));
}

function formatDate(value) {
    if (!value) {
        return "—";
    }

    const normalized = String(value).slice(0, 10);
    const parsed = new Date(`${normalized}T00:00:00`);

    if (Number.isNaN(parsed.getTime())) {
        return "—";
    }

    return parsed.toLocaleDateString("en-IN");
}

function formatType(value) {
    if (!value) {
        return "—";
    }

    return String(value)
        .replaceAll("_", " ")
        .replace(/\b\w/g, (letter) => letter.toUpperCase());
}

Show.layout = (page) => <BusinessLayout>{page}</BusinessLayout>;
