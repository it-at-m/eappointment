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
            'queue' => new Queue(),
            'reminderTimestamp' => 0,
            'requests' => new Collection\RequestList(),
            'scope' => new Scope(),
            'status' => 'free'
        ];
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
        foreach (explode(',', $requestCSV) as $id) {
            $this->requests[] = new Request(array(
                'source' => $source,
                'id' => $id
            ));
            ;
        }
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

    public function addAppointment(Appointment $newappointment)
    {
        $this->appointments[] = $newappointment;
        return $this;
    }

    public function getScopeId()
    {
        return $this->toProperty()->scope->id->get();
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

    public function isProcessed()
    {
        if ('called' == $process->status || 'processing' == $process->status) {
            return true;
        }
        return false;
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

    public function __toString()
    {
        $string = "process#";
        $string .= $this->id;
        $string .= ":".$this->authKey;
        $string .= " (" . $this->status . ")";
        $string .= " " . $this->getFirstAppointment()->toDateTime()->format('c');
        $string .= " " . $this->getFirstAppointment()->slotCount."slots";
        $string .= "*" . count($this->appointments);
        foreach ($this->requests as $request) {
            $string .= " " . $request['source'] . "." . $request['id'];
        }
        $string .= " scope." . $this['scope']['id'];
        return $string;
    }
}
