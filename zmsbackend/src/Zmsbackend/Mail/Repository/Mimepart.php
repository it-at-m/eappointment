<?php

namespace BO\Zmsbackend\Mail\Repository;

class Mimepart extends \BO\Zmsbackend\Query\Base
{
    /**
     * @var String TABLE mysql table reference
     */
    const string TABLE = 'mailpart';

    /**
     * No resolving required here
     */
    protected $resolveLevel = 0;

    /**
     * @psalm-api
     *
     * @return string[]
     *
     */
    public function getEntityMapping(): array
    {
        return [
            'mime' => 'mimepart.mime',
            'content' => 'mimepart.content',
            'base64' => 'mimepart.base64',
        ];
    }

    public function addConditionQueueId($queueId): static
    {
        $this->query->where('mimepart.queueId', '=', $queueId);
        return $this;
    }
}
