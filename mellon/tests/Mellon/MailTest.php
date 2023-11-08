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
class MailTest extends \PHPUnit\Framework\TestCase
{

    public function testMail()
    {
        $value = "abc@def.org";
        $this->assertFalse(
            Validator::value($value)->isMail()->hasFailed(),
            "mail address '$value' should validate"
        );
        $value = "abc@not-existing-dns-entry.me";
        $this->assertTrue(
            Validator::value($value)->isMail()->hasDNS()->hasFailed(),
            "mail address '$value' should not validate for not having a valid DNS"
        );
        $value = "abc@berlin.de";
        $this->assertFalse(
            Validator::value($value)->isMail()->hasDNS()->hasFailed(),
            "mail address '$value' should validate with a valid DNS"
        );
        $value = "abc@not-existing-dns-entry.me";
        $this->assertTrue(
            Validator::value($value)->isMail()->hasMX()->hasFailed(),
            "mail address '$value' should not validate for not having a valid MX"
        );
        $value = "abc@berlin.de";
        $this->assertFalse(
            Validator::value($value)->isMail()->hasMX()->hasFailed(),
            "mail address '$value' should validate with a valid MX"
        );
        $value = "abc";
        $this->assertTrue(
            Validator::value($value)->isMail()->hasFailed(),
            "mail address '$value' should not validate"
        );
    }
}
