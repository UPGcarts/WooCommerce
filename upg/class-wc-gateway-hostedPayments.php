<?php

class WC_Gateway_Hosted_Payments extends WC_Payment_Gateway
{
    const URL_LIVE = 'https://www.pay-co.net/2.0/';
    const URL_SANDBOX = 'https://sandbox.upgplc.com/2.0/';
    const MODULE_ID = 'upg';

    private $iframeErrorMessage = '';

    public function __construct()
    {
        require_once(dirname(__FILE__) . '/includes/vendor/autoload.php');
        $this->id = self::MODULE_ID;
        $this->icon = '';
        $this->has_fields = false;
        $this->method_title = UPG_MODULE_TITLE;
        $this->method_description = "Hosted Payments Page";

        $this->supports = array(
            'products',
        );

        $this->init_form_fields();
        $this->init_settings();

        $this->title = $this->get_option( 'title' );

        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
        add_action( 'woocommerce_receipt_' . $this->id, array( $this, 'receipt_page' ) );
        add_filter( 'woocommerce_checkout_fields' , array( $this, 'addCheckoutFields' ) );
        add_action( 'woocommerce_checkout_update_order_meta', array( $this, 'savePaycoFieldsFromOrder' ) );

        $clientLibaryConfig = $this->getUpgConfig($this->settings);
        require_once(plugin_dir_path( __FILE__ ) . '/includes/class-wc-gateway-upg-callback.php');
        new WC_Gateway_Upg_Callback($clientLibaryConfig, $this->settings);

        require_once(plugin_dir_path( __FILE__ ) . '/includes/class-wc-gateway-upg-mns-save-controller.php');
        new WC_Gateway_Upg_Mns_Save_Controller($clientLibaryConfig, $this->settings);

        require_once(plugin_dir_path( __FILE__ ) . '/includes/class-wc-gateway-upg-mns-cron.php');
        new WC_Gateway_Upg_Mns_Cron($clientLibaryConfig, $this->settings);

        require_once(plugin_dir_path( __FILE__ ) . '/includes/class-wc-gateway-upg-payment-info.php');
        new WC_Gateway_Upg_Payment_Info($clientLibaryConfig, $this->id);

        require_once(plugin_dir_path( __FILE__ ) . '/includes/class-wc-gateway-upg-create-order.php');
        new WC_Gateway_Upg_Create_Order();
    }

    public function addCheckoutFields($fields)
    {
        $newBillingFields = array();
        $newBillingFields['billing_payco_gender'] = array(
            'label'     => __('Gender', 'upg'),
            'type'   => 'select',
            'required' => 1,
            'clear'       => false,
            'options'     => array(
                \Upg\Library\Request\Objects\Person::SALUTATIONMALE => __('Male', $this->id),
                \Upg\Library\Request\Objects\Person::SALUTATIONFEMALE => __('Female', $this->id),
            )
        );

        $checkoutFields = $fields['billing'];

        $newCheckoutFields = array();

        $pos = (int) array_search('billing_company', array_keys($checkoutFields));

        if(array_key_exists('b2b_enable', $this->settings) && strtolower($this->settings['b2b_enable']) == 'yes') {
            $newCheckoutFields = array_merge(
                array_slice($checkoutFields, 0, ++$pos),
                array(
                    'billing_upg_company_registration_id' => array(
                        'label' => 'Company Registration ID',
                        'class' => array('form-row-wide')
                    ),
                    'billing_upg_company_vat_id' => array(
                        'label' => 'Company VAT ID',
                        'class' => array('form-row-wide')
                    ),
                    'billing_upg_company_tax_id' => array(
                        'label' => 'Company Tax ID',
                        'class' => array('form-row-wide')
                    ),
                    'billing_upg_company_register_type' => array(
                        'label' => __('Company Register Type', 'upg'),
                        'type' => 'select',
                        'required' => 0,
                        'clear' => false,
                        'options' => array(
                            '' => __('Please select', $this->id),
                            \Upg\Library\Request\Objects\Company::COMPANY_TYPE_HRA => __('German trade register department A',
                                $this->id),
                            \Upg\Library\Request\Objects\Company::COMPANY_TYPE_HRB => __('German trade register department B',
                                $this->id),
                            \Upg\Library\Request\Objects\Company::COMPANY_TYPE_PARTR => __('German partnership register',
                                $this->id),
                            \Upg\Library\Request\Objects\Company::COMPANY_TYPE_GENR => __('German partnership register',
                                $this->id),
                            \Upg\Library\Request\Objects\Company::COMPANY_TYPE_VERR => __('German register of associations',
                                $this->id),
                            \Upg\Library\Request\Objects\Company::COMPANY_TYPE_FN => __('Austrian commercial register',
                                $this->id),
                            \Upg\Library\Request\Objects\Company::COMPANY_TYPE_LUA => __('Luxembourg trade registry A',
                                $this->id),
                            \Upg\Library\Request\Objects\Company::COMPANY_TYPE_LUB => __('Luxembourg trade registry B',
                                $this->id),
                            \Upg\Library\Request\Objects\Company::COMPANY_TYPE_LUC => __('Luxembourg trade registry C',
                                $this->id),
                            \Upg\Library\Request\Objects\Company::COMPANY_TYPE_LUD => __('Luxembourg trade registry D',
                                $this->id),
                            \Upg\Library\Request\Objects\Company::COMPANY_TYPE_LUE => __('Luxembourg trade registry E',
                                $this->id),
                            \Upg\Library\Request\Objects\Company::COMPANY_TYPE_LUF => __('Luxembourg trade registry F',
                                $this->id),
                        )

                    )
                ),
                array_slice($checkoutFields, $pos)
            );
        }

        if(!empty($newCheckoutFields)) {
            $newBillingFields = array_merge($newBillingFields, $newCheckoutFields);
            $fields['billing'] = $newBillingFields;
        }else{
            $newBillingFields = array_merge($newBillingFields, $checkoutFields);
            $fields['billing'] = $newBillingFields;
        }
        return $fields;
    }

    public function get_icon()
    {
        require_once(plugin_dir_path( __FILE__ ) . '/includes/class-wc-gateway-upg-logo.php');
        $logo = new WC_Gateway_Upg_Payment_Logo(__FILE__);
        $this->icon = $logo->getUrlForLogo();
        return parent::get_icon();
    }

    public function savePaycoFieldsFromOrder($order_id)
    {
        if ( ! empty( $_POST['billing_payco_gender'] ) ) {
            update_post_meta( $order_id, 'billing_payco_gender', sanitize_text_field( $_POST['billing_payco_gender'] ) );
        }

        if ( ! empty( $_POST['billing_upg_company_registration_id'] ) ) {
            update_post_meta( $order_id, '_billing_upg_company_registration_id', sanitize_text_field( $_POST['billing_upg_company_registration_id'] ) );
        }

        if ( ! empty( $_POST['billing_upg_company_vat_id'] ) ) {
            update_post_meta( $order_id, '_billing_upg_company_vat_id', sanitize_text_field( $_POST['billing_upg_company_vat_id'] ) );
        }

        if ( ! empty( $_POST['billing_upg_company_tax_id'] ) ) {
            update_post_meta( $order_id, '_billing_upg_company_tax_id', sanitize_text_field( $_POST['billing_upg_company_tax_id'] ) );
        }

        if ( ! empty( $_POST['billing_upg_company_register_type'] ) ) {
            update_post_meta( $order_id, '_billing_upg_company_register_type', sanitize_text_field( $_POST['billing_upg_company_register_type'] ) );
        }

    }

    /**
     * Get the payco setting object
     * @param array $wcSettings
     * @return \Upg\Library\Config
     */
    public function getUpgConfig(array $wcSettings)
    {
        $data = array(
            'merchantID' => $wcSettings['merchant_id'],
            'merchantPassword' => $wcSettings['password'],
            'storeID' => $wcSettings['store_id'],
            'defaultLocale' => $wcSettings['defaultLocale'],
        );

        $url = self::URL_SANDBOX;

        if($wcSettings == 'LIVE') {
            $url = self::URL_LIVE;
        }

        $data['baseUrl'] = $url;

        $logLocation = trim($wcSettings['log_path']);
        $logLevel = trim($wcSettings['log_level']);
        if(!empty($logLocation) && !empty($logLevel)) {
            $logPath = wc_get_log_file_path($logLocation);
            $data['logLocationMain'] = $logPath;
            $data['logLocationRequest'] = $logPath;
            $data['logEnabled'] = true;
            $data['logLevel'] = $logLevel;
        }

        return new \Upg\Library\Config($data);
    }

    public function init_form_fields()
    {
        $this->form_fields = include(dirname(__FILE__) . '/includes/settings-upg.php');
    }

    public function process_payment( $order_id )
    {
        $order = new WC_Order($order_id);

        return array(
            'result' => 'success',
            'redirect' => add_query_arg('order', $order->id,
                add_query_arg('key', $order->order_key, $order->get_checkout_payment_url(true)))
        );
    }

    public function receipt_page( $order_id )
    {
        //check if transaction is being recoverd
        $url = get_post_meta( $order_id, '_payco_recover_url', true );
        if(!empty($url)) {
            echo '<iframe id="payco_iframe" src="' . $url . '" style="width:100%; border:none" width="100%" height="1500px" />';
        }else{
            //create new transaction
            $order = new WC_Order($order_id);

            $request = $this->getCreateTransactionRequest($order);
            try {
                $api = new \Upg\Library\Api\CreateTransaction($this->getUpgConfig($this->settings), $request);
                $result = $api->sendRequest();
                if(!empty($this->iframeErrorMessage)) {
                    echo '<h1 class="paycoMessage">'.$this->iframeErrorMessage.'</h1>';
                }
                echo '<iframe id="payco_iframe" src="' . $result->getData('redirectUrl') . '" style="width:100%; border:none" width="100%" height="1500px" />';

            } Catch (Exception $e) {
                WC_Gateway_Upg_Payment_Log::logError($order_id.' - '.$e->getMessage());
                $order->update_status( 'failed', __( 'Invalid request.', 'upg' ) );
                $order->cancel_order('Invalid API Request');
                echo '<p>' . __('There was an issue with your payment please contact the merchant',
                    $this->id) . '</p>';
            }
        }
    }

    /**
     * Get the Create Transaction Amount
     * @param WC_Order $order
     * @return \Upg\Library\Request\CreateTransaction
     */
    private function getCreateTransactionRequest(WC_Order $order)
    {
        $request = new \Upg\Library\Request\CreateTransaction($this->getUpgConfig($this->settings));
        $request = $this->populateCreateTransactionCustomer($order, $request);
        $request = $this->populateCreateTransactionAddresses($order, $request);
        $request = $this->populateCreateTransactionBasketItems($order, $request);

        $orderAmount = WC_Gateway_Upg_Helper::convertPriceToInt($order->order_total);
        $autoCapture = ($this->settings['autocapture'] == 'no'?false:true);
        update_post_meta( $order->id, '_payco_transaction_setting_autocapture', ($autoCapture?1:0) );
        update_post_meta( $order->id, '_payco_transaction_sent_request', 1 );
        $request->setIntegrationType(\Upg\Library\Request\CreateTransaction::INTEGRATION_TYPE_HOSTED_AFTER)
            ->setOrderID($order->get_order_number())
            ->setAutoCapture($autoCapture)
            ->setContext(\Upg\Library\Request\CreateTransaction::CONTEXT_ONLINE)
            ->setUserRiskClass($this->getRiskClass($order))
            ->setLocale(WC_Gateway_Upg_Helper::getCurrentLocale($this->settings['defaultLocale']))
            ->setAmount(new \Upg\Library\Request\Objects\Amount($orderAmount));

        $this->populateBusinessFields($order, $request);

        do_action('upg_payment_request_before_send',$request, $order);

        return $request;
    }

    /**
     * Get risk class to be used by transaction
     * @param WC_Order $order
     * @return int
     */
    private function getRiskClass(WC_Order $order)
    {
        $userId = $order->get_user_id();
        if($userId == 0) {
            return intval($this->settings['riskclass']);
        }

        if(WC_Gateway_Upg_Riskclass::customerHasRiskClass($userId)) {
            $val = get_the_author_meta('upg_riskclass', $userId);
            switch($val) {
                case '0':
                    $val = 0;
                    break;
                case '1':
                    $val = 1;
                    break;
                case '2':
                    $val = 2;
                    break;
                default:
                    intval($this->settings['riskclass']);
                    break;
            }

            return $val;
        }

        return intval($this->settings['riskclass']);
    }

    private function populateBusinessFields(WC_Order $order, \Upg\Library\Request\CreateTransaction $request)
    {
        if(array_key_exists('b2b_enable', $this->settings) && strtolower($this->settings['b2b_enable']) == 'yes') {
            $companyRegistrationId = get_post_meta($order->id, '_billing_upg_company_registration_id', true);
            $companyVatId = get_post_meta($order->id, '_billing_upg_company_vat_id', true);
            $companyTaxId = get_post_meta($order->id, '_billing_upg_company_tax_id', true);
            $companyRegisterType = get_post_meta($order->id, '_billing_upg_company_register_type', true);

            $companyName = $order->billing_company;

            if (!empty($companyName)) {
                //now check if company registration id
                if (!empty($companyRegistrationId)) {

                    $company = new \Upg\Library\Request\Objects\Company();
                    $company->setCompanyName($companyName)->setCompanyRegistrationID($companyRegistrationId);
                    if (preg_match('/(\d|[a-z]|[A-Z]){1,30}/', $companyVatId)) {
                        $company->setCompanyVatID($companyVatId);
                    }
                    if (preg_match('/(\d|[a-z]|[A-Z]){1,30}/', $companyTaxId)) {
                        $company->setCompanyTaxID($companyTaxId);
                    }
                    if (!empty($companyRegisterType)) {
                        $company->setCompanyRegisterType($companyRegisterType);
                    }

                    $request->setUserType(\Upg\Library\Request\CreateTransaction::USER_TYPE_BUSINESS);
                    $request->setCompanyData($company);
                }
            }
        }
    }

    /**
     * Populate the customer object
     * @param WC_Order $order
     * @param \Upg\Library\Request\CreateTransaction $request
     * @return \Upg\Library\Request\CreateTransaction
     */
    private function populateCreateTransactionCustomer(WC_Order $order, \Upg\Library\Request\CreateTransaction $request)
    {
        $userId = $order->get_user_id();
        if($userId == 0) {
            $userId = 'GUEST:ORDER:'.$order->get_order_number();
        }
        $user = new \Upg\Library\Request\Objects\Person();

        $user->setSalutation(get_post_meta( $order->id, 'billing_payco_gender', true ))
            ->setName($order->billing_first_name)
            ->setSurname($order->billing_last_name)
            ->setEmail($order->billing_email);

        if(WC_Gateway_Upg_Helper::validatePhoneNumber($order->billing_phone)) {
            $user->setPhoneNumber($order->billing_phone);
        }

        $request->setUserData($user)
            ->setUserType(\Upg\Library\Request\CreateTransaction::USER_TYPE_PRIVATE)
            ->setUserID($userId);

        return $request;
    }

    /**
     * Populate the address objects
     * @param WC_Order $order
     * @param \Upg\Library\Request\CreateTransaction $request
     * @return \Upg\Library\Request\CreateTransaction
     */
    private function populateCreateTransactionAddresses(WC_Order $order, \Upg\Library\Request\CreateTransaction $request)
    {
        $billingAddress = new \Upg\Library\Request\Objects\Address();
        $shippingAddress = new \Upg\Library\Request\Objects\Address();
        $billingStreet = $order->billing_address_1.' '.$order->billing_address_2;
        $shippingStreet = $order->shipping_address_1.' '.$order->shipping_address_2;

        $billingAddress->setStreet($billingStreet)
            ->setZip($order->billing_postcode)
            ->setCity($order->billing_city)
            ->setState($order->billing_state)
            ->setCountry($order->billing_country);

        $shippingAddress->setStreet($shippingStreet)
            ->setZip($order->shipping_postcode)
            ->setCity($order->shipping_city)
            ->setState($order->shipping_state)
            ->setCountry($order->shipping_country);

        $request->setBillingAddress($billingAddress);
        $request->setShippingAddress($shippingAddress);

        return $request;
    }

    /**
     * Populate th basket
     * @param WC_Order $order
     * @param \Upg\Library\Request\CreateTransaction $request
     * @return \Upg\Library\Request\CreateTransaction
     */
    private function populateCreateTransactionBasketItems(WC_Order $order, \Upg\Library\Request\CreateTransaction $request)
    {
        $calculated = 0;

        foreach ( $order->get_items() as $itemId => $item ) {
            $product     = $order->get_product_from_item( $item );
            $upgItem = new \Upg\Library\Request\Objects\BasketItem();
            $itemAmountWc = $order->get_line_subtotal( $item, false, true );
            $itemAmount = WC_Gateway_Upg_Helper::convertPriceToInt($itemAmountWc);

            $calculated += $itemAmount;

            $riskClass = get_post_meta($product->id, 'upg_riskclass', true);

            $upgItem->setBasketItemText($product->get_title())
                ->setBasketItemCount(intval($item['qty']))
                ->setBasketItemID(intval($itemId))
                ->setBasketItemType(\Upg\Library\Basket\BasketItemType::BASKET_ITEM_TYPE_DEFAULT)
                ->setBasketItemAmount(new \Upg\Library\Request\Objects\Amount($itemAmount));

            if($riskClass !== '' && is_numeric($riskClass)) {
                $upgItem->setBasketItemRiskClass(intval($riskClass));
            }

            $request->addBasketItem($upgItem);
        }

        $shippingAmount = $order->get_total_shipping();
        if(!empty($shippingAmount)) {
            $shippingAmount = WC_Gateway_Upg_Helper::convertPriceToInt($order->get_total_shipping());

            $calculated += $shippingAmount;

            $shippingItem = new \Upg\Library\Request\Objects\BasketItem();
            $shippingItem->setBasketItemText($order->get_shipping_method())
                ->setBasketItemCount(1)
                ->setBasketItemType(\Upg\Library\Basket\BasketItemType::BASKET_ITEM_TYPE_SHIPPINGCOST)
                ->setBasketItemAmount(new \Upg\Library\Request\Objects\Amount($shippingAmount));

            $request->addBasketItem($shippingItem);
        }

        $tax = $order->get_total_tax();
        if(!empty($tax)) {
            foreach($order->get_tax_totals() as $code => $tax) {
                $amount = WC_Gateway_Upg_Helper::convertPriceToInt(wc_round_tax_total($tax->amount));

                $calculated += $amount;

                $taxItem = new \Upg\Library\Request\Objects\BasketItem();
                $taxItem->setBasketItemText($tax->label)
                    ->setBasketItemCount(1)
                    ->setBasketItemType(\Upg\Library\Basket\BasketItemType::BASKET_ITEM_TYPE_DEFAULT)
                    ->setBasketItemAmount(new \Upg\Library\Request\Objects\Amount($amount));

                $request->addBasketItem($taxItem);
            }
        }

        $orderAmount = WC_Gateway_Upg_Helper::convertPriceToInt($order->order_total);

        $discount = $calculated - $orderAmount;
        if($discount > 0) {
            //get discount inc tax
            $discount = intval('0'.$discount);
            $discountItem = new \Upg\Library\Request\Objects\BasketItem();
            $discountItem->setBasketItemText( __('Discount', $this->id))
                ->setBasketItemCount(1)
                ->setBasketItemType(\Upg\Library\Basket\BasketItemType::BASKET_ITEM_TYPE_COUPON)
                ->setBasketItemAmount(new \Upg\Library\Request\Objects\Amount($discount));

            $request->addBasketItem($discountItem);
        }

        return $request;
    }
}