<?php

namespace Upg\Library\Tests\Mock\Callback;

use Upg\Library\Callback\ProcessorInterface;

class MockProcessor implements ProcessorInterface
{
    /**
     * Store the data for the mock handler
     * @var array
     */
    public $data = array();

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
        $this->data = array(
            'notificationType' => $notificationType,
            'merchantID' => $merchantID,
            'storeID' => $storeID,
            'orderID' => $orderID,
            'paymentMethod' => $paymentMethod,
            'resultCode' => $resultCode,
            'merchantReference' => $merchantReference,
            'paymentInstrumentID' => $paymentInstrumentID,
            'paymentInstrumentsPageUrl' => $paymentInstrumentsPageUrl,
            'additionalInformation' => $additionalInformation,
            'message' => $message
        );
    }

    /**
     * The run method.
     * This should return the appropriate url as shown in the manual under the Callback on the link provided for this
     * method.
     * @link http://www.manula.com/manuals/payco/payment-api/hostedpagesdraft/en/topic/reserve
     * @return string
     */
    public function run()
    {
        return 'http://something.com/success';
    }
}
