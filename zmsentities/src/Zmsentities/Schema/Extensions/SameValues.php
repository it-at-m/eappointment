<?php

namespace BO\Zmsentities\Schema\Extensions;

use Opis\JsonSchema\KeywordValidator;
use Opis\JsonSchema\ValidationContext;
use Opis\JsonSchema\Errors\ValidationError;

class SameValues implements KeywordValidator
{
    public function validate($value, $schemaKeyword, ValidationContext $context)
    {
        if (is_array($value) && $value[0] === $value[1]) {
            return null;
        }

        return new ValidationError(
            $context,
            $context->schema(),
            "Strings do not match",
            $value
        );
    }
}
