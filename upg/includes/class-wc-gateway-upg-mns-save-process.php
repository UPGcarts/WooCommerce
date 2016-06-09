<?php

class WC_Gateway_Upg_Mns_Save_Process implements \Upg\Library\Mns\ProcessorInterface
{
    private $merchantID;
    private $storeID;
    private $orderID;
    private $captureID;
    private $merchantReference;
    private $paymentReference;
    private $userID;
    private $amount;
    private $currency;
    private $transactionStatus;
    private $orderStatus;
    private $additionalData;
    private $timestamp;
    private $version;

    /**
     * @var wpdb
     */
    private $wpdb;

    public function __construct(wpdb $db)
    {
        $this->wpdb = $db;
    }

    /**
     * @param $merchantID This is the merchantID assigned by PayCo.
     * @param $storeID This is the store ID of a merchant assigned by PayCo as a merchant can have more than one store.
     * @param $orderID This is the order number tyhat the shop has assigned
     * @param $captureID The confirmation ID of the capture. Only sent for Notifications that belong to captures
     * @param $merchantReference Reference that can be set by the merchant during the createTransaction call.
     * @param $paymentReference The reference number of the
     * @param $userID The unique user id of the customer.
     * @param $amount This is either the amount of an incoming payment or “0” in case of some status changes
     * @param $currency  Currency code according to ISO4217.
     * @param $transactionStatus Current status of the transaction. Same values as resultCode
     * @param $orderStatus Possible values: PAID PAYPENDING PAYMENTFAILED CHARGEBACK CLEARED. Status of order
     * @param $additionalData Json string with aditional data
     * @param $timestamp Unix timestamp, Notification timestamp
     * @param $version notification version (currently 1.5)
     * @link http://www.manula.com/manuals/payco/payment-api/hostedpagesdraft/en/topic/notification-call
     */
    public function sendData(
        $merchantID,
        $storeID,
        $orderID,
        $captureID,
        $merchantReference,
        $paymentReference,
        $userID,
        $amount,
        $currency,
        $transactionStatus,
        $orderStatus,
        $additionalData,
        $timestamp,
        $version
    ) {
        $this->merchantID = $merchantID;
        $this->storeID = $storeID;
        $this->orderID = $orderID;
        $this->captureID = $captureID;
        $this->merchantReference = $merchantReference;
        $this->paymentReference = $paymentReference;
        $this->userID = $userID;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->transactionStatus = $transactionStatus;
        $this->orderStatus = $orderStatus;
        $this->additionalData = $additionalData;
        $this->timestamp = $timestamp;
        $this->version = $version;
    }

    /**
     * The run method used by the processor to run successfuly validated MNS notifications.
     * This should not return anything
     */
    public function run()
    {
        $this->wpdb->insert(
            $this->wpdb->prefix.'woocommerce_payco_mns_messages',
            array(
                'merchant_id' => $this->merchantID,
                'store_id' => $this->storeID,
                'order_id' => $this->orderID,
                'capture_id' => $this->captureID,
                'merchant_reference' => $this->merchantReference,
                'payment_reference' => $this->paymentReference,
                'user_id' => $this->userID,
                'amount' => $this->amount,
                'currency' => $this->currency,
                'transaction_status' => $this->transactionStatus,
                'order_status' => $this->orderStatus,
                'additional_data' => $this->additionalData,
                'mns_timestamp' => $this->timestamp,
                'version' => $this->version,
                'mns_processed' => 0,
                'mns_error_processing' => 0,
            ),
            array(
                '%d',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%s',
                '%d',
                '%s',
                '%s',
                '%s',
                '%s',
                '%d',
                '%s',
                '%d',
                '%d',
            )
        );
    }
}