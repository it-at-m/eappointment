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

    public function toJsonObject()
    {
        if (null !== $this->asObject) {
            $data = $this->asObject;
        } else {
            $data = json_decode(json_encode($this->toSanitizedArray()));
        }
        return $data;
    }

    public function setJsonObject(\stdClass $asObject)
    {
        $this->asObject = $asObject;
        return $this;
    }

    public function toSanitizedArray()
    {
        $data = $this->getArrayCopy();
        $data = $this->toSanitizedValue($data);
        return $data;
    }

    /**
     * Sanitize value for valid export as JSON
     *
     */
    protected function toSanitizedValue($value)
    {
        if ($value instanceof \ArrayObject) {
            $value = $value->getArrayCopy();
        }
        if (is_array($value)) {
            foreach ($value as $key => $item) {
                $value[$key] = $this->toSanitizedValue($item);
                if ($this->isItemEmpty($value[$key])) {
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
}
