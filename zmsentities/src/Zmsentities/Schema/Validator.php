<?php

namespace BO\Zmsentities\Schema;

use Opis\JsonSchema\{Validator as OpisValidator, ValidationResult, Schema as OpisSchema};
use Opis\JsonSchema\Resolvers\FormatResolver;
use Opis\JsonSchema\Errors\ValidationError as OpisValidationError;

class Validator
{
    protected $schemaObject;
    protected $schemaData;
    protected $locale;
    protected $validator;
    protected $validationResult;

    private static $schemasLoaded = false;
    private static $validatorInstance = null;

    public function __construct($data, Schema $schemaObject, $locale)
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

        $schemaJson = json_decode(json_encode($schemaObject->toJsonObject()));
        $data = json_decode(json_encode($data));
        $this->validationResult = $this->validator->validate($data, $schemaJson);
    }

    private function loadSchemas()
    {
        $schemaPath = realpath(dirname(__FILE__) . '/../../../schema') . '/';
        $this->validator->resolver()->registerPrefix('schema://', $schemaPath);
        $schemaFiles = glob($schemaPath . '*.json');

        // Use Loader::asJson() which includes PSR Cache for persistent caching across processes
        foreach ($schemaFiles as $schemaFile) {
            $schemaFilename = basename($schemaFile);
            $schemaContent = Loader::asJson($schemaFilename);
            $schemaName = 'schema://' . $schemaFilename;
            $this->validator->resolver()->registerRaw($schemaContent, $schemaName);
        }
    }

    public function isValid()
    {
        return $this->validationResult->isValid();
    }

    public function getErrors()
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

    private function extractErrors(OpisValidationError $error)
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

    public function getCustomMessage(OpisValidationError $error)
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

    public static function getOriginPointer(OpisValidationError $error)
    {
        $dataInfo = $error->data();

        if (empty($dataInfo->path())) {
            return '/';
        }

        $pointer = '/' . implode('/', array_map('strval', $dataInfo->path()));

        return $pointer;
    }

    public function getTranslatedPointer(OpisValidationError $error)
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
