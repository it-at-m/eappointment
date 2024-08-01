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
            'processingTime' => 'process.bearbeitungszeit',
            'name' => 'process.name',
            'services' => 'process.dienstleistungen',
            'queue__arrivalTime' => self::expression(
                'CONCAT(`process`.`Datum`, " 00:00:00")'
            ),
            'queue__callTime' => self::expression(
                'CONCAT(`process`.`Datum`, " ", SEC_TO_TIME(`wartezeit`))'
            ),
            'queue__withAppointment' => 'process.mitTermin',
            'withAppointment' => 'process.mitTermin',
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

    public function addConditionScopeIds($scopeIds)
    {
        $this->query->where(function (\Solution10\SQL\ConditionBuilder $condition) use ($scopeIds) {
            foreach ($scopeIds as $scopeId) {
                $condition
                    ->orWith('process.StandortID', '=', $scopeId);
            }
        });
        return $this;
    }


    public function addConditionTime(\DateTimeInterface $now)
    {
        $this->query->where('process.Datum', '=', $now->format('Y-m-d'));
        return $this;
    }

    public function addConditionTimes(array $dateTimes)
    {
        $this->query->where(function (\Solution10\SQL\ConditionBuilder $condition) use ($dateTimes) {
            foreach ($dateTimes as $dateTime) {
                $condition
                    ->orWith('process.Datum', '=', $dateTime->format('Y-m-d'));
            }
        });
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
        $processingTimeStr = $process->getProcessingTime();
        $bearbeitungszeit = null;

        if (!empty($processingTimeStr)) {
            list($hours, $minutes, $seconds) = explode(':', $processingTimeStr);
            $totalMinutes = (double) ($hours * 60 + $minutes + $seconds / 60);
            $bearbeitungszeit = $totalMinutes;
        }

        $waitingTimeStr = $process->getWaitingTime();
        $warteZeit = null;

        if (!empty($waitingTimeStr)) {
            list($hours, $minutes, $seconds) = explode(':', $waitingTimeStr);
            $totalMinutes = (double) ($hours * 60 + $minutes + $seconds / 60);
            $warteZeit = $totalMinutes;
        }
    
        $this->addValues([
            'StandortID' => $process->scope['id'],
            'name' => $process->getFirstClient()['familyName'],
            'dienstleistungen' => $this->getArchivedServices($process),
            'Datum' => $process->getFirstAppointment()->toDateTime()->format('Y-m-d'),
            'mitTermin' => ($process->toQueue($now)->withAppointment) ? 1 : 0,
            'nicht_erschienen' => ('missed' == $process->queue['status']) ? 1 : 0,
            'Timestamp' => $process->getArrivalTime()->format('H:i:s'),
            'bearbeitungszeit' => ($bearbeitungszeit > 0) ? $bearbeitungszeit : 0,
            'wartezeit' => ($warteZeit > 0) ? $warteZeit : 0,
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

    private function getArchivedServices(\BO\Zmsentities\Process $process)
    {
        if ($process->getRequests()->count() === 0) {
            return '';
        }
        
        $services = $process->getRequests()->getFirst()->name;

        if ($process->getRequests()->count() > 1) {
            $services .= ' +' . ($process->getRequests()->count() - 1);
        }

        return $services;
    }

    public function addConditionOlderThanDate(\DateTimeInterface $dateTime)
    {
        // Assuming 'Datum' is the column name that holds the date of the record
        // and you want to select records older than the specified $dateTime
        $this->query->where('Datum', '<', $dateTime->format('Y-m-d'));
        return $this;
    }
    
    
}
