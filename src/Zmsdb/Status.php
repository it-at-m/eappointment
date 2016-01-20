<?php

namespace BO\Zmsdb;

use \BO\Zmsentities\Status as Entity;

class Status extends Base
{
    /**
     * Fetch status from db
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
        $entity['database']['appointmentCount'] = $this->readValidAppointmentsCount();
        return $entity;
    }

    /**
     * Get the numer of valid appointments
     * @return Int
     */
    protected function readValidAppointmentsCount()
    {
        $appointmentCount = $this->getReader()->fetchValue(
            'SELECT
                SUM(b.AnzahlPersonen) as cnt
            FROM buerger AS b
            WHERE
                b.StandortID != 0
                AND (
                    b.istFolgeterminvon IS NULL
                    OR b.istFolgeterminvon = 0
                )
            '
        );
        return $appointmentCount;
    }

    /**
     * Fetch mysql config variables
     * @return Array
     */
    protected function readConfigVariables()
    {
        $configVariables = $this->getReader()->fetchPairs('SHOW VARIABLES');
        return $configVariables;
    }

    /**
     * Fetch mysql status variables
     * @return Array
     */
    protected function readStatusVariables()
    {
        $statusVariables = $this->getReader()->fetchPairs('SHOW STATUS');
        return $statusVariables;
    }
}
