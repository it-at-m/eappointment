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
abstract class Parameter
{
    /**
      * value of parameter
      *
      * @var String $value
      */
    protected $value = '';

    /**
      * name of parameter
      *
      * @var String $name
      */
    protected $name = '';

    /**
      *
      */
    public function __construct($value, $name = '')
    {
        $this->setValue($value);
        $this->setName($name);
    }

    /**
     * @return self
     */
    protected function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return self
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return String
     */
    public function getName()
    {
        return $this->name;
    }
}
