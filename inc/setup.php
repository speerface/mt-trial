<?php

class AS_Setup {

    public static $instance;

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