<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Department as Entity;
use \BO\Zmsentities\Collection\DepartmentList as Collection;

class Department extends Base
{
    /**
     * @var String TABLE mysql table reference
     */
    public static $departmentCache = array();

    public function readEntity($departmentId, $resolveReferences = 0)
    {
        if (array_key_exists($departmentId, self::$departmentCache)) {
            return $departmentCache[$departmentId];
        }
        $query = new Query\Department(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionDepartmentId($departmentId);
        $department = $this->fetchOne($query, new Entity());
        if (isset($department['id'])) {
            $department['scopes'] = (new Scope())->readByDepartmentId($departmentId, $resolveReferences);
            $department['dayoff']  = (new DayOff())->readByDepartmentId($departmentId);
            $departmentCache[$departmentId] = $department;
            return $department;
        }
        return array();
    }

    public function readList($resolveReferences = 0)
    {
        $departmentList = new Collection();
        $query = new Query\Department(Query\Base::SELECT);
        $query->addEntityMapping();
        $result = $this->fetchList($query, new Entity());
        if (count($result)) {
            foreach ($result as $department) {
                $department = $this->readEntity($department['id'], $resolveReferences - 1);
                $departmentList->addDepartment($department);
            }
        }
        return $departmentList;
    }

    public function readByOrganisationId($organisationId, $resolveReferences = 0)
    {
        $departmentList = new Collection();
        $query = new Query\Department(Query\Base::SELECT);
        $query
            ->addEntityMapping()
            ->addConditionOrganisationId($organisationId);
        $result = $this->fetchList($query, new Entity());
        if (count($result)) {
            foreach ($result as $entity) {
                $department = $this->readEntity($entity['id'], $resolveReferences - 1);
                $departmentList->addDepartment($department);
            }
        }
        return $departmentList;
    }

    /**
     * remove a department
     *
     * @param
     * departmentId
     *
     * @return Resource Status
     */
    public function deleteEntity($departmentId)
    {
        $query =  new Query\Department(Query\Base::DELETE);
        $query->addConditionDepartmentId($departmentId);
        $entityDelete = $this->deleteItem($query);

        $query = Query\Department::QUERY_MAIL_DELETE;
        $statement = $this->getWriter()->prepare($query);
        $emailDelete = $statement->execute(
            array(
                $departmentId,
            )
        );
        $query = Query\Department::QUERY_NOTIFICATIONS_DELETE;
        $statement = $this->getWriter()->prepare($query);
        $notificationsDelete = $statement->execute(
            array(
                $departmentId,
            )
        );
        if ($entityDelete && $emailDelete && $notificationsDelete) {
            return true;
        }
        return false;
    }

    /**
     * write a department
     *
     * @param
     * departmentId
     *
     * @return Entity
     */
    public function writeEntity(\BO\Zmsentities\Department $entity)
    {
        $query = new Query\Department(Query\Base::INSERT);
        $values = $query->reverseEntityMapping($entity);
        $query->addValues($values);
        $this->writeItem($query);
        $lastInsertId = $this->getWriter()->lastInsertId();

        $this->writeDepartmentMail($lastInsertId, $entity->email);
        $this->writeDepartmentNotifications(
            $lastInsertId,
            $entity->getNotificationPreferences()
        );
        return $this->readEntity($lastInsertId);
    }

    /**
     * update a department
     *
     * @param
     * departmentId
     *
     * @return Entity
     */
    public function updateEntity($departmentId, \BO\Zmsentities\Department $entity)
    {
        $query = new Query\Department(Query\Base::UPDATE);
        $query->addConditionDepartmentId($departmentId);
        $values = $query->reverseEntityMapping($entity);
        $query->addValues($values);
        $this->writeItem($query, 'department', $query::TABLE);
        if (false === $this->updateDepartmentMail($departmentId, $entity->email)) {
            $this->writeDepartmentMail($departmentId, $entity->email);
        }
        if (false === $this->updateDepartmentNotifications(
            $departmentId,
            $entity->getNotificationPreferences()
        )) {
            $this->writeDepartmentNotifications(
                $departmentId,
                $entity->getNotificationPreferences()
            );
        }
        return $this->readEntity($departmentId);
    }

    /**
     * create mail preferences of a department
     *
     * @param
     * departmentId,
     * email
     *
     * @return Boolean
     */
    protected function writeDepartmentMail($departmentId, $email)
    {
        $query = Query\Department::QUERY_MAIL_INSERT;
        $statement = $this->getWriter()->prepare($query);
        $result = $statement->execute(
            array(
                $departmentId,
                $email
            )
        );
        return $result;
    }

    /**
     * create notification preferences of a department
     *
     * @param
     * departmentId,
     * preferences
     *
     * @return Boolean
     */
    protected function writeDepartmentNotifications($departmentId, $preferences)
    {
        $query = Query\Department::QUERY_NOTIFICATIONS_INSERT;
        $statement = $this->getWriter()->prepare($query);
        $result = $statement->execute(
            array(
                $departmentId,
                ($preferences['enabled']) ? 1 : 0,
                $preferences['identification'],
                ($preferences['sendConfirmationEnabled']) ? 1 : 0,
                ($preferences['sendReminderEnabled']) ? 1 : 0
            )
        );
        return $result;
    }

    /**
     * update mail preferences of a department
     *
     * @param
     * departmentId,
     * email
     *
     * @return Boolean
     */
    protected function updateDepartmentMail($departmentId, $email)
    {
        $query = Query\Department::QUERY_MAIL_UPDATE;
        $statement = $this->getWriter()->prepare($query);
        $result = $statement->execute(
            array(
                $email,
                $departmentId
            )
        );
        return $result;
    }

    /**
     * update notification preferences of a department
     *
     * @param
     * departmentId,
     * preferences
     *
     * @return Boolean
     */
    protected function updateDepartmentNotifications($departmentId, $preferences)
    {
        $query = Query\Department::QUERY_NOTIFICATIONS_UPDATE;
        $statement = $this->getWriter()->prepare($query);
        $result = $statement->execute(
            array(
                (isset($preferences['enabled']) && $preferences['enabled']) ? 1 : 0,
                $preferences['identification'],
                (isset($preferences['sendConfirmationEnabled']) && $preferences['sendConfirmationEnabled']) ? 1 : 0,
                (isset($preferences['sendReminderEnabled']) && $preferences['sendReminderEnabled']) ? 1 : 0,
                $departmentId
            )
        );
        return $result;
    }
}
