<?php

namespace Upg\Library\Tests\Mns;

use Upg\Library\Mns\Handler;
use Upg\Library\Config;
use Upg\Library\Tests\Mock\Callback\MockProcessor;

class HandlerTest extends \PHPUnit_Framework_TestCase
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
            'baseUrl' => "http://www.something.com/"
        ));
    }

    public function tearDown()
    {
        unset($this->config);
    }

    public function testSuccessfulProcess()
    {
        $data = array(
            'merchantID' => 1,
            'storeID' => 2,
            'orderID' => 3,
            'captureID' => 4,
            'merchantReference' => 5,
            'paymentReference' => 6,
            'userID' => 7,
            'amount' => 9,
            'currency' => 10,
            'transactionStatus' => 12,
            'orderStatus' => 13,
            'additionalData' => 15,
            'timestamp' => 16,
            'version' => 1.5,
            'mac' => 'fbdc46ef7ab1ccf195781983caf60782a81bd0f1',
        );

        $processor = new \Upg\Library\Tests\Mock\Mns\MockProcessor();

        $handler = new Handler($this->config, $data, $processor);
        $handler->run();

        $this->assertArraySubset($processor->data, $data);
    }

    /**
     * Test if ParamNotProvided exception is thrown
     * @expectedException Upg\Library\Mns\Exception\ParamNotProvided
     */
    public function testParamNotProvidedException()
    {
        $data = array(
            'merchantID' => 1,
            'storeID' => 2,
            'captureID' => 4,
            'merchantReference' => 5,
            'paymentReference' => 6,
            'userID' => 7,
            'amount' => 9,
            'currency' => 10,
            'transactionStatus' => 12,
            'orderStatus' => 13,
            'additionalData' => 15,
            'timestamp' => 16,
            'version' => 1.5,
            'mac' => 'fbdc46ef7ab1ccf195781983caf60782a81bd0f1',
        );

        $processor = new \Upg\Library\Tests\Mock\Mns\MockProcessor();
        $handler = new Handler($this->config, $data, $processor);
    }

    /**
     * Test if the mac validation exception is thrown
     * @expectedException Upg\Library\Callback\Exception\MacValidation
     */
    public function testMacValidationException()
    {
        $data = array(
            'merchantID' => 1,
            'storeID' => 2,
            'orderID' => 3,
            'captureID' => 4,
            'merchantReference' => 5,
            'paymentReference' => 6,
            'userID' => 7,
            'amount' => 9,
            'currency' => 10,
            'transactionStatus' => 12,
            'orderStatus' => 13,
            'additionalData' => 15,
            'timestamp' => 16,
            'version' => 1.5,
            'mac' => 'fbdc46ef7ab1ccf195781983caf60782a81bb1e2',
        );

        $processor = new \Upg\Library\Tests\Mock\Mns\MockProcessor();

        $handler = new Handler($this->config, $data, $processor);
    }
}
