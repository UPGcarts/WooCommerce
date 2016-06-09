<?php
namespace Upg\Library\Tests\Mock\Request;

use Upg\Library\Request\RequestInterface;

class MultipartRequest implements RequestInterface
{
    public $data = array();

    public function getSerialiseType()
    {
        return 'multipart';
    }

    public function getSerializerData()
    {
        return $this->data;
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
