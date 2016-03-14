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
     public function isDate($format = 'U', $message = 'no valid date')
    {
        $this->validated = true;
        $date = \DateTime::createFromFormat($format, $this->value);
        $isDate = (bool)strtotime($date->format('Y-m-d'));
        if (false === $isDate) {
            $this->failure($message);
        }
        return $this;
    }
}
