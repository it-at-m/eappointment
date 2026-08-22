<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Schema;

/**
 * @extends \ArrayObject<array-key, mixed>
 */
class Schema extends \ArrayObject
{
    protected string $sourcePath = '';

    public function __construct(
        array $input = [],
        int $flags = \ArrayObject::ARRAY_AS_PROPS,
        string $iterator_class = "ArrayIterator"
    ) {
        parent::__construct($input, $flags, $iterator_class);
    }

    public function setSourcePath(string $sourcePath): static
    {
        $this->sourcePath = $sourcePath;
        return $this;
    }

    public function getSourcePath(): string
    {
        return $this->sourcePath;
    }

    public function withResolvedReferences(int $resolveLevel): self
    {
        if ($resolveLevel <= 0) {
            return $this;
        }
        $baseDir = $this->sourcePath !== '' ? dirname($this->sourcePath) : Loader::getSchemaPath();
        $resolved = $this->resolveReferences($this->getArrayCopy(), $resolveLevel, $baseDir);
        $schema = new self($resolved);
        $schema->setSourcePath($this->sourcePath);
        return $schema;
    }

    public function toJsonObject(): mixed
    {
        return json_decode(json_encode($this->getArrayCopy()));
    }

    protected function resolveKey(string|int $key, mixed $value, int $resolveLevel, string $baseDir): mixed
    {
        if (is_array($value)) {
            return $this->resolveReferences($value, $resolveLevel, $baseDir);
        }
        if ($key === '$ref' && is_string($value) && $value !== '' && !str_starts_with($value, '#')) {
            $refPath = $value;
            if ($refPath[0] !== '/') {
                $refPath = $baseDir . DIRECTORY_SEPARATOR . $refPath;
            }
            return Loader::asArray($refPath)->withResolvedReferences($resolveLevel - 1);
        }
        return $value;
    }

    protected function resolveReferences(array|self $hash, int $resolveLevel, string $baseDir): array
    {
        foreach ($hash as $key => $value) {
            $hash[$key] = $this->resolveKey($key, $value, $resolveLevel, $baseDir);
            if ($hash[$key] instanceof self) {
                return $hash[$key]->getArrayCopy();
            }
        }
        return $hash instanceof self ? $hash->getArrayCopy() : $hash;
    }
}
