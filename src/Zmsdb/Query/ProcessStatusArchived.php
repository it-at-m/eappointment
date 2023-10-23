<?php

namespace BO\Zmsdb\Query;

/**
*
* @SuppressWarnings(TooManyPublicMethods)
* @SuppressWarnings(Complexity)
 */
class ProcessStatusArchived extends Base implements MappingInterface
{

    /**
     *
     * @var String TABLE mysql table reference
     */
    const TABLE = 'buergerarchiv';
    const STATISTIC_TABLE = 'statistik';
    const ALIAS = 'process';

    const QUERY_INSERT_IN_STATISTIC = '
        INSERT INTO '. self::STATISTIC_TABLE .' SET
            lastbuergerarchivid = :archiveId,
            termin = :withAppointment,
            datum = :date,
            anliegenid = :requestId,
            info_dl_id = :providerId,
            standortid = :scopeId,
            clusterid = :clusterId,
            behoerdenid = :departmentId,
            organisationsid = :organisationId,
            kundenid = :ownerId,
            bearbeitungszeit = :processingTime
    ';

    public function getEntityMapping()
    {
        return [
            'archiveId' => 'process.BuergerarchivID',
            'status' => self::expression('"archived"'),
            'appointments__0__date' => self::expression(
                'CONCAT(`process`.`Datum`, " ", `process`.`Timestamp`)'
            ),
            'scope__id' => 'process.StandortID',
            '__clientsCount' => 'process.AnzahlPersonen',
            'waitingTime' => 'process.wartezeit',
            'bearbeitungszeit' => 'process.bearbeitungszeit',
            'queue__arrivalTime' => self::expression(
                'CONCAT(`process`.`Datum`, " 00:00:00")'
            ),
            'queue__callTime' => self::expression(
                'CONCAT(`process`.`Datum`, " ", SEC_TO_TIME(`wartezeit`))'
            ),
            'queue__withAppointment' => 'process.mitTermin',
            'queue__status' => self::expression(
                'IF(`process`.`nicht_erschienen`,
                    "missed",
                    "finished"
                )'
            ),
        ];
    }

    public function addConditionArchiveId($archiveId)
    {
        $this->query->where('process.BuergerarchivID', '=', $archiveId);
        return $this;
    }

    public function addConditionScopeId($scopeId)
    {
        $this->query->where('process.StandortID', '=', $scopeId);
        return $this;
    }

    public function addConditionTime(\DateTimeInterface $now)
    {
        $this->query->where('process.Datum', '=', $now->format('Y-m-d'));
        return $this;
    }

    public function addConditionIsMissed($missed)
    {
        $this->query->where('process.nicht_erschienen', '=', $missed);
        return $this;
    }

    public function addConditionWithAppointment($withAppointment)
    {
        $this->query->where('process.mitTermin', '=', $withAppointment);
        return $this;
    }

    public function addJoinStatisticFailed($dateTime, \BO\Zmsentities\Scope $scope)
    {
        //use existing index with StandortID and Datum
        $this->leftJoin(
            new Alias(self::STATISTIC_TABLE, 'statistic'),
            self::expression('
                statistic.StandortID = process.StandortID
                AND statistic.Datum = process.Datum
                AND `statistic`.`lastbuergerarchivid` = `process`.`BuergerarchivID`
            ')
        );
        $this->query->where(function (\Solution10\SQL\ConditionBuilder $query) use ($dateTime, $scope) {
            $query->andWith(
                self::expression('statistic.lastbuergerarchivid IS NULL AND process.Datum'),
                '>',
                $dateTime->format('Y-m-d')
            );
            $query->andWith('process.nicht_erschienen', '=', 0);
            $query->andWith('process.StandortID', '=', $scope->id);
        });
        return $this;
    }

    public function addValuesNewArchive(\BO\Zmsentities\Process $process, \DateTimeInterface $now)
    {
        $this->addValues([
            'StandortID' => $process->scope['id'],
            'Datum' => $process->getFirstAppointment()->toDateTime()->format('Y-m-d'),
            'mitTermin' => ($process->toQueue($now)->withAppointment) ? 1 : 0,
            'nicht_erschienen' => ('missed' == $process->queue['status']) ? 1 : 0,
            'Timestamp' => $now->format('H:i:s'),
            'wartezeit' => ($process->getWaitedSeconds() > 0) ? $process->getWaitedMinutes() : 0,
            'bearbeitungszeit' => $process->finishTime
                ? floor(
                    (
                        (new \DateTime($process->finishTime))->getTimestamp()
                        - (new \DateTime($process->showUpTime))->getTimestamp()
                    ) / 60)
                : null,
            'AnzahlPersonen' => $process->getClients()->count()
        ]);
    }

    public function postProcess($data)
    {
        $data[$this->getPrefixed("appointments__0__date")] =
            strtotime($data[$this->getPrefixed("appointments__0__date")]);
        $data[$this->getPrefixed("queue__callTime")] =
            strtotime($data[$this->getPrefixed("queue__callTime")]);
        $data[$this->getPrefixed("queue__arrivalTime")] =
            strtotime($data[$this->getPrefixed("queue__arrivalTime")]);
        if (isset($data[$this->getPrefixed('__clientsCount')])) {
            $clientsCount = $data[$this->getPrefixed('__clientsCount')];
            unset($data[$this->getPrefixed('__clientsCount')]);
            while ($clientsCount-- > 0) {
                $data[$this->getPrefixed('clients__'.$clientsCount.'__familyName')] = 'Unknown';
            }
        }
        return $data;
    }
}
