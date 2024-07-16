$(document).ready(function () {
    var path = window.location.pathname;
    var pagename = path.split("/").pop();
    function fetchTableDataSpecific(table, columns) {
        console.log("load First");
        // Get the query string from the URL
        var queryString = window.location.search;

        // Parse the query string to get the value of the "id" parameter
        var urlParams = new URLSearchParams(queryString);
        if (urlParams.get('customerid')) {
            var specificid = urlParams.get('customerid');
            var cate = "customer";
        }
        if (urlParams.get('productid')) {
            var specificid = urlParams.get('productid');
            var cate = "product";
        }
        if (urlParams.get('orderid')) {
            var specificid = urlParams.get('orderid');
            var cate = "order";
        }
        $.ajax({
            url: "process/edit_details_process.php",
            type: "GET",
            data: {table: table, columns: columns, specificid: specificid, cate: cate},
            success: function (response) {
                $('#edit_form').html(response);
            }
        });
    }
    if (pagename == "edit.php") {
        var queryString = window.location.search;
        // Parse the query string to get the value of the "id" parameter
        var urlParams = new URLSearchParams(queryString);
        if (urlParams.get('customerid')) {
            fetchTableDataSpecific('customer', 'customer_id, customer_fname, customer_lname, customer_email, customer_address, customer_number, customer_points');
        }
        if (urlParams.get('productid')) {
            fetchTableDataSpecific('product', 'product_id, product_name, product_cost, product_sd, product_ld, product_quantity, category_id');
        }
        if (urlParams.get('orderid')) {
            fetchTableDataSpecific('order', 'order_id, order_tracking_no, order_status');
        }
    }
});


