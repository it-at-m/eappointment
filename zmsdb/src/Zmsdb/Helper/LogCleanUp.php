<?php
/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

declare(strict_types=1);

namespace BO\Zmsdb\Helper;

use BO\Zmsdb\Config as ConfigRepository;
use BO\Zmsdb\Log;

class LogCleanUp
{
    protected $verbose = false;
    
    public function __construct($verbose = false)
    {
        if ($verbose) {
            $this->verbose = true;
            $this->log("INFO: Delete old logs");
        }
    }

    protected function log($message)
    {
        if ($this->verbose) {
            error_log($message);
        }
    }

    public static function startProcessing($commit = false)
    {
        $config = (new ConfigRepository())->readEntity();
        $olderThan = $config->getPreference('log', 'deleteOlderThanDays') ?? 90;

        $logRepo  = new Log();
        if ($commit) {
            $logRepo->clearDataOlderThan((int) $olderThan);
        }
    }
}
