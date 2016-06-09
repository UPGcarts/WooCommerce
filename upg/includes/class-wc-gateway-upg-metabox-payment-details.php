<?php

class WC_Gateway_Upg_Metabox_Payment_Details
{
    public static function getTransactionStatus(\Upg\Library\Config $config, WC_Order $order)
    {
        try {

            $request = new \Upg\Library\Request\GetTransactionStatus($config);
            $request->setOrderID($order->get_order_number());

            $apiEndPoint = new \Upg\Library\Api\GetTransactionStatus($config, $request);
            $result = $apiEndPoint->sendRequest();
            return $result;

        }catch(\Exception $e){
            WC_Gateway_Upg_Payment_Log::logError("When outputing details in admin for ".$order->id." Got: ".$e->getMessage());
        }

        return false;
    }

    public static function output( $post )
    {
        global $theorder;

        // This is used by some callbacks attached to hooks such as woocommerce_order_actions which rely on the global to determine if actions should be displayed for certain orders.
        if ( ! is_object( $theorder ) ) {
            $theorder = wc_get_order( $post->ID );
        }
        if($theorder->payment_method !== WC_Gateway_Hosted_Payments::MODULE_ID) {
            $text = __('Order was placed using %s','upg');
            echo "<p>".sprintf($text, $theorder->payment_method_title)."</p>";
            return;
        }

        $payment_gateways = WC()->payment_gateways->payment_gateways();
        /**
         * @var WC_Gateway_Upg $paymentModule
         */
        $paymentModule = $payment_gateways[WC_Gateway_Hosted_Payments::MODULE_ID];
        $status = self::getTransactionStatus($paymentModule->getUpgConfig($paymentModule->settings), $theorder);
        if(empty($status)) {
            echo "<p>".__('Order status could not be looked up','upg')."</p>";
            return;
        }
        $additionalData = $status->getData('additionalData');
        ?>
        <ul class="payco_payment_details">
            <li class="paymentMethod"><?php echo esc_attr(WC_Gateway_Upg_Helper::translatePaymentInfoLabelAdmin('paymentMethod')); ?> <strong><?php echo wptexturize(WC_Gateway_Upg_Helper::getOrderPaymentMethodString($additionalData['paymentMethod'])); ?></strong></li>
            <?php foreach($additionalData as $label=>$value): ?>
                <?php if($label != 'paymentMethod'): ?>
                    <li class="<?php echo esc_attr($label); ?>"><?php echo esc_attr(WC_Gateway_Upg_Helper::translatePaymentInfoLabelAdmin($label)); ?> <strong><?php echo wptexturize($value); ?></strong></li>
                <?php endif; ?>
            <?php endforeach; ?>
        </ul>
        <?php
    }
}