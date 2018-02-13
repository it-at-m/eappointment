<?php
namespace BO\Zmsentities\Collection;

use \BO\Zmsentities\Helper\Sorter;

/**
 * @SuppressWarnings(Public)
 *
 */
class ProcessList extends Base
{
    const ENTITY_CLASS = '\BO\Zmsentities\Process';

    public function toProcessListByTime()
    {
        $list = new self();
        foreach ($this as $process) {
            $appointment = $process->getFirstAppointment();
            $list[$appointment['date']][] = clone $process;
        }
        return $list;
    }

    public function sortByArrivalTime()
    {
        $this->uasort(function ($a, $b) {
            return ($a->queue['arrivalTime'] - $b->queue['arrivalTime']);
        });
        return $this;
    }

    public function sortByClientName()
    {
        $this->uasort(function ($a, $b) {
            return strcmp(
                Sorter::toSortableString($a->getFirstClient()['familyName']),
                Sorter::toSortableString($b->getFirstClient()['familyName'])
            );
        });
        return $this;
    }

    public function sortByTimeKey()
    {
        $this->uksort(function ($a, $b) {
            return ($a - $b);
        });
        return $this;
    }

    public function toProcessListByStatusAndTime()
    {
        $list = $this->getWithHoursByDay();
        $oldList = clone $this->sortByArrivalTime();
        foreach ($oldList as $process) {
            foreach ($list as $hour => $timeList) {
                $timeList = new self();
                $appointment = $process->getFirstAppointment();
                if ($hour == $appointment->toDateTime()->format('H')) {
                    if (! isset($list[$hour][intval($appointment['date'])])) {
                        $list[$hour][intval($appointment['date'])] = $timeList;
                    }
                    $list[$hour][intval($appointment['date'])]->addEntity(clone $process);
                    ksort($list[$hour]);
                }
            }
        }
        return $list;
    }

    public function getWithHoursByDay()
    {
        $list = array();
        $start = 7;
        $endTime = 18;
        $hour = $start;
        while ($hour <= $endTime) {
            $list[$hour] = array();
            $hour++;
        }
        ksort($list);
        return $list;
    }

    public function getScopeList()
    {
        $list = new ScopeList();
        foreach ($this as $process) {
            if (array_key_exists('scope', $process)) {
                $list[] = new \BO\Zmsentities\Scope($process['scope']);
            }
        }
        return $list->withUniqueScopes();
    }

    public function getAppointmentList()
    {
        $appointmentList = new AppointmentList();
        foreach ($this as $process) {
            foreach ($process["appointments"] as $appointment) {
                $appointmentList->addEntity(new \BO\Zmsentities\Appointment($appointment));
            }
        }
        return $appointmentList;
    }

    public function toQueueList($now)
    {
        $queueList = new QueueList();
        foreach ($this as $process) {
            $queue = $process->toQueue($now);
            $queueList->addEntity($queue);
        }
        return $queueList;
    }

    public function withAvailability(\BO\Zmsentities\Availability $availability)
    {
        $processList = new static();
        foreach ($this as $process) {
            if ($availability->hasAppointment($process->getFirstAppointment())) {
                $processList[] = clone $process;
            }
        }
        return $processList;
    }

    public function withAvailabilityStrict(\BO\Zmsentities\Availability $availability)
    {
        $processList = new static();
        $slotList = $availability->getSlotList();
        foreach ($this as $process) {
            if ($slotList->removeAppointment($process->getFirstAppointment())) {
                $processList[] = clone $process;
            }
        }
        return $processList;
    }

    public function withOutAvailability(\BO\Zmsentities\Collection\AvailabilityList $availabilityList)
    {
        $processList = new static();
        $slotList = $availabilityList->withType('appointment')->getSlotList();
        foreach ($this as $process) {
            if (!$slotList->removeAppointment($process->getFirstAppointment())) {
                $processList[] = clone $process;
            }
        }
        return $processList;
    }

    public function withScopeId($scopeId)
    {
        $processList = new static();
        foreach ($this as $process) {
            if ($process->scope['id'] == $scopeId) {
                $processList[] = clone $process;
            }
        }
        return $processList;
    }

    public function withOutScopeId($scopeId)
    {
        $processList = new static();
        foreach ($this as $process) {
            if ($process->scope['id'] != $scopeId) {
                $processList[] = clone $process;
            }
        }
        return $processList;
    }
}
