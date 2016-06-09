<?php

namespace Upg\Library\Tests\Request;

use Faker\Factory;
use Upg\Library\Config;
use Upg\Library\Request\GetUserPaymentInstrument;
use Upg\Library\Validation\Validation;

class GetUserPaymentInstrumentTest extends AbstractRequestTest
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

    public function testGetUserPaymentInstrumentValidationSuccess()
    {
        $request = new GetUserPaymentInstrument($this->config);
        $request->setUserID(1);

        $validation = new Validation();

        $validation->getValidator($request);
        $data = $validation->performValidation();

        $this->assertEmpty($data, "Validation found an issue when there should be none");
    }

    public function testGetUserPaymentInstrumentValidationUserID()
    {
        $request = new GetUserPaymentInstrument($this->config);
        $validation = new Validation();

        $validation->getValidator($request);
        $data = $validation->performValidation();

        /**
         *  Test required validation
         */
        $this->assertValidationReturned(
            'Upg\\Library\\Request\\GetUserPaymentInstrument',
            'userID',
            'userID is required',
            $data,
            "userID is required validation failed"
        );

        $request->setUserID($this->veryLongString);

        /**
         *  Test length
         */
        $validation->getValidator($request);
        $data = $validation->performValidation();

        $this->assertValidationReturned(
            'Upg\\Library\\Request\\GetUserPaymentInstrument',
            'userID',
            'userID must be between 1 and 50 characters',
            $data,
            "userID must be between 1 and 50 characters failed"
        );
    }
}
