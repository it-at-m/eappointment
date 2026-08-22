<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Repository;

use BO\Zmscitizenbackend\Connection\Pdo;
use BO\Zmscitizenbackend\Exceptions\AppointmentNotAvailable;
use BO\Zmscitizenbackend\Utils\ClientIpHelper;
use BO\Zmsentities\Calendar as CalendarEntity;

/**
 * @SuppressWarnings(Coupling)
 * @SuppressWarnings(TooManyMethods)
 */
class AppointmentReserveEngine
{
    private const int MAX_DEADLOCK_ATTEMPTS = 3;

    public function __construct(private Pdo $pdo)
    {
    }

    /**
     * @param list<int|string> $serviceIds
     * @param list<int|string> $serviceCounts
     * @return array{processId: int, authKey: string}
     */
    public function reserve(
        int $officeId,
        array $serviceIds,
        array $serviceCounts,
        int $timestamp
    ): array {
        $attempt = 0;
        while (true) {
            try {
                return $this->reserveAttempt($officeId, $serviceIds, $serviceCounts, $timestamp);
            } catch (\PDOException $exception) {
                $attempt++;
                if ($attempt >= self::MAX_DEADLOCK_ATTEMPTS || !$this->isDeadlock($exception)) {
                    throw $exception;
                }
                $this->rollbackQuietly();
                usleep(50000 * $attempt);
            }
        }
    }

    /**
     * @param list<int|string> $serviceIds
     * @param list<int|string> $serviceCounts
     * @return array{processId: int, authKey: string}
     */
    private function reserveAttempt(
        int $officeId,
        array $serviceIds,
        array $serviceCounts,
        int $timestamp
    ): array {
        $day = (new \DateTimeImmutable('@' . $timestamp))
            ->setTimezone(\App::$now->getTimezone())
            ->format('Y-m-d');
        $calendarEngine = new AvailableCalendarEngine($this->pdo);

        $startedTransaction = false;
        try {
            if (!$this->pdo->inTransaction()) {
                $this->pdo->beginTransaction();
                $startedTransaction = true;
            }

            $calendar = $calendarEngine->prepareBookingCalendar(
                (string) $officeId,
                implode(',', array_map('strval', $serviceIds)),
                implode(',', array_map('strval', $serviceCounts)),
                $day
            );
            $match = $this->findMatchingAppointment($calendarEngine, $calendar, $timestamp);
            $slotTimes = $this->lockSlotTimes(
                (int) $match['scopeId'],
                $timestamp,
                (int) $match['slotCount']
            );
            $this->assertStillFree($calendarEngine, $calendar, $timestamp);

            $credentials = $this->writeReservedProcess($calendar, $match, $slotTimes, $timestamp);

            if ($startedTransaction) {
                $this->pdo->commit();
            }

            return $credentials;
        } catch (\Exception $exception) {
            if ($startedTransaction) {
                $this->rollbackQuietly();
            }
            throw $exception;
        } finally {
            $calendarEngine->dropTemporaryScopeList();
        }
    }

    /**
     * @return array{scopeId: int, slotCount: int, source: string}
     */
    private function findMatchingAppointment(
        AvailableCalendarEngine $calendarEngine,
        CalendarEntity $calendar,
        int $timestamp
    ): array {
        $processes = $calendarEngine->readDeduplicatedFreeProcesses(
            $calendar,
            'public',
            0,
            false
        );
        foreach ($processes as $process) {
            $appointment = $process['appointments'][0] ?? [];
            if ((int) ($appointment['date'] ?? 0) !== $timestamp) {
                continue;
            }

            return [
                'scopeId' => (int) ($process['scope']['id'] ?? 0),
                'slotCount' => max(1, (int) ($appointment['slotCount'] ?? 1)),
                'source' => (string) ($process['scope']['source'] ?? 'dldb'),
            ];
        }

        throw new AppointmentNotAvailable();
    }

    /**
     * @return list<string>
     */
    private function lockSlotTimes(int $scopeId, int $timestamp, int $slotCount): array
    {
        $appointment = (new \DateTimeImmutable('@' . $timestamp))
            ->setTimezone(\App::$now->getTimezone());
        $startSlot = $this->pdo->fetchOne(AppointmentReserveQueries::QUERY_SELECT_START_SLOT, [
            'scopeId' => $scopeId,
            'year' => $appointment->format('Y'),
            'month' => $appointment->format('m'),
            'day' => $appointment->format('d'),
            'time' => $appointment->format('H:i:s'),
        ]);
        if (!is_array($startSlot) || $startSlot === []) {
            throw new AppointmentNotAvailable();
        }

        $hierarchy = $this->pdo->fetchAll(AppointmentReserveQueries::QUERY_SELECT_SLOT_HIERARCHY, [
            'startSlotId' => $startSlot['slotID'],
            'slotCount' => $slotCount,
        ]);
        $hierarchy = is_array($hierarchy) ? $hierarchy : [];
        if ($hierarchy === []) {
            $hierarchy = [[
                'slotID' => $startSlot['slotID'],
                'time' => $startSlot['time'],
                'ancestorLevel' => 1,
            ]];
        }
        if (count($hierarchy) < $slotCount) {
            throw new AppointmentNotAvailable();
        }

        $slotIds = [];
        $times = [];
        foreach (array_slice($hierarchy, 0, $slotCount) as $row) {
            $slotIds[] = (int) $row['slotID'];
            $times[] = (string) $row['time'];
        }

        [$placeholders, $params] = AvailableCalendarQueries::idPlaceholders('slot', $slotIds);
        $this->pdo->fetchAll(
            'SELECT slotID FROM slot WHERE slotID IN (' . $placeholders . ') ORDER BY slotID ASC FOR UPDATE',
            $params
        );

        return $times;
    }

    private function assertStillFree(
        AvailableCalendarEngine $calendarEngine,
        CalendarEntity $calendar,
        int $timestamp
    ): void {
        $this->findMatchingAppointment($calendarEngine, $calendar, $timestamp);
    }

    /**
     * @param array{scopeId: int, slotCount: int, source: string} $match
     * @param list<string> $slotTimes
     * @return array{processId: int, authKey: string}
     */
    private function writeReservedProcess(
        CalendarEntity $calendar,
        array $match,
        array $slotTimes,
        int $timestamp
    ): array {
        $now = \App::$now instanceof \DateTimeInterface ? \App::$now : new \DateTimeImmutable('now');
        $appointmentDate = (new \DateTimeImmutable('@' . $timestamp))
            ->setTimezone($now->getTimezone())
            ->format('Y-m-d');
        $createTimestamp = $now->getTimestamp();
        $createIp = ClientIpHelper::getClientIp();
        $slotCount = count($slotTimes);

        $parentId = $this->readNewProcessId();
        $authKey = bin2hex(random_bytes(32));
        $this->insertProcessRow([
            'processId' => $parentId,
            'createTimestamp' => $createTimestamp,
            'authKey' => $authKey,
            'followUpCount' => max(0, $slotCount - 1),
            'parentProcessId' => 0,
            'displayNumber' => (string) $parentId,
            'queueNumber' => 0,
            'scopeId' => $match['scopeId'],
            'appointmentDate' => $appointmentDate,
            'appointmentTime' => $slotTimes[0],
            'createIp' => $createIp,
            'familyName' => '',
            'email' => 'test@muenchen.de',
            'amendment' => '',
            'customTextfield' => '',
            'customTextfield2' => '',
            'surveyAccepted' => 0,
            'reminderTimestamp' => 0,
            'clientCount' => 1,
            'isReserved' => 1,
            'isProcessing' => 0,
            'status' => 'reserved',
            'parkedBy' => null,
            'wasMissed' => 0,
            'priority' => null,
            'externalUserId' => null,
        ]);
        $this->pdo->perform(AppointmentReserveQueries::QUERY_INSERT_SLOT_PROCESS, [
            'processId' => $parentId,
        ]);

        for ($index = 1; $index < $slotCount; $index++) {
            $childId = $this->readNewProcessId();
            $this->insertProcessRow([
                'processId' => $childId,
                'createTimestamp' => $createTimestamp,
                'authKey' => bin2hex(random_bytes(32)),
                'followUpCount' => 0,
                'parentProcessId' => $parentId,
                'displayNumber' => null,
                'queueNumber' => 0,
                'scopeId' => $match['scopeId'],
                'appointmentDate' => $appointmentDate,
                'appointmentTime' => $slotTimes[$index],
                'createIp' => $createIp,
                'familyName' => '(Folgetermin)',
                'email' => '',
                'amendment' => '',
                'customTextfield' => '',
                'customTextfield2' => '',
                'surveyAccepted' => 0,
                'reminderTimestamp' => 0,
                'clientCount' => 1,
                'isReserved' => 1,
                'isProcessing' => 0,
                'status' => 'reserved',
                'parkedBy' => null,
                'wasMissed' => 0,
                'priority' => null,
                'externalUserId' => null,
            ]);
            $this->pdo->perform(AppointmentReserveQueries::QUERY_INSERT_SLOT_PROCESS, [
                'processId' => $childId,
            ]);
        }

        foreach ($calendar->requests as $request) {
            $requestId = is_array($request) ? ($request['id'] ?? null) : ($request->id ?? null);
            $source = is_array($request) ? ($request['source'] ?? $match['source']) : ($request->source ?? $match['source']);
            if ($requestId === null) {
                continue;
            }
            $this->pdo->perform(AppointmentReserveQueries::QUERY_INSERT_REQUEST, [
                'requestId' => $requestId,
                'source' => $source,
                'processId' => $parentId,
            ]);
        }

        return [
            'processId' => $parentId,
            'authKey' => $authKey,
        ];
    }

    /**
     * @param array<string, mixed> $values
     */
    private function insertProcessRow(array $values): void
    {
        $this->pdo->perform(AppointmentReserveQueries::QUERY_INSERT_PROCESS, $values);
    }

    private function readNewProcessId(): int
    {
        $offset = random_int(20, 999);
        $processId = $this->pdo->fetchValue(
            sprintf(AppointmentReserveQueries::QUERY_NEW_PROCESS_ID, $offset)
        );
        if ($processId === false || $processId === null || (int) $processId < 1) {
            throw new AppointmentNotAvailable();
        }

        return (int) $processId;
    }

    private function isDeadlock(\PDOException $exception): bool
    {
        $sqlState = $exception->errorInfo[0] ?? '';
        $driverCode = (int) ($exception->errorInfo[1] ?? 0);

        return $sqlState === '40001' || $driverCode === 1213 || str_contains($exception->getMessage(), 'Deadlock');
    }

    private function rollbackQuietly(): void
    {
        if (!$this->pdo->inTransaction()) {
            return;
        }
        try {
            $this->pdo->rollBack();
        } catch (\PDOException) {
            // ignore, transaction may already be aborted
        }
    }
}
