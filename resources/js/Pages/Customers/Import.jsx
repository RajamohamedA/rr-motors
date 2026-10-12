import React, {
    useRef,
    useState,
} from 'react';

import {
    Head,
    Link,
} from '@inertiajs/react';

import axios from 'axios';

import BusinessLayout from '@/Layouts/BusinessLayout';

import MasterNavigation
    from '../../Components/Business/MasterNavigation';

import '../../../css/business/customers.css';

import '../../../css/business/product-import.css';

import '../../../css/business/customer-import.css';


const ACCEPTED_EXTENSIONS = [
    'xlsx',
    'xls',
    'csv',
];


export default function Import({
    template_url: templateUrl = '/customers/import/template',
    limits = {},
    context = {},
}) {
    const inputRef = useRef(null);

    const [file, setFile] = useState(null);
    const [dragging, setDragging] = useState(false);
    const [previewing, setPreviewing] = useState(false);
    const [importing, setImporting] = useState(false);
    const [preview, setPreview] = useState(null);
    const [message, setMessage] = useState(null);
    const [error, setError] = useState(null);

    const maximumSize = Number(
        limits.maximum_file_size_mb
        ?? 10
    );

    const actionableRows =
        Number(preview?.summary?.new_customers ?? 0)
        +
        Number(preview?.summary?.branch_attachments ?? 0);

    const canImport =
        Boolean(file)
        &&
        Boolean(preview)
        &&
        actionableRows > 0
        &&
        Number(preview?.summary?.invalid_rows ?? 0) === 0
        &&
        !previewing
        &&
        !importing;


    const resetResult = () => {
        setPreview(null);
        setMessage(null);
        setError(null);
    };


    const chooseFile = (
        selectedFile
    ) => {
        resetResult();

        if (!selectedFile) {
            setFile(null);
            return;
        }

        const extension =
            String(selectedFile.name)
                .split('.')
                .pop()
                ?.toLowerCase();

        if (
            !ACCEPTED_EXTENSIONS.includes(
                extension
            )
        ) {
            setFile(null);
            setError(
                'Choose an Excel (.xlsx or .xls) or CSV file.'
            );
            return;
        }

        if (
            selectedFile.size
            >
            maximumSize * 1024 * 1024
        ) {
            setFile(null);
            setError(
                `The selected file must not exceed ${maximumSize} MB.`
            );
            return;
        }

        setFile(selectedFile);
    };


    const removeFile = () => {
        setFile(null);
        resetResult();

        if (inputRef.current) {
            inputRef.current.value = '';
        }
    };


    const previewFile = async () => {
        if (!file || previewing || importing) {
            return;
        }

        setPreviewing(true);
        resetResult();

        try {
            const formData = new FormData();
            formData.append('file', file);

            const response = await axios.post(
                '/customers/import/preview',
                formData,
                {
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'multipart/form-data',
                    },
                }
            );

            setPreview(response.data);
            setMessage(response.data.message);
        } catch (requestError) {
            setError(
                extractError(
                    requestError,
                    'Unable to preview the customer file.'
                )
            );
        } finally {
            setPreviewing(false);
        }
    };


    const importFile = async () => {
        if (!canImport) {
            return;
        }

        setImporting(true);
        setMessage(null);
        setError(null);

        try {
            const formData = new FormData();
            formData.append('file', file);

            const response = await axios.post(
                '/customers/import',
                formData,
                {
                    headers: {
                        Accept: 'application/json',
                        'Content-Type': 'multipart/form-data',
                    },
                }
            );

            setMessage(response.data.message);

            window.setTimeout(
                () => {
                    window.location.assign(
                        response.data.redirect
                        ?? '/customers'
                    );
                },
                700
            );
        } catch (requestError) {
            const responseData =
                requestError?.response?.data;

            if (
                Array.isArray(
                    responseData?.errors
                )
            ) {
                setPreview((current) => ({
                    ...(current ?? {}),
                    errors: responseData.errors,
                    summary: {
                        ...(current?.summary ?? {}),
                        invalid_rows:
                            responseData.errors.length,
                    },
                }));
            }

            setError(
                extractError(
                    requestError,
                    'The customers could not be imported.'
                )
            );
            setImporting(false);
        }
    };


    return (
        <>
            <Head title="Import Customers" />


            <div className="rr-product-page rr-product-import-page rr-customer-import-page">
                <MasterNavigation
                    active="customers"
                    title="Customer Import"
                    subtitle={context.store_name
                        ? `Import customers into ${context.store_name}`
                        : 'Import customers into the active branch'}
                />


                <section className="rr-product-page-header rr-import-header rr-customer-import-header">
                    <div className="rr-product-header-copy">
                        <div className="rr-product-header-icon">
                            <i className="bi bi-people"></i>
                        </div>

                        <div>
                            <span className="rr-product-eyebrow">
                                CUSTOMER MASTER
                            </span>

                            <h1>
                                Import Customers
                            </h1>

                            <p>
                                Customer name and mobile are the only required fields.
                                Existing company customers are safely attached to this branch.
                            </p>
                        </div>
                    </div>


                    <div className="rr-product-header-actions">
                        <a
                            href={templateUrl}
                            className="rr-product-primary-btn rr-product-template-btn"
                        >
                            <i className="bi bi-file-earmark-arrow-down"></i>

                            Download Template
                        </a>

                        <Link
                            href="/customers"
                            className="rr-product-primary-btn rr-product-import-back-btn"
                        >
                            <i className="bi bi-arrow-left"></i>

                            Customer List
                        </Link>
                    </div>
                </section>


                <section className="rr-customer-import-highlights">
                    <Highlight
                        icon="bi-person-check"
                        title="Two Required Fields"
                        text="Customer name and mobile number"
                        tone="blue"
                    />

                    <Highlight
                        icon="bi-phone"
                        title="Mobile Duplicate Check"
                        text="Duplicate company customers are not recreated"
                        tone="green"
                    />

                    <Highlight
                        icon="bi-diagram-3"
                        title="Branch Attachment"
                        text="Existing customers are attached automatically"
                        tone="purple"
                    />

                    <Highlight
                        icon="bi-shield-check"
                        title="Safe Transaction"
                        text="All changes roll back if the import fails"
                        tone="orange"
                    />
                </section>


                <div className="rr-import-layout">
                    <section className="rr-import-panel rr-import-upload-panel">
                        <PanelTitle
                            number="01"
                            icon="bi-cloud-arrow-up"
                            title="Choose Customer File"
                            text="Upload the completed Excel or CSV template"
                        />


                        <input
                            ref={inputRef}
                            type="file"
                            accept=".xlsx,.xls,.csv"
                            hidden
                            onChange={(event) => {
                                chooseFile(
                                    event.target.files?.[0]
                                    ?? null
                                );
                            }}
                        />


                        <button
                            type="button"
                            className={`rr-import-dropzone ${dragging
                                ? 'is-dragging'
                                : ''} ${file
                                    ? 'has-file'
                                    : ''}`}
                            onClick={() => {
                                inputRef.current?.click();
                            }}
                            onDragEnter={(event) => {
                                event.preventDefault();
                                setDragging(true);
                            }}
                            onDragOver={(event) => {
                                event.preventDefault();
                                setDragging(true);
                            }}
                            onDragLeave={(event) => {
                                event.preventDefault();
                                setDragging(false);
                            }}
                            onDrop={(event) => {
                                event.preventDefault();
                                setDragging(false);
                                chooseFile(
                                    event.dataTransfer.files?.[0]
                                    ?? null
                                );
                            }}
                            disabled={previewing || importing}
                        >
                            <span className="rr-import-drop-icon">
                                <i className={`bi ${file
                                    ? 'bi-file-earmark-person'
                                    : 'bi-cloud-arrow-up'}`}></i>
                            </span>

                            {file ? (
                                <>
                                    <strong>
                                        {file.name}
                                    </strong>

                                    <small>
                                        {fileSize(file.size)} · Click to replace
                                    </small>
                                </>
                            ) : (
                                <>
                                    <strong>
                                        Drop the customer file here
                                    </strong>

                                    <small>
                                        or click to browse from your computer
                                    </small>
                                </>
                            )}

                            <span className="rr-import-file-types">
                                XLSX
                                <b>•</b>
                                XLS
                                <b>•</b>
                                CSV
                                <b>•</b>
                                Maximum {maximumSize} MB
                            </span>
                        </button>


                        {file && (
                            <div className="rr-import-selected-file">
                                <div>
                                    <i className="bi bi-file-earmark-person"></i>

                                    <span>
                                        <strong>
                                            {file.name}
                                        </strong>

                                        <small>
                                            Ready for duplicate and field validation
                                        </small>
                                    </span>
                                </div>

                                <button
                                    type="button"
                                    onClick={removeFile}
                                    disabled={previewing || importing}
                                    title="Remove selected file"
                                >
                                    <i className="bi bi-x-lg"></i>
                                </button>
                            </div>
                        )}


                        <div className="rr-import-actions">
                            <button
                                type="button"
                                className="rr-import-btn preview"
                                disabled={!file || previewing || importing}
                                onClick={previewFile}
                            >
                                {previewing ? (
                                    <>
                                        <span className="spinner-border spinner-border-sm"></span>
                                        Checking Customers...
                                    </>
                                ) : (
                                    <>
                                        <i className="bi bi-search"></i>
                                        Preview & Validate
                                    </>
                                )}
                            </button>

                            <button
                                type="button"
                                className="rr-import-btn import"
                                disabled={!canImport}
                                onClick={importFile}
                            >
                                {importing ? (
                                    <>
                                        <span className="spinner-border spinner-border-sm"></span>
                                        Importing Customers...
                                    </>
                                ) : (
                                    <>
                                        <i className="bi bi-person-plus"></i>
                                        Import Customers
                                    </>
                                )}
                            </button>
                        </div>
                    </section>


                    <aside className="rr-import-panel rr-import-guide-panel">
                        <PanelTitle
                            number="02"
                            icon="bi-list-check"
                            title="Customer Import Rules"
                            text="How each customer row will be handled"
                        />


                        <ol className="rr-import-checklist">
                            <Guide
                                number="1"
                                title="New mobile number"
                                text="Creates a new customer and attaches it to this branch."
                            />

                            <Guide
                                number="2"
                                title="Existing company customer"
                                text="Attaches the existing customer to this branch without duplication."
                            />

                            <Guide
                                number="3"
                                title="Already in this branch"
                                text="Safely skips the row and keeps the existing customer unchanged."
                            />

                            <Guide
                                number="4"
                                title="Repeated mobile in file"
                                text="Marks the repeated row invalid so it can be corrected."
                            />
                        </ol>


                        <div className="rr-import-note">
                            <i className="bi bi-phone"></i>

                            <p>
                                <strong>
                                    Mobile number is the customer identity
                                </strong>

                                Spaces, +91 and a leading zero are removed automatically
                                before the duplicate check.
                            </p>
                        </div>
                    </aside>
                </div>


                {error && (
                    <ImportAlert
                        type="error"
                        title="Customer import validation failed"
                        text={error}
                    />
                )}


                {message && (
                    <ImportAlert
                        type={Number(
                            preview?.summary?.invalid_rows
                            ?? 0
                        ) > 0
                            ? 'warning'
                            : 'success'}
                        title="Customer file validation completed"
                        text={message}
                    />
                )}


                {preview && (
                    <CustomerPreview
                        preview={preview}
                    />
                )}
            </div>
        </>
    );
}


function CustomerPreview({
    preview,
}) {
    const summary = preview.summary ?? {};
    const rows = preview.rows ?? [];
    const errors = preview.errors ?? [];

    return (
        <section className="rr-import-preview-panel">
            <div className="rr-import-preview-head">
                <div>
                    <span>
                        CUSTOMER FILE RESULT
                    </span>

                    <h2>
                        Customer Import Preview
                    </h2>

                    <p>
                        Review new, attach, skip and invalid actions before importing.
                    </p>
                </div>

                <div className="rr-import-preview-status">
                    <i className={`bi ${Number(summary.invalid_rows ?? 0) > 0
                        ? 'bi-exclamation-triangle'
                        : 'bi-check-circle'}`}></i>

                    {Number(summary.invalid_rows ?? 0) > 0
                        ? 'Correction Required'
                        : 'Ready to Import'}
                </div>
            </div>


            <div className="rr-customer-import-summary-grid">
                <PreviewSummary
                    label="Total Rows"
                    value={summary.total_rows}
                    icon="bi-table"
                    tone="blue"
                />

                <PreviewSummary
                    label="New Customers"
                    value={summary.new_customers}
                    icon="bi-person-plus"
                    tone="green"
                />

                <PreviewSummary
                    label="Branch Attachments"
                    value={summary.branch_attachments}
                    icon="bi-link-45deg"
                    tone="purple"
                />

                <PreviewSummary
                    label="Skipped Existing"
                    value={summary.skipped_rows}
                    icon="bi-skip-forward"
                    tone="orange"
                />

                <PreviewSummary
                    label="Invalid Rows"
                    value={summary.invalid_rows}
                    icon="bi-exclamation-circle"
                    tone="red"
                />
            </div>


            <div className="rr-import-table-wrap">
                <table className="rr-import-table rr-customer-import-table">
                    <thead>
                        <tr>
                            <th>Row</th>
                            <th>Customer Name</th>
                            <th>Mobile</th>
                            <th>Customer Code</th>
                            <th>City</th>
                            <th>Import Action</th>
                        </tr>
                    </thead>

                    <tbody>
                        {rows.map((row) => (
                            <tr key={row.row}>
                                <td>
                                    {row.row}
                                </td>

                                <td>
                                    <strong>
                                        {row.customer_name || '—'}
                                    </strong>
                                </td>

                                <td className="rr-customer-mobile">
                                    {row.mobile || '—'}
                                </td>

                                <td>
                                    {row.customer_code || 'Auto-generated'}
                                </td>

                                <td>
                                    {row.city || '—'}
                                </td>

                                <td>
                                    <ActionBadge
                                        action={row.action}
                                        text={row.action_message}
                                    />
                                </td>
                            </tr>
                        ))}
                    </tbody>
                </table>
            </div>


            {errors.length > 0 && (
                <div className="rr-import-error-list">
                    <div className="rr-import-error-title">
                        <i className="bi bi-exclamation-diamond"></i>

                        <div>
                            <strong>
                                Customer rows requiring correction
                            </strong>

                            <span>
                                Correct these rows in the file and preview it again.
                            </span>
                        </div>
                    </div>

                    {errors.map((rowError) => (
                        <article key={rowError.row}>
                            <span className="rr-import-error-row">
                                Row {rowError.row}
                            </span>

                            <div>
                                <strong>
                                    {rowError.customer_name || 'Unnamed customer'}
                                    {rowError.mobile
                                        ? ` · ${rowError.mobile}`
                                        : ''}
                                </strong>

                                <ul>
                                    {(rowError.messages ?? []).map((item) => (
                                        <li key={item}>
                                            {item}
                                        </li>
                                    ))}
                                </ul>
                            </div>
                        </article>
                    ))}
                </div>
            )}
        </section>
    );
}


function Highlight({
    icon,
    title,
    text,
    tone,
}) {
    return (
        <article className={`rr-customer-import-highlight ${tone}`}>
            <i className={`bi ${icon}`}></i>

            <div>
                <strong>
                    {title}
                </strong>

                <small>
                    {text}
                </small>
            </div>
        </article>
    );
}


function ActionBadge({
    action,
    text,
}) {
    const icon = {
        create: 'bi-person-plus',
        attach: 'bi-link-45deg',
        skip: 'bi-skip-forward',
        invalid: 'bi-x-circle',
    }[action] ?? 'bi-question-circle';

    return (
        <span className={`rr-customer-import-action ${action}`}>
            <i className={`bi ${icon}`}></i>

            {text}
        </span>
    );
}


function PanelTitle({
    number: step,
    icon,
    title,
    text,
}) {
    return (
        <div className="rr-import-panel-title">
            <span className="rr-import-step">
                {step}
            </span>

            <span className="rr-import-panel-icon">
                <i className={`bi ${icon}`}></i>
            </span>

            <div>
                <h2>
                    {title}
                </h2>

                <p>
                    {text}
                </p>
            </div>
        </div>
    );
}


function Guide({
    number: step,
    title,
    text,
}) {
    return (
        <li>
            <span>
                {step}
            </span>

            <div>
                <strong>
                    {title}
                </strong>

                <small>
                    {text}
                </small>
            </div>
        </li>
    );
}


function ImportAlert({
    type,
    title,
    text,
}) {
    const icon = type === 'error'
        ? 'bi-exclamation-octagon'
        : type === 'warning'
            ? 'bi-exclamation-triangle'
            : 'bi-check-circle';

    return (
        <div className={`rr-import-alert ${type}`}>
            <i className={`bi ${icon}`}></i>

            <div>
                <strong>
                    {title}
                </strong>

                <span>
                    {text}
                </span>
            </div>
        </div>
    );
}


function PreviewSummary({
    label,
    value,
    icon,
    tone,
}) {
    return (
        <article className={`rr-import-summary-card ${tone}`}>
            <i className={`bi ${icon}`}></i>

            <div>
                <strong>
                    {number(value)}
                </strong>

                <span>
                    {label}
                </span>
            </div>
        </article>
    );
}


function extractError(
    requestError,
    fallback
) {
    const data = requestError?.response?.data;
    const validationErrors = data?.errors;

    if (
        validationErrors
        &&
        !Array.isArray(validationErrors)
    ) {
        const first = Object.values(validationErrors)
            .flat()
            .find(Boolean);

        if (first) {
            return first;
        }
    }

    return data?.message
        ?? fallback;
}


function fileSize(bytes) {
    const size = Number(bytes ?? 0);

    if (size < 1024) {
        return `${size} B`;
    }

    if (size < 1024 * 1024) {
        return `${(
            size / 1024
        ).toFixed(1)} KB`;
    }

    return `${(
        size / 1024 / 1024
    ).toFixed(2)} MB`;
}


function number(value) {
    return new Intl.NumberFormat(
        'en-IN'
    ).format(
        Number(value ?? 0)
    );
}


Import.layout = (
    page
) => (
    <BusinessLayout>
        {page}
    </BusinessLayout>
);
