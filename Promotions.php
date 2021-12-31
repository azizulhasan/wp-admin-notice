<?php
/**
 * Plugin_Name Admin notices helper methods
 *
 * @sience 1.0.0
 */
class Promotions {

    public $json_url;
    public $template_path;
    public $default_path;
    public $template_name;

    public function __construct( $json_url , $template_name, $template_path, $default_path = ''){
        $this->json_url = $json_url;
        $this->template_name = $template_name;
        $this->template_path = $template_path;
        $this->default_path = $default_path;
    }
    /**
     * This method will display notices only under Plugin Name menu and all of its sub-menu pages
     *
     * @since 1.0.0
     *
     * @return array | void
     */
    public  function plugin_name_get_admin_notices() {
        $notices = apply_filters( 'plugin_name_admin_notices', [] );

        if ( empty( $notices ) ) {
            return $notices;
        }

        uasort( $notices, [ $this, 'plugin_name_sort_notices_by_priority' ] );

        return array_values( $notices );
    }

    /**
     * This method will display notices under all pages including Plugin Name menu and sub-menu pages
     *
     * @since 1.0.0
     *
     * @return array
     */
    public  function plugin_name_get_global_admin_notices() {
        $notices = apply_filters( 'plugin_name_global_admin_notices', [] );

        if ( empty( $notices ) ) {
            return $notices;
        }

        uasort( $notices, [ $this, 'plugin_name_sort_notices_by_priority' ] );

        return array_values( $notices );
    }

    /**
     * Plugin_Name promotional notices
     *
     * @since 1.0.0
     *
     * @return array
     */
    public  function plugin_name_get_promo_notices() {
        $promos =  false;


        //update_option( '_plugin_name_limited_time_promo', [] );
        //update_option( '_plugin_name_limited_time_promo_to_users', [] );
        if ( false === $promos ) {
            $promo_notice_url = $this->json_url;
            $response         = wp_remote_get( $promo_notice_url, array( 'timeout' => 15 ) );
            $promos           = wp_remote_retrieve_body( $response );


            if ( is_wp_error( $response ) || $response['response']['code'] !== 200 ) {
                $promos = '[]';
            }

        }

        $promos = json_decode( $promos, true );
        $notices = [];

        // check if api data is valid
        if ( empty( $promos ) || ! is_array( $promos ) ) {
            return $notices;
        }
        $est_time_now            = $this->plugin_name_current_datetime()->setTimezone( new \DateTimeZone( 'EST' ) )->format( 'Y-m-d H:i:s T' );
        $already_displayed_promo = get_option( '_plugin_name_limited_time_promo', [] );

        foreach ( $promos as $promo ) {

            if(isset($promo['show_user_based']) && $promo['show_user_based'] == true){
                $already_displayed_promo_to_users = get_option( '_plugin_name_limited_time_promo_to_users', [] );
                $user_id = get_current_user_id();
                error_log(print_r($already_displayed_promo_to_users, true));
                if ( in_array($user_id, $already_displayed_promo_to_users, true ) ) {
                    continue;
                }
            }else{
                if ( in_array( $promo['key'], $already_displayed_promo, true ) ) {
                    continue;
                }
            }

            if ( $est_time_now >= $promo['start_date'] && $est_time_now <= $promo['end_date'] ) {
                $notices[] = $promo;
            }
        }
        uasort( $notices, [ $this, 'plugin_name_sort_notices_by_priority' ] );

        return array_values( $notices );
    }


    /**
     * Sort all notices depends on priority key
     *
     * @param array $current_notice
     * @param array $next_notice
     *
     * @since 1.0.0
     *
     * @return integer
     */
    private static function plugin_name_sort_notices_by_priority( $current_notice, $next_notice ) {
        if ( isset( $current_notice['priority'] ) && isset( $next_notice['priority'] ) ) {
            return $current_notice['priority'] - $next_notice['priority'];
        }

        return -1;
    }


    /**
     * Render promotional notices html
     *
     * @return void
     */
    public function render_promo_notices_html() {
        $notice = $this->plugin_name_get_promo_notices();

        if ( empty( $notice ) ) {
            return;
        }

         $this->plugin_name_get_template(
            $this->template_name, [
                'notice' => $notice,
            ],
             $this->template_path
        );
    }

    /**
     * Get other templates (e.g. product attributes) passing attributes and including the file.
     *
     * @param mixed  $template_name
     * @param array  $args          (default: array())
     * @param string $template_path (default: '')
     * @param string $default_path  (default: '')
     *
     * @return void
     */
    public function plugin_name_get_template( $template_name, $args = [], $template_path = '', $default_path = '' ) {
        if ( $args && is_array( $args ) ) {
            extract( $args );
        }

        $located = $this->plugin_name_locate_template( $template_name, $template_path, $default_path );

        if ( ! file_exists( $located ) ) {
            _doing_it_wrong( __FUNCTION__, sprintf( '<code>%s</code> does not exist.', esc_html( $located ) ), '2.1' );

            return;
        }

        do_action( 'plugin_name_before_template_part', $template_name, $template_path, $located, $args );

        include $located;

        do_action( 'plugin_name_after_template_part', $template_name, $template_path, $located, $args );
    }

    /**
     * Locate a template and return the path for inclusion.
     *
     * This is the load order:
     *
     *      yourtheme       /   $template_path  /   $template_name
     *      yourtheme       /   $template_name
     *      $default_path   /   $template_name
     *
     * @param mixed  $template_name
     * @param string $template_path (default: '')
     * @param string $default_path  (default: '')
     *
     * @return string
     */
    public function plugin_name_locate_template( $template_name, $template_path = '', $default_path = '' ) {
        if ( ! $template_path ) {
            $template_path = $this->template_path;
        }
        if ( ! $default_path ) {
            $default_path = $this->default_path;
        }

        $template = $template_path . '/'.$template_name;

        // Get default template
        if ( ! $template ) {
            $template = $default_path . '/' . $template_name;
        }

        // Return what we found
        return apply_filters( 'plugin_name_locate_template', $template, $template_name, $template_path );
    }
    /**
     * Function current_datetime() compatibility for wp version < 5.3
     *
     * @since 1.0.0
     *
     * @return DateTimeImmutable
     */
    public function plugin_name_current_datetime() {
        if ( function_exists( 'current_datetime' ) ) {
            return current_datetime();
        }

        return new DateTimeImmutable( 'now', $this->plugin_name_wp_timezone() );
    }

    /**
     * Function wp_timezone() compatibility for wp version < 5.3
     *
     * @since 1.0.0
     *
     * @return \DateTimeZone
     */
    public function plugin_name_wp_timezone() {
        if ( function_exists( 'wp_timezone' ) ) {
            return wp_timezone();
        }

        return new \DateTimeZone( $this->plugin_name_wp_timezone_string() );
    }

    /**
     * Function wp_timezone_string() compatibility for wp version < 5.3
     *
     * @since 1.0.0
     *
     * @return string
     */
    public function plugin_name_wp_timezone_string() {
        if ( function_exists( 'wp_timezone_string' ) ) {
            return wp_timezone_string();
        }

        $timezone_string = get_option( 'timezone_string' );

        if ( $timezone_string ) {
            return $timezone_string;
        }

        $offset  = (float) get_option( 'gmt_offset' );
        $hours   = (int) $offset;
        $minutes = ( $offset - $hours );

        $sign      = ( $offset < 0 ) ? '-' : '+';
        $abs_hour  = abs( $hours );
        $abs_mins  = abs( $minutes * 60 );
        $tz_offset = sprintf( '%s%02d:%02d', $sign, $abs_hour, $abs_mins );

        return $tz_offset;
    }

    /**
     * Get a formatted date from WordPress format
     *
     * @param string|timestamp $date the date string or timestamp
     * @param string|bool $format date format string or false for default WordPress date
     *
     * @since 1.0.0
     *
     * @return string|false The date, translated if locale specifies it. False on invalid timestamp input.
     */
    public function plugin_name_format_date( $date = '', $format = false ) {
        // if date is empty, get current datetime timestamp
        if ( empty( $date ) ) {
            $date = $this->plugin_name_current_datetime()->getTimestamp();
        }

        // if no format is specified, get default WordPress date format
        if ( ! $format ) {
            $format = wc_date_format();
        }

        // if date is not timestamp, convert it to timestamp
        if ( ! is_numeric( $date ) && strtotime( $date ) ) {
            $date = $this->plugin_name_current_datetime()->modify( $date )->getTimestamp();
        }

        if ( function_exists( 'wp_date' ) ) {
            return wp_date( $format, $date );
        }

        return date_i18n( $format, $date );
    }
}

