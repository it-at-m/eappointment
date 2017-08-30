<?php

namespace BO\Zmsentities\Schema;

class Validator extends \League\JsonGuard\Validator
{
    protected $schemaArray;

    protected $schemaData;

    protected $locale;

    public function __construct($data, $schema, $locale)
    {
        $this->locale = $locale;
        $this->schemaArray = $schema;
        $this->dataArray = $data;
        parent::__construct($data, $schema);
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
            $errorsReducedList[] = new \League\JsonGuard\ValidationError(
                $this->getCustomMessage($error),
                $error->getCode(),
                '',
                $this->getTranslatedPointer($error)
            );
        }
        return $errorsReducedList;
    }

    public function getCustomMessage($error)
    {
        $message = null;
        $pointer = $this->getOriginPointer($error);
        foreach ($error->getConstraints() as $constrain => $value) {
            $value = $value;
            if (array_key_exists($pointer, $this->schemaArray->properties)) {
                if (array_key_exists('locale', $this->schemaArray->properties->{$pointer})) {
                    $message =
                        $this->schemaArray->properties->{$pointer}->locale->{$this->locale}->messages->{$constrain};
                }
            }
        }
        return ($message) ? $message : $error->getMessage();
    }

    public function getOriginPointer($error)
    {
        $pointer = explode('/', $error->getPointer());
        return (isset($pointer[1])) ? $pointer[1] : $pointer[0];
    }

    public function getTranslatedPointer($error)
    {
        $pointerTranslated = null;
        $pointer = $this->getOriginPointer($error);
        if (array_key_exists($pointer, $this->schemaArray->properties)) {
            if (array_key_exists('locale', $this->schemaArray->properties->{$pointer})) {
                $pointerTranslated = $this->schemaArray->properties->{$pointer}->locale->{$this->locale}->pointer;
            }
        }
        return ($pointerTranslated) ? $pointerTranslated : $pointer;
    }
}
