$(document).ready(function () {
    function fetchTableData(table, columns) {
        console.log("load First");
        $.ajax({
            url: "process/customerlist_process.php",
            type: "GET",
            data: {table: table, columns: columns},
            success: function (response) {
                $('#item-list').html(response);
            }
        });
    }
    fetchTableData('customer','customer_id, customer_fname, customer_lname, customer_email, customer_address, customer_number, customer_points, customer_joindate');
});