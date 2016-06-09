<?php

namespace Upg\Library\Tests\Endtoend\Integration;

use Faker\Factory;
use Upg\Library\Config;
use Upg\Library\Locale\Codes;
use Upg\Library\Request\RegisterUser as RegisterUserRequest;
use Upg\Library\Api\RegisterUser as RegisterUserApi;
use Upg\Library\Risk\RiskClass;

class RegisterUserTest extends \PHPUnit_Framework_TestCase
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

        $user = new \Upg\Library\Request\Objects\Person();
        $user->setSalutation(\Upg\Library\Request\Objects\Person::SALUTATIONMALE)
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
        $address = new \Upg\Library\Request\Objects\Address();
        $address->setStreet("Test")
            ->setNo(45)
            ->setZip("LS1 4TN")
            ->setCity("City")
            ->setState("State")
            ->setCountry("GB");

        return $address;
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

        $registerRequest = new RegisterUserRequest($this->config);
        $registerRequest->setUserID($userId)
            ->setUserType(\Upg\Library\User\Type::USER_TYPE_PRIVATE)
            ->setUserRiskClass(RiskClass::RISK_CLASS_DEFAULT)
            ->setUserData($this->getUser())
            ->setBillingAddress($this->getAddress())
            ->setBillingRecipient($this->faker->name)
            ->setShippingAddress($this->getAddress())
            ->setShippingRecipient($this->faker->name)
            ->setLocale(Codes::LOCALE_EN);

        $registerUserApi = new RegisterUserApi($this->config, $registerRequest);
        $result = $registerUserApi->sendRequest();

        $this->assertEquals(0, $result->getData('resultCode'));
        $this->assertEmpty($result->getData('message'));
        $this->assertNotEmpty($result->getData('salt'));
    }
}
