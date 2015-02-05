<?php
/**
 * @package Mellon
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Mellon;

use \BO\Mellon\Exception;

/**
  * Parameter validation
  *
  */

class Unvalidated extends \BO\Mellon\Parameter
{

    /**
     * Return a valid parameter
     * this function changes class to verify validation
     * @return \\BO\\Mellon\Valid
     */
    public function __call($name, $arguments)
    {
        if (0 !== strpos($name, 'is')) {
            throw new Exception("parameters should validate first");
        }
        $valid = new \BO\Mellon\Valid($this->value, $this->name);
        return call_user_func(array($valid, $name), $arguments);
    }
}
