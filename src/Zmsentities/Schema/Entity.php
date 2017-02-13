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
     * Read the json schema and let array act like an object
     */
    public function __construct($input = null, $flags = \ArrayObject::ARRAY_AS_PROPS, $iterator_class = "ArrayIterator")
    {
        //$this->jsonSchema = self::readJsonSchema();
        if ($input) {
            $input = $this->getUnflattenedArray($input);
            $defaults = $this->getDefaults();
            if ($defaults) {
                $input = array_replace_recursive($defaults, $input);
            }
        } else {
            $input = $this->getDefaults();
        }
        parent::__construct($input, $flags, $iterator_class);
    }

    public function exchangeArray($input)
    {
        parent::exchangeArray($this->getUnflattenedArray($input));
    }

    /**
     * Set Default values
     */
    public function getDefaults()
    {
        return [];
    }

    /**
      * split fields
      * If a key to a field has two underscores "__" it should go into a subarray
      * ATTENTION: performance critical function, keep highly optimized!
      * @param  array $hash
      *
      * @return array
      */
    public function getUnflattenedArray($hash)
    {
        foreach ($hash as $key => $value) {
            if (false !== strpos($key, '__')) {
                $currentLevel =& $hash;
                unset($hash[$key]);
                foreach (explode('__', $key) as $currentKey) {
                    if (!isset($currentLevel[$currentKey])) {
                        $currentLevel[$currentKey] = [];
                    }
                    $currentLevel =& $currentLevel[$currentKey];
                }
                $currentLevel = $value;
            }
        }
        return (array)$hash;
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
        if (!$validator->isValid()) {
            $exception = new \BO\Zmsentities\Exception\SchemaValidation();
            $exception->setSchemaName($this->getEntityName() . '.json');
            $exception->setValidationError($validator->getErrors());
            throw $exception;
        }
        return true;
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
        $schema = new Schema($schema);
        $serialize = $schema->toJsonObject();
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
}
