<?php

namespace Upg\Library\Tests\Request;

use Faker\Factory;
use Upg\Library\Config;
use Upg\Library\Request\Objects\PaymentInstrument;
use Upg\Library\Request\RegisterUserPaymentInstrument;
use Upg\Library\Validation\Validation;

class RegisterUserPaymentInstrumentTest extends AbstractRequestTest
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

    private function getPaymentInstrument()
    {
        $payment = new PaymentInstrument();
        $payment->setPaymentInstrumentType(PaymentInstrument::PAYMENT_INSTRUMENT_TYPE_CARD)
            ->setAccountHolder($this->faker->name)
            ->setNumber($this->faker->creditCardNumber)
            ->setIssuer(PaymentInstrument::ISSUER_MC)
            ->setValidity(new \DateTime('now'));
        return $payment;
    }

    public function testRegisterUserPaymentInstrumentValidationSuccess()
    {
        $request = new RegisterUserPaymentInstrument($this->config);
        $request->setUserID(1)->setPaymentInstrument($this->getPaymentInstrument());

        $validation = new Validation();
        $validation->getValidator($request);
        $data = $validation->performValidation();

        $this->assertEmpty($data, "Validation found an issue when there should be none");
    }

    public function testRegisterUserPaymentInstrumentValidationUserID()
    {
        $request = new RegisterUserPaymentInstrument($this->config);
        $request->setPaymentInstrument($this->getPaymentInstrument());

        $validation = new Validation();
        $validation->getValidator($request);
        $data = $validation->performValidation();

        /**
         * Test required
         */
        $this->assertValidationReturned(
            'Upg\\Library\\Request\\RegisterUserPaymentInstrument',
            'userID',
            'userID is required',
            $data,
            "userID is required validation failed"
        );

        /**
         * Test length
         */
        $request->setUserID($this->veryLongString);
        $validation->getValidator($request);
        $data = $validation->performValidation();
        $this->assertValidationReturned(
            'Upg\\Library\\Request\\RegisterUserPaymentInstrument',
            'userID',
            'userID must be between 1 and 50 characters',
            $data,
            "userID must be between 1 and 50 characters failed"
        );
    }

    public function testRegisterUserPaymentInstrumentValidationUserPaymentInstrument()
    {
        $request = new RegisterUserPaymentInstrument($this->config);
        $request->setUserID(1);

        $validation = new Validation();
        $validation->getValidator($request);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\RegisterUserPaymentInstrument',
            'paymentInstrument',
            'paymentInstrument is required',
            $data,
            "paymentInstrument is required failed"
        );
    }
}
