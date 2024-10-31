<?php

namespace BO\Zmsdb\Query;

/**
*
* @SuppressWarnings(Methods)
* @SuppressWarnings(Complexity)
 */
class Queue extends Process implements MappingInterface
{
    const ALIAS = 'process';

    public function getEntityMapping()
    {
        $status_expression = self::expression(
            'CASE
                WHEN process.Name = "(abgesagt)"
                    THEN "deleted"
                WHEN process.Name = "dereferenced" AND process.StandortID = 0
                    THEN "blocked"
                WHEN process.vorlaeufigeBuchung = 1 AND process.bestaetigt = 0 
                    THEN "reserved"
                WHEN process.nicht_erschienen != 0
                    THEN "missed"
                WHEN process.Abholer != 0 AND process.AbholortID != 0 AND process.NutzerID = 0
                    THEN "pending"
                WHEN process.AbholortID != 0 AND process.NutzerID != 0
                    THEN "pickup"
                WHEN process.AbholortID = 0 AND process.aufruferfolgreich != 0 AND process.NutzerID != 0
                    THEN "processing"
                WHEN process.aufrufzeit != "00:00:00" AND process.NutzerID != 0 AND process.AbholortID = 0
                    THEN "called"
                WHEN process.wsm_aufnahmezeit != "00:00:00"
                    THEN "queued"
                WHEN process.vorlaeufigeBuchung = 0 AND process.bestaetigt = 0  AND IPTimeStamp
                    THEN "preconfirmed"
                WHEN process.vorlaeufigeBuchung = 0 AND process.bestaetigt = 1 AND IPTimeStamp
                    THEN "confirmed"
                ELSE "free"
            END'
        );
        return [
            'id' => 'process.BuergerID',
            'status' => $status_expression,
            'arrivalTime' => self::expression(
                'CONCAT(
                    `process`.`Datum`,
                    " ",
                    IF(`process`.`Uhrzeit`, `process`.`Uhrzeit`, `process`.`wsm_aufnahmezeit`)
                )'
            ),
            'callCount' => 'process.AnzahlAufrufe',
            'callTime' => self::expression(
                'CONCAT(
                    `process`.`Datum`,
                    " ",
                    `process`.`aufrufzeit`
                )'
            ),
            'lastCallTime' => self::expression(
                'CONCAT(
                    `process`.`Datum`,
                    " ",
                    `process`.`Timestamp`
                )'
            ),
            'number' =>  'process.BuergerID',
            'destination' => self::expression(
                'IF(`process`.`AbholortID`,
                    `processscope`.`ausgabeschaltername`,
                    `processuser`.`Arbeitsplatznr`
)'
            ),
            'destinationHint' => 'processuser.aufrufzusatz',
            'waitingTime' => 'process.wartezeit',
            'wayTime' => 'process.wegezeit',
            'withAppointment' => self::expression(
                'IF(`process`.`wartenummer`,
                    "0",
                    "1"
                )'
            ),
        ];
    }

    public function addConditionAssigned()
    {
        $this->query->where(function (\Solution10\SQL\ConditionBuilder $query) {
            $query->andWith(self::expression('process.istFolgeterminvon IS NULL OR process.istFolgeterminvon'), '=', 0);
        });
        return $this;
    }


    public function postProcess($data)
    {
        if (false === strpos($data[$this->getPrefixed("callTime")], '00:00:00')) {
            $data[$this->getPrefixed("callTime")] = strtotime($data[$this->getPrefixed("callTime")]);
        } else {
            $data[$this->getPrefixed("callTime")] = 0;
        }
        if (false === strpos($data[$this->getPrefixed("lastCallTime")], '00:00:00')) {
            $data[$this->getPrefixed("lastCallTime")] = strtotime($data[$this->getPrefixed("lastCallTime")]);
        } else {
            $data[$this->getPrefixed("lastCallTime")] = 0;
        }
        if (!$data[$this->getPrefixed("waitingTime")]) {
            $data[$this->getPrefixed("waitingTime")] = 0;
        };
        if (!$data[$this->getPrefixed("wayTime")]) {
            $data[$this->getPrefixed("wayTime")] = 0;
        };
        $data[$this->getPrefixed("arrivalTime")] =
            strtotime($data[$this->getPrefixed("arrivalTime")]);
        $data[$this->getPrefixed("withAppointment")] =
            ($data[$this->getPrefixed("withAppointment")])  ? true : false;
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
