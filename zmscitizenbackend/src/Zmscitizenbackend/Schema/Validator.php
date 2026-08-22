<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Schema;

use Opis\JsonSchema\Errors\ValidationError as OpisValidationError;
use Opis\JsonSchema\Validator as OpisValidator;

class Validator
{
    protected \Opis\JsonSchema\ValidationResult $validationResult;

    public function __construct(mixed $data, Schema $schemaObject)
    {
        $opis = new OpisValidator();
        $schemaJson = $schemaObject->toJsonObject();
        $payload = json_decode(json_encode($data));
        $this->validationResult = $opis->validate($payload, $schemaJson);
    }

    public function isValid(): bool
    {
        return $this->validationResult->isValid();
    }

    /**
     * @return OpisValidationError[]
     */
    public function getErrors(): array
    {
        if ($this->validationResult->isValid()) {
            return [];
        }

        $error = $this->validationResult->error();
        if (!$error) {
            return [];
        }

        return $this->extractErrors($error);
    }

    /**
     * @return OpisValidationError[]
     */
    private function extractErrors(OpisValidationError $error): array
    {
        $errors = [$error];
        foreach ($error->subErrors() as $subError) {
            if ($subError instanceof OpisValidationError) {
                $errors = array_merge($errors, $this->extractErrors($subError));
            }
        }
        return $errors;
    }
}
