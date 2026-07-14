<?php

namespace BO\Zmsbackend\Tests\Service;

use BO\Zmsbackend\Cli\Db;
use PHPUnit\Framework\TestCase;

abstract class Base extends TestCase
{
    public const DB_OPERATION_SECONDS_DEFAULT = 1;
    public static $username = 'superuser';
    public static $password = 'vorschau';
    public static $now = null;
    public static $requestRelationCount = 2532;
    public static $requestCount = 308;

    public function setUp(): void
    {
        static::$now = new \DateTimeImmutable('2016-04-01 11:55:00');
        \BO\Zmsbackend\Connection\Select::setTransaction();
        \BO\Zmsbackend\Connection\Select::setProfiling();
        \BO\Zmsbackend\Connection\Select::setQueryCache(false);
        \BO\Zmsbackend\Connection\Select::getWriteConnection();
        \BO\Zmsbackend\Connection\Select::setTransaction(false);

        $className = explode('\\', static::class);
        $testName = end($className) . '/' . $this->name();
        Db::executeTestData($testName, 'setup');
    }

    public function tearDown(): void
    {
        \BO\Zmsbackend\Scope\Service\Scope::$cache = [];
        \BO\Zmsbackend\Availability\Service\Availability::$cache = [];
        \BO\Zmsbackend\Department\Service\Department::$departmentCache = [];
        \BO\Zmsbackend\Dayoff\Service\DayOff::$commonList = null;
        \BO\Zmsbackend\Connection\Select::setTransaction();
        \BO\Zmsbackend\Connection\Select::writeRollback();
        \BO\Zmsbackend\Connection\Select::closeWriteConnection();
        \BO\Zmsbackend\Connection\Select::closeReadConnection();

        $className = explode('\\', static::class);
        $testName = end($className) . '/' . $this->name();
        Db::executeTestData($testName, 'teardown');
    }

    protected function getFixturePath($filename)
    {
        $path = dirname(__FILE__) . '/fixtures/' . $filename;
        return $path;
    }

    public function readFixture($filename)
    {
        $path = $this->getFixturePath($filename);
        if (!is_readable($path) || !is_file($path)) {
            throw new \Exception("Fixture $path is not readable");
        }
        return file_get_contents($path);
    }

    public function assertEntity($entityClass, $entity)
    {
        $this->assertInstanceOf($entityClass, $entity);
        $this->assertTrue($entity->testValid());
    }

    public function assertEntityList($entityClass, $entityList)
    {
        foreach ($entityList as $entity) {
            $this->assertEntity($entityClass, $entity);
        }
    }

    protected function dumpProfiler()
    {
        echo "\nProfiler:\n";
        $profiles = \BO\Zmsbackend\Connection\Select::getReadConnection()->getProfiler()->getProfiles();
        foreach ($profiles as $profile) {
            $this->dumpProfile($profile);
        }
        /*
        $profiling = \BO\Zmsbackend\Connection\Select::getReadConnection()->fetchAll('SHOW PROFILES');
        foreach ($profiling as $profile) {
            echo $profile['Query_ID']. ' ' . $profile['Duration']. ' ' . $profile['Query'] . "\n";
        }
        */
        //var_dump($profiling);
    }

    protected function dumpProfile($profile)
    {
        $statement = $profile['statement'];
        $statement = preg_replace('#\s+#', ' ', $statement);
        $statement = substr($statement, 0, 250);
        echo round($profile['duration'] * 1000, 6) . "ms $statement \n";
    }
}
