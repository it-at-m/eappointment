<?php

/**
 *
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmsstatistic\Tests;

use \Prophecy\Argument;

use BO\Zmsentities\Useraccount;

use BO\Zmsentities\Workstation;

use BO\Zmsentities\Scope;

abstract class Base extends \BO\Zmsclient\PhpUnit\Base
{
    protected $namespace = '\\BO\\Zmsstatistic\\';

    public function readFixture($filename)
    {
        $path = dirname(__FILE__) . '/fixtures/' . $filename;
        if (! is_readable($path) || ! is_file($path)) {
            throw new \Exception("Fixture $path is not readable");
        }
        return file_get_contents($path);
    }
}
