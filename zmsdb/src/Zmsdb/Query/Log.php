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
        FROM ' . self::TABLE . ' mq
        LEFT JOIN ' . Mimepart::TABLE . ' mp ON mp.queueId = mq.id
        WHERE mq.processID=?
    ';

    public function getEntityMapping()
    {
        return [
            'type' => 'log.type',
            'reference' => 'log.reference_id',
            'scope_id' => 'log.scope_id',
            'user_id' => 'log.user_id',
            'data' => 'log.data',
            'message' => 'log.message',
            'ts' => 'log.ts',
            'action' => 'log.action',
            'display_number' => 'log.display_number',
            'queue_number' => 'log.queue_number',
            'appointment_at' => 'log.appointment_at',
            'slot_count' => 'log.slot_count',
            'client_name' => 'log.client_name',
            'services' => 'log.services',
            'scope_name' => 'log.scope_name',
            'client_email' => 'log.client_email',
            'client_phone' => 'log.client_phone',
            'process_status' => 'log.process_status',
            'db_status' => 'log.db_status',
        ];
    }

    public function addConditionProcessId($processId)
    {
        $this->query->where('log.reference_id', '=', $processId);
        $this->query->where('log.type', '=', 'buerger');
        $this->query->orderBy('log.ts', 'DESC');
        return $this;
    }

    #[\Override]
    public function postProcess($data)
    {
        $data[$this->getPrefixed('ts')] = strtotime($data[$this->getPrefixed('ts')]);
        return $data;
    }

    public function addConditionOlderThan(\DateTime $olderThanDate)
    {
        $this->query->where('log.ts', '<', $olderThanDate->format('Y-m-d H:i:s'));
    }

    public function addConditionDataSearch(string $search)
    {
        $this->query->where('log.data', 'LIKE', '%' . $search . '%');

        if (is_numeric($search)) {
            $this->query->orWhere('log.reference_id', '=', $search);
        }

        $this->query->orderBy('log.ts', 'DESC');
    }
}
