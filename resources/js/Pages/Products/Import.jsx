import React, {
    useMemo,
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

import '../../../css/business/products.css';

import '../../../css/business/product-import.css';


const ACCEPTED_EXTENSIONS = [
    'xlsx',
    'xls',
    'csv',
];


export default function Import({
    template_url: templateUrl = '/products/import/template',
    limits = {},
    masters = {},
}) {
    const inputRef = useRef(null);

    const [
        file,
        setFile,
    ] = useState(null);

    const [
        dragging,
        setDragging,
    ] = useState(false);

    const [
        previewing,
        setPreviewing,
    ] = useState(false);

    const [
        importing,
        setImporting,
    ] = useState(false);

    const [
        preview,
        setPreview,
    ] = useState(null);

    const [
        message,
        setMessage,
    ] = useState(null);

    const [
        error,
        setError,
    ] = useState(null);


    const maximumSize =
        Number(
            limits.maximum_file_size_mb
            ?? 10
        );


    const canImport =
        Boolean(file)
        &&
        Boolean(preview)
        &&
        Number(preview?.summary?.valid_rows ?? 0) > 0
        &&
        Number(preview?.summary?.invalid_rows ?? 0) === 0
        &&
        !previewing
        &&
        !importing;


    const masterCards = useMemo(
        () => [
            {
                label: 'Categories',
                count: masters.categories?.length ?? 0,
                icon: 'bi-diagram-3',
                tone: 'blue',
            },
            {
                label: 'Brands',
                count: masters.brands?.length ?? 0,
                icon: 'bi-award',
                tone: 'purple',
            },
            {
                label: 'Units',
                count: masters.units?.length ?? 0,
                icon: 'bi-rulers',
                tone: 'green',
            },
            {
                label: 'Suppliers',
                count: masters.suppliers?.length ?? 0,
                icon: 'bi-truck',
                tone: 'orange',
            },
            {
                label: 'Godowns',
                count: masters.godowns?.length ?? 0,
                icon: 'bi-buildings',
                tone: 'cyan',
            },
        ],
        [masters]
    );


    const clearResult = () => {
        setPreview(null);
        setMessage(null);
        setError(null);
    };


    const chooseFile = (
        selectedFile
    ) => {
        clearResult();

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
                'Please choose an Excel (.xlsx or .xls) or CSV file.'
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
        clearResult();

        if (inputRef.current) {
            inputRef.current.value = '';
        }
    };


    const previewFile = async () => {
        if (!file || previewing || importing) {
            return;
        }

        setPreviewing(true);
        clearResult();

        try {
            const formData = new FormData();
            formData.append('file', file);

            const response = await axios.post(
                '/products/import/preview',
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
                    'Unable to preview the selected file.'
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
        setError(null);
        setMessage(null);

        try {
            const formData = new FormData();
            formData.append('file', file);

            const response = await axios.post(
                '/products/import',
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
                        ?? '/products'
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
                    'The products could not be imported.'
                )
            );
            setImporting(false);
        }
    };


    return (
        <>
            <Head title="Import Products" />


            <div className="rr-product-page rr-product-import-page">
                <MasterNavigation
                    active="products"
                    title="Product Import"
                    subtitle="Import products into the active branch using Excel or CSV"
                />


                <section className="rr-product-page-header rr-import-header">
                    <div className="rr-product-header-copy">
                        <div className="rr-product-header-icon">
                            <i className="bi bi-file-earmark-spreadsheet"></i>
                        </div>

                        <div>
                            <span className="rr-product-eyebrow">
                                PRODUCT MASTER
                            </span>

                            <h1>
                                Import Products
                            </h1>

                            <p>
                                Download the prepared template, enter product details,
                                preview validation and import all valid rows together.
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
                            href="/products"
                            className="rr-product-primary-btn rr-product-import-back-btn"
                        >
                            <i className="bi bi-arrow-left"></i>

                            Product List
                        </Link>
                    </div>
                </section>


                <section className="rr-import-master-strip">
                    {masterCards.map((card) => (
                        <article
                            key={card.label}
                            className={`rr-import-master-card ${card.tone}`}
                        >
                            <span>
                                <i className={`bi ${card.icon}`}></i>
                            </span>

                            <div>
                                <strong>
                                    {number(card.count)}
                                </strong>

                                <small>
                                    Active {card.label}
                                </small>
                            </div>
                        </article>
                    ))}
                </section>


                <div className="rr-import-layout">
                    <section className="rr-import-panel rr-import-upload-panel">
                        <PanelTitle
                            number="01"
                            icon="bi-cloud-arrow-up"
                            title="Choose Import File"
                            text="Excel and CSV files are accepted"
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
                                    ? 'bi-file-earmark-check'
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
                                        Drop the product file here
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
                                    <i className="bi bi-file-earmark-spreadsheet"></i>

                                    <span>
                                        <strong>
                                            {file.name}
                                        </strong>

                                        <small>
                                            Ready for server validation
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
                                        Validating File...
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
                                        Importing Products...
                                    </>
                                ) : (
                                    <>
                                        <i className="bi bi-database-add"></i>
                                        Import Valid Products
                                    </>
                                )}
                            </button>
                        </div>
                    </section>


                    <aside className="rr-import-panel rr-import-guide-panel">
                        <PanelTitle
                            number="02"
                            icon="bi-list-check"
                            title="Import Checklist"
                            text="Complete these checks before uploading"
                        />


                        <ol className="rr-import-checklist">
                            <Guide
                                number="1"
                                title="Download the template"
                                text="Use the current template so all required columns are available."
                            />

                            <Guide
                                number="2"
                                title="Use exact master names"
                                text="Category and unit are required. Brand, supplier and godown are optional."
                            />

                            <Guide
                                number="3"
                                title="Check prices and stock"
                                text="Prices and quantities must be zero or positive numbers."
                            />

                            <Guide
                                number="4"
                                title="Preview before importing"
                                text="Every row must pass validation before the Import button is enabled."
                            />
                        </ol>


                        <div className="rr-import-note">
                            <i className="bi bi-shield-check"></i>

                            <p>
                                <strong>
                                    Safe branch import
                                </strong>

                                Products, opening stock and stock-ledger records are
                                saved together. If saving fails, the transaction is
                                rolled back.
                            </p>
                        </div>
                    </aside>
                </div>


                {error && (
                    <div className="rr-import-alert error">
                        <i className="bi bi-exclamation-octagon"></i>

                        <div>
                            <strong>
                                Import validation failed
                            </strong>

                            <span>
                                {error}
                            </span>
                        </div>
                    </div>
                )}


                {message && (
                    <div className={`rr-import-alert ${Number(
                        preview?.summary?.invalid_rows
                        ?? 0
                    ) > 0
                        ? 'warning'
                        : 'success'}`}
                    >
                        <i className={`bi ${Number(
                            preview?.summary?.invalid_rows
                            ?? 0
                        ) > 0
                            ? 'bi-exclamation-triangle'
                            : 'bi-check-circle'}`}></i>

                        <div>
                            <strong>
                                File validation completed
                            </strong>

                            <span>
                                {message}
                            </span>
                        </div>
                    </div>
                )}


                {preview && (
                    <PreviewResult
                        preview={preview}
                    />
                )}
            </div>
        </>
    );
}


function PreviewResult({
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
                        VALIDATION RESULT
                    </span>

                    <h2>
                        Product Import Preview
                    </h2>

                    <p>
                        Review the file result before completing the import.
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


            <div className="rr-import-summary-grid">
                <PreviewSummary
                    label="Total Rows"
                    value={summary.total_rows}
                    icon="bi-table"
                    tone="blue"
                />

                <PreviewSummary
                    label="Valid Rows"
                    value={summary.valid_rows}
                    icon="bi-check-circle"
                    tone="green"
                />

                <PreviewSummary
                    label="Invalid Rows"
                    value={summary.invalid_rows}
                    icon="bi-exclamation-circle"
                    tone="red"
                />
            </div>


            <div className="rr-import-table-wrap">
                <table className="rr-import-table">
                    <thead>
                        <tr>
                            <th>Row</th>
                            <th>Product</th>
                            <th>Code</th>
                            <th>Category</th>
                            <th>Unit</th>
                            <th className="number">Selling Price</th>
                            <th className="number">Opening Stock</th>
                            <th>Status</th>
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
                                        {row.product_name || '—'}
                                    </strong>
                                </td>

                                <td>
                                    {row.product_code || 'Auto-generated'}
                                </td>

                                <td>
                                    {row.category || '—'}
                                </td>

                                <td>
                                    {row.unit || '—'}
                                </td>

                                <td className="number">
                                    {money(row.selling_price)}
                                </td>

                                <td className="number">
                                    {number(row.opening_stock)}
                                </td>

                                <td>
                                    <span className={`rr-import-row-status ${row.valid
                                        ? 'valid'
                                        : 'invalid'}`}
                                    >
                                        <i className={`bi ${row.valid
                                            ? 'bi-check-circle'
                                            : 'bi-x-circle'}`}></i>

                                        {row.valid
                                            ? 'Valid'
                                            : 'Invalid'}
                                    </span>
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
                                Rows requiring correction
                            </strong>

                            <span>
                                Correct these rows in the source file and preview again.
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
                                    {rowError.product_name || 'Unnamed product'}
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
    const data =
        requestError?.response?.data;

    const validationErrors =
        data?.errors;

    if (
        validationErrors
        &&
        !Array.isArray(validationErrors)
    ) {
        const first =
            Object.values(validationErrors)
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
        'en-IN',
        {
            maximumFractionDigits: 3,
        }
    ).format(
        Number(value ?? 0)
    );
}


function money(value) {
    return new Intl.NumberFormat(
        'en-IN',
        {
            style: 'currency',
            currency: 'INR',
            maximumFractionDigits: 2,
        }
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
