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
class ValidJson extends Valid
{

    protected $originalJsonString = null;
    protected $defaultJsonString = '{}';

    /**
     * Allow only valid json
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
            $this->setFailure($message);
        } elseif (!$jsonString) {
            // Be compatible to javascript JSON.parse()
            $this->setFailure('Json: Empty string');
        } else {
            $this->validated = true;
            $this->value = $array;
        }
        return $this;
    }

    /**
     * Set a default string value if a string does not validate
     * The parsed value like an array or an object should be set by setDefault()
     *
     * @param String $value
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
