<?php

namespace BO\Zmsentities;

class Mailtemplate extends Schema\Entity
{
    public static $schema = "mailtemplate.json";

    /**
     * @return array
     *
     */
    #[\Override]
    public function getDefaults()
    {
        return [
        ];
    }

    public function hasType(mixed $type): bool
    {
        return (isset($this[$type])) ? true : false;
    }

    public function hasPreference(mixed $type, mixed $key): bool
    {
        return ($this->hasType($type) && isset($this[$type][$key])) ? true : false;
    }

    public function getPreference(mixed $type, mixed $key): mixed
    {
        return $this->toProperty()->$type->$key->get();
    }

    public function setPreference(mixed $type, mixed $key, mixed $value): static
    {
        $preference = $this->toProperty()->$type->$key->get();
        if (null !== $preference) {
            $this[$type][$key] = $value;
        }
        return $this;
    }
}
