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
    public function readEntity(\DateTimeImmutable $now, $includeProcessStats = true)
    {
        $entity = new Entity();
        $configVariables = $this->readConfigVariables();
        $statusVariables = $this->readStatusVariables();
        $nodeConnections = round($statusVariables['Threads_connected'] / $configVariables['max_connections'], 2);
        $entity['database']['problems'] = $this->getConfigProblems($configVariables);
        $entity['database']['locks'] = $statusVariables['Innodb_row_lock_current_waits'];
        $entity['database']['threads'] = $statusVariables['Threads_connected'];
        $entity['database']['nodeConnections'] = $nodeConnections;
        $entity['database']['clusterStatus'] =
            array_key_exists('wsrep_ready', $statusVariables) ? $statusVariables['wsrep_ready'] : 'OFF';
        $entity['database']['logbin'] =
            array_key_exists('log_bin', $configVariables) ? $configVariables['log_bin'] : 'OFF';
        $entity['processes'] = [];
        if ($includeProcessStats) {
            $entity['processes'] = $this->readProcessStats($now);
            $outdated = $this->readOutdatedSlots();
            $entity['processes']['outdated'] = $outdated['cnt'];
            $entity['processes']['outdatedOldest'] = $outdated['oldest'];
            $freeSlots = $this->readFreeSlots();
            $entity['processes']['freeSlots'] = $freeSlots['cnt'];
        }
        $entity['mail'] = $this->readMailStats();
        $entity['notification'] = $this->readNotificationStats();
        $entity['sources']['dldb']['last'] = $this->readDdldUpdateStats();
        $entity['processes']['lastCalculate'] = $this->readLastCalculateSlots();
        return $entity;
    }

    public function getConfigProblems($configVariables)
    {
        $problems = [];
        if ($configVariables['tmp_table_size'] < 32000000) {
            $problems[] = 'tmp_table_size should be at least 32MB';
        }
        if ($configVariables['max_heap_table_size'] < 32000000) {
            $problems[] = 'max_heap_table_size should be at least 32MB';
        }
        $problems = implode('; ', $problems);
        return $problems;
    }

    /**
     * Get the information on dldb update status
     *
     * @return Array
     */
    protected function readDdldUpdateStats()
    {
        $stats = $this->getReader()->fetchOne(
            'SELECT
                value
            FROM config
            WHERE name = "sources_dldb_last"
            '
        );
        return $stats['value'];
    }

    /**
     * Hint: "cancelled" slots might be older, but do not get updated, if not bookable in availability any longer
     * @return Array
     */
    protected function readOutdatedSlots()
    {
        $stats = $this->getReader()->fetchOne(
            'SELECT
                COUNT(*) cnt, MIN(a.updateTimestamp) oldest
            FROM slot s LEFT JOIN oeffnungszeit a ON s.availabilityID = a.OeffnungszeitID
            WHERE s.updateTimestamp < a.updateTimestamp AND s.status = "free"
            '
        );
        return $stats;
    }

    /**
     * @return Array
     */
    protected function readFreeSlots()
    {
        $stats = $this->getReader()->fetchOne(
            'SELECT
                SUM(intern) cnt
            FROM slot s
            WHERE s.status = "free"
            '
        );
        return $stats;
    }

    /**
     * Get the information on dldb update status
     *
     * @return Array
     */
    protected function readLastCalculateSlots()
    {
        $stats = $this->getReader()->fetchOne(
            'SELECT
                value
            FROM config
            WHERE name = "status__calculateSlotsLastRun"
            '
        );
        return $stats['value'];
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
                COUNT(id) as queueCount,
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
                COUNT(id) as queueCount,
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
    protected function readProcessStats(\DateTimeImmutable $now)
    {
        $midnight = $now->modify('00:00:00')->getTimestamp();
        $last7days = $now->modify('-7days 00:00:00')->getTimestamp();
        $processStats = $this->getReader()->fetchOne(
            'SELECT
                SUM(CASE name WHEN "dereferenced" THEN 1 ELSE NULL END) as blocked,
                SUM(CASE 
                    WHEN b.StandortID != 0 AND vorlaeufigeBuchung = 0  AND Abholer = 0 
                    THEN 1 ELSE NULL END) as confirmed,
                SUM(CASE 
                    WHEN (b.StandortID != 0 OR AbholortID != 0) AND vorlaeufigeBuchung = 0  AND Abholer = 1 
                    THEN 1 ELSE NULL END) as pending,
                SUM(CASE WHEN name = "(abgesagt)" THEN 1 ELSE NULL END) as deleted,
                SUM(CASE WHEN nicht_erschienen > 0 AND b.StandortID != 0 THEN 1 ELSE NULL END) as missed,
                SUM(CASE WHEN vorlaeufigeBuchung = 1 AND b.StandortID != 0 THEN 1 ELSE NULL END) as reserved,
                SUM(
                    CASE 
                        WHEN IPTimeStamp > '.intval($midnight).' AND b.StandortID != 0 
                            AND vorlaeufigeBuchung = 0 AND Abholer = 0 
                        THEN 
                            1 
                        ELSE 
                            NULL 
                        END) 
                    as sincemidnight,
                SUM(
                    CASE 
                        WHEN 
                            IPTimeStamp > '.intval($last7days).' AND b.StandortID != 0 
                            AND vorlaeufigeBuchung = 0  AND Abholer = 0 
                        THEN 
                            1 
                        ELSE 
                            NULL 
                        END) 
                        as last7days,
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
