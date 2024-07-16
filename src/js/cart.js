function fetchCartTableData(table, columns) {
    console.log("fetched cart");
    $.ajax({
        url: "process/cart_process.php",
        type: "GET",
        data: {table: table, columns: columns},
        success: function (response) {
            $('#cart-list').html(response);
            console.log(response);
        }
    });
}

fetchCartTableData('product', 'product_id, product_name, product_cost, product_sd, product_quantity, category_name');

function increaseCount(event, element) {
    var input = element.previousElementSibling;
    var max = parseInt(input.getAttribute('max'), 10);
    var value = parseInt(input.value, 10);
    value = isNaN(value) ? 1 : value;
    if (value < max) {
        input.value = value + 1;
    }
}

function decreaseCount(event, element) {
    var input = element.nextElementSibling;
    var value = parseInt(input.value, 10);
    if (value > 1) {
        input.value = value - 1;
    }
}

function updateQuantity(product_id) {
    var hiddenInput = document.getElementById(product_id);
    var parentContainer = hiddenInput.closest('.col-md-3');
    var quantityInput = parentContainer.querySelector('input[type="number"][name="num_item"]');
    var newQuantity = quantityInput.value;
    if (newQuantity < 1 || newQuantity > parseInt(quantityInput.getAttribute('max'), 10) || isNaN(newQuantity)) { 
        alert('Invalid quantity');
        return;
    }

    $.post('process/update_quantity.php', { product_id: product_id, new_quantity: newQuantity }, function(response) {
        alert('Quantity updated successfully!');
        fetchCartTableData('product', 'product_id, product_name, product_cost, product_sd, product_quantity, category_name');
        location.reload();
    });
}

function deleteItem(productId) {
    if (confirm('Are you sure you want to delete this item?')) {
        $.post('process/delete_item.php', { product_id: productId }, function(response) {
            alert(response);
            fetchCartTableData('product', 'product_id, product_name, product_cost, product_sd, product_quantity, category_name'); // Refresh the cart
            location.reload();
        });
    }
}
