<?php

namespace BO\Zmsdb\Query;

class Mimepart extends Base
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'mailpart';

    /**
     * No resolving required here
     */
    protected $resolveLevel = 0;

    public function getEntityMapping()
    {
        return [
            'mime' => 'mimepart.mime',
            'content' => 'mimepart.content',
            'base64' => 'mimepart.base64',
        ];
    }

    public function addConditionQueueId($queueId)
    {
        $this->query->where('mimepart.queueId', '=', $queueId);
        return $this;
    }
}
