<?php

namespace BO\Zmsentities;

use BO\Zmsentities\Helper\Property;

class Appointment extends Schema\Entity
{
    public const PRIMARY = 'id';

    public static ?string $schema = "appointment.json";

    /**
     * @return (Availability|Scope|int)[]
     *
     * @psalm-return array{date: 0, scope: Scope, availability: Availability, slotCount: 0}
     */
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

    public function toTime($lang = 'de'): string
    {
        $suffix = ($lang == 'en') ? ' o\'clock' : ' Uhr';
        return date('H:i', $this->date) . $suffix;
    }

    public function hasTime(): bool
    {
        if ($this->date == 0) {
            return false;
        }
        $time = $this->toDateTime();
        return ('00:00' != $time->format('H:i'));
    }

    /**
     * Modify time for appointment
     */
    public function setTime($timeString): static
    {
        $dateTime = $this->toDateTime();
        $this->date = $dateTime->modify($timeString)->getTimestamp();
        return $this;
    }

    public function setDateTime(\DateTimeInterface $dateTime): static
    {
        $this->date = $dateTime->getTimestamp();
        return $this;
    }

    public function addDate(int $date): static
    {
        $this->date = $date;
        return $this;
    }

    public function addScope($scopeId): static
    {
        $this->getScope()->id = $scopeId;
        return $this;
    }

    public function getScope(): Scope
    {
        $this->scope = ($this->toProperty()->scope->isAvailable()) ? new Scope($this['scope']) : new Scope();
        return $this->scope;
    }

    public function addSlotCount(int|null $slotCount = null): static
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

    public function getAvailability(): Availability
    {
        $data = array();
        if (Property::__keyExists('availability', $this)) {
            $data = $this['availability'];
        }
        return new Availability($data);
    }

    public function toDateTime($timezone = 'Europe/Berlin'): \DateTimeImmutable
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

    public function getEndTimeWithCustomSlotTime($slotTimeInMinutes)
    {
        $time = $this->getStartTime();
        return $time->modify('+' . ($slotTimeInMinutes * $this->slotCount) . ' minutes');
    }

    public function setDateByString(string $dateString, $format = 'Y-m-d H:i'): static
    {
        $appointmentDateTime = \DateTimeImmutable::createFromFormat($format, $dateString);
        if ($appointmentDateTime) {
            $this->date = $appointmentDateTime->format('U');
        } else {
            throw new Exception\DateStringWrongFormat(
                "String " . htmlspecialchars($dateString) . " not format " . htmlspecialchars($format)
            );
        }
        return $this;
    }

    /**
     * Does two appointments match, the matching appointment might have a lower slot count
     */
    public function isMatching(self $appointment): bool
    {
        if (
            $appointment['scope']['id'] == $this['scope']['id']
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
            . " " . $this['slotCount'] . "slots"
            . " scope" . $this['scope']['id']
            ;
    }
}
