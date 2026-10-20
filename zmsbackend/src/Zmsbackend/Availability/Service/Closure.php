<?php

namespace BO\Zmsbackend\Availability\Service;

use BO\Zmsbackend\Application as App;
use BO\Zmsentities\Closure as Entity;
use BO\Zmsentities\Collection\ClosureList as Collection;
use DateTime;

class Closure extends \BO\Zmsbackend\Base
{
    public function readByScopeId($scopeId = 0, bool $disableCache = false)
    {
        $cacheKey = "closuresByScope-$scopeId";

        if (!$disableCache && App::$cache) {
            $data = App::$cache->get($cacheKey);
            if (!empty($data)) {
                return $data;
            }
        }

        $closureList = new Collection();
        $query = new \BO\Zmsbackend\Availability\Repository\Closure(\BO\Zmsbackend\Query\Base::SELECT);
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

    public function readByScopeIdAndDate($scopeId, DateTime $date): Entity
    {
        $query = new \BO\Zmsbackend\Availability\Repository\Closure(\BO\Zmsbackend\Query\Base::SELECT);
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
        $query = (new \BO\Zmsbackend\Availability\Repository\Closure(\BO\Zmsbackend\Query\Base::SELECT))
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

    public function deleteEntity($closure): bool
    {
        $query = new \BO\Zmsbackend\Availability\Repository\Closure(\BO\Zmsbackend\Query\Base::DELETE);
        $query->addConditionId($closure->getId());

        $deleted = $this->deleteItem($query);
        $this->invalidateCaches((int) $closure->scopeId);

        return $deleted;
    }

    public function createOne($scopeId, DateTime $date)
    {
        $query = new \BO\Zmsbackend\Availability\Repository\Closure(\BO\Zmsbackend\Query\Base::INSERT);
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
        $this->invalidateCaches((int) $scopeId);

        return $this->readEntity($id);
    }

    /**
     * Clear closure list cache and scope caches that embed closures
     * (scope-{id}-{resolveReferences}, department/cluster lists, in-process static).
     *
     * Always delete scope-* keys (do not gate on CacheInterface::has()): some adapters
     * return false for existing keys, which would leave stale scope.closure for the
     * month overview after toggle.
     */
    protected function invalidateCaches(int $scopeId): void
    {
        if ($scopeId < 1) {
            return;
        }

        if (App::$cache) {
            App::$cache->delete('closuresByScope-' . $scopeId);
            for ($resolveReferences = 0; $resolveReferences <= 3; $resolveReferences++) {
                App::$cache->delete("scope-{$scopeId}-{$resolveReferences}");
            }
        }

        $scope = new \BO\Zmsentities\Scope(['id' => $scopeId]);
        (new \BO\Zmsbackend\Scope\Service\Scope())->removeCacheByContext($scope);

        for ($resolveReferences = 0; $resolveReferences <= 3; $resolveReferences++) {
            unset(\BO\Zmsbackend\Scope\Service\Scope::$cache["{$scopeId}-{$resolveReferences}"]);
        }
    }

    public function readEntity(string|false $id): Entity
    {
        $query = new \BO\Zmsbackend\Availability\Repository\Closure(\BO\Zmsbackend\Query\Base::SELECT);
        $query->addEntityMapping()
            ->addConditionId($id);

        return $this->fetchOne($query, new \BO\Zmsentities\Closure());
    }
}
