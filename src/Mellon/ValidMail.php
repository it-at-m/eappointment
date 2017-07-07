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

    public function hasMX($message = 'no valid DNS entry of type MX found')
    {
        $this->validated = true;
        if (null !== $this->value) {
            $domain = substr($this->value, strpos($this->value, '@') + 1);
            $hasMX = checkdnsrr($domain, 'MX');
            if (false === $hasMX) {
                $this->setFailure($message);
            }
        } else {
            $this->setFailure($message);
        }
        return $this;
    }
}
