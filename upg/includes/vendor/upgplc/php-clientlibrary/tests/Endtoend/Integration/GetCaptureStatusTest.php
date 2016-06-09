<?php

namespace Upg\Library\Tests\Endtoend\Integration;

use Faker\Factory;
use Upg\Library\Api\CreateTransaction;
use Upg\Library\Config;
use Upg\Library\Locale\Codes;
use Upg\Library\PaymentMethods\Methods;
use Upg\Library\Request\CreateTransaction as CreateTransactionRequest;
use Upg\Library\Request\Reserve as ReserveRequest;
use Upg\Library\Api\Reserve as ReserveApi;
use Upg\Library\Request\Objects\Address;
use Upg\Library\Request\Objects\Amount;
use Upg\Library\Request\Objects\BasketItem;
use Upg\Library\Request\Objects\Person;
use Upg\Library\Risk\RiskClass;
use Upg\Library\Request\Objects\PaymentInstrument as PaymentInstrumentJson;
use Upg\Library\Request\RegisterUserPaymentInstrument as RegisterUserPaymentInstrumentRequest;
use Upg\Library\Api\RegisterUserPaymentInstrument as RegisterUserPaymentInstrumentApi;
use Upg\Library\Request\Capture as CaptureRequest;
use Upg\Library\Api\Capture as CaptureApi;
use Upg\Library\Request\GetCaptureStatus as GetCaptureStatusRequest;
use Upg\Library\Api\GetCaptureStatus as GetCaptureStatusApi;

class GetCaptureStatusTest extends \PHPUnit_Framework_TestCase
{
    private $faker;

    /**
     * Config object for tests
     * @var Config
     */
    private $config;

    private $paymentInstrument;

    public function setUp()
    {
        date_default_timezone_set('Europe/Berlin');

        $faker = Factory::create();

        $this->faker = $faker;

        $merchantPassword = trim(getenv('UPG_TEST_MERCHANT_PASSWORD'));
        $merchantID = trim(getenv('UPG_TEST_MERCHANT_ID'));
        $storeID = trim(getenv('UPG_TEST_STORE_ID'));
        $baseURL = trim(getenv('UPG_TEST_BASE_URL'));
        $logEnabled = false;

        if (!empty($merchantPassword) && !empty($merchantID) && !empty($storeID) && !empty($baseURL)) {
            $this->config = new Config(array(
                'merchantPassword' => $merchantPassword,
                'merchantID' => $merchantID,
                'storeID' => $storeID,
                'logEnabled' => $logEnabled,
                'sendRequestsWithSalt' => true,
                'baseUrl' => $baseURL
            ));
        } else {
            $this->config = null;
        }
    }

    public function tearDown()
    {
        unset($this->faker);
        unset($this->config);
    }

    private function getUser()
    {
        $date = new \DateTime();
        $date->setDate(1980, 1, 1);

        $user = new Person();
        $user->setSalutation(PERSON::SALUTATIONMALE)
            ->setName($this->faker->name)
            ->setSurname($this->faker->name)
            ->setDateOfBirth($date)
            ->setEmail($this->faker->email)
            ->setPhoneNumber('03452696645')
            ->setFaxNumber('03452696645');

        return $user;
    }

    private function getAddress()
    {
        $address = new Address();
        $address->setStreet("Test")
            ->setNo(45)
            ->setZip("LS1 4TN")
            ->setCity("City")
            ->setState("State")
            ->setCountry("GB");

        return $address;
    }

    private function getPaymentInstrument()
    {
        if (is_null($this->paymentInstrument)) {
            list($month, $year) = explode('/', $this->faker->creditCardExpirationDateString);
            $year = '20' . $year;

            $date = new \DateTime();
            $date->setDate($year, $month, 1);

            $this->paymentInstrument = new PaymentInstrumentJson();
            $this->paymentInstrument->setPaymentInstrumentType(PaymentInstrumentJson::PAYMENT_INSTRUMENT_TYPE_CARD)
                ->setAccountHolder($this->faker->name)
                ->setIssuer(PaymentInstrumentJson::ISSUER_VISA)
                ->setValidity($date)
                ->setNumber('4539272776120245');
        }

        return $this->paymentInstrument;
    }

    private function getAmount()
    {
        return new Amount(100, 0, 0);
    }

    private function getBasketItem()
    {
        $item = new BasketItem();
        $item->setBasketItemText("Test Item CALL")
            ->setBasketItemCount(1)
            ->setBasketItemAmount($this->getAmount());

        return $item;
    }

    /**
     * Make an successful call
     * Create a transaction then do the reserve call
     */
    public function testSuccessfulApiCall()
    {
        if (is_null($this->config)) {
            $this->markTestSkipped('Config is not set, please set up the required environment variables');
            return false;
        }

        $request = new CreateTransactionRequest($this->config);

        //unique ID for the tests
        $orderId = hash('crc32b', microtime());
        $captureId = hash('crc32b', microtime());
        $userId = "GUEST:" . hash('md5', microtime());

        $paymentInstrumentId = hash('crc32b', microtime());

        $request->setOrderID($orderId)
            ->setUserID($userId)
            ->setIntegrationType(CreateTransactionRequest::INTEGRATION_TYPE_API)
            ->setAutoCapture(false)
            ->setContext(CreateTransactionRequest::CONTEXT_ONLINE)
            ->setMerchantReference("TEST")
            ->setUserType(CreateTransactionRequest::USER_TYPE_PRIVATE)
            ->setUserRiskClass(RiskClass::RISK_CLASS_DEFAULT)
            ->setUserData($this->getUser())
            ->setBillingAddress($this->getAddress())
            ->setAmount($this->getAmount())
            ->addBasketItem($this->getBasketItem())
            ->setLocale(Codes::LOCALE_EN);

        $apiEndPoint = new CreateTransaction($this->config, $request);

        $createTransactionResult = $apiEndPoint->sendRequest();

        $this->assertEquals(0, $createTransactionResult->getData('resultCode'));

        $allowedPayments = $createTransactionResult->getData('allowedPaymentMethods');

        $this->assertTrue(in_array('CC', $createTransactionResult->getData('allowedPaymentMethods')));

        if (!in_array('CC', $allowedPayments)) {
            $this->fail("Allowed payment from CreateTransaction lacks CC can not continue");
        }

        /**
         * Register payment instrument
         */
        $registerUserPaymentInstrumentRequest = new RegisterUserPaymentInstrumentRequest($this->config);
        $registerUserPaymentInstrumentRequest->setUserID($userId)
            ->setPaymentInstrument($this->getPaymentInstrument());

        $registerUserPaymentInstrument = new RegisterUserPaymentInstrumentApi(
            $this->config,
            $registerUserPaymentInstrumentRequest
        );

        $registerUserPaymentInstrumentResult = $registerUserPaymentInstrument->sendRequest();

        $paymentInstrumentId = $registerUserPaymentInstrumentResult->getData('paymentInstrumentID');

        $reserveRequest = new ReserveRequest($this->config);

        $reserveRequest->setOrderID($orderId)
            ->setPaymentMethod(Methods::PAYMENT_METHOD_TYPE_CC)
            ->setPaymentInstrumentID($paymentInstrumentId)
            ->setCcv(123);

        $reserveApi = new ReserveApi($this->config, $reserveRequest);
        $reserveApi->sendRequest();

        /**
         * Do the capture
         */
        $captureRequest = new CaptureRequest($this->config);
        $captureRequest->setOrderID($orderId)
            ->setCaptureID($captureId)
            ->setAmount($this->getAmount());

        $captureApi = new CaptureApi($this->config, $captureRequest);
        $captureApi->sendRequest();

        /**
         * Do getCaptureStatus call
         */
        $getCaptureStatusRequest = new GetCaptureStatusRequest($this->config);
        $getCaptureStatusRequest->setOrderID($orderId)->setCaptureID($captureId);

        $getCaptureStatusApi = new GetCaptureStatusApi($this->config, $getCaptureStatusRequest);

        $result = $getCaptureStatusApi->sendRequest();

        $this->assertEquals(0, $result->getData('resultCode'));
        $this->assertEmpty($result->getData('message'));
        $this->assertEquals('PAID', $result->getData('captureStatus'));

        $additionalData = $result->getData('additionalData');

        $this->assertEquals(100, $additionalData['transactionAmount']);
        $this->assertEquals(100, $additionalData['capturedAmount']);
        $this->assertEquals(100, $additionalData['paidAmount']);

        $this->assertEquals('EUR', $additionalData['transactionCurrency']);
    }
}
