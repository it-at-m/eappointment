<?php

namespace BO\Zmsdb\Query;

class Log extends Base
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'log';

    /**
     * No resolving required here
     */
    protected $resolveLevel = 0;

    const QUERY_DELETE_BY_PROCESS = '
        DELETE mq,  mp
        FROM '. self::TABLE .' mq
        LEFT JOIN '. Mimepart::TABLE .' mp ON mp.queueId = mq.id
        WHERE mq.processID=?
    ';

    public function getEntityMapping()
    {
        return [
            'type' => 'log.type',
            'reference' => 'log.reference_id',
            'scope_id' => 'log.scope_id',
            'message' => 'log.message',
            'ts' => 'log.ts'
        ];
    }

    public function addConditionProcessId($processId)
    {
        $this->query->where('log.reference_id', '=', $processId);
        $this->query->where('log.type', '=', 'buerger');
        $this->query->orderBy('log.ts', 'DESC');
        return $this;
    }

    public function postProcess($data)
    {
        $data[$this->getPrefixed('ts')] = strtotime($data[$this->getPrefixed('ts')]);
        return $data;
    }
}
