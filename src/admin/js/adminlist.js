$(document).ready(function () {
    function fetchTableData(table, columns) {
        console.log("load First");
        $.ajax({
            url: "process/adminlist_process.php",
            type: "GET",
            data: {table: table, columns: columns},
            success: function (response) {
                $('#item-list').html(response);
            }
        });
    }
    fetchTableData('admin','admin_id, admin_email, created_by, approved_by, approved, deleted_by');
});