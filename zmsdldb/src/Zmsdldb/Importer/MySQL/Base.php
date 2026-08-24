<?php

namespace BO\Zmsdldb\Importer\MySQL;

use BO\Zmsdldb\PDOAccess;
use BO\Zmsdldb\Importer\OptionsTrait;
use BO\Zmsdldb\Importer\PDOTrait;
use BO\Zmsdldb\Importer\Options;
use BO\Zmsdldb\Importer\MySQL\Entity\Meta as MetaEntity;
use BO\Zmsdldb\Importer\MySQL\Entity\Base as EntityBase;

abstract class Base implements Options
{
    use PDOTrait;
    use OptionsTrait;

    /** @var class-string<EntityBase>|null */
    protected ?string $entityClass = null;
    protected array $importData = [];
    protected string $hash = '';
    protected string $locale = 'de';
    protected ?MetaEntity $metaObject = null;
    /** @var array<int, EntityBase> */
    protected array $entitysToDelete = [];
    protected bool $getCurrentEntitys = true;

    public function __construct(PDOAccess $mySqlAccess, array $importData = [], string $locale = 'de', int $options = 0)
    {
        try {
            $this->setPDOAccess($mySqlAccess);
            $data = $importData['data'] ?? [];
            $hash = $importData['hash'] ?? '';
            if (!is_array($data)) {
                throw new \InvalidArgumentException('Import data must be an array');
            }
            if (!is_string($hash)) {
                throw new \InvalidArgumentException('Import hash must be a string');
            }
            $this->setImportData($data);
            $this->setImportHash($hash);
            $this->setLocale($locale);

            $this->setOptions($options);
            $this->clearEntity();
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getPDOAccess(): PDOAccess
    {
        return $this->pdoAccess;
    }

    /**
     * @return array<int, EntityBase>
     */
    public function getCurrentEntitys(): array
    {
        return $this->entitysToDelete;
    }

    public function getIterator(): iterable
    {
        foreach ($this->importData as $item) {
            yield $item;
        }
    }

    public function removeEntityFromCurrentList(int $entityId): void
    {
        unset($this->entitysToDelete[$entityId]);
    }

    public function setCurrentEntitys(): void
    {
        try {
            if (false === $this->getCurrentEntitys) {
                return;
            }
            $this->entitysToDelete = [];
            $entityClass = $this->getEntityClass();
            $sql = "SELECT 
            m.object_id AS id, 
            e.data_json AS data_json 
            FROM meta AS m
            JOIN " . $entityClass::getTableName() . " AS e ON e.id = m.object_id AND e.locale = ?
            WHERE m.locale = ?";


            $stm = $this->getPDOAccess()->prepare($sql);
            $stm->setFetchMode(\PDO::FETCH_OBJ);
            $stm->execute([$this->getLocale(),$this->getLocale()]);
            $entitys = $stm->fetchAll();
            foreach ($entitys as $entity) {
                $decoded = json_decode($entity->data_json, true);
                if (!is_array($decoded)) {
                    continue;
                }
                $entityObject = $this->createEntity($decoded);
                $this->entitysToDelete[(int) $entity->id] = $entityObject;
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function createMetaObject(): void
    {
        try {
            if (empty($this->metaObject)) {
                $entityClass = $this->getEntityClass();
                $metaObject = new MetaEntity(
                    $this->getPDOAccess(),
                    [
                        'object_id' => 0,
                        'locale' => $this->getLocale(),
                        'hash' => $this->getImportHash(),
                        'type' => $entityClass::getTableName()
                    ]
                );
                $this->metaObject = $metaObject;
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function getMetaObject(): MetaEntity
    {
        $this->createMetaObject();
        if (null === $this->metaObject) {
            throw new \RuntimeException('Failed to create meta object');
        }
        return $this->metaObject;
    }

    public function saveMetaObject(): self
    {
        $this->getMetaObject()->save();
        return $this;
    }

    public function needsUpdate(): bool
    {
        $metaObject = $this->getMetaObject();
        $needsUpdate = $metaObject->itemNeedsUpdateAlt();
        if ($needsUpdate) {
            $this->setCurrentEntitys();
        }
        return $needsUpdate;
    }

    public function setImportData(array $importData = []): self
    {
        $this->importData = $importData;
        return $this;
    }

    public function setLocale(string $locale): self
    {
        $this->locale = $locale;
        return $this;
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setImportHash(string $hash): self
    {
        $this->hash = $hash;
        return $this;
    }

    public function getImportHash(): string
    {
        return $this->hash;
    }

    /**
     * @return class-string<EntityBase>
     */
    protected function getEntityClass(): string
    {
        if (null === $this->entityClass) {
            throw new \InvalidArgumentException(__METHOD__ . " invalid entity class");
        }
        return $this->entityClass;
    }

    public function createEntity(array $data = array(), bool $setup = true): EntityBase
    {
        $entityClass = $this->getEntityClass();
        /** @psalm-suppress UnsafeInstantiation */
        $entity = new $entityClass($this->getPDOAccess(), $data, $setup);
        if (!$entity instanceof EntityBase) {
            throw new \InvalidArgumentException($entityClass . ' must extend ' . EntityBase::class);
        }
        return $entity;
    }

    final public function clearEntity(): void
    {
        try {
            if (
                !$this->checkOptionFlag(static::OPTION_CLEAR_ENTITIY_TABLE) &&
                !$this->checkOptionFlag(static::OPTION_CLEAR_ENTITIY_REFERENCES_TABLES)
            ) {
                return;
            }
            $entity = $this->createEntity(['meta' => ['locale' => $this->getLocale()]], false);
            if ($this->checkOptionFlag(static::OPTION_CLEAR_ENTITIY_TABLE)) {
                $entity->clearEntity();
            }
            if ($this->checkOptionFlag(static::OPTION_CLEAR_ENTITIY_REFERENCES_TABLES)) {
                $entity->clearEntityReferences();
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /** @psalm-api */
    public function preImport(): void
    {
    }

    /** @psalm-api */
    public function postImport(): void
    {
    }

    /** @psalm-api */
    abstract public function runImport(): bool;
}
