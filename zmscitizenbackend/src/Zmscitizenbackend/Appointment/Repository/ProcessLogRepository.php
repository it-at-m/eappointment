<?php

declare(strict_types=1);

namespace BO\Zmscitizenbackend\Appointment\Repository;

use BO\Zmscitizenbackend\Appointment\Model\ThinnedProcess;
use BO\Zmscitizenbackend\Connection\Select;

class ProcessLogRepository
{
    public const string USER_ID = '_system_citizenapi';

    public const string TYPE_PROCESS = 'buerger';

    public const string ACTION_CREATED = 'created';

    public const string ACTION_EDITED = 'edited';

    public const string ACTION_STATUS_CHANGED = 'status_changed';

    public const string ACTION_CANCELED = 'canceled';

    public const string ACTION_DELETED = 'deleted';

    private static ?self $instance = null;

    public static function use(?self $repository): void
    {
        self::$instance = $repository;
    }

    public static function create(): self
    {
        return self::$instance ?? new self();
    }

    public function writeCreated(ThinnedProcess $appointment): void
    {
        $this->write(
            $appointment,
            self::ACTION_CREATED,
            'CREATE (AppointmentReserveRepository) ' . (int) ($appointment->processId ?? 0)
        );
    }

    public function writeEdited(ThinnedProcess $appointment): void
    {
        $this->write(
            $appointment,
            self::ACTION_EDITED,
            'UPDATE (AppointmentUpdateRepository) ' . (int) ($appointment->processId ?? 0)
        );
    }

    public function writeStatusChanged(ThinnedProcess $appointment, string $source): void
    {
        $this->write(
            $appointment,
            self::ACTION_STATUS_CHANGED,
            'UPDATE (' . $source . ') ' . (int) ($appointment->processId ?? 0)
        );
    }

    public function writeCanceled(ThinnedProcess $appointment): void
    {
        $this->write(
            $appointment,
            self::ACTION_CANCELED,
            'DELETE (AppointmentCancelRepository) ' . (int) ($appointment->processId ?? 0)
        );
    }

    public function writeDeleted(ThinnedProcess $appointment): void
    {
        $this->write(
            $appointment,
            self::ACTION_DELETED,
            'DELETE (AppointmentCancelRepository) ' . (int) ($appointment->processId ?? 0)
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function indexedFields(ThinnedProcess $appointment): array
    {
        $processId = (int) ($appointment->processId ?? 0);
        $storedDisplayNumber = trim((string) ($appointment->displayNumber ?? ''));
        $displayNumber = $storedDisplayNumber !== ''
            ? $storedDisplayNumber
            : ($processId > 0 ? (string) $processId : '');
        $queueNumber = ctype_digit($storedDisplayNumber) ? (int) $storedDisplayNumber : null;
        $scopeName = trim((string) ($appointment->officeName ?? ''));
        if ($scopeName === '') {
            $scopeName = trim((string) ($appointment->scope?->provider?->name ?? ''));
        }

        return array_filter([
            'displayNumber' => $displayNumber !== '' ? $displayNumber : null,
            'queueNumber' => $queueNumber !== null && $queueNumber > 0 ? $queueNumber : null,
            'appointmentAt' => $this->appointmentAt($appointment),
            'slotCount' => $appointment->slotCount,
            'citizenName' => $this->nonEmpty($appointment->familyName ?? null),
            'services' => $this->services($appointment),
            'scopeName' => $scopeName !== '' ? $scopeName : null,
            'citizenEmail' => $this->nonEmpty($appointment->email ?? null),
            'citizenPhone' => $this->nonEmpty($appointment->telephone ?? null),
            'processStatus' => $this->nonEmpty($appointment->status ?? null),
        ], static function ($value) {
            return $value !== null && $value !== '';
        });
    }

    /**
     * @return array<string, mixed>|null
     */
    public function logRow(ThinnedProcess $appointment, string $action, string $message): ?array
    {
        $processId = (int) ($appointment->processId ?? 0);
        if ($processId < 1) {
            return null;
        }

        $indexed = $this->indexedFields($appointment);

        return [
            'type' => self::TYPE_PROCESS,
            'referenceId' => $processId,
            'message' => $message . ' [zmscitizenbackend]',
            'scopeId' => ((int) ($appointment->scope?->id ?? 0)) ?: null,
            'userId' => self::USER_ID,
            'action' => $action,
            'displayNumber' => $indexed['displayNumber'] ?? null,
            'queueNumber' => $indexed['queueNumber'] ?? null,
            'appointmentAt' => $indexed['appointmentAt'] ?? null,
            'slotCount' => $indexed['slotCount'] ?? null,
            'citizenName' => $indexed['citizenName'] ?? null,
            'services' => $indexed['services'] ?? null,
            'scopeName' => $indexed['scopeName'] ?? null,
            'citizenEmail' => $indexed['citizenEmail'] ?? null,
            'citizenPhone' => $indexed['citizenPhone'] ?? null,
            'processStatus' => $indexed['processStatus'] ?? null,
        ];
    }

    private function write(ThinnedProcess $appointment, string $action, string $message): void
    {
        $parameters = $this->logRow($appointment, $action, $message);
        if ($parameters === null) {
            return;
        }

        try {
            Select::getWriteConnection()->perform(ProcessLogQueries::QUERY_INSERT, $parameters);
        } catch (\Throwable $exception) {
            if (\App::$log) {
                \App::$log->error('Failed to write citizen process log', [
                    'processId' => $parameters['referenceId'],
                    'action' => $action,
                    'exception' => $exception->getMessage(),
                ]);
            }
        }
    }

    private function appointmentAt(ThinnedProcess $appointment): ?string
    {
        $timestamp = (int) ($appointment->timestamp ?? 0);
        if ($timestamp <= 0) {
            return null;
        }

        return (new \DateTimeImmutable('@' . $timestamp))
            ->setTimezone(new \DateTimeZone(date_default_timezone_get()))
            ->format('Y-m-d H:i:s');
    }

    private function services(ThinnedProcess $appointment): ?string
    {
        $names = [];
        $main = trim((string) ($appointment->serviceName ?? ''));
        if ($main !== '') {
            $names[] = $main;
        }
        foreach ($appointment->subRequestCounts as $subRequest) {
            if (!is_array($subRequest)) {
                continue;
            }
            $name = trim((string) ($subRequest['name'] ?? ''));
            if ($name !== '' && !in_array($name, $names, true)) {
                $names[] = $name;
            }
        }

        return $names === [] ? null : implode(', ', $names);
    }

    private function nonEmpty(?string $value): ?string
    {
        $value = trim((string) $value);
        return $value === '' ? null : $value;
    }
}
