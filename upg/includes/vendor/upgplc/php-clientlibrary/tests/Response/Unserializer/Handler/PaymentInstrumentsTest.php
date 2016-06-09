<?php

namespace Upg\Library\Tests\Response\Unserializer\Handler;

use Symfony\CS\Tests\Fixer\PSR2\EofEndingFixerTest;
use Upg\Library\Request\Objects\PaymentInstrument;
use Upg\Library\Response\Unserializer\Handler\PaymentInstruments;
use Upg\Library\Response\Unserializer\Processor;
use Upg\Library\Tests\Mock\Request\TopLevelRequest;

class PaymentInstrumentsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test if array of PaymentInstrument is returned
     */
    public function testSerialization()
    {
        $path = realpath(dirname(__FILE__));

        $json = file_get_contents("$path/PaymentInstrumentsTest.json");

        $value = json_decode($json, true);

        $unserializer = new PaymentInstruments();

        /**
         * @var PaymentInstrument $paymentInstrument
         */
        $paymentInstrument = $unserializer->unserializeProperty(new Processor(), $value);

        $this->assertEquals(1, $paymentInstrument->getPaymentInstrumentID());
        $this->assertEquals("Keyshawn Sawayn", $paymentInstrument->getAccountHolder());
        $this->assertEquals("5572314355479157", $paymentInstrument->getNumber());
        $this->assertEquals("201511", $paymentInstrument->getValidity()->format("Ym"));
        $this->assertEquals($paymentInstrument::ISSUER_MC, $paymentInstrument->getIssuer());
    }
}