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
                return new $class($this->value, $this->name);
            } else {
                throw new Exception("Validation class $class does not exists");
            }
        }
        throw new Exception("invalid validation function");
    }
}
