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
        if (isset($this['requests']) && count($this['requests']) > 0) {
            foreach ($this['requests'] as $request) {
                $idList[] = $request['id'];
            }
        }
        return $idList;
    }

    public function getRequestCSV()
    {
        return implode(',', $this->getRequestIds());
    }

    /**
     * Returns calendar with added Providers
     *
     * @return $this
     */
    public function addScope($scopeId)
    {
        $scope = new Scope();
        $scope->id = $scopeId;
        $this->scope = $scope;
        return $this;
    }

    public function addRequests($source, $requestList)
    {
        foreach (explode(',', $requestList) as $id) {
            $request = new Request();
            $request->source = $source;
            $request->id = $id;
            $this->requests[] = $request;
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

    public function addClient($formData)
    {
        $client = new Client();
        foreach ($formData as $key => $item) {
            if (null !== $item['value'] && array_key_exists($key, $client)) {
                $client[$key] = $item['value'];
            }
        }
        $this->clients[] = $client;
        return $this;
    }

    public function updateClients($client)
    {
        $this->clients[0] = $client;
        return $this;
    }

    public function addAmendment($formData)
    {
        $this['amendment'] = $formData['amendment']['value'];
        return $this;
    }

    public function hasAppointment($date, $scope)
    {
        foreach ($this->appointments as $item) {
            if ($item['date'] == $date && $item['scope']['id'] == $scope) {
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

    public function getDepartment()
    {
        return (\array_key_exists('department', $this->scope)) ? $this->scope['department'] : null;
    }

    public function getDepartmentId()
    {
        return (\array_key_exists('department', $this->scope)) ? $this->scope['department']['id'] : null;
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
}
