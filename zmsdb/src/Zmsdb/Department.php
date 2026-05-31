<?php

namespace BO\Zmsdb;

use BO\Zmsentities\Department as Entity;
use BO\Zmsentities\Collection\DepartmentList as Collection;

/**
 * @SuppressWarnings(Coupling)
 * @SuppressWarnings(Complexity)
 *
 */
class Department extends Base
{
    /**
     * @var array
     */
    public static array $departmentCache = array();

    /**
     * @param false|string $departmentId
     *
     * @psalm-param 0|1|2 $resolveReferences
     */
    public function readEntity(string|false $departmentId, int $resolveReferences = 0, bool $disableCache = false)
    {
        $cacheKey = "department-$departmentId-$resolveReferences";

        if (!$disableCache && \App::$cache && \App::$cache->has($cacheKey)) {
            $department = \App::$cache->get($cacheKey);
        }

        if (empty($department)) {
            $query = new Query\Department(Query\Base::SELECT);
            $query->addEntityMapping()
                ->addResolvedReferences($resolveReferences)
                ->addConditionDepartmentId($departmentId);
            $department = $this->fetchOne($query, new Entity());

            if (\App::$cache) {
                \App::$cache->set($cacheKey, $department);
                if (\App::$log) {
                    \App::$log->info('Department cache set', ['cache_key' => $cacheKey]);
                }
            }
        }

        if (isset($department['id']) && $department['id']) {
            $department = $this->readResolvedReferences($department, $resolveReferences, $disableCache);
            return $department->withOutClusterDuplicates();
        }

        return null;
    }

    /**
     * @return \BO\Zmsentities\Schema\Entity
     */
    public function readResolvedReferences(
        \BO\Zmsentities\Schema\Entity $entity,
        $resolveReferences,
        $disableCache = false
    ) {
        $entity['links'] = (new Link())->readByDepartmentId($entity->id, $disableCache);
        $entity['scopes'] = (new Scope())
            ->readByDepartmentId($entity->id, $resolveReferences - 1, $disableCache)
            ->sortByContactName();
        if (0 < $resolveReferences) {
            $entity['clusters'] = (new Cluster())->readByDepartmentId(
                $entity->id,
                $resolveReferences - 1,
                $disableCache
            );
            $entity['dayoff'] = (new DayOff())->readOnlyByDepartmentId($entity->id, $disableCache);
        }
        return $entity;
    }

    public function readList($resolveReferences = 0): Collection
    {
        $departmentList = new Collection();
        $query = new Query\Department(Query\Base::SELECT);
        $query->addEntityMapping();
        $query->addResolvedReferences($resolveReferences);
        $result = $this->fetchList($query, new Entity());
        if (count($result)) {
            foreach ($result as $department) {
                $department = $this->readResolvedReferences($department, $resolveReferences);
                if ($department instanceof Entity) {
                    $departmentList->addEntity($department->withOutClusterDuplicates());
                }
            }
        }
        return $departmentList;
    }

    /**
     * @return Entity[]
     *
     * @psalm-return array<Entity>
     */
    public function readEntitiesByIds(array $departmentIds, $resolveReferences = 0): array
    {
        if (empty($departmentIds)) {
            return [];
        }

        $query = new Query\Department(Query\Base::SELECT);
        $query->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionDepartmentIds($departmentIds);

        $departments = [];
        $result = $this->fetchList($query, new Entity());

        foreach ($result as $department) {
            if ($department instanceof Entity) {
                $department = $this->readResolvedReferences($department, $resolveReferences);
                if ($department instanceof Entity) {
                    $departments[$department->id] = $department->withOutClusterDuplicates();
                }
            }
        }

        return $departments;
    }

    /**
     * @psalm-param 0 $resolveReferences
     */
    public function readByScopeId($scopeId, int $resolveReferences = 0)
    {
        $query = new Query\Department(Query\Base::SELECT);
        $query->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionScopeId($scopeId);
        $department = $this->fetchOne($query, new Entity());
        $department = $this->readResolvedReferences($department, $resolveReferences);
        return (isset($department['id']) && $department['id']) ? $department->withOutClusterDuplicates() : null;
    }

    public function readByOrganisationId($organisationId, $resolveReferences = 0): Collection
    {
        $departmentList = new Collection();
        $query = new Query\Department(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionOrganisationId($organisationId);
        $result = $this->fetchList($query, new Entity());
        if (count($result)) {
            foreach ($result as $department) {
                if ($department instanceof Entity) {
                    $department = $this->readResolvedReferences($department, $resolveReferences);
                    $departmentList->addEntity($department->withOutClusterDuplicates());
                }
            }
        }
        return $departmentList;
    }

    public function deleteEntity($departmentId)
    {
        $entity = $this->readEntity($departmentId, 1);
        if ($entity) {
            if (
                0 < $entity->toProperty()->scopes->get()->count()
                || 0 < $entity->toProperty()->clusters->get()->count()
            ) {
                throw new Exception\Department\ScopeListNotEmpty();
            }

            self::$departmentCache = [];
            $query = new Query\Department(Query\Base::DELETE);
            $query->addConditionDepartmentId($departmentId);
            $entityDelete = $this->deleteItem($query);
            $emailDelete = $this->perform(Query\Department::QUERY_MAIL_DELETE, array(
                $departmentId
            ));
        }

        $this->removeCache($entity);

        return ($entity && $entityDelete && $emailDelete) ? $entity : null;
    }

    public function writeEntity(\BO\Zmsentities\Department $entity, $parentId)
    {
        self::$departmentCache = [];
        $query = new Query\Department(Query\Base::INSERT);
        $values = $query->reverseEntityMapping($entity, $parentId);
        // get owner by organisation
        $owner = (new Owner())->readByOrganisationId($parentId);
        $values['KundenID'] = $owner->id;
        $query->addValues($values);
        $this->writeItem($query);
        $lastInsertId = $this->getWriter()
            ->lastInsertId();
        if ($entity->toProperty()->links->isAvailable()) {
            $this->writeDepartmentLinks($lastInsertId, $entity->links);
        }
        if ($entity->toProperty()->dayoff->isAvailable()) {
            $this->writeDepartmentDayoffs($lastInsertId, $entity->dayoff);
        }
        if ($entity->toProperty()->email->isAvailable()) {
            $this->writeDepartmentMail(
                $lastInsertId,
                $entity->email,
                $entity->sendEmailReminderEnabled,
                $entity->sendEmailReminderMinutesBefore
            );
        }

        $this->removeCache($entity);

        return $this->readEntity($lastInsertId);
    }

    public function updateEntity($departmentId, \BO\Zmsentities\Department $entity)
    {
        self::$departmentCache = [];
        $query = new Query\Department(Query\Base::UPDATE);
        $query->addConditionDepartmentId($departmentId);
        $values = $query->reverseEntityMapping($entity);
        $query->addValues($values);
        $this->writeItem($query);
        if ($entity->toProperty()->links->isAvailable()) {
            $this->writeDepartmentLinks($departmentId, $entity->links);
        }
        if ($entity->toProperty()->dayoff->isAvailable()) {
            $this->writeDepartmentDayoffs($departmentId, $entity->dayoff);
        }
        if ($entity->toProperty()->email->isAvailable()) {
            $this->updateDepartmentMail(
                $departmentId,
                $entity->email,
                $entity->sendEmailReminderEnabled,
                $entity->sendEmailReminderMinutesBefore
            );
        }
        $this->removeCache($entity);
        return $this->readEntity($departmentId, 0, true);
    }

    /**
     * @param false|string $departmentId
     *
     * @return void
     */
    protected function writeDepartmentDayoffs(string|false $departmentId, $dayoffList)
    {
        if (!$departmentId) {
            throw new Exception\Department\InvalidId();
        }
        $existingDayoffs = (new DayOff())->readOnlyByDepartmentId($departmentId);
        if ($existingDayoffs->count()) {
            foreach ($existingDayoffs as $item) {
                $query = new DayOff();
                $query->deleteEntity($item->getId());
            }
        }

        foreach ($dayoffList as $dayoff) {
            $query = new Query\DayOff(Query\Base::INSERT);
            $query->addValues(
                [
                    'behoerdenid' => $departmentId,
                    'Feiertag' => $dayoff['name'],
                    'Datum' => (new \DateTimeImmutable('@' . $dayoff['date']))->format('Y-m-d')
                ]
            );
            $this->writeItem($query);
        }
    }

    /**
     * @param false|string $departmentId
     *
     * @return void
     */
    protected function writeDepartmentLinks(string|false $departmentId, $links)
    {
        if (!$departmentId) {
            throw new Exception\Department\InvalidId();
        }
        $existingLinks = (new Link())->readByDepartmentId($departmentId);
        if ($existingLinks->count()) {
            foreach ($existingLinks as $item) {
                $query = new Link();
                $query->deleteEntity($item->getId());
            }
        }

        foreach ($links as $link) {
            $link = new \BO\Zmsentities\Link($link);
            $query = new Link();
            $query->writeEntity($link, $departmentId);
        }
    }

    /**
     * @param false|string $departmentId
     */
    protected function writeDepartmentMail(
        string|false $departmentId,
        $email,
        $sendEmailReminderEnabled,
        $sendEmailReminderMinutesBefore
    ) {
        self::$departmentCache = [];
        $result = $this->perform(Query\Department::QUERY_MAIL_INSERT, array(
            $departmentId,
            $email,
            $sendEmailReminderEnabled,
            $sendEmailReminderMinutesBefore
        ));
        return $result;
    }

    protected function updateDepartmentMail(
        $departmentId,
        $email,
        $sendEmailReminderEnabled,
        $sendEmailReminderMinutesBefore
    ) {
        self::$departmentCache = [];
        $query = Query\Department::QUERY_MAIL_UPDATE;
        return $this->fetchAffected($query, array(
            'email' => $email,
            'departmentId' => $departmentId,
            'sendEmailReminderEnabled' => $sendEmailReminderEnabled,
            'sendEmailReminderMinutesBefore' => $sendEmailReminderMinutesBefore
        ));
    }

    public function readQueueList(
        $departmentId,
        \DateTimeInterface $dateTime,
        $resolveReferences = 0
    ): \BO\Zmsentities\Collection\QueueList {
        $queueList = new \BO\Zmsentities\Collection\QueueList();
        $department = $this->readEntity($departmentId, 2);

        foreach ($department->getScopeList() as $scope) {
            $scope = (new Scope())->readWithWorkstationCount($scope->id, $dateTime);
            $scopeQueueList = (new Scope())
                ->readQueueListWithWaitingTime($scope, $dateTime, $resolveReferences);
            if (0 < $scopeQueueList->count()) {
                $queueList->addList($scopeQueueList);
            }
        }
        return $queueList;
    }

    /**
     * @return void
     */
    public function removeCache(Entity $department)
    {
        if (!\App::$cache || !isset($department->id)) {
            return;
        }

        $invalidatedKeys = [];

        // Invalidate department entity cache for all resolveReferences levels (0, 1, 2)
        for ($resolveReferences = 0; $resolveReferences <= 2; $resolveReferences++) {
            $key = "department-{$department->id}-{$resolveReferences}";
            if (\App::$cache->has($key)) {
                \App::$cache->delete($key);
                $invalidatedKeys[] = $key;
            }
        }

        // Invalidate scopeReadByDepartmentId cache for all resolveReferences levels (0, 1, 2)
        for ($resolveReferences = 0; $resolveReferences <= 2; $resolveReferences++) {
            $key = "scopeReadByDepartmentId-{$department->id}-{$resolveReferences}";
            if (\App::$cache->has($key)) {
                \App::$cache->delete($key);
                $invalidatedKeys[] = $key;
            }
        }

        if (!empty($invalidatedKeys) && \App::$log) {
            \App::$log->info('Department cache invalidated', [
                'department_id' => $department->id,
                'invalidated_keys' => $invalidatedKeys
            ]);
        }
    }
}
