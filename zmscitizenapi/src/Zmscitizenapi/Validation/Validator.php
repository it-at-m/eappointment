<?php
declare(strict_types=1);

namespace BO\Zmscitizenapi\Validation;

interface Validator
{

    /**
     * Validates
     *
     * @param object $object
     * @return mixed
     * @throws \Exception
     */
    public function validate(object $dto): void;
}