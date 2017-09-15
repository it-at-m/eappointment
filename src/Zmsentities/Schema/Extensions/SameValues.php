<?php

namespace BO\Zmsentities\Schema\Extensions;

use League\JsonGuard;
use League\JsonGuard\Validator;
use League\JsonGuard\ValidationError;
use League\JsonGuard\Pointer;

/**
 * @codeCoverageIgnore
 */
class SameValues implements \League\JsonGuard\Constraint\DraftFour\Format\FormatExtensionInterface
{
    public function validate($value, Validator $validator = null)
    {
        if (0 === strcmp($value[0], $value[1])) {
            return null;
        }
        $message = 'Strings does not match';

        return new ValidationError(
            $message,
            $validator->getCurrentKeyword(),
            $validator->getCurrentParameter(),
            $value,
            $validator->getDataPath(),
            $validator->getSchema(),
            $validator->getSchemaPath()
        );
    }
}
