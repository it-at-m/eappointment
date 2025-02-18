<?php

namespace BO\Zmsentities\Schema\Extensions;

use Opis\JsonSchema\Keyword;
use Opis\JsonSchema\ValidationContext;
use Opis\JsonSchema\Errors\ValidationError;

class CoerceType implements Keyword
{
    public function validate($data, ValidationContext $context, $value): ?ValidationError
    {
        $type = $value;

        switch ($type) {
            case 'string':
                if (is_numeric($data) || is_bool($data)) {
                    $context->setData((string)$data);
                }
                break;
            case 'number':
                if (is_string($data) && is_numeric($data)) {
                    $context->setData((float)$data);
                }
                break;
            case 'integer':
                if (is_string($data) && ctype_digit($data)) {
                    $context->setData((int)$data);
                }
                break;
            case 'boolean':
                if (is_string($data)) {
                    if ($data === 'true') {
                        $context->setData(true);
                    } elseif ($data === 'false') {
                        $context->setData(false);
                    }
                }
                break;
        }

        return null;
    }
}
