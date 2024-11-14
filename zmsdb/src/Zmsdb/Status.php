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
        $entity['processes'] = $includeProcessStats ? $this->readProcessStats($now) : [];
        
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
        $entity['useraccounts']['activeSessions'] = $this->getTotalActiveSessions();
        $entity['useraccounts']['departments'] = $this->getActiveSessionsByBehoerdenWithScopes();

        return $entity;
    }

    /**
     * Get total active sessions where SessionID is not null or empty and sessionExpiry is in the future
     *
     * @return int
     */
    protected function getTotalActiveSessions()
    {
        $result = $this->getReader()->fetchOne(
            'SELECT COUNT(n.SessionID) as totalActiveSessions
             FROM nutzer n
             WHERE n.SessionID IS NOT NULL
             AND n.SessionID != ""
             AND n.sessionExpiry > UNIX_TIMESTAMP()'
        );

        return (int) $result['totalActiveSessions'];
    }

    /**
     * Get active sessions grouped by BehoerdenID with scopes, excluding expired sessions
     *
     * @return array
     */
    protected function getActiveSessionsByBehoerdenWithScopes()
    {
        $result = $this->getReader()->fetchAll(
            'SELECT b.BehoerdenID, b.Name as BehoerdeName, 
                    s.StandortID, s.Bezeichnung as StandortName,
                    COALESCE(SUM(CASE 
                        WHEN n.sessionExpiry > UNIX_TIMESTAMP()
                        THEN 1 ELSE 0 END), 0) as activeSessions
             FROM behoerde b
             LEFT JOIN standort s ON b.BehoerdenID = s.BehoerdenID
             LEFT JOIN nutzer n ON s.StandortID = n.StandortID
             GROUP BY b.BehoerdenID, b.Name, s.StandortID, s.Bezeichnung'
        );

        $activeSessionsByBehoerden = [];

        foreach ($result as $row) {
            $behoerdenID = $row['BehoerdenID'];
            $standortID = $row['StandortID'];

            if (!isset($activeSessionsByBehoerden[$behoerdenID])) {
                $activeSessionsByBehoerden[$behoerdenID] = [
                    'activeSessions' => 0,
                    'name' => $row['BehoerdeName'],
                    'scopes' => []
                ];
            }

            $activeSessionsByBehoerden[$behoerdenID]['scopes'][$standortID] = [
                'activeSessions' => (int) $row['activeSessions'],
                'name' => $row['StandortName']
            ];

            $activeSessionsByBehoerden[$behoerdenID]['activeSessions'] += (int) $row['activeSessions'];
        }

        return $activeSessionsByBehoerden;
    }

    /**
     * Get the configuration problems
     *
     * @return string
     */
    public function getConfigProblems($configVariables)
    {
        $problems = [];
        if ($configVariables['tmp_table_size'] < 32000000) {
            $problems[] = 'tmp_table_size should be at least 32MB';
        }
        if ($configVariables['max_heap_table_size'] < 32000000) {
            $problems[] = 'max_heap_table_size should be at least 32MB';
        }
        return implode('; ', $problems);
    }

    /**
     * Get the dldb update status
     *
     * @return array
     */
    protected function readDdldUpdateStats()
    {
        $stats = $this->getReader()->fetchOne(
            'SELECT value FROM config WHERE name = "sources_dldb_last"'
        );
        return $stats['value'];
    }

    /**
     * Get outdated slots
     *
     * @return array
     */
    protected function readOutdatedSlots()
    {
        $stats = $this->getReader()->fetchOne(
            'SELECT COUNT(*) cnt, MIN(a.updateTimestamp) oldest
             FROM slot s 
             LEFT JOIN oeffnungszeit a ON s.availabilityID = a.OeffnungszeitID
             WHERE s.updateTimestamp < a.updateTimestamp AND s.status = "free"'
        );
        return $stats;
    }

    /**
     * Get free slots count
     *
     * @return array
     */
    protected function readFreeSlots()
    {
        $stats = $this->getReader()->fetchOne(
            'SELECT SUM(intern) cnt FROM slot s WHERE s.status = "free"'
        );
        return $stats;
    }

    /**
     * Get last calculate slots information
     *
     * @return array
     */
    protected function readLastCalculateSlots()
    {
        $stats = $this->getReader()->fetchOne(
            'SELECT value FROM config WHERE name = "status__calculateSlotsLastRun"'
        );
        return $stats['value'];
    }

    /**
     * Get mail stats
     *
     * @return array
     */
    protected function readMailStats()
    {
        $stats = $this->getReader()->fetchOne(
            'SELECT COUNT(id) as queueCount, UNIX_TIMESTAMP() - MIN(createTimestamp) as oldestSeconds, 
                UNIX_TIMESTAMP() - MAX(createTimestamp) as newestSeconds
             FROM mailqueue'
        );
        return $stats;
    }

    /**
     * Get notification stats
     *
     * @return array
     */
    protected function readNotificationStats()
    {
        $stats = $this->getReader()->fetchOne(
            'SELECT COUNT(id) as queueCount, UNIX_TIMESTAMP() - MIN(createTimestamp) as oldestSeconds, 
                UNIX_TIMESTAMP() - MAX(createTimestamp) as newestSeconds
             FROM notificationqueue'
        );
        return $stats;
    }

    /**
     * Get process statistics
     *
     * @return array
     */
    protected function readProcessStats(\DateTimeImmutable $now)
    {
        $midnight = $now->modify('00:00:00')->getTimestamp();
        $last7days = $now->modify('-7 days 00:00:00')->getTimestamp();

        $processStats = $this->getReader()->fetchOne(
            'SELECT
                SUM(CASE WHEN name = "dereferenced" THEN 1 ELSE NULL END) as blocked,
                SUM(CASE WHEN b.StandortID != 0 AND vorlaeufigeBuchung = 0 AND Abholer = 0 THEN 1 ELSE NULL END) as confirmed,
                SUM(CASE WHEN (b.StandortID != 0 OR AbholortID != 0) AND vorlaeufigeBuchung = 0 AND Abholer = 1 THEN 1 ELSE NULL END) as pending,
                SUM(CASE WHEN name = "(abgesagt)" THEN 1 ELSE NULL END) as deleted,
                SUM(CASE WHEN nicht_erschienen > 0 AND b.StandortID != 0 THEN 1 ELSE NULL END) as missed,
                SUM(CASE WHEN vorlaeufigeBuchung = 1 AND b.StandortID != 0 THEN 1 ELSE NULL END) as reserved,
                SUM(CASE WHEN IPTimeStamp > '.intval($midnight).' AND b.StandortID != 0 
                    AND vorlaeufigeBuchung = 0 AND Abholer = 0 THEN 1 ELSE NULL END) as sincemidnight,
                SUM(CASE WHEN IPTimeStamp > '.intval($last7days).' AND b.StandortID != 0 
                    AND vorlaeufigeBuchung = 0 AND Abholer = 0 THEN 1 ELSE NULL END) as last7days,
                FROM_UNIXTIME(MAX(IPTimeStamp)) as lastInsert
             FROM buerger AS b
             WHERE b.istFolgeterminvon IS NULL OR b.istFolgeterminvon = 0'
        );
        return $processStats;
    }

    /**
     * Fetch MySQL config variables
     *
     * @return array
     */
    protected function readConfigVariables()
    {
        return $this->getReader()->fetchPairs('SHOW VARIABLES');
    }

    /**
     * Fetch MySQL status variables
     *
     * @return array
     */
    protected function readStatusVariables()
    {
        return $this->getReader()->fetchPairs('SHOW STATUS');
    }
}
