<?php

namespace BO\Zmsbackend\Process\Service;

use BO\Zmsentities\Process as Entity;
use BO\Zmsentities\Collection\ProcessList as Collection;

/**
 * @SuppressWarnings(Coupling)
 */
class ProcessStatusFree extends Process
{
    /**
     * @return (\BO\Zmsbackend\Day\Service\Day|\BO\Zmsentities\Calendar|array)[]
     *
     */
    private function prepareCalendarAndDays(
        \BO\Zmsentities\Calendar $calendar,
        \DateTimeInterface $now,
        int|null $slotsRequired = null
    ): array {
        $calendar = (new \BO\Zmsbackend\Calendar\Service\Calendar())->readResolvedEntity($calendar, $now, true);
        $dayquery = new \BO\Zmsbackend\Day\Service\Day();
        $dayquery->writeTemporaryScopeList($calendar, $slotsRequired);

        return [$calendar, $dayquery, $this->buildDaysList($calendar)];
    }

    private function buildDaysList(\BO\Zmsentities\Calendar $calendar): array
    {
        $selectedDate = $calendar->getFirstDay();
        $days = [$selectedDate];
        if ($calendar->getLastDay(false)) {
            $days = [];
            while ($selectedDate <= $calendar->getLastDay(false)) {
                $days[] = $selectedDate;
                $selectedDate = $selectedDate->modify('+1 day');
            }
        }

        return $days;
    }

    /**
     * Prefer concrete days already on the calendar (e.g. bookable days only).
     * Falls back to the full firstDay→lastDay range when no days are set.
     */
    private function buildDaysListFromCalendarDays(\BO\Zmsentities\Calendar $calendar): array
    {
        if (!isset($calendar->days) || count($calendar->days) < 1) {
            return $this->buildDaysList($calendar);
        }

        $daysByDate = [];
        foreach ($calendar->days as $day) {
            if (!$day instanceof \BO\Zmsentities\Day) {
                $day = new \BO\Zmsentities\Day($day);
            }
            $dateTime = $day->toDateTime();
            $daysByDate[$dateTime->format('Y-m-d')] = $dateTime;
        }

        if ($daysByDate === []) {
            return $this->buildDaysList($calendar);
        }

        ksort($daysByDate);

        return array_values($daysByDate);
    }

    public function readFreeProcessesMinimalFromPreparedCalendar(
        \BO\Zmsentities\Calendar $calendar,
        string $slotType = 'public',
        ?int $slotsRequired = null,
        bool $groupData = false
    ): array {
        $days = $this->buildDaysListFromCalendarDays($calendar);
        if ($days === []) {
            return [];
        }

        $processData = $this->getProcessDataHandle(
            $days,
            $slotType,
            $slotsRequired,
            $groupData,
            true
        );

        $processInfos = [];
        while ($item = $processData->fetch(\PDO::FETCH_ASSOC)) {
            $processInfo = $this->extractProcessInfo($item, $calendar);
            if ($processInfo) {
                $processInfos[] = $processInfo;
            }
        }

        $processData->closeCursor();

        return $this->deduplicateWithRoundRobin($processInfos);
    }

    private function getProcessDataHandle(
        array $days,
        string $slotType,
        int|null $slotsRequired,
        bool $groupData,
        bool $useAvailabilityQuery = false
    ) {
        $query = $useAvailabilityQuery
            ? \BO\Zmsbackend\Process\Repository\ProcessStatusFree::QUERY_SELECT_PROCESSLIST_DAYS_AVAILABILITY
            : \BO\Zmsbackend\Process\Repository\ProcessStatusFree::QUERY_SELECT_PROCESSLIST_DAYS;

        return $this->fetchHandle(
            sprintf(
                $query,
                \BO\Zmsbackend\Process\Repository\ProcessStatusFree::buildDaysCondition($days)
            )
            . ($groupData ? \BO\Zmsbackend\Process\Repository\ProcessStatusFree::GROUPBY_SELECT_PROCESSLIST_DAY : ''),
            [
                'slotType' => $slotType,
                'forceRequiredSlots' =>
                    ($slotsRequired === null || $slotsRequired < 1) ? 1 : intval($slotsRequired),
            ]
        );
    }

    public function readFreeProcesses(
        \BO\Zmsentities\Calendar $calendar,
        \DateTimeInterface $now,
        string $slotType = 'public',
        int|null $slotsRequired = null,
        bool $groupData = false
    ): Collection {
        list($calendar, $dayquery, $days) = $this->prepareCalendarAndDays($calendar, $now, $slotsRequired);
        $processData = $this->getProcessDataHandle($days, $slotType, $slotsRequired, $groupData);
        $processList = new Collection();
        $scopeList = [];
        while ($item = $processData->fetch(\PDO::FETCH_ASSOC)) {
            $process = new \BO\Zmsentities\Process($item);
            $process->requests = $calendar->requests;
            $process->appointments->getFirst()->setDateByString(
                $process->appointments->getFirst()->date,
                'Y-m-d H:i:s'
            );

            if (! isset($scopeList[$process->scope->id])) {
                $scopeList[$process->scope->id] = $calendar->scopes->getEntity($process->scope->id);
            }

            $process->scope = $scopeList[$process->scope->id];
            $process->queue['withAppointment'] = 1;
            $process->appointments->getFirst()->scope = $process->scope;
            $processList->addEntity($process);
        }
        $processData->closeCursor();
        unset($dayquery);
        return $processList;
    }

    public function readFreeProcessesMinimalDeduplicated(
        \BO\Zmsentities\Calendar $calendar,
        \DateTimeInterface $now,
        string $slotType = 'public',
        ?int $slotsRequired = null,
        bool $groupData = false
    ): array {
        list($calendar, $dayquery, $days) = $this->prepareCalendarAndDays($calendar, $now, $slotsRequired);
        $processData = $this->getProcessDataHandle($days, $slotType, $slotsRequired, $groupData);

        $processInfos = [];
        while ($item = $processData->fetch(\PDO::FETCH_ASSOC)) {
            $processInfo = $this->extractProcessInfo($item, $calendar);
            if ($processInfo) {
                $processInfos[] = $processInfo;
            }
        }

        $processData->closeCursor();
        unset($dayquery);

        return $this->deduplicateWithRoundRobin($processInfos);
    }

    /**
     * Keep one free process per round-robin group + timestamp.
     *
     * Default group is the provider id. When provider.data.sharedBookingOfficeIds
     * is set, all peer providers share one group so the same wall-clock slot is
     * offered once and successive timeslots round-robin across eligible scopes
     * of every peer (ZMSKVR-1046). Scopes that cannot fit the slot never appear
     * here, so fall-through is preserved.
     *
     * @param array<int, array<string, mixed>> $processInfos
     * @return array<int, array<string, mixed>>
     */
    private function deduplicateWithRoundRobin(array $processInfos): array
    {
        // Composite key: round-robin group (provider or shared-office set) + slot timestamp.
        $candidatesByGroupTimestampKey = [];
        $groupTimestampKeyOrder = [];
        foreach ($processInfos as $processInfo) {
            $roundRobinGroupKey = self::resolveRoundRobinGroupKey(
                (string) $processInfo['providerId'],
                $processInfo['sharedBookingOfficeIds'] ?? null
            );
            $groupTimestampKey = $roundRobinGroupKey . '_' . $processInfo['date'];
            if (!isset($candidatesByGroupTimestampKey[$groupTimestampKey])) {
                $groupTimestampKeyOrder[] = $groupTimestampKey;
                $candidatesByGroupTimestampKey[$groupTimestampKey] = [];
            }
            $candidatesByGroupTimestampKey[$groupTimestampKey][] = $processInfo;
        }

        $roundRobinIndexByGroup = [];
        $deduplicatedProcesses = [];
        foreach ($groupTimestampKeyOrder as $groupTimestampKey) {
            $candidates = self::uniqueCandidatesSortedByScopeId(
                $candidatesByGroupTimestampKey[$groupTimestampKey]
            );
            $roundRobinGroupKey = self::resolveRoundRobinGroupKey(
                (string) $candidates[0]['providerId'],
                $candidates[0]['sharedBookingOfficeIds'] ?? null
            );
            $roundRobinTimeslotIndex = $roundRobinIndexByGroup[$roundRobinGroupKey] ?? 0;
            $chosenCandidate = $candidates[
                self::pickRoundRobinIndex($roundRobinTimeslotIndex, count($candidates))
            ];
            $roundRobinIndexByGroup[$roundRobinGroupKey] = $roundRobinTimeslotIndex + 1;
            $deduplicatedProcesses[] = $this->createMinimalProcess($chosenCandidate);
        }

        return $deduplicatedProcesses;
    }

    /**
     * @param array<int, array<string, mixed>> $candidates
     * @return array<int, array<string, mixed>>
     */
    private static function uniqueCandidatesSortedByScopeId(array $candidates): array
    {
        $candidatesByScopeId = [];
        foreach ($candidates as $candidate) {
            $candidatesByScopeId[(string) $candidate['scopeId']] = $candidate;
        }
        $uniqueCandidates = array_values($candidatesByScopeId);
        usort(
            $uniqueCandidates,
            static fn (array $left, array $right): int =>
                ((int) $left['scopeId']) <=> ((int) $right['scopeId'])
        );

        return $uniqueCandidates;
    }

    /**
     * @internal Exposed for unit tests.
     * @param array<int, int|string>|null $sharedBookingOfficeIds
     */
    public static function resolveRoundRobinGroupKey(
        string $providerId,
        ?array $sharedBookingOfficeIds
    ): string {
        if (!is_array($sharedBookingOfficeIds) || $sharedBookingOfficeIds === []) {
            return $providerId;
        }

        $sortedSharedOfficeIds = array_map('intval', $sharedBookingOfficeIds);
        sort($sortedSharedOfficeIds, SORT_NUMERIC);

        return implode(',', $sortedSharedOfficeIds);
    }

    /**
     * @internal Exposed for unit tests.
     */
    public static function pickRoundRobinIndex(int $timeslotIndex, int $candidateCount): int
    {
        if ($candidateCount < 1) {
            throw new \InvalidArgumentException('candidateCount must be >= 1');
        }

        return $timeslotIndex % $candidateCount;
    }

    private function extractProcessInfo(array $item, \BO\Zmsentities\Calendar $calendar): ?array
    {
        $scopeId = $item['scope__id'] ?? null;
        $dateString = $item['appointments__0__date'] ?? null;

        if (!$scopeId || !$dateString) {
            return null;
        }

        $date = strtotime($dateString);
        if (!$date) {
            return null;
        }

        $scope = $calendar->scopes->getEntity($scopeId);
        if (!$scope) {
            return null;
        }

        $providerId = $scope->getProviderId();
        if (!$providerId) {
            return null;
        }

        $sharedBookingOfficeIds = null;
        $provider = $scope->getProvider();
        if (
            $provider
            && isset($provider->data['sharedBookingOfficeIds'])
            && is_array($provider->data['sharedBookingOfficeIds'])
            && $provider->data['sharedBookingOfficeIds'] !== []
        ) {
            $sharedBookingOfficeIds = array_map('intval', $provider->data['sharedBookingOfficeIds']);
        }

        return [
            'scopeId' => $scopeId,
            'source' => $scope->getSource(),
            'providerId' => $providerId,
            'sharedBookingOfficeIds' => $sharedBookingOfficeIds,
            'date' => $date
        ];
    }

    private function createMinimalProcess(array $processInfo): array
    {
        return [
            '$schema' => 'https://schema.berlin.de/queuemanagement/process.json',
            'scope' => [
                'id' => $processInfo['scopeId'],
                'source' => $processInfo['source'],
                'provider' => [
                    'id' => $processInfo['providerId'],
                    'source' => $processInfo['source'],
                ]
            ],
            'appointments' => [
                [
                    'date' => (string)$processInfo['date'],
                    'scope' => [
                        'id' => $processInfo['scopeId'],
                        'source' => $processInfo['source'],
                        'provider' => [
                            'id' => $processInfo['providerId'],
                            'source' => $processInfo['source'],
                        ]
                    ]
                ]
            ]
        ];
    }

    public function readReservedProcesses($resolveReferences = 2): Collection
    {
        $processList = new Collection();
        $query = new \BO\Zmsbackend\Process\Repository\Process(\BO\Zmsbackend\Query\Base::SELECT);
        $query
            ->addResolvedReferences($resolveReferences)
            ->addEntityMapping()
            ->addConditionAssigned()
            ->addConditionIsReserved();
        $resultData = $this->fetchList($query, new Entity());
        foreach ($resultData as $process) {
            if (2 == $resolveReferences) {
                $process->requests = (new \BO\Zmsbackend\Request\Service\Request())->readRequestByProcessId($process->id, $resolveReferences);
                $process->scope = (new \BO\Zmsbackend\Scope\Service\Scope())->readEntity($process->getScopeId(), $resolveReferences);
            }
            if ($process instanceof Entity) {
                $processList->addEntity($process);
            }
        }
        return $processList;
    }

    /**
     * Insert a new process if there are free slots
     *
     * @param \BO\Zmsentities\Process $process
     * @param \DateTimeInterface $now
     * @param String $slotType
     * @param Int $slotsRequired we cannot use process.appointments.0.slotCount, because setting slotsRequired is
     *        a priviliged operation. Just using the input would be a security flaw to get a wider selection of times
     *        If slotsRequired = 0, readFreeProcesses() uses the slotsRequired based on request-provider relation
     */
    public function writeEntityReserved(
        \BO\Zmsentities\Process $process,
        \DateTimeInterface $now,
        string $slotType = "public",
        int $slotsRequired = 0,
        int $resolveReferences = 0,
        ?\BO\Zmsentities\Useraccount $userAccount = null
    ): ?\BO\Zmsentities\Process {
        $maxAttempts = 3;
        $attempt = 0;
        while (true) {
            try {
                return $this->writeEntityReservedAttempt(
                    $process,
                    $now,
                    $slotType,
                    $slotsRequired,
                    $resolveReferences,
                    $userAccount
                );
            } catch (\BO\Zmsbackend\Exception\Pdo\DeadLockFound $exception) {
                $attempt++;
                if ($attempt >= $maxAttempts) {
                    throw $exception;
                }
                $this->resetWriteTransactionAfterDeadlock();
                usleep(50000 * $attempt);
            }
        }
    }

    protected function writeEntityReservedAttempt(
        \BO\Zmsentities\Process $process,
        \DateTimeInterface $now,
        string $slotType = "public",
        int $slotsRequired = 0,
        int $resolveReferences = 0,
        ?\BO\Zmsentities\Useraccount $userAccount = null
    ): ?\BO\Zmsentities\Process {
        $process = clone $process;
        $process->status = 'reserved';
        $appointment = $process->getAppointments()->getFirst();
        $slotList = (new \BO\Zmsbackend\Slot\Service\Slot())->readByAppointment(
            $appointment,
            $slotsRequired,
            (null !== $userAccount),
            true
        );
        $freeProcessList = $this->readFreeProcesses($process->toCalendar(), $now, $slotType, $slotsRequired);

        if (!$freeProcessList->getAppointmentList()->hasAppointment($appointment) || ! $slotList) {
            throw new \BO\Zmsbackend\Process\Exception\ProcessReserveFailed();
        }

        foreach ($slotList as $slot) {
            if ($process->id > 99999) {
                $newProcess = clone $process;
                $newProcess->getFirstAppointment()->setTime($slot->time);
                $this->writeNewProcess($newProcess, $now, $process->id, 0, true, $userAccount);
            } elseif ($process->id === 0) {
                $process = $this->writeNewProcess($process, $now, 0, count($slotList) - 1, true, $userAccount);
            } else {
                throw new \Exception("SQL UPDATE error on inserting new $process on $slot");
            }
        }
        $this->writeRequestsToDb($process);
        return $this->readEntity($process->getId(), new \BO\Zmsbackend\Helper\NoAuth(), $resolveReferences);
    }

    /**
     * After InnoDB aborts a transaction on deadlock, clear PDO state and start a fresh transaction.
     */
    protected function resetWriteTransactionAfterDeadlock(): void
    {
        $connection = \BO\Zmsbackend\Connection\Select::getWriteConnection();
        if ($connection->inTransaction()) {
            try {
                $connection->rollBack();
            } catch (\PDOException $exception) {
                \App::$log->warning('Rollback after deadlock failed', [
                    'error' => $exception->getMessage(),
                ]);
            }
        }
        if (!$connection->inTransaction()) {
            $connection->beginTransaction();
        }
    }
}
