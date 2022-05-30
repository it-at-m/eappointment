<?php

namespace BO\Zmsentities;

use \BO\Zmsentities\Helper\Property;

class Appointment extends Schema\Entity
{
    const PRIMARY = 'id';

    public static $schema = "appointment.json";

    public function getDefaults()
    {
        return [
            'date' => 0,
            'scope' => new Scope(),
            'availability' => new Availability(),
            'slotCount' => 0,
        ];
    }

    public function toDate($lang = 'de')
    {
        // Mittwoch 18. November 2015
        return ($lang == 'en') ? date('l F d, Y', $this->date) : Helper\DateTime::getFormatedDates(
            $this->toDateTime(),
            'EEEE dd. MMMM yyyy'
        );
    }

    public function toTime($lang = 'de')
    {
        $suffix = ($lang == 'en') ? ' o\'clock' : ' Uhr';
        return date('H:i', $this->date) . $suffix;
    }

    public function hasTime()
    {
        if ($this->date == 0) {
            return false;
        }
        $time = $this->toDateTime();
        return ('00:00' != $time->format('H:i'));
    }

    /**
     * Modify time for appointment
     *
     */
    public function setTime($timeString)
    {
        $dateTime = $this->toDateTime();
        $this->date = $dateTime->modify($timeString)->getTimestamp();
        return $this;
    }

    public function setDateTime(\DateTimeInterface $dateTime)
    {
        $this->date = $dateTime->getTimestamp();
        return $this;
    }

    public function addDate($date)
    {
        $this->date = $date;
        return $this;
    }

    public function addScope($scopeId)
    {
        $this->getScope()->id = $scopeId;
        return $this;
    }

    public function getScope()
    {
        $this->scope = ($this->toProperty()->scope->isAvailable()) ? new Scope($this['scope']) : new Scope();
        return $this->scope;
    }

    public function addSlotCount($slotCount = null)
    {
        if ($slotCount) {
            $this->slotCount = $slotCount;
        } else {
            $this->slotCount += 1;
        }

        return $this;
    }

    public function getSlotCount()
    {
        return $this->slotCount;
    }

    public function getAvailability()
    {
        $data = array();
        if (Property::__keyExists('availability', $this)) {
            $data = $this['availability'];
        }
        return new Availability($data);
    }

    public function toDateTime($timezone = 'Europe/Berlin')
    {
        $date = (new \DateTimeImmutable())->setTimestamp($this->date);
        //$date = \DateTimeImmutable::createFromFormat("U", $this->date);
        if ($date) {
            $date = $date->setTimeZone(new \DateTimeZone($timezone));
        }
        return $date;
    }

    public function getStartTime()
    {
        $time = $this->toDateTime();
        return $time;
    }

    public function getEndTime()
    {
        $time = $this->getStartTime();
        $availability = $this->getAvailability();
        return ($availability->slotTimeInMinutes)
          ? $time->modify('+' . ($availability->slotTimeInMinutes * $this->slotCount) . ' minutes')
          : $time;
    }

    public function setDateByString($dateString, $format = 'Y-m-d H:i')
    {
        $appointmentDateTime = \DateTimeImmutable::createFromFormat($format, $dateString);
        if ($appointmentDateTime) {
            $this->date = $appointmentDateTime->format('U');
        } else {
            throw new Exception\DateStringWrongFormat(
                "String ".htmlspecialchars($dateString)." not format ". htmlspecialchars($format)
            );
        }
        return $this;
    }

    /**
     * Does two appointments match, the matching appointment might have a lower slot count
     *
     */
    public function isMatching(self $appointment)
    {
        //error_log("Compare $this with $appointment");
        if ($appointment['scope']['id'] == $this['scope']['id']
            && $appointment['date'] == $this['date']
        ) {
            return true;
        }
        return false;
    }

    public function __toString()
    {
        return "appointment#"
            . $this->toDateTime()->format('c')
            . " ".$this['slotCount']."slots"
            . " scope".$this['scope']['id']
            ;
    }
}
