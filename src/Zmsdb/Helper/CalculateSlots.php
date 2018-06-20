<?php

namespace BO\Zmsdb\Helper;

/**
 * @codeCoverageIgnore
 */
class CalculateSlots
{
    protected $verbose = false;

    protected $startTime;

    protected $logList = [];

    public function __construct($verbose = false)
    {
        $this->verbose = $verbose;
        $this->startTime = microtime(true);
    }

    public function log($message)
    {
        $time = $this->getSpendTime();
        $memory = memory_get_usage()/(1024*1024);
        $text = sprintf("[CalculateSlots %07.3fs %07.1fmb] %s", "$time", $memory, $message);
        $this->logList[] = $text;
        if ($this->verbose) {
            error_log($text);
        }
        return $this;
    }

    public function dumpLogs()
    {
        foreach ($this->logList as $text) {
            if (!$this->verbose) {
                error_log($text);
            }
        }
        $this->verbose = true;
    }

    public function getSpendTime()
    {
        $time = round(microtime(true) - $this->startTime, 3);
        return $time;
    }

    protected function readCalculateSkip()
    {
        $skip = (new \BO\Zmsdb\Config)->readProperty('status__calculateSlotsSkip');
        return $skip;
    }

    protected function readForceVerbose()
    {
        $force = (new \BO\Zmsdb\Config)->readProperty('status__calculateSlotsForceVerbose');
        if ($force) {
            $this->log("Forced verbose, see table config.status__calculateSlotsForceVerbose");
            $this->dumpLogs();
        }
        return $force;
    }

    protected function readLastRun()
    {
        $updateTimestamp = (new \BO\Zmsdb\Config)->readProperty('status__calculateSlotsLastRun', true);
        return $updateTimestamp;
    }

    public function writeMaintenanceQueries()
    {
        $sqlList = (new \BO\Zmsdb\Config)->readProperty('status__calculateSlotsMaintenanceSQL');
        if ($sqlList) {
            $pdo = \BO\Zmsdb\Connection\Select::getWriteConnection();
            foreach (explode("\n", $sqlList) as $sql) {
                $this->log("Maintenance query: $sql");
                $pdo->exec($sql);
            }
            return \BO\Zmsdb\Connection\Select::writeCommit();
        }
        return false;
    }


    public function writeCalculations(\DateTimeInterface $now, $repair = false)
    {
        \BO\Zmsdb\Connection\Select::setTransaction();
        \BO\Zmsdb\Connection\Select::getWriteConnection();
        $this->log("Fetch Slot list with time ". $now->format('c'));
        $this->readForceVerbose();
        $updateTimestamp = $this->readLastRun();
        if ($this->readCalculateSkip()) {
            $this->log("Skip calculation due to config setting status.calculateSlotsSkip");
            return false;
        }
        if ($repair) {
            $updateTimestamp = '';
        }
        $this->log("Last Run on time=" . $updateTimestamp);
        $scopeList = (new \BO\Zmsdb\Scope())->readList(1);
        $scopeLength = count($scopeList) - 1;
        foreach ($scopeList as $key => $scope) {
            if ($this->writeCalculatedScope($scope, $now)) {
                $this->log("Calculated slots $key/$scopeLength for $scope");
            }
        }
        $this->log("Finished slot calculation");
        $slotQuery = new \BO\Zmsdb\Slot();
        $slotQuery->deleteSlotProcessOnProcess();
        $this->log("Finished to free slots for deleted processes");
        $slotQuery->deleteSlotProcessOnSlot();
        $this->log("Finished to free slots for cancelled availabilities");
        $slotQuery->updateSlotProcessMapping($updateTimestamp);
        $this->log("Updated Slot-Process-Mapping");
        (new \BO\Zmsdb\Config)->replaceProperty('status__calculateSlotsLastRun', $now->format('Y-m-d H:i:s'));
        $this->log("Committing changes (may take a while)");
        \BO\Zmsdb\Connection\Select::writeCommit();
        $this->log("Slot calculation finished");
    }

    protected function writeCalculatedScope(\BO\Zmsentities\Scope $scope, \DateTimeInterface $now)
    {
        $slotQuery = new \BO\Zmsdb\Slot();
        $updatedList = $slotQuery->writeByScope($scope, $now);
        foreach ($updatedList as $availability) {
            $this->log("Updated $availability with reason " . implode('|', $availability['processingNote']));
        }
        if (count($updatedList)) {
            \BO\Zmsdb\Connection\Select::writeCommit();
            $this->readLastRun();
            return true;
        }
        return false;
    }

    public function writeCanceledSlots(\DateTimeInterface $now, $modify = '+10 minutes')
    {
        \BO\Zmsdb\Connection\Select::getWriteConnection();
        $slotQuery = new \BO\Zmsdb\Slot();
        if ($slotQuery->writeCanceledByTime($now->modify($modify))) {
            \BO\Zmsdb\Connection\Select::writeCommit();
            $this->log("Cancelled slots older than ".$now->modify($modify)->format('c'));
            return true;
        }
        return false;
    }

    public function deleteOldSlots(\DateTimeInterface $now)
    {
        $slotQuery = new \BO\Zmsdb\Slot();
        $pdo = \BO\Zmsdb\Connection\Select::getWriteConnection();
        $pdo->exec('SET SESSION innodb_lock_wait_timeout=120');
        if ($slotQuery->deleteSlotsOlderThan($now)) {
            \BO\Zmsdb\Connection\Select::writeCommit();
            $this->log("Deleted slots older than ". $now->format('Y-m-d'));
        }
    }
}
