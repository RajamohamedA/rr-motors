import React, { useState } from "react";
import { Head, useForm } from "@inertiajs/react";

import BusinessLayout from "@/Layouts/BusinessLayout";
import MasterNavigation from "../../Components/Business/MasterNavigation";
import ConfirmModal from "../../Components/ConfirmModal";
import AccountForm from "./AccountForm";

import "../../../css/business/accounts.css";

export default function Create({
    nextCode = "",
    accountTypes = {},
    paymentModes = {},
    context = {},
}) {
    const [confirmOpen, setConfirmOpen] = useState(false);
    const { data, setData, post, processing, errors } = useForm({
        account_code: nextCode,
        account_name: "",
        account_type: "bank",
        payment_mode: "bank_transfer",
        bank_name: "",
        account_number: "",
        upi_id: "",
        opening_balance: "0.00",
        opening_balance_date: localDate(),
        status: true,
    });

    const requestSubmit = (event) => {
        event.preventDefault();
        setConfirmOpen(true);
    };

    const createAccount = () => {
        post("/accounts", {
            preserveScroll: true,
            onSuccess: () => setConfirmOpen(false),
            onError: () => setConfirmOpen(false),
        });
    };

    return (
        <>
            <Head title="Create Cash/Bank Account" />

            <main className="rr-acc-page">
                <MasterNavigation
                    active="accounts"
                    title="Create Cash/Bank Account"
                    subtitle={
                        context.store_name
                            ? `${context.company_name ?? "Company"} • ${context.store_name}`
                            : "Account Master"
                    }
                />

                <section className="rr-acc-commandbar">
                    <div className="rr-acc-command-title">
                        <span className="rr-acc-command-icon">
                            <i className="bi bi-bank" />
                        </span>
                        <div>
                            <span className="rr-acc-eyebrow">ACCOUNT MASTER</span>
                            <h1>New Cash / Bank Account</h1>
                            <p>Create a payment account for the currently selected branch.</p>
                        </div>
                    </div>

                    <a className="rr-acc-btn filter" href="/accounts">
                        <i className="bi bi-arrow-left" />
                        Account Register
                    </a>
                </section>

                <AccountForm
                    data={data}
                    setData={setData}
                    errors={errors}
                    accountTypes={accountTypes}
                    paymentModes={paymentModes}
                    processing={processing}
                    mode="create"
                    onSubmit={requestSubmit}
                />
            </main>

            <ConfirmModal
                show={confirmOpen}
                title="Create Account"
                message={`Create ${data.account_name || "this account"} with an opening balance of ${money(
                    data.opening_balance,
                )}?`}
                confirmText="Create Account"
                icon="bi-bank"
                variant="primary"
                processing={processing}
                onCancel={() => !processing && setConfirmOpen(false)}
                onConfirm={createAccount}
            />
        </>
    );
}

function localDate() {
    const date = new Date();
    const offset = date.getTimezoneOffset() * 60_000;
    return new Date(date.getTime() - offset).toISOString().slice(0, 10);
}

function money(value) {
    return new Intl.NumberFormat("en-IN", {
        style: "currency",
        currency: "INR",
        minimumFractionDigits: 2,
    }).format(Number(value ?? 0));
}

Create.layout = (page) => <BusinessLayout>{page}</BusinessLayout>;
