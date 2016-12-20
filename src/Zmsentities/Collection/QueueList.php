<?php
namespace BO\Zmsentities\Collection;

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
        $timeSlot = $processTimeAverage * 60 / $workstationCount;

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
        return $this->sortByCustomKey('arrivalTime');
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
        $workstationCount = (0 == $workstationCount) ? 1 : $workstationCount;
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
        return ($newEntity) ? array(
            'amountBefore' => $queueList->getQueuePositionByNumber($newEntity->number),
            'waitingTimeEstimate' => $newEntity->waitingTimeEstimate
        ) : array(
            'amountBefore' =>$queueList->count(),
            'waitingTimeEstimate' => $lastEntity->waitingTimeEstimate
        );
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

    public function getQueuePositionByNumber($number)
    {
        foreach ($this as $key => $entity) {
            if ($entity->number == $number) {
                return $key;
            }
        }
        return null;
    }

    public function withStatus()
    {
        $queueList = new self();
        $statusList = func_get_args();
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
            if (! $entity->destination) {
                $entity->destination = $scope->toProperty()->preferences->pickup->alternateName->get();
            }
            $list->addEntity($entity);
        }
        return $list;
    }
}
