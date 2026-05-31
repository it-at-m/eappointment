<?php

namespace BO\Zmsdb\Helper;

/**
 * @codeCoverageIgnore
 */
class ApiQuotaDeleteByCron
{
    /**
     * @var \BO\Zmsentities\Apikey
     */
    protected $quotaList;
    protected bool $verbose = false;

    public function __construct(\DateTimeInterface $dateTime, $verbose = false)
    {
        $query = new \BO\Zmsdb\Apikey();
        if ($verbose) {
            \App::$log->info('Deleting quota older than given period');
            $this->verbose = true;
        }
        $this->quotaList = $query->readExpiredQuotaListByPeriod($dateTime);
    }

    public function startProcessing($commit): void
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

    protected function removeQuota($quotaId): void
    {
        $verbose = $this->verbose;
        if (! $verbose) {
            $query = new \BO\Zmsdb\Apikey();
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
