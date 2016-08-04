<?php

class AS_Main {

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
    }

    private function add_hooks() {

        // Add the Websites Custom Post Type.
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
    }

    public function add_websites_post_type() {

        /**
         * Website Post Type
         */
        $labels = array(
            'name'               => _x( 'Websites', 'post type general name', 'as-mt-trial' ),
            'singular_name'      => _x( 'Website', 'post type singular name', 'as-mt-trial' ),
            'menu_name'          => _x( 'Websites', 'admin menu', 'as-mt-trial' ),
            'name_admin_bar'     => _x( 'Website', 'add new on admin bar', 'as-mt-trial' ),
            'add_new'            => _x( 'Add New', 'as-mt-trial', 'as-mt-trial' ),
            'add_new_item'       => __( 'Add New Website', 'as-mt-trial' ),
            'new_item'           => __( 'New Website', 'as-mt-trial' ),
            'edit_item'          => __( 'Edit Website', 'as-mt-trial' ),
            'view_item'          => __( 'View Website', 'as-mt-trial' ),
            'all_items'          => __( 'All Websites', 'as-mt-trial' ),
            'search_items'       => __( 'Search Websites', 'as-mt-trial' ),
            'parent_item_colon'  => __( 'Parent Websites:', 'as-mt-trial' ),
            'not_found'          => __( 'No Websites found.', 'as-mt-trial' ),
            'not_found_in_trash' => __( 'No Websites found in Trash.', 'as-mt-trial' ),
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
            'supports'           => array( '' ),
            'capabilities'       => array(
                'create_posts' => false,
                'edit_posts' => 'edit_others_posts',
            ),
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

        // Grab sanitized versions of the $_POST data.
        $nonce = filter_input( INPUT_POST, 'submit_website_nonce', FILTER_SANITIZE_STRING );
        $name  = filter_input( INPUT_POST, 'name', FILTER_SANITIZE_STRING );
        $url   = filter_input( INPUT_POST, 'url', FILTER_SANITIZE_URL );

        if ( ! wp_verify_nonce( $nonce, 'submit_website' ) ) {
            die( 'Security failed.' );
        }

        // Make sure both fields were submitted.
        if ( empty( $name ) || empty( $url ) ) {
            wp_redirect( site_url( 'your-website?error=missing_fields' ) );
            exit;
        }

        $body = $this->cache_website_body( $url );

        // There was an error retrieving the site.
        if ( ! $body ) {
            wp_redirect( site_url( 'your-website?error=invalid_url' ) );
            exit;
        }

        $new_post = $this->create_new_website_post( $name, $url );

        // There was an error creating the new post object.
        if ( ! $new_post ) {
            wp_redirect( site_url( 'your-website?error=server' ) );
            exit;
        }

        // Redirect the user to the form with a success status.
        wp_redirect( site_url( 'your-website?success=true' ) );
        exit;
    }

    public function cache_website_body( $url ) {

        // The site may potentially be stored as a transient already, check that first.
        if ( $body = get_transient( 'cached_site_' . $url ) ) {
            return $body;
        }

        $request = wp_remote_get( $url );

        if ( is_wp_error( $request ) ) {
            return false;
        }

        $cache_length = 3600;
        $cache = wp_remote_retrieve_header( $request, 'cache-control' );

        // If requested site is using cache-control headers, try to respect the max-age.
        if ( ! empty( $cache ) ) {

            $time = $this->get_cache_time( $cache );

            if ( $time ) {
                $cache_length = $time;
            }
        }

        $body = wp_remote_retrieve_body( $request );

        // Store the site body as a transient.
        set_transient(  'cached_site_' . $url, $body, $cache_length );

        return $body;
    }

    private function get_cache_time( $cache_string ) {

        // Max-age is tricky to grab given the number of variations, so parse it from the string.
        $parts = explode( ',', $cache_string );

        // Loop through each potential part of the cache-control string and try to find max-age.
        foreach ( $parts as $part ) {

            // If found, return the max-age value.
            if ( strpos( $part, 'max-age' ) !== false ) {
                $values = explode( '=', $part );
                return (int) trim( $values[1] );
            }
        }

        // Nothing found, return false.
        return false;
    }

    private function create_new_website_post( $name, $url ) {

        $new_post = array(
            'post_type'   => 'as_website',
            'post_status' => 'publish',
            'post_title'  => $name,
        );

        $post_id = wp_insert_post( $new_post );

        // There was an error creating the post, bail early.
        if ( ! $post_id ) {
            return false;
        }

        update_post_meta( $post_id, 'website_url', $url );

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
     * @return AS_Main
     */
    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            $name      = __CLASS__;
            self::$instance = new $name;
        }

        return self::$instance;
    }

}