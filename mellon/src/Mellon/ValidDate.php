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
        if ($this->value) {
            $selectedDate = \DateTime::createFromFormat($format, $this->value);
            //$selectedDate->setTimezone(new \DateTimeZone(\App::TIMEZONE));
            $isDate = $selectedDate->getTimestamp() > 0;
            if (false === $isDate) {
                $this->setFailure($message);
            }
        } else {
            $this->setFailure($message);
        }
        return $this;
    }
}
