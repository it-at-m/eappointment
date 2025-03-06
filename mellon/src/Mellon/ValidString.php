<?php

/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Mellon;

/**
  * Validation of Strings
  * This validation is opinionated: It sanitizes the output from special chars for HTML
  */
class ValidString extends Valid
{
    /**
     * Allow strings smaller than 64kb and do htmlspecialchars()
     *
     * @param String $message error message in case of failure
     *
     * @return self
     */
    public function isString($message = 'no valid string', $sanitize = true)
    {
        $this->isSmallerThan(65536, $message);
        if ($sanitize) {
            return $this->validate($message, FILTER_SANITIZE_SPECIAL_CHARS);
        }

        return $this;
    }

    /**
     * Allow only strings which do not match a given regular expression
     *
     * @param String $regex Regular expression including delimiter and modifier
     * @param String $message error message in case of failure
     *
     * @return self
     */
    public function isFreeOf($regex, $message = 'value contains undesired content')
    {
        $this->validated = true;
        if (preg_match($regex, $this->value)) {
            $this->setFailure($message);
        }
        return $this;
    }

    /**
     * Allow only strings which match a given regular expression
     *
     * @param String $regex Regular expression including delimiter and modifier
     * @param String $message error message in case of failure
     *
     * @return self
     */
    public function isMatchOf($regex, $message = 'not a valid matching value')
    {
        $this->isDeclared($message);
        return $this->validate($message, FILTER_VALIDATE_REGEXP, array(
            'options' => array(
                'regexp' => $regex,
            ),
        ));
    }

    /**
     * Allow only strings with a length bigger than the given value
     *
     * @param Int $size value to compare length of the string
     * @param String $message error message in case of failure
     *
     * @return self
     */
    public function isBiggerThan($size, $message = 'too small')
    {
        $this->validated = true;
        if (strlen($this->value) < $size) {
            $this->setFailure($message);
        }
        return $this;
    }

    /**
     * Allow only strings with a length smaller than the given value
     *
     * @param Int $size value to compare length of the string
     * @param String $message error message in case of failure
     *
     * @return self
     */
    public function isSmallerThan($size, $message = 'too big')
    {
        $this->validated = true;
        if (strlen($this->value) > $size) {
            $this->setFailure($message);
        }
        return $this;
    }
}
