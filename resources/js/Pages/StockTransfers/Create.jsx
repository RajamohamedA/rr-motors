import React, {
    useEffect,
    useMemo,
    useRef,
    useState,
} from "react";

import {
    Head,
    Link,
    useForm,
} from "@inertiajs/react";

import axios from "axios";

import BusinessLayout from "@/Layouts/BusinessLayout";
import MasterNavigation from "../../Components/Business/MasterNavigation";
import ConfirmModal from "../../Components/ConfirmModal";

import "../../../css/business/stock-transfer.css";

export default function Create({
    transferNo = "",
    transferDate = "",
    godowns = [],
}) {
    const searchInputRef =
        useRef(null);

    const searchTimerRef =
        useRef(null);

    const searchRequestRef =
        useRef(null);

    const searchSequenceRef =
        useRef(0);

    const [sourceLocation, setSourceLocation] =
        useState("");

    const [
        destinationLocation,
        setDestinationLocation,
    ] = useState("");

    const [search, setSearch] =
        useState("");

    const [results, setResults] =
        useState([]);

    const [searching, setSearching] =
        useState(false);

    const [searchOpen, setSearchOpen] =
        useState(false);

    const [message, setMessage] =
        useState("");

    const [confirmation, setConfirmation] =
        useState(false);

    const {
        data,
        setData,
        post,
        processing,
    } = useForm({
        transfer_date:
            transferDate,

        remarks:
            "",

        items:
            [],
    });

    const locations = useMemo(
        () => [
            {
                value: "shop",
                label: "Shop",
                code: "SHOP",
            },

            ...godowns.map(
                (godown) => ({
                    value:
                        String(
                            godown.id,
                        ),

                    label:
                        godown.godown_name,

                    code:
                        godown.godown_code ||
                        "GODOWN",
                }),
            ),
        ],
        [godowns],
    );

    const totalQuantity =
        useMemo(
            () =>
                data.items.reduce(
                    (
                        total,
                        item,
                    ) =>
                        total +
                        numberValue(
                            item.quantity,
                        ),
                    0,
                ),
            [data.items],
        );

    const totalCost =
        useMemo(
            () =>
                data.items.reduce(
                    (
                        total,
                        item,
                    ) =>
                        total +
                        numberValue(
                            item.quantity,
                        ) *
                        numberValue(
                            item.unit_cost,
                        ),
                    0,
                ),
            [data.items],
        );

    useEffect(() => {
        return () => {
            if (
                searchTimerRef.current
            ) {
                clearTimeout(
                    searchTimerRef.current,
                );
            }

            searchRequestRef.current?.abort();
        };
    }, []);

    const locationGodownId = (
        value,
    ) => {
        if (
            value === "" ||
            value === "shop"
        ) {
            return null;
        }

        return Number(value);
    };

    const sourceName =
        locationName(
            sourceLocation,
            locations,
        );

    const destinationName =
        locationName(
            destinationLocation,
            locations,
        );

    const searchProducts = (
        value,
    ) => {
        setSearch(value);
        setMessage("");

        if (
            searchTimerRef.current
        ) {
            clearTimeout(
                searchTimerRef.current,
            );
        }

        searchRequestRef.current?.abort();
        searchSequenceRef.current += 1;

        const sequence =
            searchSequenceRef.current;

        const term =
            value.trim();

        if (
            term.length < 1
        ) {
            setResults([]);
            setSearchOpen(false);
            setSearching(false);

            return;
        }

        setSearching(true);

        searchTimerRef.current =
            setTimeout(
                async () => {
                    const controller =
                        new AbortController();

                    searchRequestRef.current =
                        controller;

                    try {
                        const response =
                            await axios.get(
                                "/stock-transfers/products/search",
                                {
                                    params: {
                                        search:
                                            term,

                                        godown_id:
                                            locationGodownId(
                                                sourceLocation,
                                            ),
                                    },

                                    signal:
                                        controller.signal,
                                },
                            );

                        if (
                            sequence !==
                            searchSequenceRef.current
                        ) {
                            return;
                        }

                        const products =
                            Array.isArray(
                                response.data,
                            )
                                ? response.data
                                : [];

                        const selectedIds =
                            new Set(
                                data.items.map(
                                    (item) =>
                                        Number(
                                            item.product_id,
                                        ),
                                ),
                            );

                        setResults(
                            products.filter(
                                (product) =>
                                    !selectedIds.has(
                                        Number(
                                            product.id,
                                        ),
                                    ),
                            ),
                        );

                        setSearchOpen(
                            true,
                        );
                    } catch (error) {
                        if (
                            error.code ===
                            "ERR_CANCELED" ||
                            sequence !==
                            searchSequenceRef.current
                        ) {
                            return;
                        }

                        setResults([]);
                        setSearchOpen(
                            true,
                        );

                        setMessage(
                            error.response
                                ?.data
                                ?.message ||
                            "Unable to search products.",
                        );
                    } finally {
                        if (
                            sequence ===
                            searchSequenceRef.current
                        ) {
                            setSearching(
                                false,
                            );
                        }
                    }
                },
                300,
            );
    };

    const clearSearch = () => {
        if (
            searchTimerRef.current
        ) {
            clearTimeout(
                searchTimerRef.current,
            );
        }

        searchRequestRef.current?.abort();
        searchSequenceRef.current += 1;
        setSearch("");
        setResults([]);
        setSearchOpen(false);
        setSearching(false);
        setMessage("");

        searchInputRef.current?.focus();
    };

    const addFirstSearchResult = (
        event,
    ) => {
        if (
            event.key !== "Enter"
        ) {
            return;
        }

        event.preventDefault();

        if (
            results.length > 0
        ) {
            addProduct(
                results[0],
            );
        }
    };

    const addProduct = async (
        product,
    ) => {
        setMessage("");

        if (
            numberValue(
                product.available_stock,
            ) <= 0
        ) {
            setMessage(
                `${product.product_name} has no stock at ${sourceName}.`,
            );

            return;
        }

        if (
            sourceLocation === ""
        ) {
            setMessage(
                "Select the source location before adding products.",
            );

            return;
        }

        if (
            destinationLocation === ""
        ) {
            setMessage(
                "Select the destination location before adding products.",
            );

            return;
        }

        if (
            sourceLocation ===
            destinationLocation
        ) {
            setMessage(
                "Source and destination locations cannot be the same.",
            );

            return;
        }

        const duplicate =
            data.items.some(
                (item) =>
                    Number(
                        item.product_id,
                    ) ===
                    Number(
                        product.id,
                    ),
            );

        if (duplicate) {
            setMessage(
                `${product.product_name} is already added.`,
            );

            return;
        }

        try {
            const response =
                await axios.get(
                    "/stock-transfers/product-stock",
                    {
                        params: {
                            product_id:
                                product.id,

                            godown_id:
                                locationGodownId(
                                    sourceLocation,
                                ),
                        },
                    },
                );

            const availableStock =
                numberValue(
                    response.data
                        ?.stock,
                );

            const item = {
                row_key:
                    `${Date.now()}-${product.id}`,

                product_id:
                    product.id,

                product_code:
                    product.product_code ||
                    "",

                product_name:
                    product.product_name ||
                    "",

                part_number:
                    product.part_number ||
                    "",

                unit_name:
                    product.unit
                        ?.short_name ||
                    product.unit
                        ?.unit_name ||
                    "",

                from_godown_id:
                    locationGodownId(
                        sourceLocation,
                    ),

                to_godown_id:
                    locationGodownId(
                        destinationLocation,
                    ),

                source_stock:
                    availableStock,

                quantity:
                    "1",

                unit_cost:
                    String(
                        numberValue(
                            product.purchase_price,
                        ),
                    ),
            };

            setData(
                "items",
                [
                    ...data.items,
                    item,
                ],
            );

            clearSearch();

            setTimeout(
                () =>
                    searchInputRef.current?.focus(),
                0,
            );
        } catch (error) {
            setMessage(
                error.response
                    ?.data
                    ?.message ||
                "Unable to load source stock.",
            );
        }
    };

    const changeSource = async (
        value,
    ) => {
        setSourceLocation(
            value,
        );

        setMessage("");

        if (
            value !== "" &&
            value ===
            destinationLocation
        ) {
            setDestinationLocation(
                "",
            );
        }

        if (
            data.items.length === 0
        ) {
            return;
        }

        try {
            const updated =
                await Promise.all(
                    data.items.map(
                        async (
                            item,
                        ) => {
                            const response =
                                await axios.get(
                                    "/stock-transfers/product-stock",
                                    {
                                        params: {
                                            product_id:
                                                item.product_id,

                                            godown_id:
                                                locationGodownId(
                                                    value,
                                                ),
                                        },
                                    },
                                );

                            return {
                                ...item,

                                from_godown_id:
                                    locationGodownId(
                                        value,
                                    ),

                                source_stock:
                                    numberValue(
                                        response
                                            .data
                                            ?.stock,
                                    ),
                            };
                        },
                    ),
                );

            setData(
                "items",
                updated,
            );
        } catch (error) {
            setMessage(
                error.response
                    ?.data
                    ?.message ||
                "Unable to update source stock.",
            );
        }
    };

    const changeDestination = (
        value,
    ) => {
        setDestinationLocation(
            value,
        );

        setMessage("");

        if (
            value !== "" &&
            value === sourceLocation
        ) {
            setMessage(
                "Destination cannot be the same as the source.",
            );
        }

        setData(
            "items",
            data.items.map(
                (item) => ({
                    ...item,

                    to_godown_id:
                        locationGodownId(
                            value,
                        ),
                }),
            ),
        );
    };

    const updateItem = (
        index,
        field,
        value,
    ) => {
        const updated =
            [...data.items];

        updated[index] = {
            ...updated[index],
            [field]: value,
        };

        setData(
            "items",
            updated,
        );
    };

    const removeItem = (
        index,
    ) => {
        setData(
            "items",
            data.items.filter(
                (
                    _,
                    itemIndex,
                ) =>
                    itemIndex !==
                    index,
            ),
        );
    };

    const validateBeforeSubmit =
        () => {
            if (
                !data.transfer_date
            ) {
                return "Transfer date is required.";
            }

            if (
                sourceLocation === ""
            ) {
                return "Select the source location.";
            }

            if (
                destinationLocation === ""
            ) {
                return "Select the destination location.";
            }

            if (
                sourceLocation ===
                destinationLocation
            ) {
                return "Source and destination cannot be the same.";
            }

            if (
                data.items.length === 0
            ) {
                return "Add at least one product.";
            }

            for (
                let index = 0;
                index <
                data.items.length;
                index += 1
            ) {
                const item =
                    data.items[index];

                const quantity =
                    numberValue(
                        item.quantity,
                    );

                if (
                    quantity <= 0
                ) {
                    return `Enter a valid quantity for ${item.product_name}.`;
                }

                if (
                    quantity >
                    numberValue(
                        item.source_stock,
                    )
                ) {
                    return `${item.product_name}: available stock is ${quantityValue(
                        item.source_stock,
                    )}.`;
                }
            }

            return "";
        };

    const requestSubmit = (
        event,
    ) => {
        event.preventDefault();

        const validationMessage =
            validateBeforeSubmit();

        if (
            validationMessage
        ) {
            setMessage(
                validationMessage,
            );

            return;
        }

        setMessage("");
        setConfirmation(true);
    };

    const submitTransfer = () => {
        setConfirmation(false);

        post(
            "/stock-transfers",
            {
                preserveScroll: true,

                onError: (
                    errors,
                ) => {
                    const firstError =
                        Object.values(
                            errors,
                        )[0];

                    if (
                        firstError
                    ) {
                        setMessage(
                            String(
                                firstError,
                            ),
                        );
                    }
                },
            },
        );
    };

    return (
        <>
            <Head title="New Stock Transfer" />

            <div className="rr-transfer-page rr-transfer-create-page">
                <MasterNavigation
                    active="stock-transfers"
                    title="Inventory"
                    subtitle="Create an internal stock movement"
                />

                <div className="rr-transfer-index-ribbon">
                    <div>
                        <i className="bi bi-grid-1x2"></i>

                        <span>
                            Inventory
                        </span>

                        <i className="bi bi-chevron-right"></i>

                        <Link href="/stock-transfers">
                            Stock Transfers
                        </Link>

                        <i className="bi bi-chevron-right"></i>

                        <strong>
                            New Transfer
                        </strong>
                    </div>

                    <div>
                        <i className="bi bi-shield-check"></i>

                        Branch-isolated entry
                    </div>
                </div>

                <header className="rr-transfer-header">
                    <div className="rr-transfer-heading">
                        <div className="rr-transfer-heading-icon">
                            <i className="bi bi-arrow-left-right"></i>
                        </div>

                        <div>
                            <span>
                                Inventory Control
                            </span>

                            <h1>
                                New Stock Transfer
                            </h1>

                            <small>
                                Move stock between Shop and Godown locations
                            </small>
                        </div>
                    </div>

                    <Link
                        href="/stock-transfers"
                        className="rr-transfer-create-back"
                    >
                        <i className="bi bi-arrow-left"></i>

                        Back to Register
                    </Link>
                </header>

                {message && (
                    <div className="rr-transfer-form-message">
                        <i className="bi bi-exclamation-triangle"></i>

                        <span>
                            {message}
                        </span>

                        <button
                            type="button"
                            onClick={() =>
                                setMessage(
                                    "",
                                )
                            }
                        >
                            <i className="bi bi-x-lg"></i>
                        </button>
                    </div>
                )}

                <form
                    onSubmit={
                        requestSubmit
                    }
                >
                    <section className="rr-transfer-window">
                        <div className="rr-transfer-window-title">
                            <div>
                                <i className="bi bi-file-earmark-text"></i>

                                <strong>
                                    Transfer Details
                                </strong>
                            </div>

                            <span className="rr-transfer-document-badge">
                                {transferNo}
                            </span>
                        </div>

                        <div className="rr-transfer-document-grid">
                            <label className="rr-transfer-form-field">
                                <span>
                                    Transfer Number
                                </span>

                                <input
                                    type="text"
                                    value={
                                        transferNo
                                    }
                                    readOnly
                                />
                            </label>

                            <label className="rr-transfer-form-field">
                                <span>
                                    Transfer Date
                                    <b>*</b>
                                </span>

                                <input
                                    type="date"
                                    value={
                                        data.transfer_date
                                    }
                                    onChange={(
                                        event,
                                    ) =>
                                        setData(
                                            "transfer_date",
                                            event
                                                .target
                                                .value,
                                        )
                                    }
                                    required
                                />
                            </label>

                            <label className="rr-transfer-form-field">
                                <span>
                                    Source Location
                                    <b>*</b>
                                </span>

                                <select
                                    value={
                                        sourceLocation
                                    }
                                    onChange={(
                                        event,
                                    ) =>
                                        changeSource(
                                            event
                                                .target
                                                .value,
                                        )
                                    }
                                    required
                                >
                                    <option value="">
                                        Select source
                                    </option>

                                    {locations.map(
                                        (
                                            location,
                                        ) => (
                                            <option
                                                key={
                                                    location.value
                                                }
                                                value={
                                                    location.value
                                                }
                                            >
                                                {
                                                    location.label
                                                }{" "}
                                                [
                                                {
                                                    location.code
                                                }
                                                ]
                                            </option>
                                        ),
                                    )}
                                </select>
                            </label>

                            <div className="rr-transfer-direction">
                                <span>
                                    Transfer Direction
                                </span>

                                <div>
                                    <strong>
                                        {sourceName ||
                                            "Source"}
                                    </strong>

                                    <i className="bi bi-arrow-right"></i>

                                    <strong>
                                        {destinationName ||
                                            "Destination"}
                                    </strong>
                                </div>
                            </div>

                            <label className="rr-transfer-form-field">
                                <span>
                                    Destination
                                    <b>*</b>
                                </span>

                                <select
                                    value={
                                        destinationLocation
                                    }
                                    onChange={(
                                        event,
                                    ) =>
                                        changeDestination(
                                            event
                                                .target
                                                .value,
                                        )
                                    }
                                    required
                                >
                                    <option value="">
                                        Select destination
                                    </option>

                                    {locations.map(
                                        (
                                            location,
                                        ) => (
                                            <option
                                                key={
                                                    location.value
                                                }
                                                value={
                                                    location.value
                                                }
                                                disabled={
                                                    location.value ===
                                                    sourceLocation
                                                }
                                            >
                                                {
                                                    location.label
                                                }{" "}
                                                [
                                                {
                                                    location.code
                                                }
                                                ]
                                            </option>
                                        ),
                                    )}
                                </select>
                            </label>
                        </div>
                    </section>

                    <section className="rr-transfer-window rr-transfer-product-window">
                        <div className="rr-transfer-window-title">
                            <div>
                                <i className="bi bi-search"></i>

                                <strong>
                                    Add Products
                                </strong>
                            </div>

                            <span className="rr-transfer-record-count">
                                {
                                    data.items
                                        .length
                                }{" "}
                                items
                            </span>
                        </div>

                        <div className="rr-transfer-product-search">
                            <div className="rr-transfer-search-box">
                                <i className="bi bi-search"></i>

                                <input
                                    ref={
                                        searchInputRef
                                    }
                                    type="search"
                                    value={
                                        search
                                    }
                                    placeholder={
                                        sourceLocation &&
                                            destinationLocation
                                            ? "Search product name, code, barcode or part number"
                                            : "Select source and destination first"
                                    }
                                    disabled={
                                        !sourceLocation ||
                                        !destinationLocation ||
                                        sourceLocation ===
                                        destinationLocation
                                    }
                                    onChange={(
                                        event,
                                    ) =>
                                        searchProducts(
                                            event
                                                .target
                                                .value,
                                        )
                                    }
                                    onFocus={() =>
                                        setSearchOpen(
                                            results.length >
                                            0,
                                        )
                                    }
                                    onKeyDown={
                                        addFirstSearchResult
                                    }
                                />

                                {searching && (
                                    <span className="rr-transfer-search-loader">
                                        <i className="bi bi-arrow-repeat"></i>
                                    </span>
                                )}

                                {!searching &&
                                    search && (
                                        <button
                                            type="button"
                                            className="rr-transfer-search-clear"
                                            title="Clear product search"
                                            aria-label="Clear product search"
                                            onClick={
                                                clearSearch
                                            }
                                        >
                                            <i className="bi bi-x-lg"></i>
                                        </button>
                                    )}

                                {searchOpen &&
                                    search.trim() !== "" && (
                                        <div className="rr-transfer-search-results">
                                            {results.length >
                                                0 ? (
                                                results.map(
                                                    (
                                                        product,
                                                    ) => (
                                                        <button
                                                            key={
                                                                product.id
                                                            }
                                                            type="button"
                                                            disabled={
                                                                numberValue(
                                                                    product.available_stock,
                                                                ) <= 0
                                                            }
                                                            className={
                                                                numberValue(
                                                                    product.available_stock,
                                                                ) <= 0
                                                                    ? "is-out-of-stock"
                                                                    : ""
                                                            }
                                                            onClick={() =>
                                                                addProduct(
                                                                    product,
                                                                )
                                                            }
                                                        >
                                                            <div>
                                                                <strong>
                                                                    {
                                                                        product.product_name
                                                                    }
                                                                </strong>

                                                                <span>
                                                                    {product.product_code ||
                                                                        "No code"}

                                                                    {product.part_number
                                                                        ? ` • ${product.part_number}`
                                                                        : ""}
                                                                </span>
                                                            </div>

                                                            <div>
                                                                <span>
                                                                    Available at {sourceName}
                                                                </span>

                                                                <strong>
                                                                    {quantityValue(
                                                                        product.available_stock,
                                                                    )}{" "}
                                                                    {product.unit?.short_name ||
                                                                        product.unit?.unit_name ||
                                                                        "units"}
                                                                </strong>

                                                                <small>
                                                                    {numberValue(
                                                                        product.available_stock,
                                                                    ) > 0
                                                                        ? "Click to add"
                                                                        : "Out of stock"}
                                                                </small>
                                                            </div>
                                                        </button>
                                                    ),
                                                )
                                            ) : (
                                                <div className="rr-transfer-search-empty">
                                                    No products found
                                                </div>
                                            )}
                                        </div>
                                    )}
                            </div>

                            <small>
                                Press Enter to add the first matching product.
                            </small>
                        </div>
                    </section>

                    <section className="rr-transfer-window rr-transfer-entry-window">
                        <div className="rr-transfer-window-title">
                            <div>
                                <i className="bi bi-table"></i>

                                <strong>
                                    Transfer Items
                                </strong>
                            </div>

                            <div className="rr-transfer-entry-totals">
                                <span>
                                    Quantity
                                    <strong>
                                        {quantityValue(
                                            totalQuantity,
                                        )}
                                    </strong>
                                </span>

                                <span>
                                    Value
                                    <strong>
                                        {currency(
                                            totalCost,
                                        )}
                                    </strong>
                                </span>
                            </div>
                        </div>

                        <div className="rr-transfer-table-scroll">
                            <table className="rr-transfer-table rr-transfer-entry-table">
                                <thead>
                                    <tr>
                                        <th className="serial">
                                            #
                                        </th>

                                        <th>
                                            Product
                                        </th>

                                        <th>
                                            Unit
                                        </th>

                                        <th>
                                            Source
                                        </th>

                                        <th>
                                            Destination
                                        </th>

                                        <th className="number">
                                            Available
                                        </th>

                                        <th className="number">
                                            Transfer Qty
                                        </th>

                                        <th className="number">
                                            Unit Cost
                                        </th>

                                        <th className="number">
                                            Total
                                        </th>

                                        <th className="actions">
                                            Action
                                        </th>
                                    </tr>
                                </thead>

                                <tbody>
                                    {data.items.length >
                                        0 ? (
                                        data.items.map(
                                            (
                                                item,
                                                index,
                                            ) => {
                                                const rowTotal =
                                                    numberValue(
                                                        item.quantity,
                                                    ) *
                                                    numberValue(
                                                        item.unit_cost,
                                                    );

                                                const insufficient =
                                                    numberValue(
                                                        item.quantity,
                                                    ) >
                                                    numberValue(
                                                        item.source_stock,
                                                    );

                                                return (
                                                    <tr
                                                        key={
                                                            item.row_key
                                                        }
                                                        className={
                                                            insufficient
                                                                ? "has-stock-error"
                                                                : ""
                                                        }
                                                    >
                                                        <td className="serial">
                                                            {index +
                                                                1}
                                                        </td>

                                                        <td>
                                                            <strong className="rr-transfer-product-name">
                                                                {
                                                                    item.product_name
                                                                }
                                                            </strong>

                                                            <small>
                                                                {item.product_code ||
                                                                    "No code"}

                                                                {item.part_number
                                                                    ? ` • ${item.part_number}`
                                                                    : ""}
                                                            </small>
                                                        </td>

                                                        <td>
                                                            {item.unit_name ||
                                                                "—"}
                                                        </td>

                                                        <td>
                                                            <LocationBadge
                                                                type="source"
                                                                name={
                                                                    sourceName
                                                                }
                                                            />
                                                        </td>

                                                        <td>
                                                            <LocationBadge
                                                                type="destination"
                                                                name={
                                                                    destinationName
                                                                }
                                                            />
                                                        </td>

                                                        <td className="number">
                                                            <strong className="rr-transfer-available">
                                                                {quantityValue(
                                                                    item.source_stock,
                                                                )}
                                                            </strong>
                                                        </td>

                                                        <td className="number">
                                                            <input
                                                                className={`rr-transfer-qty-input ${insufficient
                                                                    ? "invalid"
                                                                    : ""
                                                                    }`}
                                                                type="number"
                                                                step="1"
                                                                min="1"
                                                                max={
                                                                    item.source_stock
                                                                }
                                                                value={
                                                                    item.quantity
                                                                }
                                                                onChange={(
                                                                    event,
                                                                ) =>
                                                                    updateItem(
                                                                        index,
                                                                        "quantity",
                                                                        event
                                                                            .target
                                                                            .value,
                                                                    )
                                                                }
                                                            />

                                                            {insufficient && (
                                                                <small className="rr-transfer-stock-error">
                                                                    Exceeds stock
                                                                </small>
                                                            )}
                                                        </td>

                                                        <td className="number">
                                                            <input
                                                                className="rr-transfer-cost-input"
                                                                type="number"
                                                                step="0.01"
                                                                min="0"
                                                                value={
                                                                    item.unit_cost
                                                                }
                                                                onChange={(
                                                                    event,
                                                                ) =>
                                                                    updateItem(
                                                                        index,
                                                                        "unit_cost",
                                                                        event
                                                                            .target
                                                                            .value,
                                                                    )
                                                                }
                                                            />
                                                        </td>

                                                        <td className="number money">
                                                            {currency(
                                                                rowTotal,
                                                            )}
                                                        </td>

                                                        <td className="actions">
                                                            <button
                                                                type="button"
                                                                className="rr-transfer-remove"
                                                                title="Remove product"
                                                                onClick={() =>
                                                                    removeItem(
                                                                        index,
                                                                    )
                                                                }
                                                            >
                                                                <i className="bi bi-trash3"></i>
                                                            </button>
                                                        </td>
                                                    </tr>
                                                );
                                            },
                                        )
                                    ) : (
                                        <tr>
                                            <td
                                                colSpan="10"
                                                className="rr-transfer-empty rr-transfer-entry-empty"
                                            >
                                                <i className="bi bi-box-seam"></i>

                                                <strong>
                                                    No products added
                                                </strong>

                                                <span>
                                                    Select locations and search for a product above.
                                                </span>
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </section>

                    <section className="rr-transfer-window">
                        <div className="rr-transfer-window-title">
                            <div>
                                <i className="bi bi-chat-left-text"></i>

                                <strong>
                                    Remarks
                                </strong>
                            </div>
                        </div>

                        <div className="rr-transfer-remarks-body">
                            <textarea
                                rows="3"
                                maxLength="1000"
                                value={
                                    data.remarks
                                }
                                placeholder="Optional transfer reason or internal note"
                                onChange={(
                                    event,
                                ) =>
                                    setData(
                                        "remarks",
                                        event
                                            .target
                                            .value,
                                    )
                                }
                            />

                            <span>
                                {
                                    data.remarks
                                        .length
                                }
                                /1000
                            </span>
                        </div>
                    </section>

                    <footer className="rr-transfer-form-footer">
                        <div>
                            <i className="bi bi-info-circle"></i>

                            Stock will move from{" "}
                            <strong>
                                {sourceName ||
                                    "source"}
                            </strong>{" "}
                            to{" "}
                            <strong>
                                {destinationName ||
                                    "destination"}
                            </strong>
                            .
                        </div>

                        <div>
                            <Link
                                href="/stock-transfers"
                                className="rr-transfer-secondary-button"
                            >
                                <i className="bi bi-x-lg"></i>

                                Close
                            </Link>

                            <button
                                type="submit"
                                className="rr-transfer-primary-button"
                                disabled={
                                    processing ||
                                    data.items
                                        .length ===
                                    0
                                }
                            >
                                {processing ? (
                                    <i className="bi bi-arrow-repeat rr-transfer-spin"></i>
                                ) : (
                                    <i className="bi bi-check2-circle"></i>
                                )}

                                {processing
                                    ? "Transferring..."
                                    : "Complete Transfer"}
                            </button>
                        </div>
                    </footer>
                </form>

                <ConfirmModal
                    show={
                        confirmation
                    }
                    title="Complete Stock Transfer"
                    message={`Transfer ${quantityValue(
                        totalQuantity,
                    )} quantity from ${sourceName ||
                    "the source"
                        } to ${destinationName ||
                        "the destination"
                        }?`}
                    confirmText="Complete Transfer"
                    cancelText="Review Again"
                    icon="bi-arrow-left-right"
                    variant="primary"
                    processing={
                        processing
                    }
                    onConfirm={
                        submitTransfer
                    }
                    onCancel={() =>
                        setConfirmation(
                            false,
                        )
                    }
                />
            </div>
        </>
    );
}

function LocationBadge({
    type,
    name,
}) {
    return (
        <span className={`rr-transfer-location ${type}`}>
            <i
                className={`bi ${name === "Shop"
                    ? "bi-shop"
                    : "bi-building"
                    }`}
            ></i>

            {name || "—"}
        </span>
    );
}

function locationName(
    value,
    locations,
) {
    return (
        locations.find(
            (location) =>
                location.value ===
                value,
        )?.label || ""
    );
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

function quantityValue(
    value,
) {
    return numberValue(
        value,
    ).toLocaleString(
        "en-IN",
        {
            minimumFractionDigits: 0,
            maximumFractionDigits: 3,
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
            minimumFractionDigits: 2,
            maximumFractionDigits: 2,
        },
    )}`;
}

Create.layout = (
    page,
) => (
    <BusinessLayout title="New Stock Transfer">
        {page}
    </BusinessLayout>
);
