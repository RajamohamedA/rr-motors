document.addEventListener('DOMContentLoaded', function () {

    /*
    |--------------------------------------------------------------------------
    | Transaction Configuration
    |--------------------------------------------------------------------------
    */

    const transactionTypeElement =
        document.getElementById('transactionType');

    const itemContainer =
        document.getElementById('purchaseItems');

    const productSearch =
        document.getElementById('product_search');

    const searchResult =
        document.getElementById('searchResult');

    const productSearchUrlElement =
        document.getElementById('productSearchUrl');

    const companySelect =
        document.getElementById('company_id');

    const storeSelect =
        document.getElementById('store_id');

    const supplierSelect =
        document.getElementById('supplier_id');

    const grandTotalInput =
        document.getElementById('grand_total');


    /*
    |--------------------------------------------------------------------------
    | Stop If Transaction Form Is Not Present
    |--------------------------------------------------------------------------
    */

    if (!itemContainer) {
        return;
    }


    /*
    |--------------------------------------------------------------------------
    | Transaction Type
    |--------------------------------------------------------------------------
    */

    const transactionType =
        transactionTypeElement
            ? transactionTypeElement.value
            : 'purchase';


    /*
    |--------------------------------------------------------------------------
    | Product Search URL
    |--------------------------------------------------------------------------
    */

    const productSearchUrl =
        productSearchUrlElement
            ? productSearchUrlElement.value
            : '';


    /*
    |--------------------------------------------------------------------------
    | Godown Data
    |--------------------------------------------------------------------------
    */

    let godowns = [];


    /*
    |--------------------------------------------------------------------------
    | Helper - Escape HTML
    |--------------------------------------------------------------------------
    */

    function escapeHtml(value) {

        if (value === null || value === undefined) {
            return '';
        }

        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');

    }


    /*
    |--------------------------------------------------------------------------
    | Get Godown Options
    |--------------------------------------------------------------------------
    */

    function getGodownOptions(selectedGodownId = '') {

        let html = `
            <option value="">
                Shop / No Godown
            </option>
        `;

        godowns.forEach(function (godown) {

            const selected =
                String(selectedGodownId) ===
                    String(godown.id)
                    ? 'selected'
                    : '';

            html += `
                <option
                    value="${godown.id}"
                    ${selected}>

                    ${escapeHtml(godown.godown_name)}

                </option>
            `;

        });

        return html;
    }


    /*
    |--------------------------------------------------------------------------
    | Refresh All Godown Dropdowns
    |--------------------------------------------------------------------------
    */

    function refreshAllGodownDropdowns(clearExisting = false) {

        const selects =
            itemContainer.querySelectorAll('.godown-item');


        selects.forEach(function (select) {

            let selectedGodownId = '';


            /*
            |--------------------------------------------------------------------------
            | Existing Edit Value
            |--------------------------------------------------------------------------
            */

            if (!clearExisting) {

                selectedGodownId =
                    select.dataset.existingGodown || '';

            }


            /*
            |--------------------------------------------------------------------------
            | Rebuild Options
            |--------------------------------------------------------------------------
            */

            select.innerHTML =
                getGodownOptions(selectedGodownId);


            /*
            |--------------------------------------------------------------------------
            | Remove Temporary Existing Value
            |--------------------------------------------------------------------------
            */

            if (clearExisting) {

                delete select.dataset.existingGodown;

            }

        });

    }


    /*
    |--------------------------------------------------------------------------
    | Set Godown Dropdowns Loading
    |--------------------------------------------------------------------------
    */

    function setGodownDropdownsLoading() {

        itemContainer
            .querySelectorAll('.godown-item')
            .forEach(function (select) {

                select.innerHTML = `
                    <option value="">
                        Loading godowns...
                    </option>
                `;

            });

    }


    /*
    |--------------------------------------------------------------------------
    | Load Godowns
    |--------------------------------------------------------------------------
    */

    function loadGodowns(storeId, preserveExisting = false) {

        /*
        |--------------------------------------------------------------------------
        | No Store
        |--------------------------------------------------------------------------
        */

        if (!storeId) {

            godowns = [];

            refreshAllGodownDropdowns(!preserveExisting);

            return;

        }


        /*
        |--------------------------------------------------------------------------
        | Loading
        |--------------------------------------------------------------------------
        */

        setGodownDropdownsLoading();


        /*
        |--------------------------------------------------------------------------
        | AJAX
        |--------------------------------------------------------------------------
        */

        fetch(`/purchases/godowns/${storeId}`, {
            headers: {
                'Accept': 'application/json'
            }
        })

            .then(function (response) {

                if (!response.ok) {
                    throw new Error(
                        'Unable to load godowns'
                    );
                }

                return response.json();

            })

            .then(function (data) {

                godowns =
                    Array.isArray(data)
                        ? data
                        : [];


                /*
                |--------------------------------------------------------------------------
                | Preserve Existing Godown On Edit
                |--------------------------------------------------------------------------
                */

                refreshAllGodownDropdowns(
                    !preserveExisting
                );

            })

            .catch(function (error) {

                console.error(
                    'Godown loading error:',
                    error
                );

                godowns = [];

                refreshAllGodownDropdowns(
                    !preserveExisting
                );

            });

    }


    /*
    |--------------------------------------------------------------------------
    | Company → Stores
    |--------------------------------------------------------------------------
    */

    if (companySelect) {

        companySelect.addEventListener(
            'change',
            function () {

                const companyId =
                    this.value;

                /*
                |--------------------------------------------------------------------------
                | Clear Purchase Items when Company changes
                |--------------------------------------------------------------------------
                */

                const purchaseItems = document.getElementById('purchaseItems');

                if (purchaseItems) {
                    purchaseItems.innerHTML = '';
                }

                /*
                |--------------------------------------------------------------------------
                | Reset Grand Total
                |--------------------------------------------------------------------------
                */

                const grandTotal = document.getElementById('grand_total');

                if (grandTotal) {
                    grandTotal.value = '0.00';
                }


                /*
                |--------------------------------------------------------------------------
                | Clear Store
                |--------------------------------------------------------------------------
                */

                if (storeSelect) {

                    storeSelect.innerHTML = `
                        <option value="">
                            Loading stores...
                        </option>
                    `;

                }


                /*
                |--------------------------------------------------------------------------
                | Clear Supplier
                |--------------------------------------------------------------------------
                */

                if (supplierSelect) {

                    supplierSelect.innerHTML = `
                        <option value="">
                            Loading suppliers...
                        </option>
                    `;

                }


                /*
                |--------------------------------------------------------------------------
                | IMPORTANT
                |--------------------------------------------------------------------------
                | Company change must clear godowns.
                | Godowns are loaded ONLY after Store selection.
                |--------------------------------------------------------------------------
                */

                godowns = [];

                refreshAllGodownDropdowns(true);


                /*
                |--------------------------------------------------------------------------
                | No Company
                |--------------------------------------------------------------------------
                */

                if (!companyId) {

                    if (storeSelect) {

                        storeSelect.innerHTML = `
                            <option value="">
                                Select Store
                            </option>
                        `;

                    }

                    if (supplierSelect) {

                        supplierSelect.innerHTML = `
                            <option value="">
                                Select Supplier
                            </option>
                        `;

                    }

                    return;

                }


                /*
                |--------------------------------------------------------------------------
                | Load Stores
                |--------------------------------------------------------------------------
                */

                fetch(
                    `/purchases/stores/${companyId}`,
                    {
                        headers: {
                            'Accept': 'application/json'
                        }
                    }
                )

                    .then(function (response) {

                        if (!response.ok) {
                            throw new Error(
                                'Unable to load stores'
                            );
                        }

                        return response.json();

                    })

                    .then(function (stores) {

                        if (!storeSelect) {
                            return;
                        }

                        storeSelect.innerHTML = `
                            <option value="">
                                Select Store
                            </option>
                        `;


                        stores.forEach(
                            function (store) {

                                const option =
                                    document.createElement(
                                        'option'
                                    );

                                option.value =
                                    store.id;

                                option.textContent =
                                    store.store_name;

                                storeSelect.appendChild(
                                    option
                                );

                            }
                        );

                    })

                    .catch(function (error) {

                        console.error(error);

                        if (storeSelect) {

                            storeSelect.innerHTML = `
                                <option value="">
                                    Unable to load stores
                                </option>
                            `;

                        }

                    });


                /*
                |--------------------------------------------------------------------------
                | Load Suppliers
                |--------------------------------------------------------------------------
                */

                fetch(
                    `/purchases/suppliers/${companyId}`,
                    {
                        headers: {
                            'Accept': 'application/json'
                        }
                    }
                )

                    .then(function (response) {

                        if (!response.ok) {
                            throw new Error(
                                'Unable to load suppliers'
                            );
                        }

                        return response.json();

                    })

                    .then(function (suppliers) {

                        if (!supplierSelect) {
                            return;
                        }

                        supplierSelect.innerHTML = `
                            <option value="">
                                Select Supplier
                            </option>
                        `;


                        suppliers.forEach(
                            function (supplier) {

                                const option =
                                    document.createElement(
                                        'option'
                                    );

                                option.value =
                                    supplier.id;

                                option.textContent =
                                    supplier.supplier_name;

                                supplierSelect.appendChild(
                                    option
                                );

                            }
                        );

                    })

                    .catch(function (error) {

                        console.error(error);

                        if (supplierSelect) {

                            supplierSelect.innerHTML = `
                                <option value="">
                                    Unable to load suppliers
                                </option>
                            `;

                        }

                    });

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Store → Godowns
    |--------------------------------------------------------------------------
    */

    if (storeSelect) {

        storeSelect.addEventListener(
            'change',
            function () {

                const storeId =
                    this.value;


                /*
                |--------------------------------------------------------------------------
                | Store Changed
                |--------------------------------------------------------------------------
                | Existing godown values are no longer valid.
                |--------------------------------------------------------------------------
                */

                itemContainer
                    .querySelectorAll('.godown-item')
                    .forEach(function (select) {

                        delete select.dataset.existingGodown;

                    });


                /*
                |--------------------------------------------------------------------------
                | Load New Store Godowns
                |--------------------------------------------------------------------------
                */

                loadGodowns(
                    storeId,
                    false
                );

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Product Search
    |--------------------------------------------------------------------------
    */

    let searchTimer = null;


    if (productSearch) {

        productSearch.addEventListener(
            'input',
            function () {

                const companyId = document.getElementById('company_id').value;

                if (!companyId) {
                    searchResult.innerHTML =
                        '<div class="list-group-item text-danger">' +
                        'Please select company first.' +
                        '</div>';

                    searchResult.style.display = 'block';

                    return;
                }

                const keyword =
                    this.value.trim();


                /*
                |--------------------------------------------------------------------------
                | Minimum 2 Characters
                |--------------------------------------------------------------------------
                */

                if (keyword.length < 2) {

                    clearTimeout(searchTimer);

                    searchResult.innerHTML = '';

                    searchResult.style.display =
                        'none';

                    return;

                }


                /*
                |--------------------------------------------------------------------------
                | Delay
                |--------------------------------------------------------------------------
                */

                clearTimeout(searchTimer);


                searchTimer =
                    setTimeout(
                        function () {

                            searchProducts(
                                keyword, companyId
                            );

                        },
                        300
                    );

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Search Products
    |--------------------------------------------------------------------------
    */

    function searchProducts(keyword, companyId) {

        if (!productSearchUrl) {
            return;
        }


        searchResult.style.display =
            'block';


        searchResult.innerHTML = `
            <div class="list-group-item text-center">
                <i class="fas fa-spinner fa-spin"></i>
                Searching...
            </div>
        `;


        const url =
            productSearchUrl +
            '?search=' +
            encodeURIComponent(keyword) + '&company_id=' + encodeURIComponent(companyId);


        fetch(url, {
            headers: {
                'Accept': 'application/json'
            }
        })

            .then(function (response) {

                if (!response.ok) {
                    throw new Error(
                        'Product search failed'
                    );
                }

                return response.json();

            })

            .then(function (products) {

                searchResult.innerHTML = '';


                if (
                    !Array.isArray(products) ||
                    products.length === 0
                ) {

                    searchResult.innerHTML = `
                        <div class="list-group-item text-danger">
                            No product found
                        </div>
                    `;

                    return;

                }


                products.forEach(
                    function (product) {

                        const item =
                            document.createElement(
                                'button'
                            );

                        item.type = 'button';

                        item.className =
                            'list-group-item list-group-item-action';


                        const productCode =
                            product.product_code ?? '';

                        const productName =
                            product.product_name ?? '';

                        const barcode =
                            product.barcode ?? '';

                        let price;


                        if (
                            transactionType ===
                            'purchase'
                        ) {

                            price =
                                parseFloat(
                                    product.purchase_price ??
                                    product.cost_price ??
                                    product.price ??
                                    0
                                );

                        } else {

                            price =
                                parseFloat(
                                    product.selling_price ??
                                    product.price ??
                                    0
                                );

                        }


                        const gst =
                            parseFloat(
                                product.gst_percent ??
                                product.gst ??
                                0
                            );


                        const stock =
                            parseFloat(
                                product.current_stock ??
                                0
                            );


                        item.innerHTML = `

                            <div class="d-flex justify-content-between">

                                <div>

                                    <strong>
                                        ${escapeHtml(productCode)}
                                    </strong>

                                    -
                                    ${escapeHtml(productName)}

                                    ${barcode
                                ? `
                                            <small class="text-muted ms-2">
                                                Barcode:
                                                ${escapeHtml(barcode)}
                                            </small>
                                            `
                                : ''
                            }

                                    <br>

                                    <small>

                                        Stock:
                                        ${stock}

                                        |
                                        Price:
                                        ${price.toFixed(2)}

                                    </small>

                                </div>

                                <span class="badge bg-primary align-self-center">
                                    Add
                                </span>

                            </div>

                        `;


                        item.addEventListener(
                            'click',
                            function () {

                                addProductRow(
                                    product
                                );

                                productSearch.value =
                                    '';

                                searchResult.innerHTML =
                                    '';

                                searchResult.style.display =
                                    'none';

                            }
                        );


                        searchResult.appendChild(
                            item
                        );

                    }
                );

            })

            .catch(function (error) {

                console.error(error);

                searchResult.innerHTML = `
                    <div class="list-group-item text-danger">
                        Unable to search products
                    </div>
                `;

            });

    }


    /*
    |--------------------------------------------------------------------------
    | Add Product Row
    |--------------------------------------------------------------------------
    */

    function addProductRow(product) {

        const productId =
            product.id;


        /*
        |--------------------------------------------------------------------------
        | Duplicate Product
        |--------------------------------------------------------------------------
        */

        const existingProduct =
            itemContainer.querySelector(
                `input[name="product_id[]"][value="${productId}"]`
            );


        if (existingProduct) {

            const row =
                existingProduct.closest('tr');

            const qtyInput =
                row.querySelector('.qty');


            qtyInput.value =
                (
                    parseFloat(
                        qtyInput.value || 0
                    ) + 1
                );


            calculateRow(row);

            return;

        }


        /*
        |--------------------------------------------------------------------------
        | Product Data
        |--------------------------------------------------------------------------
        */

        const productCode =
            product.product_code ?? '';

        const productName =
            product.product_name ?? '';


        const purchasePrice =
            parseFloat(
                product.purchase_price ??
                product.cost_price ??
                product.price ??
                0
            );


        const sellingPrice =
            parseFloat(
                product.selling_price ??
                product.price ??
                0
            );


        const price =
            transactionType === 'purchase'
                ? purchasePrice
                : sellingPrice;


        const gst =
            parseFloat(
                product.gst_percent ??
                product.gst ??
                0
            );


        const stock =
            parseFloat(
                product.current_stock ??
                0
            );


        /*
        |--------------------------------------------------------------------------
        | Create Row
        |--------------------------------------------------------------------------
        */

        const row =
            document.createElement('tr');


        row.innerHTML = `

            <!-- Product Code -->

            <td>

                ${escapeHtml(productCode)}

                <input
                    type="hidden"
                    name="product_id[]"
                    value="${productId}">

            </td>


            <!-- Product -->

            <td>

                ${escapeHtml(productName)}

                ${transactionType === 'sale'
                ? `
                            <br>
                            <small class="text-success">
                                Stock:
                                <span class="stock">
                                    ${stock}
                                </span>
                            </small>
                        `
                : ''
            }

            </td>


            <!-- Godown -->

            <td>

                <select
                    name="godown_id[]"
                    class="form-select godown-item">

                    ${getGodownOptions('')}

                </select>

            </td>


            <!-- Quantity -->

            <td>

                <input
                    type="number"
                    class="form-control qty"
                    name="qty[]"
                    value="1"
                    min="0.001"
                    step="0.001"
                    required>

            </td>


            <!-- Price -->

            <td>

                <input
                    type="number"
                    class="form-control price"
                    name="price[]"
                    value="${price.toFixed(2)}"
                    min="0"
                    step="0.01"
                    required>

            </td>


            <!-- GST -->

            <td>

                <input
                    type="number"
                    class="form-control gst"
                    name="gst[]"
                    value="${gst}"
                    min="0"
                    step="0.01">

            </td>


            <!-- Amount -->

            <td>

                <input
                    type="text"
                    class="form-control amount"
                    readonly
                    value="0.00">

            </td>


            <!-- Remove -->

            <td>

                <button
                    type="button"
                    class="btn btn-danger btn-sm removeRow">

                    <i class="fas fa-trash"></i>

                </button>

            </td>

        `;


        itemContainer.appendChild(row);


        calculateRow(row);

    }


    /*
    |--------------------------------------------------------------------------
    | Qty / Price / GST
    |--------------------------------------------------------------------------
    */

    itemContainer.addEventListener(
        'input',
        function (event) {

            if (
                event.target.classList.contains('qty') ||
                event.target.classList.contains('price') ||
                event.target.classList.contains('gst')
            ) {

                const row =
                    event.target.closest('tr');

                calculateRow(row);

            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Sales Stock Validation
    |--------------------------------------------------------------------------
    */

    function validateSaleStock(row) {

        if (transactionType !== 'sale') {
            return;
        }


        const qtyInput =
            row.querySelector('.qty');


        const stockElement =
            row.querySelector('.stock');


        if (!qtyInput || !stockElement) {
            return;
        }


        let qty =
            parseFloat(
                qtyInput.value || 0
            );


        const stock =
            parseFloat(
                stockElement.textContent || 0
            );


        if (qty > stock) {

            alert(
                'Only ' +
                stock +
                ' stock available.'
            );


            qty =
                stock;


            qtyInput.value =
                stock;

        }

    }


    /*
    |--------------------------------------------------------------------------
    | Calculate Row
    |--------------------------------------------------------------------------
    */

    function calculateRow(row) {

        if (!row) {
            return;
        }


        validateSaleStock(row);


        const qty =
            parseFloat(
                row.querySelector('.qty')?.value || 0
            );


        const price =
            parseFloat(
                row.querySelector('.price')?.value || 0
            );


        const gst =
            parseFloat(
                row.querySelector('.gst')?.value || 0
            );


        const basicAmount =
            qty * price;


        const gstAmount =
            basicAmount * gst / 100;


        const lineTotal =
            basicAmount + gstAmount;


        const amountInput =
            row.querySelector('.amount');


        if (amountInput) {

            amountInput.value =
                lineTotal.toFixed(2);

        }


        calculateGrandTotal();

    }


    /*
    |--------------------------------------------------------------------------
    | Remove Product Row
    |--------------------------------------------------------------------------
    */

    itemContainer.addEventListener(
        'click',
        function (event) {

            const button =
                event.target.closest('.removeRow');


            if (!button) {
                return;
            }


            const row =
                button.closest('tr');


            if (
                confirm(
                    'Remove this product?'
                )
            ) {

                row.remove();

                calculateGrandTotal();

            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Grand Total
    |--------------------------------------------------------------------------
    */

    function calculateGrandTotal() {

        let total = 0;


        itemContainer
            .querySelectorAll('.amount')
            .forEach(function (input) {

                total +=
                    parseFloat(
                        input.value || 0
                    );

            });


        if (grandTotalInput) {

            grandTotalInput.value =
                total.toFixed(2);

        }


        /*
        |--------------------------------------------------------------------------
        | Sales Payment
        |--------------------------------------------------------------------------
        */

        calculateBalance();

    }


    /*
    |--------------------------------------------------------------------------
    | Balance
    |--------------------------------------------------------------------------
    */

    function calculateBalance() {

        const paidInput =
            document.getElementById(
                'paid_amount'
            );


        const balanceInput =
            document.getElementById(
                'balance_amount'
            );


        if (!paidInput || !balanceInput) {
            return;
        }


        const grandTotal =
            parseFloat(
                grandTotalInput?.value || 0
            );


        const paid =
            parseFloat(
                paidInput.value || 0
            );


        const balance =
            grandTotal - paid;


        balanceInput.value =
            balance.toFixed(2);

    }


    /*
    |--------------------------------------------------------------------------
    | Paid Amount
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'input',
        function (event) {

            if (
                event.target.id ===
                'paid_amount'
            ) {

                calculateBalance();

            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Close Search Result
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'click',
        function (event) {

            if (
                productSearch &&
                searchResult &&
                !productSearch.contains(event.target) &&
                !searchResult.contains(event.target)
            ) {

                searchResult.style.display =
                    'none';

            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | ESC - Close Search
    |--------------------------------------------------------------------------
    */

    document.addEventListener(
        'keydown',
        function (event) {

            if (
                event.key === 'Escape' &&
                searchResult
            ) {

                searchResult.style.display =
                    'none';

            }

        }
    );


    /*
    |--------------------------------------------------------------------------
    | Enter - Don't Submit While Searching
    |--------------------------------------------------------------------------
    */

    if (productSearch) {

        productSearch.addEventListener(
            'keydown',
            function (event) {

                if (event.key === 'Enter') {

                    event.preventDefault();

                }

            }
        );

    }


    /*
    |--------------------------------------------------------------------------
    | INITIAL EDIT PAGE
    |--------------------------------------------------------------------------
    |
    | Blade puts the existing godown_id into:
    |
    | data-existing-godown="ID"
    |
    | We load the current store's godowns and automatically
    | select the matching godown.
    |
    */

    if (storeSelect && storeSelect.value) {

        itemContainer
            .querySelectorAll('.godown-item')
            .forEach(function (select) {

                /*
                |--------------------------------------------------------------------------
                | Save currently selected Blade value
                |--------------------------------------------------------------------------
                */

                if (select.value) {

                    select.dataset.existingGodown =
                        select.value;

                }

            });


        /*
        |--------------------------------------------------------------------------
        | Load Current Store Godowns
        |--------------------------------------------------------------------------
        */

        loadGodowns(
            storeSelect.value,
            true
        );

    } else {

        /*
        |--------------------------------------------------------------------------
        | Create Page Without Store
        |--------------------------------------------------------------------------
        */

        godowns = [];

        refreshAllGodownDropdowns(
            true
        );

    }


    /*
    |--------------------------------------------------------------------------
    | Initial Row Calculations
    |--------------------------------------------------------------------------
    */

    itemContainer
        .querySelectorAll('tr')
        .forEach(function (row) {

            calculateRow(row);

        });


    /*
    |--------------------------------------------------------------------------
    | Initial Grand Total
    |--------------------------------------------------------------------------
    */

    calculateGrandTotal();

});