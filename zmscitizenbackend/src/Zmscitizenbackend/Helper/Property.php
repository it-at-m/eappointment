<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Helper;

use BO\Zmscitizenbackend\Exceptions\PropertyOffsetReadOnly;

/**
 * @implements \ArrayAccess<array-key, mixed>
 */
class Property implements \ArrayAccess
{
    protected mixed $access = null;

    public function __construct(mixed $access)
    {
        $this->access = $access;
    }

    public static function create(mixed $access): self
    {
        return new self($access);
    }

    public function isAvailable(): bool
    {
        return null !== $this->access;
    }

    public function get(mixed $default = null): mixed
    {
        if (null !== $this->access) {
            return $this->access;
        }
        return $default;
    }

    #[\Override]
    public function offsetGet(mixed $property): mixed
    {
        return $this->__get($property);
    }

    #[\Override]
    public function offsetExists(mixed $property): bool
    {
        return null !== $this->__get($property)->get();
    }

    #[\Override]
    public function offsetSet(mixed $offset, mixed $value): void
    {
        throw new PropertyOffsetReadOnly(
            __CLASS__ . "[$offset] is readonly, could not set " . htmlspecialchars((string) $value)
        );
    }

    #[\Override]
    public function offsetUnset(mixed $offset): void
    {
        throw new PropertyOffsetReadOnly(__CLASS__ . "[$offset] is readonly");
    }

    public function __get(string $property): self
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

    public function __toString(): string
    {
        $string = $this->get('');
        if (!is_string($string)) {
            $string = print_r($string, true);
        }
        return $string;
    }

    public static function keyExists(string $key, mixed $data): bool
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

        return false;
    }
}
