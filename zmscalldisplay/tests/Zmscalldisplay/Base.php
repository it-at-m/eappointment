<?php

/**
 *
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Zmscalldisplay\Tests;

use \Prophecy\Argument;

abstract class Base extends \BO\Zmsclient\PhpUnit\Base
{

    protected $namespace = '\\BO\\Zmscalldisplay\\';

    public function readFixture($filename)
    {
        $path = dirname(__FILE__) . '/fixtures/' . $filename;
        if (! is_readable($path) || ! is_file($path)) {
            throw new \Exception("Fixture $path is not readable");
        }
        return file_get_contents($path);
    }
}
