<?php

namespace BO\Zmsdb\Query;

class Mail extends Base
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
        $this->query->leftJoin(
            'mailpart.id',
            '=',
            self::TABLE .'multipartID'
        );
        $joinQuery = new Scope($this->query);
        $joinQuery->addEntityMappingPrefixed($this->getPrefixed('scope__'));
        return $joinQuery;
    }

    public function getEntityMapping()
    {
        return [
            'id' => 'mail.id',
            'processID' => 'mail.process.id',
            'deparmentID' => 'mail.department.id',
            'multipartID' => 'mail.multipart.id',
            'createIP' => 'mail.createIP',
            'createTimestamp' => 'mail.createTimestamp',
            'subject' => 'mail.subject'
        ];
    }
    
    public function addRequiredJoins()
    {
        $this->query->leftJoin(
            new Alias('mailpart', 'mail.multipart'),
            'multipart.id',
            '=',
            'mail.multipartID'
        );
    }

    public function addConditionItemId($itemId)
    {
        $this->query->where('mail.id', '=', $itemId);
        return $this;
    }

    public function addConditionProcessId($processId)
    {
        $this->query->where('mail.process.id', '=', $processId);
        return $this;
    }
}
