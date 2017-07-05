<?php

namespace BO\Zmsentities\Schema;

/**
 * @SuppressWarnings(NumberOfChildren)
 * @SuppressWarnings(PublicMethod)
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
     * @var ArrayObject $jsonSchema JSON-Schema definition to validate data
     */
    protected $jsonSchema = null;

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
    private function getValidator()
    {
        $jsonSchema = self::readJsonSchema();
        $data = new Schema($this);
        if (array_key_exists('$schema', $data)) {
            unset($data['$schema']);
        }
        $validator = new Validator($data->toJsonObject(), $jsonSchema->toJsonObject());
        return $validator;
    }

    /**
     * Check if the given data validates against the given jsonSchema
     *
     * @return Boolean
     */
    public function isValid()
    {
        $validator = $this->getValidator();
        return $validator->isValid();
    }

    /**
     * Check if the given data validates against the given jsonSchema
     *
     * @throws \BO\Zmsentities\Expcetion\SchemaValidation
     * @return Boolean
     */
    public function testValid()
    {
        $validator = $this->getValidator();
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
        if (array_key_exists('example', $jsonSchema)) {
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

    public function hasId()
    {
        $idName = $this::PRIMARY;
        return (array_key_exists($idName, $this) && $this[$idName]) ? true : false;
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
        unset($entity['save']);
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
     * @param Int $resolveLevel
     * @return self
     */
    public function withResolveLevel($resolveLevel)
    {
        $entity = clone $this;
        if ($resolveLevel >= 0) {
            foreach ($entity as $key => $value) {
                if ($value instanceof Entity) {
                    $entity[$key] = $value->withResolveLevel($resolveLevel - 1);
                }
            }
            $entity->setResolveLevel($resolveLevel);
            return $entity;
        } else {
            return null;
        }
    }
}
