<?php

namespace BO\Zmsdb\Tests\Process;

use \BO\Zmsdb\Process as Query;
use \BO\Zmsdb\ProcessStatusFree;
use \BO\Zmsdb\ProcessStatusQueued;
use \BO\Zmsdb\ProcessStatusArchived;
use \BO\Zmsentities\Process as Entity;
use \BO\Zmsentities\Calendar;

class ProcessLockTest extends \BO\Zmsdb\Tests\Base
{
    protected $pdo = null;

    public function setUp()
    {
        $this->pdo = new \Pdo(
            \BO\Zmsdb\Connection\Select::$readSourceName,
            \BO\Zmsdb\Connection\Select::$username,
            \BO\Zmsdb\Connection\Select::$password,
            [\PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION]
        );
        $this->pdo->exec('SET NAMES "UTF8";');
        $this->pdo->exec('SET SESSION sql_mode = "STRICT_ALL_TABLES";');
        $this->pdo->exec('SET innodb_lock_wait_timeout = 5;');
        $this->pdo->beginTransaction();
        parent::setUp();
    }

    public function tearDown()
    {
        $this->pdo->rollBack();
        parent::tearDown();
    }

    public function setDBLock()
    {
        $query = $this->pdo
            ->prepare((new \BO\Zmsdb\Query\Process(\BO\Zmsdb\Query\Base::SELECT))->getLockProcessId())
            ->execute(['processId' => 100000]);
    }

    public function testDBIsLockedByNewProcess()
    {
        $this->expectException('\BO\Zmsdb\Exception\Pdo\LockTimeout');
        $this->setDBLock();
        $now = new \DateTimeImmutable("2016-04-01 11:55");
        $query = new Query();
        $scope = (new \BO\Zmsdb\Scope())->readEntity(141, 0, true);
        $process = $query->writeNewPickup($scope, $now);
    }

    public function testDBIsLockedByUpdateProcess()
    {
        $this->expectException('\BO\Zmsdb\Exception\Pdo\LockTimeout');
        $this->setDBLock();
        $now = new \DateTimeImmutable("2016-04-01 11:55");
        $query = new ProcessStatusFree();
        $input = (new ProcessTest)->getTestProcessEntity();
        $input->queue['callTime'] = 0;
        $process = $query->writeEntityReserved($input, $now);
        $process->amendment = 'Test amendment';
        $process->clients[] = new \BO\Zmsentities\Client(['familyName' => 'Unbekannt']);
        $process->queue['lastCallTime'] = 1459511700;
        $process = $query->updateEntity($process, $now);
    }
}
