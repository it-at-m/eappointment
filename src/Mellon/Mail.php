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
class Mail extends \BO\Mellon\Valid
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
        $email = $this->validate($message, FILTER_SANITIZE_EMAIL);
        if($email){
            $this->isDeclared($message);
            $this->isString($message);
            $email = $this->validate($message, FILTER_VALIDATE_EMAIL);
        }
        return $email;
    }
}
