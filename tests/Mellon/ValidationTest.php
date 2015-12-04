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
    public function testBoolean()
    {
        $truevalues = array(
            true,
            'on',
            'yes',
            1
        );
        foreach ($truevalues as $value) {
            $this->assertTrue(
                Validator::value($value)->isBool()->getValue(),
                var_export($value, true) . ' should be true'
            );
        }
        $falsevalues = array(
            false,
            'no',
            '',
            0,
            'off',
        );
        foreach ($falsevalues as $value) {
            $this->assertFalse(
                Validator::value($value)->isBool()->getValue(),
                var_export($value, true) . ' should be false'
            );
        }
        $this->assertTrue(
            Validator::value('dummy')->isBool()->hasFailed(),
            'unknown value for boolean should not validate'
        );
    }

    public function testNumber()
    {
        $value = "12345";
        $unvalidvalue = Validator::value($value);
        $validvalue = $unvalidvalue->isNumber();
        $this->assertEquals($value, "$validvalue");
        $value = "abc";
        $unvalidvalue = Validator::value($value);
        $validvalue = $unvalidvalue->isNumber();
        $this->assertNotEquals($value, "$validvalue");
    }

    public function testString()
    {
        $value = "abc";
        $unvalidvalue = Validator::value($value);
        $validvalue = $unvalidvalue->isString();
        $this->assertEquals($value, "$validvalue");
        $value = str_repeat("I am big!!", 7000);
        $unvalidvalue = Validator::value($value);
        $validvalue = $unvalidvalue->isString()->setDefault('ok');
        $this->assertEquals('ok', $validvalue->getValue());
    }

    public function testSize()
    {
        $this->assertTrue(
            Validator::value("123456789")->isSmallerThan(8)->hasFailed(),
            "String of length 9 should not be smaller than 8"
        );
        $this->assertTrue(
            Validator::value("123456789")->isBiggerThan(10)->hasFailed(),
            "String of length 9 should not be bigger than 10"
        );
    }

    public function testRegex()
    {
        $this->assertTrue(
            Validator::value("123456789")->isMatchOf('/abc/')->hasFailed(),
            "'abc' should not match 123456789"
        );
        $this->assertFalse(
            Validator::value("123456789")->isMatchOf('/456/')->hasFailed(),
            "'456' should match 123456789"
        );
        $this->assertFalse(
            Validator::value("123456789")->isFreeOf('/abc/')->hasFailed(),
            "'abc' should not match 123456789"
        );
        $this->assertTrue(
            Validator::value("123456789")->isFreeOf('/456/')->hasFailed(),
            "'456' should match 123456789"
        );
        $this->assertFalse(
            Validator::value(1)->isMatchOf('/^(0|1)$/')->hasFailed(),
            "'(0|1)' should match 1"
        );
        $this->assertTrue(
            Validator::value(null)->isMatchOf('/^(0|1)$/')->hasFailed(),
            "'(0|1)' should not match NULL"
        );
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
