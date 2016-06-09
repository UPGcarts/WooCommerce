<?php

namespace Upg\Library\Tests\Serializer;

use Faker\Factory;
use Upg\Library\Config;
use Upg\Library\Response\SuccessResponse;

class SuccessResponseTest extends \PHPUnit_Framework_TestCase
{
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
     * Test basic instantiation
     * with a config object and test the getData and magic method works
     */
    public function testBasicInstantiation()
    {
        $successResponse = new SuccessResponse($this->config);
        $this->assertEquals($this->config, $successResponse->getData('config'));
        $this->assertEquals($this->config, $successResponse->getConfig());

        //ok now check if the property was set with reflection
        $class = new \ReflectionClass($successResponse);
        /**
         * @var \ReflectionProperty $property
         */
        $property = $class->getProperty('config');
        $property->setAccessible(true);
        $this->assertEquals($this->config, $property->getValue($successResponse));
    }

    /**
     * Test if fixed values and extended options will be populated
     * in the appriate places;
     */
    public function testExtendedInstantiation()
    {
        $testMac = $this->faker->sentence(1);
        $testValue = $this->faker->name;
        $extendedVar = new \stdClass();
        $extendedVar->name = $this->faker->name;
        $extendedVar->userAgent = $this->faker->userAgent;

        $testArray = array(
            'url' => $this->faker->url,
            'tld' => $this->faker->tld
        );


        $successResponse = new SuccessResponse(
            $this->config,
            array(
                'mac' => $testMac,
                'value' => $testValue,
                'extendedVar' => $extendedVar,
                'testArray' => $testArray
            )
        );

        /**
         * Test method on config
         */
        $this->assertEquals($this->config, $successResponse->getData('config'));
        $this->assertEquals($this->config, $successResponse->getConfig());

        /**
         * Test method on mac
         */
        $this->assertEquals($testMac, $successResponse->getData('mac'));
        $this->assertEquals($testMac, $successResponse->getMac());

        /**
         * Test method on extended var
         */
        $this->assertEquals($extendedVar, $successResponse->getData('extendedVar'));
        $this->assertEquals($extendedVar, $successResponse->getExtendedVar());

        /**
         * ok now check if the that the extended data is in the responseData array
         * @see \Upg\Library\Response::responseData
         **/
        $extendedData = $successResponse->getAllData(true);

        $expectedExtendedData = array(
            'value' => $testValue,
            'extendedVar' => $extendedVar,
            'testArray' => $testArray
        );

        $this->assertArrayHasKey('value', $extendedData);
        $this->assertArrayHasKey('extendedVar', $extendedData);
        $this->assertArrayHasKey('testArray', $extendedData);
        $this->assertArrayNotHasKey('mac', $extendedData);
        $this->assertArrayNotHasKey('config', $extendedData);

        $this->assertArraySubset($expectedExtendedData, $extendedData, true);
    }
}
