<?php
/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

namespace BO\Zmsdb\Query;

use BO\Zmsentities\EventLog as EventLogEntity;

class EventLog extends Base
{
    public const TABLE = 'eventlog';
    public const ALIAS = 'eventLog';

    protected $resolveLevel = 0;

    public function __construct($queryType, $prefix = '', $name = false, $resolveLevel = null)
    {
        parent::__construct($queryType, $prefix, $name, $resolveLevel);

        if ($queryType === self::SELECT) {
            $this->query->orderBy(self::ALIAS . '.creationTimestamp', 'ASC');
        }
    }
    public function getEntityMapping(): array
    {
        return [
            'id'                  => self::ALIAS . '.eventId',
            'name'                => self::ALIAS . '.eventName',
            'origin'              => self::ALIAS . '.origin',
            'referenceType'       => self::ALIAS . '.referenceType',
            'reference'           => self::ALIAS . '.reference',
            'sessionid'           => self::ALIAS . '.sessionid',
            'context'             => self::ALIAS . '.contextjson',
            'creationTimestamp'   => self::ALIAS . '.creationTimestamp',
            'expirationTimestamp' => self::ALIAS . '.expirationTimestamp',
        ];
    }

    public function reverseEntityMapping(EventLogEntity $entity): array
    {
        $data = [
            'eventName' => $entity->name,
            'origin' => $entity->origin,
            'referenceType' => $entity->referenceType,
            'reference' => $entity->reference,
            'sessionid' => $entity->sessionid,
            'contextjson' => json_encode($entity->context, JSON_FORCE_OBJECT),
            'expirationTimestamp' => $entity->expirationTimestamp,
        ];

        return array_filter($data, function ($value) {
            return ($value !== null && $value !== false);
        });
    }

    /**
     * @param array $data
     * @return array
     */
    public function postProcess($data): array
    {
        $data['context'] = json_decode($data['context'], true);
        return $data;
    }

    public function addNameComparison(string $name): EventLog
    {
        $this->query->where(self::ALIAS . '.eventName', '=', $name);

        return $this;
    }

    public function addReferenceComparison(string $reference): EventLog
    {
        $this->query->where(self::ALIAS . '.reference', '=', $reference);

        return $this;
    }

    public function addExpirationCondition(): EventLog
    {
        $this->query->where(self::ALIAS . '.creationTimestamp', '<', (new \DateTime())->format('Y-m-d H:i:s'));

        return $this;
    }
}
