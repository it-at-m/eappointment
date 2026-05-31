<?php

namespace BO\Zmsentities;

class Mailtemplate extends Schema\Entity
{
    public static ?string $schema = "mailtemplate.json";

    /**
     * @return array
     *
     * @psalm-return array<never, never>
     */
    public function getDefaults()
    {
        return [
        ];
    }

    public function hasType($type): bool
    {
        return (isset($this[$type])) ? true : false;
    }

    public function hasPreference($type, $key): bool
    {
        return ($this->hasType($type) && isset($this[$type][$key])) ? true : false;
    }

    public function getPreference($type, $key)
    {
        return $this->toProperty()->$type->$key->get();
    }

    public function setPreference($type, $key, $value): static
    {
        $preference = $this->toProperty()->$type->$key->get();
        if (null !== $preference) {
            $this[$type][$key] = $value;
        }
        return $this;
    }
}
