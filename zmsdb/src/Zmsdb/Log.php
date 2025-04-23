<?php

namespace BO\Zmsdb;

use BO\Zmsentities\Collection\RequestList;
use BO\Zmsentities\Log as Entity;

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
    const ACTION_NEW_PICKUP = 'Abholtermin wurde erstellt';
    const ACTION_REDIRECTED = 'Termin wurde weitergeleitet';
    const ACTION_NEW = 'Neuer Termin wurde erstellt';
    const ACTION_DELETED = 'Termin wurde gelöscht';
    const ACTION_CANCELED = 'Termin wurde abgesagt';

    public static $operator = 'lib';

    public static function writeLogEntry(
        $message,
        $referenceId,
        $type = self::PROCESS,
        ?int $scopeId = null,
        ?string $userId = null,
        ?string $data = null
    ) {
        $message .= " [" . static::$operator . "]";
        $log = new static();
        $sql = "INSERT INTO `log` SET 
`message`=:message, 
`reference_id`=:referenceId, 
`type`=:type, 
`scope_id`=:scopeId,
`user_id`=:userId,
`data`=:dataString";

        $parameters = [
            "message" => $message . static::backtraceLogEntry(),
            "referenceId" => $referenceId,
            "type" => $type,
            "scopeId" => $scopeId,
            "userId" => $userId,
            "dataString" => $data
        ];

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
        if (! empty($process->getRequestIds())) {
            $requests = (new Request())->readRequestsByIds($process->getRequestIds());
        }

        $data = json_encode(array_filter([
            'Aktion' => $action,
            "Sachbearbeiter*in" => $userAccount ? $userAccount->getId() : '',
            "Terminnummer" => $process->getId(),
            "Terminzeit" => $process->getFirstAppointment()->toDateTime()->format('d.m.Y H:i:s'),
            "Bürger*in" => $process->getFirstClient()->familyName,
            "Dienstleistung/en" => implode(', ', array_map(function ($request) {
                return $request->getName();
            }, $requests->getAsArray())),
            "Anmerkung" => $process->getAmendment(),
            "E-Mail" => $process->getFirstClient()->email,
            "Telefon" => $process->getFirstClient()->telephone,
        ]), JSON_UNESCAPED_UNICODE);

        Log::writeLogEntry(
            $method,
            $process->getId(),
            self::PROCESS,
            $process->getScopeId(),
            $userAccount->getId(),
            $data
        );
    }

    public function readByProcessId($processId)
    {
        $query = new Query\Log(Query\Base::SELECT);
        $query->addEntityMapping();
        $query->addConditionProcessId($processId);
        $logList = new \BO\Zmsentities\Collection\LogList($this->fetchList($query, new Entity()));
        return $logList;
    }

    public function readByProcessData($search)
    {
        $query = new Query\Log(Query\Base::SELECT);
        $query->addEntityMapping();
        $query->addConditionDataSearch($search);
        $query->addLimit(1000);

        return new \BO\Zmsentities\Collection\LogList($this->fetchList($query, new Entity()));
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

    public function clearDataOlderThan(int $olderThan)
    {
        $olderThanDate = (new \DateTime())->modify('-' . $olderThan . ' days');

        $query = new Query\Log(Query\Base::UPDATE);
        $query->addConditionOlderThan($olderThanDate);
        $query->addValues([
            'data' => null
        ]);

        $this->writeItem($query);
    }
}
