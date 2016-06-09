<?php

namespace Upg\Library\Tests\Serializer;

use Upg\Library\Config;
use Upg\Library\Error\Codes;
use Upg\Library\Response\FailureResponse;

class FailureResponseTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Config object for tests
     * @var Config
     */
    private $config;

    public function setUp()
    {
        $this->config = new Config(array(
            'merchantPassword' => '8A!v#6qPc3?+G1on',
            'merchantID' => '123',
            'storeID' => 'test Store',
            'sendRequestsWithSalt' => true,
        ));
    }

    public function tearDown()
    {
        unset($this->config);
    }

    /**
     * Test that if a error status method will get set
     */
    public function testGetErrorStatusMessage()
    {
        $failureResponse = new FailureResponse(
            $this->config,
            array(
                'resultCode' => 2014,
            )
        );

        $expected = 'The user already exist.';

        $this->assertEquals($expected, $failureResponse->getErrorStatusMessage());
    }
}
