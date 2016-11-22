<?php
namespace BO\Zmsentities\Collection;

class QueueList extends Base
{
    /*
    public function withEstimatedWaitingTime($processTimeAverage, $workstationCount)
    {
        $timeSlot = $processTimeAverage * 60 / $workstationCount;
        foreach ($this as $item) {
           //to do
        }
    }
     */

    public function withSortedArrival()
    {
        return $this->sortByCustomKey('arrivalTime');
    }

    public function withAppointment()
    {
        $queueList = new self();
        foreach ($this as $entity) {
            if ($entity->withAppointment) {
                $queueList->addEntity($entity);
            }
        }
        return $queueList;
    }

    public function withOutAppointment()
    {
        $queueList = new self();
        foreach ($this as $entity) {
            if (! $entity->withAppointment) {
                $queueList->addEntity($entity);
            }
        }
        return $queueList;
    }
}
