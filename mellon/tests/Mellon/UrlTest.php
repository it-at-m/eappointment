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
class UrlTest extends \PHPUnit\Framework\TestCase
{

    public function testUrl()
    {
        $value = "http://www.domaid.tld/sub/path/file.html";
        $this->assertFalse(
            Validator::value($value)->isUrl()->hasFailed(),
            "URL '$value' should validate"
        );
        $value = "abc";
        $this->assertTrue(
            Validator::value($value)->isUrl()->hasFailed(),
            "URL '$value' should not validate"
        );
    }
}
