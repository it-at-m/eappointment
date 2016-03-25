<?php

namespace BO\Zmsdb\Query;

class MailQueue extends Base
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'mailqueue';

    const QUERY_DELETE = '
        DELETE FROM
            '. self::TABLE .'
        WHERE
            id=? AND
            processID=?
    ';
    
    public function addJoin()
    {
        return [
            $this->addJoinMailPart(),
            $this->addJoinProcess(),
            $this->addJoinDepartment(),
        ];
    }
    
    protected function addJoinMailPart()
    {
        $this->query->leftJoin(
            new Alias(MailPart::TABLE, 'mailpart'),
            'mailpart.id',
            '=',
            self::TABLE .'.multipartID'
        );
        $joinQuery = new MailPart($this->query);
        return $joinQuery;
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
            'process__id' => 'process.BuergerID',
            'department__id' => 'department.BehoerdenID',
            'multipart__0__id' => 'mailqueue.multipartID',
            'multipart__0__mime' => 'mailpart.mime',
            'multipart__0__content' => 'mailpart.content',
            'multipart__0__base64' => 'mailpart.base64',
            'createIP' => 'mailqueue.createIP',
            'createTimestamp' => 'mailqueue.createTimestamp',
            'subject' => 'mailqueue.subject',
            
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
        $this->query->where('mailqueue.id', '=', $itemId);
        return $this;
    }

    public function addConditionProcessId($processId)
    {
        $this->query->where('mailqueue.processID', '=', $processId);
        return $this;
    }
}
