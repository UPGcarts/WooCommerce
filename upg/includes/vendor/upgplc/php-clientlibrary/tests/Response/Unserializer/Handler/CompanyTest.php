<?php

namespace Upg\Library\Tests\Response\Unserializer\Handler;

use Upg\Library\Request\Objects\Company;
use Upg\Library\Response\Unserializer\Handler\Company as CompanyUnserializer;
use Upg\Library\Response\Unserializer\Processor;

class CompanyTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Test if array of PaymentInstrument is returned
     */
    public function testSerialization()
    {
        $path = realpath(dirname(__FILE__));

        $json = file_get_contents("$path/CompanyTest.json");

        $value = json_decode($json, true);

        $serializer = new CompanyUnserializer();

        /**
         * @var Company $company
         */
        $company = $serializer->unserializeProperty(new Processor(), $value);

        $this->assertEquals("Test", $company->getCompanyName());
        $this->assertEquals(1, $company->getCompanyRegistrationID());
        $this->assertEquals(1111, $company->getCompanyVatID());
        $this->assertEquals(2222, $company->getCompanyTaxID());
        $this->assertEquals('FN', $company->getCompanyRegisterType());

    }
}
