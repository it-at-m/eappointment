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
            return $this->asObject;
        }
        return $this->input;
    }

    public function setJsonObject(\stdClass $asObject)
    {
        $this->asObject = $asObject;
        return $this;
    }
}
