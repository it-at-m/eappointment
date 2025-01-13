<?php

namespace BO\Dldb\Importer\MySQL;

use BO\Dldb\PDOAccess;
use BO\Dldb\Importer\OptionsTrait;
use BO\Dldb\Importer\PDOTrait;
use BO\Dldb\Importer\ItemNeedsUpdateTrait;
use BO\Dldb\Importer\Options;
use BO\Dldb\Importer\MySQL\Entity\Meta as MetaEntity
;

abstract class Base implements Options
{
    use PDOTrait;
    use OptionsTrait;

    protected $entityClass = null;
    protected $importData = [];
    protected $hash = null;
    protected $locale = 'de';
    protected $metaObject = null;
    protected $entitysToDelete = [];
    protected $getCurrentEntitys = true;

    public function __construct(PDOAccess $mySqlAccess, array $importData = [], string $locale = 'de', $options = 0)
    {
        try {
            $this->setPDOAccess($mySqlAccess);
            $this->setImportData($importData['data']);
            $this->setImportHash($importData['hash']);
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

    public function getImportData(): array
    {
        return $this->importData;
    }

    public function getIterator(): iterable
    {
        foreach ($this->importData as $item) {
            yield $item;
        }
    }

    public function getCurrentEntitys(): array
    {
        return $this->entitysToDelete;
    }

    public function removeEntityFromCurrentList(int $entityId)
    {
        unset($this->entitysToDelete[$entityId]);
    }

    public function setCurrentEntitys()
    {
        try {
            if (false === $this->getCurrentEntitys) {
                return true;
            }
            $this->entitysToDelete = [];
            $sql = "SELECT 
            m.object_id AS id, 
            e.data_json AS data_json 
            FROM meta AS m
            JOIN " . $this->entityClass::getTableName() . " AS e ON e.id = m.object_id AND e.locale = ?
            WHERE m.locale = ?";


            $stm = $this->getPDOAccess()->prepare($sql);
            $stm->setFetchMode(\PDO::FETCH_OBJ);
            $stm->execute([$this->getLocale(),$this->getLocale()]);
            $entitys = $stm->fetchAll();
            foreach ($entitys as $entity) {
                $entityObject = $this->createEntity(json_decode($entity->data_json, true));
                $this->entitysToDelete[$entity->id] = $entityObject;
            }
            /*
            error_log(
                print_r([
                    'current',
                    $this->entityClass::getTableName(),
                    $this->getLocale(),
                    count($this->entitysToDelete)
                ],1
            ));
            */
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function createMetaObject()
    {
        try {
            if (empty($this->metaObject)) {
                $metaObject = new MetaEntity(
                    $this->getPDOAccess(),
                    [
                        'object_id' => 0,
                        'locale' => $this->getLocale(),
                        'hash' => $this->getImportHash(),
                        'type' => call_user_func($this->entityClass . '::getTableName')
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
        return $this->metaObject;
    }

    public function saveMetaObject(): self
    {
        $this->getMetaObject()->save();
        return $this;
    }

    public function needsUpdate()
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

    public function createEntity(array $data = array(), bool $setup = true)
    {
        if (null === $this->entityClass) {
            throw new \InvalidArgumentException(__METHOD__ . " invalid entity class");
        }
        return new $this->entityClass($this->getPDOAccess(), $data, $setup);
    }

    final public function clearEntity()
    {
        try {
            $entity = null;
            if ($this->checkOptionFlag(static::OPTION_CLEAR_ENTITIY_TABLE)) {
                $entity = ($entity ?? $this->createEntity(['meta' => ['locale' => $this->getLocale()]], false));
                $entity->clearEntity();
            }
            if ($this->checkOptionFlag(static::OPTION_CLEAR_ENTITIY_REFERENCES_TABLES)) {
                $entity =  ($entity ?? $this->createEntity(['meta' => ['locale' => $this->getLocale()]], false));
                $entity->clearEntityReferences();
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function preImport()
    {
    }

    public function postImport()
    {
    }

    abstract public function runImport();
}
