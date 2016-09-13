<?php
/**
 * @package Mellon
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Mellon\Tests;

use BO\Mellon\Validator;
use BO\Mellon\Valid;

/**
  *
  *
  */
class ValidationTest extends \PHPUnit_Framework_TestCase
{
    public function testAssert()
    {
        $this->setExpectedException("\BO\Mellon\Failure\Exception");
        Validator::value("test")->isBool()->assertValid();
    }

    public function testMissingFunction()
    {
        $this->setExpectedException("\BO\Mellon\Exception");
        Validator::value("test")->isANotExistingFunction();
    }

    public function testInvalidFunction()
    {
        $this->setExpectedException("\BO\Mellon\Exception");
        Validator::value("test")->isaninvalidfunction();
    }

    public function testInvalidValidationFunction()
    {
        $this->setExpectedException("\BO\Mellon\Exception");
        Validator::value("test")->isString()->isANotExistingFunction();
    }

    public function testUndefinedFunction()
    {
        $this->setExpectedException("\BO\Mellon\Exception");
        Validator::value("test")->isString()->aninvalidfunction();
    }
}
