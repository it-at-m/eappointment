<?php
namespace BO\Zmsentities\Collection;

use \BO\Zmsentities\Helper\Sorter;

use \BO\Zmsentities\Helper\Property;

use \BO\Zmsentities\Helper\Messaging;

use \BO\Zmsentities\Process;

/**
 * @SuppressWarnings(Complexity)
 * @SuppressWarnings(Public)
 * @SuppressWarnings(Coupling)
 *
 */
class ProcessList extends Base
{
    const ENTITY_CLASS = '\BO\Zmsentities\Process';

    public function toProcessListByTime($format = null)
    {
        $list = new self();
        foreach ($this as $process) {
            $appointment = $process->getFirstAppointment();
            $formattedDate = $appointment['date'];
            if ($format) {
                $formattedDate = $appointment->toDateTime()->format($format);
            }
            $list[$formattedDate][] = clone $process;
        }
        return $list;
    }

    public function withRequest($requestId)
    {
        $list = new self();
        foreach ($this as $process) {
            if ($process->requests->hasEntity($requestId)) {
                $list->addEntity(clone $process);
            }
        }
        return $list;
    }

    public function sortByScopeName()
    {
        $this->uasort(function ($a, $b) {
            return strcmp(
                Sorter::toSortableString(ucfirst($a->scope->contact['name'])),
                Sorter::toSortableString(ucfirst($b->scope->contact['name']))
            );
        });
        return $this;
    }

    public function sortByAppointmentDate()
    {
        $this->uasort(function ($a, $b) {
            return ($a->getFirstAppointment()->date - $b->getFirstAppointment()->date);
        });
        return $this;
    }

    public function sortByArrivalTime()
    {
        $this->uasort(function ($a, $b) {
            return ($a->queue['arrivalTime'] - $b->queue['arrivalTime']);
        });
        return $this;
    }

    public function sortByEstimatedWaitingTime()
    {
        $this->uasort(function ($a, $b) {
            return ($a->queue['waitingTimeEstimate'] - $b->queue['waitingTimeEstimate']);
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

    public function sortPickupQueue()
    {
        $this->uasort(function ($a, $b) {
            if ($a->queue['arrivalTime'] == $b->queue['arrivalTime']) {
                //error_log('equal arrival Time for :'. $a->id);
                return strcmp(
                    Sorter::toSortableString($a->getFirstClient()['familyName']),
                    Sorter::toSortableString($b->getFirstClient()['familyName'])
                );
            }
            return ($a->queue['arrivalTime'] - $b->queue['arrivalTime']);
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

    public function toProcessListByStatusList(array $statusList)
    {
        $collection = new self();
        foreach ($this as $process) {
            if (in_array($process->getStatus(), $statusList)) {
                $collection->addEntity($process);
            }
        }
        return $collection;
    }

    public function toConflictListByDay()
    {
        $list = [];
        $oldList = clone $this;
        foreach ($oldList as $process) {
            $appointmentList = [];
            if (!isset($list[$process->getFirstAppointment()->getStartTime()->format('Y-m-d')])) {
                $list[$process->getFirstAppointment()->getStartTime()->format('Y-m-d')] = [];
            }
            foreach ($process->getAppointments() as $appointment) {
                $availabilityId = ($appointment->getAvailability()->getId()) ?
                    $appointment->getAvailability()->getId() :
                    $appointment->getAvailability()->tempId;
                $appointmentList[] = [
                    'startTime' => $appointment->getStartTime()->format('H:i'),
                    'endTime' => $appointment->getEndTime()->format('H:i'),
                    'availability' => $availabilityId
                ];
            }
            $list[$process->getFirstAppointment()->getStartTime()->format('Y-m-d')][] = [
                'message' => $process->amendment,
                'appointments' => $appointmentList
            ];
        }
        return $list;
    }

    public function getScopeList()
    {
        $list = new ScopeList();
        foreach ($this as $process) {
            if (Property::__keyExists('scope', $process)) {
                $list[] = new \BO\Zmsentities\Scope($process['scope']);
            }
        }
        return $list->withUniqueScopes();
    }

    public function getRequestList()
    {
        $list = new RequestList();
        foreach ($this as $process) {
            if (Property::__keyExists('requests', $process)) {
                $list->addList($process->getRequests());
            }
        }
        return $list->withUniqueRequests();
    }

    public function getAppointmentList()
    {
        $appointmentList = new AppointmentList();
        foreach ($this as $process) {
            if (Property::__keyExists('appointments', $process)) {
                foreach ($process["appointments"] as $appointment) {
                    $appointmentList->addEntity(new \BO\Zmsentities\Appointment($appointment));
                }
            }
        }
        return $appointmentList;
    }

    public function setTempAppointmentToProcess($dateTime, $scopeId)
    {
        $addedAppointment = false;
        $appointment = (new \BO\Zmsentities\Appointment)->addDate($dateTime->getTimestamp())->addScope($scopeId);
        foreach ($this as $process) {
            if ($process->hasAppointment($dateTime->getTimestamp(), $scopeId) && !$addedAppointment) {
                $entity = clone $process;
                $this->addEntity($entity);
                $addedAppointment = true;
            }
        }
        if (!$addedAppointment) {
            $entity = new \BO\Zmsentities\Process();
            $entity->addAppointment($appointment);
            $this->addEntity($entity);
        }
        return $this;
    }

    public function toQueueList($now)
    {
        $queueList = new QueueList();
        foreach ($this as $process) {
            $queue = $process->toQueue($now);
            $queueList->addEntity($queue);
        }
        $queueList->setTransferedProcessList(true);
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

    public function withDepartmentNotificationEnabled()
    {
        $processList = new static();
        foreach ($this as $process) {
            if ($process->scope->hasNotificationReminderEnabled()) {
                $entity = clone $process;
                $processList->addEntity($entity);
            }
        }
        return $processList;
    }

    public function withDepartmentHasMailFrom()
    {
        $processList = new static();
        foreach ($this as $process) {
            if ($process->scope->hasEmailFrom()) {
                $entity = clone $process;
                $processList->addEntity($entity);
            }
        }
        return $processList;
    }

    
    
    public function setConflictAmendment()
    {
        foreach ($this as $process) {
            $process->amendment = 'Die Slots für diesen Zeitraum wurden überbucht';
            if (! $process->getFirstAppointment()->availability->hasId()) {
                $process->amendment = 'Der Vorgang ('. $process->getId() .') befindet sich außerhalb der Öffnungszeit!';
            }
        }
        return $this;
    }

    public function withOutAvailability(\BO\Zmsentities\Collection\AvailabilityList $availabilityList)
    {
        $processList = new static();
        foreach ($this->toProcessListByTime('Y-m-d') as $processListByDate) {
            $dateTime = $processListByDate[0]->getFirstAppointment()->toDateTime();
            $slotList = $availabilityList->withType('appointment')->withDateTime($dateTime)->getSlotList();
            foreach ($processListByDate as $process) {
                try {
                    $slotList->withSlotsForAppointment($process->getFirstAppointment());
                    if (!$slotList->removeAppointment($process->getFirstAppointment())) {
                        $process->amendment = "";
                        $processList[] = clone $process;
                    }
                } catch (\BO\Zmsentities\Exception\AppointmentNotFitInSlotList $exception) {
                    $process->amendment = "";
                    $processList[] = clone $process;
                }
            }
        }
        return $processList;
    }

    public function withUniqueScope($oncePerHour = false)
    {
        $processList = new static();
        $scopeKeyList = [];
        foreach ($this as $process) {
            $scopeKey = $process->scope->id . '-';
            if ($oncePerHour) {
                $scopeKey .= $process->getFirstAppointment()->toDateTime()->format('H');
            } else {
                $scopeKey .= $process->getFirstAppointment()->toDateTime()->format('H:i');
            }
            if (!in_array($scopeKey, $scopeKeyList)) {
                $processList[] = clone $process;
                $scopeKeyList[] = $scopeKey;
            }
        }
        return $processList;
    }

    public function withAccess(\BO\Zmsentities\Useraccount $useraccount)
    {
        $list = new static();
        foreach ($this as $process) {
            $process = clone $process;
            if ($process->getCurrentScope()->hasAccess($useraccount)) {
                $list[] = $process;
            }
        }
        return $list;
    }

    public function withScopeId($scopeId)
    {
        $processList = new static();
        foreach ($this as $process) {
            if ($scopeId == $process->scope['id']) {
                $processList[] = clone $process;
            }
        }
        return $processList;
    }

    public function withOutScopeId($scopeId)
    {
        $processList = new static();
        foreach ($this as $process) {
            if ($scopeId != $process->scope['id']) {
                $processList[] = clone $process;
            }
        }
        return $processList;
    }

    public function withOutProcessId($processId)
    {
        $processList = new static();
        foreach ($this as $process) {
            if ($processId != $process->getId()) {
                $processList[] = clone $process;
            }
        }
        return $processList;
    }

    public function withoutExpiredAppointmentDate(\DateTimeInterface $now)
    {
        $conflictList = new self();
        foreach ($this as $process) {
            if ($process->getFirstAppointment()->date > $now->getTimestamp()) {
                $conflictList->addEntity(clone $process);
            }
        }
        return $conflictList;
    }

    public function withinExactDate(\DateTimeInterface $now)
    {
        $processList = new self();
        foreach ($this as $process) {
            if ($process->getFirstAppointment()->toDateTime()->format('Y-m-d') == $now->format('Y-m-d')) {
                $processList->addEntity(clone $process);
            }
        }
        return $processList;
    }

    public function withoutDublicatedConflicts()
    {
        $collection = new self();
        foreach ($this as $conflict) {
            if (! $collection->getAppointmentList()->hasAppointment($conflict->getFirstAppointment())) {
                $collection->addEntity(clone $conflict);
            }
        }
        return $collection;
    }

    public function testProcessListLength($processList, bool $isEmptyAllowed = false): ProcessList
    {
        $collection = ($processList instanceof Process) ?
            (new self())->addEntity($processList) :
            $processList;

        if (0 === $collection->count() && ! $isEmptyAllowed) {
            throw new \BO\Zmsentities\Exception\ProcessListEmpty();
        }
        return $collection;
    }

    public function withoutProcessByStatus($process, $status)
    {
        $collection = clone $this;
        $collection = (1 <= $collection->count() && ! Messaging::isEmptyProcessListAllowed($status)) ?
            $collection->withOutProcessId($process->getId()) :
            $collection;
        return $collection;
    }

    /*
    * reduce process list to items with appointment time in range of given appointment
    */
    public function withTimeRangeByAppointment(\BO\Zmsentities\Appointment $appointment)
    {
        $processList = new self();
        if ($this->count()) {
            foreach ($this as $process) {
                if ($appointment->getEndTime() > $process->getFirstAppointment()->getStartTime() &&
                    $appointment->getStartTime() < $process->getFirstAppointment()->getEndTime()
                ) {
                    $processList->addEntity(clone $process);
                }
            }
        }
        return $processList;
    }
}
