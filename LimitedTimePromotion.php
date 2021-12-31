<?php


include  'Promotions.php';


/**
 * Limited time promotion class
 *
 * For displaying limited time promotion in admin panel
 *
 * @since 1.0.0
 *
 * @package challan
 */
class LimitedTimePromotion {

    public $template_path;
    public $promotions;
    public $template_name;
    /**
     * LimitedTimePromotion constructor
     */
    public function __construct( $json_url, $template_name, $template_path , $default_path = '') {
        $this->template_path = $template_path;
        $this->template_name = $template_name;
        $this->promotions = new Promotions($json_url, $template_name, $template_path , $default_path );
        add_action( 'admin_notices', [ $this, 'render_promo_notices_html' ] );
        add_action( 'wp_ajax_plugin_name_dismiss_limited_time_promotional_notice', [ $this, 'dismiss_limited_time_promo' ] );
    }

    /**
     * Render promotional notices html
     *
     * @return void
     */
    public function render_promo_notices_html() {
        $notices = $this->promotions->plugin_name_get_promo_notices();

        if ( empty( $notices ) ) {
            return;
        }

        foreach ($notices as $notice){
            $this->promotions->plugin_name_get_template(
                $this->template_name, [
                'notice' => $notice,
            ],
                $this->template_path
            );
        }
    }

    /**
     * Dismisses limited time promo notice
     */
    public function dismiss_limited_time_promo() {
        if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['nonce'] ) ), 'plugin_name_admin' ) ) {
            wp_send_json_error( __( 'Invalid nonce', 'challan-lite' ) );
        }

        if ( ! current_user_can( 'manage_woocommerce' ) ) {
            wp_send_json_error( __( 'You have no permission to do that', 'challan-lite' ) );
        }

        $key = isset( $_POST['key'] ) ? sanitize_text_field( wp_unslash( $_POST['key'] ) ) : '';
        $option_key = isset( $_POST['option_key'] ) ? sanitize_text_field( wp_unslash( $_POST['option_key'] ) ) : '';
        $option_value = ( $option_key == '_plugin_name_limited_time_promo_to_users') ? get_current_user_id() : $key ;
        if ( ! empty( $key ) && ! empty( sanitize_text_field( wp_unslash( $_POST['plugin_name_limited_time_promotion_dismissed'] ) ) ) ) {
            $already_displayed_promo   = get_option( $option_key, [] );
            $already_displayed_promo[] = $option_value;

            update_option( $option_key, $already_displayed_promo );
            wp_send_json_success();
        }
    }
}

