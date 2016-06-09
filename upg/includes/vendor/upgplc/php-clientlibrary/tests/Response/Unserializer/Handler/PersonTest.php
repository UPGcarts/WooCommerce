<?php

namespace Upg\Library\Tests\Response\Unserializer\Handler;

use Upg\Library\Request\Objects\Person;
use Upg\Library\Response\Unserializer\Handler\Person as PersonUnserializer;
use Upg\Library\Response\Unserializer\Processor;

class PersonTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test if array of PaymentInstrument is returned
     */
    public function testSerialization()
    {
        $path = realpath(dirname(__FILE__));

        $json = file_get_contents("$path/PersonTest.json");

        $value = json_decode($json, true);

        $serializer = new PersonUnserializer();

        /**
         * @var Person $person
         */
        $person = $serializer->unserializeProperty(new Processor(), $value);

        $this->assertEquals("M", $person->getSalutation());
        $this->assertEquals("Keyshawn", $person->getName());
        $this->assertEquals("Sawayn", $person->getSurname());
        $this->assertEquals("1986-11-11", $person->getDateOfBirth()->format("Y-m-d"));
        $this->assertEquals('test@test.com', $person->getEmail());
        $this->assertEquals('222555', $person->getPhoneNumber());
        $this->assertEquals('333444', $person->getFaxNumber());

    }
}