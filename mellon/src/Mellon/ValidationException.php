<?php

/**
 * @package Mellon
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Mellon;

class ValidationException extends \Exception
{
    /**
     * @var \BO\Mellon\Valid|null $validator
     */
    protected ?Valid $validator = null;

    /**
     * @psalm-api
     */
    public function setValidator(Valid $validator): static
    {
        $this->validator = $validator;
        return $this;
    }
}
