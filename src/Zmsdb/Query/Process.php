<?php
namespace BO\Zmsdb\Query;

class Process extends Base implements MappingInterface
{

    /**
     *
     * @var String TABLE mysql table reference
     */
    const TABLE = 'buerger';

    const QUERY_DELETE = "UPDATE `buerger` process LEFT JOIN `standort` s USING(StandortID)
        SET
            process.`Anmerkung` = CONCAT(
                'Abgesagter Termin gebucht am: ', FROM_UNIXTIME(`IPTimeStamp`) ,' | ', `Anmerkung`
            ),
            process.`Name` = '(abgesagt)',
            process.`IPadresse` = '',
            process.`IPTimeStamp` = UNIX_TIMESTAMP() + (s.loeschdauer * 60),
            process.`vorlaeufigeBuchung` = 1
        WHERE
            (process.BuergerID = ? AND process.absagecode = ?)
            OR process.istFolgeterminvon = ?
        ";
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

        return [$scopeQuery];
    }

    /**
     * TODO: Check if necessary, the slot list is built on the calendar
     */
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
        $providerSlots = $this->getReader()->fetchAll(
            $query,
            [
            'request_id' => $entity->id
            ]
        );
        return $providerSlots;
    }

    public function getEntityMapping()
    {
        return [
            'amendment' => 'process.Anmerkung',
            'id' => 'process.BuergerID',
            'appointment__date' => self::expression(
                'unix_timestamp(CONCAT(`process`.`Datum`, " ", `process`.`Uhrzeit`))'
            ),
            'appointment__scope__id' => 'process.StandortID',
            'appointment__slotCount' => 'process.hatFolgetermine',
            'authKey' => 'process.absagecode',
            'client__email' => 'process.EMail',
            'client__emailSendCount' => 'process.EMailverschickt',
            'client__familyName' => 'process.Name',
            'client__notificationsSendCount' => 'process.SMSverschickt',
            'client__surveyAccepted' => 'process.zustimmung_kundenbefragung',
            'client__telephone' => 'process.telefonnummer_fuer_rueckfragen',
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
            $this->query->where('process.absagecode', 'IS', null);
        }
        return $this;
    }

    public function reverseEntityMapping(\BO\Zmsentities\Process $process)
    {
        $data = array();
        $data['Anmerkung'] = $this->setValue('amendment', $process);
        $data['StandortID'] = $this->setValue('scope__id', $process);
        $data['absagecode'] = $this->setValue('authKey', $process);
        $data['Datum'] = $this->setValue(
            'appointments__|date',
            $process,
            date('Y-m-d', $process['appointments'][0]['date'])
        );
        $data['Uhrzeit'] = $this->setValue(
            'appointments__|date',
            $process,
            date('H:i', $process['appointments'][0]['date'])
        );

        $data['Name'] = $this->setValue('clients__|familyName', $process);
        $data['EMail'] = $this->setValue('clients__|email', $process);
        $data['telefonnummer_fuer_rueckfragen'] = $this->setValue('clients__|telephone', $process);
        $data['zustimmung_kundenbefragung'] = $this->setValue('clients__|surveyAccepted', $process);
        $data['EMailverschickt'] = $this->setValue('clients__|emailSendCount', $process);
        $data['SMSverschickt'] = $this->setValue('clients__|notificationsSendCount', $process);

        if ($process['status'] == 'reserved') {
            $data['vorlaeufigeBuchung'] = $this->setValue('status', $process, 1);
        }

        return array_filter($data);
    }

    public function setValue($property, $process, $customvalue = null)
    {
        $value = null;
        if (strpos($property, '__')) {
            list($subkey, $newkey) = explode('__', $property, 2);
            if (strpos($newkey, '|') > -1) {
                $newkey = str_replace('|', '', $newkey);
                if (array_key_exists($newkey, $process[$subkey][0])) {
                    $value = (null === $customvalue) ? $process[$subkey][0][$newkey] : $customvalue;
                }
            } else {
                if (array_key_exists($newkey, $process[$subkey])) {
                    $value = (null === $customvalue) ? $process[$subkey][$newkey] : $customvalue;
                }
            }

        } else {
            if (array_key_exists($property, $process)) {
                $value = (null === $customvalue) ? $process[$property] : $customvalue;
            }
        }

        \App::$log->debug('value: ', [$value]);
        return $value;
    }
}
