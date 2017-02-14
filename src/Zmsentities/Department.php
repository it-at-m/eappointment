<?php

namespace BO\Zmsentities;

class Department extends Schema\Entity
{
    const PRIMARY = 'id';

    public static $schema = "department.json";

    public function getDefaults()
    {
        return [
            'id' => false,
            'scopes' => new Collection\ScopeList(),
        ];
    }

    public function getNotificationPreferences()
    {
        return $this->toProperty()->preferences->notifications->get();
    }

    public function getContactPerson()
    {
        return $this->toProperty()->contact->name->get();
    }

    public function getContact()
    {
        return new Contact($this->toProperty()->contact->get());
    }

    public function getDayoffList()
    {
        if (!$this->dayoff instanceof Collection\DayoffList) {
            $this->dayoff = new Collection\DayoffList((array)$this->dayoff);
            foreach ($this->dayoff as $key => $dayOff) {
                if (!$dayOff instanceof Dayoff) {
                    $this->dayoff[$key] = new Dayoff($dayOff);
                }
            }
        }
        return $this->dayoff;
    }

    public function withOutClusterDuplicates()
    {
        $department = clone $this;
        if ($department->toProperty()->clusters->get()) {
            $clusterScopeList = new Collection\ScopeList();
            foreach ($department->clusters as $cluster) {
                if (array_key_exists('scopes', $cluster)) {
                    foreach ($cluster['scopes'] as $clusterScope) {
                        $scope = new Scope($clusterScope);
                        $clusterScopeList->addEntity($scope);
                    }
                }
            }
            $scopeList = new Collection\ScopeList();
            foreach ($department->scopes as $scope) {
                if (! $clusterScopeList->hasEntity($scope['id'])) {
                    $scope = new Scope($scope);
                    $scopeList->addEntity($scope);
                }
            }
            $department->scopes = $scopeList;
        }
        return $department;
    }

    public function withCompleteScopeList()
    {
        $department = clone $this;
        $scopeList = new Collection\ScopeList();

        foreach ($department->scopes as $scope) {
            $scopeList->addEntity(new Scope($scope));
        }

        foreach ($department->clusters as $cluster) {
            if (array_key_exists('scopes', $cluster)) {
                foreach ($cluster['scopes'] as $clusterScope) {
                    $scopeList->addEntity(new Scope($clusterScope));
                }
            }
        }

        $department->scopes = $scopeList->withUniqueScopes();

        return $department;
    }

    public function getScopeList()
    {
        $scopeList = new Collection\ScopeList();
        if ($this->toProperty()->clusters->isAvailable()) {
            foreach ($this->clusters as $cluster) {
                if (array_key_exists('scopes', $cluster)) {
                    foreach ($cluster['scopes'] as $clusterScope) {
                        $scope = new Scope($clusterScope);
                        $scopeList->addEntity($scope);
                    }
                }
            }
        }
        if ($this->toProperty()->scopes->isAvailable()) {
            foreach ($this->scopes as $scope) {
                if (! $scopeList->hasEntity($scope['id'])) {
                    $scope = new Scope($scope);
                    $scopeList->addEntity($scope);
                }
            }
        }
        return $scopeList;
    }

    public function hasAccess(Useraccount $useraccount)
    {
        return $useraccount->hasRights(['superuser']) || $useraccount->hasDepartment($this->id);
    }
}
