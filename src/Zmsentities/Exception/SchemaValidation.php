<?php

namespace BO\Zmsentities\Exception;

use \League\JsonGuard\ErrorCode;

/**
 * example class to generate an exception
 */
class SchemaValidation extends \Exception
{
    protected $code = 400;

    public $data = [];


    protected $validationErrorList = [];

    protected $schemaName = '';

    public function setValidationError(array $validationErrorList)
    {
        $this->validationErrorList = $validationErrorList;
        $this->setMessages();
        $this->setData();
        return $this;
    }

    public function setSchemaName($schemaName)
    {
        $this->schemaName = $schemaName . '.json';
        $this->template = $schemaName;
        return $this;
    }

    /**
    * Merge conflict, on error see commit c05b7e5fca6b52fc8d0936f4fbb653f3cad8f06b
    */
    public function setData()
    {
        foreach ($this->validationErrorList as $error) {
            $pointer = $error->getSchemaPath();
            if (! isset($this->data[$pointer]['messages'])) {
                $this->data[$pointer]['messages'] = array();
            }
            if (! in_array($error->getMessage(), $this->data[$pointer]['messages'])) {
                $this->data[$pointer]['messages'][] = $error->getMessage();
            }
            $this->data[$pointer]['failed'] = 1;
        }
    }

    public function getValidationErrorList()
    {
        return $this->validationErrorList;
    }

    public function setMessages()
    {
        $messages = [];
        foreach ($this->validationErrorList as $error) {
            $messages[] = $this->getErrorMessage($error);
        }
        $this->message = implode("\n", $messages);
        return $this;
    }

    public function getErrorMessage(\League\JsonGuard\ValidationError $error)
    {
        $message = $this->schemaName . '';
        $message .= $error->getSchemaPath() . '';
        //$message .= $error->getKeyword();
        $message .= ' ';
        $message .= $error->getDataPath();
        $message .= '=';
        $message .= var_export($error->getCause(), true);
        $message .= ' ';
        $message .= $error->getMessage();
        //$message .= ' (';
        //$message .= var_export($error->getConstraints(), true);
        //$message .= ')';
        //$message .= var_export($error->toArray(), true);

        return $message;
    }
}
