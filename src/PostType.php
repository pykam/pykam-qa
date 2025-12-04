<?php
namespace PykamQA;

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Registers and configures the custom post type that stores Q&A entries.
 */
class PostType {

    const POST_NAME = 'pykam-qa';
    
    /**
     * Hooks WordPress actions used by the post type.
     */
    public function __construct() {
        add_action('init', array($this, 'register_post_type'));
        add_action('template_redirect', array($this, 'hide_from_frontend'));
        add_action('pre_get_posts', array($this, 'exclude_from_widgets'));
        add_action('template_redirect', array($this, 'disable_feed'));
    }
    
    /**
     * Registers the custom post type used for questions and answers.
     *
     * @return void
     */
    public function register_post_type() {
            $labels = array(
            'name'               => __('Pykam QA', 'pykam-qa'),
            'singular_name'      => __('Question & Answer', 'pykam-qa'),
            'menu_name'          => __('Q&A', 'pykam-qa'),
            'name_admin_bar'     => __('Question & Answer', 'pykam-qa'),
            'add_new'            => __('Add New', 'pykam-qa'),
            'add_new_item'       => __('Add New Question', 'pykam-qa'),
            'new_item'           => __('New Question', 'pykam-qa'),
            'edit_item'          => __('Edit Question', 'pykam-qa'),
            'view_item'          => __('View Question', 'pykam-qa'),
            'all_items'          => __('All questions', 'pykam-qa'),
            'search_items'       => __('Search Questions', 'pykam-qa'),
            'parent_item_colon'  => __('Parent Question:', 'pykam-qa'),
            'not_found'          => __('No questions found.', 'pykam-qa'),
            'not_found_in_trash' => __('No questions found in the deleted items bin.', 'pykam-qa')
        );

        $args = array(
            'labels'             => $labels,
            'public'             => false,
            'publicly_queryable' => false,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => false,
            'rewrite'            => false,
            'capability_type'    => 'post',
            'has_archive'        => false,
            'hierarchical'       => false,
            'menu_position'      => 20,
            'menu_icon'          => 'dashicons-format-chat',
            'supports'           => array('title', 'editor', 'author'),
            'show_in_rest'       => true,
        );

        register_post_type(self::POST_NAME, $args);
    }
    
    
    /**
     * Prevents direct access to single Q&A posts.
     *
     * @return void
     */
    public function hide_from_frontend() {
        if (is_singular(self::POST_NAME)) {
            wp_redirect(home_url(), 301);
            exit;
        }
        
        if (!is_admin()) {
            global $wp_query;
            if (isset($wp_query->query_vars['post_type']) && $wp_query->query_vars['post_type'] == self::POST_NAME) {
                $wp_query->set_404();
                status_header(404);
            }
        }
    }
    
    /**
     * Removes the Q&A post type from public queries and widgets.
     *
     * @param \WP_Query $query
     *
     * @return void
     */
    public function exclude_from_widgets($query) {
        if (!is_admin() && $query->is_main_query()) {
            $post_types = $query->get('post_type');
            if (is_array($post_types)) {
                $post_types = array_diff($post_types, array(self::POST_NAME));
                $query->set('post_type', $post_types);
            } elseif ($post_types == self::POST_NAME) {
                $query->set('post_type', 'post');
            }
        }
    }
    
    /**
     * Disables RSS feeds for the custom post type.
     *
     * @return void
     */
    public function disable_feed() {
        if (is_feed() && get_query_var('post_type') == self::POST_NAME) {
            wp_die(__('The feed is not available for this type of post', 'pykam-qa'), '', array('response' => 404));
        }
    }
}