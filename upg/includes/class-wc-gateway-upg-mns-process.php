<?php

class WC_Gateway_Upg_Mns_Process
{
    private static $setting;

    public static function setSettings(array $settings)
    {
        self::$setting = $settings;
    }

    public static function processMessage($mns, wpdb $db)
    {
        $order = new WC_Order($mns->order_id);
        $paymentMethod = get_post_meta($order->id, '_payment_method', true);

        if($paymentMethod != WC_Gateway_Hosted_Payments::MODULE_ID) {
            return false;
        }

        $mnsOrderStatus = trim(strtoupper($mns->order_status));
        $mnsTransactionStatus = trim(strtoupper($mns->transaction_status));

        $processed = false;
        $orderStatusProcess = false;

        if(!empty($mnsTransactionStatus)) {
            switch($mnsTransactionStatus) {
                case 'FRAUDCANCELLED':
                    self::mnsTransactionStatusFraudCancel($order);
                    $processed = true;
                    break;
                case 'CANCELLED':
                case 'EXPIRED':
                    self::mnsTransactionStatusCancel($order);
                    $processed = true;
                    break;
                case 'NEW':
                    $processed = true;
                case 'ACKNOWLEDGEPENDING':
                    self::mnsTransactionStatusAcknowledgePending($order);
                    $processed = true;
                    break;
                case 'FRAUDPENDING':
                    self::mnsTransactionStatusFraudPending($order);
                    $processed = true;
                    break;
                case 'CIAPENDING':
                    self::mnsTransactionStatusCiaPending($order);
                    $processed = true;
                    break;
                case 'MERCHANTPENDING':
                    self::mnsTransactionStatusMerchantPending($order);
                    $processed = true;
                    break;
                case 'INPROGRESS':
                    self::mnsTransactionStatusInProgress($order);
                    $orderStatusProcess = true;
                    $processed = true;
                    break;
                case 'DONE':
                    self::mnsTransactionStatusDone($order);
                    $orderStatusProcess = true;
                    $processed = true;
                    break;
                default:
                    $processed = false;
                    break;
            }
        }

        if(!empty($mnsOrderStatus) && ($orderStatusProcess || !$processed)) {
            switch ($mnsOrderStatus) {
                case 'PAID':
                    self::mnsOrderStatusPaid($order, $db);
                    $processed = true;
                    break;
                case 'PAYPENDING':
                    self::mnsOrderStatusPayPending($order);
                    $processed = true;
                    break;
                case 'PAYMENTFAILED':
                    self::mnsOrderStatusPaymentFailed($order);
                    $processed = true;
                    break;
                case 'CHARGEBACK':
                    self::mnsOrderStatusChargeBack($order);
                    $processed = true;
                    break;
                case 'CLEARED':
                    self::mnsOrderStatusCleared($order);
                    $processed = true;
                    break;
                case 'CPM_MANAGED':
                case 'INDUNNING':
                    self::mnsOrderInDunning($order);
                    break;
                default:
                    $processed = false;
                    break;
            }
        }

        if($processed) {
            return true;
        }

        return false;
    }

    public static function mnsTransactionStatusFraudCancel(WC_Order $order)
    {
        self::updateStatus($order, self::$setting['payco_mns_fraud_cancelled']);
    }

    public static function mnsTransactionStatusCancel(WC_Order $order)
    {
        self::updateStatus($order, self::$setting['payco_mns_transaction_cancelled']);
    }

    public static function mnsTransactionStatusAcknowledgePending(WC_Order $order)
    {
        self::updateStatus($order, self::$setting['payco_mns_acknowledge_pending']);
    }

    public static function mnsTransactionStatusFraudPending(WC_Order $order)
    {
        self::updateStatus($order, self::$setting['payco_mns_fraud_pending']);
    }

    public static function mnsTransactionStatusCiaPending(WC_Order $order)
    {
        self::updateStatus($order, self::$setting['payco_mns_cia_pending']);
    }

    public static function mnsTransactionStatusMerchantPending(WC_Order $order)
    {
        self::updateStatus($order, self::$setting['payco_mns_merchant_pending']);
    }

    public static function mnsTransactionStatusInProgress(WC_Order $order)
    {
        self::updateStatus($order, self::$setting['payco_mns_transaction_in_progress']);
    }

    public static function mnsTransactionStatusDone(WC_Order $order)
    {
        self::updateStatus($order, self::$setting['mns_transaction_done']);
    }

    public static function mnsOrderStatusPaid(WC_Order $order, wpdb $db)
    {
        $autocapture = get_post_meta($order->id, '_payco_transaction_setting_autocapture', true);
        //ok now check if any autocaptures have been done
        $autoCaptured = $db->get_var($db->prepare("SELECT autocapture FROM {$db->prefix}woocommerce_payco_captures WHERE autocapture  = %d;", $order->id));

        if($autocapture && !$autoCaptured) {
            //ok go record an autocapture as happening
            //correct the captures
            $db->update(
                $db->prefix.'woocommerce_payco_captures',
                array(
                    'capture_amount' => 0,
                ),
                array('order_id' => $order->id),
                array('%f'),
                array('%d')
            );

            $db->insert(
                $db->prefix.'woocommerce_payco_captures',
                array(
                    'order_id' => $order->id,
                    'capture_amount' => $order->get_total(),
                    'capture_reference' => $order->get_order_number(),
                    'autocapture' => 1,
                ),
                array(
                    '%d',
                    '%f',
                    '%s',
                    '%d',
                )
            );
        }

        self::updateStatus($order, self::$setting['payco_mns_paid']);
    }

    public static function mnsOrderStatusPayPending(WC_Order $order)
    {
        self::updateStatus($order, self::$setting['payco_mns_paid_pending_reserve']);
    }

    public static function mnsOrderStatusPaymentFailed(WC_Order $order)
    {
        self::updateStatus($order, self::$setting['payco_mns_payment_failed']);
    }

    public static function mnsOrderStatusChargeBack(WC_Order $order)
    {
        self::updateStatus($order, self::$setting['payco_mns_payment_charge_back']);
    }

    public static function mnsOrderStatusCleared(WC_Order $order)
    {
        self::updateStatus($order, self::$setting['payco_mns_payment_cleared']);
    }

    public static function mnsOrderInDunning(WC_Order $order)
    {
        self::updateStatus($order, self::$setting['payco_mns_in_dunning']);
    }

    private static function updateStatus(WC_Order $order, $status)
    {
        $order->update_status($status);
    }

    public static function markMnsProcessed(wpdb $db, $id_mns)
    {
        $db->update(
            $db->prefix.'woocommerce_payco_mns_messages',
            array(
                'mns_processed' => 1,
                'mns_error_processing' => 0,
            ),
            array('id_mns' => $id_mns),
            array('%d', '%d'),
            array('%d')
        );
    }

    public static function markMnsError(wpdb $db, $id_mns, $processed = false)
    {
        $db->update(
            $db->prefix.'woocommerce_payco_mns_messages',
            array(
                'mns_processed' => ($processed?1:0),
                'mns_error_processing' => 1,
            ),
            array('id_mns' => $id_mns),
            array('%d', '%d'),
            array('%d')
        );
    }


}