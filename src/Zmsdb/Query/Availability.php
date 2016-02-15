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
        	'weekday__monday' => self::expression('CASE WHEN `availability`.`Wochentag` = 2 THEN true ELSE false END'),
        	'weekday__tuesday' => self::expression('CASE WHEN `availability`.`Wochentag` = 4 THEN true ELSE false END'),
        	'weekday__wednesday' => self::expression('CASE WHEN `availability`.`Wochentag` = 8 THEN true ELSE false END'),
        	'weekday__thursday' => self::expression('CASE WHEN `availability`.`Wochentag` = 16 THEN true ELSE false END'),
        	'weekday__friday' => self::expression('CASE WHEN `availability`.`Wochentag` = 32 THEN true ELSE false END'),
        	'weekday__saturday' => self::expression('CASE WHEN `availability`.`Wochentag` = 64 THEN true ELSE false END'),
        	'weekday__sunday' => self::expression('CASE WHEN `availability`.`Wochentag` = 1 THEN true ELSE false END'),
        	'workstationCount__callcenter' => self::expression('GREATEST(0, `availability`.`Anzahlterminarbeitsplaetze` - `availability`.`reduktionTermineCallcenter`)'),
        	'workstationCount__intern' => 'availability.Anzahlterminarbeitsplaetze',
        	'workstationCount__public' => self::expression('GREATEST(0, `availability`.`Anzahlterminarbeitsplaetze` - `availability`.`reduktionTermineImInternet`)')
        ];
    }

    public function addConditionAvailabilityId($availabilityId)
    {
        $this->query->where('availability.OeffnungszeitID', '=', $availabilityId);
        return $this;
    }
    
}
