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
    public const PRIMARY = 'id';

    public static $schema = "session.json";

    public function getDefaults()
    {
        return [
            'content' => array(
                'basket' => [
                    'requests' => '',
                    'providers' => '',
                    'scope' => '0',
                    'process' => '0',
                    'date' => '0',
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
                    'step' => array(
                      'dayselect' => 0,
                      'timeselect' => 0,
                      'register' => 0,
                      'summary' => 0
                    )
                ],
                'source' => 'dldb',
                'status' => 'start',
                'X-Authkey' => '',
                'error' => ''
            )
        ];
    }

    public function getContent()
    {
        return $this->toProperty()->content->get();
    }

    public function getBasket()
    {
        return $this->toProperty()->content->basket->get();
    }

    public function getHuman()
    {
        return $this->toProperty()->content->human->get();
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

    public function getSource()
    {
        return $this->toProperty()->content->source->get();
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

    public function isEmpty()
    {
        return (! $this->hasProvider() && ! $this->hasRequests() && ! $this->hasScope()) ? true : false;
    }

    public function isInChange()
    {
        return ('inChange' == $this->getStatus()) ? true : false;
    }

    public function isStalled()
    {
        return ('stalled' == $this->getStatus()) ? true : false;
    }

    public function isReserved()
    {
        return ('reserved' == $this->getStatus()) ? true : false;
    }

    public function isConfirmed()
    {
        return ('confirmed' == $this->getStatus()) ? true : false;
    }

    public function isPreconfirmed()
    {
        return ('preconfirmed' == $this->getStatus()) ? true : false;
    }

    public function isFinished()
    {
        return ('finished' == $this->getStatus()) ? true : false;
    }

    public function isProcessDeleted()
    {
        return ! $this->hasProcess();
    }

    public function hasStatus()
    {
        return (null === $this->getStatus()) ? false : true;
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
        return ('inChange' == $this->getStatus()) ? true : false;
    }

    public function hasPreviousAppointmentSearch()
    {
        return ('inProgress' == $this->getStatus()) ? true : false;
    }

    public function hasConfirmationNotification()
    {
        return ($this->toProperty()->content->confirmationNotification->get()) ? true : false;
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

    /**
     *
     * Check if entry parameter are different
     *
     * @return boolean
     */
    public function hasDifferentEntry($newEntryData)
    {
        return (
            ($this->getProviders() || $this->getScope()) &&
            $this->getRequests() &&
            $this->getEntryData() &&
            (
                !($this->getProviders() == Helper\Sorter::toSortedCsv($newEntryData['providers'])) ||
                !($this->getRequests() == Helper\Sorter::toSortedCsv($newEntryData['requests'])) ||
                !($this->getScope() == $newEntryData['scope'])
            )
         ) ? true : false;
    }

    public function withOidcDataOnly()
    {
        $entity = clone $this;
        if ($entity->toProperty()->content->basket->isAvailable()) {
            unset($entity->content['basket']);
        }
        if ($entity->toProperty()->content->human->isAvailable()) {
            unset($entity->content['human']);
        }
        if ($entity->toProperty()->content->entry->isAvailable()) {
            unset($entity->content['entry']);
        }
        if ($entity->toProperty()->content->source->isAvailable()) {
            unset($entity->content['source']);
        }
        if ($entity->toProperty()->content->status->isAvailable()) {
            unset($entity->content['status']);
        }
        if ($entity->toProperty()->content['X-Authkey']->isAvailable()) {
            unset($entity->content['X-Authkey']);
        }
        if ($entity->toProperty()->content->error->isAvailable()) {
            unset($entity->content['error']);
        }
        return $entity;
    }
}
