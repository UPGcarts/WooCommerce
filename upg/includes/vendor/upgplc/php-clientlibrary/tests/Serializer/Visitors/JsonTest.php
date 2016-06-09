<?php

namespace Upg\Library\Tests\Serializer\Visitors;

use Upg\Library\Serializer\Serializer as Serializer;
use Upg\Library\Serializer\Visitors\Json as Json;
use Upg\Library\Tests\Mock\Request\JsonRequestWithArray;
use Upg\Library\Tests\Mock\Request\NonRecursiveJsonRequest as NonRecursiveJsonRequest;

class JsonTest extends \PHPUnit_Framework_TestCase
{

    public function testNonRecursiveSerialization()
    {
        $request = new NonRecursiveJsonRequest();

        $jsonWalker = new Json();

        $serilizedData = $jsonWalker->visit($request, new Serializer());

        $this->assertJson($serilizedData, "Walker did not return json");
        $this->assertJsonStringEqualsJsonString($serilizedData, '{"test":1,"test2":2}');

    }

    public function testRecursiveSerialization()
    {
        $request = new \Upg\Library\Tests\Mock\Request\RecursiveJsonRequest();

        $jsonWalker = new Json();
        $serilizer = new Serializer();
        $serilizer->setVisitor($jsonWalker);

        $serilizedData = $jsonWalker->visit($request, $serilizer);

        $this->assertJson($serilizedData, "Walker did not return json");
        $this->assertJsonStringEqualsJsonString($serilizedData, '{"testa":1,"testb":2,"testc":{"test":1,"test2":2}}');
    }

    /**
     * Test serialization of an array
     */
    public function testArraySerialization()
    {
        $request = new JsonRequestWithArray();

        $jsonWalker = new Json();
        $serilizer = new Serializer();
        $serilizer->setVisitor($jsonWalker);

        $serilizedData = $jsonWalker->visit($request, $serilizer);

        $this->assertJson($serilizedData, "Walker did not return json");
        $this->assertJsonStringEqualsJsonString(
            $serilizedData,
            '{"test":1,"test2":2,"arrayValue":[{"test":1,"test2":2},{"test":1,"test2":2}]}'
        );
    }
}
