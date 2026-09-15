<?php

namespace BO\Zmsentities;

use BO\Zmsentities\Collection\ClosureList;
use BO\Zmsentities\Collection\DayoffList;

/**
 * @SuppressWarnings(Complexity)
 */
class Scope extends Schema\Entity implements Useraccount\AccessInterface
{
    public const string PRIMARY = 'id';

    public static $schema = "scope.json";

    /**
     * @return (Contact|DayoffList|Provider|int|string)[]
     *
     */
    #[\Override]
    public function getDefaults()
    {
        return [
            'id' => 0,
            'source' => 'dldb',
            'contact' => new Contact(),
            'provider' => new Provider(),
            'shortName' => '',
            'dayoff' => new DayoffList()
        ];
    }

    public function getSource(): mixed
    {
        return $this->toProperty()->source->get();
    }

    public function getShortName(): mixed
    {
        return $this->toProperty()->shortName->get();
    }

    public function getEmailFrom(): mixed
    {
        return $this->getPreference('client', 'emailFrom', null);
    }

    public function getEmailRequired(): mixed
    {
        return $this->getPreference('client', 'emailRequired', null);
    }

    public function getTelephoneActivated(): mixed
    {
        return $this->getPreference('client', 'telephoneActivated', null);
    }

    public function getTelephoneRequired(): mixed
    {
        return $this->getPreference('client', 'telephoneRequired', null);
    }

    public function getCustomTextfieldActivated(): mixed
    {
        return $this->getPreference('client', 'customTextfieldActivated', null);
    }

    public function getCustomTextfieldRequired(): mixed
    {
        return $this->getPreference('client', 'customTextfieldRequired', null);
    }

    public function getCustomTextfieldLabel(): mixed
    {
        return $this->getPreference('client', 'customTextfieldLabel', '');
    }

    public function getCustomTextfield2Activated(): mixed
    {
        return $this->getPreference('client', 'customTextfield2Activated', null);
    }

    public function getCustomTextfield2Required(): mixed
    {
        return $this->getPreference('client', 'customTextfield2Required', null);
    }

    public function getCustomTextfield2Label(): mixed
    {
        return $this->getPreference('client', 'customTextfield2Label', '');
    }

    public function getCaptchaActivatedRequired(): mixed
    {
        return $this->getPreference('client', 'captchaActivatedRequired', null);
    }

    public function getInfoForAppointment(): mixed
    {
        return $this->getPreference('appointment', 'infoForAppointment', null);
    }

    public function getInfoForAllAppointments(): mixed
    {
        return $this->getPreference('appointment', 'infoForAllAppointments', null);
    }


    public function getProvider(): Provider
    {
        if (!isset($this->provider)) {
            throw new Exception\ScopeMissingProvider("Provider is missing", 500);
        }
        $provider = $this->provider;
        if (!$provider instanceof Provider) {
            $provider = new Provider($this->toProperty()->provider->get());
            $this->provider = $provider;
        }
        if (!isset($provider['id']) || !$provider->id) {
            $exception = new Exception\ScopeMissingProvider("No reference to a provider found for scope $this->id");
            $exception->data['scope'] = $this->getArrayCopy();
            throw $exception;
        }
        return $provider;
    }

    public function getProviderId(): mixed
    {
        return $this->getProvider()->id;
    }

    public function getDayoffList(): DayoffList
    {
        $dayoff = $this->dayoff ?? [];
        if (!$dayoff instanceof Collection\DayoffList) {
            $dayoff = is_array($dayoff) ? $dayoff : [];
            $dayoff = new Collection\DayoffList($dayoff);
            foreach ($dayoff as $key => $entry) {
                if (!$entry instanceof Dayoff) {
                    $dayoff[$key] = new Dayoff($entry);
                }
            }
            $this->dayoff = $dayoff;
        }
        return $dayoff;
    }

    public function getClosureList(): ClosureList
    {
        $closure = $this->closure ?? [];
        if (!$closure instanceof Collection\ClosureList) {
            $closure = is_array($closure) ? $closure : [];
            $closure = new Collection\ClosureList($closure);
            foreach ($closure as $key => $entry) {
                if (!$entry instanceof Closure) {
                    $closure[$key] = new Closure($entry);
                }
            }
            $this->closure = $closure;
        }
        return $closure;
    }

    public function getRequestList(): \BO\Zmsentities\Collection\RequestList
    {
        return $this->getProvider()->getRequestList();
    }

    /**
     * @param false|null|string $isBool
     *
     */
    public function getPreference(string $preferenceKey, string $index, string|false|null $isBool = false, mixed $default = null): mixed
    {
        $preference = $this->toProperty()->preferences->$preferenceKey->$index->get($default);
        return ($isBool !== false && $isBool !== null && $isBool !== '' && $isBool !== '0')
            ? ($preference ? 1 : 0)
            : $preference;
    }

    public function getStatus(string $statusKey, string $index): mixed
    {
        return $this->toProperty()->status->$statusKey->$index->get();
    }

    public function getContactEmail(): mixed
    {
        return $this->toProperty()->contact->email->get();
    }

    public function getName(): mixed
    {
        return $this->toProperty()->contact->name->get();
    }

    public function getScopeInfo(): mixed
    {
        return $this->toProperty()->preferences->ticketprinter->buttonName->get();
    }

    public function getScopeHint(): mixed
    {
        return $this->toProperty()->hint->get();
    }

    public function getDisplayNumberPrefix(): mixed
    {
        return $this->toProperty()->preferences->queue->displayNumberPrefix->get();
    }

    public function getAlternateRedirectUrl(): mixed
    {
        $alternateUrl = $this->toProperty()->preferences->client->alternateAppointmentUrl->get();

        return ($alternateUrl) ? $alternateUrl : null;
    }

    public function getAppointmentsPerMail(): mixed
    {
        $appointmentsPerMail = $this->toProperty()->preferences->client->appointmentsPerMail->get();

        return ($appointmentsPerMail) ? $appointmentsPerMail : null;
    }

    public function getSlotsPerAppointment(): mixed
    {
        $slotsPerAppointment = $this->toProperty()->preferences->client->slotsPerAppointment->get();

        return ($slotsPerAppointment) ? $slotsPerAppointment : null;
    }

    public function getWhitelistedMails(): mixed
    {
        $emails = $this->toProperty()->preferences->client->whitelistedMails->get();

        return ($emails) ? $emails : '';
    }

    public function getReservationDuration(): mixed
    {
        return $this->toProperty()->preferences->appointment->reservationDuration->get();
    }

    public function getWaitingTimeFromQueueList(Collection\QueueList $queueList, \DateTimeInterface $dateTime): mixed
    {
        return $queueList->getEstimatedWaitingTime(
            $this->getPreference('queue', 'processingTimeAverage'),
            $this->getCalculatedWorkstationCount(),
            $dateTime
        );
    }

    public function getCalculatedWorkstationCount(): mixed
    {
        $workstationCount = null;
        if ($this->getStatus('queue', 'workstationCount') > 0) {
            $workstationCount = $this->getStatus('queue', 'workstationCount');
        } elseif ($this->getStatus('queue', 'ghostWorkstationCount') > 0) {
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

    public function updateStatusQueue(\DateTimeInterface $dateTime): static
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

    public function incrementDisplayNumber(): static
    {
        if ($this->getStatus('queue', 'lastDisplayNumber') >= $this->getStatus('queue', 'maxDisplayNumber')) {
            $this->setStatusQueue('lastDisplayNumber', 1);
            return $this;
        }

        $this->setStatusQueue('lastDisplayNumber', $this->getStatus('queue', 'lastDisplayNumber') + 1);
        return $this;
    }

    public function hasEmailFrom(): bool
    {
        $emailFrom = $this->getPreference('client', 'emailFrom');
        return ($emailFrom) ? true : false;
    }

    public function isEmailRequired(): bool
    {
        $emailFrom = $this->getPreference('client', 'emailFrom');
        $emailRequired = $this->getPreference('client', 'emailRequired');
        return ($emailFrom && $emailRequired) ? true : false;
    }

    public function isTelephoneRequired(): bool
    {
        $telephoneRequired = $this->getPreference('client', 'telephoneRequired');
        return $telephoneRequired ? true : false;
    }

    /**
     * @return bool
     */
    #[\Override]
    public function hasAccess(Useraccount $useraccount): bool
    {
        return $useraccount->isSuperUser() ||  $useraccount->hasScope($this->id);
    }

    /**
     * Keep provider and source unchanged (non-superusers must not reassign scope location data).
     */
    public function withProviderSourceFrom(self $reference): self
    {
        $entity = clone $this;
        $entity['source'] = $reference->getSource();
        $entity->provider = new Provider($reference->getProvider()->getArrayCopy());
        return $entity;
    }

    /**
     * Reduce data of dereferenced entities to a required minimum
     *
     * @return static
     */
    #[\Override]
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

    public function setStatusQueue(string $key, int $value): static
    {
        $this->status['queue'][$key] = $value;
        return $this;
    }

    public function setStatusAvailability(mixed $key, mixed $value): static
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

    public function assertBookableHorizon(): void
    {
        $startInDays = (int) $this->getPreference('appointment', 'startInDaysDefault', false, 0);
        $endInDays = (int) $this->getPreference('appointment', 'endInDaysDefault', false, 0);
        $maxBookableInDays = Availability::getMaxBookableInDays();
        if ($startInDays <= $maxBookableInDays && $endInDays <= $maxBookableInDays) {
            return;
        }

        $exception = new Exception\SchemaValidation(
            sprintf(
                'Bitte geben Sie bei \'Terminvergabe bis\' höchstens %d Tage im Voraus ein.',
                $maxBookableInDays
            )
        );
        $exception->setSchemaName($this->getEntityName());
        $exception->data['/preferences/appointment/endInDaysDefault'] = [
            'messages' => [
                'maximum' => sprintf(
                    'Bitte geben Sie bei \'Terminvergabe bis\' höchstens %d Tage im Voraus ein.',
                    $maxBookableInDays
                ),
            ],
            'headline' => '/preferences/appointment/endInDaysDefault',
            'failed' => 1,
            'data' => $endInDays,
        ];
        throw $exception;
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
