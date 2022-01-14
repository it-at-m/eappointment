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
class NumberTest extends \PHPUnit\Framework\TestCase
{
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
}
