<?php

namespace BO\Zmsbackend\Helper;

/**
 * @codeCoverageIgnore
 */
class ApiQuotaDeleteByCron
{
    protected $quotaList;
    protected $verbose = false;

    public function __construct(\DateTimeInterface $dateTime, $verbose = false)
    {
        $query = new \BO\Zmsbackend\Apikey\Service\Apikey();
        if ($verbose) {
            \App::$log->info('Deleting quota older than given period');
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
                    \App::$log->info('Processing quota', ['quota' => join(', ', $quota)]);
                }
                if ($commit) {
                    $this->removeQuota($quota['quotaid']);
                }
            }
        } elseif ($verbose) {
            \App::$log->info('No expired quota was found');
        }
    }

    protected function removeQuota($quotaId)
    {
        $verbose = $this->verbose;
        if (! $verbose) {
            $query = new \BO\Zmsbackend\Apikey\Service\Apikey();
            if ($query->writeDeletedQuota($quotaId)) {
                if ($verbose) {
                    \App::$log->info('Quota successfully removed', ['quotaId' => $quotaId]);
                }
            } else {
                \App::$log->warning('Could not remove quota', ['quotaId' => $quotaId]);
            }
        } elseif ($verbose) {
            \App::$log->info('Keep quota', ['quotaId' => $quotaId]);
        }
    }
}
