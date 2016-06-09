<?php

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$payco_admin_settings_form = array(
    'enabled' => array(
        'title'   => __( 'Enable/Disable', 'woocommerce' ),
        'type'    => 'checkbox',
        'label'   => __( 'Enable Hosted Payments', 'upg' ),
        'default' => 'no'
    ),
    'title' => array(
        'title'   => __( 'Title', 'woocommerce' ),
        'type'        => 'text',
        'description' => __( 'This controls the title which the user sees during checkout.', 'woocommerce' ),
        'default' => UPG_MODULE_FRONTEND_TITLE,
        'desc_tip'    => true,
    ),
    'log_settings' => array(
        'title'       => __( 'Log Settings', 'upg' ),
        'type'        => 'title',
        'description' => '',
    ),
    'log_path' => array(
        'title'       => __( 'Log File Name', 'upg' ),
        'type'        => 'text',
        'description' => __( 'Set an log file name for API requests that will be loged to the woocommerce default log path' ),
        'default'     => '',
        'desc_tip'    => true,
        'placeholder' => ''
    ),
    'log_level' => array(
        'title'       => __( 'Log Level', 'upg' ),
        'type'        => 'select',
        'class'       => 'wc-enhanced-select',
        'description' => __( 'Set the log level for the api.', 'upg' ),
        'desc_tip'    => true,
        'options'     => array(
            'emergency'          => __( 'Emergency', 'upg' ),
            'alert' => __( 'Alert', 'upg' ),
            'critical' => __( 'Critical', 'upg' ),
            'error' => __( 'Error', 'upg' ),
            'warning' => __( 'Warning', 'upg' ),
            'notice' => __( 'Notice', 'upg' ),
            'info' => __( 'Info', 'upg' ),
            'debug' => __( 'Debug', 'upg' ),
        )
    ),
    'merchant_settings' => array(
        'title'       => __( 'Merchant Settings', 'upg' ),
        'type'        => 'title',
        'description' => '',
    ),
    'mode' => array(
        'title'       => __( 'Mode', 'upg' ),
        'type'        => 'select',
        'class'       => 'wc-enhanced-select',
        'description' => __( 'Integration Mode.', 'upg' ),
        'desc_tip'    => true,
        'options'     => array(
            'SANDBOX' => __( 'Sandbox', 'upg' ),
            'LIVE'          => __( 'Live', 'upg' ),
        )
    ),
    'autocapture' => array(
        'title'   => __( 'Enable Autocapture', 'upg' ),
        'type'    => 'checkbox',
        'label'   => __( 'Enable Autocapture', 'upg' ),
        'default' => 'no'
    ),
    'riskclass' => array(
        'title'       => __( 'Risk Class', 'upg' ),
        'type'        => 'select',
        'class'       => 'wc-enhanced-select',
        'description' => __( 'Set the default risk level for the api.', 'upg' ),
        'desc_tip'    => true,
        'options'     => array(
            \Upg\Library\Risk\RiskClass::RISK_CLASS_TRUSTED => __( 'Trusted Risk Class', 'upg' ),
            \Upg\Library\Risk\RiskClass::RISK_CLASS_DEFAULT => __( 'Default Risk Class', 'upg' ),
            \Upg\Library\Risk\RiskClass::RISK_CLASS_HIGH => __( 'High Risk Class', 'upg' ),
        )
    ),
    'defaultLocale' => array(
        'title'       => __( 'Default Locale', 'upg' ),
        'type'        => 'select',
        'class'       => 'wc-enhanced-select',
        'description' => __( 'Set the defualt locale if customer locale can not be determined.', 'upg' ),
        'desc_tip'    => true,
        'options'     => array(
            \Upg\Library\Locale\Codes::LOCALE_EN => __( 'English', 'upg' ),
            \Upg\Library\Locale\Codes::LOCALE_DE => __( 'German', 'upg' ),
            \Upg\Library\Locale\Codes::LOCALE_FR => __( 'French', 'upg' ),
            \Upg\Library\Locale\Codes::LOCALE_ES => __( 'Spanish', 'upg' ),
            \Upg\Library\Locale\Codes::LOCALE_NL => __( 'Dutch', 'upg' ),
            \Upg\Library\Locale\Codes::LOCALE_IT => __( 'Italian', 'upg' ),
            \Upg\Library\Locale\Codes::LOCALE_FI => __( 'Finnish', 'upg' ),
            \Upg\Library\Locale\Codes::LOCALE_PT => __( 'Portuguese', 'upg' ),
            \Upg\Library\Locale\Codes::LOCALE_RU => __( 'Russian', 'upg' ),
            \Upg\Library\Locale\Codes::LOCALE_TU => __( 'Turkish', 'upg' ),
        )
    ),
    'merchant_id' => array(
        'title'       => __( 'Merchant ID', 'upg' ),
        'type'        => 'int',
        'description' => __( 'Merchant ID' ),
        'default'     => '',
        'desc_tip'    => true,
        'placeholder' => ''
    ),
    'password' => array(
        'title'       => __( 'Password', 'upg' ),
        'type'        => 'text',
        'description' => __( 'Password sent by upg' ),
        'default'     => '',
        'desc_tip'    => true,
        'placeholder' => ''
    ),
    'store_id' => array(
        'title'       => __( 'Store ID', 'upg' ),
        'type'        => 'text',
        'description' => __( 'Store ID' ),
        'default'     => '',
        'desc_tip'    => true,
        'placeholder' => ''
    ),
    'b2b_settings_title' => array(
        'title'       => __( 'B2B Settings', 'upg' ),
        'type'        => 'title',
        'description' => '',
    ),
    'b2b_enable' => array(
        'title'   => __( 'Enable/Disable', 'woocommerce' ),
        'type'    => 'checkbox',
        'label'   => __( 'B2B Enable', 'upg' ),
        'default' => 'no',
        'description' => __( 'if B2B settings are filled in' ),
    ),
    'callbackurl' => array(
        'title'       => __( 'Callback URL', 'upg' ),
        'type'        => 'title',
        'description' => WC()->api_request_url('wc_hosted_payment_callback'),
    ),
    'mnsurl' => array(
        'title'       => __( 'MNS URL', 'upg' ),
        'type'        => 'title',
        'description' => WC()->api_request_url('wc_hosted_payment_mns_save'),
    ),
    'mnscron' => array(
        'title'       => __( 'MNS Cron URL', 'upg' ),
        'type'        => 'title',
        'description' => WC()->api_request_url('wc_hosted_payment_mns_process'),
    ),
    'order_status_settings' => array(
        'title'       => __( 'Order Status Settings', 'upg' ),
        'type'        => 'title',
        'description' => '',
    ),
    'payco_return_status_success' => array(
        'title'       => __( 'Return success status', 'upg' ),
        'type'        => 'select',
        'default'     => 'wc-processing',
        'class'       => 'wc-enhanced-select',
        'description' => __( 'Once Payco returns to the site after successful processing what to set the order status to', 'upg' ),
        'desc_tip'    => true,
        'options'     => wc_get_order_statuses()
    ),
    'payco_return_status_failure' => array(
        'title'       => __( 'Return failure status', 'upg' ),
        'type'        => 'select',
        'default'     => 'wc-failed',
        'class'       => 'wc-enhanced-select',
        'description' => __( 'Once Payco returns to the site after failing to process the order', 'upg' ),
        'desc_tip'    => true,
        'options'     => wc_get_order_statuses()
    ),
    'payco_mns_paid' => array(
        'title'       => __( 'MNS PAID', 'upg' ),
        'type'        => 'select',
        'default'     => 'wc-processing',
        'class'       => 'wc-enhanced-select',
        'description' => __( 'For MNS paid messages what to set the order status to', 'upg' ),
        'desc_tip'    => true,
        'options'     => wc_get_order_statuses()
    ),
    'payco_mns_paid_pending_reserve' => array(
        'title'       => __( 'MNS PAYPENDING', 'upg' ),
        'type'        => 'select',
        'default'     => 'wc-processing',
        'class'       => 'wc-enhanced-select',
        'description' => __( 'For MNS paid pending reserve messages what to set the order status to', 'upg' ),
        'desc_tip'    => true,
        'options'     => wc_get_order_statuses()
    ),
    'payco_mns_payment_failed' => array(
        'title'       => __( 'MNS PAYMENTFAILED', 'upg' ),
        'type'        => 'select',
        'default'     => 'wc-failed',
        'class'       => 'wc-enhanced-select',
        'description' => __( 'For MNS payment failed messages what to set the order status to', 'upg' ),
        'desc_tip'    => true,
        'options'     => wc_get_order_statuses()
    ),
    'payco_mns_payment_charge_back' => array(
        'title'       => __( 'MNS CHARGEBACK', 'upg' ),
        'type'        => 'select',
        'default'     => 'wc-payco-chargeback',
        'class'       => 'wc-enhanced-select',
        'description' => __( 'For MNS payment failed messages what to set the order status to', 'upg' ),
        'desc_tip'    => true,
        'options'     => wc_get_order_statuses()
    ),
    'payco_mns_payment_cleared' => array(
        'title'       => __( 'MNS CLEARED', 'upg' ),
        'type'        => 'select',
        'default'     => 'wc-payco-cleared',
        'class'       => 'wc-enhanced-select',
        'description' => __( 'For MNS payment failed messages what to set the order status to', 'upg' ),
        'desc_tip'    => true,
        'options'     => wc_get_order_statuses()
    ),
    'payco_mns_in_dunning' => array(
        'title'       => __( 'MNS INDUNNING', 'upg' ),
        'type'        => 'select',
        'default'     => 'wc-payco-in-dunning',
        'class'       => 'wc-enhanced-select',
        'description' => __( 'For MNS in dunning', 'upg' ),
        'desc_tip'    => true,
        'options'     => wc_get_order_statuses()
    ),
    'payco_mns_acknowledge_pending' => array(
        'title'       => __( 'MNS ACKNOWLEDGEPENDING', 'upg' ),
        'type'        => 'select',
        'default'     => 'wc-payco-pending',
        'class'       => 'wc-enhanced-select',
        'description' => __( 'For MNS Acknowledge Pending messages', 'upg' ),
        'desc_tip'    => true,
        'options'     => wc_get_order_statuses()
    ),
    'payco_mns_fraud_pending' => array(
        'title'       => __( 'MNS FRAUDPENDING', 'upg' ),
        'type'        => 'select',
        'default'     => 'wc-payco-fraud-pending',
        'class'       => 'wc-enhanced-select',
        'description' => __( 'For MNS Fraud Pending messages', 'upg' ),
        'desc_tip'    => true,
        'options'     => wc_get_order_statuses()
    ),
    'payco_mns_fraud_cancelled' => array(
        'title'       => __( 'MNS FRAUDCANCELLED', 'upg' ),
        'type'        => 'select',
        'default'     => 'wc-payco-fraud-cancelled',
        'class'       => 'wc-enhanced-select',
        'description' => __( 'For MNS Fraud Cancelled messages', 'upg' ),
        'desc_tip'    => true,
        'options'     => wc_get_order_statuses()
    ),
    'payco_mns_cia_pending' => array(
        'title'       => __( 'MNS CIAPENDING', 'upg' ),
        'type'        => 'select',
        'default'     => 'wc-payco-cia-pending',
        'class'       => 'wc-enhanced-select',
        'description' => __( 'For MNS cash in Advance Pending messages', 'upg' ),
        'desc_tip'    => true,
        'options'     => wc_get_order_statuses()
    ),
    'payco_mns_transaction_cancelled' => array(
        'title'       => __( 'MNS CANCELLED / EXPIRED', 'upg' ),
        'type'        => 'select',
        'default'     => 'wc-cancelled',
        'class'       => 'wc-enhanced-select',
        'description' => __( 'For MNS Transaction Cancelled status', 'upg' ),
        'desc_tip'    => true,
        'options'     => wc_get_order_statuses()
    ),
    'payco_mns_merchant_pending' => array(
        'title'       => __( 'MNS MERCHANTPENDING', 'upg' ),
        'type'        => 'select',
        'default'     => 'wc-payco-pending',
        'class'       => 'wc-enhanced-select',
        'description' => __( 'For MNS Merchant Pending messages', 'upg' ),
        'desc_tip'    => true,
        'options'     => wc_get_order_statuses()
    ),
    'payco_mns_transaction_in_progress' => array(
        'title'       => __( 'MNS INPROGRESS', 'upg' ),
        'type'        => 'select',
        'default'     => 'wc-payco-pending',
        'class'       => 'wc-enhanced-select',
        'description' => __( 'For MNS Acknowledge Pending messages', 'upg' ),
        'desc_tip'    => true,
        'options'     => wc_get_order_statuses()
    ),
    'mns_transaction_done' => array(
        'title'       => __( 'MNS DONE', 'upg' ),
        'type'        => 'select',
        'default'     => 'wc-processing',
        'class'       => 'wc-enhanced-select',
        'description' => __( 'For MNS done messages order status', 'upg' ),
        'desc_tip'    => true,
        'options'     => wc_get_order_statuses()
    ),
);

return $payco_admin_settings_form;