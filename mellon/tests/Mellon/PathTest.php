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
class PathTest extends \PHPUnit\Framework\TestCase
{

    public function testPath()
    {
        $value = "xyz/abc def.ghi";
        $unvalidvalue = Validator::value($value);
        $validvalue = $unvalidvalue->isPath();
        $this->assertEquals($value, "$validvalue");

        $value = "../abc";
        $this->assertTrue(
            Validator::value($value)->isPath()->hasFailed(),
            "URL '$value' should not validate"
        );

        $value = "abc && rm .htaccess";
        $this->assertTrue(
            Validator::value($value)->isPath()->hasFailed(),
            "URL '$value' should not validate"
        );
    }
}
