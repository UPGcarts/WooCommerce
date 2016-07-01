<?php

namespace Upg\Library\Tests\Request\Objects;

use Upg\Library\Request\Objects\Amount as Amount;
use Upg\Library\Validation\Validation;

class AmountTest extends \PHPUnit_Framework_TestCase
{

    public function testAmountTestValidationSuccess()
    {
        $amount = new Amount();
        $amount->setAmount(9200)->setVatAmount(1840)->setVatRate(20);

        $validation = new Validation();
        $validation->getValidator($amount);
        $data = $validation->performValidation();

        $this->assertEmpty($data, "Validation found an issue when there should be none");
    }

    public function testAmountTestValidationAmountFailure()
    {
        $amount = new Amount();

        $validation = new Validation();

        /** test the invalid amount logic */
        $amount->setAmount(99999999999999999);
        $validation->getValidator($amount);
        $data = $validation->performValidation();

        $expected = array(
            'Upg\\Library\\Request\\Objects\\Amount' =>
                array(
                    'amount' =>
                        array(
                            0 => 'Amount must be between 1 and 16 digits',
                        ),
                ),
        );

        $this->assertEquals($expected, $data, 'Validation not triggered the amount invalid when more than 16 digits');

        /** Test the amount to a float */
        $amount->setAmount(99.99);
        $validation->getValidator($amount);
        $data = $validation->performValidation();

        $expected = array(
            'Upg\\Library\\Request\\Objects\\Amount' =>
                array(
                    'amount' =>
                        array(
                            0 => 'Amount must be an integer',
                        ),
                ),
        );

        $this->assertEquals($expected, $data, 'Validation not triggered the amount invalid when a float');

    }

    public function testAmountTestValidationVatAmountFailure()
    {
        /** Test length validation */
        $amount = new Amount();
        $amount->setAmount(99);
        $amount->setVatAmount(99999999999999999);

        $validation = new Validation();
        $validation->getValidator($amount);
        $data = $validation->performValidation();

        $expected = array(
            'Upg\\Library\\Request\\Objects\\Amount' =>
                array(
                    'vatAmount' =>
                        array(
                            0 => 'VatAmount must be between 1 and 16 digits',
                        ),
                ),
        );

        $this->assertEquals(
            $expected,
            $data,
            'Validation not triggered the vatAmount invalid when more than 16 digits'
        );

        $amount->setVatAmount(99.99);
        $validation->getValidator($amount);
        $data = $validation->performValidation();

        $expected = array(
            'Upg\\Library\\Request\\Objects\\Amount' =>
                array(
                    'vatAmount' =>
                        array(
                            0 => 'VatAmount must be an integer',
                            1 => 'VatAmount must be between 1 and 16 digits'
                        ),
                ),
        );

        $this->assertEquals($expected, $data, 'Integer validation not triggered the vatAmount');

    }

    public function testAmountTestValidationVatRateFailure()
    {
        $validation = new Validation();

        $amount = new Amount();
        $amount->setAmount(99);

        $amount->setVatRate("string");

        $validation->getValidator($amount);
        $data = $validation->performValidation();

        $expected = array(
            'Upg\\Library\\Request\\Objects\\Amount' =>
                array(
                    'vatRate' =>
                        array(
                            0 => 'VatRate must be an number',
                            1 => 'VatRate must be 1 to 2 digits followed by 1 to 2 decimal place',
                        ),
                ),
        );

        $this->assertEquals($expected, $data, 'Number validation not triggered the vatRate');

        $amount->setVatRate(78.254);

        $validation->getValidator($amount);
        $data = $validation->performValidation();

        $expected = array(
            'Upg\\Library\\Request\\Objects\\Amount' =>
                array(
                    'vatRate' =>
                        array(
                            0 => 'VatRate must be 1 to 2 digits followed by 1 to 2 decimal place',
                        ),
                ),
        );

        $this->assertEquals($expected, $data, 'Float validation not triggered the vatRate');

    }
}
