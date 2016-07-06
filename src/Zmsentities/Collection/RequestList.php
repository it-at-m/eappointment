<?php
namespace BO\Zmsentities\Collection;

class RequestList extends Base
{

    public function addEntity($entity)
    {
        $this[] = clone $entity;
        return $this;
    }

    public function hasEntity($entityId)
    {
        foreach ($this as $entity) {
            if ($entityId == $entity->id) {
                return true;
            }
        }
        return false;
    }

    public function getIds()
    {
        $list = array();
        foreach ($this as $entity) {
            $list[] = $entity['id'];
        }
        return $list;
    }
}
