<?php

namespace BO\Zmsbackend\Availability\Repository;

/**
 * @SuppressWarnings(Public)
 */
class Availability extends \BO\Zmsbackend\Query\Base implements \BO\Zmsbackend\Query\MappingInterface
{
    /**
     * @var String TABLE mysql table reference
     */
    const string TABLE = 'oeffnungszeit';

    const string TEMPORARY_DELETE = 'DELETE FROM oeffnungszeit WHERE `comment` = "--temporary--"';

    const string QUERY_GET_LOCK = '
        SELECT OeffnungszeitID FROM oeffnungszeit WHERE OeffnungszeitID = :availabilityId FOR UPDATE
    ';

    /**
     * @return void
     */
    #[\Override]
    public function addRequiredJoins()
    {
         $this->leftJoin(
             new \BO\Zmsbackend\Query\Alias(\BO\Zmsbackend\Query\Scope::TABLE, 'availabilityscope'),
             'availability.scope_id',
             '=',
             'availabilityscope.StandortID'
         );
    }

    /**
     * @return (\BO\Zmsbackend\Query\Builder\Expression|string)[]
     *
     */
    #[\Override]
    public function getEntityMapping($type = null)
    {
        $mapping = [
            'id' => 'availability.OeffnungszeitID',
            'scope__id' => 'availability.scope_id',
            'bookable__startInDays' => self::expression(
                'CAST(
                    IF(`availability`.`open_from_days` = "0" OR `availability`.`open_from_days`, `availability`.`open_from_days`, `availabilityscope`.`Termine_ab`)
                    AS SIGNED)'
            ),
            'bookable__endInDays' => self::expression(
                'IF((`availability`.`open_from_days` = "0" AND `availability`.`open_until_days` = "0") OR `availability`.`open_until_days`, `availability`.`open_until_days`, `availabilityscope`.`Termine_bis`)'
            ),
            'description' => 'availability.comment',
            'startDate' => 'availability.start_date',
            'startTime' => self::expression(
                'IF(`availability`.`appointment_start_time`,`availability`.`appointment_start_time`,`availability`.`start_time`)'
            ),
            'endDate' => 'availability.end_date',
            'endTime' => self::expression(
                'IF(`availability`.`appointment_start_time`, `availability`.`appointment_end_time`, `availability`.`end_time`)'
            ),
            'lastChange' => 'availability.updated_at',
            'version' => 'availability.version',
            'multipleSlotsAllowed' => 'availability.multiple_slots_allowed',
            'repeat__afterWeeks' => 'availability.every_x_weeks',
            'repeat__weekOfMonth' => 'availability.every_other_week',
            'slotTimeInMinutes' => self::expression('FLOOR(TIME_TO_SEC(`availability`.`time_slot`) / 60)') ,
            // dependant function on this IF(): \BO\Zmsbackend\Availablity\Service\Availablity::readList()
            'type' => self::expression(
                "IF(`availability`.`appointment_start_time`, 'appointment', 'openinghours')"
            ),
            'weekday__monday' => self::expression('`availability`.`weekday` & 2'),
            'weekday__tuesday' => self::expression('`availability`.`weekday` & 4'),
            'weekday__wednesday' => self::expression('`availability`.`weekday` & 8'),
            'weekday__thursday' => self::expression('`availability`.`weekday` & 16'),
            'weekday__friday' => self::expression('`availability`.`weekday` & 32'),
            'weekday__saturday' => self::expression('`availability`.`weekday` & 64'),
            'weekday__sunday' => self::expression('`availability`.`weekday` & 1'),
            'workstationCount__intern' => 'availability.appointment_workstation_count',
            'workstationCount__public' => self::expression(
                'GREATEST(0, `availability`.`appointment_workstation_count` - `availability`.`internet_reduction`)'
            )
        ];
        if ('openinghours' == $type) {
            // Test if following line is needed: type mapping with IF() a few lines before
            //$mapping['type'] = self::expression('"openinghours"');
            $mapping['startTime'] = 'availability.start_time';
            $mapping['endTime'] = 'availability.end_time';
        }
        return $mapping;
    }

    /**
     * @return \BO\Zmsbackend\Query\Builder\Expression[]
     *
     */
    #[\Override]
    public function getReferenceMapping()
    {
        return [
            'scope__$ref' => self::expression('CONCAT("/scope/", `availability`.`scope_id`, "/")'),
        ];
    }

    public function addConditionAvailabilityId(int|string $availabilityId): static
    {
        $this->query->where('availability.OeffnungszeitID', '=', $availabilityId);
        return $this;
    }

    public function addConditionScopeId($scopeId): static
    {
        $this->query->where('availabilityscope.StandortID', '=', $scopeId);
        return $this;
    }

    public function addConditionAppointmentHours(): static
    {
        $this->query
            ->where('availability.appointment_start_time', '!=', '00:00:00')
            ->where('availability.appointment_end_time', '!=', '00:00:00');
        return $this;
    }

    public function addConditionOpeningHours(): static
    {
        $this->query
            ->where('availability.start_time', '!=', '00:00:00')
            ->where('availability.end_time', '!=', '00:00:00');
        return $this;
    }

    /**
     * Used to identify old availabilities as appointment and openinghours
     *
     * @psalm-api
     */
    public function addConditionDoubleTypes(): static
    {
        $this->query
            ->where('availability.appointment_start_time', '!=', '00:00:00')
            ->where('availability.appointment_end_time', '!=', '00:00:00')
            ->where('availability.start_time', '!=', '00:00:00')
            ->where('availability.end_time', '!=', '00:00:00');
        return $this;
    }

    public function addConditionSkipOld(\DateTimeInterface $dateTime): static
    {
        $date = $dateTime->format('Y-m-d');
        $this->query
            ->where('availability.end_date', '>=', $date);
        return $this;
    }

    /**
     * Used to identify availabilities whose End Date was more than 4 weeks ago
     *
     * @psalm-api
     */
    public function addConditionOnlyOld(\DateTimeInterface $dateTime): static
    {
        $date = $dateTime->format('Y-m-d');
        $this->query
            ->where('availability.end_date', '<=', $date);
        return $this;
    }

   /**
     * Identify availabilities between two dates
     */
    public function addConditionTimeframe(\DateTimeInterface $startDate, \DateTimeInterface $endDate): static
    {
        $this->query->where(function (\BO\Zmsbackend\Query\Builder\ConditionBuilder $condition) use ($startDate, $endDate) {
            $condition
                ->andWith('availability.start_date', '<=', $endDate->format('Y-m-d'))
                ->andWith('availability.end_date', '>=', $startDate->format('Y-m-d'));
        });
        return $this;
    }

    public function addConditionDate(\DateTimeInterface $dateTime): static
    {
        $date = $dateTime->format('Y-m-d');
        $this->query
            ->where('availability.start_date', '<=', $date)
            ->where('availability.end_date', '>=', $date);
        //-- match weekday
        $this->query->where(self::expression("availability.weekday & POW(2, DAYOFWEEK('$date') - 1)"), '>=', '1');
        //-- match week
        $this->query->where(self::expression("
            (
                (
                    availability.every_x_weeks
                    AND FLOOR(
                        (
                            FLOOR(UNIX_TIMESTAMP('$date'))
                            - FLOOR(UNIX_TIMESTAMP(availability.start_date)))
                            / 86400
                            / 7
                        )
                        % availability.every_x_weeks = 0
                )
                OR (
                    availability.every_other_week
                    AND (
                        CEIL(DAYOFMONTH('$date') / 7) = availability.every_other_week
                        OR (
                            availability.every_other_week = 5
                            AND CEIL(LAST_DAY('$date') / 7) = CEIL(DAYOFMONTH('$date') / 7)
                        )
                    )
                )
                OR (availability.every_x_weeks = 0 AND availability.every_other_week = 0)
            ) AND 1
            "), '=', '1');
        return $this;
    }

    public function addConditionAppointmentTime(\DateTimeInterface $dateTime): static
    {
        $time = $dateTime->format('H:i:s');
        $this->query->where("availability.appointment_start_time", '<=', $time);
        $this->query->where("availability.appointment_end_time", '>', $time);

        return $this;
    }

    /**
     * @return (int|mixed|string)[]
     *
     */
    public function reverseEntityMapping(\BO\Zmsentities\Availability $entity): array
    {
        $data = array();
        $data['scope_id'] = $entity['scope']['id'];
        $data['open_from_days'] = $entity['bookable']['startInDays'];
        $data['open_until_days'] = $entity['bookable']['endInDays'];
        $data['comment'] = $entity['description'];
        $data['start_date'] = $entity->getStartDateTime()->format('Y-m-d');
        $data['end_date'] = $entity->getEndDateTime()->format('Y-m-d');
        $data['version'] = $entity['version'];
        if ('openinghours' == $entity['type']) {
            $data['start_time'] = $entity['startTime'];
            $data['end_time'] = $entity['endTime'];
            $data['appointment_start_time'] = 0;
            $data['appointment_end_time'] = 0;
        } else {
            $data['start_time'] = 0;
            $data['end_time'] = 0;
            $data['appointment_start_time'] = $entity['startTime'];
            $data['appointment_end_time'] = $entity['endTime'];
        }
        $data['every_x_weeks'] = $entity['repeat']['afterWeeks'];
        $data['every_other_week'] = $entity['repeat']['weekOfMonth'];
        $data['time_slot'] = gmdate("H:i", $entity['slotTimeInMinutes'] * 60);
        $data['multiple_slots_allowed'] = $entity['multipleSlotsAllowed'] ? 1 : 0;
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
        foreach ($entity['weekday'] as $weekday => $isActive) {
            if ($isActive) {
                $wochentagBinaryCoded |= $binaryCodes[$weekday];
            }
        }
        $data['weekday'] = $wochentagBinaryCoded;
        $data['appointment_workstation_count'] = $entity['workstationCount']['intern'];
        $data['internet_reduction'] =
            $entity['workstationCount']['intern'] - $entity['workstationCount']['public'];

        $data = array_filter($data, function ($value) {
            return ($value !== null && $value !== false);
        });
            return $data;
    }

    public static function getJoinExpression(string $process, string $availability): \BO\Zmsbackend\Query\Builder\Expression
    {
        // UNIX_TIMESTAMP is relative here, no dependency to TIMEZONE
        return self::expression("
            $availability.scope_id = $process.StandortID
            AND $availability.OeffnungszeitID IS NOT NULL

            -- match weekday
            AND $availability.weekday & POW(2, DAYOFWEEK($process.Datum) - 1)

            -- match week
            AND (
                (
                    $availability.every_x_weeks
                    AND FLOOR(
                        (
                            FLOOR(UNIX_TIMESTAMP($process.Datum))
                            - FLOOR(UNIX_TIMESTAMP($availability.start_date)))
                            / 86400
                            / 7
                        )
                        % $availability.every_x_weeks = 0
                )
                OR (
                    $availability.every_other_week
                    AND (
                        CEIL(DAYOFMONTH($process.Datum) / 7) = $availability.every_other_week
                        OR (
                            $availability.every_other_week = 5
                            AND CEIL(LAST_DAY($process.Datum) / 7) = CEIL(DAYOFMONTH($process.Datum) / 7)
                        )
                    )
                )
                OR ($availability.every_x_weeks = 0 AND $availability.every_other_week = 0)
            )

            -- match time and date
            AND $process.Uhrzeit >= $availability.appointment_start_time
            AND $process.Uhrzeit < $availability.appointment_end_time
            AND $process.Datum >= $availability.start_date
            AND $process.Datum <= $availability.end_date
            ");
    }

    #[\Override]
    public function postProcess($data)
    {
        $startDateKey = $this->getPrefixed("startDate");
        $endDateKey = $this->getPrefixed("endDate");
        $lastChangeKey = $this->getPrefixed("lastChange");
        $startDate = $data[$startDateKey] ?? null;
        $endDate = $data[$endDateKey] ?? null;
        $lastChange = $data[$lastChangeKey] ?? null;
        $data[$startDateKey] = $startDate !== null ? (new \DateTime($startDate))->getTimestamp() : null;
        $data[$endDateKey] = $endDate !== null ? (new \DateTime($endDate))->getTimestamp() : null;
        $data[$lastChangeKey] = $lastChange !== null ? (new \DateTime($lastChange . \BO\Zmsbackend\Connection\Select::$connectionTimezone))->getTimestamp() : null;
        return $data;
    }
}
