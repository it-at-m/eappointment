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

    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }
}
