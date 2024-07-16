$(document).ready(function () {
    function fetchTableData(table, columns) {
        console.log("load First");

        // Get the CSRF token from the meta tag
        var csrfToken = $('meta[name="csrf-token"]').attr('content');

        $.ajax({
            url: "process/cable_process.php",
            type: "POST", // Ensure this is POST
            data: {
                table: table,
                columns: columns,
                csrf_token: csrfToken // Include the CSRF token
            },
            success: function (response) {
                $('#cablecard-deck').html(response);
            },
            error: function (xhr, status, error) {
                console.error("Error fetching data:", status, error);
                console.error("Response:", xhr.responseText);
            }
        });
    }

    fetchTableData('product', 'product_id, product_name, product_cost, category_id, product_sd, product_quantity');
});
