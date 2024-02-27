<?php

namespace BO\Zmsdb\Tests;

use BO\Zmsdb\Cli\Db;
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
        \BO\Zmsdb\Connection\Select::setTransaction();
        \BO\Zmsdb\Connection\Select::setProfiling();
        \BO\Zmsdb\Connection\Select::setQueryCache(false);
        \BO\Zmsdb\Connection\Select::getWriteConnection();
        \BO\Zmsdb\Connection\Select::setTransaction(false);

        $className = explode('\\', static::class);
        $testName = end($className) . '/' . $this->getName();
        Db::executeTestData($testName, 'setup');
    }

    public function tearDown(): void
    {
        //error_log("Memory usage " . round(memory_get_peak_usage() / 1024, 0) . "kb");
        \BO\Zmsdb\Scope::$cache = [];
        \BO\Zmsdb\Availability::$cache = [];
        \BO\Zmsdb\Department::$departmentCache = [];
        \BO\Zmsdb\DayOff::$commonList = null;
        \BO\Zmsdb\Connection\Select::setTransaction();
        \BO\Zmsdb\Connection\Select::writeRollback();
        \BO\Zmsdb\Connection\Select::closeWriteConnection();
        \BO\Zmsdb\Connection\Select::closeReadConnection();

        $className = explode('\\', static::class);
        $testName = end($className) . '/' . $this->getName();
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
        $profiles = \BO\Zmsdb\Connection\Select::getReadConnection()->getProfiler()->getProfiles();
        foreach ($profiles as $profile) {
            $this->dumpProfile($profile);
        }
        /*
        $profiling = \BO\Zmsdb\Connection\Select::getReadConnection()->fetchAll('SHOW PROFILES');
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
