<?php

namespace BO\Zmsentities\Schema;

use BO\Zmsentities\Helper\Property;

/**
 * @SuppressWarnings(NumberOfChildren)
 * @SuppressWarnings(PublicMethod)
 * @SuppressWarnings(Complexity)
 */
class Entity extends \ArrayObject implements \JsonSerializable
{
    /**
     * primary id for entity
     *
     */
    const PRIMARY = 'id';

    /**
     * @var String $schema Filename of JSON-Schema file
     */
    public static $schema = null;

    /**
     * @var String $schema Filename of JSON-Schema file
     */
    public static $schemaRefPrefix = '';

    /**
     * @var \ArrayObject $jsonSchema JSON-Schema definition to validate data
     */
    protected $jsonSchema = null;

    /**
     * @var Int $jsonCompressLevel
     */
    protected $jsonCompressLevel = 0;

    /**
     * @var Array $schemaCache for not loading and interpreting a schema twice
     *
     */
    protected static $schemaCache = [];

    /**
     * @var Int $resolveLevel indicator on data integrity
     */
    protected $resolveLevel = null;

    /**
     * Read the json schema and let array act like an object
     */
    public function __construct($input = null, $flags = \ArrayObject::ARRAY_AS_PROPS, $iterator_class = "ArrayIterator")
    {
        parent::__construct($this->getDefaults(), $flags, $iterator_class);
        if ($input) {
            $input = $this->getUnflattenedArray($input);
            $this->addData($input);
        }
    }

    public function exchangeArray($input)
    {
        parent::exchangeArray($this->getDefaults());
        $input = $this->getUnflattenedArray($input);
        $this->addData($input);
    }

    public function getUnflattenedArray($input)
    {
        if (!$input instanceof UnflattedArray) {
            $input = new UnflattedArray($input);
            $input->getUnflattenedArray();
        }
        $input = $input->getValue();
        return $input;
    }
    /**
     * Set Default values
     */
    public function getDefaults()
    {
        return [];
    }

    /**
     * This method is private, because the used library should not be used outside of this class!
     */
    public function getValidator($locale = 'de_DE', $resolveLevel = 0)
    {
        $jsonSchema = self::readJsonSchema()->withResolvedReferences($resolveLevel);
        $data = (new Schema($this))->withoutRefs();
        if (Property::__keyExists('$schema', $data)) {
            unset($data['$schema']);
        }
        $validator = new Validator($data->toJsonObject(true), $jsonSchema, $locale);
        return $validator;
    }

    /**
     * Check if the given data validates against the given jsonSchema
     *
     * @return bool
     */
    public function isValid($resolveLevel = 0): bool
    {
        $validator = $this->getValidator('de_DE', $resolveLevel = 0);
        return $validator->isValid();
    }

    /**
     * Check if the given data validates against the given jsonSchema
     *
     * @throws \BO\Zmsentities\Expcetion\SchemaValidation
     * @return bool
     */
    public function testValid($locale = 'de_DE', $resolveLevel = 0): bool
    {
        $validator = $this->getValidator($locale, $resolveLevel);
        $validator = $this->registerExtensions($validator);
        if (!$validator->isValid()) {
            $exception = new \BO\Zmsentities\Exception\SchemaValidation();
            $exception->setSchemaName($this->getEntityName());
            $exception->setValidationError($validator->getErrors());
            throw $exception;
        }
        return true;
    }

    public function registerExtensions($validator)
    {
        $validator->registerFormatExtension('sameValues', new Extensions\SameValues());
        return $validator;
    }

    /**
     * create an example for testing
     *
     * @return self
     */
    public static function createExample()
    {
        $object = new static();
        return $object->getExample();
    }

    /**
     * return a new object as example
     *
     * @return self
     */
    public static function getExample()
    {
        $class = get_called_class();
        $jsonSchema = self::readJsonSchema();
        if ($jsonSchema->offsetExists('example')) {
            return new $class($jsonSchema['example']);
        }
        return new $class();
    }

    protected static function readJsonSchema()
    {
        $class = get_called_class();
        if (!array_key_exists($class, self::$schemaCache)) {
            self::$schemaCache[$class] = Loader::asArray($class::$schema);
        }
        return self::$schemaCache[$class];
    }

    public function getEntityName()
    {
        $entity = get_class($this);
        $entity = preg_replace('#.*[\\\]#', '', $entity);
        $entity = strtolower($entity);
        return $entity;
    }

    public function setJsonCompressLevel($jsonCompressLevel)
    {
        $this->jsonCompressLevel = $jsonCompressLevel;
        return $this;
    }

    public function jsonSerialize()
    {
        $schema = array(
            '$schema' => 'https://schema.berlin.de/queuemanagement/' . $this->getEntityName() . '.json'
        );
        $schema = array_merge($schema, $this->getArrayCopy());
        if ($this instanceof \BO\Zmsentities\Helper\NoSanitize) {
            $serialize = $schema;
        } else {
            $schema = new Schema($schema);
            $schema->setDefaults($this->getDefaults());
            $schema->setJsonCompressLevel($this->jsonCompressLevel);
            $serialize = $schema->toJsonObject();
        }
        return $serialize;
    }

    public function __toString()
    {
        return json_encode($this->jsonSerialize(), JSON_HEX_QUOT);
    }

    public function __clone()
    {
        foreach ($this as $key => $property) {
            if (is_object($property)) {
                $this[$key] = clone $property;
            }
        }
    }

    /**
     * Performs a merge with an iterable
     * Sub-entities are preserved
     */
    public function addData($mergeData)
    {
        foreach ($mergeData as $key => $item) {
            if (isset($this[$key])) {
                if ($this[$key] instanceof Entity) {
                    $this[$key]->setResolveLevel($this->getResolveLevel() - 1);
                    $this[$key]->addData($item);
                } elseif ($this[$key] instanceof \BO\Zmsentities\Collection\Base) {
                    $this[$key]->exchangeArray([]);
                    $this[$key]->setResolveLevel($this->getResolveLevel() - 1);
                    $this[$key]->addData($item);
                } elseif (is_array($this[$key])) {
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

    /**
     * Performs addData on a cloned entity
     */
    public function withData($mergeData)
    {
        $entity = clone $this;
        $entity->addData($mergeData);
        return $entity;
    }

    public function hasId()
    {
        return (false !== $this->getId()) ? true : false;
    }

    public function getId()
    {
        $idName = $this::PRIMARY;
        return ($this->offsetExists($idName) && $this[$idName]) ? $this[$idName] : false;
    }

    /**
     * Allow accessing properties without checking if it exists first
     *
     * @return \BO\Zmsentities\Helper\Property
     */
    public function toProperty()
    {
        return new \BO\Zmsentities\Helper\Property($this);
    }

    public function hasProperty($propertyName)
    {
        return $this->toProperty()->{$propertyName}->isAvailable();
    }

    public function getProperty($propertyName, $default = '')
    {
        return $this->toProperty()->{$propertyName}->get($default);
    }

    /**
     * Change property without changing original
     */
    public function withProperty($propertyName, $newValue)
    {
        $entity = clone $this;
        $entity[$propertyName] = $newValue;
        return $entity;
    }

    /**
     * Reduce data of dereferenced entities to a required minimum
     *
     */
    public function withLessData()
    {
        return clone $this;
    }

    /**
     * Reduce data of dereferenced entities to a required minimum
     *
     */
    public function withCleanedUpFormData()
    {
        $entity = clone $this;
        if (isset($entity['save'])) {
            unset($entity['save']);
        }
        if (isset($entity['removeImage'])) {
            unset($entity['removeImage']);
        }
        return $entity;
    }

    /**
     * @return Int
     */
    public function getResolveLevel()
    {
        return $this->resolveLevel;
    }

    /**
     * @param Int $resolveLevel
     * @return self
     */
    public function setResolveLevel($resolveLevel)
    {
        $this->resolveLevel = $resolveLevel;
        return $this;
    }

    /**
     * Set a very strict resolveLevel to reduce data
     *
     * @param Int $resolveLevel
     * @return self
     */
    public function withResolveLevel($resolveLevel)
    {
        if ($resolveLevel >= 0) {
            $entity = clone $this;
            foreach ($entity as $key => $value) {
                if ($value instanceof Entity || $value instanceof \BO\Zmsentities\Collection\Base) {
                    $entity[$key] = $value->withResolveLevel($resolveLevel - 1);
                } else {
                    $entity[$key] = $value;
                }
            }
            $entity->setResolveLevel($resolveLevel);
            return $entity;
        } else {
            return $this->withReference();
        }
    }

    /**
     * Replace data with a jsonSchema Reference
     *
     * @param Array $additionalData
     * @return self
     */
    public function withReference($additionalData = [])
    {
        if (isset($this[$this::PRIMARY])) {
            $additionalData['$ref'] =
                $this::$schemaRefPrefix . $this->getEntityName() . '/' . $this[$this::PRIMARY] . '/';
            return $additionalData;
        } else {
            return $this->withResolveLevel(0);
        }
    }
}
