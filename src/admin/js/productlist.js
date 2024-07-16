$(document).ready(function () {
    function fetchTableData(table, columns) {
        console.log("load First");
        $.ajax({
            url: "process/productlist_process.php",
            type: "GET",
            data: {table: table, columns: columns},
            success: function (response) {
                $('#item-list').html(response);
            }
        });
    }
    fetchTableData('product', 'product_id, product_name, product_cost, product_sd, product_ld, product_quantity, category_name');
});