<?php

/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Mellon;

/**
  * Validation of Strings
  * This validation is opinionated: It sanitizes the output from special chars for HTML
  */
class ValidArray extends \BO\Mellon\Valid
{
    /**
     * Allow native arrays anc class replacements
     *
     * @param String $message error message in case of failure
     *
     * @return self
     */
    public function isArray($message = 'no valid array')
    {
        if (
            is_array($this->value)
            || (
                $this->value instanceof \Traversable
                && $this->value instanceof \Countable
                && $this->value instanceof \ArrayAccess
            )
        ) {
            $this->validated = true;
            return $this;
        } else {
            $this->setFailure($message);
        }
        return $this;
    }
}
