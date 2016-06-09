<?php


namespace Upg\Library\Tests\Mock\Request;

use Upg\Library\Request\RequestInterface;
use Upg\Library\Request\Attributes\ObjectArray;

class JsonRequestWithArray implements RequestInterface
{
    /**
     * String with the visitor code that should handle serilization etc json,post etc
     * @return string
     */
    public function getSerialiseType()
    {
        return 'json';
    }

    /**
     * Return array with the data to be serialized
     * @return array
     */
    public function getSerializerData()
    {
        $objectArray = new ObjectArray();

        $objectArray->append(new NonRecursiveJsonRequest());
        $objectArray->append(new NonRecursiveJsonRequest());
        return array(
            'test' => 1,
            'test2' => 2,
            'arrayValue' => $objectArray,
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