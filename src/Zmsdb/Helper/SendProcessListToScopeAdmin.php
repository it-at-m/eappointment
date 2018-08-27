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
            error_log("INFO: Send process list of current day to scope admin");
            $this->verbose = true;
        }
        if ($scopeId) {
            $scope = (new \BO\Zmsdb\Scope())->readEntity($scopeId);
            $this->scopeList = (new \BO\Zmsentities\Collection\ScopeList())->addEntity($scope);
        } else {
            $this->scopeList = (new \BO\Zmsdb\Scope)->readListWithScopeAdminEmail(1);
        }
    }

    public function startProcessing($commit)
    {
        foreach ($this->scopeList as $scope) {
            if ($this->verbose) {
                error_log("INFO: Processing $scope");
            }
            if ($commit) {
                $processList = (new \BO\Zmsdb\Process)
                    ->readProcessListByScopeAndTime($scope->getId(), $this->dateTime, 1);
                $processList = $processList
                   ->toQueueList($this->dateTime)
                   ->withStatus(array('confirmed', 'queued', 'reserved'))
                   ->withSortedArrival()
                   ->toProcessList();
                if (0 <= $processList->count()) {
                    if ($this->sendListToQueue($scope, $processList) && $this->verbose) {
                        error_log('INFO: Send processList to:'. $scope->getContactEmail());
                    }
                } else {
                    error_log("WARNING: Processlist empty for $scope->id");
                }
            }
        }
    }

    protected function sendListToQueue($scope, $processList)
    {
        $entity = (new \BO\Zmsentities\Mail)->toScopeAdminProcessList($processList, $scope, $this->dateTime);
        if (! (new \BO\Zmsdb\Mail)->writeInQueueWithDailyProcessList($scope, $entity)) {
            error_log("WARNING: Mail writing in queue not successfull empty!");
        }
    }
}
