<?php

class WC_Gateway_Upg_Create_Order
{
    public function __construct()
    {
        add_filter('woocommerce_create_order', array($this,'cleanOrder'));
    }

    public function cleanOrder()
    {
        $order_id = absint( WC()->session->order_awaiting_payment );

        if(empty($order_id)) {
            return;
        }
        $orderSent = get_post_meta($order_id, '_payco_transaction_sent_request', true);
        if($orderSent) {
            //clean out the old order
            unset(WC()->session->order_awaiting_payment);
        }
    }
}