<?php

class WC_Gateway_Upg_Metabox_Api_Actions
{
    const API_ACTION_NAME = 'api_action';
    const API_ACTION_FINISH_VALUE = 'finish';
    const API_ACTION_CANCEL_VALUE = 'cancel';

    public static function output( $post )
    {
        global $wp;
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
        $currentUrl = admin_url('post.php?post='.$post->ID.'&action=edit');
        $finishActionUrl = self::addGetArgsToUrl($currentUrl, array(self::API_ACTION_NAME=>self::API_ACTION_FINISH_VALUE));
        $cancelActionUrl = self::addGetArgsToUrl($currentUrl, array(self::API_ACTION_NAME=>self::API_ACTION_CANCEL_VALUE));
        ?>
        <ul class="upg_payment_api_actions">
            <a href="#" id="upg_api_action_finish_call" onclick="upg_api_action_finish_call();" class="button button-primary button-large"><?php echo __('Send Finish Call','upg'); ?></a>
            <a href="#" id="upg_api_action_cancel_call" onclick="upg_api_action_cancel_call();" class="button button-primary button-large"><?php echo __('Send Cancel Call','upg'); ?></a>
        </ul>
        <script type="text/javascript">
            function upg_api_action_finish_call() {
                if (confirm("<?php echo __('Are you sure you want to send the finish call','upg'); ?>")) {
                    window.location.href="<?php echo $finishActionUrl; ?>";
                }
                return false;
            }
            function upg_api_action_cancel_call() {
                if (confirm("<?php echo __('Are you sure you want to send the cancel call','upg'); ?>")) {
                    window.location.href="<?php echo $cancelActionUrl; ?>";
                }
                return false;
            }
        </script>
        <?php
    }

    private static function addGetArgsToUrl($url, array $args)
    {
        if(stripos($url,'?') == false) {
            $url .= '?';
        }

        foreach($args as $name=>$value) {
            $url .= '&'.$name.'='.$value;
        }

        return $url;
    }

    public static function doRequest()
    {
        if(array_key_exists(self::API_ACTION_NAME, $_GET)) {
            if($_GET[self::API_ACTION_NAME] == self::API_ACTION_FINISH_VALUE) {
                //save and do the capture
                try {
                    $order = wc_get_order(intval($_GET['post']));

                    $payment_gateways = WC()->payment_gateways->payment_gateways();
                    /**
                     * @var WC_Gateway_Upg $paymentModule
                     */
                    $paymentModule = $payment_gateways[WC_Gateway_Hosted_Payments::MODULE_ID];
                    $config = $paymentModule->getUpgConfig($paymentModule->settings);

                    $request = new \Upg\Library\Request\Finish($config);
                    $request->setOrderID($order->get_order_number());
                    $apiEndPoint = new \Upg\Library\Api\Finish($config, $request);
                    $apiEndPoint->sendRequest();
                    WC_Gateway_Upg_Admin_Order_Metabox::addConfirmation(__('Finish cancel sent','upg'));
                }catch (Exception $e){
                    $text = __('There was an error sending finish request : %s','upg');
                    WC_Gateway_Upg_Admin_Order_Metabox::addError(sprintf($text, $e->getMessage()));
                }
            }

            if($_GET[self::API_ACTION_NAME] == self::API_ACTION_CANCEL_VALUE) {
                try {
                    $order = wc_get_order(intval($_GET['post']));

                    $payment_gateways = WC()->payment_gateways->payment_gateways();
                    /**
                     * @var WC_Gateway_Upg $paymentModule
                     */
                    $paymentModule = $payment_gateways[WC_Gateway_Hosted_Payments::MODULE_ID];
                    $config = $paymentModule->getUpgConfig($paymentModule->settings);

                    $request = new \Upg\Library\Request\Cancel($config);
                    $request->setOrderID($order->get_order_number());
                    $apiEndPoint = new \Upg\Library\Api\Cancel($config, $request);
                    $apiEndPoint->sendRequest();
                    WC_Gateway_Upg_Admin_Order_Metabox::addConfirmation(__('Cancel request sent','upg'));
                }catch (Exception $e){
                    $text = __('There was an error sending cancel request : %s','upg');
                    WC_Gateway_Upg_Admin_Order_Metabox::addError(sprintf($text, $e->getMessage()));
                }
            }
            WC_Gateway_Upg_Admin_Order_Metabox::saveMessages();
        }
    }
}