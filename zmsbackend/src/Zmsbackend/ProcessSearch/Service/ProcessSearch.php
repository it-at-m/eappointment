<?php

namespace BO\Zmsbackend\ProcessSearch\Service;

use BO\Zmsbackend\ProcessSearch\Repository\ProcessSearch as ProcessSearchRepository;
use BO\Zmsbackend\Query\Base as QueryBase;
use BO\Zmsentities\Collection\ProcessList as Collection;
use BO\Zmsentities\Process as Entity;
use BO\Zmsbackend\Config\Service\Config as ConfigService;

class ProcessSearch extends \BO\Zmsbackend\Base
{
    private const int DEFAULT_HISTORY_DAYS = 90;

    private const array APPOINTMENT_STATUS_FILTERS = [
        'planned',
        'completed',
        'missed',
        'cancelled_citizen',
        'cancelled_staff',
    ];

    public function mapSearchRowToProcess(array $row): Entity
    {
        return new Entity([
            'id' => (int) $row['process_id'],
            'displayNumber' => (string) ($row['display_number'] ?? ''),

            'clients__0__familyName' => (string) ($row['citizen_name'] ?? ''),
            'clients__0__telephone' => (string) ($row['telephone'] ?? ''),
            'clients__0__email' => (string) ($row['citizen_email'] ?? ''),

            'amendment' => (string) ($row['amendment'] ?? ''),

            'appointments__0__date' => !empty($row['appointment_at'])
                ? strtotime($row['appointment_at'])
                : 0,

            'appointments__0__scope__id' => (int) $row['scope_id'],

            'createTimestamp' => ($row['source'] ?? '') === 'active'
                ? (int) ($row['booked_at'] ?? 0)
                : (!empty($row['booked_at'])
                    ? strtotime($row['booked_at'])
                    : 0),

            'queue__callTime' => !empty($row['called_at'])
                ? strtotime($row['called_at'])
                : 0,

            'scope__id' => (int) $row['scope_id'],
            'scope__shortName' => (string) ($row['location_name'] ?? ''),
            'scope__contact__name' => (string) ($row['provider_name'] ?? ''),

            'status' => (string) $row['technical_status'],
            'source' => (string) $row['source'],
            'appointmentStatus' => (string) $row['appointment_status'],
            'finalizedAt' => !empty($row['finalized_at'])
                ? strtotime((string) $row['finalized_at'])
                : 0,
        ]);
    }

    public function readSearch(
        array $parameter,
        int $resolveReferences = 0,
        int $limit = 100,
        int $offset = 0
    ): Collection {
        $combined = $this->buildCombinedSearchSql(
            $parameter,
            $this->getHistoryAppointmentFrom(),
            (int) $limit,
            (int) $offset
        );

        $rows = $this->fetchAll(
            $combined['sql'],
            $combined['parameters']
        );

        $processList = new Collection();

        foreach ($rows as $row) {
            $entity = $this->mapSearchRowToProcess($row);

            $entity = $this->resolveSearchReferences(
                $entity,
                $resolveReferences
            );

            $processList->addEntity($entity);
        }

        return $processList;
    }

    public function readSearchCount(array $parameter): int
    {
        $combined = $this->buildCombinedSearchCountSql(
            $parameter,
            $this->getHistoryAppointmentFrom()
        );

        return (int) $this->fetchValue(
            $combined['sql'],
            $combined['parameters']
        );
    }

    protected function buildSearchQuery(
        array $parameter
    ): ProcessSearchRepository {
        $query = new ProcessSearchRepository(QueryBase::SELECT);

        $query
            ->addConditionAssigned()
            ->addConditionIgnoreSlots()
            ->addConditionActiveSearchStatuses();

        $appointmentStatus = $this->normalizeAppointmentStatusFilter(
            $parameter['status'] ?? null
        );

        if ($appointmentStatus !== null) {
            $query->addConditionAppointmentStatusFilter(
                $appointmentStatus
            );
        }

        if (!empty($parameter['upcomingOnly'])) {
            $now = class_exists('\App') && isset(\App::$now)
                ? \App::$now
                : new \DateTimeImmutable(
                    'now',
                    new \DateTimeZone('Europe/Berlin')
                );

            $query->addConditionUpcomingOnly($now);
        }

        if (array_key_exists('scopeIds', $parameter)) {
            $scopeIds = is_array($parameter['scopeIds'])
                ? $parameter['scopeIds']
                : array_map(
                    'intval',
                    explode(',', (string) $parameter['scopeIds'])
                );

            $scopeIds = array_values(
                array_filter($scopeIds)
            );

            $query->addConditionScopeIds($scopeIds);
        }

        if (isset($parameter['query'])) {
            if (preg_match('#^\d+$#', $parameter['query'])) {
                $query->addConditionProcessId(
                    $parameter['query']
                );

                $query->addConditionSearch(
                    $parameter['query'],
                    true
                );
            } else {
                $query->addConditionSearch(
                    $parameter['query']
                );
            }

            unset($parameter['query']);
        }

        if (count($parameter)) {
            foreach (
                [
                    'upcomingOnly',
                    'scopeIds',
                    'page',
                    'limit',
                    'offset',
                    'includePast',
                    'denyHistory',
                    'status',
                ] as $reservedKey
            ) {
                unset($parameter[$reservedKey]);
            }

            $query = $this->addSearchConditions(
                $query,
                $parameter
            );
        }

        return $query;
    }

    protected function extractSearchQuery(array $parameter): ?string
    {
        if (!isset($parameter['query'])) {
            return null;
        }

        $queryString = trim((string) $parameter['query']);

        return $queryString !== '' ? $queryString : null;
    }

    protected function addSearchConditions(
        ProcessSearchRepository $query,
        array $parameter
    ): ProcessSearchRepository {
        $this->addBasicSearchConditions(
            $query,
            $parameter
        );

        $this->addAdditionalSearchConditions(
            $query,
            $parameter
        );

        return $query;
    }

    private function addBasicSearchConditions(
        ProcessSearchRepository $query,
        array $parameter
    ): void {
        if (isset($parameter['processId']) && $parameter['processId']) {
            $query->addConditionProcessId(
                $parameter['processId']
            );
        }

        if (isset($parameter['name']) && $parameter['name']) {
            $exact = isset($parameter['exact'])
                ? $parameter['exact']
                : false;

            $query->addConditionName(
                $parameter['name'],
                $exact
            );
        }

        if (isset($parameter['amendment']) && $parameter['amendment']) {
            $query->addConditionAmendment(
                $parameter['amendment']
            );
        }

        if (isset($parameter['scopeId']) && $parameter['scopeId']) {
            $query->addConditionScopeId(
                $parameter['scopeId']
            );
        }
    }

    private function addAdditionalSearchConditions(
        ProcessSearchRepository $query,
        array $parameter
    ): void {
        if (isset($parameter['authKey']) && $parameter['authKey']) {
            $query->addConditionAuthKey(
                $parameter['authKey']
            );
        }

        if (isset($parameter['requestId']) && $parameter['requestId']) {
            $query->addConditionRequestId(
                $parameter['requestId']
            );
        }

        if (isset($parameter['provider']) && $parameter['provider']) {
            $query->addConditionScopeNameSearch(
                $parameter['provider']
            );
        }

        if (isset($parameter['service']) && $parameter['service']) {
            $query->addConditionServiceNameSearch(
                $parameter['service']
            );
        }

        if (isset($parameter['date']) && $parameter['date']) {
            $query->addConditionDate(
                $parameter['date']
            );
        }
    }

    protected function resolveSearchReferences(
        Entity $entity,
        int $resolveReferences
    ): Entity {
        if (($entity->source ?? 'active') === 'history') {
            return $entity;
        }

        (new \BO\Zmsbackend\Process\Service\Process())
            ->readResolvedReferences(
                $entity,
                $resolveReferences
            );

        return $entity;
    }

    protected function buildCombinedSearchSql(
        array $parameter,
        ?\DateTimeInterface $appointmentFrom = null,
        ?int $limit = null,
        int $offset = 0
    ): array {
        $appointmentStatus = $this->normalizeAppointmentStatusFilter(
            $parameter['status'] ?? null
        );
        $includeActive = $this->includesActiveAppointmentStatus(
            $appointmentStatus
        );

        $searchRepository = $includeActive
            ? $this->buildSearchQuery($parameter)
            : new ProcessSearchRepository(QueryBase::SELECT);

        if ($includeActive) {
            $searchRepository->addCombinedActiveProjection();
        }

        $historyParameters = [];
        $historySql = $searchRepository->getHistorySelectSql(
            $this->readScopeIdsParameter($parameter),
            $appointmentFrom,
            $this->extractSearchQuery($parameter),
            $historyParameters,
            $parameter['date'] ?? null,
            $parameter['provider'] ?? null,
            $parameter['service'] ?? null,
            [
                'name' => $parameter['name'] ?? null,
                'exact' => !empty($parameter['exact']),
                'amendment' => $parameter['amendment'] ?? null,
                'processId' => $parameter['processId'] ?? null,
                'scopeId' => $parameter['scopeId'] ?? null,
                'denyHistory' => $this->shouldDenyHistory($parameter),
                'appointmentStatus' => $appointmentStatus,
            ]
        );

        $combinedSql = $includeActive
            ? $searchRepository->getCombinedSelectSql($historySql)
            : $historySql;

        return [
            'sql' => $this->wrapCombinedSearchSql(
                $combinedSql,
                $limit,
                $offset
            ),
            'parameters' => $includeActive
                ? array_merge(
                    $searchRepository->getParameters(),
                    $historyParameters
                )
                : $historyParameters,
        ];
    }

    private function includesActiveAppointmentStatus(?string $status): bool
    {
        return !in_array(
            $status,
            ['completed', 'cancelled_citizen', 'cancelled_staff'],
            true
        );
    }

    private function shouldDenyHistory(array $parameter): bool
    {
        return !empty($parameter['denyHistory'])
            || !empty($parameter['authKey'])
            || !empty($parameter['requestId'])
            || !empty($parameter['upcomingOnly']);
    }

    private function readScopeIdsParameter(array $parameter): ?array
    {
        if (!array_key_exists('scopeIds', $parameter)) {
            return null;
        }

        $scopeIds = is_array($parameter['scopeIds'])
            ? $parameter['scopeIds']
            : explode(',', (string) $parameter['scopeIds']);

        return array_values(
            array_filter(
                array_map('intval', $scopeIds)
            )
        );
    }

    private function wrapCombinedSearchSql(
        string $combinedSql,
        ?int $limit,
        int $offset
    ): string {
        $sql = '
            SELECT *
            FROM (
                ' . $combinedSql . '
            ) combined
            ORDER BY
                combined.appointment_at DESC,
                combined.source_record_id DESC,
                combined.source ASC
        ';

        if ($limit === null) {
            return $sql;
        }

        $sql .= ' LIMIT ' . max(0, $limit);

        if ($offset > 0) {
            $sql .= ' OFFSET ' . max(0, $offset);
        }

        return $sql;
    }

    protected function buildCombinedSearchCountSql(
        array $parameter,
        ?\DateTimeInterface $appointmentFrom = null
    ): array {
        $combined = $this->buildCombinedSearchSql(
            $parameter,
            $appointmentFrom
        );

        $sql = '
            SELECT COUNT(*) AS total_count
            FROM (
                ' . $combined['sql'] . '
            ) combined_count
        ';

        return [
            'sql' => $sql,
            'parameters' => $combined['parameters'],
        ];
    }

    private function normalizeAppointmentStatusFilter(mixed $status): ?string
    {
        $status = trim((string) $status);

        if (
            $status === ''
            || !in_array($status, self::APPOINTMENT_STATUS_FILTERS, true)
        ) {
            return null;
        }

        return $status;
    }

    protected function getHistoryAppointmentFrom(): \DateTimeImmutable
    {
        $now = class_exists('\App') && isset(\App::$now)
            ? \App::$now
            : new \DateTimeImmutable(
                'now',
                new \DateTimeZone('Europe/Berlin')
            );

        $config = (new ConfigService())->readEntity();

        $retentionDays = filter_var(
            $config->getPreference(
                'processSearchHistory',
                'deleteOlderThanDays'
            ),
            FILTER_VALIDATE_INT,
            [
                'options' => [
                    'min_range' => 1,
                ],
            ]
        );

        if ($retentionDays === false) {
            $retentionDays = self::DEFAULT_HISTORY_DAYS;
        }

        return \DateTimeImmutable::createFromInterface($now)
            ->modify('-' . $retentionDays . ' days');
    }
}
