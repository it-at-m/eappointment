<?php

namespace BO\Zmsbackend\ProcessSearchHistory\Service;

use BO\Zmsbackend\Exception\Pdo\PDOFailed;
use BO\Zmsbackend\Process\Service\Process as ProcessService;
use BO\Zmsbackend\ProcessSearchHistory\Repository\ProcessSearchHistory as HistoryRepository;
use BO\Zmsbackend\Query\Base as QueryBase;
use BO\Zmsentities\Process as ProcessEntity;
use BO\Zmsentities\Scope as ScopeEntity;

class ProcessSearchHistory extends \BO\Zmsbackend\Base
{
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_MISSED = 'missed';

    private const RESOLVE_REFERENCES = 2;
    private const DUPLICATE_KEY_ERROR_CODE = 1062;

    /**
     * Bei einem bereits vorhandenen history_key wird kein zweiter
     * Datensatz angelegt. In beiden Fällen wird der history_key
     * zurückgegeben.
     */
    public function writeHistoryEntry(
        ProcessEntity $process,
        string $status,
        \DateTimeInterface $finalizedAt
    ): string {
        $this->validateStatus($status);

        $process = $this->readFullyResolvedProcess($process);
        $historyKey = $this->createHistoryKey($process);

        if ($this->existsByHistoryKey($historyKey)) {
            return $historyKey;
        }

        $query = new HistoryRepository(QueryBase::INSERT);
        $query->addValuesNewHistory(
            $this->buildHistoryEntryData(
                $process,
                $historyKey,
                $status,
                $finalizedAt
            )
        );

        try {
            $this->writeItem($query);
        } catch (PDOFailed $exception) {
            if (!$this->isDuplicateKeyException($exception)) {
                throw $exception;
            }
        }

        return $historyKey;
    }

    public function existsByHistoryKey(string $historyKey): bool
    {
        $query = new HistoryRepository(QueryBase::SELECT);
        $query
            ->addCountValue()
            ->addConditionHistoryKey($historyKey);

        return (int) $this->fetchValue(
            $query,
            $query->getParameters()
        ) > 0;
    }

    private function readFullyResolvedProcess(
        ProcessEntity $process
    ): ProcessEntity {
        if (!$process->hasProcessCredentials()) {
            throw new \InvalidArgumentException(
                'Process id and authKey are required for the history snapshot.'
            );
        }

        $processService = new ProcessService(
            $this->getWriter(),
            $this->getReader()
        );

        $resolvedProcess = $processService->readEntity(
            $process->getId(),
            $process->getAuthKey(),
            self::RESOLVE_REFERENCES
        );

        if ($resolvedProcess === null || !$resolvedProcess->hasId()) {
            throw new \RuntimeException(
                sprintf(
                    'Process %d could not be loaded for the history snapshot.',
                    $process->getId()
                )
            );
        }

        return $resolvedProcess;
    }

    private function buildHistoryEntryData(
        ProcessEntity $process,
        string $historyKey,
        string $status,
        \DateTimeInterface $finalizedAt
    ): array {
        $client = $process->getFirstClient();
        $scope = $this->getScope($process);

        return [
            'historyKey' => $historyKey,

            'processId' => (int) $process->getId(),
            'scopeId' => $this->getScopeId($process),
            'displayNumber' => $this->getNullableString(
                $process->getDisplayNumber()
            ),

            'appointmentAt' => $this->getAppointmentAt($process),
            'bookedAt' => $this->dateTimeFromTimestamp(
                (int) $process->createTimestamp
            ),
            'calledAt' => $this->dateTimeFromTimestamp(
                (int) $process
                    ->toProperty()
                    ->queue
                    ->callTime
                    ->get(0)
            ),

            'finalizedAt' => $finalizedAt,
            'status' => $status,

            'citizenName' => (string) $client
                ->toProperty()
                ->familyName
                ->get(''),

            'telephone' => (string) $client
                ->toProperty()
                ->telephone
                ->get(''),

            'citizenEmail' => (string) $client
                ->toProperty()
                ->email
                ->get(''),

            'amendment' => $this->getNullableString(
                $process->getAmendment()
            ),

            'locationName' => trim(
                (string) $scope->getShortName()
            ),

            'providerName' => trim(
                (string) $scope->getProvider()->getName()
            ),

            'services' => $this->buildServiceNames($process),
        ];
    }

    private function createHistoryKey(
        ProcessEntity $process
    ): string {
        $processId = (int) $process->getId();
        $createTimestamp = (int) $process->createTimestamp;
        $authKey = (string) $process->getAuthKey();

        if (
            $processId <= 0
            || $createTimestamp <= 0
            || $authKey === ''
        ) {
            throw new \InvalidArgumentException(
                'Process id, createTimestamp and authKey are required '
                . 'to create the history key.'
            );
        }

        return hash(
            'sha256',
            $processId
                . '|'
                . $createTimestamp
                . '|'
                . $authKey
        );
    }

    private function buildServiceNames(
        ProcessEntity $process
    ): ?string {
        $serviceNames = [];

        foreach ($process->getRequests() as $request) {
            $serviceName = trim((string) $request->getName());

            if ($serviceName === '') {
                continue;
            }

            $serviceNames[$serviceName] = true;
        }

        if ($serviceNames === []) {
            return null;
        }

        return implode(
            "\n",
            array_keys($serviceNames)
        );
    }

    private function getScope(
        ProcessEntity $process
    ): ScopeEntity {
        if ($process->scope instanceof ScopeEntity) {
            return $process->scope;
        }

        return new ScopeEntity($process->scope);
    }

    private function dateTimeFromTimestamp(
        int $timestamp
    ): ?\DateTimeImmutable {
        if ($timestamp <= 0) {
            return null;
        }

        return (new \DateTimeImmutable())
            ->setTimestamp($timestamp);
    }

    private function getNullableString(
        mixed $value
    ): ?string {
        $value = trim((string) $value);

        return $value !== '' ? $value : null;
    }

    private function validateStatus(string $status): void
    {
        if (
            !in_array(
                $status,
                [
                    self::STATUS_COMPLETED,
                    self::STATUS_MISSED,
                ],
                true
            )
        ) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Unsupported process search history status "%s".',
                    $status
                )
            );
        }
    }

    private function isDuplicateKeyException(
        PDOFailed $exception
    ): bool {
        $previous = $exception->getPrevious();

        if (!$previous instanceof \PDOException) {
            return false;
        }

        return (int) ($previous->errorInfo[1] ?? 0)
            === self::DUPLICATE_KEY_ERROR_CODE;
    }

    private function getAppointmentAt(
        ProcessEntity $process
    ): \DateTimeImmutable {
        $appointment = $process->getFirstAppointment();
        $timestamp = (int) $appointment->date;

        if ($timestamp <= 0) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Process %d has no valid appointment date.',
                    $process->getId()
                )
            );
        }

        return $appointment->toDateTime();
    }

    private function getScopeId(ProcessEntity $process): int
    {
        $scopeId = (int) $process->getScopeId();

        if ($scopeId <= 0) {
            throw new \InvalidArgumentException(
                sprintf(
                    'Process %d has no valid scope.',
                    $process->getId()
                )
            );
        }

        return $scopeId;
    }

    public function deleteOlderThan(
        \DateTimeInterface $dateTime
    ): bool {
        $query = new
            \BO\Zmsbackend\ProcessSearchHistory\Repository\ProcessSearchHistory(
                \BO\Zmsbackend\Query\Base::DELETE
            );

        $query->addConditionOlderThan($dateTime);

        return $this->deleteItem($query);
    }
}
