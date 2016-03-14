<?php

namespace BO\Zmsentities\Helper;

/**
 * Get a property from an Array or ArrayAccess
 */
class Property
{
    /**
     * @var Mixed $access
     *
     */
    protected $access = null;

    /**
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

    public function get($default = null)
    {
        if (null !== $this->access) {
            return $this->access;
        }
        return $default;
    }

    public function __get($property)
    {
        if ((is_array($this->access) || $this->access instanceof ArrayAccess)
            && array_key_exists($property, $this->access)
        ) {
            return new self($this->access[$property]);
        }
        if (is_object($this->access) && isset($this->access->$property)) {
            return new self($this->access->$property);
        }
        return new self(null);
    }
}
