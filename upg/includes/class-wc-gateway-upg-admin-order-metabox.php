<?php

class WC_Gateway_Upg_Admin_Order_Metabox
{
    /**
     * @var \Upg\Library\Config
     */
    private $config;

    /**
     * @var array
     */
    private $moduleSettings;

    private static $savedMetaBoxes = false;

    public static $errorMessages  = array();
    public static $confirmationMessages  = array();

    public function __construct()
    {
        require_once(plugin_dir_path( __FILE__ ) . '/class-wc-gateway-upg-metabox-payment-details.php');
        require_once(plugin_dir_path( __FILE__ ) . '/class-wc-gateway-upg-metabox-capture.php');
        require_once(plugin_dir_path( __FILE__ ) . '/class-wc-gateway-upg-metabox-refund.php');
        require_once(plugin_dir_path( __FILE__ ) . '/class-wc-gateway-upg-metabox-api-actions.php');

        add_action( 'add_meta_boxes', array( $this, 'addMetaBoxes' ));
        add_action( 'save_post', array( $this, 'saveMetaBoxes' ), 1, 2 );
        add_action( 'admin_init', array( $this, 'apiActions' ), 1, 2 );

        add_action( 'woocommerce_payco_save_meta', 'WC_Gateway_Upg_Metabox_Capture::save', 10, 2);
        add_action( 'woocommerce_payco_save_meta', 'WC_Gateway_Upg_Metabox_Refund::save', 20, 2);

        add_action( 'woocommerce_upg_api_actions', 'WC_Gateway_Upg_Metabox_Api_Actions::doRequest', 20, 2);

        add_action( 'admin_notices', array( $this, 'showConfirmation' ) );
        add_action( 'admin_notices', array( $this, 'showError' ) );
        add_action( 'shutdown', array( $this, 'saveMessages' ) );
    }

    public function addMetaBoxes()
    {
        foreach ( wc_get_order_types( 'order-meta-boxes' ) as $type ) {
            $order_type_object = get_post_type_object( $type );

            add_meta_box( 'woocommerce-payco-order-payment-details', sprintf( __( 'Payment Details', 'paycoDetails' ), $order_type_object->labels->singular_name ), 'WC_Gateway_Upg_Metabox_Payment_Details::output', $type, 'side', 'high' );
            add_meta_box( 'woocommerce-payco-order-payment-capture', sprintf( __( UPG_MODULE_TITLE.' Payment Captures', 'paycoCapture' ), $order_type_object->labels->singular_name ), 'WC_Gateway_Upg_Metabox_Capture::output', $type, 'side', 'high' );
            add_meta_box( 'woocommerce-payco-order-payment-refund', sprintf( __( UPG_MODULE_TITLE.' Payment Refunds', 'paycoRefund' ), $order_type_object->labels->singular_name ), 'WC_Gateway_Upg_Metabox_Refund::output', $type, 'side', 'high' );
            add_meta_box( 'woocommerce-upg-order-payment-refund', sprintf( __( UPG_MODULE_TITLE.' API', 'upgApi' ), $order_type_object->labels->singular_name ), 'WC_Gateway_Upg_Metabox_Api_Actions::output', $type, 'side', 'high' );
        }
    }

    public function apiActions()
    {
        do_action( 'woocommerce_upg_api_actions');
    }

    public function saveMetaBoxes( $post_id, $post )
    {
        // $post_id and $post are required
        if ( empty( $post_id ) || empty( $post ) || self::$savedMetaBoxes ) {
            return;
        }

        // Dont' save meta boxes for revisions or autosaves
        if ( defined( 'DOING_AUTOSAVE' ) || is_int( wp_is_post_revision( $post ) ) || is_int( wp_is_post_autosave( $post ) ) ) {
            return;
        }

        // Check the nonce
        if ( empty( $_POST['woocommerce_meta_nonce'] ) || ! wp_verify_nonce( $_POST['woocommerce_meta_nonce'], 'woocommerce_save_data' ) ) {
            return;
        }

        // Check the post being saved == the $post_id to prevent triggering this call for other save_post events
        if ( empty( $_POST['post_ID'] ) || $_POST['post_ID'] != $post_id ) {
            return;
        }

        // Check user has permission to edit
        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }

        // We need this save event to run once to avoid potential endless loops. This would have been perfect:
        //	remove_action( current_filter(), __METHOD__ );
        // But cannot be used due to https://github.com/woothemes/woocommerce/issues/6485
        // When that is patched in core we can use the above. For now:
        self::$savedMetaBoxes = true;

        if ( in_array( $post->post_type, wc_get_order_types( 'order-meta-boxes' ) ) ) {
            $order = wc_get_order( $post->ID );
            if($order->payment_method == WC_Gateway_Hosted_Payments::MODULE_ID) {
                do_action( 'woocommerce_payco_save_meta', $post_id, $post );
            }
        }

    }

    public static function addError($message)
    {
        self::$errorMessages[] = $message;
    }

    public static function addConfirmation($message)
    {
        self::$confirmationMessages[] = $message;
    }

    public static function saveMessages()
    {
        update_option( 'woocommerce_payco_meta_box_message_confirmation', self::$confirmationMessages );
        update_option( 'woocommerce_payco_meta_box_message_errors', self::$errorMessages );
    }

    public static function showError()
    {
        $errors = maybe_unserialize( get_option( 'woocommerce_payco_meta_box_message_errors' ) );

        if(!empty($errors)) {
            echo '<div class="error notice is-dismissible">';
            foreach($errors as $message) {
                echo "<p>{$message}</p>";
            }
            echo '</div>';
        }

        delete_option( 'woocommerce_payco_meta_box_message_errors' );
    }

    public static function showConfirmation()
    {
        $messages = maybe_unserialize( get_option( 'woocommerce_payco_meta_box_message_confirmation' ) );

        if(!empty($messages)) {
            echo '<div class="updated notice is-dismissible">';
            foreach($messages as $message) {
                echo "<p>{$message}</p>";
            }
            echo '</div>';
        }

        delete_option( 'woocommerce_payco_meta_box_message_confirmation' );
    }
}