<?php

namespace BO\Zmsentities\Schema;

use Opis\JsonSchema\Validator as OpisValidator;
use Opis\JsonSchema\ValidationResult;
use Opis\JsonSchema\Schema;
use BO\Zmsentities\Schema\Extensions\CoerceType;
use BO\Zmsentities\Schema\Extensions\SameValues;

class Validator
{
    protected $schemaObject;
    protected $schemaData;
    protected $locale;
    protected $validator;

    public function __construct($data, Schema $schemaObject, $locale)
    {
        $this->schemaData = $data;
        $this->schemaObject = $schemaObject;
        $this->locale = $locale;

        // Initialize Opis Validator
        $this->validator = new OpisValidator();

        // Register custom keywords
        $this->validator->addKeyword('type', new CoerceType());
        $this->validator->addKeyword('sameValues', new SameValues());
    }

    public function isValid()
    {
        $result = $this->validator->validate($this->schemaData, $this->schemaObject);
        return $result->isValid();
    }

    public function getErrors()
    {
        $result = $this->validator->validate($this->schemaData, $this->schemaObject);
        $errorsReducedList = [];

        if (!$result->isValid()) {
            foreach ($result->getErrors() as $error) {
                $errorsReducedList[] = new ValidationError(
                    $this->getCustomMessage($error),
                    $error->keyword(),
                    $error->keywordArgs(),
                    $error->data(),
                    $this->getTranslatedPointer($error),
                    $error->schema(),
                    $error->schemaLocation()
                );
            }
        }

        return $errorsReducedList;
    }

    public function getCustomMessage($error)
    {
        $message = null;
        $property = new \BO\Zmsentities\Helper\Property($error->schema());
        $message = $property['x-locale'][$this->locale]->messages[$error->keyword()]->get();
        return ($message) ? $message : $error->message();
    }

    public static function getOriginPointer($error)
    {
        $pointer = explode('/', $error->schemaLocation());
        $keys = array_keys($pointer, 'properties', true);
        if (0 < count($keys)) {
            $pointer = array_values(array_slice($pointer, end($keys) + 1, null, true));
        }
        return reset($pointer);
    }

    public function getTranslatedPointer($error)
    {
        $property = new \BO\Zmsentities\Helper\Property($error->schema());
        return $property['x-locale'][$this->locale]->pointer->get(static::getOriginPointer($error));
    }

    public function registerFormatExtension($name, $extension)
    {
        $this->validator->addKeyword($name, $extension);
    }
}
