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

    /** @psalm-api */
    public function getScope(): Scope
    {
        return $this->scope;
    }

    /** @psalm-api */
    public function setDifferentScope(Scope $scope): self
    {
        $this->scope = $scope;
        return $this;
    }

    public function readDepartment(): Department
    {
        if (!$this->department instanceof Department) {
            $entity = $this->http->readGetResult('/scope/' . $this->scope['id'] . '/department/')
                ->getEntity();
            $this->department = $entity instanceof Department ? $entity : new Department();
        }
        return $this->department;
    }

    public function readCluster(): Cluster
    {
        if (!$this->cluster instanceof Cluster) {
            $entity = $this->http->readGetResult('/scope/' . $this->scope['id'] . '/cluster/')
                ->getEntity();
            $this->cluster = $entity instanceof Cluster ? $entity : new Cluster();
        }
        return $this->cluster;
    }

    public function readProcessListByDate(
        DateTimeInterface $selectedDate,
        string $gql = ""
    ): ProcessList {
        $processList = $this->workstation->isClusterEnabled()
            ? $this->http
                ->readGetResult(
                    '/cluster/' . $this->readCluster()['id'] . '/process/' . $selectedDate->format('Y-m-d') . '/',
                    [
                        'gql' => $gql
                    ]
                )
                ->getCollection()
            : $this->http
                ->readGetResult(
                    '/scope/' . $this->scope['id'] . '/process/' . $selectedDate->format('Y-m-d') . '/',
                    [
                        'gql' => $gql
                    ]
                )
                ->getCollection();
        if ($processList instanceof ProcessList) {
            return $processList;
        }
        return new ProcessList();
    }


    /**
     * @psalm-api
     */
    public function readNextProcess(mixed $excludedIds): \BO\Zmsentities\Schema\Entity|false|null
    {
        $exclude = is_array($excludedIds) ? implode(',', $excludedIds) : $excludedIds;
        if ($this->workstation->isClusterEnabled()) {
            $process = $this->http
                ->readGetResult(
                    '/cluster/' . $this->readCluster()['id'] . '/queue/next/',
                    ['exclude' => $exclude]
                )
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
