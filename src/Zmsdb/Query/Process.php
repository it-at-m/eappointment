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

    const QUERY_SET_LOCK = "SELECT GET_LOCK('AutoIncWithOldNum', 2)";
    const QUERY_RELEASE_LOCK = "SELECT RELEASE_LOCK('AutoIncWithOldNum')";

    public function getQueryNewProcessId()
    {
        return 'SELECT A.`BuergerID`+1 AS `nextid`
            FROM `' . self::getTablename() . '` A
                LEFT JOIN `' . self::getTablename() . '` B ON A.BuergerID+1 = B.BuergerID
            WHERE B.`BuergerID` IS NULL AND A.`BuergerID` > 100000
            ORDER BY A.`BuergerID` LIMIT 1';
    }

    public function getFirstSixDigitProcessId()
    {
        return 'SELECT * FROM `' . self::getTablename() . '` A
            WHERE A.`BuergerID` = 100000';
    }

    public function addJoin()
    {
        return [
            $this->addJoinAvailability(),
        ];
    }

    /**
     * Add Availability to the dataset
     *
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

    /**
     * TODO: Check if necessary, the slot list is built on the calendar
     */
    public function readRequestOnEntity(\BO\Zmsentities\Request $entity)
    {
        $dbname_dldb = \BO\Zmsdb\Connection\Select::$dbname_dldb;
        $query = 'SELECT
                x.`dienstleister` AS provider__id,
                x.`slots`
            FROM `' . $dbname_dldb . '`.`xdienst` x
                LEFT JOIN `' . $dbname_dldb . '`.`dienstleister` d ON x.dienstleister = d.id
            WHERE
                x.`dienstleistung` = :request_id
                AND x.`termin_hide` = 0
                AND d.`zms_termin` = 1
        ';
        $providerSlots = $this->getReader()
            ->fetchAll(
                $query,
                ['request_id' => $entity->id]
            );
        return $providerSlots;
    }

    public function getEntityMapping()
    {
        return [
            'amendment' => 'process.Anmerkung',
            'id' => 'process.BuergerID',
            'appointments__0__date' => self::expression(
                'FLOOR(UNIX_TIMESTAMP(CONCAT(`process`.`Datum`, " ", `process`.`Uhrzeit`)))'
            ),
            'scope__id' => 'process.StandortID',
            'appointments__0__scope__id' => 'process.StandortID',
            //'appointments__0__slotCount' => 'process.hatFolgetermine',
            'appointments__0__slotCount' => self::expression('(SELECT COUNT(*) + 1
                    FROM ' . self::TABLE . ' as `followingProcess`
                    WHERE
                        `followingProcess`.`istFolgeterminvon` = `BuergerID`
                )'),
            'authKey' => 'process.absagecode',
            'clients__0__email' => 'process.EMail',
            'clients__0__emailSendCount' => 'process.EMailverschickt',
            'clients__0__familyName' => 'process.Name',
            'clients__0__notificationsSendCount' => 'process.SMSverschickt',
            'clients__0__surveyAccepted' => 'process.zustimmung_kundenbefragung',
            'clients__0__telephone' => 'process.telefonnummer_fuer_rueckfragen',
            'createIP' => 'process.IPAdresse',
            'createTimestamp' => 'process.IPTimeStamp',
            'queue__arrivalTime' => 'process.wsm_aufnahmezeit',
            'queue__callCount' => 'process.AnzahlAufrufe',
            'queue__callTime' => 'process.aufrufzeit',
            'queue__number' => 'process.wartenummer',
            'queue__waitingTime' => 'process.wartezeit',
            'workstation__id' => 'process.NutzerID',
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

    public function addConditionAuthKey($authKey)
    {
        if (! empty($authKey)) {
            $this->query->where('process.absagecode', '=', $authKey);
        } else {
            $this->query->where('process.absagecode', 'IS', null);
        }
        return $this;
    }

    public function addConditionIsReserved()
    {
        $this->query
            ->where('process.name', 'NOT IN', array('dereferenced','(abgesagt)'))
            ->where('process.vorlaeufigeBuchung', '=', 1)
            ->where('process.StandortID', '<>', 1)
            ->where('process.istFolgeterminvon', 'is', null);
        return $this;
    }

    public function reverseEntityMapping(\BO\Zmsentities\Process $process)
    {
        $data = array();
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
            $data['zustimmung_kundenbefragung'] = $client->surveyAccepted;
            $data['EMailverschickt'] = $client->emailSendCount;
            $data['SMSverschickt'] = $client->notificationsSendCount;
        }

        $data['vorlaeufigeBuchung'] = ($process['status'] == 'reserved') ? 1 : 0;
        $data['Erinnerungszeitpunkt'] = $process->getReminderTimestamp();

        $data = array_filter($data, function ($value) {
            return ($value !== null && $value !== false && $value !== '');
        });
        return $data;
    }
}
