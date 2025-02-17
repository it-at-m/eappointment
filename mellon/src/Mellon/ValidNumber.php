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

    /**
     * Allow only numbers greater than the given value
     *
     * @param Int $number value to compare
     * @param String $message error message in case of failure
     *
     * @return self
     */
    public function isGreaterThan($number, $message = 'too small')
    {
        $this->validated = true;
        if ($this->value < $number) {
            $this->setFailure($message);
        }
        return $this;
    }

    /**
     * Allow only numbers greater or equal than the given value
     *
     * @param Int $number value to compare
     * @param String $message error message in case of failure
     *
     * @return self
     */
    public function isGreaterEqualThan($number, $message = 'too small')
    {
        $this->validated = true;
        if ($this->value <= $number) {
            $this->setFailure($message);
        }
        return $this;
    }

    /**
     * Allow only numbers lower or equal than the given value
     *
     * @param Int $number value to compare
     * @param String $message error message in case of failure
     *
     * @return self
     */
    public function isLowerEqualThan($number, $message = 'too small')
    {
        $this->validated = true;
        if ($this->value >= $number) {
            $this->setFailure($message);
        }
        return $this;
    }

    /**
     * Allow only numbers lower than the given value
     *
     * @param Int $number value to compare
     * @param String $message error message in case of failure
     *
     * @return self
     */
    public function isLowerThan($number, $message = 'too small')
    {
        $this->validated = true;
        if ($this->value > $number) {
            $this->setFailure($message);
        }
        return $this;
    }
}
