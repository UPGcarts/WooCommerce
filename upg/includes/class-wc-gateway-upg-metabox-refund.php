<?php

class WC_Gateway_Upg_Metabox_Refund
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

        $captures = $wpdb->get_results($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}woocommerce_payco_captures WHERE order_id=%d",$theorder->id),
            ARRAY_A);

        $refunds = $wpdb->get_results($wpdb->prepare(
            "SELECT refunds.*, captures.capture_reference
            FROM {$wpdb->prefix}woocommerce_payco_refunds AS refunds
            INNER JOIN {$wpdb->prefix}woocommerce_payco_captures AS captures ON refunds.capture_id = captures.capture_id
            WHERE captures.order_id=%d",$theorder->id),
            ARRAY_A);
        ?>
        <ul class="payco_payment_refund">
            <?php foreach($refunds as $refund): ?>
                <li data-refund-amount="<?php echo $refund['refund_amount']; ?>" data-refund-capture="<?php echo $refund['capture_reference']; ?>">
                    <dl>
                        <dt><?php _e( 'Capture Reference:', 'upg' ); ?></dt>
                        <dd><?php echo $refund['capture_reference']; ?></dd>
                        <dt><?php _e( 'Refund Amount:', 'upg' ); ?></dt>
                        <dd><?php echo wc_price($refund['refund_amount']); ?></dd>
                        <dt><?php _e( 'Refund Reason:', 'upg' ); ?></dt>
                        <dd><?php echo wptexturize($refund['refund_reason']); ?></dd>
                    </dl>
                </li>
            <?php endforeach; ?>
        </ul>
        <?php if(count($captures) > 0): ?>
            <div action="" method="post" id="payco_refund_form">
                <dl>
                    <dd>
                        <select id="payco_refund_capture_id" name="payco_refund_capture_id">
                            <option value=""><?php _e( 'Please select an capture', 'upg' ); ?></option>
                            <?php foreach($captures as $capture): ?>
                                <option value="<?php echo $capture['capture_id']; ?>">
                                    <?php echo wptexturize($capture['capture_reference']); ?> : <?php echo wc_price($capture['capture_amount']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </dd>
                    <dt><label for="payco_refund_amount"><?php _e( 'Refund Amount:', 'upg' ); ?></label></dt>
                    <dd><input type="text" name="payco_refund_amount" id="payco_refund_amount" /></dd>
                    <dt><label for="payco_refund_reason"><?php _e( 'Refund Reason:', 'upg' ); ?></label></dt>
                    <dd><input type="text" name="payco_refund_reason" id="payco_refund_reason" /></dd>
                </dl>
                <button id="btn-payco-refund" class="button button-primary button-large"><?php _e( 'Refund', 'upg' ); ?></button>
            </div>
        <?php else: ?>
            <p><?php _e( 'Order currently has no available captures to refund from', 'upg' ); ?></p>
        <?php endif; ?>
        <?php

    }

    private static function process($data)
    {
        return !empty($data['payco_refund_amount']) || !empty($data['payco_refund_amount']) || !empty($data['payco_refund_reason']);
    }

    public static function save( $post_id, $post )
    {
        global $wpdb;

        $order = wc_get_order( $post->ID );

        //check if the refund form has been posted
        if(!self::process($_POST)) {
            return;
        }

        //save and do the refund
        $payment_gateways = WC()->payment_gateways->payment_gateways();
        /**
         * @var WC_Gateway_Upg $paymentModule
         */
        $paymentModule = $payment_gateways[WC_Gateway_Hosted_Payments::MODULE_ID];
        $config = $paymentModule->getUpgConfig($paymentModule->settings);

        $amount = str_replace(',','.', $_POST['payco_refund_amount']);
        $amountInt = WC_Gateway_Upg_Helper::convertPriceToInt($amount);

        $captureId = intval($_POST['payco_refund_capture_id']);
        $reason = trim($_POST['payco_refund_reason']);

        $errors = false;

        if(empty($amountInt)) {
            $text = __('Please provide an refund amount.','upg');
            WC_Gateway_Upg_Admin_Order_Metabox::addError($text);
            $errors = true;
        }

        if(empty($captureId)) {
            $text = __('Please provide an capture to refund from.','upg');
            WC_Gateway_Upg_Admin_Order_Metabox::addError($text);
            $errors = true;
        }

        if(empty($reason)) {
            $text = __('Please provide an reason for the refund.','upg');
            WC_Gateway_Upg_Admin_Order_Metabox::addError($text);
            $errors = true;
        }

        if($errors) {
            return;
        }

        try{

            $captureReference = $wpdb->get_var($wpdb->prepare(
                "SELECT capture_reference FROM {$wpdb->prefix}woocommerce_payco_captures WHERE capture_id=%d",
                $captureId)
            );

            $request = new \Upg\Library\Request\Refund($config);
            $request->setCaptureID($captureReference)
                ->setOrderID($order->get_order_number())
                ->setAmount(new \Upg\Library\Request\Objects\Amount($amountInt))
                ->setRefundDescription($reason);

            $apiEndPoint = new \Upg\Library\Api\Refund($config, $request);
            $apiEndPoint->sendRequest();

            $wpdb->insert(
                $wpdb->prefix.'woocommerce_payco_refunds',
                array(
                    'capture_id' => $captureId,
                    'refund_amount' => $amount,
                    'refund_reason' => esc_sql($reason),
                ),
                array(
                    '%d',
                    '%f',
                    '%s',
                )
            );

            wc_create_refund(array(
                'amount'     => $amount,
                'reason'     => $reason,
                'order_id'   => $post_id,
            ));

            $text = __('Redunded %s','upg');
            WC_Gateway_Upg_Admin_Order_Metabox::addConfirmation(sprintf($text, $amount));


        }catch (Exception $e){
            $text = __('There was an API error : %s','upg');
            WC_Gateway_Upg_Admin_Order_Metabox::addError(sprintf($text, $e->getMessage()));
        }

        return;
    }
}