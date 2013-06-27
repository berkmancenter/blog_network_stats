<?php 
/*
Plugin Name: Blog Network Stats
Plugin URI: http://github.com/berkmancenter/blog_network_stats
Description: A plugin that gathers stats about network blogs into one table.
Author: Tomas Reimers
Version: 0.1
*/

global $wpdb;

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
            UNIQUE KEY blog_id (blog_id),
            FOREIGN KEY (blog_id) REFERENCES ' . $wpdb->base_prefix . 'blogs(blog_id) ON DELETE CASCADE
            );';
    
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

        dbDelta($sql);

        // Populate table
        $this->update();

        // Schedule cron
        wp_schedule_event(time(), 'daily', 'blog_network_stats_update');


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
            $wpdb->prepare("SELECT blog_id FROM " . $wpdb->base_prefix . "blogs", array())
        );

        foreach ($blogs as $blog){

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

        }

    }

    public function hook_in(){
        add_submenu_page(
            'settings.php',
            __('Debug Blog Network Stats'),
            __('Debug Blog Network Stats'),
            'manage_network',
            'debug-blog-network-stats',
            array($this, 'install')
        );
    }

}

$blog_network_stats = new Blog_network_class();

// Register Cron Actions
add_action( 'blog_network_stats_update', array($blog_network_stats, 'update') );

// Register Hooks

// register_activation_hook(__FILE__, array( $blog_network_stats, 'install' )); -- COMMENTED OUT FOR DEBUGGING

register_deactivation_hook(__FILE__, array( $blog_network_stats, 'uninstall' ));
add_action('network_admin_menu', array($blog_network_stats, 'hook_in')); // FOR DEBUGGING

?>