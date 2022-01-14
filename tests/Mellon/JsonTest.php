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
class JsonTest extends \PHPUnit\Framework\TestCase
{

    public function testJson()
    {
        $value = '{"test":"value"}';
        $valid = Validator::value($value)->isJson()->setDefault(array('Test'), '["Test"]');
        $this->assertFalse(
            $valid->hasFailed(),
            "JSON '$value' should validate"
        );
        $this->assertEquals($value, (string)$valid);
        $this->assertEquals(array('test' => 'value'), $valid->getValue());

        $value = "abc";
        $valid = Validator::value($value)->isJson()->setDefault(array('Test'))->setDefaultJson('["Test"]');
        $this->assertTrue(
            $valid->hasFailed(),
            "JSON '$value' should not validate"
        );
        $this->assertEquals(array('Test'), $valid->getValue());
        $this->assertEquals('["Test"]', (string)$valid);
    }
}
