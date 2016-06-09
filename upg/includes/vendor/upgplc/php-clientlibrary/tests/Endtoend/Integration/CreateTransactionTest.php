<?php

namespace Upg\Library\Tests\Endtoend\Integration;

use Faker\Factory;
use Upg\Library\Api\CreateTransaction;
use Upg\Library\Config;
use Upg\Library\Locale\Codes;
use Upg\Library\Request\CreateTransaction as CreateTransactionRequest;
use Upg\Library\Request\Objects\Address;
use Upg\Library\Request\Objects\Amount;
use Upg\Library\Request\Objects\BasketItem;
use Upg\Library\Request\Objects\Person;
use Upg\Library\Risk\RiskClass;
use \Upg\Library\Request\Objects\Company;

class CreateTransactionTest extends \PHPUnit_Framework_TestCase
{
    private $faker;

    /**
     * Config object for tests
     * @var Config
     */
    private $config;

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
                'baseUrl' => $baseURL,
                'logLocationRequest' => '/tmp/CreateTransactionTest.log',
                'logLevel' => \Psr\Log\LogLevel::DEBUG,
                'logEnabled' => true,
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
        $userId = "GUEST:".hash('md5', microtime());

        $request->setOrderID($orderId)
            ->setUserID($userId)
            ->setIntegrationType(CreateTransactionRequest::INTEGRATION_TYPE_API)
            ->setAutoCapture(true)
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

        $result = $apiEndPoint->sendRequest();

        $this->assertEquals(0, $result->getData('resultCode'));
        $this->assertGreaterThan(1, count($result->getData('allowedPaymentMethods')));
    }

    public function testSuccessfulApiHostedBeforeIntegrationCall()
    {
        if (is_null($this->config)) {
            $this->markTestSkipped('Config is not set, please set up the required environment variables');
            return false;
        }

        $request = new CreateTransactionRequest($this->config);

        //unique ID for the tests
        $orderId = hash('crc32b', microtime());
        $userId = "GUEST:".hash('md5', microtime());

        $request->setOrderID($orderId)
            ->setUserID($userId)
            ->setIntegrationType(CreateTransactionRequest::INTEGRATION_TYPE_HOSTED_BEFORE)
            ->setAutoCapture(true)
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

        $result = $apiEndPoint->sendRequest();

        $this->assertEquals(1, $result->getData('resultCode'));
        $this->assertNotEmpty($result->getData('redirectUrl'));

        $ch = curl_init($result->getData('redirectUrl'));
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->assertEquals(200, $httpCode);
    }

    public function testSuccessfulApiHostedAfterIntegrationCall()
    {
        if (is_null($this->config)) {
            $this->markTestSkipped('Config is not set, please set up the required environment variables');
            return false;
        }

        $request = new CreateTransactionRequest($this->config);

        //unique ID for the tests
        $orderId = hash('crc32b', microtime());
        $userId = "GUEST:".hash('md5', microtime());

        $request->setOrderID($orderId)
            ->setUserID($userId)
            ->setIntegrationType(CreateTransactionRequest::INTEGRATION_TYPE_HOSTED_AFTER)
            ->setAutoCapture(true)
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

        $result = $apiEndPoint->sendRequest();

        $this->assertEquals(1, $result->getData('resultCode'));
        $this->assertNotEmpty($result->getData('redirectUrl'));

        $ch = curl_init($result->getData('redirectUrl'));
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->assertEquals(200, $httpCode);
    }

    /**
     * This test shopuld return an API error
     * @expectedException Upg\Library\Api\Exception\ApiError
     */
    public function testApiCallThatReturnsAnAPIError()
    {
        if (is_null($this->config)) {
            $this->markTestSkipped('Config is not set, please set up the required environment variables');
            return false;
        }

        $configData = $this->config->getConfigData();

        $configData['storeID'] = "WRONGSTOREID";

        $config = new Config($configData);

        $request = new CreateTransactionRequest($config);

        //unique ID for the tests
        $orderId = hash('crc32b', microtime());
        $userId = "GUEST:".hash('md5', microtime());

        $request->setOrderID($orderId)
            ->setUserID($userId)
            ->setIntegrationType(CreateTransactionRequest::INTEGRATION_TYPE_API)
            ->setAutoCapture(true)
            ->setContext(CreateTransactionRequest::CONTEXT_OFFLINE)
            ->setMerchantReference("TEST")
            ->setUserType(CreateTransactionRequest::USER_TYPE_PRIVATE)
            ->setUserRiskClass(RiskClass::RISK_CLASS_DEFAULT)
            ->setUserData($this->getUser())
            ->setBillingAddress($this->getAddress())
            ->setAmount($this->getAmount())
            ->addBasketItem($this->getBasketItem())
            ->setLocale(Codes::LOCALE_EN);

        $apiEndPoint = new CreateTransaction($config, $request);

        $apiEndPoint->sendRequest();
    }

    public function testSuccessfulB2bTransaction()
    {
        if (is_null($this->config)) {
            $this->markTestSkipped('Config is not set, please set up the required environment variables');
            return false;
        }

        $request = new CreateTransactionRequest($this->config);

        //unique ID for the tests
        $orderId = hash('crc32b', microtime());
        $userId = "GUEST:".hash('md5', microtime());

        $request->setOrderID($orderId)
            ->setUserID($userId)
            ->setIntegrationType(CreateTransactionRequest::INTEGRATION_TYPE_HOSTED_AFTER)
            ->setAutoCapture(true)
            ->setContext(CreateTransactionRequest::CONTEXT_ONLINE)
            ->setMerchantReference("TEST")
            ->setUserType(CreateTransactionRequest::USER_TYPE_BUSINESS)
            ->setUserRiskClass(RiskClass::RISK_CLASS_DEFAULT)
            ->setUserData($this->getUser())
            ->setBillingAddress($this->getAddress())
            ->setAmount($this->getAmount())
            ->addBasketItem($this->getBasketItem())
            ->setLocale(Codes::LOCALE_EN);

        $company = new Company();
        $company->setCompanyName($this->faker->company)
            ->setCompanyRegisterType(Company::COMPANY_TYPE_FN)
            ->setCompanyVatID(4087620)
            ->setCompanyVatID(4087620);

        $request->setCompanyData($company);

        $apiEndPoint = new CreateTransaction($this->config, $request);

        $result = $apiEndPoint->sendRequest();

        $this->assertEquals(1, $result->getData('resultCode'));
        $this->assertNotEmpty($result->getData('redirectUrl'));

        $ch = curl_init($result->getData('redirectUrl'));
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $this->assertEquals(200, $httpCode);
    }
}
