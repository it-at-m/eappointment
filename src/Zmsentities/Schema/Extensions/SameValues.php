<?php

namespace BO\Zmsentities\Schema\Extensions;

use League\JsonGuard;
use League\JsonGuard\ValidationError;
use League\JsonGuard\Pointer;

class SameValues implements \League\JsonGuard\FormatExtension
{
    public function validate($value, $pointer = null)
    {
        if (0 === strcmp($value[0], $value[1])) {
            return null;
        }
        $message = 'Strings does not match';

        return new ValidationError(
            $message,
            'INVALID_STRING_MATCHING',
            $value,
            $pointer,
            ['is_equal' => 'test']
        );
    }
}
