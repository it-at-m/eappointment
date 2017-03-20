<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Scope as Entity;
use \BO\Zmsentities\Collection\ScopeList as Collection;

/**
 *
 * @SuppressWarnings(Public)
 * @SuppressWarnings(Coupling)
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
            self::$cache[$cacheKey] = $this->readResolvedReferences($scope, $resolveReferences);
        }
        return self::$cache[$cacheKey];
    }

    public function readResolvedReferences(\BO\Zmsentities\Schema\Entity $scope, $resolveReferences)
    {
        if (0 < $resolveReferences) {
            $scope['dayoff'] = (new DayOff())->readByScopeId($scope->id);
        }
        return $scope;
    }

    public function readByClusterId($clusterId, $resolveReferences = 0)
    {
        $scopeList = new Collection();
        if ($resolveReferences > 0) {
            $query = new Query\Scope(Query\Base::SELECT);
            $query->addEntityMapping()
                ->addResolvedReferences($resolveReferences)
                ->addConditionClusterId($clusterId);
            $result = $this->fetchList($query, new Entity());
        } else {
            $result = $this->getReader()->perform(
                (new Query\Scope(Query\Base::SELECT))->getQuerySimpleClusterMatch(),
                [$clusterId]
            );
        }
        if (count($result)) {
            foreach ($result as $entity) {
                if (0 == $resolveReferences) {
                    $entity = new Entity(
                        array(
                            'id' => $entity['id'],
                            '$ref' => '/scope/' . $entity['id'] . '/'
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
                        array(
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
        if ($resolveReferences > 0) {
            $query = new Query\Scope(Query\Base::SELECT);
            $query->addEntityMapping()
                ->addResolvedReferences($resolveReferences)
                ->addConditionDepartmentId($departmentId);
            $result = $this->fetchList($query, new Entity());
        } else {
            $result = $this->getReader()->perform(
                (new Query\Scope(Query\Base::SELECT))->getQuerySimpleDepartmentMatch(),
                [$departmentId]
            );
        }
        if (count($result)) {
            foreach ($result as $entity) {
                if (0 == $resolveReferences) {
                    $entity = new Entity(
                        array(
                            'id' => $entity['id'],
                            'contact' => ['name' => $entity['contact__name']],
                            '$ref' => '/scope/' . $entity['id'] . '/'
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
        $availabilityList = (new Availability())->readOpeningHoursListByDate($scopeId, $now);
        if ($availabilityList->isOpened($now)) {
            $isOpened = true;
        }
        return $isOpened;
    }

    public function readIsEnabled($scopeId, $now)
    {
        $query = new Query\Scope(Query\Base::SELECT);
        $query->addEntityMapping()
            ->addConditionScopeId($scopeId);
        $scope = $this->fetchOne($query, new Entity());
        return (
            $this->readIsOpened($scopeId, $now) &&
            $this->readIsGivenNumberInContingent($scopeId) &&
            ! $scope->getStatus('ticketprinter', 'deactivated')
        );
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
    public function readIsGivenNumberInContingent($scopeId)
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
    public function readQueueList($scopeId, $dateTime, $resolveReferences = 0)
    {
        $queueList = (new Process())
            ->readProcessListByScopeAndTime($scopeId, $dateTime, $resolveReferences)
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
    public function readWithWorkstationCount($scopeId, $dateTime, $resolveReferences = 0)
    {
        //get scope
        $query = new Query\Scope(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addConditionScopeId($scopeId)
            ->addResolvedReferences($resolveReferences)
            ->addSelectWorkstationCount($dateTime);
        return $this->fetchOne($query, new Entity());
    }

    public function readQueueListWithWaitingTime($scope, $dateTime, $resolveReferences = 0)
    {
        $queueList = $this->readQueueList($scope->id, $dateTime, $resolveReferences);
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
     * update emergency
     *
     * @param
     *         scopeId
     *         entity
     *
     * @return Entity
     */
    public function updateEmergency($scopeId, \BO\Zmsentities\Scope $entity)
    {
        self::$cache = [];
        $query = new Query\Scope(Query\Base::UPDATE);
        $query->addConditionScopeId($scopeId);
        $values = $query->setEmergencyEntityMapping($entity);
        $query->addValues($values);
        $this->writeItem($query);
        return $this->readEntity($scopeId);
    }

    /**
     * update image data for call display image
     *
     * @param
     *         scopeId
     *         Mimepart entity
     *
     * @return Mimepart entity
     */
    public function writeImageData($scopeId, \BO\Zmsentities\Mimepart $entity)
    {
        $imageName = 's_'. $scopeId .'_bild.'. $entity->mime;
        $statement = $this->getWriter()->prepare((new Query\Scope(Query\Base::REPLACE))->getQueryWriteImageData());
        $statement->execute(array(
            'imagename' => $imageName,
            'imagedata' => $entity->content
        ));
        $entity->id = $scopeId;
        return $entity;
    }

    /**
     * read image data
     *
     * @param
     *         scopeId
     *
     * @return Mimepart entity
     */
    public function readImageData($scopeId)
    {
        $imageName = 's_'. $scopeId .'_bild';
        $imageData = new \BO\Zmsentities\Mimepart();
        $imageData->content = $this->getReader()->fetchValue(
            (new Query\Scope(Query\Base::SELECT))->getQueryReadImageData(),
            ['imagename' => "%$imageName%"]
        );
        return $imageData;
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
        $processListCount = (new Process())->readProcessListCountByScope($scopeId);
        if (0 < $processListCount) {
            throw new Exception\Scope\ScopeHasProcesses();
        }
        self::$cache = [];
        $entity = $this->readEntity($scopeId);
        $query = new Query\Scope(Query\Base::DELETE);
        $query->addConditionScopeId($scopeId);
        return ($this->deleteItem($query)) ? $entity : null;
    }
}
