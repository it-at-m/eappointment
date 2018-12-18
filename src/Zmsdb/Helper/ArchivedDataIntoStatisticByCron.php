<?php

namespace BO\Zmsdb\Helper;

/**
 * @codeCoverageIgnore
 */
class ArchivedDataIntoStatisticByCron
{
    protected $verbose = false;

    protected $limit = 10000;
    
    protected $loopCount = 500;

    protected $query;

    public function __construct($limit = null, $verbose = false)
    {
        if ($verbose) {
            error_log("INFO: Insert archived waiting, request and client data into statisik table");
            $this->verbose = true;
        }
        $this->limit = ($limit) ? $limit : $this->limit;
        $this->query = new \BO\Zmsdb\ProcessStatusArchived();
    }

    public function startProcessing(\DateTimeImmutable $dateTime, $commit = false)
    {
        $processCount = $this->loopCount;
        while ($processCount < $this->limit) {
            $processList = $this->query->readListForStatistic($dateTime, $this->loopCount);
            if (0 == $processList->count()) {
                break;
            }
            foreach ($processList as $process) {
                if ($this->verbose) {
                    error_log("INFO: Writing archived process list into statistic table");
                }
                if ($commit) {
                    $this->writeProcessInStatisticTable($process, $dateTime);
                }
                $processCount++;
            }
        }
    }

    protected function writeProcessInStatisticTable($process, $dateTime)
    {
        $scope = (new \BO\Zmsdb\Scope())->readEntity($process->scope->getId());
        $cluster = (new \BO\Zmsdb\Cluster())->readByScopeId($process->scope->getId());
        $department = (new \BO\Zmsdb\Department())->readByScopeId($scope->getId());
        if ($department) {
            $organisation = (new \BO\Zmsdb\Organisation())->readByDepartmentId($department->getId());
            $owner = (new \BO\Zmsdb\Owner())->readByOrganisationId($organisation->getId());
        } else {
            $department = new \BO\Zmsentities\Department();
            $organisation = new \BO\Zmsentities\Organisation();
            $owner = new \BO\Zmsentities\Owner();
        }
        $requestList = (new \BO\Zmsdb\Request())->readRequestByArchiveId($process->archiveId);
        $requestList = ($requestList->count()) ? $requestList : [new \BO\Zmsentities\Request(['id' => '-1'])];
        foreach ($requestList as $request) {
            $archived = $this->query->writeArchivedProcessToStatistic(
                $process,
                $request->getId(),
                $cluster ? $cluster->getId() : null,
                $scope->getProviderId(),
                $department->getId(),
                $organisation->getId(),
                $owner->getId(),
                $dateTime
            );
            if ($archived && $this->verbose) {
                error_log("INFO: Process {$process->archiveId} with request {$request->getId()} successfully archived");
            } else {
                error_log("WARN: Could not archive process {$process->archiveId} with request {$request->getId()}!");
            }
        }
    }
}
