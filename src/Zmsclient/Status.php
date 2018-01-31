<?php
namespace BO\Zmsclient;

/**
 * Healthcheck concerning the API
 */
class Status
{
    /**
     * throws exception on critical status variables
     *
     */
    public static function testStatus($response, $status)
    {
        if ($status instanceof \Closure) {
            try {
                $status = $status();
            } catch (\Exception $exception) {
                $status = false;
                $result = "FATAL - " . $exception->getMessage();
            }
        }
        if ($status) {
            $result = self::getDldbUpdateStats($status);

            if ($status['mail']['oldestSeconds'] > 300) {
                $result = "WARN - Oldest mail with age in seconds: " . $status['mail']['oldestSeconds'];
            } elseif ($status['notification']['oldestSeconds'] > 300) {
                $result = "WARN - Oldest sms with age in seconds: " . $status['notification']['oldestSeconds'];
            } elseif ($status['database']['logbin'] != 'ON') {
                $result = "WARN - DB connection without replication log detected";
            } elseif ($status['database']['clusterStatus'] == 'OFF') {
                $result = "WARN - DB connection is not part of a galera cluster";
            } elseif ($status['database']['nodeConnections'] > 50) {
                $result = "WARN - DB connected thread over 50% of available connections";
            } else {
                $result = "OK - DB=" . $status['database']['nodeConnections'] . "%";
            }
        }
        $response->getBody()->write($result);
        $response = $response->withHeader('Content-Type', 'text/plain');
        return $response;
    }

    public static function getDldbUpdateStats($status)
    {
        $result = '';
        $now = new \DateTimeImmutable();
        $lastDldbUpdate = new \DateTimeImmutable($status['sources']['dldb']['last']);
        if (($lastDldbUpdate->getTimestamp() + 7200) < $now->getTimestamp()) {
            $result = "WARN - Last DLDB Import is more then 2 hours ago";
        } elseif (($lastDldbUpdate->getTimestamp() + 14400) < $now->getTimestamp()) {
            $result = "CRIT - Last DLDB Import is more then 4 hours ago";
        }
        return $result;
    }
}
