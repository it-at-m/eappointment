<?php

/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Mellon;

use DateTimeImmutable;
use DateTime;

class ValidDatetime extends Valid
{
    protected DateTimeImmutable|false|null $dateTime = null;

    public function isDatetime(
        string $message = 'Please enter a valid date',
        string|false $format = false
    ): Valid {
        $this->validated = true;
        $date = $this->value;
        if ($date) {
            if ($format) {
                $dateTime = DateTime::createFromFormat($format, $date);
            } else {
                    $dateTime = date_create($date);
            }
            if (false === $dateTime) {
                    return $this->setFailure($message);
            }
            $this->dateTime = DateTimeImmutable::createFromFormat(
                'Y-m-d\TH:i:s.uP',
                $dateTime->format('Y-m-d\TH:i:s.uP')
            );
        }
        return $this;
    }

    public function isOldEnough(int $years = 18, string $message = 'Minimum age of 18 years is required'): Valid
    {
        if ($this->dateTime instanceof \DateTimeInterface) {
            $now = new DateTime();
            $interval = $this->dateTime->diff($now, true);
            if ($interval->y < $years) {
                   return $this->setFailure($message);
            }
        }
        return $this;
    }
}
