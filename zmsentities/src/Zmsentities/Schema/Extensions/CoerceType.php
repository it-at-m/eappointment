<?php

namespace BO\Zmsentities\Schema\Extensions;

use Opis\JsonSchema\KeywordValidator;
use Opis\JsonSchema\ValidationContext;
use Opis\JsonSchema\SchemaKeyword;
use Opis\JsonSchema\Errors\ValidationError;

final class CoerceType implements KeywordValidator
{
    public function validate($value, SchemaKeyword $schemaKeyword, ValidationContext $context)
    {
        $typeList = $schemaKeyword->data();
        $coercedValue = $this->coerceType($value, $typeList);
        if ($coercedValue !== $value) {
            $context->setData($coercedValue);
        }

        // Re-run default validation logic
        return null;
    }

    private function coerceType($value, $type)
    {
        if ($type === 'number') {
            return $this->toNumber($value);
        } elseif ($type === 'string') {
            return $this->toString($value);
        } elseif ($type === 'integer' && !is_int($value)) {
            return $this->toNumber($value);
        } elseif ($type === 'boolean') {
            return $this->toBoolean($value);
        }
        return $value;
    }

    private function toNumber($value)
    {
        return is_numeric($value) ? (int)$value : $value;
    }

    private function toString($value)
    {
        return (string)$value;
    }

    private function toBoolean($value)
    {
        return (bool)$value;
    }
}
