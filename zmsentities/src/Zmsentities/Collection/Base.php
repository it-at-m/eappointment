<?php
/**
 * @package Dldb
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsentities\Collection;

use \BO\Zmsentities\Helper\Sorter;
use \BO\Zmsentities\Schema\Entity;

/**
 * @SuppressWarnings(NumberOfChildren)
 * @SuppressWarnings(Public)
 * @SuppressWarnings(Complexity)
 *
 */
class Base extends \ArrayObject implements \JsonSerializable
{
    const ENTITY_CLASS = '';

    /**
     * @var Int $resolveLevel indicator on data integrity
     */
    protected $resolveLevel = null;

    public function getFirst()
    {
        $item = $this->getIterator()->current();
        return $item;
    }

    public function getLast()
    {
        $copy = $this->getArrayCopy();
        $item = end($copy);
        return $item;
    }

    public function sortByName()
    {
        $this->uasort(function ($a, $b) {
            return strcmp(
                Sorter::toSortableString(ucfirst($a->name)),
                Sorter::toSortableString(ucfirst($b->name))
            );
        });
        return $this;
    }

    public function sortByContactName()
    {
        $this->uasort(function ($a, $b) {
            return strcmp(
                Sorter::toSortableString(ucfirst($a->contact['name'])),
                Sorter::toSortableString(ucfirst($b->contact['name']))
            );
        });
        return $this;
    }

    public function sortByCustomKey($key)
    {
        $this->uasort(function ($a, $b) use ($key) {
            return ($a[$key] - $b[$key]);
        });
        return $this;
    }

    public function sortByCustomStringKey($key)
    {
        $this->uasort(function ($a, $b) use ($key) {
            return strcmp(
                Sorter::toSortableString(ucfirst($a[$key])),
                Sorter::toSortableString(ucfirst($b[$key]))
            );
        });
        return $this;
    }

    public function __clone()
    {
        foreach ($this as $key => $property) {
            $this[$key] = clone $property;
        }
    }

    public function hasEntity($primary)
    {
        foreach ($this as $entity) {
            if (isset($entity->{$entity::PRIMARY}) && $primary == $entity->{$entity::PRIMARY}) {
                return true;
            }
        }
        return false;
    }

    public function getEntity($primary)
    {
        foreach ($this as $entity) {
            if (isset($entity->{$entity::PRIMARY}) && $primary == $entity->{$entity::PRIMARY}) {
                return $entity;
            }
        }
        return null;
    }

    public function addEntity(\BO\Zmsentities\Schema\Entity $entity)
    {
        $this->offsetSet(null, $entity);
        return $this;
    }

    public function offsetSet($index, $value)
    {
        $className = $this::ENTITY_CLASS;
        if (is_a($value, $className)) {
            return parent::offsetSet($index, $value);
        } elseif (is_array($value)) {
            return parent::offsetSet($index, new $className($value));
        } else {
            throw new \Exception('Invalid entity ' . get_class($value) . ' for collection '. __CLASS__);
        }
    }

    public function addData($mergeData)
    {
        foreach ($mergeData as $item) {
            if ($item instanceof Entity) {
                if (null === $item->getResolveLevel()) {
                    $item->setResolveLevel($this->getResolveLevel());
                }
                $this->addEntity($item);
            } else {
                $className = $this::ENTITY_CLASS;
                $entity = new $className($item);
                $entity->setResolveLevel($this->getResolveLevel());
                $this->addEntity($entity);
            }
        }
        return $this;
    }

    public function addList(Base $list)
    {
        foreach ($list as $item) {
            $this->addEntity($item);
        }
        return $this;
    }

    public function getIds()
    {
        $list = [];
        foreach ($this as $entity) {
            $list[] = $entity->id;
        }
        return $list;
    }

    public function getIdsCsv()
    {
        return implode(',', $this->getIds());
    }

    public function getCsvForProperty($propertyName, $csvSeperator = ',')
    {
        $list = [];
        foreach ($this as $entry) {
            if ($entry->hasProperty($propertyName)) {
                $list[] = $entry->getProperty($propertyName);
            }
        }
        return implode($csvSeperator, $list);
    }

    public function getCsvForPropertyList(array $propertyList, $propertySeperator = '', $csvSeperator = ',')
    {
        $list = [];
        foreach ($this as $entry) {
            $propertyConcat = '';
            foreach ($propertyList as $propertyName) {
                if ($entry->hasProperty($propertyName)) {
                    if ($propertyConcat) {
                        $propertyConcat .= $propertySeperator;
                    }
                    $propertyConcat .= $entry->getProperty($propertyName);
                }
            }
            if ($propertyConcat) {
                $list[] = $propertyConcat;
            }
        }
        return implode($csvSeperator, $list);
    }

    /**
     * Change a parameter on all entries
     */
    public function withValueFor($param, $newValue)
    {
        $list = new static();
        foreach ($this as $entry) {
            $list[] = $entry->withProperty($param, $newValue);
        }
        return $list;
    }

    /**
     * Reduce items data of dereferenced entities to a required minimum
     *
     */
    public function withLessData(array $keepArray = [])
    {
        $list = new static();
        foreach ($this as $key => $item) {
            $list[$key] = $item->withLessData($keepArray);
        }
        return $list;
    }

    public function setJsonCompressLevel($jsonCompressLevel)
    {
        foreach ($this as $item) {
            $item->setJsonCompressLevel($jsonCompressLevel);
        }
    }

    public function jsonSerialize()
    {
        return $this->getArrayCopy();
    }

    public function __toString()
    {
        $list = [];
        foreach ($this as $item) {
            $list[] = $item->__toString();
        }
        return "[" . implode(',', $list) . "]";
    }

    /**
     * @return Int
     */
    public function getResolveLevel()
    {
        return $this->resolveLevel;
    }

    /**
     * @param Int $resolveLevel
     * @return self
     */
    public function setResolveLevel($resolveLevel)
    {
        $this->resolveLevel = $resolveLevel;
        return $this;
    }

    /**
     * @param Int $resolveLevel
     * @return self
     */
    public function withResolveLevel($resolveLevel)
    {
        $collection = new static();
        foreach ($this as $entity) {
            $reduced = $entity->withResolveLevel($resolveLevel);
            if (null !== $reduced) {
                $collection[] = $reduced;
            }
        }
        return $collection;
    }
}
