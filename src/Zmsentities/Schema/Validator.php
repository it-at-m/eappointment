<?php

namespace BO\Zmsentities\Schema;

class Validator extends \League\JsonGuard\Validator
{
    public function isValid()
    {
        return $this->passes();
    }
}
