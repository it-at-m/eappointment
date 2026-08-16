<?php

namespace BO\Zmsbackend\Session\Repository;

class Session extends \BO\Zmsbackend\Query\Base
{
    /**
     * @var String TABLE mysql table reference
     */
    const string TABLE = 'sessiondata';

    /**
     * No resolving required here
     */
    protected $resolveLevel = 0;

    const string QUERY_WRITE = '
        REPLACE INTO
            sessiondata
        SET
            sessionid=?,
            sessionname=?,
            sessioncontent=?
    ';

    const string QUERY_DELETE = '
        DELETE FROM
            sessiondata
        WHERE
            sessionid=? AND
            sessionname=?
    ';

    /**
     * @psalm-api
     *
     * @return string[]
     *
     */
    public function getEntityMapping(): array
    {
        return [
            'id' => 'session.sessionid',
            'name' => 'session.sessionname',
            'content' => 'session.sessioncontent'
        ];
    }

    public function addConditionSessionId($sessionId): static
    {
        $this->query->where('session.sessionid', '=', $sessionId);
        return $this;
    }

    public function addConditionSessionName(string $sessionName): static
    {
        $this->query->where('session.sessionname', '=', $sessionName);
        return $this;
    }

    public function addConditionSessionDeleteInterval(int $deleteInSeconds): static
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
    #[\Override]
    public function postProcess($data)
    {
        $data[$this->getPrefixed('content')] = json_decode($data[$this->getPrefixed('content')], 1);
        return $data;
    }
}
