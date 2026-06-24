<?php

namespace BO\Zmsdb;

use BO\Zmsentities\Collection\LogList;
use BO\Zmsentities\Collection\RequestList;
use BO\Zmsentities\Log as Entity;
use DateTime;

/**
 * Logging for actions
 *
 */
class Log extends Base
{
    const PROCESS = 'buerger';
    const MIGRATION = 'migration';
    const ERROR = 'error';

    const ACTION_MAIL_SUCCESS = 'E-Mail-Versand erfolgreich';
    const ACTION_MAIL_FAIL = 'E-Mail-Versand ist fehlgeschlagen';
    const ACTION_STATUS_CHANGE = 'Terminstatus wurde geändert';
    const ACTION_SEND_REMINDER = 'Erinnerungsmail wurde gesendet';
    const ACTION_REMOVED = 'Termin aus der Warteschlange entfernt';
    const ACTION_CALLED = 'Termin wurde aufgerufen';
    const ACTION_ARCHIVED = 'Termin wurde archiviert';
    const ACTION_EDITED = 'Termin wurde geändert';
    const ACTION_REDIRECTED = 'Termin wurde weitergeleitet';
    const ACTION_NEW = 'Neuer Termin wurde erstellt';
    const ACTION_DELETED = 'Termin wurde gelöscht';
    const ACTION_CANCELED = 'Termin wurde abgesagt';

    private const INDEXED_COLUMNS = [
        'action',
        'display_number',
        'queue_number',
        'appointment_at',
        'slot_count',
        'client_name',
        'services',
        'scope_name',
        'client_email',
        'client_phone',
        'process_status',
        'db_status',
    ];

    private const ACTION_LABEL_TO_CODE = [
        self::ACTION_MAIL_SUCCESS => 'mail_success',
        self::ACTION_MAIL_FAIL => 'mail_fail',
        self::ACTION_STATUS_CHANGE => 'status_changed',
        self::ACTION_SEND_REMINDER => 'reminder_sent',
        self::ACTION_REMOVED => 'removed_from_queue',
        self::ACTION_CALLED => 'called',
        self::ACTION_ARCHIVED => 'archived',
        self::ACTION_EDITED => 'edited',
        self::ACTION_REDIRECTED => 'redirected',
        self::ACTION_NEW => 'created',
        self::ACTION_DELETED => 'deleted',
        self::ACTION_CANCELED => 'canceled',
    ];

    public static $operator = 'lib';

    public static function writeLogEntry(
        $message,
        $referenceId,
        $type = self::PROCESS,
        ?int $scopeId = null,
        ?string $userId = null,
        ?string $data = null,
        ?array $indexedFields = null
    ) {
        $message .= " [" . static::$operator . "]";
        $log = new static();
        $setParts = [
            '`message`=:message',
            '`reference_id`=:referenceId',
            '`type`=:type',
            '`scope_id`=:scopeId',
            '`user_id`=:userId',
            '`data`=:dataString',
        ];
        $parameters = [
            'message' => $message . static::backtraceLogEntry(),
            'referenceId' => $referenceId,
            'type' => $type,
            'scopeId' => $scopeId,
            'userId' => $userId,
            'dataString' => $data,
        ];

        if ($indexedFields !== null) {
            foreach (self::INDEXED_COLUMNS as $column) {
                if (!array_key_exists($column, $indexedFields)) {
                    continue;
                }
                $setParts[] = '`' . $column . '`=:' . $column;
                $parameters[$column] = $indexedFields[$column];
            }
        }

        $sql = 'INSERT INTO `log` SET ' . implode(', ', $setParts);

        return $log->perform($sql, $parameters);
    }

    public static function writeProcessLog(
        string $method,
        string $action,
        ?\BO\Zmsentities\Process $process,
        ?\BO\Zmsentities\Useraccount $userAccount = null
    ) {
        if (empty($process) || empty($process->getId()) || empty($userAccount)) {
            return;
        }

        $requests = new RequestList();
        if (!empty($process->getRequestIds())) {
            $requests = (new Request())->readRequestsByIds($process->getRequestIds());
        }

        $payload = self::buildProcessLogPayload($process, $action, $userAccount, $requests);
        $data = json_encode($payload['display'], JSON_UNESCAPED_UNICODE);

        Log::writeLogEntry(
            $method,
            $process->getId(),
            self::PROCESS,
            $process->getScopeId(),
            $userAccount->getId(),
            $data,
            $payload['indexed']
        );
    }

    /**
     * @return array{display: array<string, mixed>, indexed: array<string, mixed>}
     */
    public static function buildProcessLogPayload(
        \BO\Zmsentities\Process $process,
        string $action,
        \BO\Zmsentities\Useraccount $userAccount,
        RequestList $requests
    ): array {
        $appointmentAt = $process->getFirstAppointment()->toDateTime();
        $display = array_filter([
            'Aktion' => $action,
            'Sachbearbeiter*in' => $userAccount->getId(),
            'Terminnummer' => $process->getDisplayNumber(),
            'Wartenummer' => $process->getQueueNumber(),
            'Terminzeit' => $appointmentAt->format('d.m.Y H:i:s'),
            'Slots' => $process->getFirstAppointment()->slotCount ?? null,
            'Bürger*in' => $process->getFirstClient()->familyName,
            'Dienstleistungen' => implode(', ', array_map(function ($request) {
                return $request->getName();
            }, $requests->getAsArray())),
            'Anmerkung' => $process->getAmendment(),
            'Standort' => $process->scope->getName(),
            'E-Mail' => $process->getFirstClient()->email,
            'Telefon' => $process->getFirstClient()->telephone,
            'Status' => $process->getStatus(),
            'DB Status' => $process->dbstatus,
        ], static function ($value) {
            return $value !== null && $value !== '';
        });

        $indexed = array_filter([
            'action' => self::actionCodeFromLabel($action),
            'display_number' => $process->getDisplayNumber(),
            'queue_number' => $process->getQueueNumber(),
            'appointment_at' => $appointmentAt->format('Y-m-d H:i:s'),
            'slot_count' => $process->getFirstAppointment()->slotCount ?? null,
            'client_name' => $process->getFirstClient()->familyName,
            'services' => implode(', ', array_map(function ($request) {
                return $request->getName();
            }, $requests->getAsArray())),
            'scope_name' => $process->scope->getName(),
            'client_email' => $process->getFirstClient()->email,
            'client_phone' => $process->getFirstClient()->telephone,
            'process_status' => $process->getStatus(),
            'db_status' => $process->dbstatus,
        ], static function ($value) {
            return $value !== null && $value !== '';
        });

        return [
            'display' => $display,
            'indexed' => $indexed,
        ];
    }

    public static function actionCodeFromLabel(string $label): ?string
    {
        return self::ACTION_LABEL_TO_CODE[$label] ?? null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public static function parseLegacyLogData(?string $dataJson): ?array
    {
        if ($dataJson === null || $dataJson === '') {
            return null;
        }

        $display = json_decode($dataJson, true);
        if (!is_array($display)) {
            return null;
        }

        $actionLabel = $display['Aktion'] ?? null;
        $appointmentAt = null;
        if (!empty($display['Terminzeit'])) {
            $parsed = \DateTimeImmutable::createFromFormat('d.m.Y H:i:s', (string) $display['Terminzeit']);
            if ($parsed instanceof \DateTimeImmutable) {
                $appointmentAt = $parsed->format('Y-m-d H:i:s');
            }
        }

        return array_filter([
            'action' => is_string($actionLabel) ? self::actionCodeFromLabel($actionLabel) : null,
            'display_number' => $display['Terminnummer'] ?? null,
            'queue_number' => isset($display['Wartenummer']) ? (int) $display['Wartenummer'] : null,
            'appointment_at' => $appointmentAt,
            'slot_count' => isset($display['Slots']) ? (int) $display['Slots'] : null,
            'client_name' => $display['Bürger*in'] ?? null,
            'services' => $display['Dienstleistungen'] ?? null,
            'scope_name' => $display['Standort'] ?? null,
            'client_email' => $display['E-Mail'] ?? null,
            'client_phone' => $display['Telefon'] ?? null,
            'process_status' => $display['Status'] ?? null,
            'db_status' => $display['DB Status'] ?? null,
        ], static function ($value) {
            return $value !== null && $value !== '';
        });
    }

    /**
     * @return array{updated: int, lastLogId: int}
     */
    public function backfillIndexedColumns(int $limit = 5000, int $afterLogId = 0): array
    {
        $limit = max(1, min(10000, $limit));
        $sql = 'SELECT log_id, data FROM log
            WHERE type = :type
              AND data IS NOT NULL
              AND client_name IS NULL
              AND log_id > :afterLogId
            ORDER BY log_id ASC
            LIMIT ' . $limit;
        $rows = $this->fetchAll($sql, [
            'type' => self::PROCESS,
            'afterLogId' => $afterLogId,
        ]);

        $updated = 0;
        $lastLogId = $afterLogId;
        foreach ($rows as $row) {
            $lastLogId = (int) $row['log_id'];
            $indexed = self::parseLegacyLogData($row['data'] ?? null);
            if ($indexed === null || $indexed === []) {
                continue;
            }

            $setParts = [];
            $parameters = ['logId' => $lastLogId];
            foreach (self::INDEXED_COLUMNS as $column) {
                if (!array_key_exists($column, $indexed)) {
                    continue;
                }
                $setParts[] = '`' . $column . '`=:' . $column;
                $parameters[$column] = $indexed[$column];
            }

            if ($setParts === []) {
                continue;
            }

            $updateSql = 'UPDATE log SET ' . implode(', ', $setParts) . ' WHERE log_id = :logId';
            $this->perform($updateSql, $parameters);
            $updated++;
        }

        return [
            'updated' => $updated,
            'lastLogId' => $lastLogId,
        ];
    }

    public function readByProcessId($processId)
    {
        $query = new Query\Log(Query\Base::SELECT);
        $query->addEntityMapping();
        $query->addConditionProcessId($processId);
        $logList = new \BO\Zmsentities\Collection\LogList($this->fetchList($query, new Entity()));
        return $logList;
    }

    public function readByProcessData(
        $generalSearch,
        $service,
        $provider,
        $date,
        $userAction,
        $page = 1,
        $perPage = 100,
        ?array $scopeIds = null
    ) {
        $fieldValues = [];
        if ($provider) {
            $fieldValues['scope_name'] = $provider;
        }

        if ($service) {
            $fieldValues['services'] = $service;
        }

        return $this->getBySearchParams(
            $fieldValues,
            $generalSearch,
            $userAction,
            $date,
            $perPage,
            ($page - 1) * $perPage,
            $scopeIds
        );
    }

    public function getBySearchParams(
        array $fieldValues,
        ?string $generalSearch,
        int $userAction,
        ?DateTime $date,
        int $perPage,
        int $offset,
        ?array $scopeIds = null
    ) {
        $sql = 'SELECT * FROM log';
        $conditions = [];
        $params = [];

        foreach ($fieldValues as $field => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $likeValue = '%' . $this->escapeLikeValue((string) $value) . '%';
            if ($field === 'scope_name') {
                $conditions[] = '(scope_name LIKE :scopeName OR (scope_name IS NULL AND data LIKE :scopeNameLegacy))';
                $params['scopeName'] = $likeValue;
                $params['scopeNameLegacy'] = '%' . $this->escapeLikeValue('Standort') . '%'
                    . $this->escapeLikeValue((string) $value) . '%';
                continue;
            }

            if ($field === 'services') {
                $conditions[] = '(services LIKE :services OR (services IS NULL AND data LIKE :servicesLegacy))';
                $params['services'] = $likeValue;
                $params['servicesLegacy'] = '%' . $this->escapeLikeValue('Dienstleistungen') . '%'
                    . $this->escapeLikeValue((string) $value) . '%';
            }
        }

        if (!empty($generalSearch)) {
            $generalSearch = trim((string) $generalSearch);
            foreach ($this->buildGeneralSearchConditions($generalSearch, $params) as $searchCondition) {
                $conditions[] = $searchCondition;
            }
        }

        if (!empty($scopeIds)) {
            $scopePlaceholders = [];
            foreach (array_values($scopeIds) as $index => $scopeId) {
                $parameterKey = 'scopeId' . $index;
                $scopePlaceholders[] = ':' . $parameterKey;
                $params[$parameterKey] = (int) $scopeId;
            }
            $conditions[] = 'scope_id IN (' . implode(', ', $scopePlaceholders) . ')';
        }

        if (!empty($date)) {
            $start = (clone $date)->setTime(0, 0, 0);
            $end = (clone $date)->setTime(0, 0, 0)->add(new \DateInterval('P1D'));
            $conditions[] = '(ts >= :start AND ts < :end)';
            $params['start'] = $start->format('Y-m-d H:i:s');
            $params['end'] = $end->format('Y-m-d H:i:s');
        }

        if ($userAction === 1) {
            $conditions[] = '(
                (user_id IS NOT NULL AND user_id != \'\' AND user_id NOT LIKE \'_system_%\')
                OR (
                    user_id IS NULL
                    AND data IS NOT NULL
                    AND data LIKE :ua_yes
                    AND data NOT LIKE :ua_system
                )
            )';
            $params['ua_yes'] = '%Sachbearbeiter*in%';
            $params['ua_system'] = '%Sachbearbeiter*in\":\"_system_%';
        }

        if ($userAction === 2) {
            $conditions[] = '(
                user_id LIKE \'_system_%\'
                OR user_id IS NULL
                OR user_id = \'\'
                OR (
                    data IS NOT NULL
                    AND (data LIKE :ua_system OR data NOT LIKE :ua_yes)
                )
            )';
            $params['ua_yes'] = '%Sachbearbeiter*in%';
            $params['ua_system'] = '%Sachbearbeiter*in\":\"_system_%';
        }

        if (!empty($conditions)) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY ts DESC LIMIT ' . (int) $perPage . ' OFFSET ' . (int) $offset;

        $rows = $this->fetchAll($sql, $params);

        $logs = new LogList();
        foreach ($rows as $row) {
            $logs->addEntity(new Entity($this->normalizeLogRow($row)));
        }

        return $logs;
    }

    private function normalizeLogRow(array $row): array
    {
        if (isset($row['reference_id']) && !isset($row['reference'])) {
            $row['reference'] = $row['reference_id'];
        }

        if (isset($row['ts']) && !is_numeric($row['ts'])) {
            $row['ts'] = strtotime((string) $row['ts']);
        }

        return $row;
    }

    public function delete($processId)
    {
        $query = new Query\Log(Query\Base::SELECT);
        $query->addEntityMapping();
        $query->addConditionProcessId($processId);
        $logList = new \BO\Zmsentities\Collection\LogList($this->fetchList($query, new Entity()));
        return $logList;
    }

    protected static function backtraceLogEntry()
    {
        $trace = debug_backtrace();
        $short = '';
        foreach ($trace as $step) {
            if (
                isset($step['file'])
                && isset($step['line'])
                && !strpos($step['file'], 'Zmsdb')
            ) {
                return ' (' . basename($step['file'], '.php') . ')';
            }
        }
        return $short;
    }

    private function escapeLikeValue(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }

    private function buildGeneralSearchConditions(string $generalSearch, array &$params): array
    {
        $terms = $this->parseSearchTerms($generalSearch);
        if ($terms === []) {
            return [];
        }

        $conditions = [];
        foreach ($terms as $index => $termInfo) {
            $term = $termInfo['value'];
            $prefix = 'generalSearch' . $index;
            $escaped = $this->escapeLikeValue($term);
            $useWordBoundary = $termInfo['quoted']
                || (!$this->isNumericSearchQuery($term) && mb_strlen($term) <= 3);

            if ($useWordBoundary) {
                $parts = $this->buildClientNameWordBoundaryParts($escaped, $params, $prefix . 'Name');
                $parts[] = '(client_name IS NULL AND data IS NOT NULL AND ('
                    . implode(' OR ', $this->buildLegacyClientNameWordBoundaryParts($escaped, $params, $prefix . 'Legacy'))
                    . '))';
            } else {
                $params[$prefix . 'Contains'] = '%' . $escaped . '%';
                $params[$prefix . 'Exact'] = $term;
                $parts = [
                    'client_name LIKE :' . $prefix . 'Contains',
                    'services LIKE :' . $prefix . 'Contains',
                    'scope_name LIKE :' . $prefix . 'Contains',
                    'client_email LIKE :' . $prefix . 'Contains',
                    'display_number = :' . $prefix . 'Exact',
                    '(client_name IS NULL AND data IS NOT NULL AND data LIKE :' . $prefix . 'Contains)',
                ];
            }

            if (count($terms) === 1 && $this->isNumericSearchQuery($term)) {
                $params[$prefix . 'Id'] = (int) $term;
                $parts[] = 'reference_id = :' . $prefix . 'Id';
            }

            $conditions[] = '(' . implode(' OR ', $parts) . ')';
        }

        return $conditions;
    }

    private function buildClientNameWordBoundaryParts(string $escapedTerm, array &$params, string $prefix): array
    {
        $params[$prefix . 'Start'] = $escapedTerm . ' %';
        $params[$prefix . 'End'] = '% ' . $escapedTerm;
        $params[$prefix . 'Middle'] = '% ' . $escapedTerm . ' %';
        $params[$prefix . 'Exact'] = $escapedTerm;

        return [
            'client_name LIKE :' . $prefix . 'Start',
            'client_name LIKE :' . $prefix . 'End',
            'client_name LIKE :' . $prefix . 'Middle',
            'client_name = :' . $prefix . 'Exact',
        ];
    }

    private function buildLegacyClientNameWordBoundaryParts(string $escapedTerm, array &$params, string $prefix): array
    {
        $legacyKey = $this->escapeLikeValue('Bürger*in');
        $params[$prefix . 'Start'] = '%' . $legacyKey . '":"' . $escapedTerm . ' %';
        $params[$prefix . 'End'] = '%' . $legacyKey . '":"% ' . $escapedTerm . '"%';
        $params[$prefix . 'Middle'] = '%' . $legacyKey . '":"% ' . $escapedTerm . ' %';
        $params[$prefix . 'Exact'] = '%' . $legacyKey . '":"' . $escapedTerm . '"%';

        return [
            'data LIKE :' . $prefix . 'Start',
            'data LIKE :' . $prefix . 'End',
            'data LIKE :' . $prefix . 'Middle',
            'data LIKE :' . $prefix . 'Exact',
        ];
    }

    private function parseSearchTerms(string $queryString): array
    {
        if ($queryString === '') {
            return [];
        }

        if (!preg_match_all('/"([^"]+)"|(\S+)/u', $queryString, $matches, PREG_SET_ORDER)) {
            return [['value' => $queryString, 'quoted' => false]];
        }

        $terms = [];
        foreach ($matches as $match) {
            $value = trim($match[1] !== '' ? $match[1] : $match[2]);
            if ($value === '') {
                continue;
            }
            $terms[] = [
                'value' => $value,
                'quoted' => $match[1] !== '',
            ];
        }

        return $terms;
    }

    private function isNumericSearchQuery(string $queryString): bool
    {
        return (bool) preg_match('#^\d+$#', $queryString);
    }

    public function clearLogsOlderThan(int $olderThan): bool
    {
        try {
            $olderThanDate = (new \DateTime())->modify('-' . $olderThan . ' days');

            $query = new Query\Log(Query\Base::DELETE);
            $query->addConditionOlderThan($olderThanDate);

            $result = $this->writeItem($query);
            return $result !== false;
        } catch (\Exception $e) {
            \App::$log->error('Error during log cleanup', ['exception' => $e->getMessage()]);
            return false;
        }
    }
}
