<?php 
/*
Plugin Name: Blog Network Stats
Plugin URI: http://github.com/berkmancenter/blog_network_stats
Description: A plugin that gathers stats about network blogs into one table and create widgets to showcase the stats.
Author: Tomas Reimers
Version: 0.1
*/

global $wpdb;

// create shortcodes
require("includes/showcase/showcase.php");
require("includes/directory/directory.php");

// Need get_userdata()
require_once(ABSPATH . 'wp-includes/pluggable.php');

class Blog_network_class {

    private $stats_table = '';

    public function __construct(){

        global $wpdb;

        $this->stats_table = $wpdb->base_prefix . 'blog_network_stats';
    }

    public function install(){

        global $wpdb;

        // create tables

        $sql = "CREATE TABLE " . $this->stats_table . ' (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            blog_id bigint(20),
            total_users bigint(20),
            recent_posts_and_comments bigint(20),
            PRIMARY KEY (id),
            UNIQUE KEY id (id),
            UNIQUE KEY blog_id (blog_id)
            );';
    
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        dbDelta($sql);

        // Schedule cron
        wp_schedule_event(time(), 'daily', 'blog_network_stats_update');

        // Populate table
        do_action('blog_network_stats_update');

    }

    public function uninstall(){

        global $wpdb;

        // delete table

        $wpdb->query(
                $wpdb->prepare("DROP TABLE " . $this->stats_table, array())
            );

        // Deschedule cron
        wp_clear_scheduled_hook( 'blog_network_stats_update' );


    }

    public function update(){

        global $wpdb;

        $blogs = $wpdb->get_col(
            $wpdb->prepare("SELECT blog_id FROM " . $wpdb->base_prefix . "blogs WHERE spam != 1 AND deleted != 1 AND archived != '1'", array())
        );

        $json_file = fopen(plugin_dir_path(__FILE__) . "new_data.json", "w");

        $first_item = true;

        fwrite($json_file, '{"aaData": [');

        foreach ($blogs as $blog){

            set_time_limit(30);

            if (get_blog_option($blog, 'blog_public') == "1"){

                // ******************
                // ***** SQL TABLE **
                // ******************

                // update total users

                $users = get_users(array(
                        'blog_id' => $blog
                    ));

                $total_users = count($users);

                // update posts and comments

                $posts = $wpdb->get_var('SELECT COUNT(*) FROM ' . $wpdb->get_blog_prefix($blog) . 'posts WHERE post_status = "publish" AND post_type = "post"');
                $comments = $wpdb->get_var('SELECT COUNT(*) FROM ' . $wpdb->get_blog_prefix($blog) . 'comments WHERE comment_approved="1"');

                $recent_posts_and_comments = $posts + $comments;

                // put in sql

                $wpdb->query(
                        $wpdb->prepare(
                            "INSERT INTO " . $this->stats_table . " (blog_id, total_users, recent_posts_and_comments) VALUES (%d, %d, %d) ON DUPLICATE KEY UPDATE total_users = %d, recent_posts_and_comments = %d",
                            array($blog, $total_users, $recent_posts_and_comments, $total_users, $recent_posts_and_comments)
                        )
                    );

                // Clear memory
                unset($users);
                unset($total_users);
                unset($posts);
                unset($comments);
                unset($recent_posts_and_comments);

                // ******************
                // ***** JSON *******
                // ******************

                if (!$first_item){
                    fwrite($json_file, ", ");
                }
                else {
                    $first_item = false;
                }

                $row = array(
                    "<a href='" . get_blog_details($blog)->path . "'>" . get_blog_option($blog, "blogname") . "</a>",
                    "<a href='" . get_blog_details($blog)->path . "'>" . get_blog_details($blog)->path . "</a>",
                    "<div title='" . get_blog_option($blog, "blogdescription") . "'>" . get_blog_option($blog, "blogdescription") . "</div>",
                    date("n/j/Y", strtotime(get_blog_details($blog)->registered)),
                    date("n/j/Y", strtotime(get_blog_details($blog)->last_updated))
                );
                
                fwrite($json_file, json_encode($row));

                unset($row);

                wp_cache_flush();

                gc_collect_cycles();

            }

        }

        fwrite($json_file, ']}');

        fclose($json_file);

        rename(plugin_dir_path(__FILE__) . "new_data.json", plugin_dir_path(__FILE__) . "data.json");

    }

}

$blog_network_stats = new Blog_network_class();

// Register Cron Actions
add_action( 'blog_network_stats_update', array($blog_network_stats, 'update') );

// Register Hooks

register_activation_hook(__FILE__, array( $blog_network_stats, 'install' ));
register_deactivation_hook(__FILE__, array( $blog_network_stats, 'uninstall' ));

?>