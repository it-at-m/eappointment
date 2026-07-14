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
            'message' => 'log.message',
            'ts' => 'log.ts',
            'action' => 'log.action',
            'display_number' => 'log.display_number',
            'queue_number' => 'log.queue_number',
            'appointment_at' => 'log.appointment_at',
            'slot_count' => 'log.slot_count',
            'citizen_name' => 'log.citizen_name',
            'services' => 'log.services',
            'scope_name' => 'log.scope_name',
            'citizen_email' => 'log.citizen_email',
            'citizen_phone' => 'log.citizen_phone',
            'process_status' => 'log.process_status',
            'db_status' => 'log.db_status',
            'process_amendment' => 'log.process_amendment',
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
}
