<?php

namespace BO\Zmsdb\Helper;

/**
 * @codeCoverageIgnore
 */
class ArchivedDataIntoStatisticByCron
{
    protected $verbose = false;

    protected $limit = 1000;

    protected $query;

    protected $timespan = "-7days";

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
        $scopeList = (new \BO\Zmsdb\Scope())->readList(0);
        $dateTime = $dateTime->modify($this->timespan);
        foreach ($scopeList as $scope) {
            $processList = $this->query->readListForStatistic($dateTime, $scope, $this->limit);
            if (count($processList)) {
                $cluster = (new \BO\Zmsdb\Cluster())->readByScopeId($scope->getId());
                $department = (new \BO\Zmsdb\Department())->readByScopeId($scope->getId());
                if ($department) {
                    $organisation = (new \BO\Zmsdb\Organisation())->readByDepartmentId($department->getId());
                    $owner = (new \BO\Zmsdb\Owner())->readByOrganisationId($organisation->getId());
                } else {
                    $department = new \BO\Zmsentities\Department();
                    $organisation = new \BO\Zmsentities\Organisation();
                    $owner = new \BO\Zmsentities\Owner();
                }
                foreach ($processList as $process) {
                    $this->writeProcessInStatisticTable(
                        $process,
                        $scope,
                        $cluster,
                        $department,
                        $organisation,
                        $owner,
                        $dateTime,
                        $commit
                    );
                }
            } elseif ($this->verbose) {
                error_log("INFO: No changes for scope $scope");
            }
        }
    }

    protected function writeProcessInStatisticTable(
        $process,
        $scope,
        $cluster,
        $department,
        $organisation,
        $owner,
        $dateTime,
        $commit = false
    ) {
        $requestList = (new \BO\Zmsdb\Request())->readRequestByArchiveId($process->archiveId);
        $requestList = ($requestList->count()) ? $requestList : [new \BO\Zmsentities\Request(['id' => '-1'])];
        foreach ($requestList as $request) {
            $archived = true; // for verbose
            if ($commit) {
                $archived = $this->query->writeArchivedProcessToStatistic(
                    $process,
                    $request->getId(),
                    $cluster ? $cluster->getId() : 0,
                    $scope->getProviderId(),
                    $department->getId(),
                    $organisation->getId(),
                    $owner->getId(),
                    $dateTime
                );
            }
            if ($archived && $this->verbose) {
                $processDate = $process->getFirstAppointment()->toDateTime()->format('Y-m-d');
                error_log(
                    "INFO: Process {$process->archiveId} with request {$request->getId()}"
                    ." for scope {$scope->getId()} archived on $processDate"
                );
            } else {
                error_log("WARN: Could not archive process {$process->archiveId} with request {$request->getId()}!");
            }
        }
    }
}
