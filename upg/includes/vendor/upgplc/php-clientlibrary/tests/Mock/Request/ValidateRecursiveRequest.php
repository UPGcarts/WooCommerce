<?php


namespace Upg\Library\Tests\Mock\Request;

use Upg\Library\Request\RequestInterface;

class ValidateRecursiveRequest implements RequestInterface
{
    private $serializerData = array(
        'test' => 1,
        'test2' => null,
        'test3' => 'string',
    );

    public function __construct()
    {
        $this->serializerData['testa'] = new ValidateNonRecursiveRequest();
    }

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

    public function setSubObjectData($key, $value)
    {
        /**
         * @var ValidateNonRecursiveRequest $obj
         */
        $obj = $this->serializerData['testa'];
        $obj->setData($key, $value);
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
        $validationData['test2'][] = array(
            'name' => 'Regex',
            'value' => '/^\d{0,16}$/',
            'message' => "test2 must be between 1 and 16 digits"
        );

        $validationData['test2'][] = array(
            'name' => 'Integer',
            'value' => null,
            'message' => "test2 must be an integer"
        );

        $validationData['test3'][] = array(
            'name' => 'Alpha',
            'value' => null,
            'message' => "test3 must be an string"
        );
        $validationData['test3'][] = array(
            'name' => 'Length',
            'value' => '0,16',
            'message' => "test3 must be between 1 and 16 characters"
        );


        return $validationData;
    }

    public function customValidation()
    {
        return array();
    }
}
