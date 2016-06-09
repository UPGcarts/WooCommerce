<?php

namespace Upg\Library\Tests\Endtoend\Integration;

use Faker\Factory;
use Upg\Library\Config;
use Upg\Library\Locale\Codes;
use Upg\Library\Request\Objects\Person;
use Upg\Library\Request\RegisterUser as RegisterUserRequest;
use Upg\Library\Request\GetUser as GetUserRequest;
use Upg\Library\Api\RegisterUser as RegisterUserApi;
use Upg\Library\Api\GetUser as GetUserApi;
use Upg\Library\Risk\RiskClass;

class GetUserTest extends \PHPUnit_Framework_TestCase
{
    private $faker;

    /**
     * Config object for tests
     * @var Config
     */
    private $config;

    private $user;

    private $address;

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
                ->setEmail(strtolower($this->faker->email))
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
        $registerUserApi->sendRequest();

        /**
         * ok now get the user
         */
        $getUserRequest = new GetUserRequest($this->config);
        $getUserRequest->setUserID($userId);

        $getUserApi = new GetUserApi($this->config, $getUserRequest);
        $result = $getUserApi->sendRequest();

        $this->assertEquals(0, $result->getData('resultCode'));
        $this->assertEmpty($result->getData('message'));
        $this->assertNotEmpty($result->getData('salt'));

        /**
         * @var Person $returnedPerson
         */
        $returnedPerson = $result->getData('userData');
        $this->assertEquals(\Upg\Library\Request\Objects\Person::SALUTATIONMALE, $returnedPerson->getSalutation());
        $this->assertEquals($this->getUser()->getName(), $returnedPerson->getName());
        $this->assertEquals($this->getUser()->getSurname(), $returnedPerson->getSurname());
        $this->assertEquals(
            $this->getUser()->getDateOfBirth()->format('Y-m-d'),
            $returnedPerson->getDateOfBirth()->format('Y-m-d')
        );

        $this->assertEquals($this->getUser()->getEmail(), $returnedPerson->getEmail());
        $this->assertEquals($this->getUser()->getPhoneNumber(), $returnedPerson->getPhoneNumber());

        $this->assertEquals($billingRecipient, $result->getData('billingRecipient'));

        /**
         * billingAddress and shippingAddress should be converted to an address object
         */
        $this->assertEquals($this->getAddress()->getNo(), $result->getData('billingAddress')->getNo());
        $this->assertEquals($this->getAddress()->getStreet(), $result->getData('billingAddress')->getStreet());
        $this->assertEquals($this->getAddress()->getZip(), $result->getData('billingAddress')->getZip());
        $this->assertEquals($this->getAddress()->getCity(), $result->getData('billingAddress')->getCity());
        $this->assertEquals($this->getAddress()->getState(), $result->getData('billingAddress')->getState());
        $this->assertEquals($this->getAddress()->getCountry(), $result->getData('billingAddress')->getCountry());

        $this->assertEquals($this->getAddress()->getNo(), $result->getData('shippingAddress')->getNo());
        $this->assertEquals($this->getAddress()->getStreet(), $result->getData('shippingAddress')->getStreet());
        $this->assertEquals($this->getAddress()->getZip(), $result->getData('shippingAddress')->getZip());
        $this->assertEquals($this->getAddress()->getCity(), $result->getData('shippingAddress')->getCity());
        $this->assertEquals($this->getAddress()->getState(), $result->getData('shippingAddress')->getState());
        $this->assertEquals($this->getAddress()->getCountry(), $result->getData('shippingAddress')->getCountry());
    }
}
