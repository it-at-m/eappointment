<?php

namespace BO\Zmsentities\Helper;

/**
 * Get a property from an Array or ArrayAccess
 */
class Property implements \ArrayAccess
{
    /**
     *
     * @var Mixed $access
     *
     */
    protected $access = null;

    /**
     *
     * @param Mixed $access
     */
    public function __construct($access)
    {
        $this->access = $access;
    }

    public static function create($access)
    {
        return new self($access);
    }

    public function isAvailable()
    {
        //shorter to avoid extra unit testing
        return (null !== $this->access) ? true : false;
    }

    public function get($default = null)
    {
        if (null !== $this->access) {
            return $this->access;
        }
        return $default;
    }

    public function offsetGet($property)
    {
        return $this->__get($property);
    }

    public function offsetExists($property)
    {
        return null !== $this->__get($property)
            ->get();
    }

    public function offsetSet($offset, $value)
    {
        throw new \BO\Zmsentities\Exception\PropertyOffsetReadOnly(
            __CLASS__ . "[$offset] is readonly, could not set " . htmlspecialchars($value)
        );
    }

    public function offsetUnset($offset)
    {
        throw new \BO\Zmsentities\Exception\PropertyOffsetReadOnly(__CLASS__ . "[$offset] is readonly");
    }

    public function __get($property)
    {
        if (
            (is_array($this->access) && array_key_exists($property, $this->access)) ||
            ($this->access instanceof \ArrayAccess && $this->access->offsetExists($property))
        ) {
            return new self($this->access[$property]);
        }
        if (is_object($this->access) && isset($this->access->$property)) {
            return new self($this->access->$property);
        }
        return new self(null);
    }

    public function __toString()
    {
        $string = $this->get('');
        if (! is_string($string)) {
            $string = print_r($string, true);
        }
        return $string;
    }

    public static function __keyExists($key, $data)
    {
        if (is_array($data)) {
            return array_key_exists($key, $data);
        }

        if ($data instanceof \ArrayObject && $data->offsetExists($key)) {
            return true;
        }

        if (is_object($data)) {
            return property_exists($data, $key);
        }
    }
}
