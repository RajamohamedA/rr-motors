document.addEventListener('DOMContentLoaded', function () {

    'use strict';

    /*
    |--------------------------------------------------------------------------
    | SALES FORM
    |--------------------------------------------------------------------------
    */

    const form = document.getElementById('salesForm');

    if (!form) {
        console.warn('salesForm not found.');
        return;
    }


    /*
    |--------------------------------------------------------------------------
    | ELEMENTS
    |--------------------------------------------------------------------------
    */

    const companySelect =
        document.getElementById('company_id');

    const storeSelect =
        document.getElementById('sales_store_id');

    const customerSelect =
        document.getElementById('sales_customer_id');

    const invoiceNo =
        document.getElementById('salesInvoiceNo');

    const searchInput =
        document.getElementById('product_search');

    const searchResult =
        document.getElementById('searchResult');

    const clearSearch =
        document.getElementById('clearSearch');

    const itemsBody =
        document.getElementById('salesItems');

    const emptyItems =
        document.getElementById('emptyItems');

    const saveSaleBtn =
        document.getElementById('saveSaleBtn');


    /*
    |--------------------------------------------------------------------------
    | CONFIG
    |--------------------------------------------------------------------------
    */

    const config =
        window.salesConfig || {};

    const searchUrl =
        config.searchUrl ||
        document.getElementById('productSearchUrl')?.value ||
        '/api/products/search';

    const checkStockUrl =
        config.checkStockUrl ||
        '/sales/check-stock';

    const quickTransferUrl =
        config.quickTransferUrl ||
        '/stock-transfers/quick';

    const isEdit =
        Boolean(config.isEdit);

    const saleId =
        config.saleId || null;


    /*
    |--------------------------------------------------------------------------
    | OLD VALUES
    |--------------------------------------------------------------------------
    */

    const oldStoreId =
        document.getElementById('old_store_id')?.value ||
        '';

    const oldCustomerId =
        document.getElementById('old_customer_id')?.value ||
        '';


    /*
    |--------------------------------------------------------------------------
    | CSRF
    |--------------------------------------------------------------------------
    */

    let csrfToken =
        document
            .querySelector('meta[name="csrf-token"]')
            ?.getAttribute('content') ||
        '';

    if (!csrfToken) {

        csrfToken =
            form
                .querySelector('input[name="_token"]')
                ?.value ||
            '';
    }

    if (!csrfToken) {

        console.error(
            'CSRF token not found.'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | STATE
    |--------------------------------------------------------------------------
    */

    let searchTimer = null;

    let selectedSearchIndex = -1;

    let stockCheckInProgress = false;

    let transferInProgress = false;

    let submitting = false;

    let currentShortageData = null;

    let modalResolver = null;

    const quantityTimers =
        new WeakMap();

    const checkingRows =
        new WeakSet();


    /*
    |--------------------------------------------------------------------------
    | BOOTSTRAP MODAL
    |--------------------------------------------------------------------------
    */

    const stockModalElement =
        document.getElementById(
            'stockShortageModal'
        );

    let stockModal = null;

    if (
        stockModalElement &&
        typeof bootstrap !== 'undefined'
    ) {

        stockModal =
            new bootstrap.Modal(
                stockModalElement
            );
    }


    /*
    |--------------------------------------------------------------------------
    | MODAL ELEMENTS
    |--------------------------------------------------------------------------
    */

    const transferButton =
        document.getElementById(
            'transferStockBtn'
        );

    const godownStockList =
        document.getElementById(
            'godownStockList'
        );

    const noGodownStockMessage =
        document.getElementById(
            'noGodownStockMessage'
        );

    const cannotFulfillMessage =
        document.getElementById(
            'cannotFulfillMessage'
        );


    /*
    |--------------------------------------------------------------------------
    | UTILITY - MONEY
    |--------------------------------------------------------------------------
    */

    function money(value) {

        const number =
            parseFloat(value) || 0;

        return number.toFixed(2);
    }


    /*
    |--------------------------------------------------------------------------
    | UTILITY - ESCAPE HTML
    |--------------------------------------------------------------------------
    */

    function escapeHtml(value) {

        const div =
            document.createElement('div');

        div.textContent =
            value ?? '';

        return div.innerHTML;
    }


    /*
    |--------------------------------------------------------------------------
    | UTILITY - SET TEXT
    |--------------------------------------------------------------------------
    |
    | This fixes:
    |
    | Uncaught ReferenceError: setText is not defined
    |
    */

    function setText(
        elementId,
        value
    ) {

        const element =
            document.getElementById(
                elementId
            );

        if (element) {

            element.textContent =
                value;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | NOTICE
    |--------------------------------------------------------------------------
    */

    function showNotice(
        message,
        type = 'warning'
    ) {

        const existing =
            form.querySelector(
                '.sales-js-alert'
            );

        if (existing) {
            existing.remove();
        }


        const alert =
            document.createElement('div');

        alert.className =
            `alert alert-${type} alert-dismissible fade show sales-js-alert`;

        alert.innerHTML = `

            <i class="fas fa-info-circle me-1"></i>

            ${escapeHtml(message)}

            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="alert">
            </button>

        `;

        form.prepend(alert);


        setTimeout(
            function () {

                if (
                    alert &&
                    alert.parentNode
                ) {

                    alert.remove();
                }

            },
            4000
        );
    }


    /*
    |--------------------------------------------------------------------------
    | SEARCH RESULT DISPLAY
    |--------------------------------------------------------------------------
    */

    function hideResults() {

        if (!searchResult) {
            return;
        }

        searchResult.innerHTML = '';

        searchResult.style.display =
            'none';

        selectedSearchIndex =
            -1;
    }


    function showResults() {

        if (!searchResult) {
            return;
        }

        searchResult.style.display =
            'block';
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE EMPTY STATE
    |--------------------------------------------------------------------------
    */

    function updateEmptyState() {

        if (!itemsBody) {
            return;
        }

        const rows =
            itemsBody.querySelectorAll(
                '.sale-item-row'
            );

        const count =
            rows.length;


        setText(
            'itemCount',
            `${count} ${count === 1 ? 'Item' : 'Items'}`
        );


        if (emptyItems) {

            emptyItems.classList.toggle(
                'd-none',
                count > 0
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | HEADER VALIDATION FOR SEARCH
    |--------------------------------------------------------------------------
    */

    function validateHeaderForProductSearch() {

        if (!companySelect?.value) {

            showNotice(
                'Please select a company before searching products.',
                'warning'
            );

            companySelect?.focus();

            return false;
        }


        if (!storeSelect?.value) {

            showNotice(
                'Please select a store before searching products.',
                'warning'
            );

            storeSelect?.focus();

            return false;
        }


        if (!customerSelect?.value) {

            showNotice(
                'Please select a customer before searching products.',
                'warning'
            );

            customerSelect?.focus();

            return false;
        }


        return true;
    }


    /*
    |--------------------------------------------------------------------------
    | COMPANY DATA
    |--------------------------------------------------------------------------
    |
    | URL:
    |
    | /sales/company/{company}/data
    |
    */

    async function loadCompanyData(
        companyId,
        keepExisting = false
    ) {

        if (!storeSelect || !customerSelect) {
            return;
        }


        /*
        | No company
        */

        if (!companyId) {

            storeSelect.innerHTML = `
                <option value="">
                    Select Company First
                </option>
            `;

            customerSelect.innerHTML = `
                <option value="">
                    Select Company First
                </option>
            `;

            storeSelect.disabled = true;

            customerSelect.disabled = true;

            if (searchInput) {
                searchInput.disabled = true;
            }

            hideResults();

            return;
        }


        /*
        | Loading
        */

        storeSelect.disabled = true;

        customerSelect.disabled = true;

        storeSelect.innerHTML = `
            <option value="">
                Loading stores...
            </option>
        `;

        customerSelect.innerHTML = `
            <option value="">
                Loading customers...
            </option>
        `;


        if (searchInput) {
            searchInput.disabled = true;
        }


        try {

            const url =
                `/sales/company/${encodeURIComponent(companyId)}/data`;


            const response =
                await fetch(
                    url,
                    {
                        method: 'GET',

                        headers: {
                            'Accept':
                                'application/json',

                            'X-Requested-With':
                                'XMLHttpRequest'
                        }
                    }
                );


            if (!response.ok) {

                let message =
                    `Unable to load company data. HTTP ${response.status}`;

                try {

                    const errorData =
                        await response.json();

                    if (errorData.message) {
                        message =
                            errorData.message;
                    }

                } catch (e) {
                    // ignore
                }

                throw new Error(message);
            }


            const data =
                await response.json();


            /*
            |--------------------------------------------------------------------------
            | STORES
            |--------------------------------------------------------------------------
            */

            storeSelect.innerHTML = `
                <option value="">
                    Select Store
                </option>
            `;


            const stores =
                Array.isArray(data.stores)
                    ? data.stores
                    : [];


            stores.forEach(
                function (store) {

                    const option =
                        document.createElement(
                            'option'
                        );

                    option.value =
                        store.id;

                    option.textContent =
                        store.store_name ??
                        store.name ??
                        `Store ${store.id}`;

                    storeSelect.appendChild(
                        option
                    );
                }
            );


            /*
            |--------------------------------------------------------------------------
            | CUSTOMERS
            |--------------------------------------------------------------------------
            */

            customerSelect.innerHTML = `
                <option value="">
                    Select Customer
                </option>
            `;


            const customers =
                Array.isArray(data.customers)
                    ? data.customers
                    : [];


            customers.forEach(
                function (customer) {

                    const option =
                        document.createElement(
                            'option'
                        );

                    option.value =
                        customer.id;

                    option.textContent =
                        customer.customer_name ??
                        customer.name ??
                        `Customer ${customer.id}`;

                    customerSelect.appendChild(
                        option
                    );
                }
            );


            /*
            |--------------------------------------------------------------------------
            | INVOICE NUMBER
            |--------------------------------------------------------------------------
            */

            if (
                data.invoiceNo &&
                invoiceNo
            ) {

                /*
                | New sale:
                | Always use server-generated invoice number.
                |
                | Edit:
                | Do not replace existing invoice number.
                */

                if (
                    !isEdit ||
                    !invoiceNo.value
                ) {

                    invoiceNo.value =
                        data.invoiceNo;
                }
            }


            /*
            |--------------------------------------------------------------------------
            | ENABLE
            |--------------------------------------------------------------------------
            */

            storeSelect.disabled =
                false;

            customerSelect.disabled =
                false;


            /*
            |--------------------------------------------------------------------------
            | RESTORE EDIT VALUES
            |--------------------------------------------------------------------------
            */

            if (keepExisting) {

                if (oldStoreId) {

                    storeSelect.value =
                        oldStoreId;
                }

                if (oldCustomerId) {

                    customerSelect.value =
                        oldCustomerId;
                }
            }


            /*
            |--------------------------------------------------------------------------
            | SEARCH
            |--------------------------------------------------------------------------
            */

            if (
                companySelect.value &&
                storeSelect.value &&
                customerSelect.value
            ) {

                if (searchInput) {
                    searchInput.disabled = false;
                }

                hideResults();
            }


        } catch (error) {

            console.error(
                'Company data error:',
                error
            );


            storeSelect.innerHTML = `
                <option value="">
                    Failed to load stores
                </option>
            `;

            customerSelect.innerHTML = `
                <option value="">
                    Failed to load customers
                </option>
            `;


            storeSelect.disabled =
                true;

            customerSelect.disabled =
                true;


            if (searchInput) {
                searchInput.disabled = true;
            }


            showNotice(
                error.message ||
                'Unable to load company data.',
                'danger'
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | COMPANY CHANGE
    |--------------------------------------------------------------------------
    */

    if (companySelect) {

        companySelect.addEventListener(
            'change',
            function () {

                /*
                | Clear previous store/customer.
                */

                loadCompanyData(
                    this.value,
                    false
                );
            }
        );


        /*
        | Edit page
        */

        if (companySelect.value) {

            loadCompanyData(
                companySelect.value,
                true
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | ENABLE SEARCH AFTER HEADER CHANGE
    |--------------------------------------------------------------------------
    */

    function updateSearchAvailability() {

        if (!searchInput) {
            return;
        }


        const ready =
            Boolean(
                companySelect?.value &&
                storeSelect?.value &&
                customerSelect?.value
            );


        searchInput.disabled =
            !ready;


        if (!ready) {
            hideResults();
        }
    }


    storeSelect?.addEventListener(
        'change',
        function () {

            hideResults();

            updateSearchAvailability();
        }
    );


    customerSelect?.addEventListener(
        'change',
        function () {

            hideResults();

            updateSearchAvailability();
        }
    );


    /*
    |--------------------------------------------------------------------------
    | PRODUCT SEARCH
    |--------------------------------------------------------------------------
    */

    async function searchProducts(
        keyword
    ) {

        keyword =
            String(keyword || '').trim();


        if (keyword.length < 2) {

            hideResults();

            return;
        }


        /*
        |--------------------------------------------------------------------------
        | REQUIRE COMPANY + STORE + CUSTOMER
        |--------------------------------------------------------------------------
        */

        if (
            !validateHeaderForProductSearch()
        ) {

            hideResults();

            return;
        }


        if (!searchUrl) {

            showNotice(
                'Product search URL is not configured.',
                'danger'
            );

            return;
        }


        const companyId =
            companySelect.value;

        const storeId =
            storeSelect.value;


        /*
        |--------------------------------------------------------------------------
        | EXACT SEARCH URL
        |--------------------------------------------------------------------------
        |
        | Example:
        |
        | /api/products/search
        | ?search=hch
        | &company_id=1
        | &sales_store_id=1
        |
        */

        const separator =
            searchUrl.includes('?')
                ? '&'
                : '?';


        const url =
            searchUrl +
            separator +
            'search=' +
            encodeURIComponent(keyword) +
            '&company_id=' +
            encodeURIComponent(companyId) +
            '&sales_store_id=' +
            encodeURIComponent(storeId);


        searchResult.innerHTML = `

            <div class="list-group-item text-center">

                <span
                    class="spinner-border spinner-border-sm me-2">
                </span>

                Searching...

            </div>

        `;

        showResults();


        try {

            const response =
                await fetch(
                    url,
                    {
                        method: 'GET',

                        headers: {
                            'Accept':
                                'application/json',

                            'X-Requested-With':
                                'XMLHttpRequest'
                        }
                    }
                );


            if (!response.ok) {

                throw new Error(
                    `Product search failed. HTTP ${response.status}`
                );
            }


            const data =
                await response.json();


            renderSearchResults(
                data
            );


        } catch (error) {

            console.error(
                'Product search error:',
                error
            );


            searchResult.innerHTML = `

                <div class="list-group-item text-danger">

                    <i class="fas fa-exclamation-circle me-1"></i>

                    ${escapeHtml(
                error.message ||
                'Unable to search products.'
            )}

                </div>

            `;

            showResults();
        }
    }


    /*
    |--------------------------------------------------------------------------
    | RENDER SEARCH RESULTS
    |--------------------------------------------------------------------------
    */

    function renderSearchResults(
        data
    ) {

        selectedSearchIndex =
            -1;


        let products =
            data;


        if (
            data &&
            Array.isArray(data.data)
        ) {

            products =
                data.data;
        }


        if (!Array.isArray(products)) {

            products = [];
        }


        if (products.length === 0) {

            searchResult.innerHTML = `

                <div class="list-group-item text-center text-muted py-3">

                    <i class="fas fa-search-minus me-1"></i>

                    No product found.

                </div>

            `;

            showResults();

            return;
        }


        searchResult.innerHTML = '';


        products.forEach(
            function (product, index) {

                const stock =
                    parseFloat(
                        product.current_stock ??
                        product.shop_stock ??
                        product.stock ??
                        0
                    ) || 0;


                const price =
                    parseFloat(
                        product.sale_price ??
                        product.selling_price ??
                        product.price ??
                        0
                    ) || 0;


                const gst =
                    parseFloat(
                        product.gst_percent ??
                        product.gst ??
                        0
                    ) || 0;


                const item =
                    document.createElement(
                        'button'
                    );


                item.type =
                    'button';


                item.className =
                    'list-group-item list-group-item-action product-search-item';


                item.dataset.index =
                    index;


                item.innerHTML = `

                    <div class="d-flex justify-content-between align-items-center">

                        <div>

                            <div class="fw-semibold">

                                ${escapeHtml(
                    product.product_name ??
                    ''
                )}

                            </div>

                            <small class="text-muted">

                                Code:
                                ${escapeHtml(
                    product.product_code ??
                    '-'
                )}

                                ${product.barcode
                        ? ' | Barcode: ' +
                        escapeHtml(product.barcode)
                        : ''
                    }

                            </small>

                        </div>


                        <div class="text-end">

                            <div class="small">

                                Stock:
                                <strong>
                                    ${money(stock)}
                                </strong>

                            </div>

                            <div class="small text-muted">

                                ₹${money(price)}

                            </div>

                        </div>

                    </div>

                `;


                item.addEventListener(
                    'click',
                    function () {

                        addProduct(
                            product
                        );
                    }
                );


                searchResult.appendChild(
                    item
                );
            }
        );


        showResults();
    }


    /*
    |--------------------------------------------------------------------------
    | SEARCH INPUT
    |--------------------------------------------------------------------------
    */

    if (searchInput) {

        searchInput.addEventListener(
            'input',
            function () {

                const value =
                    this.value.trim();


                clearTimeout(
                    searchTimer
                );


                if (value.length < 2) {

                    hideResults();

                    return;
                }


                if (
                    !validateHeaderForProductSearch()
                ) {

                    hideResults();

                    return;
                }


                searchTimer =
                    setTimeout(
                        function () {

                            searchProducts(
                                value
                            );

                        },
                        300
                    );
            }
        );


        /*
        |--------------------------------------------------------------------------
        | KEYBOARD
        |--------------------------------------------------------------------------
        */

        searchInput.addEventListener(
            'keydown',
            function (event) {

                const items =
                    searchResult?.querySelectorAll(
                        '.product-search-item'
                    ) || [];


                if (!items.length) {
                    return;
                }


                if (
                    event.key === 'ArrowDown'
                ) {

                    event.preventDefault();

                    selectedSearchIndex =
                        Math.min(
                            selectedSearchIndex + 1,
                            items.length - 1
                        );

                } else if (
                    event.key === 'ArrowUp'
                ) {

                    event.preventDefault();

                    selectedSearchIndex =
                        Math.max(
                            selectedSearchIndex - 1,
                            0
                        );

                } else if (
                    event.key === 'Enter'
                ) {

                    if (
                        selectedSearchIndex >= 0
                    ) {

                        event.preventDefault();

                        items[
                            selectedSearchIndex
                        ].click();
                    }

                    return;

                } else if (
                    event.key === 'Escape'
                ) {

                    hideResults();

                    return;
                }


                items.forEach(
                    function (item, index) {

                        item.classList.toggle(
                            'active',
                            index ===
                            selectedSearchIndex
                        );
                    }
                );
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CLEAR SEARCH
    |--------------------------------------------------------------------------
    */

    clearSearch?.addEventListener(
        'click',
        function () {

            if (searchInput) {

                searchInput.value =
                    '';

                searchInput.focus();
            }

            hideResults();
        }
    );


    /*
    |--------------------------------------------------------------------------
    | FIND EXISTING PRODUCT
    |--------------------------------------------------------------------------
    */

    function findExistingProduct(
        productId
    ) {

        const rows =
            itemsBody.querySelectorAll(
                '.sale-item-row'
            );


        for (
            const row of rows
        ) {

            const input =
                row.querySelector(
                    'input[name="product_id[]"]'
                );


            if (
                input &&
                String(input.value) ===
                String(productId)
            ) {

                return row;
            }
        }


        return null;
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE SHOP STOCK
    |--------------------------------------------------------------------------
    */

    function updateRowStock(
        row,
        stock
    ) {

        if (!row) {
            return;
        }


        const numericStock =
            parseFloat(stock) || 0;


        row.dataset.shopStock =
            numericStock;


        const stockElement =
            row.querySelector(
                '.stock'
            );


        if (!stockElement) {
            return;
        }


        stockElement.textContent =
            money(numericStock);


        stockElement.classList.remove(
            'bg-success',
            'bg-danger',
            'bg-warning'
        );


        if (numericStock > 0) {

            stockElement.classList.add(
                'bg-success'
            );

        } else {

            stockElement.classList.add(
                'bg-danger'
            );
        }
    }


    /*
    |--------------------------------------------------------------------------
    | UPDATE TRANSFER BUTTON
    |--------------------------------------------------------------------------
    */

    function updateTransferButton(
        row,
        shortage
    ) {

        if (!row) {
            return;
        }


        const button =
            row.querySelector(
                '.transferRowStock, .transfer-stock-btn'
            );


        if (!button) {
            return;
        }


        const amount =
            parseFloat(shortage) || 0;


        if (amount > 0) {

            button.classList.remove(
                'btn-outline-primary'
            );

            button.classList.add(
                'btn-danger'
            );

            button.title =
                `Shop stock insufficient - Transfer ${money(amount)} from Godown`;

        } else {

            button.classList.remove(
                'btn-danger'
            );

            button.classList.add(
                'btn-outline-primary'
            );

            button.title =
                'Transfer stock from Godown to Shop';
        }
    }


    /*
    |--------------------------------------------------------------------------
    | GET ROW STOCK DATA
    |--------------------------------------------------------------------------
    */

    function getRowStockData(
        row
    ) {

        const productId =
            row.querySelector(
                'input[name="product_id[]"]'
            )?.value || '';


        const qty =
            parseFloat(
                row.querySelector(
                    '.qty'
                )?.value
            ) || 0;


        const freeQuantity =
            parseFloat(
                row.querySelector(
                    '.free_qty'
                )?.value
            ) || 0;


        /*
        | Stock required = sale quantity + free quantity
        */

        const required =
            qty + freeQuantity;


        return {
            productId,
            qty,
            freeQuantity,
            required
        };
    }


    /*
    |--------------------------------------------------------------------------
    | ADD PRODUCT
    |--------------------------------------------------------------------------
    */

    function addProduct(
        product
    ) {

        if (
            !product ||
            !product.id
        ) {
            return;
        }


        /*
        |--------------------------------------------------------------------------
        | DUPLICATE
        |--------------------------------------------------------------------------
        */

        const existing =
            findExistingProduct(
                product.id
            );


        if (existing) {

            const qtyInput =
                existing.querySelector(
                    '.qty'
                );


            if (qtyInput) {

                const current =
                    parseFloat(
                        qtyInput.value
                    ) || 0;


                qtyInput.value =
                    (
                        current + 1
                    ).toFixed(2);
            }


            calculateRow(
                existing
            );

            calculateTotals();

            hideResults();


            if (searchInput) {

                searchInput.value =
                    '';

                searchInput.focus();
            }


            if (qtyInput) {

                qtyInput.focus();

                qtyInput.select();
            }


            scheduleRowStockCheck(
                existing
            );


            return;
        }


        /*
        |--------------------------------------------------------------------------
        | PRODUCT VALUES
        |--------------------------------------------------------------------------
        */

        const stock =
            parseFloat(
                product.current_stock ??
                product.shop_stock ??
                product.stock ??
                0
            ) || 0;


        const price =
            parseFloat(
                product.sale_price ??
                product.selling_price ??
                product.price ??
                0
            ) || 0;


        const gst =
            parseFloat(
                product.gst_percent ??
                product.gst ??
                0
            ) || 0;


        /*
        |--------------------------------------------------------------------------
        | CREATE ROW
        |--------------------------------------------------------------------------
        */

        const row =
            document.createElement(
                'tr'
            );


        row.className =
            'sale-item-row';


        row.dataset.productId =
            product.id;


        row.dataset.shopStock =
            stock;


        row.innerHTML = `

            <!-- CODE -->

            <td>

                <strong>
                    ${escapeHtml(
            product.product_code ??
            '-'
        )}
                </strong>

                <input
                    type="hidden"
                    name="product_id[]"
                    value="${product.id}">

            </td>


            <!-- PRODUCT -->

            <td>

                <div class="fw-semibold">

                    ${escapeHtml(
            product.product_name ??
            ''
        )}

                </div>

                ${product.barcode
                ? `
                            <small class="text-muted">
                                Barcode:
                                ${escapeHtml(product.barcode)}
                            </small>
                          `
                : ''
            }

            </td>


            <!-- LOCATION -->

            <td class="text-center">

                <span class="badge text-bg-primary">

                    <i class="fas fa-store me-1"></i>

                    Shop

                </span>

            </td>


            <!-- STOCK -->

            <td class="text-center">

                <span
                    class="badge bg-success stock">

                    ${money(stock)}

                </span>

            </td>


            <!-- QUANTITY -->

            <td>

                <input
                    type="number"
                    step="0.01"
                    min="0.01"
                    name="qty[]"
                    class="form-control form-control-sm qty"
                    value="1">

            </td>


            <!-- FREE -->

            <td>

                <input
                    type="number"
                    step="0.01"
                    min="0"
                    name="free_quantity[]"
                    class="form-control form-control-sm free_qty"
                    value="0">

            </td>


            <!-- PRICE -->

            <td>

                <input
                    type="number"
                    step="0.01"
                    min="0"
                    name="price[]"
                    class="form-control form-control-sm price"
                    value="${money(price)}">

            </td>


            <!-- DISCOUNT -->

            <td>

                <input
                    type="number"
                    step="0.01"
                    min="0"
                    name="discount[]"
                    class="form-control form-control-sm discount"
                    value="0">

            </td>


            <!-- GST -->

            <td>

                <input
                    type="number"
                    step="0.01"
                    min="0"
                    name="gst[]"
                    class="form-control form-control-sm gst"
                    value="${gst}">

            </td>


            <!-- GST AMOUNT -->

            <td>

                <input
                    type="text"
                    class="form-control form-control-sm gst_amount"
                    readonly
                    value="0.00">

            </td>


            <!-- TOTAL -->

            <td>

                <input
                    type="text"
                    class="form-control form-control-sm line_total fw-bold"
                    readonly
                    value="0.00">

            </td>


            <!-- TRANSFER -->

            <td class="text-center">

                <button
                    type="button"
                    class="btn btn-outline-primary btn-sm transferRowStock"
                    title="Transfer stock from Godown to Shop">

                    <i class="fas fa-exchange-alt"></i>

                </button>

            </td>


            <!-- REMOVE -->

            <td class="text-center">

                <button
                    type="button"
                    class="btn btn-outline-danger btn-sm removeRow"
                    title="Remove product">

                    <i class="fas fa-trash"></i>

                </button>

            </td>

        `;


        itemsBody.appendChild(
            row
        );


        calculateRow(
            row
        );

        calculateTotals();

        updateEmptyState();


        if (searchInput) {

            searchInput.value =
                '';

            searchInput.focus();
        }


        hideResults();


        /*
        |--------------------------------------------------------------------------
        | CHECK STOCK
        |--------------------------------------------------------------------------
        */

        checkRowStockAfterChange(
            row
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CALCULATE ROW
    |--------------------------------------------------------------------------
    */

    function calculateRow(
        row
    ) {

        if (!row) {
            return;
        }


        const qty =
            parseFloat(
                row.querySelector(
                    '.qty'
                )?.value
            ) || 0;


        const price =
            parseFloat(
                row.querySelector(
                    '.price'
                )?.value
            ) || 0;


        const discount =
            parseFloat(
                row.querySelector(
                    '.discount'
                )?.value
            ) || 0;


        const gst =
            parseFloat(
                row.querySelector(
                    '.gst'
                )?.value
            ) || 0;


        const gross =
            qty * price;


        const subtotal =
            Math.max(
                gross - discount,
                0
            );


        const gstAmount =
            subtotal * gst / 100;


        const total =
            subtotal + gstAmount;


        const gstInput =
            row.querySelector(
                '.gst_amount'
            );


        const totalInput =
            row.querySelector(
                '.line_total'
            );


        if (gstInput) {

            gstInput.value =
                money(gstAmount);
        }


        if (totalInput) {

            totalInput.value =
                money(total);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | CALCULATE TOTALS
    |--------------------------------------------------------------------------
    */

    function calculateTotals() {

        if (!itemsBody) {
            return;
        }


        let subTotal =
            0;

        let discountTotal =
            0;

        let gstTotal =
            0;

        let grandTotal =
            0;


        const rows =
            itemsBody.querySelectorAll(
                '.sale-item-row'
            );


        rows.forEach(
            function (row) {

                const qty =
                    parseFloat(
                        row.querySelector(
                            '.qty'
                        )?.value
                    ) || 0;


                const price =
                    parseFloat(
                        row.querySelector(
                            '.price'
                        )?.value
                    ) || 0;


                const discount =
                    parseFloat(
                        row.querySelector(
                            '.discount'
                        )?.value
                    ) || 0;


                const gst =
                    parseFloat(
                        row.querySelector(
                            '.gst'
                        )?.value
                    ) || 0;


                const gross =
                    qty * price;


                const lineSubtotal =
                    Math.max(
                        gross - discount,
                        0
                    );


                const gstAmount =
                    lineSubtotal *
                    gst /
                    100;


                const lineTotal =
                    lineSubtotal +
                    gstAmount;


                subTotal +=
                    lineSubtotal;


                discountTotal +=
                    discount;


                gstTotal +=
                    gstAmount;


                grandTotal +=
                    lineTotal;
            }
        );


        /*
        |--------------------------------------------------------------------------
        | DISPLAY
        |--------------------------------------------------------------------------
        */

        setText(
            'subTotalDisplay',
            money(subTotal)
        );


        setText(
            'discountDisplay',
            money(discountTotal)
        );


        setText(
            'gstDisplay',
            money(gstTotal)
        );


        setText(
            'grandTotalDisplay',
            money(grandTotal)
        );


        /*
        |--------------------------------------------------------------------------
        | HIDDEN INPUTS
        |--------------------------------------------------------------------------
        */

        const subTotalInput =
            document.getElementById(
                'sub_total'
            );

        const discountInput =
            document.getElementById(
                'discount_total'
            );

        const gstInput =
            document.getElementById(
                'gst_total'
            );

        const grandTotalInput =
            document.getElementById(
                'grand_total'
            );

        const paymentGrandTotal =
            document.getElementById(
                'paymentGrandTotal'
            );


        if (subTotalInput) {

            subTotalInput.value =
                money(subTotal);
        }


        if (discountInput) {

            discountInput.value =
                money(discountTotal);
        }


        if (gstInput) {

            gstInput.value =
                money(gstTotal);
        }


        if (grandTotalInput) {

            grandTotalInput.value =
                money(grandTotal);
        }


        if (paymentGrandTotal) {

            paymentGrandTotal.value =
                money(grandTotal);
        }


        calculateBalance();

        updateEmptyState();
    }


    /*
    |--------------------------------------------------------------------------
    | BALANCE
    |--------------------------------------------------------------------------
    */

    function calculateBalance() {

        const grandTotal =
            parseFloat(
                document.getElementById(
                    'grand_total'
                )?.value
            ) || 0;


        const paid =
            parseFloat(
                document.getElementById(
                    'paid_amount'
                )?.value
            ) || 0;


        const balance =
            Math.max(
                grandTotal - paid,
                0
            );


        const balanceInput =
            document.getElementById(
                'balance_amount'
            );


        if (balanceInput) {

            balanceInput.value =
                money(balance);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | ROW INPUT EVENTS
    |--------------------------------------------------------------------------
    */

    itemsBody.addEventListener(
        'input',
        function (event) {

            const row =
                event.target.closest(
                    '.sale-item-row'
                );


            if (!row) {
                return;
            }


            if (
                event.target.classList.contains('qty') ||
                event.target.classList.contains('free_qty') ||
                event.target.classList.contains('price') ||
                event.target.classList.contains('discount') ||
                event.target.classList.contains('gst')
            ) {

                calculateRow(
                    row
                );

                calculateTotals();
            }


            /*
            |--------------------------------------------------------------------------
            | STOCK CHECK ONLY FOR QTY/FREE
            |--------------------------------------------------------------------------
            */

            if (
                event.target.classList.contains('qty') ||
                event.target.classList.contains('free_qty')
            ) {

                scheduleRowStockCheck(
                    row
                );
            }
        }
    );


    /*
    |--------------------------------------------------------------------------
    | CHANGE EVENT
    |--------------------------------------------------------------------------
    */

    itemsBody.addEventListener(
        'change',
        function (event) {

            const row =
                event.target.closest(
                    '.sale-item-row'
                );


            if (!row) {
                return;
            }


            calculateRow(
                row
            );

            calculateTotals();


            if (
                event.target.classList.contains('qty') ||
                event.target.classList.contains('free_qty')
            ) {

                checkRowStockAfterChange(
                    row
                );
            }
        }
    );


    /*
    |--------------------------------------------------------------------------
    | BLUR EVENT
    |--------------------------------------------------------------------------
    */

    itemsBody.addEventListener(
        'blur',
        function (event) {

            const row =
                event.target.closest(
                    '.sale-item-row'
                );


            if (!row) {
                return;
            }


            if (
                event.target.classList.contains('qty') ||
                event.target.classList.contains('free_qty')
            ) {

                checkRowStockAfterChange(
                    row
                );
            }
        },
        true
    );


    /*
    |--------------------------------------------------------------------------
    | SCHEDULE STOCK CHECK
    |--------------------------------------------------------------------------
    */

    function scheduleRowStockCheck(
        row
    ) {

        const oldTimer =
            quantityTimers.get(
                row
            );


        if (oldTimer) {

            clearTimeout(
                oldTimer
            );
        }


        const timer =
            setTimeout(
                function () {

                    checkRowStockAfterChange(
                        row
                    );

                },
                500
            );


        quantityTimers.set(
            row,
            timer
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CHECK STOCK AFTER QUANTITY CHANGE
    |--------------------------------------------------------------------------
    */

    async function checkRowStockAfterChange(
        row
    ) {

        if (
            !row ||
            submitting ||
            transferInProgress
        ) {

            return;
        }


        const stockData =
            getRowStockData(
                row
            );


        if (
            !stockData.productId ||
            stockData.required <= 0
        ) {

            return;
        }


        if (
            !companySelect?.value ||
            !storeSelect?.value
        ) {

            return;
        }


        await checkSalesStock(
            row,
            null,
            false
        );
    }


    /*
    |--------------------------------------------------------------------------
    | CHECK SALES STOCK
    |--------------------------------------------------------------------------
    */

    async function checkSalesStock(
        row,
        rowIndex = null,
        finalCheck = false
    ) {

        if (!row) {

            return {
                success: false,
                cancelled: true
            };
        }


        if (
            checkingRows.has(row)
        ) {

            return {
                success: false,
                cancelled: true
            };
        }


        const stockData =
            getRowStockData(
                row
            );


        const companyId =
            companySelect?.value ||
            '';


        const storeId =
            storeSelect?.value ||
            '';


        if (!companyId) {

            if (finalCheck) {

                showNotice(
                    'Please select a company.',
                    'warning'
                );
            }

            return {
                success: false,
                cancelled: true
            };
        }


        if (!storeId) {

            if (finalCheck) {

                showNotice(
                    'Please select a store.',
                    'warning'
                );
            }

            return {
                success: false,
                cancelled: true
            };
        }


        if (
            !stockData.productId ||
            stockData.required <= 0
        ) {

            return {
                success: false,
                cancelled: true
            };
        }


        checkingRows.add(
            row
        );

        stockCheckInProgress =
            true;


        try {

            const response =
                await fetch(
                    checkStockUrl,
                    {
                        method: 'POST',

                        headers: {

                            'Content-Type':
                                'application/json',

                            'Accept':
                                'application/json',

                            'X-CSRF-TOKEN':
                                csrfToken,

                            'X-Requested-With':
                                'XMLHttpRequest'
                        },

                        body:
                            JSON.stringify({

                                company_id:
                                    companyId,

                                store_id:
                                    storeId,

                                product_id:
                                    stockData.productId,

                                quantity:
                                    stockData.required,

                                sale_id:
                                    saleId
                            })
                    }
                );


            /*
            |--------------------------------------------------------------------------
            | 419
            |--------------------------------------------------------------------------
            */

            if (
                response.status === 419
            ) {

                throw new Error(
                    'Session expired or CSRF token is invalid. Please refresh the page.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | READ RESPONSE
            |--------------------------------------------------------------------------
            */

            let data = {};

            try {

                data =
                    await response.json();

            } catch (e) {

                throw new Error(
                    `Stock check failed. HTTP ${response.status}`
                );
            }


            if (
                !response.ok ||
                !data.success
            ) {

                throw new Error(
                    data.message ||
                    'Unable to check Shop stock.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | UPDATE SHOP STOCK
            |--------------------------------------------------------------------------
            */

            updateRowStock(
                row,
                data.shop_stock
            );


            /*
            |--------------------------------------------------------------------------
            | ENOUGH STOCK
            |--------------------------------------------------------------------------
            */

            if (
                data.available
            ) {

                updateTransferButton(
                    row,
                    0
                );


                return {
                    success: true,
                    transferred: false,
                    shop_stock:
                        data.shop_stock
                };
            }


            /*
            |--------------------------------------------------------------------------
            | SHORTAGE
            |--------------------------------------------------------------------------
            */

            const shortage =
                parseFloat(
                    data.shortage
                ) || 0;


            updateTransferButton(
                row,
                shortage
            );


            /*
            |--------------------------------------------------------------------------
            | STORE CURRENT SHORTAGE
            |--------------------------------------------------------------------------
            */

            currentShortageData = {

                companyId:
                    companyId,

                storeId:
                    storeId,

                productId:
                    stockData.productId,

                quantity:
                    stockData.required,

                rowIndex:
                    rowIndex,

                shortage:
                    shortage,

                shopStock:
                    parseFloat(
                        data.shop_stock
                    ) || 0,

                godowns:
                    Array.isArray(
                        data.godowns
                    )
                        ? data.godowns
                        : [],

                row:
                    row
            };


            /*
            |--------------------------------------------------------------------------
            | SHOW POPUP
            |--------------------------------------------------------------------------
            */

            showShortageModal(
                data
            );


            /*
            |--------------------------------------------------------------------------
            | WAIT FOR TRANSFER/CANCEL
            |--------------------------------------------------------------------------
            */

            return await waitForModalResult();


        } catch (error) {

            console.error(
                'Stock check error:',
                error
            );


            if (finalCheck) {

                showNotice(
                    error.message ||
                    'Unable to check stock.',
                    'danger'
                );

            } else {

                showNotice(
                    error.message ||
                    'Unable to check Shop stock.',
                    'danger'
                );
            }


            return {
                success: false,
                cancelled: true
            };


        } finally {

            checkingRows.delete(
                row
            );

            stockCheckInProgress =
                false;
        }
    }


    /*
    |--------------------------------------------------------------------------
    | SHOW SHORTAGE MODAL
    |--------------------------------------------------------------------------
    */

    function showShortageModal(
        data
    ) {

        /*
        |--------------------------------------------------------------------------
        | Bootstrap missing
        |--------------------------------------------------------------------------
        */

        if (!stockModal) {

            alert(
                data.message ||
                'Shop stock is insufficient.'
            );

            return;
        }


        const row =
            currentShortageData?.row ||
            null;


        const productName =
            data.product_name ||
            row?.querySelector(
                'td:nth-child(2) .fw-semibold'
            )?.textContent?.trim() ||
            'Product';


        const required =
            parseFloat(
                data.required ??
                currentShortageData?.quantity
            ) || 0;


        const shopStock =
            parseFloat(
                data.shop_stock ??
                currentShortageData?.shopStock
            ) || 0;


        const shortage =
            parseFloat(
                data.shortage
            ) || 0;


        /*
        |--------------------------------------------------------------------------
        | PRODUCT
        |--------------------------------------------------------------------------
        */

        const productIdInput =
            document.getElementById(
                'shortageProductId'
            );


        if (productIdInput) {

            productIdInput.value =
                currentShortageData?.productId ||
                '';
        }


        const rowIndexInput =
            document.getElementById(
                'shortageRowIndex'
            );


        if (rowIndexInput) {

            rowIndexInput.value =
                currentShortageData?.rowIndex ??
                '';
        }


        setText(
            'shortageProductName',
            productName
        );


        setText(
            'shortageRequired',
            money(required)
        );


        setText(
            'shortageShopStock',
            money(shopStock)
        );


        setText(
            'shortageQuantity',
            money(shortage)
        );


        /*
        |--------------------------------------------------------------------------
        | RESET MESSAGES
        |--------------------------------------------------------------------------
        */

        if (godownStockList) {

            godownStockList.innerHTML =
                '';
        }


        noGodownStockMessage
            ?.classList
            .add('d-none');


        cannotFulfillMessage
            ?.classList
            .add('d-none');


        transferButton
            ?.classList
            .remove('d-none');


        /*
        |--------------------------------------------------------------------------
        | GODOWN STOCK
        |--------------------------------------------------------------------------
        */

        const godowns =
            Array.isArray(data.godowns)
                ? data.godowns
                : [];


        if (
            godowns.length === 0
        ) {

            noGodownStockMessage
                ?.classList
                .remove('d-none');


            transferButton
                ?.classList
                .add('d-none');

        } else {

            godowns.forEach(
                function (godown) {

                    const available =
                        parseFloat(
                            godown.available
                        ) || 0;


                    const transferQty =
                        parseFloat(
                            godown.transfer_quantity
                        ) ||
                        Math.min(
                            available,
                            shortage
                        );


                    const wrapper =
                        document.createElement(
                            'div'
                        );


                    wrapper.className =
                        'd-flex justify-content-between align-items-center border rounded p-2 mb-2';


                    wrapper.innerHTML = `

                        <div>

                            <strong>
                                ${escapeHtml(
                        godown.name ??
                        godown.godown_name ??
                        'Godown'
                    )}
                            </strong>

                            <div class="small text-muted">

                                Available:
                                ${money(available)}

                            </div>

                        </div>


                        <span class="badge text-bg-primary">

                            Transfer
                            ${money(transferQty)}

                        </span>

                    `;


                    godownStockList?.appendChild(
                        wrapper
                    );
                }
            );


            /*
            |--------------------------------------------------------------------------
            | CAN FULFILL
            |--------------------------------------------------------------------------
            */

            const canFulfill =
                data.can_fulfill !== false;


            if (!canFulfill) {

                cannotFulfillMessage
                    ?.classList
                    .remove('d-none');


                transferButton
                    ?.classList
                    .add('d-none');

            } else {

                transferButton
                    ?.classList
                    .remove('d-none');
            }
        }


        /*
        |--------------------------------------------------------------------------
        | SHOW
        |--------------------------------------------------------------------------
        */

        stockModal.show();
    }


    /*
    |--------------------------------------------------------------------------
    | WAIT FOR MODAL RESULT
    |--------------------------------------------------------------------------
    */

    function waitForModalResult() {

        return new Promise(
            function (resolve) {

                modalResolver =
                    resolve;
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | TRANSFER STOCK
    |--------------------------------------------------------------------------
    */

    async function transferStockAndContinue() {

        if (
            !currentShortageData ||
            transferInProgress
        ) {

            return;
        }


        if (
            !currentShortageData.godowns ||
            currentShortageData.godowns.length === 0
        ) {

            showNotice(
                'No stock is available in the godowns.',
                'danger'
            );

            return;
        }


        transferInProgress =
            true;


        if (transferButton) {

            transferButton.disabled =
                true;

            transferButton.innerHTML = `

                <span
                    class="spinner-border spinner-border-sm me-1">
                </span>

                Transferring...

            `;
        }


        try {

            let remaining =
                parseFloat(
                    currentShortageData.shortage
                ) || 0;


            /*
            |--------------------------------------------------------------------------
            | TRANSFER FROM GODOWNS
            |--------------------------------------------------------------------------
            */

            for (
                const godown
                of currentShortageData.godowns
            ) {

                if (
                    remaining <= 0
                ) {
                    break;
                }


                const available =
                    parseFloat(
                        godown.available
                    ) || 0;


                if (
                    available <= 0
                ) {
                    continue;
                }


                const transferQty =
                    Math.min(
                        available,
                        remaining
                    );


                if (
                    transferQty <= 0
                ) {
                    continue;
                }


                /*
                |--------------------------------------------------------------------------
                | IMPORTANT
                |--------------------------------------------------------------------------
                | quickTransfer controller expects:
                |
                | company_id
                | from_godown_id
                | to_store_id
                | product_id
                | quantity
                |
                */

                const response =
                    await fetch(
                        quickTransferUrl,
                        {
                            method: 'POST',

                            headers: {

                                'Content-Type':
                                    'application/json',

                                'Accept':
                                    'application/json',

                                'X-CSRF-TOKEN':
                                    csrfToken,

                                'X-Requested-With':
                                    'XMLHttpRequest'
                            },

                            body:
                                JSON.stringify({

                                    company_id:
                                        currentShortageData.companyId,

                                    from_godown_id:
                                        godown.id,

                                    to_store_id:
                                        currentShortageData.storeId,

                                    product_id:
                                        currentShortageData.productId,

                                    quantity:
                                        transferQty
                                })
                        }
                    );


                if (
                    response.status === 419
                ) {

                    throw new Error(
                        'Session expired or CSRF token is invalid. Please refresh the page.'
                    );
                }


                let data = {};

                try {

                    data =
                        await response.json();

                } catch (e) {

                    throw new Error(
                        `Stock transfer failed. HTTP ${response.status}`
                    );
                }


                if (
                    !response.ok ||
                    !data.success
                ) {

                    throw new Error(
                        data.message ||
                        'Stock transfer failed.'
                    );
                }


                remaining -=
                    transferQty;
            }


            /*
            |--------------------------------------------------------------------------
            | CHECK TRANSFER COMPLETION
            |--------------------------------------------------------------------------
            */

            if (
                remaining > 0.000001
            ) {

                throw new Error(
                    `Only part of the required stock could be transferred. Remaining shortage: ${money(remaining)}`
                );
            }


            /*
            |--------------------------------------------------------------------------
            | CLOSE MODAL
            |--------------------------------------------------------------------------
            */

            if (stockModal) {

                stockModal.hide();
            }


            /*
            |--------------------------------------------------------------------------
            | UPDATE ROW STOCK
            |--------------------------------------------------------------------------
            */

            if (
                currentShortageData.row
            ) {

                const newShopStock =
                    (
                        parseFloat(
                            currentShortageData.shopStock
                        ) || 0
                    ) +
                    (
                        parseFloat(
                            currentShortageData.shortage
                        ) || 0
                    );


                updateRowStock(
                    currentShortageData.row,
                    newShopStock
                );


                updateTransferButton(
                    currentShortageData.row,
                    0
                );
            }


            /*
            |--------------------------------------------------------------------------
            | RESOLVE
            |--------------------------------------------------------------------------
            */

            if (
                typeof modalResolver ===
                'function'
            ) {

                const resolve =
                    modalResolver;

                modalResolver =
                    null;


                resolve({

                    success: true,

                    transferred: true
                });
            }


            /*
            |--------------------------------------------------------------------------
            | SUCCESS
            |--------------------------------------------------------------------------
            */

            showStockSuccessMessage(
                'Stock transferred to Shop successfully. You can continue the sale.'
            );


            /*
            |--------------------------------------------------------------------------
            | IMPORTANT:
            | Re-check server stock.
            |--------------------------------------------------------------------------
            */

            if (
                currentShortageData.row
            ) {

                const row =
                    currentShortageData.row;


                /*
                | Clear current shortage before
                | server recheck.
                */

                currentShortageData =
                    null;


                await checkSalesStock(
                    row,
                    null,
                    false
                );
            } else {

                currentShortageData =
                    null;
            }


        } catch (error) {

            console.error(
                'Stock transfer error:',
                error
            );


            showNotice(
                error.message ||
                'Unable to transfer stock.',
                'danger'
            );

        } finally {

            transferInProgress =
                false;


            if (transferButton) {

                transferButton.disabled =
                    false;

                transferButton.innerHTML = `

                    <i class="fas fa-exchange-alt me-1"></i>

                    Transfer & Continue

                `;
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | TRANSFER BUTTON
    |--------------------------------------------------------------------------
    */

    transferButton?.addEventListener(
        'click',
        function () {

            transferStockAndContinue();
        }
    );


    /*
    |--------------------------------------------------------------------------
    | ROW TRANSFER BUTTON
    |--------------------------------------------------------------------------
    */

    itemsBody.addEventListener(
        'click',
        function (event) {

            const button =
                event.target.closest(
                    '.transferRowStock, .transfer-stock-btn'
                );


            if (!button) {
                return;
            }


            const row =
                button.closest(
                    '.sale-item-row'
                );


            if (!row) {
                return;
            }


            checkSalesStock(
                row,
                null,
                false
            );
        }
    );


    /*
    |--------------------------------------------------------------------------
    | MODAL CANCEL / CLOSE
    |--------------------------------------------------------------------------
    */

    if (stockModalElement) {

        stockModalElement.addEventListener(
            'hidden.bs.modal',
            function () {

                /*
                | If user closes modal without
                | transferring, resolve as cancelled.
                */

                if (
                    typeof modalResolver ===
                    'function'
                ) {

                    const resolve =
                        modalResolver;

                    modalResolver =
                        null;


                    resolve({

                        success: false,

                        cancelled: true
                    });
                }


                currentShortageData =
                    null;
            }
        );
    }


    /*
    |--------------------------------------------------------------------------
    | STOCK SUCCESS MESSAGE
    |--------------------------------------------------------------------------
    */

    function showStockSuccessMessage(
        message
    ) {

        showNotice(
            message,
            'success'
        );
    }


    /*
    |--------------------------------------------------------------------------
    | REMOVE PRODUCT
    |--------------------------------------------------------------------------
    */

    itemsBody.addEventListener(
        'click',
        function (event) {

            const button =
                event.target.closest(
                    '.removeRow'
                );


            if (!button) {
                return;
            }


            const row =
                button.closest(
                    '.sale-item-row'
                );


            if (!row) {
                return;
            }


            const timer =
                quantityTimers.get(
                    row
                );


            if (timer) {

                clearTimeout(
                    timer
                );
            }


            row.remove();


            calculateTotals();

            updateEmptyState();


            if (searchInput) {

                searchInput.focus();
            }
        }
    );


    /*
    |--------------------------------------------------------------------------
    | PAYMENT
    |--------------------------------------------------------------------------
    */

    const paidAmount =
        document.getElementById(
            'paid_amount'
        );


    paidAmount?.addEventListener(
        'input',
        calculateBalance
    );


    paidAmount?.addEventListener(
        'blur',
        function () {

            const grandTotal =
                parseFloat(
                    document.getElementById(
                        'grand_total'
                    )?.value
                ) || 0;


            let paid =
                parseFloat(
                    this.value
                ) || 0;


            if (
                paid > grandTotal
            ) {

                this.value =
                    money(grandTotal);
            }


            calculateBalance();
        }
    );


    /*
    |--------------------------------------------------------------------------
    | CLOSE SEARCH WHEN CLICKING OUTSIDE
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'click',
        function (event) {

            if (
                searchResult &&
                searchInput &&
                !searchResult.contains(
                    event.target
                ) &&
                !searchInput.contains(
                    event.target
                )
            ) {

                hideResults();
            }
        }
    );


    /*
    |--------------------------------------------------------------------------
    | BASIC FORM VALIDATION
    |--------------------------------------------------------------------------
    */

    function validateBasicForm() {

        const rows =
            itemsBody.querySelectorAll(
                '.sale-item-row'
            );


        if (
            rows.length === 0
        ) {

            showNotice(
                'Please add at least one product.',
                'warning'
            );

            searchInput?.focus();

            return false;
        }


        if (
            !companySelect?.value
        ) {

            showNotice(
                'Please select a company.',
                'warning'
            );

            companySelect?.focus();

            return false;
        }


        if (
            !storeSelect?.value
        ) {

            showNotice(
                'Please select a store.',
                'warning'
            );

            storeSelect?.focus();

            return false;
        }


        if (
            !customerSelect?.value
        ) {

            showNotice(
                'Please select a customer.',
                'warning'
            );

            customerSelect?.focus();

            return false;
        }


        /*
        |--------------------------------------------------------------------------
        | ROW VALIDATION
        |--------------------------------------------------------------------------
        */

        for (
            const row of rows
        ) {

            const qty =
                parseFloat(
                    row.querySelector(
                        '.qty'
                    )?.value
                ) || 0;


            const freeQty =
                parseFloat(
                    row.querySelector(
                        '.free_qty'
                    )?.value
                ) || 0;


            if (
                qty <= 0
            ) {

                showNotice(
                    'Quantity must be greater than zero.',
                    'warning'
                );

                row.querySelector(
                    '.qty'
                )?.focus();

                return false;
            }


            if (
                freeQty < 0
            ) {

                showNotice(
                    'Free quantity cannot be negative.',
                    'warning'
                );

                row.querySelector(
                    '.free_qty'
                )?.focus();

                return false;
            }
        }


        /*
        |--------------------------------------------------------------------------
        | PAYMENT
        |--------------------------------------------------------------------------
        */

        const grandTotal =
            parseFloat(
                document.getElementById(
                    'grand_total'
                )?.value
            ) || 0;


        const paid =
            parseFloat(
                document.getElementById(
                    'paid_amount'
                )?.value
            ) || 0;


        if (
            paid > grandTotal
        ) {

            showNotice(
                'Paid amount cannot be greater than Grand Total.',
                'warning'
            );

            paidAmount?.focus();

            return false;
        }


        return true;
    }


    /*
    |--------------------------------------------------------------------------
    | FINAL STOCK CHECK
    |--------------------------------------------------------------------------
    */

    async function checkAllStockBeforeSubmit() {

        const rows =
            Array.from(
                itemsBody.querySelectorAll(
                    '.sale-item-row'
                )
            );


        for (
            let index = 0;
            index < rows.length;
            index++
        ) {

            const row =
                rows[index];


            const result =
                await checkSalesStock(
                    row,
                    index,
                    true
                );


            if (
                !result.success
            ) {

                return false;
            }
        }


        return true;
    }


    /*
    |--------------------------------------------------------------------------
    | SUBMIT
    |--------------------------------------------------------------------------
    */

    form.addEventListener(
        'submit',
        async function (event) {

            event.preventDefault();


            if (
                submitting ||
                transferInProgress
            ) {

                return;
            }


            if (
                !validateBasicForm()
            ) {

                return;
            }


            /*
            |--------------------------------------------------------------------------
            | BUTTON
            |--------------------------------------------------------------------------
            */

            if (saveSaleBtn) {

                saveSaleBtn.disabled =
                    true;

                saveSaleBtn.innerHTML = `

                    <span
                        class="spinner-border spinner-border-sm me-1">
                    </span>

                    Checking Stock...

                `;
            }


            /*
            |--------------------------------------------------------------------------
            | FINAL STOCK CHECK
            |--------------------------------------------------------------------------
            */

            const stockOkay =
                await checkAllStockBeforeSubmit();


            if (!stockOkay) {

                if (saveSaleBtn) {

                    saveSaleBtn.disabled =
                        false;

                    saveSaleBtn.innerHTML = `

                        <i class="fas fa-save me-1"></i>

                        ${isEdit
                            ? 'Update Sales Invoice'
                            : 'Save Sales Invoice'
                        }

                    `;
                }


                return;
            }


            /*
            |--------------------------------------------------------------------------
            | CALCULATE FINAL TOTALS
            |--------------------------------------------------------------------------
            */

            calculateTotals();


            /*
            |--------------------------------------------------------------------------
            | SUBMIT
            |--------------------------------------------------------------------------
            */

            submitting =
                true;


            if (saveSaleBtn) {

                saveSaleBtn.innerHTML = `

                    <span
                        class="spinner-border spinner-border-sm me-1">
                    </span>

                    Saving...

                `;
            }


            /*
            |--------------------------------------------------------------------------
            | NATIVE SUBMIT
            |--------------------------------------------------------------------------
            */

            form.submit();
        }
    );


    /*
    |--------------------------------------------------------------------------
    | INITIALIZE EXISTING EDIT ROWS
    |--------------------------------------------------------------------------
    */

    itemsBody
        .querySelectorAll(
            '.sale-item-row'
        )
        .forEach(
            function (row) {

                calculateRow(
                    row
                );


                /*
                | Always Shop
                */

                const locationCell =
                    row.querySelector(
                        '.sale-location'
                    );


                if (locationCell) {

                    locationCell.innerHTML = `

                        <span class="badge text-bg-primary">

                            <i class="fas fa-store me-1"></i>

                            Shop

                        </span>

                    `;
                }


                /*
                | Check current shop stock
                */

                if (
                    companySelect?.value &&
                    storeSelect?.value
                ) {

                    checkRowStockAfterChange(
                        row
                    );
                }
            }
        );


    /*
    |--------------------------------------------------------------------------
    | INITIAL TOTALS
    |--------------------------------------------------------------------------
    */

    calculateTotals();

    updateEmptyState();


    /*
    |--------------------------------------------------------------------------
    | INITIAL SEARCH STATE
    |--------------------------------------------------------------------------
    */

    updateSearchAvailability();


    /*
    |--------------------------------------------------------------------------
    | SEARCH FOCUS
    |--------------------------------------------------------------------------
    */

    if (
        searchInput &&
        !searchInput.disabled
    ) {

        searchInput.focus();
    }

});