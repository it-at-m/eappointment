<?php

declare(strict_types=1);

namespace BO\Zmsbackend\Availability\Repository;

/**
 * Append-only opening-hours change history (ZMSKVR-1249).
 */
class AvailabilityHistory extends \BO\Zmsbackend\Query\Base
{
    public const TABLE = 'availability_history';

    public const ALIAS = 'availabilityHistory';

    public const QUERY_SELECT_COLUMNS = '
        SELECT
            id,
            scope_id AS scopeId,
            availability_id AS availabilityId,
            action,
            weekdays,
            series,
            valid_from AS validFrom,
            valid_to AS validTo,
            time_range AS timeRange,
            type,
            slot_time AS slotTime,
            workstations,
            bookable,
            description,
            changed_at AS changedAt,
            changed_by AS changedBy
        FROM availability_history
    ';

    public const QUERY_DELETE_OLDER_THAN = '
        DELETE FROM availability_history
        WHERE changed_at < :cutoff
    ';

    /** @var list<string> */
    public const ALLOWED_ACTIONS = [
        'created',
        'updated',
        'deleted',
        'dldb_slot_update',
    ];

    protected $resolveLevel = 0;

    /**
     * @param array{
     *     scope_id:int,
     *     availability_id:?int,
     *     action:string,
     *     weekdays:string,
     *     series:string,
     *     valid_from:string,
     *     valid_to:string,
     *     time_range:string,
     *     type:string,
     *     slot_time:string,
     *     workstations:string,
     *     bookable:string,
     *     description:string,
     *     changed_by:string
     * } $row
     */
    public function reverseEntityMapping(array $row): array
    {
        return [
            'scope_id' => (int) $row['scope_id'],
            'availability_id' => $row['availability_id'] !== null ? (int) $row['availability_id'] : null,
            'action' => $row['action'],
            'weekdays' => (string) $row['weekdays'],
            'series' => (string) $row['series'],
            'valid_from' => (string) $row['valid_from'],
            'valid_to' => (string) $row['valid_to'],
            'time_range' => (string) $row['time_range'],
            'type' => (string) $row['type'],
            'slot_time' => (string) $row['slot_time'],
            'workstations' => (string) $row['workstations'],
            'bookable' => (string) $row['bookable'],
            'description' => (string) $row['description'],
            'changed_by' => $row['changed_by'],
        ];
    }
}
