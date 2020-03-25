<?php
namespace BO\Zmsdb\Helper;

use \BO\Zmsdb\Log;

class ProcessStatus extends \BO\Zmsdb\Process
{
    public function writeUpdatedStatus(
        \BO\Zmsentities\Process $process,
        $status,
        \DateTimeInterface $dateTime,
        $resolveReferences,
        $userId
    ) {
        $query = new \BO\Zmsdb\Query\Process(\BO\Zmsdb\Query\Base::UPDATE);
        $query->addConditionProcessId($process['id']);
        $query->addConditionAuthKey($process['authKey']);

        $statusList = [
            'reserved' => 'createReservedProcessEntity',
            'confirmed' => 'createConfirmedProcessEntity',
            'queued' => 'createQueuedProcessEntity',
            'called' => 'createCalledProcessEntity',
            'processing' => 'createProcessingProcessEntity',
            'pending' => 'createPendingProcessEntity',
            'missed' => 'createMissedProcessEntity',
            'blocked' => 'createBlockedProcessEntity',
            'deleted' => 'createDeletedProcessEntity',
        ];

        $entity = call_user_func_array(array($this, $statusList[$status]), array($process));
        $query->addValuesUpdateProcess($entity, $dateTime);
        
        $checksum = sha1($process->getId() . '-' . $userId);
        Log::writeLogEntry("CREATE (ProcessStatus::writeUpdatedStatus) $checksum ", $process->id);
        
        $this->writeItem($query, 'process', $query::TABLE);
        $this->perform(\BO\Zmsdb\Query\Process::QUERY_UPDATE_FOLLOWING_PROCESS, [
            'reserved' => ($process->status == 'reserved') ? 1 : 0,
            'processID' => $process->getId(),
        ]);
        $process = $this->readEntity($process['id'], $process['authKey'], $resolveReferences);
        $process['status'] = $this->readProcessStatus($process['id'], $process['authKey']);
        return $process;
    }

    /**
     * get the current process status from given Id and authKey
     *
     * @return String
     */
    public function readProcessStatus($processId, $authKey)
    {
        $processData = $this->getReader()->fetchOne(
            'SELECT
            *
            FROM buerger AS b
            WHERE
                b.BuergerID = "' . $processId . '"
                AND b.absagecode = "' . $authKey . '"
            LIMIT 1
            '
        );
        $statusList = [
            'free' => true,
            'reserved' => $this->isReservedProcess($processData),
            'confirmed' => $this->isConfirmedProcess($processData),
            'queued' => $this->isQueuedProcess($processData),
            'called' => $this->isCalledProcess($processData),
            'processing' => $this->isProcessingProcess($processData),
            'pending' => $this->isPendingProcess($processData),
            'missed' => $this->isMissedProcess($processData),
            'blocked' => $this->isBlockedProcess($processData),
            'deleted' => $this->isDeletedProcess($processData),
        ];
        foreach ($statusList as $statusType => $statusCheck) {
            if ($statusCheck) {
                $status = $statusType;
            }
        }
        return $status;
    }


    /**
     * get the current process status from given Id and authKey
     * removed 2017-06-13 from TK, not in use
     * @return String
     */
     /*
    public function readQueueStatus($processId, $authKey)
    {
        $status = '';
        $processData = $this->getReader()->fetchOne(
            'SELECT
            *
            FROM buerger AS b
            WHERE
                b.BuergerID = "' . $processId . '"
                AND b.absagecode = "' . $authKey . '"
            LIMIT 1
            '
        );
        $statusList = [
            'queued' => $this->isQueuedProcess($processData),
            'confirmed' => $this->isConfirmedProcess($processData),
            'called' => $this->isCalledProcess($processData),
            'processing' => $this->isProcessingProcess($processData),
            'pending' => $this->isPendingProcess($processData),
            'missed' => $this->isMissedProcess($processData)
        ];
        foreach ($statusList as $statusType => $statusCheck) {
            if ($statusCheck) {
                $status = $statusType;
            }
        }
        return $status;
    }
    */

    protected function createConfirmedProcessEntity($process)
    {
        $process['status'] = 'confirmed';
        return $process;
    }

    /**
     * check if it is a blocked appointment
     *
     * @return Bool
     */
    protected function isBlockedProcess($process)
    {
        return ($process['Name'] == 'dereferenced');
    }

    /**
     * check if it is a queued appointment
     *
     * @return Bool
     */
    protected function isQueuedProcess($process)
    {
        return ($process['Name'] != 'dereferenced'
            && $process['vorlaeufigeBuchung'] == 0
            && $process['StandortID'] != 0
            && $process['wsm_aufnahmezeit'] != '00:00:00'
            && $process['aufrufzeit'] != '00:00:00'
            && empty($process['istFolgeterminvon'])
            );
    }

    /**
     * check if it is a confirmed appointment
     *
     * @return Bool
     */
    protected function isConfirmedProcess($process)
    {
        return ($process['Name'] != 'dereferenced'
            && $process['vorlaeufigeBuchung'] == 0
            && $process['StandortID'] != 0
            && empty($process['istFolgeterminvon'])
        );
    }

    /**
     * check if it is a reserved appointment
     *
     * @return Bool
     */
    protected function isReservedProcess($process)
    {
        return ($process['Name'] != 'dereferenced'
            && $process['vorlaeufigeBuchung'] == 1
            && $process['StandortID'] != 0
            && empty($process['istFolgeterminvon'])
        );
    }

    /**
     * check if it is a called appointment
     *
     * @return Bool
     */
    protected function isCalledProcess($process)
    {
        return ($process['Name'] != 'dereferenced'
            && $process['vorlaeufigeBuchung'] == 0
            && $process['StandortID'] != 0
            && $process['aufrufzeit'] != '00:00:00'
            && $process['aufruferfolgreich'] == 0
            && empty($process['istFolgeterminvon'])
        );
    }

    /**
     * check if it is a processing appointment
     *
     * @return Bool
     */
    protected function isProcessingProcess($process)
    {
        return ($process['Name'] != 'dereferenced'
            && $process['vorlaeufigeBuchung'] == 0
            && $process['StandortID'] != 0
            && $process['aufruferfolgreich'] != 0
            && empty($process['istFolgeterminvon'])
        );
    }

    /**
     * check if it is a processing appointment
     *
     * @return Bool
     */
    protected function isPendingProcess($process)
    {
        return ($process['Name'] != 'dereferenced'
            && $process['vorlaeufigeBuchung'] == 0
            && $process['StandortID'] != 0
            && $process['Abholer'] != 0
            && $process['AbholortID'] != 0
            && empty($process['istFolgeterminvon'])
        );
    }

    /**
     * check if it is a missed appointment
     *
     * @return Bool
     */
    protected function isMissedProcess($process)
    {
        return ($process['Name'] != 'dereferenced'
            && $process['vorlaeufigeBuchung'] == 0
            && $process['StandortID'] != 0
            && $process['nicht_erschienen'] != 0
            && empty($process['istFolgeterminvon'])
        );
    }

    /**
     * check if it is a deleted appointment
     *
     * @return Bool
     */
    protected function isDeletedProcess($process)
    {
        return ($process['Name'] == '(abgesagt)'
            && empty($process['istFolgeterminvon'])
        );
    }
}
