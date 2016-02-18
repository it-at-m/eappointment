<?php

namespace BO\Zmsdb\Query;

class Process extends Base implements MappingInterface
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'buerger';

    public function getEntityMapping()
    {
        return [
            'amendment' => 'process.Anmerkung',
            'id' => 'process.BuergerID',
            'appointment__date' => self::expression('unix_timestamp(CONCAT(`process`.`Datum`, " ", `process`.`Uhrzeit`))'),
            'appointment__scope__id' => 'process.StandortID',
            'appointment__slotCount' => 'process.hatFolgetermine',
            'authKey' => 'process.absagecode',
            'client__email' => 'process.EMail',
            'client__emailSendCount' => 'process.EMailverschickt',
            'client__familyName' => 'process.Name',
            'client__notificationsSendCount' => 'process.SMSverschickt',
            'client__surveyAccepted' => 'process.zustimmung_kundenbefragung',
            'client__telphone' => 'process.telefonnummer_fuer_rueckfragen',
            'createIP' => 'process.IPAdresse',
            'createTimestamp' => 'process.IPTimeStamp',
            'queue__arrivalTime' => 'process.wsm_aufnahmezeit',
            'queue__callCount' => 'process.AnzahlAufrufe',
            'queue__callTime' => 'process.aufrufzeit',
            'queue__number' => 'process.wartenummer',
            'queue__waitingTime' => 'process.wartezeit',
            'queue__reminderTimestamp' => 'process.Erinnerungszeitpunkt',
            'workstation__id' => 'process.NutzerID'
        ];
    }

    public function addConditionProcessId($processId)
    {
        $this->query->where('process.BuergerID', '=', $processId);
        return $this;
    }

    public function addConditionAuthKey($authKey)
    {
        if('NULL' != $authKey){
            $this->query->where('process.absagecode', '=', $authKey);
        }
        else {
            $this->query->where('process.absagecode', 'IS', NULL);
        }
        return $this;
    }

    public function addInsertValues($values)
    {
        $this->query->values($values);
        return $this;
    }

}
