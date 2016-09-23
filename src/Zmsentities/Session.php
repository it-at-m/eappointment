<?php

namespace BO\Zmsentities;

/**
 * Extension for Twig and Slim
 *
 * @SuppressWarnings(PublicMethod)
 * @SuppressWarnings(TooManyMethods)
 * @SuppressWarnings(Complexity)
 */
class Session extends Schema\Entity
{

    const PRIMARY = 'id';

    public static $schema = "session.json";

    public function getDefaults()
    {
        return [
            'content' => array (
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
                    'authKey' => ''
                ],
                'human' => [
                    'captcha_text' => '',
                    'client' => 0,
                    'ts' => 0,
                    'origin' => '',
                    'remoteAddress' => '',
                    'referer' => '',
                    'step' => array ()
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
        if (! is_array($this->content)) {
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
        $defaults = $this->getDefaults();
        return array_key_exists('basket', $this->content) ? $this->content['basket'] : $defaults['content']['basket'];
    }

    public function getHuman()
    {
        return $this->content['human'];
    }

    public function getRequests()
    {
        return Helper\Sorter::toSortedCsv($this->toProperty()->content->basket->requests->get());
    }

    public function getProviders()
    {
        return Helper\Sorter::toSortedCsv($this->toProperty()->content->basket->providers->get());
    }

    public function getProcess()
    {
        return $this->toProperty()->content->basket->process->get();
    }

    public function getScope()
    {
        return $this->toProperty()->content->basket->scope->get();
    }

    public function getAuthKey()
    {
        return $this->toProperty()->content->basket->authKey->get();
    }

    public function getLastStep()
    {
        $steps = $this->toProperty()->content->human->step->get();
        $steps = (is_array($steps)) ? array_keys($steps) : null;
        return (null !== $steps) ? end($steps) : $steps;
    }

    public function getStatus()
    {
        return $this->toProperty()->content->status->get();
    }

    public function getTask()
    {
        return $this->toProperty()->content->task->get();
    }

    public function removeLastStep()
    {
        unset($this->content['human']['step'][$this->getLastStep()]);
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
        return $this->toProperty()->content->basket->date->get();
    }

    /**
     *
     * Get entry data
     *
     * @return array
     */
    public function getEntryData()
    {
        return $this->toProperty()->content->entry->get();
    }

    public function hasEntryValues()
    {
        $providers = $this->toProperty()->content->entry->providers->get();
        $requests = $this->toProperty()->content->entry->requests->get();
        return ($providers && $requests) ? true : false;
    }

    public function isEmpty()
    {
        if (!$this->hasProcess() && !$this->hasAuthKey() && !$this->hasStatus() && !$this->hasTask() &&
             ! $this->isFinished() && ! $this->hasEntryValues()) {
            return true;
        }
        return false;
    }

    public function isFinished()
    {
        $finished = $this->toProperty()->content->finished->get();
        return (null !== $finished && false !== $this->hasProcess()) ? true : false;
    }

    public function isConfirmed()
    {
        return ('confirmed' == $this->getStatus()) ? true : false;
    }

    public function isStalled()
    {
        return ('stalled' == $this->getStatus()) ? true : false;
    }

    public function isReserved()
    {
        return (
            'reserved' == $this->getStatus() &&
            null === $this->getTask() &&
            null !== $this->getProcess() &&
            !$this->hasChangedProcess()
        ) ? true : false;
    }

    public function isProcessDeleted()
    {
        return !$this->hasProcess();
    }

    public function hasStatus()
    {
        return (null === $this->getStatus()) ? false : true;
    }

    public function hasTask()
    {
        return (null === $this->getTask()) ? false : true;
    }

    public function hasProcess()
    {
        return (null === $this->getProcess()) ? false : true;
    }

    public function hasAuthKey()
    {
        return (null === $this->getAuthKey()) ? false : true;
    }

    public function hasChangedProcess()
    {
        return ('processChanged' == $this->getStatus()) ? true : false;
    }

    public function hasPreviousAppointmentSearch()
    {
        return ('inProgress' == $this->getStatus()) ? true : false;
    }

    public function hasConfirmationNotification()
    {
        return ($this->toProperty()->content->basket->confirmationNotification->get()) ? true : false;
    }

    /**
     * Check if requests exists
     *
     * @return boolean
     */
    public function hasRequests()
    {
        return ($this->getRequests()) ? true : false;
    }

    /**
     *
     * Check if provider exists
     *
     * @return boolean
     */
    public function hasProvider()
    {
        error_log($this->getProviders());
        error_log($this->getRequests());
        return ($this->getProviders()) ? true : false;
    }

    /**
     *
     * Check if scope exists
     *
     * @return boolean
     */
    public function hasScope()
    {
        return ($this->getScope()) ? true : false;
    }

    /**
     *
     * Check if date exists
     *
     * @return boolean
     */
    public function hasDate()
    {
        return ($this->getSelectedDate()) ? true : false;
    }
}
