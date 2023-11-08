<?php

namespace BO\Zmsclient;

class WorkstationRequests
{
    /**
     * @var \BO\Zmsclient\Http $http
     */
    protected $http;

    /**
     * @var \BO\Zmsentities\Workstation $workstation
     */
    protected $workstation;

    /**
     * @var \BO\Zmsentities\Cluster $cluster
     */
    protected $cluster;

    /**
     * @var \BO\Zmsentities\Department $department
     */
    protected $department;

    /**
     * @var \BO\Zmsentities\Scope $scope
     */
    protected $scope;


    public function __construct(
        Http $http,
        \BO\Zmsentities\Workstation $workstation
    ) {
        $this->http = $http;
        $this->workstation = $workstation;
        $this->scope = $workstation->getScope();
    }

    public function getScope(): \BO\Zmsentities\Scope
    {
        return $this->scope;
    }

    public function setDifferentScope(\BO\Zmsentities\Scope $scope): self
    {
        $this->scope = $scope;
        return $this;
    }

    public function readDepartment(): \BO\Zmsentities\Department
    {
        if (!$this->department) {
            $this->department = $this->http->readGetResult('/scope/'. $this->scope['id'] .'/department/')
                ->getEntity();
        }
        return $this->department ? $this->department : new \BO\Zmsentities\Department();
    }

    public function readCluster(): \BO\Zmsentities\Cluster
    {
        if (!$this->cluster) {
            $this->cluster = $this->http->readGetResult('/scope/'. $this->scope['id'] .'/cluster/')
                ->getEntity();
        }
        return $this->cluster ? $this->cluster : new \BO\Zmsentities\Cluster();
    }

    public function readProcessListByDate(
        \DateTimeInterface $selectedDate,
        $gql = ""
    ) : \BO\Zmsentities\Collection\ProcessList {
        if ($this->workstation->isClusterEnabled()) {
            $processList = $this->http
                ->readGetResult(
                    '/cluster/'. $this->readCluster()->id .'/process/'. $selectedDate->format('Y-m-d') .'/',
                    [
                        'resolveReferences' => 1,
                        'gql' => $gql
                    ]
                )
                ->getCollection();
        } else {
            $processList = $this->http
                ->readGetResult(
                    '/scope/'. $this->scope['id'] .'/process/'. $selectedDate->format('Y-m-d') .'/',
                    [
                        'resolveReferences' => 1,
                        'gql' => $gql
                    ]
                )
                ->getCollection();
        }
        return ($processList) ? $processList : new \BO\Zmsentities\Collection\ProcessList();
    }


    public function readNextProcess($excludedIds)
    {
        if ($this->workstation->isClusterEnabled()) {
            $process = $this->http
                ->readGetResult('/cluster/'. $this->cluster['id'] .'/queue/next/', ['exclude' => $excludedIds])
                ->getEntity();
        } else {
            $process = $this->http
                ->readGetResult(
                    '/scope/'. $this->scope['id'] .'/queue/next/',
                    ['exclude' => $excludedIds]
                )
                ->getEntity();
        }
        return $process;
    }
}
