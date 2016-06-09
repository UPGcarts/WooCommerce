<?php

namespace Upg\Library\Tests\Serializer\Visitors;

use Upg\Library\Serializer\Serializer;
use Upg\Library\Serializer\Visitors\UrlEncode as UrlEncode;
use Upg\Library\Serializer\Visitors\Json as Json;
use Upg\Library\Tests\Mock\Request\NonRecursiveUrlEncodeRequest;
use Upg\Library\Tests\Mock\Request\UrlRequestWithArray;

class UrlEncodeTest extends \PHPUnit_Framework_TestCase
{

    public function testNonRecursiveSerialization()
    {
        $request = new NonRecursiveUrlEncodeRequest();

        $formWalker = new UrlEncode();

        $serilizedData = $formWalker->visit($request, new Serializer());

        $this->assertEquals("test=1&test2=2", $serilizedData, "Message serilized string is not what is expected");

    }

    public function testRecursiveSerializationWithJson()
    {
        $request = new \Upg\Library\Tests\Mock\Request\RecursiveUrlEncodeRequest();

        $formWalker = new UrlEncode();
        $serilizer = new Serializer();

        $serilizer->setVisitor($formWalker);
        $serilizer->setVisitor(new Json());

        $serilizedData = $formWalker->visit($request, $serilizer);

        $this->assertEquals(
            "test=1&test2=2&testc=%7B%22test%22%3A1%2C%22test2%22%3A2%7D",
            $serilizedData,
            "Message serilized string is not what is expected"
        );

    }

    /**
     * Test serialization of an array
     */
    public function testArraySerialization()
    {
        $request = new UrlRequestWithArray();

        $formWalker = new UrlEncode();
        $serilizer = new Serializer();

        $serilizer->setVisitor($formWalker);
        $serilizer->setVisitor(new Json());

        $serilizedData = $formWalker->visit($request, $serilizer);

        $this->assertEquals(
            "test=1&test2=2&arrayValue=%5B%7B%22test%22%3A1%2C%22test2%22%3A2%7D%2C%7B%22test%22%3A1%2C%22test2%22%3A2%7D%5D",
            $serilizedData,
            "Message serilized string is not what is expected"
        );
    }
}

