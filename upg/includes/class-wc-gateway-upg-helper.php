<?php

class WC_Gateway_Upg_Helper
{
    const PHONE_REGEX = '/^(((((((00|\+)[0-9]{2}[ \-]?)|0)[1-9][0-9]{1,4})[ \-]?)|((((00|\+)[0-9]{2}\()|\(0)[1-9][0-9]{1,4}\)[ \-]?))[0-9]{1,7}([ \-]?[0-9]{1,5})?)$/';

    public static function convertPriceToInt($price)
    {
        return intval('0'.($price * 100));
    }

    public static function getCurrentLocale($defaultLocale)
    {
        list($language,$country) = explode('_', get_locale());
        $language = strtoupper($language);

        switch($language) {
            case \Upg\Library\Locale\Codes::LOCALE_EN:
            case \Upg\Library\Locale\Codes::LOCALE_TU:
            case \Upg\Library\Locale\Codes::LOCALE_NL:
            case \Upg\Library\Locale\Codes::LOCALE_RU:
            case \Upg\Library\Locale\Codes::LOCALE_PT:
            case \Upg\Library\Locale\Codes::LOCALE_FI:
            case \Upg\Library\Locale\Codes::LOCALE_DE:
            case \Upg\Library\Locale\Codes::LOCALE_ES:
            case \Upg\Library\Locale\Codes::LOCALE_FR:
            case \Upg\Library\Locale\Codes::LOCALE_IT:
                return $language;
            default:
                return $defaultLocale;
        }
    }

    public static function validateCallBackUrl($url)
    {
        if(empty($url)){
            return false;
        }

        if (filter_var($url, FILTER_VALIDATE_URL) === FALSE) {
            return false;
        }

        $parsedUrl = parse_url($url);

        return in_array($parsedUrl['scheme'], array('http','https'));
    }

    public static function getOrderPaymentMethodString($paymentMethod)
    {
        $paymentMethod = trim($paymentMethod);

        switch($paymentMethod) {
            case 'DD':
                $paymentMethod = __('Direct Debit','upg');
                break;
            case 'CC':
                $paymentMethod = __('Credit Card','upg');
                break;
            case 'CC3D':
                $paymentMethod = __('Credit Card With 3D Secure','upg');
                break;
            case 'PREPAID':
                $paymentMethod = __('Cash in Advance','upg');
                break;
            case 'PAYPAL':
                $paymentMethod = __('PayPal','upg');
                break;
            case 'SU':
                $paymentMethod = __('Sofortüberweisung','upg');
                break;
            case 'BILL':
                $paymentMethod = __('Bill Payment without Payment Guarantee','upg');
                break;
            case 'BILL_SECURE':
                $paymentMethod = __('Bill Payment with Payment Guarantee','upg');
                break;
            case 'COD':
                $paymentMethod = __('Cash on delivery','upg');
                break;
            case 'IDEAL':
                $paymentMethod = __('Ideal','upg');
                break;
            case 'INSTALLMENT':
                $paymentMethod = __('Installment','upg');
                break;
            case 'PAYCO_WALLET':
                $paymentMethod = __('PayCo Wallet','upg');
                break;
            case 'DUMMY':
                $paymentMethod = __('Dummy','upg');
                break;
        }

        return $paymentMethod;
    }

    /**
     * Validate phonenumber using the same regex as the end point
     * @param $number
     * @return bool
     */
    public static function validatePhoneNumber($number)
    {
        return preg_match(self::PHONE_REGEX, $number)?true:false;
    }

    public static function translatePaymentInfoLabel($label)
    {
        $newLabel = false;
        $label = trim($label);
        switch($label) {
            case 'bankname':
                $newLabel = __('Bank Name','upg');
                break;
            case 'bic':
                $newLabel = __('BIC','upg');
                break;
            case 'iban':
                $newLabel = __('IBAN','upg');
                break;
            case 'bankAccountHolder':
                $newLabel = __('Bank Account Holder','upg');
                break;
            case 'paymentReference':
                $newLabel = __('Payment Reference','upg');
                break;
            case 'sepaMandate':
                $newLabel = __('SEPA Mandate','upg');
                break;
        }

        return $newLabel;
    }

    public static function translatePaymentInfoLabelAdmin($label)
    {
        $newLabel = self::translatePaymentInfoLabel($label);

        if($newLabel != false) {
            return $newLabel;
        }else{
            $newLabel = trim($label);
        }

        switch($label) {
            case 'customerEmail':
                $newLabel = __('Customer Email','upg');
                break;
            case 'email':
                $newLabel = __('Email','upg');
                break;
            case 'deliveryAddressCo':
                $newLabel = __('Delivery Address Co','upg');
                break;
            case 'deliveryAddressZip':
                $newLabel = __('Delivery Address Post Code','upg');
                break;
            case 'deliveryAddressNo':
                $newLabel = __('Delivery Address Number','upg');
                break;
            case 'deliveryAddressNoAdditional':
                $newLabel = __('Delivery Address Number Additional','upg');
                break;
            case 'deliveryAddressCity':
                $newLabel = __('Delivery Address City','upg');
                break;
            case 'deliveryAddressState':
                $newLabel = __('Delivery Address State','upg');
                break;
            case 'deliveryAddressStreet':
                $newLabel = __('Delivery Address Street','upg');
                break;
            case 'deliveryAddressRecipient':
                $newLabel = __('Delivery Address Recipient','upg');
                break;
            case 'deliveryAddressCountry':
                $newLabel = __('Delivery Address Country','upg');
                break;
            case 'transactionAmount':
                $newLabel = __('Transaction Amount','upg');
                break;
            case 'transactionCurrency':
                $newLabel = __('Transaction Currency','upg');
                break;
            case 'paymentMethod':
                $newLabel = __('Payment Method','upg');
                break;
        }

        return $newLabel;
    }
}