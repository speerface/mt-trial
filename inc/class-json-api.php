<?php

class AS_JSON_API {

    public static $instance;

    public function __construct() {
        $this->add_hooks();
    }

    private function add_hooks() {

        add_action( 'rest_api_init', array( $this, 'add_rest_endpoint' ) );

    }

    public function add_rest_endpoint() {

        register_rest_route(
            'wp/v2',
            '/websites/',
            array(
                'methods' => 'GET',
                'callback' => array( $this, 'render_rest_endpoint' ),
            )
        );
    }

    public function render_rest_endpoint( WP_REST_Request $request ){

        $return  = array();
        $filter  = $request->get_param( 'filter' );
        $refresh = (bool) $request->get_param( 'refresh' );

        $args = array(
            'post_type' => 'as_website',
            'suppress_filters' => false,
        );

        if ( ! empty( $filter ) ) {
            $args = array_merge( $args, $filter );
        }

        $posts = get_posts( $args );

        if ( empty( $posts ) ) {
            return $return;
        }

        foreach ( $posts as $post ) {

            if ( $refresh ) {
                $url = get_post_meta( $post->ID, 'website_url', true );

                if ( ! $source = get_transient( 'cached_site_' . $url ) ) {
                    $source = AS_Main::get_instance()->cache_website_body( $url, $post->ID );
                }
            } else {
                $source = get_post_meta( $post->ID, 'website_source', true );
            }

            $return[] = array(
                'ID' => $post->ID,
                'title' => $post->post_title,
                'source' => $source,
            );
        }

        return $return;
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