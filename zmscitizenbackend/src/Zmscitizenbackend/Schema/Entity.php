<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Schema;

use BO\Zmscitizenbackend\Exception\SchemaValidation;

abstract class Entity implements \JsonSerializable
{
    /**
     * @var string|null Filename of JSON-Schema file relative to zmscitizenbackend/schema
     */
    public static $schema = null;

    /**
     * @var array<string, Schema>
     */
    protected static array $schemaCache = [];

    public function getValidator(int $resolveLevel = 10): Validator
    {
        $jsonSchema = self::readJsonSchema()->withResolvedReferences($resolveLevel);
        $data = self::stripNulls(json_decode(json_encode($this->jsonSerialize())));
        return new Validator($data, $jsonSchema);
    }

    public function isValid(int $resolveLevel = 10): bool
    {
        return $this->getValidator($resolveLevel)->isValid();
    }

    /**
     * @throws SchemaValidation
     */
    public function testValid(int $resolveLevel = 10): bool
    {
        $validator = $this->getValidator($resolveLevel);
        if (!$validator->isValid()) {
            $exception = new SchemaValidation();
            $exception->setSchemaName($this->getEntityName());
            $exception->setValidationError($validator->getErrors());
            throw $exception;
        }
        return true;
    }

    public function getEntityName(): string
    {
        $entity = get_class($this);
        $entity = preg_replace('#.*[\\\]#', '', $entity);
        return strtolower($entity);
    }

    protected static function readJsonSchema(): Schema
    {
        $class = get_called_class();
        if (!array_key_exists($class, self::$schemaCache)) {
            self::$schemaCache[$class] = Loader::asArray($class::$schema);
        }
        return self::$schemaCache[$class];
    }

    protected static function stripNulls(mixed $value): mixed
    {
        if (is_array($value)) {
            $out = [];
            foreach ($value as $key => $item) {
                if ($item === null) {
                    continue;
                }
                $out[$key] = self::stripNulls($item);
            }
            return $out;
        }
        if (is_object($value)) {
            foreach ($value as $key => $item) {
                if ($item === null) {
                    unset($value->$key);
                } else {
                    $value->$key = self::stripNulls($item);
                }
            }
        }
        return $value;
    }
}
