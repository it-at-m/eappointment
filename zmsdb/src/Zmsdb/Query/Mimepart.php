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
     *
     * @var int
     */
    protected int $resolveLevel = 0;

    /**
     * @return string[]
     *
     * @psalm-return array{mime: 'mimepart.mime', content: 'mimepart.content', base64: 'mimepart.base64'}
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
