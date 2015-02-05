<?php
/**
 * @package 115Mandant
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Mellon\Tests;

use BO\Mellon\Validator;
use BO\Mellon\Valid;

/**
  * 
  * 
  */
class BasicsTest extends \PHPUnit_Framework_TestCase
{
    public function testBasic ()
    {
        $validTest = Validator::param("test")->isBool();
        $this->assertTrue($validTest instanceof Valid);
    }

    public function testNumbersuccess()
    {
        $value = "12345";
        $unvalidvalue = Validator::value($value);
        $validvalue = $unvalidvalue->isNumber();
        $this->assertEquals($value, "$validvalue");
    }

    public function testNumberfail()
    {
        $value = "abc";
        $unvalidvalue = Validator::value($value);
        $validvalue = $unvalidvalue->isNumber();
        $this->assertNotEquals($value, "$validvalue");
    }

    public function testStringsuccess()
    {
        $value = "abc";
        $unvalidvalue = Validator::value($value);
        $validvalue = $unvalidvalue->isString();
        $this->assertEquals($value, "$validvalue");
    }

    public function testStringfail()
    {
        $value = str_repeat("I am big!!", 7000);
        $unvalidvalue = Validator::value($value);
        $validvalue = $unvalidvalue->isString();
        $this->assertNotEquals($value, "$validvalue");
    }

    public function testDefaultvalue()
    {
        $valid = new Valid("123");
        $this->assertEquals(NULL, $valid->getValue(), "Unvalidated parameters without a default value should return NULL");
        $valid->setDefault('456');
        $this->assertEquals('456', $valid->getValue(), "Unvalidated parameters should return the default value");

    }
    public function testUnvalidated()
    {
        $valid = Validator::value('123');
        $this->assertTrue($valid instanceof \BO\Mellon\Unvalidated, "Parameter should not be of class Valid if no validation happened");
        $this->setExpectedException('\BO\Mellon\Exception');
        $valid->getValue();
    }

    public function testConstructFail()
    {
        $this->setExpectedException('\BO\Mellon\Exception');
        $validator = new Validator('123');
    }

    public function testMock()
    {
        Validator::resetInstance();
        $validator = new Validator(['test' => '123']);
        $value = Validator::param('test')->isNumber();
        $this->assertNotEquals('123', "$value");
        $validator->makeInstance();
        $value = Validator::param('test')->isNumber();
        $this->assertEquals('123', "$value");
    }
}
