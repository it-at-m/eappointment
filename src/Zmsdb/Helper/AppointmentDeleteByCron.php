<?php

namespace BO\Zmsdb\Helper;

/**
 * @codeCoverageIgnore
 */
class AppointmentDeleteByCron
{
    protected $processList;
    protected $verbose = false;

    public function __construct($timeIntervalDays, $verbose = false)
    {
        $deleteInSeconds = (24 * 60 * 60) * $timeIntervalDays;
        $query = new \BO\Zmsdb\Process();
        $time = new \DateTimeImmutable();
        $time = $time->setTimestamp(time() - $deleteInSeconds);
        if ($verbose) {
            error_log("INFO: Deleting appointments older than $timeIntervalDays days of date " . $time->format('c'));
            $this->verbose = true;
        }
        $this->processList = $query->readExpiredProcessList($time, 10000);
    }

    public function startProcessing($commit, $pending)
    {
        $verbose = $this->verbose;
        $statuslist = [
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
        if ($pending) {
            $statuslist[] = "pending";
        }
        foreach ($this->processList as $process) {
            if ($verbose) {
                error_log("INFO: Processing $process");
            }
            if ($commit) {
                $this->removeProcess($process, $statuslist);
            }
        }
    }

    protected function removeProcess(
        \BO\Zmsentities\Process $process,
        array $statuslist,
        array $archivelist = ["confirmed", "queued", "called", "missed", "processing", "pending"]
    ) {
        $verbose = $this->verbose;
        if (in_array($process->status, $statuslist)) {
            if (in_array($process->status, $archivelist)) {
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
        if ($query->writeDeletedEntity($process->id) && $verbose) {
            error_log("INFO: Process $process->id successfully removed");
        } else {
            error_log("WARN: Could not remove process '$process->id'!");
        }
    }
}
