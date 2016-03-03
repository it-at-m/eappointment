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
class Date extends \BO\Mellon\Valid
{
    /**
     * Allow only valid date format
     *
     * @param String $message error message in case of failure
     *
     * @return self
     */
     public function isDate($format = 'Y-m-d', $message = 'no valid date')
    {
        $this->validated = true;
        $date = \DateTime::createFromFormat($format, $this->value);
        if($date && $date->format($format) == $this->value) {
            return $this->validate($message, FILTER_SANITIZE_SPECIAL_CHARS);
        }
        return $this->validate($message, false);
    }
}
