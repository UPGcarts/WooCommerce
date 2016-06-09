<?php

namespace Upg\Library\Tests\Endtoend\Integration;

use Faker\Factory;
use Upg\Library\Config;
use Upg\Library\Locale\Codes;
use Upg\Library\Request\RegisterUser as RegisterUserRequest;
use Upg\Library\Request\RegisterUserPaymentInstrument as RegisterUserPaymentInstrumentRequest;
use Upg\Library\Request\GetUserPaymentInstrument as GetUserPaymentInstrumentRequest;
use Upg\Library\Api\RegisterUser as RegisterUserApi;
use Upg\Library\Api\RegisterUserPaymentInstrument as RegisterUserPaymentInstrumentApi;
use Upg\Library\Api\GetUserPaymentInstrument as GetUserPaymentInstrumentApi;
use Upg\Library\Request\Objects\PaymentInstrument as PaymentInstrumentJson;
use Upg\Library\Risk\RiskClass;

class getUserPaymentInstrumentTest extends \PHPUnit_Framework_TestCase
{
    private $faker;

    /**
     * Config object for tests
     * @var Config
     */
    private $config;

    private $user;

    private $address;

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
        if (is_null($this->user)) {
            $date = new \DateTime();
            $date->setDate(1980, 1, 1);

            $this->user = new \Upg\Library\Request\Objects\Person();
            $this->user->setSalutation(\Upg\Library\Request\Objects\Person::SALUTATIONMALE)
                ->setName($this->faker->name)
                ->setSurname($this->faker->name)
                ->setDateOfBirth($date)
                ->setEmail($this->faker->email)
                ->setPhoneNumber('03452696645')
                ->setFaxNumber('03452696645');
        }

        return $this->user;
    }

    private function getAddress()
    {
        if (is_null($this->address)) {
            $this->address = new \Upg\Library\Request\Objects\Address();
            $this->address->setStreet("Test")
                ->setNo(45)
                ->setZip("LS1 4TN")
                ->setCity("City")
                ->setState("State")
                ->setCountry("GB");
        }

        return $this->address;
    }

    private function getPaymentInstrument()
    {
        if (is_null($this->paymentInstrument)) {
            $expiration = $this->faker->creditCardExpirationDateString;
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

        $userId = "REGISTED:" . hash('md5', microtime());
        $billingRecipient = $this->faker->name;
        $shippingRecipient = $this->faker->name;

        $registerRequest = new RegisterUserRequest($this->config);
        $registerRequest->setUserID($userId)
            ->setUserType(\Upg\Library\User\Type::USER_TYPE_PRIVATE)
            ->setUserRiskClass(RiskClass::RISK_CLASS_DEFAULT)
            ->setUserData($this->getUser())
            ->setBillingAddress($this->getAddress())
            ->setBillingRecipient($billingRecipient)
            ->setShippingAddress($this->getAddress())
            ->setShippingRecipient($shippingRecipient)
            ->setLocale(Codes::LOCALE_EN);

        $registerUserApi = new RegisterUserApi($this->config, $registerRequest);
        $registerUserResult = $registerUserApi->sendRequest();

        $this->assertEquals(0, $registerUserResult->getData('resultCode'));
        $this->assertEmpty($registerUserResult->getData('message'));
        $this->assertNotEmpty($registerUserResult->getData('salt'));


        $registerUserPaymentInstrumentRequest = new RegisterUserPaymentInstrumentRequest($this->config);
        $registerUserPaymentInstrumentRequest->setUserID($userId)
            ->setPaymentInstrument($this->getPaymentInstrument());

        $registerUserPaymentInstrument = new RegisterUserPaymentInstrumentApi(
            $this->config,
            $registerUserPaymentInstrumentRequest
        );

        $registerUserPaymentInstrumentResult = $registerUserPaymentInstrument->sendRequest();

        $paymentId = $registerUserPaymentInstrumentResult->getData('paymentInstrumentID');

        $getUserPaymentInstrumentRequest = new GetUserPaymentInstrumentRequest($this->config);
        $getUserPaymentInstrumentRequest->setUserID($userId);

        $getUserPaymentInstrumentApi = new GetUserPaymentInstrumentApi($this->config, $getUserPaymentInstrumentRequest);

        $getUserPaymentInstrumentResult = $getUserPaymentInstrumentApi->sendRequest();

        $this->assertEquals(0, $getUserPaymentInstrumentResult->getData('resultCode'));
        $this->assertEmpty($getUserPaymentInstrumentResult->getData('message'));
        $this->assertNotEmpty($getUserPaymentInstrumentResult->getData('salt'));
        $this->assertEquals(1, count($getUserPaymentInstrumentResult->getData('paymentInstruments')));

        $paymentInstruments = $getUserPaymentInstrumentResult->getData('paymentInstruments');
        $paymentInstrument = $paymentInstruments[0];

        $this->assertEquals($this->getPaymentInstrument()->getIssuer(), $paymentInstrument->getIssuer());
        $this->assertEquals(
            $this->getPaymentInstrument()->getPaymentInstrumentType(),
            $paymentInstrument->getPaymentInstrumentType()
        );

        $this->assertEquals(
            $this->getPaymentInstrument()->getValidity()->format('Y-m'),
            $paymentInstrument->getValidity()->format('Y-m')
        );

    }
}