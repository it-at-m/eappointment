<?php

namespace BO\Zmsentities\Schema;

use Opis\JsonSchema\{Validator as OpisValidator, ValidationResult, Schema as OpisSchema};
use Opis\JsonSchema\Resolvers\FormatResolver;
use Opis\JsonSchema\Errors\ValidationError as OpisValidationError;

class Validator
{
    protected Schema $schemaObject;
    protected mixed $schemaData;
    protected mixed $locale;
    protected OpisValidator $validator;
    protected ValidationResult $validationResult;

    private static bool $schemasLoaded = false;
    private static ?OpisValidator $validatorInstance = null;

    public function __construct(mixed $data, Schema $schemaObject, mixed $locale)
    {
        $this->schemaData = $data;
        $this->schemaObject = $schemaObject;
        $this->locale = $locale;

        // Use static validator instance if available
        if (self::$validatorInstance === null) {
            self::$validatorInstance = new OpisValidator();
            $formats = self::$validatorInstance->parser()->getFormatResolver();
            $formats->registerCallable("array", "sameValues", function (array $data): bool {
                return count($data) === 2 && $data[0] === $data[1];
            });
        }
        $this->validator = self::$validatorInstance;

        // Load schemas only once for each process
        if (!self::$schemasLoaded) {
            $this->loadSchemas();
            self::$schemasLoaded = true;
        }

        $schemaJson = json_decode((string) json_encode($schemaObject->toJsonObject()));
        $data = json_decode((string) json_encode($data));
        $this->validationResult = $this->validator->validate($data, $schemaJson);
    }

    private function loadSchemas(): void
    {
        $schemaDir = realpath(dirname(__FILE__) . '/../../../schema');
        $schemaPath = ($schemaDir !== false ? $schemaDir : '') . '/';
        $this->validator->resolver()->registerPrefix('schema://', $schemaPath);
        $schemaFiles = glob($schemaPath . '*.json');

        // TODO: Implement persistent caching for schema file reads to reduce redundant disk I/O and improve application performance. Not just for each process.

        foreach ($schemaFiles as $schemaFile) {
            $schemaContent = file_get_contents($schemaFile);
            $schemaName = 'schema://' . basename($schemaFile);
            $this->validator->resolver()->registerRaw($schemaContent, $schemaName);
        }
    }

    public function isValid(): mixed
    {
        return $this->validationResult->isValid();
    }

    public function getErrors(): mixed
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

    public function getCustomMessage(OpisValidationError $error): mixed
    {
        $schemaData = $error->schema()->info()->data();
        if (is_object($schemaData)) {
            $schemaData = (array) $schemaData;
        }
        $property = new \BO\Zmsentities\Helper\Property($schemaData);

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

        $pointer = '/' . implode('/', array_map('strval', $dataInfo->path()));

        return $pointer;
    }

    public function getTranslatedPointer(OpisValidationError $error): mixed
    {
        $schemaData = $error->schema()->info()->data();
        if (is_object($schemaData)) {
            $schemaData = (array) $schemaData;
        }
        $property = new \BO\Zmsentities\Helper\Property($schemaData);

        if (
            isset($property['x-locale'][$this->locale]->pointer)
            && $property['x-locale'][$this->locale]->pointer !== null
        ) {
            return $property['x-locale'][$this->locale]->pointer->get(self::getOriginPointer($error));
        }

        return self::getOriginPointer($error);
    }
}
