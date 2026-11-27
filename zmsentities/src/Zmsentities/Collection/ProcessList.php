<?php

namespace BO\Zmsentities\Collection;

use BO\Zmsentities\Helper\Sorter;
use BO\Zmsentities\Helper\Property;
use BO\Zmsentities\Helper\Messaging;
use BO\Zmsentities\Process;
use BO\Zmsentities\Slot;

/**
 * @SuppressWarnings(Complexity)
 * @SuppressWarnings(Public)
 * @SuppressWarnings(Coupling)
 *
 * @extends Base<\BO\Zmsentities\Process>
 */
class ProcessList extends Base
{
    public const string ENTITY_CLASS = '\BO\Zmsentities\Process';

    public function toProcessListByTime(string|null $format = null): self
    {
        $list = new self();
        foreach ($this as $process) {
            $appointment = $process->getFirstAppointment();
            $formattedDate = $appointment['date'];
            if ($format !== null && $format !== '') {
                $formattedDate = $appointment->toDateTime()->format($format);
            }
            $list[$formattedDate][] = clone $process;
        }
        return $list;
    }

    public function withRequest(mixed $requestId): self
    {
        $list = new self();
        foreach ($this as $process) {
            if ($process->requests->hasEntity($requestId)) {
                $list->addEntity(clone $process);
            }
        }
        return $list;
    }

    public function sortByScopeName(): static
    {
        $this->uasort(function ($a, $b) {
            return strcmp(
                Sorter::toSortableString(ucfirst($a->scope['contact']['name'] ?? '')),
                Sorter::toSortableString(ucfirst($b->scope['contact']['name'] ?? ''))
            );
        });
        return $this;
    }

    public function sortByAppointmentDate(): static
    {
        $this->uasort(function ($a, $b) {
            return ((int) $a->getFirstAppointment()->date - (int) $b->getFirstAppointment()->date);
        });
        return $this;
    }

    public function sortByArrivalTime(): static
    {
        $this->uasort(function ($a, $b): mixed {
            return ($a->queue['arrivalTime'] - $b->queue['arrivalTime']);
        });
        return $this;
    }

    public function sortByEstimatedWaitingTime(): static
    {
        $this->uasort(function ($a, $b): mixed {
            return ($a->queue['waitingTimeEstimate'] - $b->queue['waitingTimeEstimate']);
        });
        return $this;
    }

    public function sortByClientName(): static
    {
        $this->uasort(function ($a, $b) {
            return strcmp(
                Sorter::toSortableString($a->getFirstClient()['familyName']),
                Sorter::toSortableString($b->getFirstClient()['familyName'])
            );
        });
        return $this;
    }

    public function sortByTimeKey(): static
    {
        $this->uksort(function ($a, $b): mixed {
            return ((int) $a - (int) $b);
        });
        return $this;
    }

    public function toProcessListByStatusList(array $statusList): self
    {
        $collection = new self();
        foreach ($this as $process) {
            if (in_array($process->getStatus(), $statusList)) {
                $collection->addEntity($process);
            }
        }
        return $collection;
    }

    /**
     * @return ((mixed|null)[][]|mixed)[][][]
     *
     */
    public function toConflictListByDay(): array
    {
        $list = [];
        $oldList = clone $this;
        foreach ($oldList as $process) {
            $appointmentList = [];
            if (!isset($list[$process->getFirstAppointment()->getStartTime()->format('Y-m-d')])) {
                $list[$process->getFirstAppointment()->getStartTime()->format('Y-m-d')] = [];
            }
            foreach ($process->getAppointments() as $appointment) {
                $availability = $appointment->getAvailability();
                $availabilityId = $availability->getId()
                    ? $availability->getId()
                    : ($availability['tempId'] ?? null);
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

    public function getScopeList(): \BO\Zmsentities\Collection\ScopeList
    {
        $list = new ScopeList();
        foreach ($this as $process) {
            if (Property::__keyExists('scope', $process)) {
                $list[] = new \BO\Zmsentities\Scope($process['scope']);
            }
        }
        return $list->withUniqueScopes();
    }

    public function getRequestList(): \BO\Zmsentities\Collection\RequestList
    {
        $list = new RequestList();
        foreach ($this as $process) {
            if (Property::__keyExists('requests', $process)) {
                $list->addList($process->getRequests());
            }
        }
        return $list->withUniqueRequests();
    }

    public function getAppointmentList(): AppointmentList
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

    public function setTempAppointmentToProcess(mixed $dateTime, mixed $scopeId): static
    {
        $addedAppointment = false;
        $appointment = (new \BO\Zmsentities\Appointment())->addDate($dateTime->getTimestamp())->addScope($scopeId);
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

    public function toQueueList(mixed $now): QueueList
    {
        $queueList = new QueueList();
        foreach ($this as $process) {
            $queue = $process->toQueue($now);
            $queueList->addEntity($queue);
        }
        $queueList->setTransferedProcessList(true);
        return $queueList;
    }

    public function withAvailability(\BO\Zmsentities\Availability $availability): static
    {
        $processList = new static();
        foreach ($this as $process) {
            if ($availability->hasAppointment($process->getFirstAppointment())) {
                $processList[] = clone $process;
            }
        }
        return $processList;
    }

    public function withAvailabilityStrict(\BO\Zmsentities\Availability $availability): static
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

    public function withDepartmentHasMailFrom(): static
    {
        $processList = new static();
        foreach ($this as $process) {
            if ($process->getCurrentScope()->hasEmailFrom()) {
                $entity = clone $process;
                $processList->addEntity($entity);
            }
        }
        return $processList;
    }



    public function setConflictAmendment(): static
    {
        foreach ($this as $process) {
            $process->amendment = 'Die Slots für diesen Zeitraum wurden überbucht';
            if (! $process->getFirstAppointment()->availability->hasId()) {
                $process->amendment = sprintf(
                    'Der Vorgang (%s) befindet sich außerhalb der Öffnungszeit!',
                    $process->getId()
                );
            }
        }
        return $this;
    }

    public function withOutAvailability(AvailabilityList $availabilityList): static
    {
        return $this->getAvailabilitySlotUsage($availabilityList)['conflicts'];
    }

    /**
     * @return array<int|string, int>
     */
    public function getCalculatedSlotCountByAvailability(AvailabilityList $availabilityList): array
    {
        return $this->getAvailabilitySlotUsage($availabilityList)['busySlots'];
    }

    /**
     * Count demand by booked interval for the graph, including bookings that cannot be allocated.
     * Count compatible slots per availability and report bookings with missing or full slots as conflicts.
     * The graph passes one day; occupiedByTime totals matching clock times if given multiple days.
     *
     * @return array{busySlots: array<int|string, int>, occupiedByTime: array<string, int>, conflicts: static}
     */
    public function getAvailabilitySlotUsage(AvailabilityList $availabilityList): array
    {
        $busySlots = array_fill_keys(array_keys($availabilityList->getSortedByDuration()), 0);
        $occupiedByTime = [];
        $conflicts = new static();
        foreach ($this->toProcessListByTime('Y-m-d') as $processListByDate) {
            $dateTime = $processListByDate[0]->getFirstAppointment()->toDateTime();
            $dayEnd = $dateTime->modify('tomorrow midnight');
            $availabilities = $availabilityList->withType('appointment')->withDateTime($dateTime)->getSortedByDuration();
            $slotsByTime = [];
            $slotLengths = [];
            foreach ($availabilities as $key => $availability) {
                $slotLengths[$key] = (int) $availability->getSlotTimeInMinutes();
                foreach ($availability->getSlotList() as $slot) {
                    $slotsByTime[$slot->getTimeString()][$key] = $slot;
                }
            }
            // Sweep booking starts and ends together with the graph's configured slot times.
            $demandChanges = array_fill_keys(array_keys($slotsByTime), 0);
            foreach ($processListByDate as $process) {
                $appointment = $process->getFirstAppointment();
                $bookedAvailability = $appointment->getAvailability();
                $bookedKey = $bookedAvailability->id ?: ($bookedAvailability->tempId ?? null);
                $start = $appointment->getStartTime();
                $startClock = $start->format('H:i');
                $slotCount = max(1, (int) $appointment->getSlotCount());
                $slotMinutes = (int) $bookedAvailability->getSlotTimeInMinutes();
                if ($slotMinutes <= 0) {
                    $startKey = array_key_first($slotsByTime[$startClock] ?? []);
                    $referencedLength = $bookedKey !== null ? ($slotLengths[$bookedKey] ?? 0) : 0;
                    $slotMinutes = $referencedLength ?: ($startKey !== null ? $slotLengths[$startKey] : 0);
                }
                if ($slotMinutes > 0) {
                    $end = $start->add(new \DateInterval('PT' . ($slotCount * $slotMinutes) . 'M'));
                    $endClock = $end < $dayEnd ? $end->format('H:i') : '24:00';
                    $demandChanges[$startClock] = ($demandChanges[$startClock] ?? 0) + 1;
                    $demandChanges[$endClock] = ($demandChanges[$endClock] ?? 0) - 1;
                }
                $options = static::getAppointmentSlotOptions(
                    $start,
                    $slotCount,
                    $slotMinutes,
                    $slotsByTime,
                    $slotLengths
                );
                $hasConflict = count($options) !== $slotCount;
                foreach (static::selectAppointmentSlots($options, $bookedKey) as $time => $key) {
                    if ($key === null) {
                        $hasConflict = true;
                        continue;
                    }
                    $busySlots[$key] = ($busySlots[$key] ?? 0) + 1;
                    $slot = $options[$time][$key];
                    if ($slot->intern > 0) {
                        $slot->removeAppointment();
                    } else {
                        $hasConflict = true;
                    }
                }
                if ($hasConflict) {
                    $process->amendment = '';
                    $conflicts[] = $process;
                }
            }
            ksort($demandChanges);
            $demand = 0;
            foreach ($demandChanges as $time => $change) {
                $demand += $change;
                if ($demand > 0 && isset($slotsByTime[$time])) {
                    $occupiedByTime[$time] = ($occupiedByTime[$time] ?? 0) + $demand;
                }
            }
        }
        return ['busySlots' => $busySlots, 'occupiedByTime' => $occupiedByTime, 'conflicts' => $conflicts];
    }

    /**
     * @param array<string, array<int|string, Slot>> $slotsByTime
     * @param array<int|string, int> $slotLengths
     * @return array<string, array<int|string, Slot>> candidates for each required slot time
     */
    protected static function getAppointmentSlotOptions(
        \DateTimeImmutable $start,
        int $slotCount,
        int $slotMinutes,
        array $slotsByTime,
        array $slotLengths
    ): array {
        if ($slotMinutes <= 0) {
            return [];
        }
        $compatible = array_filter($slotLengths, static fn (int $length) => $length === $slotMinutes);
        $interval = new \DateInterval('PT' . $slotMinutes . 'M');
        $dayEnd = $start->modify('tomorrow midnight');
        $options = [];
        for ($step = 0, $time = $start; $step < $slotCount && $time < $dayEnd; $step++, $time = $time->add($interval)) {
            $clock = $time->format('H:i');
            $options[$clock] = array_intersect_key($slotsByTime[$clock] ?? [], $compatible);
        }
        return $options;
    }

    /**
     * Prefer a single availability for the whole booking, then choose a compatible slot at each time.
     * Keep full slots in the result so they still count toward the availability's booked total.
     *
     * @param array<string, array<int|string, Slot>> $options
     * @return array<string, int|string|null> availability by slot time, null for a missing slot
     */
    protected static function selectAppointmentSlots(array $options, int|string|null $bookedKey): array
    {
        $covering = $freeEverywhere = reset($options) ?: [];
        $free = [];
        foreach ($options as $time => $slots) {
            $free[$time] = array_filter($slots, static fn (Slot $slot) => $slot->intern > 0);
            $freeEverywhere = array_intersect_key($freeEverywhere, $free[$time]);
            $covering = array_intersect_key($covering, $slots);
        }
        $previousKey = static::preferredSlotKey($freeEverywhere, $bookedKey) ?? $bookedKey;
        $coveringKey = static::preferredSlotKey($covering, $bookedKey);
        $selected = [];
        foreach ($options as $time => $slots) {
            $selected[$time] = static::preferredSlotKey($free[$time], $previousKey)
                ?? static::preferredSlotKey($slots, $coveringKey ?? $previousKey);
            $previousKey = $selected[$time] ?? $previousKey;
        }
        return $selected;
    }

    /** @param array<int|string, Slot> $slots */
    protected static function preferredSlotKey(array $slots, int|string|null $preferred): int|string|null
    {
        return ($preferred !== null && isset($slots[$preferred])) ? $preferred : array_key_first($slots);
    }

    public function withUniqueScope(bool $oncePerHour = false): static
    {
        $processList = new static();
        $scopeKeyList = [];
        foreach ($this as $process) {
            $scopeKey = $process->scope['id'] . '-';
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

    public function withAccess(\BO\Zmsentities\Useraccount $useraccount): static
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

    /** @psalm-api */
    public function withScopeId(int|string $scopeId): static
    {
        $processList = new static();
        foreach ($this as $process) {
            if ($scopeId == $process->getScopeId()) {
                $processList[] = clone $process;
            }
        }
        return $processList;
    }

    /** @psalm-api */
    public function withOutScopeId(int|string $scopeId): static
    {
        $processList = new static();
        foreach ($this as $process) {
            if ($scopeId != $process->getScopeId()) {
                $processList[] = clone $process;
            }
        }
        return $processList;
    }

    public function withOutProcessId(mixed $processId): static
    {
        $processList = new static();
        foreach ($this as $process) {
            if ($processId != $process->getId()) {
                $processList[] = clone $process;
            }
        }
        return $processList;
    }

    public function withoutExpiredAppointmentDate(\DateTimeInterface $now): self
    {
        $conflictList = new self();
        foreach ($this as $process) {
            if ($process->getFirstAppointment()->date > $now->getTimestamp()) {
                $conflictList->addEntity(clone $process);
            }
        }
        return $conflictList;
    }

    public function withinExactDate(\DateTimeInterface $now): self
    {
        $processList = new self();
        foreach ($this as $process) {
            if ($process->getFirstAppointment()->toDateTime()->format('Y-m-d') == $now->format('Y-m-d')) {
                $processList->addEntity(clone $process);
            }
        }
        return $processList;
    }

    public function withoutDublicatedConflicts(): self
    {
        $collection = new self();
        foreach ($this as $conflict) {
            if (! $collection->getAppointmentList()->hasAppointment($conflict->getFirstAppointment())) {
                $collection->addEntity(clone $conflict);
            }
        }
        return $collection;
    }

    /**
     * @param Process|self $processList
     */
    public function testProcessListLength(self|Process $processList, bool $isEmptyAllowed = false): ProcessList
    {
        if ($processList instanceof Process) {
            $collection = new self();
            $collection->addEntity($processList);
        } else {
            $collection = $processList;
        }

        if (0 === $collection->count() && ! $isEmptyAllowed) {
            throw new \BO\Zmsentities\Exception\ProcessListEmpty();
        }
        return $collection;
    }

    public function withoutProcessByStatus(Process $process, string $status): static
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
    public function withTimeRangeByAppointment(\BO\Zmsentities\Appointment $appointment): self
    {
        $processList = new self();
        if ($this->count()) {
            foreach ($this as $process) {
                if (
                    $appointment->getEndTime() > $process->getFirstAppointment()->getStartTime() &&
                    $appointment->getStartTime() < $process->getFirstAppointment()->getEndTime()
                ) {
                    $processList->addEntity(clone $process);
                }
            }
        }
        return $processList;
    }
}
