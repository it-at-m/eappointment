<?php
/**
 * @copyright BerlinOnline Stadtportal GmbH & Co. KG
 **/

declare(strict_types=1);

namespace BO\Zmsdb\Helper;

use BO\Zmsdb\Log;
use BO\Zmsdb\Scope;

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
        $scopes = (new Scope())->readList();

        if (! $commit) {
            return;
        }

        foreach ($scopes as $scope) {
            $olderThanDay = $scope->getPreference('logs', 'deleteLogsOlderThanDays') ?? 90;
            (new Log())->clearOldDataForScope($scope->id, (int) $olderThanDay);
        }
    }
}
