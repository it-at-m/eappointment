<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Status as Entity;

class Status extends Base
{
    /**
     * Fetch status from db
     *
     * @return \BO\Zmsentities\Status
     */
    public function readEntity()
    {
        $entity = new Entity();
        $configVariables = $this->readConfigVariables();
        $statusVariables = $this->readStatusVariables();
        $nodeConnections = round($statusVariables['Threads_connected'] / $configVariables['max_connections'], 2);
        $entity['database']['nodeConnections'] = $nodeConnections;
        $entity['database']['clusterStatus'] =
            array_key_exists('wsrep_ready', $statusVariables) ? $statusVariables['wsrep_ready'] : 'OFF';
        $entity['database']['logbin'] =
            array_key_exists('log_bin', $configVariables) ? $configVariables['log_bin'] : 'OFF';
        $entity['processes'] = $this->readProcessStats();
        $entity['mail'] = $this->readMailStats();
        $entity['notification'] = $this->readNotificationStats();
        return $entity;
    }

    /**
     * Get the information on processes
     *
     * @return Array
     */
    protected function readMailStats()
    {
        $stats = $this->getReader()->fetchOne(
            'SELECT
                SUM(id) as queueCount,
                UNIX_TIMESTAMP() - MIN(createTimestamp) as oldestSeconds,
                UNIX_TIMESTAMP() - MAX(createTimestamp) as newestSeconds
            FROM mailqueue
            '
        );
        return $stats;
    }

    /**
     * Get the information on processes
     *
     * @return Array
     */
    protected function readNotificationStats()
    {
        $stats = $this->getReader()->fetchOne(
            'SELECT
                SUM(id) as queueCount,
                UNIX_TIMESTAMP() - MIN(createTimestamp) as oldestSeconds,
                UNIX_TIMESTAMP() - MAX(createTimestamp) as newestSeconds
            FROM notificationqueue
            '
        );
        return $stats;
    }

    /**
     * Get the information on processes
     *
     * @return Array
     */
    protected function readProcessStats()
    {
        $processStats = $this->getReader()->fetchOne(
            'SELECT
                SUM(CASE name WHEN "dereferenced" THEN 1 ELSE NULL END) as blocked,
                SUM(CASE WHEN b.StandortID != 0 AND vorlaeufigeBuchung = 0 THEN 1 ELSE NULL END) as confirmed,
                SUM(CASE WHEN name = "(abgesagt)" THEN 1 ELSE NULL END) as deleted,
                SUM(CASE WHEN nicht_erschienen > 0 AND b.StandortID != 0 THEN 1 ELSE NULL END) as missed,
                SUM(CASE WHEN vorlaeufigeBuchung = 1 AND b.StandortID != 0 THEN 1 ELSE NULL END) as reserved,
                FROM_UNIXTIME(MAX(IPTimeStamp)) as lastInsert
            FROM buerger AS b
            WHERE
                b.istFolgeterminvon IS NULL
                OR b.istFolgeterminvon = 0
            '
        );
        return $processStats;
    }

    /**
     * Fetch mysql config variables
     *
     * @return Array
     */
    protected function readConfigVariables()
    {
        $configVariables = $this->getReader()->fetchPairs('SHOW VARIABLES');
        return $configVariables;
    }

    /**
     * Fetch mysql status variables
     *
     * @return Array
     */
    protected function readStatusVariables()
    {
        $statusVariables = $this->getReader()->fetchPairs('SHOW STATUS');
        return $statusVariables;
    }
}
