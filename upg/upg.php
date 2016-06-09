<?php
/**
 * Plugin Name: Hosted Payments for WooCommerce
 * Description: Accept payments for Cards, Paypal and sofort
 * Version: 2.1.1
 * Author: UPG
 * Text Domain: upg
 */
function upg_wc_plugin_init ()
{
    if (!class_exists('WooCommerce'))
    {
        /*
         * Plugin depends on WooCommerce
         * is_plugin_active() is not available yet :(
         */
        return;
    }

    load_plugin_textdomain('upg', false, dirname(plugin_basename(__FILE__)) . '/languages/');

    require_once(plugin_dir_path( __FILE__ ) . '/branding.php');
    /**
     * Install method check if there is an update to apply base on the db version flag
     */
    require_once(plugin_dir_path( __FILE__ ) . '/includes/class-wc-gateway-upg-install.php');
    WC_Gateway_Upg_Install::install();

    require_once(plugin_dir_path( __FILE__ ) . '/class-wc-gateway-hostedPayments.php');
    require_once(plugin_dir_path( __FILE__ ) . '/includes/class-wc-gateway-upg-helper.php');
    require_once(plugin_dir_path( __FILE__ ) . '/includes/class-wc-gateway-upg-log.php');
    require_once(plugin_dir_path( __FILE__ ) . '/includes/class-wc-gateway-upg-admin-order-metabox.php');
    require_once(plugin_dir_path( __FILE__ ) . '/includes/class-wc-gateway-upg-riskclass.php');
    new WC_Gateway_Upg_Admin_Order_Metabox();
    new WC_Gateway_Upg_Riskclass();

    add_filter( 'woocommerce_payment_gateways', 'upg_wc_plugin_add_gateway_class' );
}

function upg_wc_plugin_activation_hook ()
{
    if (!is_plugin_active('woocommerce/woocommerce.php')) {
        $title = sprintf(
            __('Could not activate plugin %s', 'upg'),
            'Payco for WooCommerce'
        );
        $message = ''
            . '<h1><strong>' . $title . '</strong></h1><br/>'
            . 'WooCommerce plugin not activated. Please activate WooCommerce plugin first.';

        wp_die($message, $title, array('back_link' => true));
        return;
    }
    require_once(plugin_dir_path( __FILE__ ) . '/includes/class-wc-gateway-upg-install.php');
    WC_Gateway_Upg_Install::install();
}

function upg_wc_plugin_add_gateway_class()
{
    $methods[] = 'WC_Gateway_Hosted_Payments';
    return $methods;
}

function upg_wc_plugin_add_order_post_status()
{
    register_post_status( 'wc-payco-chargeback', array(
        'label'                     => __( 'Payment Chargeback', 'upg' ),
        'public'                    => false,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Charge Back <span class="count">(%s)</span>', 'Charge Backs <span class="count">(%s)</span>', 'upg' )
    ) );

    register_post_status( 'wc-payco-cleared', array(
        'label'                     => __( 'Payment Cleared', 'upg' ),
        'public'                    => false,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Payment Cleared <span class="count">(%s)</span>', 'Payments Cleared <span class="count">(%s)</span>', 'upg' )
    ) );

    register_post_status( 'wc-payco-in-dunning', array(
        'label'                     => __( 'In Dunning', 'upg' ),
        'public'                    => false,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'In Dunning <span class="count">(%s)</span>', 'In Dunning <span class="count">(%s)</span>', 'upg' )
    ) );

    register_post_status( 'wc-payco-pending', array(
        'label'                     => __( 'Merchant Feedback Pending', 'upg' ),
        'public'                    => false,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Feedback Pending <span class="count">(%s)</span>', 'Feedback Pending <span class="count">(%s)</span>', 'upg' )
    ) );

    register_post_status( 'wc-payco-fraud-pending', array(
        'label'                     => __( 'Fraud Check Pending', 'upg' ),
        'public'                    => false,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Approved (%s)', 'Approved (%s)', 'upg' )
    ) );

    register_post_status( 'wc-payco-fraud-cancelled', array(
        'label'                     => __( 'Fraud Cancelled', 'upg' ),
        'public'                    => false,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Fraud Cancelled (%s)', 'Fraud Cancelled (%s)', 'upg' )
    ) );

    register_post_status( 'wc-payco-cia-pending', array(
        'label'                     => __( 'Cash in Advance Pending', 'upg' ),
        'public'                    => false,
        'exclude_from_search'       => false,
        'show_in_admin_all_list'    => true,
        'show_in_admin_status_list' => true,
        'label_count'               => _n_noop( 'Cash in Advance Pendin (%s)', 'Cash in Advance Pendin (%s)', 'upg' )
    ) );

}

function upg_wc_plugin_add_order_status($orderStatuses)
{
    $orderStatuses['wc-payco-chargeback'] = __( 'Payment Chargeback', 'upg' );
    $orderStatuses['wc-payco-cleared'] = __( 'Payment Cleared', 'upg' );
    $orderStatuses['wc-payco-in-dunning'] = __( 'In Dunning', 'upg' );
    $orderStatuses['wc-payco-pending'] = __( 'Merchant Feedback Pending', 'upg' );
    $orderStatuses['wc-payco-fraud-pending'] = __( 'Fraud Check Pending', 'upg' );
    $orderStatuses['wc-payco-fraud-cancelled'] = __( 'Fraud Cancelled', 'upg' );
    $orderStatuses['wc-payco-cia-pending'] = __( 'Cash in Advance Pending', 'upg' );

    return $orderStatuses;
}

register_activation_hook(__FILE__, 'upg_wc_plugin_activation_hook');
add_filter( 'init', 'upg_wc_plugin_add_order_post_status' );
add_action('plugins_loaded', 'upg_wc_plugin_init');
add_filter( 'wc_order_statuses', 'upg_wc_plugin_add_order_status' );