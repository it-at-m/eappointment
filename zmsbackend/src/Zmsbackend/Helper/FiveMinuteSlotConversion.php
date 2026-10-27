<?php

declare(strict_types=1);

namespace BO\Zmsbackend\Helper;

use BO\Zmsentities\Helper\FiveMinuteAppointmentPlan;
use BO\Zmsentities\Helper\FiveMinuteGrid;

/**
 * One-shot move of opening hours and booked appointments onto the 5-minute grid.
 *
 * Stop updateDldbData and calculateSlots before running this. The hourly import
 * copies provider slotTimeInMinutes onto opening hours and does not insert Folgetermine.
 */
class FiveMinuteSlotConversion extends \BO\Zmsbackend\Base
{
    private const string FIVE_MINUTE_SLOT = '00:05:00';

    /** @var array{openingHours: int, followUps: int, caps: int, providers: int, unchangedCaps: list<int>} */
    private array $report = [
        'openingHours' => 0,
        'followUps' => 0,
        'caps' => 0,
        'providers' => 0,
        'unchangedCaps' => [],
    ];

    /**
     * @return array{openingHours: int, followUps: int, caps: int, providers: int, unchangedCaps: list<int>}
     */
    public function convert(\DateTimeInterface $now, bool $write): array
    {
        $this->assertSlotLengthsAlign();
        $this->assertProviderDataIsJson();
        $providers = $this->readProviderSlotMinutes();
        $this->assertServiceDurationsAlign($providers);

        foreach ($this->readScopeIds() as $scopeId) {
            $this->convertScope($scopeId, $now, $write);
        }
        $this->retargetProviders($providers, $write);

        return $this->report;
    }

    private function convertScope(int $scopeId, \DateTimeInterface $now, bool $write): void
    {
        $work = $this->prepareScope($scopeId);
        if ($work === null) {
            return;
        }

        $this->report['followUps'] += $work['followUpRows'];
        $this->report['openingHours'] += count($work['openingHourIds']);
        if ($work['scaleCap']) {
            $this->report['caps']++;
        }
        if ($write && $work['needsWrite']) {
            $this->writeScope($scopeId, $now, $work);
        }
    }

    /**
     * @return array{
     *     planned: list<array{processId: int, times: list<string>, followUpCount: int}>,
     *     followUpRows: int,
     *     openingHourIds: list<int>,
     *     oldCapMinutes: int|null,
     *     scaleCap: bool,
     *     needsWrite: bool
     * }|null
     */
    private function prepareScope(int $scopeId): ?array
    {
        $minutes = $this->slotMinutesForScope($scopeId);
        if ($minutes === []) {
            return null;
        }
        if (count($minutes) > 1) {
            $this->report['unchangedCaps'][] = $scopeId;
        }

        $planned = $this->planFollowUps(
            $scopeId,
            $this->readAppointments($scopeId),
            count($minutes) === 1 ? $minutes[0] : null
        );
        $followUpRows = 0;
        foreach ($planned as $appointment) {
            $followUpRows += count($appointment['times']);
        }

        $openingHourIds = $this->openingHoursToRetarget($scopeId);
        $oldCapMinutes = $this->singleOldCapMinutes($minutes);
        $scaleCap = $this->shouldScaleCap($scopeId, $oldCapMinutes);

        return [
            'planned' => $planned,
            'followUpRows' => $followUpRows,
            'openingHourIds' => $openingHourIds,
            'oldCapMinutes' => $oldCapMinutes,
            'scaleCap' => $scaleCap,
            'needsWrite' => $this->scopeNeedsWrite($planned, $openingHourIds, $scaleCap),
        ];
    }

    private function shouldScaleCap(int $scopeId, ?int $oldCapMinutes): bool
    {
        if ($oldCapMinutes === null) {
            return false;
        }

        return (new \BO\Zmsbackend\Scope\Service\Scope())->readSlotsPerAppointment($scopeId) !== null;
    }

    /**
     * @param list<array{processId: int, times: list<string>, followUpCount: int}> $planned
     * @param list<int> $openingHourIds
     */
    private function scopeNeedsWrite(array $planned, array $openingHourIds, bool $scaleCap): bool
    {
        return $planned !== [] || $openingHourIds !== [] || $scaleCap;
    }

    /**
     * @param array{
     *     planned: list<array{processId: int, times: list<string>, followUpCount: int}>,
     *     openingHourIds: list<int>,
     *     oldCapMinutes: int|null,
     *     scaleCap: bool
     * } $work
     */
    private function writeScope(int $scopeId, \DateTimeInterface $now, array $work): void
    {
        \BO\Zmsbackend\Connection\Select::setTransaction();
        \BO\Zmsbackend\Connection\Select::getWriteConnection();
        try {
            $this->insertFollowUps($work['planned'], $now);
            $this->retargetOpeningHours($work['openingHourIds']);
            if ($work['scaleCap']) {
                (new \BO\Zmsbackend\Scope\Service\Scope())->writeScaledSlotsPerAppointment(
                    $scopeId,
                    (int) $work['oldCapMinutes'],
                    FiveMinuteGrid::MINUTES
                );
            }
            \BO\Zmsbackend\Connection\Select::writeCommit();
        } catch (\Throwable $exception) {
            \BO\Zmsbackend\Connection\Select::writeRollback();
            throw new \RuntimeException(
                'Conversion stopped on Standort ' . $scopeId . ': ' . $exception->getMessage(),
                0,
                $exception
            );
        }

        if ($work['planned'] !== [] || $work['openingHourIds'] !== []) {
            $this->rebuildSlots($scopeId, $now);
        }
    }

    /**
     * @param list<array{processId: int, times: list<string>, followUpCount: int}> $planned
     */
    private function insertFollowUps(array $planned, \DateTimeInterface $now): void
    {
        $processes = new \BO\Zmsbackend\Process\Service\Process();
        foreach ($planned as $appointment) {
            if ($appointment['times'] !== []) {
                $process = $processes->readById($appointment['processId'], 0);
                if (!$process->hasId()) {
                    throw new \RuntimeException('Appointment ' . $appointment['processId'] . ' was not found');
                }
                foreach ($appointment['times'] as $time) {
                    $processes->writeFollowUpAppointment($process, $time, $now);
                }
            }
            $this->perform(
                'UPDATE `buerger` SET `hatFolgetermine` = :followUps WHERE `BuergerID` = :id',
                [
                    'followUps' => $appointment['followUpCount'],
                    'id' => $appointment['processId'],
                ]
            );
        }
    }

    /**
     * @param list<int> $openingHourIds
     */
    private function retargetOpeningHours(array $openingHourIds): void
    {
        if ($openingHourIds === []) {
            return;
        }

        foreach ($openingHourIds as $openingHourId) {
            $this->perform(
                'UPDATE `oeffnungszeit`
                 SET `time_slot` = :slot, `version` = `version` + 1
                 WHERE `OeffnungszeitID` = :id',
                [
                    'slot' => self::FIVE_MINUTE_SLOT,
                    'id' => $openingHourId,
                ]
            );
        }

        \BO\Zmsbackend\Availability\Service\Availability::$cache = [];
        $availability = new \BO\Zmsbackend\Availability\Service\Availability();
        $history = new \BO\Zmsbackend\Availability\Service\AvailabilityHistory();
        foreach ($openingHourIds as $openingHourId) {
            $entity = $availability->readEntity($openingHourId, 0);
            if ($entity->hasId()) {
                $history->writeDldbSlotUpdate($entity);
            }
        }
    }

    private function rebuildSlots(int $scopeId, \DateTimeInterface $now): void
    {
        $scope = (new \BO\Zmsbackend\Scope\Service\Scope())->readEntity($scopeId, 1, true);
        if ($scope === null || !$scope->hasId()) {
            return;
        }

        try {
            (new \BO\Zmsbackend\Slot\Service\Slot())->writeByScope($scope, $now);
            (new CalculateSlots())->writePostProcessingByScope($scope, $now);
            \BO\Zmsbackend\Connection\Select::writeCommit();
        } catch (\Throwable $exception) {
            throw new \RuntimeException(
                'Standort ' . $scopeId
                . ' is on the 5-minute grid, but slot rebuild failed. Run calculateSlots --commit. '
                . $exception->getMessage(),
                0,
                $exception
            );
        }
    }

    /**
     * @param list<array{id: string, source: string, minutes: int}> $providers
     */
    private function retargetProviders(array $providers, bool $write): void
    {
        if ($write) {
            \BO\Zmsbackend\Connection\Select::setTransaction();
            $connection = \BO\Zmsbackend\Connection\Select::getWriteConnection();
            if (!$connection->inTransaction()) {
                $connection->beginTransaction();
            }
        }

        try {
            foreach ($providers as $provider) {
                if ($provider['minutes'] <= FiveMinuteGrid::MINUTES) {
                    continue;
                }
                if (!FiveMinuteGrid::isMultiple($provider['minutes'])) {
                    throw new \RuntimeException(
                        'Provider ' . $provider['source'] . ':' . $provider['id']
                        . ' has slot length ' . $provider['minutes']
                    );
                }
                $this->report['providers']++;
                if (!$write) {
                    continue;
                }

                $factor = intdiv($provider['minutes'], FiveMinuteGrid::MINUTES);
                $this->perform(
                    'UPDATE `request_provider`
                     SET `slots` = `slots` * :factor
                     WHERE `provider__id` = :id AND `source` = :source AND `slots` > 0',
                    [
                        'factor' => $factor,
                        'id' => $provider['id'],
                        'source' => $provider['source'],
                    ]
                );
                $this->perform(
                    'UPDATE `provider`
                     SET `data` = JSON_SET(`data`, \'$.slotTimeInMinutes\', 5)
                     WHERE `id` = :id AND `source` = :source AND JSON_VALID(`data`)',
                    [
                        'id' => $provider['id'],
                        'source' => $provider['source'],
                    ]
                );
            }

            if ($write) {
                \BO\Zmsbackend\Connection\Select::writeCommit();
            }
        } catch (\Throwable $exception) {
            if ($write) {
                \BO\Zmsbackend\Connection\Select::writeRollback();
            }
            throw $exception;
        }
    }

    /**
     * @param list<array{id: string, day: string, time: string, followUps: int|null}> $appointments
     * @return list<array{processId: int, times: list<string>, followUpCount: int}>
     */
    private function planFollowUps(int $scopeId, array $appointments, ?int $singleMinutes): array
    {
        $planned = [];
        foreach ($appointments as $appointment) {
            $slotMinutes = $singleMinutes ?? $this->slotMinutesAt(
                $scopeId,
                (string) $appointment['day'],
                (string) $appointment['time']
            );
            if ($slotMinutes === null || !FiveMinuteGrid::isMultiple($slotMinutes)) {
                throw new \RuntimeException(
                    'No 5-minute-aligned slot length for Standort ' . $scopeId
                    . ' appointment ' . $appointment['id']
                );
            }

            $slotCount = (int) $appointment['followUps'] + 1;
            if ($slotCount < 1) {
                $slotCount = 1;
            }
            $missing = FiveMinuteAppointmentPlan::missingFollowUpTimes(
                (string) $appointment['time'],
                $this->readTimes((int) $appointment['id'], (string) $appointment['time']),
                $slotCount,
                $slotMinutes
            );
            $followUpCount = FiveMinuteAppointmentPlan::followUpCount($slotCount, $slotMinutes);
            if ($missing === null || $followUpCount === null) {
                throw new \RuntimeException(
                    'Appointment ' . $appointment['id'] . ' on Standort ' . $scopeId
                    . ' does not fit a 5-minute grid'
                );
            }
            if ($missing === [] && $followUpCount === (int) $appointment['followUps']) {
                continue;
            }
            $planned[] = [
                'processId' => (int) $appointment['id'],
                'times' => $missing,
                'followUpCount' => $followUpCount,
            ];
        }

        return $planned;
    }

    /**
     * @return list<string>
     */
    private function readTimes(int $processId, string $parentTime): array
    {
        $rows = $this->fetchAll(
            'SELECT `Uhrzeit` AS time FROM `buerger` WHERE `istFolgeterminvon` = :id',
            ['id' => $processId]
        );
        $times = [$parentTime];
        foreach ($rows as $row) {
            $times[] = (string) $row['time'];
        }

        return $times;
    }

    /**
     * @return list<array{id: string, day: string, time: string, followUps: int|null}>
     */
    private function readAppointments(int $scopeId): array
    {
        return $this->fetchAll(
            'SELECT `BuergerID` AS id, `Datum` AS day, `Uhrzeit` AS time, `hatFolgetermine` AS followUps
             FROM `buerger`
             WHERE `StandortID` = :scopeId
               AND (`istFolgeterminvon` IS NULL OR `istFolgeterminvon` = 0)
               AND `Uhrzeit` <> \'00:00:00\'
               AND (`status` IS NULL OR `status` <> \'deleted\')',
            ['scopeId' => $scopeId]
        );
    }

    /**
     * @return list<int>
     */
    private function openingHoursToRetarget(int $scopeId): array
    {
        $rows = $this->fetchAll(
            'SELECT `OeffnungszeitID` AS id
             FROM `oeffnungszeit`
             WHERE `scope_id` = :scopeId
               AND TIME_TO_SEC(`time_slot`) > 0
               AND `time_slot` <> :slot',
            [
                'scopeId' => $scopeId,
                'slot' => self::FIVE_MINUTE_SLOT,
            ]
        );

        return array_map(static fn (array $row): int => (int) $row['id'], $rows);
    }

    /**
     * @return list<int>
     */
    private function slotMinutesForScope(int $scopeId): array
    {
        $rows = $this->fetchAll(
            'SELECT DISTINCT FLOOR(TIME_TO_SEC(`time_slot`) / 60) AS minutes
             FROM `oeffnungszeit`
             WHERE `scope_id` = :scopeId AND TIME_TO_SEC(`time_slot`) > 0',
            ['scopeId' => $scopeId]
        );
        $minutes = [];
        foreach ($rows as $row) {
            $minutes[] = (int) $row['minutes'];
        }
        sort($minutes);

        return $minutes;
    }

    private function slotMinutesAt(int $scopeId, string $day, string $time): ?int
    {
        $date = \DateTimeImmutable::createFromFormat('!Y-m-d', substr($day, 0, 10));
        $normalized = FiveMinuteAppointmentPlan::normalizeTime($time);
        if ($date === false || $normalized === null) {
            return null;
        }

        $value = $this->fetchValue(
            'SELECT `slotTimeInMinutes`
             FROM `slot`
             WHERE `scopeID` = :scopeId
               AND `year` = :year
               AND `month` = :month
               AND `day` = :day
               AND `status` IN ("free", "full")
               AND :time BETWEEN `time`
                 AND SEC_TO_TIME(TIME_TO_SEC(`time`) + (`slotTimeInMinutes` * 60) - 1)
             ORDER BY `slotTimeInMinutes` DESC
             LIMIT 1',
            [
                'scopeId' => $scopeId,
                'year' => (int) $date->format('Y'),
                'month' => (int) $date->format('n'),
                'day' => (int) $date->format('j'),
                'time' => $normalized,
            ]
        );
        if ($value === false || $value === null || $value === '') {
            return null;
        }
        $minutes = (int) $value;

        return $minutes > 0 ? $minutes : null;
    }

    /**
     * @param list<int> $minutes
     */
    private function singleOldCapMinutes(array $minutes): ?int
    {
        if (count($minutes) !== 1 || $minutes[0] === FiveMinuteGrid::MINUTES) {
            return null;
        }

        return $minutes[0];
    }

    /**
     * @return list<int>
     */
    private function readScopeIds(): array
    {
        $rows = $this->fetchAll('SELECT `StandortID` AS id FROM `standort` ORDER BY `StandortID`');

        return array_map(static fn (array $row): int => (int) $row['id'], $rows);
    }

    /**
     * @return list<array{id: string, source: string, minutes: int}>
     */
    private function readProviderSlotMinutes(): array
    {
        $rows = $this->fetchAll(
            'SELECT `id`, `source`,
                    CAST(JSON_UNQUOTE(JSON_EXTRACT(`data`, \'$.slotTimeInMinutes\')) AS UNSIGNED) AS minutes
             FROM `provider`
             WHERE JSON_VALID(`data`)'
        );
        $providers = [];
        foreach ($rows as $row) {
            $providers[] = [
                'id' => (string) $row['id'],
                'source' => (string) $row['source'],
                'minutes' => (int) $row['minutes'],
            ];
        }

        return $providers;
    }

    private function assertSlotLengthsAlign(): void
    {
        $rows = $this->fetchAll(
            'SELECT `OeffnungszeitID` AS id, `scope_id` AS scopeId, `time_slot` AS timeSlot
             FROM `oeffnungszeit`
             WHERE TIME_TO_SEC(`time_slot`) > 0
               AND MOD(TIME_TO_SEC(`time_slot`), :seconds) <> 0
             LIMIT 20',
            ['seconds' => FiveMinuteGrid::MINUTES * 60]
        );
        if ($rows === []) {
            return;
        }

        $listed = [];
        foreach ($rows as $row) {
            $listed[] = 'Standort ' . $row['scopeId'] . ' opening hour ' . $row['id'] . ' is ' . $row['timeSlot'];
        }
        throw new \RuntimeException(
            'Slot lengths must be multiples of 5 minutes before conversion. ' . implode('; ', $listed)
        );
    }

    private function assertProviderDataIsJson(): void
    {
        $rows = $this->fetchAll(
            'SELECT p.`id`, p.`source`
             FROM `provider` p
             WHERE NOT JSON_VALID(p.`data`)
               AND EXISTS (
                   SELECT 1 FROM `request_provider` rp
                   WHERE rp.`provider__id` = p.`id`
                     AND rp.`source` = p.`source`
                     AND rp.`slots` > 0
               )
             LIMIT 20'
        );
        if ($rows === []) {
            return;
        }

        $listed = [];
        foreach ($rows as $row) {
            $listed[] = $row['source'] . ':' . $row['id'];
        }
        throw new \RuntimeException('Provider data is not JSON: ' . implode(', ', $listed));
    }

    /**
     * @param list<array{id: string, source: string, minutes: int}> $providers
     */
    private function assertServiceDurationsAlign(array $providers): void
    {
        $minutesByProvider = [];
        foreach ($providers as $provider) {
            $minutesByProvider[$provider['source'] . ':' . $provider['id']] = $provider['minutes'];
        }

        $rows = $this->fetchAll(
            'SELECT `provider__id` AS id, `source`, `request__id` AS requestId, `slots`
             FROM `request_provider`
             WHERE `slots` > 0'
        );
        $bad = [];
        foreach ($rows as $row) {
            $minutes = $minutesByProvider[$row['source'] . ':' . $row['id']] ?? 0;
            if ($minutes < 1 || FiveMinuteGrid::isMultiple($minutes) === false) {
                if ($minutes > 0) {
                    $bad[] = $row['source'] . ':' . $row['id'];
                }
                continue;
            }
            $duration = (float) $row['slots'] * $minutes;
            if (
                abs($duration - round($duration)) > 0.001
                || ((int) round($duration)) % FiveMinuteGrid::MINUTES !== 0
            ) {
                $bad[] = $row['source'] . ':' . $row['id'] . '/' . $row['requestId'];
            }
            if (count($bad) >= 20) {
                break;
            }
        }
        if ($bad !== []) {
            throw new \RuntimeException(
                'Service durations are not multiples of 5 minutes: ' . implode(', ', $bad)
            );
        }
    }
}
