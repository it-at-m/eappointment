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
            process.Anmerkung = CONCAT(
                'Abgesagter Termin gebucht am: ',
                FROM_UNIXTIME(process.IPTimeStamp,'%d-%m-%Y %H:%i'),' | ',
                IFNULL(process.Anmerkung,'')
            ),
            process.StandortId = 0,
            process.Name = '(abgesagt)',
            process.IPadresse = '',
            process.IPTimeStamp = FLOOR(UNIX_TIMESTAMP()) + (s.loeschdauer * 60),
            process.vorlaeufigeBuchung = 1
        WHERE
            (process.BuergerID = ? AND process.absagecode = ?)
            OR process.istFolgeterminvon = ?
        ";

    public function getQueryNewProcessId()
    {
        return 'SELECT A.`BuergerID`+1 AS `nextid`
            FROM `' . self::getTablename() . '` A
                LEFT JOIN `' . self::getTablename() . '` B ON A.BuergerID+1 = B.BuergerID
            WHERE B.`BuergerID` IS NULL AND A.`BuergerID` > 100000
            ORDER BY A.`BuergerID` LIMIT 1 FOR UPDATE';
    }

    public function getFirstSixDigitProcessId()
    {
        return 'SELECT * FROM `' . self::getTablename() . '` A
            WHERE A.`BuergerID` = 100000 FOR UPDATE';
    }

    public function addJoin()
    {
        return [
            $this->addJoinAvailability()
        ];
    }

    /**
     * Add Availability to the dataset
     */
    protected function addJoinAvailability()
    {
        $this->query->leftJoin(
            new Alias(Availability::TABLE, 'availability'),
            Availability::getJoinExpression('`process`', '`availability`')
        );
        $joinQuery = new Availability($this->query, 'appointments__0__availability__');
        return $joinQuery;
    }

    public function getEntityMapping()
    {
        return [
            'amendment' => 'process.Anmerkung',
            'id' => 'process.BuergerID',
            'appointments__0__date' => self::expression(
                'CONCAT(`process`.`Datum`, " ", `process`.`Uhrzeit`)'
            ),
            'scope__id' => 'process.StandortID',
            'appointments__0__scope__id' => 'process.StandortID',
            // 'appointments__0__slotCount' => 'process.hatFolgetermine',
            'appointments__0__slotCount' => self::expression('process.hatFolgetermine + 1'),
            'authKey' => 'process.absagecode',
            'clients__0__email' => 'process.EMail',
            'clients__0__emailSendCount' => 'process.EMailverschickt',
            'clients__0__familyName' => 'process.Name',
            'clients__0__notificationsSendCount' => 'process.SMSverschickt',
            'clients__0__surveyAccepted' => 'process.zustimmung_kundenbefragung',
            'clients__0__telephone' => self::expression(
                'IF(`process`.`telefonnummer_fuer_rueckfragen`,
                    `process`.`telefonnummer_fuer_rueckfragen`,
                    `process`.`Telefonnummer`
                )'
            ),
            'createIP' => 'process.IPAdresse',
            'createTimestamp' => 'process.IPTimeStamp',
            'queue__arrivalTime' => self::expression(
                'IF(`process`.`Uhrzeit`,
                    CONCAT(`process`.`Datum`, " ", `process`.`Uhrzeit`),
                    FROM_UNIXTIME(`process`.`IPTimeStamp`)
                )'
            ),
            'queue__callCount' => 'process.AnzahlAufrufe',
            'queue__callTime' => 'process.aufrufzeit',
            'queue__number' => self::expression(
                'IF(`process`.`wartenummer`,
                    `process`.`wartenummer`,
                    `process`.`BuergerID`
                )'
            ),
            'queue__destination' => 'processuser.Arbeitsplatznr',
            'queue__destinationHint' => 'processuser.aufrufzusatz',
            'queue__waitingTime' => 'process.wartezeit',
            'queue__withAppointment' => self::expression(
                'IF(`process`.`wartenummer`,
                    "0",
                    "1"
                )'
            ),
            'reminderTimestamp' => 'process.Erinnerungszeitpunkt.'
        ];
    }

    public function addConditionProcessId($processId)
    {
        $this->query->where('process.BuergerID', '=', $processId);
        return $this;
    }

    public function addConditionScopeId($scopeId)
    {
        $this->query->where('process.StandortID', '=', $scopeId);
        return $this;
    }

    public function addConditionQueueNumber($queueNumber)
    {
        $this->query->where('process.wartenummer', '=', $queueNumber);
        return $this;
    }

    public function addConditionTime($now)
    {
        $this->query->where('process.Datum', '=', $now->format('Y-m-d'));
        return $this;
    }

    public function addConditionAuthKey($authKey)
    {
        $authKey = urldecode($authKey);
        $this->query
            ->where(function (\Solution10\SQL\ConditionBuilder $condition) use ($authKey) {
                $condition
                    ->andWith('process.absagecode', '=', $authKey)
                    ->orWith('process.Name', '=', $authKey);
            });
        return $this;
    }

    public function addConditionIsReserved()
    {
        $this->query->where('process.name', 'NOT IN', array (
            'dereferenced',
            '(abgesagt)'
        ))
            ->where('process.vorlaeufigeBuchung', '=', 1)
            ->where('process.StandortID', '<>', 1)
            ->where('process.istFolgeterminvon', 'is', null);
        return $this;
    }

    public function addValuesUpdateProcess(\BO\Zmsentities\Process $process)
    {
        $data = array ();
        $data['Anmerkung'] = $process->getAmendment();
        $data['StandortID'] = $process->getScopeId();
        $appointment = $process->getFirstAppointment();
        if (null !== $appointment) {
            $datetime = $appointment->toDateTime();
            $data['Datum'] = $datetime->format('Y-m-d');
            $data['Uhrzeit'] = $datetime->format('H:i');
        }
        $client = $process->getFirstClient();
        if (null !== $client) {
            $data['Name'] = $client->familyName;
            $data['EMail'] = $client->email;
            $data['telefonnummer_fuer_rueckfragen'] = $client->telephone;
            $data['Telefonnummer'] = $client->telephone; // to stay compatible with ZMS1
            $data['zustimmung_kundenbefragung'] = $client->surveyAccepted;
            $data['EMailverschickt'] = $client->emailSendCount;
            $data['SMSverschickt'] = $client->notificationsSendCount;
        }
        $data['IPAdresse'] = $process['createIP'];
        $data['vorlaeufigeBuchung'] = ($process['status'] == 'reserved') ? 1 : 0;
        $data['Erinnerungszeitpunkt'] = $process->getReminderTimestamp();
        if ($process->queue['callTime']) {
            $data['aufrufzeit'] = (new \DateTime())->setTimestamp($process->queue['callTime'])->format('H:i:s');
        }
        $data = array_filter(
            $data,
            function ($value) {
                return ($value !== null && $value !== false && $value !== '');
            }
        );
        $this->addValues($data);
    }

    public function postProcess($data)
    {
        $data["appointments__0__date"] = strtotime($data["appointments__0__date"]);
        if ('00:00:00' != $data["queue__callTime"]) {
            $time = explode(':', $data["queue__callTime"]);
            $data["queue__callTime"] = (new \DateTime())
                ->setTimestamp($data["appointments__0__date"])
                ->setTime($time[0], $time[1], $time[2])
                ->getTimestamp();
        } else {
            $data["queue__callTime"] = 0;
        }
        $data["queue__arrivalTime"] = strtotime($data["queue__arrivalTime"]);
        return $data;
    }

    public function addValuesNewProcess(\BO\Zmsentities\Process $process, $parentProcess = 0, $childProcessCount = 0)
    {
        $this->addValues([
            'BuergerID' => $process->id,
            'IPTimeStamp' => $process->createTimestamp,
            'absagecode' => $process->authKey,
            'hatFolgetermine' => $childProcessCount,
            'istFolgeterminvon' => $parentProcess,
            'wartenummer' => $process->queue['number']
        ]);
    }

    protected function addRequiredJoins()
    {
        $this->query->leftJoin(
            new Alias(UserAccount::TABLE, 'processuser'),
            'process.StandortID',
            '=',
            'processuser.StandortID'
        );
    }
}
