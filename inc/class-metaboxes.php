<?php

class AS_Metaboxes {

    public static $instance;

    public function init() {

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