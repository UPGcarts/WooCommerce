<?php

class WC_Gateway_Upg_Mns_Save_Controller
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
        require_once(plugin_dir_path( __FILE__ ) . '/class-wc-gateway-upg-mns-save-process.php');
        add_action( 'woocommerce_api_wc_hosted_payment_mns_save', array( $this, 'processResponse' ) );
    }

    public function processResponse()
    {
        global $wpdb;

        $data = array(
            'merchantID' => (array_key_exists('merchantID',$_POST)?$_POST['merchantID']:''),
            'storeID' => (array_key_exists('storeID',$_POST)?$_POST['storeID']:''),
            'orderID' => (array_key_exists('orderID',$_POST)?$_POST['orderID']:''),
            'captureID' => (array_key_exists('captureID',$_POST)?$_POST['captureID']:''),
            'merchantReference' => (array_key_exists('merchantReference',$_POST)?$_POST['merchantReference']:''),
            'paymentReference' => (array_key_exists('paymentReference',$_POST)?$_POST['paymentReference']:''),
            'userID' => (array_key_exists('userID',$_POST)?$_POST['userID']:''),
            'amount' => (array_key_exists('amount',$_POST)?$_POST['amount']:''),
            'currency' => (array_key_exists('currency',$_POST)?$_POST['currency']:''),
            'transactionStatus' => (array_key_exists('transactionStatus',$_POST)?$_POST['transactionStatus']:''),
            'orderStatus' => (array_key_exists('orderStatus',$_POST)?$_POST['orderStatus']:''),
            'additionalData' => (array_key_exists('additionalData',$_POST)?$_POST['additionalData']:''),
            'timestamp' => (array_key_exists('timestamp',$_POST)?$_POST['timestamp']:''),
            'version' => (array_key_exists('version',$_POST)?$_POST['version']:''),
            'mac' => (array_key_exists('mac',$_POST)?$_POST['mac']:''),
        );

        try {
            $handler = new \Upg\Library\Mns\Handler($this->config, $data, new WC_Gateway_Upg_Mns_Save_Process($wpdb));
            $handler->run();
        } catch (Exception $e) {
            WC_Gateway_Upg_Payment_Log::logError("Got MNS error for request : ".serialize($data)." : ".$e->getMessage());
        }
    }
}