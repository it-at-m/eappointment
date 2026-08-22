<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Schema;

/**
 * @extends \ArrayObject<array-key, mixed>
 */
class Schema extends \ArrayObject
{
    protected mixed $input = null;

    protected mixed $asObject = null;

    protected array $defaults = [];

    protected int $jsonCompressLevel = 0;

    public function __construct($input = [], $flags = \ArrayObject::ARRAY_AS_PROPS, $iterator_class = "ArrayIterator")
    {
        $this->input = $input;
        parent::__construct($input, $flags, $iterator_class);
    }

    public function withResolvedReferences(int $resolveLevel): self
    {
        if ($resolveLevel > 0) {
            $schema = $this->resolveReferences(clone $this, $resolveLevel);
            $schema->setJsonObject(json_decode(json_encode($schema->toSanitizedArray(true))));
            return $schema;
        }
        return $this;
    }

    protected function resolveKey(string|int $key, mixed $value, int $resolveLevel): mixed
    {
        if (is_array($value)) {
            $value = $this->resolveReferences($value, $resolveLevel);
        } elseif ($key === '$ref' && is_string($value) && $value[0] != '#') {
            $value = Loader::asArray($value)->withResolvedReferences($resolveLevel - 1);
        }
        return $value;
    }

    protected function resolveReferences(array|self $hash, int $resolveLevel): array|self
    {
        foreach ($hash as $key => $value) {
            $hash[$key] = $this->resolveKey($key, $value, $resolveLevel);
            if ($hash[$key] instanceof self) {
                return $hash[$key]->getArrayCopy();
            }
        }
        return $hash;
    }

    public function toJsonObject(bool $keepEmpty = false): mixed
    {
        if (null !== $this->asObject) {
            return $this->asObject;
        }
        return json_decode(json_encode($this->toSanitizedArray($keepEmpty)));
    }

    public function setJsonObject(\stdClass $asObject): static
    {
        $this->asObject = $asObject;
        return $this;
    }

    public function setDefaults(array $defaults): void
    {
        $this->defaults = $defaults;
    }

    public function setJsonCompressLevel(int $jsonCompressLevel): static
    {
        $this->jsonCompressLevel = $jsonCompressLevel;
        return $this;
    }

    public function toSanitizedArray(bool $keepEmpty = false): array
    {
        return $this->toSanitizedValue($this->getArrayCopy(), $keepEmpty, $this->defaults);
    }

    protected function toSanitizedValue(mixed $value, bool $keepEmpty = false, array $defaults = []): mixed
    {
        if ($value instanceof \ArrayObject) {
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

    protected function toSanitizedList(array $value, bool $keepEmpty, array $defaults = []): array
    {
        foreach ($value as $key => $item) {
            if ($this->jsonCompressLevel > 0 && isset($defaults[$key])) {
                $value[$key] = $this->toSanitizedValue($item, $keepEmpty, is_array($defaults[$key]) ? $defaults[$key] : []);
                if ($defaults[$key] === $value[$key]) {
                    $value[$key] = null;
                }
            } else {
                $value[$key] = $this->toSanitizedValue($item, $keepEmpty);
            }
            if (!$keepEmpty && $this->isItemEmpty($value[$key])) {
                unset($value[$key]);
            }
        }
        return $value;
    }

    protected static function isItemEmpty(mixed $item): bool
    {
        return (
            null === $item
            || (is_array($item) && count($item) == 0)
        );
    }

    public function withoutRefs(): self
    {
        return $this->getWithoutRefs(clone $this);
    }

    protected function getWithoutRefs(array|self $data): array|self
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
