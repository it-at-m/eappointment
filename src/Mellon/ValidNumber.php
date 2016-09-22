<?php
/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Mellon;

/**
  * Validation of Strings
  * This validation is opinionated: It sanitizes the output from special chars for HTML
  */
class ValidNumber extends Valid
{

    /**
     * Allow only integer numbers
     *
     * @param String $message error message in case of failure
     *
     * @return self
     */
    public function isNumber($message = 'no valid number')
    {
        return $this->validate($message, FILTER_VALIDATE_INT);
    }
}
