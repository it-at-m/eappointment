<?php
namespace BO\Zmsdb;

use \BO\Zmsentities\Log as Entity;

/**
 * Logging for actions
 *
 */
class Log extends Base
{
    const PROCESS = 'buerger';
    const MIGRATION = 'migration';
    const ERROR = 'error';

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
        \BO\Zmsentities\Process $process,
        ?\BO\Zmsentities\Useraccount $userAccount = null
    ) {
        $requests = (new Request)->readRequestsByIds($process->getRequestIds());

        $data = json_encode(array_filter([
            "Nutzer" => $userAccount->getId(),
            "Terminnummer" => $process->getId(),
            "Datum" => $process->getFirstAppointment()->toDateTime()->format('d.m.Y. H:i:s'),
            "Name" => $process->getFirstClient()->familyName,
            "Dienstleistungen" => implode(', ', array_map(function ($request) {
                return $request->getName();
            }, $requests->getAsArray())),
            "Anmerkungsfeld" => $process->getAmendment(),
            "E-mail" => $process->getFirstClient()->email,
            "Telefon" => $process->getFirstClient()->telephone,
        ]));

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

    protected static function backtraceLogEntry()
    {
        $trace = debug_backtrace();
        $short = '';
        foreach ($trace as $step) {
            if (isset($step['file'])
                && isset($step['line'])
                && !strpos($step['file'], 'Zmsdb')
            ) {
                return ' ('.basename($step['file'], '.php') .')';
            }
        }
        return $short;
    }
}
