<?php

declare(strict_types=1);

namespace BO\Zmsbackend\Availability\Service;

use BO\Zmsbackend\Availability\Repository\AvailabilityHistory as AvailabilityHistoryQuery;
use BO\Zmsbackend\Helper\User;
use BO\Zmsentities\Availability;
use App;

/**
 * Write opening-hours change history for tech-admin audit (ZMSKVR-1249).
 */
class AvailabilityHistory extends \BO\Zmsbackend\Base
{
    public const ACTION_CREATED = 'created';
    public const ACTION_UPDATED = 'updated';
    public const ACTION_DELETED = 'deleted';
    public const ACTION_DLDB_SLOT_UPDATE = 'dldb_slot_update';

    private const SUMMARY_MAX_LENGTH = 512;

    /** @var array<string, string> */
    private const TYPE_LABELS = [
        'openinghours' => 'Spontankunden',
        'appointment' => 'Terminkunden',
    ];

    /** @var array<string, string> monday-first like admin accordionTitle() */
    private const WEEKDAY_LABELS = [
        'monday' => 'Montag',
        'tuesday' => 'Dienstag',
        'wednesday' => 'Mittwoch',
        'thursday' => 'Donnerstag',
        'friday' => 'Freitag',
        'saturday' => 'Samstag',
        'sunday' => 'Sonntag',
    ];

    public function writeCreated(Availability $availability, ?string $changedBy = null): bool
    {
        return $this->write(self::ACTION_CREATED, $availability, $changedBy);
    }

    public function writeUpdated(Availability $availability, ?string $changedBy = null): bool
    {
        return $this->write(self::ACTION_UPDATED, $availability, $changedBy);
    }

    public function writeDeleted(Availability $availability, ?string $changedBy = null): bool
    {
        return $this->write(self::ACTION_DELETED, $availability, $changedBy ?? 'system');
    }

    public function writeDldbSlotUpdate(Availability $availability): bool
    {
        return $this->write(self::ACTION_DLDB_SLOT_UPDATE, $availability, 'dldb');
    }

    /**
     * Accordion-style one-liner matching zmsadmin accordionTitle().
     */
    public function buildSummary(Availability $availability): string
    {
        $startDate = $availability->getStartDateTime()->format('d.m.Y');
        $endDate = $availability->getEndDateTime()->format('d.m.Y');
        $startTime = $this->formatTime((string) $availability->startTime);
        $endTime = $this->formatTime((string) $availability->endTime);

        $typeLabel = self::TYPE_LABELS[$availability->type] ?? null;
        $weekdayLabels = [];
        foreach (self::WEEKDAY_LABELS as $key => $label) {
            if (!empty($availability->weekday[$key])) {
                $weekdayLabels[] = $label;
            }
        }
        $weekdayList = implode(', ', $weekdayLabels);

        $type = ($typeLabel && $weekdayList !== '')
            ? " Typ: {$typeLabel}, Wochentag: {$weekdayList}"
            : '';
        $description = $availability->description !== '' && $availability->description !== null
            ? ': ' . $availability->description
            : '';

        $summary = "Zeitraum: {$startDate} bis {$endDate}, Uhrzeit: von {$startTime} bis {$endTime},{$type}{$description}";

        if (mb_strlen($summary) > self::SUMMARY_MAX_LENGTH) {
            return mb_substr($summary, 0, self::SUMMARY_MAX_LENGTH - 3) . '...';
        }

        return $summary;
    }

    protected function write(string $action, Availability $availability, ?string $changedBy): bool
    {
        try {
            $scopeId = (int) ($availability->scope['id'] ?? 0);
            if ($scopeId < 1) {
                App::$log->warning('availability_history skipped: missing scope_id', [
                    'action' => $action,
                    'availability_id' => $availability->id ?? null,
                ]);
                return false;
            }

            $query = new AvailabilityHistoryQuery(\BO\Zmsbackend\Query\Base::INSERT);
            $query->addValues($query->reverseEntityMapping([
                'scope_id' => $scopeId,
                'availability_id' => $availability->hasId() ? (int) $availability->getId() : null,
                'action' => $action,
                'summary' => $this->buildSummary($availability),
                'changed_by' => $changedBy ?? $this->resolveChangedBy(),
            ]));

            return (bool) $this->writeItem($query);
        } catch (\Throwable $exception) {
            App::$log->error('availability_history write failed', [
                'action' => $action,
                'availability_id' => $availability->id ?? null,
                'exception' => $exception->getMessage(),
            ]);
            return false;
        }
    }

    protected function resolveChangedBy(): string
    {
        try {
            if (User::hasLogin()) {
                $userId = User::readWorkstation()->getUseraccount()->id ?? null;
                if ($userId !== null && $userId !== '') {
                    return (string) $userId;
                }
            }
        } catch (\Throwable $exception) {
            // Fall through to system actor.
        }

        return 'system';
    }

    protected function formatTime(string $value): string
    {
        if ($value === '') {
            return '';
        }
        $formats = ['H:i:s', 'H:i', 'G:i:s', 'G:i'];
        foreach ($formats as $format) {
            $parsed = \DateTimeImmutable::createFromFormat($format, $value);
            if ($parsed instanceof \DateTimeImmutable) {
                return $parsed->format('H:i');
            }
        }

        return $value;
    }
}
