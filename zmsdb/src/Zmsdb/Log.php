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

    private const FULLTEXT_SEARCH_COLUMNS = 'citizen_name, services, scope_name, citizen_email';

    private const TEXT_SEARCH_COLUMNS = [
        'citizen_name',
        'services',
        'scope_name',
        'citizen_email',
    ];

    private const INDEXED_COLUMNS = [
        'action',
        'display_number',
        'queue_number',
        'appointment_at',
        'slot_count',
        'citizen_name',
        'services',
        'scope_name',
        'citizen_email',
        'citizen_phone',
        'process_status',
        'db_status',
        'citizen_amendment',
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

    private const ACTION_CODE_TO_LABEL = [
        'mail_success' => self::ACTION_MAIL_SUCCESS,
        'mail_fail' => self::ACTION_MAIL_FAIL,
        'status_changed' => self::ACTION_STATUS_CHANGE,
        'reminder_sent' => self::ACTION_SEND_REMINDER,
        'removed_from_queue' => self::ACTION_REMOVED,
        'called' => self::ACTION_CALLED,
        'archived' => self::ACTION_ARCHIVED,
        'edited' => self::ACTION_EDITED,
        'redirected' => self::ACTION_REDIRECTED,
        'created' => self::ACTION_NEW,
        'deleted' => self::ACTION_DELETED,
        'canceled' => self::ACTION_CANCELED,
    ];

    public static $operator = 'lib';

    public static function writeLogEntry(
        $message,
        $referenceId,
        $type = self::PROCESS,
        ?int $scopeId = null,
        ?string $userId = null,
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
        ];
        $parameters = [
            'message' => $message . static::backtraceLogEntry(),
            'referenceId' => $referenceId,
            'type' => $type,
            'scopeId' => $scopeId,
            'userId' => $userId,
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

        Log::writeLogEntry(
            $method,
            $process->getId(),
            self::PROCESS,
            $process->getScopeId(),
            $userAccount->getId(),
            $payload['indexed']
        );
    }

    public static function buildProcessLogPayload(
        \BO\Zmsentities\Process $process,
        string $action,
        \BO\Zmsentities\Useraccount $userAccount,
        RequestList $requests
    ): array {
        $appointmentAt = $process->getFirstAppointment()->toDateTime();

        $indexed = array_filter([
            'action' => self::actionCodeFromLabel($action),
            'display_number' => $process->getDisplayNumber(),
            'queue_number' => $process->getQueueNumber(),
            'appointment_at' => $appointmentAt->format('Y-m-d H:i:s'),
            'slot_count' => $process->getFirstAppointment()->slotCount ?? null,
            'citizen_name' => $process->getFirstClient()->familyName,
            'services' => implode(', ', array_map(function ($request) {
                return $request->getName();
            }, $requests->getAsArray())),
            'scope_name' => $process->scope->getName(),
            'citizen_email' => $process->getFirstClient()->email,
            'citizen_phone' => $process->getFirstClient()->telephone,
            'process_status' => $process->getStatus(),
            'db_status' => $process->dbstatus,
            'citizen_amendment' => $process->getAmendment(),
        ], static function ($value) {
            return $value !== null && $value !== '';
        });

        return [
            'indexed' => $indexed,
        ];
    }

    public static function actionCodeFromLabel(string $label): ?string
    {
        return self::ACTION_LABEL_TO_CODE[$label] ?? null;
    }

    public static function actionLabelFromCode(?string $code): ?string
    {
        if ($code === null || $code === '') {
            return null;
        }

        return self::ACTION_CODE_TO_LABEL[$code] ?? null;
    }

    public static function formatDisplayFields(array $log): array
    {
        $display = [];
        $actionLabel = self::actionLabelFromCode($log['action'] ?? null);
        if ($actionLabel !== null) {
            $display['Aktion'] = $actionLabel;
        }
        if (!empty($log['user_id'])) {
            $display['Sachbearbeiter*in'] = $log['user_id'];
        }
        if (!empty($log['display_number'])) {
            $display['Terminnummer'] = $log['display_number'];
        }
        if (isset($log['queue_number']) && $log['queue_number'] !== '') {
            $display['Wartenummer'] = $log['queue_number'];
        }
        if (!empty($log['appointment_at'])) {
            $appointmentAt = \DateTimeImmutable::createFromFormat(
                'Y-m-d H:i:s',
                (string) $log['appointment_at']
            );
            if ($appointmentAt instanceof \DateTimeImmutable) {
                $display['Terminzeit'] = $appointmentAt->format('d.m.Y H:i:s');
            }
        }
        if (isset($log['slot_count']) && $log['slot_count'] !== '') {
            $display['Slots'] = $log['slot_count'];
        }
        if (!empty($log['citizen_name'])) {
            $display['Bürger*in'] = $log['citizen_name'];
        }
        if (!empty($log['services'])) {
            $display['Dienstleistungen'] = $log['services'];
        }
        if (!empty($log['citizen_amendment'])) {
            $display['Anmerkung'] = $log['citizen_amendment'];
        }
        if (!empty($log['scope_name'])) {
            $display['Standort'] = $log['scope_name'];
        }
        if (!empty($log['citizen_email'])) {
            $display['E-Mail'] = $log['citizen_email'];
        }
        if (!empty($log['citizen_phone'])) {
            $display['Telefon'] = $log['citizen_phone'];
        }
        if (!empty($log['process_status'])) {
            $display['Status'] = $log['process_status'];
        }
        if (!empty($log['db_status'])) {
            $display['DB Status'] = $log['db_status'];
        }

        return $display;
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
        $params = ['logType' => self::PROCESS];
        $conditions = array_merge(
            ['type = :logType'],
            $this->buildFieldValueConditions($fieldValues, $params),
            $this->buildGeneralSearchConditionList($generalSearch, $params),
            $this->buildScopeIdConditions($scopeIds, $params),
            $this->buildDateConditions($date, $params),
            $this->buildUserActionConditions($userAction, $params)
        );

        $sql = 'SELECT * FROM log';
        if (!empty($conditions)) {
            $sql .= ' WHERE ' . implode(' AND ', $conditions);
        }

        $sql .= ' ORDER BY ts DESC LIMIT ' . (int) $perPage . ' OFFSET ' . (int) $offset;

        $logs = new LogList();
        foreach ($this->fetchAll($sql, $params) as $row) {
            $logs->addEntity(new Entity($this->normalizeLogRow($row)));
        }

        return $logs;
    }

    private function buildFieldValueConditions(array $fieldValues, array &$params): array
    {
        $conditions = [];
        foreach ($fieldValues as $field => $value) {
            if ($value === null || $value === '') {
                continue;
            }

            $likeValue = '%' . $this->escapeLikeValue((string) $value) . '%';
            if ($field === 'scope_name') {
                $conditions[] = 'scope_name LIKE :scopeName';
                $params['scopeName'] = $likeValue;
                continue;
            }

            if ($field === 'services') {
                $conditions[] = 'services LIKE :services';
                $params['services'] = $likeValue;
            }
        }

        return $conditions;
    }

    private function buildGeneralSearchConditionList(?string $generalSearch, array &$params): array
    {
        if ($generalSearch === null || trim($generalSearch) === '') {
            return [];
        }

        return $this->buildGeneralSearchConditions(trim($generalSearch), $params);
    }

    private function buildScopeIdConditions(?array $scopeIds, array &$params): array
    {
        if (empty($scopeIds)) {
            return [];
        }

        $scopePlaceholders = [];
        foreach (array_values($scopeIds) as $index => $scopeId) {
            $parameterKey = 'scopeId' . $index;
            $scopePlaceholders[] = ':' . $parameterKey;
            $params[$parameterKey] = (int) $scopeId;
        }

        return ['scope_id IN (' . implode(', ', $scopePlaceholders) . ')'];
    }

    private function buildDateConditions(?DateTime $date, array &$params): array
    {
        if (empty($date)) {
            return [];
        }

        $start = (clone $date)->setTime(0, 0, 0);
        $end = (clone $date)->setTime(0, 0, 0)->add(new \DateInterval('P1D'));
        $params['start'] = $start->format('Y-m-d H:i:s');
        $params['end'] = $end->format('Y-m-d H:i:s');

        return ['(ts >= :start AND ts < :end)'];
    }

    private function buildUserActionConditions(int $userAction, array &$params): array
    {
        if ($userAction === 1) {
            return ['(user_id IS NOT NULL AND user_id != \'\' AND user_id NOT LIKE \'_system_%\')'];
        }

        if ($userAction === 2) {
            return ['(user_id LIKE \'_system_%\')'];
        }

        return [];
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
            $useWordBoundary = $termInfo['quoted'];

            if ($useWordBoundary) {
                $parts = $this->buildQuotedCitizenNameSearchParts($term, $params, $prefix . 'Name');
            } else {
                $parts = $this->buildUnquotedSearchParts($term, $prefix, $params);
            }

            if (count($terms) === 1 && $this->isNumericSearchQuery($term)) {
                $params[$prefix . 'Id'] = (int) $term;
                $parts[] = 'reference_id = :' . $prefix . 'Id';
            }

            $conditions[] = '(' . implode(' OR ', $parts) . ')';
        }

        return $conditions;
    }

    private function buildUnquotedSearchParts(string $term, string $paramPrefix, array &$params): array
    {
        $parts = [];
        $params[$paramPrefix . 'Exact'] = $term;
        $parts[] = 'display_number = :' . $paramPrefix . 'Exact';

        if (mb_strlen($term) <= 2) {
            if (mb_strlen($term) === 1) {
                $escaped = $this->escapeLikeValue($term);
                $params[$paramPrefix . 'Prefix'] = $escaped . '%';
                foreach (self::TEXT_SEARCH_COLUMNS as $column) {
                    $parts[] = $column . ' LIKE :' . $paramPrefix . 'Prefix';
                }

                return $parts;
            }

            $parts = array_merge($parts, $this->buildTextColumnBoundaryLikeParts($term, $paramPrefix, $params));

            return $parts;
        }

        $fulltextTerm = $this->escapeFulltextBooleanTerm($term);
        if ($fulltextTerm !== '') {
            $params[$paramPrefix . 'Ft'] = $fulltextTerm;
            $parts[] = 'MATCH(' . self::FULLTEXT_SEARCH_COLUMNS . ') AGAINST(:' . $paramPrefix . 'Ft IN BOOLEAN MODE)';
        }

        return $parts;
    }

    private function buildTextColumnBoundaryLikeParts(string $term, string $paramPrefix, array &$params): array
    {
        $escaped = $this->escapeLikeValue($term);
        $params[$paramPrefix . 'Start'] = $escaped . '%';
        $params[$paramPrefix . 'End'] = '%' . $escaped;
        $params[$paramPrefix . 'Middle'] = '% ' . $escaped . ' %';
        $params[$paramPrefix . 'MiddleStart'] = '% ' . $escaped;
        $params[$paramPrefix . 'Exact'] = $term;

        $parts = [];
        foreach (self::TEXT_SEARCH_COLUMNS as $column) {
            $parts[] = $column . ' LIKE :' . $paramPrefix . 'Start';
            $parts[] = $column . ' LIKE :' . $paramPrefix . 'End';
            $parts[] = $column . ' LIKE :' . $paramPrefix . 'Middle';
            $parts[] = $column . ' LIKE :' . $paramPrefix . 'MiddleStart';
            $parts[] = $column . ' = :' . $paramPrefix . 'Exact';
        }

        return $parts;
    }

    private function escapeFulltextBooleanTerm(string $term): string
    {
        $term = trim(preg_replace('/[+\-><()~*"@]+/u', ' ', $term) ?? '');
        if ($term === '') {
            return '';
        }

        $words = preg_split('/\s+/u', $term, -1, PREG_SPLIT_NO_EMPTY);
        if ($words === false || $words === []) {
            return '';
        }

        if (count($words) > 1) {
            return '"' . implode(' ', array_map(static fn ($word) => str_replace('"', '', $word), $words)) . '"';
        }

        return '+' . $words[0] . '*';
    }

    private function buildQuotedCitizenNameSearchParts(string $term, array &$params, string $paramPrefix): array
    {
        if (mb_strlen($term) <= 2) {
            return $this->buildCitizenNameWordBoundaryParts(
                $this->escapeLikeValue($term),
                $params,
                $paramPrefix
            );
        }

        $fulltextTerm = $this->escapeFulltextQuotedTerm($term);
        if ($fulltextTerm === '') {
            return [];
        }

        $params[$paramPrefix . 'Ft'] = $fulltextTerm;

        return ['MATCH(citizen_name) AGAINST(:' . $paramPrefix . 'Ft IN BOOLEAN MODE)'];
    }

    private function escapeFulltextQuotedTerm(string $term): string
    {
        $term = trim(preg_replace('/[+\-><()~*"@]+/u', ' ', $term) ?? '');
        if ($term === '') {
            return '';
        }

        $words = preg_split('/\s+/u', $term, -1, PREG_SPLIT_NO_EMPTY);
        if ($words === false || $words === []) {
            return '';
        }

        if (count($words) > 1) {
            return '"' . implode(' ', array_map(static fn ($word) => str_replace('"', '', $word), $words)) . '"';
        }

        return '+' . $words[0];
    }

    private function buildCitizenNameWordBoundaryParts(string $escapedTerm, array &$params, string $prefix): array
    {
        $params[$prefix . 'Start'] = $escapedTerm . ' %';
        $params[$prefix . 'End'] = '% ' . $escapedTerm;
        $params[$prefix . 'Middle'] = '% ' . $escapedTerm . ' %';
        $params[$prefix . 'Exact'] = $escapedTerm;

        return [
            'citizen_name LIKE :' . $prefix . 'Start',
            'citizen_name LIKE :' . $prefix . 'End',
            'citizen_name LIKE :' . $prefix . 'Middle',
            'citizen_name = :' . $prefix . 'Exact',
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
