<?php

class WC_Gateway_Upg_Payment_Info
{
    /**
     * @var \Upg\Library\Config
     */
    private $config;

    private $paymentMethod;


    public function __construct(\Upg\Library\Config $config, $paymentMethod)
    {
        $this->config = $config;
        $this->paymentMethod = $paymentMethod;
        // Customer Emails
        add_action( 'woocommerce_email_before_order_table', array( $this, 'email_payment_detail' ), 10, 3 );
        add_action( 'woocommerce_thankyou_'.$paymentMethod, array( $this, 'thankyou_page' ) );
    }

    public function thankyou_page( $order_id )
    {
        //ToDo show payment info
        $order = new WC_Order($order_id);

        $this->showDetails($order);

    }

    public function email_payment_detail( $order, $sent_to_admin, $plain_text = false )
    {
        if (! $sent_to_admin && $this->paymentMethod === $order->payment_method ) {
            $this->showDetails($order);
        }
    }

    private function showDetails(WC_Order $order)
    {
        try {
            $data = $this->getOrderDetails($order);

            if($this->showExtendedDetails($data)) {

                switch($data['paymentMethod']) {
                    case 'DD':
                        echo '<h2>'.__( 'For Direct Debt payment here are your details', 'upg' ).'</h2>'. PHP_EOL;
                        break;
                    case 'PREPAID':
                        echo '<h2>'.__( 'For cash in advance here are the payment detail for you to pay for your order', 'upg' ).'</h2>'. PHP_EOL;
                        break;
                    case 'BILL':
                    case 'BILL_SECURE':
                        echo '<h2>'.__( 'For Billpay here are the payment detail for you to pay for your order', 'upg' ).'</h2>'. PHP_EOL;
                        break;
                }
                echo '<ul class="order_details payco_details">' . PHP_EOL;
                foreach($data as $label=>$value)
                {
                    $translatedLabel = WC_Gateway_Upg_Helper::translatePaymentInfoLabel($label);
                    if(!empty($translatedLabel)) {
                        echo '<li class="' . esc_attr( $label ) . '">' . esc_attr( $translatedLabel ) . ': <strong>' . wptexturize( $value ) . '</strong></li>' . PHP_EOL;
                    }
                }
                echo '</ul>';
            }

        }catch (Exception $e) {
            WC_Gateway_Upg_Payment_Log::logError("When outputing details for ".$order->id." Got: ".$e->getMessage());
        }
    }

    private function showExtendedDetails(array $data)
    {
        if(empty($data) || !array_key_exists('paymentMethod', $data)) {
            return false;
        }

        switch($data['paymentMethod']) {
            case 'DD':
            case 'PREPAID':
            case 'BILL':
            case 'BILL_SECURE':
                return true;
            break;
        }

        return false;
    }

    private function getOrderDetails(WC_Order $order)
    {
        //do request
        $request = new \Upg\Library\Request\GetTransactionStatus($this->config);
        $request->setOrderID($order->get_order_number());

        $apiEndPoint = new \Upg\Library\Api\GetTransactionStatus($this->config, $request);
        $response = $apiEndPoint->sendRequest();

        $aditionalData = $response->getData('additionalData');

        if(!is_array($aditionalData)) {
            return array();
        }

        return $aditionalData;
    }
}