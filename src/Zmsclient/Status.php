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
        $response->getBody()->write($result);
        $response = $response->withHeader('Content-Type', 'text/plain');
        return $response;
    }
}
