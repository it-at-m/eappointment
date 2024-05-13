<?php
namespace BO\Zmsentities;

use \BO\Zmsentities\Helper\Property;

/**
 * @SuppressWarnings(Complexity)
 * @SuppressWarnings(Coupling)
 * @SuppressWarnings(Public)
 *
 */
class Process extends Schema\Entity
{
    const PRIMARY = 'id';

    public const STATUS_FREE       = 'free';
    public const STATUS_RESERVED   = 'reserved';
    public const STATUS_CONFIRMED  = 'confirmed';
    public const STATUS_PRECONFIRMED  = 'preconfirmed';
    public const STATUS_QUEUED     = 'queued';
    public const STATUS_CALLED     = 'called';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_PENDING    = 'pending';
    public const STATUS_PICKUP     = 'pickup';
    public const STATUS_FINISHED   = 'finished';
    public const STATUS_MISSED     = 'missed';
    public const STATUS_PARKED     = 'parked';
    public const STATUS_ARCHIVED   = 'archived';
    public const STATUS_DELETED    = 'deleted';
    public const STATUS_ANONYMIZED = 'anonymized';
    public const STATUS_BLOCKED    = 'blocked';
    public const STATUS_CONFLICT   = 'conflict';

    public static $schema = "process.json";

    public function getDefaults()
    {
        return [
            'amendment' => '',
            'customTextfield' => '',
            'appointments' => new Collection\AppointmentList(),
            'apiclient' => new Apiclient(),
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
            'status' => 'free',
            'lastChange' => time()
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
        return $this->getRequests()->getIdsCsv();
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

    public function updateRequests($source, $requestCSV = '')
    {
        $this->requests = new Collection\RequestList();
        if ($requestCSV) {
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
        }
        return $this;
    }

    public function hasScopeAdmin()
    {
        return ('' != $this->toProperty()->scope->contact->email->get());
    }

    public function sendAdminMailOnConfirmation()
    {
        return (bool)((int)$this->toProperty()->scope->preferences->client->adminMailOnAppointment->get());
    }
    
    public function sendAdminMailOnDeleted()
    {
        return (bool)((int)$this->toProperty()->scope->preferences->client->adminMailOnDeleted->get());
    }

    public function sendAdminMailOnUpdated()
    {
        return (bool)((int)$this->toProperty()->scope->preferences->client->adminMailOnUpdated->get());
    }

    public function withUpdatedData($requestData, \DateTimeInterface $dateTime, $scope = null, $notice = '')
    {
        $this->scope = ($scope) ? $scope : $this->scope;
        $this->addAppointmentFromRequest($requestData, $dateTime);
        $requestCsv = isset($requestData['requests']) ? implode(',', $requestData['requests']) : 0;
        $this->updateRequests($scope->getSource(), $requestCsv);
        $this->addClientFromForm($requestData);
        $this->addReminderTimestamp($requestData, $dateTime);
        $this->addAmendment($requestData, $notice);
        return $this;
    }

    public function addAppointmentFromRequest($requestData, \DateTimeInterface $dateTime)
    {
        $this->appointments = null;
        if (isset($requestData['selecteddate'])) {
            $dateTime = new \DateTime($requestData['selecteddate']);
        }
        if (isset($requestData['selectedtime'])) {
            $time = explode('-', $requestData['selectedtime']);
            $dateTime->setTime($time[0], $time[1]);
        }

        $appointment = (new Appointment)
            ->addDate($dateTime->getTimestamp())
            ->addScope($this->scope['id']);
        if (isset($requestData['slotCount'])) {
            $appointment->addSlotCount($requestData['slotCount']);
        }
        $this->addAppointment($appointment);
        return $this;
    }

    public function addClientFromForm($requestData)
    {
        $client = new Client();
        foreach ($requestData as $key => $value) {
            if (null !== $value && $client->offsetExists($key)) {
                $client[$key] = (isset($value['value'])) ? $value['value'] : $value;
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

    public function addReminderTimestamp($input, \DateTimeInterface $dateTime)
    {
        $this->reminderTimestamp = (
            Property::__keyExists('headsUpTime', $input) &&
            $input['headsUpTime'] > 0
        ) ? $dateTime->getTimestamp() - $input['headsUpTime'] : 0;
        return $this;
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

    /**
     * check if process is with appointment and not only queued
     * return Boolean
     */
    public function isWithAppointment()
    {
        $appointment = $this->getFirstAppointment();
        if ($appointment->hasTime()) {
            return true;
        }
        return (1 == $this->toProperty()->queue->withAppointment->get());
    }

    public function hasProcessCredentials()
    {
        return (isset($this['id']) && isset($this['authKey']) && $this['id'] && $this['authKey']);
    }

    public function withReassignedCredentials($process)
    {
        $this->id = $process->getId();
        $this->authKey = $process->getAuthKey();
        return $this;
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
        //TK 2020-09-28 changed because pickup and pending processes have assigned pickup scope
        //as current scope - see zmsdb Query/Process EntityMapping
        return $this->toProperty()->scope->id->get();
    }

    public function getCurrentScope(): Scope
    {
        return $this->getProperty('scope');
    }

    public function getAmendment()
    {
        return $this->toProperty()->amendment->get();
    }

    public function getShowUpTime()
    {
        return $this->toProperty()->showUpTime->get();
    }

    public function getWaitingTime()
    {
        return $this->toProperty()->queue->waitingTime->get();
    }

    public function getProcessingTime()
    {
        return $this->toProperty()->processingTime->get();
    }

    public function getFinishTime()
    {
        return $this->toProperty()->finishTime->get();
    }

    public function addAmendment($input, $notice = '')
    {
        $this->amendment = $notice;
        $this->amendment .= (isset($input['amendment']) && $input['amendment']) ? $input['amendment'] : '';
        trim($this->amendment);
        return $this;
    }

    public function getCustomTextfield()
    {
        return $this->toProperty()->customTextfield->get();
    }

    public function addCustomTextfield($input, $notice = '')
    {
        $this->customTextfield = $notice;
        $this->customTextfield .= (
            isset($input['customTextfield']) && $input['customTextfield']
        ) ? $input['customTextfield'] : '';
        trim($this->customTextfield);
        return $this;
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
        return $this->getCallTime()->format('H:i:s');
    }

    public function getCallTime($default = 'now', $timezone = null)
    {
        $callTime = $this->toProperty()->queue->callTime->get();
        $callDateTime = Helper\DateTime::create($default, $timezone);
        if ($callTime) {
            $callDateTime = $callDateTime->setTimestamp($callTime);
        }
        return $callDateTime;
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

    public function getFirstAppointment(): Appointment
    {
        $appointment = $this->getAppointments()->getFirst();
        if (!$appointment) {
            $appointment = new Appointment();
            $appointment->scope = $this->scope;
            $this->appointments->addEntity($appointment);
        }
        return $appointment;
    }

    public function setStatusBySettings()
    {
        $scope = new Scope($this->scope);
        if ('called' == $this->status && $this->queue['callCount'] > $scope->getPreference('queue', 'callCountMax')) {
            $this->status = 'missed';
        } elseif ('parked' == $this->status) {
            $this->status = 'parked';
        } elseif ('pickup' == $this->status) {
            $this->status = 'queued';
        } else {
            $this->status = 'confirmed';
        }
        return $this;
    }

    public function setClientsCount($count)
    {
        $clientList = $this->getClients();
        while ($clientList->count() < $count) {
            $clientList->addEntity(new Client());
        }
        return $this;
    }


    public function withoutPersonalData()
    {
        $entity = clone $this;
        if ($this->toProperty()->clients->isAvailable()) {
            $client = $entity->getFirstClient();
            unset($client['familyName']);
            unset($client['email']);
        }
        return $entity;
    }

    /**
     * Reduce data of dereferenced entities to a required minimum
     *
     */
    public function withLessData(array $keepArray = [])
    {
        $entity = clone $this;

        if (! in_array('availability', $keepArray)) {
            foreach ($entity['appointments'] as $appointment) {
                if ($appointment->toProperty()->scope->isAvailable()) {
                    $scopeId = $appointment['scope']['id'];
                    unset($appointment['scope']);
                    $appointment['scope'] = ['id' => $entity->toProperty()->scope->id->get()];
                    if ($scopeId != $entity->toProperty()->scope->id->get()) {
                        $appointment['scope'] = ['id' => $scopeId];
                    }
                }
                if ($appointment->toProperty()->availability->isAvailable()) {
                    unset($appointment['availability']);
                }
            }
        }

        unset($entity['createTimestamp']);
        unset($entity['createIP']);

        if ($entity->toProperty()->scope->status->isAvailable()) {
            unset($entity['scope']['status']);
        }

        if ($entity->status == 'free') {
            // delete keys
            foreach ([
                'authKey',
                'queue',
                'requests',
            ] as $key) {
                if (! in_array($key, $keepArray) && $entity->toProperty()->$key->isAvailable()) {
                    unset($entity[$key]);
                }
            }
            // delete if empty
            foreach ([
                'amendment',
                'id',
                'authKey',
                'archiveId',
                'reminderTimestamp',
            ] as $key) {
                if (! in_array($key, $keepArray) && $entity->toProperty()->$key->isAvailable() && !$entity[$key]) {
                    unset($entity[$key]);
                }
            }
            if (! in_array('provider', $keepArray) && $entity->toProperty()->scope->provider->data->isAvailable()) {
                unset($entity['scope']['provider']['data']);
            }
        }

        if (! in_array('dayoff', $keepArray) && $entity->toProperty()->scope->dayoff->isAvailable()) {
            unset($entity['scope']['dayoff']);
        }
        if (! in_array('scope', $keepArray) && $entity->toProperty()->scope->preferences->isAvailable()) {
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
        $queue->withAppointment = ($this->getFirstAppointment()->hasTime()) ? true : false;
        $queue->waitingTime = ($queue->waitingTime) ? $queue->waitingTime : 0;
        if ($queue->withAppointment) {
            $queue->number = $this->id;
        } else {
            $queue->number = $this->toProperty()->queue->number->get();
        }
        $queue->arrivalTime = $this->getArrivalTime($dateTime)->getTimestamp();
        return $queue->setProcess($this);
    }

    public function hasArrivalTime()
    {
        $arrivalTime = 0;
        if ($this->isWithAppointment()) {
            $arrivalTime = $this->getFirstAppointment()->date;
        } else {
            $arrivalTime = $this->toProperty()->queue->arrivalTime->get();
        }
        return ($arrivalTime) ? true : false;
    }

    public function getArrivalTime($default = 'now', $timezone = null)
    {
        $arrivalTime = 0;
        if ($this->isWithAppointment()) {
            $arrivalTime = $this->getFirstAppointment()->date;
        } else {
            $arrivalTime = $this->toProperty()->queue->arrivalTime->get();
        }
        $arrivalDateTime = Helper\DateTime::create($default, $timezone);
        if ($arrivalTime) {
            $arrivalDateTime = $arrivalDateTime->setTimestamp($arrivalTime);
        }
        return $arrivalDateTime;
    }

    /**
     * Calculate real waiting time, only available after called
     */
    public function getWaitedSeconds($defaultTime = 'now')
    {
        return $this->getCallTime($defaultTime)->getTimestamp() - $this->getArrivalTime($defaultTime)->getTimestamp();
    }

    public function getWaitedMinutes($defaultTime = 'now')
    {
        return round($this->getWaitedSeconds($defaultTime) / 60, 0);
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

    public function toDerefencedCustomTextfield()
    {
        $lastChange = (new \DateTimeImmutable)->setTimestamp($this->createTimestamp)->format('c');
        return var_export(
            array(
                'BuergerID' => $this->id,
                'StandortID' => $this->scope['id'],
                'CustomTextfield' => $this->customTextfield,
                'IPTimeStamp' => $this->createTimestamp,
                'LastChange' => $lastChange,
            ),
            1
        );
    }

    public function __toString()
    {
        $string = "process#";
        $string .= $this->id ?: $this->archiveId;
        $string .= ":".$this->authKey;
        $string .= " (" . $this->status . ")";
        $string .= " " . $this->getFirstAppointment()->toDateTime()->format('c');
        $string .= " " . ($this->isWithAppointment() ? "appoint" : "arrival:" . $this->getArrivalTime()->format('c'));
        $string .= " " . $this->getFirstAppointment()->slotCount."slots";
        $string .= "*" . count($this->appointments);
        foreach ($this->getRequests() as $request) {
            $string .= " " . $request['source'] . "." . $request['id'];
        }
        $string .= " scope." . $this['scope']['id'];
        $string .= " ~" . base_convert($this['lastChange'], 10, 35);
        $string .= " client:" . $this['apiclient']['shortname'];
        return $string;
    }
}