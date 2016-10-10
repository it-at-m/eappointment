<?php

namespace BO\Zmsdb\Query;

class Notification extends Base
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'notificationqueue';

    const QUERY_DELETE_BY_PROCESS = '
        DELETE nq FROM
            '. self::TABLE .' nq
        WHERE
            nq.processID=?
    ';

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
        $processQuery = new Process($this->query, 'process__');
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
        $departmentQuery = new Department($this->query, 'department__');
        return $departmentQuery;
    }

    public function getEntityMapping()
    {
        return [
            'id' => 'notification.id',
            'createIP' => 'notification.createIP',
            'createTimestamp' => 'notification.createTimestamp',
            'message' => 'notification.message',
            'client__telephone' => 'notification.clientTelephone',
            'client__familyName' => 'notification.clientFamilyName',

        ];
    }

    public function addConditionItemId($itemId)
    {
        $this->query->where('notification.id', '=', $itemId);
        return $this;
    }
}
