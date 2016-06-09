<?php

namespace Upg\Library\Tests\Request;


use Upg\Library\Config;
use Upg\Library\Mac\Exception\MacInvalid;
use Upg\Library\Request\MacCalculator;
use Upg\Library\Tests\Mock\Request\JsonRequestWithArray;
use Upg\Library\Tests\Mock\Request\TopLevelRequest;

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
            'sendRequestsWithSalt' => false,
        ));
    }

    public function tearDown()
    {
        unset($this->config);
    }

    /**
     * Test the mac calculator with non recursive request
     */
    public function testCalculationNonRecursive()
    {
        $request = new TopLevelRequest($this->config);

        $request->data = array('orderID' => 'testOrder');

        $macCalculator = new MacCalculator();
        $macCalculator->setConfig($this->config);
        $macCalculator->setRequest($request);

        $result = $macCalculator->calculateMac();

        $this->assertEquals('4362f9c76dba97492a193615ce7902e2d60484c0', $result, "Mac address is not what is expected");
    }

    /**
     * Test the mac calculator on a recursive serializer
     */
    public function testCalculationRecursive()
    {
        $request = new TopLevelRequest($this->config);

        $request->data['recusiveValue'] = new JsonRequestWithArray();

        $macCalculator = new MacCalculator();
        $macCalculator->setConfig($this->config);
        $macCalculator->setRequest($request);

        $result = $macCalculator->calculateMac();

        $this->assertEquals('572a56dc83b1857c721068b52307b20668681432', $result, "Mac address is not what is expected");
    }

    /**
     * Test the validate method with sucess
     */
    public function testValidateSuccess()
    {
        $request = new TopLevelRequest($this->config);

        $request->data = array('orderID' => 'testOrder');

        $macCalculator = new MacCalculator();
        $macCalculator->setConfig($this->config);
        $macCalculator->setRequest($request);

        $this->assertTrue($macCalculator->validate('4362f9c76dba97492a193615ce7902e2d60484c0'));
    }

    /**
     * Test validation failure which throws exception
     * @expectedException \Upg\Library\Mac\Exception\MacInvalid
     */
    public function testValidateFailure()
    {
        $request = new TopLevelRequest($this->config);

        $macCalculator = new MacCalculator();
        $macCalculator->setConfig($this->config);
        $macCalculator->setRequest($request);

        $macCalculator->validate('f36343aa3af500dce48464b931111cb861101753');
    }

    /**
     * Test validation failure which returns a false
     */
    public function testValidateFailureBool()
    {
        $request = new TopLevelRequest($this->config);

        $macCalculator = new MacCalculator();
        $macCalculator->setConfig($this->config);
        $macCalculator->setRequest($request);

        $this->assertFalse(
            $macCalculator->validate('f36343aa3af500dce48464b931111cb861101753', false),
            "Validation returned true"
        );
    }

}