<?php

namespace BO\Zmsdb;

use BO\Zmsdb\Application as App;
use BO\Zmsentities\Closure as Entity;
use BO\Zmsentities\Collection\ClosureList as Collection;
use DateTime;

class Closure extends Base
{
    public function readByScopeId($scopeId = 0, $disableCache = false)
    {
        $cacheKey = "closuresByScope-$scopeId";

        if (!$disableCache && App::$cache) {
            $data = App::$cache->get($cacheKey);
            if (!empty($data)) {
                return $data;
            }
        }

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

        if (App::$cache) {
            App::$cache->set($cacheKey, $closureList);
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

    public function readByScopesInRange(
        array $scopeIds,
        \DateTimeInterface $from,
        \DateTimeInterface $until
    ): array {
        $query = (new \BO\Zmsdb\Query\Closure(\BO\Zmsdb\Query\Base::SELECT))
            ->addEntityMapping()
            ->addSelectVirtualDate()
            ->addConditionScopeIds($scopeIds)
            ->addConditionDateRange($from, $until);

        $entities = $this->fetchList($query, new Entity());

        $result  = [];
        foreach ($entities as $entity) {
            $date = $entity->date ?? $entity->getDateTime()->format('Y-m-d');
            $result [] = [
                'scopeId' => (int) $entity->scopeId,
                'date'    => (string) $date,
            ];
        }
        return $result ;
    }

    public function deleteEntity($closure)
    {
        $query = new Query\Closure(Query\Base::DELETE);
        $query->addConditionId($closure->getId());

        if (App::$cache) {
            App::$cache->delete('closuresByScope-' . $closure->scopeId);
        }

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

        if (App::$cache) {
            App::$cache->delete('closuresByScope-' . $scopeId);
        }

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
