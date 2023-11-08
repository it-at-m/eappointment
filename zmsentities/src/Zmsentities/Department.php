<?php

namespace BO\Zmsentities;

use \BO\Zmsentities\Helper\Property;

class Department extends Schema\Entity implements Useraccount\AccessInterface
{
    const PRIMARY = 'id';

    public static $schema = "department.json";

    public function getDefaults()
    {
        return [
            'id' => 0,
            'scopes' => new Collection\ScopeList(),
            'links' => new Collection\LinkList(),
            'dayoff' => new Collection\DayoffList(),
            'name' => '',
        ];
    }

    public function hasMail()
    {
        return ($this->toProperty()->email->isAvailable() && $this->toProperty()->email->get());
    }

    public function hasNotificationEnabled()
    {
        $prefs = $this->getNotificationPreferences();
        return ($prefs['identification'] && $prefs['enabled']);
    }

    public function hasNotificationReminderEnabled()
    {
        $prefs = $this->getNotificationPreferences();
        return ($this->hasNotificationEnabled() && $prefs['sendReminderEnabled']);
    }

    public function hasNotificationConfirmationEnabled()
    {
        $prefs = $this->getNotificationPreferences();
        return ($this->hasNotificationEnabled() && $prefs['sendConfirmationEnabled']);
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

    public function getClusterByScopeId($scopeId)
    {
        $selectedCluster = false;
        if (isset($this->clusters)) {
            foreach ($this->clusters as $cluster) {
                $cluster = new Cluster($cluster);
                foreach ($cluster['scopes'] as $clusterScope) {
                    if ($scopeId == $clusterScope['id']) {
                        $selectedCluster = $cluster->getId();
                        break;
                    }
                }
            }
        }
        return $selectedCluster;
    }

    /**
     * Remove duplicate scopes from clusters
     * Move scopes to clusters to keep the same resolveReference Level
     */
    public function withOutClusterDuplicates()
    {
        $department = clone $this;
        if ($this->offsetExists('scopes') && $this->scopes) {
            $scopeList = clone $this->scopes;
            $department->scopes = new Collection\ScopeList();
            $removeScopeList = new Collection\ScopeList();
            if ($department->toProperty()->clusters->get()) {
                foreach ($department->clusters as $cluster) {
                    $cluster = new Cluster($cluster);
                    foreach ($cluster['scopes'] as $key => $clusterScope) {
                        $scope = $scopeList->getEntity($clusterScope['id']);
                        if ($scope) {
                            $scope = new Scope($scope);
                            $cluster['scopes'][$key] = clone $scope;
                            $removeScopeList[] = $scope;
                        }
                    }
                }
                foreach ($scopeList as $scope) {
                    if (! $removeScopeList->hasEntity($scope['id'])) {
                        $scope = new Scope($scope);
                        $department->scopes->addEntity($scope);
                    }
                }
            }
        }
        return $department;
    }

    public function withCompleteScopeList()
    {
        $department = clone $this;
        $department->scopes = $this->getScopeList()->withUniqueScopes();
        return $department;
    }

    public function getScopeList()
    {
        $scopeList = new Collection\ScopeList();
        if ($this->toProperty()->clusters->isAvailable()) {
            foreach ($this->clusters as $cluster) {
                if (Property::__keyExists('scopes', $cluster)) {
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

    /**
     * Reduce data of dereferenced entities to a required minimum
     *
     */
    public function withLessData()
    {
        $entity = clone $this;
        if (isset($entity['preferences'])) {
            unset($entity['preferences']);
            $entity['preferences'] = [];
            $entity['preferences']['notifications'] = [];
            $entity['preferences']['notifications']['enabled'] = $this['preferences']['notifications']['enabled'];
        }
        if (isset($entity['email'])) {
            unset($entity['email']);
        }
        if (isset($entity['scopes'])) {
            unset($entity['scopes']);
        }
        if (isset($entity['clusters'])) {
            unset($entity['clusters']);
        }
        if (isset($entity['links'])) {
            unset($entity['links']);
        }
        if (isset($entity['dayoff'])) {
            unset($entity['dayoff']);
        }
        if (isset($entity['contact'])) {
            unset($entity['contact']);
        }
        return $entity;
    }
}
