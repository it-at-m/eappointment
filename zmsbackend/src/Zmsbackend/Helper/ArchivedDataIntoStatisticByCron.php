<?php

namespace BO\Zmsbackend\Helper;

/**
 * @codeCoverageIgnore
 */
class ArchivedDataIntoStatisticByCron
{
    use VerboseCronLogTrait;

    protected $verbose = false;

    protected $limit = 1000;

    protected $query;

    protected $timespan = "-7days";

    protected $archivedList = [];

    public function __construct($limit = null, $verbose = false)
    {
        if ($verbose) {
            $this->verbose = true;
        }
        $this->logMessage("INFO: Insert archived waiting, request and client data into statisik table");
        $this->limit = ($limit) ? $limit : $this->limit;
        $this->query = new \BO\Zmsbackend\Process\Service\ProcessStatusArchived();
    }

    public function startProcessing(\DateTimeImmutable $dateTime, bool $commit = false): void
    {
        $scopeList = (new \BO\Zmsbackend\Scope\Service\Scope())->readList(0);
        $dateTime = $dateTime->modify($this->timespan);
        foreach ($scopeList as $scope) {
            $this->logMessage("INFO: Processing $scope");
            $processList = $this->query->readListForStatistic($dateTime, $scope, $this->limit);
            if ($processList->count()) {
                $this->logMessage("INFO: " . count($processList) . " processes for $scope");
                $cluster = (new \BO\Zmsbackend\Cluster\Service\Cluster())->readByScopeId($scope->getId());
                $department = (new \BO\Zmsbackend\Department\Service\Department())->readByScopeId($scope->getId());
                if ($department) {
                    $organisation = (new \BO\Zmsbackend\Organisation\Service\Organisation())->readByDepartmentId($department->getId());
                    $owner = (new \BO\Zmsbackend\Owner\Service\Owner())->readByOrganisationId($organisation->getId());
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
            } else {
                $this->logMessage("INFO: No changes for scope $scope");
            }
        }
        $this->logMessage("\nSUMMARY: number of archived processes: " . count($this->archivedList));
    }

    public function getArchivedList()
    {
        return $this->archivedList;
    }

    protected function logMessage(string $message, string $level = 'info'): void
    {
        $this->writeVerboseCronLog($message, $level);
    }

    protected function writeProcessInStatisticTable(
        $process,
        $scope,
        $cluster,
        $department,
        $organisation,
        $owner,
        \DateTimeImmutable|false $dateTime,
        bool $commit = false
    ): void {
        $requestIds = (new \BO\Zmsbackend\Request\Service\Request())
            ->readRequestIdsByArchiveId($process->archiveId);
        if (!count($requestIds)) {
            // unsigned buergeranliegen.AnliegenID cannot store -1 ("ohne Erfassung")
            $requestIds = [-1];
        }
        $processingTime = count($requestIds) === 1 ? $process->processingTime : null;

        foreach ($requestIds as $requestId) {
            $archived = true; // for verbose
            if ($commit) {
                $archived = $this->query->writeArchivedProcessToStatistic(
                    $process,
                    $requestId,
                    $cluster ? $cluster->getId() : 0,
                    $scope->toProperty()->provider->id->get(0),
                    $department->getId(),
                    $organisation->getId(),
                    $owner->getId(),
                    $dateTime,
                    $processingTime
                );
            }
            if ($archived) {
                $this->archivedList['scope_' . $scope->getId()][] = $process->archiveId;
                $processDate = $process->getFirstAppointment()->toDateTime()->format('Y-m-d');
                $this->logMessage(
                    "INFO: Process {$process->archiveId} with request {$requestId}"
                    . " for scope {$scope->getId()} archived on $processDate"
                );
            } else {
                $this->logMessage(
                    "WARN: Could not archive process {$process->archiveId} with request {$requestId}!"
                );
            }
        }
    }
}
