<?php
namespace BO\Zmsentities\Collection;

class RequestList extends Base
{

    public function addEntity($entity)
    {
        $this[] = clone $entity;
        return $this;
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
