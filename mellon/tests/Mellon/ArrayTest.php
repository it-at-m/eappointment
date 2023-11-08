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
class ArrayTest extends \PHPUnit\Framework\TestCase
{

    public function testArray()
    {
        $value = [1, 2, 3];
        $this->assertFalse(
            Validator::value($value)->isArray()->hasFailed(),
            "Native PHP array should validate"
        );
        $value = new \ArrayObject($value);
        $this->assertFalse(
            Validator::value($value)->isArray()->hasFailed(),
            "ArrayObject should validate"
        );
        $value = new \stdClass();
        $value->a = 1;
        $value->b = 2;
        $this->assertTrue(
            Validator::value($value)->isArray()->hasFailed(),
            "stdClass should not validate"
        );
    }
}
