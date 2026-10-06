import React from "react";
import { Link } from "@inertiajs/react";

export default function Form({
    data,
    setData,
    errors = {},
    processing,
    onSubmit,
    mode = "create",
}) {
    const field = (name, value) => {
        setData(name, value);
    };

    const isEdit = mode === "edit";

    return (
        <form
            className="rr-mec-form-card rr-mec-pro-form"
            onSubmit={onSubmit}
            noValidate
        >
            <style>{`
                .rr-mec-pro-form {
                    overflow: hidden;
                    border: 1px solid #aebdcd !important;
                    border-radius: 5px !important;
                    background: #f4f7fa !important;
                    box-shadow: 0 2px 8px rgba(24, 48, 75, .10) !important;
                }

                .rr-mec-pro-form .rr-mec-form-head {
                    display: flex;
                    align-items: center;
                    min-height: 54px;
                    padding: 9px 13px !important;
                    border-bottom: 1px solid #aebdcd;
                    background: linear-gradient(#ffffff, #e7eef6);
                }

                .rr-mec-pro-form .rr-mec-form-head-copy {
                    min-width: 0;
                }

                .rr-mec-pro-form .rr-mec-form-head-copy strong {
                    color: #173b60;
                    font-size: 14px;
                }

                .rr-mec-pro-form .rr-mec-form-head-copy span {
                    color: #5d6f84;
                    font-size: 11px;
                }

                .rr-mec-form-head-status {
                    display: inline-flex;
                    align-items: center;
                    gap: 6px;
                    margin-left: auto;
                    padding: 4px 9px;
                    border: 1px solid ${data.status ? "#8ac5a7" : "#dda5a8"};
                    border-radius: 12px;
                    background: ${data.status ? "#e5f5ec" : "#fceced"};
                    color: ${data.status ? "#176d45" : "#9c3036"};
                    font-size: 10px;
                    font-weight: 800;
                }

                .rr-mec-form-head-status::before {
                    width: 7px;
                    height: 7px;
                    border-radius: 50%;
                    background: currentColor;
                    content: "";
                }

                .rr-mec-pro-layout {
                    display: grid;
                    grid-template-columns: minmax(0, 1.45fr) minmax(330px, .8fr);
                    align-items: start;
                    gap: 10px;
                    padding: 10px;
                }

                .rr-mec-pro-column {
                    display: grid;
                    gap: 10px;
                    min-width: 0;
                }

                .rr-mec-pro-section {
                    overflow: hidden;
                    border: 1px solid #c2ceda;
                    border-radius: 5px;
                    background: #ffffff;
                }

                .rr-mec-pro-section-head {
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    min-height: 37px;
                    padding: 0 11px;
                    border-bottom: 1px solid #cbd6e1;
                    background: linear-gradient(#fbfdff, #edf2f7);
                    color: #274766;
                    font-size: 11px;
                    font-weight: 800;
                    letter-spacing: .25px;
                    text-transform: uppercase;
                }

                .rr-mec-pro-section-head i {
                    color: #2469ad;
                    font-size: 14px;
                }

                .rr-mec-pro-fields {
                    display: grid;
                    grid-template-columns: repeat(2, minmax(0, 1fr));
                    gap: 10px;
                    padding: 11px;
                }

                .rr-mec-pro-fields.one-column {
                    grid-template-columns: 1fr;
                }

                .rr-mec-pro-form .rr-mec-field {
                    min-width: 0;
                }

                .rr-mec-pro-form .rr-mec-field > label {
                    display: flex;
                    align-items: center;
                    gap: 4px;
                    min-height: 17px;
                    margin-bottom: 4px;
                    color: #3d526a;
                    font-size: 10px;
                    font-weight: 800;
                    letter-spacing: .22px;
                    text-transform: uppercase;
                }

                .rr-mec-pro-form .rr-mec-field > label i {
                    color: #5c7895;
                    font-size: 11px;
                }

                .rr-mec-pro-form .rr-mec-field label em {
                    color: #b72f37;
                    font-style: normal;
                }

                .rr-mec-input-shell {
                    position: relative;
                    display: flex;
                    align-items: center;
                    width: 100%;
                }

                .rr-mec-input-shell > i {
                    position: absolute;
                    left: 10px;
                    z-index: 1;
                    color: #61768d;
                    pointer-events: none;
                }

                .rr-mec-pro-form .rr-mec-input-shell input,
                .rr-mec-pro-form .rr-mec-field textarea {
                    width: 100%;
                    border: 1px solid #a8b8c8;
                    border-radius: 4px;
                    background: #ffffff;
                    color: #17283b;
                    font: 12px/1.4 Inter, "Segoe UI", Arial, sans-serif;
                    outline: 0;
                    cursor: pointer;
                    transition: border-color .14s, box-shadow .14s;
                }

                .rr-mec-pro-form .rr-mec-input-shell input {
                    height: 36px;
                    padding: 0 10px 0 32px;
                }

                .rr-mec-pro-form .rr-mec-field textarea {
                    min-height: 78px;
                    padding: 8px 10px;
                    resize: vertical;
                }

                .rr-mec-pro-form .rr-mec-input-shell input:hover,
                .rr-mec-pro-form .rr-mec-field textarea:hover {
                    border-color: #7897b6;
                }

                .rr-mec-pro-form .rr-mec-input-shell input:focus,
                .rr-mec-pro-form .rr-mec-field textarea:focus {
                    border-color: #2470ba;
                    box-shadow: 0 0 0 3px rgba(36, 112, 186, .13);
                }

                .rr-mec-pro-form .rr-mec-field.has-error input,
                .rr-mec-pro-form .rr-mec-field.has-error textarea {
                    border-color: #c7464d;
                    background: #fffafa;
                }

                .rr-mec-pro-form .error {
                    display: flex;
                    align-items: center;
                    gap: 4px;
                    margin-top: 4px;
                    color: #a92d34;
                    font-size: 10px;
                    font-weight: 650;
                }

                .rr-mec-code-hint {
                    display: block;
                    margin-top: 4px;
                    color: #718096;
                    font-size: 9px;
                }

                .rr-mec-status-panel {
                    padding: 11px;
                }

                .rr-mec-status-choice {
                    display: flex;
                    align-items: center;
                    gap: 10px;
                    padding: 10px;
                    border: 1px solid ${data.status ? "#94cbb0" : "#d6aaad"};
                    border-radius: 5px;
                    background: ${data.status ? "#edf8f2" : "#fff1f2"};
                    cursor: pointer;
                }

                .rr-mec-status-choice input {
                    position: absolute;
                    width: 1px;
                    height: 1px;
                    opacity: 0;
                }

                .rr-mec-status-choice .rr-mec-switch {
                    flex: 0 0 auto;
                }

                .rr-mec-status-copy strong,
                .rr-mec-status-copy small {
                    display: block;
                }

                .rr-mec-status-copy strong {
                    color: ${data.status ? "#176a43" : "#923037"};
                    font-size: 12px;
                }

                .rr-mec-status-copy small {
                    margin-top: 1px;
                    color: #637386;
                    font-size: 10px;
                }

                .rr-mec-pro-form .rr-mec-form-actions {
                    display: flex;
                    align-items: center;
                    justify-content: flex-end;
                    gap: 8px;
                    min-height: 55px;
                    padding: 9px 12px !important;
                    border-top: 1px solid #b8c6d4;
                    background: linear-gradient(#f6f8fb, #e7edf3);
                }

                .rr-mec-action-note {
                    display: flex;
                    align-items: center;
                    gap: 6px;
                    margin-right: auto;
                    color: #66778a;
                    font-size: 10px;
                }

                .rr-mec-pro-form .rr-mec-btn:disabled {
                    cursor: not-allowed;
                    opacity: .65;
                }

                @media (max-width: 1000px) {
                    .rr-mec-pro-layout {
                        grid-template-columns: 1fr;
                    }
                }

                @media (max-width: 620px) {
                    .rr-mec-pro-layout {
                        padding: 7px;
                    }

                    .rr-mec-pro-fields {
                        grid-template-columns: 1fr;
                    }

                    .rr-mec-form-head-status,
                    .rr-mec-action-note {
                        display: none;
                    }

                    .rr-mec-pro-form .rr-mec-form-actions {
                        align-items: stretch;
                        flex-direction: column-reverse;
                    }

                    .rr-mec-pro-form .rr-mec-form-actions .rr-mec-btn {
                        width: 100%;
                    }
                }
            `}</style>

            <div className="rr-mec-form-head">
                <div className="rr-mec-form-icon">
                    <i className={isEdit ? "bi bi-pencil-square" : "bi bi-person-plus"} />
                </div>

                <div className="rr-mec-form-head-copy">
                    <strong>
                        {isEdit ? "Update Mechanic Master" : "New Mechanic Master"}
                    </strong>
                    <span>
                        Enter identity, contact, address and branch availability details.
                    </span>
                </div>

                <span className="rr-mec-form-head-status">
                    {data.status ? "Active" : "Inactive"}
                </span>
            </div>

            <div className="rr-mec-pro-layout">
                <div className="rr-mec-pro-column">
                    <section className="rr-mec-pro-section">
                        <div className="rr-mec-pro-section-head">
                            <i className="bi bi-person-vcard" />
                            Basic Information
                        </div>

                        <div className="rr-mec-pro-fields">
                            <Input
                                label="Mechanic Code"
                                icon="bi-upc-scan"
                                value={data.mechanic_code}
                                error={errors.mechanic_code}
                                onChange={(event) =>
                                    field(
                                        "mechanic_code",
                                        event.target.value.toUpperCase()
                                    )
                                }
                                placeholder="Automatically generated"
                                maxLength={30}
                                autoComplete="off"
                                hint="Unique within the company"
                            />

                            <Input
                                label="Mechanic Name"
                                icon="bi-person"
                                value={data.mechanic_name}
                                error={errors.mechanic_name}
                                onChange={(event) =>
                                    field("mechanic_name", event.target.value)
                                }
                                placeholder="Enter mechanic name"
                                maxLength={150}
                                autoComplete="name"
                                required
                                autoFocus
                            />
                        </div>
                    </section>

                    <section className="rr-mec-pro-section">
                        <div className="rr-mec-pro-section-head">
                            <i className="bi bi-telephone" />
                            Contact Information
                        </div>

                        <div className="rr-mec-pro-fields">
                            <Input
                                label="Mobile Number"
                                icon="bi-phone"
                                value={data.mobile}
                                error={errors.mobile}
                                onChange={(event) =>
                                    field("mobile", event.target.value)
                                }
                                placeholder="Enter mobile number"
                                inputMode="tel"
                                maxLength={20}
                                autoComplete="tel"
                            />

                            <Input
                                label="Email Address"
                                icon="bi-envelope"
                                type="email"
                                value={data.email}
                                error={errors.email}
                                onChange={(event) =>
                                    field("email", event.target.value)
                                }
                                placeholder="Enter email address"
                                maxLength={255}
                                autoComplete="email"
                            />
                        </div>
                    </section>
                </div>

                <div className="rr-mec-pro-column">
                    <section className="rr-mec-pro-section">
                        <div className="rr-mec-pro-section-head">
                            <i className="bi bi-geo-alt" />
                            Address Information
                        </div>

                        <div className="rr-mec-pro-fields one-column">
                            <div className={`rr-mec-field ${errors.address ? "has-error" : ""}`}>
                                <label htmlFor="mechanic-address">
                                    <i className="bi bi-signpost" />
                                    Address
                                </label>

                                <textarea
                                    id="mechanic-address"
                                    rows="3"
                                    value={data.address}
                                    onChange={(event) =>
                                        field("address", event.target.value)
                                    }
                                    placeholder="Door number, street and area"
                                    maxLength={1000}
                                    autoComplete="street-address"
                                />

                                <FieldError error={errors.address} />
                            </div>

                            <Input
                                label="City"
                                icon="bi-buildings"
                                value={data.city}
                                error={errors.city}
                                onChange={(event) =>
                                    field("city", event.target.value)
                                }
                                placeholder="City"
                                maxLength={100}
                                autoComplete="address-level2"
                            />

                            <div className="rr-mec-pro-fields" style={{ padding: 0 }}>
                                <Input
                                    label="State"
                                    icon="bi-map"
                                    value={data.state}
                                    error={errors.state}
                                    onChange={(event) =>
                                        field("state", event.target.value)
                                    }
                                    placeholder="State"
                                    maxLength={100}
                                    autoComplete="address-level1"
                                />

                                <Input
                                    label="Pincode"
                                    icon="bi-mailbox"
                                    value={data.pincode}
                                    error={errors.pincode}
                                    onChange={(event) =>
                                        field("pincode", event.target.value)
                                    }
                                    placeholder="Pincode"
                                    inputMode="numeric"
                                    maxLength={20}
                                    autoComplete="postal-code"
                                />
                            </div>
                        </div>
                    </section>

                    <section className="rr-mec-pro-section">
                        <div className="rr-mec-pro-section-head">
                            <i className="bi bi-toggle-on" />
                            Availability
                        </div>

                        <div className="rr-mec-status-panel">
                            <label className="rr-mec-status-choice">
                                <input
                                    type="checkbox"
                                    checked={Boolean(data.status)}
                                    onChange={(event) =>
                                        field("status", event.target.checked)
                                    }
                                />
                                <span className="rr-mec-switch" />

                                <span className="rr-mec-status-copy">
                                    <strong>
                                        {data.status
                                            ? "Active Mechanic"
                                            : "Inactive Mechanic"}
                                    </strong>
                                    <small>
                                        {data.status
                                            ? "Available for sales, commissions and payments."
                                            : "Hidden from new business transactions."}
                                    </small>
                                </span>
                            </label>

                            <FieldError error={errors.status} />
                        </div>
                    </section>
                </div>
            </div>

            <div className="rr-mec-form-actions">
                <span className="rr-mec-action-note">
                    <i className="bi bi-shield-check" />
                    Fields are validated for the active company and branch.
                </span>

                <Link
                    className="rr-mec-btn secondary"
                    href="/mechanics"
                    aria-disabled={processing}
                    onClick={(event) => {
                        if (processing) {
                            event.preventDefault();
                        }
                    }}
                >
                    <i className="bi bi-x-lg" />
                    Cancel
                </Link>

                <button
                    className="rr-mec-btn primary"
                    type="submit"
                    disabled={processing}
                >
                    {processing ? (
                        <span className="spinner-border spinner-border-sm" />
                    ) : (
                        <i className="bi bi-check2-circle" />
                    )}

                    {processing
                        ? "Saving..."
                        : isEdit
                            ? "Update Mechanic"
                            : "Save Mechanic"}
                </button>
            </div>
        </form>
    );
}

function Input({
    label,
    icon,
    error,
    hint,
    required = false,
    type = "text",
    ...props
}) {
    const id = `mechanic-${label.toLowerCase().replaceAll(" ", "-")}`;

    return (
        <div className={`rr-mec-field ${error ? "has-error" : ""}`}>
            <label htmlFor={id}>
                {icon && <i className={`bi ${icon}`} />}
                {label}
                {required && <em>*</em>}
            </label>

            <div className="rr-mec-input-shell">
                {icon && <i className={`bi ${icon}`} />}
                <input
                    id={id}
                    type={type}
                    aria-invalid={Boolean(error)}
                    {...props}
                />
            </div>

            <FieldError error={error} />

            {!error && hint && (
                <small className="rr-mec-code-hint">{hint}</small>
            )}
        </div>
    );
}

function FieldError({ error }) {
    if (!error) {
        return null;
    }

    return (
        <small className="error">
            <i className="bi bi-exclamation-circle" />
            {error}
        </small>
    );
}
