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
    private $messages = null;

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
        if (null === $this->messages) {
            $this->messages = new Failure\MessageList();
        }
        $this->messages[] = new Failure\Message($message);
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
     * Get the validated value or the default value as string
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
     * Throws exception if validation fails
     *
     * @throws \BO\Mellon\ValidationException
     * @return Bool
     */
    public function assertValid()
    {
        if ($this->hasFailed()) {
            $exception = new Failure\Exception();
            $exception->setValidator($this);
            throw $exception;
        }
        return $this;
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
     * @param Bool $unvalidated (optional) Return original value
     *
     * @return Array
     */
    public function getStatus($unvalidated = false)
    {
        $status = array(
            'failed' => $this->failed,
            'value' => $this->getValue(),
            'messages' => $this->getMessages(),
        );
        if ($unvalidated) {
            $status['_unvalidated'] = $this->getUnvalidated();
        }
        return $status;
    }

    public function getUnvalidated()
    {
        return $this->value;
    }

    /**
     * Throw an exception with a descriptive warning
     *
     * @throws \Exception
     */
    public function __call($name, $arguments)
    {
        if (0 === strpos($name, 'is')) {
            throw new Exception(
                "the validation $name() is not defined in class " . get_class($this) . ". Read the manual."
            );
        }
        throw new Exception("function $name is not defined in " . get_class($this));
    }
}
