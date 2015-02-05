<?php
/**
 * @package Mellon
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Mellon;

use BO\Mellon\Exception;

/**
  * Validate external parameters
  *
  */
class Validator
{
    /**
      * Paramters to validate
      *
      * @var Array $parameters
      */
    protected $parameters = array();

    /**
      * Singleton instance
      *
      * @var self $instance
      */
    protected static $instance = null;

    /**
     * Always initialize using an array of parameters
     *
     */
    public function __construct($parameters)
    {
        $this->setParameters($parameters);
    }

    /**
     * @return self
     */
    public function setParameters($parameters)
    {
        if (!is_array($parameters)) {
            throw new Exception("Array argument required for parameters");
        }
        $this->parameters = $parameters;
        return $this;
    }

    /**
     * @SuppressWarnings(Superglobals)
     * @return self
     */
    public static function getInstance()
    {
        if (null === self::$instance) {
            $validator = new self($_REQUEST);
            self::$instance = $validator;
        }
        return self::$instance;
    }

    /**
     * @return self
     */
    public static function resetInstance()
    {
        self::$instance = null;
    }

    /**
     * @return self
     */
    public function makeInstance()
    {
        self::$instance = $this;
        return $this;
    }

    /**
     * @return Bool
     */
    public function hasParameter($name)
    {
        return array_key_exists($name, $this->parameters);
    }

    /**
     *
     * @return \BO\Mellon\Unvalidated
     */
    public function getParameter($name)
    {
        if ($this->hasParameter($name)) {
            return new \BO\Mellon\Unvalidated($this->parameters[$name], $name);
        }
        return new \BO\Mellon\Unvalidated(null, $name);
    }

    /**
     * @return \BO\Mellon\Unvalidated
     */
    public static function param($name)
    {
        $validator = self::getInstance();
        return $validator->getParameter($name);
    }

    /**
     * @return \BO\Mellon\Unvalidated
     */
    public static function value($mixed)
    {
        return new \BO\Mellon\Unvalidated($mixed);
    }
}
