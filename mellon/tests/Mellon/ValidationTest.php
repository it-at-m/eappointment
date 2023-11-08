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
class ValidationTest extends \PHPUnit\Framework\TestCase
{
    public function testAssert()
    {
        $this->expectException("\BO\Mellon\Failure\Exception");
        Validator::value("test")->isBool()->assertValid();
    }

    public function testMissingFunction()
    {
        $this->expectException("\BO\Mellon\Exception");
        Validator::value("test")->isANotExistingFunction();
    }

    public function testInvalidFunction()
    {
        $this->expectException("\BO\Mellon\Exception");
        Validator::value("test")->isaninvalidfunction();
    }

    public function testInvalidValidationFunction()
    {
        $this->expectException("\BO\Mellon\Exception");
        Validator::value("test")->isString()->isANotExistingFunction();
    }

    public function testUndefinedFunction()
    {
        $this->expectException("\BO\Mellon\Exception");
        Validator::value("test")->isString()->aninvalidfunction();
    }
}
