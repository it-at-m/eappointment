<?php

namespace BO\Zmsdb\Query;

class Availability extends Base implements MappingInterface
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'oeffnungszeit';

    public function getEntityMapping()
    {
        return [
            'id' => 'availability.OeffnungszeitID',
            'bookable__startInDays' => 'availability.Offen_ab',
            'bookable__endInDays' => 'availability.Offen_bis',
            'description' => 'availability.kommentar',
            'startDate' => self::expression('UNIX_TIMESTAMP(`availability`.`Startdatum`)'),
            'startTime' => self::expression('DATE_FORMAT(`availability`.`Anfangszeit`,"%H:%i")'),
            'endDate' => self::expression('UNIX_TIMESTAMP(`availability`.`Endedatum`)'),
            'endTime' => self::expression('DATE_FORMAT(`availability`.`Endzeit`,"%H:%i")'),
            'multipleSlotsAllowed' => 'availability.erlaubemehrfachslots',
            'repeat__afterWeeks' => 'availability.allexWochen',
            'repeat__weekOfMonth' => 'availability.jedexteWoche',
            'scope__id' => 'availability.StandortID',
            'slotTimeInMinutes' => 'availability.Timeslot',
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

    public function addConditionAvailabilityId($availabilityId)
    {
        $this->query->where('availability.OeffnungszeitID', '=', $availabilityId);
        return $this;
    }

    public function addConditionScopeId($scopeId)
    {
        $this->query->leftJoin(
            new Alias('standort', 'availability_scope'),
            'availability.StandortID',
            '=',
            'availability_scope.StandortID'
        );
        $this->query->where('availability_scope.StandortID', '=', $scopeId);
        return $this;
    }
}
