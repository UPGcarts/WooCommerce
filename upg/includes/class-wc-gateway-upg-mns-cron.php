<?php

class WC_Gateway_Upg_Mns_Cron
{
    public function __construct(\Upg\Library\Config $config, array $moduleSettings)
    {
        $this->config = $config;
        $this->moduleSettings = $moduleSettings;

        add_action( 'woocommerce_api_wc_hosted_payment_mns_process', array( $this, 'run' ) );

    }

    public function run()
    {
        global $wpdb;

        require_once(plugin_dir_path( __FILE__ ) . '/class-wc-gateway-upg-mns-process.php');

        WC_Gateway_Upg_Mns_Process::setSettings($this->moduleSettings);

        $mnsMessages = $wpdb->get_results($wpdb->prepare("SELECT *
        FROM {$wpdb->prefix}woocommerce_payco_mns_messages
        WHERE mns_processed = %d AND mns_error_processing =%d",0,0));

        foreach($mnsMessages as $mns) {
            if(WC_Gateway_Upg_Mns_Process::processMessage($mns, $wpdb)) {
                WC_Gateway_Upg_Mns_Process::markMnsProcessed($wpdb, $mns->id_mns);
            }else {
                WC_Gateway_Upg_Mns_Process::markMnsError($wpdb, $mns->id_mns);
            }
        }

        return true;
    }
}