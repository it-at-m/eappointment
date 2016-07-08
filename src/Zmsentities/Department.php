<?php

namespace BO\Zmsentities;

class Department extends Schema\Entity
{
    const PRIMARY = 'id';

    public static $schema = "department.json";

    public function hasId()
    {
        return (array_key_exists('id', $this)) ? true : false;
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
        $clusterScopeList = new Collection\ScopeList();
        foreach ($this->clusters as $cluster) {
            foreach ($cluster['scopes'] as $clusterScope) {
                $clusterScopeList->addEntity(new Scope($clusterScope));
            }
        }
        $scopeList = new Collection\ScopeList();
        foreach ($this->scopes as $scope) {
            if (!$clusterScopeList->hasEntity($scope['id'])) {
                $scopeList->addEntity(new Scope($scope));
            }
        }
        $this->scopes = $scopeList;
        return $this;
    }
}
