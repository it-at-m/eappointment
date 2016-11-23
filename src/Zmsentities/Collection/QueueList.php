<?php
namespace BO\Zmsentities\Collection;

class QueueList extends Base
{
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

        while (count($listWithAppointment) || count($listNoAppointment)) {
            if ($nextWithAppointment && $currentTime >= $nextWithAppointment->arrivalTime) {
                $nextWithAppointment->waitingTime = $waitingTime;
                $queueWithWaitingTime->addEntity($nextWithAppointment);
                $nextWithAppointment = array_shift($listWithAppointment);
            } elseif ($nextNoAppointment) {
                $nextNoAppointment->waitingTime = $waitingTime;
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

    public function getEstimatedWaitingTime()
    {
    }

    public function getCountOfQueuesBeforeNumber($waitingNumber)
    {

    }
}
