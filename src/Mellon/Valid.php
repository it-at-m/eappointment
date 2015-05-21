<?php
/**
 * @package Mellon
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Mellon;

/**
  * Parameter validation
  *
  * @SuppressWarnings(TooManyMethods)
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
     * validate a value using PHP builtin function filter_var()
     *
     * @param String $message error message in case of failure
     * @param Const $filter see documentation for filter_var()
     * @param Array|Const $options see documentation for filter_var()
     *
     * @return self
     */
    protected function validate($message, $filter, $options = null)
    {
        if (null !== $this->value) {
            $this->validated = true;
            $filtered = filter_var($this->value, $filter, $options);
            if (($filtered === false && $filter !== FILTER_VALIDATE_BOOLEAN) || $filtered === null) {
                $this->failure($message);
            } else {
                $this->value = $filtered;
            }
        }
        return $this;
    }

    /**
     * Set state to failed and add a message
     *
     * @param String $message error message in case of failure
     *
     * @return self
     */
    protected function failure($message)
    {
        $this->failed = true;
        $this->messages[] = $message;
        return $this;
    }

    /**
     * Do not allow NULL as value
     *
     * @param String $message error message in case of failure
     *
     * @return self
     */
    public function isDeclared($message = 'value is not declared')
    {
        if (null === $this->value) {
            $this->failure($message);
        }
        return $this;
    }

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
     * Allow strings smaller than 64kb and do htmlspecialchars()
     *
     * @param String $message error message in case of failure
     *
     * @return self
     */
    public function isString($message = 'no valid string')
    {
        $this->isSmallerThan(65536, $message);
        return $this->validate($message, FILTER_SANITIZE_FULL_SPECIAL_CHARS);
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
            $this->failure($message);
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
            $this->failure($message);
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
            $this->failure($message);
        }
        return $this;
    }

    /**
     * Get the validated value or the default value
     *
     * @return Mixed
     */
    public function getValue()
    {
        if ($this->hasFailed() || !$this->validated) {
            return $this->default;
        }
        return $this->value;
    }

    /**
     * Get the validated valie or the default value as string
     *
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
     * True if validation has failed
     *
     * @return Bool
     */
    public function hasFailed()
    {
        return $this->failed;
    }

    /**
     * Returns a list of error messages
     *
     * @return Array
     */
    public function getMessages()
    {
        return $this->messages;
    }

    /**
     * Returns a hash for usage in templates engines
     * Contains the following keys:
     *     failed - True if validation has failed
     *     messages - A list of error messages in case the validation has failed
     *     value - Value, might be the default value if validation has failed
     *
     * @return Array
     */
    public function getStatus()
    {
        $status = array(
            'failed' => $this->failed,
            'value' => $this->getValue(),
            'messages' => $this->getMessages(),
        );
        return $status;
    }

    /**
     * Throw an exception with a descriptive warning
     *
     * @throws \Exception
     */
    public function __call($name, $arguments)
    {
        if (0 === strpos($name, 'is')) {
            throw new \Exception(
                "the validation $name() is not defined in class " . get_class($this) . ". Read the manual."
            );
        }
        throw new \Exception("function $name is not defined in " . get_class($this));
    }
}
