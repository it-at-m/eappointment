<?php
namespace BO\Zmsclient;

use Fig\Http\Message\StatusCodeInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Healthcheck concerning the API
 */
class Status
{
    /**
     * throws exception on critical status variables
     * @SuppressWarnings(Complexity)
     */
    public static function testStatus(ResponseInterface $response, $status)
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
            if (isset($status['processes'])
                && isset($status['processes']['lastCalculate'])
                && time() - strtotime($status['processes']['lastCalculate']) > 600
            ) {
                $slotOutdate =
                    time() - strtotime($status['processes']['lastCalculate']);
                $result[] = "WARN - slot calculation is $slotOutdate seconds old";
            }
            $result = preg_grep('/./', $result);
            if (!count($result)) {
                $result = "OK - "
                    . "DB=" . $status['database']['nodeConnections'] . "%"
                    . " Threads=".$status['database']['threads']
                    . " Locks=".$status['database']['locks']
                    ;
            } else {
                $result = implode('; ', $result);
            }
        }

        $response->getBody()->write($result);
        $response = $response->withHeader('Content-Type', 'text/plain');

        if (strpos($result, 'CRIT') !== false || strpos($result, 'FATAL') !== false) {
            $response = $response->withStatus(
                StatusCodeInterface::STATUS_INTERNAL_SERVER_ERROR,
                'The Server is in a bad condition.'
            );
        }

        return $response;
    }

    public static function getDldbUpdateStats($status)
    {
        $result = '';
        if (isset($status['sources'])) {
            $now = new \DateTimeImmutable();
            $lastDldbUpdate = new \DateTimeImmutable($status['sources']['dldb']['last']);
            if (($lastDldbUpdate->getTimestamp() + 7200) < $now->getTimestamp() &&
                ($lastDldbUpdate->getTimestamp() + 14400) > $now->getTimestamp()
            ) {
                $result = "WARN - Last DLDB Import is more then 2 hours ago";
            } elseif (($lastDldbUpdate->getTimestamp() + 14400) < $now->getTimestamp()) {
                $result = "CRIT - Last DLDB Import is more then 4 hours ago";
            }
        }
        return $result;
    }
}
