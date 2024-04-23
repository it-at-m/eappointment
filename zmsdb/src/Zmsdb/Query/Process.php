<?php

namespace BO\Zmsdb\Query;

/**
 *
 * @SuppressWarnings(Methods)
 * @SuppressWarnings(Complexity)
 */
class Process extends Base implements MappingInterface
{

    /**
     *
     * @var String TABLE mysql table reference
     */
    const TABLE = 'buerger';

    const QUERY_DEREFERENCED = "UPDATE `buerger` process LEFT JOIN `standort` s USING(StandortID)
        SET
            process.Anmerkung = ?,
            process.custom_text_field = ?,
            process.StandortID = 0,
            process.AbholortID = 0,
            process.Abholer = 0,
            process.Name = 'dereferenced',
            process.IPadresse = '',
            process.IPTimeStamp = 0,
            process.NutzerID = 0,
            process.vorlaeufigeBuchung = 0,
            process.bestaetigt = 1,
            process.absagecode = 'deref!0',
            process.EMail = '',
            process.NutzerID = 0
        WHERE
            (process.BuergerID = ? AND process.absagecode = ?)
            OR process.istFolgeterminvon = ?
        ";

    const QUERY_CANCELED = "
        UPDATE `buerger` process LEFT JOIN `standort` s USING(StandortID)
            SET
                process.Anmerkung = CONCAT(
                    'Abgesagter Termin gebucht am: ',
                    FROM_UNIXTIME(process.IPTimeStamp,'%d.%m.%Y, %H:%i'),' Uhr | ',
                    IFNULL(process.Anmerkung,'')
                ),              
                process.Name = '(abgesagt)',
                process.IPadresse = '',
                process.IPTimeStamp = :canceledTimestamp + (IFNULL(s.loeschdauer, 15) * 60),
                process.NutzerID = 0,
                process.vorlaeufigeBuchung = 1,
                process.absagecode = RIGHT(MD5(CONCAT(process.absagecode, 'QUERY_CANCELED')), 4)
            WHERE
                (process.BuergerID = :processId AND process.absagecode = :authKey)
                OR process.istFolgeterminvon = :processId
        ";

    const QUERY_DELETE = "DELETE FROM `buerger`
        WHERE
            BuergerID = ?
            OR istFolgeterminvon = ?
        ";

    const QUERY_REASSIGN_PROCESS_CREDENTIALS = "UPDATE `buerger` process
       SET 
            process.BuergerID = :newProcessId, 
            process.absagecode = :newAuthKey
        WHERE BuergerID = :processId
    ";

    const QUERY_REASSIGN_PROCESS_REQUESTS = "UPDATE `buergeranliegen` requests
        SET 
            requests.BuergerID = :newProcessId
        WHERE BuergerID = :processId
    ";

    const QUERY_REASSIGN_FOLLWING_PROCESS = "UPDATE `buerger` process
        SET process.istFolgeterminvon = :newProcessId
        WHERE istFolgeterminvon = :processId
    ";

    const QUERY_UPDATE_FOLLOWING_PROCESS = "UPDATE buerger 
        SET vorlaeufigeBuchung = :reserved 
        WHERE istFolgeterminvon = :processID
        ";

    public function getQueryNewProcessId()
    {
        $random = rand(20, 999);
        return 'SELECT pseq.processId AS `nextid`
            FROM process_sequence pseq
            WHERE pseq.processId = (
                SELECT ps.processID FROM `process_sequence` ps LEFT JOIN `' . self::getTablename() . '` p
                    ON ps.processId = p.BuergerID
                WHERE p.`BuergerID` IS NULL
                LIMIT ' . $random . ',1)
            FOR UPDATE';
    }

    public function getLockProcessId()
    {
        return 'SELECT p.`BuergerID` FROM `' . self::getTablename() . '` p WHERE p.`BuergerID` = :processId FOR UPDATE';
    }

    public function addJoin()
    {
        return [
            $this->addJoinAvailability(),
            $this->addJoinScope(),
        ];
    }

    /**
     * Add Availability to the dataset
     */
    protected function addJoinAvailability()
    {
        $this->leftJoin(
            new Alias(Availability::TABLE, 'availability'),
            Availability::getJoinExpression('`process`', '`availability`')
        );
        $joinQuery = new Availability($this, $this->getPrefixed('appointments__0__availability__'));
        return $joinQuery;
    }

    /**
     * Add Scope to the dataset
     */
    protected function addJoinScope()
    {
        $this->leftJoin(
            new Alias(Scope::TABLE, 'scope'),
            self::expression(
                'IF(`process`.`AbholortID`,
                    `process`.`AbholortID`,
                    `process`.`StandortID`
                )'
            ),
            '=',
            'scope.StandortID'
        );
        $joinQuery = new Scope($this, $this->getPrefixed('scope__'));
        return $joinQuery;
    }

    public function getEntityMapping()
    {
        $status_expression = self::expression(
            'CASE
                WHEN process.Name = "(abgesagt)"
                    THEN "deleted"
                WHEN process.StandortID = 0 AND process.AbholortID = 0
                    THEN "blocked"
                WHEN process.vorlaeufigeBuchung = 1 AND process.bestaetigt = 0 
                    THEN "reserved"
                WHEN process.nicht_erschienen != 0
                    THEN "missed"
                WHEN process.parked != 0
                    THEN "parked"
                WHEN process.Abholer != 0 AND process.AbholortID != 0 AND process.NutzerID = 0
                    THEN "pending"
                WHEN process.AbholortID != 0 AND process.NutzerID != 0
                    THEN "pickup"
                WHEN process.AbholortID = 0 AND process.aufruferfolgreich != 0 AND process.NutzerID != 0
                    THEN "processing"
                WHEN process.aufrufzeit != "00:00:00" AND process.NutzerID != 0 AND process.AbholortID = 0
                    THEN "called"
                WHEN process.Uhrzeit = "00:00:00"
                    THEN "queued"
                WHEN process.vorlaeufigeBuchung = 0 AND process.bestaetigt = 0 
                    THEN "preconfirmed"
                WHEN process.vorlaeufigeBuchung = 0 AND process.bestaetigt = 1
                    THEN "confirmed"
                ELSE "free"
            END'
        );
        return [
            'amendment' => 'process.Anmerkung',
            'id' => 'process.BuergerID',
            'appointments__0__date' => self::expression(
                'CONCAT(`process`.`Datum`, " ", `process`.`Uhrzeit`)'
            ),
            'scope__id' => self::expression(
                'IF(`process`.`AbholortID`,
                    `process`.`AbholortID`,
                    `process`.`StandortID`
)'
            ),
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
                'IF(`process`.`telefonnummer_fuer_rueckfragen`!="",
                    `process`.`telefonnummer_fuer_rueckfragen`,
                    `process`.`Telefonnummer`
                )'
            ),
            'customTextfield' => 'process.custom_text_field',
            'createIP' => 'process.IPAdresse',
            'createTimestamp' => 'process.IPTimeStamp',
            'lastChange' => 'process.updateTimestamp',
            'showUpTime' => 'process.showUpTime',
            'timeoutTime' => 'process.timeoutTime',
            'finishTime' => 'process.finishTime',           
            'status' => $status_expression,
            'queue__status' => $status_expression,
            'queue__arrivalTime' => self::expression(
                'CONCAT(
                    `process`.`Datum`,
                    " ",
                    IF(`process`.`wsm_aufnahmezeit`, `process`.`wsm_aufnahmezeit`, `process`.`Uhrzeit`)
                )'
            ),
            'queue__callCount' => 'process.AnzahlAufrufe',
            'queue__callTime' => 'process.aufrufzeit',
            'queue__lastCallTime' => 'process.Timestamp',
            'queue__number' => self::expression(
                'IF(`process`.`wartenummer`,
                    `process`.`wartenummer`,
                    `process`.`BuergerID`
                )'
            ),
            'queue__destination' => self::expression(
                'IF(`process`.`AbholortID`,
                    `processscope`.`ausgabeschaltername`,
                    `processuser`.`Arbeitsplatznr`
)'
            ),
            'queue__destinationHint' => 'processuser.aufrufzusatz',
            'queue__waitingTime' => 'process.wartezeit',
            'queue__withAppointment' => self::expression(
                'IF(`process`.`wartenummer`,
                    "0",
                    "1"
                )'
            ),
            'reminderTimestamp' => 'process.Erinnerungszeitpunkt',
            '__clientsCount' => 'process.AnzahlPersonen',
        ];
    }

    public function addCountValue()
    {
        $this->query->select([
            'processCount' => self::expression('COUNT(*)'),
        ]);
        return $this;
    }

    public function addConditionHasTelephone()
    {
        $this->query->where(function (\Solution10\SQL\ConditionBuilder $condition) {
            $condition
                ->andWith('process.telefonnummer_fuer_rueckfragen', '!=', '')
                ->orWith('process.Telefonnummer', '!=', '');
        });
        return $this;
    }

    public function addConditionProcessDeleteInterval(\DateTimeInterface $expirationDate)
    {
        $this->query->where(function (\Solution10\SQL\ConditionBuilder $query) use ($expirationDate) {
            $query->andWith(
                self::expression(
                    'CONCAT(`process`.`Datum`, " ", `process`.`Uhrzeit`)'
                ),
                '<=',
                $expirationDate->format('Y-m-d H:i:s')
            );
        });
        $this->query->orderBy('appointments__0__date', 'ASC');
        return $this;
    }

    public function addConditionProcessExpiredIPTimeStamp(\DateTimeInterface $expirationDate)
    {
        $this->query->where(function (\Solution10\SQL\ConditionBuilder $query) use ($expirationDate) {
            $query->andWith('process.IPTimeStamp', '<=', $expirationDate->getTimestamp());
        });
        $this->query->orderBy('appointments__0__date', 'ASC');
        return $this;
    }

    public function addConditionProcessReminderInterval(\DateTimeInterface $dateTime)
    {
        $this->query->where(function (\Solution10\SQL\ConditionBuilder $query) use ($dateTime) {
            $query
                ->andWith('process.Erinnerungszeitpunkt', '<=', $dateTime->getTimestamp())
                ->andWith('process.Erinnerungszeitpunkt', '>=', $dateTime->modify("-5 Minutes")->getTimestamp());
        });
        $this->query->orderBy('reminderTimestamp', 'ASC');
        return $this;
    }

    public function addConditionProcessMailReminder(
        \DateTimeInterface $now,
        \DateTimeInterface $lastRun,
        $defaultReminderInMinutes
    ) {
        $this->query
            ->where(function (\Solution10\SQL\ConditionBuilder $query) use ($now, $lastRun, $defaultReminderInMinutes) {
                $query
                    ->andWith(
                        self::expression(
                            'CONCAT(`process`.`Datum`, " ", `process`.`Uhrzeit`)'
                        ),
                        '>',
                        $lastRun->format('Y-m-d H:i:s')
                    )
                    ->andWith(
                        self::expression(
                            'CONCAT(`process`.`Datum`, " ", `process`.`Uhrzeit`)'
                        ),
                        '>',
                        $now->format('Y-m-d H:i:s')
                    )
                    ->andWith(
                        'scopemail.send_reminder',
                        '=',
                        1
                    )
                    ->andWith(
                        'process.EMail',
                        '<>',
                        ""
                    )
                    ->andWith(
                        'process.EMailverschickt',
                        '=',
                        0
                    )
                    ->andWith(
                        self::expression(
                            'DATE_SUB(CONCAT(`process`.`Datum`, " ", `process`.`Uhrzeit`), INTERVAL '
                            . 'IFNULL(scopemail.send_reminder_minutes_before, ' . $defaultReminderInMinutes
                            . ') MINUTE)'
                        ),
                        '<=',
                        $now->format('Y-m-d H:i:s')
                    );
            });
        $this->query->orderBy('appointments__0__date', 'ASC');
        return $this;
    }

    public function addConditionProcessId($processId)
    {
        $this->query->where('process.BuergerID', '=', $processId);
        $this->query->where(function (\Solution10\SQL\ConditionBuilder $condition) {
            $condition
                ->andWith('process.istFolgeterminvon', 'IS', null)
                ->orWith('process.istFolgeterminvon', '=', 0);
        });
        return $this;
    }

    public function addConditionProcessIdFollow($processId)
    {
        $this->query->where(function (\Solution10\SQL\ConditionBuilder $condition) use ($processId) {
            $condition
                ->andWith('process.BuergerID', '=', $processId)
                ->orWith('process.istFolgeterminvon', '=', $processId);
        });
        return $this;
    }

    public function addConditionIgnoreSlots()
    {
        $this->query->where(function (\Solution10\SQL\ConditionBuilder $condition) {
            $condition
                ->andWith('process.istFolgeterminvon', 'IS', null)
                ->orWith('process.istFolgeterminvon', '=', 0);
        });
        return $this;
    }

    public function addConditionScopeId($scopeId)
    {
        $this->query->where(function (\Solution10\SQL\ConditionBuilder $query) use ($scopeId) {
            $query
                ->andWith('process.StandortID', '=', $scopeId)
                ->orWith('process.AbholortID', '=', $scopeId);
        });
        return $this;
    }

    public function addConditionQueueNumber($queueNumber, $queueLimit = 10000)
    {
        ($queueLimit > $queueNumber)
            ? $this->query->where('process.wartenummer', '=', $queueNumber)
            : $this->query->where('process.BuergerID', '=', $queueNumber);
        return $this;
    }

    public function addConditionWorkstationId($workstationId)
    {
        $this->query->where(function (\Solution10\SQL\ConditionBuilder $query) use ($workstationId) {
            $query->andWith('process.NutzerID', '=', $workstationId);
            $query->andWith('process.StandortID', '>', 0);
        });
        return $this;
    }

    public function addConditionTime($dateTime)
    {
        $this->query->where('process.Datum', '=', $dateTime->format('Y-m-d'));
        return $this;
    }

    /**
     * Identify processes between two dates
     *
     */
    public function addConditionTimeframe(\DateTimeInterface $startDate, \DateTimeInterface $endDate)
    {
        $this->query->where(function (\Solution10\SQL\ConditionBuilder $condition) use ($startDate, $endDate) {
            $condition
                ->andWith('process.Datum', '<=', $endDate->format('Y-m-d'))
                ->andWith('process.Datum', '>=', $startDate->format('Y-m-d'));
        });
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

    public function addConditionAssigned()
    {
        $this->query->where('process.StandortID', '!=', "0");
        return $this;
    }

    public function addConditionStatus($status, $scopeId = 0)
    {
        $this->query->where(function (\Solution10\SQL\ConditionBuilder $query) use ($status, $scopeId) {
            if ('deleted' == $status) {
                $query
                    ->andWith('process.Name', '=', '(abgesagt)');
            }
            if ('blocked' == $status) {
                $query
                    ->andWith('process.StandortID', '=', 0)
                    ->andWith('process.AbholortID', '=', 0);
            }
            if ('reserved' == $status) {
                $query
                    ->andWith('process.name', '!=', '(abgesagt)')
                    ->andWith('process.vorlaeufigeBuchung', '=', 1)
                    ->andWith('process.StandortID', '!=', 0)
                    ->andWith('process.istFolgeterminvon', 'is', null);
            }
            if ('missed' == $status) {
                $query->andWith('process.nicht_erschienen', '!=', 0)
                    ->andWith('process.StandortID', '!=', 0)
                    ;
            }
            if ('parked' == $status) {
                $query
                    ->andWith('process.parked', '!=', 0)
                    ->andWith('process.StandortID', '!=', 0);
            }
            if ('pending' == $status) {
                $query
                    ->andWith('process.StandortID', '!=', 0)
                    ->andWith('process.Abholer', '!=', 0)
                    ->andWith('process.NutzerID', '=', 0);
                if (0 != $scopeId) {
                    $query->andWith('process.AbholortID', '=', $scopeId);
                } else {
                    $query->andWith('process.AbholortID', '!=', 0);
                }
            }
            if ('processing' == $status) {
                $query
                    ->andWith('process.aufruferfolgreich', '!=', 0)
                    ->andWith('process.NutzerID', '!=', 0)
                    ->andWith('process.StandortID', '!=', 0);
            }
            if ('pickup' == $status) {
                $query
                    ->andWith('process.StandortID', '!=', 0)
                    ->andWith('process.NutzerID', '!=', 0);
                if (0 != $scopeId) {
                    $query->andWith('process.AbholortID', '=', $scopeId);
                } else {
                    $query->andWith('process.AbholortID', '!=', 0);
                }
            }
            if ('called' == $status) {
                $query
                    ->andWith('process.aufrufzeit', '!=', '00:00:00')
                    ->andWith('process.NutzerID', '!=', 0)
                    ->andWith('process.StandortID', '!=', 0)
                    ->andWith('process.AbholortID', '=', 0);
            }
            if ('queued' == $status) {
                $query->andWith('process.Uhrzeit', '=', '00:00:00')
                    ->andWith('process.StandortID', '!=', 0)
                    ->andWith('process.AbholortID', '=', 0);
                    ;
            }
            if ('confirmed' == $status) {
                $query
                    ->andWith('process.vorlaeufigeBuchung', '=', 0)
                    ->andWith('process.Abholer', '=', 0)
                    ->andWith('process.Uhrzeit', '!=', '00:00:00')
                    ->andWith('process.bestaetigt', '=', 1)
                    ->andWith('process.IPTimeStamp', '!=', 0);
            }
            if ('preconfirmed' == $status) {
                $query
                    ->andWith('process.vorlaeufigeBuchung', '=', 0)
                    ->andWith('process.Abholer', '=', 0)
                    ->andWith('process.StandortID', '!=', 0)
                    ->andWith('process.Uhrzeit', '!=', '00:00:00')
                    ->andWith('process.bestaetigt', '=', 0)
                    ->andWith('process.IPTimeStamp', '!=', 0);
                if (0 != $scopeId) {
                    $query
                        ->andWith('process.StandortID', '=', $scopeId);
                }
            }
        });
        return $this;
    }

    public function addConditionIsReserved()
    {
        $this->query->where('process.name', 'NOT IN', array(
            'dereferenced',
            '(abgesagt)'
        ))
            ->where('process.vorlaeufigeBuchung', '=', 1)
            ->where('process.StandortID', '>', 0);
        $this->query->where(function (\Solution10\SQL\ConditionBuilder $condition) {
            $condition
                ->andWith('process.istFolgeterminvon', 'IS', null)
                ->orWith('process.istFolgeterminvon', '=', 0);
        });
        return $this;
    }

    public function addConditionSearch($queryString, $orWhere = false)
    {
        $condition = function (\Solution10\SQL\ConditionBuilder $query) use ($queryString) {
            $queryString = trim($queryString);
            $query->orWith('process.Name', 'LIKE', "%$queryString%");
            $query->orWith('process.EMail', 'LIKE', "%$queryString%");
            $query->orWith('process.Telefonnummer', 'LIKE', "%$queryString%");
            $query->orWith('process.telefonnummer_fuer_rueckfragen', 'LIKE', "%$queryString%");
        };
        if ($orWhere) {
            $this->query->orWhere($condition);
        } else {
            $this->query->where($condition);
        }
        return $this;
    }

    public function addConditionName($name, $exactMatching = false)
    {
        if ($exactMatching) {
            $this->query->where(function (\Solution10\SQL\ConditionBuilder $query) use ($name) {
                $query->andWith('process.Name', '=', $name);
            });
        } else {
            $this->query->where(function (\Solution10\SQL\ConditionBuilder $query) use ($name) {
                $query->andWith('process.Name', 'LIKE', "%$name%");
            });
        }
        return $this;
    }

    public function addConditionMail($mailAddress, $exactMatching = false)
    {
        if ($exactMatching) {
            $this->query->where(function (\Solution10\SQL\ConditionBuilder $query) use ($mailAddress) {
                $query->andWith('process.Email', '=', $mailAddress);
            });
        } else {
            $this->query->where(function (\Solution10\SQL\ConditionBuilder $query) use ($mailAddress) {
                $query->andWith('process.Email', 'LIKE', "%$mailAddress%");
            });
        }
        return $this;
    }


    public function addConditionCustomTextfield($customText, $exactMatching = false)
    {
        if ($exactMatching) {
            $this->query->where(function (\Solution10\SQL\ConditionBuilder $query) use ($customText) {
                $query->andWith('process.custom_text_field', '=', $customText);
            });
        } else {
            $this->query->where(function (\Solution10\SQL\ConditionBuilder $query) use ($customText) {
                $query->andWith('process.custom_text_field', 'LIKE', "%$customText%");
            });
        }
        return $this;
    }

    public function addConditionAmendment($amendment)
    {
        $this->query->where(function (\Solution10\SQL\ConditionBuilder $query) use ($amendment) {
            $query->andWith('process.Anmerkung', 'LIKE', "%$amendment%");
        });
        return $this;
    }

    /**
     * Add Requests Join
     */
    public function addConditionRequestId($requestId)
    {
        $this->leftJoin(
            new Alias("buergeranliegen", 'buergeranliegen'),
            'buergeranliegen.BuergerID',
            '=',
            'process.BuergerID'
        );
        $this->query->where('buergeranliegen.AnliegenID', '=', $requestId);
        return $this;
    }

    /**
     * add condition to get process if deallocation time < now
     */
    public function addConditionDeallocate($now)
    {
        $this->query->where(function (\Solution10\SQL\ConditionBuilder $query) use ($now) {
            $query
                ->andWith('process.Name', '=', '(abgesagt)')
                ->andWith('process.IPTimeStamp', '<', $now->getTimestamp());
        });
        $this->query->orderBy('process.IPTimeStamp', 'ASC');
        return $this;
    }


    public function addValuesNewProcess(\BO\Zmsentities\Process $process, $parentProcess = 0, $childProcessCount = 0)
    {
        $values = [
            'BuergerID' => $process->id,
            'IPTimeStamp' => $process->createTimestamp,
            'absagecode' => $process->authKey,
            'hatFolgetermine' => $childProcessCount,
            'istFolgeterminvon' => $parentProcess,
            'wartenummer' => $process->queue['number']
        ];
        if ($process->toProperty()->apiclient->apiClientID->isAvailable()) {
            $values['apiClientID'] = $process->apiclient->apiClientID;
        }
        $this->addValues($values);
    }

    public function addValuesUpdateProcess(
        \BO\Zmsentities\Process $process,
        \DateTimeInterface $dateTime,
        $parentProcess = 0,
        $previousStatus = null
    ) {
        $this->addValuesIPAdress($process);
        $this->addValuesStatusData($process, $dateTime);
        if (0 === $parentProcess) {
            $this->addValuesClientData($process);
            $this->addProcessingTimeData($process, $dateTime, $previousStatus);
            $this->addValuesQueueData($process);
            $this->addValuesWaitingTimeData($process);
        }
        if ($process->isWithAppointment()) {
            $this->addValuesFollowingProcessData($process, $parentProcess);
        }
    }

    public function addValuesIPAdress($process)
    {
        $data = array();
        $data['IPAdresse'] = $process['createIP'];
        $this->addValues($data);
    }

    public function addValuesFollowingProcessData($process, $parentProcess)
    {
        $data = array();
        if (0 === $parentProcess) {
            $data['hatFolgetermine'] = (1 <= $process->getFirstAppointment()->getSlotCount()) ?
                $process->getFirstAppointment()->getSlotCount() - 1 :
                0;
        } else {
            $data['Name'] = '(Folgetermin)';
        }
        $this->addValues($data);
    }

    public function addValuesAppointmentData(
        \BO\Zmsentities\Process $process
    ) {
        $data = array();
        $appointment = $process->getFirstAppointment();
        if (null !== $appointment) {
            $datetime = $appointment->toDateTime();
            $data['Datum'] = $datetime->format('Y-m-d');
            $data['Uhrzeit'] = $datetime->format('H:i:s');
        }
        $this->addValues($data);
    }

    public function addValuesScopeData(
        \BO\Zmsentities\Process $process
    ) {
        $data = array();
        $data['StandortID'] = $process->getScopeId();
        $this->addValues($data);
    }

    public function addValuesStatusData($process, \DateTimeInterface $dateTime)
    {
        $data = array();
        $data['vorlaeufigeBuchung'] = ($process['status'] == 'reserved') ? 1 : 0;
        $data['aufruferfolgreich'] = ($process['status'] == 'processing') ? 1 : 0;
        if ($process->status == 'pending') {
            $data['AbholortID'] = $process->scope['id'];
            $data['Abholer'] = 1;
            $data['nicht_erschienen'] = 0;
            $data['parked'] = 0;
        }
        if ($process->status == 'pickup') {
            $data['AbholortID'] = $process->scope['id'];
            $data['Abholer'] = 1;
            $data['Timestamp'] = 0;
            $data['nicht_erschienen'] = 0;
            $data['parked'] = 0;
        }
        if ($process->status == 'queued') {
            $data['nicht_erschienen'] = 0;
            $data['parked'] = 0;
            if ($process->hasArrivalTime() &&
                (isset($process->queue['withAppointment']) && $process->queue['withAppointment'])
            ) {
                $data['wsm_aufnahmezeit'] = $dateTime->format('H:i:s');
            }
        }
        if ($process->status == 'missed') {
            $data['nicht_erschienen'] = 1;
        }
        if ($process->status == 'parked') {
            $data['parked'] = 1;
        }
        if ($process->status == 'confirmed') {
            $data['bestaetigt'] = 1;
        }
        if ($process->status == 'preconfirmed') {
            $data['bestaetigt'] = 0;
        }
        
        $this->addValues($data);
    }

    protected function addValuesClientData($process)
    {
        $data = array();
        $client = $process->getFirstClient();
        if ($client && $client->hasFamilyName()) {
            $data['Name'] = $client->familyName;
        }
        if ($client && $client->hasEmail()) {
            $data['EMail'] = $client->email;
        }
        if ($client && $client->offsetExists('telephone')) {
            $data['telefonnummer_fuer_rueckfragen'] = $client->telephone;
            $data['Telefonnummer'] = $client->telephone; // to stay compatible with ZMS1
        }
        if ($client && $client->offsetExists('emailSendCount')) {
            $data['EMailverschickt'] = ('-1' == $client->emailSendCount) ? 0 : $client->emailSendCount;
        }
        if ($client && $client->offsetExists('notificationsSendCount')) {
            $data['SMSverschickt'] = ('-1' == $client->notificationsSendCount) ? 0 : $client->notificationsSendCount;
        }
        if ($process->getAmendment()) {
            $data['Anmerkung'] = $process->getAmendment();
        }
        if ($process->getCustomTextfield()) {
            $data['custom_text_field'] = $process->getCustomTextfield();
        }
        $data['zustimmung_kundenbefragung'] = ($client->surveyAccepted) ? 1 : 0;
        $data['Erinnerungszeitpunkt'] = $process->getReminderTimestamp();
        $data['AnzahlPersonen'] = $process->getClients()->count();
        $this->addValues($data);
    }

    protected function addProcessingTimeData($process, \DateTimeInterface $dateTime, $previousStatus = null)
    {
        $data = array();

        if (isset($previousStatus) && ($process->status == 'called' && $previousStatus == 'called')) {
            $data['timeoutTime'] = $dateTime->format('Y-m-d H:i:s');
        } else if (isset($previousStatus) && ($process->status == 'processing' && $previousStatus == 'processing')) {
            $data['timeoutTime'] = $dateTime->format('Y-m-d H:i:s');
        } else if ($process->status == 'processing') {
            $data['showUpTime'] = $dateTime->format('Y-m-d H:i:s');
        } else if ($process->status == 'finished') {
            $data['finishTime'] = $dateTime->format('Y-m-d H:i:s');
        }

        $this->addValues($data);
    }
    

    protected function addValuesQueueData($process)
    {
        $data = array();
        $appointmentTime=$process->getFirstAppointment()->toDateTime()->format('H:i:s');

        if (isset($process->queue['callCount']) && $process->queue['callCount']) {
            $data['AnzahlAufrufe'] = $process->queue['callCount'];
        }
        if (isset($process->queue['callTime']) && $process->queue['callTime']) {
            $data['aufrufzeit'] = (new \DateTimeImmutable())
                ->setTimestamp($process->queue['callTime'])->format('H:i:s');
        }
        if (isset($process->queue['lastCallTime']) && $process->queue['lastCallTime']) {
            $data['Timestamp'] = (new \DateTimeImmutable())
                ->setTimestamp($process->queue['lastCallTime'])->format('H:i:s');
        }
        if (isset($process->queue['arrivalTime']) && $process->queue['arrivalTime']) {
            $data['wsm_aufnahmezeit'] = (new \DateTimeImmutable())
                ->setTimestamp($process->queue['arrivalTime'])->format('H:i:s');
        }
        if (isset($data['wsm_aufnahmezeit']) && $data['wsm_aufnahmezeit'] == $appointmentTime) {
            // Do not save arrivalTime if it is an appointment
            $data['wsm_aufnahmezeit'] = 0;
        }
        $this->addValues($data);
    }

    protected function addValuesWaitingTimeData($process)
    {
        $data = array();
        if ($process['status'] == 'processing') {
            $wartezeit = $process->getWaitedMinutes();
            $data['wartezeit'] = $wartezeit > 0 ? $wartezeit : 0;
        }
        $this->addValues($data);
    }

    public function postProcess($data)
    {
        $data[$this->getPrefixed("appointments__0__date")] =
            strtotime($data[$this->getPrefixed("appointments__0__date")]);
        if ('00:00:00' != $data[$this->getPrefixed("queue__callTime")]) {
            $time = explode(':', $data[$this->getPrefixed("queue__callTime")]);
            $data[$this->getPrefixed("queue__callTime")] = (new \DateTimeImmutable())
                ->setTimestamp($data[$this->getPrefixed("appointments__0__date")])
                ->setTime($time[0], $time[1], $time[2])
                ->getTimestamp();
        } else {
            $data[$this->getPrefixed("queue__callTime")] = 0;
        }
        if ('00:00:00' != $data[$this->getPrefixed("queue__lastCallTime")]) {
            $time = explode(':', $data[$this->getPrefixed("queue__lastCallTime")]);
            $data[$this->getPrefixed("queue__lastCallTime")] = (new \DateTimeImmutable())
                ->setTimestamp($data[$this->getPrefixed("appointments__0__date")])
                ->setTime($time[0], $time[1], $time[2])
                ->getTimestamp();
        } else {
            $data[$this->getPrefixed("queue__lastCallTime")] = 0;
        }
        $data[$this->getPrefixed("queue__arrivalTime")] =
            strtotime($data[$this->getPrefixed("queue__arrivalTime")]);
        if (isset($data[$this->getPrefixed('scope__provider__data')])
            && $data[$this->getPrefixed('scope__provider__data')]) {
            $data[$this->getPrefixed('scope__provider__data')] =
                json_decode($data[$this->getPrefixed('scope__provider__data')], true);
        }
        if (isset($data[$this->getPrefixed('__clientsCount')])) {
            $clientsCount = $data[$this->getPrefixed('__clientsCount')];
            unset($data[$this->getPrefixed('__clientsCount')]);
            while (--$clientsCount > 0) {
                $data[$this->getPrefixed('clients__' . $clientsCount . '__familyName')] = 'Unbekannt';
            }
        }
        $data[$this->getPrefixed("lastChange")] =
            (new \DateTimeImmutable($data[$this->getPrefixed("lastChange")] .
            \BO\Zmsdb\Connection\Select::$connectionTimezone))->getTimestamp();
        return $data;
    }

    protected function addRequiredJoins()
    {
        $this->leftJoin(
            new Alias(Useraccount::TABLE, 'processuser'),
            'process.NutzerID',
            '=',
            'processuser.NutzerID'
        );

        $this->leftJoin(
            new Alias(Scope::TABLE, 'processscope'),
            'process.StandortID',
            '=',
            'processscope.StandortID'
        );
    }
}
