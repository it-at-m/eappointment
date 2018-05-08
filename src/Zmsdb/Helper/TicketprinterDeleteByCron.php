<?php

namespace BO\Zmsdb\Helper;

/**
 * @codeCoverageIgnore
 */
class TicketprinterDeleteByCron
{
    protected $verbose = false;

    protected $deleteInterval = 0;

    public function __construct($verbose = false)
    {
        $this->deleteInterval = 30 * 24 * 3600;
        $dateTime = new \DateTimeImmutable();
        $deleteDate = $dateTime->setTimestamp($dateTime->getTimestamp() - $this->deleteInterval)->format('Y-m-d');
        if ($verbose) {
            error_log("INFO: Deleting expired ticketprinter older than 30 days ($deleteDate)");
            $this->verbose = true;
        }
        $this->scopeList = (new \BO\Zmsdb\Scope)->readList();
    }

    public function startProcessing($commit)
    {
        $ticketprinterList = (new \BO\Zmsdb\Ticketprinter)->readExpiredTicketprinterList($this->deleteInterval);
        foreach ($ticketprinterList as $entity) {
            if ($this->verbose) {
                error_log("INFO: Processing $entity");
            }
            if ($commit) {
                $this->deleteTicketpinter($entity);
            }
        }
    }

    protected function deleteTicketpinter(\BO\Zmsentities\Ticketprinter $entity)
    {
        $query = new \BO\Zmsdb\Ticketprinter();
        if ($query->deleteEntity($entity->id) && $this->verbose) {
            error_log("INFO: Ticketprinter $entity->id successfully removed");
        } elseif ($this->verbose) {
            error_log("WARN: Could not remove ticketprinter '$entity->id'!");
        }
    }
}
