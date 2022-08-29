<?php
/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

declare(strict_types=1);

namespace BO\Zmsdb\Helper;

use BO\Zmsdb\EventLog as EventLogRepository;

class EventLogCleanUpByCron
{
    public static function startProcessing()
    {
        $eventLogRepo  = new EventLogRepository();
        $eventLogRepo->deleteOutdated();
    }
}
