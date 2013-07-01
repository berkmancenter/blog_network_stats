<?php 
    
/* 
 * directory.php
 *
 * Adds the directory shortcode that allows the creation of the directory.
 *
 * [directory]
 *
 */

function network_ajax_handler(){

    echo file_get_contents(plugin_dir_path(__FILE__) . "../../data.json");

    die();
}

add_action('wp_ajax_populate_directory', 'network_ajax_handler');
add_action('wp_ajax_nopriv_populate_directory', 'network_ajax_handler');

function network_directory_handler( $atts ) {

    // Extract attributes

    extract( shortcode_atts( array(
        'perpage' => false,
        'search' => false,
        'orderby' => false,
        'page' => false

    ), $atts ) );

    // Echo necessary scripts

    $plugin_dir = plugin_dir_url(__FILE__);

    wp_enqueue_script(
        'datatables_js',
        $plugin_dir . '../vendor/datatables/js/jquery.dataTables.min.js',
        array('jquery')
    );

    wp_enqueue_script(
        'directory_engine',
        $plugin_dir . 'directory-engine.js',
        array('jquery')
    );

    wp_localize_script(
        'directory_engine',
        'ajax_object',
        array('ajax_url' => admin_url( 'admin-ajax.php' ))
    );

    wp_enqueue_style(
        'directory_css',
        $plugin_dir . 'directory.css'
    );

    wp_enqueue_style(
        'datatables_css',
        $plugin_dir . '../vendor/datatables/css/jquery.dataTables.css'
    );

    // Construct table HTML

    $dataTable = "";

    $dataTable .= "<table class='datatable'>";
        $dataTable .= "<thead>";
            $dataTable .= "<tr>";
                $dataTable .= "<th>Blog</th>";
                $dataTable .= "<th>Description</th>";
                $dataTable .= "<th>Owner</th>";
                $dataTable .= "<th>Created</th>";
                $dataTable .= "<th>Updated</th>";
            $dataTable .= "</tr>";
        $dataTable .= "</thead>";

        $dataTable .= "<tbody>";

            // populate with AJAX

        $dataTable .= "</tbody>";

    $dataTable .= "</table>";

    return $dataTable;
}
add_shortcode( 'directory', 'network_directory_handler' );

?>