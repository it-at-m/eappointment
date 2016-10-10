<?php

namespace BO\Zmsdb\Query;

class MailPart extends Base
{
    /**
     * @var String TABLE mysql table reference
     */
    const TABLE = 'mailpart';

    public function getEntityMapping()
    {
        return [
            'mime' => 'mailPart.mime',
            'content' => 'mailPart.content',
            'base64' => 'mailPart.base64',
        ];
    }

    public function addConditionQueueId($queueId)
    {
        $this->query->where('mailPart.queueId', '=', $queueId);
        return $this;
    }
}
