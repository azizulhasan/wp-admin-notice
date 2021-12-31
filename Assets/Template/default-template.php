<?php
/**
 * @var array $notice
 */

$current_dir =  plugin_dir_url(__DIR__);
$notice['logo_url'] = ($notice['logo_url'])? $notice['logo_url'] :   $current_dir.'Images/logo.svg';
$notice['promotion_icon_url'] = ($notice['promotion_icon_url']) ? $notice['promotion_icon_url']:  $current_dir.'Images/promotion.svg';

?>

<div class="plugin-name-promo-notice plugin-name-promotion notice">
    <div class="notice-content">
        <div class="logo-wrap">
            <div class="plugin-name-logo"></div>
            <span class="plugin-name-icon plugin-name-icon-promotion"></span>
        </div>
        <div class="plugin-name-message">
            <h3><?php echo esc_html( $notice['title'] ); ?></h3>
            <div><?php echo esc_html( $notice['content']['main'] ); ?></div>
            <?php
            $details = $notice['content']['details'];
            if(count($details)){
                foreach ($details as $detail){?>
                    <p><?php echo esc_html( $detail ); ?></p>
                <?php }
            }
            $option_key = ($notice['show_user_based']) ? '_plugin_name_limited_time_promo_to_users' : '_plugin_name_limited_time_promo';
            ?>
            <a class="plugin-name-btn plugin-name-btn-primary" target="_blank" href="<?php echo esc_url( $notice['action_url'] ); ?>"><?php echo esc_html( $notice['action_title'] ); ?></a>
        </div>

        <button class="close-notice" title="<?php esc_attr_e( 'Dismiss this notice', 'plugin-name-lite' ); ?>" data-option_key="<?php echo esc_attr( $option_key ) ?>" data-key="<?php echo esc_attr( $notice['key'] ); ?>">
            <span class="dashicons dashicons-no-alt"></span>
        </button>
    </div>
</div>
<style>
    .plugin-name-promo-notice {
        position: relative;
    }

    .plugin-name-promo-notice.notice {
        border-width: 0;
        padding: 0;
        background: transparent;
        box-shadow: none;
    }

    .plugin-name-promo-notice.plugin-name-promotion {
        border-left: 2px solid #f1644d;
    }

    .plugin-name-promo-notice .notice-content {
        display: flex;
        padding: 16px 20px;
        border: 1px solid #dfe2e7;
        border-radius: 0 5px 5px 0;
        background: #fff;
    }

    .plugin-name-promo-notice .logo-wrap {
        position: relative;
    }

    .plugin-name-promo-notice .logo-wrap .plugin-name-logo {
        width: 60px;
        height: 60px;
        background-image: url(<?php echo $notice['logo_url'] ;?>);
        background-repeat: no-repeat;
        background-size: cover;
        background-position: center;
    }

    .plugin-name-promo-notice .logo-wrap .plugin-name-icon {
        width: 20px;
        height: 20px;
        position: absolute;
        top: -2px;
        right: -8px;
        border: 2px solid #fff;
        border-radius: 55px;
        background: #ffffff;
    }

    .plugin-name-promo-notice .logo-wrap .plugin-name-icon-promotion {
        background-image: url(<?php echo $notice['promotion_icon_url'] ;?>)
    }

    .plugin-name-promo-notice .plugin-name-message {
        margin: 0 23px;
    }

    .plugin-name-promo-notice .plugin-name-message h3 {
        margin: 0 0 10px;
        font-weight: bold;
        font-size: 18px;
        font-family: "SF Pro Text", sans-serif;
    }

    .plugin-name-promo-notice .plugin-name-message div,
    .plugin-name-promo-notice .plugin-name-message p
    {
        color: #4b4b4b;
        font-weight: 400;
        font-size: 14px;
        font-family: "SF Pro Text", sans-serif;
    }
    .plugin-name-promo-notice .plugin-name-message p{
        line-height: .7;
        font-size: 13px;
    }

    .plugin-name-promo-notice .plugin-name-message .plugin-name-btn {
        font-size: 12px;
        font-weight: 300;
        padding: 6px 12px;
        margin-right: 15px;
        margin-top: 10px;
        border-radius: 3px;
        border: 1px solid #2c7be5;
        cursor: pointer;
        transition: all .2s linear;
        text-decoration: none;
        font-family: "SF Pro Text", sans-serif;
        display: inline-block;
    }

    .plugin-name-promo-notice .plugin-name-message .plugin-name-btn-primary {
        color: #fff;
        background: #2c7be5;
        margin-right: 15px;
        font-weight: 400;
    }

    .plugin-name-promo-notice .plugin-name-message .plugin-name-btn-primary:hover {
        background: transparent;
        color: #2c7be5;
    }

    .plugin-name-promo-notice .plugin-name-message .plugin-name-btn:disabled {
        opacity: .7;
    }

    .plugin-name-promo-notice .plugin-name-message a {
        text-decoration: none;
    }

    .plugin-name-promo-notice .close-notice {
        position: absolute;
        top: 10px;
        right: 13px;
        border: 0;
        background: transparent;
        text-decoration: none;
    }

    .plugin-name-promo-notice .close-notice span {
        font-size: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: #c2c2c2;
        transition: all .2s ease;
        cursor: pointer;
        border: 1px solid #f3f3f3;
        border-radius: 55px;
        width: 20px;
        height: 20px;
    }

    .plugin-name-promo-notice .close-notice span:hover {
        color: #2c7be5;
        border-color: #2c7be5;
    }
</style>
<script type='text/javascript'>
    jQuery( document ).ready( function ( $ ) {
        $( 'body' ).on( 'click', '.plugin-name-promo-notice .close-notice', function ( e ) {
            e.preventDefault();

            let self = $( this ),
                key = self.data( 'key' ),
                option_key = self.data( 'option_key' );

            wp.ajax.send( 'plugin_name_dismiss_limited_time_promotional_notice', {
                data: {
                    plugin_name_limited_time_promotion_dismissed: true,
                    key: key,
                    option_key: option_key,
                    nonce: '<?php echo esc_attr( wp_create_nonce( 'plugin_name_admin' ) ); ?>'
                },
                complete: function ( resp ) {
                    self.closest( '.plugin-name-promo-notice' ).fadeOut( 200 );
                }
            } );
        } );
    } );
</script>
