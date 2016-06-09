<?php

namespace Upg\Library\Tests\Serializer;

use Upg\Library\Serializer\Exception\AbstractException;
use Upg\Library\Serializer\Serializer as Serializer;
use Upg\Library\Tests\Mock\Serializer\Visitors\Json as Json;
use Upg\Library\Tests\Mock\Request\NonRecursiveJsonRequest as NonRecursiveJsonRequest;

class SerializerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test if a successful return of a string when a mock walker is found for a mock object
     */
    public function testSerializeSuccess()
    {
        $serializer = new Serializer();

        $serializer->setVisitor(new Json());

        $value = $serializer->serialize(new NonRecursiveJsonRequest());

        $this->assertInternalType('string', $value, "The serializer should return a string");
        $this->assertEquals('{"testa": 1}', $value, "The serialized string is not what is expected
        Please either amend the mock object or test");

    }

    /**
     * Test if an exception gets raised if a walker could not be found
     */
    public function testSerializerCouldNotFindVisitor()
    {
        try {
            $serializer = new Serializer();
            $serializer->serialize(new NonRecursiveJsonRequest());
            $this->fail("No exception was raised");

        } catch (AbstractException $e) {
            $this->assertInstanceOf(
                'Upg\Library\Serializer\Exception\VisitorCouldNotBeFound',
                $e,
                "Incorrect Exception type raised"
            );

        } catch (\Exception $e) {
            $instance = get_class($e);
            $this->fail("A non library serializer exception was raised instance is $instance, got: {$e->getMessage()}");

        }
    }
}
