import React from "react";
import { Head, Link, useForm } from "@inertiajs/react";

import BusinessLayout from "@/Layouts/BusinessLayout";
import MasterNavigation from "../../Components/Business/MasterNavigation";
import Form from "./Form";

import "../../../css/business/mechanics.css";

export default function Edit({
    mechanic,
    context = {},
}) {
    const {
        data,
        setData,
        put,
        processing,
        errors,
    } = useForm({
        mechanic_code: mechanic?.mechanic_code ?? "",
        mechanic_name: mechanic?.mechanic_name ?? "",
        mobile: mechanic?.mobile ?? "",
        email: mechanic?.email ?? "",
        address: mechanic?.address ?? "",
        city: mechanic?.city ?? "",
        state: mechanic?.state ?? "Tamil Nadu",
        pincode: mechanic?.pincode ?? "",
        status: Boolean(mechanic?.status),
    });

    const submit = (event) => {
        event.preventDefault();

        if (processing) {
            return;
        }

        put(`/mechanics/${mechanic.id}`, {
            preserveScroll: true,
        });
    };

    const errorCount = Object.keys(errors ?? {}).length;

    return (
        <>
            <Head title={`Edit ${mechanic.mechanic_name}`} />

            <div className="rr-mec-page rr-mec-edit-page">
                <style>{`
                    .rr-mec-edit-page {
                        width: 100%;
                        min-width: 0;
                        padding: 4px 6px 10px !important;
                    }

                    .rr-mec-edit-page .rr-mec-workspace {
                        width: 100% !important;
                        max-width: none !important;
                        margin: 0 !important;
                    }

                    .rr-mec-edit-page .rr-master-nav-shell,
                    .rr-mec-edit-page .rr-mec-titlebar,
                    .rr-mec-edit-page .rr-mec-form-card {
                        width: 100% !important;
                        max-width: none !important;
                        margin-left: 0 !important;
                        margin-right: 0 !important;
                    }

                    .rr-mec-edit-page .rr-master-nav-shell,
                    .rr-mec-edit-page .rr-mec-titlebar,
                    .rr-mec-edit-page .rr-mec-form-card {
                        margin-bottom: 8px !important;
                    }

                    .rr-mec-edit-page .rr-mec-titlebar {
                        min-height: 76px;
                        padding: 11px 14px !important;
                    }

                    .rr-mec-edit-title-main {
                        display: flex;
                        align-items: center;
                        gap: 11px;
                        min-width: 0;
                    }

                    .rr-mec-edit-title-icon {
                        display: grid;
                        place-items: center;
                        flex: 0 0 42px;
                        width: 42px;
                        height: 42px;
                        border: 1px solid #d6b46e;
                        border-radius: 5px;
                        background: linear-gradient(#fffdf7, #f8ecd1);
                        color: #a5680d;
                        font-size: 19px;
                    }

                    .rr-mec-edit-context {
                        display: flex;
                        align-items: center;
                        flex-wrap: wrap;
                        gap: 7px 13px;
                        margin-top: 4px;
                        color: #53677e;
                        font-size: 11px;
                        font-weight: 650;
                    }

                    .rr-mec-edit-context span {
                        display: inline-flex;
                        align-items: center;
                        gap: 5px;
                    }

                    .rr-mec-edit-actions {
                        display: flex;
                        align-items: center;
                        gap: 7px;
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
                        .rr-mec-edit-page {
                            padding: 3px !important;
                        }

                        .rr-mec-edit-page .rr-mec-titlebar {
                            align-items: stretch;
                        }

                        .rr-mec-edit-actions {
                            width: 100%;
                        }

                        .rr-mec-edit-actions .rr-mec-btn {
                            flex: 1;
                        }
                    }
                `}</style>

                <div className="rr-mec-workspace">
                    <MasterNavigation
                        active="mechanics"
                        title="Edit Mechanic"
                        subtitle={
                            context.store_name
                                ? `Current branch: ${context.store_name}`
                                : "Mechanic Master"
                        }
                    />

                    <section className="rr-mec-titlebar">
                        <div className="rr-mec-edit-title-main">
                            <div className="rr-mec-edit-title-icon">
                                <i className="bi bi-pencil-square" />
                            </div>

                            <div>
                                <span>MECHANIC MASTER</span>
                                <h1>Edit {mechanic.mechanic_name}</h1>
                                <p>
                                    Update mechanic identity, contact, address and availability details.
                                </p>

                                <div className="rr-mec-edit-context">
                                    <span>
                                        <i className="bi bi-upc-scan" />
                                        Code: {mechanic.mechanic_code}
                                    </span>

                                    {context.store_name && (
                                        <span>
                                            <i className="bi bi-shop" />
                                            Branch: {context.store_name}
                                        </span>
                                    )}
                                </div>
                            </div>
                        </div>

                        <div className="rr-mec-edit-actions">
                            <Link
                                className="rr-mec-btn secondary"
                                href="/mechanics"
                            >
                                <i className="bi bi-list-ul" />
                                List
                            </Link>

                            <Link
                                className="rr-mec-btn primary"
                                href={`/mechanics/${mechanic.id}`}
                            >
                                <i className="bi bi-eye" />
                                View Mechanic
                            </Link>
                        </div>
                    </section>

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
                                    Review the highlighted fields before updating the mechanic.
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
                        mode="edit"
                    />
                </div>
            </div>
        </>
    );
}

Edit.layout = (page) => <BusinessLayout>{page}</BusinessLayout>;
