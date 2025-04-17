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
    private const CACHE_KEY_PREFIX = 'cached_schema_';

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

        foreach ($schemaFiles as $schemaFile) {
            $schemaName = 'schema://' . basename($schemaFile);
            $cacheKey = self::CACHE_KEY_PREFIX . md5($schemaName);
            
            // Try to get schema from cache
            if (isset(\App::$cache) && ($cachedSchema = \App::$cache->get($cacheKey))) {
                $this->validator->resolver()->registerRaw($cachedSchema, $schemaName);
                continue;
            }

            // If not in cache, load from disk
            $schemaContent = file_get_contents($schemaFile);
            $this->validator->resolver()->registerRaw($schemaContent, $schemaName);

            // Cache the schema content
            if (isset(\App::$cache)) {
                \App::$cache->set($cacheKey, $schemaContent);
                if (isset(\App::$log)) {
                    \App::$log->info('Schema cached', [
                        'schema_name' => $schemaName,
                        'cache_key' => $cacheKey
                    ]);
                }
            }
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
