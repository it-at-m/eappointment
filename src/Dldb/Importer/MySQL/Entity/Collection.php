<?php

namespace BO\Dldb\Importer\MySQL\Entity;

class Collection implements \Countable, \ArrayAccess
{
    protected $entities = [];

    final public function offsetExists($offset) : bool
    {
        return array_key_exists($offset, $this->entities);
    }

    final public function offsetGet($offset)
    {
        if ($this->offsetExists($offset)) {
            return $this->entities[$offset];
        }
        throw new \InvalidArgumentException(__METHOD__ . " offset({$offset}) has not been set!");
    }

    final public function offsetSet($offset, $value) : Collection
    {
        if (!$value instanceof Base) {
            throw new \InvalidArgumentException(
                __METHOD__ . ' $value must be an instance of \\BO\\Dldb\\Importer\\MySQL\\Entity\\Base'
            );
        }
        if (null === $offset) {
            $this->entities[] = $value;
        } else {
            $this->entities[$offset] = $value;
        }
        return $this;
    }

    final public function offsetUnset($offset) : Collection
    {
        unset($this->entities[$offset]);
        return $this;
    }

    final public function count() : int
    {
        return count($this->entities);
    }

    public function saveEntities()
    {
        try {
            foreach ($this->entities as $entity) {
                $entity->save();
            }
        } catch (\Exception $e) {
            throw $e;
        }
    }
}
