<?php

namespace BO\Zmsentities\Schema;

class Schema extends \ArrayObject
{
    protected $input = null;

    protected $asObject = null;

    /**
     * Read the json schema and let array act like an object
     */
    public function __construct($input = [], $flags = \ArrayObject::ARRAY_AS_PROPS, $iterator_class = "ArrayIterator")
    {
        $this->input = $input;
        parent::__construct($input, $flags, $iterator_class);
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

    public function toSanitizedArray($keepEmpty = false)
    {
        $data = $this->getArrayCopy();
        $data = $this->toSanitizedValue($data, $keepEmpty);
        return $data;
    }

    /**
     * Sanitize value for valid export as JSON
     *
     */
    protected function toSanitizedValue($value, $keepEmpty = false)
    {
        if ($value instanceof \BO\Zmsentities\Helper\NoSanitize) {
            return $value;
        }
        if ($value instanceof \BO\Zmsentities\Collection\JsonUnindexed) {
            $value = array_values($value->getArrayCopy());
        } elseif ($value instanceof \ArrayObject) {
            $value = $value->getArrayCopy();
        }
        if (is_array($value)) {
            foreach ($value as $key => $item) {
                $value[$key] = $this->toSanitizedValue($item, $keepEmpty);
                if (! $keepEmpty && $this->isItemEmpty($value[$key])) {
                    unset($value[$key]);
                }
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

    public function toProperty()
    {
        return new \BO\Zmsentities\Helper\Property($this);
    }

    /**
     * Uses a path like '/changePassword/0' to fetch property settings
     */
    public function getPropertyByPath($path)
    {
        $pointerList = explode('/', trim($path, '/ '));
        $property = $this->toProperty()->properties;
        foreach ($pointerList as $pointer) {
            if ($property->type->get() == 'array') {
                $property = $property['items'];
            } elseif ($property->type->get() == 'object') {
                $property = $property->properties[$pointer];
            } else {
                $property = $property[$pointer];
            }
        }
        return $property->get([]);
    }
}
