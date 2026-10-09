<?php

declare(strict_types=1);

namespace BO\Zmsbackend\Availability\Service;

use BO\Zmsentities\Helper\SlotWindow;

/**
 * When a provider slot length changes, keep booked days on the old length
 * and open a new series the day after the furthest future booking.
 */
class AvailabilitySlotChange extends \BO\Zmsbackend\Base
{
    public function apply(int $scopeId, int $newSlotMinutes, \DateTimeInterface $now): void
    {
        if ($scopeId < 1 || $newSlotMinutes < 1) {
            return;
        }

        $rows = $this->fetchAll(
            \BO\Zmsbackend\Availability\Repository\Availability::QUERY_OPEN_ROWS_BY_SCOPE,
            [
                'scopeId' => $scopeId,
                'today' => $now->format('Y-m-d'),
            ]
        );
        if ($rows === [] || !$this->slotDiffers($rows, $newSlotMinutes)) {
            return;
        }

        $latest = (new \BO\Zmsbackend\Process\Service\Process())->readLatestBookedDate($scopeId, $now);
        $cut = $this->cutDate($latest, $now);
        foreach ($rows as $row) {
            $this->applyToRow($row, $newSlotMinutes, $cut);
        }
    }

    /**
     * @param list<array<string, mixed>> $rows
     */
    private function slotDiffers(array $rows, int $newSlotMinutes): bool
    {
        foreach ($rows as $row) {
            if ((int) $row['slot_minutes'] !== $newSlotMinutes) {
                return true;
            }
        }

        return false;
    }

    private function cutDate(?string $latestBookedDate, \DateTimeInterface $now): string
    {
        $anchor = ($latestBookedDate !== null && $latestBookedDate !== '')
            ? $latestBookedDate
            : $now->format('Y-m-d');

        return (new \DateTimeImmutable($anchor))->modify('+1 day')->format('Y-m-d');
    }

    /**
     * @param array<string, mixed> $row
     */
    private function applyToRow(array $row, int $newSlotMinutes, string $cut): void
    {
        $endDate = (string) $row['end_date'];
        if ((int) $row['slot_minutes'] === $newSlotMinutes || $endDate < $cut) {
            return;
        }

        $times = $this->fittedTimes($row, $newSlotMinutes);
        if ($times === null) {
            return;
        }

        $startDate = (string) $row['start_date'];
        if ($startDate >= $cut) {
            $this->updateSlotWindow((int) $row['id'], $newSlotMinutes, $times);
            $this->writeHistory((int) $row['id']);
            return;
        }

        $newId = $this->insertCopy((int) $row['id'], $cut, $endDate, $newSlotMinutes, $times);
        $this->perform(
            \BO\Zmsbackend\Availability\Repository\Availability::QUERY_UPDATE_END_DATE,
            [
                'endDate' => (new \DateTimeImmutable($cut))->modify('-1 day')->format('Y-m-d'),
                'id' => (int) $row['id'],
            ]
        );
        $this->writeHistory((int) $row['id']);
        if ($newId !== null) {
            $this->writeHistory($newId);
        }
    }

    /**
     * @param array<string, mixed> $row
     * @return array{start_time: string, end_time: string, appointment_start_time: string, appointment_end_time: string}|null
     */
    private function fittedTimes(array $row, int $newSlotMinutes): ?array
    {
        $opening = $this->fittedPair((string) $row['start_time'], (string) $row['end_time'], $newSlotMinutes);
        $appointment = $this->fittedPair(
            (string) $row['appointment_start_time'],
            (string) $row['appointment_end_time'],
            $newSlotMinutes
        );
        if ($opening === null || $appointment === null) {
            return null;
        }
        if ($opening['unused'] && $appointment['unused']) {
            return null;
        }

        return [
            'start_time' => $opening['start'],
            'end_time' => $opening['end'],
            'appointment_start_time' => $appointment['start'],
            'appointment_end_time' => $appointment['end'],
        ];
    }

    /**
     * @return array{start: string, end: string, unused: bool}|null
     */
    private function fittedPair(string $start, string $end, int $newSlotMinutes): ?array
    {
        if (SlotWindow::isUnused($start, $end)) {
            return ['start' => '00:00:00', 'end' => '00:00:00', 'unused' => true];
        }

        $fitted = SlotWindow::fittedEndTime($start, $end, $newSlotMinutes);
        if ($fitted === null) {
            return null;
        }

        return ['start' => $start, 'end' => $fitted, 'unused' => false];
    }

    /**
     * @param array{start_time: string, end_time: string, appointment_start_time: string, appointment_end_time: string} $times
     */
    private function updateSlotWindow(int $id, int $newSlotMinutes, array $times): void
    {
        $this->perform(
            \BO\Zmsbackend\Availability\Repository\Availability::QUERY_UPDATE_SLOT_WINDOW,
            [
                'timeSlot' => $this->slotTime($newSlotMinutes),
                'startTime' => $times['start_time'],
                'endTime' => $times['end_time'],
                'appointmentStartTime' => $times['appointment_start_time'],
                'appointmentEndTime' => $times['appointment_end_time'],
                'id' => $id,
            ]
        );
    }

    /**
     * @param array{start_time: string, end_time: string, appointment_start_time: string, appointment_end_time: string} $times
     */
    private function insertCopy(int $id, string $startDate, string $endDate, int $newSlotMinutes, array $times): ?int
    {
        $this->perform(
            \BO\Zmsbackend\Availability\Repository\Availability::QUERY_INSERT_SLOT_COPY,
            [
                'startDate' => $startDate,
                'endDate' => $endDate,
                'startTime' => $times['start_time'],
                'appointmentStartTime' => $times['appointment_start_time'],
                'endTime' => $times['end_time'],
                'appointmentEndTime' => $times['appointment_end_time'],
                'timeSlot' => $this->slotTime($newSlotMinutes),
                'id' => $id,
            ]
        );
        $newId = (int) $this->getWriter()->lastInsertId();

        return $newId > 0 ? $newId : null;
    }

    private function slotTime(int $minutes): string
    {
        return gmdate('H:i:s', $minutes * 60);
    }

    private function writeHistory(int $availabilityId): void
    {
        \BO\Zmsbackend\Availability\Service\Availability::$cache = [];
        $availability = (new Availability())->readEntity($availabilityId, 0);
        if (!$availability->hasId()) {
            return;
        }
        (new AvailabilityHistory())->writeDldbSlotUpdate($availability);
    }
}
