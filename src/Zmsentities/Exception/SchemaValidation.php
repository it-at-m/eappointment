<?php

namespace BO\Zmsentities\Exception;

use \BO\Zmsentities\Schema\Validator;

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

    public function getMessages()
    {
        return $this->messages;
    }

    protected function setMessages($validationErrorList)
    {
        foreach ($validationErrorList as $error) {
            $this->data[$error->getDataPath()]['messages'][$error->getKeyword()] = $error->getMessage();
            $this->data[$error->getDataPath()]['pointer'] = Validator::getOriginPointer($error);
            $this->data[$error->getDataPath()]['failed'] = 1;
        }
        return $this;
    }
}
