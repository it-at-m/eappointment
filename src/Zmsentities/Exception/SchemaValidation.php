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

    protected $errorMessages = [
        ErrorCode::INVALID_NUMERIC         => "INVALID_NUMERIC",
        ErrorCode::INVALID_NULL            => "INVALID_NULL",
        ErrorCode::INVALID_INTEGER         => "INVALID_INTEGER",
        ErrorCode::INVALID_STRING          => "INVALID_STRING",
        ErrorCode::INVALID_BOOLEAN         => "INVALID_BOOLEAN",
        ErrorCode::INVALID_ARRAY           => "INVALID_ARRAY",
        ErrorCode::INVALID_OBJECT          => "INVALID_OBJECT",
        ErrorCode::INVALID_ENUM            => "INVALID_ENUM",
        ErrorCode::INVALID_MIN             => "INVALID_MIN",
        ErrorCode::INVALID_EXCLUSIVE_MIN   => "INVALID_EXCLUSIVE_MIN",
        ErrorCode::INVALID_MAX             => "INVALID_MAX",
        ErrorCode::INVALID_EXCLUSIVE_MAX   => "INVALID_EXCLUSIVE_MAX",
        ErrorCode::INVALID_MIN_COUNT       => "INVALID_MIN_COUNT",
        ErrorCode::MAX_ITEMS_EXCEEDED      => "MAX_ITEMS_EXCEEDED",
        ErrorCode::INVALID_MIN_LENGTH      => "INVALID_MIN_LENGTH",
        ErrorCode::INVALID_MAX_LENGTH      => "INVALID_MAX_LENGTH",
        ErrorCode::INVALID_MULTIPLE        => "INVALID_MULTIPLE",
        ErrorCode::NOT_UNIQUE_ITEM         => "NOT_UNIQUE_ITEM",
        ErrorCode::INVALID_PATTERN         => "INVALID_PATTERN",
        ErrorCode::INVALID_TYPE            => "INVALID_TYPE",
        ErrorCode::NOT_SCHEMA              => "NOT_SCHEMA",
        ErrorCode::MISSING_REQUIRED        => "MISSING_REQUIRED",
        ErrorCode::ONE_OF_SCHEMA           => "ONE_OF_SCHEMA",
        ErrorCode::ANY_OF_SCHEMA           => "ANY_OF_SCHEMA",
        ErrorCode::ALL_OF_SCHEMA           => "ALL_OF_SCHEMA",
        ErrorCode::NOT_ALLOWED_PROPERTY    => "NOT_ALLOWED_PROPERTY",
        ErrorCode::INVALID_EMAIL           => "INVALID_EMAIL",
        ErrorCode::INVALID_URI             => "INVALID_URI",
        ErrorCode::INVALID_IPV4            => "INVALID_IPV4",
        ErrorCode::INVALID_IPV6            => "INVALID_IPV6",
        ErrorCode::INVALID_DATE_TIME       => "INVALID_DATE_TIME",
        ErrorCode::INVALID_HOST_NAME       => "INVALID_HOST_NAME",
        ErrorCode::INVALID_FORMAT          => "INVALID_FORMAT",
        ErrorCode::NOT_ALLOWED_ITEM        => "NOT_ALLOWED_ITEM",
        ErrorCode::UNMET_DEPENDENCY        => "UNMET_DEPENDENCY",
        ErrorCode::MAX_PROPERTIES_EXCEEDED => "MAX_PROPERTIES_EXCEEDED",
        "INVALID_STRING_MATCHING"          => "INVALID_STRING_MATCHING"
    ];

    protected $validationError = null;

    protected $schemaName = '';

    public function setValidationError(array $validationError)
    {
        $this->validationError = $validationError;
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

    public function setData()
    {
        foreach ($this->validationError as $error) {
            $pointer = str_replace('/', '', $error->getPointer());
            $pointer = (strpos($pointer, 'changePassword') !== false) ? 'changePassword[]' : $pointer;
            if (isset($this->data[$pointer]['messages']) &&
                ! in_array($error->getMessage(), $this->data[$pointer]['messages'])
            ) {
                $this->data[$pointer]['messages'][] = $error->getMessage();
            }
            $this->data[$pointer]['failed'] = 1;
        }
    }

    public function setMessages()
    {
        $messages = [];
        foreach ($this->validationError as $error) {
            $messages[] = $this->getErrorMessage($error);
        }
        $this->message = implode("\n", $messages);
        return $this;
    }

    public function getErrorMessage(\League\JsonGuard\ValidationError $error)
    {
        $message = $this->schemaName . '#';
        $message .= $this->errorMessages[$error->getCode()];
        $message .= ' ';
        $message .= $error->getPointer();
        $message .= '=';
        $message .= var_export($error->getValue(), true);
        $message .= ' ';
        $message .= $error->getMessage();
        //$message .= ' (';
        //$message .= var_export($error->getConstraints(), true);
        //$message .= ')';

        return $message;
    }
}
