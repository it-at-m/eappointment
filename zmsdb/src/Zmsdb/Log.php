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

    public static function writeLogEntry($message, $referenceId, $type = self::PROCESS)
    {
        $message .= " [" . static::$operator . "]";
        $log = new static();
        $sql = "INSERT INTO `log` SET `message`=:message, `reference_id`=:referenceId, `type`=:type";
        $parameters = [
            "message" => $message . static::backtraceLogEntry(),
            "referenceId" => $referenceId,
            "type" => $type,
            ];
        return $log->perform($sql, $parameters);
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
