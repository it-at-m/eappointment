<?php

namespace BO\Zmsdb\Query;

class Availability extends Base implements MappingInterface
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'oeffnungszeit';

    public function addRequiredJoins()
    {
         $this->query->leftJoin(
             new Alias(Scope::TABLE, 'scope'),
             'availability.StandortID',
             '=',
             'scope.StandortID'
         );
    }

    public function getEntityMapping($type = 'appointment')
    {
        $type = (! $type) ? 'appointment' : $type;
        return [
            'id' => 'availability.OeffnungszeitID',
            'scope__id' => 'availability.StandortID',
            'bookable__startInDays' => self::expression(
                'CAST(IF(`availability`.`Offen_ab`, `availability`.`Offen_ab`, `scope`.`Termine_ab`) AS SIGNED)'
            ),
            'bookable__endInDays' => self::expression(
                'IF(`availability`.`Offen_bis`, `availability`.`Offen_bis`, `scope`.`Termine_bis`)'
            ),
            'description' => 'availability.kommentar',
            'startDate' => 'availability.Startdatum',
            'startTime' => ('appointment' == $type) ? 'availability.Terminanfangszeit' : 'availability.Anfangszeit',
            'endDate' => 'availability.Endedatum',
            'endTime' => ('appointment' == $type) ? 'availability.Terminendzeit' : 'availability.Endzeit',
            'multipleSlotsAllowed' => 'availability.erlaubemehrfachslots',
            'repeat__afterWeeks' => 'availability.allexWochen',
            'repeat__weekOfMonth' => 'availability.jedexteWoche',
            'slotTimeInMinutes' => self::expression('FLOOR(TIME_TO_SEC(`availability`.`Timeslot`) / 60)') ,
            'type' => self::expression('"'. $type .'"') ,
            'weekday__monday' => self::expression('`availability`.`Wochentag` & 2'),
            'weekday__tuesday' => self::expression('`availability`.`Wochentag` & 4'),
            'weekday__wednesday' => self::expression('`availability`.`Wochentag` & 8'),
            'weekday__thursday' => self::expression('`availability`.`Wochentag` & 16'),
            'weekday__friday' => self::expression('`availability`.`Wochentag` & 32'),
            'weekday__saturday' => self::expression('`availability`.`Wochentag` & 64'),
            'weekday__sunday' => self::expression('`availability`.`Wochentag` & 1'),
            'workstationCount__callcenter' => self::expression(
                'GREATEST(0, `availability`.`Anzahlterminarbeitsplaetze` - `availability`.`reduktionTermineCallcenter`)'
            ),
            'workstationCount__intern' => 'availability.Anzahlterminarbeitsplaetze',
            'workstationCount__public' => self::expression(
                'GREATEST(0, `availability`.`Anzahlterminarbeitsplaetze` - `availability`.`reduktionTermineImInternet`)'
            )
        ];
    }

    public function getReferenceMapping()
    {
        return [
            'scope__$ref' => self::expression('CONCAT("/scope/", `availability`.`StandortID`, "/")'),
        ];
    }

    public function addConditionAvailabilityId($availabilityId)
    {
        $this->query->where('availability.OeffnungszeitID', '=', $availabilityId);
        return $this;
    }

    public function addConditionScopeId($scopeId)
    {
        $this->query->where('scope.StandortID', '=', $scopeId);
        return $this;
    }
    
    public function addConditionDate($date)
    {
        $this->query
            ->where('availability.Startdatum', '<=', $date)
            ->where('availability.Endedatum', '>=', $date);
        return $this;
    }

    /*
     * Todo
     * Es muss noch nach Typ unterschieden werden appointment und openinghours
     */
    public function reverseEntityMapping(\BO\Zmsentities\Availability $entity)
    {
        $data = array();
        $data['StandortID'] = $entity->scope['id'];
        $data['Offen_ab'] = $entity->bookable['startInDays'];
        $data['Offen_bis'] = $entity->bookable['endInDays'];
        $data['kommentar'] = $entity->description;
        $data['Startdatum'] = (new \DateTimeImmutable('@'. $entity->startDate))->format('Y-m-d');
        $data['Endedatum'] = (new \DateTimeImmutable('@'. $entity->endDate))->format('Y-m-d');
        $data['Terminanfangszeit'] = $entity->startTime;
        $data['Terminendzeit'] = $entity->endTime;
        $data['allexWochen'] = (isset($entity->repeat['afterWeeks'])) ? 1 : 0;
        $data['jedexteWoche'] = (isset($entity->repeat['weekOfMonth'])) ? 1 : 0;
        $data['Timeslot'] = gmdate("H:i", $entity->slotTimeInMinutes * 60);
        ;
        $data['Wochentag'] = current((array_filter(array_values($entity->weekday))));
        $data['Anzahlterminarbeitsplaetze'] = $entity->workstationCount['intern'];
        $data['reduktionTermineImInternet'] =
            $entity->workstationCount['intern'] - $entity->workstationCount['public'];
        $data['reduktionTermineCallcenter'] =
            $entity->workstationCount['intern'] - $entity->workstationCount['callcenter'];

        $data = array_filter($data, function ($value) {
            return ($value !== null && $value !== false);
        });
            return $data;
    }

    public static function getJoinExpression($process, $availability)
    {
        // UNIX_TIMESTAMP is relative here, no dependency to TIMEZONE
        return self::expression("
            $availability.StandortID = $process.StandortID
            AND $availability.OeffnungszeitID IS NOT NULL

            -- ignore availability without appointment slots
            AND $availability.Anzahlterminarbeitsplaetze != 0

            -- match weekday
            AND $availability.Wochentag & POW(2, DAYOFWEEK($process.Datum) - 1)

            -- match week
            AND (
                (
                    $availability.allexWochen
                    AND FLOOR(
                        (
                            FLOOR(UNIX_TIMESTAMP($process.Datum))
                            - FLOOR(UNIX_TIMESTAMP($availability.Startdatum)))
                            / 86400
                            / 7
                        )
                        % $availability.allexWochen = 0
                )
                OR (
                    $availability.jedexteWoche
                    AND (
                        CEIL(DAYOFMONTH($process.Datum) / 7) = $availability.jedexteWoche
                        OR (
                            $availability.jedexteWoche = 5
                            AND CEIL(LAST_DAY($process.Datum) / 7) = CEIL(DAYOFMONTH($process.Datum) / 7)
                        )
                    )
                )
            )

            -- match time and date
            AND $process.Uhrzeit >= $availability.Terminanfangszeit
            AND $process.Uhrzeit < $availability.Terminendzeit
            AND $process.Datum >= $availability.Startdatum
            AND $process.Datum <= $availability.Endedatum
            ");
    }

    public function postProcess($data)
    {
        $data["startDate"] = (new \DateTime($data["startDate"]))->getTimestamp();
        $data["endDate"] = (new \DateTime($data["endDate"]))->getTimestamp();
        return $data;
    }
}
