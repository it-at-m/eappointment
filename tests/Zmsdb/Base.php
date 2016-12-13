<?php

namespace BO\Zmsdb\Tests;

abstract class Base extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        \BO\Zmsdb\Connection\Select::setTransaction();
        \BO\Zmsdb\Connection\Select::setProfiling();
        \BO\Zmsdb\Connection\Select::setQueryCache(false);
    }

    public function tearDown()
    {
        \BO\Zmsdb\Connection\Select::writeRollback();
        \BO\Zmsdb\Connection\Select::closeWriteConnection();
        \BO\Zmsdb\Connection\Select::closeReadConnection();
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
        /*
        $profiles = \BO\Zmsdb\Connection\Select::getReadConnection()->getProfiler()->getProfiles();
        foreach ($profiles as $profile) {
            $this->dumpProfile($profile);
        }*/
        $profiling = \BO\Zmsdb\Connection\Select::getReadConnection()->fetchAll('SHOW PROFILES');
        foreach ($profiling as $profile) {
            echo $profile['Query_ID']. ' ' . $profile['Duration']. ' ' . $profile['Query'] . "\n";
        }
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
