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
      * @var mixed $value
     */
    protected mixed $value = '';

    /**
      * name of parameter
      *
      * @var string $name
      */
    protected string $name = '';

    public function __construct(mixed $value, ?string $name = '')
    {
        $this->setValue($value);
        $this->setName($name);
    }

    /**
     * @return self
     */
    protected function setValue(mixed $value): self
    {
        $this->value = $value;
        return $this;
    }

    /**
     * @return self
     */
    public function setName(?string $name): self
    {
        $this->name = $name ?? '';
        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
