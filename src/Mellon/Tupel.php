<?php

/**
 *
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 *
 */
namespace BO\Mellon;

/**
 * Validation of Strings
 * This validation is opinionated: It sanitizes the output from special chars for HTML
 */
class Tupel extends \BO\Mellon\Valid
{

    /**
     * checks if is a valid array (tupel)
     *
     * @param Tupel $message
     *            error message in case of failure
     *
     * @return self
     */
    public function isTupel($message = 'no valid array')
    {
        if (is_array($this->value)) {
            $this->default = array();
            $this->validated = true;
            return $this;
        }
        return $this->failure($message);
    }
}
