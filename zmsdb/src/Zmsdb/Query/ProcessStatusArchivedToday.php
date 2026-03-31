<?php

namespace BO\Zmsdb\Query;

/**
*
* @SuppressWarnings(TooManyPublicMethods)
* @SuppressWarnings(Complexity)
 */
class ProcessStatusArchivedToday extends ProcessStatusArchived
{
    /**
     *
     * @var String TABLE mysql table reference
     */
    public const TABLE = 'buergerarchivtoday';
    const DELETE_ALL = 'DELETE FROM buergerarchivtoday';
}
