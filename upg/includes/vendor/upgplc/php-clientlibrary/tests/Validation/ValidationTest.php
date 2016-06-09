<?php

namespace Upg\Library\Tests\Validation;

use Upg\Library\Request\Attributes\ObjectArray;
use Upg\Library\Tests\Mock\Request\CustomValidationRequest;
use Upg\Library\Tests\Mock\Request\ValidateNonRecursiveRequest;
use Upg\Library\Tests\Mock\Request\ValidateRecursiveArrayRequest;
use Upg\Library\Tests\Mock\Request\ValidateRecursiveRequest;
use Upg\Library\Validation\Validation;

class ValidationTest extends \PHPUnit_Framework_TestCase
{

    public function testNonRecursiveRequiredValidation()
    {
        $validation = new Validation();

        $request = new ValidateNonRecursiveRequest();

        $validation->getValidator($request);
        $data = $validation->performValidation();

        $this->assertEmpty($data, "Initial validation of a non recursive object should pass");

        /**
         * Ok now lets test for validation issues
         * First for test member lets do the following:
         * - Remove the attribute and trigger required test
         * - Put non int value
         */
        $request->setData('test', null);
        $validation->getValidator($request);
        $data = $validation->performValidation();

        $expected = array(
            'Upg\\Library\\Tests\\Mock\\Request\\ValidateNonRecursiveRequest' =>
                array(
                    'test' =>
                        array(
                            0 => 'Test is required',
                        ),
                ),
        );

        $this->assertEquals($expected, $data, "Required value has validation failed");

        /** Now test with non int value */
        $request->setData('test', 'something');
        $validation->getValidator($request);
        $data = $validation->performValidation();

        $expected = array(
            'Upg\\Library\\Tests\\Mock\\Request\\ValidateNonRecursiveRequest' =>
                array(
                    'test' =>
                        array(
                            0 => 'Test must be an integer',
                        ),
                ),
        );

        $this->assertEquals($expected, $data, "Integer test on required value failed");

    }

    public function testNonRecursiveOptionalValidation()
    {
        /**
         * Now test one of the non required values with an invalid
         * - test2: First test fail on int validation
         * - test2: Fail on length validation
         */

        $validation = new Validation();

        $request = new ValidateNonRecursiveRequest();

        $request->setData('test2', 'string');
        $validation->getValidator($request);
        $data = $validation->performValidation();

        $expected = array(
            'Upg\\Library\\Tests\\Mock\\Request\\ValidateNonRecursiveRequest' =>
                array(
                    'test2' =>
                        array(
                            0 => 'test2 must be between 1 and 16 digits',
                            1 => 'test2 must be an integer',
                        ),
                ),
        );

        $this->assertEquals($expected, $data, "Integer test on optional value failed");


        /** Validate Length */
        $request->setData('test2', 11111111111111111);
        $validation->getValidator($request);
        $data = $validation->performValidation();

        $expected = array(
            'Upg\\Library\\Tests\\Mock\\Request\\ValidateNonRecursiveRequest' =>
                array(
                    'test2' =>
                        array(
                            0 => 'test2 must be between 1 and 16 digits',
                        ),
                ),
        );

        $this->assertEquals($expected, $data, "Length test on optional value failed");

    }

    public function testRecursiveValidation()
    {
        $validation = new Validation();
        $request = new ValidateRecursiveRequest();

        $validation->getValidator($request);
        $data = $validation->performValidation();

        $this->assertEmpty($data, "Initial validation of a recursive object should pass");

        /**
         * Put failure condition on the recursive object and sub object
         */
        $request->setData('test', 'string');
        $request->setSubObjectData('test', 'string');

        $validation->getValidator($request);
        $data = $validation->performValidation();


        $expected = array(
            'Upg\\Library\\Tests\\Mock\\Request\\ValidateRecursiveRequest' =>
                array(
                    'test' =>
                        array(
                            0 => 'Test must be an integer',
                        ),
                ),
            'Upg\\Library\\Tests\\Mock\\Request\\ValidateNonRecursiveRequest' =>
                array(
                    'test' =>
                        array(
                            0 => 'Test must be an integer',
                        ),
                ),
        );

        $this->assertEquals($expected, $data, "Validation data not as expected");

    }

    public function testRecursiveArrayValidation()
    {
        $validation = new Validation();

        $request = new ValidateRecursiveArrayRequest();

        /**
         * Ok put an error on one of the elements
         */
        $array = new ObjectArray();

        $subRequestObject = new ValidateNonRecursiveRequest();
        $subRequestObject->setData("test", "foo");

        $array->append($subRequestObject);

        $request->setData("testArray", $array);

        $validation->getValidator($request);
        $data = $validation->performValidation();


        $expected = array(
            'Upg\\Library\\Tests\\Mock\\Request\\ValidateNonRecursiveRequest' =>
                array(
                    'test' =>
                        array(
                            0 => 'Test must be an integer',
                        ),
                ),
        );

        $this->assertEquals($expected, $data, "Validation data not as expected");

    }

    public function testCustomValidation()
    {
        $validation = new Validation();
        $request = new CustomValidationRequest();

        $validation->getValidator($request);
        $data = $validation->performValidation();

        $expected = array(
            'Upg\\Library\\Tests\\Mock\\Request\\CustomValidationRequest' =>
                array(
                    'custom' =>
                        array(
                            0 => 'Test Message',
                        ),
                ),
        );

        $this->assertEquals($expected, $data, "Custom Validation not triggered");

    }
}
