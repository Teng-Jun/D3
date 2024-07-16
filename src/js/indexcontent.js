$(document).ready(function () {
    function fetchTableData(table, columns) {
        console.log("load First");
        $.ajax({
            url: "process/index_content_process.php",
            type: "GET",
            data: {table: table, columns: columns},
            success: function (response) {
                $('#card-deck').html(response);
            },
            error: function (xhr, status, error) {
                console.error("Error fetching data:", status, error);
                console.error("Response:", xhr.responseText);
            }
        });
    }
    fetchTableData('category', 'category_name');
});