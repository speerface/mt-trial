<?php

class AS_Setup {

    public static $instance;

    public function __construct() {
        $this->do_setup();
    }

    public function init() {
        flush_rewrite_rules();
    }

    public function do_setup() {
        $this->add_hooks();
        $this->setup_metaboxes();
        $this->access_control();
    }

    private function add_hooks() {
        add_action( 'init', array( $this, 'add_websites_post_type' ) );
    }

    private function setup_metaboxes() {
        AS_Metaboxes::get_instance();
        AS_Metaboxes::$instance->init();
    }

    private function access_control() {
        AS_Access_Control::get_instance();
        AS_Access_Control::$instance->init();
    }

    public function add_websites_post_type() {

        print_r( 'yo' );

        /**
         * WEBSITE Post Type
         */
        $labels = array(
            'name'               => _x( 'WEBSITES', 'post type general name', 'as-mt-trial' ),
            'singular_name'      => _x( 'WEBSITE', 'post type singular name', 'as-mt-trial' ),
            'menu_name'          => _x( 'WEBSITES', 'admin menu', 'as-mt-trial' ),
            'name_admin_bar'     => _x( 'WEBSITE', 'add new on admin bar', 'as-mt-trial' ),
            'add_new'            => _x( 'Add New', 'as-mt-trial', 'as-mt-trial' ),
            'add_new_item'       => __( 'Add New WEBSITE', 'as-mt-trial' ),
            'new_item'           => __( 'New WEBSITE', 'as-mt-trial' ),
            'edit_item'          => __( 'Edit WEBSITE', 'as-mt-trial' ),
            'view_item'          => __( 'View WEBSITE', 'as-mt-trial' ),
            'all_items'          => __( 'All WEBSITES', 'as-mt-trial' ),
            'search_items'       => __( 'Search WEBSITES', 'as-mt-trial' ),
            'parent_item_colon'  => __( 'Parent WEBSITES:', 'as-mt-trial' ),
            'not_found'          => __( 'No WEBSITES found.', 'as-mt-trial' ),
            'not_found_in_trash' => __( 'No WEBSITES found in Trash.', 'as-mt-trial' ),
        );

        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'menu_icon'          => 'dashicons-admin-site',
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'website' ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => null,
            'supports'           => array(),
            'capabilities'       => array(
                'create_posts' => false,
            ),
            'map_meta_cap'       => true,
        );

        register_post_type( 'as_website', $args );
    }

    /**
     * Gets the singleton instance of this class.
     *
     * @return AS_Setup
     */
    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            $name      = __CLASS__;
            self::$instance = new $name;
        }

        return self::$instance;
    }

}