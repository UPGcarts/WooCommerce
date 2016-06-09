<?php

namespace Upg\Library\Tests\Api;

use Upg\Library\Api\CreateTransaction as CreateTransactionApi;
use Upg\Library\Request\CreateTransaction;
use Upg\Library\Config;

class CreateTransactionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var Generator
     */
    private $faker;

    /**
     * Config object for tests
     * @var Config
     */
    private $config;

    public function setUp()
    {
        date_default_timezone_set('Europe/Berlin');

        $faker = \Faker\Factory::create();

        $this->faker = $faker;

        $this->config = new Config(array(
            'merchantPassword' => '8A!v#6qPc3?+G1on',
            'merchantID' => '123',
            'storeID' => 'test Store',
            'sendRequestsWithSalt' => true,
            'baseUrl' => "http://www.something.com/"
        ));
    }

    public function tearDown()
    {
        unset($this->faker);
        unset($this->config);
    }

    private function getUser()
    {
        $user = new \Upg\Library\Request\Objects\Person();
        $user->setSalutation(\Upg\Library\Request\Objects\Person::SALUTATIONMALE)
            ->setName($this->faker->name)
            ->setSurname($this->faker->name)
            ->setDateOfBirth(new \DateTime())
            ->setEmail($this->faker->email)
            ->setPhoneNumber('555666')
            ->setFaxNumber('555454');

        return $user;
    }

    private function getAddress()
    {
        $address = new \Upg\Library\Request\Objects\Address();
        $address->setStreet("Test")
            ->setNo(45)
            ->setZip("LS12 4TN")
            ->setCity("City")
            ->setState("State")
            ->setCountry("GB");

        return $address;
    }

    private function getAmount()
    {
        return new \Upg\Library\Request\Objects\Amount(100, 0, 0);
    }

    private function getBasketItem()
    {
        $item = new \Upg\Library\Request\Objects\BasketItem();
        $item->setBasketItemText("Test Item")
            ->setBasketItemCount(1)
            ->setBasketItemAmount($this->getAmount());

        return $item;
    }

    public function testGetUrl()
    {
        $request = new CreateTransaction($this->config);
        $request->setOrderID(1)
            ->setUserID(1)
            ->setIntegrationType(CreateTransaction::INTEGRATION_TYPE_HOSTED_AFTER)
            ->setAutoCapture(true)
            ->setContext(CreateTransaction::CONTEXT_ONLINE)
            ->setMerchantReference("TEST")
            ->setUserType(CreateTransaction::USER_TYPE_PRIVATE)
            ->setUserRiskClass(\Upg\Library\Risk\RiskClass::RISK_CLASS_DEFAULT)
            ->setUserIpAddress("192.168.1.2")
            ->setUserData($this->getUser())
            ->setBillingAddress($this->getAddress())
            ->setAmount($this->getAmount())
            ->addBasketItem($this->getBasketItem())
            ->setLocale(\Upg\Library\Locale\Codes::LOCALE_EN);

        $api = new CreateTransactionApi($this->config, $request);

        $this->assertEquals('http://www.something.com/createTransaction', $api->getUrl());
    }

    /**
     * Test the api
     */
    public function testSuccessfulMockRequest()
    {
        $header = "HTTP/1.1 200 OK
            \r\nDate: Wed, 18 Nov 2015 14:23:48 GMT
            \r\nServer: Jetty(8.1.15.v20140411)
            \r\nContent-Type: application/json;charset=UTF-8
            \r\nAccess-Control-Allow-Origin: *
            \r\nAccess-Control-Allow-Methods: POST
            \r\nAccess-Control-Expose-Headers: X-Payco-TOKEN, X-Payco-HMAC
            \r\nX-Payco-HMAC: 82b07247878f8c7dd8bf2667ea2ab39fa1cf4a48
            \r\nVia: 1.1 www.payco-sandbox.de
            \r\nConnection: close
            \r\nTransfer-Encoding: chunked";

        $rawResponse = '{
          "resultCode": 0,
          "allowedPaymentMethods": [
            "DD",
            "CC3D",
            "PAYPAL",
            "SU"
          ],
          "allowedPaymentInstruments": [
            {
              "paymentInstrumentType": "CREDITCARD",
              "accountHolder": "Keyshawn Sawayn",
              "number": "5572314355479157",
              "validity": "2015-11",
              "issuer": "MC",
              "paymentInstrumentID": 1
            }
          ],
          "url": "http://jsonlint.com/",
          "salt": "nMp9eFTqrURBqquBb3P9hRX8g7RDzE8DCvu3nKwYJLvwha8F"
        }';

        $request = new CreateTransaction($this->config);
        $request->setOrderID(1)
            ->setUserID(1)
            ->setIntegrationType(CreateTransaction::INTEGRATION_TYPE_HOSTED_AFTER)
            ->setAutoCapture(true)
            ->setContext(CreateTransaction::CONTEXT_ONLINE)
            ->setMerchantReference("TEST")
            ->setUserType(CreateTransaction::USER_TYPE_PRIVATE)
            ->setUserRiskClass(\Upg\Library\Risk\RiskClass::RISK_CLASS_DEFAULT)
            ->setUserIpAddress("192.168.1.2")
            ->setUserData($this->getUser())
            ->setBillingAddress($this->getAddress())
            ->setAmount($this->getAmount())
            ->addBasketItem($this->getBasketItem())
            ->setLocale(\Upg\Library\Locale\Codes::LOCALE_EN);

        $api = new CreateTransactionApi($this->config, $request);

        $api->setResponseRaw($rawResponse, 200, $header);

        $response = $api->sendRequest();

        $this->assertEquals(0, $response->getData('resultCode'));
        $this->assertArraySubset(array('DD', 'CC3D', 'PAYPAL', 'SU'), $response->getData('allowedPaymentMethods'));

        $paymentInstruments = $response->getData('allowedPaymentInstruments');
        $paymentInstruments = current($paymentInstruments);

        $this->assertInstanceOf('Upg\Library\Request\Objects\PaymentInstrument', $paymentInstruments);
        $this->assertEquals(
            $paymentInstruments->getPaymentInstrumentType(),
            \Upg\Library\Request\Objects\PaymentInstrument::PAYMENT_INSTRUMENT_TYPE_CARD
        );

        $this->assertEquals(
            $paymentInstruments->getIssuer(),
            \Upg\Library\Request\Objects\PaymentInstrument::ISSUER_MC
        );

        $this->assertEquals("http://jsonlint.com/", $response->getData('url'));
    }

    /**
     * Test if validation exception is thrown
     * @expectedException Upg\Library\Api\Exception\Validation
     */
    public function testValidationException()
    {
        $request = new CreateTransaction($this->config);
        $request->setOrderID(1)
            ->setUserID(1)
            ->setIntegrationType(CreateTransaction::INTEGRATION_TYPE_HOSTED_AFTER)
            ->setAutoCapture(true)
            ->setContext(CreateTransaction::CONTEXT_ONLINE)
            ->setMerchantReference("TEST")
            ->setUserType(CreateTransaction::USER_TYPE_PRIVATE)
            ->setUserRiskClass(\Upg\Library\Risk\RiskClass::RISK_CLASS_DEFAULT)
            ->setUserIpAddress("192.168.1.2")
            ->setUserData($this->getUser())
            ->setBillingAddress($this->getAddress())
            ->addBasketItem($this->getBasketItem())
            ->setLocale(\Upg\Library\Locale\Codes::LOCALE_EN);

        $api = new CreateTransactionApi($this->config, $request);

        $api->sendRequest();
    }

    /**
     * Test if validation exception is thrown
     * @expectedException Upg\Library\Api\Exception\InvalidUrl
     */
    public function testInvalidUrlException()
    {
        $request = new CreateTransaction($this->config);
        $request->setOrderID(1)
            ->setUserID(1)
            ->setIntegrationType(CreateTransaction::INTEGRATION_TYPE_HOSTED_AFTER)
            ->setAutoCapture(true)
            ->setContext(CreateTransaction::CONTEXT_ONLINE)
            ->setMerchantReference("TEST")
            ->setUserType(CreateTransaction::USER_TYPE_PRIVATE)
            ->setUserRiskClass(\Upg\Library\Risk\RiskClass::RISK_CLASS_DEFAULT)
            ->setUserIpAddress("192.168.1.2")
            ->setUserData($this->getUser())
            ->setBillingAddress($this->getAddress())
            ->setAmount($this->getAmount())
            ->addBasketItem($this->getBasketItem())
            ->setLocale(\Upg\Library\Locale\Codes::LOCALE_EN);

        $config = new Config(array(
            'merchantPassword' => '8A!v#6qPc3?+G1on',
            'merchantID' => '123',
            'storeID' => 'test Store',
            'sendRequestsWithSalt' => true,
        ));

        $api = new CreateTransactionApi($config, $request);

        $api->sendRequest();
    }

    /**
     * Test if InvalidHttpResponseCode exception is thrown
     * @expectedException Upg\Library\Api\Exception\InvalidHttpResponseCode
     */
    public function testInvalidHttpResponseCode()
    {
        $header = "HTTP/1.1 200 OK
            \r\nDate: Wed, 18 Nov 2015 14:23:48 GMT
            \r\nServer: Jetty(8.1.15.v20140411)
            \r\nContent-Type: application/json;charset=UTF-8
            \r\nAccess-Control-Allow-Origin: *
            \r\nAccess-Control-Allow-Methods: POST
            \r\nAccess-Control-Expose-Headers: X-Payco-TOKEN, X-Payco-HMAC
            \r\nX-Payco-HMAC: 82b07247878f8c7dd8bf2667ea2ab39fa1cf4a48
            \r\nVia: 1.1 www.payco-sandbox.de
            \r\nConnection: close
            \r\nTransfer-Encoding: chunked";

        $rawResponse = '{
          "resultCode": 0,
          "allowedPaymentMethods": [
            "DD",
            "CC3D",
            "PAYPAL",
            "SU"
          ],
          "allowedPaymentInstruments": [
            {
              "paymentInstrumentType": "CREDITCARD",
              "accountHolder": "Keyshawn Sawayn",
              "number": "5572314355479157",
              "validity": "2015-11",
              "issuer": "MC",
              "paymentInstrumentID": 1
            }
          ],
          "url": "http://jsonlint.com/",
          "salt": "nMp9eFTqrURBqquBb3P9hRX8g7RDzE8DCvu3nKwYJLvwha8F"
        }';

        $request = new CreateTransaction($this->config);
        $request->setOrderID(1)
            ->setUserID(1)
            ->setIntegrationType(CreateTransaction::INTEGRATION_TYPE_HOSTED_AFTER)
            ->setAutoCapture(true)
            ->setContext(CreateTransaction::CONTEXT_ONLINE)
            ->setMerchantReference("TEST")
            ->setUserType(CreateTransaction::USER_TYPE_PRIVATE)
            ->setUserRiskClass(\Upg\Library\Risk\RiskClass::RISK_CLASS_DEFAULT)
            ->setUserIpAddress("192.168.1.2")
            ->setUserData($this->getUser())
            ->setBillingAddress($this->getAddress())
            ->setAmount($this->getAmount())
            ->addBasketItem($this->getBasketItem())
            ->setLocale(\Upg\Library\Locale\Codes::LOCALE_EN);

        $api = new CreateTransactionApi($this->config, $request);

        $api->setResponseRaw($rawResponse, 201, $header);
        $api->sendRequest();
    }

    /**
     * Test if validation exception is thrown
     * @expectedException Upg\Library\Api\Exception\JsonDecode
     */
    public function testJsonDecodeException()
    {
        $header = "HTTP/1.1 200 OK
            \r\nDate: Wed, 18 Nov 2015 14:23:48 GMT
            \r\nServer: Jetty(8.1.15.v20140411)
            \r\nContent-Type: application/json;charset=UTF-8
            \r\nAccess-Control-Allow-Origin: *
            \r\nAccess-Control-Allow-Methods: POST
            \r\nAccess-Control-Expose-Headers: X-Payco-TOKEN, X-Payco-HMAC
            \r\nX-Payco-HMAC: 82b07247878f8c7dd8bf2667ea2ab39fa1cf4a48
            \r\nVia: 1.1 www.payco-sandbox.de
            \r\nConnection: close
            \r\nTransfer-Encoding: chunked";

        $rawResponse = '{
          "resultCode": 0,
          "allowedPaymentMethods": [
            "DD",
            "CC3D",
            "PAYPAL",
            "SU"
          ],
          "allowedPaymentInstruments": [
            {
              "paymentInstrumentType": "CREDITCARD",
              "accountHolder": "Keyshawn Sawayn",
              "number": "5572314355479157",
              "validity": "2015-11",
              "issuer": "MC",
              "paymentInstrumentID": 1
            }
          ],
          "url": "http://jsonlint.com/"
          "salt": "nMp9eFTqrURBqquBb3P9hRX8g7RDzE8DCvu3nKwYJLvwha8F"
        }';

        $request = new CreateTransaction($this->config);
        $request->setOrderID(1)
            ->setUserID(1)
            ->setIntegrationType(CreateTransaction::INTEGRATION_TYPE_HOSTED_AFTER)
            ->setAutoCapture(true)
            ->setContext(CreateTransaction::CONTEXT_ONLINE)
            ->setMerchantReference("TEST")
            ->setUserType(CreateTransaction::USER_TYPE_PRIVATE)
            ->setUserRiskClass(\Upg\Library\Risk\RiskClass::RISK_CLASS_DEFAULT)
            ->setUserIpAddress("192.168.1.2")
            ->setUserData($this->getUser())
            ->setBillingAddress($this->getAddress())
            ->setAmount($this->getAmount())
            ->addBasketItem($this->getBasketItem())
            ->setLocale(\Upg\Library\Locale\Codes::LOCALE_EN);

        $api = new CreateTransactionApi($this->config, $request, $header);

        $api->setResponseRaw($rawResponse, 200, $header);

        $api->sendRequest();
    }

    /**
     * Test if validation exception is thrown
     * @expectedException Upg\Library\Api\Exception\MacValidation
     */
    public function testMacValidationException()
    {
        $header = "HTTP/1.1 200 OK
            \r\nDate: Wed, 18 Nov 2015 14:23:48 GMT
            \r\nServer: Jetty(8.1.15.v20140411)
            \r\nContent-Type: application/json;charset=UTF-8
            \r\nAccess-Control-Allow-Origin: *
            \r\nAccess-Control-Allow-Methods: POST
            \r\nAccess-Control-Expose-Headers: X-Payco-TOKEN, X-Payco-HMAC
            \r\nX-Payco-HMAC: 82b07247878f8c7dd8bf2667ea2ab39fa1cf5b59
            \r\nVia: 1.1 www.payco-sandbox.de
            \r\nConnection: close
            \r\nTransfer-Encoding: chunked";

        $rawResponse = '{
          "resultCode": 0,
          "allowedPaymentMethods": [
            "DD",
            "CC3D",
            "PAYPAL",
            "SU"
          ],
          "allowedPaymentInstruments": [
            {
              "paymentInstrumentType": "CREDITCARD",
              "accountHolder": "Keyshawn Sawayn",
              "number": "5572314355479157",
              "validity": "2015-11",
              "issuer": "MC",
              "paymentInstrumentID": 1
            }
          ],
          "url": "http://jsonlint.com/",
          "salt": "nMp9eFTqrURBqquBb3P9hRX8g7RDzE8DCvu3nKwYJLvwha8F"
        }';

        $request = new CreateTransaction($this->config);
        $request->setOrderID(1)
            ->setUserID(1)
            ->setIntegrationType(CreateTransaction::INTEGRATION_TYPE_HOSTED_AFTER)
            ->setAutoCapture(true)
            ->setContext(CreateTransaction::CONTEXT_ONLINE)
            ->setMerchantReference("TEST")
            ->setUserType(CreateTransaction::USER_TYPE_PRIVATE)
            ->setUserRiskClass(\Upg\Library\Risk\RiskClass::RISK_CLASS_DEFAULT)
            ->setUserIpAddress("192.168.1.2")
            ->setUserData($this->getUser())
            ->setBillingAddress($this->getAddress())
            ->setAmount($this->getAmount())
            ->addBasketItem($this->getBasketItem())
            ->setLocale(\Upg\Library\Locale\Codes::LOCALE_EN);

        $api = new CreateTransactionApi($this->config, $request);

        $api->setResponseRaw($rawResponse, 200, $header);

        $api->sendRequest();
    }

    /**
     * Test for API Error Exception
     * Note this wont use annotation as this exception has extra data that must be validated
     */
    public function testApiErrorException()
    {
        $errorCode = \Upg\Library\Error\Codes::ERROR_MAC;
        $message = "Invalid Mac Error from API";

        $exceptionRaised = false;

        $header = "HTTP/1.1 200 OK
            \r\nDate: Wed, 18 Nov 2015 14:23:48 GMT
            \r\nServer: Jetty(8.1.15.v20140411)
            \r\nContent-Type: application/json;charset=UTF-8
            \r\nAccess-Control-Allow-Origin: *
            \r\nAccess-Control-Allow-Methods: POST
            \r\nAccess-Control-Expose-Headers: X-Payco-TOKEN, X-Payco-HMAC
            \r\nX-Payco-HMAC: d91d92a84c215dbbc045d7fdce0405a0ea14ae61
            \r\nVia: 1.1 www.payco-sandbox.de
            \r\nConnection: close
            \r\nTransfer-Encoding: chunked";

        $rawResponse = '{
          "resultCode": ' . $errorCode . ',
          "message": "' . $message . '",
          "salt": "nMp9eFTqrURBqquBb3P9hRX8g7RDzE8DCvu3nKwYJLvwha8F"
        }';
        try {
            $request = new CreateTransaction($this->config);
            $request->setOrderID(1)
                ->setUserID(1)
                ->setIntegrationType(CreateTransaction::INTEGRATION_TYPE_HOSTED_AFTER)
                ->setAutoCapture(true)
                ->setContext(CreateTransaction::CONTEXT_ONLINE)
                ->setMerchantReference("TEST")
                ->setUserType(CreateTransaction::USER_TYPE_PRIVATE)
                ->setUserRiskClass(\Upg\Library\Risk\RiskClass::RISK_CLASS_DEFAULT)
                ->setUserIpAddress("192.168.1.2")
                ->setUserData($this->getUser())
                ->setBillingAddress($this->getAddress())
                ->setAmount($this->getAmount())
                ->addBasketItem($this->getBasketItem())
                ->setLocale(\Upg\Library\Locale\Codes::LOCALE_EN);

            $api = new CreateTransactionApi($this->config, $request);

            $api->setResponseRaw($rawResponse, 400, $header);

            $api->sendRequest();
        } catch (\Upg\Library\Api\Exception\ApiError $e) {
            $exceptionRaised = true;
            $response = $e->getParsedResponse();
            $this->assertEquals($errorCode, $response->getData('resultCode'));
            $this->assertEquals($message, $response->getData('message'));
            $this->assertEquals($errorCode, $e->getCode());
        }

        if (!$exceptionRaised) {
            $this->fail("Excpected exception was not raised");
        }
    }
}
