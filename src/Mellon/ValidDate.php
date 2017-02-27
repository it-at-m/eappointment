<?php
/**
 * @package Mellon
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Mellon;

/**
  * Validation of URLs
  *
  */
class ValidDate extends Valid
{
    /**
     * Allow only valid date format
     *
     * @param String $message error message in case of failure
     *
     * @return self
     */
    public function isDate($format = 'U', $message = 'no valid date')
    {
        $this->validated = true;
        if (null !== $this->value) {
            $selectedDate = \DateTime::createFromFormat($format, $this->value);
            //$selectedDate->setTimezone(new \DateTimeZone(\App::TIMEZONE));
            $isDate = (bool)strtotime($selectedDate->format('Y-m-d'));
            if (false === $isDate) {
                $this->setFailure($message);
            }
        } else {
            $this->setFailure($message);
        }
        return $this->validate($message, FILTER_VALIDATE_INT);
    }
}
