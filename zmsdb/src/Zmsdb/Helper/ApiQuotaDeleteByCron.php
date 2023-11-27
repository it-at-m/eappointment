<?php

namespace BO\Zmsdb\Helper;

/**
 * @codeCoverageIgnore
 */
class ApiQuotaDeleteByCron
{
    protected $quotaList;
    protected $verbose = false;

    public function __construct(\DateTimeInterface $dateTime, $verbose = false)
    {
        $query = new \BO\Zmsdb\Apikey();
        if ($verbose) {
            error_log("INFO: Deleting quota older than given period");
            $this->verbose = true;
        }
        $this->quotaList = $query->readExpiredQuotaListByPeriod($dateTime);
    }

    public function startProcessing($commit)
    {
        $verbose = $this->verbose;
        if ($this->quotaList) {
            foreach ($this->quotaList as $quota) {
                if ($verbose) {
                    error_log("INFO: Processing quota: ". join(', ', $quota));
                }
                if ($commit) {
                    $this->removeQuota($quota['quotaid']);
                }
            }
        } else {
            if ($verbose) {
                error_log("INFO: no expired quota was found");
            }
        }
    }

    protected function removeQuota($quotaId)
    {
        $verbose = $this->verbose;
        if (! $verbose) {
            $query = new \BO\Zmsdb\Apikey();
            if ($query->writeDeletedQuota($quotaId)) {
                if ($verbose) {
                    error_log("INFO: Quota $quotaId successfully removed");
                }
            } else {
                error_log("WARN: Could not remove quota '$quotaId'!");
            }
        } elseif ($verbose) {
            error_log("INFO: Keep quota $quotaId");
        }
    }
}
