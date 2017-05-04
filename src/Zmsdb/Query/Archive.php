<?php

namespace BO\Zmsdb\Query;

/**
*
* @SuppressWarnings(TooManyPublicMethods)
* @SuppressWarnings(Complexity)
 */
class Archive extends Base implements MappingInterface
{

    /**
     *
     * @var String TABLE mysql table reference
     */
    const TABLE = 'buergerarchiv';

    public function getEntityMapping()
    {
        return [
            'id' => 'archive.BuergerarchivID',
            'date' => 'archive.Datum',
            'scope__id' => 'archive.StandortID',
            'clientsCount' => 'archive.AnzahlPersonen',
            'waitingTime' => 'archive.wartezeit',
            'createTimestamp' => 'archive.Timestamp',
            'missed' => 'archive.nicht_erschienen',
            'withAppointment' => 'archive.mitTermin'
        ];
    }

    public function addConditionArchiveId($archiveId)
    {
        $this->query->where('archive.BuergerarchivID', '=', $archiveId);
        return $this;
    }

    public function addConditionScopeId($scopeId)
    {
        $this->query->where('archive.StandortID', '=', $scopeId);
        return $this;
    }

    public function addConditionTime($now)
    {
        $this->query->where('archive.Datum', '=', $now->format('Y-m-d'));
        return $this;
    }

    public function addConditionIsMissed($missed)
    {
        $this->query->where('archive.nicht_erschienen', '=', $missed);
        return $this;
    }

    public function addConditionWithAppointment($withAppointment)
    {
        $this->query->where('archive.mitTermin', '=', $withAppointment);
        return $this;
    }

    public function addValuesNewArchive(\BO\Zmsentities\Process $process, \DateTimeInterface $now)
    {
        $appointment = $process->getFirstAppointment();
        $datetime = $appointment->toDateTime();
        $this->addValues([
            'StandortID' => $process->scope['id'],
            'Datum' => $datetime->format('Y-m-d'),
            'mitTermin' => $process->queue['withAppointment'],
            'nicht_erschienen' => ('missed' == $process->status) ? 1 : 0,
            'Timestamp' => $now->format('H:i:s'),
            'wartezeit' => (1 == $process->queue['withAppointment']) ? $process->queue['waitingTime'] : null,
            'AnzahlPersonen' => $process->getClients()->count()
        ]);
    }

    public function postArchive($data)
    {
        $data["date"] = strtotime($data["date"]);
        return $data;
    }
}
