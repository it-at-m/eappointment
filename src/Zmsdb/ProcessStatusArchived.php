<?php
namespace BO\Zmsdb;

use \BO\Zmsentities\Process as Entity;
use \BO\Zmsentities\Collection\ProcessList as Collection;

/**
 *
 */
class ProcessStatusArchived extends Process
{

    /**
     * Insert a new process if there are free slots
     *
     * @SuppressWarnings("unused")
     */
    public function writeEntityFinished(
        \BO\Zmsentities\Process $process,
        \DateTimeInterface $now
    ) {
        $process = $this->updateEntity($process, 1);
        $archive = null;
        if ($this->writeDereferencedEntity($process->id, $process->authKey)) {
            $archive = (new Archive)->writeNewArchivedProcess($process, $now);
        }
        // update xRequest entry and update process id as well es archived id
        if ($archive) {
            $this->writeXRequestsArchived($process->id, $archive->id);
        }
        /******************************************************
            ToDo write to statistic Table
        ******************************************************/
        return $process;
    }
}
