<?php

class AS_Metaboxes {

    public static $instance;

    public function __construct() {
        $this->add_hooks();
    }

    private function add_hooks() {
        add_action( 'add_meta_boxes', array( $this, 'add_site_source_metabox' ) );
        add_action( 'admin_menu', array( $this, 'remove_extra_meta_boxes' ) );
        add_action( 'admin_enqueue_scripts', array( $this, 'hide_right_column' ) );
    }

    public function add_site_source_metabox( $post_type ) {

        if ( 'as_website' !== $post_type ) {
            return;
        }

        add_meta_box(
            'website_source_metabox',
            __( 'Website Source Code', 'as-mt-trial' ),
            array( $this, 'render_site_source_metabox' ),
            $post_type
        );

    }

    public function remove_extra_meta_boxes() {
        remove_meta_box( 'submitdiv', 'as_website', 'side' );
    }

    public function hide_right_column() {
        global $post;

        if ( 'as_website' !== get_post_type( $post ) ) {
            return;
        }

        wp_enqueue_style( 'as-admin-style', AS_MT_PLUGIN_URI . 'admin/style.css' );
    }

    public function render_site_source_metabox( $post ) {
        $url = get_post_meta( $post->ID, 'website_url', true );

        if ( empty( $url ) ) {
            return;
        }

        if ( ! $source = get_transient( 'cached_site_' . $url ) ) {
            $source = AS_Main::get_instance()->cache_website_body( $url );
        }

        ?>
        <h3><?php esc_html_e( get_the_title( $post ) ); ?></h3>
        <h4><?php esc_html_e( $url ); ?></h4>
        <textarea cols="30" rows="10" style="width:100%;" readonly>
            <?php esc_html_e( $source ); ?>
        </textarea>
        <?php
    }

    /**
     * Gets the singleton instance of this class.
     *
     * @return AS_Metaboxes
     */
    public static function get_instance() {
        if ( ! isset( self::$instance ) ) {
            $name      = __CLASS__;
            self::$instance = new $name;
        }

        return self::$instance;
    }
}