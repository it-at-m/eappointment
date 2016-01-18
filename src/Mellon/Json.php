<?php
/**
 * @package Mellon
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Mellon;

/**
  * Validation of JSON input
  *
  * Beware: the function getValue does not return a string, it returns an object or array
  *    The function __toString() or cast like (string)$param returns the JSON-String.
  */
class Json extends \BO\Mellon\Valid
{

    protected $originalJsonString = null;
    protected $defaultJsonString = '{}';

    /**
     * Allow only valid urls
     *
     * @param String $message error message in case of failure, use null for detailled messages
     *
     * @return self
     */
    public function isJson($message = null)
    {
        $this->originalJsonString = $this->value;
        $undeclaredMessage = $message;
        if (null === $message) {
            $undeclaredMessage = "Json: Empty";
        }
        $this->isDeclared($undeclaredMessage);
        $jsonString = $this->value;
        $array = json_decode($jsonString, true);
        if (null === $array && $jsonString) {
            if (null === $message) {
                $json_error = json_last_error();
                $json_error_list = array(
                    JSON_ERROR_UTF8 => 'Malformed UTF-8 characters, possibly incorrectly encoded',
                    JSON_ERROR_DEPTH => 'Maximum stack depth exceeded',
                    JSON_ERROR_CTRL_CHAR => 'Unexpected control character found',
                    JSON_ERROR_STATE_MISMATCH => 'Underflow or the modes mismatch',
                    JSON_ERROR_SYNTAX => 'Syntax error, malformed JSON',
                    JSON_ERROR_NONE => 'No errors',
                );
                $message = 'Json: ' . $json_error_list[$json_error];
            }
            $this->failure($message);
        } else {
            $this->validated = true;
            $this->value = $array;
        }
        return $this;
    }

    /**
     * Set a default value to return if a string does not validate
     *
     * @param Mixed $value
     *
     * @return self
     */
    public function setDefault($value)
    {
            $this->default = $value;
            return $this;
    }

    /**
     * Set a default value to return if a string does not validate
     *
     * @param Mixed $value
     *
     * @return self
     */
    public function setDefaultJson($defaultJsonString)
    {
            $this->defaultJsonString = $defaultJsonString;
            return $this;
    }

    /**
     * Get the validated value or the default value as string
     *
     * @return String
     */
    public function __toString()
    {
        if ($this->hasFailed() || !$this->validated) {
            return $this->defaultJsonString;
        }
        return $this->originalJsonString;
    }
}
