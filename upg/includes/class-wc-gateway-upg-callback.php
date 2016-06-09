<?php

class WC_Gateway_Upg_Callback
{
    /**
     * @var \Upg\Library\Config
     */
    private $config;

    /**
     * @var array
     */
    private $moduleSettings;

    public function __construct(\Upg\Library\Config $config, array $moduleSettings)
    {
        $this->config = $config;
        $this->moduleSettings = $moduleSettings;
        add_action( 'woocommerce_api_wc_hosted_payment_callback', array( $this, 'processResponse' ) );
        require_once(plugin_dir_path( __FILE__ ) . '/class-wc-gateway-upg-callback-processor.php');
    }

    public function processResponse()
    {
        $data = array(
            'notificationType' => (array_key_exists('notificationType',$_GET)?$_GET['notificationType']:''),
            'merchantID' => (array_key_exists('merchantID',$_GET)?$_GET['merchantID']:''),
            'storeID' => (array_key_exists('storeID',$_GET)?$_GET['storeID']:''),
            'orderID' => (array_key_exists('orderID',$_GET)?$_GET['orderID']:''),
            'paymentMethod' => (array_key_exists('paymentMethod',$_GET)?$_GET['paymentMethod']:''),
            'resultCode' => (array_key_exists('resultCode',$_GET)?$_GET['resultCode']:''),
            'merchantReference' => (array_key_exists('merchantReference',$_GET)?$_GET['merchantReference']:''),
            'additionalInformation' => (array_key_exists('additionalInformation',$_GET)?$_GET['additionalInformation']:''),
            'paymentInstrumentsPageUrl' => (array_key_exists('paymentInstrumentsPageUrl',$_GET)?$_GET['paymentInstrumentsPageUrl']:''),
            'paymentInstrumentID' => (array_key_exists('paymentInstrumentID',$_GET)?$_GET['paymentInstrumentID']:''),
            'message' => (array_key_exists('message',$_GET)?$_GET['message']:''),
            'salt' => (array_key_exists('salt',$_GET)?$_GET['salt']:''),
            'mac' => (array_key_exists('mac',$_GET)?$_GET['mac']:''),
        );

        //$order->update_status( 'failed', __( 'Payment was declined by Simplify Commerce.', 'woocommerce' ) );
        $order = new WC_Order($data['orderID']);

        if(empty($order->post)) {
            //TODO - Error page
        }

        try{
            $processor = new WC_Gateway_Upg_Callback_Processor($order, $this->moduleSettings);
            $handler = new \Upg\Library\Callback\Handler($this->config, $data, $processor);
            echo $result = $handler->run();
            exit;

        }catch (\Upg\Library\Callback\Exception\MacValidation $e) {
            $order->update_status( 'failed', __( 'Callback could not be validated', 'upg' ) );
            echo json_encode(array('url'=>$order->get_checkout_order_received_url()));
            exit;

        }catch (Exception $e) {
            $order->update_status( 'failed', __( 'System issue with payment.', 'upg' ) );
            echo json_encode(array('url'=>$order->get_checkout_order_received_url()));
            exit;
        }


    }

}