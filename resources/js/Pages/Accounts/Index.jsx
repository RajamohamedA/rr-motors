import React, { useMemo, useState } from "react";
import { Head, Link, router, usePage } from "@inertiajs/react";

import BusinessLayout from "@/Layouts/BusinessLayout";
import MasterNavigation from "../../Components/Business/MasterNavigation";
import ConfirmModal from "../../Components/ConfirmModal";

import "../../../css/business/accounts.css";

export default function Index({
    accounts = {},
    filters = {},
    summary = {},
    accountTypes = {},
    context = {},
}) {
    const { auth = {}, flash = {} } = usePage().props;
    const permissions = auth?.user?.permissions ?? [];
    const isPrivileged =
        Boolean(auth?.user?.is_super_user) ||
        (auth?.user?.roles ?? []).includes("Company Admin");

    const can = (permission) => isPrivileged || permissions.includes(permission);

    const [form, setForm] = useState({
        search: filters.search ?? "",
        account_type: filters.account_type ?? "all",
        status: filters.status ?? "active",
        per_page: String(filters.per_page ?? 20),
    });
    const [filtersOpen, setFiltersOpen] = useState(true);
    const [selectedAccount, setSelectedAccount] = useState(null);
    const [processing, setProcessing] = useState(false);

    const rows = accounts?.data ?? [];
    const hasFilters = useMemo(
        () =>
            form.search.trim() !== "" ||
            form.account_type !== "all" ||
            form.status !== "active" ||
            form.per_page !== "20",
        [form],
    );

    const applyFilters = (event) => {
        event?.preventDefault();
        router.get("/accounts", cleanParams(form), {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    };

    const clearFilters = () => {
        const cleared = {
            search: "",
            account_type: "all",
            status: "active",
            per_page: "20",
        };
        setForm(cleared);
        router.get(
            "/accounts",
            {},
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            },
        );
    };

    const confirmStatusChange = () => {
        if (!selectedAccount || processing) return;

        setProcessing(true);
        router.patch(
            `/accounts/${selectedAccount.id}/toggle-status`,
            {},
            {
                preserveScroll: true,
                onFinish: () => {
                    setProcessing(false);
                    setSelectedAccount(null);
                },
            },
        );
    };

    return (
        <>
            <Head title="Cash & Bank Accounts" />

            <main className="rr-acc-page rr-acc-index-page">
                <MasterNavigation
                    active="accounts"
                    title="Cash & Bank Accounts"
                    subtitle={
                        context.store_name
                            ? `${context.company_name ?? "Company"} • ${context.store_name}`
                            : "Branch cash, bank and digital payment accounts"
                    }
                />

                {(flash.success || flash.error) && (
                    <div
                        className={`rr-acc-alert ${flash.error ? "error" : "success"}`}
                        role="alert"
                    >
                        <i
                            className={`bi ${flash.error ? "bi-exclamation-octagon" : "bi-check-circle"
                                }`}
                        />
                        <span>{flash.error ?? flash.success}</span>
                    </div>
                )}

                <section className="rr-acc-commandbar">
                    <div className="rr-acc-command-title">
                        <span className="rr-acc-command-icon">
                            <i className="bi bi-bank" />
                        </span>
                        <div>
                            <span className="rr-acc-eyebrow">ACCOUNT MASTER</span>
                            <h1>Cash & Bank Register</h1>
                            <p>Monitor available balances and payment channels.</p>
                        </div>
                    </div>

                    <div className="rr-acc-command-actions">
                        <button
                            type="button"
                            className={`rr-acc-btn filter ${filtersOpen ? "active" : ""}`}
                            onClick={() => setFiltersOpen((open) => !open)}
                        >
                            <i className="bi bi-funnel" />
                            Filters
                            {hasFilters && <span className="rr-acc-filter-dot" />}
                        </button>

                        {can("account.create") && (
                            <Link className="rr-acc-btn primary" href="/accounts/create">
                                <i className="bi bi-plus-lg" />
                                New Account
                            </Link>
                        )}
                    </div>
                </section>

                <section className="rr-acc-summary-grid" aria-label="Account balance summary">
                    <SummaryCard
                        tone="navy"
                        icon="bi-wallet2"
                        label="Total Available"
                        value={money(summary.total_balance)}
                        note={`${number(summary.active_accounts)} active accounts`}
                    />
                    <SummaryCard
                        tone="green"
                        icon="bi-cash-stack"
                        label="Cash in Hand"
                        value={money(summary.cash_balance)}
                        note="Physical cash balance"
                    />
                    <SummaryCard
                        tone="blue"
                        icon="bi-bank"
                        label="Bank Balance"
                        value={money(summary.bank_balance)}
                        note="All active bank accounts"
                    />
                    <SummaryCard
                        tone="violet"
                        icon="bi-phone"
                        label="Digital Balance"
                        value={money(summary.digital_balance)}
                        note="UPI and card collections"
                    />
                </section>

                {filtersOpen && (
                    <form className="rr-acc-filter-panel" onSubmit={applyFilters}>
                        <div className="rr-acc-panel-heading">
                            <div>
                                <i className="bi bi-sliders2" />
                                <span>Account Filters</span>
                            </div>
                            <small>Search and narrow the branch account register</small>
                        </div>

                        <div className="rr-acc-filter-grid">
                            <Field label="Search account" icon="bi-search" wide>
                                <input
                                    type="search"
                                    value={form.search}
                                    placeholder="Code, account, bank, number or UPI ID"
                                    onChange={(event) =>
                                        setForm((current) => ({
                                            ...current,
                                            search: event.target.value,
                                        }))
                                    }
                                />
                            </Field>

                            <Field label="Account type" icon="bi-grid">
                                <select
                                    value={form.account_type}
                                    onChange={(event) =>
                                        setForm((current) => ({
                                            ...current,
                                            account_type: event.target.value,
                                        }))
                                    }
                                >
                                    <option value="all">All account types</option>
                                    {Object.entries(accountTypes).map(([value, label]) => (
                                        <option key={value} value={value}>
                                            {label}
                                        </option>
                                    ))}
                                </select>
                            </Field>

                            <Field label="Status" icon="bi-toggle-on">
                                <select
                                    value={form.status}
                                    onChange={(event) =>
                                        setForm((current) => ({
                                            ...current,
                                            status: event.target.value,
                                        }))
                                    }
                                >
                                    <option value="active">Active</option>
                                    <option value="inactive">Inactive</option>
                                    <option value="all">All statuses</option>
                                </select>
                            </Field>

                            <Field label="Rows" icon="bi-list-ol">
                                <select
                                    value={form.per_page}
                                    onChange={(event) =>
                                        setForm((current) => ({
                                            ...current,
                                            per_page: event.target.value,
                                        }))
                                    }
                                >
                                    <option value="20">20 rows</option>
                                    <option value="50">50 rows</option>
                                    <option value="100">100 rows</option>
                                </select>
                            </Field>
                        </div>

                        <div className="rr-acc-filter-actions">
                            <button
                                type="button"
                                className="rr-acc-btn secondary"
                                onClick={clearFilters}
                                disabled={!hasFilters}
                            >
                                <i className="bi bi-arrow-counterclockwise" />
                                Reset
                            </button>
                            <button type="submit" className="rr-acc-btn apply">
                                <i className="bi bi-search" />
                                Apply Filters
                            </button>
                        </div>
                    </form>
                )}

                <section className="rr-acc-register">
                    <header className="rr-acc-register-head">
                        <div>
                            <i className="bi bi-list-check" />
                            <div>
                                <h2>Account Register</h2>
                                <p>
                                    Showing {number(accounts.from)}–{number(accounts.to)} of{" "}
                                    {number(accounts.total)} accounts
                                </p>
                            </div>
                        </div>
                        <span className="rr-acc-live-badge">
                            <i className="bi bi-circle-fill" /> Live balances
                        </span>
                    </header>

                    <div className="rr-acc-table-wrap">
                        <table className="rr-acc-table">
                            <thead>
                                <tr>
                                    <th className="code">Code</th>
                                    <th>Account</th>
                                    <th>Type</th>
                                    <th>Payment Channel</th>
                                    <th className="amount">Opening</th>
                                    <th className="amount">Current Balance</th>
                                    <th>Status</th>
                                    <th className="actions">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                {rows.length > 0 ? (
                                    rows.map((account) => (
                                        <AccountRow
                                            key={account.id}
                                            account={account}
                                            canEdit={can("account.edit")}
                                            onToggle={() => setSelectedAccount(account)}
                                        />
                                    ))
                                ) : (
                                    <tr>
                                        <td colSpan="8">
                                            <EmptyState canCreate={can("account.create")} />
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>

                    <Pagination links={accounts.links ?? []} />
                </section>
            </main>

            <ConfirmModal
                show={Boolean(selectedAccount)}
                title={
                    selectedAccount?.status ? "Deactivate Account" : "Activate Account"
                }
                message={
                    selectedAccount?.status
                        ? `Deactivate ${selectedAccount?.account_name}? It will no longer be available for new transactions.`
                        : `Activate ${selectedAccount?.account_name}? It will become available for transactions.`
                }
                confirmText={selectedAccount?.status ? "Deactivate" : "Activate"}
                icon={selectedAccount?.status ? "bi-pause-circle" : "bi-play-circle"}
                variant={selectedAccount?.status ? "danger" : "primary"}
                processing={processing}
                onCancel={() => !processing && setSelectedAccount(null)}
                onConfirm={confirmStatusChange}
            />
        </>
    );
}

function SummaryCard({ tone, icon, label, value, note }) {
    return (
        <article className={`rr-acc-summary-card ${tone}`}>
            <span className="rr-acc-summary-icon">
                <i className={`bi ${icon}`} />
            </span>
            <div>
                <small>{label}</small>
                <strong>{value}</strong>
                <p>{note}</p>
            </div>
        </article>
    );
}

function Field({ label, icon, wide = false, children }) {
    return (
        <label className={`rr-acc-field ${wide ? "wide" : ""}`}>
            <span>
                <i className={`bi ${icon}`} />
                {label}
            </span>
            <div className="rr-acc-input-shell">{children}</div>
        </label>
    );
}

function AccountRow({ account, canEdit, onToggle }) {
    const detail =
        account.bank_name || account.masked_account_number || account.upi_id;

    return (
        <tr
            className={!account.status ? "inactive" : ""}
            onDoubleClick={() => router.visit(`/accounts/${account.id}`)}
        >
            <td>
                <Link className="rr-acc-code" href={`/accounts/${account.id}`}>
                    {account.account_code}
                </Link>
                {account.is_system && <span className="rr-acc-system">SYSTEM</span>}
            </td>
            <td>
                <div className="rr-acc-account-cell">
                    <span className={`rr-acc-type-icon ${account.account_type}`}>
                        <i className={`bi ${typeIcon(account.account_type)}`} />
                    </span>
                    <div>
                        <Link href={`/accounts/${account.id}`}>{account.account_name}</Link>
                        <small>{detail || "Branch account"}</small>
                    </div>
                </div>
            </td>
            <td>
                <span className={`rr-acc-type-badge ${account.account_type}`}>
                    {account.account_type_label}
                </span>
            </td>
            <td>
                <span className="rr-acc-channel">
                    <i className="bi bi-credit-card-2-front" />
                    {paymentMode(account.payment_mode)}
                </span>
            </td>
            <td className="amount muted">{money(account.opening_balance)}</td>
            <td
                className={`amount balance ${Number(account.current_balance) < 0 ? "negative" : ""
                    }`}
            >
                {money(account.current_balance)}
            </td>
            <td>
                <span
                    className={`rr-acc-status ${account.status ? "active" : "inactive"}`}
                >
                    <i className="bi bi-circle-fill" />
                    {account.status ? "Active" : "Inactive"}
                </span>
            </td>
            <td>
                <div className="rr-acc-row-actions">
                    <Link
                        className="rr-acc-icon-btn view"
                        href={`/accounts/${account.id}`}
                        title="View account"
                    >
                        <i className="bi bi-eye" />
                    </Link>
                    {canEdit && (
                        <Link
                            className="rr-acc-icon-btn edit"
                            href={`/accounts/${account.id}/edit`}
                            title="Edit account"
                        >
                            <i className="bi bi-pencil-square" />
                        </Link>
                    )}
                    {canEdit && !account.is_system && (
                        <button
                            type="button"
                            className={`rr-acc-icon-btn ${account.status ? "disable" : "enable"
                                }`}
                            title={account.status ? "Deactivate account" : "Activate account"}
                            onClick={onToggle}
                        >
                            <i
                                className={`bi ${account.status ? "bi-pause-circle" : "bi-play-circle"
                                    }`}
                            />
                        </button>
                    )}
                </div>
            </td>
        </tr>
    );
}

function EmptyState({ canCreate }) {
    return (
        <div className="rr-acc-empty">
            <span>
                <i className="bi bi-bank" />
            </span>
            <h3>No accounts found</h3>
            <p>Change the filters or create a Cash/Bank account for this branch.</p>
            {canCreate && (
                <Link className="rr-acc-btn primary" href="/accounts/create">
                    <i className="bi bi-plus-lg" /> New Account
                </Link>
            )}
        </div>
    );
}

function Pagination({ links }) {
    if (!Array.isArray(links) || links.length <= 3) return null;

    return (
        <nav className="rr-acc-pagination" aria-label="Account pages">
            {links.map((link, index) => (
                <Link
                    key={`${link.label}-${index}`}
                    href={link.url ?? "#"}
                    preserveScroll
                    preserveState
                    className={`${link.active ? "active" : ""} ${!link.url ? "disabled" : ""
                        }`}
                    dangerouslySetInnerHTML={{ __html: link.label }}
                    onClick={(event) => !link.url && event.preventDefault()}
                />
            ))}
        </nav>
    );
}

function cleanParams(values) {
    return Object.fromEntries(
        Object.entries(values).filter(([, value]) => value !== "" && value != null),
    );
}

function typeIcon(type) {
    return (
        {
            cash: "bi-cash-stack",
            bank: "bi-bank",
            upi: "bi-phone",
            card: "bi-credit-card",
            other: "bi-wallet2",
        }[type] ?? "bi-wallet2"
    );
}

function paymentMode(value) {
    return (
        {
            cash: "Cash",
            gpay: "GPay / UPI",
            bank_transfer: "Bank Transfer",
            card: "Card",
            cheque: "Cheque",
            other: "Other",
        }[value] ??
        value?.replaceAll("_", " ") ??
        "—"
    );
}

function money(value) {
    return new Intl.NumberFormat("en-IN", {
        style: "currency",
        currency: "INR",
        minimumFractionDigits: 2,
    }).format(Number(value ?? 0));
}

function number(value) {
    return new Intl.NumberFormat("en-IN").format(Number(value ?? 0));
}

Index.layout = (page) => <BusinessLayout>{page}</BusinessLayout>;
