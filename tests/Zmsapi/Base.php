<?php
/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsapi\Tests;

use BO\Zmsapi\Helper\User;
use BO\Zmsentities\Useraccount;
use BO\Zmsentities\Workstation;
use BO\Zmsentities\Scope;

abstract class Base extends \BO\Slim\PhpUnit\Base
{
    protected $namespace = '\\BO\\Zmsapi\\';

    public function setUp()
    {
        \BO\Zmsdb\Connection\Select::setTransaction();
    }

    public function tearDown()
    {
        \BO\Zmsapi\Helper\User::$workstation = null;
        \BO\Zmsdb\Connection\Select::writeRollback();
        \BO\Zmsdb\Connection\Select::closeWriteConnection();
        \BO\Zmsdb\Connection\Select::closeReadConnection();
    }

    public function readFixture($filename)
    {
        $path = dirname(__FILE__) . '/fixtures/' . $filename;
        if (!is_readable($path) || !is_file($path)) {
            throw new \Exception("Fixture $path is not readable");
        }
        return file_get_contents($path);
    }

    protected function setWorkstation($workstationId = 123, $loginname = "testuser", $scopeId = 143)
    {
        User::$workstation = new Workstation([
            'id' => $workstationId,
            'useraccount' => new Useraccount([
                'id' => $loginname,
            ]),
            'scope' => new Scope([
                'id' => $scopeId,
            ])
        ]);
        return User::$workstation;
    }
}
