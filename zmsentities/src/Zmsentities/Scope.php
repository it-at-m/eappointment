<?php

namespace BO\Zmsentities;

/**
 * @SuppressWarnings(Complexity)
 */
class Scope extends Schema\Entity implements Useraccount\AccessInterface
{
    const PRIMARY = 'id';

    public static $schema = "scope.json";

    public function getDefaults()
    {
        return [
            'id' => 0,
            'source' => 'dldb',
            'contact' => new Contact(),
            'provider' => new Provider(),
        ];
    }

    public function getSource()
    {
        return $this->toProperty()->source->get();
    }

    public function getProvider()
    {
        if (!$this->provider instanceof Provider) {
            $this->provider = new Provider($this->toProperty()->provider->get());
        }
        if (!isset($this->provider['id']) || !$this->provider->id) {
            $exception = new Exception\ScopeMissingProvider("No reference to a provider found for scope $this->id");
            $exception->data['scope'] = $this->getArrayCopy();
            throw $exception;
        }
        return $this->provider;
    }

    public function getProviderId()
    {
        return $this->getProvider()->id;
    }

    public function getDayoffList()
    {
        if (!isset($this->dayoff) || !$this->dayoff instanceof Collection\DayoffList) {
            $this->dayoff = (!isset($this->dayoff) || !is_array($this->dayoff)) ? [] : $this->dayoff;
            $this->dayoff = new Collection\DayoffList($this->dayoff);
            foreach ($this->dayoff as $key => $dayoff) {
                if (!$dayoff instanceof Dayoff) {
                    $this->dayoff[$key] = new Dayoff($dayoff);
                }
            }
        }
        return $this->dayoff;
    }

    public function getRequestList()
    {
        return $this->getProvider()->getRequestList();
    }

    public function getNotificationPreferences()
    {
        return $this->toProperty()->preferences->notifications->get();
    }

    public function getConfirmationContent()
    {
        return $this->toProperty()->preferences->notifications->confirmationContent->get();
    }

    public function getHeadsUpContent()
    {
        return $this->toProperty()->preferences->notifications->headsUpContent->get();
    }

    public function getPreference($preferenceKey, $index, $isBool = false, $default = null)
    {
        $preference = $this->toProperty()->preferences->$preferenceKey->$index->get($default);
        return ($isBool) ? ($preference ? 1 : 0) : $preference;
    }

    public function getStatus($statusKey, $index)
    {
        return $this->toProperty()->status->$statusKey->$index->get();
    }

    public function getContactEmail()
    {
        return $this->toProperty()->contact->email->get();
    }

    public function getName()
    {
        return $this->toProperty()->contact->name->get();
    }

    public function getScopeInfo()
    {
        return $this->toProperty()->preferences->ticketprinter->buttonName->get();
    }

    public function getScopeHint()
    {
        return $this->toProperty()->hint->get();
    }

    public function getAlternateRedirectUrl()
    {
        $alternateUrl = $this->toProperty()->preferences->client->alternateAppointmentUrl->get();

        return ($alternateUrl) ? $alternateUrl : null;
    }

    public function getAppointmentsPerMail()
    {
        $appointmentsPerMail = $this->toProperty()->preferences->client->appointmentsPerMail->get();

        return ($appointmentsPerMail) ? $appointmentsPerMail : null;
    }

    public function getSlotsPerAppointment()
    {
        $slotsPerAppointment = $this->toProperty()->preferences->client->slotsPerAppointment->get();

        return ($slotsPerAppointment) ? $slotsPerAppointment : null;
    }

    public function getWhitelistedMails()
    {
        $emails = $this->toProperty()->preferences->client->whitelistedMails->get();

        return ($emails) ? $emails : '';
    }

    public function getWaitingTimeFromQueueList(Collection\QueueList $queueList, \DateTimeInterface $dateTime)
    {
        return $queueList->getEstimatedWaitingTime(
            $this->getPreference('queue', 'processingTimeAverage'),
            $this->getCalculatedWorkstationCount(),
            $dateTime
        );
    }

    public function getCalculatedWorkstationCount()
    {
        $workstationCount = null;
        if ($this->getStatus('queue', 'workstationCount') > 0) {
            $workstationCount = $this->getStatus('queue', 'workstationCount');
        } elseif (! $workstationCount && $this->getStatus('queue', 'ghostWorkstationCount') > 0) {
            $workstationCount = $this->getStatus('queue', 'ghostWorkstationCount');
        }
        return $workstationCount;
    }

    /**
    * Get last bookable start date of a scope
    *
    * @return \DateTimeImmutable $scopeEndDate
    */
    public function getBookableStartDate(\DateTimeInterface $now)
    {
        $now = Helper\DateTime::create($now);
        $scopeStartDate = $this->toProperty()->preferences->appointment->startInDaysDefault->get();
        return ($scopeStartDate) ? $now->modify('+' . $scopeStartDate . 'days') : $now;
    }

    /**
    * Get last bookable end date of a scope
    *
    * @return \DateTimeImmutable $scopeEndDate
    */
    public function getBookableEndDate(\DateTimeInterface $now)
    {
        $now = Helper\DateTime::create($now);
        $scopeEndDate = $this->toProperty()->preferences->appointment->endInDaysDefault->get();
        return ($scopeEndDate) ? $now->modify('+' . $scopeEndDate . 'days') : $now;
    }

    public function updateStatusQueue(\DateTimeInterface $dateTime)
    {
        $lastQueueUpdateDate = Helper\DateTime::create()
            ->setTimestamp($this->getStatus('queue', 'lastGivenNumberTimestamp'));
        if ($lastQueueUpdateDate->format('Y-m-d') == $dateTime->format('Y-m-d')) {
            $this->setStatusQueue('lastGivenNumber', $this->getStatus('queue', 'lastGivenNumber') + 1);
            $this->setStatusQueue('givenNumberCount', $this->getStatus('queue', 'givenNumberCount') + 1);
        } else {
            $this->setStatusQueue('lastGivenNumber', $this->getPreference('queue', 'firstNumber'));
            $this->setStatusQueue('givenNumberCount', 1);
        }
        if ($this->getStatus('queue', 'lastGivenNumber') < $this->getPreference('queue', 'firstNumber')) {
            $this->setStatusQueue('lastGivenNumber', $this->getPreference('queue', 'firstNumber'));
        } elseif ($this->getStatus('queue', 'lastGivenNumber') > $this->getPreference('queue', 'lastNumber')) {
            $this->setStatusQueue('lastGivenNumber', $this->getPreference('queue', 'firstNumber'));
        }
        $this->setStatusQueue('lastGivenNumberTimestamp', $dateTime->getTimestamp());
        return $this;
    }

    public function hasEmailFrom()
    {
        $emailFrom = $this->getPreference('client', 'emailFrom');
        return ($emailFrom) ? true : false;
    }

    public function hasNotificationEnabled()
    {
        $notificationEnabled = $this->getPreference('appointment', 'notificationConfirmationEnabled');
        return ($notificationEnabled) ? true : false;
    }

    public function hasNotificationReminderEnabled()
    {
        $hasReminderEnabled = $this->getPreference('appointment', 'notificationHeadsUpEnabled');
        return ($hasReminderEnabled) ? true : false;
    }

    public function isEmailRequired()
    {
        $emailFrom = $this->getPreference('client', 'emailFrom');
        $emailRequired = $this->getPreference('client', 'emailRequired');
        return ($emailFrom && $emailRequired) ? true : false;
    }

    public function isTelephoneRequired()
    {
        $telephoneRequired = $this->getPreference('client', 'telephoneRequired');
        return $telephoneRequired ? true : false;
    }

    public function hasAccess(Useraccount $useraccount)
    {
        return $useraccount->hasRights(['superuser']) ||  $useraccount->hasScope($this->id);
    }

    /**
     * Reduce data of dereferenced entities to a required minimum
     *
     */
    public function withLessData(array $keepArray = [])
    {
        $entity = clone $this;
        if (! in_array('dayoff', $keepArray) && $entity->toProperty()->dayoff->isAvailable()) {
            unset($entity['dayoff']);
        }
        if (! in_array('status', $keepArray)) {
            unset($entity['status']);
        }
        if (! in_array('preferences', $keepArray)) {
            unset($entity['preferences']);
        }

        return $entity;
    }

    public function setStatusQueue($key, $value)
    {
        $this->status['queue'][$key] = $value;
        return $this;
    }

    public function setStatusAvailability($key, $value)
    {
        $this->status['availability'][$key] = $value;
        return $this;
    }

    /**
     * Check if scope is newer than given time
     *
     * @return bool
     */
    public function isNewerThan(\DateTimeInterface $dateTime)
    {
        return ($dateTime->getTimestamp() < $this->lastChange);
    }

    public function __toString()
    {
        $string = 'scope#';
        $string .= $this['id'];
        $string .= ' ';
        $string .= $this->getName();
        return $string;
    }
}
