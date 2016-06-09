<?php

namespace Upg\Library\Tests\Validation\Helper;


use Upg\Library\Tests\Mock\Request\CustomValidationRequest;
use Upg\Library\Validation\Helper\Constants;

class ConstantsTest extends \PHPUnit_Framework_TestCase
{

    public function testValidateConstant()
    {
        $request = new CustomValidationRequest();
        $className = get_class($request);
        $className = "\\".$className;
        /**
         * Test successful validation
         */
        $result = Constants::validateConstant($className, 1, 'CUSTOM_TEST');
        $this->assertTrue($result, "Constant validation did not work");

        /**
         * Test unsuccessful validation
         */
        $result = Constants::validateConstant($className, 10, 'CUSTOM_TEST');
        $this->assertFalse($result, "Constant validation did not work");

        /**
         * Test unsuccessful validation
         */
        $result = Constants::validateConstant($className, "10", 'CUSTOM_TEST');
        $this->assertFalse($result, "Constant validation did not work");


    }

}
