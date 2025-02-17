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

class Unvalidated extends \BO\Mellon\Parameter
{
    /**
     * @var callable $setValid
     */
    protected $setValid;

    /**
     * Return a valid parameter
     * this function changes class to verify validation
     *
     * @throws \BO\Mellon\Exception
     *
     * @return \BO\Mellon\Valid
     */
    public function __call($name, $arguments)
    {
        if (0 !== strpos($name, 'is')) {
            throw new Exception("parameters should validate first");
        }
        $valid = new \BO\Mellon\Valid($this->value, $this->name);
        if (!method_exists($valid, $name)) {
            $valid = $this->findTypedValidator($name);
        }
        return call_user_func_array(array($valid, $name), $arguments);
    }

    /**
     * Set a callback to receive a reference on the validated object
     * The first parameter of this callback receives the validated object
     *
     * @return \BO\Mellon\Unvalidated
     */
    public function setCallback(callable $setValid)
    {
        $this->setValid = $setValid;
        return $this;
    }

    /**
     * Try to find a class for a validation function
     *
     * @param String $name like "isUrl" where "ValidUrl" is an existing class
     *
     * @throws \BO\Mellon\Exception
     *
     * @return \BO\Mellon\Valid
     */
    protected function findTypedValidator($name)
    {
        $partList = preg_split('#([A-Z][a-z]+)#', $name, -1, PREG_SPLIT_DELIM_CAPTURE);
        if (isset($partList[1])) {
            $class = __NAMESPACE__ . '\\Valid' . $partList[1];
            if (class_exists($class)) {
                $newClass = new $class($this->value, $this->name);
                if ($this->setValid) {
                    $callback = $this->setValid;
                    $callback($newClass);
                }
                return $newClass;
            } else {
                throw new Exception("Validation class $class does not exists");
            }
        }
        throw new Exception("invalid validation function");
    }
}
