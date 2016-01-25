<?php
namespace BO\Zmsentities;

class Calendar extends Schema\Entity
{

    public static $schema = "calendar.json";

    public function addProvider($source, $idList)
    {
        foreach (explode(',', $idList) as $id) {
            $provider = new Provider();
            $provider->source = $source;
            $provider->id = $id;
            $this->providers[] = $provider;
        }
        return $this;
    }

    public function addRequest($source, $requestList)
    {
        foreach (explode(',', $requestList) as $id) {
            $request = new Request();
            $request->source = $source;
            $request->id = $id;
            $this->requests[] = $request;
        }
        return $this;
    }

    public function getScopeList()
    {
        $list = array();
        foreach ($this->scopes as $scope) {
            $list[] = $scope['id'];
        }
        return $list;
    }
}
