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
 * @implements \ArrayAccess<string, mixed>
 */
abstract class Base implements \Countable, \ArrayAccess, \JsonSerializable
{
    use ItemNeedsUpdateTrait;
    use PDOTrait;

    protected array $fieldMapping = [];

    protected array $fields = [];

    protected array $referanceMapping = [];

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

    final public function setupFields(): bool
    {
        try {
            if (false === $this->setupFields) {
                return true;
            }
            $this->preSetupFields();

            $values = $this->get(array_keys(array_filter($this->fieldMapping)));
            foreach ($values as $key => $value) {
                $this->__set($key, $value);
            }
            $this->postSetupFields();
            $this->setupFields = false;
            return true;
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

    final public function setupReferences(): bool
    {
        try {
            if (false === $this->setupReferences) {
                return true;
            }
            $values = $this->getReferenceFields();

            foreach ($values as $name => $references) {
                $referenceEntityClass = $this->referanceMapping[$name]['class'];
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
            return true;
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
            } elseif (stripos($name, '_json')) {
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
        return $this->__isset($offset);
    }

    #[\Override]
    final public function offsetGet($offset): mixed
    {
        return $this->__get($offset);
    }

    #[\Override]
    final public function offsetSet($offset, $value): Base
    {
        $this->__set($offset, $value);
        return $this;
    }

    #[\Override]
    final public function offsetUnset($offset): Base
    {
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
        if (false === $keys) {
            throw new \Exception('Invalid key, key must be a string!');
        }
        $keys = array_filter($keys, 'strlen');
        $keys = array_map(function ($key) {
            return ((is_numeric($key) && !is_double(1 * $key)) ? (int)$key : $key);
        }, $keys);

        return $keys;
    }

    public function save(): bool
    {
        try {
            if (static::STATUS_NEW !== $this->getStatus()) {
                return false;
            }
            $this->saveEntitiy();
            $this->saveReferences();
            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    final public function saveEntitiy(): bool
    {
        try {
            if (static::STATUS_NEW !== $this->getStatus()) {
                return false;
            }
            if (!empty($this->fields)) {
                $sql = 'REPLACE INTO ' . static::getTableName() . ' ';
                $sql .= '(`' . implode('`, `', array_keys($this->fields)) . '`) ';

                $questionMarks = array_fill(0, count($this->fields), '?');
                $sql .= 'VALUES (' . implode(', ', $questionMarks) . ') ';

                $stm = $this->getPDOAccess()->prepare($sql);

                $stm->execute(array_values($this->fields));

                if ($stm && 0 < $stm->rowCount()) {
                    return true;
                }
                throw new \Exception('Could not save entity');
            }
            throw new \Exception('Could not save entity, fields are empty');
        } catch (\Exception $e) {
            throw $e;
        }
    }

    final public function saveReferences(): bool
    {
        try {
            if (static::STATUS_NEW !== $this->getStatus()) {
                return false;
            }
            if (!empty($this->references)) {
                array_map(function ($referencesCollection) {
                    $referencesCollection->saveEntities();
                }, $this->references);
            }
            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function delete(): bool
    {
        try {
            $this->deleteEntity();
            $this->deleteReferences();
            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    abstract public function deleteEntity(): bool;

    public function deleteReferences(): bool
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
                foreach ($mappingData['deleteFields'] as $sourceKey => $val) {
                    $addFields[$sourceKey] = $val;
                }

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
            return true;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    final public function deleteWith(array $fields): bool
    {
        try {
            $sql = "DELETE FROM " . static::getTableName();
            if (!empty($fields)) {
                $where = array_map(function ($field) {
                    return $field . ' = ?';
                }, array_keys($fields));
                $sql .= " WHERE " . implode(' AND ', $where);
            }

            $stm = $this->getPDOAccess()->prepare($sql);
            $stm->execute(array_values($fields));
            if ($stm && 0 < $stm->rowCount()) {
                return true;
            }
            return false;
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function clearEntity(array $addWhere = []): bool
    {
        try {
            return $this->deleteWith($addWhere);
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function clearEntityReferences(): bool
    {
        try {
            foreach ($this->getReferenceMapping(true) as $name => $mappingData) {
                $referenceEntityClass = $mappingData['class'];
                $clearFields = [];
                $position = 0;
                foreach (($mappingData['clearFields'] ?? []) as $key => $value) {
                    if (is_callable($value)) {
                        $clearFields[$key] = call_user_func_array($value, [$position++, $name, null]);
                    } else {
                        $clearFields[$key] = $value;
                    }
                }

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
            return true;
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
