<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Schema;

class UnflattedArray
{
    protected mixed $value = null;

    public function __construct(mixed $value)
    {
        $this->value = $value;
    }

    public function getValue(): mixed
    {
        return $this->value;
    }

    public function getUnflattenedArray(): array
    {
        $hash = $this->value;
        foreach ($hash as $key => $value) {
            if (is_string($key) && false !== strpos($key, '__')) {
                $currentLevel =& $hash;
                unset($hash[$key]);
                foreach (explode('__', $key) as $currentKey) {
                    if (!isset($currentLevel[$currentKey])) {
                        $currentLevel[$currentKey] = [];
                    }
                    $currentLevel =& $currentLevel[$currentKey];
                }
                $currentLevel = $value;
            }
        }
        $this->value = $hash;
        return (array) $hash;
    }
}
