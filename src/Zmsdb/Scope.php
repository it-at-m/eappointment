<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Scope as Entity;
use \BO\Zmsentities\Collection\ScopeList as Collection;

class Scope extends Base
{

    public static $cache = [ ];

    public function readEntity($scopeId, $resolveReferences = 0, $disableCache = false)
    {
        if (! $disableCache && ! array_key_exists($scopeId, self::$cache)) {
            $query = new Query\Scope(Query\Base::SELECT);
            $query->addEntityMapping()
                ->addResolvedReferences($resolveReferences)
                ->addConditionScopeId($scopeId);
            $scope = $this->fetchOne($query, new Entity());
            $scope = $this->addDldbData($scope, $resolveReferences);
            $scope['dayoff'] = (new DayOff())->readByScopeId($scopeId);
            self::$cache[$scopeId] = $scope;
        }
        return self::$cache[$scopeId];
    }

    public function readByClusterId($clusterId, $resolveReferences = 0)
    {
        $scopeList = new Collection();
        $query = new Query\Scope(Query\Base::SELECT);
        $query->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionClusterId($clusterId);
        $result = $this->fetchList($query, new Entity());
        if (count($result)) {
            foreach ($result as $entity) {
                if (0 == $resolveReferences) {
                    $entity = new Entity(
                        array (
                            // 'id' => $entity->id,
                            '$ref' => '/scope/' . $entity->id . '/'
                        )
                    );
                    $scopeList->addEntity($entity);
                } else {
                    if ($entity instanceof Entity) {
                        $scopeList->addEntity($entity);
                    }
                }
            }
        }
        return $scopeList;
    }

    public function readByProviderId($providerId, $resolveReferences = 0)
    {
        $scopeList = new Collection();
        $query = new Query\Scope(Query\Base::SELECT);
        $query->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionProviderId($providerId);
        $result = $this->fetchList($query, new Entity());
        if (count($result)) {
            foreach ($result as $entity) {
                if (0 == $resolveReferences) {
                    $entity = new Entity(
                        array (
                            'id' => $entity->id,
                            '$ref' => '/scope/' . $entity->id . '/'
                        )
                    );
                    $scopeList->addEntity($entity);
                } else {
                    if ($entity instanceof Entity) {
                        $scopeList->addEntity($entity);
                    }
                }
            }
        }
        return $scopeList;
    }

    public function readByDepartmentId($departmentId, $resolveReferences = 0)
    {
        $scopeList = new Collection();
        $query = new Query\Scope(Query\Base::SELECT);
        $query->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionDepartmentId($departmentId);
        $result = $this->fetchList($query, new Entity());
        if (count($result)) {
            foreach ($result as $entity) {
                if (0 == $resolveReferences) {
                    $entity = new Entity(
                        array (
                            'id' => $entity->id,
                            '$ref' => '/scope/' . $entity->id . '/'
                        )
                    );
                    $scopeList->addEntity($entity);
                } else {
                    if ($entity instanceof Entity) {
                        $scopeList->addEntity($entity);
                    }
                }
            }
        }
        return $scopeList;
    }

    public function readList($resolveReferences = 0)
    {
        $scopeList = new Collection();
        $query = new Query\Scope(Query\Base::SELECT);
        $query->addEntityMapping()
            ->addResolvedReferences($resolveReferences);
        $result = $this->fetchList($query, new Entity());
        if (count($result)) {
            foreach ($result as $entity) {
                if ($entity instanceof Entity) {
                    $scopeList->addEntity($entity);
                }
            }
        }
        return $scopeList;
    }

    /**
     * get a scope and return true if it is opened
     *
     * * @param
     * scopeId
     * now
     *
     * @return Bool
     */
    public function readIsOpened($scopeId, $now)
    {
        $isOpened = false;
        $query = new Query\Scope(Query\Base::SELECT);
        $query->addEntityMapping()
            ->addConditionScopeId($scopeId);
        $availabilityList = (new Availability())->readOpeningHoursListByDate($scopeId, $now);
        $scope = $this->fetchOne($query, new Entity());
        if ($availabilityList->isOpened($now) && ! $scope->getStatus('ticketprinter', 'deactivated')) {
            $isOpened = true;
        }
        return $isOpened;
    }

    /**
     * get waitingtime of scope
     *
     * * @param
     * scopeId
     * now
     *
     * @return Bool
     */
    public function readWaitingTime($scopeId, $now)
    {
        $waitingTime = null;
        $processList = (new Process())->readProcessListByScopeId($scopeId);
        $query = new Query\Scope(Query\Base::SELECT);
        $query->addEntityMapping()
            ->addConditionScopeId($scopeId);
        $scope = $this->fetchOne($query, new Entity());
        $workstationCount = ('-1' == $scope->getStatus('queue', 'ghostWorkstationCount')) ?
            count((new Workstation())->readByScope($scopeId)) :
            $scope->getStatus('queue', 'ghostWorkstationCount');
        if (0 < $workstationCount) {
            $timeSlot = $scope->getPreference('queue', 'processingTimeAverage') * 60 / $workstationCount;
            $referenceTime = $now->getTimestamp() + ($timeSlot * count($processList));
            $processList->toReducedWithinTime($referenceTime);
            $waitingTime = count($processList) * $timeSlot;
        }
        return $waitingTime;
    }

    /**
     * write a scope
     *
     * @return Entity
     */
    public function writeEntity(\BO\Zmsentities\Scope $entity, $parentId)
    {
        $query = new Query\Scope(Query\Base::INSERT);
        $values = $query->reverseEntityMapping($entity, $parentId);
        $query->addValues($values);
        $this->writeItem($query);
        $lastInsertId = $this->getWriter()
            ->lastInsertId();
        return $this->readEntity($lastInsertId);
    }

    /**
     * update a scope
     *
     * @param
     *            scopeId
     *
     * @return Entity
     */
    public function updateEntity($scopeId, \BO\Zmsentities\Scope $entity)
    {
        $query = new Query\Scope(Query\Base::UPDATE);
        $query->addConditionScopeId($scopeId);
        $values = $query->reverseEntityMapping($entity);
        $query->addValues($values);
        $this->writeItem($query);
        return $this->readEntity($scopeId);
    }

    /**
     * remove a scope
     *
     * @param
     *            scopeId
     *
     * @return Resource Status
     */
    public function deleteEntity($scopeId)
    {
        $query = new Query\Scope(Query\Base::DELETE);
        $query->addConditionScopeId($scopeId);
        return $this->deleteItem($query);
    }

    protected function addDldbData($scope, $resolveReferences)
    {
        if (isset($scope['provider'])) {
            if ($resolveReferences > 1 && $scope['provider']['source'] == 'dldb') {
                $scope['provider']['data'] = Helper\DldbData::readExtendedProviderData(
                    $scope['provider']['source'],
                    $scope['provider']['id']
                );
            }
        }
        return $scope;
    }
}
