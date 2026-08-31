<?php

namespace BO\Zmsbackend\ProcessSearch\Repository;

class ProcessSearch extends \BO\Zmsbackend\Process\Repository\Process
{
    const string ALIAS = 'process';

    private const array ACTIVE_SEARCH_STATUSES = [
        'reserved',
        'preconfirmed',
        'confirmed',
        'queued',
        'called',
        'processing',
        'pending',
        'parked',
        'missed',
    ];

    #[\Override]
    public function getEntityMapping()
    {
        $mapping = parent::getEntityMapping();

        $mapping['source'] = self::expression(
            '"active"'
        );

        $mapping['appointmentStatus'] = self::expression(
            'CASE
                WHEN process.status = "missed"
                    THEN "missed"
                ELSE "planned"
            END'
        );

        return $mapping;
    }

    public function getHistorySelectSql(
        ?array $scopeIds = null,
        ?\DateTimeInterface $appointmentFrom = null,
        ?string $searchQuery = null,
        array &$parameters = [],
        ?string $date = null,
        ?string $provider = null,
        ?string $service = null,
        array $filters = []
    ): string {
        $conditions = [];

        $this->addHistoryScopeCondition(
            $conditions,
            $scopeIds
        );

        $this->addHistoryAppointmentFromCondition(
            $conditions,
            $parameters,
            $appointmentFrom
        );

        $this->addHistorySearchQueryCondition(
            $conditions,
            $parameters,
            $searchQuery
        );

        $this->addHistoryDateCondition(
            $conditions,
            $parameters,
            $date
        );

        $this->addHistoryProviderCondition(
            $conditions,
            $parameters,
            $provider
        );

        $this->addHistoryServiceCondition(
            $conditions,
            $parameters,
            $service
        );

        $this->addHistoryFilterConditions(
            $conditions,
            $parameters,
            $filters
        );

        $sql = $this->getHistoryBaseSelectSql();

        if ($conditions) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        return $sql;
    }

    private function getHistoryBaseSelectSql(): string
    {
        return '
            SELECT
                history.process_id AS process_id,
                history.display_number AS display_number,
                history.citizen_name AS citizen_name,
                history.telephone AS telephone,
                history.citizen_email AS citizen_email,
                history.amendment AS amendment,
                history.appointment_at AS appointment_at,
                history.booked_at AS booked_at,
                history.called_at AS called_at,
                history.scope_id AS scope_id,
                history.location_name AS location_name,
                history.provider_name AS provider_name,
                CASE
                    WHEN history.status = "completed" THEN "finished"
                    WHEN history.status = "missed" THEN "missed"
                    WHEN history.status = "cancelled_citizen" THEN "deleted"
                    WHEN history.status = "cancelled_staff" THEN "blocked"
                    ELSE NULL
                END AS technical_status,
                CASE
                    WHEN history.status = "completed" THEN "completed"
                    WHEN history.status = "missed" THEN "missed"
                    WHEN history.status = "cancelled_citizen" THEN "cancelled_citizen"
                    WHEN history.status = "cancelled_staff" THEN "cancelled_staff"
                    ELSE NULL
                END AS appointment_status,
                history.finalized_at AS finalized_at,
                "history" AS source,
                history.id AS source_record_id
            FROM process_search_history history
        ';
    }

    private function addHistoryScopeCondition(
        array &$conditions,
        ?array $scopeIds
    ): void {
        if ($scopeIds === null) {
            return;
        }

        $scopeIds = array_values(
            array_unique(
                array_map('intval', $scopeIds)
            )
        );

        if (!$scopeIds) {
            $conditions[] = '1 = 0';
            return;
        }

        $conditions[] = 'history.scope_id IN ('
            . implode(',', $scopeIds)
            . ')';
    }

    private function addHistoryAppointmentFromCondition(
        array &$conditions,
        array &$parameters,
        ?\DateTimeInterface $appointmentFrom
    ): void {
        if ($appointmentFrom === null) {
            return;
        }

        $conditions[] = 'history.appointment_at >= ?';

        $parameters[] = $appointmentFrom
            ->format('Y-m-d H:i:s');
    }

    private function addHistorySearchQueryCondition(
        array &$conditions,
        array &$parameters,
        ?string $searchQuery
    ): void {
        if ($searchQuery === null || trim($searchQuery) === '') {
            return;
        }

        $searchCondition = $this->buildHistorySearchCondition(
            $searchQuery,
            $parameters
        );

        if ($searchCondition !== '') {
            $conditions[] = $searchCondition;
        }
    }

    private function addHistoryDateCondition(
        array &$conditions,
        array &$parameters,
        ?string $date
    ): void {
        if ($date === null || trim($date) === '') {
            return;
        }

        $dateFrom = new \DateTimeImmutable(
            trim($date)
        );

        $conditions[] = '
            history.appointment_at >= ?
            AND history.appointment_at < ?
        ';

        $parameters[] = $dateFrom
            ->setTime(0, 0, 0)
            ->format('Y-m-d H:i:s');

        $parameters[] = $dateFrom
            ->modify('+1 day')
            ->setTime(0, 0, 0)
            ->format('Y-m-d H:i:s');
    }

    private function addHistoryProviderCondition(
        array &$conditions,
        array &$parameters,
        ?string $provider
    ): void {
        if ($provider === null || trim($provider) === '') {
            return;
        }

        $provider = '%'
            . $this->escapeHistoryLikeValue(
                trim($provider)
            )
            . '%';

        $conditions[] = '(
            history.location_name LIKE ?
            OR history.provider_name LIKE ?
        )';

        $parameters[] = $provider;
        $parameters[] = $provider;
    }

    private function addHistoryServiceCondition(
        array &$conditions,
        array &$parameters,
        ?string $service
    ): void {
        if ($service === null || trim($service) === '') {
            return;
        }

        $conditions[] = '
            history.services LIKE ?
        ';

        $parameters[] = '%'
            . $this->escapeHistoryLikeValue(
                trim($service)
            )
            . '%';
    }

    private function addHistoryFilterConditions(
        array &$conditions,
        array &$parameters,
        array $filters
    ): void {
        if (!empty($filters['denyHistory'])) {
            $conditions[] = '1 = 0';
        }

        $this->addHistoryNameFilter(
            $conditions,
            $parameters,
            $filters
        );

        $this->addHistoryAmendmentFilter(
            $conditions,
            $parameters,
            $filters
        );

        $this->addHistoryProcessIdFilter(
            $conditions,
            $parameters,
            $filters
        );

        $this->addHistoryScopeIdFilter(
            $conditions,
            $parameters,
            $filters
        );

        $this->addHistoryAppointmentStatusFilter(
            $conditions,
            $parameters,
            $filters
        );
    }

    private function addHistoryAppointmentStatusFilter(
        array &$conditions,
        array &$parameters,
        array $filters
    ): void {
        $status = isset($filters['appointmentStatus'])
            ? trim((string) $filters['appointmentStatus'])
            : '';

        if ($status === '') {
            return;
        }

        if ($status === 'planned') {
            $conditions[] = '1 = 0';
            return;
        }

        $historyStatus = match ($status) {
            'completed' => 'completed',
            'missed' => 'missed',
            'cancelled_citizen' => 'cancelled_citizen',
            'cancelled_staff' => 'cancelled_staff',
            default => null,
        };

        if ($historyStatus === null) {
            return;
        }

        $conditions[] = 'history.status = ?';
        $parameters[] = $historyStatus;
    }

    private function addHistoryNameFilter(
        array &$conditions,
        array &$parameters,
        array $filters
    ): void {
        if (
            !isset($filters['name'])
            || trim((string) $filters['name']) === ''
        ) {
            return;
        }

        $name = trim(
            (string) $filters['name']
        );

        if (!empty($filters['exact'])) {
            $conditions[] = 'history.citizen_name = ?';
            $parameters[] = $name;
            return;
        }

        $conditions[] = 'history.citizen_name LIKE ?';

        $parameters[] = '%'
            . $this->escapeHistoryLikeValue($name)
            . '%';
    }

    private function addHistoryAmendmentFilter(
        array &$conditions,
        array &$parameters,
        array $filters
    ): void {
        if (
            !isset($filters['amendment'])
            || trim((string) $filters['amendment']) === ''
        ) {
            return;
        }

        $conditions[] = 'history.amendment LIKE ?';

        $parameters[] = '%'
            . $this->escapeHistoryLikeValue(
                trim((string) $filters['amendment'])
            )
            . '%';
    }

    private function addHistoryProcessIdFilter(
        array &$conditions,
        array &$parameters,
        array $filters
    ): void {
        if (
            !isset($filters['processId'])
            || !$filters['processId']
        ) {
            return;
        }

        $conditions[] = 'history.process_id = ?';
        $parameters[] = (int) $filters['processId'];
    }

    private function addHistoryScopeIdFilter(
        array &$conditions,
        array &$parameters,
        array $filters
    ): void {
        if (
            !isset($filters['scopeId'])
            || !$filters['scopeId']
        ) {
            return;
        }

        $conditions[] = 'history.scope_id = ?';
        $parameters[] = (int) $filters['scopeId'];
    }

    private function buildHistorySearchCondition(
        string $queryString,
        array &$parameters
    ): string {
        $queryString = trim($queryString);

        if ($queryString === '') {
            return '';
        }

        preg_match_all(
            '/"([^"]+)"|(\S+)/u',
            $queryString,
            $matches,
            PREG_SET_ORDER
        );

        $terms = [];

        foreach ($matches as $match) {
            $value = trim(
                $match[1] !== ''
                    ? $match[1]
                    : $match[2]
            );

            if ($value === '') {
                continue;
            }

            $terms[] = [
                'value' => $value,
                'quoted' => $match[1] !== '',
            ];
        }

        if (!$terms) {
            return '';
        }

        $conditions = [];
        $singleTerm = count($terms) === 1;

        foreach ($terms as $term) {
            $conditions[] = $this->buildHistorySearchTermCondition(
                $term['value'],
                $term['quoted'],
                $parameters,
                $singleTerm
            );
        }

        return '(' . implode(' AND ', $conditions) . ')';
    }

    private function buildHistorySearchTermCondition(
        string $term,
        bool $quoted,
        array &$parameters,
        bool $singleTerm = false
    ): string {
        $escapedTerm = $this->escapeHistoryLikeValue($term);
        $contains = '%' . $escapedTerm . '%';

        $conditions = [];
        $isNumeric = preg_match('#^\d+$#', $term);

        if ($singleTerm && $isNumeric) {
            $conditions[] = 'history.process_id = '
                . $this->addHistorySearchParameter(
                    $parameters,
                    (int) $term
                );
        }

        if ($quoted) {
            $conditions[] = $this->buildHistoryNameCondition(
                $term,
                $parameters,
                true
            );
        } elseif (!$isNumeric && mb_strlen($term) <= 3) {
            $conditions[] = $this->buildHistoryNameCondition(
                $term,
                $parameters,
                true,
                true
            );
        } else {
            $conditions[] = 'history.citizen_name LIKE '
                . $this->addHistorySearchParameter(
                    $parameters,
                    $contains
                );
        }

        foreach (
            [
                'history.citizen_email',
                'history.telephone',
                'history.display_number',
            ] as $column
        ) {
            $conditions[] = $column . ' LIKE '
                . $this->addHistorySearchParameter(
                    $parameters,
                    $contains
                );
        }

        return '(' . implode(' OR ', $conditions) . ')';
    }

    private function buildHistoryNameCondition(
        string $term,
        array &$parameters,
        bool $wordBoundaryOnly = false,
        bool $includeWordPrefix = false
    ): string {
        $escaped = $this->escapeHistoryLikeValue($term);

        $conditions = [
            'history.citizen_name LIKE '
                . $this->addHistorySearchParameter(
                    $parameters,
                    $escaped . ' %'
                ),

            'history.citizen_name LIKE '
                . $this->addHistorySearchParameter(
                    $parameters,
                    '% ' . $escaped
                ),

            'history.citizen_name LIKE '
                . $this->addHistorySearchParameter(
                    $parameters,
                    '% ' . $escaped . ' %'
                ),

            'history.citizen_name = '
                . $this->addHistorySearchParameter(
                    $parameters,
                    $term
                ),
        ];

        if ($wordBoundaryOnly) {
            if ($includeWordPrefix) {
                $conditions[] = 'history.citizen_name LIKE '
                    . $this->addHistorySearchParameter(
                        $parameters,
                        $escaped . '%'
                    );

                $conditions[] = 'history.citizen_name LIKE '
                    . $this->addHistorySearchParameter(
                        $parameters,
                        '% ' . $escaped . '%'
                    );
            }
        } else {
            $conditions[] = 'history.citizen_name LIKE '
                . $this->addHistorySearchParameter(
                    $parameters,
                    '%' . $escaped . '%'
                );
        }

        return '(' . implode(' OR ', $conditions) . ')';
    }

    private function addHistorySearchParameter(
        array &$parameters,
        mixed $value
    ): string {
        $parameters[] = $value;

        return '?';
    }

    private function escapeHistoryLikeValue(string $value): string
    {
        return str_replace(
            ['\\', '%', '_'],
            ['\\\\', '\\%', '\\_'],
            $value
        );
    }

    public function addConditionActiveSearchStatuses(): self
    {
        $this->query->whereIn(
            'process.status',
            self::ACTIVE_SEARCH_STATUSES
        );

        return $this;
    }

    public function addConditionAppointmentStatusFilter(string $status): self
    {
        $status = trim($status);

        if ($status === '') {
            return $this;
        }

        if (
            in_array(
                $status,
                ['completed', 'cancelled_citizen', 'cancelled_staff'],
                true
            )
        ) {
            $this->query->where('process.status', '=', $status);
            return $this;
        }

        if ($status === 'missed') {
            $this->query->where('process.status', '=', 'missed');
            return $this;
        }

        if ($status === 'planned') {
            $this->query->where('process.status', '!=', 'missed');
        }

        return $this;
    }

    public function addCombinedActiveProjection(): self
    {
        $this->leftJoin(
            new \BO\Zmsbackend\Query\Alias(
                \BO\Zmsbackend\Query\Scope::TABLE,
                'search_scope'
            ),
            self::expression(
                'IF(
                    `process`.`AbholortID`,
                    `process`.`AbholortID`,
                    `process`.`StandortID`
                )'
            ),
            '=',
            'search_scope.StandortID'
        );

        $this->leftJoin(
            new \BO\Zmsbackend\Query\Alias(
                \BO\Zmsbackend\Provider\Repository\Provider::TABLE,
                'search_scope_provider'
            ),
            self::expression(
                'search_scope.InfoDienstleisterID = search_scope_provider.id
                AND search_scope.source = search_scope_provider.source'
            )
        );

        $mapping = $this->getEntityMapping();

        $this->query->resetSelect();

        $this->query->select([
            'process_id' => $mapping['id'],

            'display_number' => $mapping['displayNumber'],

            'citizen_name' => $mapping['clients__0__familyName'],

            'telephone' => $mapping['clients__0__telephone'],

            'citizen_email' => $mapping['clients__0__email'],

            'amendment' => $mapping['amendment'],

            'appointment_at' => $mapping['appointments__0__date'],

            'booked_at' => 'process.IPTimeStamp',

            'called_at' => self::expression(
                'CASE
                    WHEN process.aufrufzeit IS NULL
                        OR process.aufrufzeit = "00:00:00"
                        THEN NULL
                    ELSE CONCAT(
                        process.Datum,
                        " ",
                        process.aufrufzeit
                    )
                END'
            ),

            'scope_id' => $mapping['scope__id'],

            'location_name' => 'search_scope.standortkuerzel',

            'provider_name' => 'search_scope_provider.name',

            'technical_status' => $mapping['status'],

            'appointment_status' => $mapping['appointmentStatus'],

            'finalized_at' => self::expression('NULL'),

            'source' => $mapping['source'],

            'source_record_id' => $mapping['id'],
        ]);

        return $this;
    }

    public function getCombinedSelectSql(
        string $historySql
    ): string {
        return $this->getSql()
            . "\nUNION ALL\n"
            . $historySql;
    }
}
