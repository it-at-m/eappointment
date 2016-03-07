<?php
namespace BO\Zmsentities;

class Process extends Schema\Entity
{

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
        foreach ($this['requests'] as $request) {
            $idList[] = $request['id'];
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

    public function addClient($formData)
    {
        $client = new Client();
        foreach($formData as $key => $item){
            if (null !== $item['value'] && array_key_exists($key, $client)){
               $client[$key] = $item['value'];
            }
        }
        $this->clients[] = $client;
        return $this;
    }

    public function addAmendment($formData)
    {
        $this['amendment'] = $formData['amendment']['value'];
        return $this;
    }

    public function hasAppointment($date, $scope)
    {
        foreach($this->appointments as $key => $item){
            if($item['date'] == $date && $item['scope']['id'] == $scope){
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
        return ($this->scope['id']) ? $this->scope['id'] : 0;
    }

    public function getAmendment()
    {
        return ($this->amendment) ? $this->amendment : '';
    }

    public function getFirstClient()
    {
        if (count($this->clients)){
            $data = current($this->clients);
            $client = new Client($data);
        }
        else {
            $client = new Client();
        }

        return $client;
    }

    public function getFirstAppointmentDateTime()
    {
        $appointment = current($this->appointments);
        $date = \DateTime::createFromFormat('U', $appointment['date']);
        return $date;
    }

}
