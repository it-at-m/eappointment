<?php

namespace BO\Zmsentities\Schema;

class Schema extends \ArrayObject
{
    protected $input = null;

    protected $asObject = null;

    protected $defaults = [];

    /**
     * @var Int $jsonCompressLevel
     */
    protected $jsonCompressLevel = 0;

    /**
     * Read the json schema and let array act like an object
     */
    public function __construct($input = [], $flags = \ArrayObject::ARRAY_AS_PROPS, $iterator_class = "ArrayIterator")
    {
        $this->input = $input;
        parent::__construct($input, $flags, $iterator_class);
    }

    public function withResolvedReferences($resolveLevel)
    {
        if ($resolveLevel > 0) {
            $schema = clone $this;
            $schema = $this->resolveReferences($schema, $resolveLevel);
            $schema->setJsonObject(json_decode(json_encode($schema->toSanitizedArray(true))));
            return $schema;
        }
        return $this;
    }

    protected function resolveKey($key, $value, $resolveLevel)
    {
        //error_log("Resolve($resolveLevel) Key = " . $key . " -> " . gettype($value));
        if (is_array($value)) {
            $value = $this->resolveReferences($value, $resolveLevel);
        } elseif ($key === '$ref' && $value[0] != '#') {
            //error_log("Load $value");
            $value = Loader::asArray($value)->withResolvedReferences($resolveLevel - 1);
        }
        return $value;
    }

    protected function resolveReferences($hash, $resolveLevel)
    {
        foreach ($hash as $key => $value) {
            $hash[$key] = $this->resolveKey($key, $value, $resolveLevel);
            if ($hash[$key] instanceof self) {
                // Schema from Loader::asArray() is returned, we guess $key is '$ref' and should be replaced
                return $hash[$key]->getArrayCopy();
            }
        }
        return $hash;
    }

    public function toJsonObject($keepEmpty = false)
    {
        if (null !== $this->asObject) {
            $data = $this->asObject;
        } elseif (! $keepEmpty) {
            $data = json_decode(json_encode($this->toSanitizedArray($keepEmpty)));
        } else {
            $data = json_decode(json_encode($this->toSanitizedArray($keepEmpty)));
        }
        return $data;
    }

    public function setJsonObject(\stdClass $asObject)
    {
        $this->asObject = $asObject;
        return $this;
    }

    public function setDefaults($defaults)
    {
        $this->defaults = $defaults;
    }

    public function setJsonCompressLevel($jsonCompressLevel)
    {
        $this->jsonCompressLevel = $jsonCompressLevel;
        return $this;
    }

    public function toSanitizedArray($keepEmpty = false)
    {
        $data = $this->getArrayCopy();
        $data = $this->toSanitizedValue($data, $keepEmpty, $this->defaults);
        return $data;
    }

    /**
     * Sanitize value for valid export as JSON
     *
     */
    protected function toSanitizedValue($value, $keepEmpty = false, $defaults = [])
    {
        if ($value instanceof \BO\Zmsentities\Helper\NoSanitize) {
            return $value;
        }
        if ($value instanceof \BO\Zmsentities\Collection\JsonUnindexed) {
            $value = array_values($value->getArrayCopy());
        } elseif ($value instanceof \ArrayObject) {
            if (method_exists($value, 'getDefaults')) {
                $defaults = $value->getDefaults();
            }
            $value = $value->getArrayCopy();
        }
        if (is_array($value)) {
            $value = $this->toSanitizedList($value, $keepEmpty, $defaults);
        }
        if ($value instanceof \JsonSerializable) {
            $value = $value->jsonSerialize();
        }
        return $value;
    }

    protected function toSanitizedList($value, $keepEmpty, $defaults = [])
    {
        foreach ($value as $key => $item) {
            if ($this->jsonCompressLevel > 0 && isset($defaults[$key])) {
                $value[$key] = $this->toSanitizedValue($item, $keepEmpty, $defaults[$key]);
                if ($defaults[$key] === $value[$key]) {
                    $value[$key] = null;
                }
            } else {
                $value[$key] = $this->toSanitizedValue($item, $keepEmpty);
            }
            if (! $keepEmpty && $this->isItemEmpty($value[$key])) {
                unset($value[$key]);
            }
        }
        return $value;
    }

    protected static function isItemEmpty($item)
    {
        return (
            null === $item
            || (is_array($item) && count($item) == 0)
        );
    }

    /*
     * Uses a path like '/changePassword/0' to fetch property settings
     *
    public function toProperty()
    {
        return new \BO\Zmsentities\Helper\Property($this);
    }


    public function getPropertyByPath($path)
    {
        $pointerList = explode('/', trim($path, '/ '));
        $property = $this->toProperty();
        $property = $pointerList[0] == 'properties' ? $property : $property->properties;
        foreach ($pointerList as $pointer) {
            if ($property->type->get() == 'array') {
                $property = $property['items'];
            } elseif ($property->type->get() == 'object' && $pointer !== 'properties') {
                $property = $property->properties[$pointer];
            } elseif (is_numeric($pointer)) {
                // ignore array items
            } else {
                $property = $property[$pointer];
            }
        }
        return $property->get([]);
    }
    */

    public function withoutRefs()
    {
        return $this->getWithoutRefs(clone $this);
    }

    protected function getWithoutRefs($data)
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->getWithoutRefs($value);
            }
            if ($key === '$ref') {
                unset($data[$key]);
            }
        }
        return $data;
    }
}
