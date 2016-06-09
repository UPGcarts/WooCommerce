<?php

namespace Upg\Library\Tests\Request;

abstract class AbstractRequestTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Validate validation array for a given message for a given class
     * @param string $className
     * @param string $fieldName
     * @param string $validationMessage
     * @param array $acutual
     * @param string $message
     */
    protected function assertValidationReturned($className, $fieldName, $validationMessage, $acutual, $message)
    {
        if (array_key_exists($className, $acutual)) {
            $classErrors = $acutual[$className];
            if (array_key_exists($fieldName, $classErrors)) {
                $messages = $classErrors[$fieldName];
                $this->assertArraySubset(array($validationMessage), $messages, '', $message);
            } else {
                $this->fail($message . " field was not found in validation array");
            }
        } else {
            $this->fail($message . " class name not found in validation array");
        }
    }

    /**
     * Validate that an error message has not been returned
     * @param $className
     * @param $fieldName
     * @param $validationMessage
     * @param $acutual
     * @param $message
     */
    protected function assertValidationHasNotReturned($className, $fieldName, $validationMessage, $acutual, $message)
    {
        if (array_key_exists($className, $acutual)) {
            $classErrors = $acutual[$className];
            if (array_key_exists($fieldName, $classErrors)) {
                $messages = $classErrors[$fieldName];
                $this->assertFalse(in_array($validationMessage, $messages), $message);
            } else {
                $this->assertTrue(true, "If this message shows something has gone wrong with the not returned test");
            }
        } else {
            $this->assertTrue(true, "If this message shows something has gone wrong with the not returned test");
        }
    }
}
