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
                    'scope' => null,
                    'process' => null,
                    'date' => null,
                    'familyName' => '',
                    'email' => '',
                    'telehone' => '',
                    'amendment' => '',
                    'authKey' => '',
                ],
                'human' => [
                    'captcha_text' => '',
                    'client' => 0,
                    'ts' => 0,
                    'origin' => '',
                    'remoteAddress' => '',
                    'referrer' => '',
                    'step' => array()
                ],
                'status' => 'start',
                'task' => 'new',
                'finished' => false,
                'X-Authkey' => '',
                'error' => ''
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

    public function getContent()
    {
        return $this->content;
    }

    public function getBasket()
    {
        return $this->content['basket'];
    }

    public function getHuman()
    {
        return $this->content['human'];
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

    public function getLastStep()
    {
        if (isset($this->content['human']['step'])) {
            $stepKeys = array_keys($this->content['human']['step']);
            return end($stepKeys);
        }
        return 'dayselect';
    }

    public function getStatus()
    {
        if (isset($this->content['status'])) {
            return $this->content['status'];
        }
        return null;
    }

    public function getTask()
    {
        if (isset($this->content['task'])) {
            return $this->content['task'];
        }
        return null;
    }

    public function removeLastStep()
    {
        if (isset($this->content['human']['step'][$this->getLastStep()])) {
            unset($this->content['human']['step'][$this->getLastStep()]);
        }
        return $this;
    }

    /**
     *
     * Get selected date
     *
     * @return integer
     */
    public function getSelectedDate()
    {
        if (isset($this->content['basket']['date']) &&
            $this->content['basket']['date']
            ) {
                return $this->content['basket']['date'];
        }
            return null;
    }

    /**
     *
     * Get entry data
     *
     * @return array
     */
    public function getEntryData()
    {
        if (isset($this->content['entry']) && count($this->content['entry'])) {
                return $this->content['entry'];
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
            !isset($this->content['task']) &&
            !$this->hasNoProcess() &&
            !$this->hasChangedProcess()
            ) {
            return true;
        }
        return false;
    }

    public function isProcessDeleted()
    {
        if (isset($this->content['basket']['process']) &&
            $this->content['basket']['process']
            ) {
                return false;
        }
            return true;
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

    public function hasChangedProcess()
    {
        if (isset(
            $this->content['status']
        ) &&
            'processChanged' == $this->content['status']
        ) {
            return true;
        }
        return false;
    }

    public function hasPreviousAppointmentSearch()
    {
        return (
            isset($this->content['task']) &&
            'inProgress' == $this->content['task']
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
     * Check if scope exists
     *
     * @return boolean
     */
    public function hasNoScope()
    {
        return (!isset($this->content['basket']['scope']) ||
            $this->content['basket']['scope']) ? true :false;
    }
}
