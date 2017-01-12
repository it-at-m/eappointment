<?php

namespace BO\Zmsentities\Schema;

class Validator extends \League\JsonGuard\Validator
{
    public function isValid()
    {
        return $this->passes();
    }

    public function getErrors()
    {
        $errorsReducedList = array();
        $errors = $this->errors();
        foreach ($errors as $error) {
            $errorsReducedList[] = new \League\JsonGuard\ValidationError(
                $error->getMessage(),
                $error->getCode(),
                '',
                $error->getPointer()
            );
        }
        return $errorsReducedList;
    }
}
