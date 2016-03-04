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
        $entity['processes']['blocked'] = $this->readBlockedProcessCount();
        $entity['processes']['confirmed'] = $this->readConfirmedProcessCount();
        $entity['processes']['deleted'] = $this->readDeletedProcessCount();
        $entity['processes']['lastInsert'] = $this->readLastInsertedProcessTime();
        $entity['processes']['missed'] = $this->readMissedProcessCount();
        $entity['processes']['reserved'] = $this->readReservedProcessCount();
        return $entity;
    }

    /**
     * Get the numer of blocked appointments
     *
     * @return Int
     */
    protected function readBlockedProcessCount()
    {
        $processCount = $this->getReader()->fetchValue(
            'SELECT
                SUM(b.AnzahlPersonen) as cnt
            FROM buerger AS b
            WHERE
                name = "dereferenced"
                AND (
                    b.istFolgeterminvon IS NULL
                    OR b.istFolgeterminvon = 0
                )
            '
        );
        return $processCount;
    }

    /**
     * Get the numer of confirmed appointments
     *
     * @return Int
     */
    protected function readConfirmedProcessCount()
    {
        $processCount = $this->getReader()->fetchValue(
            'SELECT
                SUM(b.AnzahlPersonen) as cnt
            FROM buerger AS b
            WHERE
                b.StandortID != 0
                AND vorlaeufigeBuchung = 0
                AND (
                    b.istFolgeterminvon IS NULL
                    OR b.istFolgeterminvon = 0
                )
            '
        );
        return $processCount;
    }

    /**
     * Get the numer of last inserted appointments
     *
     * @return Int
     */
    protected function readLastInsertedProcessTime()
    {
        $processCount = $this->getReader()->fetchValue(
            'SELECT
                FROM_UNIXTIME(MAX(IPTimeStamp)) as ts
            FROM buerger AS b
            WHERE
                b.StandortID != 0
                AND vorlaeufigeBuchung = 0
                AND (
                    b.istFolgeterminvon IS NULL
                    OR b.istFolgeterminvon = 0
                )
            '
        );
        return $processCount;
    }

    /**
     * Get the numer of deleted appointments
     *
     * @return Int
     */
    protected function readDeletedProcessCount()
    {
        $processCount = $this->getReader()->fetchValue(
            'SELECT
                SUM(b.AnzahlPersonen) as cnt
            FROM buerger AS b
            WHERE
                name = "(abgesagt)"
                AND (
                    b.istFolgeterminvon IS NULL
                    OR b.istFolgeterminvon = 0
                )
            '
        );
        return $processCount;
    }

    /**
     * Get the numer of missed appointments
     *
     * @return Int
     */
    protected function readMissedProcessCount()
    {
        $processCount = $this->getReader()->fetchValue(
            'SELECT
                SUM(b.AnzahlPersonen) as cnt
            FROM buerger AS b
            WHERE
                nicht_erschienen > 0
                AND b.StandortID != 0
                AND (
                    b.istFolgeterminvon IS NULL
                    OR b.istFolgeterminvon = 0
                )
            '
        );
        return $processCount;
    }

    /**
     * Get the numer of reserved appointments
     *
     * @return Int
     */
    protected function readReservedProcessCount()
    {
        $processCount = $this->getReader()->fetchValue(
            'SELECT
                SUM(b.AnzahlPersonen) as cnt
            FROM buerger AS b
            WHERE
                b.StandortID != 0
                AND vorlaeufigeBuchung = 1
                AND (
                    b.istFolgeterminvon IS NULL
                    OR b.istFolgeterminvon = 0
                )
            '
        );
        return $processCount;
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
