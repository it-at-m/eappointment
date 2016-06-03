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
            '. self::TABLE .' mq, '. MailPart::TABLE .' mp
        WHERE
            mq.id = mp.queueId AND mq.id=?
    ';

    const QUERY_DELETE_BY_PROCESS = '
        DELETE mq,  mp FROM
            '. self::TABLE .' mq, '. MailPart::TABLE .' mp
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
            'mailQueue.processID',
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
            'mailQueue.departmentID',
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
            'id' => 'mailQueue.id',
            'process__id' => 'mailQueue.processID',
            'process__authKey' => self::expression('(SELECT absagecode
                    FROM ' . Process::TABLE . ' as `MailProcess`
                    WHERE
                        `MailProcess`.`BuergerID` = `mailQueue`.`processID`
                )'),
            'department__id' => 'mailQueue.departmentID',
            'createIP' => 'mailQueue.createIP',
            'createTimestamp' => 'mailQueue.createTimestamp',
            'subject' => 'mailQueue.subject',
            'client__email' => 'mailQueue.clientEmail',
            'client__familyName' => 'mailQueue.clientFamilyName',

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
        $this->query->where('mailQueue.id', '=', $itemId);
        return $this;
    }
}
