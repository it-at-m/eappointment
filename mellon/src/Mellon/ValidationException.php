<?php
/**
 * @package Mellon
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Mellon;

/**
  *
  *
  */
class ValidationException extends \Exception
{
    /**
     * @var \BO\Mellon\Valid $validator
     *
     */
    protected $validator = null;

    public function setValidator(Valid $validator)
    {
        $this->validator = $validator;
        return $this;
    }
}
