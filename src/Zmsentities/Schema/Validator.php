<?php

namespace BO\Zmsentities\Schema;

use \League\JsonGuard\ValidationError;

class Validator extends \League\JsonGuard\Validator
{
    protected $schemaObject;

    protected $schemaData;

    protected $locale;

    public function __construct($data, Schema $schemaObject, $locale)
    {
        $this->schemaData = $data;
        $this->schemaObject = $schemaObject;
        $this->locale = $locale;
        parent::__construct($data, $schemaObject->toJsonObject());
    }

    public function isValid()
    {
        return $this->passes();
    }

    public function getErrors()
    {
        $errorsReducedList = array();
        $errors = $this->errors();
        foreach ($errors as $error) {
            $errorsReducedList[] = new ValidationError(
                $this->getCustomMessage($error),
                $error->getCode(),
                $error->getValue(),
                $this->getTranslatedPointer($error),
                $error->getConstraints()
            );
        }
        return $errorsReducedList;
    }

    public function getCustomMessage(ValidationError $error)
    {
        $message = null;
        foreach (array_keys($error->getConstraints()) as $constraint) {
            $property = $this->schemaObject->getPropertyByPath($error->getPointer());
            $property = new \BO\Zmsentities\Helper\Property($property);
            $message = $property['x-locale'][$this->locale]->messages[$constraint]->get();
        }
        return ($message) ? $message : $error->getMessage();
    }

    public function getOriginPointer(ValidationError $error)
    {
        $pointer = explode('/', $error->getPointer());
        $pointer = (isset($pointer[1])) ? $pointer[1] : $pointer[0];
        return $pointer;
    }

    public function getTranslatedPointer(ValidationError $error)
    {
        $pointer = [];
        $property = $this->schemaObject->getPropertyByPath($error->getPointer());
        $property = new \BO\Zmsentities\Helper\Property($property);
        $pointer['origin'] = $this->getOriginPointer($error);
        $pointer['translated'] = $property['x-locale'][$this->locale]->pointer->get($this->getOriginPointer($error));
        return $pointer;
    }
}
