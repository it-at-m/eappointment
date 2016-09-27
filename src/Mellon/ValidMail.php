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
class ValidMail extends \BO\Mellon\ValidString
{
    /**
     * Allow only valid mails
     *
     * @param String $message error message in case of failure
     *
     * @return self
     */
    public function isMail($message = 'no valid email')
    {
        $this->isString($message);
        $this->validate($message, FILTER_SANITIZE_EMAIL);
        return $this->validate($message, FILTER_VALIDATE_EMAIL);
    }
}
