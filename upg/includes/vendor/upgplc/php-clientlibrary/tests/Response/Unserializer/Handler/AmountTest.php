<?php

namespace Upg\Library\Tests\Response\Unserializer\Handler;

use Upg\Library\Request\Objects\Amount;
use Upg\Library\Request\Objects\PaymentInstrument;
use Upg\Library\Response\Unserializer\Handler\Amount as AmountUnserializer;
use Upg\Library\Response\Unserializer\Processor;

class AmountTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test if array of PaymentInstrument is returned
     */
    public function testSerialization()
    {
        $jsonString = '{"amount":15,"vatAmount":10,"vatRate":50}';

        $jsonObj = json_decode($jsonString, true);

        $amountProcessor = new AmountUnserializer();

        $amount = $amountProcessor->unserializeProperty(new Processor(), $jsonObj);

        /**
         * @var Amount $amount
         */
        $this->assertEquals(15, $amount->getAmount());
        $this->assertEquals(10, $amount->getVatAmount());
        $this->assertEquals(50, $amount->getVatRate());

    }
}
