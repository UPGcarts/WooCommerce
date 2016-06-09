<?php


namespace Upg\Library\Tests\Mock\Request;

use Upg\Library\Request\AbstractRequest;

class TopLevelRequest extends AbstractRequest
{
    public $data = array(
        'test1' => 'foo',
        'test2' => 'boo',
        'test3' => 22,
    );

    public function getPreSerializerData()
    {
        return $this->data;
    }

    public function getClassValidationData()
    {
        return array();
    }
}