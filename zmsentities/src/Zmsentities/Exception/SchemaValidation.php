<?php

namespace BO\Zmsentities\Exception;

use BO\Zmsentities\Schema\Validator;
use Opis\JsonSchema\ValidationError;

class SchemaValidation extends \Exception
{
    protected int $code = 400;

    public $data = [];

    protected string $schemaName = '';

    public $template;

    public function setValidationError(array $validationErrorList): static
    {
        $this->setMessages($validationErrorList);
        return $this;
    }

    public function setSchemaName($schemaName): static
    {
        $this->schemaName = $schemaName . '.json';
        $this->template = $schemaName;
        return $this;
    }

    protected function setMessages(array $validationErrorList): static
    {
        foreach ($validationErrorList as $error) {
            $pointer = is_array($error->data()->path())
                ? "/" . implode("/", $error->data()->path())
                : (string) ($error->data()->path() ?? "(root)");

            $message = $error->message();
            foreach ($error->args() as $key => $value) {
                $message = str_replace("{" . $key . "}", json_encode($value, JSON_UNESCAPED_SLASHES), $message);
            }

            $this->data[$pointer]['messages'][$error->keyword()] = $message;
            $this->data[$pointer]['headline'] = $pointer;
            $this->data[$pointer]['failed'] = 1;
            $this->data[$pointer]['data'] = $error->data() !== null ? $error->data()->value() : null;

            $this->message .= ($this->message ? " | " : "")
                . '[property ' . $pointer . '] '
                . json_encode($this->data[$pointer]['messages'], JSON_UNESCAPED_SLASHES);
        }
        return $this;
    }
}
