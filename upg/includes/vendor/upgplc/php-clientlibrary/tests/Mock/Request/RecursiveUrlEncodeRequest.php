<?php


namespace Upg\Library\Tests\Mock\Request;

use Upg\Library\Request\RequestInterface;

class RecursiveUrlEncodeRequest implements RequestInterface
{

    /**
     * String with the visitor code that should handle serilization etc json,post etc
     * @return string
     */
    public function getSerialiseType()
    {
        return 'urlencode';
    }

    /**
     * Return array with the data to be serialized
     * @return array
     */
    public function getSerializerData()
    {
        return array(
            'test' => 1,
            'test2' => 2,
            'testc' => new NonRecursiveJsonRequest(),
        );
    }

    public function toArray()
    {
        return $this->getSerializerData();
    }

    public function getValidationData()
    {
        return array();
    }

    public function customValidation()
    {
        return array();
    }
}
