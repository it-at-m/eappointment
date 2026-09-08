<?php

/**
 * @package Mellon
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Mellon\Failure;

use BO\Mellon\Valid;

class Exception extends \Exception
{
    /**
     * @var Valid|null $validator
     */
    protected ?Valid $validator = null;

    public function setValidator(Valid $validator): static
    {
        $this->validator = $validator;
        $this->message = (string)($validator->getMessages() ?? '');
        $this->message .=
            "({"
            . $validator->getName()
            . "}=="
            . escapeshellarg(substr((string)$validator->getUnvalidated(), 0, 65536))
            . ")";
        return $this;
    }

    /** @psalm-api */
    public function getValidator(): ?Valid
    {
        return $this->validator;
    }
}
