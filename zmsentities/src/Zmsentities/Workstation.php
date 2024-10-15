<?php

namespace BO\Zmsentities;

use \BO\Zmsentities\Helper\Property;

/**
 * @SuppressWarnings(Complexity)
 *
 */
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
        if (isset($this['queue']) && Property::__keyExists($key, $this['queue'])) {
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
        if (Property::__keyExists('rights', $this->useraccount)) {
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

    public function hasAuditAccount()
    {
        $userRights = $this->getUseraccountRights();
        if (isset($userRights['audit']) && $userRights['audit']) {
            return true;
        }

        return false;
    }

    public function getAuthKey()
    {
        return bin2hex(openssl_random_pseudo_bytes(16));
    }

    public function hasAuthKey()
    {
        return (isset($this->authkey)) ? true : false;
    }

    public function getVariantName()
    {
        return (! trim($this->name)) ? 'counter' : 'workstation';
    }

    public function getName()
    {
        return ($this->name) ? $this->name : "Tresen";
    }

    public function getScope()
    {
        if (!$this->offsetExists('scope')) {
            $this->scope = new Scope();
        } elseif (!$this->scope instanceof Scope) {
            $this->scope = new Scope($this->scope);
        }
        return $this->scope;
    }

    public function getScopeList($cluster = null)
    {
        $scopeList = new Collection\ScopeList();
        $scopeList->addEntity(new Scope($this->getScope()));
        if ($cluster && 1 == $this->queue['clusterEnabled']) {
            $scopeList = new Collection\ScopeList();
            $scopeList->addList($cluster->scopes);
        }
        return $scopeList;
    }

    public function getScopeListFromAssignedDepartments()
    {
        $scopeList = new Collection\ScopeList();
        foreach ($this->getDepartmentList() as $department) {
            $scopeList->addList($department->getScopeList());
        }
        foreach ($this->getScopeList() as $scope) {
            if (! $scopeList->hasEntity($scope->id) && $scope instanceof Scope) {
                $scopeList->addEntity($scope);
            }
        }
        return $scopeList;
    }

    public function testMatchingProcessScope($scopeList, Process $process = null)
    {
        if (null === $process) {
            $process = $this->process;
        }
        if (! $scopeList->hasEntity($process->getScopeId())) {
            $exception = new Exception\WorkstationProcessMatchScopeFailed();
            $exception->data = $process;
            throw $exception;
        }
    }

    public function setValidatedName(array $formData)
    {
        if (isset($formData['workstation']) && trim($formData['workstation']->getValue())) {
            $this->name = $formData['workstation']->getValue();
        } elseif (isset($formData['workstation']) && ! trim($formData['workstation']->getValue())) {
            $this->name = '';
        }
        return $this;
    }

    public function setValidatedHint(array $formData)
    {
        if (isset($formData['hint']) && $formData['hint']->getValue()) {
            $this->hint = $formData['hint']->getValue();
        } elseif (isset($formData['hint']) && ! $formData['hint']->getValue()) {
            $this->hint = '';
        }
        return $this;
    }

    public function setValidatedScope(array $formData)
    {
        if (isset($formData['scope']) && 'cluster' === $formData['scope']->getValue()) {
            $this->queue['clusterEnabled'] = 1;
        } elseif (isset($formData['scope'])) {
            $this->queue['clusterEnabled'] = 0;
            $this->scope = new Scope([
                'id' => $formData['scope']->getValue(),
            ]);
        }
        return $this;
    }

    public function setValidatedAppointmentsOnly(array $formData)
    {
        $this->queue['appointmentsOnly'] = (isset($formData['appointmentsOnly'])) ?
            $formData['appointmentsOnly']->getValue() :
            0;
        return $this;
    }

    public function isClusterEnabled()
    {
        return $this->queue['clusterEnabled'] ? true : false;
    }

    public function hasAccessToUseraccount($useraccount)
    {
        $departmentList = $this->getDepartmentList();
        $accessedList = $departmentList->withAccess($useraccount);
        return ($accessedList->count()) ? true : false;
    }
}
