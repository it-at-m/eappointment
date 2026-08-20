<?php

namespace BO\Zmsbackend\Process\Repository;

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
    public const string TABLE = 'buergerarchivtoday';
    const string DELETE_ALL = 'DELETE FROM buergerarchivtoday';
}
