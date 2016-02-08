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
     public function isDate($message = 'no valid date')
    {
        $format = 'Y-m-d';
        $d = \DateTime::createFromFormat($format, $this->value);
        if($d && $d->format($format) == $this->value) {
            return $this->validate($message, FILTER_SANITIZE_SPECIAL_CHARS);
        }
        return $this->validate($message, false);
    }
}
