<?php

class WC_Gateway_Upg_Callback_Processor implements \Upg\Library\Callback\ProcessorInterface
{
    const NOTIFICATION_TYPE_PAYMENT_STATUS= 'PAYMENT_STATUS';

    private $notificationType;
    private $merchantID;
    private $storeID;
    private $orderID;
    private $paymentMethod;
    private $resultCode;
    private $merchantReference;
    private $paymentInstrumentID;
    private $paymentInstrumentsPageUrl;
    private $additionalInformation = array();
    private $message;

    /**
     * @var WC_Order The order that is being processed
     */
    private $order;

    /**
     * @var array
     */
    private $settings;

    public function __construct(WC_Order $order, array $settings)
    {
        $this->order = $order;
        $this->settings = $settings;
    }

    /**
     * Send data to the processor that will be used in the run method
     * Unless specified most parameters will not be blank
     *
     * @param $notificationType This is the notification type which can be PAYMENT_STATUS, PAYMENT_INSTRUMENT_SELECTION
     * @param $merchantID This is the merchantID assigned by PayCo.
     * @param $storeID This is the store ID of a merchant assigned by PayCo as a merchant can have more than one store.
     * @param $orderID This is the order number of the shop.
     * @param $paymentMethod This is the selected payment method
     * @param $resultCode 0 means OK, any other code means error
     * @param $merchantReference Reference that was set by the merchant during the createTransaction call. Optional
     * @param $paymentInstrumentID This is the payment instrument Id that was used
     * @param $paymentInstrumentsPageUrl This is the payment instruments page url.
     * Which may or may not be given depending on user flow and integration mode
     * @param array $additionalInformation Optional additional info in an associative array
     * @param $message Details about an error, otherwise not present. Optional
     */
    public function sendData(
        $notificationType,
        $merchantID,
        $storeID,
        $orderID,
        $paymentMethod,
        $resultCode,
        $merchantReference,
        $paymentInstrumentID,
        $paymentInstrumentsPageUrl,
        array $additionalInformation,
        $message
    )
    {
        $this->notificationType = $notificationType;
        $this->merchantID = $merchantID;
        $this->storeID = $storeID;
        $this->orderID = $orderID;
        $this->paymentMethod = trim($paymentMethod);
        $this->resultCode = $resultCode;
        $this->merchantReference = $merchantReference;
        $this->paymentInstrumentID = $paymentInstrumentID;
        $this->paymentInstrumentsPageUrl = $paymentInstrumentsPageUrl;
        $this->additionalInformation = $additionalInformation;
        $this->message = $message;
    }

    public function run()
    {

        if(WC_Gateway_Upg_Helper::validateCallBackUrl($this->paymentInstrumentsPageUrl)) {
            update_post_meta( $this->order->id, '_payco_recover_url', $this->paymentInstrumentsPageUrl);
            return $this->order->get_checkout_payment_url(true);
        }else if ($this->notificationType == self::NOTIFICATION_TYPE_PAYMENT_STATUS && $this->resultCode == 0) {
            update_post_meta( $this->order->id, '_payment_method_title', WC_Gateway_Upg_Helper::getOrderPaymentMethodString($this->paymentMethod) );
            update_post_meta( $this->order->id, '_payco_payment_method', $this->paymentMethod);

            $this->order->update_status($this->settings['payco_return_status_success']);
            $this->order->reduce_order_stock();

            return $this->order->get_checkout_order_received_url();
        }else{
            $this->order->update_status( 'failed', __( 'Invalid callback.', 'upg' ) );
            return $this->order->get_checkout_order_received_url();
        }
    }

}