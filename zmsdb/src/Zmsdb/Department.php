<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Department as Entity;
use \BO\Zmsentities\Collection\DepartmentList as Collection;

/**
 * @SuppressWarnings(Coupling)
 * @SuppressWarnings(Complexity)
 *
 */
class Department extends Base
{

    /**
     *
     * @var Array \BO\Zmsentities\Department
     */
    public static $departmentCache = array();

    public function readEntity($departmentId, $resolveReferences = 0, $disableCache = false)
    {
        $cacheKey = "$departmentId-$resolveReferences";
        if (! $disableCache && array_key_exists($cacheKey, self::$departmentCache)) {
            return clone self::$departmentCache[$cacheKey];
        }
        $query = new Query\Department(Query\Base::SELECT);
        $query->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionDepartmentId($departmentId);
        $department = $this->fetchOne($query, new Entity());
        if (isset($department['id']) && $department['id']) {
            $department = $this->readResolvedReferences($department, $resolveReferences);
            $department = $department->withOutClusterDuplicates();
            self::$departmentCache[$cacheKey] = $department;
            return clone self::$departmentCache[$cacheKey];
        }
        return null;
    }

    public function readResolvedReferences(\BO\Zmsentities\Schema\Entity $entity, $resolveReferences)
    {
        $entity['links'] = (new Link())->readByDepartmentId($entity->id);
        $entity['scopes'] = (new Scope())
            ->readByDepartmentId($entity->id, $resolveReferences - 1)
            ->sortByContactName();
        if (0 < $resolveReferences) {
            $entity['clusters'] = (new Cluster())->readByDepartmentId($entity->id, $resolveReferences - 1);
            $entity['dayoff'] = (new DayOff())->readOnlyByDepartmentId($entity->id);
        }
        return $entity;
    }

    public function readList($resolveReferences = 0)
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

    public function readByScopeId($scopeId, $resolveReferences = 0)
    {
        $query = new Query\Department(Query\Base::SELECT);
        $query->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionScopeId($scopeId);
        $department = $this->fetchOne($query, new Entity());
        $department = $this->readResolvedReferences($department, $resolveReferences);
        return (isset($department['id']) && $department['id']) ? $department->withOutClusterDuplicates() : null;
    }

    public function readByOrganisationId($organisationId, $resolveReferences = 0)
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

    /**
     * remove a department
     *
     * @param
     *            departmentId
     *
     * @return Resource Status
     */
    public function deleteEntity($departmentId)
    {
        $entity = $this->readEntity($departmentId, 1);
        if ($entity) {
            if (0 < $entity->toProperty()->scopes->get()->count()
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
            $notificationsDelete = $this->perform(Query\Department::QUERY_NOTIFICATIONS_DELETE, array(
                $departmentId
            ));
        }
        return ($entity && $entityDelete && $emailDelete && $notificationsDelete) ? $entity : null;
    }

    /**
     * write a department
     *
     * @param Department $entity
     *
     * @return Entity
     */
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
        if ($entity->getNotificationPreferences()) {
            $this->writeDepartmentNotifications($lastInsertId, $entity->getNotificationPreferences());
        }
        return $this->readEntity($lastInsertId);
    }

    /**
     * update a department
     *
     * @param
     *            departmentId
     *
     * @return Entity
     */
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
        $this->updateDepartmentNotifications($departmentId, $entity->getNotificationPreferences());
        return $this->readEntity($departmentId);
    }

    /**
     * create dayoff preferences of a department
     *
     * @param
     *            departmentId,
     *            dayoffs
     *
     * @return Boolean
     */
    protected function writeDepartmentDayoffs($departmentId, $dayoffList)
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
                    'Datum' => (new \DateTimeImmutable('@'. $dayoff['date']))->format('Y-m-d')
                ]
            );
            $this->writeItem($query);
        }
    }

    /**
     * create links preferences of a department
     *
     * @param
     *            departmentId,
     *            links
     *
     * @return Boolean
     */
    protected function writeDepartmentLinks($departmentId, $links)
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
     * create mail preferences of a department
     *
     * @param
     *            departmentId,
     *            email
     *
     * @return Boolean
     */
    protected function writeDepartmentMail(
        $departmentId,
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

    /**
     * create notification preferences of a department
     *
     * @param
     *            departmentId,
     *            preferences
     *
     * @return Boolean
     */
    protected function writeDepartmentNotifications($departmentId, $preferences)
    {
        self::$departmentCache = [];

        $result = $this->perform(
            Query\Department::QUERY_NOTIFICATIONS_INSERT,
            array(
                $departmentId,
                (isset($preferences['enabled']) && $preferences['enabled']) ? 1 : 0,
                $preferences['identification'],
                (isset($preferences['sendConfirmationEnabled']) && $preferences['sendConfirmationEnabled']) ? 1 : 0,
                (isset($preferences['sendReminderEnabled']) && $preferences['sendReminderEnabled']) ? 1 : 0
            )
        );
        return $result;
    }

    /**
     * update mail preferences of a department
     *
     * @param
     *            departmentId,
     *            email
     *
     * @return Boolean
     */
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

    /**
     * update notification preferences of a department
     *
     * @param
     *            departmentId,
     *            preferences
     *
     * @return Boolean
     */
    protected function updateDepartmentNotifications($departmentId, $preferences)
    {
        self::$departmentCache = [];
        $query = Query\Department::QUERY_NOTIFICATIONS_UPDATE;
        return $this->fetchAffected(
            $query,
            array(
                'enabled' =>
                    (isset($preferences['enabled'])) ? $preferences['enabled'] : 0,
                'identification' =>
                    (isset($preferences['identification'])) ? $preferences['identification'] : 0,
                'sendConfirmationEnabled' =>
                    (isset($preferences['sendConfirmationEnabled'])) ? $preferences['sendConfirmationEnabled'] : 0,
                'sendReminderEnabled' =>
                    (isset($preferences['sendReminderEnabled'])) ? $preferences['sendReminderEnabled'] : 0,
                'departmentId' => $departmentId
            )
        );
    }
}
