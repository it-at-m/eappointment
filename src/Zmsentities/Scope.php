<?php
namespace BO\Zmsentities;

class Scope extends Schema\Entity implements Useraccount\AccessInterface
{
    const PRIMARY = 'id';

    public static $schema = "scope.json";

    public function getDefaults()
    {
        return [
            'id' => 0,
        ];
    }

    public function getProviderId()
    {
        $refString = '$ref';
        $providerId = $this->toProperty()->provider->id->get();
        $providerRef = $this->toProperty()->provider->$refString->get();
        $providerId = ($providerId) ? $providerId : preg_replace('#^.*/(\d+)/$#', '$1', $providerRef);
        if ($providerId) {
            return $providerId;
        }
        throw new Exception\ScopeMissingProvider("No reference to a provider found");
    }

    public function getDayoffList()
    {
        if (!$this->dayoff instanceof Collection\DayoffList) {
            $this->dayoff = new Collection\DayoffList($this->dayoff);
            foreach ($this->dayoff as $key => $dayoff) {
                if (!$dayoff instanceof Dayoff) {
                    $this->dayoff[$key] = new Dayoff($dayoff);
                }
            }
        }
        return $this->dayoff;
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

    public function getPreference($preferenceKey, $index, $isBool = false)
    {
        $preference = $this->toProperty()->preferences->$preferenceKey->$index->get();
        if (!$isBool && null !== $preference) {
            return $preference;
        }
        return ($isBool && $preference) ? 1 : 0;
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

    public function hasAccess(Useraccount $useraccount)
    {
        return $useraccount->hasScope($this->id);
    }

    /**
     * Reduce data of dereferenced entities to a required minimum
     *
     */
    public function withLessData()
    {
        $entity = clone $this;
        if ($entity->toProperty()->dayoff->isAvailable()) {
            unset($entity['dayoff']);
        }
        unset($entity['status']);
        unset($entity['preferences']);
        return $entity;
    }

    public function setStatusQueue($key, $value)
    {
        $this->status['queue'][$key] = $value;
        return $this;
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
