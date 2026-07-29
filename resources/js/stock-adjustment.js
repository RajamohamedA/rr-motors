document.addEventListener('DOMContentLoaded', function () {

    const store = document.querySelector('[name="store_id"]');
    const product = document.getElementById('product_id');
    const currentStock = document.getElementById('current_stock');
    const unitCost = document.getElementById('unit_cost');

    function loadStock() {

        if (!store.value || !product.value) {
            currentStock.value = 0;
            unitCost.value = '';
            return;
        }

        fetch(`/ajax/product-stock?store_id=${store.value}&product_id=${product.value}`)
            .then(response => response.json())
            .then(data => {
                currentStock.value = data.stock;
                unitCost.value = data.unit_cost;
            })
            .catch(error => {
                console.error(error);
                currentStock.value = 0;
                unitCost.value = '';
            });
    }

    store.addEventListener('change', loadStock);
    product.addEventListener('change', loadStock);

    loadStock();
});