<?php

namespace BO\Zmsentities;

class Workstation extends Schema\Entity
{
    const PRIMARY = 'id';

    public static $schema = "workstation.json";

    public function getDefaults()
    {
        return [
            'useraccount' => new Useraccount(),
            'process' => new Process(),
            'name' => '',
        ];
    }

    public function getQueuePreference($key, $isBoolean = false)
    {
        $result = null;
        if (array_key_exists($key, $this['queue'])) {
            if ($isBoolean) {
                $result = ($this['queue'][$key]) ? 1 : 0;
            } else {
                $result = $this['queue'][$key];
            }
        }
        return $result;
    }

    public function getUseraccount()
    {
        if (!$this->useraccount instanceof Useraccount) {
            $this->useraccount = new Useraccount($this->useraccount);
        }
        return $this->useraccount;
    }

    public function getDepartmentById($departmentId)
    {
        return $this->getUseraccount()->getDepartmentById($departmentId);
    }

    public function getDepartmentList()
    {
        $departmentList = new Collection\DepartmentList();
        foreach ($this->getUseraccount()->departments as $department) {
            $departmentList->addEntity(new Department($department));
        }
        return $departmentList;
    }

    public function testDepartmentList()
    {
        if (0 == $this->getDepartmentList()->count()) {
            throw new Exception\WorkstationMissingAssignedDepartments();
        }
    }

    public function getProviderOfGivenScope()
    {
        return $this->toProperty()->scope->provider->id->get();
    }

    public function getUseraccountRights()
    {
        $rights = null;
        if (array_key_exists('rights', $this->useraccount)) {
            $rights = $this->useraccount['rights'];
        }
        return $rights;
    }

    public function hasSuperUseraccount()
    {
        $isSuperuser = false;
        $userRights = $this->getUseraccountRights();
        if (isset($userRights['superuser']) && $userRights['superuser']) {
            $isSuperuser = true;
        }
        return $isSuperuser;
    }

    public function getAuthKey()
    {
        return bin2hex(openssl_random_pseudo_bytes(16));
    }

    public function hasAuthKey()
    {
        return (isset($this->authkey)) ? true : false;
    }

    public function getRedirect()
    {
        return (0 == $this->name) ? 'counter' : 'workstation';
    }

    public function getScopeList($cluster = null)
    {
        $scopeList = new Collection\ScopeList();
        if ($cluster && 1 == $this->queue['clusterEnabled']) {
            foreach ($cluster->scopes as $scope) {
                $scope = new Scope($scope);
                $scopeList->addEntity($scope);
            }
        } else {
            $scope = new Scope($this->scope);
            $scopeList->addEntity($scope);
        }
        return $scopeList;
    }

    public function testMatchingProcessScope(Cluster $cluster, Process $process = null)
    {
        if (null === $process) {
            $process = $this->process;
        }
        $scopeList = $this->getScopeList($cluster);
        if (! $scopeList->hasEntity($process->getScopeId())) {
            throw new Exception\WorkstationProcessMatchScopeFailed();
        }
    }
}
