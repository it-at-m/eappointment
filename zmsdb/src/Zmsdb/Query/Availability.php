<?php

namespace BO\Zmsdb\Query;

/**
 * @SuppressWarnings(Public)
 */
class Availability extends Base implements MappingInterface
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'oeffnungszeit';

    const TEMPORARY_DELETE = 'DELETE FROM oeffnungszeit WHERE kommentar = "--temporary--"';

    const QUERY_GET_LOCK = '
        SELECT OeffnungszeitID FROM oeffnungszeit WHERE OeffnungszeitID = :availabilityId FOR UPDATE
    ';

    public function addRequiredJoins()
    {
         $this->leftJoin(
             new Alias(Scope::TABLE, 'availabilityscope'),
             'availability.StandortID',
             '=',
             'availabilityscope.StandortID'
         );
    }

    public function getEntityMapping($type = null)
    {
        $mapping = [
            'id' => 'availability.OeffnungszeitID',
            'scope__id' => 'availability.StandortID',
            'bookable__startInDays' => self::expression(
                'CAST(
                    IF(`availability`.`Offen_ab` = "0" OR `availability`.`Offen_ab`, `availability`.`Offen_ab`, `availabilityscope`.`Termine_ab`)
                    AS SIGNED)'
            ),
            'bookable__endInDays' => self::expression(
                'IF(`availability`.`Offen_bis`, `availability`.`Offen_bis`, `availabilityscope`.`Termine_bis`)'
            ),
            'description' => 'availability.kommentar',
            'startDate' => 'availability.Startdatum',
            'startTime' => self::expression(
                'IF(`availability`.`Terminanfangszeit`,`availability`.`Terminanfangszeit`,`availability`.`Anfangszeit`)'
            ),
            'endDate' => 'availability.Endedatum',
            'endTime' => self::expression(
                'IF(`availability`.`Terminanfangszeit`, `availability`.`Terminendzeit`, `availability`.`Endzeit`)'
            ),
            'lastChange' => 'availability.updateTimestamp',
            'multipleSlotsAllowed' => 'availability.erlaubemehrfachslots',
            'repeat__afterWeeks' => 'availability.allexWochen',
            'repeat__weekOfMonth' => 'availability.jedexteWoche',
            'slotTimeInMinutes' => self::expression('FLOOR(TIME_TO_SEC(`availability`.`Timeslot`) / 60)') ,
            // dependant function on this IF(): \BO\Zmsdb\Availablity::readList()
            'type' => self::expression(
                "IF(`availability`.`Terminanfangszeit`, 'appointment', 'openinghours')"
            ),
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
        if ('openinghours' == $type) {
            // Test if following line is needed: type mapping with IF() a few lines before
            //$mapping['type'] = self::expression('"openinghours"');
            $mapping['startTime'] = 'availability.Anfangszeit';
            $mapping['endTime'] = 'availability.Endzeit';
        }
        return $mapping;
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
        $this->query->where('availabilityscope.StandortID', '=', $scopeId);
        return $this;
    }

    public function addConditionAppointmentHours()
    {
        $this->query
            ->where('availability.Terminanfangszeit', '!=', '00:00:00')
            ->where('availability.Terminendzeit', '!=', '00:00:00');
        return $this;
    }

    public function addConditionOpeningHours()
    {
        $this->query
            ->where('availability.Anfangszeit', '!=', '00:00:00')
            ->where('availability.Endzeit', '!=', '00:00:00');
        return $this;
    }

    /**
     * Used to identify old availabilities as appointment and openinghours
     *
     */
    public function addConditionDoubleTypes()
    {
        $this->query
            ->where('availability.Terminanfangszeit', '!=', '00:00:00')
            ->where('availability.Terminendzeit', '!=', '00:00:00')
            ->where('availability.Anfangszeit', '!=', '00:00:00')
            ->where('availability.Endzeit', '!=', '00:00:00');
        return $this;
    }

    public function addConditionSkipOld(\DateTimeInterface $dateTime)
    {
        $date = $dateTime->format('Y-m-d');
        $this->query
            ->where('availability.Endedatum', '>=', $date);
        return $this;
    }

    /**
     * Used to identify availabilities whose End Date was more than 4 weeks ago
     *
     */
    public function addConditionOnlyOld(\DateTimeInterface $dateTime)
    {
        $date = $dateTime->format('Y-m-d');
        $this->query
            ->where('availability.Endedatum', '<=', $date);
        return $this;
    }

   /**
     * Identify availabilities between two dates
     *
     */
    public function addConditionTimeframe(\DateTimeInterface $startDate, \DateTimeInterface $endDate)
    {
        $this->query->where(function (\Solution10\SQL\ConditionBuilder $condition) use ($startDate, $endDate) {
            $condition
                ->andWith('availability.Startdatum', '<=', $endDate->format('Y-m-d'))
                ->andWith('availability.Endedatum', '>=', $startDate->format('Y-m-d'));
        });
        return $this;
    }

    public function addConditionDate(\DateTimeInterface $dateTime)
    {
        $date = $dateTime->format('Y-m-d');
        $this->query
            ->where('availability.Startdatum', '<=', $date)
            ->where('availability.Endedatum', '>=', $date);
        //-- match weekday
        $this->query->where(self::expression("availability.Wochentag & POW(2, DAYOFWEEK('$date') - 1)"), '>=', '1');
        //-- match week
        $this->query->where(self::expression("
            (
                (
                    availability.allexWochen
                    AND FLOOR(
                        (
                            FLOOR(UNIX_TIMESTAMP('$date'))
                            - FLOOR(UNIX_TIMESTAMP(availability.Startdatum)))
                            / 86400
                            / 7
                        )
                        % availability.allexWochen = 0
                )
                OR (
                    availability.jedexteWoche
                    AND (
                        CEIL(DAYOFMONTH('$date') / 7) = availability.jedexteWoche
                        OR (
                            availability.jedexteWoche = 5
                            AND CEIL(LAST_DAY('$date') / 7) = CEIL(DAYOFMONTH('$date') / 7)
                        )
                    )
                )
                OR (availability.allexWochen = 0 AND availability.jedexteWoche = 0)
            ) AND 1
            "), '=', '1');
        return $this;
    }

    public function addConditionAppointmentTime(\DateTimeInterface $dateTime)
    {
        $time = $dateTime->format('H:i:s');
        $this->query->where("availability.Terminanfangszeit", '<=', $time);
        $this->query->where("availability.Terminendzeit", '>', $time);

        return $this;
    }

    public function reverseEntityMapping(\BO\Zmsentities\Availability $entity)
    {
        $data = array();
        $data['StandortID'] = $entity->scope['id'];
        $data['Offen_ab'] = $entity->bookable['startInDays'];
        $data['Offen_bis'] = $entity->bookable['endInDays'];
        $data['kommentar'] = $entity->description;
        $data['Startdatum'] = $entity->getStartDateTime()->format('Y-m-d');
        $data['Endedatum'] = $entity->getEndDateTime()->format('Y-m-d');
        if ('openinghours' == $entity->type) {
            $data['Anfangszeit'] = $entity->startTime;
            $data['Endzeit'] = $entity->endTime;
            $data['Terminanfangszeit'] = 0;
            $data['Terminendzeit'] = 0;
        } else {
            $data['Anfangszeit'] = 0;
            $data['Endzeit'] = 0;
            $data['Terminanfangszeit'] = $entity->startTime;
            $data['Terminendzeit'] = $entity->endTime;
        }
        $data['allexWochen'] = $entity->repeat['afterWeeks'];
        $data['jedexteWoche'] = $entity->repeat['weekOfMonth'];
        $data['Timeslot'] = gmdate("H:i", $entity->slotTimeInMinutes * 60);
        $data['erlaubemehrfachslots'] = $entity->multipleSlotsAllowed ? 1 : 0;
        $wochentagBinaryCoded = 0;
        $binaryCodes = [
            'sunday' => 1,
            'monday' => 2,
            'tuesday' => 4,
            'wednesday' => 8,
            'thursday' => 16,
            'friday' => 32,
            'saturday' => 64,
            ];
        foreach ($entity->weekday as $weekday => $isActive) {
            if ($isActive) {
                $wochentagBinaryCoded |= $binaryCodes[$weekday];
            }
        }
        $data['Wochentag'] = $wochentagBinaryCoded;
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
                OR (availability.allexWochen = 0 AND availability.jedexteWoche = 0)
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
        $data[$this->getPrefixed("startDate")] =
            (new \DateTime($data[$this->getPrefixed("startDate")]))->getTimestamp();
        $data[$this->getPrefixed("endDate")] =
            (new \DateTime($data[$this->getPrefixed("endDate")]))->getTimestamp();
        $data[$this->getPrefixed("lastChange")] =
            (new \DateTime($data[$this->getPrefixed("lastChange")] . \BO\Zmsdb\Connection\Select::$connectionTimezone))
            ->getTimestamp();
        return $data;
    }
}
