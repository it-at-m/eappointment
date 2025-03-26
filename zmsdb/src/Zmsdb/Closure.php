<?php

namespace BO\Zmsdb;

use BO\Zmsentities\Closure as Entity;
use BO\Zmsentities\Collection\ClosureList as Collection;
use DateTime;

class Closure extends Base
{
    public function readByScopeId($scopeId = 0)
    {
        $closureList = new Collection();
        $query = new Query\Closure(Query\Base::SELECT);
        $query->addEntityMapping()
            ->addConditionScopeId($scopeId);
        $result = $this->fetchList($query, new Entity());
        if (count($result)) {
            foreach ($result as $entity) {
                if ($entity instanceof Entity) {
                    $closureList->addEntity($entity);
                }
            }
        }
        return $closureList;
    }

    public function readByScopeIdAndDate($scopeId, DateTime $date)
    {
        $query = new Query\Closure(Query\Base::SELECT);
        $query->addEntityMapping()
            ->addConditionScopeId($scopeId)
            ->addConditionDate($date);

        return $this->fetchOne($query, new \BO\Zmsentities\Closure());
    }

    public function deleteEntity($itemId)
    {
        $query = new Query\Closure(Query\Base::DELETE);
        $query->addConditionId($itemId);
        return ($this->deleteItem($query));
    }

    public function createOne($scopeId, DateTime $date)
    {
        $query = new Query\Closure(Query\Base::INSERT);
        $query->addValues(
            array(
                'StandortID' => $scopeId,
                'year' => (int) $date->format('Y'),
                'month' => (int) $date->format('m'),
                'day' => (int) $date->format('d')
            )
        );
        $this->writeItem($query);
        $id = $this->getWriter()->lastInsertId();

        return $this->readEntity($id);
    }

    public function readEntity($id)
    {
        $query = new Query\Closure(Query\Base::SELECT);
        $query->addEntityMapping()
            ->addConditionId($id);

        return $this->fetchOne($query, new \BO\Zmsentities\Closure());
    }
}
