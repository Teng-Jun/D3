$(document).ready(function () {
    
    function isValidProductId(productid) {
        // Check if the product ID is a positive integer and within a valid range (e.g., 1 to 1,000,000)
        var idPattern = /^[1-9][0-9]*$/;
        return idPattern.test(productid) && productid > 0 && productid <= 1000000;
    }


    function fetchTableDataProduct(table, columns) {
        console.log("load First");
        // Get the query string from the URL
        var queryString = window.location.search;

        // Parse the query string to get the value of the "id" parameter
        var urlParams = new URLSearchParams(queryString);
        var productid = urlParams.get('id');

        // Advanced client-side validation
        if (!productid || !isValidProductId(productid)) {
            alert("Invalid product ID");
            window.history.back();
            exit();
        }

        $.ajax({
            url: "process/details_content_process.php",
            type: "GET",
            data: { table: table, columns: columns, productid: productid },
            success: function (response) {
                var jsonResponse = JSON.parse(response);
                if (jsonResponse.status === 'success') {
                    $('#details-page').html(jsonResponse.data);
                } else {
                    window.history.back();
                }
            },
            error: function (xhr, status, error) {
                console.error("Error fetching data:", status, error);
                console.error("Response:", xhr.responseText);
                alert("An error occurred while fetching the data.");
            }
        });
    }

    fetchTableDataProduct('product', 'product_id, product_name, product_cost, product_sd, product_ld, product_quantity, category_name');
});

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
