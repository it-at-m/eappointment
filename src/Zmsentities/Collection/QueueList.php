<?php
namespace BO\Zmsentities\Collection;

/**
 * @SuppressWarnings(Complexity)
 * @SuppressWarnings(PublicMethod)
 *
 */
class QueueList extends Base
{
    const FAKE_WAITINGNUMBER = 1001;

    public function withEstimatedWaitingTime($processTimeAverage, $workstationCount, \DateTimeInterface $dateTime)
    {
        $queueWithWaitingTime = new self();
        $listWithAppointment = $this->withAppointment()->withSortedArrival()->getArrayCopy();
        $listNoAppointment = $this->withOutAppointment()->withSortedArrival()->getArrayCopy();
        $nextWithAppointment = array_shift($listWithAppointment);
        $nextNoAppointment = array_shift($listNoAppointment);
        $currentTime = $dateTime->getTimestamp();

        $waitingTime = 0;
        $timeSlot = ($workstationCount) ? $processTimeAverage * 60 / $workstationCount : $processTimeAverage * 60;
        while ($nextWithAppointment || $nextNoAppointment) {
            if ($nextWithAppointment && $currentTime >= $nextWithAppointment->arrivalTime) {
                $nextWithAppointment->waitingTimeEstimate = $waitingTime;
                $queueWithWaitingTime->addEntity($nextWithAppointment);
                $nextWithAppointment = array_shift($listWithAppointment);
            } elseif ($nextNoAppointment) {
                $nextNoAppointment->waitingTimeEstimate = $waitingTime;
                $queueWithWaitingTime->addEntity($nextNoAppointment);
                $nextNoAppointment = array_shift($listNoAppointment);
            }
            $currentTime += $timeSlot;
            $waitingTime = round(($currentTime - $dateTime->getTimestamp()) / 60, 0);
        }
        return $queueWithWaitingTime;
    }

    public function withSortedArrival()
    {
        $queueList = clone $this;
        return $queueList->sortByCustomKey('arrivalTime');
    }

    public function withAppointment()
    {
        $queueList = new self();
        foreach ($this as $entity) {
            if ($entity->withAppointment) {
                $queueList->addEntity(clone $entity);
            }
        }
        return $queueList;
    }

    public function withOutAppointment()
    {
        $queueList = new self();
        foreach ($this as $entity) {
            if (! $entity->withAppointment) {
                $queueList->addEntity(clone $entity);
            }
        }
        return $queueList;
    }

    public function getEstimatedWaitingTime($processTimeAverage, $workstationCount, \DateTimeInterface $dateTime)
    {
        $queueList = clone $this;
        $entity = new \BO\Zmsentities\Queue();
        $entity->number = self::FAKE_WAITINGNUMBER;
        $entity->withAppointment = false;
        $entity->arrivalTime = $dateTime->getTimestamp();
        $queueList->addEntity($entity);
        $queueList = $queueList
          ->withEstimatedWaitingTime($processTimeAverage, $workstationCount, $dateTime);
        $newEntity = $queueList->getQueueByNumber(self::FAKE_WAITINGNUMBER);
        $lastEntity = end($queueList);

        $dataOfLastEntity = array(
            'amountBefore' =>$queueList->count(),
            'waitingTimeEstimate' => $lastEntity->waitingTimeEstimate
        );
        $dataOfFackedEntity = array(
            'amountBefore' => $queueList->getQueuePositionByNumber($newEntity->number),
            'waitingTimeEstimate' => $newEntity->waitingTimeEstimate
        );
        return ($newEntity) ? $dataOfFackedEntity : $dataOfLastEntity;
    }

    public function getQueueByNumber($number)
    {
        foreach ($this as $entity) {
            if ($entity->number == $number) {
                return $entity;
            }
        }
        return null;
    }

    public function getNextProcess(\DateTimeInterface $dateTime, $exclude = null)
    {
        $excludeNumbers = explode(',', $exclude);
        $queueList = clone $this;
        $queueList = $queueList->withStatus(['confirmed', 'queued'])->withSortedArrival()->getArrayCopy();
        $next = array_shift($queueList);
        $currentTime = $dateTime->getTimestamp();

        while ($next) {
            if (! in_array($next->number, $excludeNumbers) &&
                (0 == $next->lastCallTime || ($next->lastCallTime + (5 * 60)) <= $currentTime)
            ) {
                return $next->getProcess();
            }
            $next = array_shift($queueList);
        }
        return null;
    }

    public function getQueuePositionByNumber($number)
    {
        foreach ($this as $key => $entity) {
            if ($entity->number == $number) {
                return $key;
            }
        }
        return null;
    }

    /**
     * @param array $statusList of possible strings in process.status
     *
     */
    public function withStatus(array $statusList)
    {
        $queueList = new self();
        foreach ($this as $entity) {
            if ($entity->toProperty()->status->isAvailable() && in_array($entity->status, $statusList)) {
                $queueList->addEntity(clone $entity);
            }
        }
        return $queueList;
    }

    public function withShortNameDestinationHint(\BO\Zmsentities\Cluster $cluster, \BO\Zmsentities\Scope $scope)
    {
        $queueList = clone $this;
        $list = new self();
        foreach ($queueList as $entity) {
            if ($cluster->shortNameEnabled && $scope->shortName) {
                $entity->destinationHint = $scope->shortName;
            }
            $list->addEntity($entity);
        }
        $listWithPickups = $list->withPickupDestination($scope);
        return $listWithPickups;
    }

    public function withPickupDestination(\BO\Zmsentities\Scope $scope)
    {
        $queueList = clone $this;
        $list = new self();
        foreach ($queueList as $entity) {
            if (! $entity->toProperty()->destination->get()) {
                $entity->destination = $scope->toProperty()->preferences->pickup->alternateName->get();
            }
            $list->addEntity($entity);
        }
        return $list;
    }

    public function toProcessList()
    {
        $processList = new ProcessList();
        foreach ($this as $queue) {
            $process = $queue->getProcess();
            $processList->addEntity($process);
        }
        return $processList;
    }

    public function withoutDublicates()
    {
        $list = new self();
        foreach ($this as $entity) {
            $hasEntity = false;
            foreach ($list as $inListEntity) {
                if ($inListEntity->number == $entity->number &&
                    $inListEntity->arrivalTime == $entity->arrivalTime &&
                    $inListEntity->withAppointment == $entity->withAppointment
                ) {
                    $hasEntity = true;
                }
            }
            if (! $hasEntity) {
                $list->addEntity($entity);
            }
        }
        return $list->withSortedArrival();
    }

    public function getWaitingNumberList()
    {
        $list = [];
        foreach ($this as $entity) {
            $list[] = $entity->number;
        }
        return $list;
    }

    public function getWaitingNumberListCsv()
    {
        return implode(',', $this->getWaitingNumberList());
    }
}
