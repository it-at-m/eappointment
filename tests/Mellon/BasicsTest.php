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
class BasicsTest extends \PHPUnit_Framework_TestCase
{
    public function testBasic()
    {
        $validTest = Validator::param("test")->isBool();
        $this->assertTrue($validTest instanceof Valid);
    }

    public function testDefaultvalue()
    {
        $valid = new Valid("123");
        $this->assertEquals(
            null,
            $valid->getValue(),
            "Unvalidated parameters without a default value should return NULL"
        );
        $valid->setDefault('456');
        $this->assertEquals('456', $valid->getValue(), "Unvalidated parameters should return the default value");

    }
    public function testUnvalidated()
    {
        $valid = Validator::value('123');
        $this->assertTrue(
            $valid instanceof \BO\Mellon\Unvalidated,
            "Parameter should not be of class Valid if no validation happened"
        );
        $this->setExpectedException('\BO\Mellon\Exception');
        $valid->getValue();
    }

    public function testReturn()
    {
        $this->assertEquals(
            'abc',
            Validator::value('abc')->isString()->getValue(),
            'getValue did not return string'
        );
        $this->assertEquals(
            'abc',
            (string)Validator::value('abc')->isString(),
            'casting to string did not return string'
        );
        $status = Validator::value('abc')->isString()->getStatus();
        $this->assertArrayHasKey('failed', $status, 'No status failed found');
        $this->assertArrayHasKey('value', $status, 'No status value found');
        $this->assertArrayHasKey('messages', $status, 'No status messages found');
    }

    public function testConstructFail()
    {
        $this->setExpectedException('\BO\Mellon\Exception');
        new Validator('123');
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
