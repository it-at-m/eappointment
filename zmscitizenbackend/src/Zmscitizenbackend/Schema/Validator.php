<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Schema;

use BO\Zmscitizenbackend\Helper\Property;
use Opis\JsonSchema\Errors\ValidationError as OpisValidationError;
use Opis\JsonSchema\Validator as OpisValidator;

class Validator
{
    protected Schema $schemaObject;
    protected mixed $schemaData;
    protected string $locale;
    protected OpisValidator $validator;
    protected mixed $validationResult;

    private static bool $schemasLoaded = false;
    private static ?OpisValidator $validatorInstance = null;

    public function __construct(mixed $data, Schema $schemaObject, string $locale)
    {
        $this->schemaData = $data;
        $this->schemaObject = $schemaObject;
        $this->locale = $locale;

        if (self::$validatorInstance === null) {
            self::$validatorInstance = new OpisValidator();
            $formats = self::$validatorInstance->parser()->getFormatResolver();
            $formats->registerCallable("array", "sameValues", function (array $data): bool {
                return count($data) === 2 && $data[0] === $data[1];
            });
        }
        $this->validator = self::$validatorInstance;

        if (!self::$schemasLoaded) {
            $this->loadSchemas();
            self::$schemasLoaded = true;
        }

        $schemaJson = json_decode(json_encode($schemaObject->toJsonObject()));
        $data = json_decode(json_encode($data));
        $this->validationResult = $this->validator->validate($data, $schemaJson);
    }

    private function loadSchemas(): void
    {
        $schemaPath = Loader::getSchemaPath() . '/';
        $this->validator->resolver()->registerPrefix('schema://', $schemaPath);
        $schemaFiles = glob($schemaPath . '*.json') ?: [];

        foreach ($schemaFiles as $schemaFile) {
            $schemaContent = file_get_contents($schemaFile);
            $schemaName = 'schema://' . basename($schemaFile);
            $this->validator->resolver()->registerRaw($schemaContent, $schemaName);
        }
    }

    public function isValid(): bool
    {
        return $this->validationResult->isValid();
    }

    public function getErrors(): array
    {
        if ($this->validationResult->isValid()) {
            return [];
        }

        $errorsReducedList = [];
        $error = $this->validationResult->error();

        if ($error) {
            $errorsReducedList = $this->extractErrors($error);
        }

        return $errorsReducedList;
    }

    private function extractErrors(OpisValidationError $error): array
    {
        $errors = [];

        $errors[] = new OpisValidationError(
            $error->keyword(),
            $error->schema(),
            $error->data(),
            $this->getCustomMessage($error),
            $error->args(),
            []
        );

        foreach ($error->subErrors() as $subError) {
            if ($subError instanceof OpisValidationError) {
                $errors = array_merge($errors, $this->extractErrors($subError));
            }
        }

        return $errors;
    }

    public function getCustomMessage(OpisValidationError $error): string
    {
        $schemaData = $error->schema()->info()->data();
        if (is_object($schemaData)) {
            $schemaData = (array) $schemaData;
        }
        $property = new Property($schemaData);

        if (
            isset($property['x-locale'][$this->locale]->messages[$error->keyword()])
            && $property['x-locale'][$this->locale]->messages[$error->keyword()] !== null
        ) {
            return $property['x-locale'][$this->locale]->messages[$error->keyword()]->get();
        }

        return $error->message();
    }

    public static function getOriginPointer(OpisValidationError $error): string
    {
        $dataInfo = $error->data();

        if (empty($dataInfo->path())) {
            return '/';
        }

        return '/' . implode('/', array_map('strval', $dataInfo->path()));
    }

    public function getTranslatedPointer(OpisValidationError $error): string
    {
        $schemaData = $error->schema()->info()->data();
        if (is_object($schemaData)) {
            $schemaData = (array) $schemaData;
        }
        $property = new Property($schemaData);

        if (
            isset($property['x-locale'][$this->locale]->pointer)
            && $property['x-locale'][$this->locale]->pointer !== null
        ) {
            return $property['x-locale'][$this->locale]->pointer->get(self::getOriginPointer($error));
        }

        return self::getOriginPointer($error);
    }
}
