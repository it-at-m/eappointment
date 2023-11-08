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
class ValidUrl extends ValidString
{
    /**
     * Allow only valid urls
     *
     * @param String $message error message in case of failure
     *
     * @return self
     */
    public function isUrl($message = 'no valid url')
    {
        $this->isDeclared($message);
        $this->isString($message);
        return $this->validate($message, FILTER_VALIDATE_URL);
    }
}
