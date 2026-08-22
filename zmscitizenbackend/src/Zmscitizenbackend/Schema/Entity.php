<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Schema;

use BO\Zmscitizenbackend\Exceptions\SchemaValidation;
use BO\Zmscitizenbackend\Helper\Property;

/**
 * @SuppressWarnings(NumberOfChildren)
 * @SuppressWarnings(PublicMethod)
 * @SuppressWarnings(Complexity)
 *
 * @extends \ArrayObject<array-key, mixed>
 */
class Entity extends \ArrayObject implements \JsonSerializable
{
    public const string PRIMARY = 'id';

    public static $schema = null;

    public static $schemaRefPrefix = '';

    protected mixed $jsonSchema = null;

    protected int $jsonCompressLevel = 0;

    protected static array $schemaCache = [];

    protected mixed $resolveLevel = null;

    public function __construct($input = null, $flags = \ArrayObject::ARRAY_AS_PROPS, $iterator_class = "ArrayIterator")
    {
        parent::__construct($this->getDefaults(), $flags, $iterator_class);
        if ($input) {
            $input = $this->getUnflattenedArray($input);
            $this->addData($input);
        }
    }

    #[\Override]
    public function exchangeArray(object|array $input): array
    {
        parent::exchangeArray($this->getDefaults());
        $input = $this->getUnflattenedArray($input);
        $this->addData($input);
        return $this->getArrayCopy();
    }

    public function getUnflattenedArray(mixed $input): mixed
    {
        if (!$input instanceof UnflattedArray) {
            $input = new UnflattedArray($input);
            $input->getUnflattenedArray();
        }
        return $input->getValue();
    }

    public function getDefaults(): array
    {
        return [];
    }

    public function getValidator(string $locale = 'de_DE', int $resolveLevel = 0): Validator
    {
        $jsonSchema = self::readJsonSchema()->withResolvedReferences($resolveLevel);
        $data = (new Schema($this))->withoutRefs();
        if (Property::keyExists('$schema', $data)) {
            unset($data['$schema']);
        }
        return new Validator($data->toJsonObject(true), $jsonSchema, $locale);
    }

    public function isValid(int $resolveLevel = 0): bool
    {
        $validator = $this->getValidator('de_DE', $resolveLevel);
        return $validator->isValid();
    }

    public function testValid(string $locale = 'de_DE', int $resolveLevel = 0): bool
    {
        $validator = $this->getValidator($locale, $resolveLevel);
        if (!$validator->isValid()) {
            $exception = new SchemaValidation();
            $exception->setSchemaName($this->getEntityName());
            $exception->setValidationError($validator->getErrors());
            throw $exception;
        }
        return true;
    }

    public static function createExample(): static
    {
        $object = new static();
        return $object->getExample();
    }

    public static function getExample(): static
    {
        $class = get_called_class();
        $jsonSchema = self::readJsonSchema();
        if ($jsonSchema->offsetExists('example')) {
            return new $class($jsonSchema['example']);
        }
        return new $class();
    }

    protected static function readJsonSchema(): Schema
    {
        $class = get_called_class();
        if (!array_key_exists($class, self::$schemaCache)) {
            self::$schemaCache[$class] = Loader::asArray($class::$schema);
        }
        return self::$schemaCache[$class];
    }

    public function getEntityName(): string
    {
        $entity = get_class($this);
        $entity = preg_replace('#.*[\\\]#', '', $entity);
        return strtolower($entity ?? '');
    }

    public function setJsonCompressLevel(int $jsonCompressLevel): static
    {
        $this->jsonCompressLevel = $jsonCompressLevel;
        return $this;
    }

    #[\Override]
    public function jsonSerialize(): mixed
    {
        $schema = [
            '$schema' => 'https://schema.berlin.de/queuemanagement/' . $this->getEntityName() . '.json',
        ];
        $schema = array_merge($schema, $this->getArrayCopy());
        $schema = new Schema($schema);
        $schema->setDefaults($this->getDefaults());
        $schema->setJsonCompressLevel($this->jsonCompressLevel);
        return $schema->toJsonObject();
    }

    public function __toString(): string
    {
        return json_encode($this->jsonSerialize(), JSON_HEX_QUOT);
    }

    public function __clone(): void
    {
        foreach ($this as $key => $property) {
            if (is_object($property)) {
                $this[$key] = clone $property;
            }
        }
    }

    public function addData(array|object $mergeData): static
    {
        foreach ($mergeData as $key => $item) {
            if (isset($this[$key])) {
                if ($this[$key] instanceof Entity) {
                    $this[$key]->setResolveLevel($this->getResolveLevel() - 1);
                    $this[$key]->addData($item);
                } elseif (is_array($this[$key]) && is_array($item)) {
                    $this[$key] = array_replace_recursive($this[$key], $item);
                } else {
                    $this[$key] = $item;
                }
            } else {
                $this[$key] = $item;
            }
        }
        return $this;
    }

    public function withData(array|object $mergeData): static
    {
        $entity = clone $this;
        $entity->addData($mergeData);
        return $entity;
    }

    public function hasId(): bool
    {
        return false !== $this->getId();
    }

    public function getId(): mixed
    {
        $idName = $this::PRIMARY;
        return ($this->offsetExists($idName) && $this[$idName]) ? $this[$idName] : false;
    }

    public function toProperty(): Property
    {
        return new Property($this);
    }

    public function hasProperty(string $propertyName): bool
    {
        return $this->toProperty()->{$propertyName}->isAvailable();
    }

    public function getProperty(string $propertyName, mixed $default = ''): mixed
    {
        return $this->toProperty()->{$propertyName}->get($default);
    }

    public function withProperty(string $propertyName, mixed $newValue): static
    {
        $entity = clone $this;
        $entity[$propertyName] = $newValue;
        return $entity;
    }

    public function getResolveLevel(): mixed
    {
        return $this->resolveLevel;
    }

    public function setResolveLevel(mixed $resolveLevel): static
    {
        $this->resolveLevel = $resolveLevel;
        return $this;
    }
}
