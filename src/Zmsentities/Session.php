<?php

namespace BO\Zmsentities;

/**
 * Extension for Twig and Slim
 *
 *  @SuppressWarnings(PublicMethod)
 *  @SuppressWarnings(TooManyMethods)
 *  @SuppressWarnings(Complexity)
 */

class Session extends Schema\Entity
{
    const PRIMARY = 'id';

    public static $schema = "session.json";

    public function getDefaults()
    {
        return [
            'content' => array(
                'basket' => [
                    'requests' => '',
                    'providers' => '',
                    'scope' => '',
                    'process' => '',
                    'date' => '',
                    'familyName' => '',
                    'email' => '',
                    'telehone' => '',
                    'amendment' => '',
                    'authKey' => '',
                ],
                'human' => [
                    'captcha_text' => '',
                    'Client' => 0,
                    'TS' => 0,
                    'Origin' => '',
                    'RemoteAddress' => '',
                    'referrer' => '',
                    'Step' => array()
                ],
                'entry' => array(),
                'status' => 'free',
                'task' => '',
                'finished' => false,
                'X-Authkey' => ''
            )
        ];
    }

    public function getUnserializedContent()
    {
        if (!is_array($this->content)) {
            $this->content = unserialize($this->content);
        }
        return $this;
    }

    public function getSerializedContent()
    {
        if (is_array($this->content)) {
            $this->content = serialize($this->content);
        }
        return $this;
    }

    public function getRequests()
    {
        if (isset($this->content['basket']['requests'])) {
            return Helper\Sorter::toSortedCsv($this->content['basket']['requests']);
        }
        return null;
    }

    public function getProviders()
    {
        if (isset($this->content['basket']['providers'])) {
            return Helper\Sorter::toSortedCsv($this->content['basket']['providers']);
        }
        return null;
    }

    public function getProcess()
    {
        if (isset($this->content['basket']['process'])) {
            return $this->content['basket']['process'];
        }
        return null;
    }

    public function getAuthKey()
    {
        if (isset($this->content['basket']['authKey'])) {
            return $this->content['basket']['authKey'];
        }
        return null;
    }

    public function hasEntryValues()
    {
        if (isset($this->content['entry']) && count($this->content['entry'])) {
            return true;
        }
        return false;
    }

    public function isEmpty()
    {
        if ($this->hasNoProcess() &&
            $this->hasNoAuthKey() &&
            $this->hasNoStatus() &&
            $this->hasNoTask() &&
            !$this->isFinished() &&
            !$this->hasEntryValues()
            ) {
                return true;
        }
            return false;
    }

    public function isFinished()
    {
        if ((isset($this->content['finished']) &&
            $this->content['finished']) &&
            !$this->hasNoProcess()
        ) {
            return true;
        }
        return false;
    }

    public function isConfirmed()
    {
        if (isset($this->content['status']) &&
            'confirmed' == $this->content['status']
        ) {
            return true;
        }
        return false;
    }

    public function isReserved()
    {
        if (isset($this->content['status']) &&
            'reserved' == $this->content['status'] &&
            'continue' != $this->content['task'] &&
            !$this->hasNoProcess() &&
            !$this->hasChangedReservation()
            ) {
            return true;
        }
        return false;
    }

    public function hasNoStatus()
    {
        if ((!isset($this->content['status']) ||
            '' == $this->content['status'])) {
            return true;
        }
        return false;
    }

    public function hasNoTask()
    {
        if ((!isset($this->content['task']) ||
            '' == $this->content['task'])) {
            return true;
        }
        return false;
    }

    public function hasNoProcess()
    {
        if ((!isset($this->content['basket']['process']) ||
            '' == $this->content['basket']['process'])) {
            return true;
        }
        return false;
    }

    public function hasNoAuthKey()
    {
        if ((!isset($this->content['basket']['authKey']) ||
            '' == $this->content['basket']['authKey'])) {
            return true;
        }
        return false;
    }

    public function isProcessDeleted()
    {
        if (isset($this->content['basket']['process']) &&
            '' != $this->content['basket']['process']
        ) {
            return false;
        }
        return true;
    }

    public function hasChangedReservation()
    {
        if (isset($this->content['task']) &&
            'reservation_changed' == $this->content['task']
        ) {
            return true;
        }
        return false;
    }

    public function hasChangedProcess()
    {
        if (isset(
            $this->content['task']
        ) &&
            'process_changed' == $this->content['task']
        ) {
            return true;
        }
        return false;
    }

    public function hasPreviousAppointmentSearch()
    {
        return (
            isset($this->content['task']) &&
            'inprogress' == $this->content['task']
        ) ? true : false;
    }

    public function hasConfirmationNotification()
    {
        return (
            isset($this->content['basket']['confirmationNotification']) &&
            $this->content['basket']['confirmationNotification']
        ) ? true : false;
    }

    /**
     * Check if requests exists
     *
     * @return boolean
     */
    public function hasNoRequests()
    {
        return (
            !isset($this->content['basket']['requests']) ||
            '' == $this->content['basket']['requests']) ? true : false;
    }

    /**
     *
     * Check if provider exists
     *
     * @return boolean
     */
    public function hasNoProvider()
    {
        return (!isset($this->content['basket']['providers']) ||
            '' == $this->content['basket']['providers']) ? true : false;
    }

    /**
     *
     * Check if a date is selected
     *
     * @return boolean
     */
    public function hasNoSelectedDate()
    {
        return (!isset($this->content['basket']['date']) ||
            '' == $this->content['basket']['date']) ? true :false;
    }

    /**
     *
     * Check if scope exists
     *
     * @return boolean
     */
    public function hasNoScope()
    {
        return (!isset($this->content['basket']['scope']) ||
            '' == $this->content['basket']['scope']) ? true :false;
    }
}
