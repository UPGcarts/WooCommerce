<?php

class WC_Gateway_Upg_Metabox_Capture
{

    public static function output( $post )
    {
        global $wpdb;
        global $theorder;

        if ( ! is_object( $theorder ) ) {
            $theorder = wc_get_order( $post->ID );
        }
        if($theorder->payment_method !== WC_Gateway_Hosted_Payments::MODULE_ID) {
            echo "<p>".__('Order was not processed by Payco','upg')."</p>";
            return;
        }

        if($theorder->has_status('wc-pending') || $theorder->has_status('wc-failed')) {
            echo "<p>".__('Capture operations can not be done in the current order state','upg')."</p>";
            return;
        }

        $results = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}woocommerce_payco_captures WHERE order_id=%d",$theorder->id),
            ARRAY_A);

        $amountLeftLabel = __("Capture Amount Left: %s",'upg');
        ?>
        <ul class="payco_payment_capture">
            <?php foreach($results as $captures): ?>
                <li data-payco-amount="<?php echo $captures['capture_amount']; ?>"><?php echo wptexturize($captures['capture_reference']); ?> - <?php echo wc_price($captures['capture_amount']); ?></li>
            <?php endforeach; ?>
        </ul>
        <?php if(self::canCapture($theorder)): ?>
        <form action="" method="post">
            <p>
                <p><?php echo sprintf($amountLeftLabel, self::calculateCaptureAmountLeft($theorder)); ?></p>
                <label for="payco_capture_amount"><?php _e( 'Capture Amount:', 'upg' ); ?></label>
                <input type="text" name="payco_capture_amount" id="payco_capture_amount" />
                <br />
                <button id="btn-payco-capture" class="button button-primary button-large"><?php _e( 'Capture', 'upg' ); ?></button>
            </p>
        </form>
        <?php else: ?>
            <p><?php echo __('As Auto-Capture was enabled no manual capture may be done until the paid notification.','upg'); ?></p>
        <?php endif; ?>
        <?php
    }

    public static function save( $post_id, $post )
    {
        global $wpdb;

        $order = wc_get_order( $post->ID );

        //save and do the capture
        $payment_gateways = WC()->payment_gateways->payment_gateways();
        /**
         * @var WC_Gateway_Upg $paymentModule
         */
        $paymentModule = $payment_gateways[WC_Gateway_Hosted_Payments::MODULE_ID];
        $config = $paymentModule->getUpgConfig($paymentModule->settings);

        if(!array_key_exists('payco_capture_amount', $_POST) || empty($_POST['payco_capture_amount'])) {
            return;
        }

        $amount = str_replace(',','.', $_POST['payco_capture_amount']);
        $amountInt = WC_Gateway_Upg_Helper::convertPriceToInt($amount);

        if(empty($amountInt)) {
            WC_Gateway_Upg_Admin_Order_Metabox::addError(__('You must provide an amount for capture.','upg'));
            return;
        }


        try{

            $captures = $wpdb->get_var("SELECT count(capture_id) AS captures FROM {$wpdb->prefix}woocommerce_payco_captures WHERE order_id={$order->id}");

            if(!empty($captures)) {
                $captures++;
            }else {
                $captures = 1;
            }

            $captureId = $order->get_order_number().':'.$captures;

            $request = new \Upg\Library\Request\Capture($config);
            $request->setOrderID($order->get_order_number())
                ->setAmount(new \Upg\Library\Request\Objects\Amount($amountInt))
                ->setCaptureID($captureId);

            $apiEndPoint = new \Upg\Library\Api\Capture($config, $request);

            $result = $apiEndPoint->sendRequest();

            $wpdb->insert(
                $wpdb->prefix.'woocommerce_payco_captures',
                array(
                    'order_id' => $order->id,
                    'capture_amount' => $amount,
                    'capture_reference' => $captureId,
                ),
                array(
                    '%d',
                    '%f',
                    '%s',
                )
            );

            $text = __('Captured %s','upg');
            WC_Gateway_Upg_Admin_Order_Metabox::addConfirmation(sprintf($text, $amount));

        }catch (Exception $e){
            $text = __('There was an API error : %s','upg');
            WC_Gateway_Upg_Admin_Order_Metabox::addError(sprintf($text, $e->getMessage()));
        }

        return;
    }

    private static function canCapture(WC_Order $order)
    {
        global $wpdb;

        $autocaptured = $wpdb->get_var($wpdb->prepare("SELECT autocapture FROM {$wpdb->prefix}woocommerce_payco_captures WHERE order_id = %d;", $order->id));

        $autocaptureEnabled = get_post_meta($order->id, '_payco_transaction_setting_autocapture', true);

        if(!$autocaptureEnabled) {
            return true;
        }

        if($autocaptured && $autocaptureEnabled) {
            return true;
        }

        return false;
    }

    private static function calculateCaptureAmountLeft(WC_Order $order)
    {
        global $wpdb;
        $paymentMethod = get_post_meta( $order->id, '_payco_payment_method', true );

        $captureAmount = $wpdb->get_var($wpdb->prepare(
            "SELECT SUM(capture_amount) AS capture_amount_sum FROM {$wpdb->prefix}woocommerce_payco_captures WHERE order_id=%d",$order->id)
        );

        switch($paymentMethod) {
            case 'BILL':
            case 'BILL_SECURE':
                $refunds = $wpdb->get_var($wpdb->prepare(
                    "SELECT SUM(refund_table.refund_amount) AS refundAmount
                    FROM {$wpdb->prefix}woocommerce_payco_refunds AS refund_table
                    INNER JOIN {$wpdb->prefix}woocommerce_payco_captures AS capture_table
                    ON capture_table.capture_id = refund_table.capture_id
                     WHERE capture_table.order_id=%d",$order->id)
                );

                if($refunds > 0) {
                    $captureAmount = $captureAmount - $refunds;
                }
                break;
            default;
                break;
        }

        $captureAmountLeft = $order->get_total() - $captureAmount;
        return wc_price($captureAmountLeft);

    }
}