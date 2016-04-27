<?php

namespace BO\Zmsdb\Query;

class Notification extends Base
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
            'notification.processID',
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
            'notification.departmentID',
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
            'id' => 'notification.id',
            'process__id' => 'notification.processID',
            'process__authKey' => self::expression('(SELECT absagecode
                    FROM ' . Process::TABLE . ' as `NotificationProcess`
                    WHERE
                        `NotificationProcess`.`BuergerID` = `notification`.`processID`
                )'),
            'department__id' => 'notification.departmentID',
            'createIP' => 'notification.createIP',
            'createTimestamp' => 'notification.createTimestamp',
            'message' => 'notification.message',

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
        $this->query->where('notification.id', '=', $itemId);
        return $this;
    }
}
