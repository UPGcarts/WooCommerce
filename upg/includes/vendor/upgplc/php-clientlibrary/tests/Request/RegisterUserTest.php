<?php

namespace Upg\Library\Tests\Request;

use Faker\Factory;
use Upg\Library\Config;
use Upg\Library\Locale\Codes;
use Upg\Library\Request\Objects\Address;
use Upg\Library\Request\Objects\Person;
use Upg\Library\Request\RegisterUser;
use Upg\Library\Risk\RiskClass;
use Upg\Library\Validation\Validation;

class RegisterUserTest extends AbstractRequestTest
{
    /**
     * @var string A very long string
     */
    private $veryLongString;

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

        $faker = Factory::create();

        $this->veryLongString = preg_replace("/[^A-Za-z0-9]/", '', $faker->sentence(90));
        $this->faker = $faker;

        $this->config = new Config(array(
            'merchantPassword' => '8A!v#6qPc3?+G1on',
            'merchantID' => '123',
            'storeID' => 'test Store',
            'sendRequestsWithSalt' => true,
        ));
    }

    public function tearDown()
    {
        unset($this->faker);
        unset($this->config);
    }

    /**
     * Get the person
     * @return Person
     */
    private function getUser()
    {
        $user = new Person();
        $user->setSalutation(PERSON::SALUTATIONMALE)
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
        $address = new Address();
        $address->setStreet("Test")
            ->setNo(45)
            ->setZip("LS12 4TN")
            ->setCity("City")
            ->setState("State")
            ->setCountry("GB");

        return $address;
    }

    public function testRegisterUserValidationSuccess()
    {
        $request = new RegisterUser($this->config);
        $request->setUserId(11)
            ->setUserType(\Upg\Library\User\Type::USER_TYPE_PRIVATE)
            ->setUserRiskClass(RiskClass::RISK_CLASS_DEFAULT)
            ->setUserData($this->getUser())
            ->setBillingRecipient($this->faker->name)
            ->setBillingAddress($this->getAddress())
            ->setShippingRecipient($this->faker->name)
            ->setShippingAddress($this->getAddress())
            ->setLocale(Codes::LOCALE_EN);

        $validation = new Validation();

        $validation->getValidator($request);
        $data = $validation->performValidation();

        $this->assertEmpty($data, "Validation found an issue when there should be none");
    }

    public function testRegisterUserValidationUserID()
    {
        $request = new RegisterUser($this->config);
        $request->setUserType(\Upg\Library\User\Type::USER_TYPE_PRIVATE)
            ->setUserRiskClass(RiskClass::RISK_CLASS_DEFAULT)
            ->setUserData($this->getUser())
            ->setBillingRecipient($this->faker->name)
            ->setBillingAddress($this->getAddress())
            ->setShippingRecipient($this->faker->name)
            ->setShippingAddress($this->getAddress())
            ->setLocale(Codes::LOCALE_EN);

        $validation = new Validation();

        $validation->getValidator($request);
        $data = $validation->performValidation();

        /**
         * Test Required
         */
        $this->assertValidationReturned(
            'Upg\\Library\\Request\\RegisterUser',
            'userID',
            'userID is required',
            $data,
            "userID is required validation failed"
        );

        /**
         * Test length validation
         */
        $request->setUserID($this->veryLongString);
        $validation->getValidator($request);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\RegisterUser',
            'userID',
            'userID must be between 1 and 50 characters',
            $data,
            "userID must be between 1 and 50 characters failed"
        );
    }

    public function testRegisterUserValidationUserType()
    {
        $request = new RegisterUser($this->config);
        $request->setUserID(1)
            ->setUserRiskClass(RiskClass::RISK_CLASS_DEFAULT)
            ->setUserData($this->getUser())
            ->setBillingRecipient($this->faker->name)
            ->setBillingAddress($this->getAddress())
            ->setShippingRecipient($this->faker->name)
            ->setShippingAddress($this->getAddress())
            ->setLocale(Codes::LOCALE_EN);

        $validation = new Validation();

        $validation->getValidator($request);
        $data = $validation->performValidation();

        /**
         * Test Required
         */
        $this->assertValidationReturned(
            'Upg\\Library\\Request\\RegisterUser',
            'userType',
            'userType is required',
            $data,
            "userType is required validation failed"
        );

        /**
         * Test length validation
         */
        $request->setUserType($this->veryLongString);
        $validation->getValidator($request);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\RegisterUser',
            'userType',
            'userType must be certain values',
            $data,
            "userType must be certain values failed"
        );
    }

    public function testRegisterUserValidationUserRiskclass()
    {
        $request = new RegisterUser($this->config);
        $request->setUserId(11)
            ->setUserType(\Upg\Library\User\Type::USER_TYPE_PRIVATE)
            ->setUserData($this->getUser())
            ->setBillingRecipient($this->faker->name)
            ->setBillingAddress($this->getAddress())
            ->setShippingRecipient($this->faker->name)
            ->setShippingAddress($this->getAddress())
            ->setLocale(Codes::LOCALE_EN);

        $validation = new Validation();

        /**
         * Test length validation
         */
        $request->setUserRiskClass($this->veryLongString);
        $validation->getValidator($request);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\RegisterUser',
            'userRiskClass',
            'userRiskClass must certain values or be empty',
            $data,
            "userRiskClass must certain values or be empty failed"
        );
    }

    public function testRegisterUserValidationBillingRecipient()
    {
        $request = new RegisterUser($this->config);
        $request->setUserId(11)
            ->setUserType(\Upg\Library\User\Type::USER_TYPE_PRIVATE)
            ->setUserRiskClass(RiskClass::RISK_CLASS_DEFAULT)
            ->setUserData($this->getUser())
            ->setBillingRecipient($this->veryLongString)
            ->setBillingAddress($this->getAddress())
            ->setShippingRecipient($this->faker->name)
            ->setShippingAddress($this->getAddress())
            ->setLocale(Codes::LOCALE_EN);

        $validation = new Validation();

        /**
         * Test length validation
         */
        $request->setUserRiskClass($this->veryLongString);
        $validation->getValidator($request);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\RegisterUser',
            'billingRecipient',
            'billingRecipient must be between 1 and 80 characters',
            $data,
            "billingRecipient must be between 1 and 80 characters failed"
        );
    }

    public function testRegisterUserValidationShippingRecipient()
    {
        $request = new RegisterUser($this->config);
        $request->setUserId(11)
            ->setUserType(\Upg\Library\User\Type::USER_TYPE_PRIVATE)
            ->setUserRiskClass(RiskClass::RISK_CLASS_DEFAULT)
            ->setUserData($this->getUser())
            ->setBillingRecipient($this->faker->name)
            ->setBillingAddress($this->getAddress())
            ->setShippingRecipient($this->veryLongString)
            ->setShippingAddress($this->getAddress())
            ->setLocale(Codes::LOCALE_EN);

        $validation = new Validation();

        /**
         * Test length validation
         */
        $request->setUserRiskClass($this->veryLongString);
        $validation->getValidator($request);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\RegisterUser',
            'shippingRecipient',
            'shippingRecipient must be between 1 and 80 characters',
            $data,
            "shippingRecipient must be between 1 and 80 characters failed"
        );
    }

    public function testRegisterUserValidationLocale()
    {
        $request = new RegisterUser($this->config);
        $request->setUserId(11)
            ->setUserType(\Upg\Library\User\Type::USER_TYPE_PRIVATE)
            ->setUserRiskClass(RiskClass::RISK_CLASS_DEFAULT)
            ->setUserData($this->getUser())
            ->setBillingRecipient($this->faker->name)
            ->setBillingAddress($this->getAddress())
            ->setShippingRecipient($this->faker->name)
            ->setShippingAddress($this->getAddress());

        /**
         * Test required
         */
        $validation = new Validation();
        $validation->getValidator($request);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\RegisterUser',
            'locale',
            'locale must be set for the request',
            $data,
            "locale must be set for the request failed"
        );

        /**
         * Test values validation
         */
        $request->setLocale($this->faker->name);

        $validation = new Validation();
        $validation->getValidator($request);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\RegisterUser',
            'locale',
            'locale must be certain values',
            $data,
            "locale must be certain values failed"
        );
    }
}
