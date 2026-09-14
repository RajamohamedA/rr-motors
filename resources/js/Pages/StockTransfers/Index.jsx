import React, {
    useMemo,
    useState,
} from "react";

import {
    Head,
    Link,
    router,
} from "@inertiajs/react";

import BusinessLayout from "@/Layouts/BusinessLayout";
import MasterNavigation from "../../Components/Business/MasterNavigation";
import ConfirmModal from "../../Components/ConfirmModal";

import "../../../css/business/stock-transfer.css";

export default function Index({
    transfers = {
        data: [],
        links: [],
        meta: {},
    },
    filters = {},
    summary = {},
}) {
    const records =
        Array.isArray(
            transfers.data,
        )
            ? transfers.data
            : [];

    const [form, setForm] =
        useState({
            search:
                filters.search ??
                "",

            status:
                filters.status ===
                    null ||
                    filters.status ===
                    undefined
                    ? ""
                    : String(
                        filters.status,
                    ),

            date_from:
                filters.date_from ??
                "",

            date_to:
                filters.date_to ??
                "",

            per_page:
                String(
                    filters.per_page ??
                    25,
                ),
        });

    const [
        confirmation,
        setConfirmation,
    ] = useState({
        show: false,
        transfer: null,
        processing: false,
    });

    const activeFilterCount =
        useMemo(
            () =>
                [
                    form.search,
                    form.status,
                    form.date_from,
                    form.date_to,
                ].filter(
                    (value) =>
                        value !== "",
                ).length,
            [
                form.search,
                form.status,
                form.date_from,
                form.date_to,
            ],
        );

    const updateField = (
        field,
        value,
    ) => {
        setForm(
            (current) => ({
                ...current,
                [field]: value,
            }),
        );
    };

    const applyFilters = (
        nextForm = form,
    ) => {
        router.get(
            "/stock-transfers",
            cleanFilters(
                nextForm,
            ),
            {
                preserveState:
                    true,

                preserveScroll:
                    true,

                replace:
                    true,
            },
        );
    };

    const submitFilters = (
        event,
    ) => {
        event.preventDefault();

        applyFilters();
    };

    const resetFilters =
        () => {
            const next = {
                search: "",
                status: "",
                date_from: "",
                date_to: "",
                per_page: "25",
            };

            setForm(next);
            applyFilters(next);
        };

    const changePageSize = (
        value,
    ) => {
        const next = {
            ...form,
            per_page: value,
        };

        setForm(next);
        applyFilters(next);
    };

    const openDetails = (
        event,
        transfer,
    ) => {
        if (
            event.target.closest(
                "a, button, input, select",
            )
        ) {
            return;
        }

        router.visit(
            `/stock-transfers/${transfer.id}`,
        );
    };

    const openCancel = (
        transfer,
    ) => {
        setConfirmation({
            show: true,
            transfer,
            processing: false,
        });
    };

    const closeCancel =
        () => {
            if (
                confirmation.processing
            ) {
                return;
            }

            setConfirmation({
                show: false,
                transfer: null,
                processing: false,
            });
        };

    const cancelTransfer =
        () => {
            if (
                !confirmation.transfer
            ) {
                return;
            }

            setConfirmation(
                (current) => ({
                    ...current,
                    processing: true,
                }),
            );

            router.patch(
                `/stock-transfers/${confirmation.transfer.id}/cancel`,
                {},
                {
                    preserveScroll:
                        true,

                    onFinish:
                        () => {
                            setConfirmation({
                                show: false,
                                transfer:
                                    null,
                                processing:
                                    false,
                            });
                        },
                },
            );
        };

    return (
        <>
            <Head title="Stock Transfers" />

            <div className="rr-transfer-page rr-transfer-index-page">
                <MasterNavigation
                    active="stock-transfers"
                    title="Inventory"
                    subtitle="Internal stock movement and transfer history"
                />

                <div className="rr-transfer-index-ribbon">
                    <div>
                        <i className="bi bi-grid-1x2"></i>

                        <span>
                            Inventory
                        </span>

                        <i className="bi bi-chevron-right"></i>

                        <strong>
                            Stock Transfer Register
                        </strong>
                    </div>

                    <div>
                        <i className="bi bi-shield-check"></i>

                        Ledger-controlled records
                    </div>
                </div>

                <header className="rr-transfer-header rr-transfer-index-hero">
                    <div className="rr-transfer-heading">
                        <div className="rr-transfer-heading-icon">
                            <i className="bi bi-arrow-left-right"></i>
                        </div>

                        <div>
                            <span>
                                Inventory Control
                            </span>

                            <h1>
                                Stock Transfers
                            </h1>

                            <small>
                                Move and track stock between Shop and Godown locations
                            </small>
                        </div>
                    </div>

                    <Link
                        href="/stock-transfers/create"
                        className="rr-transfer-create"
                    >
                        <i className="bi bi-plus-lg"></i>

                        New Transfer
                    </Link>
                </header>

                <section className="rr-transfer-metrics">
                    <Metric
                        icon="bi-arrow-left-right"
                        label="Total Transfers"
                        value={integer(
                            summary.total,
                        )}
                        tone="slate"
                        caption="All transfer documents"
                    />

                    <Metric
                        icon="bi-check2-circle"
                        label="Completed"
                        value={integer(
                            summary.completed,
                        )}
                        tone="green"
                        caption="Active ledger movements"
                    />

                    <Metric
                        icon="bi-calendar-check"
                        label="Today"
                        value={integer(
                            summary.today,
                        )}
                        tone="blue"
                        caption="Transfers created today"
                    />

                    <Metric
                        icon="bi-x-octagon"
                        label="Cancelled"
                        value={integer(
                            summary.cancelled,
                        )}
                        tone="amber"
                        caption="Reversed transfer records"
                    />
                </section>

                <section className="rr-transfer-window rr-transfer-filter-window">
                    <div className="rr-transfer-window-title">
                        <div>
                            <i className="bi bi-funnel"></i>

                            <strong>
                                Search &amp; Filter
                            </strong>

                            {activeFilterCount >
                                0 && (
                                    <span className="rr-transfer-filter-count">
                                        {
                                            activeFilterCount
                                        }{" "}
                                        active
                                    </span>
                                )}
                        </div>

                        <button
                            type="button"
                            className="rr-transfer-reset"
                            onClick={
                                resetFilters
                            }
                        >
                            <i className="bi bi-arrow-counterclockwise"></i>

                            Reset
                        </button>
                    </div>

                    <form
                        className="rr-transfer-filter-body"
                        onSubmit={
                            submitFilters
                        }
                    >
                        <FilterField
                            className="rr-transfer-search-field"
                            label="Search"
                            icon="bi-search"
                        >
                            <input
                                type="search"
                                value={
                                    form.search
                                }
                                placeholder="Transfer no., product, code or remarks"
                                onChange={(
                                    event,
                                ) =>
                                    updateField(
                                        "search",
                                        event
                                            .target
                                            .value,
                                    )
                                }
                            />
                        </FilterField>

                        <FilterField
                            label="Status"
                            icon="bi-check2-square"
                        >
                            <select
                                value={
                                    form.status
                                }
                                onChange={(
                                    event,
                                ) =>
                                    updateField(
                                        "status",
                                        event
                                            .target
                                            .value,
                                    )
                                }
                            >
                                <option value="">
                                    All Statuses
                                </option>

                                <option value="1">
                                    Completed
                                </option>

                                <option value="0">
                                    Cancelled
                                </option>
                            </select>
                        </FilterField>

                        <FilterField
                            label="From Date"
                            icon="bi-calendar3"
                        >
                            <DateInput
                                value={
                                    form.date_from
                                }
                                onChange={(
                                    value,
                                ) =>
                                    updateField(
                                        "date_from",
                                        value,
                                    )
                                }
                            />
                        </FilterField>

                        <FilterField
                            label="To Date"
                            icon="bi-calendar3"
                        >
                            <DateInput
                                value={
                                    form.date_to
                                }
                                min={
                                    form.date_from ||
                                    undefined
                                }
                                onChange={(
                                    value,
                                ) =>
                                    updateField(
                                        "date_to",
                                        value,
                                    )
                                }
                            />
                        </FilterField>

                        <button
                            type="submit"
                            className="rr-transfer-filter-button"
                        >
                            <i className="bi bi-search"></i>

                            Apply Filter
                        </button>
                    </form>
                </section>

                <section className="rr-transfer-window rr-transfer-table-window">
                    <div className="rr-transfer-window-title rr-transfer-register-title">
                        <div>
                            <i className="bi bi-table"></i>

                            <strong>
                                Transfer Register
                            </strong>

                            <span className="rr-transfer-record-count">
                                {integer(
                                    transfers.total ??
                                    transfers
                                        .meta
                                        ?.total ??
                                    records.length,
                                )}{" "}
                                records
                            </span>
                        </div>

                        <div className="rr-transfer-page-size">
                            <label htmlFor="transfer-page-size">
                                Rows
                            </label>

                            <select
                                id="transfer-page-size"
                                value={
                                    form.per_page
                                }
                                onChange={(
                                    event,
                                ) =>
                                    changePageSize(
                                        event
                                            .target
                                            .value,
                                    )
                                }
                            >
                                <option value="25">
                                    25
                                </option>

                                <option value="50">
                                    50
                                </option>

                                <option value="100">
                                    100
                                </option>

                                <option value="200">
                                    200
                                </option>
                            </select>
                        </div>
                    </div>

                    <div className="rr-transfer-table-scroll">
                        <table className="rr-transfer-table">
                            <thead>
                                <tr>
                                    <th className="serial">
                                        #
                                    </th>

                                    <th>
                                        Transfer
                                    </th>

                                    <th>
                                        Date
                                    </th>

                                    <th>
                                        Branch
                                    </th>

                                    <th className="number">
                                        Items
                                    </th>

                                    <th className="number">
                                        Total Qty
                                    </th>

                                    <th className="number">
                                        Stock Value
                                    </th>

                                    <th>
                                        Created By
                                    </th>

                                    <th>
                                        Status
                                    </th>

                                    <th className="actions">
                                        Actions
                                    </th>
                                </tr>
                            </thead>

                            <tbody>
                                {records.length >
                                    0 ? (
                                    records.map(
                                        (
                                            transfer,
                                            index,
                                        ) => {
                                            const completed =
                                                Number(
                                                    transfer.status,
                                                ) ===
                                                1;

                                            return (
                                                <tr
                                                    key={
                                                        transfer.id
                                                    }
                                                    className={
                                                        completed
                                                            ? ""
                                                            : "is-cancelled"
                                                    }
                                                    tabIndex={
                                                        0
                                                    }
                                                    onClick={(
                                                        event,
                                                    ) =>
                                                        openDetails(
                                                            event,
                                                            transfer,
                                                        )
                                                    }
                                                    onKeyDown={(
                                                        event,
                                                    ) => {
                                                        if (
                                                            event.key ===
                                                            "Enter" ||
                                                            event.key ===
                                                            " "
                                                        ) {
                                                            event.preventDefault();

                                                            router.visit(
                                                                `/stock-transfers/${transfer.id}`,
                                                            );
                                                        }
                                                    }}
                                                >
                                                    <td className="serial">
                                                        {rowNumber(
                                                            transfers,
                                                            index,
                                                        )}
                                                    </td>

                                                    <td>
                                                        <Link
                                                            href={`/stock-transfers/${transfer.id}`}
                                                            className="rr-transfer-number"
                                                        >
                                                            {transfer.transfer_no ||
                                                                "—"}
                                                        </Link>

                                                        <small>
                                                            {truncate(
                                                                transfer.remarks,
                                                                48,
                                                            ) ||
                                                                "No remarks"}
                                                        </small>
                                                    </td>

                                                    <td className="date-cell">
                                                        {dateValue(
                                                            transfer.transfer_date,
                                                        )}
                                                    </td>

                                                    <td>
                                                        <div className="rr-transfer-branch">
                                                            <i className="bi bi-shop"></i>

                                                            <span>
                                                                {transfer
                                                                    .store
                                                                    ?.store_name ??
                                                                    "—"}
                                                            </span>
                                                        </div>
                                                    </td>

                                                    <td className="number strong">
                                                        {integer(
                                                            transfer.items_count,
                                                        )}
                                                    </td>

                                                    <td className="number quantity">
                                                        {quantity(
                                                            transfer.total_quantity,
                                                        )}
                                                    </td>

                                                    <td className="number money">
                                                        {currency(
                                                            transfer.total_cost,
                                                        )}
                                                    </td>

                                                    <td>
                                                        <div className="rr-transfer-user">
                                                            <i className="bi bi-person-circle"></i>

                                                            <span>
                                                                {transfer
                                                                    .creator
                                                                    ?.name ??
                                                                    "System"}
                                                            </span>
                                                        </div>
                                                    </td>

                                                    <td>
                                                        <StatusBadge
                                                            status={
                                                                transfer.status
                                                            }
                                                        />
                                                    </td>

                                                    <td className="actions">
                                                        <div className="rr-transfer-row-actions">
                                                            <Link
                                                                href={`/stock-transfers/${transfer.id}`}
                                                                className="view"
                                                                title="View transfer"
                                                            >
                                                                <i className="bi bi-eye"></i>
                                                            </Link>

                                                            <a
                                                                href={`/stock-transfers/${transfer.id}/print`}
                                                                target="_blank"
                                                                rel="noreferrer"
                                                                className="print"
                                                                title="Print transfer"
                                                            >
                                                                <i className="bi bi-printer"></i>
                                                            </a>

                                                            <a
                                                                href={`/stock-transfers/${transfer.id}/pdf`}
                                                                className="pdf"
                                                                title="Download PDF"
                                                            >
                                                                <i className="bi bi-file-earmark-pdf"></i>
                                                            </a>

                                                            {completed && (
                                                                <button
                                                                    type="button"
                                                                    className="cancel"
                                                                    title="Cancel transfer"
                                                                    onClick={() =>
                                                                        openCancel(
                                                                            transfer,
                                                                        )
                                                                    }
                                                                >
                                                                    <i className="bi bi-x-octagon"></i>
                                                                </button>
                                                            )}
                                                        </div>
                                                    </td>
                                                </tr>
                                            );
                                        },
                                    )
                                ) : (
                                    <tr>
                                        <td
                                            colSpan={
                                                10
                                            }
                                            className="rr-transfer-empty"
                                        >
                                            <i className="bi bi-inbox"></i>

                                            <strong>
                                                No stock transfers found
                                            </strong>

                                            <span>
                                                Change the filters or create a new stock transfer.
                                            </span>

                                            <Link href="/stock-transfers/create">
                                                <i className="bi bi-plus-lg"></i>

                                                New Transfer
                                            </Link>
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>

                    <TableFooter
                        transfers={
                            transfers
                        }
                        records={
                            records
                        }
                    />
                </section>

                <ConfirmModal
                    show={
                        confirmation.show
                    }
                    title="Cancel Stock Transfer"
                    message={`Cancel "${confirmation
                            .transfer
                            ?.transfer_no ??
                        ""
                        }"? Reversing stock-ledger entries will be created. The original transfer will remain available for audit history.`}
                    confirmText="Cancel Transfer"
                    cancelText="Keep Transfer"
                    icon="bi-x-octagon"
                    variant="danger"
                    processing={
                        confirmation.processing
                    }
                    onConfirm={
                        cancelTransfer
                    }
                    onCancel={
                        closeCancel
                    }
                />
            </div>
        </>
    );
}

function Metric({
    icon,
    label,
    value,
    tone,
    caption = "",
}) {
    return (
        <article className={`rr-transfer-metric ${tone}`}>
            <div className="rr-transfer-metric-icon">
                <i className={`bi ${icon}`}></i>
            </div>

            <div className="rr-transfer-metric-content">
                <span>
                    {label}
                </span>

                <strong>
                    {value}
                </strong>

                <em>
                    {caption}
                </em>
            </div>

            <i className="bi bi-chevron-right rr-transfer-metric-arrow"></i>
        </article>
    );
}

function FilterField({
    label,
    icon,
    className = "",
    children,
}) {
    return (
        <label className={`rr-transfer-field ${className}`}>
            <span>
                <i className={`bi ${icon}`}></i>

                {label}
            </span>

            {children}
        </label>
    );
}

function DateInput({
    value,
    min,
    onChange,
}) {
    const openPicker = (
        event,
    ) => {
        if (
            typeof event
                .currentTarget
                .showPicker ===
            "function"
        ) {
            event.currentTarget.showPicker();
        }
    };

    return (
        <input
            type="date"
            value={
                value
            }
            min={
                min
            }
            onClick={
                openPicker
            }
            onChange={(
                event,
            ) =>
                onChange(
                    event.target.value,
                )
            }
        />
    );
}

function StatusBadge({
    status,
}) {
    const completed =
        Number(status) === 1;

    return (
        <span
            className={`rr-transfer-status ${completed
                    ? "completed"
                    : "cancelled"
                }`}
        >
            <i
                className={`bi ${completed
                        ? "bi-check2-circle"
                        : "bi-x-circle"
                    }`}
            ></i>

            {completed
                ? "Completed"
                : "Cancelled"}
        </span>
    );
}

function TableFooter({
    transfers,
    records,
}) {
    const links =
        Array.isArray(
            transfers.links,
        )
            ? transfers.links
            : [];

    const from =
        transfers.from ??
        transfers.meta?.from ??
        0;

    const to =
        transfers.to ??
        transfers.meta?.to ??
        records.length;

    const total =
        transfers.total ??
        transfers.meta?.total ??
        records.length;

    return (
        <footer className="rr-transfer-table-footer">
            <span>
                Showing{" "}
                <strong>
                    {integer(from)}
                </strong>{" "}
                to{" "}
                <strong>
                    {integer(to)}
                </strong>{" "}
                of{" "}
                <strong>
                    {integer(total)}
                </strong>{" "}
                records
            </span>

            {links.length >
                3 && (
                    <nav aria-label="Stock transfer pages">
                        {links.map(
                            (
                                link,
                                index,
                            ) => (
                                <Link
                                    key={`${link.label}-${index}`}
                                    href={
                                        link.url ??
                                        "#"
                                    }
                                    preserveScroll
                                    preserveState
                                    className={`${link.active
                                            ? "active"
                                            : ""
                                        } ${!link.url
                                            ? "disabled"
                                            : ""
                                        }`}
                                    dangerouslySetInnerHTML={{
                                        __html:
                                            link.label,
                                    }}
                                    onClick={(
                                        event,
                                    ) => {
                                        if (
                                            !link.url
                                        ) {
                                            event.preventDefault();
                                        }
                                    }}
                                />
                            ),
                        )}
                    </nav>
                )}
        </footer>
    );
}

function cleanFilters(
    form,
) {
    return Object.fromEntries(
        Object.entries(
            form,
        ).filter(
            ([, value]) =>
                value !== "",
        ),
    );
}

function rowNumber(
    transfers,
    index,
) {
    const from =
        transfers.from ??
        transfers.meta?.from ??
        1;

    return Number(from) + index;
}

function numberValue(
    value,
) {
    const parsed =
        Number(value);

    return Number.isFinite(
        parsed,
    )
        ? parsed
        : 0;
}

function integer(
    value,
) {
    return Math.round(
        numberValue(value),
    ).toLocaleString(
        "en-IN",
    );
}

function quantity(
    value,
) {
    return numberValue(
        value,
    ).toLocaleString(
        "en-IN",
        {
            minimumFractionDigits:
                0,

            maximumFractionDigits:
                3,
        },
    );
}

function currency(
    value,
) {
    return `₹${numberValue(
        value,
    ).toLocaleString(
        "en-IN",
        {
            minimumFractionDigits:
                2,

            maximumFractionDigits:
                2,
        },
    )}`;
}

function dateValue(
    value,
) {
    if (
        !value
    ) {
        return "—";
    }

    const date =
        new Date(
            `${String(
                value,
            ).slice(
                0,
                10,
            )}T00:00:00`,
        );

    if (
        Number.isNaN(
            date.getTime(),
        )
    ) {
        return value;
    }

    return date.toLocaleDateString(
        "en-IN",
        {
            day:
                "2-digit",

            month:
                "short",

            year:
                "numeric",
        },
    );
}

function truncate(
    value,
    length,
) {
    const text =
        String(
            value ??
            "",
        ).trim();

    return text.length >
        length
        ? `${text.slice(
            0,
            length - 1,
        )}…`
        : text;
}

Index.layout = (
    page,
) => (
    <BusinessLayout title="Stock Transfers">
        {page}
    </BusinessLayout>
);