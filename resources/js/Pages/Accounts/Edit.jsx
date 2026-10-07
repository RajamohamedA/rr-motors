import React, { useState } from "react";
import { Head, Link, useForm } from "@inertiajs/react";

import BusinessLayout from "@/Layouts/BusinessLayout";
import MasterNavigation from "../../Components/Business/MasterNavigation";
import ConfirmModal from "../../Components/ConfirmModal";
import AccountForm from "./AccountForm";

import "../../../css/business/accounts.css";

export default function Edit({
    account,
    accountTypes = {},
    paymentModes = {},
    context = {},
}) {
    const [confirmOpen, setConfirmOpen] = useState(false);
    const { data, setData, put, processing, errors, isDirty } = useForm({
        account_code: account?.account_code ?? "",
        account_name: account?.account_name ?? "",
        account_type: account?.account_type ?? "bank",
        payment_mode: account?.payment_mode ?? "",
        bank_name: account?.bank_name ?? "",
        account_number: account?.account_number ?? "",
        upi_id: account?.upi_id ?? "",
        opening_balance: decimal(account?.opening_balance),
        opening_balance_date: account?.opening_balance_date ?? "",
        status: Boolean(account?.status),
    });

    const requestSubmit = (event) => {
        event.preventDefault();

        if (!isDirty) {
            return;
        }

        setConfirmOpen(true);
    };

    const updateAccount = () => {
        put(`/accounts/${account.id}`, {
            preserveScroll: true,
            onSuccess: () => setConfirmOpen(false),
            onError: () => setConfirmOpen(false),
        });
    };

    return (
        <>
            <Head title={`Edit ${account?.account_name ?? "Account"}`} />

            <main className="rr-acc-page rr-acc-edit-page">
                <MasterNavigation
                    active="accounts"
                    title="Edit Cash/Bank Account"
                    subtitle={
                        context.store_name
                            ? `${context.company_name ?? "Company"} • ${context.store_name}`
                            : "Account Master"
                    }
                />

                <section className="rr-acc-commandbar">
                    <div className="rr-acc-command-title">
                        <span className="rr-acc-command-icon">
                            <i className="bi bi-pencil-square" />
                        </span>
                        <div>
                            <span className="rr-acc-eyebrow">ACCOUNT MASTER</span>
                            <h1>{account?.account_name ?? "Edit Account"}</h1>
                            <p>
                                {account?.account_code ?? "—"} •{" "}
                                {account?.account_type_label ?? "Account"}
                            </p>
                        </div>
                    </div>

                    <div className="rr-acc-command-actions">
                        <Link className="rr-acc-btn filter" href="/accounts">
                            <i className="bi bi-list-ul" />
                            Account Register
                        </Link>
                        <Link
                            className="rr-acc-btn primary"
                            href={`/accounts/${account.id}`}
                        >
                            <i className="bi bi-eye" />
                            View Account
                        </Link>
                    </div>
                </section>

                <div className={`rr-acc-edit-state ${isDirty ? "changed" : "clean"}`}>
                    <i
                        className={`bi ${isDirty ? "bi-pencil-fill" : "bi-shield-check"
                            }`}
                    />
                    <span>
                        <strong>{isDirty ? "Unsaved changes" : "Account is up to date"}</strong>
                        <small>
                            {isDirty
                                ? "Review the modified information and select Update Account."
                                : "Change one or more fields to enable the update confirmation."}
                        </small>
                    </span>
                </div>

                <AccountForm
                    data={data}
                    setData={setData}
                    errors={errors}
                    accountTypes={accountTypes}
                    paymentModes={paymentModes}
                    processing={processing}
                    submitDisabled={!isDirty}
                    mode="edit"
                    isSystem={Boolean(account?.is_system)}
                    onSubmit={requestSubmit}
                />
            </main>

            <ConfirmModal
                show={confirmOpen}
                title="Update Account"
                message={`Save changes made to ${data.account_name || account?.account_name}?`}
                confirmText="Update Account"
                icon="bi-pencil-square"
                variant="primary"
                processing={processing}
                confirmDisabled={!isDirty}
                onCancel={() => !processing && setConfirmOpen(false)}
                onConfirm={updateAccount}
            />
        </>
    );
}

function decimal(value) {
    const amount = Number(value ?? 0);
    return Number.isFinite(amount) ? amount.toFixed(2) : "0.00";
}

Edit.layout = (page) => <BusinessLayout>{page}</BusinessLayout>;
