<?php
namespace BO\Zmsdb;

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
        $writer =$log->getWriter();
        $sql = "INSERT INTO `log` SET `message`=:message, `reference_id`=:referenceId, `type`=:type";
        $parameters = [
            "message" => $message,
            "referenceId" => $referenceId,
            "type" => $type,
            ];
        //error_log("$message");
        return $writer->perform($sql, $parameters);
    }
}
