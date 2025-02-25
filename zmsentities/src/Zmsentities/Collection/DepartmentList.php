<?php

namespace BO\Zmsentities\Collection;

use BO\Zmsentities\Helper\Property;

class DepartmentList extends Base implements JsonUnindexed
{
    public const ENTITY_CLASS = '\BO\Zmsentities\Department';

    public function withOutClusterDuplicates()
    {
        $departmentList = new self();
        foreach ($this as $department) {
            $entity = new \BO\Zmsentities\Department($department);
            $departmentList->addEntity($entity->withOutClusterDuplicates());
        }
        return $departmentList;
    }

    public function getUniqueScopeList()
    {
        $scopeList = new ScopeList();
        $clusterList = $this->getUniqueClusterList();
        foreach ($this as $department) {
            $entity = new \BO\Zmsentities\Department($department);
            foreach ($entity->scopes as $scope) {
                $scope = new \BO\Zmsentities\Scope($scope);
                $scopeList->addEntity($scope);
            }
        }
        foreach ($clusterList as $cluster) {
            foreach ($cluster->scopes as $scope) {
                $scope = new \BO\Zmsentities\Scope($scope);
                $scopeList->addEntity($scope);
            }
        }
        return $scopeList->withUniqueScopes();
    }

    public function getUniqueClusterList()
    {
        $clusterList = new ClusterList();
        foreach ($this as $department) {
            if (Property::__keyExists('clusters', $department)) {
                foreach ($department['clusters'] as $cluster) {
                    $entity = new \BO\Zmsentities\Cluster($cluster);
                    $clusterList->addEntity($entity);
                }
            }
        }
        return $clusterList->withUniqueClusters();
    }

    public function withAccess(\BO\Zmsentities\Useraccount $useraccount)
    {
        $list = new static();
        foreach ($this as $department) {
            if ($department->hasAccess($useraccount)) {
                if ($useraccount->rights['organisation']) {
                    return clone $this;
                }
                $list->addEntity(clone $department);
            }
        }
        return $list;
    }

    public function withMatchingScopes(ScopeList $scopeList)
    {
        $list = new static();
        foreach ($this as $department) {
            $entity = clone $department;
            $entity->scopes = new ScopeList();
            $departmentScopeList = $department->getScopeList()->withUniqueScopes();
            foreach ($scopeList as $scope) {
                if ($departmentScopeList->hasEntity($scope->id)) {
                    $entity->scopes->addEntity($scope);
                }
            }
            if ($entity->scopes->count()) {
                $list->addEntity($entity);
            }
        }
        return $list;
    }

    public function sortByName()
    {
        parent::sortByName();
        foreach ($this as $department) {
            if (isset($department->clusters) && $department->clusters instanceof ClusterList) {
                $department->clusters->sortByName();
            }
            if (isset($department->scopes) && $department->scopes instanceof ScopeList) {
                $department->scopes->sortByName();
            }
        }
        return $this;
    }
}
