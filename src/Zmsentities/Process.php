<?php
namespace BO\Zmsentities;

/**
 * @SuppressWarnings(Complexity)
 * @SuppressWarnings(Coupling)
 * @SuppressWarnings(Public)
 *
 */
class Process extends Schema\Entity
{
    const PRIMARY = 'id';

    public static $schema = "process.json";

    public function getDefaults()
    {
        return [
            'amendment' => '',
            'appointments' => new Collection\AppointmentList(),
            'authKey' => '',
            'clients' => new Collection\ClientList(),
            'createIP' => '',
            'createTimestamp' => time(),
            'id' => 0,
            'archiveId' => 0,
            'queue' => new Queue(),
            'reminderTimestamp' => 0,
            'requests' => new Collection\RequestList(),
            'scope' => new Scope(),
            'status' => 'free'
        ];
    }

    public static function createFromScope(Scope $scope, \DateTimeInterface $dateTime)
    {
        $appointment = new Appointment();
        $appointment->addScope($scope->id);
        $appointment->addSlotCount(0);
        $appointment->addDate($dateTime->modify('00:00:00')->getTimestamp());
        $process = new static();
        $process->scope = $scope;
        $process->setStatus('queued');
        $process->addAppointment($appointment);
        return $process;
    }

    /**
     * @return Collection\RequestList
     *
     */
    public function getRequests()
    {
        if (!$this->requests instanceof Collection\RequestList) {
            $requestList = new Collection\RequestList();
            foreach ($this->requests as $request) {
                $request = ($request instanceof Request) ? $request : new Request($request);
                $requestList->addEntity($request);
            }
            $this->requests = $requestList;
        }
        return $this->requests;
    }

    public function getRequestIds()
    {
        return $this->getRequests()->getIds();
    }

    public function getRequestCSV()
    {
        return $this->getRequests()->getCSV();
    }

    public function addScope($scopeId)
    {
        $this->scope = new Scope(array('id' => $scopeId));
        return $this;
    }

    public function addQueue($number, \DateTimeInterface $dateTime)
    {
        $this->queue = new Queue(array(
            'number' => $number,
            'arrivalTime' => $dateTime->getTimestamp()
        ));
        return $this;
    }

    public function addRequests($source, $requestCSV)
    {
        $requestList = $this->getRequests();
        foreach (explode(',', $requestCSV) as $id) {
            if (! $requestList->hasRequests($id)) {
                $this->requests[] = new Request(array(
                    'source' => $source,
                    'id' => $id
                ));
            }
        }
        return $this;
    }

    public function updateRequests($source, $requestCSV)
    {
        $this->requests = new Collection\RequestList();
        foreach (explode(',', $requestCSV) as $id) {
            $this->requests->addEntity(
                new Request(
                    array(
                        'source' => $source,
                        'id' => $id
                    )
                )
            );
        }
        return $this;
    }

    public function hasScopeAdmin()
    {
        return ('' != $this->toProperty()->scope->contact->email->get());
    }

    public function withUpdatedData($formData, $requestData, $scope = null, $dateTime = null)
    {
        if ($dateTime) {
            $this->addAppointment(
                (new Appointment())
                    ->addDate($dateTime->getTimestamp())
                    ->addScope($scope['id'])
                    ->addSlotCount($requestData['slotCount'])
            );
        }
        if ($scope) {
            $this->scope = $scope;
        }
        $this->updateRequests('dldb', implode(',', $formData['requests']['value']));
        $this->addClientFromForm($formData);
        $this->reminderTimestamp = (array_key_exists('headsUpTime', $requestData) && $requestData['headsUpTime'] > 0) ?
            $dateTime->getTimestamp() - $requestData['headsUpTime'] : 0;
        $this->amendment = (array_key_exists('amendment', $formData)) ?
            $formData['amendment']['value'] : null;
        return $this;
    }

    public function addClientFromForm($formData)
    {
        $client = new Client();
        foreach ($formData as $key => $item) {
            if (null !== $item['value'] && array_key_exists($key, $client)) {
                $client[$key] = $item['value'];
            }
        }
        $this->clients = array();
        $this->clients[] = $client;
        return $this;
    }

    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function getReminderTimestamp()
    {
        $timestamp = $this->toProperty()->reminderTimestamp->get();
        return ($timestamp) ? $timestamp : 0;
    }

    /**
     * @return \BO\Zmsentities\Collection\AppointmentList
     *
     */
    public function getAppointments()
    {
        if (!$this['appointments'] instanceof Collection\AppointmentList) {
            $this['appointments'] = new Collection\AppointmentList($this['appointments']);
            foreach ($this['appointments'] as $index => $appointment) {
                if (! $appointment instanceof Appointment) {
                    $this['appointments'][$index] = new Appointment($appointment);
                }
            }
        }
        return $this['appointments'];
    }

    /**
     * @return \BO\Zmsentities\Collection\ClientList
     *
     */
    public function getClients()
    {
        if (!$this['clients'] instanceof Collection\ClientList) {
            $this['clients'] = new Collection\ClientList($this['clients']);
            foreach ($this['clients'] as $index => $client) {
                if (! $client instanceof Client) {
                    $this['clients'][$index] = new Client($client);
                }
            }
        }
        return $this['clients'];
    }

    public function hasAppointment($date, $scopeId)
    {
        return $this->getAppointments()->hasDateScope($date, $scopeId);
    }

    public function hasProcessCredentials()
    {
        return (isset($this['id']) && isset($this['authKey']) && $this['id'] && $this['authKey']);
    }

    public function hasQueueNumber()
    {
        return (isset($this['queue']) && isset($this['queue']['number']) && $this['queue']['number']);
    }

    public function addAppointment(Appointment $newappointment)
    {
        $this->appointments[] = $newappointment;
        return $this;
    }

    /**
     * Reminder: A process might have multiple scopes. Each appointment can
     * have his own scope. The scope in $this->scope is the current/next scope.
     * This function returns the original scope ID and ignores internal scope
     * which are used for processing like to pick up documents
     *
     */
    public function getScopeId()
    {
        if ($this->status == 'pending' || $this->status == 'pickup') {
            $scope = $this->getFirstAppointment()->getScope();
            $scopeId = $scope->id;
        } else {
            $scopeId = $this->toProperty()->scope->id->get();
        }
        return $scopeId;
    }

    public function getAmendment()
    {
        return $this->toProperty()->amendment->get();
    }

    public function getAuthKey()
    {
        return $this->toProperty()->authKey->get();
    }

    public function setRandomAuthKey()
    {
        $this->authKey = substr(md5(rand()), 0, 4);
    }

    public function setCallTime($dateTime = null)
    {
        $this->queue['callTime'] = ($dateTime) ? $dateTime->getTimestamp() : 0;
        return $this;
    }

    public function getCallTimeString()
    {
        return (new \DateTimeImmutable)->setTimestamp($this->queue['callTime'])->format('H:i:s');
    }

    public function getFirstClient()
    {
        $client = $this->getClients()->getFirst();
        if (!$client) {
            $client = new Client();
            $this->clients->addEntity($client);
        }
        return $client;
    }

    public function setClientsCount($count)
    {
        $clientList = $this->getClients();
        while ($clientList->count() < $count) {
            $clientList->addEntity(new Client());
        }
        return $this;
    }

    public function getFirstAppointment()
    {
        $appointment = $this->getAppointments()->getFirst();
        if (!$appointment) {
            $appointment = new Appointment();
            $appointment->scope = $this->scope;
            $this->appointments->addEntity($appointment);
        }
        return $appointment;
    }

    public function isConfirmationSmsRequired()
    {
        $prop = $this->toProperty();
        return (
            $prop->clients[0]->telephone->get()
            && $prop->scope->department->preferences->notifications->enabled->get()
            && $prop->scope->department->preferences->notifications->sendConfirmationEnabled->get()
        );
    }

    public function setStatusBySettings()
    {
        $scope = new Scope($this->scope);
        if ('called' == $this->status && $this->queue['callCount'] > $scope->getPreference('queue', 'callCountMax')) {
            $this->status = 'missed';
        } elseif ('pickup' == $this->status) {
            $this->status = 'queued';
        } else {
            $this->status = 'confirmed';
        }
        return $this;
    }

    public function withoutPersonalData()
    {
        $entity = clone $this;
        if ($this->toProperty()->clients->isAvailable()) {
            unset($entity['clients']);
        }
        if ($this->toProperty()->appointments->isAvailable()) {
            unset($entity['appointments']);
        }
        return $entity;
    }

    /**
     * Reduce data of dereferenced entities to a required minimum
     *
     */
    public function withLessData()
    {
        $entity = clone $this;

        foreach ($entity['appointments'] as $appointment) {
            if ($appointment->toProperty()->scope->isAvailable()) {
                $scopeId = $appointment['scope']['id'];
                unset($appointment['scope']);
                $appointment['scope'] = ['id' => $scopeId];
            }
            if ($appointment->toProperty()->availability->isAvailable()) {
                unset($appointment['availability']);
            }
        }
        unset($entity['createTimestamp']);
        unset($entity['createIP']);
        if ($entity->toProperty()->scope->status->isAvailable()) {
            unset($entity['scope']['status']);
        }
        if ($entity->toProperty()->scope->dayoff->isAvailable()) {
            unset($entity['scope']['dayoff']);
        }
        if ($entity->toProperty()->scope->preferences->isAvailable()) {
            unset($entity['scope']['preferences']);
        }
        return $entity;
    }

    public function toCalendar()
    {
        $calendar = new Calendar();
        $dateTime = $this->getFirstAppointment()->toDateTime();
        $day = new Day();
        $day->setDateTime($dateTime);
        $calendar->firstDay = $day;
        $calendar->lastDay = $day;
        $calendar->requests = clone $this->getRequests();
        $calendar->scopes = new Collection\ScopeList([$this->scope]);
        return $calendar;
    }

    public function toQueue(\DateTimeInterface $dateTime)
    {
        $queue = new Queue($this->queue);
        $queue->withAppointment = ($this->getAppointments()->getFirst()->hasTime()) ? true : false;
        $queue->waitingTime = ($queue->waitingTime) ? $queue->waitingTime : 0;
        if ($queue->withAppointment) {
            $queue->number = $this->id;
            $queue->arrivalTime = $this->getFirstAppointment()->date;
        } else {
            $queue->number = $this->toProperty()->queue->number->get();
            $queue->arrivalTime = ($queue->arrivalTime) ? $queue->arrivalTime : $dateTime->getTimestamp();
        }
        return $queue->setProcess($this);
    }

    /**
     * Calculate real waiting time, only available after called
     */
    public function getWaitedSeconds()
    {
        if (!$this->queue->callTime) {
            return null;
        }
        return $this->queue->arrivalTime - $this->queue->callTime;
    }

    public function toDerefencedAmendment()
    {
        $lastChange = (new \DateTimeImmutable)->setTimestamp($this->createTimestamp)->format('c');
        return var_export(
            array(
                'BuergerID' => $this->id,
                'StandortID' => $this->scope['id'],
                'Anmerkung' => $this->amendment,
                'IPTimeStamp' => $this->createTimestamp,
                'LastChange' => $lastChange,
            ),
            1
        );
    }

    public function __toString()
    {
        $string = "process#";
        $string .= $this->id;
        $string .= ":".$this->authKey;
        $string .= " (" . $this->status . ")";
        $string .= " " . $this->getFirstAppointment()->toDateTime()->format('c');
        $string .= " " . $this->getFirstAppointment()->slotCount."slots";
        $string .= "*" . count($this->appointments);
        foreach ($this->getRequests() as $request) {
            $string .= " " . $request['source'] . "." . $request['id'];
        }
        $string .= " scope." . $this['scope']['id'];
        return $string;
    }
}
