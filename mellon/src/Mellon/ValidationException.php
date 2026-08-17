<?php

/**
 * @package Mellon
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Mellon;

class ValidationException extends \Exception
{
    /**
     * @var \BO\Mellon\Valid $validator
     *
     */
    protected $validator = null;

    /**
     * @psalm-api
     */
    public function setValidator(Valid $validator): static
    {
        $this->validator = $validator;
        return $this;
    }
}
