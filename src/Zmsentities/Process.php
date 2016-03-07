<?php
namespace BO\Zmsentities;

class Process extends Schema\Entity
{

    public static $schema = "process.json";

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

    public function addFormData($formData)
    {
        foreach ($formData as $param => $item) {
            $this->clients[$param] = $item['value'];
        }
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
}
