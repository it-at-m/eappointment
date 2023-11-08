<?php
/**
 * @package Mellon
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Mellon\Failure;

use BO\Mellon\Valid;

/**
  *
  *
  */
class Exception extends \Exception
{
    /**
     * @var \BO\Mellon\Valid $validator
     *
     */
    protected $validator = null;

    public function setValidator(Valid $validator)
    {
        $this->validator = $validator;
        $this->message = (string)$validator->getMessages();
        $this->message .=
            "({"
            . $validator->getName()
            . "}=="
            . htmlspecialchars(escapeshellarg(substr((string)$validator->getUnvalidated(), 0, 65536)))
            . ")";
        return $this;
    }
}
