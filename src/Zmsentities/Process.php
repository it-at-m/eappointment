<?php
namespace BO\Zmsentities;

class Process extends Schema\Entity
{
    const PRIMARY = 'id';

    public static $schema = "process.json";

    public function getDefaults()
    {
        return [
            'amendment' => '',
            'appointments' => [],
            'authKey' => '',
            'clients' => [],
            'createIP' => '',
            'createTimestamp' => '',
            'id' => 0,
            'queue' => [],
            'reminderTimestamp' => 0,
            'requests' => [],
            'scope' => [],
            'status' => ''
        ];
    }

    public function getRequestIds()
    {
        $idList = array();
        $requests = $this->toProperty()->requests->get();
        if ($requests) {
            foreach ($requests as $request) {
                $idList[] = $request['id'];
            }
        }
        return $idList;
    }

    public function getRequestCSV()
    {
        return implode(',', $this->getRequestIds());
    }

    public function addScope($scopeId)
    {
        $this->scope = new Scope(array('id' => $scopeId));
        return $this;
    }

    public function addRequests($source, $requestList)
    {
        foreach (explode(',', $requestList) as $id) {
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

    public function updateClients($client)
    {
        $this->clients[0] = $client;
        return $this;
    }

    public function hasAppointment($date, $scopeId)
    {
        foreach ($this->appointments as $item) {
            if ($item['date'] == $date && $item['scope']['id'] == $scopeId) {
                return true;
            }
        }
        return false;
    }

    public function addAppointment(Appointment $newappointment)
    {
        $this->appointments[] = $newappointment;
        return $this;
    }

    public function getScopeId()
    {
        return (\array_key_exists('id', $this->scope)) ? $this->scope['id'] : null;
    }

    public function getAmendment()
    {
        return ($this->amendment) ? $this->amendment : null;
    }

    public function getAuthKey()
    {
        return ($this->authKey) ? $this->authKey : null;
    }

    public function getFirstClient()
    {
        $client = null;
        if (count($this->clients) > 0) {
            $data = current($this->clients);
            $client = new Client($data);
        }
        return $client;
    }

    public function getFirstAppointment()
    {
        $appointment = null;
        if (count($this->appointments) > 0) {
            $data = current($this->appointments);
            $appointment = new Appointment($data);
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
}
