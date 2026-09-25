<?php

namespace BO\Zmsbackend\Tests\ProcessLock\Service;

use \BO\Zmsbackend\Process\Service\Process as Query;
use \BO\Zmsbackend\Process\Service\ProcessStatusFree;
use \BO\Zmsbackend\Process\Service\ProcessStatusQueued;
use \BO\Zmsbackend\Process\Service\ProcessStatusArchived;
use \BO\Zmsentities\Process as Entity;
use \BO\Zmsentities\Calendar;

class ProcessLockTest extends \BO\Zmsbackend\Tests\Service\Base
{
    protected $pdo = null;
    protected static $pdoList = [];

    public function setUp(): void
    {
        parent::setUp();
        $this->pdo = static::createPDO();
        // Set shorter timeout for the test to not block other tests using vendor/bin/paratest
        \BO\Zmsbackend\Connection\Select::getWriteConnection()->perform('SET SESSION innodb_lock_wait_timeout=1');
    }

    public static function createPDO()
    {
        $pdo = new \BO\Zmsbackend\Connection\Pdo(
            \BO\Zmsbackend\Connection\Select::$readSourceName,
            \BO\Zmsbackend\Connection\Select::$username,
            \BO\Zmsbackend\Connection\Select::$password,
            []
        );
        $pdo->exec('SET NAMES "UTF8";');
        $pdo->exec('SET SESSION sql_mode = "STRICT_ALL_TABLES";');
        $pdo->exec('SET SESSION innodb_lock_wait_timeout=300');
        $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $pdo->beginTransaction();
        static::$pdoList[] = $pdo;
        return $pdo;
    }

    public function tearDown(): void
    {
        foreach (static::$pdoList as $pdo) {
            $pdo->rollBack();
        }
        static::$pdoList = [];
        parent::tearDown();
    }

    /**
     * Another worker can turn the waited lock into a deadlock. Both mean the write was blocked.
     */
    private function assertBlocked(callable $write): void
    {
        try {
            $write();
            $this->fail('The locked write completed');
        } catch (\BO\Zmsbackend\Exception\Pdo\LockTimeout | \BO\Zmsbackend\Exception\Pdo\DeadLockFound $blocked) {
            $this->assertNotEmpty($blocked->getMessage());
        }
    }

    public function writeDBLock()
    {
        $statement = $this->pdo
            ->prepare("SELECT * FROM process_sequence  WHERE processId > 100000 AND processId < 102000 FOR UPDATE");
        $statement
            ->execute();
        $statement->fetchAll();
    }

    public function testDBIsLockedByNewProcess()
    {
        $this->writeDBLock();
        $now = static::$now;
        $scope = (new \BO\Zmsbackend\Scope\Service\Scope())->readEntity(141, 0, true);
        $query = new \BO\Zmsbackend\Process\Service\ProcessStatusQueued();
        $this->assertBlocked(function () use ($query, $scope, $now) {
            $query->writeNewFromTicketprinter($scope, $now);
        });
    }

    public function testDBIsLockedByUpdateProcess()
    {
        $this->writeDBLock();
        $now = static::$now;
        $query = new \BO\Zmsbackend\Process\Service\ProcessStatusFree();
        $input = \BO\Zmsbackend\Tests\Process\Service\ProcessTest::getTestProcessEntity();
        $input->queue['callTime'] = 0;
        $this->assertBlocked(function () use ($query, $input, $now) {
            $process = $query->writeEntityReserved($input, $now);
            $process->amendment = 'Test amendment';
            $process->clients[] = new \BO\Zmsentities\Client(['familyName' => 'Unbekannt']);
            $process->queue['lastCallTime'] = 1459511700;
            $query->updateEntity($process, $now);
        });
    }

    public function testConcurrentOnSameSlot()
    {
        $now = static::$now;
        $statement = $this->pdo
            ->prepare(
                "SELECT * FROM slot "
                ."WHERE scopeID=151 AND year=2016 AND month=5 AND day=27 AND time='12:00:00' FOR UPDATE"
            );
        $statement
            ->execute();
        $statement->fetchAll();
        $process = \BO\Zmsbackend\Tests\Process\Service\ProcessTest::getTestProcessEntity();
        $process->getFirstAppointment()->date = 1464343200;// 2016-05-27 12:00:00 (1 slot free)
        $query = new \BO\Zmsbackend\Process\Service\ProcessStatusFree();
        $this->assertBlocked(function () use ($query, $process, $now) {
            $query->writeEntityReserved($process, $now, 'public', 1);
        });
    }
}
