<?php

namespace BO\Zmsentities\Exception;

use \BO\Zmsentities\Schema\Validator;
use Opis\JsonSchema\ValidationError;

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
            $this->data[$pointer]['messages'][$error->keyword()] = $error->message();
            $this->data[$pointer]['headline'] = $error->dataPointer();
            $this->data[$pointer]['failed'] = 1;
            $this->data[$pointer]['data'] = $error->data();
        }
        $message = '[property '. $error->dataPointer() .'] '. json_encode($this->data[$pointer]['messages'], 1);
        $this->message = (! $this->message) ? $message : $this->message;
        return $this;
    }
}