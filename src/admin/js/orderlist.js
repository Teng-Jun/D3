$(document).ready(function () {
    function fetchTableData(table, columns) {
        console.log("load First");
        $.ajax({
            url: "process/orderlist_process.php",
            type: "GET",
            data: {table: table, columns: columns},
            success: function (response) {
                $('#item-list').html(response);
            }
        });
    }
    fetchTableData('order', 'order_id, order_tracking_no, order_quantity, order_status, product.product_id, product_name, product_cost, customer.customer_id, customer_lname, customer_address');
});