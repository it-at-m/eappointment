<?php

namespace BO\Zmsdb\Query;

class NotificationQueue extends Base
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'notificationqueue';

    public function addJoin()
    {
        return [
            $this->addJoinProcess(),
            $this->addJoinDepartment(),
        ];
    }

    protected function addJoinProcess()
    {
        $this->query->leftJoin(
            new Alias(Process::TABLE, 'process'),
            'notificationqueue.processID',
            '=',
            'process.BuergerID'
        );
        $processQuery = new Process($this->query);
        $processQuery->addEntityMappingPrefixed($this->getPrefixed('process__'));
        return $processQuery;
    }

    protected function addJoinDepartment()
    {
        $this->query->leftJoin(
            new Alias(Department::TABLE, 'department'),
            'notificationqueue.departmentID',
            '=',
            'department.BehoerdenID'
        );
        $departmentQuery = new Department($this->query);
        $departmentQuery->addEntityMappingPrefixed($this->getPrefixed('department__'));
        return $departmentQuery;
    }

    public function getEntityMapping()
    {
        return [
            'id' => 'notificationqueue.id',
            'process__id' => 'notificationqueue.processID',
            'department__id' => 'notificationqueue.departmentID',
            'createIP' => 'notificationqueue.createIP',
            'createTimestamp' => 'notificationqueue.createTimestamp',
            'subject' => 'notificationqueue.subject',

        ];
    }

    public function getReferenceMapping()
    {
        return [
            'department__$ref' => self::expression('CONCAT("/department/", `scope`.`BehoerdenID`, "/")'),
            'process__$ref' => self::expression('CONCAT("/process/", `process`.`BuergerID`, "/")'),
        ];
    }

    public function addConditionItemId($itemId)
    {
        $this->query->where('notificationqueue.id', '=', $itemId);
        return $this;
    }
}
