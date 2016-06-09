<?php

class WC_Gateway_Upg_Install
{
    const DB_VERSION = "0.0.2";
    const VERSION_OPTION_LABEL = 'upg_payment_db_version';

    public static function install()
    {
        $version = get_site_option(WC_Gateway_Upg_Install::VERSION_OPTION_LABEL);

        if($version != WC_Gateway_Upg_Install::DB_VERSION) {
            require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
            dbDelta(self::getDbSchema());
            delete_site_option(self::VERSION_OPTION_LABEL);
            add_site_option(self::VERSION_OPTION_LABEL, self::DB_VERSION);
        }
    }

    private static function getDbSchema()
    {
        global $wpdb;

        $collate = '';

        if ( $wpdb->has_cap( 'collation' ) ) {
            if ( ! empty( $wpdb->charset ) ) {
                $collate .= "DEFAULT CHARACTER SET $wpdb->charset";
            }
            if ( ! empty( $wpdb->collate ) ) {
                $collate .= " COLLATE $wpdb->collate";
            }
        }

        return "
CREATE TABLE {$wpdb->prefix}woocommerce_payco_captures(
  capture_id bigint(20) NOT NULL AUTO_INCREMENT,
  order_id bigint(20) NOT NULL,
  capture_reference VARCHAR(255),
  capture_amount DECIMAL (20,2),
  autocapture TINYINT(1) DEFAULT 0,
  PRIMARY KEY  (capture_id),
  KEY order_id (order_id)
)$collate;
CREATE TABLE {$wpdb->prefix}woocommerce_payco_refunds(
  refund_id bigint(20) NOT NULL AUTO_INCREMENT,
  capture_id bigint(20) NOT NULL,
  refund_amount DECIMAL (20,2),
  refund_reason VARCHAR (255),
  PRIMARY KEY  (refund_id),
  KEY capture_id (capture_id)
)$collate;
CREATE TABLE {$wpdb->prefix}woocommerce_payco_mns_messages(
    id_mns INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
    merchant_id INT(16) UNSIGNED NOT NULL,
    store_id VARCHAR(255) NOT NULL,
    order_id VARCHAR(255) NOT NULL,
    capture_id VARCHAR(255) NOT NULL,
    merchant_reference VARCHAR(255) NOT NULL,
    payment_reference VARCHAR(255) NOT NULL,
    user_id VARCHAR(255) NOT NULL,
    amount INT(16) UNSIGNED NOT NULL,
    currency VARCHAR(255) NOT NULL,
    transaction_status VARCHAR(255) NOT NULL,
    order_status VARCHAR(255) NOT NULL,
    additional_data TEXT,
    mns_timestamp BIGINT(20) UNSIGNED NOT NULL,
    version VARCHAR(255),
    mns_processed TINYINT(1) DEFAULT 0,
    mns_error_processing TINYINT(1) DEFAULT 0,
    PRIMARY KEY  (id_mns),
    KEY {$wpdb->prefix}_mns_processed (mns_processed),
    KEY {$wpdb->prefix}_mns_timestamp (mns_timestamp),
    KEY {$wpdb->prefix}_mns_order_id (order_id),
    KEY {$wpdb->prefix}_mns_error (mns_error_processing)
)$collate;
   ";
    }
}