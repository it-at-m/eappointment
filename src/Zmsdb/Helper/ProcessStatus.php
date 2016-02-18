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
