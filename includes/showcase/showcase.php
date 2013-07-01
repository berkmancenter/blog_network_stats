<?php 
    
/* 
 * showcase.php
 *
 * Adds the showcase shortcode that allows the creation of the showcase.
 *
 * [showcase number=":NUMBER-OF-BLOGS-TO-DISPLAY"]
 *
 */

function network_showcase_handler( $atts ) {

    global $wpdb;

    // Extract attributes

    extract( shortcode_atts( array(
        'number' => '30'
    ), $atts ) );

    // Echo necessary scripts

    $plugin_dir = plugin_dir_url(__FILE__);

    wp_enqueue_script(
        'showcase_masonry_js',
        $plugin_dir . '../vendor/jquery.masonry.min.js',
        array('jquery')
    );

    wp_enqueue_script(
        'showcase_engine',
        $plugin_dir . 'showcase-engine.js',
        array('jquery')
    );

    wp_enqueue_style(
        'showcase_css',
        $plugin_dir . 'showcase.css'
    );

    // Get blogs to show 

    $filters = array(
        "Newest" => "SELECT blog_id FROM " . $wpdb->base_prefix . "blogs ORDER BY registered DESC LIMIT %d",
        "Recently Updated" => "SELECT blog_id FROM " . $wpdb->base_prefix . "blogs ORDER BY last_updated DESC LIMIT %d",
        "Popular" => "SELECT blog_id FROM " . $wpdb->base_prefix . "blog_network_stats ORDER BY total_users DESC LIMIT %d",
        "Active" => "SELECT blog_id FROM " . $wpdb->base_prefix . "blog_network_stats ORDER BY recent_posts_and_comments DESC LIMIT %d"
    );

    $filter_results = array();

    foreach ($filters as $key => $sql){
        $filter_results[$key] = $wpdb->get_col(
            $wpdb->prepare($sql, array($number))
        );
    }

    // Prepare HTML

    $showcase_html = "";

    $showcase_html .= "<div class='showcase'>";
        $showcase_html .= "<div class='showcase_links'>";
            $first_link_class = "current_showcase_section_link";
            foreach ($filter_results as $label => $blogs){
                $showcase_html .= "<a href='#' class='" . $first_link_class . "' onclick='return showcase_open(\".showcase-" . strtolower(str_replace(" ", "-", $label)) . "\", this)'>" . $label . "</a>";
                $first_link_class = "";
            }
        $showcase_html .= "</div>";

        $show_item = "display: block;";

        foreach ($filter_results as $label => $blogs){
            $showcase_html .= "<div style='" . $show_item . "' class='showcase_section showcase-" . strtolower(str_replace(" ", "-", $label)) . "'>";
            foreach ($blogs as $blog){

                $blogusers = get_users(array(
                        'blog_id' => $blog,
                        'role' => 'administrator'
                    ));
                $first_author = true;

                $showcase_html .= "<div class='showcase_item'><div class='showcase_box'>";
                    $showcase_html .= "<div class='showcase_title'>" . "<a href='" . get_blog_details($blog)->path . "'>" . get_blog_option($blog, "blogname") . "</a>" . "</div>";
                    $showcase_html .= "<div class='showcase_authors'>By ";
                        foreach ($blogusers as $user) {
                            if (!$first_author){
                                $showcase_html .= ", ";
                            }
                            $first_author = false;
                            $showcase_html .= $user->display_name;
                        }
                    $showcase_html .= "</div>";
                    if (get_blog_option($blog, "blogdescription") != ""){
                        $showcase_html .= "<div class='showcase_description'>" . get_blog_option($blog, "blogdescription") . "</div>";
                    }
                    $showcase_html .= "<div class='showcase_meta'>Created " . date("n/j/Y", strtotime(get_blog_details($blog)->registered)) . "</div>";
                    $showcase_html .= "<div class='showcase_meta'>Last Updated " . date("n/j/Y", strtotime(get_blog_details($blog)->last_updated)) . "</div>";
                $showcase_html .= "</div></div>";
            }
            $showcase_html .= "</div>";

            $show_item = "display: none;";

        }
    $showcase_html .= "</div>";

    // Return 

    return $showcase_html;
}
add_shortcode( 'showcase', 'network_showcase_handler' );

?>