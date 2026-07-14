<?php

namespace BO\Zmsbackend\Helper;

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
                            \App::$log->info('Deleting expired ticketprinter older than 30 days', ['beforeDate' => $dateString]);
            $this->verbose = true;
        }
        $this->scopeList = (new \BO\Zmsbackend\Scope\Service\Scope())->readList();
    }

    public function startProcessing($commit)
    {
        $ticketprinterList = (new \BO\Zmsbackend\Ticketprinter\Service\Ticketprinter())->readExpiredTicketprinterList($this->deleteDateTime);
        foreach ($ticketprinterList as $entity) {
            if ($this->verbose) {
                \App::$log->info('Processing ticketprinter', ['ticketprinter' => (string) $entity]);
            }
            if ($commit) {
                $this->deleteTicketpinter($entity);
            }
        }
    }

    protected function deleteTicketpinter(\BO\Zmsentities\Ticketprinter $entity)
    {
        $query = new \BO\Zmsbackend\Ticketprinter\Service\Ticketprinter();
        if ($query->deleteEntity($entity->id) && $this->verbose) {
            \App::$log->info('Ticketprinter successfully removed', ['ticketprinterId' => $entity->id]);
        } elseif ($this->verbose) {
            \App::$log->warning('Could not remove ticketprinter', ['ticketprinterId' => $entity->id]);
        }
    }
}
