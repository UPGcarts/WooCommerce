<?php

namespace Upg\Library\Tests\Api;

use Upg\Library\Api\MacCalculator;
use Upg\Library\Config;

class MacCalculatorTest extends \PHPUnit_Framework_TestCase
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

    public function testSuccessfulValidation()
    {
        //mac 4362f9c76dba97492a193615ce7902e2d60484c0
        $jsonString = '{"merchantID":123,
        "orderID":"testOrder",
        "storeID":"test Store"}';

        $header = "HTTP/1.1 200 OK
            \r\nDate: Wed, 18 Nov 2015 14:23:48 GMT
            \r\nServer: Jetty(8.1.15.v20140411)
            \r\nContent-Type: application/json;charset=UTF-8
            \r\nAccess-Control-Allow-Origin: *
            \r\nAccess-Control-Allow-Methods: POST
            \r\nAccess-Control-Expose-Headers: X-Payco-TOKEN, X-Payco-HMAC
            \r\nX-Payco-HMAC: 7c28ba55d1f20663a5348066f556ea672c4bcf11
            \r\nVia: 1.1 www.payco-sandbox.de
            \r\nConnection: close
            \r\nTransfer-Encoding: chunked";

        $macCalculator = new MacCalculator($this->config);
        $macCalculator->setResponse($jsonString, $header);

        $this->assertTrue($macCalculator->validateResponse(), "Validation failed");
    }

    public function testSuccessfulMultiLevelValidation()
    {
        //mac c8c360a36aeca25fff834d3a74988e15eac6ffc2
        $jsonString = '{
            "merchantID": 123,
            "orderID": "testOrder",
            "storeID": "test Store",
            "data": [1,2]
        }';

        $header = "HTTP/1.1 200 OK
            \r\nDate: Wed, 18 Nov 2015 14:23:48 GMT
            \r\nServer: Jetty(8.1.15.v20140411)
            \r\nContent-Type: application/json;charset=UTF-8
            \r\nAccess-Control-Allow-Origin: *
            \r\nAccess-Control-Allow-Methods: POST
            \r\nAccess-Control-Expose-Headers: X-Payco-TOKEN, X-Payco-HMAC
            \r\nX-Payco-HMAC: 472f53b8ae50c75157c007358cc926fde5bcb574
            \r\nVia: 1.1 www.payco-sandbox.de
            \r\nConnection: close
            \r\nTransfer-Encoding: chunked";

        $macCalculator = new MacCalculator($this->config);
        $macCalculator->setResponse($jsonString, $header);

        $this->assertTrue($macCalculator->validateResponse(), "Validation failed");
    }

    /**
     * @expectedException Upg\Library\Api\Exception\MacValidation
     */
    public function testFailedValidation()
    {
        //mac AbCDeFc76dba97492a193615ce7902e2d60484c0
        $jsonString = '{"merchantID":123,
        "orderID":"testOrder",
        "storeID":"test Store"}';

        $header = "HTTP/1.1 200 OK
            \r\nDate: Wed, 18 Nov 2015 14:23:48 GMT
            \r\nServer: Jetty(8.1.15.v20140411)
            \r\nContent-Type: application/json;charset=UTF-8
            \r\nAccess-Control-Allow-Origin: *
            \r\nAccess-Control-Allow-Methods: POST
            \r\nAccess-Control-Expose-Headers: X-Payco-TOKEN, X-Payco-HMAC
            \r\nX-Payco-HMAC: AbCDeFc76dba97492a193615ce7902e2d60484c0
            \r\nVia: 1.1 www.payco-sandbox.de
            \r\nConnection: close
            \r\nTransfer-Encoding: chunked";

        $macCalculator = new MacCalculator($this->config);
        $macCalculator->setResponse($jsonString, $header);

        $this->assertTrue($macCalculator->validateResponse(), "Validation failed");
    }

    /**
     * @expectedException Upg\Library\Api\Exception\JsonDecode
     */
    public function testJsonDecodeError()
    {
        $header = "HTTP/1.1 200 OK
            \r\nDate: Wed, 18 Nov 2015 14:23:48 GMT
            \r\nServer: Jetty(8.1.15.v20140411)
            \r\nContent-Type: application/json;charset=UTF-8
            \r\nAccess-Control-Allow-Origin: *
            \r\nAccess-Control-Allow-Methods: POST
            \r\nAccess-Control-Expose-Headers: X-Payco-TOKEN, X-Payco-HMAC
            \r\nX-Payco-HMAC: 4362f9c76dba97492a193615ce7902e2d60484c0
            \r\nVia: 1.1 www.payco-sandbox.de
            \r\nConnection: close
            \r\nTransfer-Encoding: chunked";

        $jsonString = '{"merchantID":123,
        "orderID":"testOrder",
        "storeID":"test Store}';

        $macCalculator = new MacCalculator($this->config);
        $macCalculator->setResponse($jsonString, $header);
    }
}
