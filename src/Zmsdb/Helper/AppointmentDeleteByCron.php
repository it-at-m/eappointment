<?php

namespace BO\Zmsdb\Helper;

/**
 * @codeCoverageIgnore
 */
class AppointmentDeleteByCron
{
    protected $verbose = false;

    protected $limit = 10000;

    protected $loopCount = 500;

    protected $time;

    protected $statuslist = [
        "reserved",
        "deleted",
        "blocked",
        "confirmed",
        "queued",
        "called",
        "missed",
        "processing",
        "free",
    ];

    protected $archivelist = [
        "confirmed",
        "queued",
        "called",
        "missed",
        "processing",
        "pending"
    ];

    public function __construct($timeIntervalDays, \DateTimeInterface $now, $verbose = false)
    {
        $deleteInSeconds = (24 * 60 * 60) * $timeIntervalDays;
        $this->time = $time->setTimestamp($now->getTimestamp() - $deleteInSeconds);
        if ($verbose) {
            error_log(
                "INFO: Deleting appointments older than $timeIntervalDays days of date " . $this->time->format('c')
            );
            $this->verbose = true;
        }
    }

    public function startProcessing($commit, $pending = false)
    {
        $query = new \BO\Zmsdb\Process();
        if ($pending) {
            $this->statuslist[] = "pending";
        }
        $processCount = $this->loopCount;
        while ($processCount < $this->limit) {
            $processList = $query->readExpiredProcessList($this->time, $this->loopCount);
            if (0 == $processList->count()) {
                break;
            }
            foreach ($processList as $process) {
                if ($this->verbose) {
                    error_log("INFO: Processing $process");
                }
                if ($commit) {
                    $this->removeProcess($process);
                }
                $processCount++;
            }
        }
    }

    protected function removeProcess(\BO\Zmsentities\Process $process)
    {
        $verbose = $this->verbose;
        if (in_array($process->status, $this->statuslist)) {
            if (in_array($process->status, $this->archivelist)) {
                $process = $this->updateProcessStatus($process);
                $this->archiveProcess($process);
            }
            $this->deleteProcess($process);
        } elseif ($verbose) {
            error_log("INFO: Keep process $process->id");
        }
    }

    protected function updateProcessStatus(\BO\Zmsentities\Process $process)
    {
        if (in_array($process->status, ["confirmed", "queued", "called"])) {
            $process->status = 'missed';
        }
        return $process;
    }

    protected function archiveProcess(\BO\Zmsentities\Process $process)
    {
        $verbose = $this->verbose;
        $now = new \DateTimeImmutable();
        $archiver = new \BO\Zmsdb\ProcessStatusArchived();
        $archived = null;
        $archived = $archiver->writeEntityFinished($process, $now);
        if ($archived && $verbose) {
            error_log("INFO: Archived with Status=$process->status and Id=" . $archived->archiveId);
        }
    }

    protected function deleteProcess(\BO\Zmsentities\Process $process)
    {
        $verbose = $this->verbose;
        $query = new \BO\Zmsdb\Process();
        if ($query->writeDeletedEntity($process->id)) {
            if ($verbose) {
                error_log("INFO: Process $process->id successfully removed");
            }
        } else {
            if ($verbose) {
                error_log("WARN: Could not remove process '$process->id'!");
            }
        }
    }
}
