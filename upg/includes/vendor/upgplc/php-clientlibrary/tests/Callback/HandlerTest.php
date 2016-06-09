<?php

namespace Upg\Library\Tests\Callback;

use Upg\Library\Callback\Handler;
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

    public function testSuccessfulCallBack()
    {
        $data = array(
            'notificationType' => 'PAYMENT_INSTRUMENT_SELECTION',
            'merchantID' => 10,
            'storeID' => 'store',
            'orderID' => 10,
            'paymentMethod' => 'CC',
            'resultCode' => 0,
            'merchantReference' => 'test',
            'additionalInformation' => '{"test":1}',
            'paymentInstrumentID' => 1,
            'message' => 'test',
            'salt' => "randomSalt",
            'mac' => "2429c36de46b23b9f5920de18be90e4a5a58439b"
        );

        $processor = new MockProcessor();

        $handler = new Handler($this->config, $data, $processor);
        $result = $handler->run();

        $expected = json_encode(array('url'=>'http://something.com/success'));

        $this->assertJsonStringEqualsJsonString($expected, $result);

        $data['additionalInformation'] = array('test'=>1);
        $data['paymentInstrumentsPageUrl'] = '';

        $this->assertArraySubset($processor->data, $data);
    }

    public function testSuccessfulCallBackWithOptionalParam()
    {
        $data = array(
            'notificationType' => 'PAYMENT_INSTRUMENT_SELECTION',
            'merchantID' => 10,
            'storeID' => 'store',
            'orderID' => 10,
            'paymentMethod' => 'CC',
            'resultCode' => 0,
            'merchantReference' => 'test',
            'additionalInformation' => '{"test":1}',
            'paymentInstrumentID' => 1,
            'paymentInstrumentsPageUrl' => "http://something.com",
            'message' => 'test',
            'salt' => "randomSalt",
            'mac' => "5ecb4686ffc2794a6568df85627b965fa773e0ab"
        );

        $processor = new MockProcessor();

        $handler = new Handler($this->config, $data, $processor);
        $result = $handler->run();

        $expected = json_encode(array('url'=>'http://something.com/success'));

        $this->assertJsonStringEqualsJsonString($expected, $result);

        $data['additionalInformation'] = array('test'=>1);

        $this->assertArraySubset($processor->data, $data);
    }

    /**
     * Test if ParamNotProvided exception is thrown
     * @expectedException Upg\Library\Callback\Exception\ParamNotProvided
     */
    public function testParamException()
    {
        $data = array(
            'storeID' => 'store',
            'orderID' => 10,
            'resultCode' => 0,
            'merchantReference' => 'test',
            'message' => 'test'
        );

        $processor = new MockProcessor();

        $handler = new Handler($this->config, $data, $processor);
    }

    /**
     * Test if validation exception is thrown
     * @expectedException Upg\Library\Callback\Exception\MacValidation
     */
    public function testMacValidationException()
    {
        $data = array(
            'notificationType' => 'PAYMENT_INSTRUMENT_SELECTION',
            'merchantID' => 10,
            'storeID' => 'store',
            'orderID' => 10,
            'paymentMethod' => 'CC',
            'resultCode' => 0,
            'merchantReference' => 'test',
            'additionalInformation' => '{test:1}',
            'paymentInstrumentID' => 1,
            'message' => 'test',
            'salt' => "randomSalt",
            'mac' => "TESTMAC"
        );

        $processor = new MockProcessor();

        $handler = new Handler($this->config, $data, $processor);
        $result = $handler->run();
    }
}
