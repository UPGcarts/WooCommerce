<?php
namespace Upg\Library\Tests\Mock\Response\Unserializer\Handler;


use Upg\Library\Response\Unserializer\Handler\UnserializerInterface;
use Upg\Library\Response\Unserializer\Processor;
use Upg\Library\Tests\Mock\Request\TopLevelRequest;

class MockHandleRecursive implements UnserializerInterface
{
    /**
     * Return the string of the property that the unserializer will handle
     * @return string
     */
    public function getAttributeNameHandler()
    {
        return array('testobj');
    }

    /**
     * @param $value
     * @param Processor $processor
     * @return \Upg\Library\Request\RequestInterface
     */
    public function unserializeProperty(Processor $processor, $value)
    {
        $request = new TopLevelRequest();
        $request->data = $value;
        return $request;
    }
}
