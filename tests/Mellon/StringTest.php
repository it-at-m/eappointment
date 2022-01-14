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
class StringTest extends \PHPUnit\Framework\TestCase
{

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
        $this->assertEquals('<h1>', 
            Validator::value("<h1>")->isString('', false)->getValue(),
            "HTML Should not be escaped"
        );
        $this->assertEquals('&#60;h1&#62;', 
            Validator::value("<h1>")->isString('', true)->getValue(),
            "HTML Should be escaped"
        );
    }

    public function testSize()
    {
        $this->assertTrue(
            Validator::value("123456789")->isString()->isSmallerThan(8)->hasFailed(),
            "String of length 9 should not be smaller than 8"
        );
        $this->assertTrue(
            Validator::value("123456789")->isString()->isBiggerThan(10)->hasFailed(),
            "String of length 9 should not be bigger than 10"
        );
    }

    public function testRegex()
    {
        $this->assertTrue(
            Validator::value("123456789")->isString()->isMatchOf('/abc/')->hasFailed(),
            "'abc' should not match 123456789"
        );
        $this->assertFalse(
            Validator::value("123456789")->isString()->isMatchOf('/456/')->hasFailed(),
            "'456' should match 123456789"
        );
        $this->assertFalse(
            Validator::value("123456789")->isString()->isFreeOf('/abc/')->hasFailed(),
            "'abc' should not match 123456789"
        );
        $this->assertTrue(
            Validator::value("123456789")->isString()->isFreeOf('/456/')->hasFailed(),
            "'456' should match 123456789"
        );
        $this->assertFalse(
            Validator::value(1)->isString()->isMatchOf('/^(0|1)$/')->hasFailed(),
            "'(0|1)' should match 1"
        );
        $this->assertTrue(
            Validator::value(null)->isString()->isMatchOf('/^(0|1)$/')->hasFailed(),
            "'(0|1)' should not match NULL"
        );
    }
}
