jQuery(document).ready(function() {
    var oTable = jQuery('.datatable').dataTable({
        "bProcessing": true,
        "sAjaxSource": ajax_object.ajax_url + "?action=populate_directory",
        "sDom": "<'clear'f><lprtip>",
        "aoColumns": [
            { "sWidth": "170px" },
            {
                "sWidth": "170px",
                "sClass": "directory_column_cutoff"
            },
            {
                "sWidth": "300px",
                "sClass": "directory_column_wrap"
            },
            { 
                "sWidth": "140px",
                "sType": "date"
            },
            {
                "sWidth": "140px",
                "sType": "date"
            }
        ]
    });
});