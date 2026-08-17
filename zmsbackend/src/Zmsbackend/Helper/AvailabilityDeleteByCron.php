<?php

namespace BO\Zmsbackend\Helper;

/**
 * @codeCoverageIgnore
 */
class AvailabilityDeleteByCron
{
    protected $verbose = false;
    protected $query;

    public function __construct($verbose = false)
    {
        $this->query = new \BO\Zmsbackend\Availability\Service\Availability();
        if ($verbose) {
            $this->verbose = true;
        }
    }

    public function startProcessing(\DateTimeImmutable $datetime, $commit = false): void
    {
        $availabilityList = $this->query->readAvailabilityListBefore($datetime);
        if ($this->verbose) {
            \App::$log->info('Reading availability list');
        }
        foreach ($availabilityList as $availability) {
            if ($commit) {
                $this->deleteAvailability($availability->getId());
            } elseif ($this->verbose) {
                \App::$log->info('Would remove availability', ['availability' => (string) $availability]);
            }
        }
    }

    protected function deleteAvailability(string $availabilityId): void
    {
        $entity = null;
        try {
            $entity = $this->query->readEntity($availabilityId, 1);
        } catch (\Throwable $exception) {
            // Still attempt delete; history write needs a resolved entity.
            \App::$log->warning('availability_history pre-delete read failed', [
                'availabilityId' => $availabilityId,
                'exception' => $exception->getMessage(),
            ]);
        }

        if ($this->query->deleteEntity($availabilityId)) {
            if ($entity && $entity->hasId()) {
                (new \BO\Zmsbackend\Availability\Service\AvailabilityHistory())
                    ->writeDeleted($entity, 'cron');
            } else {
                \App::$log->warning('availability_history skipped: entity unavailable before cron delete', [
                    'availabilityId' => $availabilityId,
                ]);
            }
            if ($this->verbose) {
                \App::$log->info('Availability successfully removed', ['availabilityId' => $availabilityId]);
            }
        } else {
            \App::$log->warning('Could not remove availability', ['availabilityId' => $availabilityId]);
        }
    }
}
