<?php
namespace BO\Zmsdb\Query;

class Process extends Base implements MappingInterface
{

    /**
     *
     * @var String TABLE mysql table reference
     */
    const TABLE = 'buerger';

    public function addJoin()
    {
        $this->query->leftJoin(
            new Alias(Scope::TABLE, 'scope'),
            'process.StandortID',
            '=',
            'scope.StandortID'
            );
        $scopeQuery = new Scope($this->query);
        $scopeQuery->addEntityMappingPrefixed('scope__');

        $this->query->leftJoin(
            new Alias(Provider::TABLE, 'provider'),
            'scope.InfoDienstleisterID',
            '=',
            'provider.id'
        );
        $providerQuery = new Provider($this->query);
        $providerQuery->addEntityMappingPrefixed('scope__provider__');

        $this->query->leftJoin(
            new Alias('buergeranliegen', 'xrequest'),
            'process.BuergerID',
            '=',
            'xrequest.BuergerID'
            );
        $this->query->leftJoin(
            new Alias(REQUEST::TABLE, 'request'),
            'request.id',
            '=',
            'xrequest.AnliegenID'
            );
        $requestQuery = new Request($this->query);
        $requestQuery->addEntityMappingPrefixed('requests__');
        return [$scopeQuery];
    }

    public function readRequestOnEntity(\BO\Zmsentities\Request $entity)
    {
        $query = 'SELECT
                x.`dienstleister` AS provider__id,
                x.`slots`
            FROM `startinfo`.`xdienst` x
                LEFT JOIN `startinfo`.`dienstleister` d ON x.dienstleister = d.id
            WHERE
                x.`dienstleistung` = :request_id
                AND x.`termin_hide` = 0
                AND d.`zms_termin` = 1
        ';
        $providerSlots = $this->getReader()->fetchAll($query, [
            'request_id' => $entity->id
        ]);
        return $providerSlots;
    }

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
            'workstation__id' => 'process.NutzerID',
        ];
    }

    public function addConditionProcessId($processId)
    {
        $this->query->where('process.BuergerID', '=', $processId);
        return $this;
    }

    public function addConditionAuthKey($authKey)
    {
        if (! empty($authKey)) {
            $this->query->where('process.absagecode', '=', $authKey);
        } else {
            $this->query->where('process.absagecode', 'IS', NULL);
        }
        return $this;
    }

    public function reverseEntityMapping($processData)
    {
        $data = array();
        if ($this->hasKey($processData, 'amendment')){
            $data['Anmerkung'] = $processData['amendment'];
        }
        if ($this->hasKey($processData['appointments'][0], 'date')){
            $data['Datum'] = date('Y-m-d', $processData['appointments'][0]['date']);
            $data['Uhrzeit'] = date('H:i', $processData['appointments'][0]['date']);
        }
        if ($this->hasKey($processData, 'scope')){
            $data['StandortID'] = $processData['scope']['id'];
        }
        if ($this->hasKey($processData, 'authKey')){
            $data['absagecode'] = $processData['authKey'];
        }
        if ($this->hasKey($processData, 'status')){
            if($processData['status'] == 'reserved'){
                $data['vorlaeufigeBuchung'] = 1;
            }

        }
        return $data;
            /*
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
            'workstation__id' => 'process.NutzerID',
            */
    }

    public function hasKey($array, $value)
    {
        if (array_key_exists($value, $array)) {
            return true;
        }
        return false;
    }
}
