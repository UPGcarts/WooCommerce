<?php

namespace Upg\Library\Tests\Request;

use Faker\Factory;
use Upg\Library\Config;
use Upg\Library\Request\Objects\Amount;
use Upg\Library\Request\Refund;
use Upg\Library\Validation\Validation;

class RefundTest extends AbstractRequestTest
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

    private function getAmount()
    {
        return new Amount(100, 0, 0);
    }

    public function testRefundValidationSuccess()
    {
        $request = new Refund($this->config);
        $request->setOrderID(1)
            ->setCaptureID(1)
            ->setAmount($this->getAmount())
            ->setRefundDescription($this->faker->sentence(1));

        $validation = new Validation();

        $validation->getValidator($request);
        $data = $validation->performValidation();

        $this->assertEmpty($data, "Validation found an issue when there should be none");
    }

    public function testRefundValidationOrderID()
    {
        $request = new Refund($this->config);
        $request->setCaptureID(1)
            ->setAmount($this->getAmount())
            ->setRefundDescription($this->faker->sentence(1));

        $validation = new Validation();

        $validation->getValidator($request);
        $data = $validation->performValidation();

        /**
         * Test the orderId required validation
         */
        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Refund',
            'orderID',
            'orderID is required',
            $data,
            "orderID is required failed to trigger"
        );

        /**
         * Test length
         */
        $request->setOrderID($this->veryLongString);
        $validation->getValidator($request);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Refund',
            'orderID',
            'orderID must be between 1 and 30 characters',
            $data,
            "orderID must be between 1 and 30 characters failed to trigger"
        );
    }

    public function testRefundValidationCaptureID()
    {
        $request = new Refund($this->config);
        $request->setOrderID(1)
            ->setAmount($this->getAmount())
            ->setRefundDescription($this->faker->sentence(1));

        $validation = new Validation();

        $validation->getValidator($request);
        $data = $validation->performValidation();

        /**
         * Test the captureID required validation
         */
        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Refund',
            'captureID',
            'captureID is required',
            $data,
            "captureID is required failed to trigger"
        );

        /**
         * Test length
         */
        $request->setCaptureID($this->veryLongString);
        $validation->getValidator($request);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Refund',
            'captureID',
            'captureID must be between 1 and 30 characters',
            $data,
            "captureID must be between 1 and 30 characters failed to trigger"
        );
    }

    public function testRefundValidationAmount()
    {
        $request = new Refund($this->config);
        $request->setOrderID(1)
            ->setCaptureID(1)
            ->setRefundDescription($this->faker->sentence(1));

        $validation = new Validation();

        $validation->getValidator($request);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Refund',
            'amount',
            'amount is required',
            $data,
            "amount is required failed to trigger"
        );
    }

    public function testRefundValidationRefundDescription()
    {
        $request = new Refund($this->config);
        $request->setOrderID(1)
            ->setCaptureID(1)
            ->setAmount($this->getAmount());

        $validation = new Validation();

        $validation->getValidator($request);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Refund',
            'refundDescription',
            'refundDescription is required',
            $data,
            "refundDescription is required failed to trigger"
        );

        /**
         * Length test
         */
        $request->setRefundDescription($this->veryLongString);
        $validation->getValidator($request);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\Refund',
            'refundDescription',
            'refundDescription must be between 1 and 256 characters',
            $data,
            "refundDescription must be between 1 and 256 characters failed to trigger"
        );
    }
}
