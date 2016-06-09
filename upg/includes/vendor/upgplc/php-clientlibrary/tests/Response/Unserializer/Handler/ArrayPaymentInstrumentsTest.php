<?php

namespace Upg\Library\Tests\Response\Unserializer\Handler;

use Upg\Library\Request\Objects\PaymentInstrument;
use Upg\Library\Response\Unserializer\Handler\ArrayPaymentInstruments;
use Upg\Library\Response\Unserializer\Processor;
use Upg\Library\Tests\Mock\Request\TopLevelRequest;

class ProcessorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test if array of PaymentInstrument is returned
     */
    public function testSerialization()
    {
        $path = realpath(dirname(__FILE__));

        $json = file_get_contents("$path/ArrayPaymentInstrumentsTest.json");

        $value = json_decode($json, true);

        $arrayPaymentInstruments = new ArrayPaymentInstruments();

        $data = $arrayPaymentInstruments->unserializeProperty(new Processor(), $value);

        $this->assertEquals(count($data), 2, "array does not contain two elements");

        $objValidationRan = 0;

        foreach ($data as $paymentInstrument) {
            /**
             * @var PaymentInstrument $paymentInstrument
             */
            if ($paymentInstrument->getPaymentInstrumentType() == PaymentInstrument::PAYMENT_INSTRUMENT_TYPE_CARD ) {
                $this->assertEquals(1, $paymentInstrument->getPaymentInstrumentID());
                $this->assertEquals("Keyshawn Sawayn", $paymentInstrument->getAccountHolder());
                $this->assertEquals("5572314355479157", $paymentInstrument->getNumber());
                $this->assertEquals("201511", $paymentInstrument->getValidity()->format("Ym"));
                $this->assertEquals($paymentInstrument::ISSUER_MC, $paymentInstrument->getIssuer());
                $objValidationRan++;
            }
            if ($paymentInstrument->getPaymentInstrumentType() == PaymentInstrument::PAYMENT_INSTRUMENT_TYPE_BANK) {
                $this->assertEquals(2, $paymentInstrument->getPaymentInstrumentID());
                $this->assertEquals("Dr. Edwardo Nitzsche III", $paymentInstrument->getAccountHolder());
                $this->assertEquals("FI1350001540000056", $paymentInstrument->getIban());
                $this->assertEquals("OKOYFIHH", $paymentInstrument->getBic());
                $objValidationRan++;
            }

        }

        $this->assertEquals(2, $objValidationRan, "Not all object validation has ran in this test");

    }
}
