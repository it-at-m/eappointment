<?php

namespace BO\Zmsentities\Schema;

use \League\JsonGuard\ValidationError;

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
        $ruleset = new \League\JsonGuard\RuleSet\DraftFour();
        $ruleset->set('type', Extensions\CoerceType::class);
        $this->validator = new \League\JsonGuard\Validator($data, $schemaObject->toJsonObject(), $ruleset);
    }

    public function isValid()
    {
        return $this->validator->passes();
    }

    public function getErrors()
    {
        $errorsReducedList = array();
        $errors = $this->validator->errors();
        foreach ($errors as $error) {
            $errorsReducedList[] = new ValidationError(
                $this->getCustomMessage($error),
                $error->getKeyword(),
                $error->getParameter(),
                $error->getData(),
                $this->getTranslatedPointer($error),
                $error->getSchema(),
                $error->getSchemaPath()
            );
        }
        return $errorsReducedList;
    }

    public function getCustomMessage(ValidationError $error)
    {
        $message = null;
        $property = new \BO\Zmsentities\Helper\Property($error->getSchema());
        $message = $property['x-locale'][$this->locale]->messages[$error->getKeyword()]->get();
        return ($message) ? $message : $error->getMessage();
    }

    public static function getOriginPointer(ValidationError $error)
    {
        $pointer = explode('/', $error->getSchemaPath());
        $keys = array_keys($pointer, 'properties', true);
        if (0 < count($keys)) {
            $pointer = array_values(array_slice($pointer, end($keys) + 1, null, true));
        }
        return reset($pointer);
    }

    /**
     * on error see merge conflict with c05b7e5fca6b52fc8d0936f4fbb653f3cad8f06b
     */
    public function getTranslatedPointer(ValidationError $error)
    {
        $property = new \BO\Zmsentities\Helper\Property($error->getSchema());
        return $property['x-locale'][$this->locale]->pointer->get(static::getOriginPointer($error));
    }

    public function registerFormatExtension($name, $extension)
    {
        return $this->validator->getRuleset()->get('format')->addExtension($name, $extension);
    }
}
