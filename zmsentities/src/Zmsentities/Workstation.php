<?php

namespace BO\Zmsentities;

use BO\Zmsentities\Helper\Property;

/**
 * @SuppressWarnings(Complexity)
 *
 * @property int|string $id
 * @property Useraccount $useraccount
 * @property Process $process
 * @property string $name
 * @property Scope $scope
 * @property array $queue
 */
class Workstation extends Schema\Entity
{
    public const string PRIMARY = 'id';

    public static $schema = "workstation.json";

    /**
     * @return (Process|Scope|Useraccount|int|string)[]
     *
     */
    #[\Override]
    public function getDefaults()
    {
        return [
            'id' => 0,
            'useraccount' => new Useraccount(),
            'process' => new Process(),
            'name' => '',
            'scope' => new Scope()
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

    public function getUseraccount(): Useraccount
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

    public function getDepartmentList(): Collection\DepartmentList
    {
        $departmentList = new Collection\DepartmentList();
        foreach ($this->getUseraccount()->departments as $department) {
            $departmentList->addEntity(new Department($department));
        }
        return $departmentList;
    }

    /**
     * @return void
     */
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

    public function hasSuperUseraccount()
    {
        return $this->getUseraccount()->isSuperUser();
    }

    public function hasAuditAccount()
    {
        return $this->getUseraccount()->hasPermissions(['logs']);
    }

    public function getAuthKey(): string
    {
        return bin2hex(openssl_random_pseudo_bytes(16));
    }

    public function hasAuthKey(): bool
    {
        return (isset($this->authkey)) ? true : false;
    }

    public function getVariantName(): string
    {
        return (! trim($this->name)) ? 'counter' : 'workstation';
    }

    public function getName()
    {
        return ($this->name) ? $this->name : "Tresen";
    }

    public function getScope(): Scope
    {
        $scope = $this->offsetExists('scope') ? $this['scope'] : null;
        if (!$scope instanceof Scope) {
            $scope = new Scope($scope);
            $this['scope'] = $scope;
        }
        return $scope;
    }

    public function getProcess(): Process
    {
        $process = $this->offsetExists('process') ? $this['process'] : null;
        if (!$process instanceof Process) {
            $process = new Process($process);
            $this['process'] = $process;
        }
        return $process;
    }

    public function getScopeList($cluster = null): Collection\ScopeList
    {
        $scopeList = new Collection\ScopeList();
        $scopeList->addEntity(new Scope($this->getScope()));
        if ($cluster && 1 == $this->queue['clusterEnabled']) {
            $scopeList = new Collection\ScopeList();
            $scopeList->addList($cluster->scopes);
        }
        return $scopeList;
    }

    public function getScopeListFromAssignedDepartments(): Collection\ScopeList
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

    /**
     * @return void
     */
    public function validateProcessScopeAccess($scopeList, $process = null)
    {
        if (null === $process) {
            $process = $this->process;
        }
        if (! $scopeList->hasEntity($process->getScopeId())) {
            $exception = new Exception\WorkstationProcessMatchScopeFailed();
            $scopeContactName = null;
            $currentScope = $process->getCurrentScope();
            if ($currentScope && isset($currentScope['contact']['name'])) {
                $scopeContactName = $currentScope['contact']['name'];
            }

            $exception->data = [
                'id' => $process->getId(),
                'scope' => [
                    'id' => $process->getScopeId(),
                    'contact' => [
                        'name' => $scopeContactName
                    ]
                ]
            ];
            throw $exception;
        }
    }

    public function setValidatedName(array $formData): static
    {
        if (isset($formData['workstation']) && trim($formData['workstation']->getValue())) {
            $this->name = $formData['workstation']->getValue();
        } elseif (isset($formData['workstation']) && ! trim($formData['workstation']->getValue())) {
            $this->name = '';
        }
        return $this;
    }

    public function setValidatedHint(array $formData): static
    {
        if (isset($formData['hint']) && $formData['hint']->getValue()) {
            $this->hint = $formData['hint']->getValue();
        } elseif (isset($formData['hint']) && ! $formData['hint']->getValue()) {
            $this->hint = '';
        }
        return $this;
    }

    public function setValidatedScope(array $formData): static
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

    public function setValidatedAppointmentsOnly(array $formData): static
    {
        $this->queue['appointmentsOnly'] = (isset($formData['appointmentsOnly'])) ?
            $formData['appointmentsOnly']->getValue() :
            0;
        return $this;
    }

    public function isClusterEnabled(): bool
    {
        return $this->queue['clusterEnabled'] ? true : false;
    }

    public function hasAccessToUseraccount($useraccount): bool
    {
        $departmentList = $this->getDepartmentList();
        $accessedList = $departmentList->withAccess($useraccount);
        return ($accessedList->count()) ? true : false;
    }
}
