<?php

namespace BO\Zmsdb\Helper;

/**
 * @codeCoverageIgnore
 */
class SendProcessListToScopeAdmin
{
    protected $scopeList;

    protected $datetime;

    protected $verbose = false;

    public function __construct($verbose = false, $scopeId = false)
    {
        $this->dateTime = new \DateTimeImmutable();
        if ($verbose) {
            $this->verbose = true;
            $this->log("INFO: Send process list of current day to scope admin");
        }
        if ($scopeId) {
            $scope = (new \BO\Zmsdb\Scope())->readEntity($scopeId);
            $this->scopeList = (new \BO\Zmsentities\Collection\ScopeList())->addEntity($scope);
        } else {
            $this->scopeList = (new \BO\Zmsdb\Scope())->readListWithScopeAdminEmail(1);
        }
    }

    public function startProcessing($commit)
    {
        foreach ($this->scopeList as $scope) {
            if ($this->verbose) {
                $this->log("INFO: Processing $scope");
            }
            if ($commit) {
                $processList = (new \BO\Zmsdb\Process())
                    ->readProcessListByScopeAndTime($scope->getId(), $this->dateTime, 1);
                $processList = $processList
                   ->toQueueList($this->dateTime)
                   ->withStatus(array('confirmed', 'queued', 'reserved'))
                   ->withSortedArrival()
                   ->toProcessList();
                if (0 <= $processList->count()) {
                    if ($this->sendListToQueue($scope, $processList) && $this->verbose) {
                        $this->log('INFO: Send processList to:' . $scope->getContactEmail());
                    }
                } else {
                    $this->log("WARNING: Processlist empty for $scope->id");
                }
            }
        }
    }

    protected function sendListToQueue($scope, $processList)
    {
        $entity = (new \BO\Zmsentities\Mail())->toScopeAdminProcessList($processList, $scope, $this->dateTime);
        if (! (new \BO\Zmsdb\Mail())->writeInQueueWithDailyProcessList($scope, $entity)) {
            $this->log("WARNING: Mail writing in queue not successfull empty!");
        }
    }

    protected function log($message)
    {
        if (!$this->verbose) {
            return;
        }
        $level = 'info';
        if (strpos($message, 'WARNING') === 0 || strpos($message, 'WARN') === 0) {
            $level = 'warning';
        } elseif (strpos($message, 'ERROR') === 0) {
            $level = 'error';
        }
        $trimmed = trim((string) $message);
        if (isset(\App::$log)) {
            \App::$log->{$level}($trimmed);
        } else {
            error_log($trimmed);
        }
    }
}
