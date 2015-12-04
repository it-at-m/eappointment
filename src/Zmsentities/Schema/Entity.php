<?php

namespace BO\Zmsentities\Schema;

/**
 * @SuppressWarnings(NumberOfChildren)
 */
class Entity extends \ArrayObject
{
    /**
     * @var String $schema Filename of JSON-Schema file
     */
    public static $schema = null;

    /**
     * @var ArrayObject $jsonSchema JSON-Schema definition to validate data
     */
    protected $jsonSchema = null;

    public function __construct($input = [], $flags = 0, $iterator_class = "ArrayIterator")
    {
        $this->jsonSchema = self::readJsonSchema();
        parent::__construct($input, $flags, $iterator_class);
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
     */
    public function isValid()
    {
        $validator = $this->getValidator();
        return $validator->isValid();
    }

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
}
