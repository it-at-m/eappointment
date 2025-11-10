<?php

namespace BO\Zmsdb\Helper;

/**
 * @codeCoverageIgnore
 */
class TicketprinterDeleteByCron
{
    protected $verbose = false;

    protected $deleteDateTime = 0;

    public function __construct($verbose = false)
    {
        $dateTime = new \DateTimeImmutable();
        $this->deleteDateTime = $dateTime->setTimestamp($dateTime->getTimestamp() - (30 * 24 * 3600));
        $dateString = $this->deleteDateTime->format('Y-m-d');
        if ($verbose) {
            \App::$log->info(
                "INFO: Deleting expired ticketprinter older than 30 days ($dateString)"
            );
            $this->verbose = true;
        }
        $this->scopeList = (new \BO\Zmsdb\Scope())->readList();
    }

    public function startProcessing($commit)
    {
        $ticketprinterList = (new \BO\Zmsdb\Ticketprinter())->readExpiredTicketprinterList($this->deleteDateTime);
        foreach ($ticketprinterList as $entity) {
            if ($this->verbose) {
                \App::$log->info("Processing ticketprinter", ['entity' => (string) $entity]);
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
            \App::$log->info("Ticketprinter successfully removed", ['id' => $entity->id]);
        } elseif ($this->verbose) {
            \App::$log->warning("Could not remove ticketprinter", ['id' => $entity->id]);
        }
    }
}
