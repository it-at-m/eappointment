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
class BoolTest extends \PHPUnit\Framework\TestCase
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
}
