<?php

namespace Upg\Library\Tests\Request;

use Faker\Factory;
use Upg\Library\Config;
use Upg\Library\Locale\Codes;
use Upg\Library\PaymentMethods\Methods;
use Upg\Library\Request\Reserve;
use Upg\Library\Validation\Validation;

class ReserveTest extends AbstractRequestTest
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

    public function testReserveValidationSuccess()
    {
        $request = new Reserve($this->config);
        $request->setOrderID(12)
            ->setPaymentMethod(Methods::PAYMENT_METHOD_TYPE_CC)
            ->setPaymentInstrumentID(20)
            ->setCcv(111);

        $validation = new Validation();

        $validation->getValidator($request);
        $data = $validation->performValidation();

        $this->assertEmpty($data, "Validation found an issue when there should be none");
    }

    public function testReserveValidationOrderID()
    {
        $request = new Reserve($this->config);
        $request->setPaymentMethod(Methods::PAYMENT_METHOD_TYPE_CC)
            ->setPaymentInstrumentID(20)
            ->setCcv(111);

        $validation = new Validation();

        $validation->getValidator($request);
        $data = $validation->performValidation();

        /**
         * Test the orderId required validation
         */
        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Reserve',
            'orderID',
            'orderID is required',
            $data,
            "orderID is required failed"
        );

        /**
         * Test the length validation
         */
        $request->setOrderID($this->veryLongString);
        $validation->getValidator($request);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Reserve',
            'orderID',
            'orderID must be between 1 and 30 characters',
            $data,
            "orderID must be between 1 and 30 characters failed"
        );
    }

    public function testReserveValidationPaymentMethod()
    {
        $request = new Reserve($this->config);
        $request->setOrderID(12)
            ->setPaymentInstrumentID(20)
            ->setCcv(111);

        $validation = new Validation();

        $validation->getValidator($request);
        $data = $validation->performValidation();

        /**
         * Test required
         */
        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Reserve',
            'paymentMethod',
            'paymentMethod is required',
            $data,
            "paymentMethod is required validation failed to trigger"
        );

        /**
         * Test value
         */
        $request->setPaymentMethod("foo");

        $validation->getValidator($request);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Reserve',
            'paymentMethod',
            'paymentMethod must be certain values',
            $data,
            "paymentMethod must be certain values validation failed to trigger"
        );
    }

    public function testReserveValidationCvv()
    {
        $request = new Reserve($this->config);
        $request->setOrderID(12)
            ->setPaymentMethod(Methods::PAYMENT_METHOD_TYPE_CC)
            ->setPaymentInstrumentID(20)
            ->setCcv(11111);

        $validation = new Validation();

        $validation->getValidator($request);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Reserve',
            'cvv',
            'cvv must be between 1 and 4 characters',
            $data,
            "cvv must be between 1 and 4 characters failed validation to trigger"
        );
    }
}
