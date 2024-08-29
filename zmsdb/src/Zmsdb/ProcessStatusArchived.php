<?php
namespace BO\Zmsdb;

use \BO\Zmsentities\Processarchived as Entity;
use \BO\Zmsentities\Collection\ProcessList as Collection;

/**
 *
 */
class ProcessStatusArchived extends Process
{
    public function readArchivedEntity($archiveId, $resolveReferences = 0)
    {
        if (!$archiveId) {
            return null;
        }
        $query = new Query\ProcessStatusArchived(Query\Base::SELECT);
        $query->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionArchiveId($archiveId);
        $archive = $this->fetchOne($query, new Entity());
        $archive = $this->readResolvedReferences($archive, $resolveReferences);
        return $archive;
    }

    public function readListByScopeId($scopeId, $resolveReferences = 0)
    {
        $query = new Query\ProcessStatusArchived(Query\Base::SELECT);
        $query->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionScopeId($scopeId);
        return $this->readResolvedList($query, $resolveReferences);
    }

    public function readListByDate($dateTime, $resolveReferences = 0)
    {
        $query = new Query\ProcessStatusArchived(Query\Base::SELECT);
        $query->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionTime($dateTime);
        return $this->readResolvedList($query, $resolveReferences);
    }

    public function readListByScopeAndDate($scopeId, $dateTime, $resolveReferences = 0)
    {
        $query = new Query\ProcessStatusArchived(Query\Base::SELECT);
        $query->addEntityMapping()
            ->addConditionScopeId($scopeId)
            ->addResolvedReferences($resolveReferences)
            ->addConditionTime($dateTime);
        return $this->readResolvedList($query, $resolveReferences);
    }

    public function readListByScopesAndDates($scopeIds, $dateTimes, $resolveReferences = 0)
    {
        $query = new Query\ProcessStatusArchived(Query\Base::SELECT);
        $query->addEntityMapping()
            ->addConditionScopeIds($scopeIds)
            ->addResolvedReferences($resolveReferences)
            ->addConditionTimes($dateTimes);

        return $this->readResolvedList($query, $resolveReferences);
    }

    public function readListForStatistic($dateTime, \BO\Zmsentities\Scope $scope, $limit = 500, $resolveReferences = 0)
    {
        $query = new Query\ProcessStatusArchived(Query\Base::SELECT);
        $query->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addJoinStatisticFailed($dateTime, $scope)
            ->addLimit($limit);
        return $this->readResolvedList($query, $resolveReferences);
    }

    public function readListIsMissed($isMissed = 1, $resolveReferences = 0)
    {
        $query = new Query\ProcessStatusArchived(Query\Base::SELECT);
        $query->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionIsMissed($isMissed);
        return $this->readResolvedList($query, $resolveReferences);
    }

    public function readListWithAppointment($withAppointment = 1, $resolveReferences = 0)
    {
        $query = new Query\ProcessStatusArchived(Query\Base::SELECT);
        $query->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionWithAppointment($withAppointment);
        return $this->readResolvedList($query, $resolveReferences);
    }

    protected function readResolvedList($query, $resolveReferences)
    {
        $processList = new Collection();
        $resultList = $this->fetchList($query, new Entity());
        if (count($resultList)) {
            foreach ($resultList as $entity) {
                if (0 == $resolveReferences) {
                    $processList->addEntity($entity);
                } else {
                    if ($entity instanceof Entity) {
                        $entity = $this->readResolvedReferences($entity, $resolveReferences);
                        $processList->addEntity($entity);
                    }
                }
            }
        }
        return $processList;
    }

    public function writeEntityFinished(
        \BO\Zmsentities\Process $process,
        \DateTimeInterface $now,
        bool $calculateStatistic = false
    ) {
        $process = $this->updateEntity($process, $now, 1);
        $archived = null;
        if ($this->writeBlockedEntity($process)) {
            $archived = $this->writeNewArchivedProcess($process, $now, 0, $calculateStatistic);
        }
        // update xRequest entry and update process id as well as archived id
        if ($archived) {
            $this->writeXRequestsArchived($process->id, $archived->archiveId);
        }
        return $archived;
    }

    /**
     * write an archived process to statistic table
     *
     */
    public function writeArchivedProcessToStatistic(
        Entity $process,
        $requestId,
        $clusterId,
        $providerId,
        $departmentId,
        $organisationId,
        $ownerId,
        $dateTime,
        $processingTime
    ) {
        return $this->perform(
            Query\ProcessStatusArchived::QUERY_INSERT_IN_STATISTIC,
            [
                'archiveId' => $process->archiveId,
                'scopeId' => $process->scope->getId(),
                'clusterId' => $clusterId,
                'providerId' => $providerId,
                'departmentId' => $departmentId,
                'organisationId' => $organisationId,
                'ownerId' => $ownerId,
                'date' => $process->getFirstAppointment()->toDateTime()->format('Y-m-d'),
                'withAppointment' => ($process->toQueue($dateTime)->withAppointment) ? 1 : 0,
                'requestId' => $requestId,
                'processingTime' => $processingTime
            ]
        );
    }

    /**
     * write a new archived process to DB
     *
     */
    public function writeNewArchivedProcess(
        \BO\Zmsentities\Process $process,
        \DateTimeInterface $now,
        $resolveReferences = 0,
        bool $calculateStatistic = false
    ) {
        $query = new Query\ProcessStatusArchived(Query\Base::INSERT);
        $query->addValuesNewArchive($process, $now);
        $this->writeItem($query);
        $archiveId = $this->getWriter()->lastInsertId();
        Log::writeLogEntry("ARCHIVE (Archive::writeNewArchivedProcess) $archiveId -> $process ",
            $process->id,
            Log::PROCESS,
            $process->getScopeId()
        );

        if ($calculateStatistic) {
            (new ExchangeWaitingscope())->updateWaitingStatistics($process, $now);
        }
        
        return $this->readArchivedEntity($archiveId, $resolveReferences);
    }

    protected function writeXRequestsArchived($processId, $archiveId)
    {
        $query = new Query\XRequest(Query\Base::UPDATE);
        $query->addConditionProcessId($processId);
        $query->addValues([
            'BuergerID' => 0,
            'BuergerarchivID' => $archiveId
        ]);
        $this->writeItem($query);
    }

    /**
     * Anonymizes names in the buergerarchiv table for entries older than a specified timespan.
     *
     * @param \DateTimeInterface $dateTime The date before which records should be anonymized.
     * @return bool Indicates whether the update operation was successful.
     */
    public function anonymizeNames(\DateTimeInterface $dateTime)
    {
        $query = new Query\ProcessStatusArchived(Query\Base::UPDATE);
        $query->addConditionOlderThanDate($dateTime);  
        $query->addValues([
            'name' => 'ANONYMIZED'
        ]);
    
        return $this->writeItem($query);
    }
        
}
