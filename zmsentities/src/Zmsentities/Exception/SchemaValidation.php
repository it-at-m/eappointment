<?php

namespace BO\Zmsentities\Exception;

use BO\Zmsentities\Schema\Validator;

/**
 * example class to generate an exception
 */
class SchemaValidation extends \Exception
{
    protected $code = 400;

    public $data = [];

    protected $schemaName = '';

    public function setValidationError(array $validationErrorList)
    {
        $this->setMessages($validationErrorList);
        return $this;
    }

    public function setSchemaName($schemaName)
    {
        $this->schemaName = $schemaName . '.json';
        $this->template = $schemaName;
        return $this;
    }

    protected function setMessages($validationErrorList)
    {
        foreach ($validationErrorList as $error) {
            $pointer = Validator::getOriginPointer($error);
            $this->data[$pointer]['messages'][$error->getKeyword()] = $error->getMessage();
            $this->data[$pointer]['headline'] = $error->getDataPath();
            $this->data[$pointer]['failed'] = 1;
            $this->data[$pointer]['data'] = $error->getData();
        }
        $message = '[property ' . $error->getDataPath() . '] ' . json_encode($this->data[$pointer]['messages'], 1);
        $this->message = (! $this->message) ? $message : $this->message;
        return $this;
    }
}
