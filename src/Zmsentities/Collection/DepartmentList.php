<?php
namespace BO\Zmsentities\Collection;

class DepartmentList extends Base implements JsonUnindexed
{
    const ENTITY_CLASS = '\BO\Zmsentities\Department';

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
            if (array_key_exists('clusters', $department)) {
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
                $list[] = clone $department;
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
