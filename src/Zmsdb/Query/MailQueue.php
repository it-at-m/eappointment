<?php

namespace BO\Zmsdb\Query;

class MailQueue extends Base
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'mailqueue';

    const QUERY_DELETE = '
        DELETE mq,  mp FROM
            '. self::TABLE .' mq, '. Mailpart::TABLE .' mp
        WHERE
            mq.id = mp.queueId AND mq.id=?
    ';

    const QUERY_DELETE_BY_PROCESS = '
        DELETE mq,  mp FROM
            '. self::TABLE .' mq, '. Mailpart::TABLE .' mp
        WHERE
            mq.id = mp.queueId AND mq.processID=?
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
            'mailqueue.processID',
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
            'mailqueue.departmentID',
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
            'id' => 'mailqueue.id',
            'process__id' => 'mailqueue.processID',
            'process__authKey' => self::expression('(SELECT absagecode
                    FROM ' . Process::TABLE . ' as `MailProcess`
                    WHERE
                        `MailProcess`.`BuergerID` = `mailqueue`.`processID`
                )'),
            'department__id' => 'mailqueue.departmentID',
            'createIP' => 'mailqueue.createIP',
            'createTimestamp' => 'mailqueue.createTimestamp',
            'subject' => 'mailqueue.subject',
            'client__email' => 'mailqueue.clientEmail',
            'client__familyName' => 'mailqueue.clientFamilyName',

        ];
    }

    public function getReferenceMapping()
    {
        return [
            'department__$ref' => self::expression('CONCAT("/department/", `department`.`BehoerdenID`, "/")'),
            'process__$ref' => self::expression('CONCAT(
                "/process/", `process`.`BuergerID`, "/", `process`.`absagecode`, "/"
            )'),
        ];
    }

    public function addConditionItemId($itemId)
    {
        $this->query->where('mailqueue.id', '=', $itemId);
        return $this;
    }
}
