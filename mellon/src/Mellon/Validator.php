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
     * Content of STDIN
     *
     * @var String $input
     */
    protected $input = null;

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
    public function __construct($parameters, $input = null)
    {
        $this->setParameters($parameters);
        $this->setInput($input);
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
     * @return self
     */
    public function setInput($input)
    {
        $this->input = $input;
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
     * Validate a key from the given parameters
     *
     * @param String $name of the key
     *
     * @return \BO\Mellon\Unvalidated
     */
    public static function param($name)
    {
        $validator = self::getInstance();
        return $validator->getParameter($name);
    }

    /**
     * Validate a mixed value
     *
     * @param Mixed $mixed
     * @param String $name an optional name to identify the value
     *
     * @return \BO\Mellon\Unvalidated
     */
    public static function value($mixed, $name = null)
    {
        return new \BO\Mellon\Unvalidated($mixed, $name);
    }

    /**
     * Validate content of STDIN, usually the body of an HTTP request
     *
     * @param String $name an optional name to identify the value
     *
     * @return \BO\Mellon\Unvalidated
     */
    public static function input($name = null)
    {
        $validator = self::getInstance();
        return $validator->getInput($name);
    }

    /**
     * @param String $name an optional name to identify the value
     *
     * @return \BO\Mellon\Unvalidated
     */
    public function getInput($name = null)
    {
        if (null === $this->input) {
            $this->input = file_get_contents('php://input');
        }
        return self::value($this->input, $name);
    }

    /**
     * @return self
     */
    public static function collection($validatorList)
    {
        $collection = new Collection($validatorList);
        return $collection;
    }
}
