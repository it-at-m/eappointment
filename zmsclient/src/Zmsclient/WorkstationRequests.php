<?php

namespace BO\Zmsclient;

use DateTimeInterface;
use BO\Zmsentities\Workstation;
use BO\Zmsentities\Cluster;
use BO\Zmsentities\Department;
use BO\Zmsentities\Scope;
use BO\Zmsentities\Collection\ProcessList;

class WorkstationRequests
{
    protected Http $http;
    protected Workstation $workstation;
    protected ?Cluster $cluster = null;
    protected ?Department $department = null;
    protected Scope $scope;

    public function __construct(
        Http $http,
        Workstation $workstation
    ) {
        $this->http = $http;
        $this->workstation = $workstation;
        $this->scope = $workstation->getScope();
    }

    public function getScope(): Scope
    {
        return $this->scope;
    }

    public function setDifferentScope(Scope $scope): self
    {
        $this->scope = $scope;
        return $this;
    }

    public function readDepartment(): Department
    {
        if (!$this->department) {
            $this->department = $this->http->readGetResult('/scope/' . $this->scope['id'] . '/department/')
                ->getEntity();
        }
        return $this->department ? $this->department : new Department();
    }

    public function readCluster(): Cluster
    {
        if (!$this->cluster) {
            $this->cluster = $this->http->readGetResult('/scope/' . $this->scope['id'] . '/cluster/')
                ->getEntity();
        }
        return $this->cluster ? $this->cluster : new Cluster();
    }

    public function readProcessListByDate(
        DateTimeInterface $selectedDate,
        $gql = ""
    ): ProcessList {
        if ($this->workstation->isClusterEnabled()) {
            $processList = $this->http
                ->readGetResult(
                    '/cluster/' . $this->readCluster()->id . '/process/' . $selectedDate->format('Y-m-d') . '/',
                    [
                        'gql' => $gql
                    ]
                )
                ->getCollection();
        } else {
            $processList = $this->http
                ->readGetResult(
                    '/scope/' . $this->scope['id'] . '/process/' . $selectedDate->format('Y-m-d') . '/',
                    [
                        'gql' => $gql
                    ]
                )
                ->getCollection();
        }
        return ($processList) ? $processList : new ProcessList();
    }


    public function readNextProcess($excludedIds)
    {
        $exclude = is_array($excludedIds) ? implode(',', $excludedIds) : $excludedIds;
        if ($this->workstation->isClusterEnabled()) {
            $process = $this->http
                ->readGetResult('/cluster/' . $this->cluster['id'] . '/queue/next/', ['exclude' => $exclude])
                ->getEntity();
        } else {
            $process = $this->http
                ->readGetResult(
                    '/scope/' . $this->scope['id'] . '/queue/next/',
                    ['exclude' => $exclude]
                )
                ->getEntity();
        }
        return $process;
    }
}
