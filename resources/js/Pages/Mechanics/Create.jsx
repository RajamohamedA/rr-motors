import React from "react";
import { Head, Link, useForm } from "@inertiajs/react";

import BusinessLayout from "@/Layouts/BusinessLayout";
import MasterNavigation from "../../Components/Business/MasterNavigation";
import Form from "./Form";

import "../../../css/business/mechanics.css";

export default function Create({
    next_code: nextCode = "",
    context = {},
}) {
    const {
        data,
        setData,
        post,
        processing,
        errors,
    } = useForm({
        mechanic_code: nextCode,
        mechanic_name: "",
        mobile: "",
        email: "",
        address: "",
        city: "",
        state: "Tamil Nadu",
        pincode: "",
        status: true,
    });

    const submit = (event) => {
        event.preventDefault();

        if (processing) {
            return;
        }

        post("/mechanics", {
            preserveScroll: true,
        });
    };

    const errorCount = Object.keys(errors ?? {}).length;

    return (
        <>
            <Head title="Add Mechanic" />

            <div className="rr-mec-page rr-mec-create-page">
                <style>{`
                    .rr-mec-create-page {
                        width: 100%;
                        min-width: 0;
                        padding: 4px 6px 10px !important;
                    }

                    .rr-mec-create-page .rr-mec-workspace {
                        width: 100% !important;
                        max-width: none !important;
                        margin: 0 !important;
                    }

                    .rr-mec-create-page .rr-master-nav-shell,
                    .rr-mec-create-page .rr-mec-titlebar,
                    .rr-mec-create-page .rr-mec-form-card {
                        width: 100% !important;
                        max-width: none !important;
                        margin-left: 0 !important;
                        margin-right: 0 !important;
                    }

                    .rr-mec-create-page .rr-master-nav-shell,
                    .rr-mec-create-page .rr-mec-titlebar,
                    .rr-mec-create-page .rr-mec-form-card {
                        margin-bottom: 8px !important;
                    }

                    .rr-mec-create-page .rr-mec-titlebar {
                        min-height: 76px;
                        padding: 11px 14px !important;
                    }

                    .rr-mec-create-page .rr-mec-form-section {
                        padding: 13px 14px !important;
                    }

                    .rr-mec-create-page .rr-mec-form-actions {
                        padding: 10px 14px !important;
                    }

                    .rr-mec-create-title-main {
                        display: flex;
                        align-items: center;
                        gap: 11px;
                        min-width: 0;
                    }

                    .rr-mec-create-title-icon {
                        display: grid;
                        place-items: center;
                        flex: 0 0 42px;
                        width: 42px;
                        height: 42px;
                        border: 1px solid #9bbadd;
                        border-radius: 5px;
                        background: linear-gradient(#f8fbff, #e1edf9);
                        color: #1d65aa;
                        font-size: 19px;
                    }

                    .rr-mec-branch-context {
                        display: inline-flex;
                        align-items: center;
                        gap: 5px;
                        margin-top: 4px;
                        color: #53677e;
                        font-size: 11px;
                        font-weight: 650;
                    }

                    .rr-mec-validation-alert {
                        display: flex;
                        align-items: flex-start;
                        gap: 10px;
                        width: 100%;
                        margin: 0 0 8px;
                        padding: 9px 12px;
                        border: 1px solid #e0a4a8;
                        border-left: 4px solid #b8323a;
                        border-radius: 5px;
                        background: #fff1f2;
                        color: #8f2930;
                    }

                    .rr-mec-validation-alert > i {
                        margin-top: 1px;
                        font-size: 17px;
                    }

                    .rr-mec-validation-alert strong,
                    .rr-mec-validation-alert span {
                        display: block;
                    }

                    .rr-mec-validation-alert span {
                        margin-top: 1px;
                        font-size: 11px;
                    }

                    @media (max-width: 760px) {
                        .rr-mec-create-page {
                            padding: 3px !important;
                        }

                        .rr-mec-create-page .rr-mec-titlebar {
                            align-items: stretch;
                        }

                        .rr-mec-create-page .rr-mec-titlebar > .rr-mec-btn {
                            width: 100%;
                        }
                    }
                `}</style>

                <div className="rr-mec-workspace">
                    <MasterNavigation
                        active="mechanics"
                        title="Add Mechanic"
                        subtitle={
                            context.store_name
                                ? `Current branch: ${context.store_name}`
                                : "Mechanic Master"
                        }
                    />

                    <PageTitle
                        title="Create Mechanic"
                        text="Create the mechanic master and make it available in the active branch."
                        branchName={context.store_name}
                    />

                    {errorCount > 0 && (
                        <div
                            className="rr-mec-validation-alert"
                            role="alert"
                        >
                            <i className="bi bi-exclamation-octagon-fill" />
                            <div>
                                <strong>
                                    Please correct {errorCount} form {errorCount === 1 ? "error" : "errors"}.
                                </strong>
                                <span>
                                    Review the highlighted fields before saving the mechanic.
                                </span>
                            </div>
                        </div>
                    )}

                    <Form
                        data={data}
                        setData={setData}
                        errors={errors}
                        processing={processing}
                        onSubmit={submit}
                        mode="create"
                    />
                </div>
            </div>
        </>
    );
}

function PageTitle({
    title,
    text,
    branchName,
}) {
    return (
        <section className="rr-mec-titlebar">
            <div className="rr-mec-create-title-main">
                <div className="rr-mec-create-title-icon">
                    <i className="bi bi-person-plus-fill" />
                </div>

                <div>
                    <span>MECHANIC MASTER</span>
                    <h1>{title}</h1>
                    <p>{text}</p>

                    {branchName && (
                        <div className="rr-mec-branch-context">
                            <i className="bi bi-shop" />
                            Active Branch: {branchName}
                        </div>
                    )}
                </div>
            </div>

            <Link
                className="rr-mec-btn secondary"
                href="/mechanics"
            >
                <i className="bi bi-list-ul" />
                Mechanic List
            </Link>
        </section>
    );
}

Create.layout = (page) => <BusinessLayout>{page}</BusinessLayout>;
