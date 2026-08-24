<?php

namespace BO\Zmsbackend\ProcessSearchHistory\Repository;

class ProcessSearchHistory extends \BO\Zmsbackend\Query\Base implements
    \BO\Zmsbackend\Query\MappingInterface
{
    public const string TABLE = 'process_search_history';
    public const string ALIAS = 'psh';

    #[\Override]
    public function getEntityMapping(): array
    {
        return [
            'id' => self::ALIAS . '.id',
            'historyKey' => self::ALIAS . '.history_key',

            'processId' => self::ALIAS . '.process_id',
            'scopeId' => self::ALIAS . '.scope_id',
            'displayNumber' => self::ALIAS . '.display_number',

            'appointmentAt' => self::ALIAS . '.appointment_at',
            'bookedAt' => self::ALIAS . '.booked_at',
            'calledAt' => self::ALIAS . '.called_at',
            'finalizedAt' => self::ALIAS . '.finalized_at',

            'status' => self::ALIAS . '.status',

            'citizenName' => self::ALIAS . '.citizen_name',
            'telephone' => self::ALIAS . '.telephone',
            'citizenEmail' => self::ALIAS . '.citizen_email',
            'amendment' => self::ALIAS . '.amendment',

            'locationName' => self::ALIAS . '.location_name',
            'providerName' => self::ALIAS . '.provider_name',
            'services' => self::ALIAS . '.services',

            'createdAt' => self::ALIAS . '.created_at',
        ];
    }

    public function addConditionHistoryKey(string $historyKey): self
    {
        $this->query->where(
            self::ALIAS . '.history_key',
            '=',
            $historyKey
        );

        return $this;
    }

    public function addConditionOlderThan(
        \DateTimeInterface $dateTime
    ): self {
        $this->query->where(
            self::ALIAS . '.appointment_at',
            '<',
            $this->formatDateTime($dateTime)
        );

        return $this;
    }

    public function addCountValue(): self
    {
        $this->query->select([
            'historyCount' => self::expression('COUNT(*)'),
        ]);

        return $this;
    }

    public function addValuesNewHistory(array $data): self
    {
        $values = [
            'history_key' => $data['historyKey'],

            'process_id' => $data['processId'],
            'scope_id' => $data['scopeId'],
            'display_number' => $data['displayNumber'] ?? null,

            'appointment_at' => $this->formatDateTime(
                $data['appointmentAt']
            ),
            'booked_at' => $this->formatNullableDateTime(
                $data['bookedAt'] ?? null
            ),
            'called_at' => $this->formatNullableDateTime(
                $data['calledAt'] ?? null
            ),
            'finalized_at' => $this->formatDateTime(
                $data['finalizedAt']
            ),

            'status' => $data['status'],

            'citizen_name' => $data['citizenName'],
            'telephone' => $data['telephone'],
            'citizen_email' => $data['citizenEmail'],
            'amendment' => $data['amendment'] ?? null,

            'location_name' => $data['locationName'],
            'provider_name' => $data['providerName'],
            'services' => $data['services'] ?? null,
        ];

        if (isset($data['createdAt'])) {
            $values['created_at'] = $this->formatDateTime(
                $data['createdAt']
            );
        }

        $this->addValues($values);

        return $this;
    }

    private function formatDateTime(
        \DateTimeInterface $dateTime
    ): string {
        return $dateTime->format('Y-m-d H:i:s');
    }

    private function formatNullableDateTime(
        ?\DateTimeInterface $dateTime
    ): ?string {
        return $dateTime?->format('Y-m-d H:i:s');
    }
}
