<?php

namespace BO\Zmsentities\Schema;

use Opis\JsonSchema\{Validator as OpisValidator, ValidationResult, Schema as OpisSchema};
use Opis\JsonSchema\Errors\ValidationError as OpisValidationError;

class Validator
{
    protected $schemaObject;
    protected $schemaData;
    protected $locale;
    protected $validator;
    protected $validationResult;

    public function __construct($data, Schema $schemaObject, $locale)
    {
        $this->schemaData = $data;
        $this->schemaObject = $schemaObject;
        $this->locale = $locale;

        $this->validator = new OpisValidator();

        // Register schema loader for resolving $refs
        // $schemaPath = '/__w/eappointment/eappointment/zmsentities/schema/';
        var_dump(realpath(dirname(__FILE__) . '/../../../schema') . '/');
        $schemaPath = '/var/www/html/zmsentities/schema/';
        // $schemaPath = dirname(__FILE__);
        // error_log("Resolved schemaPath: " . $schemaPath);
        // error_log("Current working directory: " . getcwd());
        // error_log("Checking if availability.json exists: " . (file_exists($schemaPath . 'availability.json') ? 'Yes' : 'No'));

        // Register all schema files
        $commonSchemas = [
            'apiclient.json',
            'apikey.json',
            'appointment.json',
            'availability.json',
            'calendar.json',
            'calldisplay.json',
            'client.json',
            'cluster.json',
            'config.json',
            'contact.json',
            'day.json',
            'dayoff.json',
            'department.json',
            'eventlog.json',
            'exchange.json',
            'ics.json',
            'link.json',
            'log.json',
            'mail.json',
            'mailtemplate.json',
            'metaresult.json',
            'mimepart.json',
            'month.json',
            'notification.json',
            'organisation.json',
            'owner.json',
            'process.json',
            'processarchived.json',
            'provider.json',
            'queue.json',
            'request.json',
            'requestrelation.json',
            'scope.json',
            'session.json',
            'slot.json',
            'source.json',
            'status.json',
            'ticketprinter.json',
            'useraccount.json',
            'workstation.json',
        ];

        // Register schema loader
        $this->validator->resolver()->registerPrefix('schema://', $schemaPath);

        // Register each schema file
        foreach ($commonSchemas as $schema) {
            if (file_exists($schemaPath . $schema)) {
                $schemaContent = file_get_contents($schemaPath . $schema);
                $this->validator->resolver()->registerRaw(
                    $schemaContent,
                    'schema://' . $schema
                );
            }
        }

        // Convert schema to JSON and create schema object
        $schemaJson = json_encode($schemaObject->toJsonObject());
        $schemaJson = json_decode($schemaJson);

        // Convert data to JSON object
        $data = json_decode(json_encode($data));

        // Debugging
        // var_dump("Schema:", json_encode($schemaJson, JSON_PRETTY_PRINT));
        // var_dump("*********************************************");
        // var_dump("Data:", json_encode($data, JSON_PRETTY_PRINT));

        // Set max errors and validate
        $this->validator->setMaxErrors(1000);
        $this->validator->setStopAtFirstError(false);
        $this->validationResult = $this->validator->validate($data, $schemaJson);
        //$this->validationResult = $this->validator->dataValidation($data, $schemaData);
    }

    public function isValid()
    {
        return $this->validationResult->isValid();
    }

    public function getErrors()
    {
        if ($this->validationResult->isValid()) {
            return [];
        }

        $errorsReducedList = [];
        $error = $this->validationResult->error();

        if ($error) {
            $errorsReducedList = $this->extractErrors($error);
        }

        return $errorsReducedList;
    }

    private function extractErrors(OpisValidationError $error)
    {
        $errors = [];

        $errors[] = new OpisValidationError(
            $error->keyword(),
            $error->schema(),
            $error->data(),
            $this->getCustomMessage($error),
            $error->args(),
            []
        );

        foreach ($error->subErrors() as $subError) {
            if ($subError instanceof OpisValidationError) {
                $errors = array_merge($errors, $this->extractErrors($subError));
            }
        }

        return $errors;
    }

    public function getCustomMessage(OpisValidationError $error)
    {
        $schemaData = $error->schema()->info()->data();
        if (is_object($schemaData)) {
            $schemaData = (array) $schemaData;
        }
        $property = new \BO\Zmsentities\Helper\Property($schemaData);

        if (
            isset($property['x-locale'][$this->locale]->messages[$error->keyword()])
            && $property['x-locale'][$this->locale]->messages[$error->keyword()] !== null
        ) {
            return $property['x-locale'][$this->locale]->messages[$error->keyword()]->get();
        }

        return $error->message();
    }

    public static function getOriginPointer(OpisValidationError $error)
    {
        $dataInfo = $error->data();

        if (empty($dataInfo->path())) {
            return '/';
        }

        $pointer = '/' . implode('/', array_map('strval', $dataInfo->path()));

        return $pointer;
    }

    public function getTranslatedPointer(OpisValidationError $error)
    {
        $schemaData = $error->schema()->info()->data();
        if (is_object($schemaData)) {
            $schemaData = (array) $schemaData;
        }
        $property = new \BO\Zmsentities\Helper\Property($schemaData);

        if (
            isset($property['x-locale'][$this->locale]->pointer)
            && $property['x-locale'][$this->locale]->pointer !== null
        ) {
            return $property['x-locale'][$this->locale]->pointer->get(self::getOriginPointer($error));
        }

        return self::getOriginPointer($error);
    }

    private function resolveRefs(&$schema)
    {
        if (is_object($schema)) {
            foreach ($schema as $key => &$value) {
                if ($key === '$ref' && is_string($value)) {
                    // Convert relative path to schema:// protocol
                    $value = 'schema://' . $value;
                } elseif (is_object($value) || is_array($value)) {
                    $this->resolveRefs($value);
                }
            }
        } elseif (is_array($schema)) {
            foreach ($schema as &$value) {
                if (is_object($value) || is_array($value)) {
                    $this->resolveRefs($value);
                }
            }
        }
    }
}
