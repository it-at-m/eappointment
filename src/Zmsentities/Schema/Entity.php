<?php

namespace BO\Zmsentities\Schema;

/**
 * @SuppressWarnings(NumberOfChildren)
 */
class Entity extends \ArrayObject implements \JsonSerializable
{
    /**
     * @var String $schema Filename of JSON-Schema file
     */
    public static $schema = null;

    /**
     * @var ArrayObject $jsonSchema JSON-Schema definition to validate data
     */
    protected $jsonSchema = null;

    /**
     * Read the json schema and let array act like an object
     */
    public function __construct($input = [], $flags = \ArrayObject::ARRAY_AS_PROPS, $iterator_class = "ArrayIterator")
    {
        $this->jsonSchema = self::readJsonSchema();
        $input = $this->getUnflattenedArray($input);
        parent::__construct($input, $flags, $iterator_class);
    }

    public function exchangeArray($input)
    {
        parent::exchangeArray($this->getUnflattenedArray($input));
    }

    /**
      * split fields
      * If a key to a field has two underscores "__" it should go into a subarray
      *
      * @param  array $hash
      *
      * @return array
      */
    public function getUnflattenedArray($hash)
    {
        $splittedHash = array();
        foreach ($hash as $key => $value) {
            $position = strpos($key, '__');
            if (false !== $position && 0 < $position) {
                list($subkey, $newkey) = explode('__', $key, 2);
                if (!isset($splittedHash[$subkey])) {
                    $splittedHash[$subkey] = array();
                }
                $splittedHash[$subkey][$newkey] = $value;
                $splittedHash[$subkey] = $this->getUnflattenedArray($splittedHash[$subkey]);
            } else {
                $splittedHash[$key] = $value;
            }
        }
        return $splittedHash;
    }

    /**
     * This method is private, because the used library should not be used outside of this class!
     */
    private function getValidator()
    {
        $validator = new \JsonSchema\Validator();
        $validator->check($this, $this->jsonSchema);
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
     * create an example for testing
     *
     * @return self
     */
    public static function createExample()
    {
        $class = get_called_class();
        $object = new $class();
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
        return Loader::asArray($class::$schema);
    }

    public function jsonSerialize()
    {
        $serialize = $this->getArrayCopy();
        $entity = get_class($this);
        $entity = preg_replace('#.*[\\\]#', '', $entity);
        $entity = strtolower($entity);
        $schema = array('$schema' => 'https://schema.berlin.de/queuemanagement/' . $entity . '.json');
        return array_merge($schema, $serialize);
    }

    public function __toString()
    {
        return json_encode($this->jsonSerialize(), JSON_HEX_QUOT);
    }
}
