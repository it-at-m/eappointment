<?php
/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Mellon;

/**
  * Validation of Strings
  * This validation is opinionated: It sanitizes the output from special chars for HTML
  */
class ValidBool extends Valid
{

    /**
     * Allow only boolean values like
     * Allowed values are:
     *   true
     *   false
     *   yes
     *   no
     *   on
     *   off
     *   1
     *   0
     *   ''
     *
     * @param String $message error message in case of failure
     *
     * @return self
     */
    public function isBool($message = 'not a boolean value')
    {
        return $this->validate($message, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }
}
