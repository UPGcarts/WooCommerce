<?php


namespace Upg\Library\Tests\Mock\Request;

use Upg\Library\Request\RequestInterface;

class ValidateRecursiveArrayRequest implements RequestInterface
{
    private $serializerData = array(
        'test' => 1,
        'testArray' => null,
    );

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
        return $this->serializerData;
    }

    /**
     * For the test so values can be set to cover all eventualities
     * @param $key
     * @param $value
     * @return $this
     */
    public function setData($key, $value)
    {
        $this->serializerData[$key] = $value;
        return $this;
    }

    /**
     * Validator uses this function for the data
     * @return array
     */
    public function toArray()
    {
        return $this->serializerData;
    }

    public function getValidationData()
    {
        $validationData = array();

        $validationData['test'][] = array('name' => 'required', 'value' => null, 'message' => "Test is required");
        $validationData['test'][] = array(
            'name' => 'Integer',
            'value' => null,
            'message' => "Test must be an integer"
        );
        $validationData['testArray'][] = array(
            'name' => 'required',
            'value' => null,
            'message' => "testArray is required"
        );


        return $validationData;
    }

    public function customValidation()
    {
        return array();
    }
}