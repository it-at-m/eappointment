<?php

namespace BO\Zmsbackend\Mail\Repository;

class MailQueue extends \BO\Zmsbackend\Query\Base
{
    /**
     * @var String TABLE mysql table reference
     */
    const string TABLE = 'mailqueue';

    const QUERY_DELETE = '
        DELETE mq,  mp
            FROM ' . self::TABLE . ' mq
            LEFT JOIN ' . \BO\Zmsbackend\Mail\Repository\Mimepart::TABLE . ' mp ON mp.queueId = mq.id
            WHERE mq.id=?
    ';

    const QUERY_MULTI_DELETE = '
        DELETE mq, mp
        FROM ' . self::TABLE . ' mq
        LEFT JOIN ' . \BO\Zmsbackend\Mail\Repository\Mimepart::TABLE . ' mp ON mp.queueId = mq.id
        WHERE mq.id IN (?)
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
            'id' => 'mailQueue.id',
            'createIP' => 'mailQueue.createIP',
            'createTimestamp' => 'mailQueue.createTimestamp',
            'subject' => 'mailQueue.subject',
            'client__email' => 'mailQueue.clientEmail',
            'client__familyName' => 'mailQueue.clientFamilyName',
            'process__id' => 'mailQueue.processID',
            'department__id' => 'mailQueue.departmentID'
        ];
    }

    public function addConditionItemId($itemId): static
    {
        $this->query->where('mailQueue.id', '=', $itemId);
        return $this;
    }

    public function addOrderBy(string $parameter, $order = 'ASC'): static
    {
        $this->query->orderBy('mailQueue.' . $parameter, $order);
        return $this;
    }

    public function addWhereIn(string $column, array $itemIds): static
    {
        if (!empty($itemIds)) {
            $this->query->where($column, 'IN', $itemIds);
        }
        return $this;
    }

    public function selectFields(array $fields): static
    {
        $this->query->select($fields);
        return $this;
    }
}
