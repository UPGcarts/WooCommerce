<?php

namespace Upg\Library\Tests\Request;

use Faker\Factory;
use Upg\Library\Config;
use Upg\Library\Locale\Codes;
use Upg\Library\Request\CreateTransaction;
use Upg\Library\Request\Objects\Address;
use Upg\Library\Request\Objects\Amount;
use Upg\Library\Request\Objects\BasketItem;
use Upg\Library\Request\Objects\Person;
use Upg\Library\Risk\RiskClass;
use Upg\Library\Validation\Validation;

class CreateTransactionTest extends AbstractRequestTest
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

    private function getAmount()
    {
        return new Amount(100, 0, 0);
    }

    private function getBasketItem()
    {
        $item = new BasketItem();
        $item->setBasketItemText("Test Item")
            ->setBasketItemCount(1)
            ->setBasketItemAmount($this->getAmount());

        return $item;
    }


    public function testCreateTransactionValidationSuccess()
    {
        $request = new CreateTransaction($this->config);
        $request->setOrderID(1)
            ->setUserID(1)
            ->setIntegrationType(CreateTransaction::INTEGRATION_TYPE_HOSTED_AFTER)
            ->setAutoCapture(true)
            ->setContext(CreateTransaction::CONTEXT_ONLINE)
            ->setMerchantReference("TEST")
            ->setUserType(CreateTransaction::USER_TYPE_PRIVATE)
            ->setUserRiskClass(RiskClass::RISK_CLASS_DEFAULT)
            ->setUserIpAddress("192.168.1.2")
            ->setUserData($this->getUser())
            ->setBillingAddress($this->getAddress())
            ->setAmount($this->getAmount())
            ->addBasketItem($this->getBasketItem())
            ->setLocale(Codes::LOCALE_EN);

        $validation = new Validation();

        $validation->getValidator($request);
        $data = $validation->performValidation();

        $this->assertEmpty($data, "Validation found an issue when there should be none");
    }

    public function testCreateTransactionValidationUserID()
    {
        $validation = new Validation();
        /**
         * Test required
         */

        $request = new CreateTransaction($this->config);
        $request->setOrderID(1)
            ->setIntegrationType(CreateTransaction::INTEGRATION_TYPE_HOSTED_AFTER)
            ->setAutoCapture(true)
            ->setContext(CreateTransaction::CONTEXT_ONLINE)
            ->setMerchantReference("TEST")
            ->setUserType(CreateTransaction::USER_TYPE_PRIVATE)
            ->setUserRiskClass(RiskClass::RISK_CLASS_DEFAULT)
            ->setUserIpAddress("192.168.1.2")
            ->setUserData($this->getUser())
            ->setBillingAddress($this->getAddress())
            ->setAmount($this->getAmount())
            ->addBasketItem($this->getBasketItem());

        $validation->getValidator($request);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\CreateTransaction',
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
            'Upg\\Library\\Request\\CreateTransaction',
            'userID',
            'userID must be between 1 and 50 characters',
            $data,
            "userID must be between 1 and 50 characters failed"
        );
    }

    public function testCreateTransactionValidationIntegrationType()
    {
        $request = new CreateTransaction($this->config);
        $request->setOrderID(1)
            ->setUserID(1)
            ->setIntegrationType('blah')
            ->setAutoCapture(true)
            ->setContext(CreateTransaction::CONTEXT_ONLINE)
            ->setMerchantReference("TEST")
            ->setUserType(CreateTransaction::USER_TYPE_PRIVATE)
            ->setUserRiskClass(RiskClass::RISK_CLASS_DEFAULT)
            ->setUserIpAddress("192.168.1.2")
            ->setUserData($this->getUser())
            ->setBillingAddress($this->getAddress())
            ->setAmount($this->getAmount())
            ->addBasketItem($this->getBasketItem());

        $validation = new Validation();

        $validation->getValidator($request);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\CreateTransaction',
            'integrationType',
            'integrationType must be certain values',
            $data,
            "integrationType must be certain values failed"
        );
    }

    public function testCreateTransactionValidationMerchantReference()
    {
        $request = new CreateTransaction($this->config);
        $request->setOrderID(1)
            ->setUserID(1)
            ->setIntegrationType(CreateTransaction::INTEGRATION_TYPE_HOSTED_AFTER)
            ->setAutoCapture(true)
            ->setContext(CreateTransaction::CONTEXT_ONLINE)
            ->setMerchantReference($this->veryLongString)
            ->setUserType(CreateTransaction::USER_TYPE_PRIVATE)
            ->setUserRiskClass(RiskClass::RISK_CLASS_DEFAULT)
            ->setUserIpAddress("192.168.1.2")
            ->setUserData($this->getUser())
            ->setBillingAddress($this->getAddress())
            ->setAmount($this->getAmount())
            ->addBasketItem($this->getBasketItem());
        $validation = new Validation();

        $validation->getValidator($request);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\CreateTransaction',
            'merchantReference',
            'merchantReference must be between 1 and 255 characters',
            $data,
            "merchantReference must be between 1 and 255 characters failed"
        );
    }

    public function testCreateTransactionValidationContext()
    {
        $validation = new Validation();

        $request = new CreateTransaction($this->config);
        $request->setOrderID(1)
            ->setUserID(1)
            ->setIntegrationType(CreateTransaction::INTEGRATION_TYPE_HOSTED_AFTER)
            ->setAutoCapture(true)
            ->setContext(null)
            ->setMerchantReference("TEST FIELD")
            ->setUserType(CreateTransaction::USER_TYPE_PRIVATE)
            ->setUserRiskClass(RiskClass::RISK_CLASS_DEFAULT)
            ->setUserIpAddress("192.168.1.2")
            ->setUserData($this->getUser())
            ->setBillingAddress($this->getAddress())
            ->setAmount($this->getAmount())
            ->addBasketItem($this->getBasketItem());

        /**
         * Test the required
         */
        $validation->getValidator($request);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\CreateTransaction',
            'context',
            'context is required',
            $data,
            "context is required failed"
        );

        /**
         * Test fixed field validation
         */
        $request->setContext($this->faker->name);
        $validation->getValidator($request);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\CreateTransaction',
            'context',
            'context must be certain values',
            $data,
            "context must be certain values failed"
        );

    }

    public function testCreateTransactionValidationUserType()
    {
        $validation = new Validation();

        $request = new CreateTransaction($this->config);
        $request->setOrderID(1)
            ->setUserID(1)
            ->setIntegrationType(CreateTransaction::INTEGRATION_TYPE_HOSTED_AFTER)
            ->setAutoCapture(true)
            ->setContext(CreateTransaction::CONTEXT_ONLINE)
            ->setMerchantReference("TEST FIELD")
            ->setUserType(null)
            ->setUserRiskClass(RiskClass::RISK_CLASS_DEFAULT)
            ->setUserIpAddress("192.168.1.2")
            ->setUserData($this->getUser())
            ->setBillingAddress($this->getAddress())
            ->setAmount($this->getAmount())
            ->addBasketItem($this->getBasketItem());

        $validation->getValidator($request);
        $data = $validation->performValidation();

        /**
         * Test required
         */
        $this->assertValidationReturned(
            'Upg\\Library\\Request\\CreateTransaction',
            'userType',
            'userType must be certain values',
            $data,
            "userType must be certain values failed"
        );

        /**
         * Test certain values validation
         */
        $request->setUserType($this->faker->name);

        $validation->getValidator($request);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\CreateTransaction',
            'userType',
            'userType must be certain values',
            $data,
            "userType must be certain values failed"
        );

    }

    public function testCreateTransactionValidationUserRiskClass()
    {
        $validation = new Validation();

        $request = new CreateTransaction($this->config);
        $request->setOrderID(1)
            ->setUserID(1)
            ->setIntegrationType(CreateTransaction::INTEGRATION_TYPE_HOSTED_AFTER)
            ->setAutoCapture(true)
            ->setContext(CreateTransaction::CONTEXT_ONLINE)
            ->setMerchantReference("TEST FIELD")
            ->setUserType(CreateTransaction::USER_TYPE_PRIVATE)
            ->setUserRiskClass($this->faker->name)
            ->setUserIpAddress("192.168.1.2")
            ->setUserData($this->getUser())
            ->setBillingAddress($this->getAddress())
            ->setAmount($this->getAmount())
            ->addBasketItem($this->getBasketItem());


        $request->setUserType($this->faker->name);

        $validation->getValidator($request);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\CreateTransaction',
            'userRiskClass',
            'userRiskClass must contain certain values or be empty',
            $data,
            "userRiskClass must certain values or be empty failed"
        );

    }

    public function testCreateTransactionValidationUserIpAddress()
    {
        $validation = new Validation();

        $request = new CreateTransaction($this->config);
        $request->setOrderID(1)
            ->setUserID(1)
            ->setIntegrationType(CreateTransaction::INTEGRATION_TYPE_HOSTED_AFTER)
            ->setAutoCapture(true)
            ->setContext(CreateTransaction::CONTEXT_ONLINE)
            ->setMerchantReference("TEST FIELD")
            ->setUserType(CreateTransaction::USER_TYPE_PRIVATE)
            ->setUserRiskClass(RiskClass::RISK_CLASS_DEFAULT)
            ->setUserIpAddress($this->veryLongString)
            ->setUserData($this->getUser())
            ->setBillingAddress($this->getAddress())
            ->setAmount($this->getAmount())
            ->addBasketItem($this->getBasketItem());

        $validation->getValidator($request);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\CreateTransaction',
            'userIpAddress',
            'userIpAddress must be between 1 and 15 characters',
            $data,
            "userIpAddress must be between 1 and 15 characters failed"
        );
    }

    public function testCreateTransactionValidationBillingRecipient()
    {
        $validation = new Validation();

        $request = new CreateTransaction($this->config);
        $request->setOrderID(1)
            ->setUserID(1)
            ->setIntegrationType(CreateTransaction::INTEGRATION_TYPE_HOSTED_AFTER)
            ->setAutoCapture(true)
            ->setContext(CreateTransaction::CONTEXT_ONLINE)
            ->setMerchantReference("TEST FIELD")
            ->setUserType(CreateTransaction::USER_TYPE_PRIVATE)
            ->setUserRiskClass(RiskClass::RISK_CLASS_DEFAULT)
            ->setUserIpAddress('192.168.0.1')
            ->setUserData($this->getUser())
            ->setBillingAddress($this->getAddress())
            ->setAmount($this->getAmount())
            ->addBasketItem($this->getBasketItem())
            ->setBillingRecipient($this->veryLongString);

        $validation->getValidator($request);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\CreateTransaction',
            'billingRecipient',
            'billingRecipient must be between 1 and 80 characters',
            $data,
            "billingRecipient must be between 1 and 80 characters failed"
        );
    }

    public function testCreateTransactionValidationBillingRecipientAddition()
    {
        $validation = new Validation();

        $request = new CreateTransaction($this->config);
        $request->setOrderID(1)
            ->setUserID(1)
            ->setIntegrationType(CreateTransaction::INTEGRATION_TYPE_HOSTED_AFTER)
            ->setAutoCapture(true)
            ->setContext(CreateTransaction::CONTEXT_ONLINE)
            ->setMerchantReference("TEST FIELD")
            ->setUserType(CreateTransaction::USER_TYPE_PRIVATE)
            ->setUserRiskClass(RiskClass::RISK_CLASS_DEFAULT)
            ->setUserIpAddress('192.168.0.1')
            ->setUserData($this->getUser())
            ->setBillingAddress($this->getAddress())
            ->setAmount($this->getAmount())
            ->addBasketItem($this->getBasketItem())
            ->setBillingRecipientAddition($this->veryLongString);

        $validation->getValidator($request);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\CreateTransaction',
            'billingRecipientAddition',
            'billingRecipientAddition must be between 1 and 80 characters',
            $data,
            "billingRecipientAddition must be between 1 and 80 characters failed"
        );
    }

    public function testCreateTransactionValidationShippingRecipient()
    {
        $validation = new Validation();

        $request = new CreateTransaction($this->config);
        $request->setOrderID(1)
            ->setUserID(1)
            ->setIntegrationType(CreateTransaction::INTEGRATION_TYPE_HOSTED_AFTER)
            ->setAutoCapture(true)
            ->setContext(CreateTransaction::CONTEXT_ONLINE)
            ->setMerchantReference("TEST FIELD")
            ->setUserType(CreateTransaction::USER_TYPE_PRIVATE)
            ->setUserRiskClass(RiskClass::RISK_CLASS_DEFAULT)
            ->setUserIpAddress('192.168.0.1')
            ->setUserData($this->getUser())
            ->setBillingAddress($this->getAddress())
            ->setAmount($this->getAmount())
            ->addBasketItem($this->getBasketItem())
            ->setShippingRecipient($this->veryLongString);

        $validation->getValidator($request);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\CreateTransaction',
            'shippingRecipient',
            'shippingRecipient must be between 1 and 80 characters',
            $data,
            "shippingRecipient must be between 1 and 80 character failed"
        );
    }

    public function testCreateTransactionValidationShippingRecipientAddition()
    {
        $validation = new Validation();

        $request = new CreateTransaction($this->config);
        $request->setOrderID(1)
            ->setUserID(1)
            ->setIntegrationType(CreateTransaction::INTEGRATION_TYPE_HOSTED_AFTER)
            ->setAutoCapture(true)
            ->setContext(CreateTransaction::CONTEXT_ONLINE)
            ->setMerchantReference("TEST FIELD")
            ->setUserType(CreateTransaction::USER_TYPE_PRIVATE)
            ->setUserRiskClass(RiskClass::RISK_CLASS_DEFAULT)
            ->setUserIpAddress('192.168.0.1')
            ->setUserData($this->getUser())
            ->setBillingAddress($this->getAddress())
            ->setAmount($this->getAmount())
            ->addBasketItem($this->getBasketItem())
            ->setShippingRecipientAddition($this->veryLongString);

        $validation->getValidator($request);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\CreateTransaction',
            'shippingRecipientAddition',
            'shippingRecipientAddition must be between 1 and 80 characters',
            $data,
            "shippingRecipientAddition must be between 1 and 80 characters failed"
        );
    }

    public function testCreateTransactionValidationAmount()
    {
        $validation = new Validation();

        $request = new CreateTransaction($this->config);
        $request->setOrderID(1)
            ->setUserID(1)
            ->setIntegrationType(CreateTransaction::INTEGRATION_TYPE_HOSTED_AFTER)
            ->setAutoCapture(true)
            ->setContext(CreateTransaction::CONTEXT_ONLINE)
            ->setMerchantReference("TEST FIELD")
            ->setUserType(CreateTransaction::USER_TYPE_PRIVATE)
            ->setUserRiskClass(RiskClass::RISK_CLASS_DEFAULT)
            ->setUserIpAddress('192.168.0.1')
            ->setUserData($this->getUser())
            ->setBillingAddress($this->getAddress())
            ->addBasketItem($this->getBasketItem());

        $validation->getValidator($request);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\CreateTransaction',
            'amount',
            'amount must be set for the transaction',
            $data,
            "amount must be set for the transaction failed"
        );
    }

    public function testCreateTransactionValidationBasketItems()
    {
        $validation = new Validation();

        $request = new CreateTransaction($this->config);
        $request->setOrderID(1)
            ->setUserID(1)
            ->setIntegrationType(CreateTransaction::INTEGRATION_TYPE_HOSTED_AFTER)
            ->setAutoCapture(true)
            ->setContext(CreateTransaction::CONTEXT_ONLINE)
            ->setMerchantReference("TEST FIELD")
            ->setUserType(CreateTransaction::USER_TYPE_PRIVATE)
            ->setUserRiskClass(RiskClass::RISK_CLASS_DEFAULT)
            ->setUserIpAddress('192.168.0.1')
            ->setUserData($this->getUser())
            ->setBillingAddress($this->getAddress())
            ->setAmount($this->getAmount());

        $validation->getValidator($request);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\CreateTransaction',
            'basketItems',
            'basketItems must be added to the transaction',
            $data,
            "basketItems must be added to the transaction failed"
        );
    }

    public function testCreateTransactionValidationBasketlocale()
    {
        $validation = new Validation();

        $request = new CreateTransaction($this->config);
        $request->setOrderID(1)
            ->setUserID(1)
            ->setIntegrationType(CreateTransaction::INTEGRATION_TYPE_HOSTED_AFTER)
            ->setAutoCapture(true)
            ->setContext(CreateTransaction::CONTEXT_ONLINE)
            ->setMerchantReference("TEST")
            ->setUserType(CreateTransaction::USER_TYPE_PRIVATE)
            ->setUserRiskClass(RiskClass::RISK_CLASS_DEFAULT)
            ->setUserIpAddress("192.168.1.2")
            ->setUserData($this->getUser())
            ->setBillingAddress($this->getAddress())
            ->setAmount($this->getAmount())
            ->addBasketItem($this->getBasketItem());

        /**
         * Test required
         */
        $validation->getValidator($request);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\CreateTransaction',
            'locale',
            'locale must be set for the transaction',
            $data,
            "locale must be set for the transaction failed"
        );

        /**
         * Test set values validation
         */
        $request->setLocale($this->faker->name);
        $validation->getValidator($request);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\CreateTransaction',
            'locale',
            'locale must be certain values',
            $data,
            "locale must be certain values failed"
        );
    }

    public function testCreateTransactionSaltGeneration()
    {
        $request = new CreateTransaction($this->config);
        $request->setOrderID(1)
            ->setUserID(1)
            ->setIntegrationType(CreateTransaction::INTEGRATION_TYPE_HOSTED_AFTER)
            ->setAutoCapture(true)
            ->setContext(CreateTransaction::CONTEXT_ONLINE)
            ->setMerchantReference("TEST")
            ->setUserType(CreateTransaction::USER_TYPE_PRIVATE)
            ->setUserRiskClass(RiskClass::RISK_CLASS_DEFAULT)
            ->setUserIpAddress("192.168.1.2")
            ->setUserData($this->getUser())
            ->setBillingAddress($this->getAddress())
            ->setAmount($this->getAmount())
            ->addBasketItem($this->getBasketItem())
            ->setLocale(Codes::LOCALE_EN);

        $data = $request->getSerializerData();
        $this->assertArrayHasKey("salt", $data, "Salt was not set");
    }
}
