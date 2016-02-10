<?php
namespace BO\Zmsdb;

use \BO\Zmsentities\Process as Entity;
use \BO\Zmsdb\Query\SlotList;

class Process extends Base
{

    public function readEntity($processId, $authKey, $resolveReferences = 0)
    {
        $query = new Query\Process(Query\Base::SELECT);
        $query->addEntityMapping()
            ->addResolvedReferences($resolveReferences)
            ->addConditionProcessId($processId)
            ->addConditionAuthKey($authKey);
        // echo json_decode($query->getSql());
        // var_dump($this->fetchOne($query, new Entity()));
        $process = $this->fetchOne($query, new Entity());
        $process->status = $this->getProcessStatus($processId, $authKey);
        return $process;
    }

    public function readResolvedEntity(\BO\Zmsentities\Process $process)
    {
        $process['processing'] = [];
        $process['processing']['slotlist'] = new SlotList();
        $process = $this->readResolvedProviders($process);
        $process = $this->readResolvedRequests($process);
        $process = $this->readResolvedDay($process);
        unset($process['processing']);
        return $process;
    }

    protected function readResolvedRequests(\BO\Zmsentities\Process $process)
    {
        $requestReader = new Request($this->getWriter(), $this->getReader());
        if (!isset($process['processing']['slotinfo'])) {
            $process['processing']['slotinfo'] = [];
        }
        foreach ($process['requests'] as $key => $request) {
            $request = $requestReader->readEntity('dldb', $request['id']);
            $process['requests'][$key] = $request;
            foreach ($requestReader->readSlotsOnEntity($request) as $slotinfo) {
                if (!isset($process['processing']['slotinfo'][$slotinfo['provider__id']])) {
                    $process['processing']['slotinfo'][$slotinfo['provider__id']] = 0;
                }
                $process['processing']['slotinfo'][$slotinfo['provider__id']] += $slotinfo['slots'];
            }
        }
        return $process;
    }

    protected function readResolvedProviders(\BO\Zmsentities\Process $process)
    {
        $scopeReader = new Scope($this->getWriter(), $this->getReader());
        $providerReader = new Provider($this->getWriter(), $this->getReader());
        foreach ($process['providers'] as $key => $provider) {
            $process['providers'][$key] = $providerReader->readEntity('dldb', $provider['id']);
            $scopeList = $scopeReader->readByProviderId($provider['id']);
            foreach ($scopeList as $scope) {
                $process['scopes'][] = $scope;
            }
        }
        return $process;
    }

    protected function readResolvedDay(\BO\Zmsentities\Process $process)
    {
        $query = SlotList::getQuery();
        $statement = $this->getReader()->prepare($query);
        $date = \DateTime::createFromFormat('Y-m-d', $process['date']);
        $process['appointments'] = array();
        foreach ($process->scopes as $scope) {
            $statement->execute(SlotList::getParameters($scope['id'], $date));
            while ($slotData = $statement->fetch(\PDO::FETCH_ASSOC)) {
                $appointment = $this->addAppointmentsToProcess($process, $slotData, $date);
                if(null !== $appointment){
                    $process['appointments'][] = $appointment;
                }
            }
        }
        return $process;
    }

    protected function addAppointmentsToProcess($process, array $slot, \DateTime $date)
    {
        $appointment = null;
        $slotDate = \DateTime::createFromFormat('Y-m-d', $slot['year']."-".$slot['month'].'-'.$slot['day']);
        if($slotDate->format('Y-m-d') == $date->format('Y-m-d')){
            $scopeReader = new Scope($this->getWriter(), $this->getReader());
            $scope = $scopeReader->readEntity($slot['appointment__scope__id'],1);
            $appointment = new \BO\Zmsentities\Appointment();
            $appointment->addDate($slot['appointment__date']);
            $appointment->scope = $scope;

        }
        return $appointment;
    }

    /**
     * get the current process status from given Id and authKey
     *
     * @return String
     */
    public function getProcessStatus($processId, $authKey)
    {
        $processData = $this->getReader()->fetchOne('SELECT
            *
            FROM buerger AS b
            WHERE
                b.BuergerID = "' . $processId . '"
                AND b.absagecode = "' . $authKey . '"
        ');

        $status = 'free';

        if ($this->isReservedProcess($processData)) {
            $status = 'reserved';
        }
        if ($this->isConfirmedProcess($processData)) {
            $status = 'confirmed';
        }
        if ($this->isQueuedProcess($processData)) {
            $status = 'queued';
        }
        if ($this->isCalledProcess($processData)) {
            $status = 'called';
        }
        if ($this->isProcessingProcess($processData)) {
            $status = 'processing';
        }
        if ($this->isPendingProcess($processData)) {
            $status = 'pending';
        }
        if ($this->isMissedProcess($processData)) {
            $status = 'missed';
        }
        if ($this->isBlockedProcess($processData) || $this->isDeletedProcess($processData)) {
            $status = 'blocked';
        }

        return $status;
    }

    /**
     * check if it is a blocked appointment
     *
     * @return Bool
     */
    protected function isBlockedProcess($process)
    {
        if ($process['Name'] == 'dereferenced') {
            return true;
        }
        return false;
    }

    /**
     * check if it is a confirmed appointment
     *
     * @return Bool
     */
    protected function isConfirmedProcess($process)
    {
        if ($process['Name'] != 'dereferenced'
            && $process['vorlaeufigeBuchung'] == 0
            && $process['StandortID'] != 0
            && empty($process['istFolgeterminvon'])) {
            return true;
        }
        return false;
    }

    /**
     * check if it is a reserved appointment
     *
     * @return Bool
     */
    protected function isReservedProcess($process)
    {
        if ($process['Name'] != 'dereferenced'
            && $process['vorlaeufigeBuchung'] == 1
            && $process['StandortID'] != 0
            && empty($process['istFolgeterminvon'])) {
            return true;
        }
        return false;
    }

    /**
     * check if it is a called appointment
     *
     * @return Bool
     */
    protected function isCalledProcess($process)
    {
        if ($process['Name'] != 'dereferenced'
            && $process['vorlaeufigeBuchung'] == 0
            && $process['StandortID'] != 0
            && $process['aufrufzeit'] != '00:00:00'
            && $process['aufruferfolgreich'] == 0
            && empty($process['istFolgeterminvon'])) {
            return true;
        }
        return false;
    }

    /**
     * check if it is a queued appointment
     *
     * @return Bool
     */
    protected function isQueuedProcess($process)
    {
        if ($process['Name'] != 'dereferenced'
            && $process['vorlaeufigeBuchung'] == 0
            && $process['StandortID'] != 0
            && $process['wsm_aufnahmezeit'] != '00:00:00'
            && empty($process['istFolgeterminvon'])) {
                return true;
            }
            return false;
    }

    /**
     * check if it is a processing appointment
     *
     * @return Bool
     */
    protected function isProcessingProcess($process)
    {
        if ($process['Name'] != 'dereferenced'
            && $process['vorlaeufigeBuchung'] == 0
            && $process['StandortID'] != 0
            && $process['aufruferfolgreich'] != 0
            && empty($process['istFolgeterminvon'])) {
                return true;
            }
            return false;
    }

    /**
     * check if it is a processing appointment
     *
     * @return Bool
     */
    protected function isPendingProcess($process)
    {
        if ($process['Name'] != 'dereferenced'
            && $process['vorlaeufigeBuchung'] == 0
            && $process['StandortID'] != 0
            && $process['Abholer'] != 0
            && empty($process['istFolgeterminvon'])) {
                return true;
            }
            return false;
    }

    /**
     * check if it is a missed appointment
     *
     * @return Bool
     */
    protected function isMissedProcess($process)
    {
        if ($process['Name'] != 'dereferenced'
            && $process['vorlaeufigeBuchung'] == 0
            && $process['StandortID'] != 0
            && $process['nicht_erschienen'] != 0
            && empty($process['istFolgeterminvon'])) {
                return true;
            }
            return false;
    }

    /**
     * check if it is a deleted appointment
     *
     * @return Bool
     */
    protected function isDeletedProcess($process)
    {
        if ($process['Name'] == '(abgesagt)'
            && empty($process['istFolgeterminvon'])) {
                return true;
            }
            return false;
    }
}
