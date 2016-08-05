<?php
/**
 * Provides an endpoint for the WP JSON API at /wp/v2/websites
 *
 * @package as-mt-trial
 * @version 1.0
 */

namespace Inc;

/**
 * Class AS_JSON_API
 */
class AS_JSON_API {

    /**
     * @var $instance
     */
    public static $instance;

    /**
     * AS_JSON_API constructor.
     */
    public function __construct() {
        $this->add_hooks();
    }

    /**
     * Adds the hook for creating the rest endpoint.
     */
    private function add_hooks() {
        add_action( 'rest_api_init', array( $this, 'add_rest_endpoint' ) );
    }

    /**
     * Registers the rest endpoint with the API.
     */
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

    /**
     * Renders the rest endpoint data.
     *
     * @param object $request
     *
     * @return array
     */
    public function render_rest_endpoint( $request ){

        $return  = array();
        $filter  = $request->get_param( 'filter' );
        $refresh = (bool) $request->get_param( 'refresh' );

        $args = array(
            'post_type' => 'as_website',
            'suppress_filters' => false,
        );

        // Merge in any passed filters to the arguments.
        if ( ! empty( $filter ) ) {
            $args = array_merge( $args, $filter );
        }

        $posts = get_posts( $args );

        // Return an empty array if no items are found.
        if ( empty( $posts ) ) {
            return $return;
        }

        foreach ( $posts as $post ) {

            // If the refresh parameter is passed, retrieve any expired source code. Otherwise,
            // simply return the post_meta.
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