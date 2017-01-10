<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Scope as Entity;
use \BO\Zmsentities\Collection\ScopeList as Collection;

/**
 *
 * @SuppressWarnings(Public)
 *
 */
class Scope extends Base
{

    public static $cache = [ ];

    public function readEntity($scopeId, $resolveReferences = 0, $disableCache = false)
    {
        $cacheKey = "$scopeId-$resolveReferences";
        if (! $disableCache && ! array_key_exists($cacheKey, self::$cache)) {
            $query = new Query\Scope(Query\Base::SELECT);
            $query->addEntityMapping()
                ->addResolvedReferences($resolveReferences)
                ->addConditionScopeId($scopeId);
            $scope = $this->fetchOne($query, new Entity());
            if (! $scope->hasId()) {
                return null;
            }
            $scope = $this->addDldbData($scope, $resolveReferences);
            if (0 < $resolveReferences) {
                $scope['dayoff'] = (new DayOff())->readByScopeId($scopeId);
            }
            self::$cache[$cacheKey] = $scope;
        }
        return self::$cache[$cacheKey];
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
                            'id' => $entity->id,
                            '$ref' => '/scope/' . $entity->id . '/'
                        )
                    );
                    $scopeList->addEntity($entity);
                } else {
                    if ($entity instanceof Entity) {
                        $entity = $this->addDldbData($entity, $resolveReferences);
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
                        $entity = $this->addDldbData($entity, $resolveReferences);
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
                        $entity = $this->addDldbData($entity, $resolveReferences);
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
     * get last given waitingnumer and return updated (+1) waitingnumber
     *
     * * @param
     * scopeId
     * now
     *
     * @return Bool
     */
    public function readWaitingNumberUpdated($scopeId, $dateTime)
    {
        if (! $this->readIsGivenNumberInContingent($scopeId)) {
            throw new Exception\Scope\GivenNumberCountExceeded();
        }
        $this->getReader()
            ->fetchValue((new Query\Scope(Query\Base::SELECT))
            ->getQueryLastWaitingNumber(), ['scope_id' => $scopeId]);
        $entity = $this->readEntity($scopeId)->updateStatusQueue($dateTime);
        $scope = $this->updateEntity($scopeId, $entity);
        return $scope->getStatus('queue', 'lastGivenNumber');
    }

    /**
     * get last given waitingnumer and return updated (+1) waitingnumber
     *
     * * @param
     * scopeId
     * now
     *
     * @return Bool
     */
    protected function readIsGivenNumberInContingent($scopeId)
    {
        $isInContingent = $this->getReader()
            ->fetchValue((new Query\Scope(Query\Base::SELECT))
            ->getQueryGivenNumbersInContingent(), ['scope_id' => $scopeId]);
        return ($isInContingent) ? true : false;
    }

    /**
     * get list of queues on scope by daytime
     *
     * * @param
     * scopeId
     * now
     *
     * @return number
     */
    public function readQueueList($scopeId, $dateTime)
    {
        $queueList = (new Process())
            ->readProcessListByScopeAndTime($scopeId, $dateTime)
            ->toQueueList($dateTime);
        return $queueList->withSortedArrival();
    }

    /**
     * get waitingtime of scope
     *
     * * @param
     * scopeId
     * now
     *
     * @return number
     */
    public function readWithWorkstationCount($scopeId, $dateTime)
    {
        //get scope
        $query = new Query\Scope(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addConditionScopeId($scopeId)
            ->addSelectWorkstationCount($dateTime);
        return $this->fetchOne($query, new Entity());
    }

    public function readQueueListWithWaitingTime($scope, $dateTime)
    {
        $queueList = $this->readQueueList($scope->id, $dateTime);
        $timeAverage = $scope->getPreference('queue', 'processingTimeAverage');
        $workstationCount = $scope->getCalculatedWorkstationCount();
        return $queueList->withEstimatedWaitingTime($timeAverage, $workstationCount, $dateTime);
    }

    /**
     * write a scope
     *
     * @return Entity
     */
    public function writeEntity(\BO\Zmsentities\Scope $entity, $parentId)
    {
        self::$cache = [];
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
        self::$cache = [];
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
        self::$cache = [];
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
