jQuery(document).ready(function() {
    var oTable = jQuery('.datatable').dataTable({
        "bProcessing": true,
        "sAjaxSource": ajax_object.ajax_url + "?action=populate_directory",
        "sDom": "<'clear'f><lprtip>",
        "aoColumns": [
            null,
            null,
            null,
            null,
            { "sType": "date" },
            { "sType": "date" },
        ]
    });
});