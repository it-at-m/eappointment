<?php
namespace BO\Zmsclient;

/**
 * Healthcheck concerning the API
 */
class Status
{
    /**
     * throws exception on critical status variables
     * @SuppressWarnings(Complexity)
     */
    public static function testStatus($response, $status)
    {
        $result = '';
        if ($status instanceof \Closure) {
            try {
                $status = $status();
            } catch (\Exception $exception) {
                $status = false;
                $result = "FATAL - " . $exception->getMessage();
            }
        }
        if ($status && !$result) {
            $result = [];
            $result[] = self::getDldbUpdateStats($status);

            if ($status['mail']['oldestSeconds'] > 300) {
                $result[] = "WARN - Oldest mail with age in seconds: "
                    . $status['mail']['oldestSeconds'] . 's';
            }
            if ($status['notification']['oldestSeconds'] > 300) {
                $result[] = "WARN - Oldest sms with age in seconds: "
                    . $status['notification']['oldestSeconds'] . 's';
            }
            if ($status['database']['logbin'] != 'ON') {
                $result[] = "WARN - DB connection without replication log detected";
            }
            if ($status['database']['clusterStatus'] == 'OFF') {
                $result[] = "WARN - DB connection is not part of a galera cluster";
            }
            if ($status['database']['locks'] > 10) {
                $result[] = "WARN - High amount of DB-Locks: ".$status['database']['locks'];
            }
            if ($status['database']['threads'] > 30) {
                $result[] = "WARN - High amount of DB-Threads: ".$status['database']['threads'];
            }
            if ($status['database']['nodeConnections'] > 50) {
                $result[] = "WARN - DB connected thread over 50% of available connections";
            }
            if (!count($result)) {
                $result = "OK - DB-Threads(Locked)="
                    . $status['database']['threads']
                    . "(".$status['database']['locks'].")"
                    ;
            } else {
                $result = implode('; ', $result);
            }
        }
        $response->getBody()->write($result);
        $response = $response->withHeader('Content-Type', 'text/plain');
        return $response;
    }

    public static function getDldbUpdateStats($status)
    {
        $result = '';
        if (isset($status['sources'])) {
            $now = new \DateTimeImmutable();
            $lastDldbUpdate = new \DateTimeImmutable($status['sources']['dldb']['last']);
            if (($lastDldbUpdate->getTimestamp() + 7200) < $now->getTimestamp()) {
                $result = "WARN - Last DLDB Import is more then 2 hours ago";
            } elseif (($lastDldbUpdate->getTimestamp() + 14400) < $now->getTimestamp()) {
                $result = "CRIT - Last DLDB Import is more then 4 hours ago";
            }
        }
        return $result;
    }
}
