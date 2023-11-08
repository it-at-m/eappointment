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

    public function setUp(): void
    {
        \BO\Zmsdb\Connection\Select::setTransaction();
        \BO\Zmsdb\Connection\Select::setProfiling();
    }

    public function tearDown(): void
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

    protected function setWorkstation(
        $workstationId = 137,
        $loginname = "testuser",
        $scopeId = 143,
        $password = "vorschau"
    ) {
        User::$workstation = new Workstation([
            'id' => $workstationId,
            'useraccount' => new Useraccount([
                'id' => $loginname,
                'password' => md5($password)
            ]),
            'scope' => new Scope([
                'id' => $scopeId,
                'preferences' => [
                    'queue' => [
                        'processingTimeAverage' => 10,
                    ]
                ]
            ])
        ]);
        User::$workstationResolved = 2;
        return User::$workstation;
    }

    protected function setDepartment($departmentId)
    {
        $department = new \BO\Zmsentities\Department([
            'id' => $departmentId,
            'name' => "TestDepartment $departmentId",
            ]);
        User::$workstation->getUseraccount()->addDepartment($department);
        return $department;
    }

    protected function dumpProfiler()
    {
        echo "\nProfiler:\n";
        $profiler = \BO\Zmsdb\Connection\Select::getReadConnection()->getProfiler();
        $logger   = $profiler->getLogger();

        if (method_exists($logger, 'getMessages')) {
            foreach ($logger->getMessages() as $message) {
                echo $message . PHP_EOL;
            }
        }
    }
}
