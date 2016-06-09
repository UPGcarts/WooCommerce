<?php

namespace Upg\Library\Tests\Response\Unserializer;

use Upg\Library\Response\Unserializer\Processor;
use Upg\Library\Tests\Mock\Request\TopLevelRequest;
use Upg\Library\Tests\Mock\Response\Unserializer\Handler\MockHandleRecursive;

class ProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test to check if a std object is returned
     */
    public function testNonRecursiveProcessorReturnsRawJsonObject()
    {
        $processor = new Processor();

        $value = json_decode('{"firstName": "John","lastName": "Smith"}');

        $result = $processor->unSerialize('test', $value);

        $this->assertInstanceOf('\stdClass', $result);
        $this->assertObjectHasAttribute("firstName", $result);
        $this->assertObjectHasAttribute("lastName", $result);
    }

    /**
     * Test if processor will call the handler and return an object
     */
    public function testNonRecursiveProcessorReturnsProcessedObject()
    {
        $processor = new Processor();

        $processor->addUnserializerHandler(new MockHandleRecursive());

        $value = json_decode('{"firstName": "John","lastName": "Smith"}', true);

        $result = $processor->unSerialize('testobj', $value);

        $this->assertInstanceOf('\Upg\Library\Tests\Mock\Request\TopLevelRequest', $result);
        $data = $result->getPreSerializerData();

        $this->assertArrayHasKey("firstName", $data);
        $this->assertArrayHasKey("lastName", $data);

        $this->assertEquals("John", $data['firstName']);
        $this->assertEquals("Smith", $data['lastName']);
    }

    /**
     * Check if the processor can process a serialization for a sub object
     */
    public function testRecursiveProcessorReturnsProcessedSubObject()
    {
        $processor = new Processor();

        $processor->addUnserializerHandler(new MockHandleRecursive());

        $value = json_decode('{"firstName": "John","lastName": "Smith","testobj": {"a": 1,"b": 2}}', true);

        $result = $processor->unSerialize('test', $value);

        $this->assertArrayHasKey("firstName", $result);
        $this->assertArrayHasKey("lastName", $result);
        $this->assertArrayHasKey("testobj", $result);

        $this->assertInstanceOf('\Upg\Library\Tests\Mock\Request\TopLevelRequest', $result['testobj']);
        /**
         * @var TopLevelRequest $subObject
         */
        $subObject = $result['testobj'];
        $data = $subObject->getPreSerializerData();

        $this->assertArrayHasKey("a", $data);
        $this->assertArrayHasKey("b", $data);

        $this->assertEquals("1", $data['a']);
        $this->assertEquals("2", $data['b']);
    }

    /**
     * For this deserialization both the top and sub object should deserialize to
     * \Upg\Library\Tests\Mock\Request\TopLevelRequest
     */
    public function testRecursiveProcessorReturnsProcessed()
    {
        $processor = new Processor();

        $processor->addUnserializerHandler(new MockHandleRecursive());

        $value = json_decode('{"firstName": "John","lastName": "Smith","testobj": {"a": 1,"b": 2}}', true);

        /**
         * @var TopLevelRequest $result
         */
        $result = $processor->unSerialize('testobj', $value);

        $this->assertInstanceOf('\Upg\Library\Tests\Mock\Request\TopLevelRequest', $result);

        $data = $result->getPreSerializerData();

        $this->assertArrayHasKey("firstName", $data);
        $this->assertArrayHasKey("lastName", $data);
        $this->assertArrayHasKey("testobj", $data);

        $this->assertEquals("John", $data['firstName']);
        $this->assertEquals("Smith", $data['lastName']);

        /**
         * @var TopLevelRequest $subObject
         */
        $subObject = $data['testobj'];
        $this->assertInstanceOf('\Upg\Library\Tests\Mock\Request\TopLevelRequest', $subObject);

        $subObjectData = $subObject->getPreSerializerData();
        $this->assertArrayHasKey("a", $subObjectData);
        $this->assertArrayHasKey("b", $subObjectData);

        $this->assertEquals("1", $subObjectData['a']);
        $this->assertEquals("2", $subObjectData['b']);
    }
}
