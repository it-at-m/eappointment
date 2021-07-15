<?php

namespace BO\Dldb\Importer\MySQL\Entity;

use BO\Dldb\PDOAccess,
    BO\Dldb\Importer\PDOTrait,
    BO\Dldb\Importer\ItemNeedsUpdateTrait,
    BO\Dldb\Importer\MySQL\Entity\Collection AS EntityCollection
;

abstract class Base implements \Countable, \ArrayAccess, \JsonSerializable
{
    use ItemNeedsUpdateTrait, PDOTrait;
    
    protected $fieldMapping = [];

    protected $fields = [];

    protected $referanceMapping = [];

    protected $preFormatFields = [];

    protected $references = [];

    protected $dataRaw = [];

    protected $setupFields = true;
    protected $setupReferences = true;

    protected $status = 1;

    const STATUS_NEW = 1;
    const STATUS_OLD = 0;

    public function __construct(PDOAccess $mySqlAccess, array $dataRaw = [], bool $setup = true) {
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
        }
        catch (\Exception $e) {
            throw $e;
        }
    }

    public function setRawData(array $rawData = []) {
        $this->dataRaw = $rawData;
        return $this;
    }

    public function getRawData() : array {
        return $this->dataRaw;
    }

    public function setStatus(int $status = Base::STATUS_NEW) {
        $this->status = $status;
    }

    public function getStatus() : int {
        return $this->status;
    }

    public function getReferenceMapping($setup = false) : array {
        try {
            if (true === $setup) {
                $this->setupMapping();
            }

            return $this->referanceMapping;
        }
        catch (\Exception $e) {
            throw $e;
        }
    }

    protected function setupPreFormatFields() {}

    protected function setupMapping() {}

    public function preSetup() {}

    public function postSetup() {}
    
    public function preSetupFields() {}

    public function postSetupFields() {}

    final public function setupFields() : bool {
        try {
            if (false === $this->setupFields) {
                return true;
            }
            $this->preSetupFields();
            
            $values = $this->get(array_keys(array_filter($this->fieldMapping)));
            foreach ($values AS $key => $value) {
                
                $this->__set($key, $value);
            }
            $this->postSetupFields();
            $this->setupFields = false;
            return true;
        }
        catch (\Exception $e) {
            throw $e;
        }
    }

    protected function getReferenceFields() {
        $referenceFields = array_flip(array_keys(array_filter($this->referanceMapping)));
        
        foreach ($referenceFields AS $name => $v) {
            $referenceFields[$name] = $this->get($name);
        }
        return $referenceFields;
    }

    final public function setupReferences() {
        try {
            if (false === $this->setupReferences) {
                return true;
            }
            $values = $this->getReferenceFields();

            foreach ($values AS $name => $references) {
                $referenceEntityClass = $this->referanceMapping[$name]['class'];
                $addFields = [];
                
                foreach ($this->referanceMapping[$name]['neededFields'] AS $sourceKey => $destinationKey) {
                    $addFields[$destinationKey] = $this->get($sourceKey);
                }
                $isMultiple = $this->referanceMapping[$name]['multiple'] ?? true;
               
                if (false === $isMultiple) {
                    $references = [$references];
                }
                
                $position = 0;
                foreach ($references AS $reference) {
                    foreach ($this->referanceMapping[$name]['addFields'] AS $key => $value) {
                        if (is_callable($value)) {
                            $addFields[$key] = call_user_func_array($value, [$position, $name, $reference]);
                        }
                        else {
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
                        array_merge($reference, $addFields)
                    );

                    $this->addReference($name, $referencesInstance);
                    $position++;
                }
            }
            $this->setupReferences = false;
            return true;
        }
        catch (\Exception $e) {
            throw $e;
        }
    }

    final public function __set($name, $value) {
        if (array_key_exists($name, $this->fieldMapping)) {
            $name = $this->fieldMapping[$name];
            if (is_bool($value)) {
                $value = (int)$value;
            }
            else if (stripos($name, '_json')) {
                $value = json_encode($value);
            }
            $this->fields[$name] = $value;
        }
        else if (array_key_exists($name, $this->referanceMapping)) {
            $this->addReference($name, $value);
        }
    }

    public function addReference(string $name, Base $reference) {
        if (array_key_exists($name, $this->referanceMapping)) {
            if (!isset($this->references[$name])) {
                $this->references[$name] = new EntityCollection();
            }
            $this->references[$name][] = $reference;
        }
    }

    public function getReference(string $name) {
        if (array_key_exists($name, $this->references)) {
            return $this->references[$name];
        }
        throw new \InvalidArgumentException(__METHOD__ . " reference {$name} has not been set!");
    }

    final public function __get($name) {
        if (array_key_exists($name, $this->fields)) {
            return $this->fields[$name];
        }
        if (array_key_exists($name, $this->references)) {
            return $this->references[$name];
        }
        throw new \InvalidArgumentException(__METHOD__ . " {$name} has not been set!");
    }

    final public function __isset($name) : bool {
        return array_key_exists($name, $this->fields) || array_key_exists($name, $this->references);
    }

    final public function __unset($name) {
        if (array_key_exists($name, $this->fields)) {
            unset($this->fields[$name]);
        }
        if (array_key_exists($name, $this->references)) {
            unset($this->references[$name]);
        }
    }

    final public function offsetExists($offset) : bool {
        return $this->__isset($offset);
    }

    final public function offsetGet($offset) {
        return $this->__get($offset);
    }

    final public function offsetSet($offset, $value) : Base {
        $this->__set($offset, $value);
        return $this;
    }

    final public function offsetUnset($offset) : Base {
        $this->__unset($offset);
        return $this;
    }

    final public function count() : int {
        return count($this->fields);
    }

    public function jsonSerialize() {
        return $this->fields;
    }

    public function getFields() : array {
        return $this->fields;
    }

    public function get($key = null, $default = null) {
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

            $pointer = &$this->dataRaw;
            for ($i = 0; $i < count($levels); ++$i) {
                if (array_key_exists($levels[$i], $pointer)) {
                    $pointer = &$pointer[$levels[$i]];
                    $value = $pointer;
                    
                    continue;
                }
            }
            $values[$key] = ($value);
        }
        return 1 == count($keys) ? $values[$keys[0]] : $values;
    }

    protected static function arrayAccessByDotPerpareKeys(string $key = null) : array {
        if (null === $key) {
            return [];
        }
        $keys = explode('.', $key);
        if (false === $keys) {
            throw new \Exception('Invalid key, key must be a string!');
        }
        $keys = array_filter($keys, 'strlen');
        $keys = array_map(function($key) {
            return ((is_numeric($key) && !is_double(1*$key)) ? (int)$key : $key);
        }, $keys);

        return $keys;
    }

    public function save() {
        try {
            if (static::STATUS_NEW !== $this->getStatus()) {
                return false;
            }
            $this->saveEntitiy();
            $this->saveReferences();
            return true;
        }
        catch (\Exception $e) {
            throw $e;
        }
    }

    final public function saveEntitiy() : bool {
        try {
            if (static::STATUS_NEW !== $this->getStatus()) {
                return false;
            }
            if (!empty($this->fields)) {
                $sql = 'REPLACE INTO ' . static::getTableName() . ' ';
                $sql .= '(`' . implode('`, `', array_keys($this->fields)) . '`) ';
                
                $qm = array_fill(0, count($this->fields), '?');
                $sql .= 'VALUES (' . implode(', ', $qm) . ') ';

                #print_r($sql . \PHP_EOL) ;
                $stm = $this->getPDOAccess()->prepare($sql);

                $stm->execute(array_values($this->fields));
                
                if ($stm && 0 < $stm->rowCount()) {
                    return true;
                }
                throw new \Exception('Could not save entity');
            }
            throw new \Exception('Could not save entity, fields are empty');
            return false;
        }
        catch (\Exception $e) {
            throw $e;
        }
        return false;
    }

    final public function saveReferences() : bool {
        try {
            if (static::STATUS_NEW !== $this->getStatus()) {
                return false;
            }
            if (!empty($this->references)) {
                array_map(function($referencesCollection) {
                    $referencesCollection->saveEntities();
                }, $this->references);
            }
            return true;
        }
        catch (\Exception $e) {
            throw $e;
        }
    }

    public function delete() : bool {
        try {
            $this->deleteEntity();
            $this->deleteReferences();
            return true;
        }
        catch (\Exception $e) {
            throw $e;
        }
    }

    abstract public function deleteEntity() : bool;

    public function deleteReferences(): bool
    {
        #return true;
        try {
            foreach ($this->referanceMapping AS $name => $mappingData) {
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
                foreach ($mappingData['deleteFields'] AS $sourceKey => $val) {
                    $addFields[$sourceKey] = $val;
                }
                
                
                $referencesInstance = new $referenceEntityClass(
                    $this->getPDOAccess(), 
                    $addFields,
                    false
                );
                $referencesInstance->setupFields();

                #print_r(array_intersect_key($referencesInstance->getFields(), $addFields));
                #exit;

                $referencesInstance->deleteWith(
                    array_intersect_key($referencesInstance->getFields(), $addFields)
                );
            }
            return true;
        }
        catch (\Exception $e) {
            throw $e;
        }
    }

    final public function deleteWith(array $fields): bool
    {
        try {
            $sql = "DELETE FROM " . static::getTableName();
            if (!empty($fields)) {
                $where = array_map(function($field) {
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
        }
        catch (\Exception $e) {
            throw $e;
        }
    }

    public function clearEntity(array $addWhere = []) : bool {
        try {
            return $this->deleteWith($addWhere);
        }
        catch (\Exception $e) {
            throw $e;
        }
    }

    public function clearEntityReferences() : bool {
        try {
            foreach ($this->getReferenceMapping(true) AS $name => $mappingData) {
                $referenceEntityClass = $mappingData['class'];
                $clearFields = [];
                $position = 0;
                foreach (($mappingData['clearFields'] ?? []) AS $key => $value) {
                    if (is_callable($value)) {
                        $clearFields[$key] = call_user_func_array($value, [$position++, $name, null]);
                    }
                    else {
                        $clearFields[$key] = $value;
                    }
                }
                $referencesInstance = new $referenceEntityClass(
                    $this->getPDOAccess(), 
                    [],
                    false
                );
                $referencesInstance->deleteWith($clearFields);
            }
            return true;
        }
        catch (\Exception $e) {
            throw $e;
        }
    }

    public static function getTableName() : string {
        if (defined('static::TABLENAME')) {
            return strtolower(static::TABLENAME);
        }
        $e = explode("\\", static::class);
        return strtolower(end($e));
    }
}