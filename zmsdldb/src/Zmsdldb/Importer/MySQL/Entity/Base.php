<?php

namespace BO\Zmsdldb\Importer\MySQL\Entity;

use BO\Zmsdldb\PDOAccess;
use BO\Zmsdldb\Importer\PDOTrait;
use BO\Zmsdldb\Importer\ItemNeedsUpdateTrait;
use BO\Zmsdldb\Importer\MySQL\Entity\Collection as EntityCollection
;

/**
 * @SuppressWarnings(PHPMD.TooManyPublicMethods)
 * @SuppressWarnings(PHPMD.TooManyMethods)
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 * @SuppressWarnings(PHPMD.ExcessiveClassComplexity)
 * @implements \ArrayAccess<string|null, mixed>
 */
abstract class Base implements \Countable, \ArrayAccess, \JsonSerializable
{
    use ItemNeedsUpdateTrait;
    use PDOTrait;

    protected array $fieldMapping = [];

    /** @var array<string, mixed> */
    protected array $fields = [];

    protected array $referanceMapping = [];

    /** @var array<string, EntityCollection> */
    protected array $references = [];

    protected array $dataRaw = [];

    protected bool $setupFields = true;
    protected bool $setupReferences = true;

    protected int $status = 1;

    public const int STATUS_NEW = 1;
    public const int STATUS_OLD = 0;

    public function __construct(PDOAccess $mySqlAccess, array $dataRaw = [], bool $setup = true)
    {
        try {
            $this->pdoAccess = $mySqlAccess;
            $this->dataRaw = $dataRaw;

            if (true === $setup) {
                $this->setupMapping();

                $this->preSetup();

                $this->setupFields();

                $this->setupReferences();

                $this->postSetup();
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    protected function entityNeedsUpdate(): bool
    {
        $fields = $this->get(['id', 'meta.locale', 'meta.hash']);
        $hash = $fields['meta.hash'] ?? '';

        return $this->itemNeedsUpdate(
            (int) ($fields['id'] ?? 0),
            (string) ($fields['meta.locale'] ?? ''),
            is_scalar($hash) ? (string) $hash : '',
            static::getTableName()
        );
    }

    public function setStatus(int $status = Base::STATUS_NEW): void
    {
        $this->status = $status;
    }

    public function getStatus(): int
    {
        return $this->status;
    }

    public function getReferenceMapping(bool $setup = false): array
    {
        try {
            if (true === $setup) {
                $this->setupMapping();
            }

            return $this->referanceMapping;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    protected function setupMapping(): void
    {
    }

    public function preSetup(): void
    {
    }

    public function postSetup(): void
    {
    }

    public function preSetupFields(): void
    {
    }

    public function postSetupFields(): void
    {
    }

    final public function setupFields(): void
    {
        try {
            if (false === $this->setupFields) {
                return;
            }
            $this->preSetupFields();

            $values = $this->get(array_keys(array_filter($this->fieldMapping)));
            foreach ($values as $key => $value) {
                if (!is_string($key)) {
                    continue;
                }
                $this->__set($key, $value);
            }
            $this->postSetupFields();
            $this->setupFields = false;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    protected function getReferenceFields(): array
    {
        $referenceFields = array_flip(array_keys(array_filter($this->referanceMapping)));

        foreach (array_keys($referenceFields) as $name) {
            $referenceFields[$name] = $this->get($name);
        }
        return $referenceFields;
    }

    final public function setupReferences(): void
    {
        try {
            if (false === $this->setupReferences) {
                return;
            }
            $values = $this->getReferenceFields();

            foreach ($values as $name => $references) {
                $referenceEntityClass = $this->referanceMapping[$name]['class'];
                if (!is_string($referenceEntityClass)) {
                    throw new \InvalidArgumentException('Invalid reference entity class');
                }
                /** @var class-string<Base> $referenceEntityClass */
                $addFields = [];

                foreach (($this->referanceMapping[$name]['neededFields'] ?? []) as $sourceKey => $destinationKey) {
                    $addFields[$destinationKey] = $this->get($sourceKey);
                }
                $isMultiple = $this->referanceMapping[$name]['multiple'] ?? true;

                if (false === $isMultiple) {
                    $references = [$references];
                }

                $position = 0;
                foreach (($references ?? []) as $reference) {
                    foreach ($this->referanceMapping[$name]['addFields'] as $key => $value) {
                        if (is_callable($value)) {
                            $addFields[$key] = call_user_func_array($value, [$position, $name, $reference]);
                        } else {
                            $addFields[$key] = $value;
                        }
                    }
                    if (true === ($this->referanceMapping[$name]['selfAsArray'] ?? false)) {
                        $reference = [
                            $name => $reference
                        ];
                    }
                    if (!is_array($reference)) {
                        $reference = [];
                    }
                    /** @psalm-suppress UnsafeInstantiation */
                    $referencesInstance = new $referenceEntityClass(
                        $this->getPDOAccess(),
                        array_merge(
                            $reference,
                            $addFields
                        )
                    );
                    if (!$referencesInstance instanceof Base) {
                        throw new \InvalidArgumentException($referenceEntityClass . ' must extend ' . Base::class);
                    }

                    $this->addReference($name, $referencesInstance);
                    $position++;
                }
            }
            $this->setupReferences = false;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    final public function __set(string $name, mixed $value): void
    {
        if (array_key_exists($name, $this->fieldMapping)) {
            $name = $this->fieldMapping[$name];
            if (is_bool($value)) {
                $value = (int)$value;
            } elseif (false !== stripos($name, '_json')) {
                $value = json_encode($value);
            }
            $this->fields[$name] = $value;
        } elseif (array_key_exists($name, $this->referanceMapping) && $value instanceof Base) {
            $this->addReference($name, $value);
        }
    }

    public function addReference(string $name, Base $reference): void
    {
        if (array_key_exists($name, $this->referanceMapping)) {
            if (!isset($this->references[$name])) {
                $this->references[$name] = new EntityCollection();
            }
            $this->references[$name][] = $reference;
        }
    }

    final public function __get(string $name): mixed
    {
        if (array_key_exists($name, $this->fields)) {
            return $this->fields[$name];
        }
        if (array_key_exists($name, $this->references)) {
            return $this->references[$name];
        }
        throw new \InvalidArgumentException(__METHOD__ . " {$name} has not been set!");
    }

    final public function __isset(string $name): bool
    {
        return array_key_exists($name, $this->fields) || array_key_exists($name, $this->references);
    }

    final public function __unset(string $name): void
    {
        if (array_key_exists($name, $this->fields)) {
            unset($this->fields[$name]);
        }
        if (array_key_exists($name, $this->references)) {
            unset($this->references[$name]);
        }
    }

    #[\Override]
    final public function offsetExists($offset): bool
    {
        return is_string($offset) && $this->__isset($offset);
    }

    #[\Override]
    final public function offsetGet($offset): mixed
    {
        if (!is_string($offset)) {
            throw new \InvalidArgumentException(__METHOD__ . ' offset must be a string');
        }
        return $this->__get($offset);
    }

    #[\Override]
    final public function offsetSet($offset, $value): Base
    {
        if (!is_string($offset)) {
            throw new \InvalidArgumentException(__METHOD__ . ' offset must be a string');
        }
        $this->__set($offset, $value);
        return $this;
    }

    #[\Override]
    final public function offsetUnset($offset): Base
    {
        if (!is_string($offset)) {
            throw new \InvalidArgumentException(__METHOD__ . ' offset must be a string');
        }
        $this->__unset($offset);
        return $this;
    }

    #[\Override]
    final public function count(): int
    {
        return count($this->fields);
    }

    #[\Override]
    public function jsonSerialize(): mixed
    {
        return $this->fields;
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function get(null|array|string $key = null, mixed $default = null): mixed
    {
        if (null === $key) {
            return $this->dataRaw;
        }
        $keys = $key;
        if (!is_array($keys)) {
            $keys = [$keys];
        }
        $values = [];

        foreach ($keys as $key) {
            if ('__RAW__' == $key) {
                $values[$key] = $this->dataRaw;
                continue;
            }
            $levels = static::arrayAccessByDotPerpareKeys($key);

            $value = $default;

            $current = $this->dataRaw;
            $levelsCount = count($levels);
            for ($i = 0; $i < $levelsCount; ++$i) {
                if (!is_array($current) || !array_key_exists($levels[$i], $current)) {
                    continue;
                }
                $current = $current[$levels[$i]];
                $value = $current;
            }
            $values[$key] = ($value);
        }
        return 1 == count($keys) ? $values[$keys[0]] : $values;
    }

    protected static function arrayAccessByDotPerpareKeys(?string $key = null): array
    {
        if (null === $key) {
            return [];
        }
        $keys = explode('.', $key);
        $keys = array_filter($keys, 'strlen');
        $keys = array_map(static function (string $segment): int|string {
            if (is_numeric($segment) && !str_contains($segment, '.')) {
                return (int) $segment;
            }
            return $segment;
        }, $keys);

        return $keys;
    }

    public function save(): void
    {
        try {
            if (static::STATUS_NEW !== $this->getStatus()) {
                return;
            }
            $this->saveEntitiy();
            $this->saveReferences();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    final public function saveEntitiy(): void
    {
        try {
            if (static::STATUS_NEW !== $this->getStatus()) {
                return;
            }
            if (!empty($this->fields)) {
                $sql = 'REPLACE INTO ' . static::getTableName() . ' ';
                $sql .= '(`' . implode('`, `', array_keys($this->fields)) . '`) ';

                $questionMarks = array_fill(0, count($this->fields), '?');
                $sql .= 'VALUES (' . implode(', ', $questionMarks) . ') ';

                $stm = $this->getPDOAccess()->prepare($sql);

                $stm->execute(array_values($this->fields));

                if ($stm && 0 < $stm->rowCount()) {
                    return;
                }
                throw new \Exception('Could not save entity');
            }
            throw new \Exception('Could not save entity, fields are empty');
        } catch (\Exception $e) {
            throw $e;
        }
    }

    final public function saveReferences(): void
    {
        try {
            if (static::STATUS_NEW !== $this->getStatus()) {
                return;
            }
            foreach ($this->references as $referencesCollection) {
                $referencesCollection->saveEntities();
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /** @psalm-api */
    public function delete(): void
    {
        try {
            $this->deleteEntity();
            $this->deleteReferences();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    abstract public function deleteEntity(): void;

    public function deleteReferences(): void
    {
        try {
            foreach ($this->referanceMapping as $name => $mappingData) {
                if (isset($mappingData['deleteFunction']) && is_callable($mappingData['deleteFunction'])) {
                    call_user_func_array($mappingData['deleteFunction'], [$this, $name, $mappingData]);
                    continue;
                }
                if (array_key_exists('delete', $mappingData) && false === $mappingData['delete']) {
                    continue;
                }
                if (!array_key_exists('deleteFields', $mappingData) || empty($mappingData['deleteFields'])) {
                    throw new \Exception(static::class . ' missing $deleteFields in reference mapping');
                }
                $addFields = [];
                $referenceEntityClass = $mappingData['class'];
                if (!is_string($referenceEntityClass)) {
                    throw new \InvalidArgumentException('Invalid reference entity class');
                }
                /** @var class-string<Base> $referenceEntityClass */
                foreach ($mappingData['deleteFields'] as $sourceKey => $val) {
                    $addFields[$sourceKey] = $val;
                }

                /** @psalm-suppress UnsafeInstantiation */
                $referencesInstance = new $referenceEntityClass(
                    $this->getPDOAccess(),
                    $addFields,
                    false
                );
                if (!$referencesInstance instanceof Base) {
                    throw new \InvalidArgumentException($referenceEntityClass . ' must extend ' . Base::class);
                }
                $referencesInstance->setupFields();

                $referencesInstance->deleteWith(
                    array_intersect_key($referencesInstance->getFields(), $addFields)
                );
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    final public function deleteWith(array $fields): void
    {
        try {
            $sql = "DELETE FROM " . static::getTableName();
            if (!empty($fields)) {
                $where = array_map(function (int|string $field): string {
                    return $field . ' = ?';
                }, array_keys($fields));
                $sql .= " WHERE " . implode(' AND ', $where);
            }

            $stm = $this->getPDOAccess()->prepare($sql);
            $stm->execute(array_values($fields));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function clearEntity(array $addWhere = []): void
    {
        try {
            $this->deleteWith($addWhere);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function clearEntityReferences(): void
    {
        try {
            foreach ($this->getReferenceMapping(true) as $name => $mappingData) {
                $referenceEntityClass = $mappingData['class'];
                if (!is_string($referenceEntityClass)) {
                    throw new \InvalidArgumentException('Invalid reference entity class');
                }
                /** @var class-string<Base> $referenceEntityClass */
                $clearFields = [];
                $position = 0;
                foreach (($mappingData['clearFields'] ?? []) as $key => $value) {
                    if (is_callable($value)) {
                        $clearFields[$key] = call_user_func_array($value, [$position++, $name, null]);
                    } else {
                        $clearFields[$key] = $value;
                    }
                }

                /** @psalm-suppress UnsafeInstantiation */
                $referencesInstance = new $referenceEntityClass(
                    $this->getPDOAccess(),
                    [],
                    false
                );
                if (!$referencesInstance instanceof Base) {
                    throw new \InvalidArgumentException($referenceEntityClass . ' must extend ' . Base::class);
                }
                if (!empty($clearFields)) {
                    $referencesInstance->deleteWith($clearFields);
                } else {
                    $referencesInstance->clearEntity();
                }
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public static function getTableName(): string
    {
        $classNameWithNs = explode("\\", static::class);
        $className = end($classNameWithNs);
        $table = defined('static::TABLENAME')
            ? constant('static::TABLENAME')
            : preg_replace('/(?<!^)[A-Z]/', '_$0', $className);

        return strtolower((string) $table);
    }
}
