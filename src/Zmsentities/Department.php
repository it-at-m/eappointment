<?php

namespace BO\Zmsentities;

class Department extends Schema\Entity
{
    const PRIMARY = 'id';

    public static $schema = "department.json";

    public function getDefaults()
    {
        return [
            'name' => '',
            'scopes' => new Collection\ScopeList(),
        ];
    }

    public function hasNotificationEnabled()
    {
        if (isset($this->preferences['notifications'])) {
            if (array_key_exists('enabled', $this->preferences['notifications'])) {
                return ($this->preferences['notifications']['enabled']) ? true : false;
            }
        }
        return false;
    }

    public function setNotificationPreferences($status = true)
    {
        if ($status) {
            $this->preferences['notifications']['enabled'] = 1;
        } else {
            unset($this->preferences['notifications']['enabled']);
        }
        return $this;
    }

    public function getNotificationPreferences()
    {
        return ($this->preferences['notifications']);
    }

    public function getContactPerson()
    {
        return $this->contact['name'];
    }

    public function getContact()
    {
        return new Contact($this->contact);
    }

    public function withOutClusterDuplicates()
    {
        $department = clone $this;
        $clusterScopeList = new Collection\ScopeList();
        foreach ($department->clusters as $cluster) {
            foreach ($cluster['scopes'] as $clusterScope) {
                $clusterScopeList->addEntity(clone $clusterScope);
            }
        }
        $scopeList = new Collection\ScopeList();
        foreach ($department->scopes as $scope) {
            if (!$clusterScopeList->hasEntity($scope['id'])) {
                var_dump($scope);
                $scopeList->addEntity(clone $scope);
            }
        }
        $department->scopes = $scopeList;
        return $department;
    }
}
