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

    public const QUERY_SELECT_BY_SCOPE = '
        SELECT
            id,
            scope_id AS scopeId,
            availability_id AS availabilityId,
            action,
            summary,
            changed_at AS changedAt,
            changed_by AS changedBy
        FROM availability_history
        WHERE scope_id = :scopeId
          AND changed_at >= :from
          AND changed_at <= :to
        ORDER BY changed_at DESC, id DESC
    ';

    public const QUERY_SELECT_BY_SCOPE_AND_AVAILABILITY = '
        SELECT
            id,
            scope_id AS scopeId,
            availability_id AS availabilityId,
            action,
            summary,
            changed_at AS changedAt,
            changed_by AS changedBy
        FROM availability_history
        WHERE scope_id = :scopeId
          AND availability_id = :availabilityId
          AND changed_at >= :from
          AND changed_at <= :to
        ORDER BY changed_at DESC, id DESC
    ';

    public const QUERY_DELETE_OLDER_THAN = '
        DELETE FROM availability_history
        WHERE changed_at < :cutoff
    ';

    protected $resolveLevel = 0;

    /**
     * @param array{
     *     scope_id:int,
     *     availability_id:?int,
     *     action:string,
     *     summary:string,
     *     changed_by:string
     * } $row
     */
    public function reverseEntityMapping(array $row): array
    {
        return [
            'scope_id' => (int) $row['scope_id'],
            'availability_id' => $row['availability_id'] !== null ? (int) $row['availability_id'] : null,
            'action' => $row['action'],
            'summary' => $row['summary'],
            'changed_by' => $row['changed_by'],
        ];
    }
}
