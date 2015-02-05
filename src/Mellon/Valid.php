<?php
/**
 * @package Mellon
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Mellon;

/**
  * Parameter validation
  *
  */
class Valid extends \BO\Mellon\Parameter
{

    /**
      * validation errors
      *
      * @var Array $messages
      */
    protected $messages = array();

    /**
      * TRUE if value is validated, at least once
      *
      * @var Bool $validated
      */
    protected $validated = false;

    /**
      * TRUE if validation failed
      *
      * @var Bool $failed
      */
    protected $failed = false;

    /**
      * default value to return
      *
      * @var String $default
      */
    protected $default = null;

    /**
     * @return self
     */
    protected function validate($message, $filter, $options = null)
    {
        if (null !== $this->value) {
            $this->validated = true;
            $filtered = filter_var($this->value, $filter, $options);
            if ($filtered === false && $this->value !== false) {
                $this->failure($message);
            } else {
                $this->value = $filtered;
            }
        }
        return $this;
    }

    /**
     * @return self
     */
    protected function failure($message)
    {
        $this->failed = true;
        $this->value = $this->default;
        $this->messages[] = $message;
        return $this;
    }

    /**
     * @return self
     */
    public function isBool($message = 'not a boolean value')
    {
        return $this->validate($message, FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * @return self
     */
    public function isNumber($message = 'no valid number')
    {
        return $this->validate($message, FILTER_VALIDATE_INT);
    }

    /**
     * @return self
     */
    public function isString($message = 'no valid string')
    {
        if (strlen($this->value) > 65536) {
            $this->failure($message);
        }
        return $this->validate($message, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
    }

    /**
     * @return self
     */
    public function getValue()
    {
        if ($this->validated) {
            return $this->value;
        }
        return $this->default;
    }

    /**
     * @return String
     */
    public function __toString()
    {
        $value = $this->getValue();
        if (null === $value) {
            $value = '';
        }
        return (string)$value;
    }

    /**
     * @return self
     */
    public function setDefault($value)
    {
            $this->default = $value;
            return $this;
    }
}
