<?php

namespace BO\Zmsdb\Query;

class Session extends Base
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'sessiondata';

    /**
     * No resolving required here
     */
    protected $resolveLevel = 0;

    const QUERY_WRITE = '
        REPLACE INTO
            sessiondata
        SET
            sessionid=?,
            sessionname=?,
            sessioncontent=?
    ';

    const QUERY_DELETE = '
        DELETE FROM
            sessiondata
        WHERE
            sessionid=? AND
            sessionname=?
    ';

    public function getEntityMapping()
    {
        return [
            'id' => 'session.sessionid',
            'name' => 'session.sessionname',
            'content' => 'session.sessioncontent'
        ];
    }

    public function addConditionSessionId($sessionId)
    {
        $this->query->where('session.sessionid', '=', $sessionId);
        return $this;
    }

    public function addConditionSessionName($sessionName)
    {
        $this->query->where('session.sessionname', '=', $sessionName);
        return $this;
    }

    public function addConditionSessionDeleteInterval($deleteInSeconds)
    {
        $this->query->where(
            self::expression(
                'UNIX_TIMESTAMP(NOW()) - UNIX_TIMESTAMP(`session`.`ts`)'
            ),
            '>=',
            $deleteInSeconds
        );
        return $this;
    }

    /**
     * postProcess data if necessary
     *
     */
    public function postProcess($data)
    {
        $data[$this->getPrefixed('content')] = json_decode($data[$this->getPrefixed('content')], 1);
        return $data;
    }
}
