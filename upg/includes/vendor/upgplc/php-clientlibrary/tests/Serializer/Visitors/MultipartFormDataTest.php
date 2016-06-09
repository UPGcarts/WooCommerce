<?php

namespace Upg\Library\Tests\Serializer\Visitors;

use Upg\Library\Request\Objects\Attributes\File;
use Upg\Library\Serializer\Serializer;
use Upg\Library\Serializer\Visitors\Json;
use Upg\Library\Serializer\Visitors\MultipartFormData;
use Upg\Library\Tests\Mock\Request\MultipartRequest;
use Upg\Library\Tests\Mock\Request\NonRecursiveJsonRequest;
use Upg\Library\Tests\Mock\Request\NonRecursiveUrlEncodeRequest;
use Upg\Library\Tests\Mock\Request\UrlRequestWithArray;

class MultipartFormDataTest extends \PHPUnit_Framework_TestCase
{
    public function testNonRecursiveSerialization()
    {
        $request = new MultipartRequest();

        $multipartRequest = new MultipartFormData();

        $request->data = array(
            'test1' => 1,
            'test2' => 2,
        );

        $serilizedData = $multipartRequest->visit($request, new Serializer());

        $this->assertNotEmpty($serilizedData, "serializer returned nothing");
        $this->assertEquals(array('test1' => 1, 'test2' => 2), $serilizedData, "serializer returned did not work");
    }

    public function testNonRecursiveSerializationWithFile()
    {
        $request = new MultipartRequest();
        $multipartRequest = new MultipartFormData();

        $file = new File();
        $file->setPath(__FILE__);

        $request->data = array(
            'test1' => 1,
            'file' => $file,
        );

        $serilizedData = $multipartRequest->visit($request, new Serializer());
        $this->assertNotEmpty($serilizedData, "serializer returned nothing");

        $this->assertArrayHasKey('test1', $serilizedData, "test1 field is non existent");
        $this->assertArrayHasKey('file', $serilizedData, "test1 field is non existent");

        $this->assertEquals(1, $serilizedData['test1']);
        $this->assertEquals("FILE::".__FILE__, $serilizedData['file']);
    }

    public function testRecursiveSerialization()
    {
        $request = new MultipartRequest();

        $multipartRequest = new MultipartFormData();

        $request->data = array(
            'test1' => 1,
            'json' => new NonRecursiveJsonRequest(),
        );

        $serializer = new Serializer();

        $serializer->setVisitor(new Json());

        $serilizedData = $multipartRequest->visit($request, $serializer);

        $this->assertNotEmpty($serilizedData, "serializer returned nothing");

        $this->assertArrayHasKey('test1', $serilizedData, "test1 field is non existent");
        $this->assertArrayHasKey('json', $serilizedData, "test1 field is non existent");

        $this->assertEquals(1, $serilizedData['test1']);

        $this->assertJson($serilizedData['json']);
        $this->assertJsonStringEqualsJsonString($serilizedData['json'], '{"test":1,"test2":2}');
    }
}
