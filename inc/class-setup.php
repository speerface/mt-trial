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

        // Add the WEBSITES Custom Post Type.
        add_action( 'init', array( $this, 'add_websites_post_type' ) );

        // Add the rewrite rule for your-website.
        add_action( 'init', array( $this, 'add_form_page_rewrite' ) );

        // Add hook to load template file.
        add_filter( 'template_include', array( $this, 'load_form_template' ), 10 );

        // Add endpoint for the form submission.
        add_action( 'admin_post_submit_website', array( $this, 'handle_website_submission' ) );
        add_action( 'admin_post_nopriv_submit_website', array( $this, 'handle_website_submission' ) );
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
            'capabilities'       => array( 'create_posts' => false ),
            'map_meta_cap'       => true,
        );

        register_post_type( 'as_website', $args );
    }

    public function add_form_page_rewrite() {
        add_rewrite_rule( '^your-website/?', 'index.php?page=your-website', 'top' );
    }

    public function load_form_template( $template ) {

        global $wp_query;

        if ( 'your-website' !== $wp_query->query['page'] ) {
            return $template;
        }

        // Grab the template file from our plugin, with the option to override in the theme.
        $new_template = $this->locate_template_file( 'website-form.php' );

        if ( $new_template ) {
            return $new_template;
        }

        return $template;
    }

    public function handle_website_submission() {

        $nonce = filter_input( INPUT_POST, 'submit_website_nonce', FILTER_SANITIZE_STRING );
        $name  = filter_input( INPUT_POST, 'name', FILTER_SANITIZE_STRING );
        $url   = filter_input( INPUT_POST, 'url', FILTER_SANITIZE_URL );

        if ( ! wp_verify_nonce( $nonce, 'submit_website' ) ) {
            die( 'Security failed.' );
        }

        if ( empty( $name ) || empty( $url ) ) {
            wp_redirect( site_url( 'your-website?error=missing_fields' ) );
            exit;
        }

        $body = $this->get_website_body( $url );

        if ( ! $body ) {
            wp_redirect( site_url( 'your-website?error=invalid_url' ) );
            exit;
        }

        $new_post = $this->create_new_website_post( $name, $url, $body );

        if ( ! $new_post ) {
            wp_redirect( site_url( 'your-website?error=server' ) );
            exit;
        }

        wp_redirect( site_url( 'your-website?success=true' ) );
        exit;
    }

    private function get_website_body( $url ) {
        $request = wp_remote_get( $url );

        if ( is_wp_error( $request ) ) {
            return false;
        }

        return $request['body'];
    }

    private function create_new_website_post( $name, $url, $body ) {

        $new_post = array(
            'post_type'   => 'as_website',
            'post_status' => 'publish',
            'post_title'  => $name,
        );

        $post_id = wp_insert_post( $new_post );

        if ( ! $post_id ) {
            return false;
        }

        update_post_meta( $post_id, 'website_url', $url );
        update_post_meta( $post_id, 'website_body', $body );

        return $post_id;

    }

    private function locate_template_file( $template_name ) {

        // Template name may have been passed with a slash; remove it.
        $template_name = ltrim( $template_name, '/' );

        // Check in the child Theme for the file.
        if ( file_exists( trailingslashit( get_stylesheet_directory() ) . 'mt-trial/' . $template_name ) ) {
            return trailingslashit( get_stylesheet_directory() ) . 'mt-trial/' . $template_name;
        }

        // Check in the Parent Theme for the file.
        if ( file_exists( trailingslashit( get_template_directory() ) . 'mt-trial/' . $template_name ) ) {
            return trailingslashit( get_template_directory() ) . 'mt-trial/' . $template_name;
        }

        // Check in the Plugin directory for the file.
        if ( file_exists( trailingslashit( AS_MT_PLUGIN_DIR ) . 'templates/' . $template_name ) ) {
            return trailingslashit( AS_MT_PLUGIN_DIR ) . 'templates/' . $template_name;
        }

        // File wasn't found, return false.
        return false;

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