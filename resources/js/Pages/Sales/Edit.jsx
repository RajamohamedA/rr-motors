import React, { useEffect, useLayoutEffect, useMemo, useRef, useState } from "react";

import axios from "axios";
import { createPortal } from "react-dom";

import { Head, Link, useForm, usePage } from "@inertiajs/react";

import BusinessLayout from "@/Layouts/BusinessLayout";
import MasterNavigation from "../../Components/Business/MasterNavigation";

import "../../../css/business/sales.css";
import "../../../css/business/sales-payment-split.css";
import "../../../css/business/sales-create.css";

export default function Edit({
    sale,
    customers = [],
    mechanics = [],
    godowns = [],
    accounts = [],
}) {
    const { business } = usePage().props;

    const initialRows = (sale?.items ?? []).map((item) => {
        const quantity = numberValue(item.quantity ?? item.qty ?? 1);
        const freeQuantity = numberValue(item.free_quantity);
        const currentShopStock = firstNumber(
            item.product?.shop_stock,
            item.shop_stock,
            0,
        );
        const editableShopStock = firstNumber(
            item.editable_shop_stock,
            item.available_stock,
            currentShopStock + quantity + freeQuantity,
        );

        const storedGst = numberValue(item.gst_percent ?? item.gst);
        const factor = 1 + storedGst / 100;
        const discountType = item.discount_type ?? 'fixed';
        const storedDiscount = numberValue(item.discount_value ?? item.discount ?? item.item_discount);
        const inclusiveDiscount = discountType === 'percentage'
            ? storedDiscount : round2(storedDiscount * factor);
        const lineTotal = numberValue(item.line_total);
        const gross = discountType === 'percentage' && storedDiscount < 100
            ? lineTotal / (1 - storedDiscount / 100)
            : lineTotal + (discountType === 'fixed' ? inclusiveDiscount : 0);
        const inclusiveRate = quantity > 0 && gross > 0
            ? round2(gross / quantity)
            : round2(numberValue(item.sale_price) * factor);

        return {
            original_shop_qty: item.godown_id == null ? quantity + freeQuantity : 0,
            key: `existing-${item.id}`,
            product_id: item.product_id,
            product: item.product,
            qty: String(quantity),
            free_quantity: String(freeQuantity),
            sale_price: String(inclusiveRate),
            item_discount_type: discountType,
            item_discount: String(inclusiveDiscount),
            gst: String(storedGst > 0 ? storedGst : 18),
            shop_stock: editableShopStock,
            stock_state:
                editableShopStock >= quantity + freeQuantity ? "enough" : "low",
        };
    });

    const [customerOptions, setCustomerOptions] = useState(
        uniqueById([sale?.customer, ...customers].filter(Boolean)),
    );
    const [mechanicOptions, setMechanicOptions] = useState(
        uniqueById([sale?.mechanic, ...mechanics].filter(Boolean)),
    );

    const [customerSearch, setCustomerSearch] = useState("");
    const [customerSearchOpen, setCustomerSearchOpen] = useState(false);
    const [customerSearching, setCustomerSearching] = useState(false);

    const [customerModal, setCustomerModal] = useState(false);
    const [mechanicModal, setMechanicModal] = useState(false);

    const [customerSaving, setCustomerSaving] = useState(false);
    const [mechanicSaving, setMechanicSaving] = useState(false);

    const [customerErrors, setCustomerErrors] = useState({});
    const [mechanicErrors, setMechanicErrors] = useState({});

    const [productSearch, setProductSearch] = useState("");
    const [productResults, setProductResults] = useState([]);
    const [productSearchOpen, setProductSearchOpen] = useState(false);
    const [productSearching, setProductSearching] = useState(false);

    const [rows, setRows] = useState(initialRows);
    const [quickQuantity, setQuickQuantity] = useState(false);
    const [selectedResult, setSelectedResult] = useState(0);
    const [productSearchError, setProductSearchError] = useState('');
    const [searchPopup, setSearchPopup] = useState({ top: 0, left: 0, width: 640, maxHeight: 320 });
    const [submitting, setSubmitting] = useState(false);
    const [submitIntent, setSubmitIntent] = useState('save');
    const [submitError, setSubmitError] = useState('');
    const submitLock = useRef(false);
    const printWindow = useRef(null);
    const searchVersion = useRef(0);
    const pendingSearchEnter = useRef(null);
    const billingFormRef = useRef(null);



    const initialPayments = (sale?.sale_payments ?? [])
        .filter(
            (payment) =>
                payment.status === "Completed" &&
                payment.account_id,
        )
        .map((payment) => ({
            account_id: payment.account_id,
            payment_mode: payment.payment_mode,
            amount: String(payment.amount ?? 0),
            reference_no: payment.reference_no ?? "",
        }));

    const [stockAssist, setStockAssist] = useState({
        show: false,
        loading: false,
        transferring: false,
        rowIndex: null,
        data: null,
        selectedGodownId: "",
        transferQty: "",
        error: "",
    });

    const customerSearchTimer = useRef(null);
    const customerSearchBoxRef = useRef(null);
    const productSearchBoxRef = useRef(null);
    const searchInputRef = useRef(null);

    const { data, setData, put, processing, errors, clearErrors, transform } = useForm({
        invoice_date: dateInputValue(sale?.invoice_date),
        customer_id: String(sale?.customer_id ?? ""),
        billing_type: sale?.billing_type ?? "gst",

        sale_source: sale?.sale_source ?? "customer",
        mechanic_id: String(sale?.mechanic_id ?? ""),
        commission_type: sale?.commission_type ?? "percentage",
        commission_percent: String(sale?.commission_percent ?? 10),
        commission_fixed_amount:
            sale?.commission_type === "fixed"
                ? String(sale?.commission_amount ?? 0)
                : "",

        final_discount: String(sale?.final_discount ?? 0),
        paid_amount: String(sale?.paid_amount ?? 0),
        payments: initialPayments,
        remarks: sale?.remarks ?? "",

        product_id: [],
        godown_id: [],
        qty: [],
        free_quantity: [],
        sale_price: [],
        discount_type: [],
        discount_value: [],
        item_discount: [],
        gst: [],
    });

    /* ---------------------------------------------------------------------
             Synchronize React product rows -> Inertia form arrays
             --------------------------------------------------------------------- */

    useEffect(() => {
        setData((previous) => ({
            ...previous,
            product_id: rows.map((row) => row.product_id),
            godown_id: rows.map(() => ""), // Sales are from Shop after Stock Assist transfer.
            qty: rows.map((row) => row.qty),
            free_quantity: rows.map((row) => row.free_quantity),
            sale_price: rows.map((row) => row.sale_price),
            discount_type: rows.map((row) => row.item_discount_type ?? "fixed"),
            discount_value: rows.map((row) => row.item_discount),
            // Retained for legacy backend compatibility. The new controller
            // uses discount_type + discount_value first.
            item_discount: rows.map((row) => String(calculateRow(row, data.billing_type).discount)),
            gst: rows.map((row) => row.gst),
        }));
    }, [rows, data.billing_type]);

    /* ---------------------------------------------------------------------
             Customer Search
             --------------------------------------------------------------------- */

    useEffect(() => {
        if (customerSearchTimer.current) {
            window.clearTimeout(customerSearchTimer.current);
        }

        const value = customerSearch.trim();

        if (value.length < 2) {
            setCustomerSearching(false);
            return;
        }

        customerSearchTimer.current = window.setTimeout(async () => {
            setCustomerSearching(true);

            try {
                const response = await axios.get("/sales/customers/search", {
                    params: { search: value },
                });

                const results = Array.isArray(response.data?.customers)
                    ? response.data.customers
                    : [];

                setCustomerOptions((previous) => uniqueById([...previous, ...results]));
                setCustomerSearchOpen(true);
            } catch (error) {
                console.error(error);
                setCustomerSearchOpen(true);
            } finally {
                setCustomerSearching(false);
            }
        }, 300);

        return () => {
            if (customerSearchTimer.current) {
                window.clearTimeout(customerSearchTimer.current);
            }
        };
    }, [customerSearch]);

    useEffect(() => {
        const closeCustomerSearch = (event) => {
            if (
                customerSearchBoxRef.current &&
                !customerSearchBoxRef.current.contains(event.target)
            ) {
                setCustomerSearchOpen(false);
            }
        };

        document.addEventListener("mousedown", closeCustomerSearch);

        return () => {
            document.removeEventListener("mousedown", closeCustomerSearch);
        };
    }, []);

    const selectedCustomer = useMemo(
        () =>
            customerOptions.find(
                (customer) => String(customer.id) === String(data.customer_id),
            ) ?? null,
        [customerOptions, data.customer_id],
    );

    const visibleCustomerOptions = useMemo(() => {
        const value = customerSearch.trim().toLowerCase();

        if (value.length >= 2) {
            return customerOptions
                .filter((customer) =>
                    [
                        customer.customer_name,
                        customer.customer_code,
                        customer.mobile,
                        customer.phone,
                        customer.gst_number,
                    ]
                        .filter(Boolean)
                        .some((field) => String(field).toLowerCase().includes(value)),
                )
                .slice(0, 20);
        }

        return customerOptions.slice(0, 10);
    }, [customerOptions, customerSearch]);

    const selectCustomer = (customer) => {
        setCustomerOptions((previous) => uniqueById([...previous, customer]));
        setData("customer_id", String(customer.id));
        clearErrors("customer_id");
        setCustomerSearch("");
        setCustomerSearchOpen(false);
    };

    const clearCustomer = () => {
        setData("customer_id", "");
        setCustomerSearch("");
        setCustomerSearchOpen(false);
    };

    /* ---------------------------------------------------------------------
             Product Search
             --------------------------------------------------------------------- */

    useEffect(() => {
        const controller = new AbortController();
        const version = ++searchVersion.current;
        const value = productSearch.trim();
        setSelectedResult(0);
        setProductResults([]);
        setProductSearchError('');
        if (!value) {
            setProductSearching(false);
            setProductSearchOpen(false);
            return () => controller.abort();
        }
        setProductSearching(true);
        setProductSearchOpen(true);
        const timer = window.setTimeout(async () => {
            try {
                const response = await axios.get('/sales/search', {
                    params: { search: value },
                    signal: controller.signal,
                });
                if (controller.signal.aborted || version !== searchVersion.current) return;
                setProductResults(Array.isArray(response.data) ? response.data : []);
            } catch (error) {
                if (controller.signal.aborted || version !== searchVersion.current) return;
                setProductSearchError('Search failed. Try again or check your connection.');
            } finally {
                if (!controller.signal.aborted && version === searchVersion.current) {
                    setProductSearching(false);
                }
            }
        }, 180);
        return () => {
            window.clearTimeout(timer);
            controller.abort();
        };
    }, [productSearch]);

    useLayoutEffect(() => {
        if (!productSearchOpen) return;
        const positionPopup = () => {
            const rect = searchInputRef.current?.getBoundingClientRect();
            if (!rect) return;
            const width = Math.min(760, window.innerWidth - 24);
            const below = window.innerHeight - rect.bottom - 16;
            const above = rect.top - 16;
            const openAbove = below < 220 && above > below;
            const maxHeight = Math.max(100, Math.min(380, openAbove ? above : below));
            setSearchPopup({
                top: openAbove ? Math.max(12, rect.top - maxHeight - 4) : rect.bottom + 4,
                left: Math.max(12, Math.min(rect.left, window.innerWidth - width - 12)),
                width,
                maxHeight,
            });
        };
        positionPopup();
        window.addEventListener('resize', positionPopup);
        window.addEventListener('scroll', positionPopup, true);
        return () => {
            window.removeEventListener('resize', positionPopup);
            window.removeEventListener('scroll', positionPopup, true);
        };
    }, [productSearchOpen]);

    useEffect(() => {
        if (productSearchOpen) {
            document.getElementById(`sale-product-option-${selectedResult}`)
                ?.scrollIntoView({ block: 'nearest' });
        }
    }, [selectedResult, productSearchOpen]);

    useEffect(() => {
        const closeProductSearch = (event) => {
            if (!productSearchBoxRef.current?.contains(event.target)
                && !event.target.closest?.('.rr-sale-counter-results')) {
                setProductSearchOpen(false);
            }
        };
        document.addEventListener('mousedown', closeProductSearch);
        return () => document.removeEventListener('mousedown', closeProductSearch);
    }, []);

    useEffect(() => {
        if (stockAssist.show || customerModal || mechanicModal) setProductSearchOpen(false);
    }, [stockAssist.show, customerModal, mechanicModal]);

    const focusProductSearch = () => {
        requestAnimationFrame(() => {
            searchInputRef.current?.focus();
            searchInputRef.current?.select();
        });
    };

    const changeProductSearch = (value) => {
        // Invalidate the previous response immediately, before the next effect.
        searchVersion.current += 1;
        pendingSearchEnter.current = null;
        setProductResults([]);
        setSelectedResult(0);
        setProductSearching(Boolean(value.trim()));
        setProductSearch(value);
        setProductSearchOpen(Boolean(value.trim()));
    };

    const handleProductSearchKey = (event) => {
        if (event.nativeEvent.isComposing) return;
        if (event.key === 'ArrowLeft' && (!productSearch || event.target.selectionStart === 0)) {
            const previousFields = Array.from(billingFormRef.current.querySelectorAll(
                `[data-sale-row="${rows.length - 1}"][data-sale-field]`
            ));
            const previous = previousFields.at(-1);
            if (previous) {
                event.preventDefault();
                setProductSearchOpen(false);
                previous.focus(); previous.select?.();
            }
        } else if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
            event.preventDefault();
            setProductSearchOpen(true);
            setSelectedResult((previous) => {
                const count = productResults.length;
                if (!count) return 0;
                return (previous + (event.key === 'ArrowDown' ? 1 : -1) + count) % count;
            });
        } else if (event.key === 'Enter') {
            event.preventDefault();
            if (event.repeat) return;
            if (event.ctrlKey || event.metaKey) {
                if (rows.length) {
                    document.querySelector(`[data-sale-row="${rows.length - 1}"][data-sale-field="qty"]`)?.focus();
                }
                return;
            }
            if (productSearching) {
                pendingSearchEnter.current = productSearch.trim();
            } else if (productResults[selectedResult]) {
                addProduct(productResults[selectedResult]);
            }
        } else if (event.key === 'Escape') {
            event.preventDefault();
            changeProductSearch('');
        }
    };

    useEffect(() => {
        if (productSearching || !pendingSearchEnter.current) return;
        const query = pendingSearchEnter.current;
        pendingSearchEnter.current = null;
        // Scanner Enter can arrive before the AJAX response. Auto-add only
        // when that same search has exactly one unambiguous result.
        if (query === productSearch.trim() && productResults.length === 1) {
            addProduct(productResults[0]);
        }
    }, [productSearching, productResults, productSearch]);

    const handleBillingKeys = (event) => {
        if (event.defaultPrevented || event.nativeEvent.isComposing || stockAssist.show) return;
        if (event.key === 'F2') {
            event.preventDefault();
            focusProductSearch();
            return;
        }
        const input = event.target;
        const rowIndex = input.dataset.saleRow;
        if (rowIndex === undefined) {
            // Avoid completing a bill accidentally while entering customer/payment data.
            if (event.key === 'Enter' && input.tagName === 'INPUT') event.preventDefault();
            return;
        }
        const fields = Array.from(billingFormRef.current.querySelectorAll(
            `[data-sale-row="${rowIndex}"][data-sale-field]`
        ));
        const position = fields.indexOf(input);
        const last = position === fields.length - 1;
        if (event.key === 'Enter' || event.key === 'Escape'
            || event.key === 'ArrowRight' || event.key === 'ArrowLeft'
            || (event.key === 'Tab' && !event.shiftKey && last)) {
            event.preventDefault();
            if ((event.ctrlKey || event.metaKey) && event.key === 'Enter') {
                focusProductSearch();
                return;
            }
            const backwards = event.key === 'ArrowLeft' || (event.key === 'Enter' && event.shiftKey);
            const next = fields[position + (backwards ? -1 : 1)];
            if (next) {
                next.focus(); next.select?.();
            } else if (backwards && Number(rowIndex) > 0) {
                const previousFields = Array.from(billingFormRef.current.querySelectorAll(
                    `[data-sale-row="${Number(rowIndex) - 1}"][data-sale-field]`
                ));
                const previous = previousFields.at(-1);
                previous?.focus(); previous?.select?.();
            } else if (event.key === 'ArrowRight' && Number(rowIndex) < rows.length - 1) {
                const nextRow = billingFormRef.current.querySelector(
                    `[data-sale-row="${Number(rowIndex) + 1}"][data-sale-field="qty"]`
                );
                nextRow?.focus(); nextRow?.select?.();
            } else focusProductSearch();
        }
    };

    /* ---------------------------------------------------------------------
             Validation auto focus
             --------------------------------------------------------------------- */

    useEffect(() => {
        const keys = Object.keys(errors ?? {});
        if (!keys.length) return;

        window.setTimeout(() => {
            focusValidationError(keys[0], searchInputRef);
        }, 120);
    }, [errors]);

    /* ---------------------------------------------------------------------
             Sale source
             --------------------------------------------------------------------- */

    const changeSaleSource = (source) => {
        if (source === "customer") {
            setData((previous) => ({
                ...previous,
                sale_source: "customer",
                mechanic_id: "",
                commission_type: "percentage",
                commission_percent: "10",
                commission_fixed_amount: "",
            }));
        } else {
            setData("sale_source", "mechanic");
        }

        clearErrors(
            "sale_source",
            "mechanic_id",
            "commission_type",
            "commission_percent",
            "commission_fixed_amount",
        );
    };

    /* ---------------------------------------------------------------------
             Product rows
             --------------------------------------------------------------------- */

    const addProduct = (product) => {
        const existingIndex = rows.findIndex(
            (row) => Number(row.product_id) === Number(product.id),
        );

        if (existingIndex >= 0) {
            updateRow(
                existingIndex,
                "qty",
                String(numberValue(rows[existingIndex].qty) + 1),
            );
            changeProductSearch("");
            setProductSearchOpen(false);
            if (quickQuantity) focusProductSearch();
            else requestAnimationFrame(() => {
                document.querySelector(`[data-sale-row="${existingIndex}"][data-sale-field="qty"]`)?.focus();
            });
            return;
        }

        const salePrice = firstNumber(
            product.sale_price,
            product.selling_price,
            product.price,
            product.mrp,
            0,
        );

        const gst = firstNumber(
            product.gst_percent,
            product.gst,
            product.tax_percent,
            0,
        );

        setRows((previous) => [
            ...previous,
            {
                key: `${product.id}-${Date.now()}`,
                product_id: product.id,
                product,
                qty: "1",
                free_quantity: "0",
                sale_price: String(salePrice),
                item_discount_type: "fixed",
                item_discount: "0",
                gst: String(gst > 0 ? gst : 18),
                shop_stock: numberValue(product.shop_stock),
                stock_state: numberValue(product.shop_stock) >= 1 ? "enough" : "low",
            },
        ]);

        changeProductSearch("");
        setProductSearchOpen(false);

        if (quickQuantity) {
            focusProductSearch();
        } else {
            requestAnimationFrame(() => {
                const input = document.querySelector(
                    `[data-sale-row="${rows.length}"][data-sale-field="qty"]`,
                );
                input?.focus();
                input?.select?.();
            });
        }
    };

    const updateRow = (index, field, value) => {
        setRows((previous) => {
            const next = [...previous];
            next[index] = {
                ...next[index],
                [field]: value,
            };
            return next;
        });

        clearErrors(`${field === "qty" ? "qty" : field}.${index}`);
    };

    const removeRow = (index) => {
        setRows((previous) => previous.filter((_, rowIndex) => rowIndex !== index));
    };

    /* ---------------------------------------------------------------------
             Stock Assist
             --------------------------------------------------------------------- */

    const checkRowStock = async (index) => {
        const row = rows[index];
        if (!row) return;

        const required = numberValue(row.qty) + numberValue(row.free_quantity);

        if (required <= 0) return;

        // Check silently. Opening the modal here caused an empty/loading modal
        // to flash even when Shop already had enough stock.
        setStockAssist({
            show: false,
            loading: true,
            transferring: false,
            rowIndex: index,
            data: null,
            selectedGodownId: "",
            transferQty: "",
            error: "",
        });

        try {
            const response = await axios.post("/sales/check-stock", {
                product_id: row.product_id,
                sale_id: sale.id,
                quantity: required,
            });

            const result = response.data;

            if (result.shop_has_enough) {
                setRows((previous) => {
                    const next = [...previous];
                    next[index] = {
                        ...next[index],
                        shop_stock: numberValue(result.shop_stock),
                        stock_state: "enough",
                    };
                    return next;
                });

                setStockAssist((previous) => ({
                    ...previous,
                    show: false,
                    loading: false,
                }));

                return;
            }

            const preferred = (result.godowns ?? []).find(
                (godown) => numberValue(godown.available_stock) > 0,
            );

            setRows((previous) => {
                const next = [...previous];
                next[index] = {
                    ...next[index],
                    shop_stock: numberValue(result.shop_stock),
                    stock_state: result.total_has_enough ? "transfer" : "insufficient",
                };
                return next;
            });

            setStockAssist((previous) => ({
                ...previous,
                show: true,
                loading: false,
                data: result,
                selectedGodownId: preferred ? String(preferred.id) : "",
                transferQty: preferred
                    ? String(
                        preferred.suggested_transfer_qty ?? result.shortage_qty ?? "",
                    )
                    : "",
            }));
        } catch (error) {
            setStockAssist((previous) => ({
                ...previous,
                show: true,
                loading: false,
                error: apiError(error, "Unable to check stock."),
            }));
        }
    };

    const changeStockAssistGodown = (godownId) => {
        const selected = stockAssist.data?.godowns?.find(
            (godown) => Number(godown.id) === Number(godownId),
        );

        setStockAssist((previous) => ({
            ...previous,
            selectedGodownId: godownId,
            transferQty: selected
                ? String(selected.suggested_transfer_qty ?? "")
                : "",
            error: "",
        }));
    };

    const transferToShop = async () => {
        if (!stockAssist.data || stockAssist.rowIndex === null) return;

        const selected = stockAssist.data.godowns?.find(
            (godown) => Number(godown.id) === Number(stockAssist.selectedGodownId),
        );

        const qty = numberValue(stockAssist.transferQty);

        if (!selected) {
            setStockAssist((previous) => ({
                ...previous,
                error: "Select a Godown.",
            }));
            return;
        }

        if (qty <= 0) {
            setStockAssist((previous) => ({
                ...previous,
                error: "Enter a transfer quantity greater than zero.",
            }));
            return;
        }

        if (qty > numberValue(selected.available_stock)) {
            setStockAssist((previous) => ({
                ...previous,
                error: `Maximum available in this Godown is ${formatQty(selected.available_stock)}.`,
            }));
            return;
        }

        setStockAssist((previous) => ({
            ...previous,
            transferring: true,
            error: "",
        }));

        try {
            const response = await axios.post("/sales/quick-transfer", {
                product_id: stockAssist.data.product.id,
                from_godown_id: selected.id,
                transfer_qty: qty,
            });

            const transfer = response.data?.transfer;
            const index = stockAssist.rowIndex;

            setRows((previous) => {
                const next = [...previous];
                const row = next[index];

                if (!row) return previous;

                const required = numberValue(row.qty) + numberValue(row.free_quantity);
                const newShopStock = numberValue(transfer?.shop_stock) + numberValue(row.original_shop_qty);

                next[index] = {
                    ...row,
                    shop_stock: newShopStock,
                    stock_state: newShopStock >= required ? "enough" : "transfer",
                };

                return next;
            });

            // Re-check after transfer. This supports partial transfers cleanly.
            setStockAssist((previous) => ({
                ...previous,
                transferring: false,
                show: false,
            }));

            window.setTimeout(() => checkRowStock(index), 70);
        } catch (error) {
            setStockAssist((previous) => ({
                ...previous,
                transferring: false,
                error: apiError(error, "Unable to transfer stock."),
            }));
        }
    };

    /* ---------------------------------------------------------------------
             Quick Customer
             --------------------------------------------------------------------- */

    const [quickCustomer, setQuickCustomer] = useState({
        customer_name: "",
        mobile: "",
        email: "",
        gst_number: "",
        address: "",
    });

    const saveQuickCustomer = async (event) => {
        event.preventDefault();
        setCustomerSaving(true);
        setCustomerErrors({});

        try {
            const response = await axios.post("/sales/quick-customer", quickCustomer);
            const customer = response.data?.customer;

            if (customer) {
                selectCustomer(customer);
                setCustomerModal(false);
                setQuickCustomer({
                    customer_name: "",
                    mobile: "",
                    email: "",
                    gst_number: "",
                    address: "",
                });
            }
        } catch (error) {
            setCustomerErrors(
                error.response?.data?.errors ?? {
                    general: apiError(error, "Unable to create customer."),
                },
            );
        } finally {
            setCustomerSaving(false);
        }
    };

    /* ---------------------------------------------------------------------
             Quick Mechanic
             --------------------------------------------------------------------- */

    const [quickMechanic, setQuickMechanic] = useState({
        mechanic_name: "",
        mobile: "",
        email: "",
        address: "",
        city: "",
    });

    const saveQuickMechanic = async (event) => {
        event.preventDefault();
        setMechanicSaving(true);
        setMechanicErrors({});

        try {
            const response = await axios.post("/sales/quick-mechanic", quickMechanic);
            const mechanic = response.data?.mechanic;

            if (mechanic) {
                setMechanicOptions((previous) => uniqueById([...previous, mechanic]));
                setData((previous) => ({
                    ...previous,
                    sale_source: "mechanic",
                    mechanic_id: String(mechanic.id),
                }));
                setMechanicModal(false);
                setQuickMechanic({
                    mechanic_name: "",
                    mobile: "",
                    email: "",
                    address: "",
                    city: "",
                });
            }
        } catch (error) {
            setMechanicErrors(
                error.response?.data?.errors ?? {
                    general: apiError(error, "Unable to create mechanic."),
                },
            );
        } finally {
            setMechanicSaving(false);
        }
    };

    /* ---------------------------------------------------------------------
             Totals
             --------------------------------------------------------------------- */

    const totals = useMemo(() => {
        let taxableSubtotal = 0;
        let itemDiscountTotal = 0;
        let gstTotal = 0;

        rows.forEach((row) => {
            const qty = numberValue(row.qty);
            const price = numberValue(row.sale_price);
            const calculated = calculateRow(row, data.billing_type);
            const safeDiscount = calculated.discount;
            const taxable = calculated.taxable;
            const gstAmount = calculated.gstAmount;

            taxableSubtotal += taxable;
            itemDiscountTotal += safeDiscount;
            gstTotal += gstAmount;
        });

        taxableSubtotal = round2(taxableSubtotal);
        itemDiscountTotal = round2(itemDiscountTotal);
        gstTotal = round2(gstTotal);

        const grossInvoice = round2(taxableSubtotal + gstTotal);

        const finalDiscount = Math.min(
            numberValue(data.final_discount),
            grossInvoice,
        );

        const beforeRound = round2(grossInvoice - finalDiscount);
        const grandTotal = Math.round(beforeRound);
        const roundOff = round2(grandTotal - beforeRound);

        const splitPaid = round2(
            (data.payments ?? []).reduce(
                (total, payment) => total + numberValue(payment.amount),
                0,
            ),
        );

        const paid = data.payments.length > 0
            ? splitPaid
            : Math.min(numberValue(data.paid_amount), grandTotal);

        const balance = round2(grandTotal - paid);

        let paymentStatus = "Unpaid";
        if (paid > 0 && balance > 0) paymentStatus = "Partially Paid";
        if (paid > 0 && balance <= 0) paymentStatus = "Paid";

        const commissionBase = Math.max(0, taxableSubtotal);

        let commissionAmount = 0;

        if (data.sale_source === "mechanic") {
            if (data.commission_type === "percentage") {
                commissionAmount = round2(
                    (commissionBase * numberValue(data.commission_percent)) / 100,
                );
            } else {
                commissionAmount = round2(numberValue(data.commission_fixed_amount));
            }
        }

        return {
            taxableSubtotal,
            itemDiscountTotal,
            gstTotal,
            grossInvoice,
            finalDiscount,
            beforeRound,
            roundOff,
            grandTotal,
            paid,
            balance,
            paymentStatus,
            commissionBase,
            commissionAmount,
        };
    }, [
        rows,
        data.billing_type,
        data.final_discount,
        data.paid_amount,
        data.payments,
        data.sale_source,
        data.commission_type,
        data.commission_percent,
        data.commission_fixed_amount,
    ]);

    const hasStockProblem = rows.some((row) => {
        const required = numberValue(row.qty) + numberValue(row.free_quantity);
        return required > numberValue(row.shop_stock);
    });

    /* ---------------------------------------------------------------------
             Submit
             --------------------------------------------------------------------- */

    const submit = async (event, requestedIntent = null) => {
        event.preventDefault();
        if (submitLock.current || processing || stockAssist.show) return;
        if (!rows.length) { focusProductSearch(); return; }
        const intent = requestedIntent ?? event.nativeEvent?.submitter?.value ?? 'save';
        submitLock.current = true;
        setSubmitIntent(intent);
        setSubmitting(true);
        setSubmitError('');
        if (intent === 'print') {
            printWindow.current = window.open('about:blank', '_blank');
            if (!printWindow.current) {
                setSubmitError('Allow pop-ups for this application, then use Complete & Print.');
                submitLock.current = false;
                setSubmitting(false);
                return;
            }
            printWindow.current.document.write(`<!doctype html><html><head><title>Preparing invoice</title><style>
                body{margin:0;min-height:100vh;display:grid;place-items:center;background:#edf3fc;font:14px Arial;color:#193b67}
                .box{padding:32px 40px;border:1px solid #cddbed;border-radius:12px;background:white;text-align:center;box-shadow:0 8px 25px #193b6712}
                .spinner{width:34px;height:34px;border:3px solid #dce7f6;border-top-color:#315fce;border-radius:50%;margin:0 auto 16px;animation:spin .8s linear infinite}
                h1{font-size:18px}p{color:#526580;line-height:1.6}@keyframes spin{to{transform:rotate(360deg)}}
                @media(prefers-reduced-motion:reduce){.spinner{animation:none}}
                </style></head><body><div class="box" role="status"><div class="spinner"></div><h1>Updating your invoice</h1><p>Updating the sale and preparing the print layout.<br>Please keep this tab open.</p></div></body></html>`);
            printWindow.current.document.close();
        }
        const cancelPrint = () => {
            printWindow.current?.close();
            printWindow.current = null;
        };
        try {
            for (let index = 0; index < rows.length; index += 1) {
                const row = rows[index];
                if (numberValue(row.qty) + numberValue(row.free_quantity) > numberValue(row.shop_stock)) {
                    cancelPrint();
                    await checkRowStock(index);
                    submitLock.current = false;
                    setSubmitting(false);
                    return;
                }
            }
            // Build arrays from the current rows to avoid a form synchronization race.
            transform((formData) => ({
                ...formData,
                print_after_save: intent === 'print',
                price_includes_gst: true,
                product_id: rows.map((row) => row.product_id),
                godown_id: rows.map(() => ''),
                qty: rows.map((row) => row.qty),
                free_quantity: rows.map((row) => row.free_quantity),
                sale_price: rows.map((row) => row.sale_price),
                discount_type: rows.map((row) => row.item_discount_type ?? 'fixed'),
                discount_value: rows.map((row) => row.item_discount),
                item_discount: rows.map((row) => String(calculateRow(row, data.billing_type).discount)),
                gst: rows.map((row) => row.gst),
            }));
            put(`/sales/${sale.id}`, {
                preserveScroll: true,
                onSuccess: async (page) => {
                    if (intent !== 'print') return;
                    const url = page.props.salePrintUrl ?? page.props.flash?.sale_print_url;
                    if (!url) {
                        cancelPrint();
                        window.alert('Sale updated. The print URL was not returned. Open this invoice from Sales and print it. Do not submit it again.');
                        return;
                    }
                    const target = new URL(url, window.location.origin);
                    if (target.origin !== window.location.origin) {
                        cancelPrint();
                        window.alert('Sale updated. The print URL must belong to this application.');
                        return;
                    }
                    if (printWindow.current && !printWindow.current.closed) {
                        const popup = printWindow.current;
                        printWindow.current = null;
                        try {
                            const response = await fetch(target.href, { credentials: 'same-origin' });
                            if (!response.ok || new URL(response.url).pathname !== target.pathname) {
                                throw new Error('Sale updated, but the print layout could not be loaded. Open the saved invoice to print; do not save it again.');
                            }
                            const html = await response.text();
                            if (!popup.closed) {
                                popup.history.replaceState(null, '', target.href);
                                popup.document.open();
                                popup.document.write(html);
                                popup.document.close();
                            }
                        } catch (error) {
                            if (!popup.closed) {
                                popup.document.querySelector('h1').textContent = 'Invoice saved';
                                popup.document.querySelector('p').textContent = error.message || 'Unable to load printing. Open the saved invoice to print.';
                                popup.document.querySelector('.spinner')?.remove();
                            }
                        }
                    } else {
                        window.alert('Sale updated. The print window was closed. Open the saved invoice to print.');
                    }
                    printWindow.current = null;
                },
                onError: cancelPrint,
                onCancel: cancelPrint,
                onFinish: () => {
                    submitLock.current = false;
                    setSubmitting(false);
                },
            });
        } catch (error) {
            cancelPrint();
            submitLock.current = false;
            setSubmitting(false);
            setSubmitError('Unable to complete the request. Check whether the invoice was saved before retrying.');
        }
    };

    useEffect(() => {
        const completeFromKeyboard = (event) => {
            if (!['F9', 'F10'].includes(event.key)) return;
            event.preventDefault();
            if (event.repeat || processing || submitting || stockAssist.show || customerModal || mechanicModal) return;
            // Always bring the invoice grid into focus before validation/save.
            const item = billingFormRef.current?.querySelector(
                `[data-sale-row="${Math.max(0, rows.length - 1)}"][data-sale-field="qty"]`
            );
            if (item) { item.focus(); item.select?.(); }
            else searchInputRef.current?.focus();
            if (billingFormRef.current?.reportValidity()) {
                submit(event, event.key === 'F10' ? 'print' : 'save');
            }
        };
        document.addEventListener('keydown', completeFromKeyboard);
        return () => document.removeEventListener('keydown', completeFromKeyboard);
    });

    return (
        <>
            <Head title={`Edit Sale ${sale?.invoice_no ?? ""}`} />

            {(processing || submitting) && (
                <div className="rr-sale-busy-overlay" role="status" aria-live="polite">
                    <div className="rr-sale-busy-panel">
                        <span className="spinner-border" aria-hidden="true" />
                        <strong>{submitIntent === 'print' ? 'Updating & preparing print' : 'Updating your sale'}</strong>
                        <span>Please wait. Your invoice is being processed.</span>
                    </div>
                </div>
            )}
            <div className="rr-sale-page">
                <MasterNavigation
                    active="sales"
                    title="Sales"
                    subtitle="Fast counter billing, customer accounts and mechanic referrals"
                />

                <form ref={billingFormRef} onSubmit={submit} onKeyDown={handleBillingKeys} aria-busy={processing || submitting}>
                    {/* =========================================================
                        HEADER
                       ========================================================= */}
                    <section className="rr-sale-header">
                        <div className="rr-sale-header-main">
                            <Link
                                href="/sales"
                                className="rr-sale-back-btn"
                            >
                                <i className="bi bi-arrow-left"></i>
                            </Link>

                            <div className="rr-sale-header-icon">
                                <i className="bi bi-receipt-cutoff"></i>
                            </div>

                            <div>
                                <span className="rr-sale-eyebrow">Counter Billing</span>
                                <h1>Edit Sale</h1>
                                <div className="rr-sale-header-meta">
                                    <span>
                                        <i className="bi bi-receipt"></i>
                                        {sale?.invoice_no || "Sale Invoice"}
                                    </span>
                                    <span>
                                        <i className="bi bi-buildings"></i>
                                        {business?.company_name ?? "Company"}
                                    </span>
                                    <span>
                                        <i className="bi bi-shop"></i>
                                        {business?.store_name ?? "Branch"}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div className="rr-sale-header-actions">
                            <Link
                                href="/sales"
                                className="rr-sale-btn secondary"
                            >
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                name="sale_action"
                                value="save"
                                className="rr-sale-btn primary"
                                disabled={processing || submitting || rows.length === 0 || hasStockProblem}
                            >
                                {processing || submitting ? (
                                    <>
                                        <span className="spinner-border spinner-border-sm"></span>{" "}
                                        Saving...
                                    </>
                                ) : (
                                    <>
                                        <i className="bi bi-check-lg"></i> Update Sale
                                    </>
                                )}
                            </button>
                            <button
                                type="submit"
                                name="sale_action"
                                value="print"
                                className="rr-sale-btn print"
                                disabled={processing || submitting || rows.length === 0 || hasStockProblem}
                                title="Save invoice and print (F10)"
                            >
                                {processing || submitting ? <><span className="spinner-border spinner-border-sm" aria-hidden="true" /> Preparing…</>
                                    : <><i className="bi bi-printer" aria-hidden="true" /> Update &amp; Print</>}
                            </button>
                        </div>
                    </section>

                    {/* =========================================================
                        CUSTOMER + SOURCE
                       ========================================================= */}
                    <section className="rr-sale-window rr-sale-customer-window">
                        <WindowTitle
                            icon="bi-person"
                            title="Customer & Sale Source"
                        />

                        <div className="rr-sale-window-body">
                            <div className="rr-sale-customer-grid">
                                <div className="rr-sale-field">
                                    <label>
                                        Customer <Required />
                                    </label>
                                    <div className="rr-sale-select-action align-items-start">
                                        <div
                                            ref={customerSearchBoxRef}
                                            className="position-relative flex-grow-1"
                                            data-validation-key="customer_id"
                                        >
                                            {selectedCustomer ? (
                                                <div className="form-control d-flex align-items-center justify-content-between gap-2 bg-white">
                                                    <button
                                                        type="button"
                                                        className="btn btn-link text-start text-decoration-none p-0 flex-grow-1 text-dark"
                                                        onClick={() => {
                                                            setCustomerSearch("");
                                                            setCustomerSearchOpen(true);
                                                        }}
                                                    >
                                                        <span className="d-block fw-semibold text-truncate">
                                                            {selectedCustomer.customer_name}
                                                        </span>
                                                        <small className="d-block text-muted text-truncate">
                                                            {[
                                                                selectedCustomer.customer_code,
                                                                selectedCustomer.mobile ||
                                                                selectedCustomer.phone,
                                                            ]
                                                                .filter(Boolean)
                                                                .join(" · ") || "Selected customer"}
                                                        </small>
                                                    </button>

                                                    <button
                                                        type="button"
                                                        className="btn btn-sm btn-outline-secondary"
                                                        title="Clear selected customer"
                                                        aria-label="Clear selected customer"
                                                        onClick={clearCustomer}
                                                    >
                                                        <i className="bi bi-x-lg"></i>
                                                    </button>
                                                </div>
                                            ) : (
                                                <div className="input-group">
                                                    <span className="input-group-text bg-white">
                                                        <i className="bi bi-search"></i>
                                                    </span>
                                                    <input
                                                        type="search"
                                                        className={`form-control ${errors.customer_id ? "is-invalid" : ""
                                                            }`}
                                                        value={customerSearch}
                                                        placeholder="Search name, mobile, code or GST..."
                                                        autoComplete="off"
                                                        onFocus={() => setCustomerSearchOpen(true)}
                                                        onChange={(event) => {
                                                            setCustomerSearch(event.target.value);
                                                            setCustomerSearchOpen(true);
                                                        }}
                                                    />
                                                    {customerSearching && (
                                                        <span className="input-group-text bg-white">
                                                            <span className="spinner-border spinner-border-sm text-primary"></span>
                                                        </span>
                                                    )}
                                                </div>
                                            )}

                                            {customerSearchOpen && !selectedCustomer && (
                                                <div
                                                    className="rr-sale-customer-results position-absolute start-0 end-0 mt-1 bg-white border rounded shadow-lg"
                                                    style={{
                                                        zIndex: 1080,
                                                    }}
                                                    role="listbox"
                                                    aria-label="Customer search results"
                                                >
                                                    <button
                                                        type="button"
                                                        className="rr-sale-customer-result add-new btn w-100 text-start"
                                                        onClick={() => {
                                                            setCustomerErrors({});
                                                            setCustomerSearchOpen(false);
                                                            setCustomerModal(true);
                                                        }}
                                                    >
                                                        <i className="bi bi-person-plus-fill me-2"></i>
                                                        Add New Customer
                                                    </button>

                                                    {customerSearch.trim().length === 1 ? (
                                                        <div className="px-3 py-3 text-muted small">
                                                            Type one more character to search.
                                                        </div>
                                                    ) : visibleCustomerOptions.length > 0 ? (
                                                        visibleCustomerOptions.map((customer) => (
                                                            <button
                                                                type="button"
                                                                key={customer.id}
                                                                className="rr-sale-customer-result btn w-100 text-start"
                                                                onClick={() => selectCustomer(customer)}
                                                                role="option"
                                                                aria-selected="false"
                                                            >
                                                                <span className="d-flex justify-content-between gap-3">
                                                                    <span className="fw-semibold text-dark">
                                                                        {customer.customer_name}
                                                                    </span>
                                                                    {customer.is_system_customer && (
                                                                        <span className="badge text-bg-secondary">
                                                                            Walk-in
                                                                        </span>
                                                                    )}
                                                                </span>
                                                                <small className="d-block text-muted mt-1">
                                                                    {[
                                                                        customer.customer_code,
                                                                        customer.mobile || customer.phone,
                                                                        customer.gst_number,
                                                                    ]
                                                                        .filter(Boolean)
                                                                        .join(" · ") || "No contact details"}
                                                                </small>
                                                            </button>
                                                        ))
                                                    ) : customerSearching ? (
                                                        <div className="px-3 py-3 text-muted small">
                                                            Searching customers...
                                                        </div>
                                                    ) : (
                                                        <div className="px-3 py-3 text-muted small">
                                                            {customerSearch.trim().length >= 2
                                                                ? "No customer found. Use Add New Customer."
                                                                : "Type at least 2 characters or choose a recent customer."}
                                                        </div>
                                                    )}
                                                </div>
                                            )}
                                        </div>

                                        <button
                                            type="button"
                                            className="rr-sale-customer-add-btn"
                                            title="Quick add customer"
                                            onClick={() => {
                                                setCustomerErrors({});
                                                setCustomerModal(true);
                                            }}
                                        >
                                            <i className="bi bi-person-plus-fill"></i>
                                            <span>Quick Add</span>
                                        </button>
                                    </div>
                                    <ErrorText message={errors.customer_id} />
                                </div>

                                <div className="rr-sale-field">
                                    <label>
                                        Invoice Date <Required />
                                    </label>
                                    <DateInput
                                        value={data.invoice_date}
                                        onChange={(value) => setData("invoice_date", value)}
                                    />
                                    <ErrorText message={errors.invoice_date} />
                                </div>

                                <div className="rr-sale-field rr-sale-bill-type-field">
                                    <label>
                                        Billing Type <Required />
                                    </label>
                                    <div
                                        className="rr-sale-bill-type"
                                        data-validation-key="billing_type"
                                    >
                                        <button
                                            type="button"
                                            className={data.billing_type === "gst" ? "active" : ""}
                                            onClick={() => setData("billing_type", "gst")}
                                        >
                                            <i className="bi bi-receipt"></i> GST Bill
                                        </button>
                                        <button
                                            type="button"
                                            className={
                                                data.billing_type === "non_gst" ? "active" : ""
                                            }
                                            onClick={() => setData("billing_type", "non_gst")}
                                        >
                                            <i className="bi bi-receipt-cutoff"></i> Without GST
                                        </button>
                                    </div>
                                    <small className="rr-sale-field-help">
                                        {data.billing_type === "gst"
                                            ? "GST rate and amount will appear on the customer invoice."
                                            : "GST is still calculated internally, but hidden on the customer invoice."}
                                    </small>
                                    <ErrorText message={errors.billing_type} />
                                </div>
                            </div>

                            <div className="rr-sale-source-section">
                                <span className="rr-sale-source-label">Sale Source</span>

                                <div className="rr-sale-source-buttons">
                                    <button
                                        type="button"
                                        className={`rr-sale-source-btn ${data.sale_source === "customer" ? "active" : ""}`}
                                        onClick={() => changeSaleSource("customer")}
                                    >
                                        <i className="bi bi-person-check"></i>
                                        <div>
                                            <strong>Customer Only</strong>
                                            <span>No referral commission</span>
                                        </div>
                                    </button>

                                    <button
                                        type="button"
                                        className={`rr-sale-source-btn mechanic ${data.sale_source === "mechanic" ? "active" : ""}`}
                                        onClick={() => changeSaleSource("mechanic")}
                                    >
                                        <i className="bi bi-tools"></i>
                                        <div>
                                            <strong>Mechanic Referral</strong>
                                            <span>Credit commission to mechanic</span>
                                        </div>
                                    </button>
                                </div>
                            </div>

                            {data.sale_source === "mechanic" && (
                                <div className="rr-sale-mechanic-panel">
                                    <div className="rr-sale-field rr-sale-mechanic-select">
                                        <label>
                                            Mechanic <Required />
                                        </label>
                                        <div className="rr-sale-select-action">
                                            <select
                                                data-validation-key="mechanic_id"
                                                value={data.mechanic_id}
                                                onChange={(event) =>
                                                    setData("mechanic_id", event.target.value)
                                                }
                                            >
                                                <option value="">Select mechanic</option>
                                                {mechanicOptions.map((mechanic) => (
                                                    <option
                                                        key={mechanic.id}
                                                        value={mechanic.id}
                                                    >
                                                        {mechanic.mechanic_name}
                                                        {mechanic.mobile ? ` - ${mechanic.mobile}` : ""}
                                                    </option>
                                                ))}
                                            </select>

                                            <button
                                                type="button"
                                                className="rr-sale-quick-btn mechanic"
                                                onClick={() => {
                                                    setMechanicErrors({});
                                                    setMechanicModal(true);
                                                }}
                                            >
                                                <i className="bi bi-tools"></i>
                                                Quick Add
                                            </button>
                                        </div>
                                        <ErrorText message={errors.mechanic_id} />
                                    </div>

                                    <div className="rr-sale-commission-box">
                                        <div className="rr-sale-commission-top">
                                            <label>Commission</label>
                                            <div className="rr-sale-commission-types">
                                                <button
                                                    type="button"
                                                    className={
                                                        data.commission_type === "percentage"
                                                            ? "active"
                                                            : ""
                                                    }
                                                    onClick={() =>
                                                        setData("commission_type", "percentage")
                                                    }
                                                >
                                                    Percentage
                                                </button>
                                                <button
                                                    type="button"
                                                    className={
                                                        data.commission_type === "fixed" ? "active" : ""
                                                    }
                                                    onClick={() => setData("commission_type", "fixed")}
                                                >
                                                    Fixed
                                                </button>
                                            </div>
                                        </div>

                                        {data.commission_type === "percentage" ? (
                                            <div className="rr-sale-commission-percentage">
                                                <div className="rr-sale-percent-presets">
                                                    {[10, 15].map((percent) => (
                                                        <button
                                                            key={percent}
                                                            type="button"
                                                            className={
                                                                Number(data.commission_percent) === percent
                                                                    ? "active"
                                                                    : ""
                                                            }
                                                            onClick={() =>
                                                                setData("commission_percent", String(percent))
                                                            }
                                                        >
                                                            {percent}%
                                                        </button>
                                                    ))}
                                                    <span>Custom</span>
                                                    <input
                                                        type="number"
                                                        min="0"
                                                        max="100"
                                                        step="0.01"
                                                        data-validation-key="commission_percent"
                                                        value={data.commission_percent}
                                                        onChange={(event) =>
                                                            setData("commission_percent", event.target.value)
                                                        }
                                                    />
                                                </div>
                                                <ErrorText message={errors.commission_percent} />
                                            </div>
                                        ) : (
                                            <div className="rr-sale-fixed-commission">
                                                <span>₹</span>
                                                <input
                                                    type="number"
                                                    min="0"
                                                    step="1"
                                                    data-validation-key="commission_fixed_amount"
                                                    value={data.commission_fixed_amount}
                                                    onChange={(event) =>
                                                        setData(
                                                            "commission_fixed_amount",
                                                            event.target.value,
                                                        )
                                                    }
                                                    placeholder="Fixed commission"
                                                />
                                                <ErrorText message={errors.commission_fixed_amount} />
                                            </div>
                                        )}

                                        <div className="rr-sale-commission-preview">
                                            <span>Commission Base</span>
                                            <strong>{formatMoney(totals.commissionBase)}</strong>
                                            <span>Mechanic Credit</span>
                                            <strong className="amount">
                                                {formatMoney(totals.commissionAmount)}
                                            </strong>
                                        </div>
                                    </div>
                                </div>
                            )}
                        </div>
                    </section>

                    {/* =========================================================
                        PRODUCT SEARCH
                       ========================================================= */}
                    <section className="rr-sale-window">
                        <div className="rr-sale-window-title">
                            <div>
                                <i className="bi bi-table"></i>
                                <strong>Invoice Items</strong>
                            </div>
                            <span>{rows.length} item(s)</span>
                        </div>

                        <div className="rr-sale-price-note">
                            Selling prices already include GST. {data.billing_type === 'gst'
                                ? 'The rate excluding GST is shown separately; GST is extracted, never added on top. Default GST: 18%.'
                                : 'Without GST: the selling rate is used directly. No GST is added.'}
                        </div>
                        <div className="rr-sale-counter-toolbar">
                            <label className="rr-sale-quick-quantity">
                                <input type="checkbox" checked={quickQuantity}
                                    onChange={(event) => setQuickQuantity(event.target.checked)} />
                                Qty 1 mode
                                <small>Return directly to product search</small>
                            </label>
                            <span><kbd>↑ ↓</kbd> Choose <kbd>Enter</kbd> Add / Next
                                <kbd>Ctrl + Enter</kbd> Next product <kbd>F2</kbd> Search</span>
                        </div>
                        <details className="rr-sale-shortcut-notes">
                            <summary><i className="bi bi-keyboard" aria-hidden="true" /> Billing shortcuts</summary>
                            <div className="rr-sale-shortcut-grid">
                                <span><kbd>↑ / ↓</kbd> Choose a search result</span>
                                <span><kbd>Enter</kbd> Add product / Next field</span>
                                <span><kbd>Esc</kbd> Next item field / Clear search</span>
                                <span><kbd>Shift + Enter</kbd> Previous item field</span>
                                <span><kbd>→ / ←</kbd> Next / Previous item field</span>
                                <span><kbd>Ctrl + Enter</kbd> Next product search</span>
                                <span><kbd>F2</kbd> Focus product search</span>
                                <span><kbd>F9</kbd> Update Sale</span>
                                <span><kbd>F10</kbd> Update &amp; Print</span>
                            </div>
                            <p>Item entry: Qty → Free → Rate → Discount → GST → next product.
                                GST is skipped for non-GST bills. Left Arrow from Qty moves to the previous row.
                                F9/F10 focus the item grid before completing the sale. Enable <strong>Qty 1 mode</strong> to add single-unit items continuously.
                                In search, <kbd>Ctrl + Enter</kbd> edits the last item's quantity.
                                Esc keeps the current field value. F9/F10 work across this page when dialogs are closed.</p>
                        </details>
                        <ErrorText message={errors.product_id || errors.items} />
                        <div className="rr-sale-items-wrap">
                            <table className="rr-sale-items-table">
                                <thead>
                                    <tr>
                                        <th className="serial">#</th>
                                        <th>Product</th>
                                        <th className="number">Shop Stock</th>
                                        <th className="number">Qty</th>
                                        <th className="number">Free</th>
                                        <th className="number">Selling Rate<br /><small>GST inclusive</small></th>
                                        {data.billing_type === 'gst' && (
                                            <th className="number">Rate excl. GST</th>
                                        )}
                                        <th className="number rr-sale-discount-heading">
                                            Discount
                                        </th>
                                        {data.billing_type === "gst" && (
                                            <th className="number">GST %</th>
                                        )}
                                        <th className="number">Amount</th>
                                        <th className="center">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    {rows.map((row, index) => {
                                        const calc = calculateRow(row, data.billing_type);
                                        const required =
                                            numberValue(row.qty) + numberValue(row.free_quantity);
                                        const short = Math.max(
                                            0,
                                            required - numberValue(row.shop_stock),
                                        );

                                        return (
                                            <tr key={row.key}>
                                                <td className="serial">{index + 1}</td>
                                                <td>
                                                    <div className="rr-sale-product-cell">
                                                        <div className="rr-sale-product-icon">
                                                            <i className="bi bi-box-seam"></i>
                                                        </div>
                                                        <div>
                                                            <strong>
                                                                {row.product?.product_name ?? "Product"}
                                                            </strong>
                                                            <span>
                                                                {row.product?.product_code ?? ""}
                                                                {row.product?.part_number
                                                                    ? ` • ${row.product.part_number}`
                                                                    : ""}
                                                                {row.product?.unit?.short_name
                                                                    ? ` • ${row.product.unit.short_name}`
                                                                    : ""}
                                                            </span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td className="number">
                                                    <button
                                                        type="button"
                                                        className={`rr-sale-stock-pill ${required <= numberValue(row.shop_stock) ? "enough" : row.stock_state === "insufficient" ? "danger" : "transfer"}`}
                                                        title="Check stock"
                                                        onClick={() => checkRowStock(index)}
                                                    >
                                                        <i
                                                            className={`bi ${required <= numberValue(row.shop_stock) ? "bi-check-circle" : "bi-box-arrow-in-down"}`}
                                                        ></i>
                                                        {formatQty(row.shop_stock)}
                                                        {short > 0 && (
                                                            <small>Short {formatQty(short)}</small>
                                                        )}
                                                    </button>
                                                </td>
                                                <td className="number">
                                                    <GridInput
                                                        index={index}
                                                        field="qty"
                                                        value={row.qty}
                                                        min="1"
                                                        step="1"
                                                        error={errors[`qty.${index}`]}
                                                        onChange={(value) =>
                                                            updateRow(index, "qty", value)
                                                        }
                                                        onBlur={() => checkRowStock(index)}
                                                    />
                                                </td>
                                                <td className="number">
                                                    <GridInput
                                                        index={index}
                                                        field="free_quantity"
                                                        value={row.free_quantity}
                                                        min="0"
                                                        step="1"
                                                        error={errors[`free_quantity.${index}`]}
                                                        onChange={(value) =>
                                                            updateRow(index, "free_quantity", value)
                                                        }
                                                        onBlur={() => checkRowStock(index)}
                                                    />
                                                </td>
                                                <td className="number">
                                                    <GridInput
                                                        index={index}
                                                        field="sale_price"
                                                        value={row.sale_price}
                                                        min="0"
                                                        step="1"
                                                        error={errors[`sale_price.${index}`]}
                                                        onChange={(value) =>
                                                            updateRow(index, "sale_price", value)
                                                        }
                                                    />
                                                </td>
                                                {data.billing_type === 'gst' && (
                                                    <td className="number rr-sale-exclusive-rate" title="Selling rate divided by 1 + GST percentage">
                                                        {formatMoney(calc.exclusiveRate)}
                                                    </td>
                                                )}
                                                <td className="number">
                                                    <DiscountInput
                                                        index={index}
                                                        type={row.item_discount_type}
                                                        value={row.item_discount}
                                                        gross={calc.gross}
                                                        error={errors[`item_discount.${index}`]}
                                                        onTypeChange={(value) =>
                                                            updateRow(index, "item_discount_type", value)
                                                        }
                                                        onChange={(value) =>
                                                            updateRow(index, "item_discount", value)
                                                        }
                                                    />
                                                </td>
                                                {data.billing_type === "gst" && (
                                                    <td className="number">
                                                        <GridInput
                                                            index={index}
                                                            field="gst"
                                                            value={row.gst}
                                                            min="0"
                                                            max="100"
                                                            step="1"
                                                            onBlur={() => {
                                                                if (numberValue(row.gst) <= 0) updateRow(index, 'gst', '18');
                                                            }}
                                                            error={errors[`gst.${index}`]}
                                                            onChange={(value) =>
                                                                updateRow(index, "gst", value)
                                                            }
                                                        />
                                                    </td>
                                                )}
                                                <td className="number rr-sale-line-total">
                                                    {formatMoney(calc.total)}
                                                </td>
                                                <td className="center">
                                                    <button
                                                        type="button"
                                                        className="rr-sale-row-delete"
                                                        title="Remove product"
                                                        onClick={() => removeRow(index)}
                                                    >
                                                        <i className="bi bi-trash3"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        );
                                    })}
                                    <tr className="rr-sale-draft-row">
                                        <td className="serial">{rows.length + 1}</td>
                                        <td className="rr-sale-draft-product">
                                            <div
                                                ref={productSearchBoxRef}
                                                className="rr-sale-product-search"
                                            >
                                                <div
                                                    className={`rr-sale-product-search-input ${productSearchOpen ? "open" : ""}`}
                                                >
                                                    <i className="bi bi-search"></i>
                                                    <input
                                                        ref={searchInputRef}
                                                        type="search"
                                                        value={productSearch}
                                                        role="combobox"
                                                        aria-label="Search product for next invoice item"
                                                        aria-expanded={productSearchOpen}
                                                        aria-controls={productSearchOpen ? 'sale-product-listbox' : undefined}
                                                        aria-activedescendant={productSearchOpen && productResults.length ? `sale-product-option-${selectedResult}` : undefined}
                                                        onKeyDown={handleProductSearchKey}
                                                        placeholder="Search product name, code, barcode, part number or OEM..."
                                                        autoComplete="off"
                                                        onFocus={() => {
                                                            if (productSearch.trim()) setProductSearchOpen(true);
                                                        }}
                                                        onChange={(event) => changeProductSearch(event.target.value)}
                                                    />

                                                    {productSearching ? (
                                                        <span className="spinner-border spinner-border-sm"></span>
                                                    ) : productSearch ? (
                                                        <button
                                                            type="button"
                                                            onClick={() => {
                                                                changeProductSearch("");
                                                                setProductSearchOpen(false);
                                                            }}
                                                        >
                                                            <i className="bi bi-x-lg"></i>
                                                        </button>
                                                    ) : null}
                                                </div>

                                                {productSearchOpen && createPortal(
                                                    <div
                                                        className="rr-sale-search-results rr-sale-counter-results"
                                                        style={{
                                                            '--counter-top': `${searchPopup.top}px`,
                                                            '--counter-left': `${searchPopup.left}px`,
                                                            '--counter-width': `${searchPopup.width}px`,
                                                            '--counter-height': `${searchPopup.maxHeight}px`,
                                                        }}
                                                        id="sale-product-listbox"
                                                        role="listbox"
                                                        aria-label="Product search results"
                                                    >
                                                        <div className="rr-sale-search-results-header">
                                                            <strong>Product Search</strong>
                                                            <div>
                                                                <span>{productResults.length} result(s)</span>
                                                                <button
                                                                    type="button"
                                                                    className="rr-sale-search-clear"
                                                                    onClick={() => {
                                                                        changeProductSearch("");
                                                                        setProductResults([]);
                                                                        setProductSearchOpen(false);
                                                                        searchInputRef.current?.focus();
                                                                    }}
                                                                >
                                                                    <i className="bi bi-x-circle"></i> Clear
                                                                </button>
                                                            </div>
                                                        </div>

                                                        <div className="rr-sale-search-results-list">
                                                            {productSearching ? (
                                                                <div className="rr-sale-search-empty" role="status">
                                                                    <span className="spinner-border spinner-border-sm" aria-hidden="true" />
                                                                    <strong>Searching products…</strong>
                                                                </div>
                                                            ) : productResults.length ? (
                                                                productResults.map((product, resultIndex) => (
                                                                    <button
                                                                        type="button"
                                                                        key={product.id}
                                                                        className={`rr-sale-search-result ${selectedResult === resultIndex ? "keyboard-selected" : ""}`}
                                                                        id={`sale-product-option-${resultIndex}`}
                                                                        tabIndex={-1}
                                                                        onMouseDown={(event) => event.preventDefault()}
                                                                        onMouseEnter={() => setSelectedResult(resultIndex)}
                                                                        onClick={() => addProduct(product)}
                                                                        role="option"
                                                                        aria-selected={selectedResult === resultIndex}
                                                                    >
                                                                        <div className="rr-sale-search-result-icon">
                                                                            <i className="bi bi-box-seam"></i>
                                                                        </div>
                                                                        <div className="rr-sale-search-result-name">
                                                                            <strong>{product.product_name}</strong>
                                                                            <span>
                                                                                {product.product_code ?? ""}
                                                                                {product.part_number
                                                                                    ? ` • ${product.part_number}`
                                                                                    : ""}
                                                                                {product.brand?.brand_name
                                                                                    ? ` • ${product.brand.brand_name}`
                                                                                    : ""}
                                                                            </span>
                                                                        </div>
                                                                        <div className="rr-sale-search-result-stock">
                                                                            <span>Shop Stock</span>
                                                                            <strong
                                                                                className={
                                                                                    numberValue(product.shop_stock) > 0
                                                                                        ? "ok"
                                                                                        : "low"
                                                                                }
                                                                            >
                                                                                {formatQty(product.shop_stock)}
                                                                            </strong>
                                                                        </div>
                                                                        <div className="rr-sale-search-result-price">
                                                                            <span>Sale Rate</span>
                                                                            <strong>
                                                                                {formatMoney(
                                                                                    firstNumber(
                                                                                        product.sale_price,
                                                                                        product.selling_price,
                                                                                        product.price,
                                                                                        product.mrp,
                                                                                        0,
                                                                                    ),
                                                                                )}
                                                                            </strong>
                                                                        </div>
                                                                        <i className="bi bi-plus-circle"></i>
                                                                    </button>
                                                                ))
                                                            ) : !productSearching ? (
                                                                <div className="rr-sale-search-empty">
                                                                    <i className="bi bi-search"></i>
                                                                    <strong>{productSearchError || "No products found"}</strong>
                                                                    <span>
                                                                        Try another product name, code, barcode or part
                                                                        number.
                                                                    </span>
                                                                </div>
                                                            ) : null}
                                                        </div>
                                                    </div>,
                                                    document.body
                                                )}
                                            </div>

                                        </td>
                                        <td className="number">—</td>
                                        <td className="number"><span className="rr-sale-draft-value">1</span></td>
                                        <td className="number">0</td>
                                        <td className="number">—</td>
                                        {data.billing_type === 'gst' && <td className="number">—</td>}
                                        <td className="number">—</td>
                                        {data.billing_type === 'gst' && <td className="number">—</td>}
                                        <td className="number">—</td>
                                        <td className="center"><i className="bi bi-plus-circle" aria-hidden="true" /></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </section>

                    {/* =========================================================
                        PAYMENT + TOTALS
                       ========================================================= */}
                    <div className="rr-sale-bottom-grid">
                        <section className="rr-sale-window">
                            <WindowTitle
                                icon="bi-wallet2"
                                title="Payment & Remarks"
                            />
                            <div className="rr-sale-window-body">
                                <div className="rr-sale-payment-grid">
                                    <div className="rr-sale-field">
                                        <label>Invoice Discount</label>
                                        <MoneyInput
                                            validationKey="final_discount"
                                            value={data.final_discount}
                                            onChange={(value) => setData("final_discount", value)}
                                        />
                                        <ErrorText message={errors.final_discount} />
                                    </div>

                                    <div className="rr-sale-payment-status-box">
                                        <span>Payment Status</span>
                                        <strong
                                            className={paymentStatusClass(totals.paymentStatus)}
                                        >
                                            {totals.paymentStatus}
                                        </strong>
                                    </div>
                                </div>

                                <SplitPaymentPanel
                                    accounts={accounts}
                                    payments={data.payments}
                                    grandTotal={totals.grandTotal}
                                    paid={totals.paid}
                                    balance={totals.balance}
                                    errors={errors}
                                    legacyPaidAmount={
                                        data.payments.length === 0
                                            ? numberValue(data.paid_amount)
                                            : 0
                                    }
                                    onChange={(payments) =>
                                        setData((previous) => ({
                                            ...previous,
                                            payments,
                                            paid_amount: String(
                                                payments.length > 0
                                                    ? round2(
                                                        payments.reduce(
                                                            (sum, payment) =>
                                                                sum + numberValue(payment.amount),
                                                            0,
                                                        ),
                                                    )
                                                    : 0,
                                            ),
                                        }))
                                    }
                                />

                                <ErrorText message={errors.paid_amount} />

                                <div className="rr-sale-field rr-sale-remarks-field">
                                    <label>Remarks</label>
                                    <textarea
                                        data-validation-key="remarks"
                                        rows="4"
                                        value={data.remarks}
                                        onChange={(event) => setData("remarks", event.target.value)}
                                        placeholder="Optional invoice remarks..."
                                    ></textarea>
                                    <ErrorText message={errors.remarks} />
                                </div>
                            </div>
                        </section>

                        <section className="rr-sale-total-panel">
                            <div className="rr-sale-total-title">
                                <i className="bi bi-calculator"></i>
                                Invoice Summary
                            </div>

                            <div className="rr-sale-total-body">
                                <TotalRow
                                    label={
                                        data.billing_type === "gst"
                                            ? "Taxable Subtotal"
                                            : "Items Subtotal"
                                    }
                                    value={totals.taxableSubtotal}
                                />
                                {data.billing_type === "gst" && (
                                    <TotalRow
                                        label="GST"
                                        value={totals.gstTotal}
                                    />
                                )}
                                <TotalRow
                                    label="Gross Total"
                                    value={totals.grossInvoice}
                                    strong
                                />
                                <TotalRow
                                    label="Final Discount"
                                    value={-totals.finalDiscount}
                                    muted
                                />
                                <TotalRow
                                    label="Round Off"
                                    value={totals.roundOff}
                                    signed
                                    tone={totals.roundOff >= 0 ? "positive" : "negative"}
                                />

                                <div className="rr-sale-net-total">
                                    <span>NET AMOUNT</span>
                                    <strong>{formatMoney(totals.grandTotal)}</strong>
                                </div>

                                <TotalRow
                                    label="Paid"
                                    value={totals.paid}
                                    tone="paid"
                                />

                                <div className="rr-sale-balance-total">
                                    <span>Balance</span>
                                    <strong>{formatMoney(totals.balance)}</strong>
                                </div>

                                {data.sale_source === "mechanic" && (
                                    <div className="rr-sale-mechanic-credit-total">
                                        <span>
                                            <i className="bi bi-tools"></i> Mechanic Commission
                                        </span>
                                        <strong>{formatMoney(totals.commissionAmount)}</strong>
                                    </div>
                                )}
                            </div>
                        </section>
                    </div>

                    {(submitError || errors.print_after_save) && (
                        <div className="rr-sale-submit-error" role="alert">
                            {submitError || errors.print_after_save}
                        </div>
                    )}
                    <div className="rr-sale-command-bar">
                        <div>
                            {hasStockProblem ? (
                                <span className="rr-sale-command-warning">
                                    <i className="bi bi-exclamation-triangle"></i>
                                    Resolve Shop stock shortages before completing the sale.
                                </span>
                            ) : (
                                <span>
                                    <i className="bi bi-check-circle"></i>
                                    Stock will be deducted from Shop when the sale is completed.
                                </span>
                            )}
                        </div>

                        <div>
                            <Link
                                href="/sales"
                                className="rr-sale-btn secondary"
                            >
                                Cancel
                            </Link>
                            <button
                                type="submit"
                                name="sale_action"
                                value="save"
                                className="rr-sale-btn primary"
                                disabled={processing || submitting || rows.length === 0 || hasStockProblem}
                            >
                                {processing || submitting ? (
                                    <>
                                        <span className="spinner-border spinner-border-sm"></span>{" "}
                                        Saving...
                                    </>
                                ) : (
                                    <>
                                        <i className="bi bi-receipt"></i> Update Sale
                                    </>
                                )}
                            </button>
                            <button
                                type="submit"
                                name="sale_action"
                                value="print"
                                className="rr-sale-btn print"
                                disabled={processing || submitting || rows.length === 0 || hasStockProblem}
                                title="Save invoice and print (F10)"
                            >
                                {processing || submitting ? <><span className="spinner-border spinner-border-sm" aria-hidden="true" /> Preparing…</>
                                    : <><i className="bi bi-printer" aria-hidden="true" /> Update &amp; Print</>}
                            </button>
                        </div>
                    </div>
                </form>

                {/* =============================================================
                    STOCK ASSIST MODAL
                   ============================================================= */}
                {stockAssist.show && (
                    <StockAssistModal
                        state={stockAssist}
                        onClose={() => {
                            if (!stockAssist.transferring) {
                                setStockAssist((previous) => ({
                                    ...previous,
                                    show: false,
                                }));
                            }
                        }}
                        onGodownChange={changeStockAssistGodown}
                        onTransferQtyChange={(value) =>
                            setStockAssist((previous) => ({
                                ...previous,
                                transferQty: value,
                                error: "",
                            }))
                        }
                        onTransfer={transferToShop}
                    />
                )}

                {/* =============================================================
                    QUICK CUSTOMER MODAL
                   ============================================================= */}
                {customerModal && (
                    <QuickModal
                        title="Quick Add Customer"
                        icon="bi-person-plus"
                        saving={customerSaving}
                        onClose={() => !customerSaving && setCustomerModal(false)}
                        onSubmit={saveQuickCustomer}
                    >
                        <ModalField
                            label="Customer Name"
                            required
                            value={quickCustomer.customer_name}
                            error={customerErrors.customer_name}
                            autoFocus
                            onChange={(value) =>
                                setQuickCustomer((previous) => ({
                                    ...previous,
                                    customer_name: value,
                                }))
                            }
                        />
                        <ModalField
                            label="Mobile"
                            value={quickCustomer.mobile}
                            error={customerErrors.mobile}
                            onChange={(value) =>
                                setQuickCustomer((previous) => ({
                                    ...previous,
                                    mobile: value,
                                }))
                            }
                        />
                        <ModalField
                            label="Email"
                            type="email"
                            value={quickCustomer.email}
                            error={customerErrors.email}
                            onChange={(value) =>
                                setQuickCustomer((previous) => ({
                                    ...previous,
                                    email: value,
                                }))
                            }
                        />
                        <ModalField
                            label="GST Number"
                            value={quickCustomer.gst_number}
                            error={customerErrors.gst_number}
                            onChange={(value) =>
                                setQuickCustomer((previous) => ({
                                    ...previous,
                                    gst_number: value,
                                }))
                            }
                        />
                        <ModalArea
                            label="Address"
                            value={quickCustomer.address}
                            error={customerErrors.address}
                            onChange={(value) =>
                                setQuickCustomer((previous) => ({
                                    ...previous,
                                    address: value,
                                }))
                            }
                        />
                        <ErrorText message={customerErrors.general} />
                    </QuickModal>
                )}

                {/* =============================================================
                    QUICK MECHANIC MODAL
                   ============================================================= */}
                {mechanicModal && (
                    <QuickModal
                        title="Quick Add Mechanic"
                        icon="bi-tools"
                        saving={mechanicSaving}
                        onClose={() => !mechanicSaving && setMechanicModal(false)}
                        onSubmit={saveQuickMechanic}
                    >
                        <ModalField
                            label="Mechanic Name"
                            required
                            value={quickMechanic.mechanic_name}
                            error={mechanicErrors.mechanic_name}
                            autoFocus
                            onChange={(value) =>
                                setQuickMechanic((previous) => ({
                                    ...previous,
                                    mechanic_name: value,
                                }))
                            }
                        />
                        <ModalField
                            label="Mobile"
                            value={quickMechanic.mobile}
                            error={mechanicErrors.mobile}
                            onChange={(value) =>
                                setQuickMechanic((previous) => ({
                                    ...previous,
                                    mobile: value,
                                }))
                            }
                        />
                        <ModalField
                            label="Email"
                            type="email"
                            value={quickMechanic.email}
                            error={mechanicErrors.email}
                            onChange={(value) =>
                                setQuickMechanic((previous) => ({
                                    ...previous,
                                    email: value,
                                }))
                            }
                        />
                        <ModalField
                            label="City"
                            value={quickMechanic.city}
                            error={mechanicErrors.city}
                            onChange={(value) =>
                                setQuickMechanic((previous) => ({
                                    ...previous,
                                    city: value,
                                }))
                            }
                        />
                        <ModalArea
                            label="Address"
                            value={quickMechanic.address}
                            error={mechanicErrors.address}
                            onChange={(value) =>
                                setQuickMechanic((previous) => ({
                                    ...previous,
                                    address: value,
                                }))
                            }
                        />
                        <ErrorText message={mechanicErrors.general} />
                    </QuickModal>
                )}
            </div>
        </>
    );
}

/* ==========================================================================
   Components
   ========================================================================== */

function WindowTitle({ icon, title }) {
    return (
        <div className="rr-sale-window-title">
            <div>
                <i className={`bi ${icon}`}></i>
                <strong>{title}</strong>
            </div>
        </div>
    );
}

function Required() {
    return <span className="rr-sale-required">*</span>;
}

function ErrorText({ message }) {
    if (!message) return null;
    return (
        <div className="rr-sale-error">
            <i className="bi bi-exclamation-circle"></i>
            {message}
        </div>
    );
}

function DateInput({ value, onChange }) {
    const ref = useRef(null);

    const open = () => {
        if (!ref.current) return;
        try {
            ref.current.showPicker?.();
        } catch {
            ref.current.focus();
        }
    };

    return (
        <div className="rr-sale-date-input">
            <button
                type="button"
                tabIndex="-1"
                onClick={open}
            >
                <i className="bi bi-calendar3"></i>
            </button>
            <input
                ref={ref}
                type="date"
                data-validation-key="invoice_date"
                value={value}
                onClick={open}
                onChange={(event) => onChange(event.target.value)}
            />
        </div>
    );
}

function GridInput({
    index,
    field,
    value,
    onChange,
    onBlur,
    error,
    min,
    max,
    step = "1",
}) {
    return (
        <>
            <input
                type="number"
                className={`rr-sale-grid-input ${error ? "is-invalid" : ""}`}
                data-sale-row={index}
                data-sale-field={field}
                min={min}
                max={max}
                step={step}
                value={value}
                onFocus={(event) => event.target.select()}
                onChange={(event) => onChange(event.target.value)}
                onBlur={onBlur}
            />
            {error && <span className="rr-sale-grid-error">{error}</span>}
        </>
    );
}

function MoneyInput({ value, onChange, validationKey }) {
    return (
        <div className="rr-sale-money-input">
            <span>₹</span>
            <input
                type="number"
                min="0"
                step="1"
                data-validation-key={validationKey}
                value={value}
                onFocus={(event) => event.target.select()}
                onChange={(event) => onChange(event.target.value)}
            />
        </div>
    );
}

function DiscountInput({
    index,
    type,
    value,
    gross,
    error,
    onTypeChange,
    onChange,
}) {
    const isPercentage = type === "percentage";
    const discountAmount = isPercentage
        ? round2((numberValue(gross) * Math.min(numberValue(value), 100)) / 100)
        : Math.min(numberValue(value), numberValue(gross));

    return (
        <div className={`rr-sale-discount-input ${error ? "is-invalid" : ""}`}>
            <div
                className="rr-sale-discount-type"
                aria-label="Discount type"
            >
                <button
                    type="button"
                    className={!isPercentage ? "active" : ""}
                    title="Fixed amount"
                    onClick={() => onTypeChange("fixed")}
                >
                    ₹
                </button>
                <button
                    type="button"
                    className={isPercentage ? "active" : ""}
                    title="Percentage"
                    onClick={() => onTypeChange("percentage")}
                >
                    %
                </button>
            </div>
            <input
                type="number"
                min="0"
                max={isPercentage ? 100 : gross}
                step="0.01"
                data-sale-row={index}
                data-sale-field="item_discount"
                value={value}
                onFocus={(event) => event.target.select()}
                onChange={(event) => onChange(event.target.value)}
            />
            {numberValue(value) > 0 && (
                <small>
                    {isPercentage ? `${formatMoney(discountAmount)} off` : "Fixed off"}
                </small>
            )}
            {error && <span className="rr-sale-grid-error">{error}</span>}
        </div>
    );
}

function TotalRow({
    label,
    value,
    strong = false,
    muted = false,
    signed = false,
    tone = "",
}) {
    const shown = signed
        ? `${numberValue(value) >= 0 ? "+" : "-"}${formatMoney(Math.abs(numberValue(value)))}`
        : formatMoney(value);

    return (
        <div
            className={`rr-sale-total-row ${strong ? "strong" : ""} ${muted ? "muted" : ""} ${tone}`}
        >
            <span>{label}</span>
            <strong>{shown}</strong>
        </div>
    );
}

function StockAssistModal({
    state,
    onClose,
    onGodownChange,
    onTransferQtyChange,
    onTransfer,
}) {
    const data = state.data;

    const selected = data?.godowns?.find(
        (godown) => Number(godown.id) === Number(state.selectedGodownId),
    );

    const transferQty = numberValue(state.transferQty);
    const shortage = numberValue(data?.shortage_qty);
    const remainingShortage = Math.max(0, round2(shortage - transferQty));

    return (
        <div className="rr-sale-modal-layer">
            <div
                className="rr-sale-modal-backdrop"
                onClick={onClose}
            ></div>
            <div
                className="rr-sale-stock-modal"
                role="dialog"
                aria-modal="true"
            >
                <div className="rr-sale-modal-header stock">
                    <div className="rr-sale-modal-icon">
                        <i className="bi bi-box-arrow-in-down"></i>
                    </div>
                    <div>
                        <strong>Stock Assist</strong>
                        <span>
                            Transfer stock from Godown to Shop without leaving the sale.
                        </span>
                    </div>
                    <button
                        type="button"
                        onClick={onClose}
                        disabled={state.transferring}
                    >
                        <i className="bi bi-x-lg"></i>
                    </button>
                </div>

                {state.loading ? (
                    <div className="rr-sale-modal-loading">
                        <span className="spinner-border"></span>
                        Checking Shop and Godown stock...
                    </div>
                ) : data ? (
                    <>
                        <div className="rr-sale-stock-product">
                            <div>
                                <span>Product</span>
                                <strong>{data.product?.product_name}</strong>
                                <small>{data.product?.product_code}</small>
                            </div>
                            <div className="rr-sale-stock-required">
                                <span>Required</span>
                                <strong>{formatQty(data.required_qty)}</strong>
                            </div>
                        </div>

                        <div className="rr-sale-stock-summary">
                            <StockMetric
                                label="Shop Stock"
                                value={data.shop_stock}
                                tone="shop"
                            />
                            <StockMetric
                                label="Shortage"
                                value={data.shortage_qty}
                                tone="short"
                            />
                            <StockMetric
                                label="Godown Stock"
                                value={data.godown_stock}
                                tone="godown"
                            />
                            <StockMetric
                                label="Total Available"
                                value={data.total_stock}
                                tone={data.total_has_enough ? "ok" : "danger"}
                            />
                        </div>

                        {!data.total_has_enough && (
                            <div className="rr-sale-stock-danger-note">
                                <i className="bi bi-exclamation-octagon"></i>
                                Total available stock is not enough for this quantity.
                            </div>
                        )}

                        <div className="rr-sale-transfer-section">
                            <div className="rr-sale-field">
                                <label>Transfer From Godown</label>
                                <select
                                    value={state.selectedGodownId}
                                    onChange={(event) => onGodownChange(event.target.value)}
                                >
                                    <option value="">Select Godown</option>
                                    {(data.godowns ?? []).map((godown) => (
                                        <option
                                            key={godown.id}
                                            value={godown.id}
                                            disabled={numberValue(godown.available_stock) <= 0}
                                        >
                                            {godown.godown_name} — {formatQty(godown.available_stock)}{" "}
                                            available
                                        </option>
                                    ))}
                                </select>
                            </div>

                            <div className="rr-sale-field">
                                <label>Transfer Quantity</label>
                                <div className="rr-sale-transfer-qty-control">
                                    <button
                                        type="button"
                                        onClick={() =>
                                            onTransferQtyChange(String(Math.max(1, transferQty - 1)))
                                        }
                                    >
                                        −
                                    </button>
                                    <input
                                        type="number"
                                        min="1"
                                        max={selected?.available_stock ?? undefined}
                                        step="1"
                                        value={state.transferQty}
                                        onFocus={(event) => event.target.select()}
                                        onChange={(event) =>
                                            onTransferQtyChange(event.target.value)
                                        }
                                    />
                                    <button
                                        type="button"
                                        onClick={() =>
                                            onTransferQtyChange(
                                                String(
                                                    Math.min(
                                                        numberValue(selected?.available_stock),
                                                        transferQty + 1,
                                                    ),
                                                ),
                                            )
                                        }
                                    >
                                        +
                                    </button>
                                </div>
                                {selected && (
                                    <small className="rr-sale-transfer-help">
                                        Available: {formatQty(selected.available_stock)}
                                    </small>
                                )}
                            </div>
                        </div>

                        <div
                            className={`rr-sale-transfer-result ${remainingShortage > 0 ? "warning" : "ok"}`}
                        >
                            <span>After this transfer</span>
                            <strong>
                                {remainingShortage > 0
                                    ? `Still short ${formatQty(remainingShortage)}`
                                    : "Shop will have enough stock"}
                            </strong>
                        </div>

                        {state.error && (
                            <div className="rr-sale-modal-error">
                                <i className="bi bi-exclamation-circle"></i>
                                {state.error}
                            </div>
                        )}

                        <div className="rr-sale-modal-footer">
                            <button
                                type="button"
                                className="rr-sale-btn secondary"
                                onClick={onClose}
                                disabled={state.transferring}
                            >
                                Cancel
                            </button>
                            <button
                                type="button"
                                className="rr-sale-btn transfer"
                                disabled={
                                    state.transferring ||
                                    !state.selectedGodownId ||
                                    transferQty <= 0 ||
                                    transferQty > numberValue(selected?.available_stock)
                                }
                                onClick={onTransfer}
                            >
                                {state.transferring ? (
                                    <>
                                        <span className="spinner-border spinner-border-sm"></span>{" "}
                                        Transferring...
                                    </>
                                ) : (
                                    <>
                                        <i className="bi bi-arrow-down-up"></i> Transfer to Shop
                                    </>
                                )}
                            </button>
                        </div>
                    </>
                ) : (
                    <div className="rr-sale-modal-error">
                        {state.error || "Unable to load stock information."}
                    </div>
                )}
            </div>
        </div>
    );
}

function StockMetric({ label, value, tone }) {
    return (
        <div className={`rr-sale-stock-metric ${tone}`}>
            <span>{label}</span>
            <strong>{formatQty(value)}</strong>
        </div>
    );
}

function QuickModal({ title, icon, saving, onClose, onSubmit, children }) {
    return (
        <div className="rr-sale-modal-layer">
            <div
                className="rr-sale-modal-backdrop"
                onClick={onClose}
            ></div>
            <form
                className="rr-sale-quick-modal"
                onSubmit={onSubmit}
            >
                <div className="rr-sale-modal-header">
                    <div className="rr-sale-modal-icon">
                        <i className={`bi ${icon}`}></i>
                    </div>
                    <div>
                        <strong>{title}</strong>
                        <span>Save and select without leaving this invoice.</span>
                    </div>
                    <button
                        type="button"
                        onClick={onClose}
                        disabled={saving}
                    >
                        <i className="bi bi-x-lg"></i>
                    </button>
                </div>

                <div className="rr-sale-quick-modal-body">{children}</div>

                <div className="rr-sale-modal-footer">
                    <button
                        type="button"
                        className="rr-sale-btn secondary"
                        onClick={onClose}
                        disabled={saving}
                    >
                        Cancel
                    </button>
                    <button
                        type="submit"
                        className="rr-sale-btn primary"
                        disabled={saving}
                    >
                        {saving ? (
                            <>
                                <span className="spinner-border spinner-border-sm"></span>{" "}
                                Saving...
                            </>
                        ) : (
                            <>
                                <i className="bi bi-check-lg"></i> Save & Select
                            </>
                        )}
                    </button>
                </div>
            </form>
        </div>
    );
}

function ModalField({
    label,
    value,
    onChange,
    error,
    required,
    type = "text",
    autoFocus = false,
}) {
    return (
        <div className="rr-sale-field">
            <label>
                {label}
                {required && <Required />}
            </label>
            <input
                type={type}
                value={value}
                autoFocus={autoFocus}
                onChange={(event) => onChange(event.target.value)}
            />
            <ErrorText message={Array.isArray(error) ? error[0] : error} />
        </div>
    );
}

function ModalArea({ label, value, onChange, error }) {
    return (
        <div className="rr-sale-field rr-sale-modal-area">
            <label>{label}</label>
            <textarea
                rows="3"
                value={value}
                onChange={(event) => onChange(event.target.value)}
            ></textarea>
            <ErrorText message={Array.isArray(error) ? error[0] : error} />
        </div>
    );
}

/* ==========================================================================
   Helpers
   ========================================================================== */

function calculateRow(row, billingType = "gst") {
    const qty = numberValue(row.qty);
    const price = numberValue(row.sale_price);
    const gross = round2(qty * price);
    const enteredDiscount = numberValue(row.item_discount);
    const discount =
        row.item_discount_type === "percentage"
            ? Math.min(round2((gross * Math.min(enteredDiscount, 100)) / 100), gross)
            : Math.min(enteredDiscount, gross);
    const total = round2(gross - discount);
    const gstPercent = billingType === 'gst' ? (numberValue(row.gst) || 18) : 0;
    const divisor = 1 + gstPercent / 100;
    const taxable = round2(total / divisor);
    const gstAmount = round2(total - taxable);

    return {
        gross,
        discount,
        taxable,
        gstAmount,
        total,
        exclusiveRate: price / divisor,
        gstPercent,
    };
}

function focusValidationError(errorKey, searchInputRef) {
    let target = null;

    const arrayMatch = String(errorKey).match(
        /^(qty|free_quantity|sale_price|item_discount|gst)\.(\d+)$/,
    );

    if (arrayMatch) {
        target = document.querySelector(
            `[data-sale-row="${arrayMatch[2]}"][data-sale-field="${arrayMatch[1]}"]`,
        );
    } else if (errorKey === "product_id" || errorKey === "items") {
        target = searchInputRef.current;
    } else {
        target = document.querySelector(`[data-validation-key="${errorKey}"]`);
    }

    if (!target) return;

    target.scrollIntoView({
        behavior: "smooth",
        block: "center",
    });
    target.classList.add("rr-sale-validation-focus");

    window.setTimeout(() => {
        target.focus?.({ preventScroll: true });
        target.select?.();
    }, 260);

    window.setTimeout(() => {
        target.classList.remove("rr-sale-validation-focus");
    }, 2200);
}

function SplitPaymentPanel({
    accounts,
    payments,
    grandTotal,
    paid,
    balance,
    errors,
    legacyPaidAmount = 0,
    onChange,
}) {
    const activeAccounts = Array.isArray(accounts) ? accounts : [];
    const rows = Array.isArray(payments) ? payments : [];
    const overpaid = paid > grandTotal;

    const addAccount = (account) => {
        if (rows.some((row) => Number(row.account_id) === Number(account.id))) {
            return;
        }

        const amount = rows.length === 0 && legacyPaidAmount > 0
            ? Math.min(legacyPaidAmount, grandTotal)
            : balance;

        onChange([
            ...rows,
            {
                account_id: account.id,
                payment_mode: account.payment_mode || modeForAccount(account),
                amount: amount > 0 ? String(amount) : "0",
                reference_no: "",
            },
        ]);
    };

    const updateRow = (index, field, value) => {
        onChange(
            rows.map((row, rowIndex) =>
                rowIndex === index
                    ? { ...row, [field]: value }
                    : row,
            ),
        );
    };

    const removeRow = (index) => {
        onChange(rows.filter((_, rowIndex) => rowIndex !== index));
    };

    return (
        <div className="rr-sale-split-payment">
            <div className="rr-sale-split-heading">
                <div>
                    <strong>Payment Methods</strong>
                    <span>Add one or more payment accounts</span>
                </div>

                <div className="rr-sale-payment-metrics">
                    <span>Paid <b>{formatMoney(paid)}</b></span>
                    <span className={overpaid ? "danger" : "balance"}>
                        {overpaid ? "Excess" : "Balance"}
                        <b>{formatMoney(Math.abs(grandTotal - paid))}</b>
                    </span>
                </div>
            </div>

            {legacyPaidAmount > 0 && rows.length === 0 && (
                <div className="rr-sale-payment-warning">
                    <i className="bi bi-info-circle"></i>
                    Existing paid amount {formatMoney(legacyPaidAmount)} has no account breakdown.
                    Select an account to classify it before updating.
                </div>
            )}

            <div className="rr-sale-payment-shortcuts">
                {activeAccounts.map((account) => {
                    const selected = rows.some(
                        (row) => Number(row.account_id) === Number(account.id),
                    );

                    return (
                        <button
                            key={account.id}
                            type="button"
                            disabled={
                                selected ||
                                (balance <= 0 && legacyPaidAmount <= 0)
                            }
                            className={`rr-sale-payment-shortcut ${account.account_type || "other"}`}
                            onClick={() => addAccount(account)}
                        >
                            <i className={`bi ${accountIcon(account)}`}></i>
                            {account.account_name}
                            {selected && <i className="bi bi-check2"></i>}
                        </button>
                    );
                })}
            </div>

            {activeAccounts.length === 0 && (
                <div className="rr-sale-payment-warning">
                    <i className="bi bi-exclamation-triangle"></i>
                    No active payment accounts are available for this branch.
                </div>
            )}

            <div className="rr-sale-payment-rows">
                {rows.map((payment, index) => {
                    const account = activeAccounts.find(
                        (item) => Number(item.id) === Number(payment.account_id),
                    );

                    const needsReference = payment.payment_mode !== "cash";

                    return (
                        <div className="rr-sale-payment-row" key={`${payment.account_id}-${index}`}>
                            <div className="rr-sale-payment-account">
                                <i className={`bi ${accountIcon(account)}`}></i>
                                <span>
                                    <strong>{account?.account_name || "Payment Account"}</strong>
                                    <small>{paymentModeLabel(payment.payment_mode)}</small>
                                </span>
                            </div>

                            <div className="rr-sale-field">
                                <label>Amount</label>
                                <MoneyInput
                                    validationKey={`payments.${index}.amount`}
                                    value={payment.amount}
                                    onChange={(value) => updateRow(index, "amount", value)}
                                />
                                <ErrorText message={errors[`payments.${index}.amount`]} />
                            </div>

                            <div className="rr-sale-field">
                                <label>{needsReference ? "Reference No." : "Reference (optional)"}</label>
                                <input
                                    type="text"
                                    maxLength="150"
                                    value={payment.reference_no || ""}
                                    placeholder={needsReference ? "Transaction/reference number" : "Optional note"}
                                    onChange={(event) =>
                                        updateRow(index, "reference_no", event.target.value)
                                    }
                                />
                                <ErrorText message={errors[`payments.${index}.reference_no`]} />
                            </div>

                            <button
                                type="button"
                                className="rr-sale-payment-remove"
                                title="Remove payment"
                                onClick={() => removeRow(index)}
                            >
                                <i className="bi bi-x-lg"></i>
                            </button>
                        </div>
                    );
                })}
            </div>

            {rows.length === 0 && legacyPaidAmount <= 0 && activeAccounts.length > 0 && (
                <div className="rr-sale-payment-empty">
                    <i className="bi bi-wallet2"></i>
                    No payment entered. This invoice will be saved as unpaid.
                </div>
            )}

            {overpaid && (
                <div className="rr-sale-payment-error">
                    Payment total cannot exceed the invoice total.
                </div>
            )}
        </div>
    );
}

function modeForAccount(account) {
    if (account?.account_type === "cash") return "cash";
    if (account?.account_type === "upi") return "upi";
    if (account?.account_type === "bank") return "bank_transfer";
    if (account?.account_type === "card") return "card";
    return "cash";
}

function accountIcon(account) {
    if (account?.account_type === "cash") return "bi-cash-stack";
    if (account?.account_type === "upi") return "bi-phone";
    if (account?.account_type === "bank") return "bi-bank";
    if (account?.account_type === "card") return "bi-credit-card";
    return "bi-wallet2";
}

function paymentModeLabel(mode) {
    const labels = {
        cash: "Cash",
        gpay: "GPay",
        phonepe: "PhonePe",
        upi: "UPI",
        bank_transfer: "Bank Transfer",
        card: "Card",
        cheque: "Cheque",
    };

    return labels[mode] || "Payment";
}

function paymentStatusClass(status) {
    if (status === "Paid") return "paid";
    if (status === "Partially Paid") return "partial";
    return "unpaid";
}

function apiError(error, fallback) {
    const errors = error?.response?.data?.errors;
    if (errors) {
        const first = Object.values(errors).flat()[0];
        if (first) return first;
    }

    return error?.response?.data?.message || fallback;
}

function uniqueById(items) {
    const map = new Map();
    items.forEach((item) => map.set(Number(item.id), item));
    return [...map.values()];
}

function firstNumber(...values) {
    for (const value of values) {
        const parsed = Number(value);
        if (Number.isFinite(parsed)) return parsed;
    }
    return 0;
}

function numberValue(value) {
    const number = Number(value ?? 0);
    return Number.isFinite(number) ? number : 0;
}

function round2(value) {
    return Math.round((numberValue(value) + Number.EPSILON) * 100) / 100;
}

function formatMoney(value) {
    return new Intl.NumberFormat("en-IN", {
        style: "currency",
        currency: "INR",
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(numberValue(value));
}

function formatQty(value) {
    return new Intl.NumberFormat("en-IN", {
        minimumFractionDigits: 0,
        maximumFractionDigits: 2,
    }).format(numberValue(value));
}

function today() {
    const now = new Date();
    const offset = now.getTimezoneOffset();
    return new Date(now.getTime() - offset * 60000).toISOString().slice(0, 10);
}

function dateInputValue(value) {
    if (!value) return today();

    const stringValue = String(value);

    if (/^\d{4}-\d{2}-\d{2}/.test(stringValue)) {
        return stringValue.slice(0, 10);
    }

    const date = new Date(value);

    if (Number.isNaN(date.getTime())) return today();

    const offset = date.getTimezoneOffset();
    return new Date(date.getTime() - offset * 60000).toISOString().slice(0, 10);
}

Edit.layout = (page) => <BusinessLayout>{page}</BusinessLayout>;
