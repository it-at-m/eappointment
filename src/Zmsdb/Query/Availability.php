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

    public function getEntityMapping()
    {
        return [
            'id' => 'availability.OeffnungszeitID',
            'scope__id' => 'availability.StandortID',
            'bookable__startInDays' => self::expression(
                'IF(`availability`.`Offen_ab`, `availability`.`Offen_ab`, `scope`.`Termine_ab`)'
            ),
            'bookable__endInDays' => self::expression(
                'IF(`availability`.`Offen_bis`, `availability`.`Offen_bis`, `scope`.`Termine_bis`)'
            ),
            'description' => 'availability.kommentar',
            'startDate' => self::expression('UNIX_TIMESTAMP(`availability`.`Startdatum`)'),
            'startTime' => 'availability.Terminanfangszeit',
            'endDate' => self::expression('UNIX_TIMESTAMP(`availability`.`Endedatum`)'),
            'endTime' => 'availability.Terminendzeit',
            'multipleSlotsAllowed' => 'availability.erlaubemehrfachslots',
            'repeat__afterWeeks' => 'availability.allexWochen',
            'repeat__weekOfMonth' => 'availability.jedexteWoche',
            'slotTimeInMinutes' => self::expression('FLOOR(TIME_TO_SEC(`availability`.`Timeslot`) / 60)') ,
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
        $this->query->where('StandortID', '=', $scopeId);
        return $this;
    }

    public function addConditionTime(\DateTimeInterface $time)
    {
        $time = \BO\Zmsentites\Helper\DateTime::create($time);
    }

    public static function getJoinExpression($process, $availability)
    {
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
                        (UNIX_TIMESTAMP($process.Datum) - UNIX_TIMESTAMP($availability.Startdatum)) / 86400 / 7)
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
}
