<?php
namespace BO\Zmsdb\Helper;

class ProcessStatus extends \BO\Zmsdb\Process
{

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
     * check if it is a blocked appointment
     *
     * @return Bool
     */
    protected function isBlockedProcess($process)
    {
        return ($process['Name'] == 'dereferenced');
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
