<?php

namespace Upg\Library\Tests\Response\Unserializer\Handler;

use Upg\Library\Request\Objects\Address;
use Upg\Library\Response\Unserializer\Handler\Address as AddressUnserializer;
use Upg\Library\Response\Unserializer\Processor;

class AddressTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test if array of PaymentInstrument is returned
     */
    public function testSerialization()
    {
        $path = realpath(dirname(__FILE__));

        $json = file_get_contents("$path/AddressTest.json");

        $value = json_decode($json, true);

        $serializer = new AddressUnserializer();

        /**
         * @var Address $address
         */
        $address = $serializer->unserializeProperty(new Processor(), $value);

        $this->assertEquals("Test", $address->getStreet());
        $this->assertEquals(1, $address->getNo());
        $this->assertEquals('LS124TN', $address->getZip());
        $this->assertEquals('Leeds', $address->getCity());
        $this->assertEquals('West Yorks', $address->getState());
        $this->assertEquals('GB', $address->getCountry());

    }
}
